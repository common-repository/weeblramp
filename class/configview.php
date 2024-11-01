<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author       weeblrPress
 * @copyright    (c) WeeblrPress - Weeblr,llc - 2020
 * @package      AMP on WordPress - weeblrAMP CE
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version      1.12.5.783
 *
 * 2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Settings page base class
 */
class WeeblrampClass_Configview {

	const ROOT_MENU_PAGE = 'weeblramp-settings';
	const SETTINGS_PAGE = 'default';

	public $settingsPageHook = '';

	public $title     = '';
	public $menuTitle = '';

	protected $configName = '';
	protected $config     = null;

	/**
	 * WeeblrampClass_Configview constructor.
	 *
	 * Translate titles
	 */
	public function __construct( $options = array() ) {

		$this->title     = __( $this->title, 'weeblramp' );
		$this->menuTitle = __( $this->menuTitle, 'weeblramp' );
		$this->config    = wbArrayGet( $options, 'config' );
		if ( empty( $this->config ) && ! empty( $this->configName ) ) {
			$this->config = WeeblrampFactory::getThe( $this->configName );
		}

		// enqueue required script
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_action_scripts' ) );

		// display admin footer (version, license,...)
		// @TODO Display new versions availability in footer also
		add_filter( 'admin_footer_text', array( $this, 'filter_admin_footer' ) );
	}

	/**
	 * Add an option page for this config page
	 *
	 * @return false|string
	 */
	public function addPage() {

		$this->settingsPageHook = add_submenu_page(
			static::ROOT_MENU_PAGE,
			$this->title,
			$this->menuTitle,
			'manage_options',
			static::SETTINGS_PAGE,
			array(
				$this,
				'render'
			)
		);

		// configuration will need this
		$this->config->setPage( static::SETTINGS_PAGE );
		$this->config->setPageHook( $this->settingsPageHook );

		// allow notices to be displayed on this page
		WeeblrampFactory::getThe( 'WblSystem_Notices' )->addDisplayPage( $this->settingsPageHook );

		return $this->settingsPageHook;
	}

	/**
	 * Display version, (c) and link to home page in settings footer
	 *
	 * @param $footer
	 *
	 * @return string
	 */
	public function filter_admin_footer( $footer ) {

		if ( get_current_screen()->id != $this->settingsPageHook ) {
			return $footer;
		}

		$__data = array(
			'versionInfo' => WeeblrampFactory::getThe( 'weeblramp.version_info' )
		);

		return WblMvcLayout_Helper::render( 'weeblramp.admin.settings.footer', $__data, WEEBLRAMP_LAYOUTS_PATH );
	}

	/**
	 * Render the options page:
	 * 1 - prepare any data
	 * 2 - Display the layout
	 */
	public function render() {

		try {
			// will hold rendered partials
			$__data = array();

			// load user config object and have it render its settings page
			$__data['rendered_settings_page'] = $this->config->renderSettingsPage( static::SETTINGS_PAGE );
			$__data['settings_definitions']   = $this->config->getDefinitions();
			$__data['settings_page']          = static::SETTINGS_PAGE;
			$__data['title']                  = $this->title;

			// display the fully rendered page
			echo WblMvcLayout_Helper::render( 'weeblramp.admin.settings.main', $__data, WEEBLRAMP_LAYOUTS_PATH );
		}
		catch ( Exception $e ) {
			WblWordpress_Helper::adminDie(
				sprintf( 'Error displaying a %s page', WEEBLRAMP_PLUGIN_NAME ),
				$e->getMessage(),
				'options-general.php'
			);
		}

		return $this;
	}

	/**
	 * Add minimal javascript/style to the settings page
	 *
	 */
	public function admin_action_scripts() {

		if ( get_current_screen()->id != $this->settingsPageHook ) {
			return;
		}

		WeeblrampHelper_Admin::admin_action_scripts();
	}
}
