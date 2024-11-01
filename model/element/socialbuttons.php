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

class WeeblrampModelElement_Socialbuttons extends WeeblrampClass_Base {

	public function getData( $currentData ) {

		$socialData = array();
		if ( WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_NONE != $this->customizeConfig->get( 'social_buttons_location' ) ) {
			$socialData['types'] = $this->customizeConfig->get( 'social_buttons_types' );
			$socialData['theme'] = $this->customizeConfig->get( 'social_buttons_theme' );
			$socialData['style'] = $this->customizeConfig->get( 'social_buttons_style' );
		}

		// return processed content and possibly required AMP scripts
		$result = array(
			'data'    => $socialData,
			'scripts' => array(),
			'styles'  => 'social'
		);

		if ( WeeblrampConfig_Customize::SOCIAL_BUTTONS_TYPE_AMPSOCIAL == $this->customizeConfig->get( 'social_buttons_type' ) ) {
			$result['scripts'] = array(
				'amp-social-share' => sprintf( WeeblrampModel_Renderer::AMP_SCRIPTS_PATTERN, 'social-share', WeeblrampModel_Renderer::AMP_SCRIPTS_VERSION )
			);
		}

		return $result;
	}
}
