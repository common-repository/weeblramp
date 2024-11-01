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
use Weeblr\Wblib\Joomla\Uri\Uri;

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Routing related methods
 *
 * Some parts based on Automattic AMP plugin
 */
class WeeblrampClass_Route extends WeeblrampClass_Base {

	/**
	 * Store standard permalinks structures
	 */
	// Posts: Day & name
	const PERMALINK_DAY_NAME = '/%year%/%monthnum%/%day%/%postname%/';
	// Posts: Month & name
	const PERMALINK_MONTH_NAME = '/%year%/%monthnum%/%postname%/';
	// Posts: Numeric
	const PERMALINK_NUMERIC = '/archives/%post_id%';
	// Posts: Post name
	const PERMALINK_POSTNAME = '/%postname%/';
	// Posts: Post name
	const PERMALINK_CUSTOM_CATNAME_POSTNAME = '/%category%/%postname%/';

	/**
	 * Flag set to true when on AMP version
	 * of home page; requires special processing
	 * as the added /amp/ path breaks the subtle
	 * balance of WP parse_query method.
	 *
	 * @var bool
	 */
	private $isAmpHome = false;

	/**
	 * We use that flag and remove 'amp' from the query vars. This is less risky
	 * with respect to the request parsing process of WP and other plugins.
	 *
	 * @var bool Global flag raised on an AMP page.
	 */
	private $isAmpPage = false;

	/**
	 * Shorthands
	 *
	 * @var mixed
	 */
	private $queryVar        = null;
	private $taxonomyManager = null;

	/**
	 * Constructor
	 *
	 * @param array $options An array of options.
	 *
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );

		// store the router and taxonomy manager, needed downstream
		$this->taxonomyManager = WeeblrampFactory::getA( 'WeeblrampClass_Taxonomy' );
	}

	/**
	 * Shorthand for whether we are in standalone mode
	 *
	 * @return bool
	 */
	public function isStandaloneMode() {

		static $isStandaloneMode = null;

		if ( is_null( $isStandaloneMode ) ) {
			if ( WeeblrampHelper_Version::isFullEdition() ) {
				$isStandaloneMode = WeeblrampConfig_User::OP_MODE_STANDALONE == $this->userConfig->get( 'op_mode' );
				/**
				 * Filter whether weeblrAMP standalone mode is enabled
				 *
				 * @api
				 * @package weeblrAMP\filter\route
				 * @var weeblramp_standalone_mode
				 * @since   1.0.0
				 *
				 * @param bool $isStandaloneMode
				 *
				 * @return bool
				 */
				$isStandaloneMode = apply_filters( 'weeblramp_standalone_mode', $isStandaloneMode );
			} else {
				$isStandaloneMode = false;
			}
		}

		return $isStandaloneMode;
	}

	/**
	 * Getter for the AMP home page flag
	 *
	 * @return bool
	 */
	public function isAmpHome() {

		return $this->isAmpHome;
	}

	/**
	 * Getter for the AMP pages suffix. Will run the `weeblramp_suffix` filter
	 *
	 * @return string
	 */
	public function getQueryVar() {

		if ( is_null( $this->queryVar ) ) {
			/**
			 * Filter the query var used to identify AMP requests.
			 *
			 * Default to amp. Value can be changed by user in weeblrAMP settings.
			 *
			 * @api
			 * @package weeblrAMP\filter\config
			 * @var weeblramp_query_var
			 * @since   1.0.0
			 *
			 * @param string $queryVar The current query var used to distinguish AMP requests
			 *
			 * @return string
			 */
			$this->queryVar = apply_filters(
				'weeblramp_query_var',
				WeeblrampFactory::getThe( 'weeblramp.config.user' )->get( 'amp_suffix', 'amp' )
			);
		}

		return $this->queryVar;
	}

	/**
	 * Setter for the AMP pages suffix
	 *
	 * @param string $suffix
	 */
	public function setQueryVar( $suffix ) {

		$this->queryVar = $suffix;
	}

