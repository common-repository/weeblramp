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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

class WblSystem_Config {

	/**
	 * Storage prefix for using WP settings API
	 */
	const STORAGE_PREFIX = 'wbl';
	const CONFIG_ID = '__wpamp_default__';

	/*
	* System constant*
	*/
	const OPTION_TEXT = 0;
	const OPTION_TEXTAREA = 1;
	const OPTION_LIST = 2;
	const OPTION_RADIO = 3;
	const OPTION_CHECKBOX = 4;
	const OPTION_SEPARATOR = 5;
	const OPTION_POST_TYPES = 6;
	const OPTION_MEDIA = 7;
	const OPTION_COLOR_PICKER = 8;
	const OPTION_MENUS = 9;
	const OPTION_CHECKBOX_GROUP = 10;
	const OPTION_EDITOR = 11;
	const OPTION_HIDDEN = 12;

	// no saved data
	const OPTION_CLEAR_TRANSIENTS = 100;
	const OPTION_FLUSH_REWRITE_RULES = 101;

	// custom (by other plugins): 500 -> 999

	// visual clues
	const OPTION_SECTION = 1024;
	const OPTION_TAB = 1025;
	const OPTION_HELP = 1026;
	const OPTION_HARDCODED = 1027;
	const OPTION_SETTING_SEPARATOR = 1028;
	const OPTION_RAW_HTML = 1029;

	/**
	 * Remote configuration handling
	 */
	const REMOTE_CONFIG_NOT_AVAILABLE = '__remote_config_not_available__';

	/**
	 * Actions
	 */
	const ACTION_AJAX = 'wp_ajax_wblib_config_action';

	/**
	 * Definition of options, including
	 * names, default values and type
	 *
	 * @var array
	 */
	static protected $defs = array();

	/**
	 * Copy of definitions, accessed directly by key
	 * Easier to access in some situations
	 *
	 * @var array
	 */
	static protected $defsByKey = array();

	/**
	 * Storage for configurations values
	 *
	 * @var null
	 */
	static protected $_configs = array();

	/**
	 * @var null|string
	 */
	protected $currentConfig = null;

	/**
	 * Holds current setting section when rendering
	 * settings page
	 *
	 * @var null
	 */
	protected $currentSection = null;

	/**
	 * Maintains the current tab being rendered,
	 * needed for closing the tab
	 *
	 * @var null
	 */
	protected $currentTab = null;

	/**
	 * Stores nonces to be used in settings pages
	 *
	 * @var array
	 */
	protected $nonces = array();

	/**
	 * The WP admin page name where the config should be displayed
	 *
	 * @var string
	 */
	protected $configPageName = '';

	/**
	 * The WP admin page hook name where the config should be displayed
	 *
	 * @var string
	 */
	protected $configPageHook = '';

	/**
	 * @var bool If true, all configuration items are available regardless of the plugin edition.
	 */
	protected $bypassEditionCheck = false;

	/**
	 * Build the configuration definition variable and the user configuration object
	 *
	 * options:
	 *   load_remote_url  String If not empty, we'll attempt to load a remote copy of this config content
	 *
	 * @param       string Full path to file holding settings definitions
	 * @param array $options
	 */
	public function __construct( $options = array() ) {

		// init current config, but do not override descendant which may already have done that
		$this->currentConfig = empty( $this->currentConfig ) ? static::CONFIG_ID : $this->currentConfig;

		// if not loaded from DB, load the default from file
		if ( ! isset( static::$_configs[ $this->currentConfig ] ) ) {
			$definitionFile = wbArrayGet( $options, 'definition_file', str_replace( '.php', '.cfg.php', __FILE__ ) );
			if ( file_exists( $definitionFile ) ) {
				$configData = include_once $definitionFile;
				if ( is_array( $configData ) ) {
					static::$defs[ $this->currentConfig ]      = $configData;
					static::$defsByKey[ $this->currentConfig ] = array();

					// build an easy to use configuration object from this definition
					$this->buildConfig();
				}
			}
		}

		// override hardcoded config definition with a remote one
		$remoteUrl = wbArrayGet( $options, 'load_remote_url', '' );
		if ( ! empty( $remoteUrl ) ) {
			$this->maybeLoadRemote( $remoteUrl, wbArrayGet( $options, 'remote_config_caching_ttl' ) );
		}

		// try to load it from DB
		if ( wbArrayGet( $options, 'load_values', false ) ) {
			$this->load();
		}

		// hook up ajax handler
		add_action(
			static::ACTION_AJAX,
			array(
				$this,
				'ajaxHandler'
			),
			10,
			2
		);

		// hook up fallback ajax handler
		add_action(
			static::ACTION_AJAX,
			array(
				$this,
				'ajaxHandler'
			),
			9999,
			2
		);

		// let decendant apply any on the fly conversion
		// required by changes in config structure.
		$this->applyUpdates();
	}

	/**
	 * Hook to apply any conversion in config structure that may
	 * be needed in the future.
	 */
	protected function applyUpdates() {
	}

	/**
	 * Setter for the page name where this config object is being displayed
	 *
	 * @param string $page
	 *
	 * @return mixed
	 */
	public function setPage( $page ) {

		$this->configPageName = $page;

		return $this;
	}

	/**
	 * Setter for the page hook where this config object is being displayed
	 *
	 * @param string $page
	 *
	 * @return mixed
	 */
	public function setPageHook( $pageHook ) {

		$this->configPageHook = $pageHook;

		return $this;
	}

	/**
	 * Getter for the complete definitions of all settings
	 *
	 * @return mixed
	 */
	public function getDefinitions() {

		return static::$defs[ $this->currentConfig ];
	}

	/**
	 * Check if a config element is an actual option
	 * or a "meta" option, such as a tab, an help element, etc
	 * Meta option are probably not going to be saved to db for instance
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function isOption( $type ) {

		return ! is_null( $type ) && $type < 1024;
	}

	/**
	 * Read a config object from storage
	 */
	public function load() {

		$options = get_option( static::STORAGE_PREFIX . '_' . $this->currentConfig );
		if ( false !== $options && is_array( $options ) ) {
			static::$_configs[ $this->currentConfig ] = array_merge( static::$_configs[ $this->currentConfig ], $options );
		}

		return $this;
	}

