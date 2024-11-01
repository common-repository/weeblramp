<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author       weeblrPress
 * @copyright    (c) WeeblrPress - Weeblr,llc - 2020
 * @package      AMP on WordPress - weeblrAMP CE
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version      1.12.5.783
 *
 * 2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Loads integrations with other plugins
 */
class WeeblrampHelper_Integrations {

	/**
	 * @var array Holds instantiated integrations object available this request.
	 */
	private static $integrations = array();

	/**
	 * Instantiate all integrations enabled by user
	 * Called during loaded event
	 */
	public static function load() {

		// load integrations
		/**
		 * Filter the list of integrations that will be loaded on any AMP page.
		 * This is a key => value array, where key is the plugin file (ie weeblramp/weeblr.php) and the value is a human-readable integration title.
		 *
		 * @api
		 * @package weeblrAMP\filter\config
		 * @var weeblramp_integrations_list
		 * @since 1.0.0
		 *
		 * @param array $integrations Current list of integrations
		 *
		 * @return array
		 */
		$integrations = apply_filters(
			'weeblramp_integrations_list',
			WeeblrampFactory::getThe( 'weeblramp.config.user' )
			                ->get( 'integrations_list' )
		);

		// load them
		foreach ( $integrations as $integrationName => $enabled ) {
			if ( $enabled ) {
				// instantiate a model, this will setup all the filters and actions
				self::$integrations[ $integrationName ] = self::buildIntegrationModel( $integrationName, $enabled );
				if ( ! empty( self::$integrations[ $integrationName ] ) ) {
					self::$integrations[ $integrationName ]->load();
				}
			}
		}
	}

	/**
	 * Returns instantiated integration object, if any
	 *
	 * @param WeeblrampClass_Integration $integrationName
	 */
	public static function get( $integrationName ) {

		return wbArrayGet( self::$integrations, $integrationName, null );
	}

	/**
	 * Trigger initialization of each integrations
	 * Called during init event
	 */
	public static function init() {

		/**
		 * Filter the list of integrations that will be loaded on any AMP page.
		 * This is a key => value array, where key is the plugin file (ie weeblramp/weeblr.php) and the value is a human-readable integration title.
		 *
		 * @api
		 * @package weeblrAMP\filter\config
		 * @var weeblramp_integrations_list
		 * @since 1.0.0
		 *
		 * @param array $integrations Current list of integrations
		 *
		 * @return array
		 */
		$integrations = apply_filters(
			'weeblramp_integrations_list',
			WeeblrampFactory::getThe( 'weeblramp.config.user' )
			                ->get( 'integrations_list' )
		);

		// init them
		foreach ( $integrations as $integrationName => $enabled ) {
			if ( $enabled && ! empty( self::$integrations[ $integrationName ] ) ) {
				self::$integrations[ $integrationName ]->init();
			}
		}
	}

	/**
	 * Instantiate a model to fetch data from a specific integration,
	 * based on the integration name, as stored in the (user) config.
	 * The integration name is actually a full plugin name
	 * (eg weeblramp/weeblramp.php) so we need to process it a bit
	 * to build a class name from that.
	 *
	 * @param string $integrationName
	 *
	 * @return bool
	 */
	private static function buildIntegrationModel( $integrationName, $enabled ) {

		$nameBits = explode( '/', $integrationName );
		$name     = empty( $nameBits[0] ) ? false : $nameBits[0];
		if ( empty( $nameBits ) ) {
			return false;
		}
		$name      = str_replace( array( '-', '_' ), '', $name );
		$modelName = 'WeeblrampIntegration_' . ucfirst( $name );
		if ( class_exists( $modelName ) ) {
			return new $modelName(
				array(
					'user_enabled' => $enabled
				)
			);
		}

		return false;
	}
}
