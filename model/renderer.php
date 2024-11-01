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

/**
 * Gather all data for rendering a full AMP page.
 */
class WeeblrampModel_Renderer extends WeeblrampClass_Model {

	const AMP_SCRIPTS_VERSION = 0.1;
	const AMP_SCRIPTS_VERSION_MUSTACHE = 0.2;
	const AMP_SCRIPTS_PATTERN = 'https://cdn.ampproject.org/v0/amp-%s-%s.js';

	private $_ampProcessor    = null;
	private $_taxonomyManager = null;
	private $_extractedImage  = null;
	private $_assetsCollector = null;

	/**
	 * Adds used objects as properties
	 *
	 * WeeblrampModel_Renderer constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );

		$this->_taxonomyManager = WeeblrampFactory::getA( 'WeeblrampClass_Taxonomy' );
		$this->_ampProcessor    = WeeblrampFactory::getA( 'WeeblrampModel_Ampprocessor' );
		$this->_assetsCollector = WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' );
	}

	/**
	 * Builds an array of all data required to display the
	 * AMP page. Suitable for use with WblMvcLayout_Base.
	 *
	 * @return $this
	 */
	protected function loadData() {

		$data = array();

		// store config(s)
		$data['user_config']      = $this->userConfig;
		$data['customize_config'] = $this->customizeConfig;
		$data['system_config']    = $this->systemConfig;
		$data['amp_config']       = $this->ampConfig;
		$data['router']           = $this->router;
		$data['assets_collector'] = $this->_assetsCollector;
		$data['taxonomy_manager'] = $this->_taxonomyManager;

		// wp-rendered content needs to be reprocessed
		$data['amp_processor']      = $this->_ampProcessor;  // amp converter
		$data['amp_form_processor'] = WeeblrampFactory::getA(  // amp converter, with forms
			'WeeblrampModel_Ampprocessor',
			array(
				'amp_config' => WeeblrampFactory::getA( 'weeblramp.config.ampform' )
			)
		);

		$data['content_processor'] = WeeblrampFactory::getA( 'WeeblrampModel_Content' ); // content processor, adjust content to AMP needs
		$data['content_protector'] = WeeblrampFactory::getThe( 'weeblramp.content.protector' ); // content protector, allows marking some content, to bypass AMP conversion and validation (forms for instance)

		// which template to use for the content?
		$data['request_type'] = $this->getContentType();

		// page data
		$data['amp_url']   = WblWordpress_Helper::getCurrentRequestUrl( true );
		$data['canonical'] = $this->router->getCanonicalFromAmpUrl( $data['amp_url'] );
		$data['amp_path']  = wbLTrim( $data['amp_url'], WblWordpress_Helper::getSiteUrl() );

		// custom content
		$data['custom_style'] = $this->customizeConfig->get( 'custom_css' );
		$data['custom_links'] = $this->customizeConfig->get( 'custom_links' );

		// find out which color theme to use
		$installedThemes = $this->userConfig->optionsCallback_global_theme( null );
		$configuredTheme = $this->userConfig->get( 'global_theme' );
		if ( ! array_key_exists( $configuredTheme, $installedThemes ) ) {
			// revert to build in default theme
			$this->userConfig->reset( 'global_theme' );
		}
		$data['theme'] = $this->userConfig->get( 'global_theme' );

		// collect navigation menu data, for rendering
		if ( WeeblrampConfig_Customize::MENU_TYPE_NONE != $this->customizeConfig->get( 'menu_type' ) ) {
			$data['navigation_menu'] = $this->getElementData( 'navigation', $data, '' );

			// right or left
			$data['navigation_menu_side']        = $this->customizeConfig->get( 'menu_side' );
			$data['navigation_menu_button_text'] = $this->customizeConfig->get( 'navigation_menu_button_text' );
		} else {
			$data['navigation_menu'] = array();
		}

		// collect main content
		$rawContent = $this->getContent( $data );

		// extract content from tags set by user in original content (and remove them)
		$userSetDataRecord     = WblFactory::getA( 'WeeblrampModel_Usersetdata' )->extractUserSetData( $rawContent );
		$rawContent            = $userSetDataRecord['processed_content'];
		$data['user_set_data'] = $userSetDataRecord['user_set_data'];

		// store raw main content
		$data['main_content'] = $rawContent;

		// figure out publication dates and information
		// use date of post, if there's only one
		if ( 1 == count( $data['main_content'] ) ) {
			$data['date_published'] = WblSystem_Date::toDateTimeObject(
				$data['main_content'][0]['post']->post_date,
				WblWordpress_Helper::getTimezone()
			);
			$data['date_modified']  = WblSystem_Date::toDateTimeObject(
				$data['main_content'][0]['post']->post_modified,
				WblWordpress_Helper::getTimezone()
			);

			$data['author'] = get_userdata( $data['main_content'][0]['post']->post_author );
		}

		// collect social buttons data, for rendering
		if ( WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_NONE != $this->customizeConfig->get( 'social_buttons_location' ) ) {
			$data['social_buttons']      = $this->getElementData(
				'socialbuttons', $data, array(
					               'types' => array(),
					               'theme' => 'colors',
					               'style' => 'rounded'
				               )
			);
			$data['social_buttons_type'] = $this->customizeConfig->get( 'social_buttons_type' );
		}

		// ads, insert "fake" shortcodes to automatically insert ads after n paragraphs
		$data['main_content'] = WeeblrampHelper_Ads::autoInsert( $data );

		// ads, make sure amp-ad script is loaded: main_content is modified
		$data['main_content'] = $this->getElementData( 'ad', $data['main_content'], $data['main_content'] );

		// user embeds (FB, twitter,...) main_content is modified
		$data['main_content'] = $this->getElementData( 'embedtags', $data['main_content'], $data['main_content'] );

		$data['site_name']                 = $this->userConfig->get( 'site_name' );
		$data['site_link']                 = WblWordpress_Helper::getSiteUrl();
		$data['site_image']                = $this->userConfig->get( 'site_image' );
		$data['site_image_size']           = array();
		$data['site_image_size']['width']  = 0;
		$data['site_image_size']['height'] = 0;
		$data['site_image_size']           = WeeblrampHelper_Media::findImageSizeIfMissing( $data['site_image'], $data['site_image_size'] );
		$data['site_tag_line']             = $this->userConfig->get( 'site_tag_line' );

		$data['publisher'] = array(
			'@type' => 'Organization',
			'name'  => $this->userConfig->get( 'publisher_name' ),
			'url'   => $this->userConfig->get( 'publisher_url', wbArrayGet( $data, 'site_link' ) )
		);

		// footer, a custom HTML content created by user
		$data['footer'] = $this->customizeConfig->isTruthy( 'show_footer' ) ? $this->customizeConfig->get( 'footertext' ) : '';

		// Link to main site
		// @TODO: move to method
		$linkToMainSiteUrl = $this->customizeConfig->get( 'link_to_main_site_link_url' );
		$linkToMainSiteUrl = str_replace(
			'[weeblramp_current_page_non_amp]',
			$data['canonical'],
			$linkToMainSiteUrl
		);
		$linkToMainSiteUrl = empty( $linkToMainSiteUrl ) ? $data['canonical'] : $linkToMainSiteUrl;
		if ( WeeblrampConfig_Customize::LINK_TO_SITE_NOTIFICATION == $this->customizeConfig->get( 'show_link_to_main_site' ) && $this->customizeConfig->isTruthy( 'link_to_main_site_text' ) ) {
			// display as an amp-notification

			$data['link_to_main_site'] = array(
				'text'         => $this->customizeConfig->get( 'link_to_main_site_text' )
				                  . '<a href="' . $linkToMainSiteUrl . '"><button>' . $this->customizeConfig->get( 'link_to_main_site_link_text' ) . '</button></a>'
				,
				'button'       => __( 'Do not ask again', 'weeblramp' ),
				'do_dismiss'   => true,
				'show_href'    => '',
				'dismiss_href' => '',
				'theme'        => $this->customizeConfig->get( 'link_to_main_site_theme' )
			);

			$data['link_to_main_site']['id'] =
				// NB: the wbamp- in front of the id is required, as otherwise the computed sha1 may start with a number
				// which would make up an invalid HTMl id
				'wbamp-'
				. sha1(
					'amp-user-notification'
					. $this->customizeConfig->get( 'link_to_main_site_link_text' )
					. $data['link_to_main_site']['button']
				);

			$this->_assetsCollector
				->addScripts(
					array(
						'amp-user-notification' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'user-notification', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION ),
					)
				)
				->addStyle( 'notification' );
		} else if ( WeeblrampConfig_Customize::LINK_TO_SITE_NONE != $this->customizeConfig->get( 'show_link_to_main_site' ) && $this->customizeConfig->isTruthy( 'link_to_main_site_text' ) ) {
			$data['link_to_main_site'] = array(
				'text'     => $this->customizeConfig->get( 'link_to_main_site_text' ),
				'link'     => $this->customizeConfig->get( 'link_to_main_site_link_text' ),
				'link_url' => $linkToMainSiteUrl,
				'theme'    => $this->customizeConfig->get( 'link_to_main_site_theme' )
			);

			$this->_assetsCollector->addStyle( 'link_to_main' );
		}