	/**
	 * Read a config object from a remote location. Cache it for 12 hours
	 *
	 * @param string $url Full URL to the online configuration file
	 *
	 * @return $this
	 */
	public function maybeLoadRemote( $url, $ttl ) {

		if ( empty( $url ) ) {
			// can't do a thing without a target URL to load from
			return $this;
		}

		// read remote config from the cache, loading/updating it if needed
		$remoteConfig = WblFactory::getA( 'WblSystem_Cache' )
		                          ->get(
			                          'remote_config_amp',
			                          array(
				                          $this,
				                          'loadRemote'
			                          ),
			                          array(
				                          'load_remote_url' => $url
			                          ),
			                          $ttl
		                          );

		// if we got an actual config back, use it
		if ( static::REMOTE_CONFIG_NOT_AVAILABLE !== $remoteConfig ) {
			static::$defs[ $this->currentConfig ] = $remoteConfig;
			// build an easy to use configuration object from this definition
			$this->buildConfig();
		}

		return $this;
	}

	/**
	 * Actually perform remote URL loading, called by cache handler
	 *
	 * @param array $options Options passed by caller
	 */
	public function loadRemote( $options ) {

		$urls = wbArrayGet( $options, 'load_remote_url' );
		if ( empty( $urls ) ) {
			// can't do a thing without a target URL to load from
			return static::REMOTE_CONFIG_NOT_AVAILABLE;
		}

		// get the remote URL
		$rawRemoteConfig = wp_remote_get(
			$urls,
			array(
				'timeout' => 2
			)
		);

		// error?
		if ( is_wp_error( $rawRemoteConfig ) || 200 !== wp_remote_retrieve_response_code( $rawRemoteConfig ) ) {
			return static::REMOTE_CONFIG_NOT_AVAILABLE;
		}

		$rawRemoteConfigContent = wp_remote_retrieve_body( $rawRemoteConfig );
		// sanitize the data read
		if ( empty( $rawRemoteConfigContent ) ) {
			return static::REMOTE_CONFIG_NOT_AVAILABLE;
		}

		// unserialize and store in config
		$config = json_decode( $rawRemoteConfigContent, $asArray = true );
		if ( $this->validateRemoteConfig( $config ) ) {
			return $config;
		}

		return static::REMOTE_CONFIG_NOT_AVAILABLE;
	}

	/**
	 * Store a config object to storage
	 */
	public function store() {

		update_option( static::STORAGE_PREFIX . '_' . $this->currentConfig, static::$_configs[ $this->currentConfig ] );

		return $this;
	}

	/**
	 * Public getter for a config option. Normally, no default value should be supplied when callin,
	 * as the default value is read from the settings definition.
	 * This default value can be overriden by passing a default value as the second parameter, though this
	 * is not recommended (better use the defined default value)
	 *
	 * @param string $key the desired config option identifier
	 * @param mixed  $default Optional default value. If missing, default value is read from config definition
	 *                        (preferred)
	 */
	public function get( $key ) {

		if ( empty( $key ) ) {
			wbThrow( new InvalidArgumentException( 'wbLib : trying to read a config option with empty key' ) );
		}

		if ( isset( static::$_configs[ $this->currentConfig ][ $key ] ) ) {

			// if this setting is not allowed on current edition, use the default value
			if ( $this->isValidOnThisEdition( $key ) ) {
				$value = static::$_configs[ $this->currentConfig ][ $key ];
			} else {
				$value = $this->getSettingDefaultValue( static::$defsByKey[ $this->currentConfig ][ $key ] );
			}

			$value = $this->getMultipleOptionsSettingValuePerEdition( $key, $value );

			// allow overriding
			$value = $this->filterValue( $value, $key );
		} else {

			// we don't know this config option, so we don't even have a default value for it
			// did caller provided one?
			if ( func_num_args() > 1 ) {
				// allow overriding
				$value = $this->filterValue( func_get_arg( 1 ), $key );
			} else {
				WblSystem_Log::error( 'wbLib', '%s::%d: %s', __METHOD__, __LINE__, 'wbLib : trying to read an unknown configuration option ' . $key );
				$value = null;
			}
		}

		return $value;
	}

	/**
	 * For lists and multiple checkbox, individual options can be disabled per edition. Means we must
	 * check them one by one, possibly getting the possible values from callbacks, of the options
	 * are not hardcoded.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	protected function getMultipleOptionsSettingValuePerEdition( $key, $value ) {

		// if setting is checkbox group or list, individual options
		// might be disabled
		$type = wbArrayGet( static::$defsByKey, array( $this->currentConfig, $key, 'type' ) );
		switch ( $type ) {
			case WblSystem_Config::OPTION_LIST:
				$optionDefs = wbArrayGet( static::$defsByKey, array( $this->currentConfig, $key, 'content', 'options' ), array() );
				foreach ( $optionDefs as $optionKey => $optionDef ) {
					if ( is_array( $optionDef ) && $value == $optionKey ) {
						$editions = wbArrayGet( $optionDef, 'editions', null );
						if ( ! is_null( $editions ) && ! WeeblrampHelper_Version::isOneOfEditions( $editions ) ) {
							$value = $this->getSettingDefaultValue( static::$defsByKey[ $this->currentConfig ][ $key ] );
						}
					}
				}
				break;
			case WblSystem_Config::OPTION_CHECKBOX_GROUP:
				// we must get the value definition record
				$optionDefs = wbArrayGet( static::$defsByKey, array( $this->currentConfig, $key, 'content', 'options' ), null );

				// and possibly all of the possible values from the callback, if values are not hardcoded and a c/b is provided
				if ( empty( $optionDefs ) ) {
					$callback = wbArrayGet( static::$defsByKey, array( $this->currentConfig, $key, 'content', 'options_callback' ), null );
					if ( ! empty( $callback ) && is_callable( $callback ) ) {
						// if a callback function is defined, call it and store the options it generated
						$settingData = $this->getDefaultSettingLayoutData(
							wbArrayGet(
								static::$defsByKey,
								array(
									$this->currentConfig,
									$key
								)
							)
						);
						$optionDefs  = call_user_func_array( $callback, array( $settingData ) );

						// store in class variable, can/will be used later on by others
						static::$defsByKey[ $this->currentConfig ][ $key ]['content']['options'] = $optionDefs;
					}
				}

				// possible issue: if the type of an option changes, ie we change a setting
				// from a boolean to a checkbox list, then we must update the $value
				if(!is_array($value)) {
					// stored value was not an array but we now need
					// it as an array
					$storedValue = $value;
					$value = array();
					// try to move forward the stored value
					foreach ( $optionDefs as $optionKey => $optionDef ) {
						if($storedValue == $optionDef['name']) {
							$value[ $optionDef['name'] ] = 1;
						}
					}
				}

				// we now have the setting options definitions, we can iterate over them.
				// if one option is not available on this edition, we always return its default value
				// regardless of what's stored (the stored value might be from a time when another edition
				// was installed and used)
				// As a candy, the default, hardcoded value can be different based on the running edition!
				foreach ( $optionDefs as $optionKey => $optionDef ) {
					$editions = wbArrayGet( $optionDef, 'editions', null );
					if ( ! is_null( $editions ) && ! WeeblrampHelper_Version::isOneOfEditions( $editions ) ) {
						$defaultValue = wbArrayGet( $optionDef, 'default' );
						if ( wbArrayIsSet(
							$optionDef,
							array(
								'default_edition',
								WeeblrampHelper_Version::getEdition()
							)
						)
						) {
							$defaultValue = wbArrayGet(
								$optionDef,
								array(
									'default_edition',
									WeeblrampHelper_Version::getEdition()
								)
							);
						}
						$value[ $optionDef['name'] ] = $defaultValue;
					}
				}
				break;
		}

		return $value;
	}

	/**
	 * Finds if a provided configuration key is allowed on this edition of the plugin.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	private function isValidOnThisEdition( $key ) {

		if ( $this->bypassEditionCheck ) {
			return true;
		}

		$definition = static::$defsByKey[ $this->currentConfig ][ $key ];
		$editions   = wbArrayGet( $definition, 'editions', array() );

		return WeeblrampHelper_Version::isOneOfEditions( $editions );
	}

	/**
	 * Check if there exists a specific configuration key definition
	 *
	 * @param $key
	 */
	public function hasConfigKey( $key ) {

		return isset( static::$defsByKey[ $this->currentConfig ][ $key ] );
	}

