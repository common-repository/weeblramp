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

use Weeblr\Wblib\Joomla\Uri\Uri;

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * A few specific helpers
 */
class WblWordpress_Html {

	/**
	 * Storage for post display globals
	 * @var array
	 */
	private static $globals = array();

	/**
	 * Fetch a post title, possibly inserting the Protected string
	 *
	 * @param WP_POST $post
	 *
	 * @return string
	 */
	public static function getPostTitle( $post ) {

		$isPasswordProtected = post_password_required( $post );
		$postTitle           = $post->post_title;
		if ( $isPasswordProtected ) {
			$postTitle = self::getProtectedTitle( $postTitle, $post );
		}

		return $postTitle;
	}

	/**
	 * Build the post title for a password protected post
	 *
	 * @param string  $title
	 * @param WP_POST $post
	 *
	 * @return string
	 */
	public static function getProtectedTitle( $title, $post ) {

		$protected_title_format = apply_filters( 'protected_title_format', __( 'Protected: %s' ), $post );
		$title                  = sprintf( $protected_title_format, $title );

		return $title;
	}

	/**
	 * Find and filter a post excerpt
	 *
	 * @param int | WP_POST $post
	 * @param bool          $filter
	 * @param bool          $forceExcerpt
	 *
	 * @return mixed|null|void
	 */
	public static function getTheExcerpt( $post, $filter = true, $forceExcerpt = false ) {

		$excerpt    = '';
		$postId     = empty( $post ) ? null : $post->ID;
		$hasExcerpt = has_excerpt( $postId );

		if ( $hasExcerpt ) {
			$excerpt = $post->post_excerpt;
		}

		if ( $forceExcerpt && ! $hasExcerpt ) {
			// search: we require an excerpt
			$excerpt = $post->post_content;
		}

		if ( ! empty( $excerpt ) || $forceExcerpt ) {
			$excerpt = wp_trim_words(
				$excerpt,
				apply_filters( 'excerpt_length', 55 ),
				''
			);

			// insert html to be able to style Continue reading link
			if ( $forceExcerpt ) {
				// detect tag
				$readMoreLinkText = sprintf(
					__( 'View more<span class="screen-reader-text"> "%s"</span>', 'weeblramp' ),
					WblWordpress_Html::getPostTitle( $post )
				);

				$customizeConfig = WeeblrampFactory::getThe( 'weeblramp.config.customize' );
				$router          = WeeblrampFactory::getThe( 'WeeblrampClass_Route' );
				if ( $customizeConfig->isTruthy( 'item_search_display_options', 'search_amplify_readmore' ) ) {
					$postLink = $router->getAmpPagePermalink( $postId );
				} else {
					$postLink = get_permalink( $postId );
				}
				/**
				 * Filters the Read More link text.
				 *
				 * @since 2.8.0
				 *
				 * @param string $more_link_element Read More link element.
				 * @param string $more_link_text Read More text.
				 */
				$link = apply_filters(
					'the_content_more_link',
					'<div class="wbamp-readmore"><a href="' . $postLink . '" class="more-link">' . $readMoreLinkText . '&nbsp;&nbsp;&raquo;</a></div>',
					$readMoreLinkText
				);

				$excerpt = force_balance_tags( $excerpt ) . $link;
			}
		}

		return $filter ? apply_filters( 'the_excerpt', $excerpt ) : $excerpt;
	}

	/**
	 * Applies the_content filter to a piece of html content
	 *
	 * @param string $content
	 *
	 * @return mixed|null|void
	 */
	public static function getTheContent( $content ) {

		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		return $content;
	}

	/**
	 * Set WP globals based on what we just found
	 * this will let things like wp_link_pages work normally
	 *
	 * @param array $postRecord
	 *
	 * @return void
	 **/
	public static function exportToGlobals( $postRecord ) {

		global $page, $numpages, $multipage, $more, $post, $wp_query;

		// built-in content export
		self::$globals['post']      = $post;
		self::$globals['page']      = $page;
		self::$globals['numpages']  = $numpages;
		self::$globals['multipage'] = $multipage;
		self::$globals['more']      = $more;
		self::$globals['wp_query']  = $wp_query;

		$post      = $postRecord['post'];
		$page      = $postRecord['page'];
		$numpages  = $postRecord['numpages'];
		$multipage = $postRecord['multipage'];
		$more      = $postRecord['more'];

		// let integrations also export globals
		/**
		 * Action when exporting all data to globals before rendering an AMP page.
		 *
		 * @api
		 * @package weeblrAMP\action\system
		 * @var weeblramp_export_globals
		 * @since   1.0.0
		 *
		 * @param array $globals Value of globals before weeblrAMP exported its own values
		 * @param array $postRecord Array of global data for the current page post/page
		 *
		 */
		do_action( 'weeblramp_export_globals', self::$globals, $postRecord );
	}

	public static function resetGlobals() {

		global $page, $numpages, $multipage, $more, $post, $wp_query;

		// let integrations reset globals
		/**
		 * Action when restoring all data exported to globals before rendering an AMP page.
		 *
		 * @api
		 * @package weeblrAMP\action\system
		 * @var weeblramp_reset_globals
		 * @since   1.0.0
		 *
		 * @param array $globals Array of data stored before exporting, will be restored to globals
		 *
		 */
		do_action( 'weeblramp_reset_globals', self::$globals );

		// built in content reset
		$post      = self::$globals['post'];
		$page      = self::$globals['page'];
		$numpages  = self::$globals['numpages'];
		$multipage = self::$globals['multipage'];
		$more      = self::$globals['more'];
		$wp_query  = self::$globals['wp_query'];
	}

	/**
	 * Let WP process comments, so that they are ready and stored
	 * in global variables. We will later on display them ourselves
	 */
	public static function processComments() {

		// comments processing, using existing comment-template function
		ob_start();
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		ob_end_clean();
	}
}
