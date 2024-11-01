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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Replace email addresses in content
 * with an obfuscated version
 *
 */
class WeeblrampModelProcessor_Obfuscator extends WeeblrampClass_Model {

	private $testMode = false;

	/**
	 * Process raw content (html), finding email addresses
	 * and replacing them by something that's harder to
	 * harvest for crawlers.
	 *
	 * @param string $content The initial content to harvest for emails.
	 * @param bool   $testMode If true, emails are not actually obfuscated, but just wrapped with square brackets.
	 *
	 * @return bool whether the content has been modified
	 */
	public function process( &$content, $testMode = false ) {

		$this->testMode = $testMode;

		/*
		 * Check for presence of [wbamp_disable_email_protection] which explicitely disables this
		 * bot for the item.
		 */
		if ( wbContains( $content, '[wbamp_disable_email_protection]' ) ) {
			$content = StringHelper::str_ireplace(
				array(
					'[wbamp_disable_email_protection]',
					'<p>[wbamp_disable_email_protection]</p>'
				),
				'',
				$content
			);

			return true;
		}

		// quick check
		if ( ! wbContains( $content, '@' ) ) {
			return false;
		}

		// regexp to find emails
		$regex      = '#(mailto:)?[A-Z0-9-%_.+]{1,64}@(?:[A-Z0-9](?:[A-Z0-9-]{0,62}[A-Z0-9])?\.){1,8}[A-Z]{2,63}#iu';
		$newContent = preg_replace_callback( $regex, array( $this, '_obfuscateAddress' ), $content );
		$modified   = $content != $newContent;
		if ( $modified ) {
			$content = $newContent;
		}

		return $modified;
	}

	/**
	 * Process a email address match
	 *
	 * @param $match
	 *
	 * @return string
	 */
	protected function _obfuscateAddress( $match ) {

		// detect type we can handle
		$originalMatch = $match[0];
		if ( ! empty( $originalMatch ) ) {
			// Test mode wrap captured text with brackets
			// allow test suite to be sure regexp works fine
			// on edge cases
			if ( $this->testMode ) {
				return '[' . $originalMatch . ']';
			} else {
				return $this->processAddress( $originalMatch );
			}
		}

		return $originalMatch;
	}

	/**
	 * Apply encoding to email address
	 *
	 * @param $address
	 *
	 * @return mixed
	 */
	protected function processAddress( $address ) {

		// we separate the mailto: prefix as if left with the
		// full address, it may be left unencoded because
		// of poor randomness of encoding function
		if ( wbStartsWith( $address, 'mailto:' ) ) {
			$prefix  = WblSystem_Email::eae_encode_str( 'mailto:' );
			$address = StringHelper::substr( $address, 7 );
		} else {
			$prefix = '';
		}

		$processed = $prefix . WblSystem_Email::eae_encode_str( $address );

		return $processed;
	}
}
