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
defined( 'WBLIB_ROOT_PATH' ) || die;

class WblSystem_Version {

	const EDITION_FULL = 'full';
	const EDITION_COMMUNITY = 'community';

	/**
	 * Version related object
	 *
	 * 'package' => 'weeblrXXXX',
	 * 'version' => '1.12.5',
	 * 'version_full' => '1.12.5.783',
	 * 'date' => '2020-05-19',
	 * 'license' => 'http://www.gnu.org/copyleft/gpl.html GNU/GPL',
	 * 'copyright' => '(c) WeeblrPress - Weeblr,llc - 2020',
	 * 'author' => 'weeblrPress',
	 * 'url' => 'https://www.weeblrpress.com',
	 * 'documentation_url' => 'https://www.weeblrpress.com/documentation/products.weeblramp/1/index.html',
	 * 'edition' => 'community',
	 * 'php' => array(
	 *      'min' => '5.3',
	 *      'max' => ''
	 * ),
	 * 'wp' => array(
	 *      'min' => '4.5',
	 *      'max' => ''
	 * )
	 *
	 * @var array
	 */
	static protected $versions = array();

	/**
	 * Package name for this object
	 *
	 * @var null
	 */
	protected $package = null;

	/**
	 * Store versions information for packages
	 *
	 * @param string $package Unique id for the package
	 * @param array  $version An array of version information
	 */
	public function __construct( $versionInfo ) {

		if ( empty( $versionInfo['package'] ) ) {
			wbThrow( new InvalidArgumentException( 'wbLib: invalid version information sent, not package name' ) );
		}

		$this > $this->package = $versionInfo['package'];
		self::$versions[ $versionInfo['package'] ] = $versionInfo;
	}

	/**
	 * Detects whether we are on a local development version
	 *
	 * @return bool
	 */
	public static function isDevVersion() {

		return strpos( '1.12.5', '_version_' ) !== false;
	}

	/**
	 * Getter for package
	 */
	public function getPackage() {

		return self::$versions[ $this->package ]['package'];
	}

	/**
	 * Getter for package title
	 */
	public function getPackageTitle() {

		return self::$versions[ $this->package ]['package_title'];
	}

	/**
	 * Returns current version as full string
	 *
	 * @param bool $full If true, returns the version number with the build number
	 *
	 * @return string
	 */
	public function getVersion( $full = false ) {

		return $full ? self::$versions[ $this->package ]['version_full'] : self::$versions[ $this->package ]['version'];
	}

	/**
	 * Returns current value for a complex data type
	 *
	 * @param string $type php | wp
	 * @param string $subType min | max
	 *
	 * @return string
	 */
	public function getValue( $type, $subType ) {

		if ( isset( self::$versions[ $this->package ][ $type ] ) && isset( self::$versions[ $this->package ][ $type ][ $subType ] ) ) {
			return self::$versions[ $this->package ][ $type ][ $subType ];
		} else {
			return 'n/a';
		}
	}

	/**
	 * Getter for release date
	 */
	public function getReleaseDate() {

		return self::$versions[ $this->package ]['date'];
	}

	/**
	 * Getter for license
	 */
	public function getLicense() {

		return self::$versions[ $this->package ]['license'];
	}

	/**
	 * Getter for copyright
	 */
	public function getCopyright() {

		return self::$versions[ $this->package ]['copyright'];
	}

	/**
	 * Getter for Author
	 */
	public function getAuthor() {

		return self::$versions[ $this->package ]['author'];
	}

	/**
	 * Getter for URL
	 */
	public function getUrl() {

		return self::$versions[ $this->package ]['url'];
	}

	/**
	 * Getter for Edition
	 */
	public function getEdition() {

		return self::$versions[ $this->package ]['edition'];
	}

	/**
	 * Getter for Documentation URL
	 */
	public function getDocumentationUrl() {

		return self::$versions[ $this->package ]['documentation_url'];
	}

	/**
	 * True if current version is higher or equal than param.
	 *
	 * @param string $minVersion major.minor.patch
	 *
	 * @return bool
	 */
	public function isHigherThan( $minVersion, $version = null ) {

		$version = is_null( $version ) ? $this->getVersion() : $version;

		return version_compare( $version, $minVersion, 'ge' );
	}

	/**
	 * True if current version is strictly lower than param.
	 *
	 * @param string $minVersion major.minor.patch
	 *
	 * @return bool
	 */
	public function isLowerThan( $minVersion, $version = null ) {

		$version = is_null( $version ) ? $this->getVersion() : $version;

		return version_compare( $version, $minVersion, 'lt' );
	}

	/**
	 * Whether this version is marked compatible with dependency (wp or php)
	 * as per the specification passed during creation.
	 *
	 * True if current version if higher or equal to minimal dependency version
	 * and strictly lower than maximal dependency version.
	 *
	 * @param string $type php | wp
	 * @param string $version x[.y[.z]] version of the software checked
	 *
	 * @return int | bool 0 if package unknown, true if compatible, false if not
	 */
	public function isCompatibleWith( $type, $version ) {

		if ( ! array_key_exists( $type, self::$versions[ $this->package ] ) ) {
			return 0;
		}

		if ( ! empty( self::$versions[ $this->package ][ $type ]['min'] )
		     && ! $this->isHigherThan( self::$versions[ $this->package ][ $type ]['min'], $version )
		) {
			return false;
		}

		if ( ! empty( self::$versions[ $this->package ][ $type ]['max'] )
		     && ! $this->isLowerThan( self::$versions[ $this->package ][ $type ]['max'], $version )
		) {
			return false;
		}

		return true;
	}

}
