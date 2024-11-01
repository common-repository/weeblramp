<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author                  weeblrPress
 * @copyright               (c) WeeblrPress - Weeblr,llc - 2020
 * @package                 AMP on WordPress - weeblrAMP CE
 * @license                 http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version                 1.12.5.783
 *
 * 2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Initialization of the plugin
 */
class WeeblrampHelper_Boot {

	const DEVELOPMENT = 0;
	const PRODUCTION = 1;

	const UPDATE_SERVER_URL = 'https://u1.weeblrpress.com/public/direct/weeblramp/update/weeblramp_update.json';
	const UPDATE_CHECK_FREQUENCY = 2;

	private static $updateChecker = null;

	/**
	 * Register weeblrAMP hooks
	 */
	public static function boot() {

		// setup system hooks
		register_activation_hook( WEEBLRAMP_PLUGIN, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( WEEBLRAMP_PLUGIN, array( __CLASS__, 'deactivate' ) );

		// add system actions
		add_action( 'plugins_loaded', array( __CLASS__, 'loaded' ), 100 );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );

		// init after most plugins, to let them time to register their taxonomies and post types
		add_action( 'init', array( __CLASS__, 'init' ), 99 );

		// hook into plugins activation, to update our rewrite rules
		add_action( 'activated_plugin', array( __CLASS__, 'onPluginActivated' ), 10, 2 );
		add_action( 'deactivated_plugin', array( __CLASS__, 'onPluginDeActivated' ), 10, 2 );

		// Setup one-click updater
		// The updater requires setting its own hooks, some of them very early
		// so it must be instantiated here, as early as possible
		// Failsafe: prevent the checker to run on local build, avoid
		// destroying version controlled copy
		if ( is_admin() && self::isFullEdition() && ! self::isDevVersion() ) {
			require_once WBLIB_ROOT_PATH . 'vendor/w-shadow/plugin-update-checker.php';
			self::$updateChecker = new PluginUpdateChecker_3_1 (
				self::UPDATE_SERVER_URL,
				WEEBLRAMP_PLUGIN_FILE,
				WEEBLRAMP_PLUGIN,
				self::UPDATE_CHECK_FREQUENCY
			);
		}
	}

	/**
	 * Loaded hook. Run wbLib startup code (autoloader mostly),
	 * then register weeblrAMP own classes with the autoloader
	 * and register admin menu
	 */
	public static function loaded() {

		try {
			// load a language file
			load_plugin_textdomain( 'weeblramp', false, dirname( WEEBLRAMP_PLUGIN ) . '/languages/' );

			// load libraries and external tools
			self::loadLibraries();
			$wbLib = new Wblib();
			$wbLib->boot();

			// register WeeblrAMP classes with the autoloader
			WblSystem_Autoloader::registerPrefix( 'Weeblramp', WEEBLRAMP_PLUGIN_DIR );

			// load settings page(s) if admin side of things
			if ( is_admin() ) {
				// register our settings hooks with WP:
				// root menu item
				add_action(
					'admin_menu',
					array( 'WeeblrampHelper_Admin', 'addAdminMenu' ),
					WeeblrampViewAdmin_Options::ROOT_MENU_PAGE,
					array( WeeblrampFactory::getThe( 'WeeblrampViewAdmin_Options' ), 'render' )
				);

				// add settings and customize pages
				add_action( 'admin_menu', array(
					WeeblrampFactory::getThe( 'WeeblrampViewAdmin_Options' ),
					'addPage'
				) );
				add_action( 'admin_menu', array(
					WeeblrampFactory::getThe( 'WeeblrampViewAdmin_Customize' ),
					'addPage'
				) );
				add_filter( 'plugin_action_links_' . WEEBLRAMP_PLUGIN, array(
					'WeeblrampHelper_Admin',
					'filter_plugins_action_links'
				) );

				// register post update actions
				add_action(
					'upgrader_process_complete',
					array( 'WeeblrampHelper_Boot', 'postUpdateHook' ),
					10,
					2
				);

				// set a couple of hooks to prevent wp.org updates to show on full edition, on local dev.
				$updateHelper = WeeblrampFactory::getA( 'WeeblrampClass_Update' );
				$updateHelper->hideWporgUpdates();

				// hook into the http requests, to build appropriate authorization headers when updating.
				if ( ! self::isDevVersion() ) {
					add_filter(
						'pre_http_request',
						array(
							$updateHelper,
							'pre_http_request'
						),
						10,
						3
					);
				}
			}

			// emulate some of the standard AMP plugin functions
			include 'boot_functions.php';

			// load integrations, so as to be able to setup filters very early
			WeeblrampHelper_Integrations::load();
		}
		catch ( Exception $e ) {
			$details = sprintf( '%s::%d %s', __METHOD__, __LINE__, 'Loading error: ' . $e->getMessage() );

			WblSystem_Log::error( 'weeblramp', $details );

			// die as gracefully as possible
			wbAdminDie(
				sprintf( 'Error during %s loading', WEEBLRAMP_PLUGIN_NAME ),
				$details,
				'plugins.php'
			);
		}
	}

