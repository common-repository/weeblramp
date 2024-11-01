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

class WeeblrampHelper_Features {

	/**
	 * Outputs a search box, based on passed template location.
	 *
	 * @param string   $currentLocation Template location from which this is called.
	 * @param mixed    $displayData Data set to pass to the search box rendering template.
	 * @param callable $processorMethod AMP processor to use to process the HTML.
	 */
	public static function maybeOutputSearchBox(
		$currentLocation,
		$displayData,
		$processorMethod
	) {

		return '';
	}

	/**
	 * Outputs a language switcher, based on passed template location.
	 *
	 * @param string   $currentLocation Template location from which this is called.
	 * @param mixed    $displayData Data set to pass to the template switcher rendering template.
	 * @param callable $processorMethod AMP processor to use to process the HTML.
	 */
	public static function maybeOutputLanguageSwitcher(
		$currentLocation,
		$languageSwitcher,
		$processorMethod
	) {

		return '';
	}
}
