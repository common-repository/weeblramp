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
 * Manage and provide access to user-defined configuration
 *
 * Actual settings definition is located in user.cfg.php, in same directory
 *
 * Class WeeblrampConfig_User
 */
class WeeblrampConfig_User extends WblSystem_Config {

	/**
	 * Storage prefix for using WP settings API
	 */
	const STORAGE_PREFIX = 'weeblramp';

	/*
	 * Unique ID for this configuration object
	 */
	const CONFIG_ID = '__weeblramp_user__';

	/**
	 * Plugin options constants
	 *
	 */
	const OP_MODE_NORMAL = 0;
	const OP_MODE_DEV = 1;
	const OP_MODE_STANDALONE = 2;

	const DOC_TYPE_ARTICLE = 'article';
	const DOC_TYPE_BLOG_POSTING = 'blog';
	const DOC_TYPE_NEWS_ARTICLE = 'news';
	const DOC_TYPE_PHOTOGRAPH = 'photograph';
	const DOC_TYPE_RECIPE = 'recipe';
	const DOC_TYPE_REVIEW = 'review';
	const DOC_TYPE_WEBPAGE = 'webpage';

	const ADS_NO_ADS = 'none';
	const ADS_BEFORE_CONTENT = 'before';
	const ADS_AFTER_CONTENT = 'after';
	const ADS_AFTER_INFO_BLOCK = 'after_info_block';

	const WP_CONTENT_NORMAL = 'normal';
	const WP_CONTENT_SHORTCODES = 'shortcodes';
	const WP_CONTENT_NONE = 'none';

	const ANALYTICS_NONE = 'none';
	const ANALYTICS_STANDARD = 'standard';
	const ANALYTICS_GTM = 'gtm';
	const ANALYTICS_FACEBOOK_PIXEL = 'fb_pixel';

	const COMMENTS_NATIVE = 'wp';
	const COMMENTS_DISQUS = 'disqus';

	// disqus handling
	const OPTION_DISQUS_SHORTNAME = 500;
	const DISQUS_CONNECT_CONNECTED = 'connected';
	const DISQUS_CONNECT_NOT_CONNECTED = 'not_connected';
	protected $adsNetworks         = array(
		self::ADS_NO_ADS => 'No ads',
		'adsense'        => 'AdSense',
		'criteo'         => 'Criteo',
		'doubleclick'    => 'Doubleclick',
		'custom'         => 'Custom'
	);
	protected $defaultDebugToken   = '';
	private   $pageSelectionConfig = array(
		'amp_post_types',
		'amplify_categories',
		'amplify_archives',
		'amplify_tags',
		'amplify_authors'
	);
	/**
	 * Temp storage when sanitizing user input
	 * @var null
	 */
	private $beingSanitizedData = null;

	/**
	 * @see WblSystem_Config::__construct
	 */
	public function __construct( $options = array() ) {

		// override definitions file
		$options['definition_file'] = str_replace( '.php', '.cfg.php', __FILE__ );

		// we want to load values set by user from db
		$options['load_values'] = true;

		// register config filters
		add_filter(
			static::STORAGE_PREFIX . '_allow_taxonomy_select',
			array(
				$this,
				'filter_weeblramp_allow_taxonomy_select'
			),
			5,
			3
		);

		parent::__construct( $options );
	}

	/**
	 * Required changes in config structure.
	 */
	protected function applyUpdates() {

		// @1.10.0: analytics_type changed from a string to an array of strings
		$analyticsType = $this->get( 'analytics_type' );
		if ( ! is_array( $analyticsType ) ) {
			$this->set(
				'analytics_type',
				array(
					$analyticsType => 1
				)
			);
		}
	}

	/**
	 * Just before rendering the settings page, special actions
	 *
	 * @see parent::renderSettingsPage
	 */
	public function renderSettingsPage( $settingsPage ) {

		// let parent perform main task
		$output = parent::renderSettingsPage( $settingsPage );

		// save current amp suffix value to DB
		// used to detect a change after saving, and
		// trigger a flush_rewrite_rules if so
		update_option( 'weeblramp_last_amp_suffix', $this->get( 'amp_suffix' ) );
		update_option( 'weeblramp_last_op_mode', $this->get( 'op_mode' ) );
		// same for rewrite rules
		update_option(
			'weeblramp_last_post_types_hash',
			$this->getConfiguredPageSelectionHash()
		);

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

		switch ( $settingDetails['type'] ) {
			case self::OPTION_DISQUS_SHORTNAME:
				echo WblMvcLayout_Helper::render( $settingDetails['sub_layout'], $settingDetails, WEEBLRAMP_LAYOUTS_PATH );
				break;
			default:
				parent::settingRenderCallback( $settingDetails );
				break;
		}

		return $this;
	}

