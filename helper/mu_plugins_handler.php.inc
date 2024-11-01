<?php
/*
 * Plugin Name: weeblrAMP plugins handler
 * Plugin URI: https://www.weeblrpress.com/weeblramp
 * Description: Provides Accelerated Mobile Pages support for Wordpress
 * Author: WeeblrPress
 * Version: 1.12.5.783
 * Author URI: https://www.weeblrpress.com/
 * License: GNU GPL v2
 * Text Domain: weeblramp
 * Domain Path: /languages
 *
 * 1.12.5.783
 *
 */
defined( 'WPINC' ) || die;

// Play nice with http://wp-cli.org/
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	return;
}

/**
 * A class to manage disabling plugins on the fly, per request.
 *
 * Class WeeblrampPluginsHandler
 */
class WeeblrampPluginsHandler {

	private $isAmpPage           = false;
	private $shouldFilterPlugins = null;
	private $requestedUri        = null;
	private $userSettings        = null;
	private $pluginsToRemove     = array();
	private $isStandaloneMode    = false;
	private $edition             = 'community';

	/**
	 * Read config and conditions to find if we must filter out
	 * some plugins from the (AMP) request
	 *
	 * WeeblrampPluginsHandler constructor.
	 */
	public function __construct() {

		$this->shouldFilterPlugins = $this->shouldProcess();
	}

	/**
	 * Actually remove the plugins set by user from the
	 * active plugins list
	 *
	 * @param array $pluginsList
	 *
	 * @return array
	 */
	public function filterPlugins( $pluginsList ) {

		// just in case this is called directly somehow
		if ( ! is_array( $pluginsList ) ) {
			return $pluginsList;
		}

		// weeblrAMP not enabled, don't do anything
		if ( ! in_array( 'weeblramp/weeblramp.php', $pluginsList ) ) {
			$this->isAmpPage = false;

			return $pluginsList;
		}

		$newList = $pluginsList;
		if ( $this->shouldFilterPlugins && ! empty( $this->pluginsToRemove ) && is_array( $this->pluginsToRemove ) ) {
			$newList = array_diff( $pluginsList, $this->pluginsToRemove );
		}

		// re-order plugins so that our themes and extensions are loaded
		// after weeblrAMP (so that they can use wbLib and other stuff)
		// extract them
		$weeblrampPlugins = array();
		foreach ( $newList as $key => $plugin ) {
			if ( 'weeblramp/weeblramp.php' == $plugin ) {
				unset( $newList[ $key ] );
				continue;
			}
			if ( 0 === strpos( $plugin, 'weeblramp' ) ) {
				unset( $newList[ $key ] );
				$weeblrampPlugins[] = $plugin;
			}
		}

		$newList = array_merge(
			array( 'weeblramp/weeblramp.php' ),
			$weeblrampPlugins,
			$newList
		);

		// done
		return array_values( $newList );
	}

	/**
	 * Let user disable also network enabled plugins.
	 *
	 * @param $pluginsList
	 *
	 * @return array
	 */
	public function filterNetworkPlugins( $pluginsList ) {

		// just in case this is called directly somehow
		if ( ! is_array( $pluginsList ) ) {
			return $pluginsList;
		}

		$newList = $pluginsList;
		if ( $this->shouldFilterPlugins && ! empty( $this->pluginsToRemove ) && is_array( $this->pluginsToRemove ) ) {
			$newList = array_diff_key( $pluginsList, $this->pluginsToRemove );
		}

		// done
		return $newList;
	}

