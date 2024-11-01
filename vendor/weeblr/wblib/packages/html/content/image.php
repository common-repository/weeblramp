<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author      weeblrPress
 * @copyright   (c) WeeblrPress - Weeblr,llc - 2020
 * @package     AMP on WordPress - weeblrAMP CE
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     1.12.5.783
 * @date        2020-05-19
 */

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die();

class WblHtmlContent_Image {

	const IMAGE_SEARCH_NONE = 0;
	const IMAGE_SEARCH_FIRST = 1;
	const IMAGE_SEARCH_LARGEST = 2;

	/**
	 * Get an image size from the file
	 *
	 * @param $url
	 *
	 * @return array Width/height of the image, 0/0 if not found
	 */
	public static function getImageSize( $url ) {

		// default values ?
		$dimensions = array( 'width' => 0, 'height' => 0 );
		$imagePath  = self::getImageLocalPath( $url );
		$isRemote   = false == $imagePath;

		if ( $isRemote ) {
			// a URL, but not on this site, try to download it and read its size
			$dimensions = self::getRemoteImageDimensions( $url );
		} else {
			if ( file_exists( $imagePath ) ) {
				$imageInfos = getimagesize( $imagePath );
				if ( ! empty( $imageInfos ) ) {
					$dimensions = array( 'width' => $imageInfos[0], 'height' => $imageInfos[1] );
				}
			}
		}

		return $dimensions;
	}

	/**
	 * Computes an image path local full path. Returns false if the image is remote.
	 *
	 * @param string $url
	 *
	 * @return bool|string
	 */
	public static function getImageLocalPath( $url ) {

		static $rootPath = '';
		static $pathLength = 0;
		static $rootUrl = '';
		static $rootLength = 0;
		static $protocoleRelRootUrl = '';
		static $protocoleRelRootLength = 0;

		if ( empty( $rootPath ) ) {
			$rootUrl                = home_url( '/' );
			$rootLength             = StringHelper::strlen( $rootUrl );
			$protocoleRelRootUrl    = str_replace( array( 'https://', 'http://' ), '//', $rootUrl );
			$protocoleRelRootLength = StringHelper::strlen( $protocoleRelRootUrl );
			$rootPath               = WblWordpress_Helper::getBaseUrl( true );
			$pathLength             = StringHelper::strlen( $rootPath );
		}

		// build the physical path from the URL
		$isRemote = false;
		if ( StringHelper::substr( $url, 0, $rootLength ) == $rootUrl ) {
			$cleanedPath = StringHelper::substr( $url, $rootLength );
		} else if ( StringHelper::substr( $url, 0, 2 ) == '//' && substr( $url, 0, $protocoleRelRootLength ) == $protocoleRelRootUrl ) {
			// protocol relative URL
			$cleanedPath = StringHelper::substr( $url, $protocoleRelRootLength );
		} else if ( WblSystem_Route::isFullyQUalified( $url ) ) {
			$isRemote = true;
		} else {
			$cleanedPath = $url;
		}

		if ( $isRemote ) {
			$imagePath = false;
		} else {
			$cleanedPath = ! empty( $rootPath ) && StringHelper::substr( $cleanedPath, 0, $pathLength ) == $rootPath ? StringHelper::substr( $url, $pathLength ) : $cleanedPath;
			include_once ABSPATH . 'wp-admin/includes/file.php';
			$imagePath = get_home_path() . $cleanedPath;
		}

		return $imagePath;
	}

	/**
	 * Based on AMP plugin
	 *
	 * @param $dimensions
	 * @param $url
	 *
	 * @return array|bool|mixed
	 */
	public static function readRemoteImageDimensions( $url ) {

		$url_hash = md5( $url );

		// Very simple lock to prevent stampedes
		$transient_lock_name = sprintf( 'wblib_tr_img_lock_%s', $url_hash );
		if ( false !== get_transient( $transient_lock_name ) ) {
			return false;
		}
		set_transient( $transient_lock_name, 1, MINUTE_IN_SECONDS );

		// use utility class to fetch remote image
		$dimensions = WblFactory::getThe( 'WblHtmlContentRemoteimage_FasterImage' )
		                        ->getSize( $url );

		// clear lock
		delete_transient( $transient_lock_name );

		// format response
		if ( is_array( $dimensions ) ) {
			$dimensions = array( 'width' => $dimensions[0], 'height' => $dimensions[1] );
		} else {
			$dimensions = false;
		}

		return $dimensions;
	}

