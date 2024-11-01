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

class WeeblrampModelElement_Embedtags extends WeeblrampClass_Base {

	private $_scripts = array();
	private $styles   = '';

	public function getData( $currentData ) {

		$processedContent = $this->embed( $currentData );

		// return processed content and possibly required AMP scripts
		$result = array(
			'data'    => $processedContent,
			'scripts' => $this->_scripts,
			'styles'  => $this->styles
		);

		return $result;
	}

	/**
	 * Search an HTML content for tags
	 * to specific content that have AMP version,
	 * such as:
	 * - twitter tweets
	 * - instagrams images
	 * - vine videos
	 * - youtube videos
	 *
	 * Tags format:
	 *
	 * [wbamp_embed type="_type_" attr*=""]
	 *
	 * where
	 * type = twitter | instagram | vine | youtube | carousel
	 *
	 * + various attributes, dependent on the item being processed
	 *
	 * quotes (") are required around each value
	 *
	 * @param $rawContent
	 *
	 * @return mixed
	 */
	private function embed( $rawContent ) {

		if ( $this->userConfig->isFalsy( 'embed_user_tags' ) ) {
			return $rawContent;
		}

		if ( empty( $rawContent ) ) {
			return $rawContent;
		}

		$isArray    = is_array( $rawContent );
		$rawContent = $isArray ? $rawContent : (array) $rawContent;

		$processedContent = array();
		foreach ( $rawContent as $key => $contentRecord ) {
			$content = is_array( $contentRecord ) ? $contentRecord['content'] : $contentRecord;

			$regex   = '#\[\s*wbamp\-embed([^\]]*)\s*\]#ium';
			$content = preg_replace_callback( $regex, array( $this, '_processEmbededTag' ), $content );

			if ( is_array( $contentRecord ) ) {
				$contentRecord['content'] = $content;
			} else {
				$contentRecord = $content;
			}

			$processedContent[ $key ] = $contentRecord;
		}

		return $isArray ? $processedContent : $processedContent[0];
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
			$type       = empty( $attributes['type'] ) ? '' : $attributes['type'];

			// proceed to extract tag and replace it
			return $this->_embedTag( $type, $attributes );
		}

		return $match[0];
	}

	/**
	 * Process a user content tag and replace it with
	 * its AMP counterpart.
	 *
	 * @param $type
	 * @param $attributes
	 *
	 * @return string
	 */
	private function _embedTag( $type, $attributes ) {

		$displayData = array(
			'user_config'   => $this->userConfig,
			'system_config' => $this->systemConfig,
			'amp_config'    => $this->ampConfig,
			'data'          => $attributes
		);

		return WeeblrampHelper_Tags::buildTag( $type, $displayData );
	}
}
