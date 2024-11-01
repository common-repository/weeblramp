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

/**
 * Allow access to basic information about the current
 * request. Can help extensions adjust their content
 * when rendering an AMP request
 *
 * Usage:
 *
 * Are we processing an AMP request?
 * boolean WeeblrAMPApi::isAMPRequest()
 *
 * Fully qualified canonical for the current AMP request
 * Empty if not an AMP request
 * string Weeblramp_Api::getCanonicalUrl()
 *
 * Get AMP URL for a path
 * If path is empty, current request is used
 * if $full is true, the URL is fully qualified
 * Weeblramp_Api::getAMPUrl($path = '', $full = true)
 *
 * Class Weeblramp_Api
 *
 * @method bool isAMPRequest()
 * @method string getCanonicalUrl()
 * @method string getAMPUrl( $path = '', $full = true )
 * @method bool isStandaloneMode()
 */
class Weeblramp_Api {

	/**
	 * Stores the weeblrAmp dispatcher
	 *
	 * @var null|object
	 */
	private static $dispatcher = null;

	/**
	 * Wraps a piece of content containing form or forms element
	 * so that it is converted to AMP without stripping them.
	 *
	 * @param string $content
	 *
	 * @return string mixed
	 *
	 * @since 1.8.2
	 *
	 */
	public static function wrapForm( $content ) {

		$content = WeeblrampFactory::getThe( 'weeblramp.content.protector' )
		                           ->protect(
			                           $content,
			                           array(
				                           WeeblrampFactory::getA(  // amp converter, with forms
					                           'WeeblrampModel_Ampprocessor',
					                           array(
						                           'amp_config' => WeeblrampFactory::getA( 'weeblramp.config.ampform' )
					                           )
				                           ),
				                           'convert'
			                           )
		                           );

		return $content;
	}

	/**
	 * Wraps a piece of content containing arbitrary content
	 * so that it is NOT converted nor stipped.
	 *
	 * @param string $content
	 *
	 * @return string mixed
	 *
	 * @since 1.8.3
	 *
	 */
	public static function wrapCustomContent( $content ) {

		$content = WeeblrampFactory::getThe( 'weeblramp.content.protector' )
		                           ->protect(
			                           $content
		                           );

		return $content;
	}

	/**
	 * Magic method to fetch information from weeblrAMP dispatcher
	 * Use only on frontend
	 *
	 * @param string $name The name of the api class method to call.
	 * @param array  $arguments Optional array of arguments passed to the method.
	 *
	 * @return mixed
	 */
	public static function __callStatic( $name, $arguments ) {

		if ( is_admin() ) {
			return false;
		}

		switch ( strtolower( $name ) ) {
			case 'isamprequest':
			case 'getcanonicalurl':
			case 'getampurl':
			case 'isstandalonemode':
				self::load_dispatcher();

				return self::$dispatcher->$name( $arguments );
				break;
			default:
				trigger_error( 'Method ' . $name . ' not defined', E_USER_ERROR );
				break;
		}
	}

	/**
	 * Cache an instance of the weeblrAMP dispatcher
	 */
	private static function load_dispatcher() {

		if ( is_null( self::$dispatcher ) ) {
			self::$dispatcher = WeeblrampFactory::getThe( 'WeeblrampClass_Dispatcher' );
		}
	}
}

