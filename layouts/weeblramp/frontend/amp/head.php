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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// no direct access
defined('WEEBLRAMP_EXEC') || die;

// output favicons, if any
if ( $this->hasDisplayData( 'site_favicons' ) ) {
	echo WblHtml_Helper::printHtmlBlock( $this->get( 'site_favicons' ), 'Sites favicons' );
}

// build up the list of meta data to display
$metas = array();

// always include basic meta data
$metas[] = WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.metadata.meta', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );

// load user settings to know if we have more meta data
$userConfig = $this->get( 'user_config' );
if ( $userConfig->isTruthy( 'ogp_enabled' ) ) {
	$metas[] = WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.metadata.ogp', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
}
if ( $userConfig->isTruthy( 'tcards_enabled' ) ) {
	$metas[] = WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.metadata.twitter_cards', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
}

if ( $userConfig->isAnalyticsEnabled() ) {
    $metas[] = "\t" . '<!-- weeblrAMP: analytics client ID API -->';
	$metas[] = "\t" . '<meta name="amp-google-client-id-api" content="googleanalytics">';
	$metas[] = "\t" . '<!-- weeblrAMP: analytics client ID API -->';
}

// output them all
echo "\t" . implode( PHP_EOL, $metas );

// allows 3rd-party to add custom meta
echo "\n\t" . '<!-- weeblrAMP: 3rd-party integrations meta -->' . "\n";

/**
 * Let 3rd parties output custom meta data on AMP pages
 *
 * @api
 * @package weeblrAMP\action\seo
 * @var weeblramp_print_meta_data
 * @since   1.0.0
 *
 */
do_action( 'weeblramp_print_meta_data' );
echo "\t" . '<!-- weeblrAMP: 3rd-party integrations meta -->';

// user provided custom links
$customLinks = StringHelper::trim( $this->get( 'custom_links' ) );
if ( ! empty( $customLinks ) ) {
	echo "\n" . WblHtml_Helper::printHtmlBlock( $customLinks . "\n", 'AMP custom links' );
}

?>
    <!-- weeblrAMP: AMP boilerplate -->
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    <!-- weeblrAMP: AMP boilerplate -->
<?php

echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.style', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );

echo WblMvcLayout_Helper::render(
	'weeblramp.frontend.amp.generic_json',
	array(
		'json'  => $this->get( 'json-ld' ),
		'title' => 'weeblrAMP: AMP page meta data'
	),
	WEEBLRAMP_LAYOUTS_PATH
);

echo WblMvcLayout_Helper::render(
	'weeblramp.frontend.amp.metadata.structured',
	$this->getDisplayData(),
	WEEBLRAMP_LAYOUTS_PATH
);

echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.scripts', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
echo WblHtml_Helper::printHtmlBlock( "\t" . '<script async src="https://cdn.ampproject.org/v0.js"></script>' . "\n", 'AMP runtime' );

