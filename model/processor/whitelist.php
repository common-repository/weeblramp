<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author           weeblrPress
 * @copyright        (c) WeeblrPress - Weeblr,llc - 2020
 * @package          AMP on WordPress - weeblrAMP CE
 * @license          http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version          1.12.5.783
 *
 * 2020-05-19
 */

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

/**
 * Screen tags and some attributes in a page based on white lists
 * (and a couple of small blacklists)
 *
 */
class WeeblrampModelProcessor_Whitelist extends WeeblrampClass_Model {

	/**
	 * Temp list of items to delete from the DOM
	 * @var array
	 */
	private $nodesToDelete = array();

	/**
	 * Strip tags and attributes not on white lis
	 * @return null
	 */
	public function sanitize( $dom ) {

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		$this->cleanContent( $body );
		foreach ( $this->nodesToDelete as $element ) {
			$this->removeElement( $element );
		}

		return $dom;
	}

	/**
	 * Clean a node and its descendant, storing nodes
	 * that need to be removed on the way
	 *
	 * @param $node
	 */
	private function cleanContent( $node ) {

		if ( $node->nodeType !== XML_ELEMENT_NODE ) {
			return;
		}

		// store element name
		$nodeName = $node->nodeName;

		// if not a whitelisted element, discard, else dig further
		// allow empty tags white list, for testing
		$tagsWhiteList = $this->ampConfig->get( 'tags_white_list' );
		if (
			empty( $tagsWhiteList )

			|| substr( $nodeName, 0, 4 ) == 'amp-' // an AMP node, we don't list them on the white list

			|| in_array( $nodeName, $this->ampConfig->get( 'tags_white_list' ) )
		) {
			if ( substr( $nodeName, 0, 4 ) != 'amp-' ) {
				// check if tag has all mandatory attr
				$removed = $this->checkTagMandatoryParent( $node );
				if ( $removed ) {
					// we can cut through all further processing
					// tag was (will be) removed
					return;
				}

				// if we have some attributes white list for this element, apply them
				if ( $node->hasAttributes() ) {
					$this->cleanAttributes( $node );
				}

				// check if tag has all mandatory attr
				// NB: must be done after cleanAttributes, as
				// cleanAttributes can remove some attributes, making
				// the whole tag invalid
				$removed = $this->checkTagMandatoryAttributes( $node );
				if ( $removed ) {
					// we can cut through all further processing
					// tag was (will be) removed
					return;
				}
			} else {
				// special cases handling for amp-*
				// amp-* are supposed to be valid, so we don't normally check much
				// on it, but some output of the WP AMP plugin can still be wrong
				switch ( $nodeName ) {
					case 'amp-iframe':
						if ( $node->hasAttributes() ) {
							$length = $node->attributes->length;
							for ( $i = $length - 1; $i >= 0; $i -- ) {
								$attribute     = $node->attributes->item( $i );
								$attributeName = strtolower( $attribute->name );
								if ( 'src' == $attributeName && ! $this->cleanProtocol( $node, $attribute, $nodeName . '.' . $attributeName ) ) {
									$this->nodesToDelete[] = $node;
									break;
								}
							}
						}
						break;
					case 'amp-carousel':
						if ( $node->hasAttributes() ) {
							$length = $node->attributes->length;
							for ( $i = $length - 1; $i >= 0; $i -- ) {
								$attribute     = $node->attributes->item( $i );
								$attributeName = strtolower( $attribute->name );
								switch ( $attributeName ) {
									case 'autoplay':
										// must be empty or integer
										$normalizedValue = (int) $attribute->value;
										$normalizedValue = empty( $normalizedValue ) ? '' : $normalizedValue;
										$node->setAttribute( $attributeName, $normalizedValue );
										break;
									case 'loop':
									case 'controls':
										// cannot have any value
										$node->setAttribute( $attributeName, '' );
										break;
								}
							}
						}
						break;
				}
			}

			// then process children
			foreach ( $node->childNodes as $childNode ) {
				if ( $childNode->nodeName != '#text' ) {
					$this->cleanContent( $childNode );
				}
			}

		} else {
			// tags not on white list, remove
			$this->nodesToDelete[] = $node;
		}
	}

