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

/** ensure this file is being included by a parent file */
defined( 'WBLIB_ROOT_PATH' ) || die;

Class WblMvcLayout_Helper {

	public static $defaultBasePath = '';

	public static function render( $layoutFile, $__data = null, $basePath = '' ) {

		$basePath       = empty( $basePath ) ? self::$defaultBasePath : $basePath;
		$layout         = new WblMvcLayout_File( $layoutFile, $basePath );
		$renderedLayout = $layout->render( $__data );

		return $renderedLayout;
	}

	/**
	 * Check if a layout file exist
	 *
	 * @param string $layoutFile
	 * @param string $basePath
	 *
	 * @return bool
	 */
	public static function layoutExists( $layoutFile, $basePath = '' ) {

		$basePath = empty( $basePath ) ? self::$defaultBasePath : $basePath;
		$layout   = new WblMvcLayout_File( $layoutFile, $basePath );

		return $layout->exists();
	}

	/**
	 * Iterate over a list of layout files, and returns the name
	 * of the first that exists
	 *
	 * @param array  $layoutFiles
	 * @param string $basePath
	 *
	 * @return string
	 */
	public static function getExistingLayout( $layoutFiles, $basePath = '' ) {

		if ( empty( $layoutFiles ) ) {
			return '';
		}

		$layoutFiles = (array) $layoutFiles;
		foreach ( $layoutFiles as $layoutFile ) {
			if ( self::layoutExists( $layoutFile, $basePath ) ) {
				return $layoutFile;
			}
		}

		return '';
	}
}