	/**
	 * Sanitize, or otherwise handle, configuration data in the process
	 * of being saved. Currently handling rewrite rules flushing
	 *
	 * @param array $userData the user supplied data being saved
	 *
	 * @return mixed
	 */
	public function sanitizeCallback( $userData ) {

		// get default processing from parent
		$this->beingSanitizedData = parent::sanitizeCallback( $userData );

		$this->sanitizeAmpSuffix()
		     ->sanitizeOpMode()
		     ->sanitizeAmpPostsTypes()
		     ->sanitizeLoggingConfig()
		     ->resizeUploadedImage( 'publisher_image' );

		return $this->beingSanitizedData;
	}

	/**
	 * Override to parent, handles Disqus comments Weeblrpress connection
	 */
	public function ajaxHandler() {

		check_ajax_referer( 'wblib-settings-nonces-' . $_REQUEST['config_item'] );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unable to perform action, not authorized.', 'weeblramp' ) );
		} else {
			try {
				switch ( $_REQUEST['config_item'] ) {
					case 'comment_disqus_shortname':
						$handler  = WeeblrampFactory::getA( 'WeeblrampModelFeature_Disqus' );
						$response = $handler->dispatch( $_REQUEST );
						if ( is_wp_error( $response ) ) {
							wp_send_json_error(
								$response
							);
						} else {
							wp_send_json_success(
								$response
							);
						}
						break;
				}
			}
			catch ( Exception $e ) {
				wp_send_json_error( 'Error: ' . __( $e->getMessage() ) );
			}
		}

