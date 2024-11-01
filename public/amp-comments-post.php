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

$edition = 'community';

if ( strpos( $edition, 'community' ) === false ) {
	$filename = __DIR__ . '/' . $edition . '/' . basename( __FILE__ );
} else {
	$filename = __DIR__ . '/full/' . basename( __FILE__ );
}

if ( file_exists( $filename ) ) {
	defined('WEEBLRAMP_EXEC') or define('WEEBLRAMP_EXEC', 1);
	include_once $filename;
} else {
	throw new Exception( 'weeblrAMP: unsupported feature' );
}
