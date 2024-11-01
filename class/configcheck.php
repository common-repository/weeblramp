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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;
use Weeblr\Wblib\Joomla\Uri\Uri;

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Updates to a standard HTML page, which has an AMP version
 */
class WeeblrampClass_Configcheck extends WeeblrampClass_Base {

	const NOTIFY = 'notify';
	const LOG = 'log';

	/**
	 * Hold a list of check methods to run,
	 * based on current settings page being displayed
	 *
	 * @var array
	 */
	private $checksList = array(
		WeeblrampViewAdmin_Options::SETTINGS_PAGE   => array(
			'checkLogoDimensions',
			'analyticsPropertyId',
			'ampSocialShareFbAppid',
			'disqusEndpoint',
			'diviInTheme',
			'mupluginCreated',
			// Customize errors
			'invalidCustomCSSWarning',
		),
		WeeblrampViewAdmin_Customize::SETTINGS_PAGE => array(
			'invalidCustomCSSWarning',
			'ampSocialShareFbAppid',
		)
	);

	/**
	 * Handle on admin notifier
	 * @var WblSystem_Notices
	 */
	private $notifier = null;

	/**
	 * Flag to disable admin notifications
	 * @var bool
	 */
	private $notificationType = self::NOTIFY;

	/**
	 * Constructor
	 *
	 * @param   array $options An array of options.
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );

		// store an instance of the notifier
		$this->notifier = WeeblrampFactory::getThe( 'WblSystem_Notices' );
	}

	/**
	 * Public helper to check publisher logo size against AMP rules
	 *
	 * @param int $width
	 * @param int $height
	 *
	 * @return bool
	 */
	public static function checkPublisherLogoSize( $width, $height ) {

		$valid        = true;
		$requiredSize = WeeblrampFactory::getThe( 'weeblramp.config.amp' )->get( 'publisherLogoSize' );
		if (
			( ! empty( $width ) && $width != $requiredSize['width'] )
			&&
			( ! empty( $height ) && $height != $requiredSize['height'] )
		) {
			$valid = false;
		}

		return $valid;
	}

	/**
	 * Execute all configuration checks
	 *
	 * @param string $settingsPage
	 * @param string $notificationType
	 */
	public function execute( $settingsPage, $notificationType = self::NOTIFY ) {

		// set/reset notification type
		$this->notificationType = $notificationType;

		// run all config checks
		$list = wbArrayGet( $this->checksList, $settingsPage );
		if ( ! empty( $list ) ) {
			foreach ( $list as $method ) {
				$this->{$method}();
			}
		}
	}

	/**
	 * If a publisher logo has been entered, it must
	 * match rules on its size
	 *
	 * https://developers.google.com/structured-data/carousels/top-stories#logo_guidelines
	 *
	 */
	private function checkLogoDimensions() {

		// if filled in, the publisher logo must
		// fit within 600px x 60px
		// height should be 60 or width should be 600px
		$logoUrl = $this->userConfig->get( 'publisher_image' );
		if ( ! empty( $logoUrl ) ) {
			$logoSize = WblHtmlContent_Image::getImageSize( $logoUrl );
			if ( self::checkPublisherLogoSize(
				$logoSize['width'],
				$logoSize['height'] )
			) {
				$this->removeNotification( 'config_check_publisher_logo_size' );
			} else {
				$message = __( 'Invalid logo dimensions', 'weeblramp' )
				           . ' '
				           . __( 'You have entered a <em>Publisher logo URL</em>, under the <em>Meta data tab</em>, but the dimensions of this image are not valid. Height must be 60 pixels (best), or width must be 600 px.', 'weeblramp' );

				$options = array(
					'type' => WblSystem_Notices::ERROR
				);
				$this->notify(
					'config_check_publisher_logo_size',
					$message,
					$options
				);
			}
		}
	}

