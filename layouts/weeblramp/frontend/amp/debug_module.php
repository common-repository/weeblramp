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

$this->get('assets_collector')->addStyle('link_to_main');

$ampUrl = $this->getAsAbsoluteUrl('amp_url');
$validateUrl = sprintf(
	$this->get('system_config')
	     ->get('urls.google.amp_validator'),
	urlencode($ampUrl)
);

$structuredDataTesterUrl = sprintf(
	$this->get('system_config')
	     ->get('urls.google.structured_data_tester'),
	urlencode($ampUrl)
);
?>
<div class="wbamp-link-to-main wbamp-link-to-main-dark">
    <span class="wbamp-link-to-main-text"></span>
    <span class="wbamp-link-to-main-link">
		<a target="_blank" href="<?php echo $validateUrl; ?>"><?php echo __('Validate AMP', 'weeblramp'); ?></a>
	</span>
    <span class="wbamp-link-to-main-link">
		<a target="_blank"
           href="<?php echo $structuredDataTesterUrl; ?>"><?php echo __('Validate Structured Data', 'weeblramp'); ?></a>
	</span>
</div>
