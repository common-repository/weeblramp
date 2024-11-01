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
 * Integration with Mailchimp for WP
 *
 */
class WeeblrampIntegration_Mailchimpforwp extends WeeblrampClass_Integration {

	protected $id = 'mailchimp-for-wp/mailchimp-for-wp.php';

	protected $filters = array(
		array(
			'filter_name'   => 'weeblramp_config_set_defaults',
			'method'        => 'setConfigDefaults',
			'priority'      => 10,
			'accepted_args' => 1
		),
	);

	protected $actions = array(
		array(
			'action_name' => 'wp',
			'method'      => 'actionReplaceShortCodes',
			'priority'    => 100
		),
	);

	/**
	 * Storage for MC4WP built-in shortcode handler
	 *
	 * @var Callable
	 */
	private $handler = null;

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
	 * Replace MC4WP shortcode handler with our so as
	 * to be able to wrap MC4WP HTML output into a wrapper
	 * allowing proper AMP conversion
	 */
	public function actionReplaceShortCodes() {

		global $shortcode_tags;

		if ( ! Weeblramp_Api::isAmpRequest() ) {
			return;
		}

		// store current handler
		$this->handler = wbArrayGet( $shortcode_tags, 'mc4wp_form' );

		// replace original handler with our
		add_shortcode(
			'mc4wp_form',
			array(
				$this,
				'weeblramp_mc4wp_form_handler'
			)
		);
	}

	public function weeblramp_mc4wp_form_handler( $atts, $content = null, $code = '' ) {

		if ( empty( $this->handler ) || ! is_callable( $this->handler ) ) {
			return $content;
		}

		return '';
	}

	/**
	 * Pull some data from Yoast to serve as default values for config
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

		return class_exists( 'MC4WP_Form_Manager' );
	}
}
