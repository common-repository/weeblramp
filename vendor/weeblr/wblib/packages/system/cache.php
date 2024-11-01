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

/**
 * Simple caching wrapper
 *
 * Class WblSystem_Cache
 */
class WblSystem_Cache {

	const DEFAULT_NAME_SPACE = 'wblib_tr';
	const DEFAULT_TTL = 43200; // 12 hours

	private $debug     = false;
	private $nameSpace = self::DEFAULT_NAME_SPACE;

	/**
	 * Sets whether we operate in debug mode, ie always using
	 * fresh data.
	 *
	 * WblSystem_Cache constructor.
	 */
	public function __construct( $options = array() ) {

		$this->debug     = WblWordpress_Helper::isDebug();
		$this->nameSpace = wbArrayGet( $options, 'name_space', self::DEFAULT_NAME_SPACE );
	}

	/**
	 * Sets a transient
	 *
	 * @param string $key
	 * @param        $value
	 * @param int    $ttl if null, use default TTL (12 hours)
	 */
	public function set( $key, $value, $ttl = self::DEFAULT_TTL ) {

		return set_transient( $this->nameSpace . '_' . $key, $value, $ttl );
	}

	/**
	 * Get a transient. If transient is not set, or return false:
	 *
	 * - if WP_DEBUG is true, always consider the value not set
	 *   (ie always force usage of fresh data)
	 * - if WP_DEBUG is false:
	 *   1 - if $callBack is a callback, call it to get the fresh value and cache that
	 *   2 - if $callBack is not a callback, return it, ie use it as a default value
	 *
	 * @param string        $key the transient key, will be prefixed with $this->nameSpace
	 * @param bool|callback $defaultOrCallback default value when key is not found. Can be a callback
	 * @param array         $args Optional arguments for the callback
	 * @param int           $ttl optional TTL when recaching a missing key with a callback
	 *
	 * @return bool|mixed
	 */
	public function get( $key, $defaultOrCallback = false, $args = array(), $ttl = self::DEFAULT_TTL ) {

		if ( ! $this->debug ) {
			// not in debug mode, fetch transient
			$value = get_transient( $this->nameSpace . '_' . $key );
		}

		// if debugging, or no value stored in transient
		if ( $this->debug || false === $value ) {
			// if we have a callback, use it and cache the result
			if ( is_callable( $defaultOrCallback ) ) {
				$value = call_user_func_array( $defaultOrCallback, array( $args ) );
				set_transient( $this->nameSpace . '_' . $key, $value, $ttl );
			} else {
				// not a callback, use the default value directly
				$value = $defaultOrCallback;
			}
		}

		return $value;
	}

	/**
	 * Delete a specific transient
	 *
	 * @param $key
	 */
	public function delete( $key ) {

		return delete_transient( $this->nameSpace . '_' . $key );
	}

	/**
	 *
	 * @param string $filter
	 */
	public function deleteAll() {

		WblDb_Helper::delete(
			'#__options',
			'option_name like ? or option_name like ?',
			array(
				'_transient_' . $this->nameSpace . '%',
				'_transient_timeout_' . $this->nameSpace . '%'
			)
		);
	}
}
