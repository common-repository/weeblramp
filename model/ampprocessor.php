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

class WeeblrampModel_Ampprocessor extends WeeblrampClass_Model {

	private $_args = array();

	private $_scripts = array();

	private $_processorsFiles = array(
		// vendor WP
		'/vendor/wp/includes/sanitizers/class-amp-audio-sanitizer.php',
		'/vendor/wp/includes/sanitizers/class-amp-base-sanitizer.php',
		'/vendor/wp/includes/sanitizers/class-amp-iframe-sanitizer.php',
		'/vendor/wp/includes/sanitizers/class-amp-img-sanitizer.php',
		'/vendor/wp/includes/sanitizers/class-amp-video-sanitizer.php',
		'/vendor/wp/includes/utils/class-amp-dom-utils.php',
		'/vendor/wp/includes/utils/class-amp-html-utils.php',
	);

	private $_sanitizers = array();

	/**
	 * Adds used objects as properties
	 *
	 * WeeblrampModel_Ampprocessor constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );

		$maxWidth          = (int) $this->customizeConfig->get( 'content_max_width' );
		$params            = empty( $maxWidth ) ? array() : array( 'content_max_width' => $maxWidth );
		$this->_sanitizers = array(
			'AMP_Img_Sanitizer'    => $params,
			'AMP_Video_Sanitizer'  => $params,
			'AMP_Audio_Sanitizer'  => $params,
			'AMP_Iframe_Sanitizer' => array_merge(
				$params,
				array(
					'add_placeholder' => true,
				)
			)
		);

		$this->_assetsCollector = WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' );
	}

	/**
	 * Convert an HTML fragment to AMP specs
	 *
	 * @param string $rawContent
	 *
	 * @return mixed|string
	 */
	public function convert( $rawContent ) {

		if ( empty( $rawContent ) ) {
			return $rawContent;
		}

		$content = $rawContent;

		// filter out unwanted stuff, with reg exp and tags
		// warning: rawContent can be modified
		$content = $this->_cleanContent( $content );

		// load multiples sanitizer files
		$this->_loadProcessors();

		// instantiate the cleaners, injecting cleaning configuration
		$options          = array(
			'router'        => $this->router,
			'user_config'   => $this->userConfig,
			'system_config' => $this->systemConfig,
			'amp_config'    => $this->ampConfig
		);
		$cssCleaner       = new WeeblrampModelProcessor_Csscleanup( $options );
		$whiteListCleaner = new WeeblrampModelProcessor_Whitelist( $options );

		/** process each post */
		// create a DOM object
		$dom = WeeblrampHelper_Dom::fromContent( $content );

		// remove content from DOM, based on CSS classes rules
		// warning: rawContent can be modified
		$dom = $cssCleaner->sanitize( $content, $dom );

		// convert to AMP tags, mostly through black list
		$dom = $this->_convertToAmp( $dom );

		// final cleanup is whitelist-based
		$dom = $whiteListCleaner->sanitize( $dom );

		$convertedContent = WeeblrampHelper_Dom::fromDom( $dom );

		return $convertedContent;
	}

	/**
	 * Apply final content filters on content
	 *
	 * Note: this must be applied late in the process
	 * on raw text, as processing through DOM object
	 * would revert the encoding to regular UTF-8 chars
	 *
	 * @param string | array $rawContent
	 *
	 * @return mixed
	 */
	public function applyCleanupFilters( $rawContent ) {

		if ( empty( $rawContent ) ) {
			return $rawContent;
		}

		// store whether input is an array or a string,
		// then turn into an array anyway
		$isArray    = is_array( $rawContent );
		$rawContent = $isArray ? $rawContent : (array) $rawContent;

		$processedContent = array();
		foreach ( $rawContent as $key => $contentRecord ) {
			$content = is_array( $contentRecord ) ? $contentRecord['content'] : $contentRecord;

			// remove or replace content with user supplied regular expressions
			$content = $this->_processRegExp( $content );

			// email obfuscation
			if ( $this->userConfig->get( 'email_protection' ) ) {
				$obfuscator = new WeeblrampModelProcessor_Obfuscator();
				$obfuscator->process( $content );
			}

			// restore whether processing a string or a post record
			if ( is_array( $contentRecord ) ) {
				$contentRecord['content'] = $content;
			} else {
				$contentRecord = $content;
			}

			$processedContent[ $key ] = $contentRecord;
		}

		// restore initial form of the content, string or array
		$processedContent = $isArray ? $processedContent : $processedContent[0];

		return $processedContent;
	}

