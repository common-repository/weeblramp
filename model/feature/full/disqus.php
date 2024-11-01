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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Handles communication with WeeblrPress service in relation with Disqus support.
 *
 * Currently 3 actions handled:
 *
 * - update: adds/modifies disqus support for a site
 * - delete: remove support
 * - download_relay_file: let user downloads the Disqus relay file we built for them.
 *
 */
class WeeblrampModelFeature_Disqus extends WeeblrampClass_Model {

	public function dispatch( $request ) {

		// validate action
		$action = wbArrayGet( $request, 'request' );
		switch ( $action ) {
			case 'update':
			case 'delete':
				$response = $this->performRemoteRequest( $request, $action );
				break;
			case 'download_relay_file':
				// download will not return
				$this->downloadRelayFile( $request );
				break;
			default:
				throw new Exception( 'Invalid action requested. Aborting.' );
				break;
		}

		return $response;
	}

	/**
	 * Trigger the download of a custom-built Disqus relay file.
	 *
	 * @param array $request The $_REQUEST array basically.
	 *
	 * @throws Exception
	 */
	private function downloadRelayFile( $request ) {

		$renderedTemplate = WblMvcLayout_Helper::render(
			'weeblramp.frontend.amp.contents.amp_disqus_tpl',
			array( 'disqus_identifier' => wbArrayGet( $request, 'shortname' ) ),
			WEEBLRAMP_LAYOUTS_PATH
		);

		$downloaded = WblFs_File::triggerDownload(
			'amp-disqus.html',
			$filename = null,
			array(
				'content'      => $renderedTemplate,
				'content_type' => 'text/html',
				'die'          => false,
				'cookies'      => array(
					array(
						'name'   => 'wbamp_dl_success',
						'value'  => wbArrayGet( $request, 'id' ),
						'expire' => time() + 10
					)
				)
			)
		);
		if ( ! $downloaded ) {
			throw new Exception( 'Unable to download the Disqus file.' );
		}

		// exit to have the response sent.
		exit();
	}

	/**
	 * Communicate with WeeblrPress servers to enable/disable a Disqus relay file for this site.
	 *
	 * @param array  $request The ajax request received from UI to trigger this action.
	 * @param string $action The action to perform.
	 *
	 * @return array|WP_Error
	 */
	private function performRemoteRequest( $request, $action ) {

		// add site info to request, and communicate with remote host
		$requestData = array(
			'request_type' => $action,
			'shortname'    => wbArrayGet( $request, 'shortname' )
		);
		$response    = $this->post(
			$requestData
		);

		// return directly any error
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// request went through, build information record
		switch ( $requestData['request_type'] ) {
			case 'update':
				$newConnectState = WeeblrampConfig_User::DISQUS_CONNECT_CONNECTED;
				$decodedResponse = json_decode(
					wbArrayGet( $response, 'body', '' )
				);

				$newEndPoint    = empty( $decodedResponse ) || ! is_object( $decodedResponse ) ? 'N/A' : $decodedResponse->endpoint;
				$newButtonLabel = __( 'Stop using Disqus AMP file from WeeblrPress', 'weeblramp' );
				$message        = __( 'Connected shortname ' . esc_html( $requestData['shortname'] ) . ' to WeeblrPress Disqus support for AMP!' );
				break;
			case 'delete':
				$newConnectState = WeeblrampConfig_User::DISQUS_CONNECT_NOT_CONNECTED;
				$newEndPoint     = '';
				$newButtonLabel  = __( 'Use Disqus AMP file from WeeblrPress', 'weeblramp' );
				$message         = __( 'Not using WeeblrPress Disqus AMP file any longer for shortname ' . esc_html( $requestData['shortname'] ) );
				break;
		}

		// store immediately the new settings and information
		$this->userConfig
			->set(
				'comment_disqus_shortname',
				$requestData['shortname']
			)->set(
				'disqus_connect_state',
				$newConnectState
			)->set(
				'comment_disqus_endpoint',
				$newEndPoint
			)
			->store();

		// prepare a response so that client javascript can inform user and update some input fields
		$response = array(
			'message'           => $message,
			'new_connect_state' => $newConnectState,
			'new_endpoint'      => $newEndPoint,
			'new_button_label'  => $newButtonLabel
		);

		return $response;
	}

	/**
	 * Sends a request to WeeblrPress servers to enable disqus support for a site.
	 *
	 * @param array $requestData Information about current site: disqus shortname and access key
	 *
	 * @return array|WP_Error
	 */
	private function post( $requestData ) {

		// auth key
		$authKey = $this->userConfig->get(
			'access_key'
		);
		$authKey = StringHelper::trim( $authKey );
		if ( empty( $authKey ) ) {
			// cannot connect without a valid key
			$response = new WP_Error(
				200,
				__( 'Please enter and save your WeeblrPress access key on the < strong>System </strong > tab of this page before trying to connect to WeeblrPress . ', 'weeblramp' )
			);

			return $response;
		}

		$queryVars = array(
			'wblr_t'        => 'disqus',
			'wblr_a'        => wbArrayGet( $requestData, 'request_type' ),
			'wblr_v'        => 1,
			'wblr_disqus_o' => '',
			'wblr_disqus_i' => ''
		);

		// append origin
		$origin                     = StringHelper::rtrim(
			WblWordpress_Helper::getSiteUrl(),
			'/'
		);
		$queryVars['wblr_disqus_o'] = $origin;

		// disqus shortcode to use
		$queryVars['wblr_disqus_i'] = wbArrayGet(
			$requestData,
			'shortname'
		);

		// extra information: multisite?
		$extra = is_multisite() ? 'site:multi' : 'site:single';

		// use lib to format and sign our api request
		$signedRequest = WblSystem_Auth::signRequest(
			$queryVars,
			$authKey,
			$origin,
			$extra
		);

		// finally perform POST request to host
		// allow custom URL for dev
		if ( WblSystem_Version::isDevVersion() ) {
			$endpoint = $this->systemConfig->get( 'urls.api.dev' );
		} else {
			$endpoint = $this->systemConfig->get( 'urls.api.prod' );
		}
		$response = wp_remote_post(
			$endpoint . '/index.php?' . $signedRequest->urlEncodedQuery,
			array(
				'timeout'     => 5,
				'redirection' => 2,
				'blocking'    => true,
				'headers'     => $signedRequest->headers,
				'sslverify'   => ! WblSystem_Version::isDevVersion()
			)
		);

		$response = $this->handleErrors( $response );

		return $response;
	}

	/**
	 * Handles an error when communicating with WeeblrPress servers. Formats an ajax response
	 * by extracting message from the error.
	 *
	 * @param array $response A response array as provided by wp_remote_post.
	 *
	 * @return WP_Error
	 */
	private function handleErrors( $response ) {

		$responseCode = (int) wbArrayGet( $response, array( 'response', 'code' ) );
		if ( 200 != $responseCode ) {
			// maintenance, subs has been disabled, display as an error
			$decodedResponse = json_decode(
				wbArrayGet( $response, 'body', '' )
			);

			$message  = empty( $decodedResponse ) || ! is_object( $decodedResponse ) ? 'We are unable to contact the WeeblrPress servers. Please try again a little bit later or check our <a href="https://weeblrpress.wbstatus.com" target="_blank">status page</a> for further information.' : $decodedResponse->message;
			$response = new WP_Error(
				$responseCode,
				$message
			);
		}

		return $response;
	}
}
