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

echo '<!-- weeblrAMP: page meta data-->';
// canonical to the regular html page
if ( $this->hasDisplayData( 'canonical' ) ) {
	echo "\n\t" . '<link rel="canonical" href="' . $this->getAsAbsoluteUrl( 'canonical' ) . '" />';
}

if ( $this->hasDisplayData( 'metadata', 'robots' ) ) {
	echo "\n\t" . '<meta name="robots" content="' . esc_attr( $this->getInArray( 'metadata', 'robots' ) ) . '">';
}

if ( $this->hasDisplayData( 'metadata', 'title' ) ) {
	echo "\n\t" . '<title>' . $this->getInArray( 'metadata', 'title' ) . '</title>';
}
if ( $this->hasDisplayData( 'metadata', 'description' ) ) {
	echo "\n\t" . '<meta name="description" content="' . esc_attr( $this->getInArray( 'metadata', 'description' ) ) . '">';
}
if ( $this->hasDisplayData( 'metadata', 'keywords' ) ) {
	echo "\n\t" . '<meta name="keywords" content="' . esc_attr( $this->getInArray( 'metadata', 'keywords' ) ) . '">';
}
if ( $this->hasDisplayData( 'metadata', 'next' ) ) {
	echo "\n\t" . '<link rel="next" href="' . WblSystem_Route::absolutify( $this->get( 'metadata', 'next' ) ) . '">';
}
if ( $this->hasDisplayData( 'metadata', 'prev' ) ) {
	echo "\n\t" . '<link rel="prev" href="' . WblSystem_Route::absolutify( $this->get( 'metadata', 'prev' ) ) . '">';
}
echo "\n\t<!-- weeblrAMP: page meta data -->";