	/**
	 * Apply global postprocessing to the full page
	 * already rendered, ready to be displayed
	 *
	 * @param $rawPage
	 */
	public function amplifyLinks( $rawContent ) {

		if ( empty( $rawContent ) ) {
			return $rawContent;
		}

		// store whether input is an array or a string,
		// then turn into an array anyway
		$isArray    = is_array( $rawContent );
		$rawContent = $isArray ? $rawContent : (array) $rawContent;

		$processedContent = array();
		foreach ( $rawContent as $key => $contentRecord ) {
			$content = is_array( $contentRecord ) ? $contentRecord['content'] : $contentRecord;

			// create a DOM object
			$dom = WeeblrampHelper_Dom::fromContent( $content );

			// search for links marked as wbamp-link
			$modified   = false;
			$links      = $dom->getElementsByTagName( 'a' );
			$linksCount = $links->length;
			if ( empty( $linksCount ) ) {
				$processedContent[ $key ] = $contentRecord;
				continue;
			}

			for ( $i = $linksCount - 1; $i >= 0; $i -- ) {
				$link       = $links->item( $i );
				$attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $link );

				if ( ! array_key_exists( 'href', $attributes ) ) {
					continue;
				}

				$shouldAmplify = false;

				// link was marked by user
				if ( ! $shouldAmplify && array_key_exists( 'class', $attributes ) ) {
					$shouldAmplify = $this->shouldAmplyByClass( $link );
				}

				// process if we should
				if ( $shouldAmplify ) {
					// replace that link
					$href = $link->getAttribute( 'href' );
					$href = $this->router->getAmpUrlFromCanonical( $href );
					$link->setAttribute( 'href', $href );
					$modified = true;
				}

				// identify specific links we want to autolink, or rather auto tag:
				if ( WeeblrampHelper_Version::isFullEdition() ) {
					$autoTagger = new WeeblrampModelElement_Autotag();
					$autoTagged = $autoTagger->autotag( $dom, $link, $attributes );
					if ( $autoTagged ) {
						$modified = true;
					}
				}
			}

			if ( $modified ) {
				// rebuild page from DOM
				$content = WeeblrampHelper_Dom::fromDom( $dom );
			}

			if ( is_array( $contentRecord ) ) {
				$contentRecord['content'] = $content;
			} else {
				$contentRecord = $content;
			}

			$processedContent[ $key ] = $contentRecord;
		}

		// restore initial form of the content, string or array
		$processedContent = $isArray ? $processedContent : $processedContent[0];

