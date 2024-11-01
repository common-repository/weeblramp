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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// no direct access
defined('WEEBLRAMP_EXEC') || die;

$json = $this->get('user_config')->get('doubleclick-json');
if (!empty($json))
{
	$json = str_replace('"', '__WBAMP_ESCAPE_QUOTE_IN_ATTR__', $json);
}

$id = $this->hasDisplayData('ad_id') ? $this->getAsId('ad_id') : '1';

?>

<div class="wbamp-block wbamp-ad">
	<div class="wbamp-amp-tag wbamp-doubleclick" id="wbamp-doubleclick-<?php echo $id; ?>">
		<amp-ad width="<?php echo $this->get('user_config')->get('ad_width'); ?>"
		        height="<?php echo $this->get('user_config')->get('ad_height'); ?>"
		        type="doubleclick"
		        data-slot="<?php echo $this->get('user_config')->get('doubleclick-slot'); ?>"
			<?php
			if (!empty($json))
			{
				echo "\t\tjson=\"__WBAMP_ESCAPE_START_QUOTE_IN_ATTR__" . StringHelper::trim($json) . '__WBAMP_ESCAPE_END_QUOTE_IN_ATTR__"';
			}
			?>>
			<?php echo WblMvcLayout_Helper::render('weeblramp.frontend.amp.ads-networks.wbamp_shared', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH); ?>
		</amp-ad>
	</div>
</div>
