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
 */
class WeeblrampHelper_Error {

	public static $error = '';

	public static function adminInitError() {

		WblWordpress_Helper::adminError( self::$error, WEEBLRAMP_PLUGIN_NAME );
	}

	/**
	 * Build an amp-img string for an image to be displayed on the 404 error page
	 *
	 * @TODO:
	 *
	 * - add a backend setting: Display image on 404 error pages?
	 * - add a system config value for the CDN root, or better for the URL template to use
	 *
	 * @return string
	 */
	public static function getErrorPageImage() {

		$urlTemplate = "https://cdn.weeblr.net/dist/img/mix/wh/600-400/wb-%d.jpg";

		return sprintf(
			'<amp-img src="' . $urlTemplate . '"  width="600" height="400" layout="responsive"></amp-img>',
			mt_rand( 1, 32 )
		);
	}
}