	/**
	 * Check if a given config option is truthy
	 *
	 * @param string $key
	 * @param string $subKey
	 *
	 * @return bool
	 */
	public function isTruthy( $key, $subKey = '' ) {

		$value = $this->get( $key );
		if ( ! empty( $subKey ) && is_array( $value ) ) {
			$value = wbArrayGet( $value, $subKey, null );
		}

		// allow overriding
		$value = $this->filterValue( $value, $key, $subKey );

		return ! empty( $value );
	}

	/**
	 * Check if a given config option is falsy
	 * Can fetch a subkey in an array as well
	 *
	 * @param string $key
	 * @param string $subKey
	 *
	 * @return bool
	 */
	public function isFalsy( $key, $subKey = '' ) {

		$value = $this->get( $key );
		if ( ! empty( $subKey ) && is_array( $value ) ) {
			$value = wbArrayGet( $value, $subKey, null );
		}

		// allow overriding
		$value = $this->filterValue( $value, $key, $subKey );

		return empty( $value );
	}

	/**
	 * Set a value for a key
	 *
	 * @param $key
	 * @param $value
	 */
	public function set( $key, $value ) {

		if ( empty( $key ) ) {
			wbThrow( new InvalidArgumentException( 'wbLib : trying to set config option with empty key' ) );
		}

		static::$_configs[ $this->currentConfig ][ $key ] = $value;

		return $this;
	}

	/**
	 * Returns a parameter to its default value
	 *
	 * @param string $key
	 *
	 * @return $this
	 * @throws InvalidArgumentException
	 */
	public function reset( $key ) {

		// do the reset
		$this->set(
			$key,
			$this->getDefault( $key )
		);

		return $this;
	}

	/**
	 * Returns the default value of a parameter
	 *
	 * @param string $key
	 *
	 * @return $this
	 * @throws InvalidArgumentException
	 */
	public function getDefault( $key ) {

		// find setting definition
		$settingDef = wbArrayGet( static::$defsByKey[ $this->currentConfig ], $key, null );
		if ( empty( $settingDef ) ) {
			wbThrow( new InvalidArgumentException( 'wbLib : trying to find default for a non-existing configuration (key: ' . esc_html( $key ) ) );
		}

		return $this->getSettingDefaultValue( $settingDef );
	}

	/**
	 * Set multiple configuration options at the same time,
	 *
	 * @param array $configOptions an associative array of config options
	 */
	public function setMultiple( $configOptions ) {

		if ( ! is_array( $configOptions ) ) {
			wbThrow( new InvalidArgumentException( 'wbLib : config options passed is not an array' ) );
		}
		if ( empty( $configOptions ) ) {
			return;
		}

		static::$_configs[ $this->currentConfig ] = array_merge( static::$_configs[ $this->currentConfig ], $configOptions );

		return $this;
	}

	/**
	 * Register all settings in the config with WP
	 *
	 * @return $this
	 */
	public function registerSettings() {

		foreach ( static::$defs[ $this->currentConfig ] as $key => $def ) {
			$this->registerSetting( $def )
			     ->storeNonce( $def );

			// render this option (a tab, a section)
			// then render any option it wraps
			if ( ! empty( $def['content'] ) ) {
				foreach ( $def['content'] as $configOption ) {
					$this->registerSetting( $configOption )
					     ->storeNonce( $configOption );
				}
			}
		}

		// adds a hook to include nonces required by setting in head
		add_action( 'admin_head', array( $this, 'addAjaxNonce' ) );
		add_action( 'admin_head', array( $this, 'addPageInfos' ) );

		return $this;
	}

	/**
	 * Render our custom settings pages. Iterate over this config object
	 * list of fields and calls a renderer for each.
	 * Rendered output is aggregated in a single string.
	 *
	 * @param string $settingsPage Current page id
	 *
	 * @return string
	 */
	public function renderSettingsPage( $settingsPage ) {

		$output               = '';
		$this->currentSection = '';
		foreach ( static::$defs[ $this->currentConfig ] as $key => $def ) {

			// skip entirely if not enabled on this edition
			// This is different than edition-related behavior for other settings:
			// tabs are not rendered vs other settings are displayed, with special annotations
			if (
				static::OPTION_TAB == wbArrayGet( $def, 'type' )
				&&
				! wbArrayIsEmpty( $def, 'editions' )
				&&
				! WeeblrampHelper_Version::isOneOfEditions(
					wbArrayGet( $def, 'editions', array() )
				)
			) {
				continue;
			}

			// render this option (a tab, a section)
			$output .= $this->renderSetting( $def );

			// then render any option it wraps
			if ( ! empty( $def['content'] ) ) {
				foreach ( $def['content'] as $configOption ) {
					$output .= $this->renderSetting( $configOption );
				}
			}
		}

		// run config checks
		/**
		 * Hook into the user configuration after_render event.
		 *
		 * Allows performing checks on the configuration values for instance.
		 *
		 * @api
		 * @package weeblrAMP\action\config
		 * @var weeblramp_config_after_render
		 *
		 * @param string $settingsPage Name of settings page being rendered
		 *
		 * @since   1.0.0
		 *
		 */
		do_action( static::STORAGE_PREFIX . '_config_after_render', $settingsPage );

		return $output;
	}

