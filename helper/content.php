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

class WeeblrampHelper_Content {

	/**
	 * Removes all [wbamp] tags from a string (on a regular HTML page)
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public static function scrubRegularHtmlPage( $content ) {

		/**
		 * Filter whether standard (ie non-AMP) page should be scrubbed of all weeblrAMP shortcodes.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblamp_should_scrub_regular_html_page
		 * @since   1.9.0
		 *
		 * @param boolean $shouldScrub Whether the standard page should be scrubbed of all weeblrAMP shortcodes.
		 *
		 * @return string
		 */
		$shouldScrub = apply_filters(
			'weeblamp_should_scrub_regular_html_page',
			true
		);

		if ( ! $shouldScrub ) {
			return $content;
		}

		// shortcut
		if ( empty( $content ) || strpos( $content, '[wbamp-no-scrub]' ) != false ) {
			$content = str_replace( '[wbamp-no-scrub]', '', $content );

			return $content;
		}

		// remove content that should only be displayed on AMP pages
		$regExp  = '#\[\s*wbamp-show\s*start\s*\].*\[\s*wbamp-show\s*end\s*\]#iuUs';
		$content = preg_replace( $regExp, '', $content );

		// remove all remaining {wbamp tags
		$regex   = '#\[\s*wbamp([^\]]*)\]#um';
		$content = preg_replace( $regex, '', $content );

		return $content;
	}

	/**
	 * Removes all [wbamp] tags from a string (on an AMP page)
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public static function scrubAmpHtmlPage( $content ) {

		$content = str_replace( '[wbamp-no-scrub]', '', $content );

		// PHP adds a closing tag for input, which is invalid
		$content = str_replace( '</input>', '', $content );

		return $content;
	}

	/**
	 * Gather data about a post/page featured image
	 *
	 * @param $post
	 *
	 * @return array
	 */
	public static function getPostFeaturedImage( $post ) {

		$featuredImageId = get_post_thumbnail_id( $post->ID );

		return self::getImageDetails( $featuredImageId );
	}

	/**
	 * Gather data about a specific image
	 *
	 * @param int $imageId The image id
	 *
	 * @return array
	 */
	public static function getImageDetails( $imageId ) {

		$featuredImage = array();

		if ( ! empty( $imageId ) ) {
			// get atatchment details
			$featuredImageMeta = get_posts(
				array(
					'post_type'     => 'attachment',
					'numberposts'   => 1,
					'post_status'   => 'any',
					'attachment_id' => $imageId,
				)
			);
			$featuredImageMeta = ! empty( $featuredImageMeta ) && is_array( $featuredImageMeta ) ? $featuredImageMeta[0] : $featuredImageMeta;

			// get image details
			$imageMeta = wp_get_attachment_metadata( $imageId );

			$featuredImage = array(
				'id'         => $imageId,
				'meta'       => $featuredImageMeta,
				'image_meta' => $imageMeta,
				'url'        => wp_get_attachment_url( $imageId ),
				'imgs'       => array(
					'thumbnail'    => wp_get_attachment_image( $imageId, $size = 'thumbnail', $icon = false, $attr = '' ),
					'medium'       => wp_get_attachment_image( $imageId, $size = 'medium', $icon = false, $attr = '' ),
					'medium_large' => wp_get_attachment_image( $imageId, $size = 'medium_large', $icon = false, $attr = '' ),
					'large'        => wp_get_attachment_image( $imageId, $size = 'large', $icon = false, $attr = '' ),
					'full'         => wp_get_attachment_image( $imageId, $size = 'full', $icon = false, $attr = '' ),
				)
			);
		}

		return $featuredImage;
	}

	/**
	 * Compute a unique Page identifier for the passed post.
	 *
	 * We use the same syntax as Disqus, as the initial goal of this id
	 * is to identify a page, so that the same Disqus comments are displayed
	 * on both the STD and the AMP version of a page
	 *
	 * NB: Future reference: if a site was not using the Disqus plugin, but instead simply
	 * inserting the universal Disqus snippet, comments are identified by a string derived from
	 * the page URL. This is why we now specify the URL in the disqus relay iframe. In addition,
	 * the comment_location_id must be computed as follow:
	 *
	 * $uri = JUri::getInstance($currentData['canonical']);
	 * $path = $uri->getPath();
	 * $bits = explode('/', trim($path, '/'));
	 * $pageId = empty($bits) ? '/' : array_pop($bits);
	 * The id is the last segment of the path (for some reason, they also turn dashes into
	 * underscores, but that's internal, it is enough for us to pass the proper segment).
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public static function getCommentLocationId( $post ) {

		$id = '';
		if ( ! empty( $post ) && $post instanceof WP_Post ) {
			$id = $post->ID . ' ' . $post->guid;
		}

		// allow filtering
		/**
		 * Filter the id used to uniquely identify a page when associating it with its comments.
		 *
		 * We use the same convention as Disqus: post_id + a space + post_guid
		 *
		 * @api
		 * @package weeblrAMP\filter\comment
		 * @var weeblramp_comments_location_id
		 * @since   1.0.0
		 *
		 * @param string $id Current page id used for commenting
		 *
		 * @return string
		 */
		$id = apply_filters(
			'weeblramp_comments_location_id',
			$id
		);

		return $id;
	}

	/**
	 * Extract the current post being displayed from a page Data record.
	 *
	 * @param array $pageData The currently collected page data record.
	 *
	 * @return mixed|WP_Post
	 */
	public static function getPostFromPageData( $pageData ) {

		// try to build one
		$content = wbArrayGet( $pageData, 'main_content' );
		if ( is_array( $content ) ) {
			$content = array_shift( $content );
		}
		$post = wbArrayGet( $content, 'post' );

		return $post;
	}

	/**
	 * Derived from WordPress wp_tri_excerpt().
	 *
	 * Generates an excerpt from the content, if needed.
	 *
	 * The excerpt word amount will be 55 words and if the amount is greater than
	 * that, then the string ' [&hellip;]' will be appended to the excerpt. If the string
	 * is less than 55 words, then the content will be returned as is.
	 *
	 * The 55 word limit can be modified by plugins/themes using the {@see 'excerpt_length'} filter
	 * The ' [&hellip;]' string can be modified by plugins/themes using the {@see 'excerpt_more'} filter
	 *
	 * @since 1.9.1
	 *
	 * @param WP_Post $post
	 *
	 * @return string The excerpt.
	 */
	public static function getPostExcerpt( $post ) {

		$raw_excerpt = $post->post_excerpt;
		$raw_excerpt = empty( $raw_excerpt ) ? $post->post_content : $raw_excerpt;
		$text        = $raw_excerpt;

		$text = strip_shortcodes( $text );

		/** This filter is documented in wp-includes/post-template.php */
		$text = apply_filters( 'the_content', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );

		/**
		 * Filters the number of words in an excerpt.
		 *
		 * @since 2.7.0
		 *
		 * @param int $number The number of words. Default 55.
		 */
		$excerpt_length = apply_filters( 'excerpt_length', 55 );
		/**
		 * Filters the string in the "more" link displayed after a trimmed excerpt.
		 *
		 * @since 2.9.0
		 *
		 * @param string $more_string The string shown within the more link.
		 */
		$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		$text         = wp_trim_words( $text, $excerpt_length, $excerpt_more );

		/**
		 * Filters the trimmed excerpt string.
		 *
		 * @since 2.8.0
		 *
		 * @param string $text The trimmed text.
		 * @param string $raw_excerpt The text prior to trimming.
		 */
		return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt );
	}
}
