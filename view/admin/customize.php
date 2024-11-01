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
 * Settings page for the plugin
 */
class WeeblrampViewAdmin_Customize extends WeeblrampClass_Configview {

	const SETTINGS_PAGE = 'weeblramp-customize';

	public $title     = 'Customize appearance';
	public $menuTitle = 'Customize';

	protected $configName = 'weeblramp.config.customize';

	/**
	 * WeeblrampViewAdmin_Options constructor.
	 */
	public function __construct() {

		parent::__construct();

		// hook up config checks on settings page
		add_action(
			'weeblramp_customize_config_after_render',
			array(
				WeeblrampFactory::getA( 'WeeblrampClass_Configcheck' ),
				'execute'
			)
		);
	}

}