	/**
	 * Render a setting, using sublayouts for each setting type
	 *
	 * @param array $settingDetails
	 *
	 * @return $this
	 */
	public function settingRenderCallback( $settingDetails ) {

		echo $output = WblMvcLayout_Helper::render( $settingDetails['sub_layout'], $settingDetails, WBLIB_LAYOUTS_PATH );

		return $this;
	}

	/**
	 * Default sanitize callback. Currently only make sure unchecked checkboxes
	 * are saved with a 0 value, allowing to have checked or unchecked default value
	 *
	 * @param array $userData the user supplied data being saved
	 *
	 * @return mixed
	 */
	public function sanitizeCallback( $userData ) {

		foreach ( static::$defs[ $this->currentConfig ] as $settingGroup => $settingGroupDef ) {
			if ( ! empty( $settingGroupDef['content'] ) ) {
				foreach ( $settingGroupDef['content'] as $settingKey => $settingDef ) {
					switch ( $settingDef['type'] ) {
						case static::OPTION_MEDIA:
							$imageUrl = $userData[ $settingDef['name'] ];
							$siteUrl  = WblWordpress_Helper::getSiteUrl();
							if ( wbStartsWith( $imageUrl, $siteUrl ) ) {
								// remove the local root URL
								$userData[ $settingDef['name'] ] = StringHelper::substr( $userData[ $settingDef['name'] ], StringHelper::strlen( $siteUrl ) );
							}
							break;
						case static::OPTION_CHECKBOX:
							if ( empty( $userData[ $settingDef['name'] ] ) ) {
								// check box not saved, set value to 0
								$userData[ $settingDef['name'] ] = '0';
							}
							break;

						case static::OPTION_CHECKBOX_GROUP:
							foreach ( $settingDef['content']['options'] as $option ) {
								if ( empty( $userData[ $settingDef['name'] ][ $option['name'] ] ) ) {
									// check box not saved, set value to 0
									$userData[ $settingDef['name'] ][ $option['name'] ] = '0';
								}
							}
							break;

						case static::OPTION_POST_TYPES:

							// checkboxes in the categories selector are not declared individually, so
							// we need custom code for them
							// load the setting definition for 'amp_post_types'
							$settingData = $this->getSettingLayoutData( $settingDef );
							$settingName = $settingData['details']['name'];

							// shorthand for post types listed
							$postTypes = $settingData['details']['content']['options']['post_types'];

							// and the list of taxonomies found for those post types
							$taxonomiesList    = $settingData['details']['content']['options']['taxonomies'];
							$allowedTaxonomies = $this->getUserSelectableTaxonomies( $settingDef, $postTypes );

							// iterate over all categories for each post type, if any
							foreach ( $postTypes as $postType ) {
								// fill-in for unchecked checkboxes, for each post type
								if ( empty( $userData[ $settingDef['name'] ][ $postType->name ]['enabled'] ) ) {
									// post type check box is not checked, set it so
									$userData[ $settingDef['name'] ][ $postType->name ]['enabled'] = '0';
								}

								if ( empty( $userData[ $settingDef['name'] ][ $postType->name ]['per_taxonomy'] ) ) {
									// post type check box is not checked, set it so
									$userData[ $settingDef['name'] ][ $postType->name ]['per_taxonomy'] = array();
								}

								if ( ! empty( $taxonomiesList[ $postType->name ] ) ) {
									foreach ( $taxonomiesList[ $postType->name ] as $taxoName => $terms ) {
										if ( ! empty( $allowedTaxonomies[ $postType->name ] ) && in_array( $taxoName, $allowedTaxonomies[ $postType->name ] ) ) {
											// there are some categories amongst the taxonomies for that post type
											foreach ( $terms as $term ) {
												// if the user has not checked the check box for one category
												if ( empty( $userData[ $settingName ][ $postType->name ] )
												     ||
												     empty( $userData[ $settingName ][ $postType->name ]['per_taxonomy'] )
												     ||
												     empty( $userData[ $settingName ][ $postType->name ]['per_taxonomy'][ $taxoName ][ $term->term_id ] )
												) {
													// explicitely set that checkbox to 0, so that it is saved to the options by the settings API
													$userData[ $settingName ][ $postType->name ]['per_taxonomy'][ $taxoName ][ $term->term_id ] = 0;
												}
											}
										}
									}
								}
							}
							break;

						case static::OPTION_MENUS:
							// checkboxes in the menus selector are not declared individually, so
							// we need custom code for them
							// load the setting definition for the menu
							$settingData = $this->getSettingLayoutData( $settingDef );

							// shorthand for menus listed
							$menuList = $settingData['details']['content']['options'];

							// iterate over all menus
							foreach ( $menuList as $menu ) {
								// fill-in for unchecked checkboxes, for each menu
								if ( empty( $userData[ $settingDef['name'] ][ $menu->term_id ]['enabled'] ) ) {
									// menu check box is not checked, set it so
									$userData[ $settingDef['name'] ][ $menu->term_id ]['enabled'] = '0';
								}
								// same for "Should AMPlify" option
								if ( empty( $userData[ $settingDef['name'] ][ $menu->term_id ]['should_amplify'] ) ) {
									// menu check box is not checked, set it so
									$userData[ $settingDef['name'] ][ $menu->term_id ]['should_amplify'] = '0';
								}
								// same for "Show menu name" option
								if ( empty( $userData[ $settingDef['name'] ][ $menu->term_id ]['show_name'] ) ) {
									// menu check box is not checked, set it so
									$userData[ $settingDef['name'] ][ $menu->term_id ]['show_name'] = '0';
								}
							}
							break;

						case static::OPTION_EDITOR:
							// we must collect editor data ourselves
							// as they can't be part of an array of POSTed data
							// which is what we use for all other settings
							if ( ! empty( $_POST[ $settingDef['name'] ] ) ) {
								$userData[ $settingDef['name'] ] = wp_kses_post(
								// drop slashes added to $_POST by WP
									stripslashes(
										$_POST[ $settingDef['name'] ]
									)
								);
							}
							break;
					}
				}
			}
		}

		return $userData;
	}

	/**
	 * A callback function used by settings to populate a list of available
	 * post types
	 *
	 * @param array $settingDef the setting definition, that requested a list of post types
	 *
	 * @return array|null
	 */
	public function optionsCallback_post_types( $settingDef ) {

		static $postTypes = array();

		$signature = md5( serialize( $settingDef ) );
		if ( ! isset( $postTypes[ $signature ] ) ) {
			$types = get_post_types(
				array(
					'public' => true,
				),
				'objects'
			);

			// whitelist
			if ( ! empty( $settingDef['details']['includes'] ) ) {
				foreach ( $types as $type => $typeData ) {
					if ( ! in_array( $type, $settingDef['details']['includes'] ) ) {
						unset( $types[ $type ] );
					}
				}
			}

			// blacklist
			if ( ! empty( $settingDef['details']['excludes'] ) ) {
				foreach ( $types as $type => $typeData ) {
					if ( in_array( $type, $settingDef['details']['excludes'] ) ) {
						unset( $types[ $type ] );
					}
				}
			}

			$postTypes[ $signature ] = $types;
		}

		return $postTypes[ $signature ];
	}

