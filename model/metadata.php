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

class WeeblrampModel_Metadata extends WeeblrampClass_Model {

	/**
	 * Stores data collected about the page
	 *
	 * @var array
	 */
	private $pageData = array();

	/**
	 * Adds used objects as properties
	 *
	 * WeeblrampModel_Renderer constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );
	}

	/**
	 * Get current page meta data, optionally filtering
	 * the output to only get some parts
	 *
	 * @param array $pageData Array of information about the page being rendered
	 *
	 * @return array
	 */
	public function getData( $pageData = array() ) {

		$this->pageData = $pageData;

		$data = parent::getData();

		return $data;
	}

	/**
	 * Collect meta data in an array, filter it to allow extension
	 * by others
	 *
	 * @return $this
	 */
	protected function loadData() {

		$this->loadMeta()
		     ->loadOgp()
		     ->loadTCards()
		     ->loadFbAppId()
		     ->loadTweetVia();

		// filtering for plugins
		/**
		 * Filter the page meta data
		 *
		 * This is an array of page meta data. Includes: meta (title, description,...), Open Graph, Twitter Cards, Facebook App id, Tweet Via, Publisher Id
		 *
		 * @api
		 *
		 * @param array $data The full array of meta data collected/built
		 *
		 * @return array
		 * @since   1.0.0
		 *
		 * @package weeblrAMP\filter\seo
		 * @var weeblramp_get_metadata
		 */
		$this->__data = apply_filters( 'weeblramp_get_metadata', $this->__data, $this->pageData );

		// store
		$this->dataLoaded = true;

		return $this;
	}

	/**
	 * Compute "main" meta data for the current page: title, description
	 *
	 * @return $this
	 */
	private function loadMeta() {

		global $wp_query;

		$this->__data['title']       = WblWordpress_Compat::wp_get_document_title();
		$this->__data['description'] = '';
		if ( is_search() ) {
			$this->__data['robots'] = 'noindex,follow';
		} else {
			// default value for robots meta
			$this->__data['robots'] = 'max-snippet:-1, max-image-preview:large, max-video-preview:-1';
		}

		// object describing the current request
		$object = $wp_query->get_queried_object();
		if ( empty( $object ) ) {
			$object = $wp_query;
		}

		// fetch title based on object type
		$class = get_class( $object );
		switch ( $class ) {
			case 'WP_Post':
				$this->__data['description'] = strip_tags(
					WeeblrampHelper_Content::getPostExcerpt( $object ) );
				break;
			case 'WP_Term':
				$this->__data['description'] = $object->description;
				break;
			case 'WP_Query':
				$this->__data['description'] = strip_tags( get_the_archive_description() );
				break;
		}

		$this->__data['description'] = wbAbridge(
			$this->__data['description'],
			160,
			157
		);

		return $this;
	}

	/**
	 * Compute OpenGraph meta data for the page
	 *
	 * @return $this
	 */
	private function loadOgp() {

		$ogp = array(
			'locale'          => str_replace( '-', '_', get_bloginfo( 'language' ) ),
			'title'           => $this->__data['title'],
			'description'     => $this->__data['description'],
			'type'            => 'article',
			'url'             => $this->pageData['canonical'],
			'site_name'       => $this->pageData['site_name'],
			'facebook_app_id' => $this->userConfig->get( 'facebook_app_id' )
		);

		if ( ! empty( $this->pageData['image'] ) ) {
			$ogp['image']        = $this->pageData['image']['url'];
			$ogp['image_width']  = $this->pageData['image']['width'];
			$ogp['image_height'] = $this->pageData['image']['height'];
		}

		/**
		 * Filter the page OpenGraph data, as an associative array of values.
		 *
		 * @api
		 *
		 * @param array $ogp An array of OGP data
		 * @param array $pageData Full array of page data collected up to now.
		 *
		 * @return array
		 * @package weeblrAMP\filter\seo
		 * @var weeblramp_get_ogp
		 * @since   1.0.0
		 *
		 */
		$this->__data['ogp'] = apply_filters( 'weeblramp_get_ogp', $ogp, $this->pageData );

		// add secondary image field, if HTTPS
		if ( is_ssl() ) {
			$this->__data['ogp']['image_secure_url'] = wbArrayGet( $this->__data, array( 'ogp', 'image' ) );
		}

		return $this;
	}

	/**
	 * Compute Twitter Cards tags for the page
	 *
	 * @return $this
	 */
	private function loadTCards() {

		$tcards = array(
			'card_type'    => $this->userConfig->get( 'tcards_type' ),
			'site_account' => $this->userConfig->get( 'twitter_account' ),
			'creator'      => $this->userConfig->get( 'twitter_account' ),
			'title'        => $this->__data['title'],
			'description'  => $this->__data['description'],
			'url'          => $this->pageData['canonical'],
		);

		if ( ! empty( $this->pageData['image'] ) ) {
			$tcards['image'] = $this->pageData['image']['url'];
		}

		/**
		 * Filter the page Twitter Cards data, as an associative array of values.
		 *
		 * @api
		 *
		 * @param array $tcards An array of Twitter Cards data
		 * @param array $pageData Full array of page data collected up to now.
		 *
		 * @return array
		 * @package weeblrAMP\filter\seo
		 * @var weeblramp_get_tcards
		 * @since   1.0.0
		 *
		 */
		$this->__data['tcards'] = apply_filters( 'weeblramp_get_tcards', $tcards, $this->pageData );

		return $this;
	}

	/**
	 * Fetch Facebook App id from configuration
	 *
	 * @return $this
	 */
	private function loadFbAppId() {

		$this->__data['facebook_app_id'] = $this->userConfig->get( 'facebook_app_id' );

		return $this;
	}

	/**
	 * Load tweet via account from configuration
	 *
	 * @return $this
	 */
	private function loadTweetVia() {

		$this->__data['tweet_via'] = $this->userConfig->get( 'twitter_account' );

		return $this;
	}
}
