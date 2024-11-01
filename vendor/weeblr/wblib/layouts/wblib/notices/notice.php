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

// Security check to ensure this file is being included by a parent file.
defined('WBLIB_ROOT_PATH') || die;

foreach ($this->getAsArray('notices_group') as $notice):
	$type = $this->get('type', WblSystem_Notices::SUCCESS);
	$canDismiss = WblSystem_Notices::CAN_DISMISS == $notice['dismissable'];
	$class = $type != WblSystem_Notices::ALERT ? $type : 'warning';
	?>
	<div class="notice notice-<?php echo $class; ?><?php echo $canDismiss ? ' is-dismissible' : ''; ?>">
		<p><?php echo $notice['notice']; ?></p>
	</div>
	<?php
endforeach;
