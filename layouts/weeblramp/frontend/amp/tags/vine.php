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

// data preparation
$layout = $this->hasDisplayData('data', 'width') ? 'fixed' : 'responsive';
$layout = $this->getInArray('data', 'layout', $layout);
$width = (int) $this->getInArray('data', 'width', 450);
$height = (int) $this->getInArray('data', 'height', 253);

?>

<div class="wbamp-amp-tag wbamp-<?php echo $this->getInArray('data', 'type'); ?>">
	<amp-vine width="<?php echo $width; ?>" height="<?php echo $height; ?>" layout="<?php echo $layout; ?>"
	          data-vineid="<?php echo $this->getInArray('data', 'vineid'); ?>">
	</amp-vine>
</div>
