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

if ($this->hasDisplayData('amp_scripts') || $this->hasDisplayData('amp_templates')):
?>
	<!-- weeblrAMP: AMP elements scripts -->
<?php
	foreach ($this->getAsArray('amp_scripts') as $element => $script) : ?>
	<script custom-element="<?php echo $this->escape($element, ENT_QUOTES); ?>" src="<?php echo WblSystem_Route::absolutify($script, WblSystem_Route::FORCE_DOMAIN); ?>" async></script>
<?php	endforeach;?>    <!-- weeblrAMP: AMP elements scripts -->
	<!-- weeblrAMP: AMP templates scripts -->
<?php
	foreach ($this->getAsArray('amp_templates') as $element => $template) : ?>
	<script custom-template="<?php echo $this->escape($element, ENT_QUOTES); ?>" src="<?php echo WblSystem_Route::absolutify($template, WblSystem_Route::FORCE_DOMAIN); ?>" async></script>
<?php	endforeach;?>    <!-- weeblrAMP: AMP templates scripts -->
<?php
endif;