	/**
	 * - Set the AMP query var in the request, if it is set but empty
	 * - Remove cache busting variables we may have introduced before
	 *
	 * Based on Automattic AMP plugin
	 *
	 * @param array $query_vars
	 *
	 * @return array
	 */
	public function filter_request( $query_vars ) {

		// cache busting removal
		if ( isset( $query_vars['_wb_bust'] ) ) {
			unset( $query_vars['_wb_bust'] );
		}

		// fix amp queryvar
		if (
			isset( $query_vars[ $this->queryVar ] )
			&&
			(
				'' === $query_vars[ $this->queryVar ]
				||
				'1' === $query_vars[ $this->queryVar ]
			)
		) {
			$query_vars[ $this->queryVar ] = 1;
		}

		// custom processing for AMP home page
		if (
			isset( $query_vars[ $this->queryVar ] )
			&&
			1 === $query_vars[ $this->queryVar ]
		) {
			// @see /wp-includes/class-wp-query.php@970: function parse_query()
			// the list of query vars is not filtered, so we cannot add "amp" to it
			$this->isAmpHome = ! array_diff(
				array_keys( $query_vars ),
				array_merge(
					array( $this->queryVar ),
					array( 'preview', 'page', 'paged', 'cpage' )
				)
			);

			// raise flag and remove amp vars from query, reduces
			// risk of interfering with rewrite rules of WP and other plugins.
			$this->isAmpPage = true;
			unset( $query_vars[ $this->queryVar ] );
			unset( $_REQUEST[ $this->queryVar ] );
		}

		return $query_vars;
	}

	/**
	 * Decides whether the current request should be amplified, by
	 * checking if all the posts on the page should be amplified
	 * If at least one post should be amplified, then the page
	 * should be amplified
	 *
	 * @return bool
	 */
	public function shouldAmplifyPage() {

		global $wp_query, $page;

		// offer integrations and others possiblity to prevent
		// a page to be AMPlified
		/**
		 * Filter whether to AMPlify the current request. Default to true.
		 *
		 * @api
		 * @package weeblrAMP\filter\config
		 * @var weeblramp_should_amplify_page
		 * @since   1.0.0
		 *
		 * @param bool     $shouldAmplifyPage Whether to AMPlify the current request
		 * @param WP_Query $wp_query The current global WordPress query object
		 * @param int      $page The current global WordPress page number
		 *
		 * @return bool
		 */
		if ( ! apply_filters( 'weeblramp_should_amplify_page', true, $wp_query, $page ) ) {
			return false;
		}

		if ( $this->isStandaloneMode() ) {
			// standalone mode, AMP is all we know
			return true;
		}

		// special case for home page
		if ( $this->isAmpHome && $this->userConfig->isFalsy( 'amplify_home' ) ) {
			return false;
		}

		// valid cases
		switch ( true ) {

			// amplifiable
			case $wp_query->is_author:
				$shouldAmplify = $this->userConfig->isTruthy( 'amplify_authors' );
				break;

			case $wp_query->is_search:
				$shouldAmplify = $this->userConfig->isTruthy( 'amplify_search_page' );
				break;

			case $wp_query->is_archive:
				if ( ( ! $wp_query->is_category || ! $wp_query->is_tag ) && $this->userConfig->isFalsy( 'amplify_archives' ) ) {
					return false;
				}
				// an empty archive
				if ( ! $wp_query->is_category && ! $wp_query->is_tag && empty( $wp_query->posts ) && ! $this->userConfig->isFalsy( 'amplify_archives' ) ) {
					return true;
				}

				if ( ( ! $wp_query->is_category || ! $wp_query->is_tag ) && ! empty( $page ) && $page > 1 ) {
					// we only allow AMP on paginated pages
					// for post, not custom posts
					// custom post types archives cannot be handled
					// because of WP rewrite API limitations
					// unless specific rewrite rules are added
					// Integrations can add such rules
					$posts = $wp_query->posts;
					foreach ( $posts as $key => $post ) {
						if ( ! $this->shouldAmplifyArchivePostPagination( $post->post_type ) ) {
							return false;
						}
					}
				}

			case $wp_query->is_tax:
				if($wp_query->is_tax) {
					$queriedTerm = $wp_query->get_queried_object();
					if (
						! empty( $queriedTerm )
						&&
						$queriedTerm instanceof WP_Term
						&&
						! $this->taxonomyManager->shouldAmplifyTerm( $queriedTerm->term_id, $queriedTerm->taxonomy )
					) {
						return false;
					}
				}
			case $wp_query->is_tag:
				if ( $wp_query->is_tag && $this->userConfig->isFalsy( 'amplify_tags' ) ) {
					return false;
				}
				// an empty tag
				if ( $wp_query->is_tag && empty( $wp_query->posts ) && ! $this->userConfig->isFalsy( 'amplify_tags' ) ) {
					return true;
				}

			case $wp_query->is_posts_page:
				if($wp_query->is_posts_page) {
					$postsPageId           = get_option( 'page_for_posts' );
					$postPageIdUserEnabled = get_post_meta(
						$postsPageId,
						'_wbamp_enable_amp',
						true
					);
					switch ( $postPageIdUserEnabled ) {
						case '1':
							return true;
							break;
						case '0':
							return false;
							break;
						default:
							break;
					}
				}
			case $wp_query->is_category:
				if ( $wp_query->is_category && $this->userConfig->isFalsy( 'amplify_categories' ) ) {
					return false;
				}
				// an empty category
				if ( $wp_query->is_category && empty( $wp_query->posts ) && ! $this->userConfig->isFalsy( 'amplify_categories' ) ) {
					return true;
				}

			case $wp_query->is_home:
				if ( $wp_query->is_home && $this->userConfig->isFalsy( 'amplify_home' ) ) {
					return false;
				}

			case $wp_query->is_page:
			case $wp_query->is_single:
			case $wp_query->is_singular:
				$posts = $wp_query->posts;
				foreach ( $posts as $key => $post ) {

					// check for manual override by user
					$userEnabled = get_post_meta(
						$post->ID,
						'_wbamp_enable_amp',
						true
					);

					// otherwise use taxonomies
					switch ( $userEnabled ) {
						case '1':
							break;
						case '0':
							unset( $posts[ $key ] );
							break;
						default:
							if ( ! $this->taxonomyManager->shouldAmplifyItem( $post ) ) {
								unset( $posts[ $key ] );
							}
							break;
					}
				}

				// if at least one post is AMPlifiable, we should show/advertise the AMP page
				$shouldAmplify = ! empty( $posts );
				break;

			default:
				$shouldAmplify = false;
				break;
		}

		return $shouldAmplify;
	}

