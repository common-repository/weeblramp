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

/**
 * Using parameters:
 *
 * if user set a width, we do not use layout = responsive (otherwise the default)
 * as this cause width parameter to be ignored
 * if a layout has been set by user, this override the calculated one
 */

$layout = $this->hasDisplayData('data', 'width') ? 'fixed' : 'responsive';
$layout = $this->getInArray('data', 'layout', $layout);
$width = (int) $this->getInArray('data', 'width', 300);
$height = (int) $this->getInArray('data', 'height', 200);

$isVideo = 'videos' == $this->getInArray('data', 'subtype');
$href = 'https://www.facebook.com/' . $this->getInArray('data', 'user') . '/' . $this->getInArray('data', 'subtype') . '/' . $this->getInArray('data', 'id');
?>

<div class="wbamp-amp-tag wbamp-<?php echo $this->getInArray('data', 'type'); ?>">
	<amp-facebook width="<?php echo $width; ?>" height="<?php echo $height; ?>" layout="<?php echo $layout; ?>"
	              data-href="<?php echo $href; ?>"
		<?php
		if ($isVideo)
		{
			echo 'data-embed-as="video"';
		}
		?>>
	</amp-facebook>
</div>
