<?php
/**
 * Class AMP_Instagram_Embed_Handler
 *
 * @package AMP
 */

if ( ! class_exists( 'AMP_Base_Embed_Handler' ) ) {
	require_once( WEEBLRAMP_AMP__DIR__ . 'includes/embeds/class-amp-base-embed-handler.php' );
}

/**
 * Class AMP_Instagram_Embed_Handler
 *
 * Much of this class is borrowed from Jetpack embeds
 */
class AMP_Instagram_Embed_Handler extends AMP_Base_Embed_Handler {
	const SHORT_URL_HOST = 'instagr.am';
	const URL_PATTERN = '#http(s?)://(www\.)?instagr(\.am|am\.com)/p/([^/?]+)#i';

	protected $DEFAULT_WIDTH = 600;
	protected $DEFAULT_HEIGHT = 600;

	public function register_embed() {
		wp_embed_register_handler( 'amp-instagram', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
		add_shortcode( 'instagram', array( $this, 'shortcode' ) );
	}

	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-instagram', -1 );
		remove_shortcode( 'instagram' );
	}

	public function shortcode( $attr ) {
		$url = false;

		$instagram_id = false;
		if ( isset( $attr['url'] ) ) {
			$url = trim( $attr['url'] );
		}

		if ( empty( $url ) ) {
			return '';
		}

		$instagram_id = $this->get_instagram_id_from_url( $url );

		return $this->render( array(
			'url' => $url,
			'instagram_id' => $instagram_id,
		) );
	}

	public function oembed( $matches, $attr, $url, $rawattr ) {
		return $this->render( array( 'url' => $url, 'instagram_id' => end( $matches ) ) );
	}

	public function render( $args ) {
		$args = wp_parse_args( $args, array(
			'url' => false,
			'instagram_id' => false,
		) );

		if ( empty( $args['instagram_id'] ) ) {
			return AMP_HTML_Utils::build_tag( 'a', array( 'href' => esc_url( $args['url'] ), 'class' => 'amp-wp-embed-fallback' ), esc_html( $args['url'] ) );
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-instagram',
			array(
				'data-shortcode' => $args['instagram_id'],
				'layout' => 'responsive',
				'width' => $this->args['width'],
				'height' => $this->args['height'],
			)
		);
	}

	private function get_instagram_id_from_url( $url ) {
		$found = preg_match( self::URL_PATTERN, $url, $matches );

		if ( ! $found ) {
			return false;
		}

		return end( $matches );
	}

	public function get_scripts() {

		return array(
			'amp-instagram' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'instagram', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
		);
	}
}
