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
defined('WEEBLRAMP_EXEC') || die;

// read and apply user configuration for display
$isPasswordProtected = post_password_required($this->get('post'));
$postTitle = WblWordpress_Html::getPostTitle($this->get('post'));

$userConfig = $this->get('user_config');
$customizeConfig = $this->get('customize_config');
$showFeaturedImage = true;
if ($showFeaturedImage)
{
	$imgs = $this->getInArray('featured_image', 'imgs');
	$size = apply_filters('weeblramp_item_featured_image_size', 'medium_large');
	$featuredImage = wbArrayGet($imgs, $size);
}

/**
 * Filter whether to display comments on the home page.
 *
 * Note: this can be set by user in settings
 *
 * @api
 * @package weeblrAMP\filter\comment
 * @var weeblramp_comments_show_on_home_page
 * @since   1.0.0
 *
 * @param bool    $showComments If true, comments are displayed on the page
 * @param string  $requestType  Request type descriptor: page, post, home, search,...
 * @param WP_Post $post         The global post object
 * @param string  $commentType  The comment provider name (built-in, Disqus, ...)
 *
 * @return bool
 */
$showComments = apply_filters(
	'weeblramp_comments_show_on_home_page',
	false,
	$this->get('request_type'),
	$this->get('post'),
	$this->get('comment_type')
);

if ($customizeConfig->isTruthy('home_display_options', 'amplify_readmore'))
{
	$postLink = $this->get('router')
	                 ->getAmpPagePermalink(
		                 $this->getinObject('post', 'ID')
	                 );
}
else
{
	$postLink = get_permalink($this->getinObject('post', 'ID'));
}

?>
<div class="wbamp-block wbamp-content-item">
    <article id="post_<?php echo $this->getInObject('post', 'ID'); ?>"
             class="wbamp-category-item">
		<?php if ($customizeConfig->isTruthy('home_display_options', 'show_title')): ?>
            <header class="wbamp-block wbamp-item-header">
                <h1><a href="<?php echo esc_url($postLink); ?>"><?php echo $this->escape($postTitle); ?></a></h1>
            </header>
			<?php
		endif;

		if ($isPasswordProtected):
			// specific format for password protected posts
			echo WblMvcLayout_Helper::render('weeblramp.frontend.amp.contents.item_pwd', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH);
		else: ?>
			<?php if (!empty($featuredImage)): ?>
                <div class="wbamp-featured-image">
					<?php echo $featuredImage; ?>
                </div>
			<?php endif; ?>
            <div class="wbamp-item-content entry-content">
				<?php
				echo $this->get('content');
				?>
            </div>

			<?php
			wp_link_pages(
				array(
					'before' => '<div class="wbamp-block wbamp-page-links"><span class="wbamp-page-links-title">' . __('Page', 'weeblramp') . '</span>',
					'after' => '</div>',
					'link_before' => '<span>',
					'link_after' => '</span>',
					'pagelink' => '<span class="screen-reader-text">' . __('Page', 'weeblramp') . ' </span>%',
					'separator' => '',
				)
			);

			if ($showComments)
			{
				echo
				$this->get('content_protector')
				     ->protect(
					     WblMvcLayout_Helper::render('weeblramp.frontend.amp.contents.item_comments_' . $this->get('comment_type'), $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH),
					     array(
						     $this->get('amp_form_processor'),
						     'convert'
					     )
				     );
			}

			?>
		<?php endif; ?>
    </article>
</div>
