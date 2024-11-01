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

// data preparation: size
if ('hidden' == $this->getInarray('data', 'cards'))
{
	$defaultWidth = 327;
	$defaultHeight = 176;
}
else
{
	$defaultWidth = 250;
	$defaultHeight = 224;
}
$layout = $this->hasDisplayData('data', 'width') ? 'container' : 'responsive';
$layout = $this->getInArray('data', 'layout', $layout);
$width = (int) $this->getInArray('data', 'width', $defaultWidth);
$height = (int) $this->getInArray('data', 'height', $defaultHeight);

?>
<div class="wbamp-amp-tag wbamp-<?php echo $this->getInArray('data', 'type'); ?>">
	<amp-twitter width="<?php echo $width; ?>" height="<?php echo $height; ?>" layout="<?php echo $layout; ?>"
	             data-tweetid="<?php echo $this->getInArray('data', 'tweetid'); ?>"
		<?php
		if ($this->hasDisplayData('data', 'cards'))
		{
			echo ' data-cards="' . esc_attr($this->getInArray('data', 'cards')) . '"';
		}
		if ($this->hasDisplayData('data', 'theme'))
		{
			echo 'data-theme="' . esc_attr($this->getInArray('data', 'theme')) . '"';
		}
		if ($this->hasDisplayData('data', 'align'))
		{
			echo 'data-align="' . esc_attr($this->getInArray('data', 'align')) . '"';
		}
		?>>
	</amp-twitter>
</div>