	/**
	 * Verify is a tag complies with parent rules:
	 * whether its direct parent is on the allowed list
	 * or is NOT on the disallowed list
	 *
	 * @param $tag
	 *
	 * @return bool true when tag is set to be removed
	 * @throws Exception
	 */
	private function checkTagMandatoryParent( $tag ) {

		$willBeRemoved = false;
		if ( array_key_exists( $tag->nodeName, $this->ampConfig->get( 'tag_mandatory_parents' ) ) ) {
			$elementParent = $tag->parentNode;
			if ( ! empty( $elementParent ) ) {
				$mandatoryParents = wbArrayGet( $this->ampConfig->get( 'tag_mandatory_parents' ), $tag->nodeName );
				$willBeRemoved    =
					// parent is on disallowed list
					in_array( $elementParent->nodeName, $mandatoryParents['forbidden_parents'] )
					// or we have an allowed list, and parent is not on it
					||
					(
						! empty( $mandatoryParents['mandatory_parents'] )
						&&
						! in_array( $elementParent->nodeName, $mandatoryParents['mandatory_parents'] )
					);
				if ( $willBeRemoved ) {
					$this->nodesToDelete[] = $tag;
				}
			}
		}

		return $willBeRemoved;
	}

	/**
	 * Check if a tag has all mandatory attributes
	 *
	 * @param $tag
	 *
	 * @return bool true if the tag was removed, to allow cutting down further processing
	 */
	private function checkTagMandatoryAttributes( $tag ) {

		$willBeRemoved = false;
		if ( array_key_exists( $tag->nodeName, $this->ampConfig->get( 'tag_mandatory_attr' ) ) ) {
			$mandatoryAttributes = wbArrayGet( $this->ampConfig->get( 'tag_mandatory_attr' ), $tag->nodeName );
			foreach ( $mandatoryAttributes as $attrName => $rule ) {
				if ( ! $tag->hasAttribute( $attrName ) ) {
					// missing attribute
					switch ( $rule['action'] ) {
						case 'add':
							$tag->setAttribute( $attrName, $rule['add_value'] );
							break;
						case 'remove_tag':
							$this->nodesToDelete[] = $tag;
							$willBeRemoved         = true;
							break;
						default:
							throw new Exception( 'Internal error: invalid AttrMandatory rule action ' . $attrName . ' for tag ' . $tag->nodeName );
							break;
					}
				}
			}
		}

		return $willBeRemoved;
	}

	/**
	 * Clean a node attributes, according to white list
	 * and a few special cases, currently hardcoded
	 *
	 * @param $tag
	 */
	private function cleanAttributes( $tag ) {

		$tagName = $tag->nodeName;
		$length  = $tag->attributes->length;

		// let any amp tags go through
		if ( substr( $tagName, 0, 4 ) == 'amp-' ) {
			$tagHasWhiteList = false;
		} else {
			$fullPerTagAttrWhiteList = $this->ampConfig->get( 'per_tag_attr_white_list' );
			if ( array_key_exists( $tagName, $fullPerTagAttrWhiteList ) ) {
				$perTagAttrWhiteList = $fullPerTagAttrWhiteList[ $tagName ];
			} else {
				$perTagAttrWhiteList = $this->ampConfig->get( 'per_tag_attr_default_white_list' );
			}
			$tagHasWhiteList = true;
		}

		$tagHasBlackList = array_key_exists( $tagName, $this->ampConfig->get( 'per_tag_attr_black_list' ) );

		// review all attributes of this tag
		for ( $i = $length - 1; $i >= 0; $i -- ) {
			$attribute     = $tag->attributes->item( $i );
			$attributeName = strtolower( $attribute->name );

			// some attr are forbidden entirely, for any tag
			if ( in_array( $attributeName, $this->ampConfig->get( 'invalid_attributes' ) ) ) {
				$tag->removeAttribute( $attributeName );
				continue;
			}

			// some attr are only forbidden on some tags
			$fullPerTagAttrBlackList = $this->ampConfig->get( 'per_tag_attr_black_list' );
			if ( $tagHasBlackList && in_array( $attributeName, $fullPerTagAttrBlackList[ $tagName ] ) ) {
				$tag->removeAttribute( $attributeName );
				continue;
			}

			// if an attribute whitelist is available for this tag
			if ( $tagHasWhiteList ) {
				// if we accept any attribute, move on
				if ( in_array( '__wbamp_any__', $perTagAttrWhiteList ) ) {
					$this->cleanAttribute( $tag, $tagName, $attribute, $attributeName );
					continue;
				}

				// if this attribute is a global HTML attribute, and this tag accept that, clean the attribute content and move to next
				if ( in_array( '__wbamp_global__', $perTagAttrWhiteList ) && in_array( $attributeName, $this->ampConfig->get( 'global_attributes' ) ) ) {
					$this->cleanAttribute( $tag, $tagName, $attribute, $attributeName );
					continue;
				}

				// if this attribute is a data attribute, and this tag accept that, clean the attribute content and move to next
				if ( in_array( '__wbamp_data__', $perTagAttrWhiteList ) && substr( $attributeName, 0, 5 ) == 'data-' ) {
					$this->cleanAttribute( $tag, $tagName, $attribute, $attributeName );
					continue;
				}

				// if this attribute is an aria attribute, and this tag accept that, clean the attribute content and move to next
				if ( in_array( '__wbamp_aria__', $perTagAttrWhiteList ) && substr( $attributeName, 0, 5 ) == 'aria-' ) {
					$this->cleanAttribute( $tag, $tagName, $attribute, $attributeName );
					continue;
				}

				// if this attribute is on a specific list of attributes accepted by this tag, clean the attribute content and move to next
				if ( ! empty( $perTagAttrWhiteList ) && in_array( $attributeName, $perTagAttrWhiteList ) ) {
					$this->cleanAttribute( $tag, $tagName, $attribute, $attributeName );
					continue;
				}

				// we had a white list of attributes for this tag, but this attribute was not on it, remove it
				$tag->removeAttribute( $attributeName );

				continue;
			}

			// not on any white or black list, keep it but clean it first
			$this->cleanAttribute( $tag, $tagName, $attribute, $attributeName );
		}
	}