	/**
	 * Finds whether pagination should be amplified for a given
	 * post type (for single/singular items)
	 *
	 * @param string $postType
	 *
	 * @return bool
	 */
	public function shouldAmplifyPostPagination( $postType ) {

		$shouldAmplify = in_array(
			$postType,
			$this->getPaginatedPostTypes()
		);

		return $shouldAmplify;
	}

	/**
	 * Return a list of post types for which
	 * we allow pagination to operate on singel/singular pages
	 *
	 * @return array
	 */

	public function getPaginatedPostTypes( $postType = null ) {

		static $def = null;

		if ( is_null( $def ) ) {
			$def = apply_filters(
				'weeblramp_paginated_post_types',
				array(
					'post',
					'page'
				)
			);
		}

		if ( empty( $postType ) ) {
			return $def;
		}

		return wbArrayGet( $def, $postType, '' );
	}

	/**
	 * Finds whether pagination should be amplified for a given
	 * archive page post type (for single/singular items)
	 *
	 * @param string $postType
	 *
	 * @return bool
	 */
	public function shouldAmplifyArchivePostPagination( $postType ) {

		$shouldAmplify = in_array(
			$postType,
			$this->getArchivesPaginatedPostTypes()
		);

		return $shouldAmplify;
	}

	/**
	 * Return a list of post types for which
	 * we allow pagination to operate on archive pages
	 *
	 * @return array
	 */
	public function getArchivesPaginatedPostTypes() {

		return apply_filters(
			'weeblramp_paginated_archived_post_types',
			array(
				'post',
				'page',
				'author'
			)
		);
	}

