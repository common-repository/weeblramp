<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author       weeblrPress
 * @copyright    (c) WeeblrPress - Weeblr,llc - 2020
 * @package      AMP on WordPress - weeblrAMP CE
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version      1.12.5.783
 * @date         2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * HTML output helper
 *
 */
class WblHtml_Helper {

	/**
	 * Expand an associative array into an html string of attributes
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function attrToHtml( $attributes ) {

		$output = '';
		if ( ! is_array( $attributes ) ) {
			return $output;
		}

		foreach ( $attributes as $key => $value ) {
			$output .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}

		return $output;
	}

	/**
	 * Wraps a list of items in an unordered list
	 *
	 * @param array  $items list of strings
	 * @param string $class optional class to applied to the ul
	 *
	 * @return string
	 */
	public static function makeList( $items, $ulClass = '', $liClass = '' ) {

		if ( ! empty( $ulClass ) ) {
			$ulClass = self::attrToHtml( array( 'class' => $ulClass ) );
		}
		if ( ! empty( $liClass ) ) {
			$liClass = self::attrToHtml( array( 'class' => $liClass ) );
		}
		$items  = is_array( $items ) ? $items : (array) $items;
		$output = "<ul{$ulClass}><li{$liClass}>" . implode( "</li><li{$liClass}>", $items ) . '</li></ul>';

		return $output;
	}

	/**
	 * Returns and optionally echo a block of HTML, surrounded by comments
	 * built with provided title
	 *
	 * @param string $html
	 * @param string $title
	 * @param bool   $echo
	 *
	 * @return string
	 */
	public static function printHtmlBlock( $html, $title, $echo = false ) {

		$printedBlock = "\t" . '<!-- weeblrAMP: ' . $title . ' -->';
		$printedBlock .= "\n" . $html;
		$printedBlock .= "\t" . '<!-- weeblrAMP: ' . $title . ' -->' . "\n";

		if ( $echo ) {
			echo $printedBlock;
		}

		return $printedBlock;
	}
}