	/**
	 * Display a warning if some suspicious CSS is seen in the custom CSS
	 *
	 * @return $this
	 */
	private function invalidCustomCSSWarning() {

		$invalidCss = array(
			'no_important' => array(
				'reg' => '/\!\s*important/i',
				'msg' => '<i>!important</i> tag is invalid.'
			)
		);

		$msgs      = array();
		$customCss = $this->customizeConfig->get( 'custom_css' );
		if ( ! empty( $customCss ) ) {
			foreach ( $invalidCss as $key => $cssRule ) {
				// simple text found
				if ( ! empty( $cssRule['txt'] ) ) {
					if ( strpos( $customCss, $cssRule['txt'] ) !== false ) {
						$msgs[] = __( $cssRule['msg'] );
					}
				}

				// reg exp test
				if ( ! empty( $cssRule['reg'] ) ) {
					if ( preg_match( $cssRule['reg'], $customCss ) ) {
						$msgs[] = __( $cssRule['msg'] );
					}
				}
			}
		}

		if ( ! empty( $msgs ) ) {
			$message = (
				__( 'We found some suspicious CSS in the <strong>Custom CSS</strong> field. Please refer to the <a href="https://www.ampproject.org/docs/guides/responsive/style_pages.html" target="_blank">AMP project guidelines</a>. Details:', 'weeblramp' )
				. ' '
				. implode( ',', $msgs )
			);
			$options = array(
				'type' => WblSystem_Notices::ERROR
			);
			$this->notify(
				'config_check_invalid_custom_css',
				$message,
				$options
			);
		} else {
			$this->removeNotification( 'config_check_invalid_custom_css' );
		}

		return $this;
	}

	private function analyticsPropertyId() {

		if ( WeeblrampHelper_Version::isFullEdition() ) {
			if (
				( $this->userConfig->isAnalyticsEnabled( WeeblrampConfig_User::ANALYTICS_STANDARD ) && $this->userConfig->isFalsy( 'analytics_webproperty_id' ) )
				||
				( $this->userConfig->isAnalyticsEnabled( WeeblrampConfig_User::ANALYTICS_GTM ) && $this->userConfig->isFalsy( 'analytics_gtm_id' ) )
				||
				( $this->userConfig->isAnalyticsEnabled( WeeblrampConfig_User::ANALYTICS_FACEBOOK_PIXEL ) && $this->userConfig->isFalsy( 'analytics_fb_pixel_id' ) )
			) {

				$message = __( 'You have enabled Analytics, but did not set yet an analytics web property ID. Analytics will not be able to record traffic data on your site.', 'weeblramp' );
				$options = array(
					'type' => WblSystem_Notices::ALERT
				);
				$this->notify(
					'config_check_analytics_missing_property_id',
					$message,
					$options
				);
			} else {
				$this->removeNotification( 'config_check_analytics_missing_property_id' );
			}
		}
	}

	/**
	 * amp-social-share for facebook button requires an app id
	 */
	private function ampSocialShareFbAppid() {

		if ( WeeblrampHelper_Version::isFullEdition() ) {
			$buttonsEnabled         = $this->customizeConfig->get( 'social_buttons_types' );
			$facebookSharingEnabled = ! empty( $buttonsEnabled['facebook_share'] );
			if (
				WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_NONE != $this->customizeConfig->get( 'social_buttons_location' )
				&& WeeblrampConfig_Customize::SOCIAL_BUTTONS_TYPE_AMPSOCIAL == $this->customizeConfig->get( 'social_buttons_type' )
				&& $facebookSharingEnabled
				&& $this->userConfig->isFalsy( 'facebook_app_id' )
			) {
				$message = __( 'You have enabled amp-social sharing buttons, including a Facebook button, but you have not yet entered a <strong>Facebook App Id</strong> (under the <strong>SEO</strong> tab). This button cannot work without an app id, so we will hide it until you have one.', 'weeblramp' );
				$options = array(
					'type' => WblSystem_Notices::ALERT
				);
				$this->notify(
					'config_check_social_share_no_fb_app_id',
					$message,
					$options
				);
			} else {
				$this->removeNotification( 'config_check_social_share_no_fb_app_id' );
			}
		}
	}