	/**
	 * Callback to be used with a OPTION_CHECKBOX_GROUP to select
	 * one or more installed plugins
	 *
	 * @param $settingDef
	 *
	 * @return array
	 */
	public function optionsCallback_plugins_selector( $settingDef ) {

		$plugins        = WblWordpress_Helper::getAllPlugins();
		$pluginsRecords = array();
		foreach ( $plugins as $id => $def ) {
			$pluginsRecords[] = array(
				'name'     => $id,
				'caption'  => $def['Title'],
				'default'  => 0,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL )
			);
		}

		return $pluginsRecords;
	}

	/**
	 * A callback function used by settings to populate a list of available
	 * menus for use in multiple choice option (checkbox group)
	 *
	 * @param array $settingDef the setting definition, that requested a list of menus
	 *
	 * @return array|null
	 */
	public function optionsCallback_menus( $settingDef ) {

		static $menus = array();

		$signature = md5( serialize( $settingDef ) );
		if ( ! isset( $menus[ $signature ] ) ) {
			$menusObjects        = apply_filters(
				static::STORAGE_PREFIX . '_get_nav_menus',
				wp_get_nav_menus()
			);
			$menus[ $signature ] = $menusObjects;
		}

		return $menus[ $signature ];
	}

	/**
	 * A callback function used by settings to populate a list of available
	 * menus for use in single choice option (select)
	 *
	 * @param array $settingDef the setting definition, that requested a list of menus
	 *
	 * @return array|null
	 */
	public function optionsCallback_menus_select( $settingDef ) {

		static $menus = array();

		$signature = md5( serialize( $settingDef ) );
		if ( ! isset( $menus[ $signature ] ) ) {
			$menusObjects        = apply_filters(
				static::STORAGE_PREFIX . '_get_nav_menus',
				wp_get_nav_menus()
			);
			$menus[ $signature ] = array();
			foreach ( $menusObjects as $menusObject ) {
				$menus[ $signature ][ $menusObject->term_id ] = $menusObject->name;
			}
		}

		return $menus[ $signature ];
	}

	/**
	 * Action hook to handle settings using ajax
	 */
	public function ajaxHandler() {

		check_ajax_referer( 'wblib-settings-nonces-' . $_REQUEST['config_item'] );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unable to perform action, not authorized.', 'weeblramp' ) );
		} else {
			try {
				switch ( $_REQUEST['config_item'] ) {
					case 'clear_transients':
						WblFactory::getA( 'WblSystem_Cache' )->deleteAll();
						$message = __( 'Cached data cleared!', 'weeblramp' );
						// send message and exit
						wp_send_json_success( __( $message ) );
						break;
					case 'flush_rewrite_rules':
						flush_rewrite_rules();
						$message = __( 'Rewrite rules flushed!', 'weeblramp' );
						// send message and exit
						wp_send_json_success( __( $message ) );
						break;
				}
			}
			catch ( Exception $e ) {
				wp_send_json_error( 'Error: ' . __( $e->getMessage() ) );
			}
		}
	}

	/**
	 * Low priority ajax handler, will return an error message if no other handler
	 * can handle the ajax request.
	 *
	 */
	public function fallbackAjaxHandler() {

		check_ajax_referer( 'wblib-settings-nonces-' . $_REQUEST['config_item'] );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unable to perform action, not authorized.', 'weeblramp' ) );
		} else {

			wp_send_json_error( 'Invalid command: ' . esc_html( $_REQUEST['config_item'] ) );
		}
	}

	/**
	 * Insert nonces javascript into the page
	 *
	 * NB: could not find a way to only insert that script when a setting actually
	 * requires it, because the admin_head hook is triggered before we start
	 *
	 * @return $this
	 */
	public function addAjaxNonce() {

		// include a nonce in the page, for use by the ajax javascript
		if ( ! empty( $this->configPageHook ) && get_current_screen()->id == $this->configPageHook ) {
			echo WblMvcLayout_Helper::render(
				'wblib.settings.nonces',
				array(
					'nonces' => $this->nonces
				),
				WBLIB_LAYOUTS_PATH
			);
		}

		return $this;
	}

	/**
	 * Add some information about current page, can useful for
	 * some ajax ops
	 *
	 * @return $this
	 */
	public function addPageInfos() {

		// include basic infor about the page, for use by the ajax javascript
		if ( ! empty( $this->configPageHook ) && get_current_screen()->id == $this->configPageHook ) {
			echo WblMvcLayout_Helper::render(
				'wblib.settings.page_infos', array(
				'page_infos' => array(
					'base_url'                => WblWordpress_Helper::getBaseUrl(),
					'full_base_url'           => WblWordpress_Helper::getBaseUrl( false ),
					'current_request_url'     => WblWordpress_Helper::getCurrentRequestUrl(),
					'current_request_url_abs' => WblWordpress_Helper::getCurrentRequestUrl( true ),
					'current_request_path'    => WblWordpress_Helper::getCurrentRequestUrlCustom( array( 'path' ) ),
					'blog_id'                 => get_current_blog_id(),
				)
			),
				WBLIB_LAYOUTS_PATH
			);
		}

		return $this;
	}

	/**
	 * Filters the list of taxonomies a user can choose from
	 * when selecting taxonomies
	 *
	 * @return array
	 */

	function filter_amp_post_types_user_selectable_taxonomies( $taxonomies ) {

		return $taxonomies;
	}

	/**
	 * Check if a remote configuration looks valid
	 *
	 * @param array $config
	 *
	 * @return bool
	 */
	protected function validateRemoteConfig( $config ) {

		if ( empty( $config ) || ! is_array( $config ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Trigger a filter to allow overriding of user options
	 *
	 * @param mixed  $value
	 * @param string $key
	 * @param string $subKey
	 *
	 * @return mixed
	 */
	protected function filterValue( $value, $key, $subKey = '' ) {

		/**
		 * Filter weeblrAMP configuration options. Allows overriding any setting set by user.
		 *
		 * @api
		 * @package weeblrAMP\filter\config
		 * @var weeblramp_option
		 * @since   1.0.0
		 *
		 * @param string $value The current value of the option
		 * @param string $key The option name
		 * @param string $subKey If option is an array, an optional index key in the array
		 *
		 * @return mixed Updated config value
		 */
		$value = apply_filters( static::STORAGE_PREFIX . '_option', $value, $key, $subKey );

		return $value;
	}

	/**
	 * @Renders an individual setting
	 *
	 * @param array $settingDef
	 *
	 * @return string
	 */
	protected function renderSetting( $settingDef ) {

		$output = '';

		// Setup all the callbacks
		switch ( $settingDef['type'] ) {
			case static::OPTION_TAB:
				// Move on with rendering
				if ( ! empty( $this->currentTab ) ) {
					// output closing html for a tab
					$output .= WblMvcLayout_Helper::render( 'wblib.settings.tab-close', $settingDef, WBLIB_LAYOUTS_PATH );
				}
				$this->currentTab     = $settingDef['name'];
				$this->currentSection = 'default';

				// include option help and link:
				$settingDef['details'] = array(
					'help'            => wbArrayGet( $settingDef, 'help' ),
					'desc'            => wbArrayGet( $settingDef, 'desc' ),
					'doc_link'        => wbArrayGet( $settingDef, 'doc_link' ),
					'doc_link_button' => wbArrayGet( $settingDef, 'doc_link_button' ),
					'doc_embed'       => wbArrayGet( $settingDef, 'doc_embed' ),
				);
				// render opening HTMl for a tab
				$output .= WblMvcLayout_Helper::render( 'wblib.settings.tab-start', $settingDef, WBLIB_LAYOUTS_PATH );
				break;
			case static::OPTION_SECTION:
				$this->currentSection = $settingDef['name'];
				$output               = WblMvcLayout_Helper::render( 'wblib.settings.section', $this->getSettingLayoutData( $settingDef ), WBLIB_LAYOUTS_PATH );
				break;
			case static::OPTION_HIDDEN:
				// Hidden fields do have a value, we need to read it
				if ( $this->isOption( $settingDef['type'] ) ) {
					$settingDef['current_value'] = $this->get( $settingDef['name'] );
				} else {
					$settingDef['current_value'] = null;
				}
				$output = WblMvcLayout_Helper::render( 'wblib.settings.setting_hidden', $this->getSettingLayoutData( $settingDef ), WBLIB_LAYOUTS_PATH );
				break;
			case static::OPTION_HELP:
				$output = WblMvcLayout_Helper::render( 'wblib.settings.help', $settingDef, WBLIB_LAYOUTS_PATH );
				break;
			case static::OPTION_RAW_HTML:
				$output = WblMvcLayout_Helper::render( 'wblib.settings.raw_html', $settingDef, WBLIB_LAYOUTS_PATH );
				break;
			case static::OPTION_SEPARATOR:
				$output = WblMvcLayout_Helper::render( 'wblib.settings.separator', $settingDef, WBLIB_LAYOUTS_PATH );
				break;
			case static::OPTION_TEXT:
			case static::OPTION_TEXTAREA:
			case static::OPTION_LIST:
			case static::OPTION_RADIO:
			case static::OPTION_CHECKBOX:
			case static::OPTION_POST_TYPES:
			case static::OPTION_MEDIA:
			case static::OPTION_COLOR_PICKER:
			case static::OPTION_MENUS:
			case static::OPTION_CHECKBOX_GROUP:
			case static::OPTION_CLEAR_TRANSIENTS:
			case static::OPTION_FLUSH_REWRITE_RULES:
			case static::OPTION_EDITOR:
			case static::OPTION_SETTING_SEPARATOR:

				// if setting is not part of a section, we must render it
				// if it's part of a section, the section renderer will
				// call do_settings_fields and render all fields it contains
				// using the registered callback settingRenderCallback()
				// which was added during add_settings_field()
				if ( 'default' == $this->currentSection ) {
					// build the data array for the display layout
					$output = WblMvcLayout_Helper::render(
						'wblib.settings.setting',
						$this->getSettingLayoutData( $settingDef ),
						WBLIB_LAYOUTS_PATH
					);
				}
				break;
		}

		return $output;
	}

	/**
	 * Build the HTML field name for a setting, prepending
	 * a storage prefix and the current configuration object scope
	 *
	 * @param array $optionName
	 *
	 * @return string
	 */
	protected function getHtmlOptionName( $optionName ) {

		return static::STORAGE_PREFIX . '_' . $this->currentConfig . '[' . $optionName . ']';
	}

	/**
	 * @param Array $settingDef
	 *
	 * @return array
	 */
	protected function getDefaultSettingLayoutData( $settingDef ) {

		// base values
		$__data = array(
			'title'      => wbArrayGet( $settingDef, 'title' ),
			'page'       => $this->configPageName,
			'label_for'  => $this->getHtmlOptionName( $settingDef['name'] ),
			'type'       => wbArrayGet( $settingDef, 'type', '' ),
			'class'      => wbArrayGet( $settingDef, 'class', '' ),
			'name'       => $this->getHtmlOptionName( $settingDef['name'] ),
			'default'    => $this->getSettingDefaultValue( $settingDef ),
			'details'    => $settingDef,
			'sub_layout' => $this->getLayoutFromSettingType( $settingDef['type'] ),
		);

		return $__data;
	}

	/**
	 * Builds an array of data suitable for later rendering
	 * by one of our layouts
	 *
	 * @param $optionName
	 * @param $settingDef
	 *
	 * @return array
	 */
	protected function getSettingLayoutData( $settingDef ) {

		// build default value array
		$__data = $this->getDefaultSettingLayoutData( $settingDef );

		// visual separators and help fields don't have a real value
		if ( $this->isOption( $settingDef['type'] ) ) {
			$__data['current_value'] = $this->get( $settingDef['name'] );
		} else {
			$__data['current_value'] = null;
		}

		$__data = WeeblrampHelper_Version::updateSettingForEdition( $__data );

		// some overrides per type
		switch ( $settingDef['type'] ) {
			case static::OPTION_SECTION:
				$__data['name'] = $settingDef['name'];
				break;
			case static::OPTION_CHECKBOX_GROUP:
			case static::OPTION_LIST:
			case static::OPTION_MENUS:
				// get hardcoded options for the list or menu list
				$options = empty( $__data['details']['content']['options'] ) ? array() : $__data['details']['content']['options'];

				// if a callback function is defined, call it and merge the options it created with the hardcoded ones
				if ( ! empty( $__data['details']['content']['options_callback'] ) && is_callable( $__data['details']['content']['options_callback'] ) ) {
					$options = array_merge( $options, call_user_func_array( $__data['details']['content']['options_callback'], array( $__data ) ) );
				}
				// store final result
				$__data['details']['content']['options'] = $options;
				break;

			case static::OPTION_POST_TYPES:

				// collect custom data per post type, using specified callback
				$postTypes = empty( $__data['details']['content']['options'] ) ? array() : $__data['details']['content']['options'];
				if ( ! empty( $__data['details']['content']['options_callback'] ) && is_callable( $__data['details']['content']['options_callback'] ) ) {
					$postTypes = array_merge(
						$postTypes,
						call_user_func_array(
							$__data['details']['content']['options_callback'],
							array( $__data )
						)
					);
				}
				$__data['details']['content']['options']['post_types'] = $postTypes;

				// add taxonomy information
				$__data['details']['content']['options']['taxonomies'] = array();

				// @TODO: refactor and move out of wblib. Requires making the taxonomy manager part of wblib
				$taxonomyManager = WeeblrampFactory::getThe( 'WeeblrampClass_Taxonomy' );

				// build the taxonomies list
				$__data['user_selectable_taxonomies'] = array();
				foreach ( $postTypes as $postType ) {
					$taxonomiesDetails = $taxonomyManager->getObjectTaxonomiesDetails( $postType->name );

					// store final result
					$__data['details']['content']['options']['taxonomies'][ $postType->name ] = $taxonomiesDetails;
				}

				// finally store the list of taxonomies user can select from
				$__data['user_selectable_taxonomies'] = $this->getUserSelectableTaxonomies(
					$settingDef,
					$postTypes
				);

				break;
		}

		// process show-if attributes
		$showIf = wbArrayGet( $settingDef, 'show-if' );

		// If callable, this is a call-back to decide to show
		// the setting server side
		if ( is_callable( $showIf ) ) {
			$showIf = $showIf();
		}

		// process result
		if ( ! empty( $showIf ) ) {
			if ( 'never' == $showIf ) {
				$__data['show-if-attrs'] = array(
					'data-always_hide' => 'true'
				);
			} else {
				if ( is_array( $showIf['id'] ) ) {
					$idsList = array();
					foreach ( $showIf['id'] as $id ) {
						$idsList[] = WblSystem_Strings::asHtmlId(
							$this->getHtmlOptionName(
								$id
							)
						);
					}
				} else {
					$idsList = array(
						WblSystem_Strings::asHtmlId(
							$this->getHtmlOptionName(
								$showIf['id']
							)
						)
					);
				}

				$__data['show-if-attrs'] = array(
					'data-show_if_id'   => implode( ' ', $idsList ),
					'data-show_include' => implode( ' ', (array) wbArrayGet( $showIf, 'include' ) ),
					'data-show_exclude' => implode( ' ', (array) wbArrayGet( $showIf, 'exclude' ) )
				);
			}
			$__data['class'] .= ' js-wbamp-show-if js-data-' . WblSystem_Strings::asHtmlId( $__data['name'] );
		}

		return $__data;
	}

	/**
	 * Builds a list of taxonomies can select from, for a list of
	 * post types, and filtering the resulting list to allow
	 * plugins and integrations to add/remove them
	 *
	 * @param array $settingDef
	 * @param array $postTypes
	 *
	 * @return array
	 */
	protected function getUserSelectableTaxonomies( $settingDef, $postTypes ) {

		$taxonomyManager      = WeeblrampFactory::getThe( 'WeeblrampClass_Taxonomy' );
		$maxTaxonomiesDetails = WeeblrampFactory::getThe( 'weeblramp.config.system' )
		                                        ->get( 'taxonomies_max_user_selectable' );
		$taxonomies           = array();
		foreach ( $postTypes as $postType ) {
			$taxonomies[ $postType->name ] = array();
			$taxonomiesDetails             = $taxonomyManager->getObjectTaxonomiesDetails( $postType->name );

			foreach ( $taxonomiesDetails as $taxonomyName => $taxonomiesDetail ) {

				/**
				 * Allow/disallow taxonomy elements selection in control panel, per post type.
				 *
				 * @api
				 * @package weeblrAMP\filter\config
				 * @var weeblramp_allow_taxonomy_select'
				 * @since   1.9.0
				 *
				 * @param bool   $selectTaxonomy True to allow user selection of elements from this taxonomy.
				 * @param string $postType Name of the post type of this taxonomy.
				 * @param string $taxonomyName Taxonomy name.
				 *
				 * @return bool
				 */
				$shouldShowTaxonomy = apply_filters(
					static::STORAGE_PREFIX . '_allow_taxonomy_select',
					true,
					$postType->name,
					$taxonomyName

				);

				// max items per taxonomy: limited as can trigger PHP error due to
				// max_input_vars value.
				if ( count( $taxonomiesDetail ) > $maxTaxonomiesDetails ) {
					continue;
				}

				if ( $shouldShowTaxonomy ) {
					$taxonomies[ $postType->name ][] = $taxonomyName;
				}
			}
		}

		$taxonomies = apply_filters(
			static::STORAGE_PREFIX . '_' . $settingDef['name'] . '_user_selectable_taxonomies',
			$taxonomies
		);

		return $taxonomies;
	}

	/**
	 * Get a setting default value as per the configuration file for that setting,
	 * either calling the callback or using the raw value.
	 *
	 * @param array $settingDef
	 *
	 * @return mixed
	 */
	protected function getSettingDefaultValue( $settingDef ) {

		$settingDef['default'] = wbArrayGet( $settingDef, 'default', null );

		// not full edition, are there some edition-specific defaults?
		if ( wbArrayIsSet(
			$settingDef,
			array(
				'default_edition',
				WeeblrampHelper_Version::getEdition()
			)
		)
		) {
			$settingDef['default'] = wbArrayGet(
				$settingDef,
				array(
					'default_edition',
					WeeblrampHelper_Version::getEdition()
				)
			);
		}

		if ( isset( $settingDef['type'] ) ) {
			// type is set: this is a user configurable setting

			switch ( $settingDef['type'] ) {
				case WblSystem_Config::OPTION_CHECKBOX_GROUP:
					// check box groups are special, as there is a different default
					// value set for each sub option, each check box in the group.
					// These can be replaced by a callback.
					if ( is_callable( $settingDef['default'] ) ) {
						// still if a call back is set as default, we prefer it
						$default = call_user_func_array( $settingDef['default'], array( $settingDef ) );
					} else {
						// no callback, parse each checkbox def for its default value
						// But for each checkbox, there can be a different default value
						// per edition (full, community)
						$default = array();
						foreach ( $settingDef['content']['options'] as $option ) {
							$defaultValue = wbArrayGet( $option, 'default', null );
							if ( wbArrayIsSet(
								$option,
								array(
									'default_edition',
									WeeblrampHelper_Version::getEdition()
								)
							)
							) {
								$defaultValue = wbArrayGet(
									$option,
									array(
										'default_edition',
										WeeblrampHelper_Version::getEdition()
									)
								);
							}
							$default[ $option['name'] ] = $defaultValue;
						}
					}
					break;
				default:
					// configured default value can be a callback instead of an actual value
					// we only allow ***arrays** to called back that way, to try limit conflicts with Worpdress
					// existing names. For instance, the 'wp' string is considered callable by PHP, because of the global $wp object
					$default = is_array( $settingDef['default'] ) && is_callable( $settingDef['default'] ) ? call_user_func_array( $settingDef['default'], array( $settingDef ) ) : $settingDef['default'];
					break;
			}
		} else {

			// no type set: just a harcoded value in a config file
			// configured default value can be a callback instead of an actual value
			switch ( true ) {
				case is_callable( $settingDef['default'] ):
					$default = call_user_func_array( $settingDef['default'], array( $settingDef ) );
					break;
				default:
					$default = $settingDef['default'];
					break;
			}
		}

		return $default;
	}

	/**
	 * Finds out which layout to render
	 *
	 * @param int $settingType
	 *
	 * @return null|string
	 */
	protected function getLayoutFromSettingType( $settingType ) {

		switch ( $settingType ) {
			case static::OPTION_TEXT:
				$layout = 'text';
				break;
			case static::OPTION_TEXTAREA:
				$layout = 'textarea';
				break;
			case static::OPTION_LIST:
				$layout = 'list';
				break;
			case static::OPTION_RADIO:
				$layout = 'radio';
				break;
			case static::OPTION_CHECKBOX:
				$layout = 'checkbox';
				break;
			case static::OPTION_SEPARATOR:
				$layout = 'separator';
				break;
			case static::OPTION_SETTING_SEPARATOR:
				$layout = 'separator';
				break;
			case static::OPTION_POST_TYPES:
				$layout = 'post_types';
				break;
			case static::OPTION_MEDIA:
				$layout = 'media';
				break;
			case static::OPTION_COLOR_PICKER:
				$layout = 'color_picker';
				break;
			case static::OPTION_MENUS:
				$layout = 'menus';
				break;
			case static::OPTION_CHECKBOX_GROUP:
				$layout = 'checkbox_group';
				break;
			case static::OPTION_CLEAR_TRANSIENTS:
				$layout = 'clear_transients';
				break;
			case static::OPTION_FLUSH_REWRITE_RULES:
				$layout = 'flush_rewrite_rules';
				break;
			case static::OPTION_EDITOR:
				$layout = 'editor';
				break;
			case static::OPTION_HIDDEN:
				$layout = 'hidden';
				break;
			case static::OPTION_RAW_HTML:
				$layout = 'raw_html';
				break;
			default:
				$layout = null;
		}

		return empty( $layout ) ? '' : 'wblib.settings.setting_' . $layout;
	}

	/**
	 * Recursively store config options into the underlying
	 * configuration object.
	 *
	 * @param null $definitions
	 *
	 * @return $this
	 */
	protected function buildConfig( $definitions = null ) {

		$definitions = is_null( $definitions ) ? static::$defs[ $this->currentConfig ] : $definitions;
		foreach ( $definitions as $key => $def ) {
			if ( ! empty( $def['content'] ) ) {
				foreach ( $def['content'] as $configOption ) {
					if ( ! isset( $configOption['type'] ) || $this->isOption( $configOption['type'] ) ) {
						$default = $this->getSettingDefaultValue( $configOption );
						$this->set( $configOption['name'], $default );

						// store definition indexed by key, easier to access sometimes
						static::$defsByKey[ $this->currentConfig ][ $configOption['name'] ] = $configOption;
					} elseif ( ! empty( $configOption['content'] ) ) {
						$this->buildConfig( $configOption );
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Register an individual setting, called by registerSettings()
	 *
	 * @param array $settingDef
	 *
	 * @return $this
	 */
	protected function registerSetting( $settingDef ) {

		switch ( $settingDef['type'] ) {
			case static::OPTION_TAB:
				// reset the current section at each new tab
				$this->currentSection = 'default';
				break;
			case static::OPTION_SECTION:
				// store current section
				// used for each setting in the section
				// when calling add_settings_fields()
				$this->currentSection = $settingDef['name'];
				add_settings_section(
					$settingDef['name'],
					$settingDef['title'],
					null, // we do not use WP to render sections
					$this->configPageName
				);
				break;
			case static::OPTION_HIDDEN:
			case static::OPTION_HELP:
			case static::OPTION_SEPARATOR:
			case static::OPTION_RAW_HTML:
				break;
			case static::OPTION_SETTING_SEPARATOR:
				$optionName = $this->getHtmlOptionName( $settingDef['name'] );
				add_settings_field(
					$optionName,
					wbArrayGet( $settingDef, 'title', '' ),
					array( $this, 'settingRenderCallback' ),
					$this->configPageName,
					$this->currentSection,
					$this->getSettingLayoutData( $settingDef )
				);
				break;
			case static::OPTION_TEXT:
			case static::OPTION_TEXTAREA:
			case static::OPTION_LIST:
			case static::OPTION_RADIO:
			case static::OPTION_CHECKBOX:
			case static::OPTION_POST_TYPES:
			case static::OPTION_MEDIA:
			case static::OPTION_COLOR_PICKER:
			case static::OPTION_MENUS:
			case static::OPTION_CHECKBOX_GROUP:
			case static::OPTION_CLEAR_TRANSIENTS:
			case static::OPTION_FLUSH_REWRITE_RULES:
			case static::OPTION_EDITOR:
				$optionName = $this->getHtmlOptionName( $settingDef['name'] );
				register_setting(
					$this->configPageName,
					static::STORAGE_PREFIX . '_' . $this->currentConfig,
					array( $this, 'sanitizeCallback' )
				);

				add_settings_field(
					$optionName,
					wbArrayGet( $settingDef, 'title', $optionName ),
					array( $this, 'settingRenderCallback' ),
					$this->configPageName,
					$this->currentSection,
					$this->getSettingLayoutData( $settingDef )
				);
				break;
		}

		return $this;
	}

	/**
	 * Store a nonce in an array, as there might be multiple nonce,
	 * one per setting/action that requires an ajax call
	 *
	 * @param $def
	 *
	 * @return $this
	 */
	private function storeNonce( $def ) {

		if ( ! empty( $def['use_ajax'] ) ) {
			$this->nonces = array_merge(
				$this->nonces,
				array(
					$def['name'] => wp_create_nonce( 'wblib-settings-nonces-' . $def['name'] )
				)
			);
		}

		return $this;
	}
}
