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
 * Class WeeblrampModel_Contentprotector
 *
 * Protects some parts of the page from the AMP converter. The content is removed from the page (and replaced with a
 * tag) then can be put back at a later stage, after the initial conversion to AMP.
 *
 * Typical usage, inside a template, before AMPlification:
 *
 * echo $contentProtector->protect( $someContent);
 *
 * Then after AMPlification has taken place (and all invalid tags have been removed, modified):
 *
 * $pageContent = $contentProtector->injectProtectedContent($pageContent);
 *
 * where $pageContent is the AMPlified content of the page.
 *
 * In addition, a specific AMP processor can be passed during the initial call, so that
 * the protected content is also AMPlified, but maybe with a different set of rules:
 *
 * echo $contentProtector->protect(
 * $someContent, array(
 *   $this->get('amp_form_processor'),
 *   'convert'
 * ));
 *
 * When injecting back each bit of protected content, it will be processed
 * by using the callback provided to protect(). The callback receives some string content
 * and must return the processed content as a string.
 *
 * Currently, we use this to allow forms in our comment or search implementation,
 * while still removing all forms and input in other parts of the page.
 */
class WeeblrampModel_Contentprotector extends WeeblrampClass_Model {

	/**
	 * Store a piece of content, and an optional processor, returning a
	 * back by the stored content.
	 *
	 * @param string   $content
	 * @param callback $processor
	 *
	 * @return string
	 */
	public function protect( $content, $processor = null ) {

		return $content;
	}

	/**
	 * Inject back one or all protected content into a page content (a string)
	 *
	 * @param string $pageContent The current page content
	 * @param string $tag A protected content tag found in content
	 *
	 * @return string
	 */
	public function injectProtectedContent( $pageContent, $tag = null ) {

		return $pageContent;
	}
}
