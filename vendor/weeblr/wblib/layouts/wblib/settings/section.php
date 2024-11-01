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

$class = $this->hasDisplayData( 'class' ) ? 'class="' . $this->getAsAttr( 'class' ) . '"' : '';

$icon = '';
if ( $this->hasDisplayData( 'details', 'icon' ) ) {
	$icon = '<span class="wblib-admin-section-icon">'
	        . WeeblrampFactory::getThe( 'weeblramp.config.system' )->get( $this->getInArray( 'details', 'icon' ) )
	        . '</span>';
}

'<span class="wblib-settings-upgrade-icon">' . WeeblrampFactory::getThe( 'weeblramp.config.system' )->get( 'assets.icons.upgrade', '' ) . '</span>'
?>
<div id="<?php echo WblSystem_Strings::asHtmlId( $this->get( 'name' ) ); ?>" <?php echo $class; ?> <?php echo WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ); ?> >
    <h2 class="wblib-settings-section"><?php echo $icon . $this->getEscaped( 'title' ); ?></h2>
	<?php
	if ( $this->hasDisplayData( 'details', 'help' ) || $this->hasDisplayData( 'disabled' ) ) : ?>
        <p class="description"><?php echo $this->getInArray( 'details', 'help' ); ?></p>
		<?php
		if ( $this->hasDisplayData( 'disabled' ) ) {
			echo WeeblrampHelper_Version::getEditionUpdateMessage( $this->getDisplayData() );
		}
		?>
        <hr/>
	<?php endif; ?>
    <table class="form-table">
		<?php do_settings_fields( $this->get( 'page' ), $this->get( 'name' ) ); ?>
    </table>
</div>
