<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author      weeblrPress
 * @copyright   (c) WeeblrPress - Weeblr,llc - 2020
 * @package     AMP on WordPress - weeblrAMP CE
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     1.12.5
 * @date                2020-05-19
 */

// no direct access
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * Utilities to load/parse xml
 *
 * @since    0.2.8
 *
 */
class WblSystem_Xml {

	public static function fromFile( $input, $class = null ) {

		$xml = self::_xml( $input, $class, 'file' );

		return $xml;
	}

	public static function fromString( $input, $class = null ) {

		$xml = self::_xml( $input, $class, 'string' );

		return $xml;
	}

	/**
	 * Remove invalid UTF-8 character from string, prevent fatal errors
	 * when using string as input for PHP XML functions
	 *
	 * @param $string
	 *
	 * @return mixed|string
	 */
	public static function sanitizeUTF8( $string ) {

		$output = preg_replace( '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $string );
		if ( is_null( $output ) ) {
			$output = "";
			if ( empty( $string ) ) {
				return $output;
			}

			$length = strlen( $string );
			for ( $i = 0; $i < $length; $i ++ ) {
				$current = ord( $string{$i} );
				if ( ( $current == 0x9 ) ||
				     ( $current == 0xA ) ||
				     ( $current == 0xD ) ||

				     ( ( $current >= 0x28 ) && ( $current <= 0xD7FF ) ) ||
				     ( ( $current >= 0xE000 ) && ( $current <= 0xFFFD ) ) ||
				     ( ( $current >= 0x10000 ) && ( $current <= 0x10FFFF ) )
				) {
					$output .= chr( $current );
				} else {
					$output .= " ";
				}
			}
		}

		return $output;
	}

	private static function _xml( $input, $class = null, $type ) {

		// Disable libxml errors and allow to fetch error information as needed
		$errorSetting = libxml_use_internal_errors( true );

		$xml = $type == 'file' ? simplexml_load_file( $input, $class ) : simplexml_load_string( $input, $class );

		libxml_use_internal_errors( $errorSetting );

		if ( $xml === false ) {
			foreach ( libxml_get_errors() as $error ) {
				WblSystem_Log::error( 'wbLib', '%s::%d: %s', __METHOD__, __LINE__, $error );
			}
		}

		return $xml;
	}
}
