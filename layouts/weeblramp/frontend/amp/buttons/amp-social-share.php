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

// no direct access
defined( 'WEEBLRAMP_EXEC' ) || die;

// filter buttons
$buttonType = $this->get( 'button_type' );

/**
 * Filter width and height of social buttons to display.
 *
 * @api
 * @package weeblrAMP\filter\output
 * @var weeblramp_sharing_buttons_dimensions
 * @since   1.3.0
 *
 * @param array $socialButtonsDimensions Array of integer dimensions, indexed with 'width' and 'height'.
 *
 * @return bool
 */
$this->__data['social_buttons_dimensions'] = apply_filters(
	'weeblramp_sharing_buttons_dimensions',
	array(
		'width'  => $this->get( 'system_config' )->get( 'sizes.social_buttons_width' ),
		'height' => $this->get( 'system_config' )->get( 'sizes.social_buttons_height' )
	)
);

// optional extra attributes
$extraAttributes = array();
switch ( $buttonType ) {
	case 'facebook_share':
		$buttonType      = 'facebook';
		$extraAttributes = array(
			'data-param-app_id' => $this->get( 'user_config' )->get( 'facebook_app_id' )
		);
		if ( empty( $extraAttributes['data-param-app_id'] ) ) {
			// don't display button if no app_id
			return;
		}
		break;
	case 'linkedin_share':
		$buttonType = 'linkedin';
		break;
	case 'pinterest_share':
		$buttonType = 'pinterest';
		break;
	case 'twitter_share':
		$buttonType = 'twitter';
		break;
	case 'whatsapp_share':
		// not with amp-social-share yet
		return;
		break;
}

// attributes to html
$extraAttributes = WblHtml_Helper::attrToHtml( $extraAttributes );

?>
<amp-social-share type="<?php echo esc_attr( $buttonType ); ?>"
                  width="<?php echo esc_attr( $this->getInArray( 'social_buttons_dimensions', 'width' ) ); ?>"
                  height="<?php echo esc_attr( $this->getInArray( 'social_buttons_dimensions', 'height' ) ); ?>" <?php echo $extraAttributes; ?>>
</amp-social-share>
