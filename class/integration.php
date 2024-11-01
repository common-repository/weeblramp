<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author           weeblrPress
 * @copyright        (c) WeeblrPress - Weeblr,llc - 2020
 * @package          AMP on WordPress - weeblrAMP CE
 * @license          http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version          1.12.5.783
 * @date                2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

Class WeeblrampClass_Integration extends WeeblrampClass_Base {

	/**
	 * Unique id for the integration, normally the
	 * integrated plugin file.
	 * eg: woocommerce/woocommerce.php
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * List of filters to hook when the integrations is enabled
	 *
	 * @var array
	 */
	/*protected $filters = array(
		array(
			'filter_name' => 'someFilterName',
			'method' => 'filterCustomRewriteRules',
			'priority' => 10,
			'accepted_args' => 1
		),
	);
	*/
	protected $filters = array();

	/* List of actions to hook when the integrations is enabled
	*
	* @var array
	*/
	/*
	 protected $actions = array(
		array(
			'action_name' => 'someName',
			'method' => 'someMethod',
			'priority' => 10,
			'accepted_args' => 1
		)
	);
	*/
	protected $actions = array();

	/**
	 * Set of custom rewrite rules for paginated archives
	 *
	 * @var array
	 */
	protected $customRWRules = array();

	/**
	 * Whether the integration can be enabled
	 * ie: the corresponding plugin is installed on the site
	 * or other similar conditions. Set to the
	 * value returned by the discover() method
	 *
	 * @var bool
	 */
	protected $active = false;

	/**
	 * Whether user enabled this integration from the
	 * control panel
	 *
	 * @var bool|mixed
	 */
	protected $userEnabled = false;

	/**
	 * Constructor
	 *
	 * @param   array $options An array of options.
	 *
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );

		// did user disabled this integration in config?
		$this->userEnabled = wbArrayGet( $options, 'user_enabled', $this->userEnabled );

		// is the plugin installed/ available
		$this->active = $this->discover();

		// filter rewrite rules
		if ( $this->active ) {
			add_filter(
				'weeblramp_custom_rewrite_rules',
				array(
					$this,
					'filterCustomRewriteRules'
				),
				10
			);
		}
	}

	/**
	 * Executed at WP plugins loaded
	 */
	public function load() {
	}

	/**
	 * Executed at WP init
	 */
	public function init() {

		// register filters
		foreach ( $this->filters as $def ) {
			if ( $this->shouldHook( $def ) ) {
				$callback = $this->getCallbackFromDef( $def['method'] );
				if ( ! empty( $callback ) ) {
					add_filter(
						wbArrayGet( $def, 'filter_name' ),
						$callback,
						wbArrayGet( $def, 'priority', 10 ),
						wbArrayGet( $def, 'accepted_args', 1 )
					);
				}
			}
		}

		// register actions
		foreach ( $this->actions as $def ) {
			if ( $this->shouldHook( $def ) ) {
				$callback = $this->getCallbackFromDef( $def['method'] );
				if ( ! empty( $callback ) ) {
					add_action(
						wbArrayGet( $def, 'action_name' ),
						$callback,
						wbArrayGet( $def, 'priority', 10 ),
						wbArrayGet( $def, 'accepted_args', 1 )
					);
				}
			}
		}

		// always have the ability to remove shortcodes
		// managed by the plugin
		add_filter(
			'weeblramp_shortcodes_disable_list',
			array( $this, 'cleanShortcodes' )
		);
	}

	/**
	 * Filter a list of shortcodes handled by the plugin
	 * and that thus should be always removed
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public function cleanShortcodes( $shortcodes ) {

		return $shortcodes;
	}

	/**
	 * Filter the list of custom rewrite rules added to WP
	 * Required to handle pagination in categories
	 *
	 * @param array $rules
	 */
	public function filterCustomRewriteRules( $rules ) {

		$rules = array_merge(
			$rules,
			$this->customRWRules
		);

		return $rules;
	}

	/**
	 * Returns true if this integration is available, ie if the
	 * corresponding plugin or service is installed and activated
	 *
	 * @return bool
	 */
	protected function discover() {

		return false;
	}

	/**
	 * Builds a callback based on user definition
	 * - if a function, return as is
	 * - if an array (object -> method) return as is
	 * - if otherwise, assume this is a method on the current integration bject
	 *
	 * @param $def
	 *
	 * @return array
	 */
	protected function getCallbackFromDef( $def ) {

		if ( function_exists( $def ) ) {
			return $def;
		}

		if ( is_array( $def ) ) {
			return $def;
		}

		if ( method_exists( $this, $def ) ) {
			return array( $this, $def );
		}

		return null;
	}

	/**
	 * Shorthand for integration enabled
	 *
	 * @return bool
	 */
	protected function isEnabled() {

		return $this->userEnabled && $this->active;
	}

	/**
	 * Decides if a particular hook (filter/action) should be
	 * hooked, based on integration enabled by user,
	 * integration is active, and setting from the integration definition
	 *
	 * @param array $def
	 *
	 * @return bool
	 */
	private function shouldHook( $def ) {

		$shouldHook =
			( $this->userEnabled && $this->active )
			||
			( is_admin() && wbArrayGet( $def, 'always_in_admin', false ) );

		return $shouldHook;
	}
}
