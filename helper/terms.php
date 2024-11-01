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
 * Helps using categories
 */
class WeeblrampHelper_Terms {

	private $terms        = null;
	private $termsParents = null;

	/**
	 * WeeblrampHelper_Terms constructor.
	 *
	 * Loads the list of all categories
	 */
	public function __construct() {

		$this->terms        = WblWordpress_Compat::get_terms(
			array(
				'hide_empty' => false,
				'taxonomy'   => null
			)
		);
		$this->termsParents = WblWordpress_Compat::get_terms(
			array(
				'hide_empty' => false,
				'fields'     => 'id=>parent',
				'taxonomy'   => null
			)
		);
	}

	/**
	 * Helper to find out how many parents a term has
	 *
	 * @param int $termId
	 * @param int $level
	 *
	 * @return int
	 */
	public function getTermLevel( $termId, $level = 0 ) {

		// if not set or parent = 0, return level = 1
		if ( ! empty( $this->termsParents[ $termId ] ) ) {
			// cat has a term, increase level
			return $this->getTermLevel( $this->termsParents[ $termId ], $level + 1 );
		}

		return $level;
	}

	/**
	 * Rebuild a list, inserting children just below their parents.
	 *
	 * Children are sorted alphabetically
	 *
	 * @param $taxonomiesList
	 *
	 * @return mixed
	 */
	public function sortTermsList( $unsortedList, $sortedList = array(), $parentId = 0 ) {

		if ( ! is_array( $unsortedList ) ) {
			return $unsortedList;
		}

		foreach ( $unsortedList as $item ) {
			// root term
			if ( $item->parent == $parentId ) {
				$sortedList[] = $item;
				$children     = $this->getChildren( $item->term_id, $unsortedList );
				if ( ! empty( $children ) ) {
					foreach ( $children as $child ) {
						$sortedList[]  = $child;
						$childrenItems = $this->sortTermsList( $unsortedList, $sortedList, $child->term_id );
						$sortedList    += $childrenItems;
					}
				}
			}
		}

		return $sortedList;
	}

	/**
	 * add_rewrite_endpoint does not work on custom taxonomies
	 * @see https://core.trac.wordpress.org/ticket/33728
	 *
	 * Using workaround provided on the bug report, which consists
	 * in adding "manually" the missing rewrite rules for each custom taxonomies
	 *
	 * @param string $queryVar the endpoint we want to enable for taxonomies
	 * @param array  $postTypesRules user set list of usable taxonomies. For better perf, we should only add rules to
	 *     the active ones
	 */
	public function fixRewriteRules( $queryVar, $postTypesRules ) {

		global $wp_rewrite;

		$taxonomies = get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects' );
		foreach ( $taxonomies as $tax_id => $taxonomy ) {
			// some taxonomies may not have rewrite rules
			if ( empty( $taxonomy->rewrite ) ) {
				continue;
			}

			$shouldAddRule = false;
			// is this taxonomy allowed?
			$postTypes = $taxonomy->object_type;
			foreach ( $postTypes as $postType ) {
				if ( array_key_exists( $postType, $postTypesRules ) ) {
					if ( empty( $postTypesRules[ $postType ]['enabled'] ) ) {
						// this post type is disabled, skip
						continue;
					}

					// post type is enabled on AMP pages, does it have restrictions on terms?
					if (
						empty( $postTypesRules[ $postType ]['per_taxonomy'] )
						||
						empty( $postTypesRules[ $postType ]['per_taxonomy'][ $taxonomy->name ] )
					) {
						// post type is enabled, and there is no restriction on that taxonomy terms
						$shouldAddRule = true;
					} else {
						// post type is enabled, but there are some restrictions on that taxonomy terms
						// at least one term should be enabled to require adding the rule
						foreach ( $postTypesRules[ $postType ]['per_taxonomy'][ $taxonomy->name ] as $termId => $enabled ) {
							if ( ! empty( $enabled ) ) {
								// we found one enable term, we must add the rewrite rule for this taxonomy
								// and break out immediately
								$shouldAddRule = true;
								break;
							}
						}
					}
				}
			}

			if ( $shouldAddRule ) {

				// Make sure we use verbose rules.
				// This is required to avoid conflicts on page/2+ pages with WooCommerce and other CPT.
				$wp_rewrite->use_verbose_page_rules = true;

				// add paged tax rewrite rule
				add_rewrite_rule(
					$taxonomy->rewrite['slug'] . '/(.+?)/' . $wp_rewrite->pagination_base . '/([0-9]+)/' . $queryVar . '/?$',
					'index.php?' . $tax_id . '=$matches[1]&paged=$matches[2]&' . $queryVar . '=1',
					'top'
				);

				// add tax rewrite rule
				add_rewrite_rule(
					$taxonomy->rewrite['slug'] . '/(.+?)/' . $queryVar . '/?$',
					'index.php?' . $tax_id . '=$matches[1]&' . $queryVar . '=1',
					'top'
				);
			}
		}

		return $this;
	}

	private function getChildren( $parentId, $list ) {

		$children = array();
		foreach ( $list as $item ) {
			if ( $item->parent == $parentId ) {
				$children[] = $item;
			}
		}

		return $children;
	}
}
