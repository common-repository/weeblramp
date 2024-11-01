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
 * Integration with Elementor for WP
 *
 */
class WeeblrampIntegration_Elementor extends WeeblrampClass_Integration {

	protected $id = 'elementor/elementor.php';

	protected $filters = array();

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

		if ( $this->active ) {
			add_filter(
				'weeblramp_the_content',
				array(
					$this,
					'filterTheContent'
				),
				10,
				3
			);
			add_action(
				'weeblramp_before_content_footer_process',
				array(
					$this,
					'actionBeforeFooterProcess'
				),
				\Elementor\Frontend::THE_CONTENT_FILTER_PRIORITY
			);
		}
	}

	/**
	 * Trigger Elementor of the content.
	 *
	 * @param string  $content Raw content from the post.
	 * @param WP_Post $post Current post for the page.
	 * @param array   $pageData Full array of data available about the current page.
	 *
	 * @return string
	 */
	public function filterTheContent( $content, $post, $pageData ) {

		if ( ! Weeblramp_Api::isAmpRequest() ) {
			return;
		}

		if ( ! empty( $post ) && ! empty( $post->ID ) ) {

			$elementor = \Elementor\Plugin::instance()->frontend;
			$elementor->remove_content_filter();
			$builderContent = $elementor->get_builder_content( $post->ID );
			if ( ! empty( $builderContent ) ) {
				$content = $builderContent;
			}
		}

		return $content;
	}

	/**
	 * Hooks just before the footer content processing.
	 *
	 * @param array $pageData Full array of information available about the current AMP page.
	 */
	public function actionBeforeFooterProcess( $pageData ) {

		if ( ! Weeblramp_Api::isAmpRequest() ) {
			return;
		}

		\Elementor\Plugin::instance()->frontend->remove_content_filter();
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

		return class_exists( '\Elementor\Plugin' );
	}
}