		return $processedContent;
	}

	/**
	 * Finds out if an element classes match the user set list of CSS classes to AMPlify.
	 *
	 * @param DOMElement $link
	 *
	 * @return bool
	 */
	protected function shouldAmplyByClass( $link ) {

		static $classesToAmplify = null;

		if ( is_null( $classesToAmplify ) ) {
			$classes          = $this->userConfig->get( 'link_auto_amp_by_class' );
			$classes          = WblSystem_Strings::stringToCleanedArray( $classes, "\n" );
			$classesToAmplify = array();
			foreach ( $classes as $class ) {
				$class              = preg_replace( '/\s+/', ' ', $class );
				$classesToAmplify[] = explode( ' ', $class );
			}
		}

		$shouldAmplify = WeeblrampHelper_Dom::matchByClass( $link, $classesToAmplify );

		return $shouldAmplify;
	}

	/**
	 * Collect all scripts added by various plugins
	 *
	 * @return array
	 */
	public function getScripts() {

		return $this->_scripts;
	}

	/**
	 * Remove/replace any Wordpress-specific tags or content
	 * that could break AMP
	 * Also remove user-marked content, using
	 * [wbamp hide_on_amp start] and [wbamp hide_on_amp end] tags
	 *
	 * @param string $rawContent
	 *
	 * @return string
	 */
	private function _cleanContent( $rawContent ) {

		if ( empty( $rawContent ) ) {
			return $rawContent;
		}
		$content = $rawContent;

		// remove content marked for deletion by content creator
		$regExp  = '#\[\s*wbamp-hide\s*start\s*\].*\[\s*wbamp-hide\s*end\s*\]#iuUs';
		$content = preg_replace( $regExp, '', $content, - 1 );

		// remove tags around content that should only be displayed on AMP pages
		$regExp  = '#\[\s*wbamp-show\s*start\s*\]#iuUs';
		$content = preg_replace( $regExp, '', $content, - 1 );

		$regExp  = '#\[\s*wbamp-show\s*end\s*\]#iuUs';
		$content = preg_replace( $regExp, '', $content, - 1 );

		// remove all embed tags
		$regExp  = '#\[\s*wbamp-embed[^\]]*\]#iuUs';
		$content = preg_replace( $regExp, '', $content, - 1 );

		// remove additional tags
		$regExp  = '#\[\s*wbamp-no-ads[^\]]*\]#iuUs';
		$content = preg_replace( $regExp, '', $content, - 1 );
		$regExp  = '#\[\s*wbamp-no-widgets[^\]]*\]#iuUs';
		$content = preg_replace( $regExp, '', $content, - 1 );

		return $content;
	}

	/**
	 * Apply user-defined regular expressions replacements on
	 * on the AMP content.
	 *
	 * @param string $rawContent
	 *
	 * @return string
	 */
	private function _processRegExp( $rawContent ) {

		$content    = $rawContent;
		$regExpsRaw = $this->customizeConfig->get( 'cleanup_regexp' );

		if ( empty( $regExpsRaw ) ) {
			return $content;
		}

		$regExps = explode( "\n", $regExpsRaw );
		if ( empty( $regExps ) || StringHelper::trim( $regExps[0] ) == '-' ) {
			return $content;
		}

		foreach ( $regExps as $regExpRecord ) {
			$record = StringHelper::trim( $regExpRecord );
			if ( empty( $record ) ) {
				continue;
			}
			if ( StringHelper::strpos( $record, ';' ) === 0 ) {
				continue;
			}
			$regExpParts = explode( '=>', $record );
			if ( count( $regExpParts ) != 2 ) {
				WblSystem_Log::error( 'weeblramp', 'Invalid regular expression when cleaning page (' . $regExpRecord . '). Please verify the value of Clean-up expressions in wbAMP parameters.' );

				return $content;
			}

			$regExp      = StringHelper::trim( $regExpParts[0] );
			$replacement = StringHelper::trim( $regExpParts[1], ' ' );
			$replacement = StringHelper::trim( $replacement, '"' );
			$content     = preg_replace( $regExp, $replacement, $content, - 1, $replaceCount );
		}

		return $content;
	}

	/**
	 * Remove/replace html tags to comply with AMP specs
	 *
	 * @param $rawContent
	 *
	 * @return string
	 */
	private function _convertToAmp( $dom ) {

		foreach ( $this->_sanitizers as $sanitizer_class => $args ) {
			$sanitizer = new $sanitizer_class( $dom, array_merge( $this->_args, $args ) );

			if ( ! is_subclass_of( $sanitizer, 'AMP_Base_Sanitizer' ) ) {
				WblSystem_Log::error( 'weeblramp', 'Unable to load AMP building classes.' );
				continue;
			}

			$sanitizer->sanitize();
			$this->_assetsCollector->addScripts(
				$sanitizer->get_scripts()
			);
		}

		return $dom;
	}

	/**
	 * Loads WP amp plugin classes, with a small set of ompatibility functions
	 */
	private function _loadProcessors() {

		// Override/replacement for image extraction class
		include_once WEEBLRAMP_PLUGIN_DIR . '/helper/wp_overrides.php';

		// actual files to include

		foreach ( $this->_processorsFiles as $file ) {
			include_once WEEBLRAMP_PLUGIN_DIR . $file;
		}
	}
}
