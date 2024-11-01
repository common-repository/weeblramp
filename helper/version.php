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
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Performs checks related to version of things
 */
class WeeblrampHelper_Version {

	/**
	 * @var Object Holds various version information about this plugin.
	 */
	static private $versionInfo = null;

	static private $editionTitles = array(
		'full'      => '',
		'community' => 'Community edition'
	);

	/**
	 * Performs sanity check on the system (php and wp versions).
	 *
	 * @return array
	 */
	public static function checkSystem() {

		try {
			$errors = array();

			// check PHP version
			self::load();
			if ( ! self::$versionInfo->isCompatibleWith( 'php', PHP_VERSION ) ) {
				$maxPhp       = self::$versionInfo->getValue( 'php', 'max' );
				$maxPhpString = empty( $maxPhp ) ? '' : sprintf( __( ', but less than %s', 'weeblramp' ), $maxPhp );
				$errors[]     =
					sprintf(
						__( 'weeblrAMP needs the PHP version running on your server to be %s or higher%s. You are using PHP version %s, which is not compatible. Please make sure you use a compatible version.', 'weeblramp' ),
						self::$versionInfo->getValue( 'php', 'min' ),
						$maxPhpString,
						PHP_VERSION
					);
			}

			// check WP version
			global $wp_version;
			if ( ! self::$versionInfo->isCompatibleWith( 'wp', $wp_version ) ) {
				$maxWp       = self::$versionInfo->getValue( 'wp', 'max' );
				$maxWpString = empty( $maxWp ) ? '' : sprintf( __( ', but less than %s' ), $maxWp );
				$errors[]    =
					sprintf(
						__( 'weeblrAMP needs your WordPress version to be %s or higher%s. You are using WordPress version %s, which is not compatible. Please make sure you use a compatible version.', 'weeblramp' ),
						self::$versionInfo->getValue( 'wp', 'min' ),
						$maxWpString,
						$wp_version
					);
			}
		}
		catch ( Exception $e ) {
			// die and inform user
			$errors[] = $e->getMessage();
		}

		return $errors;
	}

	/**
	 * returns whether we are running the full edition of this plugin
	 * @return bool
	 */
	public static function isFullEdition() {

		static $isFull = null;

		if ( is_null( $isFull ) ) {
			$isFull = self::isEdition( WblSystem_Version::EDITION_FULL );
		}

		return $isFull;
	}

	/**
	 * Get the current plugin edition. Shortcut to getting it from the version object directly.
	 *
	 * @return int
	 */
	public static function getEdition() {

		static $edition = null;

		if ( is_null( $edition ) ) {
			self::load();

			$edition = self::$versionInfo->getEdition();
		}

		return $edition;
	}

	/**
	 * Gets a translated title for the current edition.
	 */
	public static function getEditionTitle() {

		self::load();

		$editionTitle = wbArrayGet( self::$editionTitles, self::$versionInfo->getEdition(), '' );

		return __( $editionTitle, 'weeblramp' );
	}

	/**
	 * Finds if current install is a given edition type.
	 *
	 * @param string $edition
	 *
	 * @return bool
	 */
	public static function isEdition( $edition ) {

		self::load();

		$isEdition = $edition == self::$versionInfo->getEdition();

		return $isEdition;
	}

	/**
	 * Finds if current install is one of the provided editions.
	 *
	 * @param array $editions
	 *
	 * @return bool
	 */
	public static function isOneOfEditions( $editions = array() ) {

		self::load();

		$isInList = in_array( self::$versionInfo->getEdition(), $editions );

		return $isInList;
	}

	/**
	 * Loader for the edition-specific version of a file.
	 *
	 * Seach for the file in a "edition_name" sub-directory of the original directory.
	 *
	 * @param string $file Full original file path.
	 * @param bool   $once If true, file is loaded with include_once, else include;
	 *
	 * @return mixed The inclusion returned value, or false if file is not found.
	 */
	public static function loadEditionFile( $file, $once = true ) {

		$output   = false;
		$fileName = basename( $file );
		$path     = wbRTrim( $file, $fileName );
		$fullPath = $path . strtolower( self::getEdition() ) . '/' . $fileName;
		if ( file_exists( $fullPath ) ) {
			$output = $once ? include_once $fullPath : include $fullPath;
		}

		return $output;
	}

