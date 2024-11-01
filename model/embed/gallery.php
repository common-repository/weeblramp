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

if ( ! class_exists( 'AMP_Base_Embed_Handler' ) ) {
	require_once( WEEBLRAMP_AMP__DIR__ . 'includes/embeds/class-amp-base-embed-handler.php' );
}

/**
 * Implements handling of "gallery" Wordpress built-in shortcode
 * Turn a gallery into a carousel (slides type), also using
 * images caption if some exist
 *
 * Class WeeblrampModelEmbed_Gallery
 */
class WeeblrampModelEmbed_Gallery extends AMP_Base_Embed_Handler {

	protected $customizeConfig = null;
	private   $hasCaption      = false;

	/**
	 * Constructor
	 *
	 * @param   array $options An array of options.
	 *
	 */
	public function __construct( $args = array() ) {

		parent::__construct( $args );

		// store user and system config, needed pretty much everywhere
		$this->customizeConfig = wbArrayGet( $args, 'customize_config', WeeblrampFactory::getThe( 'weeblramp.config.customize' ) );
	}

	public function register_embed() {

		add_shortcode( 'gallery', array( $this, 'shortcode' ) );
	}

	public function unregister_embed() {

		remove_shortcode( 'gallery' );
	}

	public function get_scripts() {

		if ( ! $this->did_convert_elements ) {
			return array();
		}

		$scripts = array(
			'amp-carousel' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'carousel', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
		);
		if ( ! empty( $this->hasCaption ) ) {
			$scripts['amp-fit-text'] = sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'fit-text', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION );
		}

