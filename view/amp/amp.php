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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Render an AMP page
 */
class WeeblrampViewAmp extends WeeblrampClass_View {

	/**
	 * Renders the view content, returning it in a string and
	 * optionally echoing it
	 */
	public function render() {

		try {
			$output = '';
			if ( ! empty( $this->layouts ) ) {
				// render GTM and sidebar
				$this->__data['tags_menus'] = WblMvcLayout_Helper::render(
					'weeblramp.frontend.amp.tags_menus',
					$this->__data,
					$this->baseLayoutPath
				);

				// render the page content (body)
				$this->__data['rendered_body'] = WblMvcLayout_Helper::render(
					$this->getLayout( 'body' ),
					$this->__data,
					$this->baseLayoutPath
				);

				// process shortcodes for main body
				/**
				 * Before processing the main post content.
				 *
				 * @api
				 * @package weeblrAMP\action\display
				 * @var weeblramp_before_main_content_process
				 * @since   1.8.3
				 *
				 * @params array $pageData The full page information data array.
				 */
				do_action(
					'weeblramp_before_main_content_process',
					$this->__data
				);
				$this->__data['rendered_body'] = $this->__data['content_processor']->doWPProcessing( $this->__data['rendered_body'], $post = null );
				/**
				 * After processing the main post content.
				 *
				 * @api
				 * @package weeblrAMP\action\display
				 * @var weeblramp_after_main_content_process
				 *
				 * @since   1.8.3
				 *
				 * @param array $pageData The full page information data array.
				 */
				do_action(
					'weeblramp_after_main_content_process',
					$this->__data
				);

				// process shortcodes for footer
				/**
				 * Before processing the post footer content.
				 *
				 * @api
				 * @package weeblrAMP\action\display
				 * @var weeblramp_before_content_footer_process
				 * @param array $pageData The full page information data array.
				 * @since   1.8.3
				 *
				 */
				do_action(
					'weeblramp_before_content_footer_process',
					$this->__data
				);
				$this->__data['footer'] = $this->__data['content_processor']->doWPProcessing( $this->__data['footer'], $post = null );
				/**
				 * After processing the post footer content.
				 *
				 * @api
				 * @package weeblrAMP\action\display
				 * @var weeblramp_after_content_footer_process
				 * @param array $pageData The full page information data array.
				 * @since   1.8.3
				 *
				 */
				do_action(
					'weeblramp_after_content_footer_process',
					$this->__data
				);

				// convert to amp
				$this->__data['tags_menus']    = $this->__data['amp_processor']->convert( $this->__data['tags_menus'] );
				$this->__data['rendered_body'] = $this->__data['amp_processor']->convert( $this->__data['rendered_body'] );
				$this->__data['footer']        = $this->__data['amp_processor']->convert( $this->__data['footer'] );
				$this->__data['page_bottom']   = $this->__data['amp_processor']->convert( $this->__data['page_bottom'] );

				// restore "protected" areas, that were removed as they can have normally invalid content (forms for instance)
				$this->__data['tags_menus']    = $this->__data['content_protector']->injectProtectedContent( $this->__data['tags_menus'] );
				$this->__data['rendered_body'] = $this->__data['content_protector']->injectProtectedContent( $this->__data['rendered_body'] );
				$this->__data['footer']        = $this->__data['content_protector']->injectProtectedContent( $this->__data['footer'] );
				$this->__data['page_bottom']   = $this->__data['content_protector']->injectProtectedContent( $this->__data['page_bottom'] );

				// apply all additional filters and clean up
				$this->__data['tags_menus'] = $this->__data['amp_processor']->amplifyLinks( $this->__data['tags_menus'] );
				$this->__data['tags_menus'] = $this->__data['amp_processor']->applyCleanupFilters( $this->__data['tags_menus'] );
				$this->__data['tags_menus'] = WeeblrampHelper_Content::scrubAmpHtmlPage( $this->__data['tags_menus'] );

				$this->__data['rendered_body'] = $this->__data['amp_processor']->amplifyLinks( $this->__data['rendered_body'] );
				$this->__data['rendered_body'] = $this->__data['amp_processor']->applyCleanupFilters( $this->__data['rendered_body'] );
				$this->__data['rendered_body'] = WeeblrampHelper_Content::scrubAmpHtmlPage( $this->__data['rendered_body'] );

				$this->__data['footer'] = $this->__data['amp_processor']->amplifyLinks( $this->__data['footer'] );
				$this->__data['footer'] = $this->__data['amp_processor']->applyCleanupFilters( $this->__data['footer'] );
				$this->__data['footer'] = WeeblrampHelper_Content::scrubAmpHtmlPage( $this->__data['footer'] );

				// collect all styles and scripts that may be required by all elements on the page
				$this->__data['amp_scripts']   = WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' )->getScripts();
				$this->__data['amp_templates'] = WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' )->getTemplates();
				$this->__data['css']           = $this->getCss();

				// finally insert the body in the full page template, and render it (head section)
				$output = WblMvcLayout_Helper::render(
					$this->getLayout( 'page' ),
					$this->__data,
					$this->baseLayoutPath
				);

				if ( $this->echoOutput ) {
					echo $output;
				}
			}

			return $output;
		}
		catch ( Exception $e ) {
			WblWordpress_Helper::dieNicely(
				sprintf( 'Error displaying a %s contentpage', WEEBLRAMP_PLUGIN_NAME ),
				$e->getMessage(),
				array(
					'response'  => 500,
					'back_link' => true
				)
			);
		}
	}

	/**
	 * Collect all CSS, in the following order:
	 *   - from the template
	 *   - from the theme, through the weeblramp_theme_css filter
	 *   - set by user in the admin custom_css option
	 * Those 3 parts are store in the returned array under the 'template', 'theme' and 'user'
	 * keys respectively
	 *
	 * @return $this
	 */
	private function getCss() {

		// storage and get the assets collector model
		$css = array();

		// which default files to always include?
		$coreCssFiles = array(
			'core',
			'layout',
			'amp'
		);

		// compute core css files list
		$content = wbArrayGet(
			$this->__data,
			'rendered_body',
			''
		);
		if ( wbContains(
			$content,
			'wp-block-gallery'
		) ) {
			$coreCssFiles[] = 'gutenberg-gallery';
		}

		// collect all customized CSS, built in and from themes
		$css['template'] = WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' )
		                                   ->addCustomizer( $this->customizeConfig )
		                                   ->getCustomizedStyles(
			                                   $this->__data['theme'],
			                                   $coreCssFiles
		                                   );

		// collect any additional CSS by other extensions and themes
		/**
		 * Filter CSS to be included in AMP pages.
		 *
		 * Provide valid AMP CSS to be included in the currently rendered AMP page. Will be added to the CSS provided by weeblrAMP built-in CSS and the CSS provided by user through the control panel.
		 * CSS is collected in the following order:
		 *   - from the built-in template
		 *   - from the theme, through the weeblramp_theme_css filter
		 *   - set by user in the admin custom_css option
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_theme_css
		 * @since   1.0.0
		 *
		 * @param string $themeCSS Raw CSS to be included in AMP page
		 *
		 * @return string
		 */
		$css['theme'] = apply_filters( 'weeblramp_theme_css', '' );

		// finally override by user input CSS
		$css['user'] = StringHelper::trim( $this->__data['custom_style'] );

		return $css;
	}
}
