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

?>
<div class="wbamp-block wbamp-content wbamp-error  wbl-no-border">
	<?php if ($this->hasDisplayData('error_title')) : ?>
        <div class="wbamp-error-title">
			<?php echo $this->get('error_title'); ?>
        </div>
	<?php endif; ?>

    <div class="wbamp-block wbamp-error-body">
        <div class="wbamp-error-description">
			<?php echo $this->get('error_body'); ?>
        </div>
	    <?php if ($this->hasDisplayData('error_image')) : ?>
            <div class="wbamp-block wbamp-error-image">
			    <?php echo $this->get('error_image'); ?>
            </div>
	    <?php endif; ?>
		<?php if ($this->hasDisplayData('error_footer')) : ?>
            <div class="wbamp-block wbamp-error-footer">
				<?php echo $this->get('error_footer'); ?>
            </div>
		<?php endif; ?>
    </div>
