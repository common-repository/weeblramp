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

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Settings definition for weeblrAMP Visual customization
 *
 * Used by class WeeblrampConfig_Customize
 */
return array(
	// Main tab ------------------------------------------------------------
	array(
		'name'    => 'page_layout',
		'type'    => WblSystem_Config::OPTION_TAB,
		'title'   => __( 'Page layout', 'weeblramp' ),
		'content' => array(

			// Search box --------------------------------------------------------------
			array(
				'name'     => 'section_search_box',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Search box', 'weeblramp' ),
			),

			array(
				'name'      => 'search_box_location',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Search box location', 'weeblramp' ),
				'desc'      => __( 'Select whether to display search box on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_a_search_box',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_Customize::SEARCH_BOX_NONE,
				'class'     => '',
				'content'   => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::SEARCH_BOX_NONE           => __( 'None', 'weeblramp' ),
						WeeblrampConfig_Customize::SEARCH_BOX_CONTENT_TOP    => __( 'Before content', 'weeblramp' ),
						WeeblrampConfig_Customize::SEARCH_BOX_CONTENT_BOTTOM => __( 'After content', 'weeblramp' ),
						WeeblrampConfig_Customize::SEARCH_BOX_HEADER_TOP     => __( 'Top of header', 'weeblramp' ),
						WeeblrampConfig_Customize::SEARCH_BOX_HEADER_BOTTOM  => __( 'Bottom of header', 'weeblramp' ),
						WeeblrampConfig_Customize::SEARCH_BOX_MENU_TOP       => __( 'Top of menu', 'weeblramp' ),
						WeeblrampConfig_Customize::SEARCH_BOX_MENU_BOTTOM    => __( 'Bottom of menu', 'weeblramp' ),
					),
				)
			),

			// Language switcher -------------------------------------------------------
			array(
				'name'     => 'section_language_switcher',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Language switcher', 'weeblramp' ),
				'show-if'  => function () {

					return array();
				},
			),

			array(
				'name'      => 'language_switcher_location',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Language switcher location', 'weeblramp' ),
				'desc'      => __( 'Select whether to display a language switcher on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_language_switcher',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_Customize::LANGUAGE_SWITCHER_NONE,
				'class'     => '',
				'content'   => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_NONE           => __( 'None', 'weeblramp' ),
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_CONTENT_TOP    => __( 'Before content', 'weeblramp' ),
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_CONTENT_BOTTOM => __( 'After content', 'weeblramp' ),
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_HEADER_TOP     => __( 'Top of header', 'weeblramp' ),
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_HEADER_BOTTOM  => __( 'Bottom of header', 'weeblramp' ),
					),
				)
			),

			array(
				'name'      => 'language_switcher_layout',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Language switcher layout', 'weeblramp' ),
				'desc'      => __( 'Select how the language switcher should be laid out on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_language_switcher',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_Customize::LANGUAGE_SWITCHER_FLAGS,
				'class'     => '',
				'content'   => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_FLAGS            => __( 'Flags', 'weeblramp' ),
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_NAMES            => __( 'Names', 'weeblramp' ),
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_NAMES_HORIZONTAL => __( 'Names (horizontal)', 'weeblramp' ),
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_FLAGS_NAMES      => __( 'Flags and names', 'weeblramp' ),
						WeeblrampConfig_Customize::LANGUAGE_SWITCHER_DROPDOWN_NAMES   => __( 'Dropdown of names', 'weeblramp' ),
					),
				)
			),

			// Link to main site module ------------------------------------------------
			array(
				'name'     => 'section_link_to_main_site',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Announcement box', 'weeblramp' ),
			),

			array(
				'name'      => 'show_link_to_main_site',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Show announcement', 'weeblramp' ),
				'desc'      => __( 'Display an announcement box on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_an_announcement_box',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_Customize::LINK_TO_SITE_NONE,
				'class'     => '',
				'content'   => array(
					'attr'             => array(),
					'options'          => array(
						WeeblrampConfig_Customize::LINK_TO_SITE_NOTIFICATION => __( 'As an AMP notification', 'weeblramp' ),
						WeeblrampConfig_Customize::LINK_TO_SITE_NONE         => __( 'Do not show', 'weeblramp' ),
						WeeblrampConfig_Customize::LINK_TO_SITE_TOP          => __( 'Top of page', 'weeblramp' ),
						WeeblrampConfig_Customize::LINK_TO_SITE_BOTTOM       => __( 'Bottom of page', 'weeblramp' ),
						WeeblrampConfig_Customize::LINK_TO_SITE_TOP_BOTTOM   => __( 'Top and bottom of page', 'weeblramp' ),
					),
					'options_callback' => array()
				)
			),

			array(
				'name'      => 'link_to_main_site_text',
				'type'      => WblSystem_Config::OPTION_TEXTAREA,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Message text', 'weeblramp' ),
				'desc'      => __( 'This text will be displayed on AMP pages. You can enter raw HTML, so be very careful with your entry.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_an_announcement_box',
				'doc_embed' => true,
				'default'   => 'This is the superfast but simplified version of our site:',
				'class'     => '',
				'show-if'   => array(
					'id'      => 'show_link_to_main_site',
					'include' => array(),
					'exclude' => array( 'none' )
				),
				'content'   => array(
					'attr' => array(
						'cols'  => 60,
						'rows'  => 10,
						'class' => 'regular-text code',
					)
				)
			),

			array(
				'name'      => 'link_to_main_site_link_text',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Link text', 'weeblramp' ),
				'desc'      => __( 'If you have entered some text above to inform your users, you can add here the text for a link. It will be added to the text above as a button.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_an_announcement_box',
				'doc_embed' => true,
				'default'   => 'Go to full site',
				'class'     => '',
				'show-if'   => array(
					'id'      => 'show_link_to_main_site',
					'include' => array( '' ),
					'exclude' => array( 'none' )
				),
				'content'   => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'      => 'link_to_main_site_link_url',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Link url', 'weeblramp' ),
				'desc'      => __( 'If you have added a link text above, you can enter a URL to link to. Default is to link to the current AMP page standard HTML version.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_an_announcement_box',
				'doc_embed' => true,
				'default'   => '[weeblramp_current_page_non_amp]',
				'class'     => '',
				'show-if'   => array(
					'id'      => 'show_link_to_main_site',
					'include' => array( '' ),
					'exclude' => array( 'none' )
				),
				'content'   => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'      => 'link_to_main_site_theme',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Link theme', 'weeblramp' ),
				'desc'      => __( 'Choose the theme that matches best your website colors.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_an_announcement_box',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_Customize::NOTIFICATION_THEME_DARK,
				'class'     => '',
				'show-if'   => array(
					'id'      => 'show_link_to_main_site',
					'include' => array(),
					'exclude' => array( 'none' )
				),
				'content'   => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::NOTIFICATION_THEME_DARK  => __( 'Dark', 'weeblramp' ),
						WeeblrampConfig_Customize::NOTIFICATION_THEME_LIGHT => __( 'Light', 'weeblramp' ),
					),
				)
			),

			// Footer section ----------------------------------------------------------
			array(
				'name'     => 'section_footer',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Footer', 'weeblramp' ),
			),

			array(
				'name'      => 'show_footer',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Show footer', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled, the footer content you create in next field will be displayed on all AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_a_footer',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'footertext',
				'type'     => WblSystem_Config::OPTION_EDITOR,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Footer text', 'weeblramp' ),
				'desc'     => __( 'This text will be displayed at the bottom of all AMP pages. You can enter raw HTML, so be very careful in your entry. Tip: use the shortcode [weeblramp_current_year], it will be replaced by the current year. Useful for copyright mention!', 'weeblramp' ),
				'default'  => '(c) ' . WblWordpress_Helper::getSiteName() . ' - [weeblramp_current_year]',
				'show-if'  => array(
					'id'      => 'show_footer',
					'include' => 'checked',
				),
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'cols'  => 60,
						'rows'  => 10,
						'class' => 'wbamp-editor',
					)
				)
			),

			// User notification -------------------------------------------------------
			array(
				'name'     => 'section_user_notification',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'User notification', 'weeblramp' ),
			),

			array(
				'name'      => 'notification_enabled',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'User notifications', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled, a user notification will be displayed on AMP Pages. Fill the text to display in next fields.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html#h3_an_amp_user_notification',
				'doc_embed' => true,
				'default'   => 0,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'notification_text',
				'type'     => WblSystem_Config::OPTION_TEXTAREA,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Notification text', 'weeblramp' ),
				'desc'     => __( 'This text will be used to display a user notification on page load. You can enter raw HTML, so be very careful in your entry.', 'weeblramp' ),
				'default'  => 'Welcome! we have cookies, want some? ;)',
				'show-if'  => array(
					'id'      => 'notification_enabled',
					'include' => 'checked',
				),
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'cols'  => 60,
						'rows'  => 10,
						'class' => 'regular-text code',
					)
				)
			),

			array(
				'name'     => 'notification_button',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Notification button text', 'weeblramp' ),
				'desc'     => __( 'If you have entered some text above to display a user notification, you can add here the text for a button (\'I agree\' or \'Dismiss\' for instance). If not empty, the notification will be displayed to user until they click on the button.', 'weeblramp' ),
				'default'  => 'Yes, thanks!',
				'show-if'  => array(
					'id'      => 'notification_enabled',
					'include' => 'checked',
				),
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'notification_theme',
				'type'     => WblSystem_Config::OPTION_LIST,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Notification theme', 'weeblramp' ),
				'desc'     => __( 'Choose the theme that matches best your website colors.', 'weeblramp' ),
				'default'  => WeeblrampConfig_Customize::NOTIFICATION_THEME_DARK,
				'show-if'  => array(
					'id'      => 'notification_enabled',
					'include' => 'checked',
				),
				'class'    => '',
				'content'  => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::NOTIFICATION_THEME_DARK  => __( 'Dark', 'weeblramp' ),
						WeeblrampConfig_Customize::NOTIFICATION_THEME_LIGHT => __( 'Light', 'weeblramp' ),
					),
				)
			),
		),
	),

	// Page style ----------------------------------------------------------
	array(
		'name'     => 'page_style',
		'type'     => WblSystem_Config::OPTION_TAB,
		'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
		'title'    => __( 'Page style', 'weeblramp' ),
		'content'  => array(

			// Page styles section ---------------------------------------------------------
			array(
				'name'     => 'section_page_style',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Page style', 'weeblramp' ),
			),

			array(
				'name'      => 'content_max_width',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Maximum width', 'weeblramp' ),
				'desc'      => __( 'Enter the main page content maximum width, such as: <strong>640px</strong>.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/page-layout-style.html',
				'doc_embed' => true,
				'default'   => '640px',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'colors_background_page',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Background color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_page',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Text color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),
		)
	),
	// Header tab ----------------------------------------------------------
	array(
		'name'    => 'header',
		'type'    => WblSystem_Config::OPTION_TAB,
		'title'   => __( 'Header', 'weeblramp' ),
		'content' => array(

			// Layout ----------------------------------------------------------------------
			array(
				'name'     => 'section_header_layout',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Header layout', 'weeblramp' ),
			),

			array(
				'name'      => 'min_height_header',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Minimum height', 'weeblramp' ),
				'desc'      => __( 'Enter the page header minimum height, using CSS syntax: <strong>100px</strong> or <strong>10em</strong>.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/header.html',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'image_align_header',
				'type'     => WblSystem_Config::OPTION_LIST,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Header image position', 'weeblramp' ),
				'desc'     => __( 'Choose a position for the header image.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr'    => array(),
					'options' => array(
						''      => __( 'Default', 'weeblramp' ),
						'table' => __( 'Centered', 'weeblramp' ),
						'block' => __( 'Left', 'weeblramp' ),
					),
				)
			),

			// Header visuals --------------------------------------------------------------
			array(
				'name'     => 'section_header_visual',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Header visuals', 'weeblramp' ),
			),

			array(
				'name'         => 'image_background_header',
				'type'         => WblSystem_Config::OPTION_MEDIA,
				'editions'     => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'        => __( 'Header background image URL', 'weeblramp' ),
				'desc'         => __( 'Optional image to use as the <strong>background</strong> of the page header. Enter either a fully qualified URL (https://site.com/path/to/image.png) or a relative one (path/to/image.png)', 'weeblramp' ),
				'doc_link'     => 'products.weeblramp/1/going-further/customization/header.html',
				'doc_embed'    => true,
				'default'      => '',
				'class'        => '',
				'content'      => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 1024,
						'class'     => 'regular-text code',
					),
				),
				'with_preview' => true,
				'min_width'    => 479,
				'max_width'    => 0,
				'min_height'   => 0,
				'max_height'   => 0,
			),

			array(
				'name'     => 'colors_background_header',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Background color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_header',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Text color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_header_tag_line',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Tag line color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'fonts_family_header',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Font family', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS font specification, ie <strong>Roboto, sans-serif</strong>. If needed, use double-quote but <strong>no single quote</strong>.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'fonts_size_header',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Font size', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS font size specification, ie <strong>16px</strong> or <strong>1em</strong>.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

		),
	),

	// Content layout tab --------------------------------------------------
	array(
		'name'    => 'content_layout',
		'type'    => WblSystem_Config::OPTION_TAB,
		'title'   => __( 'Content layout', 'weeblramp' ),
		'content' => array(

			// Regular content (posts) -----------------------------------------------------
			array(
				'name'     => 'section_content_layout_posts',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Posts', 'weeblramp' ),
			),

			array(
				'name'      => 'item_display_options',
				'type'      => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Display options', 'weeblramp' ),
				'desc'      => __( 'Choose among various options to configure single posts output on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'doc_embed' => true,
				'default'   => array(),
				'class'     => 'wbamp-settings-checkbox-group',
				'content'   => array(
					'attr'    => array(),
					'layout'  => 'vertical', // vertical || horizontal
					'options' => array(
						array(
							'name'     => 'item_show_featured_image',
							'caption'  => __( 'Show featured image', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_header_show_excerpt',
							'caption'  => __( 'Show excerpt', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_header_show_info_block',
							'caption'  => __( 'Show info block', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_show_sharing_buttons',
							'caption'  => __( 'Show sharing buttons (if enabled globally)', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_header_author_use_moments',
							'caption'  => __( 'Display content date as elapsed time (eg: published 2 weeks ago)', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_header_author_show_avatar',
							'caption'  => __( 'Show author avatar', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_header_author_show_bio',
							'caption'  => __( 'Show author bio', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_author_bio_show_avatar',
							'caption'  => __( 'Show author avatar on bio', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_author_bio_show_link_to_posts',
							'caption'  => __( 'Show links to author other posts', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_amplify_pagination',
							'caption'  => __( 'AMPlify pagination links', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_show_linked_posts',
							'caption'  => __( 'Show previous/next posts links', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_show_widgets',
							'caption'  => __( 'Show AMP content widgets', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_FULL ),
						),
					)
				)
			),

			// Regular content (pages) -----------------------------------------------------
			array(
				'name'     => 'section_content_layout_pages',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Pages', 'weeblramp' ),
			),

			array(
				'name'      => 'item_page_display_options',
				'type'      => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Display options', 'weeblramp' ),
				'desc'      => __( 'Choose among various options to configure page output on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'doc_embed' => true,
				'default'   => array(),
				'class'     => 'wbamp-settings-checkbox-group',
				'content'   => array(
					'attr'    => array(),
					'layout'  => 'vertical', // vertical || horizontal
					'options' => array(
						array(
							'name'     => 'item_show_featured_image',
							'caption'  => __( 'Show featured image', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_show_sharing_buttons',
							'caption'  => __( 'Show sharing buttons (if enabled globally)', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'item_show_widgets',
							'caption'  => __( 'Show AMP content widgets', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_FULL ),
						),
					)
				)
			),

			// Categories & archives -------------------------------------------------------
			array(
				'name'     => 'section_content_layout_archives',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Categories & archives', 'weeblramp' ),
			),

			array(
				'name'      => 'item_category_display_options',
				'type'      => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Display options', 'weeblramp' ),
				'desc'      => __( 'Choose among various options to configure category and archive products output on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'doc_embed' => true,
				'default'   => array(),
				'class'     => 'wbamp-settings-checkbox-group',
				'content'   => array(
					'attr'    => array(),
					'layout'  => 'vertical', // vertical || horizontal
					'options' => array(
						array(
							'name'     => 'category_show_title',
							'caption'  => __( 'Show title', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'category_show_description',
							'caption'  => __( 'Show description', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'category_show_sharing_buttons',
							'caption'  => __( 'Show sharing buttons (if enabled globally)', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'category_amplify_readmore',
							'caption'  => __( 'AMPlify title & read more links', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'category_amplify_pagination',
							'caption'  => __( 'AMPlify pagination links', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'category_show_widgets',
							'caption'  => __( 'Show AMP content widgets', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_FULL ),
						),
					)
				)
			),

			// Home page -------------------------------------------------------------------
			array(
				'name'     => 'section_content_layout_home',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Home page', 'weeblramp' ),
				'help'     => __( 'These settings will only apply when pages or posts are displayed on the home page. For instance when using a WooCommerce shop as your home page, the WooCommerce integration settings will apply.', 'weeblramp' )
			),

			array(
				'name'      => 'home_display_options',
				'type'      => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Display options', 'weeblramp' ),
				'desc'      => __( 'Choose among various options to configure home page output on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'doc_embed' => true,
				'default'   => array(),
				'class'     => 'wbamp-settings-checkbox-group',
				'content'   => array(
					'attr'    => array(),
					'layout'  => 'vertical', // vertical || horizontal
					'options' => array(
						array(
							'name'     => 'show_title',
							'caption'  => __( 'Show title', 'weeblramp' ),
							'default'  => 0,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'show_description',
							'caption'  => __( 'Show description', 'weeblramp' ),
							'default'  => 0,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'show_sharing_buttons',
							'caption'  => __( 'Show sharing buttons (if enabled globally)', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'amplify_readmore',
							'caption'  => __( 'AMPlify title & read more links', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'amplify_pagination',
							'caption'  => __( 'AMPlify pagination links', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'show_widgets',
							'caption'  => __( 'Show AMP content widgets', 'weeblramp' ),
							'default'  => 0,
							'editions' => array( WblSystem_Version::EDITION_FULL ),
						),
					)
				)
			),

			// Comments --------------------------------------------------------------------
			array(
				'name'     => 'section_content_layout_comments',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Comments', 'weeblramp' ),
			),

			array(
				'name'      => 'comments_display_options',
				'type'      => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Display options<br /><small>(WordPress comments)</small>', 'weeblramp' ),
				'desc'      => __( 'Choose among various options to configure comments output on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'doc_embed' => true,
				'default'   => array(),
				'class'     => 'wbamp-settings-checkbox-group',
				'content'   => array(
					'attr'    => array(),
					'layout'  => 'vertical', // vertical || horizontal
					'options' => array(
						array(
							'name'     => 'show',
							'caption'  => __( 'Show comments', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'show_avatar',
							'caption'  => __( 'Show avatars', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'             => 'show_reply_to',
							'caption'          => __( 'Show Leave a comment form', 'weeblramp' ),
							'default'          => 1,
							'default_editions' => array(
								WblSystem_Version::EDITION_COMMUNITY => 0
							),
							'editions'         => array( WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'show_reply_to_a_comment',
							'caption'  => __( 'Show reply to specific comment', 'weeblramp' ),
							'default'  => 0,
							'editions' => array( WblSystem_Version::EDITION_FULL ),
						),
					)
				)
			),

			// Search ----------------------------------------------------------------------
			array(
				'name'     => 'section_content_layout_search',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Search results', 'weeblramp' ),
			),
			array(
				'name'      => 'item_search_display_options',
				'type'      => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Display options', 'weeblramp' ),
				'desc'      => __( 'Choose among various options to configure search results output on AMP pages.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'doc_embed' => true,
				'default'   => array(),
				'class'     => 'wbamp-settings-checkbox-group',
				'content'   => array(
					'attr'    => array(),
					'layout'  => 'vertical', // vertical || horizontal
					'options' => array(
						array(
							'name'     => 'search_show_sharing_buttons',
							'caption'  => __( 'Show sharing buttons (if enabled globally)', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'search_amplify_readmore',
							'caption'  => __( 'AMPlify title & read more links', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'search_amplify_pagination',
							'caption'  => __( 'AMPlify pagination links', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'search_show_widgets',
							'caption'  => __( 'Show AMP content widgets', 'weeblramp' ),
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
					)
				)
			),

		),
	),

	// Content tab ---------------------------------------------------------
	array(
		'name'    => 'content_style',
		'type'    => WblSystem_Config::OPTION_TAB,
		'title'   => __( 'Content style', 'weeblramp' ),
		'content' => array(

			// Galleries section -----------------------------------------------------------
			array(
				'name'     => 'section_galleries',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Galleries style', 'weeblramp' ),
			),

			array(
				'name'      => 'galleries_show_preview',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Show thumbnails', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled, image carousels on AMP pages will also show a single line of clickable thumbnails, to quickly access a specific image.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/content-layout-style.html#h2_content_styles_galleries',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'galleries_preview_image_height',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Thumbnails height', 'weeblramp' ),
				'desc'     => __( 'Enter a height in pixels, ie <strong>80</strong> or <strong>80px</strong>.', 'weeblramp' ),
				'default'  => '80',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'galleries_preview_image_width',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Thumbnails width', 'weeblramp' ),
				'desc'     => __( 'Enter a width in pixels, ie <strong>80</strong> or <strong>80px</strong>.', 'weeblramp' ),
				'default'  => '80',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			// Content section -------------------------------------------------------------
			array(
				'name'     => 'section_content',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Content style', 'weeblramp' ),
			),

			array(
				'name'      => 'fonts_family_content',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Font family', 'weeblramp' ),
				'desc'      => __( 'Enter a CSS font specification, ie <strong>Roboto, sans-serif</strong>. If needed, use double-quote but <strong>no single quote</strong>.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/content-layout-style.html#h2_content_styles',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'fonts_size_content',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Font size', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS font size specification, ie <strong>16px</strong> or <strong>1em</strong>.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'colors_background_content',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Background color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_content',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Text color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_hover_content',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Text hover color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_content_summary',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Summary text color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_border_default',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Separator lines color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			// Pagination section ----------------------------------------------------------
			array(
				'name'     => 'section_content_pagination',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Pagination style', 'weeblramp' ),
			),

			array(
				'name'     => 'colors_background_page_links',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Background color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'doc_link' => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_page_links',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Text color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_background_page_links_hover',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Hover background', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_page_links_hover',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Hover text', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_background_page_links_current',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Current page background', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_page_links_current',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Current page text', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_background_page_links_title',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Title background', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_page_links_title',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Title text', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			// Readmore section ------------------------------------------------------------
			array(
				'name'     => 'section_content_readmore',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Read More style', 'weeblramp' ),
			),

			array(
				'name'     => 'colors_background_readmore',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Background color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'doc_link' => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_readmore',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Text color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_background_readmore_hover',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Hover background', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_readmore_hover',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Hover text', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			// Content links section--------------------------------------------------------
			array(
				'name'     => 'section_content_links',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Links style', 'weeblramp' ),
			),

			array(
				'name'     => 'colors_text_content_link',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Links color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'doc_link' => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'decoration_content_link',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Links decoration', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS link text decoration style', 'weeblramp' ),
				'default'  => 'underline',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_content_link_hover',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Links hover color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_content_link_visited',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Links visited color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			// H1 heading section ----------------------------------------------------------
			array(
				'name'     => 'section_headings_h1',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'H1 headings style', 'weeblramp' ),
			),

			array(
				'name'     => 'fonts_family_heading_h1',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Font family', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS font specification, ie <strong>Roboto, sans-serif</strong>. If needed, use double-quote but <strong>no single quote</strong>.', 'weeblramp' ),
				'doc_link' => 'products.weeblramp/1/going-further/customization/content-layout-style.html',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'fonts_size_heading_h1',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Font size', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS font size specification, ie <strong>16px</strong> or <strong>1em</strong>.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'colors_background_heading_h1',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Background color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_heading_h1',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Text color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			// h2 heading section ----------------------------------------------------------
			array(
				'name'     => 'section_headings_h2',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'H2 headings style', 'weeblramp' ),
			),

			array(
				'name'     => 'fonts_family_heading_h2',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Font family', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS font specification, ie <strong>Roboto, sans-serif</strong>. If needed, use double-quote but <strong>no single quote</strong>.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'fonts_size_heading_h2',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Font size', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS font size specification, ie <strong>16px</strong> or <strong>1em</strong>.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'colors_background_heading_h2',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Background color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_heading_h2',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Text color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			// h3 heading section ----------------------------------------------------------
			array(
				'name'     => 'section_headings_h3',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'H3 headings style', 'weeblramp' ),
			),

			array(
				'name'     => 'fonts_family_heading_h3',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Font family', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS font specification, ie <strong>Roboto, sans-serif</strong>. If needed, use double-quote but <strong>no single quote</strong>.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'fonts_size_heading_h3',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Font size', 'weeblramp' ),
				'desc'     => __( 'Enter a CSS font size specification, ie <strong>16px</strong> or <strong>1em</strong>.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'     => 'colors_background_heading_h3',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Background color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'colors_text_heading_h3',
				'type'     => WblSystem_Config::OPTION_COLOR_PICKER,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Text color', 'weeblramp' ),
				'desc'     => __( 'Pick a color to use for this element. Use the Clear button (shown when the color selector is opened) to restore default color. ', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

		),
	),

	// Navigation tab ------------------------------------------------------
	array(
		'name'            => 'navigation',
		'type'            => WblSystem_Config::OPTION_TAB,
		'title'           => __( 'Menus', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/customization/menus.html',
		'doc_link_button' => __( 'Introduction...', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Navigation and links ----------------------------------------------------
			array(
				'name'     => 'section_navigation',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Navigation', 'weeblramp' ),
			),

			array(
				'name'      => 'menu_type',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Menu type', 'weeblramp' ),
				'desc'      => __( 'Select whether to display a sidewise sliding menu or a dropdown menu.', 'weeblramp' ),
				'default'   => WeeblrampConfig_Customize::MENU_TYPE_NONE,
				'doc_link'  => 'products.weeblramp/1/going-further/customization/menus.html#h2_menu_types',
				'doc_embed' => true,
				'class'     => '',
				'content'   => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::MENU_TYPE_NONE     => __( 'Do not show menu', 'weeblramp' ),
						WeeblrampConfig_Customize::MENU_TYPE_SLIDE    => __( 'Slide out sidebar', 'weeblramp' ),
						WeeblrampConfig_Customize::MENU_TYPE_DROPDOWN => array(
							'editions' => array( WblSystem_Version::EDITION_FULL ),
							'option'   => __( 'Dropdown', 'weeblramp' )
						),
					),
				)
			),

			array(
				'name'      => 'navigation_menu',
				'type'      => WblSystem_Config::OPTION_MENUS,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Menus to display', 'weeblramp' ),
				'desc'      => sprintf(
					__( 'Select which menu to display on AMP pages, and whether links on those menu should go to the AMP version of the linked page. Use existing menu(s), or create AMP-specific one(s) as usual, on <a href="%s">this WordPress page</a>. ', 'weeblramp' ),
					admin_url( 'nav-menus.php?action=edit' )
				),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/menus.html#h2_menus_customization',
				'doc_embed' => true,
				'default'   => array(),
				'show-if'   => array(
					'id'      => 'menu_type',
					'include' => array(),
					'exclude' => array( 'none' )
				),
				'class'     => '',
				'content'   => array(
					'attr'             => array(),
					'options_callback' => array(
						$this,
						'optionsCallback_menus'
					)
				)
			),

			array(
				'name'     => 'menu_side',
				'type'     => WblSystem_Config::OPTION_LIST,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Menu side', 'weeblramp' ),
				'desc'     => __( 'Select whether to display any navigation menu on the left or right side.', 'weeblramp' ),
				'default'  => WeeblrampConfig_Customize::MENU_TYPE_SIDE_LEFT,
				'show-if'  => array(
					'id'      => 'menu_type',
					'include' => array(),
					'exclude' => array( 'none' )
				),
				'class'    => '',
				'content'  => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::MENU_TYPE_SIDE_LEFT  => __( 'Left', 'weeblramp' ),
						WeeblrampConfig_Customize::MENU_TYPE_SIDE_RIGHT => __( 'Right', 'weeblramp' ),
					),
				)
			),

			array(
				'name'     => 'navigation_menu_button_text',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Menu button text', 'weeblramp' ),
				'desc'     => __( 'By default, a <i>hamburger</i> icon is displayed to open to menu. You can enter here some text that will replace the icon, such as <i>Menu</i> for instance.', 'weeblramp' ),
				'default'  => '',
				'show-if'  => array(
					'id'      => 'menu_type',
					'include' => array(),
					'exclude' => array( 'none' )
				),
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 255,
						'class'     => 'regular-text',
					)
				)
			),
		),
	),

	// Sharing buttons -----------------------------------------------------
	array(
		'name'            => 'sharing_buttons',
		'type'            => WblSystem_Config::OPTION_TAB,
		'title'           => __( 'Sharing buttons', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/customization/sharing-buttons.html',
		'doc_link_button' => __( 'Introduction...', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Location ----------------------------------------------------------------
			array(
				'name'     => 'section_sharing_buttons_location',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Location & type', 'weeblramp' ),
			),

			array(
				'name'      => 'social_buttons_location',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Location', 'weeblramp' ),
				'desc'      => __( 'Select whether to hide social buttons bar, or display it after or before your article content.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/sharing-buttons.html#h2_settings',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_NONE,
				'class'     => '',
				'content'   => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_NONE             => __( 'None', 'weeblramp' ),
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_AFTER_INFO_BLOCK => __( 'After author info block', 'weeblramp' ),
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_BEFORE           => __( 'Before content', 'weeblramp' ),
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_LOCATION_AFTER            => __( 'After content', 'weeblramp' ),
					),
				)
			),

			array(
				'name'            => 'social_buttons_type',
				'type'            => WblSystem_Config::OPTION_LIST,
				'editions'        => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'           => __( 'Buttons type', 'weeblramp' ),
				'desc'            => __( 'Static buttons are displayed faster (and still tracked with analytics). Using amp-social-share official AMP tag displays a bit slower, but may allow a nicer user experience and more networks.', 'weeblramp' ),
				'default'         => WeeblrampConfig_Customize::SOCIAL_BUTTONS_TYPE_STATIC,
				'default_edition' => array(
					WblSystem_Version::EDITION_COMMUNITY => WeeblrampConfig_Customize::SOCIAL_BUTTONS_TYPE_AMPSOCIAL,
				),
				'show-if'         => array(
					'id'      => 'social_buttons_location',
					'include' => array(),
					'exclude' => array( 'none' )
				),
				'doc_link'        => 'products.weeblramp/1/going-further/customization/sharing-buttons.html#h2_settings',
				'doc_embed'       => true,
				'class'           => '',
				'content'         => array(
					'attr'    => array(
						'class' => 'js-wbamp-handle'
					),
					'options' => array(
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_TYPE_AMPSOCIAL => __( 'Use amp-social-share', 'weeblramp' ),
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_TYPE_STATIC    => array(
							'editions' => array( WblSystem_Version::EDITION_FULL ),
							'option'   => __( 'Fully static', 'weeblramp' )
						),
					),
				)
			),

			array(
				'name'     => 'social_buttons_types',
				'type'     => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Buttons to show', 'weeblramp' ),
				'desc'     => wbJoin(
					' ',
					__( 'Select social sharing buttons to display on AMP pages.', 'weeblramp' ),
					__( 'When using a Facebook button, enter your Facebook App id under the <strong>SEO</strong> tab.', 'weeblramp' ),
					__( 'When using a Tweet button, enter your Twitter account under the <strong>SEO</strong> tab.', 'weeblramp' ),
					__( 'WhatsApp buttons are only displayed on phones.', 'weeblramp' )
				),
				'default'  => array(),
				'show-if'  => array(
					'id'      => 'social_buttons_location',
					'include' => array(),
					'exclude' => array( 'none' )
				),
				'class'    => 'wbamp-settings-checkbox-group',
				'content'  => array(
					'attr'    => array(),
					'layout'  => 'vertical', // vertical || horizontal
					'options' => array(
						array(
							'name'    => 'facebook_share',
							'caption' => __( 'Facebook share', 'weeblramp' ),
							'default' => 1
						),
						array(
							'name'    => 'twitter_share',
							'caption' => __( 'Tweet', 'weeblramp' ),
							'default' => 1
						),
						array(
							'name'    => 'linkedin_share',
							'caption' => __( 'LinkedIn', 'weeblramp' ),
							'default' => 1
						),
						array(
							'name'    => 'pinterest_share',
							'caption' => __( 'Pinterest', 'weeblramp' ),
							'default' => 1
						),
						array(
							'name'    => 'whatsapp_share',
							'caption' => __( 'WhatsApp (needs static buttons)', 'weeblramp' ),
							'default' => 1
						)
					)
				)
			),

			// Style -------------------------------------------------------------------
			array(
				'name'     => 'section_sharing_buttons_style',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Styles', 'weeblramp' ),
				'show-if'  => array(
					'id'      => 'social_buttons_location',
					'include' => array(),
					'exclude' => array( 'none' )
				),
			),

			array(
				'name'      => 'social_buttons_theme',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Buttons theme', 'weeblramp' ),
				'desc'      => wbJoin( ' ', __( 'Pick a color theme for the social icons.', 'weeblramp' ), __( 'Only used with <strong>static</strong> buttons.', 'weeblramp' ) ),
				'default'   => WeeblrampConfig_Customize::SOCIAL_BUTTONS_THEME_COLORS,
				'doc_link'  => 'products.weeblramp/1/going-further/customization/sharing-buttons.html#h2_buttons_styles',
				'doc_embed' => true,
				'class'     => 'js-social_buttons_theme_styles',
				'content'   => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_THEME_COLORS => __( 'Colors', 'weeblramp' ),
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_THEME_WHITE  => __( 'White', 'weeblramp' ),
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_THEME_DARK   => __( 'Dark', 'weeblramp' ),
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_THEME_LIGHT  => __( 'Light', 'weeblramp' ),
					),
				)
			),

			array(
				'name'     => 'social_buttons_style',
				'type'     => WblSystem_Config::OPTION_LIST,
				'editions' => array( WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Buttons style', 'weeblramp' ),
				'desc'     => wbJoin( ' ', __( 'Pick a style for the social icons.', 'weeblramp' ), __( 'Only used with <strong>static</strong> buttons.', 'weeblramp' ) ),
				'default'  => WeeblrampConfig_Customize::SOCIAL_BUTTONS_STYLE_ROUNDED,
				'class'    => 'js-social_buttons_theme_styles',
				'content'  => array(
					'attr'    => array(),
					'options' => array(
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_STYLE_ROUNDED => __( 'Rounded', 'weeblramp' ),
						WeeblrampConfig_Customize::SOCIAL_BUTTONS_STYLE_SQUARED => __( 'Squared', 'weeblramp' ),
					),
				)
			),
		)
	),

	// Custom CSS tab ------------------------------------------------------
	array(
		'name'            => 'custom_css',
		'type'            => WblSystem_Config::OPTION_TAB,
		'title'           => __( 'Custom styles', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/customization/custom-styles.html',
		'doc_link_button' => __( 'Introduction...', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Custom styles section ---------------------------------------------------
			array(
				'name'     => 'section_custom_styles',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Custom CSS', 'weeblramp' ),
			),

			array(
				'name'      => 'custom_css',
				'type'      => WblSystem_Config::OPTION_TEXTAREA,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Raw AMP CSS', 'weeblramp' ),
				'desc'      => __( 'You can enter here some CSS rules. They will be inserted in your AMP pages to alter its visual aspect. No control is done on the content you enter here, it will be inserted as-is. Be sure to double-check your entry.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/custom-styles.html#h2_css_restrictions',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'cols'        => 60,
						'rows'        => 10,
						'class'       => 'regular-text code',
						'placeholder' => '.sample { color: #FFFFFF; }'

					)
				)
			),

			array(
				'name'     => 'custom_links',
				'type'     => WblSystem_Config::OPTION_TEXTAREA,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Raw AMP links', 'weeblramp' ),
				'desc'     => __( 'You can enter here some custom links, to be inserted in the HEAD section of the document (one per line). No control is done on the content you enter here, it will be inserted as-is. Be sure to double-check your entry.', 'weeblramp' ),
				'default'  => '<link	href="https://fonts.googleapis.com/css?family=Roboto:400,400italic,700,700italic|Open+Sans:400,700,400italic,700italic" rel="stylesheet" type="text/css" />',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'cols'  => 60,
						'rows'  => 10,
						'class' => 'regular-text code',
					)
				)
			),

		),
	),

	// Clean up  --------------------------------------------------
	array(
		'name'            => 'cleanup',
		'type'            => WblSystem_Config::OPTION_TAB,
		'title'           => __( 'Cleanup', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/customization/cleanup.html',
		'doc_link_button' => __( 'Introduction...', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// CSS cleanup section -----------------------------------------------------
			array(
				'name'     => 'section_css_cleanup',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'CSS cleanup', 'weeblramp' ),
			),

			array(
				'name'      => 'cleanup_css_classes',
				'type'      => WblSystem_Config::OPTION_TEXTAREA,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Remove if CSS classes', 'weeblramp' ),
				'desc'      => __( 'HTML elements with one or more of the CSS classes listed in this field will be removed from the final AMP page. You can use this to clean up content not suited for AMP. List one class per line. If an element must have 2 or more classes to be removed, list them on the same line.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/cleanup.html#h3_css-based_cleanup',
				'doc_embed' => true,
				'default'   => 'wbamp-remove-on-amp',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'cols'  => 60,
						'rows'  => 10,
						'class' => 'regular-text code',
					)
				)
			),

			array(
				'name'      => 'cleanup_css_ids',
				'type'      => WblSystem_Config::OPTION_TEXTAREA,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Remove if CSS id', 'weeblramp' ),
				'desc'      => __( 'HTML elements with the HTML id attribute listed in this field will be removed from the final AMP page. You can use this to clean up content not suited for AMP. List one id per line. No need for a leading # symbol.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/cleanup.html#h3_css-based_cleanup',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'cols'  => 60,
						'rows'  => 10,
						'class' => 'regular-text code',
					)
				)
			),

			// Regular expressions cleanup ---------------------------------------------
			array(
				'name'     => 'section_regexp_cleanup',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Regular expressions cleanup', 'weeblramp' ),
			),

			array(
				'name'      => 'cleanup_regexp',
				'type'      => WblSystem_Config::OPTION_TEXTAREA,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Cleanup expressions', 'weeblramp' ),
				'desc'      => __(
					'Lines starting with a ; will not be used. Enter a single <strong>-</strong> character on the first line to disable this feature.<br />
You can use this, for instance, to remove content that should be otherwise used by plugins not allowed on AMP pages. See the documentation for the default list of regular expressions.', 'weeblramp'
				),
				'doc_link'  => 'products.weeblramp/1/going-further/customization/cleanup.html#h3_cleanup_expressions',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'cols'  => 60,
						'rows'  => 10,
						'class' => '',
					)
				)
			),

		)
	),

	// Upgrade ----------------------------------------------------
	array(
		'name'     => 'upgrade',
		'type'     => WblSystem_Config::OPTION_TAB,
		'editions' => array( WblSystem_Version::EDITION_COMMUNITY ),
		'title'    => __( 'Upgrade', 'weeblramp' ),
		'content'  => array(

			// Upgrade -----------------------------------------------------------------
			WeeblrampHelper_Version::getUpgradeSettingDefContent()
		)
	),

);

