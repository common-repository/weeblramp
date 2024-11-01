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
 * Manage and provide access to system-defined configuration
 * Not suitable for option page rendering, all values are hardcoded
 *
 * Class WeeblrampConfig_System
 */
class WeeblrampConfig_System extends WblSystem_Config {

	/*
	 * Unique ID for this configuration object
	 */
	const CONFIG_ID = '__weeblramp_system__';

	/**
	 * @see WblSystem_Config::__construct
	 */
	public function __construct( $options = array() ) {

		// override definitions file
		$options['definition_file'] = str_replace( '.php', '.cfg.php', __FILE__ );

		// we want to load values set by user from db
		$options['load_values'] = false;

		// hardcoded values, don't load/store to db
		parent::__construct( $options );

		// system settings are the same regardless of edition.
		$this->bypassEditionCheck = true;
	}
}
