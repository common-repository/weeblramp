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

// then navigation
if (WeeblrampConfig_Customize::MENU_TYPE_NONE != $this->get('customize_config')->get('menu_type'))
{
	echo WblMvcLayout_Helper::render('weeblramp.frontend.amp.tags.menu_' . $this->get('customize_config')->get('menu_type'), $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH);
}
