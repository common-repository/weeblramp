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
 * Remove elements that have CSS classes or id in a user defined list
 *
 */
class WeeblrampModelProcessor_Csscleanup extends WeeblrampClass_Model {

	/**
	 *  List of CSS classes that should cause an element to be deleted
	 *
	 * @var array
	 */
	private $forbiddenClasses = array();

	/**
	 * List of CSS ids that should cause an element to be deleted
	 * @var array
	 */
	private $forbiddenIds = array();

	/**
	 * Temp list of items to delete from the DOM
	 * @var array
	 */
	private $nodesToDelete = array();

	/**
	 * Stores rules set
	 *
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );
		$this->getForbiddenElements();
	}

	/**
	 * Strip elements with ids or class on a user-defined list
	 *
	 * @return null
	 */
	public function sanitize( & $rawContent, $dom ) {

		// bail out if no classes listed
		if ( empty( $this->forbiddenClasses ) ) {
			return $dom;
		}

		// break down content, and process each element
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		$this->cleanContent( $body );

		// delete what needs to be deleted
		$modified = count( $this->nodesToDelete ) > 0;
		foreach ( $this->nodesToDelete as $element ) {
			$this->removeElement( $element );
		}

		if ( $modified ) {
			$rawContent = WeeblrampHelper_Dom::fromDom( $dom );
		}

		return $dom;
	}

	/**
	 * Clean up user entry for a list of classes or ids
	 * store them in object variable
	 */
	private function getForbiddenElements() {

		$classes = $this->customizeConfig->get( 'cleanup_css_classes' );
		$classes = WblSystem_Strings::stringToCleanedArray( $classes, "\n" );
		foreach ( $classes as $class ) {
			$class                    = preg_replace( '/\s+/', ' ', $class );
			$this->forbiddenClasses[] = explode( ' ', $class );
		}
		$ids                = $this->customizeConfig->get( 'cleanup_css_ids' );
		$this->forbiddenIds = WblSystem_Strings::stringToCleanedArray( $ids, "\n" );
	}

	/**
	 * Clean a node and its descendants, storing nodes
	 * that need to be removed on the way
	 *
	 * @param DOMElement $node
	 */
	private function cleanContent( $node ) {

		if ( $node->nodeType !== XML_ELEMENT_NODE ) {
			return;
		}

		// does the element has a class attr?
		if ( $node->hasAttribute( 'class' ) ) {
			// if match, put on the remove list
			if ( WeeblrampHelper_Dom::matchByClass( $node, $this->forbiddenClasses ) ) {
				$this->nodesToDelete[] = $node;
			}
		}

		// does the element has an id?
		if ( $node->hasAttribute( 'id' ) ) {
			// if match, put on the remove list
			if ( $this->shouldRemoveById( $node ) ) {
				$this->nodesToDelete[] = $node;
			}
		}

		// then process children
		foreach ( $node->childNodes as $childNode ) {
			if ( $childNode->nodeName != '#text' ) {
				$this->cleanContent( $childNode );
			}
		}
	}

	/**
	 * Decides whether the element id match our list
	 *
	 * @param DOMElement $node
	 *
	 * @return bool
	 */
	private function shouldRemoveById( $node ) {

		$nodeId = $node->getAttribute( 'id' );

		// too simple: classes need to be exactly equal to the target
		$shouldRemove = in_array( $nodeId, $this->forbiddenIds );

		return $shouldRemove;
	}

	/**
	 * Remove an element from the DOM
	 *
	 * @param DOMElement $element
	 */
	private function removeElement( $element ) {

		$elementParent = $element->parentNode;
		if ( ! empty( $elementParent ) ) {
			$elementParent->removeChild( $element );
			// also remove the parent, if it just become empty
			if ( 'body' !== $elementParent->nodeName && $this->isEmptyNode( $elementParent ) ) {
				$elementParent->parentNode->removeChild( $elementParent );
			}
		}
	}

	/**
	 * Test if a DOM element is empty: content and children
	 *
	 * @param DOMElement $node
	 *
	 * @return bool
	 */
	private function isEmptyNode( $node ) {

		return 0 === $node->childNodes->length && empty( $node->textContent );
	}

}