	/**
	 * Performs tasks required for all admin-side pages
	 */
	public static function admin_init() {

		try {
			// global on/off switch
			if ( false === apply_filters( 'weeblramp_is_enabled', true ) ) {
				return;
			}

			// run post update code
			self::postUpdateActions();

			// register our settings
			$configObjectList = WeeblrampFactory::getThe( 'weeblramp.config.system' )
			                                    ->get( 'config.lists.objects' );
			if ( is_array( $configObjectList ) ) {
				foreach ( $configObjectList as $configObjectName ) {
					WeeblrampFactory::getThe( $configObjectName )->registerSettings();
				}
			}

			// register AMP-only widgets
			WeeblrampHelper_Widget::registerAmpWidgets();

			// register meta boxes
			$postTypes = WeeblrampFactory::getThe( 'weeblramp.config.user' )
			                             ->get( 'amp_post_types' );
			foreach ( $postTypes as $postTypeName => $postTypeDef ) {
				add_action(
					'add_meta_boxes_' . $postTypeName,
					array(
						'WeeblrampHelper_Admin',
						'addMetaBox'
					)
				);
				add_action(
					'save_post_' . $postTypeName,
					array(
						'WeeblrampHelper_Admin',
						'saveMetaBox'
					)
				);
			}

			// allow 3rd-parties to run code
			/**
			 * Hook into the 'admin_init' weeblrAMP event.
			 *
			 * @api
			 * @package weeblrAMP\action\system
			 * @var weeblramp_init
			 * @since   1.0.0
			 *
			 */
			do_action( 'weeblramp_admin_init' );
		}
		catch ( Exception $e ) {
			// don't die during admin_init, that would bring the whole admin down
			$details = sprintf( '%s::%d %s', __METHOD__, __LINE__, 'Admin initialization error: ' . $e->getMessage() );
			WblSystem_Log::error( 'weeblramp', $details );
			WeeblrampHelper_Error::$error = 'Error during ' . WEEBLRAMP_PLUGIN_NAME . ' admin_init';
			add_action( 'admin_notices', array( 'WeeblrampHelper_Error', 'adminInitError' ) );
		}
	}

	/**
	 * Performs tasks required for all front-end pages
	 */
	public static function init() {

		try {
			// global on/off switch
			if ( false === apply_filters( 'weeblramp_is_enabled', true ) ) {
				return;
			}

			// Initialize integrations with other plugins
			WeeblrampHelper_Integrations::init();

			// load user config and load the defaults value (that can't be hardcoded)
			$userConfig = WeeblrampFactory::getThe( 'weeblramp.config.user' )
			                              ->setDefaults();

			// make logging facility check detailed logging time out
			// and update config if changed
			$newPreset = WblSystem_Log::maybeResetLoggingPresetAfterTimeout();
			if ( ! empty( $newPreset ) ) {
				$userConfig->set(
					'logging_level',
					$newPreset
				)
				           ->store();
			}

			// initialize display customization config
			WeeblrampFactory::getThe( 'weeblramp.config.customize' )
			                ->setDefaults();

			// and finally the router
			$router = WeeblrampFactory::getThe( 'WeeblrampClass_Route' );

			// include a custom functions.php created by user in active theme
			// cannot rely on standard functions.php, as it's not loaded in admin
			self::loadUserFunctions();

			// allow 3rd-parties to run code
			/**
			 * Hook into the 'init' weeblrAMP event
			 *
			 * @api
			 * @package weeblrAMP\action\system
			 * @var weeblramp_init
			 * @since   1.0.0
			 *
			 */
			do_action( 'weeblramp_init' );

			// register our supported post types
			$postTypes = $userConfig->get( 'amp_post_types' );
			foreach ( $postTypes as $postTypeName => $postTypeDef ) {
				if ( ! empty( $postTypeDef['enabled'] ) ) {
					add_post_type_support( $postTypeName, $router->getQueryVar() );
				}
			}

			// register AMP endpoint, unless standalone mode
			// and other rewrite rules
			if ( ! $router->isStandaloneMode() ) {
				$router->setRewriteRules( $postTypes );
				WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Set rewriterules for post types: ' . print_r( $postTypes, true ) );
			}

			// force the amp query var
			add_filter( 'request', array( $router, 'filter_request' ) );

			// Possibly flush rewwrite rules (after we added ours)
			// Used after a plugin has been activated, so that we can add
			// rewrite rules for their custom post types and their taxonomies
			$flushRulesRequired = get_option( 'weeblramp_rewrite_rules_flush_required' );
			if ( ! empty( $flushRulesRequired ) ) {
				flush_rewrite_rules();
				update_option( 'weeblramp_rewrite_rules_flush_required', 0 );
				WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Flushed rewrite rules and reset weeblramp_rewrite_rules_flush_required to 0' );
			}

			// hook-up into the WP rendering process
			$dispatcher = WeeblrampFactory::getThe( 'WeeblrampClass_Dispatcher' );
			add_action( 'wp', array( $dispatcher, 'dispatch' ) );
		}
		catch ( Exception $e ) {
			$details = sprintf( '%s::%d %s', __METHOD__, __LINE__, 'Initialization error: ' . $e->getMessage() );
			WblSystem_Log::error( 'weeblramp', $details );
		}
	}

