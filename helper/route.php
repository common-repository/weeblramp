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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;
use Weeblr\Wblib\Joomla\Uri\Uri;

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Route helper
 *
 */
class WeeblrampHelper_Route {

	private static $localhosts = array(
		'127.0.0.1',
		'locahost',
		/*'local.web',*/
		'local.dev'
	);

	/**
	 * Decides if a given path specification rule applies
	 * to the current request
	 *
	 * Rule specs:
	 * * => any URL
	 * xxxx => exactly 'xxxxx'
	 * xxx?yyy => 'xxx' + any character + 'yyy'
	 * xxx*yyy => 'xxx' + any string + 'yyy'
	 * *xxxx => any string + 'xxxxx'
	 * xxxx* => 'xxxx' + any string
	 * *xxxx* => any string + 'xxxxx' + any string
	 * *xxxx*yyyy => any string + 'xxxxx' + any string + 'yyyy'
	 *
	 * @param string $rule
	 * @param string $path the path relative to the root of the site, starting with a /
	 */
	public static function pathRuleMatch( $rule, $path ) {

		// shortcuts
		if ( '*' == $rule ) {
			return true;
		}

		// build a reg exp based on rule
		if ( StringHelper::substr( $rule, 0, 1 ) == '~' ) {
			// this is a regexp, use it directly
			$regExp = $rule;
		} else {
			// actually build the reg exp
			$saneStarBits = array();
			$starBits     = explode( '*', $rule );
			foreach ( $starBits as $sBit ) {
				// same thing with ?
				$questionBits = explode( '?', $sBit );
				$saneQBit     = array();
				foreach ( $questionBits as $qBit ) {
					$saneQBit[] = preg_quote( $qBit );
				}

				$saneStarBits[] = implode( '?', $saneQBit );
			}

			// each part has been preg_quoted
			$sanitized = implode( '*', $saneStarBits );
			$regExp    = str_replace( '?', '.', $sanitized );
			$regExp    = str_replace( '*', '.*', $regExp );
			$regExp    = '~^' . $regExp . '$~uU';
		}

		// execute and return
		$shouldApply = preg_match( $regExp, $path );

		return $shouldApply;
	}

	/**
	 * Get a debug security token as set by user, only if in development mode
	 *
	 * @return string
	 */
	public static function getDebugToken() {

		$userConfig = WeeblrampFactory::getThe( 'weeblramp.config.user' );
		if ( WeeblrampConfig_User::OP_MODE_DEV != $userConfig->get( 'op_mode' ) ) {
			return '';
		}

		return $userConfig->get( 'debug_token' );
	}

	/**
	 * Add a debug token as a query variable to a URL or a URI object
	 *
	 * @param string | Uri $url
	 *
	 * @return string
	 */
	public static function addDebugTokenQuery( $url ) {

		$token = self::getDebugToken();
		if ( empty( $token ) ) {
			return $url;
		}

		if ( $url instanceof Uri ) {
			$url->setVar( 'amptoken', urlencode( $token ) );
		} else {
			$separator = wbContains( $url, '?' ) ? '&' : '?';
			$url       .= $separator . 'amptoken=' . urlencode( $token );
		}

		return $url;
	}

	/**
	 * Remove a debug token query variable from a URL or a URI object
	 *
	 * @param string | Uri $url
	 *
	 * @return mixed
	 */
	public static function removeDebugTokenQuery( $url ) {

		$token = self::getDebugToken();
		if ( empty( $token ) ) {
			return $url;
		}

		if ( $url instanceof Uri ) {
			$url->delVar( 'amptoken' );
		} else {
			$url = str_replace(
				array(
					'&amptoken=' . urlencode( $token ),
					'?amptoken=' . urlencode( $token )
				),
				'',
				$url
			);
		}

		return $url;
	}

	/**
	 * Decides if a request should be blocked for lack of a debug token
	 * in the URL requested (as a GET variable).
	 *
	 * @return bool
	 */
	public static function shouldBlockAmpRequest() {

		$configuredToken = self::getDebugToken();
		if ( empty( $configuredToken ) ) {
			return false;
		}

		$requestToken = wbArrayGet( $_GET, 'amptoken', '' );

		// block if configured token different from token in request
		return $requestToken != $configuredToken;
	}

	/**
	 * Decides whether to show the debug module, based on configuration
	 *
	 * Module is NOT displayed on localhosts, as validators need to access
	 * the page
	 *
	 * @return bool
	 */
	public static function shouldShowDebugModule() {

		if ( in_array(
			strtolower( WblWordpress_Helper::getHost() ),
			self::$localhosts
		)
		) {
			return false;
		}

		$userConfig = WeeblrampFactory::getThe( 'weeblramp.config.user' );
		if ( WeeblrampConfig_User::OP_MODE_DEV == $userConfig->get( 'op_mode' ) && $userConfig->isFalsy( 'hide_debug_module' ) ) {
			return true;
		}

		return false;
	}
}
