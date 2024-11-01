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
 * Integration with Yoast SEO
 *
 */
class WeeblrampIntegration_Wordpressseo extends WeeblrampClass_Integration {

	protected $id = 'wordpress-seo/wp-seo.php';

	protected $filters = array(
		array(
			'filter_name'   => 'weeblramp_config_set_defaults',
			'method'        => 'setConfigDefaults',
			'priority'      => 10,
			'accepted_args' => 1
		),
		array(
			'filter_name'   => 'weeblramp_json_manifest',
			'method'        => 'getJsonLd',
			'priority'      => 10,
			'accepted_args' => 2
		),
		array(
			'filter_name'   => 'weeblramp_get_metadata',
			'method'        => 'getMetadata',
			'priority'      => 10,
			'accepted_args' => 2
		),
		array(
			'filter_name'   => 'weeblramp_get_ogp',
			'method'        => 'getOgp',
			'priority'      => 10,
			'accepted_args' => 2
		),
		array(
			'filter_name'   => 'weeblramp_get_tcards',
			'method'        => 'getTCards',
			'priority'      => 10,
			'accepted_args' => 2
		)
	);

	/**
	 * Pull some data from Yoast to serve as default values for config
	 *
	 * @param $config
	 *
	 * @return mixed
	 */
	public function setConfigDefaults( $config ) {

		// not enabled or not able to instantiate WPSEO, leave
		if ( ! $this->isEnabled() ) {
			return $config;
		}

		// if not active, disable the integration
		if ( ! $this->active && ! empty( $this->id ) ) {
			// site name
			$config['integrations_list'][ $this->id ] = 0;
		}

		// site name
		$config['site_name'] = wbInitEmpty( $config['site_name'], WeeblrampIntegrationWordpressseo_Compat::getOptionsOrPageData( 'site_name', '' ) );

		// analytics id
		if ( empty( $config['analytics_webproperty_id'] ) ) {
			if ( class_exists( 'Yoast_GA_Options' ) ) {
				$id = Yoast_GA_Options::instance()->get_tracking_code();
				if ( ! empty( $id ) ) {
					$config['analytics_webproperty_id'] = $id;
				}
			}
		}

		// publisher info, entered under Company name
		if ( empty( $config['publisher_name'] ) ) {
			$config['publisher_name'] = WeeblrampIntegrationWordpressseo_Compat::getOptionsOrPageData( 'company_name', '' );
		}

		if ( empty( $config['publisher_image'] ) ) {
			$image = WeeblrampIntegrationWordpressseo_Compat::getCompanyLogo();
			if ( ! empty( $image ) ) {
				$imageSize = WblHtmlContent_Image::getImageSize( $image );
				if ( WeeblrampClass_Configcheck::checkPublisherLogoSize( $imageSize['width'], $imageSize['height'] ) ) {
					$config['publisher_image']        = $image;
					$config['publisher_image_width']  = $imageSize['width'];
					$config['publisher_image_height'] = $imageSize['height'];
				}
			}
		}

		// Facebook App ID
		if ( empty( $config['facebook_app_id'] ) ) {
			$config['facebook_app_id'] = WPSEO_Options::get( 'fbadminapp', '' );
		}

		// Twitter account
		if ( empty( $config['twitter_account'] ) ) {
			$account                   = WeeblrampIntegrationWordpressseo_Compat::getOptionsOrPageData( 'twitter_site', '' );
			$config['twitter_account'] = empty( $account ) ? '' : '@' . $account;
		}

		// social profiles
		if ( empty( $config['struct_profiles_social'] ) ) {
			$profiles = array();

			// twitter
			if ( ! empty( $config['twitter_account'] ) ) {
				$profiles[] = 'https://www.twitter.com/' . WeeblrampIntegrationWordpressseo_Compat::getOptionsOrPageData( 'twitter_site', '' );
			}

			$options = array(
				'facebook_site',
				'instagram_url',
				'linkedin_url',
				'myspace_url',
				'pinterest_url',
				'youtube_url'
			);
			foreach ( $options as $option ) {
				$value = WPSEO_Options::get( $option, '' );
				if ( ! empty( $value ) ) {
					$profiles[] = $value;
				}
			}

			if ( ! empty( $profiles ) ) {
				$config['struct_profiles_social'] = implode( PHP_EOL, $profiles );
			}
		}

		return $config;
	}

	/**
	 * Read main OGP data from Yoast, to get per page, user-defined values
	 *
	 * @param $ogp
	 *
	 * @return mixed
	 */
	public function getOgp( $ogp, $pageData ) {

		// not enabled or not able to instantiate WPSEO, leave
		if ( ! $this->isEnabled() ) {
			return $ogp;
		}

		// we can get title, description and image
		$ogp['title']       = wbInitEmpty( WeeblrampIntegrationWordpressseo_Compat::getOgpTitle(), $ogp['title'] );
		$ogp['description'] = wbInitEmpty( WeeblrampIntegrationWordpressseo_Compat::getOgpDescription(), $ogp['description'] );

		if ( 1 == count( $pageData['main_content'] ) ) {
			$image = WeeblrampIntegrationWordpressseo_Compat::getOgpImage( $pageData['main_content'][0]['post']->ID );
			if ( ! empty( $image ) ) {
				$imageSize           = WblHtmlContent_Image::getImageSize( $image );
				$ogp['image']        = $image;
				$ogp['image_width']  = $imageSize['width'];
				$ogp['image_height'] = $imageSize['height'];
			}
		}

		return $ogp;
	}

	/**
	 * Get Twitter cards per page user-defined values
	 *
	 * @param $tcards
	 * @param $pageData
	 *
	 * @return mixed
	 */
	public function getTCards( $tcards, $pageData ) {

		// not enabled or not able to instantiate WPSEO, leave
		if ( ! $this->isEnabled() ) {
			return $tcards;
		}

		$tcards['title']       = wbInitEmpty( WeeblrampIntegrationWordpressseo_Compat::getTCardsTitle(), $tcards['title'] );
		$tcards['description'] = wbInitEmpty( WeeblrampIntegrationWordpressseo_Compat::getTCardsDescription(), $tcards['description'] );
		if ( 1 == count( $pageData['main_content'] ) ) {
			$image = WeeblrampIntegrationWordpressseo_Compat::getTCardsImage();
			if ( ! empty( $image ) ) {
				$imageSize              = WblHtmlContent_Image::getImageSize( $image );
				$tcards['image']        = $image;
				$tcards['image_width']  = $imageSize['width'];
				$tcards['image_height'] = $imageSize['height'];
			}
		}

		return $tcards;
	}

	public function getJsonLd( $jsonld, $data ) {

		// not enabled or not able to instantiate WPSEO, leave
		if ( ! $this->isEnabled() ) {
			return $jsonld;
		}

		return $jsonld;
	}

	/**
	 * @param $metaData
	 *
	 * @return mixed
	 */

	public function getMetadata( $metaData, $pageData ) {

		// not enabled or not able to instantiate WPSEO, leave
		if ( ! $this->isEnabled() ) {
			return $metaData;
		}

		// we're good, collect information from wpseo
		$description = WeeblrampIntegrationWordpressseo_Compat::getMetaDescription();
		if ( $description ) {
			$metadata['description'] = $description;
		}

		return $metaData;
	}

	/**
	 * Returns true if this integration is available, ie if the
	 * corresponding plugin or service is installed and activated
	 *
	 * @return bool
	 */
	protected function discover() {

		return defined( 'WPSEO_FILE' );
	}
}
