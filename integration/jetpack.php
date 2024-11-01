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

/**
 * Integration with Jetpack
 *
 * Mostly based on Automattic AMP plugin Jetpack helper
 */
class WeeblrampIntegration_Jetpack extends WeeblrampClass_Integration {

	protected $id = 'jetpack/jetpack.php';

	protected $filters = array(
		array(
			'filter_name'   => 'weeblramp_get_page_bottom_content',
			'method'        => 'filterGetStatPixel',
			'priority'      => 10,
			'accepted_args' => 1
		),
		array(
			'filter_name'   => 'weeblramp_show_sharing_buttons',
			'method'        => 'filterShowSharingButtons',
			'priority'      => 10,
			'accepted_args' => 2
		),
		array(
			'filter_name'     => 'weeblramp_amp_post_types_user_selectable_taxonomies',
			'method'          => 'filterSelectableTaxonomies',
			'priority'        => 10,
			'accepted_args'   => 1,
			'always_in_admin' => 1,
		),
		array(
			'filter_name'   => 'weeblramp_paginated_post_types',
			'method'        => 'filterPaginatedPostTypes',
			'priority'      => 10,
			'accepted_args' => 1
		),
		array(
			'filter_name'   => 'weeblramp_paginated_archived_post_types',
			'method'        => 'filterArchivedPaginatedPostTypes',
			'priority'      => 10,
			'accepted_args' => 1
		),
	);

	protected $actions = array(
		array(
			'action_name'   => 'weeblramp_print_meta_data',
			'method'        => 'jetpack_verification_print_meta',
			'priority'      => 1,
			'accepted_args' => 0
		)
	);

	/**
	 * Modules that should be disabled on AMP pages
	 *
	 * @var array
	 */
	protected $modulesToDisable = array(
		'likes',
		'minileven',
		'related-posts',
		'sharedaddy',
		'subscriptions'
	);

	/**
	 * Set of custom rewrite rules for paginated archives
	 *
	 * @var array
	 */
	protected $customRWRules = array(
		// 1010
		'1010.jetpack.portfolio'            => array(
			'rule'                => 'portfolio/{PAGINATION_BASE}/([0-9]+)/{AMP_SUFFIX}/?$',
			'rewrite'             => 'index.php?post_type=jetpack-portfolio&paged=$matches[1]&{AMP_SUFFIX}=1',
			'permalink_structure' => '',
			'position'            => 'top'
		),
		'1010.jetpack.portfolio.custom.category.postname'            => array(
			'rule'                => 'portfolio/([^/]+)(?:/([0-9]+))?/{AMP_SUFFIX}/?$',
			'rewrite'             => 'index.php?portfolio=$matches[1]&page=$matches[2]&{AMP_SUFFIX}=1',
			'permalink_structure' => WeeblrampClass_Route::PERMALINK_CUSTOM_CATNAME_POSTNAME,
			'position'            => 'top'
		),
		// 1000
		'1000.jetpack.project-type'         => array(
			'rule'                => 'project-type/([^/]+)/{PAGINATION_BASE}/([0-9]+)/{AMP_SUFFIX}/?$',
			'rewrite'             => 'index.php?jetpack-portfolio-type=$matches[1]&paged=$matches[2]&{AMP_SUFFIX}=1',
			'permalink_structure' => '',
			'position'            => 'top'
		),
		'1000.jetpack.project-type.custom.category.postname'         => array(
			'rule'                => 'project-type/([^/]+)/{AMP_SUFFIX}/?$',
			'rewrite'             => 'index.php?jetpack-portfolio-type=$matches[1]&{AMP_SUFFIX}=1',
			'permalink_structure' => WeeblrampClass_Route::PERMALINK_CUSTOM_CATNAME_POSTNAME,
			'position'            => 'top'
		),
		'990.jetpack.project-type.numeric'  => array(
			'rule'                => 'archives/project-type/([^/]+)/{PAGINATION_BASE}/([0-9]+)/{AMP_SUFFIX}/?$',
			'rewrite'             => 'index.php?jetpack-portfolio-type=$matches[1]&paged=$matches[2]&{AMP_SUFFIX}=1',
			'permalink_structure' => WeeblrampClass_Route::PERMALINK_NUMERIC,
			'position'            => 'top'
		),
		'1000.jetpack.project-type.numeric' => array(
			'rule'                => 'archives/project-type/([^/]+)/{AMP_SUFFIX}/?$',
			'rewrite'             => 'index.php?jetpack-portfolio-type=$matches[1]&{AMP_SUFFIX}=1',
			'permalink_structure' => WeeblrampClass_Route::PERMALINK_NUMERIC,
			'position'            => 'top'
		),
		'1000.jetpack.portfolio.comments'   => array(
			'rule'                => 'portfolio/([^/]+)/comment-page-([0-9]+)/{AMP_SUFFIX}/?$',
			'rewrite'             => 'index.php?portfolio=$matches[1]&cpage=$matches[2]&{AMP_SUFFIX}=1',
			'permalink_structure' => '',
			'position'            => 'top'
		),
		'1000.jetpack.portfolio.page'            => array(
			'rule'                => 'portfolio/([^/]+)/([0-9]+)/{AMP_SUFFIX}/?$',
			'rewrite'             => 'index.php?portfolio=$matches[1]&page=$matches[2]&{AMP_SUFFIX}=1',
			'permalink_structure' => '',
			'position'            => 'top'
		),
		'1000.jetpack.portfolio'            => array(
			'rule'                => 'portfolio/{AMP_SUFFIX}/?$',
			'rewrite'             => 'index.php?post_type=jetpack-portfolio&{AMP_SUFFIX}=1',
			'permalink_structure' => '',
			'position'            => 'top'
		),
	);

