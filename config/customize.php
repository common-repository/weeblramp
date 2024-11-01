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
class WeeblrampConfig_Customize extends WblSystem_Config {

	/**
	 * Storage prefix for using WP settings API
	 */
	const STORAGE_PREFIX = 'weeblramp_customize';

	/*
	 * Unique ID for this configuration object
	 */
	const CONFIG_ID = '__weeblramp_customize__';

	/**
	 * Plugin options constants
	 *
	 */
	const MENU_TYPE_NONE = 'none';
	const MENU_TYPE_SLIDE = 'sidebar';
	const MENU_TYPE_DROPDOWN = 'dropdown';
	const MENU_TYPE_SIDE_LEFT = 'left';
	const MENU_TYPE_SIDE_RIGHT = 'right';

	const LINK_TO_SITE_NONE = 'none';
	const LINK_TO_SITE_TOP = 'top';
	const LINK_TO_SITE_BOTTOM = 'bottom';
	const LINK_TO_SITE_TOP_BOTTOM = 'top-bottom';
	const LINK_TO_SITE_NOTIFICATION = 'notification';

	const NOTIFICATION_THEME_DARK = 'dark';
	const NOTIFICATION_THEME_LIGHT = 'light';

	const SEARCH_BOX_NONE = 'none';
	const SEARCH_BOX_CONTENT_TOP = 'top';
	const SEARCH_BOX_CONTENT_BOTTOM = 'bottom';
	const SEARCH_BOX_HEADER_TOP = 'header-top';
	const SEARCH_BOX_HEADER_BOTTOM = 'header-bottom';
	const SEARCH_BOX_MENU_TOP = 'menu-top';
	const SEARCH_BOX_MENU_BOTTOM = 'menu-bottom';

	const SOCIAL_BUTTONS_LOCATION_NONE = 'none';
	const SOCIAL_BUTTONS_LOCATION_AFTER_INFO_BLOCK = 'after_info_block';
	const SOCIAL_BUTTONS_LOCATION_BEFORE = 'before';
	const SOCIAL_BUTTONS_LOCATION_AFTER = 'after';

	const SOCIAL_BUTTONS_TYPE_STATIC = 'static';
	const SOCIAL_BUTTONS_TYPE_AMPSOCIAL = 'amp-social';

	const SOCIAL_BUTTONS_THEME_COLORS = 'colors';
	const SOCIAL_BUTTONS_THEME_WHITE = 'white';
	const SOCIAL_BUTTONS_THEME_DARK = 'dark';
	const SOCIAL_BUTTONS_THEME_LIGHT = 'light';

	const SOCIAL_BUTTONS_STYLE_ROUNDED = 'rounded';
	const SOCIAL_BUTTONS_STYLE_SQUARED = 'squared';

	const LANGUAGE_SWITCHER_NONE = 'none';
	const LANGUAGE_SWITCHER_CONTENT_TOP = 'top';
	const LANGUAGE_SWITCHER_CONTENT_BOTTOM = 'bottom';
	const LANGUAGE_SWITCHER_HEADER_TOP = 'header-top';
	const LANGUAGE_SWITCHER_HEADER_BOTTOM = 'header-bottom';

	const LANGUAGE_SWITCHER_FLAGS = 'flags';
	const LANGUAGE_SWITCHER_NAMES = 'names';
	const LANGUAGE_SWITCHER_NAMES_HORIZONTAL = 'names-horiz';
	const LANGUAGE_SWITCHER_FLAGS_NAMES = 'flags-names';
	const LANGUAGE_SWITCHER_DROPDOWN_NAMES = 'dropdown-names';

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

		parent::__construct( $options );
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

		// wrap background image in url()
		$headerBackgroundImage = wbArrayGet( $this->beingSanitizedData, 'image_background_header' );
		if ( ! empty( $headerBackgroundImage ) ) {
			$this->beingSanitizedData['image_background_header'] =
				'url('
				. WblSystem_Route::absolutify( $headerBackgroundImage, $forceDomain = true )
				. ')';
		}

		// sanitize max width, MUST be in px, so that both WP sanitizers and our
		// CSS customizer are happy
		$contentMaxWidth = wbArrayGet( $this->beingSanitizedData, 'content_max_width' );
		$contentMaxWidth = empty( $contentMaxWidth ) ? $this->getDefault( 'content_max_width' ) : $contentMaxWidth;
		if (
			! is_int( $contentMaxWidth )
			&&
			! is_int( wbRTrim( strtolower( $contentMaxWidth ), 'px' ) )
		) {
			$this->beingSanitizedData['content_max_width'] = absint( $contentMaxWidth ) . 'px';
		}

		return $this->beingSanitizedData;
	}

	/**
	 * Manually set some default values in special cases, where
	 * they cannot be hardcoded
	 *
	 * @return $this
	 */
	public function setDefaults() {

		// cleanup regexp default is taken from AMP config object
		// as we want it overridable remotely
		// This prevents us from setting this as a default value in
		// the setting definition file, as we otherwise would get a loop
		// AMP config needs User config (to check if remote config is allowed by user)
		// while User config would then need AMP config to read the default reg exp value
		// so we just set it now manually
		$regExp = $this->get( 'cleanup_regexp' );
		if ( empty( $regExp ) ) {
			$this->set(
				'cleanup_regexp',
				WeeblrampFactory::getThe( 'weeblramp.config.amp' )
				                ->get( 'default_cleanup_regexp' )
			);
		}

		return $this;
	}

	/**
	 * Builds an array of data suitable for later rendering
	 * by one of our layouts
	 *
	 * Override for special cases processing
	 *
	 * @param $optionName
	 * @param $settingDef
	 *
	 * @return array
	 */
	protected function getSettingLayoutData( $settingDef ) {

		$__data = parent::getSettingLayoutData( $settingDef );

		switch ( $settingDef['name'] ) {
			case 'image_background_header':
				// drop the url() wrapper, required by CSS

				$__data['current_value'] = wbLtrim( $__data['current_value'], 'url(' );
				$__data['current_value'] = wbLtrim( $__data['current_value'], WblWordpress_Helper::getBaseUrl( $pathOnly = false ) );
				$__data['current_value'] = wbRtrim( $__data['current_value'], ')' );
				break;
		}

		return $__data;
	}
}
