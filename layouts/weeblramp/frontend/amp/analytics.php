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

if ( ! $this->get( 'user_config' )->isAnalyticsEnabled() ) {
	return;
}

?>

<!-- weeblrAMP: Analytics definition -->

<?php
$analyticsTypes = $this->get( 'user_config' )->get( 'analytics_type' );
$output         = array();
foreach ( $analyticsTypes as $analyticsType => $enabled ) {
	if ( $enabled ) {
		$layoutName = 'weeblramp.frontend.amp.tags.analytics_' . $analyticsType;
		$output[]   = WblMvcLayout_Helper::render(
			$layoutName,
			$this->getDisplayData(),
			WEEBLRAMP_LAYOUTS_PATH
		);
	}
}
echo implode(
	"\n",
	$output
)
?>

<!-- weeblrAMP: Analytics definition -->
