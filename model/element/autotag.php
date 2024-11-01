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

class WeeblrampModelElement_Autotag extends WeeblrampClass_Base {

	private $_modified = false;

	public function autotag( $dom, $link, $attributes ) {

		// identify specific links we want to autolink to:
		if ( $this->userConfig->get( 'embed_auto_link' ) && ! empty( $attributes['href'] ) ) {
			foreach ( $this->systemConfig->get( 'embed_tags' ) as $tagName => $tagRecord ) {
				if ( ! empty( $tagRecord['url_regexp'] ) && preg_match( $tagRecord['url_regexp'], $attributes['href'], $matches ) ) {
					// replace the current link with an AMP tag
					$method = 'get' . ucfirst( $tagName ) . 'UrlData';
					$newTag = WeeblrampHelper_Tags::buildTag( $tagName, WeeblrampHelper_Tags::$method( $matches ) );

					if ( ! empty( $newTag ) ) {
						// insert new tag
						$fragment = $dom->createDocumentFragment();
						$fragment->appendXML( $newTag );
						$parent = $link->parentNode;
						$parent->insertBefore( $fragment, $link );
						$parent->removeChild( $link );

						// mark as modified, to update the DOM object
						$this->_modified = true;

						// add stylesheet, if any specified
						if ( ! empty( $tagRecord['style'] ) ) {
							WeeblrampFactory::getThe( 'WeeblrampModel_Assetscollector' )->addStyle( $tagRecord['style'] );
						}

						// we have replaced the link with an amp tag,
						// don't keep trying to do it again
						break;
					}
				}
			}
		}

		return $this->_modified;
	}
}