		return $scripts;
	}

	/**
	 * Method added to AMP base class, allow collecting CSS
	 * on top of scripts
	 * This will load the corresponding weeblrAMP CSS package,
	 * it's not meant to return actual CSS
	 *
	 * One can append CSS to the page using the weeblramp_theme_css filter.
	 *
	 * @return array
	 */
	public function weeblramp_get_styles() {

		if ( ! $this->did_convert_elements ) {
			return array();
		}

		return array( 'carousel' );
	}

	public function shortcode( $attr ) {

		$post = get_post();

		if ( ! empty( $attr['ids'] ) ) {
			// 'ids' is explicitly ordered, unless you specify otherwise.
			if ( empty( $attr['orderby'] ) ) {
				$attr['orderby'] = 'post__in';
			}
			$attr['include'] = $attr['ids'];
		}

		$atts = shortcode_atts(
			array(
				'order'                 => 'ASC',
				'orderby'               => 'menu_order ID',
				'id'                    => $post ? $post->ID : 0,
				'include'               => '',
				'exclude'               => '',
				'size'                  => array( $this->args['width'], $this->args['height'] ),
				'amp_delay'             => '',
				'amp_loop'              => '',
				'amp_autoplay'          => '',
				'amp_controls'          => '',
				'amp_type'              => 'slides',
				'amp_with_thumbnails'   => (bool) $this->customizeConfig->get( 'galleries_show_preview' ),
				'amp_thumbnails_width'  => $this->customizeConfig->get( 'galleries_preview_image_width' ),
				'amp_thumbnails_height' => $this->customizeConfig->get( 'galleries_preview_image_height' )
			),
			$attr,
			'gallery'
		);

		$id = intval( $atts['id'] );

		if ( ! empty( $atts['include'] ) ) {
			$attachments = get_posts(
				array(
					'include'        => $atts['include'],
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $atts['order'],
					'orderby'        => $atts['orderby'],
					//'fields' => 'ids',
				)
			);
		} elseif ( ! empty( $atts['exclude'] ) ) {
			$attachments = get_children(
				array(
					'post_parent'    => $id,
					'exclude'        => $atts['exclude'],
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $atts['order'],
					'orderby'        => $atts['orderby'],
					//'fields' => 'ids',
				)
			);
		} else {
			$attachments = get_children(
				array(
					'post_parent'    => $id,
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $atts['order'],
					'orderby'        => $atts['orderby'],
					//'fields' => 'ids',
				)
			);
		}

		if ( empty( $attachments ) ) {
			return '';
		}

		$urls           = array();
		$numberOfImages = count( $attachments );

		// compute an image width/height ratio so as to be able
		// to guess the proper carousel dimensions.
		// we use responsive layouts, and so we need a proper aspect ratio
		// We get it from the first image, as there is not really
		// anything better to do
		// @TODO: evaluate storing the largest width and height from all the
		// images to be displayed, set a the carousel dimensions to that
		// and use the "fill" layout instead. It may give better results in some cases
		// so we may make this a parameter?
		$ratio = null;
		foreach ( $attachments as $attachment ) {
			list( $url, $width, $height ) = wp_get_attachment_image_src( $attachment->ID, $atts['size'], true );

			if ( ! $url ) {
				continue;
			}

			$urlRecord = array(
				'url'     => $url,
				'width'   => $width,
				'height'  => $height,
				'caption' => $attachment->post_excerpt
			);

			if ( is_null( $ratio ) && ! empty( $height ) ) {
				$ratio = $width / $height;
			}

			if ( ! empty( $atts['amp_with_thumbnails'] ) && $numberOfImages > 1 ) {
				list( $url, $width, $height ) = wp_get_attachment_image_src( $attachment->ID, 'thumbnail', true );

				if ( ! $url ) {
					continue;
				}

				$thumb = array(
					'url'    => $url,
					'width'  => $width,
					'height' => $height,
				);

				$urlRecord['thumbnail'] = $thumb;
			}

			$urls[] = $urlRecord;
		}

		if ( ! empty( $ratio ) ) {
			$this->args['height'] = (int) ( $this->args['width'] / $ratio );
		}

		return $this->render(
			array(
				'images'        => $urls,
				'thumbs_width'  => (int) $atts['amp_thumbnails_width'],
				'thumbs_height' => (int) $atts['amp_thumbnails_height']
			),
			$atts
		);
	}

	public function render( $args, $attributes ) {

		$this->did_convert_elements = true;

		$args = wp_parse_args(
			$args,
			array(
				'images' => false,
			)
		);

		if ( empty( $args['images'] ) ) {
			return '';
		}

		$images = array();
		$thumbs = array();
		foreach ( $args['images'] as $index => $image ) {
			$images[] = $this->makeImage( $image );
			$thumb    = wbArrayGet( $image, 'thumbnail' );
			if ( ! empty( $thumb ) ) {
				$extraAttr = array(
					'on'       => 'tap:wbamp-wc-carousel.goToSlide(index=' . $index . ')',
					'role'     => 'button',
					'layout'   => 'fixed',
					'width'    => $args['thumbs_width'],
					'height'   => $args['thumbs_height'],
					'tabindex' => $index
				);
				$thumbs[]  = $this->makeImage( $thumb, $extraAttr );
			}
		}

		return WblMvcLayout_Helper::render(
			'weeblramp.frontend.amp.tags.carousel',
			array(
				'width'         => $this->args['width'],
				'height'        => $this->args['height'],
				'type'          => 'carousel',
				'subtype'       => isset( $attributes['amp_type'] ) ? $attributes['amp_type'] : null,
				// slides | carousel
				'layout'        => 'responsive',
				'delay'         => isset( $attributes['amp_delay'] ) ? $attributes['amp_delay'] : null,
				'loop'          => isset( $attributes['amp_loop'] ) ? $attributes['amp_loop'] : null,
				'autoplay'      => isset( $attributes['amp_autoplay'] ) ? $attributes['amp_autoplay'] : null,
				'controls'      => isset( $attributes['amp_controls'] ) ? $attributes['amp_controls'] : null,
				'content'       => $images,
				'thumbnails'    => $thumbs,
				'thumbs_height' => $args['thumbs_height'],
				'thumbs_width'  => $args['thumbs_width']
			),
			WEEBLRAMP_LAYOUTS_PATH
		);
	}

	/**
	 * Builds an array of valid AMP html to display an image
	 * possibly with a caption
	 *
	 * @param array $image
	 * @param array $extrAttr associative array of extra attributes for the amp-img
	 *
	 * @return array
	 */
	private function makeImage( $image, $extrAttr = array() ) {

		$imageHtml = AMP_HTML_Utils::build_tag(
			'amp-img',
			array_merge(
				array(
					'src'    => $image['url'],
					'width'  => $image['width'],
					'height' => $image['height'],
					'layout' => 'responsive',
				),
				$extrAttr
			)
		);

		if ( ! empty( $image['caption'] ) ) {
			$this->hasCaption = true;
			$captionHtml      = AMP_HTML_Utils::build_tag(
				'amp-fit-text',
				array(
					'width'         => $image['width'] - 50,
					'height'        => (int) ( $image['height'] / 4 ),
					'layout'        => 'responsive',
					'max-font-size' => '12'
				),
				$image['caption']
			);
		} else {
			$captionHtml = '';
		}

		return array(
			'image'   => $imageHtml,
			'caption' => $captionHtml
		);
	}
}
