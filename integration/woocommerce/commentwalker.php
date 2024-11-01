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

// no direct access
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Comment API: Walker_Comment class
 *
 * @package WordPress
 * @subpackage Comments
 * @since 4.4.0
 */

/**
 * Core walker class used to create an HTML list of comments.
 *
 * @since 2.7.0
 *
 * @see Walker
 */
class WeeblrampIntegrationWoocommerce_Commentwalker extends Walker_Comment {

	/**
	 * Outputs a comment in the HTML5 format.
	 *
	 * @since 3.6.0
	 * @access protected
	 *
	 * @see wp_list_comments()
	 *
	 * @param WP_Comment $comment Comment to display.
	 * @param int        $depth Depth of the current comment.
	 * @param array      $args An array of arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {

		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
		?>
        <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent' : '', $comment ); ?>>
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
            <footer class="comment-meta">
                <div class="comment-author vcard">
					<?php if ( 0 != $args['avatar_size'] ) {
						echo get_avatar( $comment, $args['avatar_size'] );
					} ?>
					<?php
					/**
					 * Filter HTML output displaying a single comment author.
					 *
					 * @api
					 * @package weeblrAMP\filter\output
					 * @var weeblramp_wc_review_author_html
					 * @since   1.12.1
					 *
					 * @param string     $authorHtml The rendered HTML to display a comment/review.
					 * @param WP_Comment $comment The comment/review being rendered.
					 *
					 * @return string
					 */
					echo apply_filters(
						'weeblramp_wc_review_author_html',
						sprintf( __( '%s<span class="says"> says:</span>', 'weeblramp' ), sprintf( '<b class="fn">%s</b>', get_comment_author_link( $comment ) ) ),
						$comment
					);
					?>
                </div><!-- .comment-author -->

                <div class="comment-metadata">
                    <time datetime="<?php comment_time( 'c' ); ?>">
						<?php
						/**
						 * Filter HTML output displaying a single comment date and time.
						 *
						 * @api
						 * @package weeblrAMP\filter\output
						 * @var weeblramp_wc_review_date_time_html
						 * @since   1.12.1
						 *
						 * @param string     $datetimeHtml The rendered HTML to display a comment/review date and time.
						 * @param WP_Comment $comment The comment/review being rendered.
						 *
						 * @return string
						 */
						echo apply_filters(
							'weeblramp_wc_review_date_time_html',
							/* translators: 1: comment date, 2: comment time */
							sprintf( __( '%1$s at %2$s', 'weeblramp' ), get_comment_date( '', $comment ), get_comment_time() ),
							$comment
						);
						?>
                    </time>
                </div><!-- .comment-metadata -->
            </footer><!-- .comment-meta -->

			<?php
			$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
			$full   = str_repeat( '★', (int) $rating );
			$empty  = str_repeat( '☆', 5 - (int) $rating );

			// disabled until we have an appropriate font for this
			if ( $rating && get_option( 'woocommerce_enable_review_rating' ) === 'yes' ):

				// required CSS
				WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' )
				                ->addStyle( 'wc.ratings' );
				?>
                <div class="comment-rating"
                     title="<?php echo sprintf( esc_attr__( 'Rated %d out of 5', 'weeblramp' ), esc_attr( $rating ) ) ?>">
						<span class="wbamp-wc-rating">
                                <?php echo $full . $empty; ?>
                            </span>
                    </span>
                </div>
			<?php endif; ?>

            <div itemprop="description" class="comment-content">
				<?php comment_text(); ?>
            </div><!-- .comment-content -->
        </article><!-- .comment-body -->
		<?php
	}
}