	/**
	 * Disable some JetPack modules. Must be done when plugins are loaded
	 */
	public function load() {

		parent::load();
		if ( $this->isEnabled() ) {
			// disable some modules
			add_filter(
				'jetpack_get_available_modules',
				array(
					$this,
					'disableUnwantedModules'
				)
			);
		}
	}

	/**
	 * Actual integration init, ran at WP init event
	 *
	 */
	public function init() {

		parent::init();

		if ( $this->isEnabled() ) {
			// disable some JetPack function that should not work well on AMP pages
		}
	}

	/**
	 * Filter out some modules we cannot have on AMP Pages
	 *
	 * from https://www.wpsitecare.com/disable-jetpack-modules/
	 *
	 * @param $modules
	 */
	public function disableUnwantedModules( $modules ) {

		$modulesToDisable = apply_filters( 'weeblramp_jetpack_modules_to_disable', $this->modulesToDisable );
		$modules          = array_diff_key(
			$modules,
			array_flip( $modulesToDisable )
		);

		return $modules;
	}

	/**
	 * Pull some data from Yoast to serve as default values for config
	 *
	 * @param $config
	 *
	 * @return mixed
	 */
	public function setConfigDefaults( $config ) {

		// if not active, disable the integration
		if ( ! $this->active && ! empty( $this->id ) ) {
			// site name
			$config['integrations_list'][ $this->id ] = 0;
		}

		return $config;
	}

	/**
	 * Filters whether a post should show sharing buttons. This is in accordance
	 * with the "Show sharing buttons" checkbox added by Jetpack to posts and pages edit page
	 *
	 * @param bool  $shouldShow
	 * @param array $rawContent
	 *
	 * @return bool
	 */
	public function filterShowSharingButtons( $shouldShow, $rawContent ) {

		if ( 1 == count( $rawContent ) && ! empty( $rawContent[0]['meta'] ) ) {
			if ( ! empty( $rawContent[0]['meta']['sharing_disabled'] ) ) {
				$shouldShow = false;
			}
		}

		return $shouldShow;
	}

	/**
	 * Filter the list of shortcodes that will be cleaned/disabled
	 * on AMP pages
	 *
	 * @see https://jetpack.com/support/shortcode-embeds/
	 *
	 * @param array $shortcodes
	 */
	public function cleanShortCodes( $shortcodes ) {

		if ( ! $this->isEnabled() ) {
			$shortcodes = array_merge(
				$shortcodes,
				array(
					// shortcode-embeds
					'blip.tv',
					'dailymotion',
					'flickr',
					'ted',
					'twitchtv',
					'vimeo',
					'vine',
					'youtube',
					'audio',
					'soundcloud',
					'bandcamp',
					'mixcloud',
					'presentation',
					'scribd',
					'instagram',
					'slideshare',
					// 'gallery', // don't remove, we can handle
					'archives',
					'carto',
					'codepen',
					'facebook',
					'gist',
					'googlemaps',
					'houzz',
					'medium',
					'polldaddy',
					'recipe',
					'twitter-timeline',
					'untappd-menu',
					'wufoo',
					'jetpack_subscription_form',
					'jetpack_top_posts_widget',
					// contact form module
					'contact-form'
				)
			);
		}

		// those shortcodes are always removed
		$shortcodes = array_merge(
			$shortcodes,
			array(
				// contact form module
				'contact-form'
			)
		);

		return $shortcodes;
	}

