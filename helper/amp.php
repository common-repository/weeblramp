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

class WeeblrampHelper_Amp {

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
	public static function validateDimension( $dimension, $default = 0 ) {

		$validated = self::isValidDimension( $dimension ) ? $dimension : $default;

		return $validated;
	}

	/**
	 * Finds out if an HTML tag dimension is valid (width or height)
	 *
	 * - must be numeric (no % or other sign)
	 * - except when set in px, ie 250px is valid
	 *
	 * @param mixed $dimension
	 *
	 * @return bool
	 */
	public static function isValidDimension( $dimension ) {

		$validated = is_numeric( $dimension );
		if ( ! $validated && wbEndsWith( $dimension, 'px' ) ) {
			// try again without trailing px
			$dimension = wbRTrim( $dimension, 'px' );
			$validated = is_numeric( $dimension );
		}

		return $validated;
	}

	/**
	 * Makes sure an action URL is a valid AMP form action/action-xhr one:
	 *
	 * - https:// or // : untouched
	 * - http:// -> // or https://
	 * - relative: untouched
	 *
	 * @param string $url
	 * @param bool   $randomize If true, RANDOM AMP parameter is added to URL
	 *
	 */
	public static function makeAmpFormUrl( $url, $randomize = false ) {

		if ( WblSystem_Route::isFullyQUalified( $url ) ) {
			if ( wbStartsWith( $url, 'http://' ) ) {
				// insecure not allowed, replace with relative
				$url = '//' . wbLtrim( $url, 'http://' );
			}
		}

		if ( $randomize ) {
			$separator = wbContains( $url, '?' ) ? '&' : '?';
			$url       .= $separator . '__rwbamp=RANDOM';
		}

		return $url;
	}
}
