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
 * Integration with BeaverBuilder for WP
 *
 */
class WeeblrampIntegration_BeaverBuilder extends WeeblrampClass_Integration {

	protected $id = 'beaver-builder/fl-builder.php';

	protected $filters = array(
		array(
			'filter_name'   => 'weeblramp_the_content',
			'method'        => 'filterTheContent',
			'priority'      => 10,
			'accepted_args' => 3
		),
		array(
			'filter_name' => 'weeblamp_should_scrub_regular_html_page',
			'method'      => 'filterWeeblampShouldScrubRegularHtmlPage'
		),
		array(
			'filter_name' => 'weeblramp_wpautop_function',
			'method'      => 'filterWpautopFunction'
		),
	);

	protected $actions = array();

	/**
	 * @var array Stores list of current posts
	 */
	protected $globalPosts = array();

	/**
	 * Constructor
	 *
	 * @param   array $options An array of options.
	 *
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );
	}

	/**
	 * Trigger BeaverBuilderRendering of the content.
	 *
	 * @param string  $content Raw content from the post.
	 * @param WP_Post $post Current post for the page.
	 * @param array   $pageData Full array of data available about the current page.
	 *
	 * @return string
	 */
	public function filterTheContent( $content, $post, $pageData ) {

		// add current post to list of posts
		if ( ! empty( $post ) ) {
			$this->globalPosts = array( $post->ID );
		}

		// Hook into BVB global posts filter, to trigger BVB rendering of the post.
		add_filter(
			'fl_builder_global_posts',
			array(
				$this,
				'flGlobalPosts'
			)
		);

		// trigger post rendering
		$content = FLBuilder::render_content( $content );

		// Hook into BVB global posts filter, to trigger BVB rendering of the post.
		remove_filter(
			'fl_builder_global_posts',
			array(
				$this,
				'flGlobalPosts'
			)
		);

		return $content;
	}

	/**
	 * Filter whether standard (ie non-AMP) page should be scrubbed of all weeblrAMP shortcodes.
	 * When in edit mode, we should not filter out the wbamp-* shortcodes.
	 *
	 * @param bool $shouldScrub Filter whether standard (ie non-AMP) page should be scrubbed of all weeblrAMP shortcodes.
	 *
	 * @return bool
	 */
	public function filterWeeblampShouldScrubRegularHtmlPage( $shouldScrub ) {

		if ( isset( $_GET['fl_builder'] ) ) {
			$shouldScrub = false;
		}

		return $shouldScrub;
	}

	/**
	 * Disable autop for BeaverBuilder rendering.
	 *
	 * @param callable $autopFunctionName Name of the function to use for autop handling on AMP content.
	 *
	 * @return callable
	 */
	public function filterWpautopFunction( $autopFunctionName ) {

		return '';
	}

	/**
	 * Adds current post id to list of id used by BVB. This will cause
	 * BVB to render the post content as BVB content.
	 *
	 * @param array $globalsArray
	 *
	 * @return array
	 */
	public function flGlobalPosts( $globalsArray ) {

		$globalsArray = array_merge(
			$globalsArray,
			$this->globalPosts
		);

		return $globalsArray;
	}

	/**
	 * Set default values for config.
	 *
	 * @param $config
	 *
	 * @return mixed
	 */
	public function setConfigDefaults( $config ) {

		// if not active, disable by default the integration
		if ( ! $this->active && ! empty( $this->id ) ) {
			// site name
			$config['integrations_list'][ $this->id ] = 0;
		}

		return $config;
	}

	/**
	 * Returns true if this integration is available, ie if the
	 * corresponding plugin or service is installed and activated
	 *
	 * @return bool
	 */
	protected function discover() {

		return class_exists( 'FLBuilder' );
	}
}
