<?php
/**
 * Class AMP_Pinterest_Embed_Handler
 *
 * @package AMP
 */

if ( ! class_exists( 'AMP_Base_Embed_Handler' ) ) {
	require_once( WEEBLRAMP_AMP__DIR__ . 'includes/embeds/class-amp-base-embed-handler.php' );
}

/**
 * Class AMP_Pinterest_Embed_Handler
 */
class AMP_Pinterest_Embed_Handler extends AMP_Base_Embed_Handler {

	const URL_PATTERN = '#https?://(www\.)?pinterest\.com/pin/.*#i';

	protected $DEFAULT_WIDTH  = 600;
	protected $DEFAULT_HEIGHT = 450;

	public function register_embed() {

		wp_embed_register_handler(
			'amp-pinterest',
			self::URL_PATTERN,
			array( $this, 'oembed' ),
			- 1 );
	}

	public function unregister_embed() {

		wp_embed_unregister_handler( 'amp-pinterest', - 1 );
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

		WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' )
		                ->addStyle( 'embed-pinterest' );

		return AMP_HTML_Utils::build_tag(
			'amp-pinterest',
			array(
				'width'    => $this->args['width'],
				'height'   => $this->args['height'],
				'data-do'  => "embedPin",
				'data-url' => $args['url'],
				'layout'   => 'responsive'
			)
		);
	}

	public function get_scripts() {

		return array(
			'amp-pinterest' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'pinterest', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
		);
	}
}