	/**
	 * Action hook for plugin_activated
	 * When a plugin is activated, we set a flag, which will cause the rewrite rules to be
	 * flushed after the activation redirect.
	 *
	 * @param string $plugin
	 * @param bool   $network_wide
	 */
	public static function onPluginActivated( $plugin, $network_wide ) {

		if ( ! is_admin() ) {
			// should not happen
			return;
		}

		// filter out some plugins
		// @TODO move this list to system config
		$excluded = array(
			'weeblramp/weeblramp.php',
			'weeblramp-theme-teal/teal.php',
			'weeblramp-theme-wc/wc.php',
			'weeblramp-theme-edd/edd.php',
			'hello.php'
		);
		if ( in_array( $plugin, $excluded ) ) {
			return;
		}

		// new plugin enabled, flush rewrite rules in case they
		// enable new post types and taxonomies
		update_option( 'weeblramp_rewrite_rules_flush_required', 1 );
	}

	/**
	 * Action hook for plugin_deactivated
	 * When a plugin is deactivated, and it is one of ours, we check
	 * and adjust the currently selected global theme, so as to not use
	 * a deactivated one
	 *
	 * @param string $plugin
	 * @param bool   $network_wide
	 */
	public static function onPluginDeActivated( $plugin, $network_deactivating ) {

		if ( ! is_admin() ) {
			// should not happen
			return;
		}
		try {
			// if a theme plugin has been deactivated, revert to default theme
			// @TODO move this list to system config
			$revertTheme = array(
				'weeblramp-theme-teal/teal.php',
				'weeblramp-theme-wc/wc.php',
				'weeblramp-theme-edd/edd.php',
			);

			if ( in_array( $plugin, $revertTheme ) ) {
				WeeblrampFactory::getThe( 'weeblramp.config.user' )
				                ->reset( 'global_theme' )
				                ->store();
			}
		}
		catch ( Exception $e ) {
			$details = sprintf( '%s::%d %s', __METHOD__, __LINE__, 'Plugin ' . $plugin . ' deactivation error: ' . $e->getMessage() );
			WblSystem_Log::error( 'weeblramp', $details );
		}
	}

	/**
	 * Use the upgrader_process_complete action hook to
	 * perform actions immediately after an upgrade,
	 * performed using the ajax bulk updater.
	 * The standard mechanism (used below in postUpdateActions())
	 * waits til the next backend request to perform post_update actions
	 *
	 * By using upgrader_process_complete hook, we can perform
	 * post update actions in the same request as the ajax update.
	 *
	 * @param $upgrader
	 * @param $params
	 */
	public static function postUpdateHook( $upgrader, $params ) {

		if (
			'update' != wbArrayGet( $params, 'action' )
			||
			'plugin' != wbArrayGet( $params, 'type' )
			||
			true != wbArrayGet( $params, 'bulk' )
		) {
			// not a plugin update through updater, leave
			return;
		}

		if ( ! in_array( WEEBLRAMP_PLUGIN, wbArrayGet( $params, 'plugins', array() ) ) ) {
			// not this plugin update, only flush rewrite rules
			// in case they were updated by the new plugin(s) version
			update_option( 'weeblramp_rewrite_rules_flush_required', 1 );

			return;
		}

		// Clear OPcache
		if ( function_exists( 'opcache_reset' ) ) {
			opcache_reset();
		} elseif ( function_exists( 'apc_clear_cache' ) ) {
			@apc_clear_cache();
		}

		WblSystem_Log::debug( 'weeblramp', 'In upgrader_process_complete hook, triggering post ajax update actions' );

		self::postUpdateActions();
	}

