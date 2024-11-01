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

// no direct access
defined( 'WEEBLRAMP_EXEC' ) || die;

$userConfig      = $this->get( 'user_config' );
$customizeConfig = $this->get( 'customize_config' );

?>
<!doctype html>
<html <?php language_attributes(); ?> amp>

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1">
	<?php echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.head', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH ); ?>
</head>

<body>
<?php

// if in development mode, and user did not disable it, show the debug module
if ( WeeblrampHelper_Route::shouldShowDebugModule() ) {
	echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.debug_module', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
}

if (
	WeeblrampConfig_Customize::LINK_TO_SITE_TOP == $customizeConfig->get( 'show_link_to_main_site' )
	||
	WeeblrampConfig_Customize::LINK_TO_SITE_TOP_BOTTOM == $customizeConfig->get( 'show_link_to_main_site' )
) {
	echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.link_to_main', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
}

// sidebar
echo $this->get( 'tags_menus' );

?>
<div class="wbamp-body-background">
	<?php

	echo $this->get( 'rendered_body' );

	?>
</div>

<div class="wbamp-wrapper-footer wbl-margin-top">
	<?php
	echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.footer', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
	?>
</div>

<?php

// bottom of page
if ( $this->hasDisplayData( 'page_bottom' ) ) {
	echo $this->get( 'page_bottom' );
}

// standard link to main site, bottom of page
if (
	WeeblrampConfig_Customize::LINK_TO_SITE_BOTTOM == $customizeConfig->get( 'show_link_to_main_site' )
	||
	WeeblrampConfig_Customize::LINK_TO_SITE_TOP_BOTTOM == $customizeConfig->get( 'show_link_to_main_site' )
) {
	echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.link_to_main', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
}

// user notification
if ( $this->hasDisplayData( 'user-notification' ) ) {
	echo WblMvcLayout_Helper::render(
		'weeblramp.frontend.amp.tags.user-notification',
		array_merge(
			array( 'assets_collector' => $this->get( 'assets_collector' ) ),
			$this->getAsArray( 'user-notification' )
		),
		WEEBLRAMP_LAYOUTS_PATH
	);
}

// Link to main site as an amp-notification
if ( WeeblrampConfig_Customize::LINK_TO_SITE_NOTIFICATION == $customizeConfig->get( 'show_link_to_main_site' ) && $customizeConfig->isTruthy( 'link_to_main_site_text' ) ) {
	echo WblMvcLayout_Helper::render(
		'weeblramp.frontend.amp.tags.user-notification',
		array_merge(
			array( 'assets_collector' => $this->get( 'assets_collector' ) ),
			$this->getAsArray( 'link_to_main_site' )
		),
		WEEBLRAMP_LAYOUTS_PATH
	);
}

// analytics
echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.analytics', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );

?>
</body>
</html>
