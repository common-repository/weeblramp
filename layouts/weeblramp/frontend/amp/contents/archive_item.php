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

	/**
	 * Filter the size of the featured image to use on posts and pages.
	 *
	 * @see     https://codex.wordpress.org/Post_Thumbnails
	 *
	 * @api
	 * @package weeblrAMP\filter\output
	 * @var weeblramp_item_featured_image_size
	 * @since   1.0.0
	 *
	 * @param string $size The name of the WordPress image size to use
	 *
	 * @return string
	 */
	$size = apply_filters('weeblramp_item_featured_image_size', 'medium_large');
	$featuredImage = wbArrayGet($imgs, $size);
}

if ($customizeConfig->isTruthy('item_category_display_options', 'category_amplify_readmore'))
{
	$postLink = $this->get('router')->getAmpPagePermalink($this->getinObject('post', 'ID'));
}
else
{
	$postLink = get_permalink($this->getinObject('post', 'ID'));
}

?>
<div class="wbamp-block wbamp-content-item">
    <article id="post_<?php echo $this->getInObject('post', 'ID'); ?>"
             class="wbamp-category-item">
        <header class="wbamp-block wbamp-item-header">
            <h1><a href="<?php echo esc_url($postLink); ?>"><?php echo $this->escape($postTitle); ?></a></h1>
        </header>

		<?php

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
			?>
		<?php endif; ?>
    </article>
</div>
