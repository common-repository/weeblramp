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

class WeeblrampModel_Usersetdata extends WeeblrampClass_Model {

	/**
	 * The list of user tags we handle
	 * @var array
	 */
	private $userSetTags = array(
		'doc_type',
		'doc_name',
		'image',
		'author',
		'publisher',
		'date_published',
		'date_modified'
	);

	/**
	 * @var array Collects user set data we found in the content.
	 */
	private $userSetData = array();

	/**
	 * Extract and store meta data set by user using [wbamp-*]tags in the content.
	 *
	 * @param array $rawContent An array of raw content descriptors.
	 *
	 * @return mixed
	 */
	public function extractUserSetData( $rawContent ) {

		$this->userSetData = array();

		// process (and possibly modify)
		$processedContent = array_map( array( $this, 'getUserSetData' ), $rawContent );

		$result = array(
			'user_set_data'     => $this->userSetData,
			'processed_content' => $processedContent
		);

		return $result;
	}

	/**
	 * Extract and store meta data set by user from a single raw content descriptor.
	 *
	 * @param array $post
	 */
	private function getUserSetData( $post ) {

		$regex           = '#\[\s*wbamp\-meta([^\]]*)\s*\]#mu';
		$post['content'] = preg_replace_callback( $regex, array( $this, 'processUserSetData' ), $post['content'] );

		// also update post object
		if ( ! empty( $post['post'] ) && is_object( $post['post'] ) ) {
			$post['post']->post_content = $post['content'];
		}

		return $post;
	}

	/**
	 * Regular expression callback to extract user set data.
	 *
	 * @param string $match
	 *
	 * @return string
	 */
	private function processUserSetData( $match ) {

		// detect type we can handle
		if ( ! empty( $match[1] ) ) {
			$attributes = WblSystem_Strings::parseAttributes( $match[1] );
			$type       = empty( $attributes['name'] ) ? '' : $attributes['name'];
			if ( in_array( $type, $this->userSetTags ) ) {
				$this->userSetData[ $type ] = $attributes;
			}

			return '';
		}

		return $match[0];
	}
}