	/**
	 * Whether we should filter plugins on this request:
	 *
	 * - not in admin
	 * - only on AMP pages
	 *
	 * @return bool
	 */
	private function shouldProcess() {

		$this->requestedUri = $this->getUri();

		// not on admin pages
		if ( is_admin() ) {

			return false;
		}

		// get Weeblramp settings
		$this->userSettings = get_option( 'weeblramp___weeblramp_user__' );
		if ( false == $this->userSettings ) {
			// don't know what to do without settings
			return false;
		}

		// make sure we are on an AMP page
		// In standalone mode, all (frontend) pages are AMP
		$this->isStandaloneMode = 2 == $this->userSettings['op_mode'] && ( 'full' == $this->edition || 'community' == $this->edition );
		// otherwise we look for the amp suffix in URLs to identify them
		$ampSuffix = empty( $this->userSettings['amp_suffix'] ) ? null : $this->userSettings['amp_suffix'];
		if ( empty( $ampSuffix ) && ! $this->isStandaloneMode ) {
			// no suffix and not in standalone mode, not an AMP page
			return false;
		}

		// does the request ends with the amp suffix +/- the trailing slash?
		$uriTrailingSlug = rtrim( $this->requestedUri, '/' );
		$uriTrailingSlug = substr( $uriTrailingSlug, - strlen( $ampSuffix ) );
		// not an amp page (has no suffix and not in standalone mode)
		if ( $ampSuffix != $uriTrailingSlug && ! $this->isStandaloneMode ) {
			// no AMP suffix, not an AMP page
			return false;
		}

		// store we are on an AMP page
		$this->isAmpPage = true;

		// get user-set list of plugins to keep/disable
		$toRemoveSetting = empty( $this->userSettings['plugins_to_disable'] ) ? array() : $this->userSettings['plugins_to_disable'];

		// store the plugins that needs removal
		foreach ( $toRemoveSetting as $pluginName => $toRemove ) {
			if ( $toRemove ) {
				$this->pluginsToRemove[ $pluginName ] = $pluginName;
			}
		}

		// read list of other AMP plugins
		$file = WP_PLUGIN_DIR . '/weeblramp/helper/compat.php';
		if ( file_exists( $file ) ) {
			defined( 'WEEBLRAMP_EXEC' ) or define( 'WEEBLRAMP_EXEC', 1 );
			include_once $file;
		} else {
			// somethings's wrong, files are missing, admin may
			// have moved the plugin folder
			$this->isAmpPage = false;

			return false;
		}

		// merge in hardcoded list of incompatible plugins
		$this->pluginsToRemove = array_merge(
			$this->pluginsToRemove,
			array_keys( WeeblrampHelper_Compat::$incompatiblePlugins ),
			WeeblrampHelper_Compat::$pluginsToAlwaysRemove
		);

		// don't hook if no plugin to remove
		if ( empty( $this->pluginsToRemove ) ) {
			return false;
		}

		// this should be an AMP page
		return true;
	}

