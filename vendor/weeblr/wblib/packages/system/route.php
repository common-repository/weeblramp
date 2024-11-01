<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author           weeblrPress
 * @copyright        (c) WeeblrPress - Weeblr,llc - 2020
 * @package          AMP on WordPress - weeblrAMP CE
 * @license          http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version          1.12.5.783
 * @date        2020-05-19
 */

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;
use Weeblr\Wblib\Joomla\Uri\Uri;

defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * Route helper
 *
 */
class WblSystem_Route {

	const FORCE_DOMAIN = true;

	public static $canonicalDomain = null;

	/**
	 * Turn a relative-to-page URL into an absolute one, using the site canonical domain if any
	 *
	 * @param      $url
	 * @param bool $forceDomain if URL is already absolute, we won't fully qualify it with a domain (if relative we
	 *                          still prepend the full domain)
	 *
	 * @return string
	 */
	public static function absolutify( $url, $forceDomain = false ) {

		// is it already absolute?
		if ( self::isFullyQUalified( $url ) ) {
			return $url;
		}

		// relative URL, make it fully qualified
		$base = WblWordpress_Helper::getBaseUrl( ! $forceDomain );

		if ( '/' == substr( $url, 0, 1 ) ) {
			if ( ! empty( $forceDomain ) ) {
				$url = StringHelper::rtrim(
						WblWordpress_Helper::getSiteUrl(
							array(
								'scheme',
								'host'
							)
						),
						'/'
					)
				       . '/'
				       . StringHelper::ltrim( $url, '/' );

				return $url;
			}
		}

		return StringHelper::rtrim( $base, '/' ) . '/' . StringHelper::ltrim( $url, '/' );
	}

	/**
	 * Finds if a URL is fully qualified, ie starts with a scheme
	 * Protocal-relative URLs are considered fully qualified
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public static function isFullyQUalified( $url ) {

		$isFullyQualified = substr( $url, 0, 7 ) == 'http://' || substr( $url, 0, 8 ) == 'https://' || substr( $url, 0, 2 ) == '//';

		return $isFullyQualified;
	}

	/**
	 * Make a url fully qualified and protocol relative
	 *
	 * @param string $url
	 *
	 * @return mixed|string
	 */
	public static function makeProtocolRelative( $url ) {

		$url = self::absolutify( $url, true );
		$url = preg_replace( '#^https?:\/\/#', '//', $url );

		return $url;
	}

	/**
	 * Builds and return the canonical domain of the page.
	 *
	 * We must use site_url() here, as home_url() would be wrong for our purpose on
	 * multilingual sites (PolyLang at least).
	 *
	 * @return string
	 */
	public static function getCanonicalRoot() {

		if ( is_null( self::$canonicalDomain ) ) {
			self::$canonicalDomain = StringHelper::trim(
				site_url(),
				'/'
			);
		}

		return self::$canonicalDomain;
	}

	/**
	 * Finds if an URL is internal, ie on the same site
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function isInternal( $url ) {

		// absolutify, prepending domain if missing
		$url = self::absolutify( $url, true );

		$canonicalRootUrl = self::getCanonicalRoot();
		if ( wbStartsWith( $url, '//' ) ) {
			$canonicalRootUrl = wbLTrim( $canonicalRootUrl, 'https:' );
			$canonicalRootUrl = wbLTrim( $canonicalRootUrl, 'http:' );
		}

		// is it local?
		/**
		 * Filter whether a URL is internal to the site.
		 *
		 * @api
		 * @package weeblrAMP\filter\route
		 * @var weeblramp_url_is_internal
		 * @since   1.0.4
		 *
		 * @param bool   $urlIsInternal Whether the URL is internal
		 * @param string $url The fully qualified URL we want to find about
		 * @param string $canonicalRootUrl The root URL of the site, as reported by WP
		 *
		 * @return string
		 */
		$internal = apply_filters(
			'weeblramp_url_is_internal',
			wbStartsWith( $url, $canonicalRootUrl ),
			$url,
			$canonicalRootUrl
		);

		return $internal;
	}

	/**
	 * Append a query variable with a random value to bust caching
	 *
	 * @param String $url
	 */
	public static function cacheBust( $url ) {

		if ( false !== strpos( '_wb_bust=', $url ) ) {
			// already there, just update the value
			$url = preg_replace(
				'/_wb_bust=[^&?]+/',
				'_wb_bust=' . mt_rand(),
				$url
			);
		} else {
			$separator = false == strpos( $url, '?' ) ? '?' : '&';
			$url       .= $separator . '_wb_bust=' . mt_rand();
		}

		return $url;
	}

	/**
	 * Removes any _wb_bust query var that may have been added
	 * to a URL
	 *
	 * @param String $url
	 *
	 * @return string
	 */
	public static function removeCacheBust( $url ) {

		$uri = new Uri( $url );
		$uri->delVar( '_wb_bust' );

		return $uri->toString();
	}

	/**
	 * Append a query string (param=1&param=2...) to an existing URL. Should make minimal modification
	 * to the oroginal URL, including handling existing query string, fragment, path, protocol and domain if any.
	 *
	 * @param string $rawUrl
	 * @param string $append
	 *
	 * @return string
	 */
	public static function appendQuery( $rawUrl, $append ) {

		// fix for protocol-relative URLs
		$isProtocolRelative = wbStartsWith( $rawUrl, '//' );

		$uri   = new Uri( $rawUrl );
		$query = $uri->getQuery();
		$uri->setQuery( wbJoin( '&', $query, $append ) );
		$url = ( $isProtocolRelative ? '//' : '' ) . $uri->toString();

		return $url;
	}

}