	/**
	 * Actions to be executed after an update
	 * We store version number in an option
	 * so as to be able to check if current code
	 * is from a new version
	 *
	 * This handle post_update actions triggering when
	 * updates is done through FTP(?) or more generally
	 * not using the admin.
	 */
	public static function postUpdateActions() {

		// a funny one: we cannot just hardcode the current version here
		// because when the update is done through ajax, the current file is
		// first loaded and so at post_update we run again the same PHP code
		// and thus the old version is still there
		$version = include 'boot_version.php';

		if ( ! is_admin() || false !== strpos( $version, '@build' ) ) {
			// bail out on frontend or development version
			return;
		}

		// get last installed version
		$optionId        = 'wblib_last_installed_version_' . WEEBLRAMP_PLUGIN;
		$previousVersion = get_option( $optionId, 0 );

		// store new version
		update_option( $optionId, $version );

		// execute callback
		if ( ! empty( $previousVersion ) && version_compare( $version, $previousVersion, '>' ) ) {
			// run update code
			// for the same reason as $version, we must put the post update actions
			// in a separate file, so that we can use it in the same request
			// that initiated the update
			WblSystem_Log::debug( 'weeblramp', 'In postUpdateActions, performing post update actions' );
			include 'post_update_actions.php';
		}
	}

	/**
	 * Activation hook. Activate wbLib, then run system checks to allow activation
	 * and install must-use plugin (to control other plugins activation on AMP pages)
	 */
	public static function activate() {

		try {
			// load libraries and external tools
			self::loadLibraries();
			$wbLib = new Wblib();
			$wbLib
				->boot()
				->activate();

			// register weeblrAMP classes with the autoloader
			WblSystem_Autoloader::registerPrefix( 'Weeblramp', WEEBLRAMP_PLUGIN_DIR );

			// call helper to check php, wp versions
			$errors = WeeblrampHelper_Version::checkSystem();

			// now check if there are active incompatible plugins
			$errors = array_merge(
				$errors,
				WeeblrampHelper_Compat::checkIncompatiblePlugins()
			);

			// install must-use plugin
			if ( empty( $errors ) ) {
				$pluginManagerErrors = self::installPluginManager();
				update_option( 'weeblramp_activation_plugin_manager_errors', $pluginManagerErrors );
				if ( ! empty( $pluginManagerErrors ) ) {
					WblSystem_Log::error( 'weeblramp', '%s::%d %s', __METHOD__, __LINE__, 'Activation error: ' . $pluginManagerErrors );
				}
			}

			// finally run init code
			if ( empty( $errors ) ) {
				// run init, as same code is needed after activation
				self::init();
				update_option( 'weeblramp_rewrite_rules_flush_required', 1 );
			}
		}
		catch ( Exception $e ) {
			// store error for later display
			$errors[] = $e->getMessage();
		}

		// properly display any error, and disable plugin if any
		if ( ! empty( $errors ) ) {
			$errorsMessages = WblHtml_Helper::makeList( $errors );

			// log
			WblSystem_Log::error( 'weeblramp', '%s::%d %s', __METHOD__, __LINE__, 'Activation error: ' . $errorsMessages );

			// in case we did install it
			self::removePluginManager();

			// make sure plugin is disabled
			deactivate_plugins( WEEBLRAMP_PLUGIN );

			// die as gracefully as possible
			WblWordpress_Helper::adminDie(
				sprintf( 'There were one or more issues during %s activation', WEEBLRAMP_PLUGIN_NAME ),
				$errorsMessages,
				'plugins.php'
			);
		}
	}

	/**
	 * De-activation hook.
	 */
	public static function deactivate() {

		try {
			// remove the plugin manager from must-use plugins
			$errors = self::removePluginManager();

			// remove AMP suffix rewrite endpoint
			flush_rewrite_rules();
		}
		catch ( Exception $e ) {
			// store error for later display
			$errors[] = $e->getMessage();
		}

		// properly display any error
		if ( ! empty( $errors ) ) {
			$errorsMessages = WblHtml_Helper::makeList( $errors );

			// log
			WblSystem_Log::error( 'weeblramp', '%s::%d %s', __METHOD__, __LINE__, 'Activation error: ' . $errorsMessages );

			// die as gracefully as possible
			WblWordpress_Helper::adminDie(
				sprintf( 'There were one or more issues during %s activation', WEEBLRAMP_PLUGIN_NAME ),
				$errorsMessages,
				'plugins.php'
			);
		}
	}

