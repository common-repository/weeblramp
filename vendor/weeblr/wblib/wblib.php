<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author       weeblrPress
 * @copyright    (c) WeeblrPress - Weeblr,llc - 2020
 * @package      AMP on WordPress - weeblrAMP CE
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version      1.12.5.783
 * @date        2020-05-19
 *
 */

// no direct access
defined( 'WBLIB_ROOT_PATH' ) || die( __FILE__ );

Class Wblib {

	/**
	 * Setup db tables and other items as needed
	 */
	public function activate() {

		// db setup
		$this->updateDbSchema();

		return $this;
	}

	/**
	 * Remove db tables and any leftovers
	 */
	public function uninstall() {

		$this->removeDbSchema();

		return $this;
	}

	/**
	 * Run time init
	 */
	public function boot() {

		// path to layouts
		defined( 'WBLIB_LAYOUTS_PATH' ) or define( 'WBLIB_LAYOUTS_PATH', WBLIB_ROOT_PATH . 'layouts/' );
		defined( 'WBLIB_PACKAGES_PATH' ) or define( 'WBLIB_PACKAGES_PATH', WBLIB_ROOT_PATH . 'packages/' );
		// assets path from the PLUGIN root
		defined( 'WBLIB_ASSETS_PATH' ) or define( 'WBLIB_ASSETS_PATH', 'vendor/weeblr/wblib/assets/' );

		// global flags
		defined( 'WBLIB_LOG_EXCEPTIONS' ) or define( 'WBLIB_LOG_EXCEPTIONS', true );

		// load code from Joomla Framework
		include_once WBLIB_ROOT_PATH . 'vendor/joomla/string/src/phputf8/utf8.php';
		include_once WBLIB_ROOT_PATH . 'vendor/joomla/string/src/phputf8/trim.php';
		include_once WBLIB_ROOT_PATH . 'vendor/joomla/string/src/phputf8/ucfirst.php';
		include_once WBLIB_ROOT_PATH . 'vendor/joomla/string/src/StringHelper.php';
		include_once WBLIB_ROOT_PATH . 'vendor/joomla/uri/src/UriInterface.php';
		include_once WBLIB_ROOT_PATH . 'vendor/joomla/uri/src/AbstractUri.php';
		include_once WBLIB_ROOT_PATH . 'vendor/joomla/uri/src/UriHelper.php';
		include_once WBLIB_ROOT_PATH . 'vendor/joomla/uri/src/Uri.php';

		// register our autoloader
		$autoloader = WBLIB_PACKAGES_PATH . 'system/autoloader.php';
		if ( file_exists( $autoloader ) ) {
			include_once $autoloader;
			WblSystem_Autoloader::initialize( WBLIB_PACKAGES_PATH );
		} else {
			throw new RuntimeException( 'wbLib: cannot initialize autoloader, autoloader file is missing' );
		}

		// load php shortcuts functions, not autoloaded
		$file = WBLIB_PACKAGES_PATH . 'system/php_shortcuts.php';
		if ( file_exists( $file ) ) {
			include_once $file;
		} else {
			throw new RuntimeException( 'wbLib: cannot initialize php_shortcuts, php_shortcuts file is missing' );
		}

		defined( 'WBLIB_VERSION' ) or define( 'WBLIB_VERSION', '1.12.5' );

		return $this;
	}

	/**
	 * @TODO: move to own class
	 */
	private function updateDbSchema() {

		$queries = array();
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// generic key store
		$queries[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wblib_keystore` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`scope` VARCHAR(50) NOT NULL DEFAULT 'default',
			`key` VARCHAR(200) NOT NULL DEFAULT '',
			`value` MEDIUMTEXT NOT NULL,
			`user_id` INT NOT NULL DEFAULT 0,
			`format` TINYINT(3) NOT NULL DEFAULT 1,
			`modified_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (`id`),
			KEY `main` (`scope`,`key`)
			) " . $charset_collate;

		// run query
		$this->_runQueries( $queries );

		return $this;
	}

	/**
	 * @TODO: move to own class
	 */
	private function removeDbSchema() {

		global $wpdb;

		$queries = array(
			"DROP TABLE IF EXISTS `" . $wpdb->prefix . "wblib_keystore`"
		);

		$this->_runQueries( $queries );

		return $this;
	}

	/**
	 * @TODO: move to own class
	 */
	private function _runQueries( $queries ) {

		if ( empty( $queries ) ) {
			return;
		}

		global $wpdb;
		foreach ( $queries as $query ) {
			$wpdb->query( $query );
			if ( ! empty( $wpdb->last_error ) ) {
				throw new RuntimeException( $wpdb->last_error );
			}
		}

		return $this;
	}
}
