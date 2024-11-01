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

if ($this->hasDisplayData('structured_data'))
{
	foreach ($this->getAsArray('structured_data') as $id => $data)
	{
		echo WblMvcLayout_Helper::render(
			'weeblramp.frontend.amp.generic_json',
			array(
				'json' => $data,
				'title' => 'weeblrAMP: schema.org ' . $id
			),
			WEEBLRAMP_LAYOUTS_PATH
		);
	}
}
