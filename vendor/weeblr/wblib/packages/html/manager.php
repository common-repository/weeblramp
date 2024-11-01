<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author           weeblrPress
 * @copyright        (c) WeeblrPress - Weeblr,llc - 2020
 * @package          AMP on WordPress - weeblrAMP CE
 * @license          http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version          1.12.5.783
 * @date         2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * Manages html helpers
 *
 */
class WblHtml_Manager {

	const VERSION = '1.12.5.783';

	const ASSETS_PATH = '/assets';

	const DEV = 0;
	const PRODUCTION = 1;
	const SINGLE = 0;
	const BUNDLE = 1;
	public  $assetsMode     = self::DEV;
	private $assetsBundling = self::SINGLE;
	private $assetsVersions = array();
	private $rootUrl        = null;
	private $filesRoot      = WBLIB_ROOT_PATH;
	private $filesPath      = array( 'assets' => '' );

	public function __construct( $options ) {

		$debug                = WblWordpress_Helper::isDebug();
		$this->assetsMode     = $debug ? self::DEV : self::PRODUCTION;
		$this->assetsBundling = $debug ? self::SINGLE : self::BUNDLE;
		$this->rootUrl        = wbArrayGet( $options, 'root_url' );
		$this->filesRoot      = wbArrayGet( $options, 'files_root', $this->filesRoot );
		$this->filesPath      = wbArrayGet( $options, 'files_path', $this->filesPath );
	}

	public function getImageUrl( $relativePath ) {

		$fullUrl = wbSlashJoin( $this->rootUrl, $relativePath );

		return $fullUrl;
	}

	/**
	 * Build ups the full URL to a CSS or JS file, possibly minified/versioned/gzipped
	 *
	 * @param string $name JS file name, no extension
	 * @param string $type js | css
	 * @param array  $options
	 *                     files_root  Root path to file location, default to JPATH_ROOT
	 *                     files_path  Subpath to file location, will be added to files_root, default to "assets"
	 *                     url_root    Root URL to link files to, default to JURI::root(true)
	 *
	 * @return string
	 */
	public function getMediaLink( $name, $type, $options = array() ) {

		$rootUrl   = wbArrayGet( $options, 'root_url', $this->rootUrl );
		$filesRoot = wbArrayGet( $options, 'files_root', $this->filesRoot );
		$path      = wbArrayGet( $options, 'files_path', $this->filesPath );

		$link = $this->getMedia( 'url', $name, $type, $rootUrl, $filesRoot, $path, $options );

		return $link;
	}

	/**
	 * Build ups the full PATH (including filename) to a CSS or JS file, possibly minified/versioned/gzipped
	 *
	 * @param string $name JS file name, no extension
	 * @param string $type js | css
	 * @param array  $options
	 *                     files_root  Root path to file location, default to JPATH_ROOT
	 *                     files_path  Subpath to file location, will be added to files_root, default to "assets"
	 *                     url_root    Root URL to link files to, default to JURI::root(true)
	 *
	 * @return string
	 */
	public function getMediaFullPath( $name, $type, $options = array() ) {

		$filesRoot = wbArrayGet( $options, 'files_root', $this->filesRoot );
		$path      = wbArrayGet( $options, 'files_path', $this->filesPath );

		// getting a path: files_root is considered URL root
		$link = $this->getMedia( 'file', $name, $type, $filesRoot, $filesRoot, $path, $options );

		return $link;
	}

	private function getMedia( $resultType, $name, $type, $root, $filesRoot, $path, $options ) {

		$root = rtrim( $root, '/' );

		// possibly call for overrides and color themes
		/**
		 * Filter a list of directories to search for CSS overrides.
		 *
		 * You can provide directories where weeblrAMP should look for CSS or javascript overrides for its own CSS and javascript files. Those assets should bear the same names as the originals to be picked up.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_assets_path_supplemental
		 * @since   1.0.0
		 *
		 * @param array $supplementalFilePaths An array of directories full path
		 *
		 * @return array
		 */
		$supplementalFilePaths = is_admin() ? array() : apply_filters( 'weeblramp_assets_path_supplemental', array() );
		$paths                 = array_merge(
			$supplementalFilePaths,
			(array) $path
		);

		foreach ( $paths as $possiblePath => $version ) {
			$version      = empty( $version ) ? self::VERSION : $version;
			$possiblePath = '/' . trim( $possiblePath, '/' );

			$link = $this->buildFullPath( $possiblePath, $version, $resultType, $name, $type, $root, $filesRoot, $path, $options );
			if ( ! empty( $link ) ) {
				// found a link, as this is a URL, we can't check its validity
				// so we just return the first one found
				break;
			}
		}

		return $link;
	}

	private function buildFullPath( $possiblePath, $version, $resultType, $name, $type, $root, $filesRoot, $path, $options ) {

		$hash = md5( $resultType . $name . $type . $root . $possiblePath . $filesRoot . serialize( $options ) );

		if ( $this->assetsMode == self::PRODUCTION && ! isset( $this->assetsVersions[ $hash ] ) ) {
			$this->assetsVersions[ $hash ] = '/' . ( WblSystem_Version::isDevVersion() ? '__version__' : $version );
		}

		$mode     = wbArrayGet( $options, 'assets_mode', $this->assetsMode );
		$bundling = wbArrayGet( $options, 'assets_bundling', $this->assetsBundling );
		if ( $mode == self::PRODUCTION ) {
			$link = $root . $possiblePath . '/dist/'
			        . $type
			        . $this->assetsVersions[ $hash ]
			        . '/' . ( $bundling ? 'bundle' : $name )
			        . '.min.' . $type;
		} else {
			$link = $root . $possiblePath . '/dist/' . $type . '/raw/' . $name . '.' . $type;
		}

		if ( 'file' == $resultType && ! file_exists( $link ) ) {
			return '';
		}

		return $link;
	}
}
