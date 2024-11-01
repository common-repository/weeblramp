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
/**
 * Filter whether to display social sharing button on the search results page.
 *
 * @api
 * @package weeblrAMP\filter\output
 * @var weeblramp_show_sharing_buttons_search
 * @since   1.0.0
 *
 * @param bool $showSharingButtons If true, buttons are displayed on the page
 *
 * @return bool
 */
$showSharingButtons = apply_filters(
	'weeblramp_show_sharing_buttons_search',
	$customizeConfig
		->isTruthy(
			'item_search_display_options',
			'search_show_sharing_buttons'
		)
);

?>
<div class="wbamp-block wbamp-content  wbl-no-border">
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
	if (
		$showSharingButtons
		&&
		// don't show on "nothing found" page
		$this->hasDisplayData( 'main_content' )
		&&
		in_array(
			$this->get( 'customize_config' )->get( 'social_buttons_location' ),
			array(
				WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_BEFORE,
				WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_AFTER_INFO_BLOCK
			)
		)
	) {
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
		$customizeConfig->isTruthy( 'item_search_display_options', 'search_show_widgets' )
	);

	?>
    <header class="wbamp-page-header">
        <h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'weeblramp' ), '<span>' . esc_html( get_search_query() ) . '</span>' ); ?></h1>
    </header>

	<?php

	// actual search results
	$contentData = $this->getAsArray( 'main_content' );
	if ( ! empty( $contentData ) ) {
		foreach ( $contentData as $contentItem ) {
			// export to globals current post, for pagination
			$wp_query->setup_postdata( $contentItem['post'] );

			echo WblMvcLayout_Helper::render(
				'weeblramp.frontend.amp.contents.' . $requestType . '_item',
				array_merge(
					$this->getDisplayData(),
					array(
						'post'           => $contentItem['post'],
						'request_type'   => $requestType,
						'content'        => $contentItem['content'],
						'featured_image' => $contentItem['featured_image'],
						'excerpt'        => $contentItem['excerpt']
					)
				),
				WEEBLRAMP_LAYOUTS_PATH
			);
		}

		// optional pagination
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

	// display "before_content" widgets
	echo WeeblrampHelper_Widget::getWidgetAreaWidgets(
		'weeblramp_after_content',
		$requestType,
		$customizeConfig->isTruthy( 'item_search_display_options', 'search_show_widgets' )
	);

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
