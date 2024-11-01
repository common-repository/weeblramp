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
 * Performs tags replacement in some CSS string
 */
class WeeblrampClass_Customize extends WeeblrampClass_Base {

	protected $tagsReplaceConfig = null;

	/**
	 * Constructor
	 *
	 * @param   array $options An array of options.
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );

		// get some default values
		$this->tagsReplaceConfig = wbArrayGet(
			$options,
			'customize_config',
			$this->customizeConfig
		);
	}

	/**
	 * Performs replacement of CSS values with user configured
	 * customized ones.
	 *
	 * @param string $rawCss
	 * @param string $preifx
	 */
	public function customize( $rawCss, $prefix = 'wb' ) {

		if ( empty( $rawCss ) ) {
			return $rawCss;
		}

		// search for customization tags in stylesheet, and replace them
		$regex = '#\'{' . $prefix . '_([^}]*)}\'#';
		$css   = preg_replace_callback( $regex, array( $this, '_replace' ), $rawCss );

		return $css;
	}

	/**
	 * preg_replace callback, replaces a tag based on user-set values
	 *
	 * @param $match
	 *
	 * @return mixed
	 */
	protected function _replace( $match ) {

		$value = $match[0];

		// set user-set value, or use default if none
		if ( ! empty( $match[1] ) ) {
			list( $name, $defaultValue ) = explode( '\\', $match[1] );
			$newValue = $this->tagsReplaceConfig->get( $name );
			$value    = empty( $newValue ) ? $defaultValue : $newValue;
		}

		return $value;
	}
}
