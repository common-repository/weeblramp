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

$author = get_userdata($this->getInObject('post', 'post_author'));
if (empty($author->description))
{
	// no description, better hide the whole thing
	return;
}

// read and apply user configuration for display
$userConfig = $this->get('user_config');
$customizeConfig = $this->get('customize_config');
$displayConfig = $customizeConfig->get('item_display_options');

/**
 * Filter whether to display an author avatar on their bio.
 *
 * @api
 * @package weeblrAMP\filter\output
 * @var weeblramp_item_author_bio_show_avatar
 * @since   1.0.0
 *
 * @param bool $useAvatar If true, avatar is displayed in bio
 *
 * @return bool
 */
$useAvatar = apply_filters('weeblramp_item_author_bio_show_avatar', wbArrayGet($displayConfig, 'item_author_bio_show_avatar', true));
$showLinkToPosts = apply_filters('weeblramp_item_author_bio_show_link_to_posts', wbArrayGet($displayConfig, 'item_author_bio_show_link_to_posts', true));
if ($useAvatar)
{
	/**
	 * Filter the size of the author avatar displayed on an author bio.
	 *
	 * @api
	 * @package weeblrAMP\filter\output
	 * @var weeblramp_item_author_bio_avatar_size
	 * @since   1.0.0
	 *
	 * @param int $avatarSize In pixels, size of bio author avatar
	 *
	 * @return int
	 */
	$avatarSize = apply_filters(
		'weeblramp_item_author_bio_avatar_size',
		$this->get('system_config')->get('sizes.item_author_bio_avatar_default_size')
	);
	$avatar = get_avatar($author->user_email, $avatarSize);
}

?>
<div id="wbamp-author-bio" class="wbamp-block wbamp-author-bio">
    <h2 class="wbamp-author-title">
		<span class="wbamp-author"
              itemprop="name"><?php echo esc_html(sprintf('About the author, %s', $author->display_name)); ?></span>
    </h2>

	<?php if (!empty($author->description)): ?>
        <div class="wbamp-author-bio-description">
			<?php if ($useAvatar): ?>
                <div class="wbamp-author-avatar" itemprop="image"><?php echo $avatar; ?></div>
			<?php endif; ?>
            <div class="wbamp-author-description">
				<?php echo esc_html($author->description); ?>
				<?php if ($showLinkToPosts): ?>
                    <div class="wbamp-author-link">
                        <a href="<?php echo esc_url($this->get('router')->getAmpUrlFromCanonical(get_author_posts_url($author->ID))); ?>"
                           rel="author">
							<?php printf(__('View all posts by %s', 'weeblramp'), $author->display_name); ?>
                        </a>
                    </div>
				<?php endif; ?>
            </div>
        </div>
	<?php endif; ?>
</div>