	/**
	 * If disqus is used for comment, and an endpoint has been specified,
	 * it must be:
	 * - over https
	 * - on a separate domain
	 */
	private function disqusEndpoint() {

		if ( WeeblrampHelper_Version::isFullEdition() ) {
			$endpoint = StringHelper::trim(
				$this->userConfig->get( 'comment_disqus_endpoint' )
			);
			if (
				WeeblrampConfig_User::COMMENTS_DISQUS == $this->userConfig->get( 'commenting_system' )
				&&
				( ! empty( $endpoint ) )
			) {
				$messages = array();
				// is the endpoint over https
				if ( ! wbStartsWith( $endpoint, 'https://' )
				) {
					$messages[] = __(
						' the endpoint is not hosted on <strong>HTTPS</strong>',
						'weeblramp'
					);
				}

				$thisHost     = StringHelper::strtolower(
					WblWordpress_Helper::getSiteUrl(
						array( 'scheme', 'host' )
					)
				);
				$endpointUri  = new Uri( $endpoint );
				$endpointHost = StringHelper::strtolower(
					$endpointUri->toString(
						array( 'scheme', 'host' )
					)
				);
				if ( $endpointHost == $thisHost ) {
					$messages[] = sprintf(
						__(
							' the endpoint is <strong>on the same domain</strong> (%s) as this site, which is not allowed by AMP',
							'weeblramp'
						),
						esc_html( $endpointHost )
					);
				}

				// build up full message and notify
				if ( ! empty( $messages ) ) {
					$message = __( 'Disqus commenting system is enabled, however', 'weeblramp' )
					           . implode(
						           __( ' and ', 'weeblramp' ),
						           $messages
					           )
					           . '.';

					$options = array(
						'type' => WblSystem_Notices::ERROR
					);
					$this->notify(
						'config_check_disqus_endpoint',
						$message,
						$options
					);
				} else {
					$this->removeNotification( 'config_check_disqus_endpoint' );
				}
			}
		}
	}

	/**
	 * If Divi is running, but there is no divi plugin then it is in the theme.
	 * Therefore the theme should not be disabled on AMP pages, which is the default, from 1.5.1.
	 *
	 */
	private function diviInTheme() {

		$showNotice  = false;
		$diviRunning = function_exists( 'et_builder_load_framework' );
		if ( $diviRunning ) {
			// search for the Divi plugin
			$activePlugins = get_option( 'active_plugins', array() );
			if ( ! in_array( 'divi-builder/divi-builder.php', $activePlugins ) ) {
				// divi is here, but not as a plugin.
				$showNotice = $this->userConfig->isTruthy( 'disable_theme' );
			}
		}

		if ( $showNotice ) {
			$message = __( 'The Divi page builder is running, but not as a plugin. It\'s probably in your theme which is currently disabled on AMP pages: you should probably enable it. See  <strong>Disable theme on AMP pages</strong> on the <strong>Comments and plugins</strong> tab.',
			               'weeblramp'
			);
			$options = array(
				'type'         => WblSystem_Notices::ALERT,
				'hide_after'   => WblSystem_Notices::DAY_2,
				'remove_after' => WblSystem_Notices::DAY_30
			);
			$this->notify(
				'config_check_divi_in_theme',
				$message,
				$options
			);
		} else {
			$this->removeNotification( 'config_check_divi_in_theme' );
		}
	}

	/**
	 * Checks whether the mu plugin was created and properly running.
	 *
	 * @return bool
	 */
	public function mupluginCreated() {

		$valid = class_exists( 'WeeblrampPluginsHandler' );
		if ( ! $valid ) {
			$message = __( 'We could not install the mu-plugin used by weeblrAMP to disable other plugins on AMP pages, which prevent using this feature. This usually happens when your server configuration prevents weeblrAMP to write in the WordPress MU-plugin directory. Your hosting company should be able to fix that.' );
			$options = array(
				'type'         => WblSystem_Notices::WARNING,
				'hide_after'   => WblSystem_Notices::DAY_2,
				'remove_after' => WblSystem_Notices::DAY_30,
				'dismissable'  => WblSystem_Notices::CANNOT_DISMISS
			);
			$this->notify(
				'config_check_mu_plugin_created',
				$message,
				$options
			);
		}

		return $valid;
	}

	/**
	 * Process an error notification according to current settings
	 *
	 * @param $options
	 */
	private function notify( $id, $message, $options ) {

		switch ( $this->notificationType ) {
			case self::LOG:
				WblSystem_Log::error( $message );
				break;
			case self::NOTIFY:
				$this->notifier->addTimedNotice(
					$id,
					$message,
					$options
				);
				break;
		}
	}

	/**
	 * Removes a previously displayed admin notification.
	 *
	 * @param $options
	 */
	private function removeNotification( $id ) {

		switch ( $this->notificationType ) {
			case self::NOTIFY:
				$this->notifier->removeTimedNotice( $id );
				break;
		}
	}
}
