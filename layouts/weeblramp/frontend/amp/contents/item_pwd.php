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

// display information for a password protected post

$userConfig = $this->get('user_config');

// standalone mode, we don't have a way to let user enter a password. Yet.
if (WeeblrampConfig_User::OP_MODE_STANDALONE == $userConfig->get('op_mode'))
{ ?>
	<div class="wbamp-pwd-btn">
		<p><?php echo __('Sorry, this content is password protected.', 'weeblramp'); ?></p>
	</div>
	<?php
}
else
{
	// other modes: send visitor to standard HTML version of this page, to enter password
	?>
	<div class="wbamp-pwd-btn">
		<p><?php echo __('This content is password protected. Please click on the button below to enter your password.', 'weeblramp'); ?></p>
		<a class="wbamp-pwd-btn"
		   href="<?php echo esc_url(get_permalink($this->getinObject('post', 'ID'))); ?>">Go to password entry form</a>
	</div>
	<?php
}
