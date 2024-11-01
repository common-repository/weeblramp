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

// read and apply user configuration for display
$userConfig      = $this->get( 'user_config' );
$customizeConfig = $this->get( 'customize_config' );

/**
 * Filter whether to display comments reply_to link.
 *
 * Note: reply_to links are not AMPlified, they will take back user to regular version of the site.
 *
 * @api
 * @package weeblrAMP\filter\comment
 * @var weeblramp_comments_show_reply_to
 * @since   1.0.0
 *
 * @param bool $showReplyTo If true, comments reply_to links are displayed on the page
 *
 * @return bool
 */
$showReplyTo = apply_filters( 'weeblramp_comments_show_reply_to', $customizeConfig->isTruthy( 'comments_display_options', 'show_reply_to', true ) );

/**
 * Filter whether to display user avatars on comments.
 *
 * @api
 * @package weeblrAMP\filter\comment
 * @var weeblramp_comments_show_avatar
 * @since   1.0.0
 *
 * @param bool $useAvatar If true, commenter avatars are displayed on the page
 *
 * @return bool
 */
$useAvatar = apply_filters( 'weeblramp_comments_show_avatar', $customizeConfig->isTruthy( 'comments_display_options', 'show_avatar', true ) );

/**
 * Filter the size of avatars displayed on comments.
 *
 * @api
 * @package weeblrAMP\filter\comment
 * @var weeblramp_comments_avatar_size
 * @since   1.0.0
 *
 * @param int $avatarSize Avatar size in pixels.
 *
 * @return int
 */
$avatarSize = apply_filters( 'weeblramp_comments_avatar_size', 24 );

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

$hasContent =
	have_comments()
	||
	( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) )
	||
	$showReplyTo && comments_open();

if ( $hasContent ): ?>
    <div id="comments" class="wbamp-block wbamp-comments wbamp-comments-wp">
<?php endif; ?>

<?php if ( have_comments() ) : ?>
    <div class="comments-title">
		<?php
		$comments_number = get_comments_number();
		if ( 1 === $comments_number ) {
			/* translators: %s: post title */
			printf( _x( 'One comment on &ldquo;%s&rdquo;', 'comments title', 'weeblramp' ), get_the_title() );
		} else {
			printf(
			/* translators: 1: number of comments, 2: post title */
				_nx(
					'%1$s comment on &ldquo;%2$s&rdquo;',
					'%1$s comments on &ldquo;%2$s&rdquo;',
					$comments_number,
					'comments title',
					'weeblramp'
				),
				number_format_i18n( $comments_number ),
				get_the_title()
			);
		}
		?>
    </div>

	<?php
	$router = $this->get( 'router' );
	add_filter(
		'get_comments_pagenum_link',
		function ( $link ) use ( $router ) {

			$link = $router->getAmpUrlFromCanonical( $link );

			return $link;
		},
		10,
		4
	);

	the_comments_navigation(
		array(
			'prev_text' => '«&nbsp;' . __( 'Older comments', 'weeblramp' ),
			'next_text' => __( 'Newer comments', 'weeblramp' ) . '&nbsp;»'
		)
	);

	?>

    <ul class="comment-list">
		<?php

		// optionally nuke the reply to specific comment link
		// which has a link to standard HTML
		if ( $customizeConfig->isFalsy( 'comments_display_options', 'show_reply_to_a_comment' ) ) {
			add_filter(
				'comment_reply_link',
				function ( $link, $args, $comment, $post ) {

					return '';
				},
				10,
				4
			);
		}

		wp_list_comments(
			array(
				'style'       => 'ul',
				'format'      => 'html5',
				// only comments, we don't want trackbacks on the amp page (except in standalone mode)
				'type'        => $this->get( 'router' )->isStandaloneMode() ? 'all' : 'comment',
				'short_ping'  => false,
				'avatar_size' => $useAvatar ? 24 : 0,
				'echo'        => true,
			)
		);

		?>
    </ul><!-- .comment-list -->

	<?php
	the_comments_navigation(
		array(
			'prev_text' => '«&nbsp;' . __( 'Older comments' ),
			'next_text' => __( 'Newer comments' ) . '&nbsp;»'
		)
	);
	?>

<?php endif; ?>

<?php
// If comments are closed and there are comments, let's leave a little note, shall we?
if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
    <p class="no-comments"><?php echo __( 'Comments are closed.', 'weeblramp' ); ?></p>
<?php endif; ?>

<?php
// there are comments, or we comments are opened, we are now sure we'll need the CSS
$this->get( 'assets_collector' )
     ->addStyle(
	     'comments'
     )->addStyle(
		'form'
	);
$commentFormFile = wbSlashJoin( __DIR__, WeeblrampHelper_Version::getEdition(), 'item_comments_wp_form.php' );
if ( file_exists( $commentFormFile ) ) {
	include_once $commentFormFile;
}

?>

<?php if ( $hasContent ): ?>
    </div>
<?php endif;
