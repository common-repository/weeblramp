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

// optional description
if ( ! empty( $details['desc'] ) ) {
	$details['content']['attr']['aria-describedby'] = $this->getAsId( 'name' ) . '_description';
}
// turn into text
$attributes = WblHtml_Helper::attrToHtml( $details['content']['attr'] );

?>
<button <?php echo WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ); ?> type="button"
                                                                                   class="js-wbamp-clear-transients-button wbamp-clear-transients-button button"
	<?php echo $attributes; ?>
>
	<?php echo __( 'Clear now', 'weeblramp' ); ?>
</button>
<div class="wbamp-clear-transients-msg wbamp-ajax-response-msg">
    <div id="js-wbamp-clear-transients-msg"></div>
    <div id="js-wbamp-clear-transients-spinner"></div>
</div>
<?php echo WblMvcLayout_Helper::render( 'wblib.settings.setting_description', $this->getDisplayData(), WBLIB_LAYOUTS_PATH ); ?>
