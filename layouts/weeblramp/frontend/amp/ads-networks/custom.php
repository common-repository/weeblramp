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

$adContent = StringHelper::trim($this->get('user_config')->get('ads-custom-content', ''));
if (empty($adContent))
{
	return '';
}

$id = $this->hasDisplayData('ad_id') ? $this->getAsId('ad_id') : '1';
?>

<div class="wbamp-block wbamp-ad">
	<div class="wbamp-amp-tag wbamp-custom" id="wbamp-custom-<?php echo $id; ?>">
		<?php echo $adContent; ?>
	</div>
</div>
