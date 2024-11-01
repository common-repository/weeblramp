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

$groupName             = $this->get( 'name' );
$hasDescription        = $this->hasDisplayData( 'details', 'desc' );
$entireSettingDisabled = $this->hasDisplayData( 'disabled' );

?>
<fieldset <?php echo $this->hasDisplayData( 'disabled' ) ? 'class="wblib-na-this-edition"' : ''; ?>>
    <legend class="screen-reader-text"><?php echo esc_html( $this->getInArray( 'details', 'title' ) ); ?></legend>
	<?php

	$details      = $this->getInArray( 'details', 'content' );
	$disabledFlag = false;
	foreach ( $details['options'] as $record ):

		$disabledItem = $entireSettingDisabled;

		$boxName = $groupName . '[' . $record['name'] . ']';

		// force type
		$attr = array(
			'type' => 'checkbox',
			'name' => $boxName,
			'id'   => WblSystem_Strings::asHtmlId( $boxName )
		);

		// optional description
		if ( $hasDescription ) {
			$attr['aria-describedby'] = $this->getAsId( 'name' ) . '_description';
		}

		// edition
		$editions = wbArrayGet( $record, 'editions', null );
		if (
			$entireSettingDisabled
			||
			(
				! is_null( $editions )
				&&
				! WeeblrampHelper_Version::isOneOfEditions( $editions )
			)
		) {
			$attr['disabled'] = 'disabled';
			$disabledFlag     = true;
			$disabledItem     = true;
		}

		$attr = array_merge( $attr, $details['attr'] );

		// turn into text
		$attributes = WblHtml_Helper::attrToHtml( $attr );

		?>
        <label
			<?php echo 'vertical' == wbArrayGet( $details, 'layout', 'horizontal' ) ? 'class="wbamp-settings-checkbox-group-vertical" ' : ''; ?>for="<?php echo esc_attr( $boxName ); ?>">
            <input <?php echo $attributes; ?>
                    value="1"<?php checked( $this->getInArray( 'current_value', $record['name'] ), 1 ); ?> />
            <span<?php echo $disabledItem ? ' class="wblib-na-this-edition"' : ''; ?>><?php echo esc_html( $record['caption'] ); ?></span>
        </label>
		<?php

	endforeach;

	echo '<span id="' . WblSystem_Strings::asHtmlId( $this->getAsAttr( 'name' ) ) . '" ' . WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ) . '></span>';

	if($disabledFlag)
	{
		$this->__data['disabled'] = true;
	}

	echo WblMvcLayout_Helper::render( 'wblib.settings.setting_description', $this->getDisplayData(), WBLIB_LAYOUTS_PATH );

	?>
</fieldset>

