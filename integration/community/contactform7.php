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
 * Integration with Contact Form 7
 *
 */
class WeeblrampIntegration_Contactform7 extends WeeblrampClass_Integration {

	protected $id = 'contact-form-7/wp-contact-form-7.php';

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
	 * Storage for CF7 built-in shortcode handler
	 *
	 * @var Callable
	 */
	private $cf7Handler = null;

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
	 * Replace CF7 shortcode handler with our so as
	 * to be able to wrap CF7 HTML output into a wrapper
	 * allowing proper AMP conversion
	 */
	public function actionReplaceShortCodes() {

		global $shortcode_tags;

		if ( ! Weeblramp_Api::isAmpRequest() ) {
			return;
		}

		// store current handler
		$this->cf7Handler = wbArrayGet( $shortcode_tags, 'contact-form-7' );

		// replace original handler with our
		add_shortcode(
			'contact-form-7',
			array(
				$this,
				'weeblramp_wpcf7_contact_form_tag_func'
			)
		);
		add_shortcode(
			'contact-form',
			array(
				$this,
				'weeblramp_wpcf7_contact_form_tag_func'
			)
		);
	}

	public function weeblramp_wpcf7_contact_form_tag_func( $atts, $content = null, $code = '' ) {

		if ( empty( $this->cf7Handler ) || ! is_callable( $this->cf7Handler ) ) {
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

		return class_exists( 'WPCF7' );
	}
}
