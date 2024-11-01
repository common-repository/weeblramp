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

if (
	!$this->hasDisplayData('widget_name')
	||
	!is_active_sidebar($this->get('widget_name'))
)
{
	return;
}
?>
<div class="wbamp-block wbamp-widget-area <?php echo $this->getAsAttr('widget_name'); ?>" role="complementary">
	<?php
	dynamic_sidebar(
		$this->get('widget_name')
	);
	?>
</div>
