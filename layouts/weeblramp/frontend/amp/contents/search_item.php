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

$requestType = $this->get('request_type');

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

$showExcerpt = apply_filters('weeblramp_search_item_header_show_excerpt', $customizeConfig->isTruthy('item_search_display_options', 'search_item_header_show_excerpt'));

if ($customizeConfig->isTruthy('item_search_display_options', 'search_amplify_readmore'))
{
	$postLink = $this->get('router')->getAmpPagePermalink($this->getinObject('post', 'ID'));
}
else
{
	$postLink = get_permalink($this->getinObject('post', 'ID'));
}

// allow filtering of pagination links, which can be
// (optionally) amplified
// NB: only working for pages and post. requires a bit of work on rewrite rules
// for it to be ok in a more general way.
$shouldAmplifyPagination = $this->get('router')->shouldAmplifyPostPagination($this->getInObject('post','post_type'));
if ($shouldAmplifyPagination)
{
	// individual posts
	add_filter('wp_link_pages_link', array($this->get('router'), 'filter_wp_link_pages_link'), 10, 2);
}

?>
<div class="wbamp-block wbamp-content-item">

    <article id="post_<?php echo $this->getInObject('post', 'ID'); ?>"
             class="wbamp-search-item">
        <header class="wbamp-item-header">
            <h2><a href="<?php echo esc_url($postLink); ?>"><?php echo $this->escape($postTitle); ?></a></h2>
			<?php

			if ($showExcerpt && $this->hasDisplayData('excerpt'))
			{
				echo WblMvcLayout_Helper::render('weeblramp.frontend.amp.contents.item_description', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH);
			}

			?>

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
            <div class="wbamp-block wbamp-item-content entry-content">
                <div class="entry-summary">
					<?php
					echo $this->get('excerpt');
					?>
                </div>
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

<?php

if ($shouldAmplifyPagination)
{
	remove_filter('wp_link_pages_link', array($this->get('router'), 'filter_wp_link_pages_link'), 10);
}

