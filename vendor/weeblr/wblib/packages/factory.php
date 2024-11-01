<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author                  weeblrPress
 * @copyright               (c) WeeblrPress - Weeblr,llc - 2020
 * @package                 AMP on WordPress - weeblrAMP CE
 * @license                 http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version                 1.12.5.783
 *
 * 2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * Initialization of the plugin
 */
class WblFactory {

	/**
	 * Stores built objects
	 *
	 * @var array
	 */
	static protected $objects = array();

	/*
	 * Instance created by static facades
	 */
	static protected $instances = array();

	/**
	 * Create a new instance of an object of the requested class
	 * passing in optional array of parameters to its constructor
	 * Only works for array of params, or a single params
	 *
	 * @param string $class
	 * @param mixed  $args
	 *
	 * @return mixed
	 */
	public static function getA( $class, $args = null ) {

		return self::getFactory( __CLASS__ )->getObject( 'a', $class, $args );
	}

	/**
	 * Singleton method, can pass parameters to the constructor
	 *
	 * @param string $class
	 * @param mixed  $parameters
	 *
	 * @return mixed
	 */
	public static function getThe( $class, $args = null ) {

		return self::getFactory( __CLASS__ )->getObject( 'the', $class, $args );
	}

	/**
	 * Multiton method, can pass parameters to the constructor
	 *
	 * @param string $class
	 * @param string $key
	 * @param mixed  $parameters
	 *
	 * @return mixed
	 */
	public static function getThis( $class, $key, $args = null ) {

		return self::getFactory( __CLASS__ )->getObject( 'this', $class, $args, $key );
	}

	/**
	 * Builder for factory instance
	 *
	 * @return WblFactory
	 */
	protected static function getFactory( $factoryClass ) {

		if ( empty( self::$instances[ $factoryClass ] ) ) {
			self::$instances[ $factoryClass ] = new static();
		}

		return self::$instances[ $factoryClass ];
	}

	/**
	 * Manages storage of objects
	 *
	 * @param        $method
	 * @param        $class
	 * @param null   $args
	 * @param string $key
	 *
	 * @return mixed
	 * @throws Exception
	 */
	protected function getObject( $method, $class, $args = null, $key = '' ) {

		// then instantiate object
		switch ( $method ) {
			// return new object at each call
			case 'a':
				$object = $this->buildObject( $method, $class, $args, $key );
				break;

			// singleton
			case 'the':
				if ( empty( self::$objects[ $class ] ) ) {
					self::$objects[ $class ] = $this->buildObject( $method, $class, $args, $key );
				}

				$object = self::$objects[ $class ];
				break;

			// multiton
			case 'this':
				if ( empty( $key ) ) {
					throw new Exception( 'wbLib: no key specified while using method ' . $method . ', requesting object ' . $class );
				}

				$signature = $class . ' . ' . $key;
				if ( empty( self::$objects[ $signature ] ) ) {
					self::$objects[ $signature ] = $this->buildObject( $method, $class, $args, $key );
				}

				$object = self::$objects[ $signature ];
				break;

			// invalid method
			default:
				$this->invalidMethod( $method, $class, $args, $key );
				break;
		}

		return $object;
	}

	/**
	 * Build an object, with optional arguments
	 *
	 * @param        $method
	 * @param        $class
	 * @param null   $args
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function buildObject( $method, $class, $args = null, $key = '' ) {

		switch ( $class ) {
			// In descendant, build here objects for classes that require
			// specific build process
			// case 'SomeClass':
			//   return xxxx
			// break;
			// simply build an object of a class
			default:
				if ( class_exists( $class ) ) {
					if ( is_null( $args ) ) {
						return new $class();
					} else {
						return new $class( $args );
					}
				}
				break;
		}
	}

	/**
	 * Throw an invalid method exception
	 *
	 * @param        $method
	 * @param        $class
	 * @param null   $args
	 * @param string $key
	 *
	 * @throws Exception
	 */
	protected function invalidMethod( $method, $class, $args = null, $key = '' ) {

		throw new Exception( 'wbLib: invalid method ' . $method . ' when requesting object ' . $class );
	}
}
