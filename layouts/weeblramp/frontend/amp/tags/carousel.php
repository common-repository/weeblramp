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
$layout = $this->hasDisplayData('width') ? 'responsive' : 'fixed';
$subtype = $this->get('subtype', 'carousel');
if ($subtype == 'carousel' && $layout == 'responsive')
{
	// not supported yet
	$layout = 'fixed';
}

// size
$width = (int) $this->get('width', 480);
$height = (int) $this->get('height', 325);

// other
$controls = $this->hasDisplayData('controls') ? ' controls' : '';
$delay = $subtype == 'slides' && $this->hasDisplayData('delay') ? ' delay="' . $this->getAsInt('delay') . '"' : '';
$loop = $subtype == 'slides' && $this->hasDisplayData('loop') ? ' loop' : '';
$autoplay = $subtype == 'slides' && $this->hasDisplayData('autoplay') ? ' autoplay' : '';

?>

<div class="wbamp-amp-tag wbamp-<?php echo $this->get('type'); ?>">
    <amp-carousel id="wbamp-wc-carousel"
                  width="<?php echo $width; ?>" height="<?php echo $height; ?>" layout="<?php echo $layout; ?>"
                  type="<?php echo $this->get('subtype'); ?>"
		<?php echo $loop; ?>
		<?php echo $autoplay; ?>
		<?php echo $delay; ?>
		<?php echo $controls; ?>
    >
		<?php foreach ($this->getAsArray('content') as $image): ?>
            <div class="wbamp-carousel wbamp-slide">
				<?php echo $image['image']; ?>
				<?php if (!empty($image['caption'])): ?>
                    <div class="wbamp-carousel wbamp-caption">
						<?php echo $image['caption']; ?>
                    </div>
				<?php endif; ?>
            </div>
		<?php endforeach; ?>
    </amp-carousel>
	<?php if ($this->hasDisplayData('thumbnails')): ?>
        <amp-carousel class="wbamp-carousel-preview" width="auto"
                      height="<?php echo $this->getAsInt('thumbs_height'); ?>" layout="fixed-height" type="carousel">
			<?php foreach ($this->getAsArray('thumbnails') as $index => $image): ?>
				<?php echo wbArrayGet($image, 'image'); ?>
			<?php endforeach; ?>
        </amp-carousel>
	<?php endif; ?>
</div>