	/**
	 * Build the AMP URL based on a post ID
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function getAmpPagePermalink( $post_id = 0 ) {

		$htmlUrl = empty( $post_id ) ? WblWordpress_Helper::getCurrentRequestUrl( $absolute = true ) : get_permalink( $post_id );
		$ampUrl  = $this->getAmpUrlFromCanonical( $htmlUrl );

		/**
		 * Filter the AMP URL generated by weeblrAMP for an item. If the item is a WP_POST, its id is also provided.
		 *
		 * @api
		 * @package weeblrAMP\filter\route
		 * @var weeblramp_get_amp_permalink
		 * @since   1.0.0
		 *
		 * @param string $ampUrl AMP url as generated by weeblrAMP
		 * @param int    $post_id Id of the WP_Post which is linked to, if any
		 *
		 * @return string
		 */
		return apply_filters( 'weeblramp_get_amp_permalink', $ampUrl, $post_id );
	}

	/**
	 * Build the AMP URL based on a URL
	 *
	 * Based on Automattic AMP plugin, somehow
	 *
	 * @param string $canonicalUrl
	 */
	public function getAmpUrlFromCanonical( $canonicalUrl ) {

		// is this a local URL?
		if ( ! WblSystem_Route::isInternal( $canonicalUrl ) ) {
			return $canonicalUrl;
		}

		// already has AMP suffix?
		if ( $this->isAmpUrl( $canonicalUrl ) ) {
			return $canonicalUrl;
		}

		// offer integrations and others possiblity to prevent
		// a link to be AMPlified
		/**
		 * Filter whether to AMPlify a link. Default to true.
		 *
		 * @api
		 * @package weeblrAMP\filter\config
		 * @var weeblramp_should_amplify_url
		 * @since   1.0.0
		 *
		 * @param string $canonicalUrl The full URL to amplify
		 *
		 * @return string
		 */
		if ( ! apply_filters( 'weeblramp_should_amplify_url', true, $canonicalUrl ) ) {
			return $canonicalUrl;
		}

		// standalone mode, all URLs are equal
		if ( $this->isStandaloneMode() ) {
			return $canonicalUrl;
		}

		if ( '' != get_option( 'permalink_structure' ) ) {
			$uri     = new Uri( $canonicalUrl );
			$newPath =
				trailingslashit( $uri->getPath() )
				. user_trailingslashit( $this->getQueryVar(), 'weeblramp_amp' );
			$uri->setPath( $newPath );
			$uri    = WeeblrampHelper_Route::addDebugTokenQuery( $uri );
			$ampUrl = $uri->toString();
		} else {
			$ampUrl = add_query_arg( $this->getQueryVar(), 1, $canonicalUrl );
		}

		return $ampUrl;
	}

	/**
	 * Remove trailing amp slug
	 *
	 * @param $amp
	 */
	public function getCanonicalFromAmpUrl( $ampUrl ) {

		// standalone mode, all URLs are equal
		if ( $this->isStandaloneMode() ) {
			return $ampUrl;
		}

		$canonical = $ampUrl;
		if ( '' != get_option( 'permalink_structure' ) ) {
			$uri     = new Uri( $canonical );
			$newPath = wbRTrim(
				user_trailingslashit( $uri->getPath(), 'weeblramp_amp' ),
				user_trailingslashit( $this->getQueryVar(), 'weeblramp_amp' )
			);
			$uri->setPath( $newPath );
			$uri       = WeeblrampHelper_Route::removeDebugTokenQuery( $uri );
			$canonical = $uri->toString();
		}

		// always remove amp query var, we may use it sometimes, even if permalinkg_structure
		$canonical = remove_query_arg( $this->getQueryVar(), $canonical );

		return $canonical;
	}

	/**
	 * Finds out if a URL ends with the AMP suffix.
	 *
	 * @param string $url
	 */
	public function looksLikeAnAmpUrl( $url ) {

		return wbEndsWith(
			StringHelper::rtrim( $url, '/' ),
			$this->getQueryVar()
		);
	}

	/**
	 * Are we currently on an AMP URL?
	 *
	 * Note: will always return `false` if called before the `parse_query` hook.
	 */
	public function isAmpPage() {

		// standalone mode, all URLs are equal
		if ( $this->isStandaloneMode() ) {
			return true;
		}

		return
			( $this->isAmpHome && $this->userConfig->isTruthy( 'amplify_home' ) )
			||
			$this->isAmpPage
			||
			( false !== get_query_var(
					$this->getQueryVar(),
					false
				)
			);
	}

	/**
	 * Roughly finds out if a provided URL already has
	 * the AMP suffix
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public function isAmpUrl( $url ) {

		$pattern = '#\/' . $this->queryVar . '\/?$#i';

		return (bool) preg_match( $pattern, $url );
	}

	/**
	 * Finds out whether the current request is a 404
	 *
	 * @return mixed
	 */
	public function isErrorPage() {

		global $wp_query;

		return $wp_query->is_404;
	}

	/**
	 * If not in standalone mode, set the amp suffix endpoint, and some
	 * additional rewrite rules, needed to handle special cases
	 *
	 * In standalone mode, all pages are AMP, so we don't need any specific
	 * rewrite rules
	 *
	 * @param array $postTypes
	 *
	 * @return $this
	 */
	public function setRewriteRules( $postTypes ) {

		if ( $this->isStandaloneMode() ) {
			return $this;
		}

		global $wp_rewrite;

		// collect rules from others, and merge with built-in
		/**
		 * Filter list of custom rewrite rules added for AMP support
		 *
		 * Rules are listed using the following format (example from system config)
		 *
		 *
		 * Priority 200
		 * // categories
		 * '200.categories' => array(
		 * 'rule' => '{CATEGORY_BASE}/(.+?)/{PAGINATION_BASE}/([0-9]{1,})/{AMP_SUFFIX}/?$',
		 * 'rewrite' => 'index.php?category_name=$matches[1]&paged=$matches[2]&{AMP_SUFFIX}=1',
		 * 'permalink_structure' => '',
		 * 'position' => 'top'
		 * ),
		 * '200.categories.numeric' => array(
		 * 'rule' => 'archives/{CATEGORY_BASE}/(.+?)/{PAGINATION_BASE}/([0-9]{1,})/{AMP_SUFFIX}/?$',
		 * 'rewrite' => 'index.php?category_name=$matches[1]&paged=$matches[2]&{AMP_SUFFIX}=1',
		 * 'permalink_structure' => WeeblrampClass_Route::PERMALINK_NUMERIC,
		 * 'position' => 'top'
		 * ),
		 *
		 * Notes:
		 *
		 * - the identifier (200.categories.numeric) is prepended with an integer to control the order in which rules are added to WordPress rewrite rules list.
		 *      naming: NN.jetpack.project-type (example)
		 *      NN is an integer, standard rules have NN = 100
		 *      Higher number have higher precedence
		 * - 'rule' and 'rewrite" are same as WordPress
		 * - 'permalink_structure", if provided, will cause the rule to only be injected if WordPress current user-selected permalink structure is the one specified
		 * - 'position' is used when calling "add_rewrite_rules"
		 *
		 * @api
		 * @package weeblrAMP\filter\system
		 * @var weeblramp_custom_rewrite_rules
		 * @since   1.0.0
		 *
		 * @param array $rules List of rewrite rules used on current request
		 *
		 * @return array
		 */
		$rules = apply_filters(
			'weeblramp_custom_rewrite_rules',
			$this->systemConfig->get( 'custom_rw_rules' )
		);

		// use naming convention to order rules
		// naming: NN.jetpack.project-type
		// NN is an integer, built-in rules have NN = 100
		// Higher number have higher precedence
		krsort( $rules, SORT_NUMERIC );

		// add them to WP rewrite
		$tags         = array(
			'{AMP_SUFFIX}',
			'{PAGINATION_BASE}',
			'{CATEGORY_BASE}',
			'{TAG_BASE}'
		);
		$replacements = array(
			$this->getQueryVar(),
			$wp_rewrite->pagination_base,
			wbInitEmpty(
				get_option( 'category_base' ),
				'category'
			),
			wbInitEmpty(
				get_option( 'tag_base' ),
				'tag'
			)
		);

		$permalinkStructure = get_option( 'permalink_structure' );
		$rewrittenRules     = array();
		foreach ( $rules as $name => $rule ) {
			if (
				empty( $rule['permalink_structure'] )
				||
				$permalinkStructure == $rule['permalink_structure']
			) {
				$rule['rule']            = str_replace( $tags, $replacements, $rule['rule'] );
				$rule['rewrite']         = str_replace( $tags, $replacements, $rule['rewrite'] );
				$rewrittenRules[ $name ] = $rule;
			}
		}

		$rewrittenRules = apply_filters(
			'weeblramp_multilingual_rewrite_rules',
			$rewrittenRules
		);

		foreach ( $rewrittenRules as $rule ) {
			add_rewrite_rule(
				$rule['rule'],
				$rule['rewrite'],
				$rule['position']
			);
		}

		// simple endpoint
		add_rewrite_endpoint(
			$this->getQueryVar(),
			EP_ALL & ~EP_ATTACHMENT
		);

		// even when using EP_ALL, custom endpoints are not added to
		// custom taxonomies, so we need to add them manually
		WeeblrampFactory::getA( 'WeeblrampHelper_Terms' )
		                ->fixRewriteRules(
			                $this->getQueryVar(),
			                $postTypes
		                );

		return $this;
	}

	/**
	 * Filters the pagination link on posts and pages, to insert
	 * lnks to AMP version of page 2, 3,..;
	 *
	 * @param string $link
	 * @param int    $i
	 *
	 * @return mixed
	 */
	public function filter_wp_link_pages_link( $link, $i ) {

		// standalone mode, all URLs are equal
		if ( $this->isStandaloneMode() ) {
			return $link;
		}

		if ( $this->customizeConfig->isFalsy( 'item_display_options', 'item_amplify_pagination' ) ) {
			return $link;
		}

		// extract link
		$matched = preg_match( '/href="([^"]*)"/', $link, $matches );
		if ( empty( $matched ) || empty( $matches[1] ) ) {
			return $link;
		}

		// build AMP URL
		$oldHRef = StringHelper::rtrim( $matches[1], '/' );
		$newHref = $this->getAmpUrlFromCanonical( $oldHRef );

		$link = str_replace( 'href="' . $matches[1] . '"', 'href="' . $newHref . '"', $link );

		return $link;
	}

	/**
	 * Filters the pagination links of category/archive/search pages, to insert AMP version
	 *
	 * @param string $link
	 *
	 * @return mixed
	 */
	public function filter_paginate_links( $link ) {

		// standalone mode, all URLs are equal
		if ( $this->isStandaloneMode() || empty( $link ) ) {
			return $link;
		}

		global $wp_rewrite;

		$bits = explode( '#', $link, 2 );
		if ( ! empty( $bits[1] ) ) {
			// there is a query string
			$link     = $bits[0];
			$fragment = '#' . $bits[1];
		} else {
			$fragment = '';
		}

		// drop query string if any
		$bits = explode( '?', $link, 2 );
		if ( ! empty( $bits[1] ) ) {
			// there is a query string
			$link  = $bits[0];
			$query = '?' . $bits[1];
		} else {
			$query = '';
		}

		// already AMPlified?
		$pattern = '#\/' . $wp_rewrite->pagination_base . '\/[0-9]+\/' . $this->queryVar . '\/$#i';
		if ( preg_match( $pattern, $link ) ) {
			$link = preg_replace( $pattern, '/' . $this->queryVar . '/', $link );

			return $link . $query . $fragment;
		}

		// remove previous pagination
		// eg: /category/category-a/page/3/p/amp/page/2/ -> /category/category-a/page/2/
		$pattern = '#\/' . $wp_rewrite->pagination_base . '\/[0-9]+\/' . $this->queryVar . '(\/' . $wp_rewrite->pagination_base . '\/[0-9]+\/?)$#i';
		$link    = preg_replace( $pattern, '/' . $this->queryVar . '$1', $link );

		// eg: /category/category-a/amp/page/2/ -> /category/category-a/page/2/
		$pattern = '#\/' . $this->queryVar . '(\/' . $wp_rewrite->pagination_base . '\/[0-9]+\/?)$#i';
		$link    = preg_replace( $pattern, '$1', $link );

		if ( is_search() ) {
			$shouldAmplify = $this->customizeConfig->isTruthy( 'item_search_display_options', 'search_amplify_pagination' );
		} else {
			$shouldAmplify = $this->customizeConfig->isTruthy( 'item_category_display_options', 'category_amplify_pagination' );
		}

		if ( ! $shouldAmplify ) {
			// drop any security token
			$pattern = '/[&?]{1}amptoken=.*$/';
			$query   = preg_replace( $pattern, '', $query );

			return $link . $query . $fragment;
		}

		// now append amp suffix
		$link = $this->getAmpUrlFromCanonical( $link );

		return $link . $query . $fragment;
	}
}

