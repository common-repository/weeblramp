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

if (
	! defined( 'WP_UNINSTALL_PLUGIN' )
	||
	! WP_UNINSTALL_PLUGIN
	||
	dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) )
) {
	status_header( 404 );
	exit;
}

try {
	// base constants
	defined( 'WEEBLRAMP_EXEC' ) or define( 'WEEBLRAMP_EXEC', 1 );
	defined( 'WEEBLRAMP_PLUGIN_FILE' ) or define( 'WEEBLRAMP_PLUGIN_FILE', realpath( __DIR__ . '/weeblramp.php' ) );

	// include other constants
	include_once 'defines.php';

	// load code from Joomla Framework
	include_once WBLIB_ROOT_PATH . 'vendor/joomla/string/src/phputf8/utf8.php';
	include_once WBLIB_ROOT_PATH . 'vendor/joomla/string/src/phputf8/trim.php';
	include_once WBLIB_ROOT_PATH . 'vendor/joomla/string/src/phputf8/ucfirst.php';
	include_once WBLIB_ROOT_PATH . 'vendor/joomla/string/src/StringHelper.php';

	// load code from wblib
	include_once WBLIB_ROOT_PATH . 'wblib.php';

	// instantiate wbLib and run uninstall
	$wbLib = new Wblib();
	$wbLib
		->boot()
		->uninstall();

	// delete some utility options
	$options = array(
		'weeblramp_last_amp_suffix',
		'weeblramp_last_op_mode',
		'weeblramp_rewrite_rules_flush_required',
		'weeblramp_last_post_types_hash'
	);

	if ( is_multisite() ) {
		foreach ( $options as $option ) {
			delete_site_option( $option );
		}
	} else {
		foreach ( $options as $option ) {
			delete_option( $option );
		}
	}
}
catch ( Exception $e ) {
	// die as gracefully as possible
	$msgTemplate =
		'<h1>%s</h1>'  // title
		. __( '<p>Looks like we were not able to perform this action. More details below:</p><i>%s</i>', 'weeblramp' ) // details
		. __( '<p>Sorry about the trouble! Go back by clicking <a href="%s">on this link</a>.</p>', 'weeblramp' ); // footer
	$msg         = sprintf(
		$msgTemplate,
		__( sprintf( 'Error during %s uninstall', 'weeblrAMP' ) ),
		$e->getMessage(),
		admin_url( 'plugins.php' )
	);
	wp_die( $msg );
}
