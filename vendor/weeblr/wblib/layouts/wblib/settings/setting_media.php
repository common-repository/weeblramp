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

// set some defaults if missing
wbArrayKeyInit( $details['content']['attr'], 'name', $__data['name'] );
wbArrayKeyInit( $details['content']['attr'], 'id', $this->getAsId( 'name' ) );

// set current value
wbArrayKeyInit( $details['content']['attr'], 'value', $this->get( 'current_value' ) );

// force class
$details['content']['attr']['class'] = 'js-wbamp-media-manager-field wbamp-media-manager-field';

// optional description
if ( ! empty( $details['desc'] ) ) {
	$details['content']['attr']['aria-describedby'] = $this->getAsId( 'name' ) . '_description';
}

// turn all attributes into text
$attributes = WblHtml_Helper::attrToHtml( $details['content']['attr'] );

?>
<fieldset class="wblib-media-setting-wrapper<?php echo $this->hasDisplayData('disabled') ? ' wblib-na-this-edition' : ''; ?>">
    <legend class="screen-reader-text"><?php echo esc_html( $details['title'] ); ?></legend>
    <label for="<?php echo $this->getAsAttr( 'name' ); ?>">
        <input <?php echo WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ); ?> <?php echo $attributes; ?>
                type="text">
        <button <?php echo $this->hasDisplayData('disabled') ? ' disabled="disabled"' : ''; ?>
                data-media-type="image"
                data-media-title="<?php echo esc_attr( 'Select or upload an image' ); ?>"
                data-media-button="<?php echo esc_attr( 'Use this image' ); ?>"
			<?php echo empty( $details['with_preview'] ) ? '' : ' data-media-preview="true"'; ?>
			<?php echo empty( $details['min_width'] ) ? '' : ' data-media-min-width="' . (int) $details['min_width'] . '"'; ?>
			<?php echo empty( $details['max_width'] ) ? '' : ' data-media-max-width="' . (int) $details['max_width'] . '"'; ?>
			<?php echo empty( $details['min_height'] ) ? '' : ' data-media-min-height="' . (int) $details['min_height'] . '"'; ?>
			<?php echo empty( $details['max_height'] ) ? '' : ' data-media-max-height="' . (int) $details['max_height'] . '"'; ?>
                class="js-wbamp-media-manager-button wbamp-media-manager-button button"><?php echo __( 'Select image...', 'weeblramp' ); ?>
        </button>
    </label>
	<?php if ( ! empty( $details['with_preview'] ) ): ?>
        <div id="<?php echo WblSystem_Strings::asHtmlId( 'js-' . $this->get( 'name' ) . '-preview' ); ?>"
             class="wblib-image-preview">
			<?php if ( $this->hasDisplayData( 'current_value' ) ): ?>
                <img
                        src="<?php echo esc_url( WblSystem_Route::absolutify( $this->get( 'current_value' ) ) ); ?>">
			<?php endif; ?>
        </div>
	<?php endif; ?>
</fieldset>
<?php echo WblMvcLayout_Helper::render( 'wblib.settings.setting_description', $this->getDisplayData(), WBLIB_LAYOUTS_PATH ); ?>
