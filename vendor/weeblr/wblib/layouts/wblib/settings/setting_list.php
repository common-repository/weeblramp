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

/**
 * array(
 * 'label_for' => wbJoin('_', static::STORAGE_PREFIX, $settingDef['name']),
 * 'type' => $settingDef['type'],
 * 'name' => wbJoin('_', static::STORAGE_PREFIX, $settingDef['name']),
 * 'details' => $settingDef,
 * 'layout' => $this->getLayoutFromSettingType($settingDef['type'])
 * )
 */

$details = $this->getAsArray( 'details' );

// set some defaults if missing
wbArrayKeyInit( $details['content']['attr'], 'name', $this->get( 'name' ) );
wbArrayKeyInit( $details['content']['attr'], 'id', $this->getAsId( 'name' ) );

// turn into text
$attributes = WblHtml_Helper::attrToHtml( $details['content']['attr'] );

// sanitize
$options = empty( $details['content']['options'] ) ? array() : $details['content']['options'];

// optional description
if ( ! empty( $details['desc'] ) ) {
	$details['content']['attr']['aria-describedby'] = $this->getAsId( 'name' ) . '_description';
}

$entireSettingDisabled = $this->hasDisplayData( 'disabled' );
$disabledFlag          = false;

?>
<select <?php echo WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ); ?> <?php echo $attributes; ?>>
	<?php foreach ( $options as $value => $display ) :
		if ( is_array( $display ) ) {
			$editions    = wbArrayGet( $display, 'editions', null );
			$disabled    = ! is_null( $editions ) && ! WeeblrampHelper_Version::isOneOfEditions( $editions );
			$disabled    = $disabled || $entireSettingDisabled;
			$optionTitle = wbArrayGet( $display, 'option' );
		} else {
			$disabled    = $entireSettingDisabled;
			$optionTitle = $display;
		}
		$disabledText = '';
		if ( $disabled ) {
			$disabledFlag = true;
			$disabledText = ' disabled="disabled"';
		}
		?>
        <option <?php echo $disabledText; ?>
                value="<?php echo $value ?>"<?php selected( $value, $this->get( 'current_value' ) ); ?>><?php echo esc_html( $optionTitle ); ?></option>
	<?php endforeach; ?>
</select>
<?php

if($disabledFlag)
{
    $this->__data['disabled'] = true;
}
echo WblMvcLayout_Helper::render( 'wblib.settings.setting_description', $this->getDisplayData(), WBLIB_LAYOUTS_PATH );
?>

