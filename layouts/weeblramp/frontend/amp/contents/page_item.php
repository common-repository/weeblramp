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

$requestType = $this->get( 'request_type' );

// read and apply user configuration for display
$isPasswordProtected = post_password_required( $this->get( 'post' ) );
$postTitle           = $this->getInObject( 'post', 'post_title' );
if ( $isPasswordProtected ) {
	$postTitle = WblWordpress_Html::getProtectedTitle( $postTitle, $this->get( 'post' ) );
}

$userConfig      = $this->get( 'user_config' );
$customizeConfig = $this->get( 'customize_config' );

/**
 * Filter whether to display an item featured image.
 *
 * Note: this can be set by user in settings
 *
 * @api
 * @package weeblrAMP\filter\output
 * @var weeblramp_item_show_featured_image
 * @since   1.0.0
 *
 * @param bool $showFeaturedImage If true, featured image is displayed on the page
 *
 * @return bool
 */
$showFeaturedImage = apply_filters(
	'weeblramp_item_show_featured_image',
	$customizeConfig->isTruthy( 'item_page_display_options', 'item_show_featured_image' ) && $this->hasDisplayData( 'featured_image', 'imgs' )
);
if ( $showFeaturedImage ) {
	$imgs          = $this->getInArray( 'featured_image', 'imgs' );
	$size          = apply_filters( 'weeblramp_item_featured_image_size', 'medium_large' );
	$featuredImage = wbArrayGet( $imgs, $size, 'medium_large' );
}

/**
 * Filter whether to display social sharing button.
 *
 * @api
 * @package weeblrAMP\filter\output
 * @var weeblramp_show_sharing_buttons
 * @since   1.0.0
 *
 * @param bool    $showSharingButtons If true, buttons are displayed on the page
 * @param string  $requestType Request type descriptor: page, post, home, search,...
 * @param WP_Post $post The global post object for the request
 *
 * @return bool
 */
$showSharingButtons = apply_filters(
	'weeblramp_show_sharing_buttons',
	$customizeConfig->isTruthy( 'item_page_display_options', 'item_show_sharing_buttons' ),
	$this->get( 'request_type' ),
	$this->get( 'post' )
);

/**
 * Filter whether to display comments on a page.
 *
 * Note: this can be set by user in settings
 *
 * @api
 * @package weeblrAMP\filter\comment
 * @var weeblramp_comments_show
 * @since   1.0.0
 *
 * @param bool    $showComments If true, comments are displayed on the page
 * @param string  $requestType Request type descriptor: page, post, home, search,...
 * @param WP_Post $post The global post object
 *
 * @return bool
 */
$showComments = apply_filters(
	'weeblramp_comments_show',
	$customizeConfig->isTruthy( 'comments_display_options', 'show' )
	&&
	'closed' != $this->getInObject( 'post', 'comment_status' ),
	$this->get( 'request_type' ),
	$this->get( 'post' )
);

// allow filtering of pagination links, which can be
// (optionally) amplified
$shouldAmplifyPagination = $this->get( 'router' )->shouldAmplifyPostPagination( $this->getInObject( 'post', 'post_type' ) );
if ( $shouldAmplifyPagination ) {
	add_filter( 'wp_link_pages_link', array( $this->get( 'router' ), 'filter_wp_link_pages_link' ), 10, 2 );
}

$postClasses = get_post_class( 'wbamp-block wbl-no-border', $this->getInObject( 'post', 'ID' ) );

