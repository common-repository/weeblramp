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
 * Settings definition for weeblrAMP
 *
 * Used by class WeeblrampConfig_User
 */
return array(
	// Main tab ------------------------------------------------------------
	array(
		'name'            => 'main',
		'type'            => WblSystem_Config::OPTION_TAB,
		'title'           => __( 'Main', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/getting-started/index.html',
		'doc_link_button' => __( 'Getting started guide...', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Main settings section -------------------------------------------------------
			array(
				'name'     => 'section_main',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Main settings', 'weeblramp' ),
				'icon'     => 'assets.icons.home'
			),

			array(
				'name'      => 'op_mode',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Operation mode', 'weeblramp' ),
				'desc'      => __( '<strong>Development mode</strong>: AMP pages are enabled, but they are not advertised to search engines. Use this mode when setting things up, until you have validated your pages.<br /><strong>Normal</strong>: AMP pages are advertised to search engines, as separate pages from your site.<br /><strong>Standalone</strong>: the site is rendered entirely as AMP, there is no regular HTML version (experimental).', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/index.html#h2_after_installation',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_User::OP_MODE_DEV,
				'class'     => '',
				'content'   => array(
					'attr'             => array(),
					'options'          => array(
						WeeblrampConfig_User::OP_MODE_NORMAL     => __( 'Normal', 'weeblramp' ),
						WeeblrampConfig_User::OP_MODE_DEV        => __( 'Development', 'weeblramp' ),
						WeeblrampConfig_User::OP_MODE_STANDALONE => array(
							'editions' => array( WblSystem_Version::EDITION_FULL ),
							'option'   => __( 'Standalone', 'weeblramp' )
						),
					),
					'options_callback' => array()
				)
			),

			array(
				'name'     => 'global_theme',
				'type'     => WblSystem_Config::OPTION_LIST,
				'editions' => array( WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Global theme', 'weeblramp' ),
				'desc'     => __( 'Select a color theme for your AMP pages, from the list of weeblrAMP theme plugins installed and activated.<br /><strong>NB:</strong>The weeblrAMP Customizer handles the built-in display. Additional weeblrAMP themes can be customized using their own customizers only.', 'weeblramp' ),
				'default'  => 'wp-content/plugins/weeblramp/assets/default',
				'class'    => '',
				'content'  => array(
					'attr'             => array(),
					'options'          => array(
						'wp-content/plugins/weeblramp/assets/default' => __( 'Default theme', 'weeblramp' ),
					),
					'options_callback' => array(
						$this,
						'optionsCallback_global_theme'
					)
				)
			),
		),
	),
	// Page selection tab --------------------------------------------------
	array(
		'name'            => 'select_pages',
		'type'            => WblSystem_Config::OPTION_TAB,
		'editions'        => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
		'title'           => __( 'Select pages', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/getting-started/select-content.html',
		'doc_link_button' => __( 'Introduction', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Page selection section - ----------------------------------------------------
			array(
				'name'     => 'section_pages_selection',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Items to AMPlify', 'weeblramp' ),
				'icon'     => 'assets.icons.select_list'
			),

			array(
				'name'              => 'amp_post_types',
				'type'              => WblSystem_Config::OPTION_POST_TYPES,
				'editions'          => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'             => __( 'Post & pages types', 'weeblramp' ),
				'desc'              => __( 'Select the pages on your site for which weeblrAMP should create an AMP version. If needed, you can also disable AMP version creation per category, tags, etc.', 'weeblramp' ),
				'default'           => array( 'post' => array( 'enabled' => 1 ), 'page' => array( 'enabled' => 1 ) ),
				'doc_link'          => 'products.weeblramp/1/getting-started/select-content.html#h2_display_type_selection',
				'doc_embed'         => true,
				'class'             => '',
				'select_taxonomies' => true, // boolean
				'excludes'          => array( 'attachment' ),  // can exclude by name some post types
				'includes'          => array(),
				'content'           => array(
					'attr'             => array(),
					'options_callback' => array(
						$this,
						'optionsCallback_post_types'
					)
				)
			),
			array(
				'name'      => 'amplify_categories',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Categories', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled links to categories, and categories display, will be AMPlified when possible.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/select-content.html#h2_display_type_selection',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'amplify_archives',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Archives', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled links to categories, and categories display, will be AMPlified when possible.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/select-content.html#h2_display_type_selection',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'amplify_home',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Home page', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled the home page itself will have an AMP version.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/select-content.html#h2_display_type_selection',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'amplify_tags',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Tags', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled links to tags pages, and tags pages display, will be AMPlified when possible.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/select-content.html#h2_display_type_selection',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'amplify_search_page',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Search page', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled the WordPress search results page will be AMPlified when possible.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/select-content.html#h2_display_type_selection',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'amplify_authors',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Author pages', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled links to author pages, and author pages display, will be AMPlified when possible.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/select-content.html#h2_display_type_selection',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),
		)
	),

	// Meta data -----------------------------------------------------------
	array(
		'name'            => 'metadata',
		'type'            => WblSystem_Config::OPTION_TAB,
		// Metadata tab ------------------------------------------------------------
		'title'           => __( 'Metadata', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/getting-started/site-meta-data.html',
		'doc_link_button' => __( 'Introduction', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Site information section ----------------------------------------------------
			array(
				'name'     => 'section_site_information',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Site information', 'weeblramp' ),
				'icon'     => 'assets.icons.website'
			),

			array(
				'name'      => 'site_name',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Site name', 'weeblramp' ),
				'desc'      => __( 'Enter a site name to be displayed on AMP pages. Will default to current site name.', 'weeblramp' ),
				'default'   => array( 'WblWordpress_Helper', 'getSiteName' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/site-meta-data.html',
				'doc_embed' => true,
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
				'name'      => 'site_tag_line',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Site tagline', 'weeblramp' ),
				'desc'      => __( 'Enter a site description to be displayed on AMP pages. Will default to current site tag line.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/site-meta-data.html',
				'doc_embed' => true,
				'default'   => array( 'WblWordpress_Helper', 'getSiteTagline' ),
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
				'name'         => 'site_image',
				'type'         => WblSystem_Config::OPTION_MEDIA,
				'editions'     => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'        => __( 'Header image URL', 'weeblramp' ),
				'desc'         => __( 'Optional image to use as a page header when displaying AMP pages. Enter either a fully qualified URL (https://site.com/path/to/image.png) or a relative one (path/to/image.png)', 'weeblramp' ),
				'doc_link'     => 'products.weeblramp/1/getting-started/site-meta-data.html',
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
				'min_width'    => 0,
				'max_width'    => 0,
				'min_height'   => 0,
				'max_height'   => 0,
			),

			// Publisher information section -----------------------------------------------
			array(
				'name'     => 'section_publisher_information',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Publisher information', 'weeblramp' ),
				'icon'     => 'assets.icons.publisher'
			),

			array(
				'name'      => 'publisher_name',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Publisher name', 'weeblramp' ),
				'desc'      => __( 'Enter your organization name (not the author name), for use in AMP meta data.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/site-meta-data.html#h2_publisher_information',
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
				'name'      => 'publisher_url',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Publisher URL', 'weeblramp' ),
				'desc'      => __( 'Enter the fully qualified (eg: https://www.example.com) URL of the organization publishing the content. Defaults to the current site URL if not filled in.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/getting-started/site-meta-data.html#h2_publisher_information',
				'doc_embed' => true,
				'default'   => get_home_url(),
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
				'name'         => 'publisher_image',
				'type'         => WblSystem_Config::OPTION_MEDIA,
				'editions'     => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'        => __( 'Publisher image URL', 'weeblramp' ),
				'desc'         => __( 'The logo image is required for AMP meta data. Enter either a fully qualified URL (https://site.com/path/to/image.png) or a relative one (path/to/image.png)', 'weeblramp' ),
				'doc_link'     => 'products.weeblramp/1/getting-started/site-meta-data.html#h2_publisher_information',
				'doc_embed'    => true,
				'default'      => '',
				'class'        => '',
				'content'      => array(
					'attr' => array(
						'size'      => 50,
						'maxlength' => 1024,
						'class'     => 'regular-text code',
					)
				)
				,
				'with_preview' => true,
				'min_width'    => 0,
				'max_width'    => 600,
				'min_height'   => 60,
				'max_height'   => 60,
			),

			// Document type ---------------------------------------------------------------
			array(
				'name'     => 'section_document_type',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Document defaults', 'weeblramp' ),
				'icon'     => 'assets.icons.gear'
			),

			array(
				'name'     => 'default_doc_type',
				'type'     => WblSystem_Config::OPTION_LIST,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Default article type', 'weeblramp' ),
				'desc'     => __( 'Select the default article type to use for your AMP pages. You can override this value on each page with custom meta data shortcodes.', 'weeblramp' ),
				'default'  => WeeblrampConfig_User::DOC_TYPE_ARTICLE,
				'class'    => 'test',
				'content'  => array(
					'attr'             => array(),
					'options'          => array(
						WeeblrampConfig_User::DOC_TYPE_ARTICLE      => __( 'Article', 'weeblramp' ),
						WeeblrampConfig_User::DOC_TYPE_BLOG_POSTING => __( 'BlogPosting', 'weeblramp' ),
						WeeblrampConfig_User::DOC_TYPE_NEWS_ARTICLE => __( 'NewsArticle', 'weeblramp' ),
						WeeblrampConfig_User::DOC_TYPE_PHOTOGRAPH   => __( 'Photograph', 'weeblramp' ),
						WeeblrampConfig_User::DOC_TYPE_RECIPE       => __( 'Recipe', 'weeblramp' ),
						WeeblrampConfig_User::DOC_TYPE_REVIEW       => __( 'Review', 'weeblramp' ),
						WeeblrampConfig_User::DOC_TYPE_WEBPAGE      => __( 'WebPage', 'weeblramp' ),
					),
					'options_callback' => array()
				)
			),

		)
	),

	// SEO -----------------------------------------------------------
	array(
		'name'            => 'seo',
		'type'            => WblSystem_Config::OPTION_TAB,
		// Metadata tab ------------------------------------------------------------
		'title'           => __( 'SEO', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/seo/index.html',
		'doc_link_button' => __( 'Introduction', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// OGP and TCards ----------------------------------------------------------
			array(
				'name'     => 'section_page_description_data',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Page description', 'weeblramp' ),
				'icon'     => 'assets.icons.seo'
			),

			array(
				'name'      => 'ogp_enabled',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Open graph metadata', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled, we will insert Open Graph meta data on AMP pages. OGP is used by Facebook and many others to describe page content.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/seo/index.html#h2_page_description_meta_data',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'facebook_app_id',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Facebok App ID', 'weeblramp' ),
				'desc'      => wbJoin( ' ', __( 'Your Facebook application ID.', 'weeblramp' ), __( 'Used when inserting OGP data or showing Facebook sharing buttons.', 'weeblramp' ) ),
				'doc_link'  => 'products.weeblramp/1/going-further/seo/index.html#h2_page_description_meta_data',
				'doc_embed' => true,
				'default'   => '',
				'class'     => 'js-social_button_facebook_app_id',
				'content'   => array(
					'attr' => array(
						'size'        => 20,
						'maxlength'   => 50,
						'class'       => 'regular-text code',
						'placeholder' => '123456789012345'
					)
				)
			),

			array(
				'name'  => 'separator_seo_1',
				'type'  => WblSystem_Config::OPTION_SETTING_SEPARATOR,
				'class' => 'wbamp-settings-separator'
			),

			array(
				'name'      => 'tcards_enabled',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Twitter cards', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled, we will insert Twitter cards meta data on AMP pages. This is used by Twitter and others to describe page content.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/seo/index.html#h2_page_description_meta_data',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'tcards_type',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Cards type', 'weeblramp' ),
				'desc'      => __( 'Choose which type of Twitter cards to use, when enabled.', 'weeblramp' ),
				'doc_link'  => 'https://dev.twitter.com/cards/types',
				'doc_embed' => true,
				'default'   => 'summary_large_image',
				'show-if'   => array(
					'id'      => 'tcards_enabled',
					'include' => 'checked',
				),
				'class'     => '',
				'content'   => array(
					'attr'             => array(),
					'options'          => array(
						'summary'             => __( 'Summary', 'weeblramp' ),
						'summary_large_image' => __( 'Summary with large image', 'weeblramp' ),
					),
					'options_callback' => array()
				)
			),

			array(
				'name'     => 'twitter_account',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Twitter account', 'weeblramp' ),
				'desc'     => wbJoin(
					' ',
					__( 'The Twitter account to use when sharing content. For instance: @weeblr.', 'weeblramp' ),
					__( 'Used when inserting Twitter cards or showing Twitter tweet buttons.', 'weeblramp' )
				),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'        => 20,
						'maxlength'   => 50,
						'class'       => 'regular-text code',
						'placeholder' => '@sampleaccount'
					)
				)
			),

			// Structured data embedding -----------------------------------------------
			array(
				'name'     => 'section_meta_structured_data',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Structured data (Schema.org)', 'weeblramp' ),
				'icon'     => 'assets.icons.tags'
			),

			array(
				'name'      => 'struct_data_enabled',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Insert schema.org data', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled, we will insert json schema.org data in AMP pages, according to the AMP project specification.<br />Visit those pages on the AMP documentation for details: <ul><li><a href="https://developers.google.com/search/docs/data-types/sitename" target="_blank">Site name in search results</a></li><li><a href="https://developers.google.com/search/docs/data-types/social-profile-links" target="_blank">Social profile links</a></li><li><a href="https://developers.google.com/search/docs/data-types/corporate-contacts" target="_blank">Corporate contacts</a></li></ul>', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/seo/index.html#h2_schema_org_structured_data',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'  => 'separator_structured_data_1',
				'type'  => WblSystem_Config::OPTION_SETTING_SEPARATOR,
				'class' => 'wbamp-settings-separator'
			),

			array(
				'name'      => 'struct_profiles_social',
				'type'      => WblSystem_Config::OPTION_TEXTAREA,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Company social profiles', 'weeblramp' ),
				'desc'      => __( 'Enter one or more links to your company social profiles page, <strong>one per line</strong>. A social profile is a full URL such as: https://www.facebook/weeblrpress or https://instagram.com/weeblrpress', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/seo/index.html#h2_schema_org_structured_data',
				'doc_embed' => true,
				'default'   => '',
				'show-if'   => array(
					'id'      => 'struct_data_enabled',
					'include' => 'checked',
				),
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'cols'        => 60,
						'rows'        => 10,
						'class'       => 'regular-text code',
						'placeholder' => "https://www.facebook.com/sample\nhttps://www.twitter.com/sample"
					)
				)
			),

			array(
				'name'      => 'struct_profiles_contact_sales',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Sales phone number', 'weeblramp' ),
				'desc'      => __( 'Enter a phone number for sales contact. Number must follow international format. Examples: <i>+1-800-555-1212</i>, <i>+44-2078225951</i>. Visit <a href="https://developers.google.com/search/docs/data-types/corporate-contacts" target="_blank">Google documentation for more details</a>.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/seo/index.html#h2_schema_org_structured_data',
				'doc_embed' => true,
				'default'   => '',
				'show-if'   => array(
					'id'      => 'struct_data_enabled',
					'include' => 'checked',
				),
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'        => 20,
						'maxlength'   => 50,
						'class'       => 'regular-text code',
						'placeholder' => '+1-800-555-1212'
					)
				)
			),

			array(
				'name'      => 'struct_profiles_contact_customer',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Customer service number', 'weeblramp' ),
				'desc'      => __( 'Enter a phone number for customer service contact. Number must follow international format. Examples: <i>+1-800-555-1212</i>, <i>+44-2078225951</i>', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/seo/index.html#h2_schema_org_structured_data',
				'doc_embed' => true,
				'default'   => '',
				'show-if'   => array(
					'id'      => 'struct_data_enabled',
					'include' => 'checked',
				),
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'        => 20,
						'maxlength'   => 50,
						'class'       => 'regular-text code',
						'placeholder' => '+1-800-555-1212'
					)
				)
			),

		)
	),

	// Analytics -----------------------------------------------------------
	array(
		'name'            => 'analytics',
		'type'            => WblSystem_Config::OPTION_TAB,
		// Metadata tab ------------------------------------------------------------
		'title'           => __( 'Analytics', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/analytics-tag/index.html',
		'doc_link_button' => __( 'Introduction', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Analytics type ----------------------------------------------------------
			array(
				'name'     => 'section_analytics_type',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Analytics type', 'weeblramp' ),
				'icon'     => 'assets.icons.stats'
			),

			array(
				'name'      => 'analytics_type',
				'type'      => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Analytics', 'weeblramp' ),
				'desc'      => __( 'If enabled, one or more amp-analytics tag will be added to all AMP pages, using parameters set below. If the social buttons option is enabled, tracking code for social interactions will also be added.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/analytics-tag/index.html#h2_analytics_type',
				'doc_embed' => true,
				'default'   => array(),
				'class'     => 'wbamp-settings-checkbox-group',
				'content'   => array(
					'attr'    => array(),
					'layout'  => 'vertical', // vertical || horizontal
					'options' => array(
						array(
							'name'     => WeeblrampConfig_User::ANALYTICS_STANDARD,
							'caption'  => 'Google Analytics',
							'default'  => 0,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => WeeblrampConfig_User::ANALYTICS_GTM,
							'caption'  => 'Google Tag Manager',
							'default'  => 0,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => WeeblrampConfig_User::ANALYTICS_FACEBOOK_PIXEL,
							'caption'  => 'Facebook Pixel',
							'default'  => 0,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
					)
				)
			),

			// Analytics detailed settings section -------------------------------------
			array(
				'name'     => 'section_analytics_settings',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Settings', 'weeblramp' ),
				'icon'     => 'assets.icons.gear',
				'show-if'  => array(
					'id'      => array( 'analytics_typestandard', 'analytics_typegtm', 'analytics_typefb_pixel' ),
					'include' => 'checked',
				),
			),

			array(
				'name'      => 'analytics_webproperty_id',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Web property ID', 'weeblramp' ),
				'show-if'   => array(
					'id'      => 'analytics_typestandard',
					'include' => 'checked',
				),
				'desc'      => __( 'The Google Analytics web property ID. Similar to UA-XXXXX-Y.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/analytics-tag/index.html#h2_analytics_settings',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'        => 10,
						'maxlength'   => 50,
						'class'       => 'regular-text code',
						'placeholder' => 'UA-123456-1'
					)
				)
			),

			array(
				'name'      => 'analytics_gtm_id',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Tag manager ID', 'weeblramp' ),
				'show-if'   => array(
					'id'      => 'analytics_typegtm',
					'include' => 'checked',
				),
				'desc'      => __( 'The Google Tag manager ID to use. Similar to GTM-XXXXXX.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/analytics-tag/index.html#h2_analytics_settings',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'        => 10,
						'maxlength'   => 50,
						'class'       => 'regular-text code',
						'placeholder' => 'GTM-ABC123'
					)
				)
			),

			array(
				'name'      => 'analytics_fb_pixel_id',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Facebook Pixel ID', 'weeblramp' ),
				'show-if'   => array(
					'id'      => 'analytics_typefb_pixel',
					'include' => 'checked',
				),
				'desc'      => __( 'The Facebook Pixel pixel id.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/analytics-tag/index.html#h2_analytics_settings',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'        => 10,
						'maxlength'   => 50,
						'class'       => 'regular-text code',
						'placeholder' => 'XYZ789'
					)
				)
			),

			array(
				'name'      => 'analytics_require_consent',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Require consent', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => sprintf(
					__( 'If enabled, no Analytics tracking will happen before the <a href="%s">User Notification</a> has been accepted by a visitor, by clicking the notification button.', 'weeblramp' ),
					admin_url( 'admin.php?page=weeblramp-customize#section_user_notification' )
				),
				'doc_link'  => 'products.weeblramp/1/going-further/analytics-tag/index.html#h3_require_consent',
				'doc_embed' => true,
				'default'   => 0,
				'show-if'   => array(
					'id'      => array( 'analytics_typestandard', 'analytics_typegtm', 'analytics_typefb_pixel' ),
					'include' => 'checked',
				),
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'analytics_data_credentials',
				'type'     => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Allow cookie write', 'weeblramp' ),
				'caption'  => __( 'Enabled', 'weeblramp' ),
				'desc'     =>
					__( 'If enabled, a <i>data-credentials</i> attributes will be added to the <i>amp-analytics</i> tag, so that cookies can be written across domains. For more details, see the data-credentials attribute documentation linked below.', 'weeblramp' ),
				'doc_link' => 'https://www.ampproject.org/docs/reference/components/amp-analytics#attributes',
				'default'  => 0,
				'show-if'  => array(
					'id'      => array( 'analytics_typestandard' ),
					'include' => 'checked',
				),
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'analytics_tracked_events',
				'type'      => WblSystem_Config::OPTION_TEXTAREA,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Tracked events', 'weeblramp' ),
				'desc'      => __( 'Enter one or more definitions of the Analytics events you track on your site, one per line. Can only be used with standard analytics, <strong>not with Google Tag Manager</strong>. Example for tracking clicks on an element, on any page: <strong>* | click | #header | Sample category | header-click</strong>', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/analytics-tag/index.html#h3_tracked_events',
				'doc_embed' => true,
				'default'   => '',
				'show-if'   => array(
					'id'      => array( 'analytics_typestandard' ),
					'include' => 'checked',
				),
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'cols'        => 60,
						'rows'        => 10,
						'class'       => 'regular-text code',
						'placeholder' => '2016/09/21/sample-post-a-2 | click | #header | Sample category | header-click'
					)
				)
			),
		)
	),

	// Ad networks ------------------------------------------------
	array(
		'name'            => 'ad_networks',
		'type'            => WblSystem_Config::OPTION_TAB,
		'title'           => __( 'Ad networks', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/ad-networks/index.html',
		'doc_link_button' => __( 'Introduction', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Customize main section help -------------------------------------------------
			array(
				'name'     => 'section_ad_placement',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Ads location and type', 'weeblramp' ),
				'icon'     => 'assets.icons.advert'
			),

			array(
				'name'      => 'ads_network',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Ads network', 'weeblramp' ),
				'desc'      => __( 'Select the ads network to use for your AMP pages', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/ad-networks/index.html#h2_built-in_support',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_User::ADS_NO_ADS,
				'class'     => '',
				'content'   => array(
					'attr'             => array(),
					'options'          => $this->adsNetworks,
					'options_callback' => array()
				)
			),

			array(
				'name'      => 'ads_location',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Ads location', 'weeblramp' ),
				'desc'      => __( 'Select how to show ads in your content. Ads can be displayed automatically before or after content, and/or based on paragraphs count, and/or using the <strong>&lsqb;wbamp-embed&rsqb;</strong> shortcode wherever you require in your content.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/ad-networks/index.html#h2_ads_location_and_type',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_User::ADS_AFTER_CONTENT,
				'show-if'   => array(
					'id'      => 'ads_network',
					'exclude' => WeeblrampConfig_User::ADS_NO_ADS,
				),
				'class'     => '',
				'content'   => array(
					'attr'             => array(),
					'options'          => array(
						WeeblrampConfig_User::ADS_AFTER_CONTENT  => __( 'Show after content', 'weeblramp' ),
						WeeblrampConfig_User::ADS_BEFORE_CONTENT => __( 'Show before content', 'weeblramp' ),
						WeeblrampConfig_User::ADS_NO_ADS         => array(
							'editions' => array( WblSystem_Version::EDITION_FULL ),
							'option'   => __( 'Show ads from shortcodes/autoinsert only', 'weeblramp' )
						)
					),
					'options_callback' => array()
				)
			),

			array(
				'name'      => 'autoinsert_ads_rules_post',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Auto-insert ads in posts', 'weeblramp' ),
				'desc'      => __( 'Enter a rule to automatically insert ads in content based on paragraphs count. Can be a comma-separated list of paragraph numbers (e.g. 2, 5, 9). Or insert / followed by a number, to insert ads every so many paragraphs (e.g. /3 inserts an ad every 3 paragraphs).', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/ad-networks/index.html#h2_ads_location_and_type',
				'doc_embed' => true,
				'default'   => '',
				'show-if'   => array(
					'id'      => 'ads_network',
					'exclude' => WeeblrampConfig_User::ADS_NO_ADS,
				),
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'      => 'autoinsert_ads_rules_page',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Auto-insert ads in pages', 'weeblramp' ),
				'desc'      => __( 'Enter a rule to automatically insert ads in content based on paragraphs count. Can be a comma-separated list of paragraph numbers (e.g. 2, 5, 9). Or insert / followed by a number, to insert ads every so many paragraphs (e.g. /3 inserts an ad every 3 paragraphs).' ),
				'doc_link'  => 'products.weeblramp/1/going-further/ad-networks/index.html#h2_ads_location_and_type',
				'doc_embed' => true,
				'default'   => '',
				'show-if'   => array(
					'id'      => 'ads_network',
					'exclude' => WeeblrampConfig_User::ADS_NO_ADS,
				),
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			// AdSense ads data ------------------------------------------------------------
			array(
				'name'     => 'section_adsense',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => 'AdSense',
				'icon'     => 'assets.icons.gear',
				'show-if'  => array(
					'id'      => 'ads_network',
					'include' => 'adsense',
				),
			),

			array(
				'name'     => 'adsense-ad-client',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => 'ad-client',
				'desc'     => '',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'     => 'adsense-ad-slot',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => 'ad-slot',
				'desc'     => '',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'     => 'adsense-ad-auto-format',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => 'adsense-ad-auto-format',
				'desc'     => '',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'      => 'adsense-ad-full-width',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => 'adsense-ad-full-width',
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => '',
				'doc_link'  => '',
				'doc_embed' => true,
				'default'   => 0,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			// Criteo ads data -------------------------------------------------------------
			array(
				'name'     => 'section_criteo',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => 'Criteo',
				'icon'     => 'assets.icons.gear',
				'show-if'  => array(
					'id'      => 'ads_network',
					'include' => 'criteo',
				),
			),

			array(
				'name'     => 'criteo-zone',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => 'zone',
				'desc'     => '',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			// Doubleclick ads data --------------------------------------------------------
			array(
				'name'     => 'section_doubleclick',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => 'Doubleclick',
				'icon'     => 'assets.icons.gear',
				'show-if'  => array(
					'id'      => 'ads_network',
					'include' => 'doubleclick',
				),
			),

			array(
				'name'     => 'doubleclick-slot',
				'type'     => WblSystem_Config::OPTION_TEXT,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => 'slot',
				'desc'     => '',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'     => 'doubleclick-json',
				'type'     => WblSystem_Config::OPTION_TEXTAREA,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => 'json',
				'desc'     => '',
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'cols'  => 60,
						'rows'  => 10,
						'class' => 'regular-text code',
					)
				)
			),

			// Custom ads network details section ------------------------------------------
			array(
				'name'     => 'section_custom_ad_content',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Custom ads network', 'weeblramp' ),
				'icon'     => 'assets.icons.gear',
				'show-if'  => array(
					'id'      => 'ads_network',
					'include' => 'custom',
				),
			),

			array(
				'name'      => 'ads-custom-content',
				'type'      => WblSystem_Config::OPTION_TEXTAREA,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( '&lt;amp-ad&gt; tag content', 'weeblramp' ),
				'desc'      => __(
					'Enter here the full and exact content of the &lt;amp-ad&gt; tag required for your ads network. <br />Example: <br /><i>&lt;amp-ad width=300 height=250
    type="a9"
    data-aax_size="300x250"
    data-aax_pubname="test123"
    data-aax_src="302"&gt;&lt;/amp-ad&gt;</i><br />Please refer to <a target="_blank" href="https://www.ampproject.org/docs/reference/amp-ad.html">the &lt;amp-ad&gt; documentation</a> for a list of accepted ads network.', 'weeblramp'
				),
				'doc_link'  => 'products.weeblramp/1/going-further/ad-networks/index.html#h2_other_networks',
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

			// Ads dimensions and details  -------------------------------------------------
			array(
				'name'     => 'section_ads_display',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Ads display details', 'weeblramp' ),
				'icon'     => 'assets.icons.display',
				'show-if'  => array(
					'id'      => 'ads_network',
					'exclude' => WeeblrampConfig_User::ADS_NO_ADS,
				),
			),

			array(
				'name'      => 'ad_width',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Ad width (px)', 'weeblramp' ),
				'desc'      => __( 'Set ad width in pixels. Mandatory.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/ad-networks/index.html#h2_ads_display_details',
				'doc_embed' => true,
				'default'   => '300',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'      => 'ad_height',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Ad height (px)', 'weeblramp' ),
				'desc'      => __( 'Set ad height in pixels. Mandatory.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/ad-networks/index.html#h2_ads_display_details',
				'doc_embed' => true,
				'default'   => '150',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'      => 'ad_placeholder',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Placeholder', 'weeblramp' ),
				'desc'      => __( 'Text to show until the ads is actually displayed.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/ad-networks/index.html#h2_ads_display_details',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'      => 'ad_fallback',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Fallback', 'weeblramp' ),
				'desc'      => __( 'Text to display if the ad fails to show.', 'weeblramp' ),
				'default'   => '<p>There is currently no ad to display on this page.</p>',
				'doc_link'  => 'products.weeblramp/1/going-further/ad-networks/index.html#h2_ads_display_details',
				'doc_embed' => true,
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),
		)
	),

	// Plugins management -----------------------------------------
	array(
		'name'            => 'plugins',
		'type'            => WblSystem_Config::OPTION_TAB,
		'title'           => __( 'Comments and plugins', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/comments-plugins/index.html',
		'doc_link_button' => __( 'Introduction', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Commenting --------------------------------------------------------------
			array(
				'name'     => 'section_commenting',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Comment system', 'weeblramp' ),
				'icon'     => 'assets.icons.comments',
			),

			array(
				'name'      => 'commenting_system',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Commenting system to use', 'weeblramp' ),
				'desc'      => __( 'Select which commenting system to use. Defaults to built-in WordPress system. You can use other supported commenting systems listed here. Those may require additional setup on your server.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/comments-plugins/index.html#h3_wordpress_native_comments',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_User::COMMENTS_NATIVE,
				'class'     => '',
				'content'   => array(
					'attr'             => array(),
					'options'          => array(
						WeeblrampConfig_User::COMMENTS_NATIVE => __( 'WordPress built-in comments', 'weeblramp' ),
						WeeblrampConfig_User::COMMENTS_DISQUS => array(
							'editions' => array( WblSystem_Version::EDITION_FULL ),
							'option'   => __( 'Disqus', 'weeblramp' )
						),
					),
					'options_callback' => array()
				)
			),

			array(
				'name'      => 'comment_disqus_shortname',
				'type'      => WeeblrampConfig_User::OPTION_DISQUS_SHORTNAME,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'use_ajax'  => true,
				'title'     => __( 'Disqus shortname', 'weeblramp' ),
				'desc'      => __( 'Enter your Disqus shortname for this site. Disqus for AMP requires that a small file is hosted on a <strong>separate website, using HTTPS</strong>. You can use the one we host for you at WeeblrPress, the process is then entirely automatic. Alternatively, you can also download a Disqus file we prepared for you, and host it yourself on another site. Fill in the full URL of the file on that server in next input field.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/comments-plugins/index.html#h3_disqus',
				'doc_embed' => true,
				'default'   => array(
					$this,
					'getDisqusShortnameFromPlugin'
				),
				'show-if'   => array(
					'id'      => 'commenting_system',
					'include' => array( 'disqus' ),
					'exclude' => array()
				),
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 40,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'      => 'comment_disqus_endpoint',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Disqus endpoint', 'weeblramp' ),
				'desc'      => sprintf(
					__( 'Fully qualified URL to the Disqus endpoint file. When using the WeeblrPress hosted file, this URL is filled in automatically. If you host the file yourself, provide the full URL to it.', 'weeblramp' ),
					wbArrayGet( WblWordpress_Helper::getUploadsInfo(), 'url' )
				),
				'doc_link'  => 'products.weeblramp/1/going-further/comments-plugins/index.html#h3_disqus',
				'doc_embed' => true,
				'default'   => '',
				'show-if'   => array(
					'id'      => 'commenting_system',
					'include' => array( 'disqus' ),
					'exclude' => array()
				),
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 40,
						'maxlength' => 255,
						'class'     => 'regular-text code',
					)
				)
			),

			array(
				'name'     => 'disqus_connect_state',
				'type'     => WblSystem_Config::OPTION_HIDDEN,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Disqus connect state', 'weeblramp' ),
				'desc'     => '',
				'default'  => WeeblrampConfig_User::DISQUS_CONNECT_NOT_CONNECTED,
				'class'    => '',
				'content'  => array()
			),

			// Disabling other plugins -------------------------------------------------
			array(
				'name'     => 'section_plugins_disabling',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Plugins management', 'weeblramp' ),
				'icon'     => 'assets.icons.plug',
			),

			array(
				'name'      => 'plugins_to_disable',
				'type'      => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Disable on AMP pages', 'weeblramp' ),
				'desc'      => __( 'Plugins you select on this list will be <strong>disabled</strong> when displaying an AMP page. Some plugins are not shown as they will be disabled automatically (typically, other AMP plugins, which would otherwise conflict.)', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/comments-plugins/index.html#h2_plugins_management',
				'doc_embed' => true,
				'default'   => array(),
				'class'     => 'wbamp-settings-checkbox-group',
				'content'   => array(
					'attr'             => array(),
					'layout'           => 'vertical', // vertical || horizontal
					'options'          => array(),
					'options_callback' => array( $this, 'optionsCallback_plugins_selector_filtered' )
				)
			),

			// Disabling theme ---------------------------------------------------------
			array(
				'name'     => 'section_theme_disabling',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Theme management', 'weeblramp' ),
				'icon'     => 'assets.icons.plug',
			),

			array(
				'name'      => 'disable_theme',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Disable theme on AMP pages', 'weeblramp' ),
				'caption'   => __( 'Disabled', 'weeblramp' ),
				'desc'      => __( 'Your theme is not used on AMP pages, but some themes may still interfere and include content inside your AMP pages that breaks AMP validation. If this happens, you can disable the theme entirely on AMP pages. If however you see raw codes appearing on your AMP pages, then some required plugins (page builders for instance) are located in your theme, and it should not be disabled.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/comments-plugins/index.html#h2_theme_management',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array(),
				)
			),

			// Worpress content processing ---------------------------------------------
			array(
				'name'     => 'section_wp_content_processing',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'WordPress content processing', 'weeblramp' ),
				'icon'     => 'assets.icons.content',
			),

			array(
				'name'      => 'wp_processing_mode',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Let WP process content...', 'weeblramp' ),
				'desc'      => __( 'Select whether you want to disable some parts of WordPress standard content processing, including all filters and <a href="https://codex.wordpress.org/Shortcode" target="__blank">shortcodes</a>. This can be useful if some plugins still leaves some undesired content on your AMP pages. Note that built-in weeblrAMP shortcodes, such as the <i>gallery</i> one will still be ran - unless you disable them in next field.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/comments-plugins/index.html#h2_content_processing',
				'doc_embed' => true,
				'default'   => WeeblrampConfig_User::WP_CONTENT_NORMAL,
				'class'     => '',
				'content'   => array(
					'attr'             => array(),
					'options'          => array(
						WeeblrampConfig_User::WP_CONTENT_NORMAL     => __( 'As usual, all filters and shortcodes', 'weeblramp' ),
						WeeblrampConfig_User::WP_CONTENT_SHORTCODES => __( 'Only process shortcodes', 'weeblramp' ),
						WeeblrampConfig_User::WP_CONTENT_NONE       => __( 'Disable all filters and shortcodes', 'weeblramp' ),
					),
					'options_callback' => array()
				)
			),

			array(
				'name'     => 'shortcodes_disable_list',
				'type'     => WblSystem_Config::OPTION_TEXTAREA,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Remove shortcodes on this list', 'weeblramp' ),
				'desc'     => __( 'Shortcodes listed here will be disabled and removed from AMP pages. Put one shortcode per line, and do not surround them with square brackets (eg list <i>gallery</i>, not <i>[gallery]</i>)', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array(
						'cols'  => 60,
						'rows'  => 10,
						'class' => 'regular-text code',
					)
				)
			),

		)
	),

	// Integration ------------------------------------------------
	array(
		'name'            => 'integrations',
		'type'            => WblSystem_Config::OPTION_TAB,
		'title'           => __( 'Integrations', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/integrations/index.html',
		'doc_link_button' => __( 'Introduction', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Integrations with other plugins -----------------------------------------
			array(
				'name'     => 'section_integrations',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Integrations', 'weeblramp' ),
				'icon'     => 'assets.icons.plug',
			),

			array(
				'name'      => 'integrations_list',
				'type'      => WblSystem_Config::OPTION_CHECKBOX_GROUP,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Plugins integrations', 'weeblramp' ),
				'desc'      => __( 'weeblrAMP can fetch some data from the plugins listed here, if installed and activated. If you disable an integration, we will also disable the corresponding plugin when displaying an AMP page.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/integrations/index.html#h2_woocommerce',
				'doc_embed' => true,
				'default'   => array(),
				'class'     => 'wbamp-settings-checkbox-group',
				'content'   => array(
					'attr'    => array(),
					'layout'  => 'vertical', // vertical || horizontal
					'options' => array(
						array(
							'name'     => 'wordpress-seo/wp-seo.php',
							'caption'  => 'Yoast SEO',
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'     => 'jetpack/jetpack.php',
							'caption'  => 'JetPack',
							'default'  => 1,
							'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'            => 'contact-form-7/wp-contact-form-7.php',
							'caption'         => 'Contact Form 7',
							'default'         => 1,
							'default_edition' => array(
								WblSystem_Version::EDITION_COMMUNITY => 1,
							),
							'editions'        => array( WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'            => 'easy-digital-downloads/easy-digital-downloads.php',
							'caption'         => 'Easy Digital Downloads',
							'default'         => 1,
							'default_edition' => array(
								WblSystem_Version::EDITION_COMMUNITY => 1,
							),
							'editions'        => array( WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'            => 'gravityforms/gravityforms.php',
							'caption'         => 'Gravity Forms',
							'default'         => 1,
							'default_edition' => array(
								WblSystem_Version::EDITION_COMMUNITY => 1,
							),
							'editions'        => array( WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'            => 'wpforms/wpforms.php',
							'caption'         => 'WPForms',
							'default'         => 1,
							'default_edition' => array(
								WblSystem_Version::EDITION_COMMUNITY => 1,
							),
							'editions'        => array( WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'            => 'mailchimp-for-wp/mailchimp-for-wp.php',
							'caption'         => 'Mailchimp for WordPress',
							'default'         => 1,
							'default_edition' => array(
								WblSystem_Version::EDITION_COMMUNITY => 1,
							),
							'editions'        => array( WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'            => 'beaver-builder/fl-builder.php',
							'caption'         => 'Beaver Builder',
							'default'         => 1,
							'default_edition' => array(
								WblSystem_Version::EDITION_COMMUNITY => 1,
							),
							'editions'        => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'            => 'elementor/elementor.php',
							'caption'         => 'Elementor',
							'default'         => 1,
							'default_edition' => array(
								WblSystem_Version::EDITION_COMMUNITY => 1,
							),
							'editions'        => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'            => 'polylang/polylang.php',
							'caption'         => 'Polylang',
							'default'         => 1,
							'default_edition' => array(
								WblSystem_Version::EDITION_COMMUNITY => 1,
							),
							'editions'        => array( WblSystem_Version::EDITION_FULL ),
						),
						array(
							'name'            => 'woocommerce/woocommerce.php',
							'caption'         => 'WooCommerce',
							'default'         => 1,
							'default_edition' => array(
								WblSystem_Version::EDITION_COMMUNITY => 1,
							),
							'editions'        => array( WblSystem_Version::EDITION_FULL ),
						),
					)
				)
			),
		)

	),

	// System -----------------------------------------------------
	array(
		'name'            => 'system',
		'type'            => WblSystem_Config::OPTION_TAB,
		'title'           => __( 'System', 'weeblramp' ),
		'doc_link'        => 'products.weeblramp/1/going-further/system/index.html',
		'doc_link_button' => __( 'Introduction', 'weeblramp' ),
		'doc_embed'       => true,
		'content'         => array(

			// Rendering section -------------------------------------------------------
			array(
				'name'     => 'section_rendering',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Rendering', 'weeblramp' ),
				'icon'     => 'assets.icons.display',
			),

			array(
				'name'      => 'link_auto_amp_by_class',
				'type'      => WblSystem_Config::OPTION_TEXTAREA,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'AMPlify links by CSS class', 'weeblramp' ),
				'desc'      => __( 'Links that have a CSS class listed here will be AMPlified, if they have an AMP equivalent. List one class per line. If a link must have 2 or more classes to be AMPlified, list them on the same line.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_auto_amplify_links_by_css_clas',
				'doc_embed' => true,
				'default'   => 'wbamp-link',
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
				'name'      => 'email_protection',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => 'Protect email addresses',
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If set to yes, wbAMP will apply some encoding to email addresses on AMP pages, to help protect them from automated emails collectors. The protection method does not use javascript, but should be efficient in many cases.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_protect_email_addresses',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'embed_user_tags',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Process Weeblramp embed codes', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled, weeblrAMP custom embed codes in your content will be turned into their AMP equivalent. Embed codes look like [wbamp type="twitter" tweetid="123456789" cards="hidden"]. If disabled, those codes will still be removed from content.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_process_weeblramp_embed_codes',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'embed_auto_link',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Auto embed known links', 'weeblramp' ),
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled, known URLs in your content will be turned into their AMP equivalent. Example URLs are Twitter links such as https://twitter.com/weeblr/status/616276503786049536', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_auto-embed_known_links',
				'doc_embed' => true,
				'default'   => 1,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			// Update credentials section ----------------------------------------------
			array(
				'name'     => 'section_update_credentials',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Access credentials', 'weeblramp' ),
				'icon'     => 'assets.icons.lock'
			),

			array(
				'name'      => 'access_key',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Access key', 'weeblramp' ),
				'desc'      => __( 'Enter here your update access key. Get it from <a href="https://www.weeblrpress.com/dashboard" target="_blank">your dashboard.</a>', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h2_access_credentials',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 100,
						'class'     => 'regular-text code',
					)
				)
			),

			// Others section ----------------------------------------------------------
			array(
				'name'     => 'section_others',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Others', 'weeblramp' ),
				'icon'     => 'assets.icons.gear',
			),

			array(
				'name'      => 'amp_suffix',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'AMP suffix', 'weeblramp' ),
				'desc'      => __( 'The suffix to use to recognize AMP pages. Defaul to <strong>amp</strong>. Not used if in Standalone mode, where all pages are AMPlified anyway.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_amp_suffix',
				'doc_embed' => true,
				'default'   => 'amp',
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 50,
						'class'     => 'regular-text',
					)
				)
			),

			array(
				'name'      => 'hide_debug_module',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => 'Hide debug module',
				'caption'   => __( 'Hide module', 'weeblramp' ),
				'desc'      => __( 'Hide the links to the AMP validator and Google Structured Data tester normally displayed at top of page when in <strong>Development mode</strong>.<br /><strong>NB</strong>: the module is never displayed on localhost or 127.0.0.1, as validators need access to the pages tested.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_hide_debug_module',
				'doc_embed' => true,
				'default'   => 0,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'debug_token',
				'type'      => WblSystem_Config::OPTION_TEXT,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Debug token', 'weeblramp' ),
				'desc'      => __( 'A random token that will be required to access your AMP pages when in <strong>Development mode</strong>. This is used to prevent general access, but let AMP validators read the pages to test them. Empty the field to disable that feature. You can access the AMP version of a page by appending <strong>/amp/?amptoken=YOUR_DEBUG_TOKEN</strong> to the page regular URL.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_debug_token',
				'doc_embed' => true,
				'default'   => $this->defaultDebugToken,
				'class'     => '',
				'content'   => array(
					'attr' => array(
						'size'      => 20,
						'maxlength' => 50,
						'class'     => 'regular-text',
					)
				)
			),

			// Really obscure settings -------------------------------------------------
			array(
				'name'     => 'section_obscure',
				'type'     => WblSystem_Config::OPTION_SECTION,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'    => __( 'Really obscure settings', 'weeblramp' ),
				'icon'     => 'assets.icons.obscure',
			),

			array(
				'name'      => 'logging_level',
				'type'      => WblSystem_Config::OPTION_LIST,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'title'     => __( 'Logging level', 'weeblramp' ),
				'desc'      => __( 'Select how much details weeblrAMP should log to a log file. In normal operation, set this to <strong>Normal</strong>, which will only record errors. When researching issues, it might be useful to increase to <strong>Detailed</strong>. Log files are stored in the /wp-content/weeblramp_logs directory on your server, and can only be accessed with FTP. <strong>NB</strong>: if your site runs with WP_DEBUG set to true, <strong>Detailed</strong> logging is used unless you set it otherwise', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_logging',
				'doc_embed' => true,
				'default'   => WblSystem_Log::LOGGING_PRODUCTION,
				'class'     => '',
				'content'   => array(
					'attr'             => array(),
					'options'          => array(
						WblSystem_Log::LOGGING_NONE       => __( 'None', 'weeblramp' ),
						WblSystem_Log::LOGGING_PRODUCTION => __( 'Normal', 'weeblramp' ),
						WblSystem_Log::LOGGING_DETAILED   => __( 'Debugging', 'weeblramp' ),
					),
					'options_callback' => array()
				)
			),

			array(
				'name'      => 'remote_configuration',
				'type'      => WblSystem_Config::OPTION_CHECKBOX,
				'editions'  => array( WblSystem_Version::EDITION_FULL ),
				'title'     => 'Remote configuration',
				'caption'   => __( 'Enabled', 'weeblramp' ),
				'desc'      => __( 'If enabled, weeblrAMP will look for a small JSON file on WeeblrPress server to read updated AMP specification definition. This allows updating the specification without requiring you to update the entire plugin. The remote information is cached for 12 hours, so that it does not add any load on your server.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_remote_configuration',
				'doc_embed' => true,
				'default'   => 0,
				'class'     => '',
				'content'   => array(
					'attr' => array()
				)
			),

			array(
				'name'     => 'clear_transients',
				'type'     => WblSystem_Config::OPTION_CLEAR_TRANSIENTS,
				'editions' => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'use_ajax' => true,
				'title'    => __( 'Clear cached data', 'weeblramp' ),
				'desc'     => __( 'Some parts of your AMP pages are stored in cache (for 12 hours usually), for faster rendering. Press this button to clear the saved data and have new one rebuilt. Use if settings changes are not reflected immediately on AMP pages.', 'weeblramp' ),
				'default'  => '',
				'class'    => '',
				'content'  => array(
					'attr' => array()
				)
			),

			array(
				'name'      => 'flush_rewrite_rules',
				'type'      => WblSystem_Config::OPTION_FLUSH_REWRITE_RULES,
				'editions'  => array( WblSystem_Version::EDITION_COMMUNITY, WblSystem_Version::EDITION_FULL ),
				'use_ajax'  => true,
				'title'     => __( 'Flush rewrite rules', 'weeblramp' ),
				'desc'      => __( 'Flush WordPress rewrite rules. May be needed after installng a new plugin for instance.', 'weeblramp' ),
				'doc_link'  => 'products.weeblramp/1/going-further/system/index.html#h3_flush_rewrite_rules',
				'doc_embed' => true,
				'default'   => '',
				'class'     => '',
				'content'   => array(
					'attr' => array()
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

