<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author           weeblrPress
 * @copyright        (c) WeeblrPress - Weeblr,llc - 2020
 * @package          AMP on WordPress - weeblrAMP CE
 * @license          http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version          1.12.5.783
 * @date                2020-05-19
 */

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// no direct access
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * Provide a few string manipulation methods
 *
 * @since    0.2.1
 *
 */
class WblSystem_Strings {

	const NONE = 'none';
	const LOWERCASE = 'lowercase';
	const UPPERCASE = 'uppercase';
	const UCFIRST = 'ucfirst';

	/**
	 * Performs a preg_replace, wrapping it to catch errors
	 * caused by bad characters or otherwise
	 *
	 * @param string $pattern RegExp pattern
	 * @param string $replace RegExp replacement
	 * @param string $subject RegExp subject
	 * @param string $ref Optional reference, to be logged in case of error
	 *
	 * @return    string    the result of preg_replace operation
	 */
	public static function pr( $pattern, $replace, $subject, $ref = '' ) {

		static $pageUrl = null;

		$tmp = preg_replace( $pattern, $replace, $subject );
		if ( is_null( $tmp ) ) {
			$pageUrl = is_null( $pageUrl ) ? ( empty( $_SERVER['REQUEST_URI'] ) ? '' : ' on page ' . $_SERVER['REQUEST_URI'] ) : $pageUrl;
			WblSystem_Log::error(
				'wblib', '%s::%d: %s', __METHOD__, __LINE__,
				'RegExp failed: invalid character' . $pageUrl . ( empty( $ref ) ? '' : ' (' . $ref . ')' )
			);

			return $subject;
		} else {
			return $tmp;
		}
	}

	/**
	 * Format into K and M for large number
	 * 0 -> 9999 : literral
	 * 10 000 -> 999999 : 10K -> 999,9K (max one decimal)
	 * > 1000 000 : 1M -> 1,9M (max 1 decimals)
	 *
	 * @param $n
	 * @param $format
	 */
	public static function formatIntForTitle( $n ) {

		if ( $n < 10000 ) {
			return (int) $n;
		} else if ( $n < 1000000 ) {
			$n = $n / 100.0;
			$n = floor( $n ) / 10;
			$n = sprintf( '%.1f', $n ) . 'K';
		} else {
			$n = $n / 100000;
			$n = floor( $n ) / 10;
			$n = sprintf( '%.1f', $n ) . 'M';
		}

		return $n;
	}

	/**
	 * Explode a string about a delimiter, then store each part
	 * into an array, after trimming characters at both ends.
	 * Only non-empty cleaned items are added to the returned array.
	 *
	 * @param string $string
	 * @param string $delimiter
	 * @param string $caseHandling none | lowercase | uppercase | ufcirst
	 *
	 * @return array
	 */
	public static function stringToCleanedArray( $string, $delimiter = ',', $caseHandling = self::NONE ) {

		$output = array();
		$bits   = explode( $delimiter, $string );
		if ( ! empty( $bits ) ) {
			foreach ( $bits as $bit ) {
				$cleaned = StringHelper::trim( $bit );
				if ( ! empty( $cleaned ) ) {
					switch ( $caseHandling ) {
						case self::LOWERCASE:
							$output[] = JString::strtolower( $cleaned );
							break;
						case self::UPPERCASE:
							$output[] = JString::strtoupper( $cleaned );
							break;
						case self::UCFIRST:
							$output[] = JString::ucfirst( $cleaned );
							break;
						default:
							$output[] = $cleaned;
							break;
					}
				}
			}
		}

		return $output;
	}

	/**
	 * @package     Joomla.Platform
	 * @subpackage  Utilities
	 *
	 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE
	 */

	/**
	 * Method to extract key/value pairs out of a string with XML style attributes
	 *
	 * @param   string $string String containing XML style attributes
	 *
	 * @return  array  Key/Value pairs for the attributes
	 *
	 * @since   11.1
	 */
	public static function parseAttributes( $string ) {

		$attr     = array();
		$retarray = array();

		// Let's grab all the key/value pairs using a regular expression
		preg_match_all( '/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr );

		if ( is_array( $attr ) ) {
			$numPairs = count( $attr[1] );

			for ( $i = 0; $i < $numPairs; $i ++ ) {
				$retarray[ $attr[1][ $i ] ] = $attr[2][ $i ];
			}
		}

		return $retarray;
	}

	/**
	 * Return passed string as a pretty-printed JSON string, if
	 * PHP version allows.
	 *
	 * @param string $json
	 */
	public static function jsonPrettyPrint( $json ) {

		static $displayOptions = null;
		if ( is_null( $displayOptions ) ) {
			// display options based on PHP version
			$displayOptions = false;
			$displayOptions = defined( 'JSON_NUMERIC_CHECK' ) ? $displayOptions | JSON_NUMERIC_CHECK : $displayOptions;
			$displayOptions = defined( 'JSON_UNESCAPED_SLASHES' ) ? $displayOptions | JSON_UNESCAPED_SLASHES : $displayOptions;
			$displayOptions = defined( 'JSON_UNESCAPED_UNICODE' ) ? $displayOptions | JSON_UNESCAPED_UNICODE : $displayOptions;
			$displayOptions = defined( 'JSON_PRETTY_PRINT' ) && WblWordpress_Helper::isDebug() ? $displayOptions | JSON_PRETTY_PRINT : $displayOptions;
		}

		return json_encode( $json, $displayOptions );
	}

	/**
	 * Return passed string as a pretty-printed JSON string, if
	 * PHP version allows. Also UNESCAPE all slashes, as slashes
	 * escpaing will always happen on PHP less than 5.4
	 * While valid json, escaping slashes can sometimes cause issues
	 * and is a waste anyway.
	 *
	 * Meant to unescape URLs in json fields.Do not use if regular content
	 * may have escaped slashes that should stay escaped.
	 *
	 * @param string $json
	 *
	 * @return mixed|string|void
	 */
	public static function jsonPrettyPrintAndUnescapeSlashes( $json ) {

		static $shouldUnescapeSlashes = null;

		if ( is_null( $shouldUnescapeSlashes ) ) {
			$shouldUnescapeSlashes = ! defined( 'JSON_UNESCAPED_SLASHES' );
		}

		$encodedJson = self::jsonPrettyPrint( $json );
		if ( $shouldUnescapeSlashes ) {
			$encodedJson = str_replace( '\\/', '/', $encodedJson );
		}

		return $encodedJson;
	}

	/**
	 * Make a string safe to use as an HTML id attribute
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public static function asHtmlId( $id ) {

		$id = str_replace( array( '[', ']' ), '', esc_attr( $id ) );
		$id = str_replace( array( '/', '\\', '.' ), '_', $id );

		return $id;
	}
}