?>
<div class="wbamp-content-item">

	<?php

	// top of page search box
	WeeblrampHelper_Features::maybeOutputSearchBox(
		WeeblrampConfig_Customize::SEARCH_BOX_CONTENT_TOP,
		$this->getDisplayData(),
		array(
			$this->get( 'amp_form_processor' ),
			'convert'
		)
	);

	// maybe add a language switcher
	WeeblrampHelper_Features::maybeOutputLanguageSwitcher(
		WeeblrampConfig_Customize::LANGUAGE_SWITCHER_CONTENT_TOP,
		$this->get( 'language_switcher' ),
		array(
			$this->get( 'amp_form_processor' ),
			'convert'
		)
	);

	// social buttons (before content)
	if ( $showSharingButtons && WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_BEFORE == $this->get( 'customize_config' )->get( 'social_buttons_location' ) ) {
		echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.social_buttons_' . $this->get( 'social_buttons_type' ), $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
	}

	// Optional ads
	echo WeeblrampHelper_Ads::get(
		WeeblrampConfig_User::ADS_BEFORE_CONTENT,
		$this->getDisplayData()
	);

	// display "before_content" widgets
	echo WeeblrampHelper_Widget::getWidgetAreaWidgets(
		'weeblramp_before_content',
		$requestType,
		$this->get( 'customize_config' )->isTruthy( 'item_page_display_options', 'item_show_widgets' )
	);

	?>
    <article id="post_<?php echo $this->getInObject( 'post', 'ID' ); ?>"
             class="<?php echo join( ' ', $postClasses ); ?>">
        <header class="wbamp-item-header">
            <h1><?php echo $this->escape( $postTitle ); ?></h1>
			<?php

			// social buttons (after author info block)
			if ( WeeblrampHelper_Version::isFullEdition() ) {
				if ( $showSharingButtons && WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_AFTER_INFO_BLOCK == $this->get( 'customize_config' )->get( 'social_buttons_location' ) ) {
					echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.social_buttons_' . $this->get( 'social_buttons_type' ), $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
				}
			}
			?>
        </header>

		<?php

		if ( $isPasswordProtected ):
			// specific format for password protected posts
			echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.contents.item_pwd', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
		else: ?>
			<?php if ( ! empty( $featuredImage ) ): ?>
                <div class="wbamp-featured-image">
					<?php echo $featuredImage; ?>
                </div>
			<?php endif; ?>
            <div class="wbamp-block wbl-no-border wbamp-item-content entry-content">
				<?php echo $this->get( 'content' ); ?>
            </div>
			<?php
			wp_link_pages(
				array(
					'before'      => '<div class="wbamp-block wbamp-page-links"><span class="wbamp-page-links-title">' . __( 'Page', 'weeblramp' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
					'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'weeblramp' ) . ' </span>%',
					'separator'   => '',
				)
			);
			?>

		<?php endif; ?>

    </article>
	<?php

	// widget area "before_comments"
	echo WeeblrampHelper_Widget::getWidgetAreaWidgets(
		'weeblramp_before_comments',
		$this->get( 'request_type' ),
		$this->get( 'customize_config' )->isTruthy( 'item_display_options', 'item_show_widgets' )
	);

	if ( $showComments ) {
		echo
		$this->get( 'content_protector' )
		     ->protect(
			     WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.contents.item_comments_' . $this->get( 'comment_type' ), $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH ),
			     array(
				     $this->get( 'amp_form_processor' ),
				     'convert'
			     )
		     );
	}

	// display "after_content" widgets
	echo WeeblrampHelper_Widget::getWidgetAreaWidgets(
		'weeblramp_after_content',
		$requestType,
		$this->get( 'customize_config' )->isTruthy( 'item_page_display_options', 'item_show_widgets' )
	);

	if ( $shouldAmplifyPagination ) {
		remove_filter( 'wp_link_pages_link', array( $this->get( 'router' ), 'filter_wp_link_pages_link' ), 10 );
	}

	// social buttons (after content)
	if ( $showSharingButtons && WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_AFTER == $this->get( 'customize_config' )->get( 'social_buttons_location' ) ) {
		echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.social_buttons_' . $this->get( 'social_buttons_type' ), $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );
	}

	// Optional ads
	echo WeeblrampHelper_Ads::get(
		WeeblrampConfig_User::ADS_AFTER_CONTENT,
		$this->getDisplayData()
	);

	// bottom of page search box
	WeeblrampHelper_Features::maybeOutputSearchBox(
		WeeblrampConfig_Customize::SEARCH_BOX_CONTENT_BOTTOM,
		$this->getDisplayData(),
		array(
			$this->get( 'amp_form_processor' ),
			'convert'
		)
	);

	// maybe add a language switcher
	WeeblrampHelper_Features::maybeOutputLanguageSwitcher(
		WeeblrampConfig_Customize::LANGUAGE_SWITCHER_CONTENT_BOTTOM,
		$this->get( 'language_switcher' ),
		array(
			$this->get( 'amp_form_processor' ),
			'convert'
		)
	);

	?>
</div>
</div>