	/**
	 * Lookup an image tag in some html content, and returns the src attribute,
	 * based on a selection process:
	 * - none, first found or largest image selection
	 * - an array of minimal width/height the image must have to be included in the selection process
	 *
	 * @param string $content the raw content
	 * @param int    $selectionMode one of this class constant for search mode
	 * @param array  $requiredSize a minimal width/height specification
	 *
	 * @return string image URL, as found in the content (ie relative or absolute)
	 */
	public static function getBestImage( $content, $selectionMode = self::IMAGE_SEARCH_NONE, $requiredSize ) {

		$bestImage = '';

		// save time if no image in content
		if ( empty( $content ) || ! wbContains( $content, '<img' ) || $selectionMode == self::IMAGE_SEARCH_NONE ) {
			return $bestImage;
		}

		// check for a "disable auto search tag" in content
		if ( wbContains( $content, '{disable_auto_meta_image_detection}' ) ) {
			return $bestImage;
		}

		// collect images, and select one according to settings
		$regex = '#<img([^>]*)>#Uum';
		$found = preg_match_all( $regex, $content, $matches, PREG_SET_ORDER );
		if ( ! empty( $found ) ) {
			$bestImageSize = 0;
			foreach ( $matches as $match ) {
				$imageUrl = '';
				if ( ! empty( $match[1] ) ) {
					$attributes = WblSystem_Strings::parseAttributes( $match[1] );
					if ( ! empty( $attributes['src'] ) ) {
						$imageUrl = $attributes['src'];
					}
				}
				if ( ! empty( $imageUrl ) ) {
					// validate size (200x200)
					$imageSize = self::getImageSize( $imageUrl );

					// is it big enough?
					if (
						( ! empty( $imageSize['width'] ) && ( ! empty( $imageSize['width'] ) && $imageSize['width'] >= $requiredSize['width'] ) )
						&&
						( ! empty( $imageSize['height'] ) && ( ! empty( $imageSize['height'] ) && $imageSize['height'] >= $requiredSize['height'] ) )
					) {
						if ( self::IMAGE_SEARCH_FIRST == $selectionMode ) {
							// we got a winner
							$bestImage = $imageUrl;
							break;
						} else {
							// looking for the biggest one
							// store current image size
							$currentImageSize = (int) $imageSize['width'] + (int) $imageSize['height'];
							if ( $currentImageSize > $bestImageSize ) {
								$bestImage     = $imageUrl;
								$bestImageSize = $currentImageSize;
							}
						}
					}
				}
			}
		}

		return $bestImage;
	}

	/**
	 * Find and cache dimensions of a remotely stored image
	 *
	 * @param string $url
	 *
	 * @return array
	 */
	protected static function getRemoteImageDimensions( $url ) {

		$dimensions = array( 'width' => 0, 'height' => 0 );

		// if this is a protocole relative, URL, make sure we have a scheme
		if ( StringHelper::substr( $url, 0, 2 ) == '//' ) {
			$url = set_url_scheme( $url );
		}

		$remoteImageDimensions =
			WblFactory::getA( 'WblSystem_Cache' )
			          ->get(
			          // cache ID
				          md5( $url ),
				          // callback for when item is not in cache
				          array(
					          'WblHtmlContent_Image',
					          'readRemoteImageDimensions'
				          ),
				          // callback param
				          $url,
				          // TTL
				          30 * DAY_IN_SECONDS
			          );

		$dimensions = false == $remoteImageDimensions ? $dimensions : $remoteImageDimensions;

		// return now
		return $dimensions;
	}
}
