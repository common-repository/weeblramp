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
defined( 'WEEBLRAMP_EXEC' ) || die;

class WeeblrampModelElement_Navigation extends WeeblrampClass_Base {

	private $router          = null;
	private $taxonomyManager = null;

	/**
	 * Renders a user-selected (optional) menu module in a simple layout
	 * to provide navigation.
	 * Optionally also, links in that menu can be turned into their AMP equivalent
	 *
	 * @return mixed|string
	 */
	public function getData( $currentData ) {

		$renderedMenus = '';
		if ( WeeblrampConfig_Customize::MENU_TYPE_NONE != $this->customizeConfig->get( 'menu_type' ) ) {
			// fetch the menu from transient, or build and store it

			// @TODO: cache again. Cache key must at least include language slug, as menu
			// selection can be done per language.

			//$renderedMenus = WeeblrampFactory::getA('WblSystem_Cache')
			//                                 ->get(
			//	                                 'weeblramp_fe_rendered_menus',
			//	                                 array(
			//		                                 $this,
			//		                                 'getRenderedMenu'
			//	                                 ),
			//	                                 array(),
			//	                                 $this->systemConfig->get('ttl.weeblramp_fe_rendered_menus')
			//                                 );

			// caching disabled to simplify workflow. Timing not so much
			// an issue in this context.
			$renderedMenus = $this->getRenderedMenu();
		}

		// store rendered menus
		$result = array(
			'data' => $renderedMenus
		);

		// and add scripts and styles in accordance with selected menu styles
		switch ( $this->customizeConfig->get( 'menu_type' ) ) {
			case WeeblrampConfig_Customize::MENU_TYPE_SLIDE:
				$result['scripts'] = array(
					'amp-sidebar' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'sidebar', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
				);
				$result['styles']  = 'sidebar';
				break;
			case WeeblrampConfig_Customize::MENU_TYPE_DROPDOWN:
				$result['scripts'] = array(
					'amp-accordion' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'accordion', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION ),
				);
				$result['styles']  = 'drop_down_menu';
				break;
		}

		// return processed content and possibly required AMP scripts
		return $result;
	}

	/**
	 * Builds HTML for the set of AMP menus selected by user in config
	 * Applies a filter to only amplify links the user selected
	 *
	 * @return array
	 */
	public function getRenderedMenu() {

		$renderedMenus = array();

		// filter out menu items that shouldn't be AMPlified
		add_filter( 'wp_nav_menu_objects', array( $this, 'menuItemsFilter' ), 10, 2 );

		$menusList = $this->customizeConfig->get( 'navigation_menu' );
		foreach ( $menusList as $menuId => $menu ) {
			if ( ! empty( $menu['enabled'] ) ) {
				// ask WP to render the menu
				$renderedMenu = wp_nav_menu(
					array(
						'menu'            => $menuId,
						'menu_class'      => '',
						'menu_id'         => '',
						'container'       => '',
						'container_class' => '',
						'container_id'    => '',
						'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
						'echo'            => false,
						'depth'           => 0
					)
				);

				/**
				 * Filter the list of menus to be displayed on AMP pages.
				 *
				 * Identical to WordPress wp_get_nav_menus filter, but only run on AMP pages.
				 *
				 * @api
				 * @package weeblrAMP\filter\output
				 * @var weeblramp_get_nav_menus
				 * @since   1.0.0
				 *
				 * @param array $menuDetails List of WordPress menu objects
				 *
				 * @return array
				 */
				$menuDetails = apply_filters(
					'weeblramp_get_nav_menus',
					wp_get_nav_menus( array( 'include' => $menuId ) )
				);

				if ( ! empty( $menuDetails ) ) {
					$menuDetails              = $menuDetails[0];
					$renderedMenus[ $menuId ] = array(
						'menu_data'     => $menuDetails,
						'menu_settings' => $menu,
						'menu_rendered' => $renderedMenu
					);
				}
			}
		}

		// avoid interference, remove our filter
		remove_filter( 'wp_nav_menu_objects', array( $this, 'menuItemsFilter' ), 10 );

		return $renderedMenus;
	}

	/**
	 * Filters the sorted list of menu item objects before generating the menu's HTML.
	 *
	 * @since 3.1.0
	 *
	 * @param array  $sorted_menu_items The menu items, sorted by each menu item's menu order.
	 * @param object $args An object containing wp_nav_menu() arguments.
	 */
	/**
	 * We optionally AMPlify the links in the menu items
	 *
	 * @param array  $sorted_menu_items
	 * @param object $args
	 *
	 * @return mixed
	 */
	public function menuItemsFilter( $sorted_menu_items, $args ) {

		if ( is_null( $this->taxonomyManager ) ) {
			$this->taxonomyManager = WeeblrampFactory::getThe( 'WeeblrampClass_Taxonomy' );
		}

		if ( is_null( $this->router ) ) {
			$this->router = WeeblrampFactory::getThe( 'WeeblrampClass_Route' );
		}

		$settings = $this->customizeConfig->get( 'navigation_menu' );

		$queryVar = $this->router->getQueryVar();
		if ( empty( $settings[ $args->menu ] ) || empty( $settings[ $args->menu ]['should_amplify'] ) ) {
			// not set to amplify links in this menu
			return $sorted_menu_items;
		}

		// iterate over each link, and amplify those links we're supposed to amplify
		foreach ( $sorted_menu_items as $menuId => $item ) {
			switch ( true ) {
				case 'taxonomy' == $item->type:
					// a link to a tag, a custom term
					// is this one of the allowed terms?
					if ( $this->taxonomyManager->shouldAmplifyTerm( $item->object_id, $item->object ) ) {
						$sorted_menu_items[ $menuId ]->url = $this->router->getAmpUrlFromCanonical( $sorted_menu_items[ $menuId ]->url );
					}
					break;
				case 'post_type_archive' == $item->type:
					// amplify if post type is set to be amplified
					$shouldAmplify = post_type_supports( $item->object, $this->router->getQueryVar() );
					if ( $shouldAmplify ) {
						$sorted_menu_items[ $menuId ]->url = $this->router->getAmpUrlFromCanonical( $sorted_menu_items[ $menuId ]->url );
					}
					break;
				case 'custom' == $item->object:
					// custom links, don't touch
					break;
				case 'page' == $item->object:
				case 'post' == $item->object:
					// another post type, does it support/is enabled for AMP?
				default:
					$sorted_menu_items[ $menuId ]->url = $this->maybeGetItemAmpUrl( $item, $queryVar );
					break;
			}
		}

		return $sorted_menu_items;
	}

	/**
	 * Finds out if a given post, as identified in a
	 *
	 * @param $item
	 * @param $queryVar
	 *
	 * @return mixed
	 */
	private function maybeGetItemAmpUrl( $item, $queryVar ) {

		$url = $item->url;
		if ( post_type_supports( $item->object, $queryVar ) ) {
			if ( $this->taxonomyManager->shouldAmplifyItem( $item->object_id ) ) {
				$url = $this->router->getAmpUrlFromCanonical( $url );
			}
		}

		return $url;
	}
}
