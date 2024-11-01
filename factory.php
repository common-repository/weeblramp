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
 * Simple plugin factory, builds a few specific objects
 */
class WeeblrampFactory extends WblFactory {

	/**
	 * Build an object, with optional arguments
	 *
	 * @param        $method
	 * @param        $class
	 * @param null   $args
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function buildObject( $method, $class, $args = null, $key = '' ) {

		switch ( $class ) {
			// gather all versions info
			case 'weeblramp.version_info':
				// enforce singleton
				if ( 'the' != $method ) {
					$this->invalidMethod( $method, $class, $args, $key );
				}

				return $this->getVersionInfo();

				break;

			// manages links to assets
			case 'weeblramp.html_manager':
				// enforce singleton
				if ( 'the' != $method ) {
					$this->invalidMethod( $method, $class, $args, $key );
				}

				return parent::buildObject(
					'a',
					'WblHtml_Manager',
					array(
						'root_url'   => plugins_url( '', WEEBLRAMP_PLUGIN_FILE ),
						'files_root' => WEEBLRAMP_PLUGIN_DIR,
						'files_path' => array( 'assets/default' => '' )
					)
				);

				break;

			// cannot request configs, must use descriptors (below)
			case 'WeeblrampConfig_System':
			case 'WeeblrampConfig_User':
			case 'WeeblrampConfig_Amp':
			case 'WeeblrampConfig_Ampform':
			case 'WeeblrampConfig_Customize':
				$this->invalidMethod( $method, $class, $args, $key );
				break;
			case 'weeblramp.config.system':
				return parent::buildObject( 'the', 'WeeblrampConfig_System' );
				break;
			case 'weeblramp.config.user':
				return parent::buildObject( 'the', 'WeeblrampConfig_User' );
				break;
			case 'weeblramp.config.customize':
				return parent::buildObject( 'the', 'WeeblrampConfig_Customize' );
			case 'weeblramp.config.amp':
				return $this->getAmpConfig();
				break;
			case 'weeblramp.config.ampform':
				return $this->getAmpFormConfig();
				break;

			case 'WeeblrampModel_Contentprotector':
				$this->invalidMethod( $method, $class, $args, $key );
				break;
			case 'weeblramp.content.protector':
				return parent::buildObject( 'the', 'WeeblrampModel_Contentprotector' );
				break;
			// default: call parent, which simply builds an object of a class
			default:
				return parent::buildObject( $method, $class, $args, $key );
				break;
		}
	}

	/**
	 * Builds the AMP definition object, optionanally allowing
	 * a remote configuration override
	 *
	 * @return WeeblrampConfig_Amp
	 */
	private function getAmpConfig() {

		// get user and system config
		$systemConfig = WeeblrampFactory::getThe( 'weeblramp.config.system' );
		$userConfig   = WeeblrampFactory::getThe( 'weeblramp.config.user' );
		// if allowed to, fetch remote AMP specification file
		if ( $userConfig->get( 'remote_configuration' ) ) {
			$ampConfig = parent::buildObject(
				'the',
				'WeeblrampConfig_Amp',
				array(
					'load_remote_url'           => $systemConfig->get( 'urls.remote_config_amp' ),
					'remote_config_caching_ttl' => $systemConfig->get( 'ttl.remote_config_amp' ),
				)
			);
		} else {
			$ampConfig = parent::buildObject(
				'the',
				'WeeblrampConfig_Amp'
			);
		}

		return $ampConfig;
	}

	/**
	 * Builds the AMP definition object, optionally allowing
	 * a remote configuration override
	 *
	 * @return WeeblrampConfig_Amp
	 */
	private function getAmpFormConfig() {

		if ( ! WeeblrampHelper_Version::isFullEdition() ) {
			return $this->getAmpConfig();
		}

		// get user and system config
		$systemConfig = WeeblrampFactory::getThe( 'weeblramp.config.system' );
		$userConfig   = WeeblrampFactory::getThe( 'weeblramp.config.user' );
		// if allowed to, fetch remote AMP specification file
		if ( $userConfig->get( 'remote_configuration' ) ) {
			$ampConfig = parent::buildObject(
				'the',
				'WeeblrampConfig_Ampform',
				array(
					'load_remote_url'           => $systemConfig->get( 'urls.remote_config_ampform' ),
					'remote_config_caching_ttl' => $systemConfig->get( 'ttl.remote_config_ampform' ),
				)
			);
		} else {
			$ampConfig = parent::buildObject(
				'the',
				'WeeblrampConfig_Ampform'
			);
		}

		return $ampConfig;
	}

	/**
	 * Builds a version information object
	 *
	 * @return WblSystem_Version
	 */
	private function getVersionInfo() {

		static $version = null;

		if ( is_null( $version ) ) {
			if ( false === strpos( '1.12.5', '_version_' ) ) {
				$versionInfo = array(
					'package'           => 'AMP on WordPress - weeblrAMP CE',
					'package_title'     => 'weeblrAMP - Accelerated Mobile Pages for Wordpress',
					'version'           => '1.12.5',
					'version_full'      => '1.12.5.783',
					'date'              => '2020-05-19',
					'license'           => 'http://www.gnu.org/copyleft/gpl.html GNU/GPL',
					'copyright'         => '(c) WeeblrPress - Weeblr,llc - 2020',
					'author'            => 'weeblrPress',
					'url'               => 'https://www.weeblrpress.com',
					'edition'           => 'community',
					'documentation_url' => 'https://www.weeblrpress.com/documentation/products.weeblramp/1/index.html',
					'php'               => array(
						'min' => '5.3',
						'max' => ''
					),
					'wp'                => array(
						'min' => '4.5',
						'max' => ''
					)
				);
			} else {
				$versionInfo = array(
					'package'           => 'weeblramp',
					'package_title'     => 'weeblrAMP',
					'version'           => '1.3.0',
					'version_full'      => '1.3.0.547',
					'date'              => '2016-08-18 13:05:06',
					'license'           => 'GPL Version 2',
					'copyright'         => '(c) WeeblrPress - Weeblr,llc - 2017',
					'author'            => 'WeeblrPress',
					'url'               => 'https://www.weeblrpress.com',
					'edition'           => 'full',
					//'edition'           => 'community',
					'documentation_url' => 'https://www.weeblrpress.com/documentation/weeblramp',
					'php'               => array(
						'min' => '5.3',
						'max' => '7.5'
					),
					'wp'                => array(
						'min' => '4.5',
						'max' => '6'
					)
				);
			}

			$version = new WblSystem_Version( $versionInfo );
		}

		return $version;
	}
}
