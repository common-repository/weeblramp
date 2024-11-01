<?php
/*
 * Plugin Name: weeblrAMP
 * Plugin URI: https://www.weeblrpress.com/weeblramp
 * Description: Provides Accelerated Mobile Pages support for Wordpress
 * Author: WeeblrPress
 * Version: 1.12.5.783
 * Author URI: https://www.weeblrpress.com/
 * License: GNU GPL v2
 * Text Domain: weeblramp
 * Domain Path: /languages
 *
 * 1.12.5.783
 *
 */
defined('WPINC') || die;

// Play nice with http://wp-cli.org/
if (defined('WP_CLI') && WP_CLI)
{
	return;
}

// base constants
defined('WEEBLRAMP_EXEC') or define('WEEBLRAMP_EXEC', 1);
defined('WEEBLRAMP_PLUGIN_FILE') or define('WEEBLRAMP_PLUGIN_FILE', __FILE__);

// include other constants
include_once 'defines.php';

// load bootstrap class
include 'helper/boot.php';

// register hooks and other initialization operations
WeeblrampHelper_Boot::boot();

