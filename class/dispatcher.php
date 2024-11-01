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
 * Dispatch request and render if appropriate
 *
 * Some parts based on Automattic AMP plugin
 */
class WeeblrampClass_Dispatcher extends WeeblrampClass_Base {

	private $router = null;

	/**
	 * Constructor
	 *
	 * @param array $options An array of options.
	 *
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );

		// store the router and taxonomy manager, needed downstream
		$this->router = WeeblrampFactory::getThe( 'WeeblrampClass_Route' );
	}

	/**
	 * Set up actions hooks, based on current request
	 * and user settings
	 */
	public function dispatch() {

		// don't handle feeds
		if ( is_feed() ) {
			return;
		}

		WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Dispatching: ' . WblWordpress_Helper::getCurrentRequestUrl() );

		// filter WP old link redirect, so that it applies also to the AMP version of a renamed page
		add_filter( 'old_slug_redirect_url', array( $this, 'filterOldSlugRedirect' ) );

		$this->filterOutInvalidLinks();

		// find out whether this is a request for the AMP version of a page
		$isAMPPage = $this->router->isAmpPage();
		WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Dispatch: ' . ( $isAMPPage ? 'is' : 'is not' ) . ' an AMP request' );

		// if not supposed to amplify this request, fail or redirect to regular HTML page
		if ( ! $this->router->shouldAmplifyPage() ) {
			WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Dispatch: should NOT amplify page' );

			// not an AMP page, just handoff control
			if ( ! $isAMPPage ) {
				$this->addRegularHtmlPageScrubbing();

				return;
			}

			// try redirect to regular HTML version, but not in standalone mode
			// as this would otherwise create an infinite loop
			if ( ! $this->router->isStandaloneMode() ) {
				$post = $this->getPost();
				if ( ! empty( $post ) ) {
					$target = get_permalink( $this->getPost()->ID );
					WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Dispatch: redirect to std HTML ' . $target );
					wp_safe_redirect(
						$target
					);
					exit;
				}

				// no post found for this request
				// stop processing page as an AMP one, let WP render a regular 404
				return;
			}

			// at this stage we know we're are in standalone mode
			// but there is no AMP version of this page
			// so we must trigger a 404 - but an AMP one,
			global $wp_query;
			$wp_query->set_404();

			// remove the redirect_canonical action, avoid auto redirect to regular HTML
			add_filter( 'old_slug_redirect_url', '__return_false' );
			add_filter( 'redirect_canonical', '__return_false' );

			WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Dispatch: triggered a 404 - standalone mode' );
		}

		if ( $isAMPPage ) {
			// check access with debug token
			$this->checkDebugAccess();

			// remove canonical redirect, as it otherwise fires before our own
			// rendering method, which has priority 200 to let other plugins
			// setup their shortcodes and stuff
			remove_action( 'template_redirect', 'redirect_canonical' );

			// rendering an AMP page, hook up our AMP rendering method to template_redirect
			add_action( 'template_redirect', array( $this, 'render' ), 200 );
		} else {
			// render regular HTML page, basically add hooks to include amphtml link and do some cleanup
			$this->renderRegularHtmlPage();
		}
	}

	/**
	 * Trigger a 404 on a few invalid URLs that might otherwise render due to
	 * WP URL rewrite rules way of using endpoints.
	 */
	protected function filterOutInvalidLinks() {

		// special case for invalid links Google might pick up from form templates
		$invalidSuffixes = array(
			'%7b%7blink%7d%7d',
			'%7b%7blink',
			'%7b%7blink/',
			'{{link}}',
			'{{link',
			'{{link/'
		);

		global $wp_query;
		$ampSuffix = $this->router->getQueryVar();
		if ( isset( $wp_query->query_vars[ $ampSuffix ] ) && in_array( strtolower( $wp_query->query_vars[ $ampSuffix ] ), $invalidSuffixes ) ) {
			// remove the redirect_canonical action, avoid auto redirect to regular HTML
			add_filter( 'old_slug_redirect_url', '__return_false' );
			add_filter( 'redirect_canonical', '__return_false' );
			unset( $wp_query->query_vars[ $ampSuffix ] );
			unset( $wp_query->query_vars['name'] );
			unset( $wp_query->query_vars['pagename'] );
			$wp_query->set_404();
		}
	}

