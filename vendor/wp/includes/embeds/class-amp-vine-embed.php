<?php
/**
 * Class AMP_Vine_Embed_Handler
 *
 * @package AMP
 */

if ( ! class_exists( 'AMP_Base_Embed_Handler' ) ) {
	require_once( WEEBLRAMP_AMP__DIR__ . 'includes/embeds/class-amp-base-embed-handler.php' );
}

/**
 * Class AMP_Vine_Embed_Handler
 */
class AMP_Vine_Embed_Handler extends AMP_Base_Embed_Handler {
	const URL_PATTERN = '#https?://vine\.co/v/([^/?]+)#i';

	protected $DEFAULT_WIDTH = 400;
	protected $DEFAULT_HEIGHT = 400;

	public function register_embed() {
		wp_embed_register_handler( 'amp-vine', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
	}

	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-vine', -1 );
	}

	public function oembed( $matches, $attr, $url, $rawattr ) {
		return $this->render( array( 'url' => $url, 'vine_id' => end( $matches ) ) );
	}

	public function render( $args ) {
		$args = wp_parse_args( $args, array(
			'url' => false,
			'vine_id' => false,
		) );

		if ( empty( $args['vine_id'] ) ) {
			return AMP_HTML_Utils::build_tag( 'a', array( 'href' => esc_url( $args['url'] ), 'class' => 'amp-wp-embed-fallback' ), esc_html( $args['url'] ) );
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-vine',
			array(
				'data-vineid' => $args['vine_id'],
				'layout' => 'responsive',
				'width' => $this->args['width'],
				'height' => $this->args['height'],
			)
		);
	}

	public function get_scripts() {

		return array(
			'amp-vine' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'vine', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
		);
	}
}