		// debug module CSS
		if ( WeeblrampHelper_Route::shouldShowDebugModule() ) {
			$this->_assetsCollector->addStyle( 'link_to_main' );
		}

		// pagination
		$data['previous_post'] = get_previous_post();
		if ( ! empty( $data['previous_post'] )
		     &&
		     (
			     $this->_taxonomyManager->shouldAmplifyItem( $data['previous_post'] )
			     ||
			     $this->router->isStandaloneMode()
		     )
		) {
			$data['previous_post_link'] = $this->router->getAmpPagePermalink( $data['previous_post'] );
		}
		$data['next_post'] = get_next_post();
		if ( ! empty( $data['next_post'] )
		     &&
		     (
			     $this->_taxonomyManager->shouldAmplifyItem( $data['next_post'] )
			     ||
			     $this->router->isStandaloneMode()
		     )
		) {
			$data['next_post_link'] = $this->router->getAmpPagePermalink( $data['next_post'] );
		}

		// user notification
		// @TODO: move to method
		if ( $this->customizeConfig->isTruthy( 'notification_enabled' ) && $this->customizeConfig->isTruthy( 'notification_text' ) ) {
			$data['user-notification'] = array(
				'text'         => $this->customizeConfig->get( 'notification_text' ),
				'button'       => $this->customizeConfig->get( 'notification_button' ),
				// NB: the wbamp- in front of the id is required, as otherwise the computed sha1 may start with a number
				// which would make up an invalid HTMl id
				'id'           => 'wbamp-'
				                  . sha1(
					                  'amp-user-notification'
					                  . $this->customizeConfig->get( 'notification_text' )
					                  . $this->customizeConfig->get( 'notification_button' )
				                  ),
				'do_dismiss'   => true,
				'show_href'    => '',
				'dismiss_href' => '',
				'theme'        => $this->customizeConfig->get( 'notification_theme' )
			);
			$this->_assetsCollector
				->addScripts(
					array(
						'amp-user-notification' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'user-notification', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION ),
					)
				)
				->addStyle( 'notification' );
		}

		// insert analytics AMP element
		$data['analytics_data'] = $this->getElementData( 'analytics', $data );

		// find the page image
		$data['image'] = $this->findPageImage( $data );

		// find the site favicon
		$data['site_favicons'] = $this->getRenderedFavicons();

		// stuff that needs to be put at the page bottom, before closing body tag
		// we don't have any (yet) but others may have
		/**
		 * Filter content appended to the page bottom.
		 *
		 * Raw content output after the page main content, accepts raw HTML.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_get_page_bottom_content
		 * @since   1.0.0
		 *
		 * @param string $content Content to output
		 *
		 * @return string
		 */
		$data['page_bottom'] = apply_filters( 'weeblramp_get_page_bottom_content', '' );

		// default meta data set.
		$data['metadata'] = WeeblrampFactory::getA( 'WeeblrampModel_Metadata' )->getData( $data );

		// Sharing URLs and title. Use metadata, so had to wait until here to get them
		$data['share_url']   = $data['amp_url'];
		$data['share_title'] = $data['metadata']['title'];

		// let plugins build json-ld data
		$data['json-ld'] = $this->getJsonldData( $data );

		// collect data from other plugins
		/**
		 * Filter (almost the) data used to render the current AMP page.
		 *
		 * This is an array of (almost) all data before the page rendering process starts. Only Structured data are missing, as they are built using some of the filtered data.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_get_request_data
		 * @since   1.0.0
		 *
		 * @param array $data The full array of data collected, before being passed to rendering
		 *
		 * @return array
		 */
		$data = apply_filters( 'weeblramp_get_request_data', $data );

		// structured data, may re-use some data in json-ld and requires all other data to be collected
		$data['structured_data'] = WeeblrampFactory::getA( 'WeeblrampModel_Structureddata' )->getData( $data );

		// override some for error page
		if ( $this->router->isErrorPage() ) {
			$data['metadata']['title']       = __( 'The page you requested was not found on our site!', 'weeblramp' );
			$data['metadata']['description'] = __( 'Page not found', 'weeblramp' );
			$data['metadata']['keywords']    = '';
			$data['error_title']             = __( 'Oops! that page cannot be found.', 'weeblramp' );
			$data['error_body']              = __( 'We do not have any page at the address you requested. Please use the menu at the top to navigate to other pages of the site.', 'weeblramp' );
			$data['error_footer']            = sprintf( __( 'You requested %s', 'weeblramp' ), WblWordpress_Helper::getCurrentRequestUrl() );
			$data['error_image']             = WeeblrampHelper_Error::getErrorPageImage();
		}

		// add request type specific CSS, if any
		if ( $this->router->isErrorPage() ) {
			$this->_assetsCollector->addStyle( 'error' );
		} else {
			$this->_assetsCollector->addStyle( 'content' );
		}

		/**
		 * Filter data used to render the current AMP page.
		 *
		 * This is an array of all data before the page rendering process starts.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_final_request_data
		 * @since   1.0.0
		 *
		 * @param array $data The full array of data collected, before being passed to rendering
		 *
		 * @return array
		 */
		$this->__data = apply_filters( 'weeblramp_final_request_data', $data );
		WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Renderer model, post filter: ' . print_r( $data, true ) );

		return $this;
	}

	/**
	 * Decides which content template to use, based on the request
	 *
	 * NB: this can be overriden by integrations, meaning that integrations
	 * have to pay attention to
	 *
	 * @return bool
	 */
	protected function getContentType() {

		global $wp_query;

		switch ( true ) {
			case (
				$this->userConfig->isTruthy( 'amplify_home' )
				&&
				$this->router->isAmpHome()
			):
				$type = 'home';
				break;
			case $wp_query->is_404:
				$type = 'error';
				break;
			case $wp_query->is_author:
				$type = 'archive';
				break;
			case $wp_query->is_search:
				$type = 'search';
				break;
			case $wp_query->is_category:
				$type = 'archive';
				break;
			case $wp_query->is_archive:
				$type = 'archive';
				break;
			case $wp_query->is_post_type_archive:
				$type = 'archive';
				break;
			case $wp_query->is_tag:
				$type = 'archive';
				break;
			case $wp_query->is_page:
				$type = 'page';
				break;
			case $wp_query->is_single:
				$type = 'single';
				break;
			default:
				$type = 'archive';
				break;
		}

		/**
		 * Filter the name of the current request type
		 *
		 * Built-in types are:
		 * home
		 * error
		 * archive
		 * search
		 * archive
		 * page
		 * single
		 *
		 * One can add more request types (see WooCommerce plugin for instance), but suitable models and layouts should be provided for rendering.
		 *
		 * @api
		 * @package weeblrAMP\filter\system
		 * @var weeblramp_request_type
		 * @since   1.0.0
		 *
		 * @param string   $type Current request type
		 * @param WP_Query $wp_query Current global WordPress query object
		 *
		 * @return string
		 */
		$type = apply_filters( 'weeblramp_request_type', $type, $wp_query );

		return $type;
	}

	/**
	 * Get from $wp_query and store post(s) for the current request
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function getContent( $data ) {

		global $wp_query;

		wp_reset_query();

		$rawContent = array();

		// existing page, store content for processing and display
		foreach ( $wp_query->posts as $post ) {
			$postDef = array(
				'post'                 => clone( $post ),
				'meta'                 => get_post_meta( $post->ID ),
				'featured_image'       => WeeblrampHelper_Content::getPostFeaturedImage( $post ),
				'content'              => $post->post_content,
				'comment_type'         => $this->userConfig->get( 'commenting_system' ),
				'comment_location_id'  => WeeblrampHelper_Content::getCommentLocationId( $post ),
				'comment_location_url' => wbArrayGet( $data, 'canonical' ),
				'comment_status'       => $post->comment_status,
				'comment_count'        => $post->comment_count,
				'comments'             => array(),
				'excerpt'              => WblWordpress_Html::getTheExcerpt(
					$post,
					$filter = true,
					$forceExcerpt = is_search()
				),
				'pages'                => array(),
				'page'                 => 1,
				'paged'                => 0,
				'numpages'             => 1,
				'multipage'            => 0,
				'more'                 => 0,
				'strip_teaser'         => false
			);

			// fetch the comments for the post
			if ( WeeblrampConfig_User::COMMENTS_NATIVE == $this->userConfig->get( 'commenting_system' ) ) {
				$postDef['comments'] = $this->getComments( $postDef );
			}

			// are we on a post full page
			$postDef = $this->isFullPage( $postDef );

			/**
			 * Let others add/process raw content extracted from post (ie pagebuilders for instance).
			 *
			 * @api
			 * @package weeblrAMP\filter\output
			 * @var weeblramp_the_content
			 * @since   1.9.0
			 *
			 * @param string  $content Raw content from the post.
			 * @param WP_Post $post Current post for the page.
			 * @param array   $pageData Full array of data available about the current page.
			 *
			 * @return string
			 */
			$postDef['content'] = apply_filters(
				'weeblramp_the_content',
				$postDef['content'],
				$post,
				$data
			);

			// split on newt page tag
			$postDef = $this->handlePagination( $postDef );

			// split on readmore tag
			$postDef = $this->handleReadMore( $postDef );

			// search content for user-set flags that may
			// change the page rendering
			$postDef = $this->processUserSetFlags( $postDef );

			// auto embed before autop or else URLs won't be transformed
			$data['content_processor']->setEmbedHandlers();
			$postDef['content'] = $this->autoembed( $postDef['content'] );
			$data['content_processor']->unsetEmbedHandlers();

			// finally autop the content
			$postDef['content'] = $this->autop( $postDef['content'] );

			$rawContent[] = $postDef;
		}

		return $rawContent;
	}

	/**
	 * Fetch comments associated with a post
	 *
	 * @param Array $postRecord
	 *
	 * @return array|int
	 */
	private function getComments( $postRecord ) {

		$comments = array();

		$showComments = apply_filters(
			'weeblramp_item_show_comments',
			wbArrayGet(
				$this->customizeConfig->get( 'item_display_options' ),
				'item_show_comments',
				true
			)
		);

		if ( empty( $showComments ) || empty( $postRecord['comment_count'] ) ) {
			return $comments;
		}

		$args     = array(
			'status'  => 'approve',
			'post_id' => $postRecord['post']->ID,
			'order'   => 'ASC'

		);
		$comments = get_comments( $args );

		return $comments;
	}

	/**
	 * Are we on an object full page?
	 *
	 * @param array $postRecord
	 *
	 * @return mixed
	 */
	private function isFullPage( $postRecord ) {

		global $wp_query;

		if ( $postRecord['post']->ID === get_queried_object_id() && ( $wp_query->is_page() || $wp_query->is_single() ) ) {
			$postRecord['more'] = 1;
		} elseif ( $wp_query->is_feed() ) {
			$postRecord['more'] = 1;
		} else {
			$postRecord['more'] = 0;
		}

		return $postRecord;
	}

	/**
	 * Build up some variables describing paginated posts
	 * Based on setup_postdata()
	 *
	 * @global      $wp_query
	 *
	 *
	 * @param array $postRecord
	 *
	 * @return array
	 */
	private function handlePagination( $postRecord ) {

		global $wp_query;

		$content = $postRecord['content'];
		if ( false !== strpos( $content, '<!--nextpage-->' ) ) {
			$content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
			$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
			$content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );

			// Ignore nextpage at the beginning of the content.
			if ( 0 === strpos( $content, '<!--nextpage-->' ) ) {
				$content = substr( $content, 15 );
			}

			$postRecord['pages'] = explode( '<!--nextpage-->', $content );
		} else {
			$postRecord['pages'] = array( $content );
		}

		// store current requested page
		$postRecord['page'] = $wp_query->get( 'page' );
		if ( empty( $postRecord['page'] ) ) {
			$postRecord['page'] = 1;
		}

		// store back the content, with next page tags removed
		$postRecord['content'] = wbArrayGet( $postRecord['pages'], $postRecord['page'] - 1, '' );

		// find about number of pages
		$postRecord['numpages'] = count( $postRecord['pages'] );
		if ( $postRecord['numpages'] > 1 ) {
			if ( $postRecord['page'] > 1 ) {
				$postRecord['more'] = 1;
			}
			$postRecord['multipage'] = 1;
		} else {
			$postRecord['multipage'] = 0;
		}

		return $postRecord;
	}

	/**
	 * Handle readmore tags in content
	 *
	 * Based on get_the_content()
	 *
	 * @param array $postRecord
	 *
	 * @return array
	 */
	private function handleReadMore( $postRecord ) {

		$content = $postRecord['content'];

		// detect tag
		$readMoreLinkText = sprintf(
			__( 'View more<span class="screen-reader-text"> "%s"</span>', 'weeblramp' ),
			WblWordpress_Html::getPostTitle( $postRecord['post'] )
		);

		if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
			if ( has_block( 'more', $content ) ) {
				// Remove the core/more block delimiters. They will be left over after $content is split up.
				$content = preg_replace( '/<!-- \/?wp:more(.*?) -->/', '', $content );
			}

			$content = explode( $matches[0], $content, 2 );
			if ( ! empty( $matches[1] ) && ! empty( $readMoreLinkText ) ) {
				$readMoreLinkText = strip_tags( wp_kses_no_null( trim( $matches[1] ) ) );
			}
			$has_teaser = true;
		} else {
			$has_teaser = false;
			$content    = array( $content );
		}

		// should be remove the teaser part?
		if ( false !== strpos( $postRecord['post']->post_content, '<!--noteaser-->' ) && ( empty( $postRecord['multipage'] ) || $postRecord['page'] == 1 ) ) {
			$postRecord['strip_teaser'] = true;
		}

		// first part of post is displayed, unless user added a tag to strip the teaser
		if ( ! empty( $postRecord['more'] ) && $postRecord['strip_teaser'] && $has_teaser ) {
			$output = '';
		} else {
			$output = $content[0];
		}

		// now add a link to the rest of the content
		if ( count( $content ) > 1 ) {
			if ( $postRecord['more'] ) {
				$output .= '<span id="more-' . $postRecord['post']->ID . '"></span>' . $content[1];
			} else {
				if ( $this->customizeConfig->isTruthy( 'item_category_display_options', 'category_amplify_readmore' ) ) {
					$postLink = $this->router->getAmpPagePermalink( $postRecord['post']->ID );
				} else {
					$postLink = get_permalink( $postRecord['post']->ID );
				}
				/**
				 * Filters the Read More link text.
				 *
				 * @since 2.8.0
				 *
				 * @param string $more_link_element Read More link element.
				 * @param string $more_link_text Read More text.
				 */
				$output .= apply_filters(
					'the_content_more_link',
					'<div class="wbamp-readmore"><a href="' . $postLink . '#more-' . $postRecord['post']->ID . '" class="more-link">' . $readMoreLinkText . '&nbsp;&nbsp;&raquo;</a></div>',
					$readMoreLinkText
				);
				$output = force_balance_tags( $output );
			}
		}

		$postRecord['content'] = $output;

		return $postRecord;
	}

	/**
	 * Apply autoembed to some text
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private function autoembed( $text ) {

		global $wp_embed;

		/**
		 * Filter the function to use when applying autoembed to AMP content. Returning an empty function
		 * will disable autoembed altogether for AMP content.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_autoembed_function
		 * @since   1.10.0
		 *
		 * @param callable $autoembedFunction Name of function - or function - to use to apply autoembed to AMP content.
		 *
		 * @return callable
		 */
		$autoembedFunction = apply_filters(
			'weeblramp_autoembed_function',
			array(
				$wp_embed,
				'autoembed'
			)
		);

		if (
			empty( $autoembedFunction )
			||
			! is_callable( $autoembedFunction )
		) {
			return $text;
		}

		$autoembeddedText = call_user_func(
			$autoembedFunction,
			$text
		);

		/**
		 * Filter proxy for autoembed. Applied to AMP content only.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_autoembed
		 * @since   1.10.0
		 *
		 * @param string $text The text to autoembed
		 *
		 * @return string
		 */
		return apply_filters(
			'weeblramp_autoembed',
			$autoembeddedText
		);
	}

	/**
	 * Apply wpauto to some text
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private function autop( $text ) {

		/**
		 * Filter the function to use when applying autop to AMP content. Returning an empty function
		 * will disable autop altogether for AMP content.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_wpautop_function
		 * @since   1.9.1
		 *
		 * @param callable $autopFunction Name of function - or function - to use to apply autop to AMP content.
		 *
		 * @return callable
		 */
		$autopFunction = apply_filters(
			'weeblramp_wpautop_function',
			'wpautop'
		);
		if (
			empty( $autopFunction )
			||
			! is_callable( $autopFunction )
		) {
			return $text;
		}

		$autopedText = call_user_func(
			$autopFunction,
			$text
		);

		/**
		 * Filter proxy for wpautop. Applied to AMP content only.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_wpautop
		 * @since   1.0.0
		 *
		 * @param string $text The text to wpautop
		 *
		 * @return string
		 */
		return apply_filters(
			'weeblramp_wpautop',
			$autopedText
		);
	}

	/**
	 * Instantiate an element-specific renderer model
	 * and use its getData() method to collect
	 * some piece of content
	 *
	 * @param  string $element Element name
	 * @param  array  $currentData
	 *
	 * @param array   $default
	 *
	 * @return array
	 */
	private function getElementData( $element, $currentData, $default = array() ) {

		$name    = 'WeeblrampModelElement_' . ucfirst( str_replace( '-', '', $element ) );
		$element = new $name();
		$result  = $element->getData( $currentData );
		$data    = isset( $result['data'] ) ? $result['data'] : array();
		$this->_assetsCollector->addScripts( isset( $result['scripts'] ) ? $result['scripts'] : array() );
		$this->_assetsCollector->addStyle( isset( $result['styles'] ) ? $result['styles'] : array() );

		return $data;
	}

	/**
	 * Build up an array of meta data that can be json_encoded and output
	 * directly to the page
	 *
	 * @param $data
	 *
	 * @return array
	 */
	private function getJsonldData( $data ) {

		$jsonld = array();

		try {
			// global meta data
			// Item global meta data
			$jsonld['@context'] = 'http://schema.org';

			$isSingular = is_single() || is_singular();
			if ( $isSingular ) {
				$defaultArticleType = $this->userConfig->get( 'default_doc_type' );
				$jsonld['@type']    = wbArrayGet( $this->ampConfig->get( 'documentTypes' ), $defaultArticleType );
			} else {
				$jsonld['@type'] = WeeblrampConfig_User::DOC_TYPE_WEBPAGE;
			}

			$jsonld['mainEntityOfPage'] = $data['amp_url'];
			$headlineMaxLength          = $this->ampConfig->get( 'headlineMaxLength' );
			$jsonld['headline']         = wbAbridge( $data['metadata']['title'], $headlineMaxLength, $headlineMaxLength - 3 );
			$jsonld['description']      = wbArrayGet(
				$data,
				array( 'metadata', 'description' )
			);

			// image: we already searched for it, as it was needed to build meta data
			if ( ! empty( $data['image'] ) ) {
				$jsonld['image'] = $data['image'];
			}

			// publisher
			$jsonld['publisher'] = wbArrayGet(
				$data,
				'publisher'
			);

			$publisherImageUrl        = $this->userConfig->get( 'publisher_image' );
			$imageSize                = array();
			$imageSize['width']       = 0;
			$imageSize['height']      = 0;
			$publisherImageDescriptor = $this->buildValidJsonLdImageDescriptor(
				$publisherImageUrl,
				$imageSize,
				array( $this, 'validatePublisherImageDimensions' )
			);

			if ( ! empty( $publisherImageDescriptor ) ) {
				$jsonld['publisher']['logo'] = $publisherImageDescriptor;
			}

			// dates
			if ( ! empty( $data['date_published'] ) ) {
				$jsonld['datePublished'] = WblSystem_Date::toAtom( $data['date_published'] );
			}
			if ( ! empty( $data['date_modified'] ) ) {
				$jsonld['dateModified'] = WblSystem_Date::toAtom( $data['date_modified'] );
			}

			// author info
			if ( ! empty( $data['author'] ) ) {
				$jsonld['author'] = array(
					'@type' => 'Person',
					'name'  => $data['author']->display_name
				);
			}

			/**
			 * Runs the Automattic plugin json-ld filter.
			 *
			 * Filter the AMP Json+ld manifest, an associative array of values to be output as the json+ld manifest of the AMP page. weeblrAMP will convert it to Json before output.
			 *
			 * @api
			 * @package weeblrAMP\filter\seo
			 * @var amp_post_template_metadata
			 * @since   1.8.0
			 *
			 * @param array   $jsonld An array of structured data
			 * @param WP_Post $post The post being displayed.
			 *
			 * @return array
			 */
			$jsonld = apply_filters( 'amp_post_template_metadata', $jsonld, WeeblrampHelper_Content::getPostFromPageData( $data ) );

			/**
			 * Filter the AMP Json+ld manifest, an associative array of values to be output as the json+ld manifest of the AMP page. weeblrAMP will convert it to Json before output.
			 *
			 * @api
			 * @package weeblrAMP\filter\seo
			 * @var weeblramp_json_manifest
			 * @since   1.0.0
			 *
			 * @param array $jsonld An array of structured data
			 * @param array $data Full array of page data collected up to now.
			 *
			 * @return array
			 */
			$jsonld = apply_filters( 'weeblramp_json_manifest', $jsonld, $data );

			// then look for overrides set by user in content or otherwise

			// publication date: [wbamp-meta name="date_published" content="2016-03-11 06:00:00"]
			if ( $isSingular && wbArrayGet( $data, array( 'user_set_data', 'date_published', 'content' ) ) ) {
				$jsonld['datePublished'] = WblSystem_Date::toAtom( $data['user_set_data']['date_published']['content'] );
			}

			// modification date: [wbamp-meta name="date_modified" content="2016-03-11 06:00:00"]
			if ( $isSingular && wbArrayGet( $data, array( 'user_set_data', 'date_modified', 'content' ) ) ) {
				$jsonld['dateModified'] = WblSystem_Date::toAtom( $data['user_set_data']['date_modified']['content'] );
			}

			// document type: [wbamp-meta name="doc_type" content="xxxxx"]
			if ( $isSingular && wbArrayGet( $data, array( 'user_set_data', 'doc_type', 'content' ) ) ) {
				$jsonld['@type'] = StringHelper::trim( $data['user_set_data']['doc_type']['content'] );
			}

			// document name: [wbamp-meta name="doc_name" content="xxxxx"]
			if ( $isSingular && wbArrayGet( $data, array( 'user_set_data', 'doc_name', 'content' ) ) ) {
				$jsonld['name'] = StringHelper::trim( $data['user_set_data']['doc_name']['content'] );
			}

			// author: [wbamp-meta name="author" type="Person" content="Yannick Gaultier"]
			if ( $isSingular && wbArrayGet( $data, array( 'user_set_data', 'author' ) ) ) {
				if ( ! empty( $data['user_set_data']['author']['type'] ) ) {
					$jsonld['author']['@type'] = StringHelper::trim( $data['user_set_data']['author']['type'] );
				}
				if ( ! empty( $data['user_set_data']['author']['content'] ) ) {
					$jsonld['author']['name'] = StringHelper::trim( $data['user_set_data']['author']['content'] );
				}
			}
		}
		catch ( Exception $e ) {
			WblSystem_Log::error( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, $e->getMessage() );
			$jsonld = array();
		}

		// chack a few basic rules on json-ld
		$jsonld = $this->sanitizeJsonLd( $jsonld, $data );

		/**
		 * Filter the AMP manifest Json+LD data.
		 *
		 * This is an array of page and author data. It will be later on json_encoded when rendered.
		 *
		 * @api
		 * @package weeblrAMP\filter\seo
		 * @var weeblramp_get_jsonld_data
		 * @since   1.0.0
		 *
		 * @param array $jsonld The full array of json+ld data collected/built.
		 * @param array $pageData The full array of data available about the current page being rendered.
		 *
		 * @return array
		 */
		$jsonld = apply_filters( 'weeblramp_get_jsonld_data', $jsonld, $data );

		return $jsonld;
	}

	/**
	 * Apply a few basic consistency rules, to improve chances of validation.
	 *
	 * @param array $jsonld Array of json-ld amp page manifest collected.
	 * @param array $pageData Array of data collected thus far to build the page.
	 *
	 * @return array
	 */
	private function sanitizeJsonLd( $jsonld, $pageData ) {

		$docType = wbArrayGet( $jsonld, '@type' );
		$post    = null;

		// per doc type adjustments
		switch ( $docType ) {
			case 'Recipe':
				// must have a name
				if ( empty( $jsonld['name'] ) ) {
					// try to build one
					$post = is_null( $post ) ? WeeblrampHelper_Content::getPostFromPageData( $pageData ) : $post;
					if ( ! empty( $post ) ) {
						$jsonld['name'] = $post->post_title;
					}

					if ( empty( $jsonld['name'] ) ) {
						// use meta data
						$jsonld['name'] = wbArrayGet( $pageData, array( 'metadata', 'title' ), __( 'Recipe' ) );
					}
				}
				break;
			// no doc type yet
			case '':
				$jsonld['@type'] = 'WebPage';
				break;
			default:
				break;
		}

		// always with a description
		if ( empty( $jsonld['description'] ) ) {
			$post = is_null( $post ) ? WeeblrampHelper_Content::getPostFromPageData( $pageData ) : $post;
			if ( ! empty( $post ) ) {
				$jsonld['description'] = $post->post_decription;
			}

			if ( empty( $jsonld['description'] ) ) {
				// use meta data
				$jsonld['description'] = wbArrayGet( $pageData, array( 'metadata', 'description' ), '' );
			}

			if ( empty( $jsonld['description'] ) ) {
				unset( $jsonld['description'] );
			}
		}

		return $jsonld;
	}

	/**
	 * Collect the site favicon(s), using WP API
	 *
	 */
	private function getRenderedFavicons() {

		if ( function_exists( 'wp_site_icon' ) ) {
			ob_start();
			wp_site_icon();
			$renderedFavicons = ob_get_clean();
		} else {
			$renderedFavicons = '';
		}

		return $renderedFavicons;
	}

	/**
	 * Try to find the best image to be used as the page image (used in Top stories carrousel)
	 *
	 * @param array $data
	 *
	 * @return array|null
	 */
	private function findPageImage( $data ) {

		$image = array();
		// image, if set by user in regular content, [wbamp-meta name="image" url="" height="123" width="456"]
		if ( ! empty( $data['user_set_data'] ) && ! empty( $data['user_set_data']['image'] ) && ! empty( $data['user_set_data']['image']['url'] ) ) {
			$imageSize           = array();
			$imageSize['width']  = empty( $data['user_set_data']['image']['width'] ) ? 0 : $data['user_set_data']['image']['width'];
			$imageSize['height'] = empty( $data['user_set_data']['image']['height'] ) ? 0 : $data['user_set_data']['image']['height'];
			$image               = $this->buildValidJsonLdImageDescriptor(
				$data['user_set_data']['image']['url'],
				$imageSize,
				array( $this, 'validatePageImageDimensions' )
			);
		}

		// look for a Featured image if no image found yet
		if ( empty( $image ) ) {
			$image = $this->_findFeaturedImage( $data['main_content'] );
		}

		// fallback to finding an image automatically if none set
		if ( empty( $image ) ) {
			foreach ( $data['main_content'] as $contentItem ) {
				$image = $this->_findImageIncontent( $contentItem['content'] );
				if ( ! empty( $image ) ) {
					break;
				}
			}
		}

		return $image;
	}

	/**
	 * Search posts definitions for featured images
	 *
	 * @param $content
	 *
	 * @return array|null
	 */
	private function _findFeaturedImage( $content ) {

		$image = null;
		if ( empty( $content ) || ! is_array( $content ) ) {
			return $image;
		}

		foreach ( $content as $contentItem ) {
			if ( ! empty( $contentItem['featured_image'] ) ) {
				$imageUrl = wbArrayGet( $contentItem['featured_image'], 'url', '' );

				if ( ! empty( $imageUrl ) ) {
					$imageUrl = WblSystem_Route::absolutify( $imageUrl );

					// validate this image against AMP rules
					$imageSize = wbArrayGet(
						$contentItem['featured_image'], 'image_meta', array(
							                              'width'  => 0,
							                              'height' => 0
						                              )
					);
					$imageSize = WeeblrampHelper_Media::findImageSizeIfMissing( $imageUrl, $imageSize );
					if ( ! empty( $imageSize['width'] ) && ! empty( $imageSize['height'] ) && $imageSize['width'] >= $this->ampConfig->get( 'pageImageMinWidth' ) ) {
						$image = array(
							'@type'  => 'ImageObject',
							'url'    => $imageUrl,
							'width'  => $imageSize['width'],
							'height' => $imageSize['height'],

						);
					}
				}
			}

			if ( ! empty( $image ) ) {
				break;
			}
		}

		return $image;
	}

	/**
	 * Lookup images in the  page content and extract an image
	 * that can represent the page for AMP. Min width restrictions apply
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	private function _findImageIncontent( $content ) {

		$regex = '#<img([^>]*)>#Uum';
		preg_replace_callback( $regex, array( $this, '_extractImageFromContent' ), $content );

		return $this->_extractedImage;
	}

	/**
	 * Stores the first image found in content to be used
	 * as page image
	 *
	 * @param $match
	 *
	 * @return mixed
	 */
	private function _extractImageFromContent( $match ) {

		// detect type we can handle
		if ( ! empty( $match[1] ) && empty( $this->_extractedImage ) ) {
			$attributes = WblSystem_Strings::parseAttributes( $match[1] );
			if ( empty( $attributes['src'] ) ) {
				// this happens
				return $match[0];
			}
			$imageSize           = array();
			$imageSize['width']  = empty( $attributes['width'] ) ? 0 : $attributes['width'];
			$imageSize['height'] = empty( $attributes['height'] ) ? 0 : $attributes['height'];

			$this->_extractedImage = $this->buildValidJsonLdImageDescriptor(
				$attributes['src'],
				$imageSize,
				array( $this, 'validatePageImageDimensions' )
			);
		}

		return $match[0];
	}

	/**
	 * Search content for flags that may alter the way the page is rendered
	 *
	 * [wbamp-no-ads]: suppress automatically displayed ads on the page
	 * [wbamp-no-widgets]: suppress amp widgets display on the page
	 *
	 * @param array $postRecord
	 *
	 * @return array
	 */
	private function processUserSetFlags( $postRecord ) {

		if ( empty( $postRecord ) || empty( $postRecord['content'] ) ) {
			return $postRecord;
		}

		if ( false !== strpos( $postRecord['content'], '[wbamp-no-ads]' ) ) {
			$postRecord['content']         = str_replace( '[wbamp-no-ads]', '', $postRecord['content'] );
			WeeblrampHelper_Ads::$disabled = true;

			// must remove the amp-ad script and css. amp-ad script will cause (soon) an error, while css is a waste
			// of space
			$this->_assetsCollector->removeScript( 'amp-ad' );
		}

		if ( false !== strpos( $postRecord['content'], '[wbamp-no-widgets]' ) ) {
			$postRecord['content']                  = str_replace( '[wbamp-no-widgets]', '', $postRecord['content'] );
			WeeblrampHelper_Widget::$widgetDisabled = true;
		}

		return $postRecord;
	}

	/**
	 * Builds an image record suitable to be json_encoded into an AMP
	 * json+ld manifest.
	 *
	 * Calls a validation function on the image. Pass null as validator function
	 * to skip those checks.
	 *
	 * Returns null if image is not valid
	 *
	 * @param string   $imageUrl
	 * @param array    $imageSize
	 * @param callable $validator
	 *
	 * @return array|null
	 */
	private function buildValidJsonLdImageDescriptor(
		$imageUrl,
		$imageSize = array(
			'width'  => 0,
			'height' => 0
		),
		$validator = null
	) {

		$imageDescriptor = null;
		if ( ! empty( $imageUrl ) ) {
			$imageUrl  = WblSystem_Route::absolutify( $imageUrl, WblSystem_Route::FORCE_DOMAIN );
			$imageSize = WeeblrampHelper_Media::findImageSizeIfMissing( $imageUrl, $imageSize );
			if ( is_callable( $validator ) ) {
				$valid = call_user_func( $validator, $imageUrl, $imageSize );
			} else {
				$valid = true;
			}
			if ( $valid ) {
				$imageDescriptor = array(
					'@type'  => 'ImageObject',
					'url'    => $imageUrl,
					'width'  => $imageSize['width'],
					'height' => $imageSize['height'],

				);
			}
		}

		return $imageDescriptor;
	}

	/**
	 * Validate a publisher logo image dimensions against known AMP rules.
	 *
	 * @param string $imageUrl
	 * @param array  $imageSize
	 *
	 * @return bool
	 */
	private function validatePublisherImageDimensions( $imageUrl, $imageSize ) {

		return WeeblrampClass_Configcheck::checkPublisherLogoSize(
			$imageSize['width'],
			$imageSize['height']
		);
	}

	/**
	 * Validate a page image dimensions against known AMP rules.
	 *
	 * @param string $imageUrl
	 * @param array  $imageSize
	 */
	private function validatePageImageDimensions( $imageUrl, $imageSize ) {

		$valid = false;
		if (
			empty( $imageSize['width'] )
			||
			empty( $imageSize['height'] )
			||
			$imageSize['width'] < $this->ampConfig->get( 'pageImageMinWidth' )
		) {
			return $valid;
		}

		$pixels = $imageSize['width'] * $imageSize['height'];
		if ( $pixels < $this->ampConfig->get( 'pageImageMinPixels' ) ) {
			return $valid;
		}

		$extension = pathinfo( $imageUrl, PATHINFO_EXTENSION );
		if ( ! in_array( $extension, $this->ampConfig->get( 'pageImageTypes' ) ) ) {
			return $valid;
		}

		return true;
	}
}
