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
 * Settings definition for AMP sanitization
 *
 * Used by class WeeblrampConfig_Amp
 */
return array(

	// Definitions of AMP HTML rules ---------------------------------------------------
	array(
		'name'    => 'amp_def',

		// options in group
		'content' => array(

			// Global white list ---------------------------------------------------------------
			// https://github.com/ampproject/amphtml/blob/master/spec/amp-tag-addendum.md
			array(
				'name'    => 'tags_white_list',
				'default' => array(
					/* 'html', 'head','title','link','style' not in body */
					'meta',
					'link',
					'body',
					'article',
					'section',
					'nav',
					'aside',
					'h1',
					'h2',
					'h3',
					'h4',
					'h5',
					'h6',
					'header',
					'footer',
					'address',
					'p',
					'hr',
					'pre',
					'blockquote',
					'ol',
					'ul',
					'li',
					'dl',
					'dt',
					'dd',
					'figure',
					'figcaption',
					'div',
					'main',
					'a',
					'em',
					'strong',
					'small',
					's',
					'cite',
					'q',
					'dfn',
					'abbr',
					'data',
					'time',
					'code',
					'var',
					'samp',
					'kbd',
					'sub',
					'sup',
					'i',
					'b',
					'u',
					'mark',
					'ruby',
					'rb',
					'rt',
					'rtc',
					'rp',
					'bdi',
					'bdo',
					'span',
					'br',
					'wbr',
					'ins',
					'del',
					'source',
					'svg',
					'g',
					'path',
					'glyph',
					'glyphref',
					'marker',
					'view',
					'circle',
					'line',
					'polygon',
					'polyline',
					'rect',
					'text',
					'textpath',
					'tref',
					'tspan',
					'clippath',
					'filter',
					'lineargradient',
					'radialgradient',
					'mask',
					'pattern',
					'vkern',
					'hkern',
					'defs',
					'use',
					'symbol',
					'desc',
					'title',
					'table',
					'caption',
					'colgroup',
					'col',
					'tbody',
					'thead',
					'tfoot',
					'tr',
					'td',
					'th',
					'button',
					'script',
					'noscript',
					'acronym',
					'big',
					'center',
					'dir',
					'hgroup',
					'listing',
					'multicol',
					'nextid',
					'nobr',
					'spacer',
					'strike',
					'tt',
					'xmp',
					'o:p',
					// form support
					//'form',
					'input',
					'textarea',
					'select',
					'option',
					'fieldset',
					'label',
					//'template'
				)
			),

			// HTML global attributes ----------------------------------------------------------
			array(
				'name'    => 'global_attributes',

				// options in group
				'default' => array(
					'itemid',
					'itemprop',
					'itemref',
					'itemscope',
					'itemtype',
					'class',
					'id',
					'title',
					'tabindex',
					'dir',
					'draggable',
					'lang',
					'accesskey',
					'translate',
					'role',
					'placeholder',
					'fallback'
				)
			),

			// Rel attributes whitelist --------------------------------------------------------
			// http://microformats.org/wiki/existing-rel-values
			array(
				'name'    => 'rel_white_list',

				// options in group
				'default' => array(
					'accessibility',
					'alternate',
					'apple-touch-icon',
					'apple-touch-icon-precomposed',
					'apple-touch-startup-image',
					'appendix',
					'archived',
					'archive',
					'archives',
					'attachment',
					'author',
					'bibliography',
					'category',
					'cc:attributionurl',
					'chapter',
					'chrome-webstore-item',
					'cite',
					'code-license',
					'code-repository',
					'colorschememapping',
					'comment',
					'content-license',
					'content-repository',
					'contents',
					'contribution',
					'copyright',
					'designer',
					'directory',
					'discussion',
					'dofollow',
					'edit-time-data',
					'EditURI',
					'endorsed',
					'fan',
					'feed',
					'file-list',
					'follow',
					'footnote',
					'galeria',
					'galeria2',
					'generator',
					'glossary',
					'group',
					'help',
					'home',
					'homepage',
					'hub',
					'icon',
					'image_src',
					'in-reply-to',
					'index',
					'indieauth',
					'introspection',
					'issues',
					'its-rules',
					'jslicense',
					'license',
					'lightbox',
					'made',
					'manifest',
					'map',
					'me',
					'member',
					'meta',
					'micropub',
					'microsummary',
					'next',
					'nofollow',
					'noreferrer',
					'ole-object-data',
					'original-source',
					'owns',
					'p3pv1',
					'payment',
					'pgpkey',
					'pingback',
					'prettyphoto',
					'privacy',
					'pronounciation',
					'profile',
					'pronunciation',
					'publisher',
					'prev',
					'previous',
					'referral',
					'related',
					'rendition',
					'replies',
					'reply-to',
					'schema.dc',
					'schema.DCTERMS',
					'search',
					'section',
					'service',
					'service.post',
					'shortcut',
					'shortlink',
					'sidebar',
					'sitemap',
					'source',
					'sponsor',
					'sponsored',
					'start',
					'status',
					'subsection',
					'syndication',
					'tag',
					'themedata',
					'timesheet',
					'toc',
					'token_endpoint',
					'top',
					'trackback',
					'transformation',
					'ugc',
					'unendorsed',
					'up',
					'user',
					'vcalendar-parent',
					'vcalendar-sibling',
					'webmention',
					'wikipedia',
					'wlwmanifest',
					'yandex-tableau-widget'
				)
			),

			// Per tag always allowed attributes -----------------------------------------------
			array(
				'name'    => 'per_tag_attr_default_white_list',

				// options in group
				'default' => array(
					'__wbamp_global__',
					'__wbamp_data__',
					'__wbamp_aria__'
				)
			),

			// Partial per tag white list for attributes ---------------------------------------
			// Complete per tag, but some tags are missing
			array(
				'name'    => 'per_tag_attr_white_list',

				// options in group
				'default' => array(
					'a'          => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'href',
						'hreflang',
						'target',
						'rel',
						'name',
						'download',
						'media',
						'type',
						'border'
					),
					'audio'      => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'autoplay',
						'controls',
						'loop',
						'muted',
						'preload',
						'src'
					),
					'bdo'        => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'dir' ),
					'blockquote' => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'cite' ),
					'button'     => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'disabled',
						'name',
						'type',
						'value',
						'on'
					),
					'caption'    => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'align' ),
					'col'        => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'span' ),
					'colgroup'   => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'align' ),
					'del'        => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'cite', 'datetime' ),
					'img'        => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'alt',
						'border',
						'height',
						'ismap',
						'longdesc',
						'src',
						'srcset',
						'width'
					),
					'ins'        => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'cite', 'datetime' ),
					'li'         => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'value' ),
					'link'       => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'crossorigin',
						'href',
						'hreflang',
						'media',
						'rel',
						'type'
					),
					// http-equiv forbidden on meta
					'meta'       => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'charset',
						'content',
						'name'
					),
					'ol'         => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'reversed',
						'start',
						'type'
					),
					'path'       => array( '__wbamp_any__' ),
					'q'          => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'cite' ),
					'section'    => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'expanded' ),
					'script'     => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'type' ),
					'source'     => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'sizes',
						'src',
						'type'
					),
					'svg'        => array( '__wbamp_any__' ),
					'table'      => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'align',
						'border',
						'bgcolor',
						'cellpadding',
						'cellspacing',
						'width'
					),
					'tbody'      => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__' ),
					'td'         => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'colspan',
						'headers',
						'rowspan',
						'align',
						'bgcolor',
						'height',
						'valign',
						'width'
					),
					'tfoot'      => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__' ),
					'th'         => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'abbr',
						'colspan',
						'headers',
						'rowspan',
						'scope',
						'sorted',
						'align',
						'bgcolor',
						'height',
						'valign',
						'width'
					),
					'thead'      => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__' ),
					'tr'         => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'align',
						'bgcolor',
						'height',
						'valign'
					),
					'video'      => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'autoplay',
						'controls',
						'height',
						'loop',
						'muted',
						'poster',
						'preload',
						'src',
						'width'
					),

					// form support
					'input'      => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'accept',
						'autocomplete',
						'autofocus',
						'checked',
						'disabled',
						'height',
						'inputmode',
						'list',
						'max',
						'maxlength',
						'min',
						'minlength',
						'multiple',
						'name',
						'pattern',
						'readonly',
						'required',
						'selectiondirection',
						'size',
						'spellcheck',
						'step',
						'type',
						'value',
						'width'
					),
					'textarea'   => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'autocomplete',
						'autofocus',
						'cols',
						'disabled',
						'maxlength',
						'minlenght',
						'name',
						'readonly',
						'required',
						'rows',
						'selectiondirection',
						'selectionend',
						'selectionstart',
						'spellcheck',
						'wrap'
					),
					'select'     => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'autofocus',
						'disabled',
						'multiple',
						'name',
						'required',
						'selected',
						'size',
						'on'
					),
					'optgroup'   => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'disabled',
						'label'
					),
					'option'     => array(
						'__wbamp_global__',
						'__wbamp_data__',
						'__wbamp_aria__',
						'disabled',
						'label',
						'selected',
						'value'
					),
					'fieldset'   => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'disabled', 'name' ),
					'legend'     => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__' ),
					'label'      => array( '__wbamp_global__', '__wbamp_data__', '__wbamp_aria__', 'for' ),
				)
			),

			// Attributes that must be removed, but only on some tags --------------------------
			array(
				'name'    => 'per_tag_attr_black_list',

				// options in group
				'default' => array(
					'article' => array( 'itemtype' ),
					'aside'   => array( 'itemtype' ),
					'section' => array( 'itemtype' ),
					'span'    => array( 'aria-current' ),
				)
			),

			// Invalid protocols for attribute to be removed ----------------------------------
			array(
				'name'    => 'protocols_def',

				// options in group
				'default' => array(
					'a.href'         => array(
						'allowed' => array(
							'ftp',
							'http',
							'https',
							'mailto',
							'fb-messenger',
							'intent',
							'skype',
							'sms',
							'snapchat',
							'tel',
							'tg',
							'threema',
							'twitter',
							'viber',
							'whatsapp'
						),
					),
					'link.href'      => array(
						'allowed' => array(
							'http',
							'https'
						),
					),
					// img not handled yet, only allowed within <noscript>
					'img.src'        => array(
						'allowed' => array(
							'data',
							'https'
						),
					),
					'video.src'      => array(
						'allowed' => array(
							'data',
							'https'
						),
					),
					'audio.src'      => array(
						'allowed' => array(
							'data',
							'https'
						),
					),
					'amp-iframe.src' => array(
						'allowed' => array(
							'data',
							'https'
						),
					)
				)
			),

			// Attributes that will always be removed ------------------------------------------
			array(
				'name'    => 'invalid_attributes',

				// options in group
				'default' => array(
					'style'
				)
			),

			// Some tags are only allowed within others. Currently only checking direct parents
			array(
				'name'    => 'tag_mandatory_parents',

				// options in group
				'default' => array(
					//'example' => array
					//(
					//	'forbidden_parents' => array(),
					//	'mandatory_parents' => array( 'amp-analytics', 'amp-social-share' )
					//)
					'p'    => array
					(
						'forbidden_parents' => array( 'amp-accordion' ),
						'mandatory_parents' => array()
					),
					'div'  => array
					(
						'forbidden_parents' => array( 'amp-accordion' ),
						'mandatory_parents' => array()
					),
					'span' => array
					(
						'forbidden_parents' => array( 'amp-accordion' ),
						'mandatory_parents' => array()
					)
				)
			),

			// Some tags may be required to have one or more -----------------------------------
			// attributes. They can either be removed if
			// an attribute is missing, or the attr can
			// be added with a default value
			array(
				'name'    => 'tag_mandatory_attr',

				// options in group
				'default' => array(
					'script' => array
					(
						'type' => array(
							'action'    => 'remove_tag', // add | remove_tag
							'add_value' => ''
						)
					),
				)
			),

			// Attribute is valid but must have specific values --------------------------------
			// Attribute value is enforced
			array(
				'name'    => 'attr_forced_value',

				// options in group
				'default' => array(
					'a.target' => array
					(
						'allow'        => array( '_blank', '_self' ),
						'forced_value' => '_blank'
					)
				)
			),

			// Attribute is valid but must have a specific value -------------------------------
			// @TODO: remove $_attr_forced_value rules which can now be expressed using $_attr_mandatory_value
			array(
				'name'    => 'attr_mandatory_value',

				// options in group
				'default' => array(
					'script.type'  => array
					(
						'processed_values' => array(
							'application/ld+json' => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'application/json'    => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							)
						),
						'other_values'     => array(
							'action'       => 'remove_tag', // allow | replace | remove_attr | remove_tag
							'replace_with' => ''
						)
					),
					'a.type'       => array
					(
						'processed_values' => array(
							'text/html' => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							)
						),
						'other_values'     => array(
							'action'       => 'remove_attr', // allow | replace | remove_attr | remove_tag
							'replace_with' => ''
						)
					),
					'a.href'       => array
					(
						'processed_values' => array(
							'void'                => array(
								'action'       => 'replace', // allow | replace | remove_attr | remove_tag
								'replace_with' => '#0'
							),
							'void(0)'             => array(
								'action'       => 'replace', // allow | replace | remove_attr | remove_tag
								'replace_with' => '#0'
							),
							'void(0);'            => array(
								'action'       => 'replace', // allow | replace | remove_attr | remove_tag
								'replace_with' => '#0'
							),
							'Void'                => array(
								'action'       => 'replace', // allow | replace | remove_attr | remove_tag
								'replace_with' => '#0'
							),
							'Void(0)'             => array(
								'action'       => 'replace', // allow | replace | remove_attr | remove_tag
								'replace_with' => '#0'
							),
							'Void(0);'            => array(
								'action'       => 'replace', // allow | replace | remove_attr | remove_tag
								'replace_with' => '#0'
							),
							'__amp_source_origin' => array(
								'action'       => 'replace', // allow | replace | remove_attr | remove_tag
								'replace_with' => '#0'
							)
						),
						'empty'            => array(
							'action'       => 'replace', // allow | replace | remove_attr | remove_tag
							'replace_with' => '#0'
						)
					),
					'meta.charset' => array
					(
						'other_values' => array(
							'action'       => 'remove_tag', // allow | replace | remove_attr | remove_tag
							'replace_with' => ''
						),
						'empty'        => array(
							'action' => 'remove_tag', // allow | replace | remove_attr | remove_tag
						)
					),
					'table.border' => array
					(
						'processed_values' => array(
							'0' => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'1' => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							)
						),
						'other_values'     => array(
							'action'       => 'remove_attr', // allow | replace | remove_attr | remove_tag
							'replace_with' => ''
						)
					),

					'input.type' => array
					(
						'processed_values' => array(
							'checkbox'       => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'color'          => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'date'           => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'datetime-local' => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'email'          => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'hidden'         => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'month'          => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'number'         => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'radio'          => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'range'          => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'reset'          => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'search'         => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'submit'         => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'tel'            => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'text'           => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'time'           => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'url'            => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
							'week'           => array(
								'action'       => 'allow', // allow | replace | remove_attr | remove_tag
								'replace_with' => ''
							),
						),
						'other_values'     => array(
							'action'       => 'remove_tag', // allow | replace | remove_attr | remove_tag
							'replace_with' => ''
						)
					)

				)
			),

			// Attribute has forbidden value ---------------------------------------------------
			array(
				'name'    => 'attr_forbidden_value',

				// options in group
				'default' => array(
					'div.itemtype' => array(
						'http://schema.org/Article'      => array(
							'action'       => 'remove_attr', // replace | remove_attr | remove_tag
							'replace_with' => ''
						),
						'http://schema.org/NewsArticle'  => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
						'http://schema.org/BlogPosting'  => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
						'http://schema.org/Blog'         => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
						'https://schema.org/Article'     => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
						'https://schema.org/NewsArticle' => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
						'https://schema.org/BlogPosting' => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
						'https://schema.org/Blog'        => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
					),
					'link.rel'     => array(
						'stylesheet' => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
						'preconnect' => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
						'prerender'  => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
						'prefetch'   => array(
							'action'       => 'remove_attr',
							'replace_with' => ''
						),
					),
				)
			),

			// List of article types to use ----------------------------------------------------
			// Not used to whitelist, to allow for user customization
			array(
				'name'    => 'documentTypes',

				// options in group
				'default' => array(
					'article'    => 'Article',
					'blog'       => 'BlogPosting',
					'news'       => 'NewsArticle',
					'photograph' => 'Photograph',
					'recipe'     => 'Recipe',
					'review'     => 'Review',
					'webpage'    => 'WebPage'

				)
			),

			// Width and height for the publisher logo size -----------------------------------
			array(
				'name'    => 'publisherLogoSize',

				// options in group
				'default' => array(
					'width'  => 600,
					'height' => 60
				)
			),

			// Minimal width for a page image ---------------------------------------------------
			array(
				'name'    => 'pageImageMinWidth',

				// options in group
				'default' => 1200
			),

			// Minimal width for a page image. Not specced, just to have something --------------
			array(
				'name'    => 'pageImageMinHeight',

				// options in group
				'default' => 100
			),

			// Minimal pixels count in page image. ----------------------------------------------
			array(
				'name'    => 'pageImageMinPixels',

				// options in group
				'default' => 800000
			),

			// Allowed image types for page image. ----------------------------------------------
			array(
				'name'    => 'pageImageTypes',

				// options in group
				'default' => array('jpg', 'png', 'gif')
			),

			// Max length of json-ld headline --------------------------------------------------
			array(
				'name'    => 'headlineMaxLength',

				// options in group
				'default' => 110
			),

			// Default cleanup regular expressions ---------------------------------------------
			array(
				'name'    => 'default_cleanup_regexp',

				// options in group
				'default' => "
; -------- Default cleanup expressions -------
"
			),

			// Self closing tags, for cleanup --------------------------------------------------
			array(
				'name'    => 'self_closing_tags',

				// options in group
				'default' => array(
					'br',
					'meta',
					'link'
				)
			),

			// Auto closing tags, for cleanup --------------------------------------------------
			array(
				'name'    => 'auto_closing_tags',

				// options in group
				'default' => array(
					'hr'
				)
			),
		),
	)
);
