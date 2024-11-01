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

// no text, no show
if (!$this->hasDisplayData('lb_id'))
{
	return '';
}

// load assets
$this->get('assets_collector')
     ->addScripts(
	     array(
		     'amp-lightbox' => sprintf(WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'lightbox', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION),
	     )
     )->addStyle('lightbox');

// data preparation
$id = $this->get('lb_id');
$class = $this->get('lb_class');

?>
<amp-lightbox class="wbamp-lightbox <?php echo $class; ?>" layout="nodisplay" id="<?php echo $id; ?>">
    <div class="wbamp-lightbox">
		<?php echo $this->get('lb_content'); ?>
    </div>
</amp-lightbox>
