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

if ( ! $this->hasDisplayData( 'analytics_data' ) ) {
	return;
}
$analyticsConfig = $this->getInArray( 'analytics_data', 'config' );
$triggers        = wbArrayGet(
	$analyticsConfig,
	WeeblrampConfig_user::ANALYTICS_STANDARD
);
?>
<!-- weeblrAMP: Google Analytics definition -->
<amp-analytics type="googleanalytics" id="wbamp_analytics_standard_1"
	<?php echo $this->getInArray( 'analytics_data', 'credentials' ); ?>
	<?php echo $this->getInArray( 'analytics_data', 'consent' ); ?>
>
	<?php
	echo '<script type="application/json">' . "\n";
	echo WblSystem_Strings::jsonPrettyPrintAndUnescapeSlashes( $triggers );
	echo "\n</script>\n";
	?>
</amp-analytics>
<!-- weeblrAMP: Google Analytics definition -->
