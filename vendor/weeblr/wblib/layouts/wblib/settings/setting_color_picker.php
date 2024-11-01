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
defined( 'WBLIB_ROOT_PATH' ) || die;

$details = $this->getAsArray( 'details' );

// force type
$details['content']['attr']['type'] = 'text';

// set some defaults if missing
wbArrayKeyInit( $details['content']['attr'], 'name', $this->get( 'name' ) );
wbArrayKeyInit( $details['content']['attr'], 'id', $this->getAsId( 'name' ) );

// set current value
wbArrayKeyInit( $details['content']['attr'], 'value', $this->get( 'current_value' ) );

// optional description
if ( ! empty( $details['desc'] ) ) {
	$details['content']['attr']['aria-describedby'] = $this->getAsId( 'name' ) . '_description';
}

// add marker for color picker
wbArrayKeyAppend( $details['content']['attr'], 'class', 'js-wblib-color-picker', ' ' );

// turn into text
$attributes = WblHtml_Helper::attrToHtml( $details['content']['attr'] );

?>
    <input <?php echo WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ); ?> <?php echo $attributes; ?> />
<?php echo WblMvcLayout_Helper::render( 'wblib.settings.setting_description', $this->getDisplayData(), WBLIB_LAYOUTS_PATH ); ?>
