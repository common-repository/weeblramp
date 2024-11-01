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
 * Updates to a standard HTML page, which has an AMP version
 */
class WeeblrampClass_View extends WeeblrampClass_Base {

	protected $layouts        = array();
	protected $__data         = null;
	protected $baseLayoutPath = null;
	protected $echoOutput     = true;
	protected $headers        = array();

	/**
	 * Constructor
	 *
	 * @param   array $options An array of options.
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );

		// add the router to all views
		$this->router = WeeblrampFactory::getThe( 'WeeblrampClass_Route' );

		// get some default values
		$this->setLayouts( wbArrayGet( $options, 'layouts', array() ) );
		$this->baseLayoutPath = wbArrayGet( $options, 'base_layout_path', WEEBLRAMP_LAYOUTS_PATH );
		$this->echoOutput     = wbArrayGet( $options, 'echo_output', $this->echoOutput );
	}

	/**
	 * Renders the view content, returning it in a string and
	 * optionally echoing it
	 */
	public function render() {

		try {
			$output = '';
			if ( ! empty( $this->layouts ) ) {
				$output = WblMvcLayout_Helper::render( $this->getLayout( 'default' ), $this->__data, $this->baseLayoutPath );
				if ( $this->echoOutput ) {
					echo $output;
				}
			}

			return $output;
		}
		catch ( Exception $e ) {
			WblWordpress_Helper::dieNicely(
				sprintf( 'Error displaying a %s contentpage', WEEBLRAMP_PLUGIN_NAME ),
				$e->getMessage(),
				array(
					'response'  => 500,
					'back_link' => true
				)
			);
		}
	}

	/**
	 * Setter for an array of layouts file names, alternative to
	 * setting it as an option in the constructor
	 *
	 * @param array $layout
	 *
	 * @return $this
	 */
	public function setLayouts( $layouts ) {

		$layouts       = is_array( $layouts ) ? $layouts : (array) $layouts;
		$this->layouts = array_merge( $this->layouts, $layouts );

		return $this;
	}

	/**
	 * Getter for a specific layout file descriptor
	 * $types:
	 *   page
	 *   body
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function getLayout( $type ) {

		$layoutFile = wbArrayGet( $this->layouts, $type, '' );

		return $layoutFile;
	}

	/**
	 * Stores data required for display, sent by dispatcher/controller
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function setDisplayData( $data ) {

		$this->__data = $data;

		return $this;
	}

	/**
	 * Store a header value, as a key/value array
	 *
	 * @param array $header key => value list of headers to output
	 *
	 * @return $this
	 */
	public function setHeader( $header ) {

		$this->headers = array_merge( $this->headers, $header );

		return $this;
	}

	/**
	 * Output headers stored up until now, unless headers
	 * have already been sent
	 *
	 * @return $this
	 */
	public function outputHeaders() {

		if ( ! headers_sent() ) {
			// run filter to collect headers
			/**
			 * Filter the list of HTTP headers included in an AMP page response
			 *
			 * @api
			 * @package weeblrAMP\filter\output
			 * @var weeblramp_response_headers
			 * @since   1.0.0
			 *
			 * @param array $headers Name => Value indexed array of headers ready to be sent
			 *
			 * @return array
			 */
			$headers = apply_filters( 'weeblramp_response_headers', $this->headers );

			// output headers
			foreach ( $headers as $name => $content ) {
				if ( 'status' == strtolower( $name ) ) {
					status_header( $content );
				} else {
					header( $name . ': ' . $content );
				}
			}
		} else {
			WblSystem_Log::error( 'weeblramp', '%s::%d %s', __METHOD__, __LINE__, 'Headers already sent!' );
		}

		return $this;
	}
}
