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
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * A few specific helpers
 */
class WblWordpress_Compat {

	/**
	 * @since 4.5.0
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function get_terms( $args ) {

		if ( self::isGTE( '4.5.0' ) ) {
			return get_terms( $args );
		} else {
			$taxonomies = wbArrayGet( $args, 'taxonomy', array() );
			unset( $args['taxonomy'] );

			return get_terms( $taxonomies, $args );
		}
	}

	/**
	 *
	 * @param string $fallback
	 *
	 * @return string
	 */
	public static function wp_get_document_title() {

		if ( self::isGTE( '4.4.0' ) ) {
			return wp_get_document_title();
		}

		return wp_title( $sep = '»', $display = false, $seplocation = 'right' );
	}

	/**
	 * Returns true if runnin WP version is
	 *    Greater Than or Equal
	 * the passed version
	 *
	 * @param string $version
	 *
	 * @return bool
	 */
	public static function isGTE( $version ) {

		global $wp_version;

		return version_compare( $wp_version, $version, '>=' );
	}
}
