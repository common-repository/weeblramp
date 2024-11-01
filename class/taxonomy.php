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
 * Dispatch request and render if appropriate
 *
 * Some parts based on Automattic AMP plugin
 */
class WeeblrampClass_Taxonomy extends WeeblrampClass_Base {

	private $router = null;

	/**
	 * Loads all definitions of taxonomies attached to an object type (post, pages, CPT)
	 *
	 * @TODO: should memoize?
	 *
	 * @param string $objectName
	 *
	 * @return array
	 */
	public function getObjectTaxonomiesDetails( $objectName ) {

		$taxonomies        = get_object_taxonomies( $objectName );
		$taxonomiesDetails = array();
		foreach ( $taxonomies as $taxonomy ) {
			$details = WblWordpress_Compat::get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false
				)
			);

			// sort hierarchically
			if ( ! is_wp_error( $details ) ) {
				$taxonomiesDetails[ $taxonomy ] = WblFactory::getThe( 'WeeblrampHelper_Terms' )->sortTermsList( $details );
			}
		}

		// store final result
		return $taxonomiesDetails;
	}

	/**
	 * Whether a particular post should be AMPlified or not
	 *
	 * @param WP_Post | int $item
	 *
	 * @return bool
	 */
	public function shouldAmplifyItem( $item ) {

		if ( is_numeric( $item ) ) {
			// we have only an ID, fetch the actual post
			$item = get_post( $item );
		}

		if ( empty( $item ) ) {
			return false;
		}

		if ( is_null( $this->router ) ) {
			// store the router, needed downstream
			$this->router = WeeblrampFactory::getThe( 'WeeblrampClass_Route' );
		}

		// finds out if this post type has support for AMP
		if ( ! empty( $item->post_type ) && ! post_type_supports( $item->post_type, $this->router->getQueryVar() ) ) {
			return false;
		}

		// check for manual override by user
		$userEnabled = get_post_meta(
			$item->ID,
			'_wbamp_enable_amp',
			true
		);

		// otherwise use taxonomies
		switch ( $userEnabled ) {
			case '1':
				break;
			case '0':
				return false;
				break;
			default:
				// apply category/taxonomies filtering
				if ( ! $this->hasUserSelectedTerm( $item ) ) {
					return false;
				}
				break;
		}

		// Apply filter to skip a specific post. Disregarded when in standalone mode (ie entire site is AMP)
		/**
		 * Filter whether an item (post, page, custom) should be amplified or not.
		 *
		 * This filter is run after weeblrAMP has already applied user set rules for AMPlification, so it can only disable AMPlification for an item, not enable it
		 *
		 * @api
		 * @package weeblrAMP\filter\route
		 * @var weeblramp_skip_item
		 * @since   1.0.0
		 *
		 * @param bool  $skipItem Whether the item should be AMPlified or not
		 * @param int   $item_id Id of the WP_Post which is linked to, if any
		 * @param mixed $item the item itself
		 *
		 * @return bool
		 */
		if ( true === apply_filters( 'weeblramp_skip_item', false, $item->ID, $item ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether a specific term should be amplified
	 *
	 * @param int    $termId
	 * @param string $taxonomy optional
	 *
	 * @return bool
	 */
	public function shouldAmplifyTerm( $termId, $taxonomy = '' ) {

		$term = get_term( $termId, $taxonomy );
		if ( empty( $term ) || is_wp_error( $term ) ) {
			return false;
		}

		switch ( $taxonomy ) {
			case 'category':
				if ( $this->userConfig->isFalsy( 'amplify_categories' ) ) {
					return false;
				}
			case 'archives':
				if ( $this->userConfig->isFalsy( 'amplify_archives' ) ) {
					return false;
				}
			case 'post_tag':
				if ( $this->userConfig->isFalsy( 'amplify_tags' ) ) {
					return false;
				}
			case 'author':
				if ( $this->userConfig->isFalsy( 'amplify_authors' ) ) {
					return false;
				}
			default:
				$postTypesOption = $this->userConfig->get( 'amp_post_types' );
				// !!!! we only handle one to one taxonomy/post types
				$taxonomyDef = get_taxonomy( $taxonomy );
				$postType    = wbArrayGet( $taxonomyDef->object_type, 0, null );

				$postTypeDef = wbArrayGet( $postTypesOption, $postType );
				if ( empty( $postTypeDef ) || ! is_array( $postTypeDef ) ) {
					// we don't know this post type
					return false;
				}

				if ( empty( $postTypeDef['enabled'] ) ) {
					// taxonomy disabled entirely
					return false;
				}

				if (
					! array_key_exists( 'per_taxonomy', $postTypeDef )
					||
					! is_array( $postTypeDef['per_taxonomy'] )
					||
					empty( $postTypeDef['per_taxonomy'][ $term->taxonomy ] )
				) {
					// post type is enabled globally, but nothing
					// is set for individual taxonomies: allow any term
					return true;
				}

				if ( ! empty( $postTypeDef['per_taxonomy'][ $term->taxonomy ][ $termId ] ) ) {
					// if we have something set for this particular term
					// and that something happens to be yes
					// allow this term to be amplified
					return true;
				}

				if ( ! array_key_exists( $termId, $postTypeDef['per_taxonomy'][ $term->taxonomy ] ) ) {
					// in the same way, if no option has yet been set by user
					// for this term, allow it to be amplified
					return true;
				}
				break;
		}

		return false;
	}

	/**
	 * Whether a post has one of the terms (category, tags,...) selected by user
	 * to create AMP version of the content
	 *
	 * @param $post
	 *
	 * @return bool
	 */
	private function hasUserSelectedTerm( $post ) {

		$hasTerm = false;
		if ( empty( $post ) ) {
			return $hasTerm;
		}

		$postTypesOption = $this->userConfig->get( 'amp_post_types' );

		/**
		 * Filter the list of taxonomies displayed to a user to select pages to AMPlify.
		 *
		 * Plugins or integrations with plugins (see Jetpack integration) should add the taxonomies they manage to that list so that user can select to AMPlify content from them.
		 *
		 * @api
		 * @package weeblrAMP\filter\config
		 * @var weeblramp_user_selectable_taxonomies
		 * @since   1.0.0
		 *
		 * @param array $allowedTaxonomies Current list of taxonomies
		 *
		 * @return array
		 */
		$allowedTaxonomies = apply_filters(
			'weeblramp_amp_post_types_user_selectable_taxonomies',
			$this->systemConfig->get( 'taxonomies.built_in_selectable' )
		);

		if ( empty( $allowedTaxonomies[ $post->post_type ] ) ) {
			// no user selectable taxonomies provided
			// by 3rd-party plugin or integration for this post type
			// we cannot/don't want to select it based on terms
			return true;
		}

		$postTaxonomies = $this->getObjectTaxonomiesDetails( $post->post_type );
		if ( empty( $postTaxonomies ) ) {
			// if no term is attached to the item,
			// we cannot/don't want to select it based on terms
			return true;
		}

		foreach ( $postTaxonomies as $postTaxonomyName => $terms ) {
			if ( array_key_exists( $post->post_type, $postTypesOption ) ) {
				// special case: the post type is listed as allowed
				// but no specific taxonomy has been set as user selectable
				// ie they can only switch on/off the entire post type,
				// meaning if we are here, they did switch on that post type
				// so we let go
				if ( empty( $postTypesOption[ $post->post_type ]['per_taxonomy'] ) ) {
					return true;
				}
				if (
					! empty( $postTypesOption[ $post->post_type ]['per_taxonomy'] )
					&&
					! empty( $postTypesOption[ $post->post_type ]['per_taxonomy'][ $postTaxonomyName ] )
					&&
					is_array( $postTypesOption[ $post->post_type ]['per_taxonomy'][ $postTaxonomyName ] )
				) {
					$enabledTerms             = array_keys( array_filter( $postTypesOption[ $post->post_type ]['per_taxonomy'][ $postTaxonomyName ] ) );
					$postTermsForThisTaxonomy = wp_get_post_terms( $post->ID, $postTaxonomyName );
					// if item has no term for this taxonomy, it's a yes
					// but this may be reversed when examining other taxonomies
					if ( empty( $postTermsForThisTaxonomy ) ) {
						$hasTerm = true;
						continue;
					}
					if ( array_key_exists( $postTaxonomyName, $postTypesOption[ $post->post_type ]['per_taxonomy'] ) ) {
						foreach ( $terms as $term ) {
							// we have a category selection for this post type
							if ( ! empty( $allowedTaxonomies[ $post->post_type ] ) && in_array( $term->taxonomy, $allowedTaxonomies[ $post->post_type ] ) ) {

								// if item has a term that is disabled, that's a no go
								if (
									has_term( $term->slug, $postTaxonomyName, $post )
									&&
									array_key_exists( $term->term_id, $postTypesOption[ $post->post_type ]['per_taxonomy'][ $postTaxonomyName ] )
									&&
									! in_array( $term->term_id, $enabledTerms )
								) {
									// if term is in list, and is not enabled (by user)
									return false;
								}

								// if item has a term which is enabled, that's a possible yes
								// subject to no other rule being broken
								if (
									has_term( $term->slug, $postTaxonomyName, $post )
									&&
									in_array( $term->term_id, $enabledTerms )
								) {
									// if term is in list, and is enabled (by user)
									$hasTerm = true;
								}
							}
						}
					}
				}
			}
		}

		return $hasTerm;
	}
}