	/**
	 * Get the current request path (excluding host and query string)
	 *
	 * @return string
	 */
	private function getUri() {

		// First we need to detect the URI scheme.
		if ( isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTPS'] ) && ( strtolower( $_SERVER['HTTPS'] ) != 'off' ) ) {
			$scheme = 'https://';
		} else {
			$scheme = 'http://';
		}

		/*
		 * There are some differences in the way that Apache and IIS populate server environment variables.  To
		 * properly detect the requested URI we need to adjust our algorithm based on whether or not we are getting
		 * information from Apache or IIS.
		 */
		// Define variable to return
		$uri = '';

		// If PHP_SELF and REQUEST_URI are both populated then we will assume "Apache Mode".
		if ( ! empty( $_SERVER['PHP_SELF'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
			// The URI is built from the HTTP_HOST and REQUEST_URI environment variables in an Apache environment.
			$uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} // If not in "Apache Mode" we will assume that we are in an IIS environment and proceed.
		elseif ( isset( $_SERVER['HTTP_HOST'] ) ) {
			// IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
			$uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

			// If the QUERY_STRING variable exists append it to the URI string.
			if ( isset( $_SERVER['QUERY_STRING'] ) && ! empty( $_SERVER['QUERY_STRING'] ) ) {
				$uri .= '?' . $_SERVER['QUERY_STRING'];
			}
		}

		$uri = trim( $uri );

		// Extra cleanup to remove invalid chars in the URL to prevent injections through the Host header
		$uri = str_replace( array( "'", '"', '<', '>' ), array( "%27", "%22", "%3C", "%3E" ), $uri );

		// drop the root URI for the site
		$root = get_home_url();
		if ( 0 === strpos( $uri, $root ) ) {
			$uri = substr( $uri, strlen( $root ) );
		}

		// drop the query string
		$bits = explode( '?', $uri, 2 );
		$uri  = ! empty( $bits[0] ) ? $bits[0] : $uri;

		return $uri;
	}

	/**
	 * Nuke the template directory: entirely bypass main theme, avoids interferences by themes on AMP pages.
	 *
	 * @param string $template_dir
	 * @param string $template
	 * @param string $theme_root
	 *
	 * @return string
	 */
	public function filterTemplateDirectory( $template_dir, $template, $theme_root ) {

		static $currentTemplate = '';
		static $updated = false;

		if ( empty( $currentTemplate ) ) {
			$currentTemplate = get_option( 'weeblramp_current_template' );
		}

			// store in option
		if (
			! $updated
			&&
			$currentTemplate != $template_dir
		) {
				$currentTemplate = $template_dir;
				update_option(
					'weeblramp_current_template',
					$template_dir
				);
			$updated = true;
			}

		if (
			$this->shouldDisableTheme()
			&&
			( $this->isAmpPage || $this->checkAgainStandaloneMode() )
		) {
			$template_dir = WP_PLUGIN_DIR . '/weeblramp/public/theme';

			$this->weShouldReallyNotHaveToDoThat();
		}

		return $template_dir;
	}

	/**
	 * Nuke the stylesheet directory: entirely bypass child theme, avoids interferences by themes on AMP pages.
	 *
	 * @param string $stylesheet_dir
	 * @param string $stylesheet
	 * @param string $theme_root
	 *
	 * @return string
	 */
	public function filterStylesheetDirectory( $stylesheet_dir, $stylesheet, $theme_root ) {

		static $currentStylesheet = '';
		static $updated = false;

		if ( empty( $currentStylesheet ) ) {
			$currentStylesheet = get_option( 'weeblramp_current_stylesheet' );
		}

		if (
			! $updated
			&&
			$currentStylesheet != $stylesheet_dir
		) {
				$currentStylesheet = $stylesheet_dir;
				update_option(
					'weeblramp_current_stylesheet',
					$stylesheet_dir
				);
			$updated = true;
			}

		if (
			$this->shouldDisableTheme()
			&&
			( $this->isAmpPage || $this->checkAgainStandaloneMode() )
		) {

			$stylesheet_dir = WP_PLUGIN_DIR . '/weeblramp/public/theme';

			$this->weShouldReallyNotHaveToDoThat();
		}

		return $stylesheet_dir;
	}

	/**
	 * Finds out whether user asked to disable the theme.
	 *
	 * @return bool
	 */
	private function shouldDisableTheme() {

		$default = false;

		if (
			empty( $this->userSettings )
			||
			! isset( $this->userSettings['disable_theme'] )
		) {
			return $default;
		}

		return ! empty( $this->userSettings['disable_theme'] );
	}

	/**
	 * Check if we are indeed in standalone mode. The initial check, done at at startup
	 * may not have been conclusive in some rare edge cases. Now that weeblrAMP is
	 * fully loaded, we can properly check the configuration object.
	 *
	 * @return bool
	 */
	private function checkAgainStandaloneMode() {

		if ( $this->isStandaloneMode && is_callable( array( 'WeeblrampFactory', 'getThe' ) ) ) {
			// we are here because we thought this was standalone mode
			// but under some circumstances, standalone mode can be selected
			// but not available (ie community version)
			if ( 2 == WeeblrampFactory::getThe( 'weeblramp.config.user' )->get( 'op_mode' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Things I thought I wouldn't have to do until many years
	 * of accumulating technical debt, and yet.
	 */
	private function weShouldReallyNotHaveToDoThat() {

		// PolyLang hack: this function must be defined, as PLL calls it
		// on init event.
		// Will be tested before use in next version of PolyLang, and
		// this hack can go away. Thanks Frederic!
		if ( ! function_exists( 'twentyseventeen_panel_count' ) ) {
			function twentyseventeen_panel_count() {

				return 0;
			}
		}
	}
}

/**
 * Start processing
 */

$handler = new WeeblrampPluginsHandler();

// if AMP request, hook into the active_plugin filter
add_filter( 'site_option_active_sitewide_plugins', array( $handler, 'filterNetworkPlugins' ) );
add_filter( 'option_active_plugins', array( $handler, 'filterPlugins' ) );

// filter theme, avoid interferences
add_filter( 'template_directory', array( $handler, 'filterTemplateDirectory' ), 10, 3 );
add_filter( 'stylesheet_directory', array( $handler, 'filterStylesheetDirectory' ), 10, 3 );

