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

// special case for hidden fields, only display the field, no title or any html
if ( WeeblrampConfig_User::OPTION_HIDDEN == $this->get( 'type' ) ) {
	echo WblMvcLayout_Helper::render( $this->get( 'sub_layout' ), $this->getDisplayData(), WBLIB_LAYOUTS_PATH );

	return;
}

?>
<table class="form-table">
    <tr <?php echo WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ); ?>>
		<?php if ( $this->hasDisplayData( 'name' ) ): ?>
            <th scope="row">
                <label
                        for="<?php echo $this->getAsAttr( 'label_for' ); ?>"><?php echo $this->getEscaped( 'title' ); ?>
                </label>
            </th>
		<?php else: ?>
            <th scope="row"><?php echo $this->getEscaped( 'title' ); ?></th>
		<?php endif; ?>
        <td>
			<?php echo WblMvcLayout_Helper::render( $this->get( 'sub_layout' ), $this->getDisplayData(), WBLIB_LAYOUTS_PATH ); ?>
        </td>
    </tr>
</table>