	/**
	 * Filters old_slug_redirect_url, to try redirect a 404 to the AMP version of its new target.
	 *
	 * If a user changes a post slug, WP will create a redirect from olf to new slug. But even if that page had
	 * an AMP version, WP will  always redirect to the non-AMP, new slug, page.
	 * So if the current request is a 404, and it looks like it was AMP (had the amp suffix), and WP found
	 * a redirect target for it, we transform that redirect target to be the AMP version.
	 *
	 * NB: we cannot be sure the new target page is supposed to be AMP as well (might have restrictions on taxonomies, categories, ect).
	 * But that does not matter, because if so, when the redirect hits the site, we will decide not to amplify it
	 * and WP will display the corresponding non-amp page.
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	public function filterOldSlugRedirect( $link ) {

		if ( ! empty( $link ) ) {
			$currentUrl = WblWordpress_Helper::getCurrentRequestUrl( true );
			if ( $this->router->looksLikeAnAmpUrl( $currentUrl ) ) {
				// was probably a request for an AMP URL, and WP found
				// a possible alternative. We'll try to redirect to the
				// AMP version of that alternative first
				$updatedLink = $this->router->getAmpUrlFromCanonical( $link );
				if ( $updatedLink != $currentUrl ) {
					$link = $updatedLink;
				}
			}
		}

		return $link;
	}

	/**
	 * Instantiate an AMP page rendered model, get data from it
	 * and feed it to the AMP page view for output.
	 */
	public function render() {

		try {
			WblSystem_Log::debug( 'weeblramp', '%s::%d: %s', __METHOD__, __LINE__, ' - Dispatch: starting to render AMP page' );

			// prepare model and view, required for rendering
			$model = WeeblrampFactory::getA( 'WeeblrampModel_Renderer' );
			$view  = WeeblrampFactory::getA( 'WeeblrampViewAmp' );

			// disable external resources known to cause issues on AMP pages
			// NewRelic, PageSpeed
			WeeblrampHelper_Environment::handleSpecificEnvironment( $view );

			// register AMP-only widgets
			WeeblrampHelper_Widget::registerAmpWidgets();

			// now collect data and display the page content
			$view
				->setLayouts(
					array(
						'body' => $this->getBodyLayout(),
						'page' => $this->getPageLayout()
					)
				)
				->setDisplayData(
					$model->getData()
				)
				->setHeader(
					$this->getHeaders()
				)
				->outputHeaders()
				->render();

			// then terminate processing
			exit();
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
	 * API proxy to find out if this is an AMP request
	 *
	 * @return bool
	 */
	public function isAMPRequest() {

		return $this->router->isAmpPage();
	}

	/**
	 * API proxy to find out if we are on a standalone AMP site
	 *
	 * @return bool
	 */
	public function isStandaloneMode() {

		return $this->router->isStandaloneMode();
	}

	/**
	 * API proxy to the router, to find out the canonical page
	 * of the current AMP page
	 *
	 * @return string
	 */
	public function getCanonicalUrl() {

		return $this->router->getCanonicalFromAmpUrl(
			$this->router->getAmpPagePermalink()
		);
	}

	/**
	 * API proxy to the router, to find out the current page
	 * AMP version URL
	 *
	 * @return string
	 */
	public function getAMPUrl() {

		return $this->router->getAmpPagePermalink();
	}

	/**
	 * Returns the current post object for the request
	 *
	 * @return WP_Term
	 */
	protected function getPost() {

		global $wp_query;

		return $wp_query->post;
	}

	/**
	 * In development mode, access to AMP page is normally restricted by a token
	 * unless disabled by the user
	 */
	private function checkDebugAccess() {

		if ( WeeblrampHelper_Route::shouldBlockAmpRequest() ) {
			// trigger a 403
			WblSystem_Http::abort( WblSystem_Http::RETURN_FORBIDDEN, __( 'weeblrAMP: missing debug token in URL.', 'weeblramp' ) );
		}
	}

	/**
	 * Attach some hooks to include required AMP meta data in standard HTML page
	 * and perform some clean up on shortcodes and such
	 */
	private function renderRegularHtmlPage() {

		// not an amp page, update the regular HTML page with
		// info about AMP version, except if in development mode
		if ( WeeblrampConfig_User::OP_MODE_DEV != $this->userConfig->get( 'op_mode' ) ) {
			// create a suitable view object
			$regularHtmlView = WeeblrampFactory::getA( 'WeeblrampClass_View' )
			                                   ->setLayouts(
				                                   array(
					                                   'default' => 'weeblramp.frontend.regular_html.head'
				                                   )
			                                   )
			                                   ->setDisplayData(
				                                   array(
					                                   'user_config' => $this->userConfig,
					                                   'amp_url'     => $this->router->getAmpPagePermalink()
				                                   )
			                                   );
			// hook it to the head rendering
			add_action(
				'wp_head',
				array( $regularHtmlView, 'render' )
			);
		}

		$this->addRegularHtmlPageScrubbing();
	}

	private function addRegularHtmlPageScrubbing() {

		// remove weeblrAMP-specific tags and shortcodes from regular pages
		add_filter(
			'the_content',
			array(
				'WeeblrampHelper_Content',
				'scrubRegularHtmlPage'
			)
		);

		return $this;
	}

	/**
	 * Get the main template descriptor for rendering the page body
	 *
	 * @return string
	 */
	private function getBodyLayout() {

		/**
		 * Filter the descriptor of the layout file used to render the page body.
		 *
		 * Use a dot notation. Default to weeblramp.frontend.amp.body
		 *
		 * @api
		 * @package weeblrAMP\filter\layout
		 * @var weeblramp_layouts_page_body
		 * @since   1.0.0
		 *
		 * @param string $bodyLayout The dot separated descriptor of the layout file
		 *
		 * @return string
		 */
		$bodyLayout = apply_filters(
			'weeblramp_layouts_page_body',
			'weeblramp.frontend.amp.body'
		);

		return $bodyLayout;
	}

	/**
	 * Get the main template descriptor for rendering the page
	 *
	 * @return string
	 */
	private function getPageLayout() {

		/**
		 * Filter the descriptor of the layout file used to render the page.
		 *
		 * Use a dot notation. Default to weeblramp.frontend.amp.template
		 *
		 * @api
		 * @package weeblrAMP\filter\layout
		 * @var weeblramp_layouts_page_main
		 * @since   1.0.0
		 *
		 * @param string $mainLayout The dot separated descriptor of the layout file
		 *
		 * @return string
		 */
		$mainLayout = apply_filters(
			'weeblramp_layouts_page_main',
			'weeblramp.frontend.amp.template'
		);

		return $mainLayout;
	}

	/**
	 * Builds a list of HTTP headers for the page
	 * to be later rendered by the view
	 *
	 * @return array
	 */
	private function getHeaders() {

		// mark page as an AMP one
		$headers = array(
			'X-amphtml' => 'weeblrAMP'
		);

		// optionally set HTTP status code to error
		if ( $this->router->isErrorPage() ) {
			$headers['status'] = 404;
		}

		return $headers;
	}
}
