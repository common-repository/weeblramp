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

class WeeblrampModelElement_Analytics extends WeeblrampClass_Base {

	const EVENT_TRACKING_RULE_SEPARATOR = '|';

	/**
	 * Adds a Google Analytics tracking tag
	 * with various user-set options
	 *
	 */
	public function getData( $pageData ) {

		$analyticsData = array(
			'config'      => array(),
			'consent'     => false,
			'credentials' => ''
		);

		// no analytics
		if ( ! $this->userConfig->isAnalyticsEnabled() ) {
			return $analyticsData;
		}

		// compute a consent id if user-notification is enabled
		if ( $this->userConfig->isTruthy( 'analytics_require_consent' ) ) {
			// User wants analytics collection to start only after visitor consented
			// Was a user notification enabled?
			$userNotification = wbArrayGet( $pageData, 'user-notification' );
			if ( ! empty( $userNotification ) && ! empty( $userNotification['button'] ) ) {
				// there is a user notification set, and it has some button
				// use that
				$analyticsData['consent'] = ' data-consent-notification-id="' . $userNotification['id'] . '"';
			}
		}

		// Did user allow setting cookies across analytics request domains?
		// Required for Google Tag Manager
		if ( $this->userConfig->isTruthy( 'analytics_data_credentials' ) ) {
			$analyticsData['credentials'] = ' data-credentials="include"';
		}

		// Google Tag Manager: only data required is ID
		if ( $this->userConfig->isAnalyticsEnabled( WeeblrampConfig_User::ANALYTICS_GTM ) ) {
			$analyticsData['config'] = array(
				'gtm_id' => trim( $this->userConfig->get( 'analytics_gtm_id' ) )
			);
			$result                  = array(
				'data'    => $analyticsData,
				'scripts' => array(
					'amp-analytics' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'analytics', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
				)
			);

			return $result;
		}

		// standard analytics:
		// build up the analytics parameters json object
		$analyticsData['config'][ WeeblrampConfig_User::ANALYTICS_STANDARD ]             = array(
			'vars' => array(
				'account' => $this->userConfig->get( 'analytics_webproperty_id' )
			)
		);
		$analyticsData['config'][ WeeblrampConfig_User::ANALYTICS_STANDARD ]['triggers'] = array(
			'wbTrackPageview' => array(
				'on'      => 'visible',
				'request' => 'pageview'
			)
		);

		// finally link to amp analytics handler
		$result = array(
			'data'    => $analyticsData,
			'scripts' => array(
				'amp-analytics' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'analytics', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
			)
		);

		return $result;
	}
}
