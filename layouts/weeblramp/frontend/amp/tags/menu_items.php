<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author                  weeblrPress
 * @copyright               (c) WeeblrPress - Weeblr,llc - 2020
 * @package                 AMP on WordPress - weeblrAMP CE
 * @license                 http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version                 1.12.5.783
 *
 * 2020-05-19
 */

// no direct access
defined( 'WEEBLRAMP_EXEC' ) || die;

if ( ! $this->hasDisplayData( 'navigation_menu' ) ) {
	return;
}

$renderedMenuRecords = $this->get( 'navigation_menu' );

/**
 * Hook before displaying menu items
 *
 * Output any HTML you want displayed at the top of the sidebar/drop down menu
 *
 * @api
 * @package weeblrAMP\action\output
 * @var weeblramp_before_menu_items
 * @since   1.0.0
 *
 */
do_action( 'weeblramp_before_menu_items' );

// optional search box before menu items
WeeblrampHelper_Features::maybeOutputSearchBox(
	WeeblrampConfig_Customize::SEARCH_BOX_MENU_TOP,
	$this->getDisplayData(),
	array(
		$this->get( 'amp_form_processor' ),
		'convert'
	)
);

foreach ( $renderedMenuRecords as $menuId => $menuRecord ) :
	if ( ! empty( $menuRecord['menu_settings']['show_name'] ) ) {
		echo '<div class="wamp-menu-title">' . esc_html( $menuRecord['menu_data']->name ) . '</div>';
	}

	echo $menuRecord['menu_rendered'];

endforeach;

// optional search box after menu items
WeeblrampHelper_Features::maybeOutputSearchBox(
	WeeblrampConfig_Customize::SEARCH_BOX_MENU_BOTTOM,
	$this->getDisplayData(),
	array(
		$this->get( 'amp_form_processor' ),
		'convert'
	)
);

/**
 * Hook after displaying menu items
 *
 * Output any HTML you want displayed at the bottom of the sidebar/drop down menu
 *
 * @api
 * @package weeblrAMP\action\output
 * @var weeblramp_after_menu_items
 * @since   1.0.0
 *
 */
do_action( 'weeblramp_after_menu_items' );
