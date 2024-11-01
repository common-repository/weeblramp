<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author       weeblrPress
 * @copyright    (c) WeeblrPress - Weeblr,llc - 2020
 * @package      AMP on WordPress - weeblrAMP CE
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version      1.12.5.783
 *
 * 2020-05-19
 */

// no direct access
defined('WEEBLRAMP_EXEC') || die;

$href = "https://facebook.com/sharer.php?u=" . urlencode($this->get('share_url'));
?>
<li class="wbamp-social-button-facebook-share">
	<a class="wbamp-social-buttons"
	   id="wbamp-button_facebook_share_1"
	   href="<?php echo $href; ?>"
	   title="Facebook"
	   target="_blank">
<span class="wbamp-social-icon wbamp-facebook">
<svg width="32" height="32" viewBox="-2 -2 32 32">
	<path d="M17.9 14h-3v8H12v-8h-2v-2.9h2V8.7C12 6.8 13.1 5 16 5c1.2 0 2 .1 2 .1v3h-1.8c-1 0-1.2.5-1.2 1.3v1.8h3l-.1 2.8z"></path>
</svg>
</span>
	</a>
</li>
