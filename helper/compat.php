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
 * Performs checks related to version of things
 */
class WeeblrampHelper_Compat {

	/**
	 * List of plugins preventing Weeblramp to be activated
	 *
	 * @var array
	 */
	public static $incompatiblePlugins = array(

		// AMP related
		'accelerated-mobile-pages/accelerated-mobile-pages.php' => 'Accellerated Mobile Pages plugin',
		'amp/amp.php'                                           => 'WordPress AMP plugin',
		'amp-supremacy/amp-supremacy.php'                       => 'AMP supremacy plugin',
		'custom-amp-accelerated-mobile-pages/custom-amp.php'    => "Custom AMP plugin",
		'glue-for-yoast-seo-amp/yoastseo-amp.php'               => 'Glue for Yoast SEO & AMP plugin',
		'pagefrog/pagefrog.php'                                 => 'PageFrog AMP/FBIA plugin',
		'amp-woocommerce/amp-woocommerce.php'                   => 'WooComerce AMP plugin',
	);

	/**
	 * Those are not incompatible per se, but can't be used,
	 * so don't let the use choose to disable them, just disable them
	 *
	 * @var array
	 */
	public static $pluginsToAlwaysRemove = array(
		'hello.php',
		'akismet/akismet.php',
		'disqus-comment-system/disqus.php',
		'fakerpress/fakerpress.php',
	);

	public static function checkIncompatiblePlugins() {

		try {
			// prepare data
			$errors                  = array();
			$activePlugins           = get_option( 'active_plugins' );
			$incompatiblePluginsList = array_keys(
			/**
			 * Filter a list of plugins that will be prevented from running on any AMP page.
			 * Receives a key => value array, where key is the plugin file (ie: weeblramp/weeblramp.php) and the value is a human-readable title for the plugin. Plugins on that list will be blocked by weeblrAMP on AMP pages.
			 *
			 * @api
			 * @package weeblrAMP\filter\config
			 * @var weeblramp_prevent_activation_plugins
			 * @since   1.0.0
			 *
			 * @param array $incompatiblePlugins List of plugins to block on AMP pages.
			 *
			 * @return array
			 *
			 */
				apply_filters(
					'weeblramp_prevent_activation_plugins',
					self::$incompatiblePlugins
				)
			);

			// compute list of activated incompatible plugins
			$incompatiblePlugins = array_intersect( $incompatiblePluginsList, $activePlugins );
			if ( ! empty( $incompatiblePlugins ) ) {
				$msg = __( '<p>The following plugin(s) are already active, but not compatible with %s:</p>%s<p>Please disable them before trying to activate %s again.</p>', 'weeblramp' );
				foreach ( $incompatiblePlugins as $incompatiblePlugin ) {
					$errors[] = self::$incompatiblePlugins[ $incompatiblePlugin ];
				}
				$errors = array(
					sprintf(
						$msg,
						WEEBLRAMP_PLUGIN_NAME,
						WblHtml_Helper::makeList( $errors ),
						WEEBLRAMP_PLUGIN_NAME
					)
				);
			}
		}
		catch ( Exception $e ) {
			// die and inform user
			$errors[] = $e->getMessage();
		}

		return $errors;
	}

}
