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

class WeeblrampModelElement_Ad extends WeeblrampClass_Base {

	private $scripts;
	private $styles;

	/**
	 * Finds if any ads is to be displayed based on shortcodes.
	 *
	 * @return mixed|string
	 */
	public function getData( $rawContent ) {

		if ( WeeblrampHelper_Ads::$disabled ) {
			// ads may have been disabled using the wbamp-no-ads shortcode
			return array(
				'data'    => $rawContent,
				'scripts' => array(),
				'styles'  => array()
			);
		}

		$this->scripts = array();

		// search for embed tags in content
		$isArray    = is_array( $rawContent );
		$rawContent = $isArray ? $rawContent : (array) $rawContent;

		$processedContent = array();
		// actual string content might be in $contentRecord or in $contentRecord['content']
		foreach ( $rawContent as $key => $contentRecord ) {
			$content = wbArrayGet( $contentRecord, 'content', $contentRecord );
			if ( ! empty( $content ) ) {
				$regex   = '#\[\s*wbamp\-embed([^\]]*)\s*\]#ium';
				$content = preg_replace_callback( $regex, array( $this, '_processEmbededTag' ), $content );
			}

			if ( is_array( $contentRecord ) ) {
				$contentRecord['content'] = $content;
			} else {
				$contentRecord = $content;
			}

			$processedContent[ $key ] = $contentRecord;
		}

		// return processed content and possibly required AMP scripts
		$result = array(
			'data'    => $isArray ? $processedContent : $processedContent[0],
			'scripts' => $this->scripts,
			'styles'  => $this->styles
		);

		return $result;
	}

	/**
	 * Preg replace callback, identify tags to replace
	 * them with the AMP version
	 *
	 * @param $match
	 *
	 * @return string
	 */
	private function _processEmbededTag( $match ) {

		// detect type we can handle
		if ( ! empty( $match[1] ) ) {
			$attributes = WblSystem_Strings::parseAttributes( $match[1] );
			$adType     = empty( $attributes['type'] ) ? '' : $attributes['type'];
			if ( 'ad' == $adType ) {
				// proceed to extract tag and replace it
				unset( $attributes['type'] );

				return $this->_embedTag( $attributes );
			}
		}

		return $match[0];
	}

	/**
	 * Builds an amp-ad tag, from a shortcode in content
	 * in one of 2 forms: with or without attributes
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	private function _embedTag( $attributes ) {

		$tag = '';

		if ( empty( $attributes ) ) {
			// no attributes, we use the default ad type and params set by user
			$network = strtolower( $this->userConfig->get( 'ads_network' ) );
			if ( WeeblrampConfig_User::ADS_NO_ADS != $network ) {
				$tag = WblMvcLayout_Helper::render(
					'weeblramp.frontend.amp.ads-networks.' . $network,
					array(
						'user_config'       => $this->userConfig,
						'system_config'     => $this->systemConfig,
						'amp_config'        => $this->ampConfig,
						'ad_id'             => md5( mt_rand() . $network ),
						'assets_collector'  => WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' ),
						'content_protector' => WeeblrampFactory::getThe( 'weeblramp.content.protector' )
					),
					WEEBLRAMP_LAYOUTS_PATH
				);
			}
		} else {
			// rename the ad-type to type, could not use it earlier
			// as it conflicts with our wbamp-embed syntax
			if ( ! empty( $attributes['ad-type'] ) ) {
				$attributes['type'] = $attributes['ad-type'];
				unset( $attributes['ad-type'] );
			}

			// safety net
			$attributes['width']  = wbArrayGet( $attributes, 'width', $this->userConfig->get( 'ad_width' ) );
			$attributes['height'] = wbArrayGet( $attributes, 'height', $this->userConfig->get( 'ad_height' ) );

			// we have some attributes, use them directly to build the amp-ad tag
			$tag = WblMvcLayout_Helper::render(
				'weeblramp.frontend.amp.ads-networks.wbamp_ad_tag',
				array( 'attributes' => $attributes ),
				WEEBLRAMP_LAYOUTS_PATH
			);
		}

		if ( ! empty( $tag ) && empty( $this->scripts ) ) {
			// finally add script to execute the tag
			$this->scripts =
				array(
					// Comment out next line for a temp fix for issue in Google AMP validator https://github.com/ampproject/amphtml/issues/3802
					'amp-ad' => sprintf(
						WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN,
						'ad',
						WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION
					)
				);
			$this->styles  = 'ads';
		}

		return $tag;
	}
}
