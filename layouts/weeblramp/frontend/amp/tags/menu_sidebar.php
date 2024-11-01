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

if ( ! $this->hasDisplayData( 'navigation_menu' ) ) {
	return;
}

?>
<amp-sidebar id='wbamp_sidebar_1' layout='nodisplay' class="wbamp-sidebar"
             side="<?php echo $this->get( 'navigation_menu_side' ); ?>">
    <div class="wbamp-menu wbamp-<?php echo $this->get( 'navigation_menu_side' ); ?>">
		<?php echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.tags.menu_items', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH ); ?>
    </div>
    <button value="<?php echo esc_attr( __( 'Close menu', 'weeblramp' ) ); ?>" type="button" class="wbamp-sidebar-button menu-close"
            on='tap:wbamp_sidebar_1.close'>&times;
    </button>
</amp-sidebar>

<div class="wbamp-wrapper wbamp-wrapper-hidden">
    <div class="wbamp-sidebar-control wbamp-<?php echo $this->get( 'navigation_menu_side' ); ?>">
        <button value="<?php echo esc_attr( __( 'Open menu', 'weeblramp' ) ); ?>" type="button" class="wbamp-sidebar-button menu-open"
                on='tap:wbamp_sidebar_1.open'>
			<?php if ( $this->hasDisplayData( 'navigation_menu_button_text' ) ) : ?>
                <span class="menu-icon-text"><?php echo $this->getEscaped( 'navigation_menu_button_text' ); ?></span>
			<?php else: ?>
                <span class="menu-icon-bar"></span>
                <span class="menu-icon-bar"></span>
                <span class="menu-icon-bar"></span>
			<?php endif; ?>
        </button>
    </div>
</div>
