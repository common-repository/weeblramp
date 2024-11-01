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

class WeeblrampHelper_Tags {

	static $systemConfig    = null;
	static $assetsCollector = null;

	/**
	 * Builds the HTML for an AMP tag, by rendering the appropriate layout
	 * Also adds amp scripts and style sheets as needed.
	 *
	 * @param string $type
	 * @param        $displayData
	 *
	 * @return string
	 */
	public static function buildTag( $type, $displayData ) {

		if ( is_null( self::$systemConfig ) ) {
			self::$systemConfig = WeeblrampFactory::getThe( 'weeblramp.config.system' );
		}
		if ( is_null( self::$assetsCollector ) ) {
			self::$assetsCollector = WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' );
		}

		$tagsDef = self::$systemConfig->get( 'embed_tags' );
		if ( empty( $tagsDef[ $type ] ) ) {
			return '';
		}

		// tag exists, render it
		$tag = WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.tags.' . $type, $displayData, WEEBLRAMP_LAYOUTS_PATH );

		// finally add script to execute the tag
		self::$assetsCollector->addScripts(
			array(
				$tagsDef[ $type ]['amp_tag'] => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, $tagsDef[ $type ]['script'], WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
			)
		);

		// optional style
		if ( ! empty( $tagsDef[ $type ]['style'] ) ) {
			self::$assetsCollector->addStyle( $tagsDef[ $type ]['style'] );
		}

		return $tag;
	}

	public static function getYoutubeUrlData( $match ) {

		$url = $match[1];
		if ( strpos( $url, '&' ) !== false ) {
			list( $url ) = explode( '&', $url );
		}
		$displayData = array(
			'data' => array(
				'type'    => 'youtube',
				'videoid' => empty( $url ) ? '' : $url
			)
		);

		return $displayData;
	}

	public static function getDailymotionUrlData( $match ) {

		$displayData = array(
			'data' => array(
				'type'    => 'youtube',
				'videoid' => empty( $match[1] ) ? 0 : $match[1]
			)
		);

		return $displayData;
	}

	public static function getTwitterUrlData( $match ) {

		$displayData = array(
			'data' => array(
				'type'    => 'twitter',
				'tweetid' => empty( $match[5] ) ? 0 : $match[5]
			)
		);

		return $displayData;
	}

	public static function getInstagramUrlData( $match ) {

		$displayData = array(
			'data' => array(
				'type'      => 'instagram',
				'shortcode' => empty( $match[1] ) ? '' : $match[1]
			)
		);

		return $displayData;
	}

	public static function getFacebookUrlData( $match ) {

		$displayData = array(
			'data' => array(
				'type'    => 'facebook',
				'user'    => empty( $match[1] ) ? 0 : $match[1],
				'subtype' => empty( $match[2] ) ? 0 : $match[2],
				'id'      => empty( $match[3] ) ? 0 : $match[3]
			)
		);

		return $displayData;
	}
}
