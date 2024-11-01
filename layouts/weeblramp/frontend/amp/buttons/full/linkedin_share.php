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

$href = 'https://www.linkedin.com/shareArticle?url=' . urlencode($this->get('share_url')) . '&title=' . urlencode($this->getInArray('metadata', 'title'));
?>

<li class="wbamp-social-button-linkedin-share">
	<a class="wbamp-social-buttons"
	   id="wbamp-button_linkedin_share_1"
	   href="<?php echo $href; ?>"
	   title="LinkedIn"
	   target="_blank">
<span class="wbamp-social-icon wbamp-linked-in">
<svg width="30" height="30" viewBox="0 0 30 30"><path d="M10.576 7.985c.865 0 1.568.703 1.568 1.568 0 .866-.703 1.57-1.568 1.57-.867 0-1.568-.704-1.568-1.57 0-.865.7-1.568 1.568-1.568zm-1.353 4.327h2.706v8.704H9.222v-8.704zm4.403 0h2.595v1.19h.038c.36-.685 1.244-1.407 2.56-1.407 2.737 0 3.243 1.803 3.243 4.147v4.774h-2.7v-4.232c0-1.01-.02-2.308-1.407-2.308-1.408 0-1.623 1.1-1.623 2.235v4.306h-2.704v-8.704"/></svg>
</span>
	</a>
</li>
