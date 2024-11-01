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
 * Specific helpers to handle transition across Yoast versions
 */
class WeeblrampIntegrationWordpressseo_Compat {

	/**
	 * Get the current Yoast version, or null if not installed/running
	 *
	 * @return string
	 */
	public static function getVersion() {

		static $version = null;

		if ( is_null( $version ) ) {
			if ( defined( 'WPSEO_VERSION' ) ) {
				$version = WPSEO_VERSION;
			} else {
				$version = '0';
			}
		}

		return $version;
	}

	/**
	 * Getter for meta description
	 * @return string|null
	 */
	public static function getMetaDescription() {

		if ( self::versionCompare( '14.0' ) ) {
			return YoastSEO()->meta->for_current_page()->description;
		} else {
			return WPSEO_Frontend::get_instance()->metadesc( false );
		}
	}

	/**
	 * Getter for OGP title.
	 *
	 * @return string
	 */
	public static function getOgpTitle() {

		if ( self::versionCompare( '14.0' ) ) {
			return YoastSEO()->meta->for_current_page()->open_graph_title;
		} else {
			return self::getPre14Ogp( 'og_title', '' );
		}
	}

	/**
	 * Getter for OGP description.
	 *
	 * @return string
	 */
	public static function getOgpDescription() {

		if ( self::versionCompare( '14.0' ) ) {
			return YoastSEO()->meta->for_current_page()->open_graph_description;
		} else {
			return self::getPre14Ogp( 'description', '' );
		}
	}

	/**
	 * Getter for OGP image of a given post.
	 *
	 * @param int $postId
	 *
	 * @return array|mixed|string|null
	 */
	public static function getOgpImage( $postId ) {

		if ( self::versionCompare( '14.0' ) ) {
			$images = YoastSEO()->meta->for_current_page()->open_graph_images;
			$image  = empty( $images ) || ! is_array( $images ) ? '' : array_shift( $images );

			return wbArrayGet( $image, 'url', '' );
		} else {
			return WPSEO_Meta::get_value( 'opengraph-image', $postId );
		}
	}

	/**
	 * Helper to get OGP details pre-version 14.0.
	 *
	 * @param string $dataName
	 * @param mixed  $default
	 *
	 * @return string
	 */
	private static function getPre14Ogp( $dataName, $default = '' ) {

		static $ogpModel = null;

		if ( empty( $ogpModel ) ) {
			// Yoast attaches multiple filters and action
			// which would break AMP pages, such as OGP prefix in <html> tag
			// so we do not use let it output ogp tags (and others), but instead
			// retrieve the data from the output captured
			global $wp_filter;
			$filtersBackup = $wp_filter;
			$ogpModel      = new WPSEO_OpenGraph;
			$wp_filter     = $filtersBackup;
		}

		if ( empty( $ogpModel ) ) {
			return $default;
		}

		return $ogpModel->{$dataName}( false );
	}

	/**
	 * Getter for Twitter cards title.
	 *
	 * @return string
	 */
	public static function getTCardsTitle() {

		if ( self::versionCompare( '14.0' ) ) {
			return YoastSEO()->meta->for_current_page()->twitter_title;
		} else {
			return self::getPre14TCards( 'title', '' );
		}
	}

	/**
	 * Getter for Twitter Cards description.
	 *
	 * @return string
	 */
	public static function getTCardsDescription() {

		if ( self::versionCompare( '14.0' ) ) {
			return YoastSEO()->meta->for_current_page()->twitter_description;
		} else {
			return self::getPre14TCards( 'description', '' );
		}
	}

	/**
	 * Getter for Twitter Cards image.
	 *
	 * @return string
	 */
	public static function getTCardsImage() {

		if ( self::versionCompare( '14.0' ) ) {
			return YoastSEO()->meta->for_current_page()->twitter_image;
		} else {
			return self::getPre14TCards( 'image', '' );
		}
	}

	/**
	 * Helper to get Twitter cards details pre-version 14.0.
	 *
	 * @param string $dataName
	 * @param mixed  $default
	 *
	 * @return string
	 */
	private static function getPre14TCards( $dataName, $default = '' ) {

		static $parsedTCards = null;

		if ( empty( $parsedTCards ) ) {
			// harder to extract data from Yoast Twitter object
			ob_start();
			WPSEO_Twitter::get_instance();
			$output = ob_get_clean();

			$rawBits    = WblSystem_Strings::stringToCleanedArray( $output, '<meta' );
			$parsedBits = array();
			foreach ( $rawBits as $bit ) {
				$bit                 = wbRTrim( $bit, '/>' );
				$parsedBit           = WblSystem_Strings::parseAttributes( $bit );
				$name                = str_replace( 'twitter:', '', $parsedBit['name'] );
				$parsedBits[ $name ] = $parsedBit['content'];
			}
		}

		if ( empty( $parsedTCards ) ) {
			return $default;
		}

		return $parsedTCards[ $dataName ];
	}

	/**
	 * Get current site name.
	 *
	 * @return array
	 */
	public static function getCompanyLogo() {

		if ( self::versionCompare( '14.0' ) ) {
			$logoId = self::getPageData( 'company_logo_id' );
			if ( empty( $logoId ) ) {
				return '';
			}

			return wp_get_attachment_url( $logoId );
		} else {
			return WPSEO_Options::get( 'company_logo', '' );
		}
	}

	public static function getOptionsOrPageData( $dataName, $default = null ) {

		if ( self::versionCompare( '14.0' ) ) {
			return self::getPageData( $dataName, $default );
		} else {
			return WPSEO_Options::get( $dataName, $default );
		}
	}

	/**
	 * Generic getter for current page information.
	 *
	 * @param mixed $itemName
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public static function getPageData( $itemName, $default = null ) {

		return self::versionCompare( '14.0' )
			?
			YoastSEO()->meta->for_current_page()->{$itemName}
			:
			$default;
	}

	/**
	 * Compares the current Yoast version with provided one.
	 *
	 * @param string $version Version to compare to
	 * @param string $compareType Optional comparison operator, default to ge
	 *
	 * @return mixed
	 */
	public static function versionCompare( $version, $compareType = 'ge' ) {

		return version_compare(
			self::getVersion(),
			$version,
			$compareType
		);
	}
}
