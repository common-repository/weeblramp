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

// force type
$__data['details']['content']['attr']['type'] = 'checkbox';

// set some defaults if missing
wbArrayKeyInit( $__data['details']['content']['attr'], 'name', $this->get( 'name' ) );
wbArrayKeyInit( $__data['details']['content']['attr'], 'id', $this->getAsId( 'name' ) );
wbArrayKeyInit( $__data['details'], 'caption', '' );

// optional description
if ( ! empty( $__data['details']['desc'] ) ) {
	$__data['details']['content']['attr']['aria-describedby'] = $this->getAsId( 'name' ) . '_description';
}

// turn into text
$attributes = WblHtml_Helper::attrToHtml( $__data['details']['content']['attr'] );

?>
<fieldset <?php echo $this->hasDisplayData('disabled') ? 'class="wblib-na-this-edition"' : ''; ?>>
    <legend class="screen-reader-text"><?php echo esc_html( $this->getInArray( 'details', 'title' ) ); ?></legend>
    <label for="<?php echo esc_attr( $this->get( 'name' ) ); ?>">
        <input <?php echo WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ); ?> <?php echo $attributes; ?>
                value="1"<?php checked( $this->get( 'current_value' ), 1 ); ?> />
		<?php echo esc_html( $this->getInArray( 'details', 'caption' ) ); ?>
    </label>
</fieldset>
<?php echo WblMvcLayout_Helper::render( 'wblib.settings.setting_description', $this->getDisplayData(), WBLIB_LAYOUTS_PATH ); ?>
