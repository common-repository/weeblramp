<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author      weeblrPress
 * @copyright   (c) WeeblrPress - Weeblr,llc - 2020
 * @package     AMP on WordPress - weeblrAMP CE
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     1.12.5.783
 * @date                2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

Class WeeblrampClass_Update extends WeeblrampClass_Base {

	public function pre_http_request( $preempt, $requestArgs, $url ) {

		return false;
	}

	/**
	 * Set hooks to prevent updates from wp.org repo
	 * to be visible on the dev version.
	 *
	 * NB: this won't work if the plugin is not activated,
	 * so there's always a chance a user will install the
	 * community version over their full version.
	 * That should not cause any data loss however, not any
	 * change in site behavior, as the man plugin has to be
	 * disabled already for this to happen anyway.
	 */
	public function hideWporgUpdates() {

		// check for updates
		add_filter(
			'pre_set_site_transient_update_plugins',
			array(
				$this,
				'filterTransientUpdatePlugins'
			)
		);
	}

	/**
	 * Check for updates and if so, nuke the wp.org ones for Community version.
	 *
	 * @param object $data
	 *
	 * @return object $data
	 */
	public function filterTransientUpdatePlugins( $data ) {

		if ( empty( $data ) || empty( $data->response ) ) {
			return $data;
		}

		$weeblrampResponse = wbArrayGet(
			$data->response,
			array( WEEBLRAMP_PLUGIN )
		);

		// hide wp.org updates (as we are running the full edition)
		$id = empty( $weeblrampResponse->id ) ? '' : $weeblrampResponse->id;
		if ( ! empty( $id ) && wbStartsWith( $id, 'w.org' ) ) {
			unset( $data->response[ WEEBLRAMP_PLUGIN ] );
		}

		// hide updates if local development version
		if ( WblSystem_Version::isDevVersion() ) {
			$url = empty( $weeblrampResponse->url ) ? '' : $weeblrampResponse->url;
			if ( ! empty( $url ) && wbStartsWith( $url, 'https://www.weeblrpress.com' ) ) {
				unset( $data->response[ WEEBLRAMP_PLUGIN ] );
			}
		}

		return $data;
	}
}
