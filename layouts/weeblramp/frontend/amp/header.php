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

?>
    <div class="wbamp-nav-container">
        <nav
                class="wbamp-header <?php echo ! $this->hasDisplayData( 'site_image' ) ? '' : 'wbamp-header-image wbamp-header-no-background'; ?> <?php echo ! $this->hasDisplayData( 'site_name' ) ? '' : 'wbamp-header-site-name'; ?>">
            <a class="wbamp-site-logo"
               href="<?php echo $this->getAsAbsoluteUrl( 'site_link', '/' ); ?>">
                <div class="wbamp-header-block">
					<?php if ( $this->hasDisplayData( 'site_image' ) ) : ?>
                        <div class="wbamp-header-sub-block wbamp-site-image-wrapper">
                            <amp-img
                                    alt="<?php echo esc_attr( sprintf( __( '%s logo', 'weeblramp' ), $this->getEscaped( 'site_name' ) ) ); ?>"
                                    src="<?php echo $this->getAsAbsoluteUrl( 'site_image' ); ?>"
                                    width="<?php echo $this->getInArray( 'site_image_size', 'width' ); ?>"
                                    height="<?php echo $this->getInArray( 'site_image_size', 'height' ); ?>"
                                    class="amp-wp-enforced-sizes wbamp-site-image"
                            >
                            </amp-img>
                        </div>
					<?php endif; ?>
					<?php if ( $this->hasDisplayData( 'site_name' ) ) : ?>
                        <div class="wbamp-header-sub-block wbamp-header-text">
							<?php echo $this->getEscaped( 'site_name' ); ?>
                        </div>
					<?php endif; ?>
                </div>
				<?php if ( $this->hasDisplayData( 'site_tag_line' ) ) : ?>
                    <div class="wbamp-header-text-tag-line<?php echo 'table' == $this->get( 'customize_config' )->get( 'image_align_header' ) ? ' wbl-txt-center' : ''; ?>">
						<?php echo $this->getEscaped( 'site_tag_line' ); ?>
                    </div>
				<?php endif; ?>
            </a>
        </nav>
		<?php

		// maybe add a search icon
		WeeblrampHelper_Features::maybeOutputSearchBox(
			WeeblrampConfig_Customize::SEARCH_BOX_HEADER_TOP,
			$this->getDisplayData(),
			array(
				$this->get( 'amp_form_processor' ),
				'convert'
			)
		);

		// maybe add a language switcher
		WeeblrampHelper_Features::maybeOutputLanguageSwitcher(
			WeeblrampConfig_Customize::LANGUAGE_SWITCHER_HEADER_TOP,
			$this->get( 'language_switcher' ),
			array(
				$this->get( 'amp_form_processor' ),
				'convert'
			)
		);
		?>
    </div>
<?php

// maybe add a search icon
WeeblrampHelper_Features::maybeOutputSearchBox(
	WeeblrampConfig_Customize::SEARCH_BOX_HEADER_BOTTOM,
	$this->getDisplayData(),
	array(
		$this->get( 'amp_form_processor' ),
		'convert'
	)
);

// maybe add a language switcher
WeeblrampHelper_Features::maybeOutputLanguageSwitcher(
	WeeblrampConfig_Customize::LANGUAGE_SWITCHER_HEADER_BOTTOM,
	$this->get( 'language_switcher' ),
	array(
		$this->get( 'amp_form_processor' ),
		'convert'
	)
);

