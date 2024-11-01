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

class WeeblrampModel_Assetscollector extends WeeblrampClass_Model {

	private $_scripts     = array();
	private $_templates   = array();
	private $_styles      = array();
	private $_customizers = array();

	/**
	 * Add an amp tag handler script definition
	 * to the list of scripts to load in the page
	 *
	 * @param $scripts
	 */
	public function addScripts( $scripts ) {

		$this->_scripts = array_merge( $this->_scripts, (array) $scripts );

		return $this;
	}

	/**
	 * Removes a script from the script list. Used by some user-set tags
	 * to remove scripts on a page by page basis.
	 *
	 * @param string $scriptKey Unique id for the script to remove
	 */
	public function removeScript( $scriptKey ) {

		if ( array_key_exists( $scriptKey, $this->_scripts ) ) {
			unset( $this->_scripts[ $scriptKey ] );
		}
	}

	/**
	 * Add an amp tag handler template definition
	 * to the list of template scripts to load in the page
	 *
	 * @param $scripts
	 */
	public function addTemplates( $templates ) {

		$this->_templates = array_merge( $this->_templates, (array) $templates );

		return $this;
	}

	/**
	 * Collect all scripts added by renderer or postprocessor
	 *
	 * @return array
	 */
	public function getScripts() {

		/**
		 * Filter the list of (AMP) scripts collected to be inserted on AMP pages
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_get_scripts
		 * @since   1.0.0
		 *
		 * @param array $scripts The list of scripts
		 *
		 * @return array
		 */
		return apply_filters( 'weeblramp_get_scripts', $this->_scripts );
	}

	/**
	 * Collect all template scripts added by renderer or postprocessor
	 *
	 * @return array
	 */
	public function getTemplates() {

		/**
		 * Filter the list of (AMP) mustache templates collected to be inserted on AMP pages
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_get_templates
		 * @since   1.0.0
		 *
		 * @param array $templates The list of templates
		 *
		 * @return array
		 */
		return apply_filters( 'weeblramp_get_templates', $this->_templates );
	}

	/**
	 * Add an amp tag handler script definition
	 * to the list of scripts to load in the page
	 *
	 * @param $scripts
	 */
	public function addStyle( $style ) {

		if ( ! empty( $style ) && ! in_array( $style, $this->_styles ) ) {
			$this->_styles = array_merge( $this->_styles, (array) $style );
		}

		return $this;
	}

	/**
	 * Getter for collected CSS styles
	 *
	 * @return array
	 */
	public function getStyles() {

		/**
		 * Filter the list of (AMP) styles collected to be inserted on AMP pages
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_get_styles
		 * @since   1.0.0
		 *
		 * @param array $styles The list of styles to include in the page. Each style is the name of  CSS module, as available in the /assets/dist/css folder: ads, amp, carousel, comments, content, core, etc
		 *
		 * @return array
		 */
		return apply_filters( 'weeblramp_get_styles', $this->_styles );
	}

	/**
	 * Add a configuration object that may contain
	 * css customization settings
	 *
	 * NB Better use the weeblramp_css_customizers filter to add customization options
	 *
	 * @param WblSystem_Config $customizer
	 *
	 * @return $this
	 */
	public function addCustomizer( $customizer, $prefix = 'wb' ) {

		$this->_customizers[] =
			array(
				'prefix'     => $prefix,
				'customizer' => $customizer
			);

		return $this;
	}

	/**
	 * Read all styles from files, and apply all user
	 * customizations that have been registered
	 *
	 * @param string $theme
	 * @param array  $defaultStyles
	 *
	 * @return string
	 */
	public function getCustomizedStyles( $theme, $defaultStyles = array() ) {

		$styles = array_filter(
			array_merge(
				$defaultStyles,
				$this->getStyles()
			)
		);

		$css = '';

		// collect all styles
		$htmlManager = WeeblrampFactory::getThe( 'weeblramp.html_manager' );
		foreach ( $styles as $style ) {
			// shared LESS generated css
			$filename = $htmlManager->getMediaFullPath(
				'weeblramp_fe.' . $style,
				'css',
				array(
					'files_root'      => ABSPATH,
					'files_path'      => array( $theme => '' ),
					'assets_bundling' => false
				)
			);
			$css      .= WblFs_File::getIncludedFile( $filename );
		}

		// clean up copyright and license, waste of space here
		$css = preg_replace( '#\/\*\!.*\*\/#mU', '', $css );

		// apply user-customization
		foreach ( $this->getCustomizers() as $customizerDef ) {
			$css = WeeblrampFactory::getA(
				'WeeblrampClass_Customize',
				array( 'customize_config' => $customizerDef['customizer'] )
			)->customize(
				$css,
				$customizerDef['prefix']
			);
		}

		return $css;
	}

	/**
	 * Collect all CSS customization configuration objects
	 *
	 * @return array
	 */
	private function getCustomizers() {

		/**
		 * Filter list of CSS customizers.
		 *
		 * A customizer is a WeeblrampClass_Customize object, used to process a CSS string, replacing agreed upon tags with values provided by the user in the control panel
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_css_customizers
		 * @since   1.0.0
		 *
		 * @param array $customizers List of WeeblrampClass_Customize customizers
		 *
		 * @return array
		 */
		$customizers = apply_filters(
			'weeblramp_css_customizers',
			$this->_customizers
		);

		return $customizers;
	}
}
