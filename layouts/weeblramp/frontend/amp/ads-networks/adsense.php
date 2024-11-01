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

$id = $this->hasDisplayData('ad_id') ? $this->getAsId('ad_id') : '1';
$autoFormat = $this->get('user_config')->get('adsense-ad-auto-format');
$autoFormat = empty($autoFormat) ? '' : 'data-auto-format="' . esc_attr($autoFormat) . '"';
$fullWidth  = $this->get('user_config')->get('adsense-ad-full-width', '');
$fullWidth  = empty($fullWidth) ? '' : 'data-full-width';
?>

<div class="wbamp-block wbamp-ad">
	<div class="wbamp-amp-tag wbamp-adsense" id="wbamp-adsense-<?php echo $id; ?>">
		<amp-ad width="<?php echo $this->get('user_config')->get('ad_width'); ?>"
		        height="<?php echo $this->get('user_config')->get('ad_height'); ?>"
		        type="adsense"
		        data-ad-client="<?php echo $this->get('user_config')->get('adsense-ad-client'); ?>"
		        data-ad-slot="<?php echo $this->get('user_config')->get('adsense-ad-slot'); ?>"
			<?php echo $autoFormat; ?>
			<?php echo $fullWidth; ?>>
            <div overflow></div>
			<?php echo WblMvcLayout_Helper::render('weeblramp.frontend.amp.ads-networks.wbamp_shared', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH); ?>
		</amp-ad>
	</div>
</div>
