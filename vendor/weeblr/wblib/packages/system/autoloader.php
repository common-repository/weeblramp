<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author      weeblrPress
 * @copyright   (c) WeeblrPress - Weeblr,llc - 2020
 * @package     AMP on WordPress - weeblrAMP CE
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     1.12.5.783
 * @date                2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 *
 * Autoloader, as per naming conventions:
 *
 * - root path stored in self::$_rootPathes, one per registered prefix
 * - process all classes starting with registered list of prefixes
 * - Global naming scheme:
 *   WblSubdirOthersubdir_Filename
 *
 *        Wbl is one of many prefixes. Prefixes are registered to the class
 *    using the registerPrefix() method, passing in the desired prefix
 *    together with an associated root path
 *    Subdir, Othersubdir are nested subdirs, all lowercase
 *
 *    After the first underscore, the file name is set. If the file name is missing
 *    then the last subdir name is used instead.
 *    It'll be lowercased as well
 *
 *    Example1: WblSubdirOthersubdir_Filename will be searched in
 *
 *    self::$_rootPathes['Wbl'] . '/subdir/othersubdir/filename.php'
 *
 *    Example2: YgExampleClassClient_Http will be searched for in
 *
 *    self::$_rootPathes['Yg'] . 'example/classe/client/http.php'
 *
 *        Example3: YgExampleClassClient will be searched for in
 *
 *    self::$_rootPathes['Yg'] . 'example/class/client/client.php'
 *
 *    Note: only forward slashes are used throughout
 *
 */
class WblSystem_Autoloader {

	// array of prefixes
	protected static $_prefixes   = array();
	protected static $_rootPathes = array();

	/**
	 * Register our autoloader function with PHP
	 *
	 * @param string $rootPath full path to root dir of library packages
	 */
	public static function initialize( $rootPath ) {

		// get other extensions/scripts autoloader out
		spl_autoload_unregister( "__autoload" );

		// add our own
		self::registerPrefix( 'Wbl', $rootPath );
		$registered = spl_autoload_register( array( 'WblSystem_Autoloader', 'autoload' ) );

		// stitch back any pre-existing autoload function at the end of the list
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		if ( ! $registered ) {
			wbThrow( new RuntimeException( 'Unable to initialize the wbLib autoloader, spl_autoload_register returned false' ) );
		}
	}

	/**
	 * Register a prefix and associate it with a root path. Classes which name
	 * start with that prefix will be loaded from that root path
	 *
	 * @param string $prefix a case-sensitive prefix
	 * @param string $rootPath a root path. All path and files are lower-cased
	 *
	 * @return bool
	 */
	public static function registerPrefix( $prefix, $rootPath ) {

		$prefix = trim( $prefix );
		// no prefix, no love
		if ( empty( $prefix ) ) {
			wbThrow( new RuntimeException( 'Empty prefix sent to registerPrefix.' ) );
		}

		$rootPath = trim( $rootPath );
		$rootPath = realpath( $rootPath );

		// path maybe set, or else use default
		if ( empty( $rootPath ) ) {
			wbThrow( new RuntimeException( 'Empty path sent to registerPrefix.' ) );
		}

		// we have a path, and a prefix, we can register them
		// store the prefix
		self::$_prefixes[ $prefix ] = strlen( $prefix );

		// finalize and store the path
		$rootPath                     = str_replace( DIRECTORY_SEPARATOR, '/', $rootPath );
		$rootPath                     = rtrim( $rootPath, '/' ) . '/';
		self::$_rootPathes[ $prefix ] = $rootPath;

		return true;
	}

	/**
	 * Actually load a class file, based on its name
	 *
	 * @param string $class the full class name
	 *
	 * @return bool true if the class file was found and included
	 */
	public static function autoload( $class ) {

		// check if not already there
		if ( class_exists( $class, false ) ) {
			return true;
		}

		// search for one of our prefixes, and exit if not found
		$prefix = self::_searchPrefix( $class );
		if ( empty( $prefix ) ) {
			return false;
		}

		// remove prefix
		$path = ltrim( substr( $class, self::$_prefixes[ $prefix ] ) );

		// separate path and file name
		$bits = explode( '_', $path );

		// do we have a filename ?
		$fileName = empty( $bits[1] ) ? '' : strtolower( $bits[1] );

		// process path
		preg_match_all( '#([A-Z][a-z0-9_]+)#', $bits[0], $matches );
		$path = '';
		if ( ! empty( $matches[0] ) ) {
			foreach ( $matches[0] as $part ) {
				$path .= self::_format( $part ) . '/';
			}
		}

		// if we don't have a filename yet, let's use the last sub dir name
		if ( ! empty( $fileName ) ) {
			$fullPath = self::$_rootPathes[ $prefix ] . $path . $fileName . '.php';
			$fullPath = file_exists( $fullPath ) ? $fullPath : '';
		} else {
			$fileName = strtolower( array_pop( $matches[0] ) ) . '.php';
			$fullPath = self::$_rootPathes[ $prefix ] . $path . $fileName;
			$fullPath = file_exists( $fullPath ) ? $fullPath : '';
			if ( empty( $fullPath ) ) {
				$fullPath = self::$_rootPathes[ $prefix ] . $fileName;
				$fullPath = file_exists( $fullPath ) ? $fullPath : '';
			}
		}

		// If the class is registered include the file.
		if ( ! empty( $fullPath ) ) {
			include_once $fullPath;

			return true;
		}

		return false;
	}

	/**
	 * Iterate over registered prefixes record and return
	 * longest prefix that matches beginning of class name
	 *
	 * @param string $class full class name we're trying to autoload
	 */
	protected static function _searchPrefix( $class ) {

		$prefix = '';
		foreach ( self::$_prefixes as $storedPrefix => $prefixLength ) {
			$match = substr( $class, 0, $prefixLength ) == $storedPrefix;
			if ( $match ) {
				if ( strlen( $storedPrefix ) > strlen( $prefix ) || empty( $prefix ) ) {
					$prefix = $storedPrefix;
				}
			}
		}

		return $prefix;
	}

	protected static function _format( $pathPart, $pluralize = false ) {

		$pathPart = trim( $pathPart );
		if ( empty( $pathPart ) ) {
			return '';
		}

		$formated = strtolower( $pathPart );
		if ( $pluralize ) {
			$formated .= strtolower( substr( $pathPart, - 1 ) ) == 's' ? 'es' : 's';
		}

		return $formated;
	}

}
