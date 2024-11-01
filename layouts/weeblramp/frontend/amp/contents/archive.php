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

global $wp_query;

$requestType     = $this->get( 'request_type' );
$userConfig      = $this->get( 'user_config' );
$customizeConfig = $this->get( 'customize_config' );
$displayConfig   = $customizeConfig->get( 'item_category_display_options' );
$showHeader      = $customizeConfig->isTruthy( 'item_category_display_options', 'category_show_title' ) || $customizeConfig->isTruthy( 'item_category_display_options', 'category_show_description' );

/**
 * Filter whether to display social sharing button on a category page.
 *
 * @api
 * @package weeblrAMP\filter\output
 * @var weeblramp_show_sharing_buttons_category
 * @since   1.0.0
 *
 * @param bool $showSharingButtons If true, buttons are displayed on the page
 *
 * @return bool
 */
$showSharingButtons = apply_filters(
	'weeblramp_show_sharing_buttons_category',
	$customizeConfig->isTruthy( 'item_category_display_options', 'category_show_sharing_buttons' )
);

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
if ( $showSharingButtons && WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_BEFORE == $customizeConfig->get( 'social_buttons_location' ) ) {
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
	$customizeConfig->isTruthy( 'item_category_display_options', 'category_show_widgets' )
);

?>
    <div class="wbamp-content wbamp-block  wbl-no-border">

		<?php

		if ( $showHeader ): ?>
            <header class="wbamp-page-header">
				<?php
				if ( $customizeConfig->isTruthy( 'item_category_display_options', 'category_show_title' ) ) {
					if ( $wp_query->is_posts_page ) {
						$postsPageId = get_option( 'page_for_posts' );
						echo '<h1 class="page-title">' . $this->escape( get_the_title( $postsPageId ) ) . '</h1>';
					} else {
						the_archive_title( '<h1 class="page-title">', '</h1>' );
					}
				}
				if ( $customizeConfig->isTruthy( 'item_category_display_options', 'category_show_description' ) ) {
					the_archive_description(
						'<div class="taxonomy-description">',
						'</div>'
					);
				}
				?>
            </header>
		<?php endif; ?>
		<?php

		$contentData = $this->getAsArray( 'main_content' );
		if ( ! empty( $contentData ) ) {
			foreach ( $contentData as $contentItem ) {
				// export to globals current post, for pagination
				$wp_query->setup_postdata( $contentItem['post'] );

				// allow filtering of pagination links, which can be
				// (optionally) amplified
				// NB: only working for pages and post. requires a bit of work on rewrite rules
				// for it to be ok in a more general way.
				$shouldAmplifyPagination = $this->get( 'router' )->shouldAmplifyPostPagination( $contentItem['post']->post_type );
				if ( $shouldAmplifyPagination ) {
					// individual posts
					add_filter( 'wp_link_pages_link', array( $this->get( 'router' ), 'filter_wp_link_pages_link' ), 10, 2 );
				}

				echo WblMvcLayout_Helper::render(
					'weeblramp.frontend.amp.contents.' . $requestType . '_item',
					array_merge(
						$this->getDisplayData(),
						array(
							'post'           => $contentItem['post'],
							'request_type'   => $requestType,
							'content'        => $contentItem['content'],
							'featured_image' => $contentItem['featured_image'],
							'excerpt'        => $contentItem['excerpt'],
							// config
							'user_config'    => $userConfig
						)
					),
					WEEBLRAMP_LAYOUTS_PATH
				);

				if ( $shouldAmplifyPagination ) {
					remove_filter(
						'wp_link_pages_link',
						array(
							$this->get( 'router' ),
							'filter_wp_link_pages_link'
						),
						10
					);
				}
			}

			// Previous/next page navigation.
			ob_start();
			add_filter( 'paginate_links', array( $this->get( 'router' ), 'filter_paginate_links' ), 10, 2 );
			// Previous/next page navigation.
			the_posts_pagination(
				array(
					'prev_text'          => __( '«', 'weeblramp' ),
					'next_text'          => __( '»', 'weeblramp' ),
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'weeblramp' ) . ' </span>',
				)
			);
			remove_filter( 'paginate_links', array( $this->get( 'router' ), 'filter_paginate_links' ), 10 );
			$pagination = ob_get_clean();
			if ( ! empty( $pagination ) ) {
				echo '<div class="wbamp-block wbamp-block-pagination">' . $pagination . '</div>';
			}
		} else {
			// no item to display
			echo WblMvcLayout_Helper::render(
				'weeblramp.frontend.amp.contents.not-found',
				$this->getDisplayData(),
				WEEBLRAMP_LAYOUTS_PATH
			);
		}

		?>
    </div>

<?php

// display "before_content" widgets
echo WeeblrampHelper_Widget::getWidgetAreaWidgets(
	'weeblramp_after_content',
	$requestType,
	$customizeConfig->isTruthy( 'item_category_display_options', 'category_show_widgets' )
);

// social buttons (after content)
if ( $showSharingButtons && WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_AFTER == $customizeConfig->get( 'social_buttons_location' ) ) {
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

