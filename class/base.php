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

Class WeeblrampClass_Base {

	protected $systemConfig    = null;
	protected $userConfig      = null;
	protected $ampConfig       = null;
	protected $customizeConfig = null;

	/**
	 * Constructor
	 *
	 * @param   array $options An array of options.
	 *
	 */
	public function __construct( $options = array() ) {

		// store user and system config, needed pretty much everywhere
		$this->systemConfig    = wbArrayGet( $options, 'system_config', WeeblrampFactory::getThe( 'weeblramp.config.system' ) );
		$this->userConfig      = wbArrayGet( $options, 'user_config', WeeblrampFactory::getThe( 'weeblramp.config.user' ) );
		$this->ampConfig       = wbArrayGet( $options, 'amp_config', WeeblrampFactory::getThe( 'weeblramp.config.amp' ) );
		$this->customizeConfig = wbArrayGet( $options, 'customize_config', WeeblrampFactory::getThe( 'weeblramp.config.customize' ) );
	}
}
