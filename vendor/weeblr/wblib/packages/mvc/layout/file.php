<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author           weeblrPress
 * @copyright        (c) WeeblrPress - Weeblr,llc - 2020
 * @package          AMP on WordPress - weeblrAMP CE
 * @license          http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version          1.12.5.783
 * @date        2020-05-19
 */

/** ensure this file is being included by a parent file */
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @since       0.2.1
 */
class WblMvcLayout_File extends WblMvcLayout_Base {

	/**
	 * @var    string  Dot separated path to the layout file, relative to base path
	 * @since  0.2.1
	 */
	protected $layoutId = '';

	/**
	 * @var    string  Base path to use when loading layout files
	 * @since  0.2.1
	 */
	protected $basePath = null;

	/**
	 * @var    string  Full path to actual layout files, after possible template override check
	 * @since  0.2.2
	 */
	private $fullPath = null;

	/**
	 * Method to instantiate the file-based layout.
	 *
	 * @param string $layoutId Dot separated path to the layout file, relative to base path
	 * @param mixed  $basePath Base path, or list of base paths to use when loading layout files
	 *
	 * @since   3.0
	 */
	public function __construct( $layoutId, $basePath = null ) {

		$this->layoutId = $layoutId;
		$this->basePath = empty( $basePath ) ? WBLIB_LAYOUTS_PATH . 'layouts' : ( is_string( $basePath ) ? rtrim( $basePath, DIRECTORY_SEPARATOR ) : $basePath );

		// user supplied base path (on front end)
		if ( ! is_admin() ) {
			// child theme is highest priority override source
			$childThemeRoot = get_option( 'weeblramp_current_stylesheet' );
			if ( ! empty( $childThemeRoot ) ) {
			$themePaths     = array( $childThemeRoot . '/weeblramp/layouts' );
			} else {
				$themePaths = array();
			}

			// then current theme
			$themeRoot = get_option( 'weeblramp_current_template' );
			if ( ! empty( $themeRoot ) ) {
				$themePaths = array_unique(
					array_merge(
						$themePaths,
						array( $themeRoot . '/weeblramp/layouts' )
					)
				);
			}

			// then add other overrides, from plugins
			/**
			 * Filter a list of supplemental directories where weeblrAMP should look for possible layouts files
			 *
			 * Provide full absolute directories path.
			 *
			 * @api
			 * @package weeblrAMP\filter\output
			 * @var weeblramp_template_dir
			 * @since   1.0.0
			 *
			 * @param array $supplementalBasePaths Array of supplemental full path
			 *
			 * @return array
			 */
			$supplementalBasePaths = apply_filters(
				'weeblramp_template_dir',
				array()
			);
			$supplementalBasePaths = $this->validateBasePaths( $supplementalBasePaths );

			// merge everything together: WP theme then weeblramp theme(s), then built in
			$this->basePath = array_merge(
				$themePaths,
				$supplementalBasePaths,
				(array) $this->basePath
			);
		}
	}

	/**
	 * Method to render the layout.
	 *
	 * @param object $__data Object which properties are used inside the layout file to build displayed output
	 *
	 * @return  string  The necessary HTML to display the layout
	 *
	 * @since   3.0
	 */
	public function render( $__data ) {

		$layoutOutput = parent::render( $__data );

		// Check possible overrides, and build the full path to layout file
		$path = $this->getPath();

		// If there exists such a layout file, include it and collect its output
		if ( ! empty( $path ) ) {
			ob_start();
			include $path;
			$layoutOutput = ob_get_contents();
			ob_end_clean();
		}

		// apply a filter for 3rd-party content customization
		if ( ! is_admin() ) {
			$filterName   = 'weeblramp_layout_' . str_replace( '.', '_', $this->layoutId );
			$layoutOutput = apply_filters( $filterName, $layoutOutput, $__data );
		}

		return $layoutOutput;
	}

	/**
	 * Finds whether there is at least one layout file
	 * for the current layout id.
	 *
	 * @return bool
	 */
	public function exists() {

		$path = $this->getPath();

		return ! empty( $path );
	}

	/**
	 * Method to finds the full real file path, checking possible overrides
	 *
	 * @return  string  The full path to the layout file
	 *
	 * @since   3.0
	 */
	protected function getPath() {

		if ( is_null( $this->fullPath ) && ! empty( $this->layoutId ) ) {
			$rawPath  = str_replace( '.', '/', $this->layoutId ) . '.php';
			$fileName = basename( $rawPath );
			$filePath = dirname( $rawPath );

			$possiblePaths = array();

			// add built-in path(s), which are fallbacks if user supplied a folder
			if ( is_string( $this->basePath ) ) {
				$possiblePaths[] = $this->basePath . '/' . $filePath;
			} else if ( is_array( $this->basePath ) ) {
				foreach ( $this->basePath as $path ) {
					if ( is_string( $path ) ) {
						$possiblePaths[] = rtrim( $path, '/\\' ) . '/' . $filePath;
					}
				}
			}

			$this->fullPath = WblFs_File::find( $possiblePaths, $fileName );
		}

		return $this->fullPath;
	}

	/**
	 * Basic validation of user-supplied base path
	 *
	 * @param array $path
	 *
	 * @return string
	 */
	protected function validateBasePaths( $paths ) {

		$validatedPaths = array();
		foreach ( $paths as $path ) {
			if (
				empty( $path )
				|| wbContains( $path, array( '..', './' ) )
				|| ! file_exists( $path )
			) {
				WblSystem_Log::error( 'weeblramp', '%s::%d %s', __METHOD__, __LINE__, 'Invalid template supplied through weeblramp_template_dir filter: ' . $path );
				continue;
			}

			// good t go, keep it
			$validatedPaths[] = $path;
		}

		return $validatedPaths;
	}
}