	/**
	 * Clean up an attribute of a given html tag
	 *
	 * @param DOMElement $tag
	 * @param String     $tagName
	 * @param DOMAttr    $attribute
	 * @param String     $attributeName
	 */
	private function cleanAttribute( $tag, $tagName, $attribute, $attributeName ) {

		if ( $attributeName == 'rel'
		     && ! in_array( strtolower( $attribute->value ), $this->ampConfig->get( 'rel_white_list' ) )
		     && ! in_array( $attribute->value, $this->ampConfig->get( 'rel_white_list' ) )
		) {
			$tag->removeAttribute( $attributeName );

			return;
		}

		// special cases, hardcoded

		// remove event listeners
		if ( substr( $attributeName, 0, 2 ) == 'on' && $attributeName != 'on' ) {
			$tag->removeAttribute( $attributeName );

			return;
		}

		// rules on attributes content
		$descriptor = $tagName . '.' . $attributeName;

		// protocol in href attr
		if ( ! $this->cleanProtocol( $tag, $attribute, $descriptor ) ) {
			return;
		}

		// forced value
		$this->cleanAttrForcedValues( $tag, $attribute, $descriptor );

		// mandatory value
		if ( ! $this->cleanAttrMandatoryValues( $tag, $attribute, $descriptor ) ) {
			return;
		}

		// forbidden values
		if ( ! $this->cleanAttrForbiddenValue( $tag, $attribute, $descriptor ) ) {
			return;
		}
	}