	/**
	 * Load functions.php files that user can create under the weeblramp
	 * directory in either child or current theme (in that order)
	 *
	 * Differ from WP loading user functions.php in them in that functions
	 * are loaded both in frontend and admin. More specifically:
	 *
	 * functions.php is loaded first, and always
	 * functions_admin.php is loaded after functions.php (per theme path), and only when in the admin
	 *
	 */
	private static function loadUserFunctions() {

		// child
		$childThemeRoot = get_option( 'weeblramp_current_stylesheet' );
		if ( ! empty( $childThemeRoot ) ) {
			$themePaths = array( $childThemeRoot . '/weeblramp' );
		} else {
			$themePaths = array();
		}

		// then current theme
		$themeRoot = get_option( 'weeblramp_current_template' );
		if ( ! empty( $themeRoot ) ) {
			$themePaths = array_unique( array_merge( $themePaths, array( $themeRoot . '/weeblramp' ) ) );
		}

		// search and load all functions.php
		foreach ( $themePaths as $themePath ) {
			$file = $themePath . '/functions.php';
			if ( file_exists( $file ) ) {
				include_once $file;
			}
			if ( is_admin() ) {
				$file = $themePath . '/functions_admin.php';
				if ( file_exists( $file ) ) {
					include_once $file;
				}
			}
		}
	}

	/**
	 * Install the plugin manager as a Must-Use plugin. It will
	 * disable on the fly any plugin designated by the site admin
	 * on AMP requests.
	 *
	 * @return array Any error
	 */
	private static function installPluginManager() {

		$errors = array();

		// locate mu directory
		$muFolder = defined( 'WPMU_PLUGIN_DIR' ) ? WPMU_PLUGIN_DIR : str_replace( '\\', '/', ABSPATH ) . 'wp-content/mu-plugins';

		// create it if missing
		$createdOrExists = @wp_mkdir_p( $muFolder );
		if ( ! $createdOrExists ) {
			$errors[] = sprintf( __( 'Unable to create directory for weeblrAMP plugins handler. We will not be able to disable plugins on AMP pages. (path: %s)', 'weeblramp' ), $muFolder );

			return $errors;
		}

		// copy our mu plugin to mu-folder
		$copied = @copy(
			WP_PLUGIN_DIR . '/weeblramp/helper/mu_plugins_handler.php.inc',
			$muFolder . '/weeblramp_plugins_handler.php'
		);
		if ( ! $copied ) {
			$errors[] = sprintf( __( 'Unable to copy weeblrAMP plugins handler to Must-use directory. We will not be able to disable plugins on AMP pages. (path: %s)', 'weeblramp' ), $muFolder );

			return $errors;
		}

		return $errors;
	}

	/**
	 * Remove the plugin manager we installed as a must-use plugin
	 * during activation
	 */
	private static function removePluginManager() {

		$errors = array();

		// locate mu directory
		$muFolder = defined( 'WPMU_PLUGIN_DIR' ) ? WPMU_PLUGIN_DIR : str_replace( '\\', '/', ABSPATH ) . 'wp-content/mu-plugins';

		// no such folder, leave
		if ( ! is_dir( $muFolder ) ) {
			return $errors;
		}

		$pluginFile = $muFolder . '/weeblramp_plugins_handler.php';
		if ( file_exists( $pluginFile ) ) {
			$deleted = unlink( $pluginFile );
			if ( ! $deleted ) {
				$errors[] = sprintf( __( 'weeblrAMP was deactivated, but we could not to remove the weeblrAMP plugin manager from the Must-use directory. Please remove it manually (path: %s)', 'weeblramp' ), $pluginFile );
			}
		}

		return $errors;
	}

	/**
	 * Includes for the libaries used.
	 */
	private static function loadLibraries() {

		// load code from wblib
		include_once WBLIB_ROOT_PATH . 'wblib.php';
	}

	/**
	 * Detects whether we are on a local development version.
	 *
	 * @return bool
	 */
	private static function isDevVersion() {

		return strpos( '1.12.5', '_version_' ) !== false;
	}

	/**
	 * Detects whether we are running the full edition.
	 *
	 * @return bool
	 */
	private static function isFullEdition() {

		return 'full' == 'community';
	}
}