	/**
	 * Adds Jetpack custom post types taxonomies to the list
	 * of taxonomies user can select from to display AMP pages or not
	 *
	 * @param array $selectableTaxonomies
	 *
	 * @return array
	 */
	public function filterSelectableTaxonomies( $selectableTaxonomies ) {

		if ( is_array( $selectableTaxonomies ) ) {
			$selectableTaxonomies = array_merge(
				$selectableTaxonomies,
				array(
					'jetpack-portfolio'   => array( 'jetpack-portfolio-tag', 'jetpack-portfolio-type' ),
					'jetpack-testimonial' => array( 'jetpack-testimonial-tag', 'jetpack-testimonial-type' )
				)
			);
		}

		return $selectableTaxonomies;
	}

	/**
	 * Add Portfolio to the list of post types that can
	 * support paginated archives
	 *
	 * @param array $postTypesList
	 *
	 * @return array
	 */
	public function filterPaginatedPostTypes( $postTypesList ) {

		$postTypesList = array_merge(
			$postTypesList,
			array(
				'jetpack-portfolio'
			)
		);

		return $postTypesList;
	}

	/**
	 * Add Portfolios to the list of post types that can
	 * support paginated archives
	 *
	 * @param array $postTypesList
	 *
	 * @return array
	 */
	public function filterArchivedPaginatedPostTypes( $postTypesList ) {

		$postTypesList = array_merge(
			$postTypesList,
			array(
				'project-type'
			)
		);

		return $postTypesList;
	}

	/**
	 * Build and inject a fake pixel image, to enable Jetpacks stats
	 *
	 * @param $ogp
	 *
	 * @return mixed
	 */
	public function filterGetStatPixel( $html ) {

		// not enabled or not able to instantiate WPSEO, leave
		if ( ! $this->isEnabled() ) {
			return $html;
		}

		// whether JP added the stats action
		if ( ! has_action( 'wp_footer', 'stats_footer' ) ) {
			return $html;
		}

		// if set, build the stat pixel
		$html .= "\n" . '<amp-pixel src="' . esc_url( $this->jetpack_amp_build_stats_pixel_url() ) . '"></amp-pixel>';

		return $html;
	}

	/**
	 * From Automattic AMP plugin Jetpack helper
	 *
	 * Generate the stats pixel.
	 *
	 * Looks something like:
	 *     https://pixel.wp.com/g.gif?v=ext&j=1%3A3.9.1&blog=1234&post=5678&tz=-4&srv=example.com&host=example.com&ref=&rand=0.4107963021218808
	 */
	protected function jetpack_amp_build_stats_pixel_url() {

		global $wp_the_query;
		if ( function_exists( 'stats_build_view_data' ) ) { // added in https://github.com/Automattic/jetpack/pull/3445
			$data = stats_build_view_data();
		} else {
			$blog     = Jetpack_Options::get_option( 'id' );
			$tz       = get_option( 'gmt_offset' );
			$v        = 'ext';
			$blog_url = parse_url( site_url() );
			$srv      = $blog_url['host'];
			$j        = sprintf( '%s:%s', JETPACK__API_VERSION, JETPACK__VERSION );
			$post     = $wp_the_query->get_queried_object_id();
			$data     = compact( 'v', 'j', 'blog', 'post', 'tz', 'srv' );
		}

		$data['host'] = rawurlencode( $_SERVER['HTTP_HOST'] );
		$data['rand'] = 'RANDOM'; // amp placeholder
		$data['ref']  = 'DOCUMENT_REFERRER'; // amp placeholder
		$data         = array_map( 'rawurlencode', $data );

		return add_query_arg( $data, 'https://pixel.wp.com/g.gif' );
	}

	/**
	 * Returns true if this integration is available, ie if the
	 * corresponding plugin or service is installed and activated
	 *
	 * @return bool
	 */
	protected function discover() {

		return class_exists( 'Jetpack' ) && ! ( defined( 'IS_WPCOM' ) && IS_WPCOM );
	}
}
