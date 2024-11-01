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

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Remove from $wp_filter all hooks that belongs to a list
 * of plugins, effectively disabling them
 *
 * KEPT for REFERENCE ONLY: we switched to a must-use plugin which
 * actually disable the plugins. Faster, simpler and more reliable:
 * only removing hooks may leave the plugins still doing things
 * in its initialization code
 *
 */
class WeeblrampClass_Plugins extends WeeblrampClass_Base {

	private $hooksToRemove = array();
	private $pluginsToKeep = array();
	private $pathList      = array();
	private $themesRoot    = '';
	private $pluginsRoot   = '';

	/**
	 * Disable one or more front end plugins by removing
	 * all their hooks
	 *
	 * @param array $pluginsToDisable
	 */
	public function disablePluginsAndThemes( $pluginsToKeep = array() ) {

		// dev only
		$pluginsToKeep = array(
			'weeblramp',
		);

		$this->themesRoot  = str_replace( '\\', '/', get_theme_root() );
		$this->pluginsRoot = str_replace( '\\', '/', WP_PLUGIN_DIR );

		// prepare list of plugins path
		$this->buildPluginsToKeepList( $pluginsToKeep )
			// build the current hook list, filtered by our plugins
			 ->findHooks()
			// and remove them
			 ->removeHooks();
	}

	private function buildPluginsToKeepList( $pluginsToKeep ) {

		$pluginsToKeep = wbInitEmpty( $pluginsToKeep, $this->userConfig->get( 'plugins_to_disable' ) );

		// build a list of valid path
		if ( ! empty( $pluginsToKeep ) ) {
			foreach ( $pluginsToKeep as $pluginName ) {
				$this->pluginsToKeep[] = $this->pluginsRoot . '/' . $pluginName;
			}
		}

		return $this;
	}

	private function findHooks() {

		// reset removal list
		$this->hooksToRemove = array();

		// get all filters
		global $wp_filter;

		// iterate over them
		foreach ( $wp_filter as $hookName => $perHookNameList ) {
			foreach ( $perHookNameList as $priority => $perPriorityList ) {
				foreach ( $perPriorityList as $callback => $callbackDefinition ) {
					if ( $this->shouldRemoveFilter( $callbackDefinition['function'] ) ) {
						$this->hooksToRemove[] = array(
							'hook_name' => $hookName,
							'callback'  => $callback,
							'priority'  => $priority
						);
					}
				}
			}
		}

		return $this;
	}

	private function shouldRemoveFilter( $filterCallback ) {

		// build a reflection object
		switch ( true ) {
			case ( is_array( $filterCallback ) ):
				$object     = $filterCallback[0];
				$reflection = new ReflectionClass( $object );
				break;
			case ( is_string( $filterCallback ) ):
				if ( function_exists( $filterCallback ) ) {
					$reflection = new ReflectionFunction( $filterCallback );
				}
				break;
		}

		// decide whether to remove or not, based on path
		// the hook callback lives in
		if ( ! empty( $reflection ) ) {
			$path = $reflection->getFileName();

			// will be false for PHP built-in
			if ( ! empty( $path ) ) {
				$path             = str_replace( '\\', '/', $path );
				$this->pathList[] = $path;

				// disable themes and functions.php
				if ( wbStartsWith( $path, $this->themesRoot ) ) {
					// global flag for theme
					return $this->userConfig->get( 'disable_theme' );
				}

				// not a theme and not a plugin, keep it
				if ( ! wbStartsWith( $path, $this->pluginsRoot ) ) {
					return false;
				}

				// This is a plugin, is it on the "keep" list?
				if ( wbStartsWith( $path, $this->pluginsToKeep ) ) {
					return false;
				}

				// a plugin, but not on the list of plugins to keep: remove
				return true;
			}
		}

		return false;
	}

	/**
	 * Delete a list of hooks
	 *
	 * @param array $hooks Hook definition: hook name, callback, priority
	 */
	private function removeHooks() {

		foreach ( $this->hooksToRemove as $hook ) {
			remove_filter( $hook['hook_name'], $hook['callback'], $hook['priority'] );
		}

		return $this;
	}
}
