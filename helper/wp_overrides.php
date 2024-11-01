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

if ( ! class_exists( 'AMP_Image_Dimension_Extractor' ) ) {
	class AMP_Image_Dimension_Extractor {

		static public function extract( $url ) {

			$dimensions = WblHtmlContent_Image::getImageSize( $url );

			return array( $dimensions['width'], $dimensions['height'] );
		}
	}
}

