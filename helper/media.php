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

class WeeblrampHelper_Media {

	/**
	 * Finds a local image and read its dimensions
	 * If current dimensions as known by the caller are missing,
	 * they'll be replaced by the dimensions read from file
	 * This still allow overriding dimensions by user
	 *
	 * If the image is not local, then size cannot be read, and
	 * current dimensions will be unchanged.
	 *
	 * @param $url
	 * @param $currentDimensions
	 *
	 * @return mixed
	 */
	public static function findImageSizeIfMissing( $url, $currentDimensions ) {

		// no image, can't read size
		if ( empty( $url ) ) {
			return $currentDimensions;
		}

		// at least one dimension missing, try read size from file
		if ( empty( $currentDimensions['width'] ) || empty( $currentDimensions['height'] ) ) {
			$newDimensions = WblHtmlContent_Image::getImageSize( $url );
		}

		foreach ( $currentDimensions as $key => $value ) {
			if ( empty( $value ) && ! empty( $newDimensions[ $key ] ) ) {
				$currentDimensions[ $key ] = $newDimensions[ $key ];
			}
		}

		return $currentDimensions;
	}
}
