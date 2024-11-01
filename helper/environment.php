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
defined( 'WEEBLRAMP_EXEC' ) || die;

class WeeblrampHelper_Environment {

	/**
	 * Runs checks and take actions based on server configuration
	 * or other scripts or conditions that may be interfering
	 * with the AMP page
	 *
	 * Should be called only within the context of rendering
	 * an actuall AMP page, not on a regular HTML page
	 */
	public static function handleSpecificEnvironment( $view ) {

		self::disableNewRelic( $view );
		self::disablePageSpeed( $view );
	}

	/**
	 * Disable NewRelic APM agent, which otherwise
	 * injects <script> tag in page
	 *
	 * @return void
	 */
	private static function disableNewRelic( $view ) {

		/**
		 * Filter whether to disable NewRelic extension when rendering an AMP page. NewRelic can cause AMP pages to invalidate.
		 *
		 * @api
		 * @package weeblrAMP\filter\system
		 * @var weeblramp_disable_newrelic
		 * @since   1.0.0
		 *
		 * @param bool $disable If true, Newrelic will be disabled
		 *
		 * @return bool
		 */
		if (
			apply_filters( 'weeblramp_disable_newrelic', true )
			&&
			extension_loaded( 'newrelic' )
			&&
			function_exists( 'newrelic_disable_autorun' )
		) {
			WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Disabled NewRelic' );
			newrelic_disable_autorun();
		}
	}

	/**
	 * Send header to disable PageSpeed which can
	 * insert javascript on the fly, at least
	 * prior to June 2016. Google announced
	 * PS will not alter AMP page in an upcomnig release
	 */
	private static function disablePageSpeed( $view ) {

		/**
		 * Filter whether to disable PageSpeed when rendering an AMP page. PageSpeed can cause AMP pages to invalidate. PageSpeed is disabled by sending out appropriate headers
		 *
		 * @api
		 * @package weeblrAMP\filter\system
		 * @var weeblramp_disable_pagespeed
		 * @since   1.0.0
		 *
		 * @param bool $disable If true, PageSpeed will be disabled
		 *
		 * @return bool
		 */
		if ( apply_filters( 'weeblramp_disable_pagespeed', true ) ) {
			WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Disabled PageSpeed' );
			$view->setHeader(
				array(
					'PageSpeed' => 'off'
				)
			);
		}
	}
}