		parent::ajaxHandler();
	}

	/**
	 * Filter plugins selector for disabling plugins, to remove
	 * those which we have an integration for.
	 * Those will be disabled when the integration itself is disabled.
	 * We also remove plugins that should always be removed (incompatible, other AMP plugins)
	 * and finally our own plugins
	 *
	 * @param $settingDef
	 *
	 * @return array
	 */
	public function optionsCallback_plugins_selector_filtered( $settingDef ) {

		$pluginsRecords = parent::optionsCallback_plugins_selector( $settingDef );

		// filter out our own plugins
		$weeblrpressPlugins = WeeblrampFactory::getThe( 'weeblramp.config.system' )
		                                      ->get( 'plugins.do_not_disable_on_amp' );

		// filter out plugins with an integration
		$integrationsList = $this->get( 'integrations_list' );

		// drop plugins for which we have an integration option
		// from the list of plugins to select from
		foreach ( $pluginsRecords as $key => $pluginRecord ) {
			if (
				array_key_exists( $pluginRecord['name'], $integrationsList )
				||
				// filter out some known plugins automatically
				array_key_exists( $pluginRecord['name'], WeeblrampHelper_Compat::$incompatiblePlugins )
				||
				in_array( $pluginRecord['name'], $weeblrpressPlugins )
				||
				in_array( $pluginRecord['name'], WeeblrampHelper_Compat::$pluginsToAlwaysRemove )
			) {
				unset( $pluginsRecords[ $key ] );
			}
		}

		return $pluginsRecords;
	}

	/**
	 * Collect installed color themes
	 *
	 * @param $settingDef
	 *
	 * @return array
	 */
	public function optionsCallback_global_theme( $settingDef ) {

		$extensionThemes = apply_filters( 'weeblramp_full_themes_lists', array() );

		return $extensionThemes;
	}

	/**
	 * Manually set some default values in special cases, where
	 * they cannot be hardcoded
	 *
	 * @return $this
	 */
	public function setDefaults() {

		// collect defaults data from other plugins

		// initialize integrations with other plugins
		/**
		 * Modify defaults values for a configuration object
		 *
		 * @api
		 * @package weeblrAMP\filter\config
		 * @var weeblramp_config_set_defaults
		 * @since   1.0.0
		 *
		 * @param WblSystem_Config $config The configuration object
		 * @param String           $name The name of the current config object (unique ID)
		 *
		 * @return string
		 */
		self::$_configs[ $this->currentConfig ] = apply_filters(
			'weeblramp_config_set_defaults',
			self::$_configs[ $this->currentConfig ],
			$this->currentConfig
		);

		return $this;
	}

	/**
	 * Override to only allow some of the built-in taxonomies to the list of user selectable
	 * taxonomies.
	 *
	 * @param $selectTaxonomy
	 * @param $postType
	 * @param $taxonomyName
	 *
	 * @return bool
	 */
	public function filter_weeblramp_allow_taxonomy_select( $selectTaxonomy, $postType, $taxonomyName ) {

		$taxonomies = WeeblrampFactory::getThe( 'weeblramp.config.system' )
		                              ->get( 'taxonomies.built_in_selectable' );
		$postTypes  = array_keys( $taxonomies );
		if ( in_array( $postType, $postTypes ) ) {
			if ( ! in_array( $taxonomyName, $taxonomies[ $postType ] ) ) {
				$selectTaxonomy = false;
			}
		}

		return $selectTaxonomy;
	}

	/**
	 * Reads user set disqus comment shortname that may have been
	 * set by the standard Disqus commenting plugin
	 *
	 * @return string
	 */
	public function getDisqusShortnameFromPlugin() {

		return strtolower( get_option( 'disqus_forum_url', '' ) );
	}

	/**
	 * Finds out if one or more analytics vendors have been enabled by user.
	 * If no specific type is requested, returns true if any type is enabled.
	 * If multiple types are passed and $matchAll is true, they must all be enabled.
	 * If $matchAll is false, it's enough that one or more is enabled.
	 *
	 * @param string | array $analyticsType
	 * @param bool           $matchAll
	 *
	 * @return bool
	 */
	public function isAnalyticsEnabled( $analyticsType = array(), $matchAll = true ) {

		$types = is_array( $analyticsType ) ? $analyticsType : array( $analyticsType );

		// remove all disabled types
		$enabled = array_filter(
			$this->get( 'analytics_type' )
		);
		// only keep an array of enabled types names
		$enabled = array_keys( $enabled );

		// if empty it'll always be a no
		if ( empty( $enabled ) ) {
			return false;
		}

		// if there are some enabled and no specific types requested,
		// it'll always be a yes
		if ( empty( $types ) ) {
			return true;
		}

		// if some types enabled and some types specifically looked for,
		// we have a match if arrays intersect
		$intersect = array_intersect(
			$enabled,
			$types
		);

		$enabled = $matchAll ? count( $intersect ) == count( $types ) : ! empty( $intersect );

		return $enabled;
	}

	/**
	 * Override to handle weeblrAMP custom settings rendering
	 *
	 * @param array $settingDef
	 *
	 * @return string
	 */
	protected function renderSetting( $settingDef ) {

		// Setup all the callbacks
		switch ( $settingDef['type'] ) {
			case self::OPTION_DISQUS_SHORTNAME:
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
				} else {
					$output = '';
				}
				break;
			default:
				$output = parent::renderSetting( $settingDef );
				break;
		}

		return $output;
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
			case self::OPTION_DISQUS_SHORTNAME:
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
			default:
				parent::registerSetting( $settingDef );
				break;
		}

		return $this;
	}

	protected function getSettingLayoutData( $settingDef ) {

		switch ( $settingDef['type'] ) {
			case self::OPTION_DISQUS_SHORTNAME:
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
					'sub_layout' => 'weeblramp.admin.settings.disqus_shortname',
				);

				// visual separators and help fields don't have a real value
				if ( $this->isOption( $settingDef['type'] ) ) {
					$__data['current_value'] = $this->get( $settingDef['name'] );
				} else {
					$__data['current_value'] = null;
				}

				// process show-if attributes
				$showIf = wbArrayGet( $settingDef, 'show-if' );
				if ( ! empty( $showIf ) ) {
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
					$__data['class']         .= ' js-wbamp-show-if js-data-' . WblSystem_Strings::asHtmlId( $__data['name'] );
				}

				break;
			default:
				$__data = parent::getSettingLayoutData( $settingDef );
				break;
		}

		switch ( $settingDef['name'] ) {
			case 'comment_disqus_endpoint':
				if ( WeeblrampConfig_User::DISQUS_CONNECT_CONNECTED == $this->get( 'disqus_connect_state' ) ) {
					$__data['details']['content']['attr']['disabled'] = '';
				} else {
					unset( $__data['details']['content']['attr']['disabled'] );
				}
				break;
		}

		return $__data;
	}

	/**
	 * Flush rewrite rules as needed when amp pages suffix has been changed.
	 * We store the previous amp suffix in a separate option, to be able to detect changes
	 *
	 * @return $this
	 */
	private function sanitizeAmpSuffix() {

		$previousAmpSuffix = get_option(
			'weeblramp_last_amp_suffix',
			$this->get( 'amp_suffix' )
		);

		// amp suffix cannot be empty
		if ( empty( $this->beingSanitizedData['amp_suffix'] ) ) {
			$this->beingSanitizedData['amp_suffix'] = $previousAmpSuffix;

			// enqueue a message, to be displayed after the redirect
			add_settings_error( 'weeblramp-settings', 'weeblramp_invalid_amp_suffix', __( 'AMP suffix cannot be empty. It was restored to its previous value.', 'weeblramp' ) );
		} else {
			// flush rewrite rules when we changed the amp suffix
			if ( false !== $previousAmpSuffix && $this->beingSanitizedData['amp_suffix'] != $previousAmpSuffix ) {
				// kill the previous suffix
				add_rewrite_endpoint( $previousAmpSuffix, EP_NONE );

				// and register the new AMP endpoint (if not in standalone mode)
				if ( self::OP_MODE_STANDALONE != $this->beingSanitizedData['op_mode'] ) {
					add_rewrite_endpoint( $this->beingSanitizedData['amp_suffix'], EP_ALL );
				}

				// update rewrite rules
				flush_rewrite_rules();
			}
		}

		return $this;
	}

	/**
	 * Flush rewrite rules when operation mode has changed. In standalone mode,
	 * we do not have any amp pages suffix, so rewrite rule has to go.
	 *
	 * @return $this
	 */
	private function sanitizeOpMode() {

		// flush rewrite rules when we changed the operation mode
		$previousOpMode = get_option( 'weeblramp_last_op_mode' );
		if ( false !== $previousOpMode && $this->beingSanitizedData['op_mode'] != $previousOpMode ) {
			// update AMP rewrite endpoint
			if ( self::OP_MODE_STANDALONE == $this->beingSanitizedData['op_mode'] ) {
				// should disable the endpoint. Otherwise, we need to save
				// configuration twice so that the endpoint is removed
				add_rewrite_endpoint( $this->beingSanitizedData['amp_suffix'], EP_NONE );
			} else {
				add_rewrite_endpoint( $this->beingSanitizedData['amp_suffix'], EP_ALL );
			}

			// update rewrite rules
			flush_rewrite_rules();
		}

		return $this;
	}

	/**
	 * When the amp Post Types setting is modified, we mark rewrite rules
	 * as requiring a flush, so that they are updated after page redirect
	 */
	private function sanitizeAmpPostsTypes() {

		$lastHash = get_option( 'weeblramp_last_post_types_hash' );
		if ( false !== $lastHash ) {
			$newHash = $this->getSavingPageSelectionHash( $this->beingSanitizedData );

			if ( $newHash != $lastHash ) {
				update_option( 'weeblramp_rewrite_rules_flush_required', 1 );
			}
		}

		return $this;
	}

	/**
	 * Configure the wbLib logger according to user choice
	 * Config is persisted by the logger itself, so that it does not have to
	 * read this config object
	 */
	private function sanitizeLoggingConfig() {

		$loggingLevel = wbArrayGet(
			$this->beingSanitizedData,
			'logging_level'
		);

		WblSystem_Log::configure(
			$loggingLevel,
			$persist = true
		);

		return $this;
	}

	/**
	 * Attempt to resize the selected publisher logo if it does not match AMP rules.
	 * Only works for local URLs ATM.
	 *
	 * @param string $settingName The name of the image upload setting.
	 *
	 * @return $this
	 */
	private function resizeUploadedImage( $settingName ) {


		$logoUrl = wbArrayGet(
			$this->beingSanitizedData,
			$settingName
		);

		if ( empty( $logoUrl ) ) {
			return $this;
		}

		// check image size
		$logoSize = WblHtmlContent_Image::getImageSize( $logoUrl );
		if ( WeeblrampClass_Configcheck::checkPublisherLogoSize(
			$logoSize['width'],
			$logoSize['height'] )
		) {
			return $this;
		}

		// save filename for later
		$imageFileName = basename( $logoUrl );

		// is it local?
		$localPath = WblHtmlContent_Image::getImageLocalPath( $logoUrl );
		if ( false == $localPath ) {
			$additionalInfo = __( 'We cannot resize it as it is not a local image.', 'weeblramp' );
			$messageType    = 'error';
		} else {
			$image        = wp_get_image_editor( $localPath );
			$targetHeight = null;
			$targetWidth  = null;
			$upscaling    = false;
			if ( $image && ! is_wp_error( $image ) ) {
				$minWidth = (int) wbArrayGet( self::$defsByKey, array( $this->currentConfig, $settingName, 'min_width' ), 0 );
				if ( ! empty( $minWidth ) && $logoSize['width'] < $minWidth ) {
					$targetWidth = $minWidth;
					$upscaling   = true;
				} else {
					$maxWidth = (int) wbArrayGet( self::$defsByKey, array( $this->currentConfig, $settingName, 'max_width' ), 0 );
					if ( ! empty( $maxWidth ) && $logoSize['width'] > $maxWidth ) {
						$targetWidth = $maxWidth;
					}
				}

				$minHeight = (int) wbArrayGet( self::$defsByKey, array( $this->currentConfig, $settingName, 'min_height' ), 0 );
				if ( ! empty( $minHeight ) && $logoSize['height'] < $minHeight ) {
					$targetHeight = $minHeight;
					$upscaling    = true;
				} else {
					$maxHeight = (int) wbArrayGet( self::$defsByKey, array( $this->currentConfig, $settingName, 'max_height' ), 0 );
					if ( ! empty( $maxHeight ) && $logoSize['height'] > $maxHeight ) {
						$targetHeight = $maxHeight;
					}
				}

				if ( empty( $targetHeight ) && empty( $targetWidth ) ) {
					return $this;
				}

				if ( $upscaling ) {
					// result
					$additionalInfo = __( 'It is too small to be resized. Please select another image.', 'weeblramp' );
					$messageType    = 'error';
				} else {
					$image->resize(
						$targetWidth,
						$targetHeight
					);

					$pathInfo = pathinfo( $localPath );
					if ( strpos( $pathInfo['filename'], '_' . $settingName . '_resized_for_amp' ) === false ) {
						$resizedFileName = $pathInfo['filename'] . '_' . $settingName . '_resized_for_amp.' . $pathInfo['extension'];
					} else {
						// keep same name if previously resized
						$resizedFileName = $pathInfo['filename'] . '.' . $pathInfo['extension'];
					}
					$resizedPath = $pathInfo['dirname'] . '/' . $resizedFileName;
					$resized     = $image->save( $resizedPath );
					$resized     = ! empty( $resized ) && ! is_wp_error( $resized );

					// result
					$additionalInfo = $resized ? __( 'We have resized it to the correct dimensions.', 'weeblramp' ) : __( 'We tried to resize it, but could not. Please select another image.', 'weeblramp' );
					$messageType    = $resized ? 'updated' : 'error';

					// updated file name
					$this->beingSanitizedData[ $settingName ] = str_replace(
						$imageFileName,
						$resizedFileName,
						$logoUrl
					);
				}
			}
		}

		// enqueue a message, to be displayed after the redirect
		$title = wbArrayGet(
			self::$defsByKey,
			array(
				$this->currentConfig,
				$settingName,
				'title'
			),
			$settingName
		);

		add_settings_error(
			'weeblramp-settings',
			'weeblramp_publisher_logo_resized',
			sprintf(
				__( 'The image used for <em>%s</em> is not valid per AMP specification (see documentation for the correct values).', 'weeblramp' ),
				$title
			)
			. ' '
			. $additionalInfo,
			$messageType
		);

		return $this;
	}

	/**
	 * Builds a hash of the current page selection configuration
	 * so as to be able to detect whether user changed it
	 *
	 * @return string
	 */
	private function getConfiguredPageSelectionHash() {

		$dataSet = array();
		foreach ( $this->pageSelectionConfig as $settingName ) {
			$dataSet[ $settingName ] = $this->get( $settingName );
		}

		$hash = md5( serialize( $dataSet ) );

		return $hash;
	}

	/**
	 * Builds a hash of the incoming page selection configuration
	 * so as to be able to compare it with initial one, and detect
	 * any change by user
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private function getSavingPageSelectionHash(
		$data
	) {

		$dataSet = array();
		foreach ( $this->pageSelectionConfig as $settingName ) {
			$dataSet[ $settingName ] = wbArrayGet( $data, $settingName );
		}

		$hash = md5( serialize( $dataSet ) );

		return $hash;
	}
}
