<?php
/**
 * Class AMP_Facebook_Embed_Handler
 *
 * @package AMP
 */

if ( ! class_exists( 'AMP_Base_Embed_Handler' ) ) {
	require_once( WEEBLRAMP_AMP__DIR__ . 'includes/embeds/class-amp-base-embed-handler.php' );
}

/**
 * Class AMP_Facebook_Embed_Handler
 */
class AMP_Facebook_Embed_Handler extends AMP_Base_Embed_Handler {
	const URL_PATTERN = '#https?://(www\.)?facebook\.com/.*#i';

	protected $DEFAULT_WIDTH = 600;
	protected $DEFAULT_HEIGHT = 400;

	public function register_embed() {
		wp_embed_register_handler( 'amp-facebook', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
	}

	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-facebook', -1 );
	}

	public function oembed( $matches, $attr, $url, $rawattr ) {
		return $this->render( array( 'url' => $url ) );
	}

	public function render( $args ) {
		$args = wp_parse_args( $args, array(
			'url' => false,
		) );

		if ( empty( $args['url'] ) ) {
			return '';
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-facebook',
			array(
				'data-href' => $args['url'],
				'layout' => 'responsive',
				'width' => $this->args['width'],
				'height' => $this->args['height'],
			)
		);
	}

	public function get_scripts() {

		return array(
			'amp-facebook' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'facebook', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
		);
	}
}
