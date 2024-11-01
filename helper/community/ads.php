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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

class WeeblrampHelper_Ads {

	/**
	 * Flag to disable all ads on current page
	 *
	 * @var bool
	 */
	public static $disabled = false;

	/**
	 * @var string Holds the current piece of content type for auto insert stuff
	 */
	private static $contentType = '';

	/**
	 * Read params and run filter to find out if ads should be
	 * displayed on current page
	 *
	 * @param string  $requestType
	 * @param WP_POST $post
	 *
	 * @return bool
	 */
	public static function shouldShow( $requestType, $post = null ) {

		$shouldShow = ! self::$disabled && WeeblrampFactory::getThe( 'weeblramp.config.user' )->get( 'ads_network' );

		/**
		 * Filter whether to show ads on current AMP request.
		 *
		 * Note: user can also enable/disable ads showing by using the [wbamp-no-ads] shortcode anywhere in a post content.
		 *
		 * @package weeblrAMP\filter\ads
		 * @var weeblramp_show_ads
		 * @since   1.0.0
		 *
		 * @param bool    $shouldShow If true, ads are displayed on the page
		 * @param string  $requestType Request type descriptor: page, post, home, search,...
		 * @param WP_Post $post The global post object for the request
		 *
		 * @return bool
		 */
		return apply_filters( 'weeblramp_show_ads', $shouldShow, $requestType, $post );
	}

	/**
	 * Output the HTML for ads, checking first if they should be displayed
	 * on the current page
	 *
	 * @param string $position
	 * @param array  $data
	 *
	 * @return string
	 */
	public static function get( $position, $data ) {

		$output = '';
		if ( self::$disabled ) {
			return $output;
		}

		$userConfig = WeeblrampFactory::getThe( 'weeblramp.config.user' );
		if (
			$position == $userConfig->get( 'ads_location' )
			&&
			self::shouldShow(
				wbArrayGet( $data, 'request_type' ),
				wbArrayGet( $data, 'post' )
			)
		) {
			$output = WblMvcLayout_Helper::render(
				'weeblramp.frontend.amp.ads-networks.' . $userConfig->get( 'ads_network' ),
				$data,
				WEEBLRAMP_LAYOUTS_PATH
			);
		}

		return $output;
	}

	/**
	 * Automatically insert ads in content passed, based on user set rules
	 * using number of paragraphs.
	 *
	 * @param array $pageData Current content of the page.
	 *
	 * @return array
	 */
	public static function autoInsert( $pageData ) {

		$rawContent = wbArrayGet( $pageData, 'main_content' );

		return $rawContent;
	}
}
