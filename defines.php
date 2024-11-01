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
defined('WEEBLRAMP_EXEC') || die;

// this plugin
defined('WEEBLRAMP_PLUGIN') or define('WEEBLRAMP_PLUGIN', plugin_basename(WEEBLRAMP_PLUGIN_FILE));
defined('WEEBLRAMP_PLUGIN_NAME') or define('WEEBLRAMP_PLUGIN_NAME', 'weeblrAMP');

// base bath for this plugin and its main libs, layouts and logs
defined('WEEBLRAMP_PLUGIN_DIR') or define('WEEBLRAMP_PLUGIN_DIR', plugin_dir_path(WEEBLRAMP_PLUGIN_FILE));
defined('WEEBLRAMP_LAYOUTS_PATH') or define('WEEBLRAMP_LAYOUTS_PATH', WEEBLRAMP_PLUGIN_DIR . 'layouts/');
defined('WEEBLRAMP_ASSETS_PATH') or define('WEEBLRAMP_ASSETS_PATH', WEEBLRAMP_PLUGIN_DIR . 'assets/');
defined('WEEBLRAMP_LOGS_DIR') or define('WEEBLRAMP_LOGS_DIR', WP_CONTENT_DIR . '/weeblramp_logs/');

// wbLib
defined('WBLIB_ROOT_PATH') or define('WBLIB_ROOT_PATH', WEEBLRAMP_PLUGIN_DIR . 'vendor/weeblr/wblib/');

// allow using WP AMP plugins classes
defined('WEEBLRAMP_AMP__DIR__') or define('WEEBLRAMP_AMP__DIR__', WEEBLRAMP_PLUGIN_DIR . 'vendor/wp/');