	/**
	 * Update a setting definition based on current edition.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function updateSettingForEdition( $data ) {

		$editions   = wbArrayGet( $data, array( 'details', 'editions' ), array() );
		$isDisabled = ! WeeblrampHelper_Version::isOneOfEditions( $editions );
		if ( $isDisabled ) {
			$data['disabled'] = true;
			$settingType      = wbArrayGet( $data, 'type' );
			switch ( $settingType ) {

				// do not set the disabled flag on settings which are groups of items,
				// the disabled state is determined item per item.
				case WblSystem_Config::OPTION_CHECKBOX_GROUP:
				case WblSystem_Config::OPTION_LIST:
					break;
				default:
					$data['details']['content']['attr']['disabled'] = 'disabled';
					break;
			}
		}

		return $data;
	}

	/**
	 * Builds a notice message prompting user to upgrade to full edition.
	 *
	 * Can be different based on setting type.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function getEditionUpdateMessage( $data ) {

		$settingType = wbArrayGet( $data, 'type' );
		$upgradeIcon =
			'<span class="wblib-settings-upgrade-icon">' . WeeblrampFactory::getThe( 'weeblramp.config.system' )->get( 'assets.icons.upgrade', '' ) . '</span>';
		switch ( $settingType ) {

			case WblSystem_Config::OPTION_SECTION:
				$msg = $upgradeIcon . '<span class="description wblib-na-this-edition wblib-upgrade-description wblib-upgrade-description">' . __( '<a class="wblib-upgrade-link js-wblib-upgrade-link" href="#0">Upgrade</a> to access all options in this section.', 'weeblramp' ) . '</span>';
				break;
			default:
				$msg = $upgradeIcon . '<span class="description wblib-settings-description wblib-na-this-edition wblib-upgrade-description">' . __( '<a class="wblib-upgrade-link js-wblib-upgrade-link" href="#0">Upgrade</a> to enable this setting or all of its options.', 'weeblramp' ) . '</span>';
				break;
		}

		return $msg;
	}

	/**
	 * Validate an HTML tag dimension (width or height)
	 *
	 * - must be numeric (no % or other sign)
	 * - except when set in px, ie 250px is valid
	 *
	 * @param mixed $dimension
	 * @param int   $default value if invalid
	 *
	 * @return int
	 */
	public static function getUpgradeSettingDefContent() {

		$content = array(
			'name'     => 'upgrade_text',
			'type'     => WblSystem_Config::OPTION_RAW_HTML,
			'editions' => array( WblSystem_Version::EDITION_COMMUNITY ),
			'class'    => '',
			'default'  =>
				'<h2 class="wblib-settings-section">'
				. '<span class="wblib-admin-section-icon">'
				. WeeblrampFactory::getThe( 'weeblramp.config.system' )->get( 'assets.icons.upgrade' )
				. '</span>'
				. __( 'Bring the full AMP experience to your visitors', 'weeblramp' )
				. '</h2>'
				. '<p>'
				. __( 'You are using the <strong>Community edition of weeblrAMP</strong>, which we develop for, and share with the WordPress community.', 'weeblramp' )
				. '</p>'
				. '<p>'
				. __( 'Our premium edition of weeblrAMP brings you <strong>support and an extended feature set</strong>:', 'weeblramp' )
				. '</p>'
				. '<p></p>'
				. '<ul>'
				. '<li>'
				. wbJoin(
					'</li><li>',
					__( '<strong>Automatic forms conversion</strong>: Contact Form 7, Gravity Forms and WPForms', 'weeblramp' ),
					__( 'Extensive <strong>WooCommerce</strong> support, with an AMP Add-to-cart button', 'weeblramp' ),
					__( 'Automatic paragraph-count-based, or shortcodes-based ads insertion in content', 'weeblramp' ),
					__( 'Extensive <strong>Easy Digital Downloads</strong> support, with categories navigation and AMP purchase buttons', 'weeblramp' ),
					__( '<strong>Mailchimp for WP</strong> support, with automatic conversion of your signup forms', 'weeblramp' ),
					__( 'Automatic paragraph-count-based, or shortcodes-based ads insertion in content', 'weeblramp' ),
					__( 'Tracking <strong>user events</strong> with Google Analytics', 'weeblramp' ),
					__( 'Live commenting for both <strong>WordPress and Disqus comments</strong>', 'weeblramp' ),
					__( 'One-click Disqus setup, with nothing to do on your server', 'weeblramp' ),
					__( 'Full <strong>search</strong> support', 'weeblramp' ),
					__( 'Enhanced static social sharing buttons', 'weeblramp' ),
					__( 'AMP-only widgets, to include all types of content', 'weeblramp' ),
					__( '<strong>PolyLang</strong> support', 'weeblramp' ),
					__( 'Extended schema.org support', 'weeblramp' ),
					__( 'A unique <strong>standalone mode</strong>', 'weeblramp' ),
					__( 'Professional support', 'weeblramp' )
				)
				. '</li>'
				. '</ul>'
				. __( '</ul><p></p>', 'weeblramp' )
				. __( '<a href="https://www.weeblrpress.com/plan/subscribe" target="_blank">Get weeblrAMP now!</a>', 'weeblramp' )
				. __( '<p class="">Prices range from $49/yr (3 sites) to $69/yr (unlimited sites + Extras), with a 20% renewal discount.</p>', 'weeblramp' )
			,
		);

		return $content;
	}

	/**
	 * Builds up a string to be displayed in a page footer, including
	 * product name, version, edition and links to home page.
	 *
	 * @param array $parts An array of items to be included in the string
	 *
	 * @return string
	 */
	public static function getDisplayableVersionInfo( $parts = array( 'title', 'version', 'edition', 'copyright', 'link' ) ) {

		static $partsContent = null;

		self::load();
		if ( is_null( $partsContent ) ) {
			$partsContent = array(
				'title'          => esc_html( self::$versionInfo->getPackageTitle() ),
				'version'        => 'version ' . esc_html( self::$versionInfo->getVersion( $full = true ) ),
				'version_simple' => 'version ' . esc_html( self::$versionInfo->getVersion( $full = false ) ),
				'edition'        => esc_html( self::getEditionTitle() ),
				'copyright'      => esc_html( self::$versionInfo->getCopyright() ),
				'link'           => '<a href="' . esc_url( self::$versionInfo->getUrl() ) . '" target="_blank">' . self::$versionInfo->getUrl() . '</a>'
			);
		}

		$versionInfo = array();
		foreach ( $parts as $part ) {
			$versionInfo[] = $partsContent[ $part ];
		}
		$versionInfo = implode( ' - ', $versionInfo );

		return $versionInfo;
	}

	/**
	 * Loads and stores version info if not already loaded.
	 */
	private static function load() {

		self::$versionInfo = is_null( self::$versionInfo ) ? WeeblrampFactory::getThe( 'weeblramp.version_info' ) : self::$versionInfo;
	}
}