	/**
	 * Check attributes such href or src for the presence of invalid protocols
	 *
	 * @param $tag
	 * @param $attribute
	 * @param $attributeName
	 * @param $descriptor
	 *
	 * @return bool
	 */
	private function cleanProtocol( $tag, $attribute, $descriptor ) {

		$protocolsDef = $this->ampConfig->get( 'protocols_def' );

		// protocol relative link, or relative to host
		if ( wbStartsWith( $attribute->value, array( '//', '/' ) ) ) {
			return true;
		}

		// does it have a protocol?
		if ( wbContains( $attribute->value, ':' ) ) {
			// do we have restrictions for this tag/attribute pair?
			if ( array_key_exists( $descriptor, $protocolsDef ) ) {
				$protocol = strtok( $attribute->value, ':' );

				// white list
				$allowed = empty( $protocolsDef[ $descriptor ] ) ? array() : $protocolsDef[ $descriptor ]['allowed'];
				if ( empty( $allowed ) || ! in_array( $protocol, $allowed ) ) {
					$tag->removeAttribute( $attribute->name );

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * if present, this attribute must have a specific value, but we can't enforce it
	 * so instead we remove the invalid attribute
	 *
	 * @param $tag
	 * @param $attribute
	 * @param $attributeName
	 * @param $descriptor
	 *
	 * @return bool
	 */
	private function cleanAttrMandatoryValues( $tag, $attribute, $descriptor ) {

		$attrMandatoryValues = $this->ampConfig->get( 'attr_mandatory_value' );
		if ( array_key_exists( $descriptor, $attrMandatoryValues ) ) {
			// if present, this attribute must have a specific value, but we can't enforce it
			// so instead we remove the invalid attribute
			if ( ! empty( $attrMandatoryValues[ $descriptor ]['processed_values'] ) && array_key_exists( $attribute->value, $attrMandatoryValues[ $descriptor ]['processed_values'] ) ) {
				switch ( $attrMandatoryValues[ $descriptor ]['processed_values'][ $attribute->value ]['action'] ) {
					case 'allow':
						break;
					case 'replace':
						$tag->setAttribute( $attribute->name, $attrMandatoryValues[ $descriptor ]['processed_values'][ $attribute->value ]['replace_with'] );
						break;
					case 'remove_attr':
						$tag->removeAttribute( $attribute->name );
						break;
					case 'remove_tag':
						$this->nodesToDelete[] = $tag;
						break;
					default:
						throw new Exception( 'Internal error: invalid AttrMandatory rule action ' . $attrMandatoryValues[ $descriptor ]['processed_values'][ $attribute->value ]['action'] );
						break;
				}
			} else if ( ! empty( $attrMandatoryValues[ $descriptor ]['empty'] ) && empty( $attribute->value ) ) {
				switch ( $attrMandatoryValues[ $descriptor ]['empty']['action'] ) {
					case 'allow':
						break;
					case 'replace':
						$tag->setAttribute( $attribute->name, $attrMandatoryValues[ $descriptor ]['empty']['replace_with'] );
						break;
					case 'remove_attr':
						$tag->removeAttribute( $attribute->name );
						break;
					case 'remove_tag':
						$this->nodesToDelete[] = $tag;
						break;
					default:
						throw new Exception( 'Internal error: invalid AttrMandatory rule action ' . $attrMandatoryValues[ $descriptor ]['empty'] );
						break;
				}
			} else if ( ! empty( $attrMandatoryValues[ $descriptor ]['other_values'] ) ) {
				// there is no specific rule for that tag/attribute combination
				// apply the "other_values" rules, to decide what to do with it
				switch ( $attrMandatoryValues[ $descriptor ]['other_values']['action'] ) {
					case 'allow':
						break;
					case 'replace':
						$tag->setAttribute( $attribute->name, $attrMandatoryValues[ $descriptor ]['other_values']['replace_with'] );
						break;
					case 'remove_attr':
						$tag->removeAttribute( $attribute->name );
						break;
					case 'remove_tag':
						$this->nodesToDelete[] = $tag;
						break;
					default:
						throw new Exception( 'Internal error: invalid AttrMandatory rule action ' . $attrMandatoryValues[ $descriptor ]['other_values']['action'] );
						break;
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Remove or replace attributes with forbidden values
	 *
	 * @param $tag
	 * @param $attribute
	 * @param $descriptor
	 *
	 * @return bool
	 */
	private function cleanAttrForbiddenValue( $tag, $attribute, $descriptor ) {

		$attrForbiddenValues = $this->ampConfig->get( 'attr_forbidden_value' );
		if ( array_key_exists( $descriptor, $attrForbiddenValues ) ) {
			$forbiddenValues = $attrForbiddenValues[ $descriptor ];
			if ( array_key_exists( $attribute->value, $forbiddenValues ) ) {
				// if present, this attribute cannot have a specific value
				// if this value is found, we either remove it or replace the value with a new one
				if ( $forbiddenValues[ $attribute->value ]['action'] == 'replace' ) {
					$tag->setAttribute( $attribute->name, $forbiddenValues[ $attribute->value ]['replace_with'] );
				} else {
					$tag->removeAttribute( $attribute->name );
				}

				return false;
			}
		}

		return true;
	}

	/**
	 * If present, this attribute must have a specific value, and we enforce it
	 *
	 * @param $tag
	 * @param $attribute
	 * @param $descriptor
	 */
	private function cleanAttrForcedValues( $tag, $attribute, $descriptor ) {

		$attrForcedValues = $this->ampConfig->get( 'attr_forced_value' );
		if ( array_key_exists( $descriptor, $attrForcedValues ) ) {
			if ( ! in_array( $attribute->value, $attrForcedValues[ $descriptor ]['allow'] ) ) {
				$tag->setAttribute( $attribute->name, $attrForcedValues[ $descriptor ]['forced_value'] );
			}
		}
	}

	/**
	 * Remove an element from the DOM
	 *
	 * @param $element
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
	 * @param $node
	 *
	 * @return bool
	 */
	private function isEmptyNode( $node ) {

		return 0 === $node->childNodes->length && empty( $node->textContent );
	}
}
