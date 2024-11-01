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
defined( 'WEEBLRAMP_EXEC' ) || die;

$this->get( 'assets_collector' )
     ->addScripts(
	     array(
		     'amp-ad' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'ad', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION ),
	     )
     )->addStyle(
		array(
			'ad'
		)
	);

$config      = $this->get( 'user_config' );
$placeholder = $config->get( 'ad_placeholder' );
$fallback    = $config->get( 'ad_fallback' );

if ( ! empty( $placeholder ) ) {
	echo "\n\t" . '<div placeholder>' . $placeholder . '</div>';
}

if ( ! empty( $fallback ) ) {
	echo "\n\t" . '<div fallback>' . $fallback . '</div>';
}
