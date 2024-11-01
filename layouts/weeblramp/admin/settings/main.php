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

$systemConfig = WeeblrampFactory::getThe( 'weeblramp.config.system' );
$ampLogo      = $systemConfig->get( 'assets.amp_logo', '' );
$weeblrAMP    = $systemConfig->get( 'assets.settings_logo', '' );
$editionLogo  = $systemConfig->get( 'assets.' . WeeblrampHelper_Version::getEdition() . '_logo', '' );

?>
<div class="wrap weeblramp-settings">
    <h1 class="wbamp-settings-header">
        <span class="wbamp-weeblramp-logo" title="<?php
        echo esc_attr(
	        WeeblrampHelper_Version::getDisplayableVersionInfo(
		        array( 'title', 'version_simple' )
	        )
        );
        ?>"><?php echo $weeblrAMP; ?></span>
		<?php if ( ! empty( $editionLogo ) ) : ?>
            <span class="wbamp-weeblramp-sep"></span>
            <span class="wbamp-edition-logo" title="<?php
			echo esc_attr(
				WeeblrampHelper_Version::getDisplayableVersionInfo(
					array( 'edition' )
				)
			);
			?>"><?php echo $editionLogo; ?></span>
		<?php endif; ?>
        <span class="wbamp-weeblramp-sep"></span>
        <span class="wbamp-weeblramp-area"><?php echo $this->getEscaped( 'title' ); ?></span>
        <span class="wbamp-amp-logo">
            <a href="https://www.ampproject.org/" title="<?php echo __( 'Visit the AMP project website.', 'weeblramp' ); ?>"
               target="_blank">
                <?php echo $ampLogo; ?>
            </a>
        </span>
    </h1>

    <p class="js-wbamp-no-javascript">Loading settings...</p>

	<?php

	echo WeeblrampFactory::getThe( 'WblSystem_Notices' )->adminActionRenderNotices();
	settings_errors();
	echo WblMvcLayout_Helper::render( 'weeblramp.admin.settings.header', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );

	?>

    <div class="content-box">
        <form method="post" action="options.php" id="js-wbamp-settings-form" name="wbampSettingsForm">
			<?php

			// settings tabs start
			echo WblMvcLayout_Helper::render( 'wblib.settings.tabs-header', $this->getDisplayData(), WBLIB_LAYOUTS_PATH );

			// hidden fields rendered by WP
			settings_fields( $this->get( 'settings_page' ) );

			// our settings, rendered
			//echo $__data['rendered_settings_page'];
			echo $this->get( 'rendered_settings_page' );

			// tabs closing html
			echo WblMvcLayout_Helper::render( 'wblib.settings.tabs-footer', $this->getDisplayData(), WBLIB_LAYOUTS_PATH );

			// the submit button
			submit_button(
				$text = null,
				$type = 'primary js-wbamp-setting-submit'
			);
			?>
        </form>
    </div>

</div>
