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
 * Specific helpers to handle transiation to WC version 2.0
 */
class WeeblrampIntegrationWoocommerce_Compat {

	/**
	 * Get the current WC version, or null if not installed/running
	 *
	 * @return string
	 */
	public static function getWcVersion() {

		static $version = null;

		if ( is_null( $version ) ) {
			global $woocommerce;
			if ( ! empty( $woocommerce ) ) {
				$version = $woocommerce->version;
			} else {
				$version = '0';
			}
		}

		return $version;
	}

	/**
	 * Compares the current WC version with provided one.
	 *
	 * @param string $version Version to compare to
	 * @param string $compareType Optional comparison operator, default to ge
	 *
	 * @return mixed
	 */
	public static function wcVersionCompare( $version, $compareType = 'ge' ) {

		return version_compare(
			self::getWcVersion(),
			$version,
			$compareType
		);
	}

	/**
	 * Gets the post object attached to a product
	 *
	 * @param WP_Post $product
	 *
	 * @return array|null|WP_Post
	 */
	public static function getProductPost( $product ) {

		return get_post(
			self::getProductProperty( $product, 'id' )
		);
	}

	/**
	 * Gets the permalink to the post attached to a product
	 *
	 * @param WC_Product $product
	 *
	 * @return false|string
	 */
	public static function getProductPermalink( $product ) {

		return get_permalink(
			self::getProductPost( $product )
		);
	}

	/**
	 * Get related products for a given products
	 *
	 * @param WC_Product $product Source product
	 * @param int        $perPage How many items to return max
	 * @param array      $excludedIds Optional array of products id to be excluded
	 *
	 * @return array
	 */
	public static function getRelatedProducts( $product, $perPage, $excludedIds = array() ) {

		return self::wcVersionCompare( '3.0' )
			?
			wc_get_related_products(
				self::getProductProperty( $product, 'id' ),
				$perPage,
				$excludedIds
			)
			:
			$product->get_related( $perPage );
	}

	/**
	 * Gets a product displayable price
	 *
	 * @param WC_Product $product
	 *
	 * @return float
	 */
	public static function getDisplayPrice( $product ) {

		return self::wcVersionCompare( '3.0' )
			?
			wc_get_price_to_display(
				$product
			)
			:
			$product->get_display_price();
	}

	/**
	 * Gets the list of categories a product belongs to.
	 *
	 * Proxy for wc_get_product_category_list()
	 *
	 * @param WC_Product $product
	 * @param string     $sep
	 * @param string     $before
	 * @param string     $after
	 *
	 * @return string
	 */
	public static function getCategories( $product, $sep = ', ', $before = '', $after = '' ) {

		return self::wcVersionCompare( '3.0' )
			?
			wc_get_product_category_list(
				self::getProductProperty( $product, 'id' ),
				$sep,
				$before,
				$after
			)
			:
			$product->get_categories( $sep, $before, $after );
	}

	/**
	 * Gets the list of tags a product has
	 *
	 * Proxy for wc_get_product_category_list()
	 *
	 * @param WC_Product $product
	 * @param string     $sep
	 * @param string     $before
	 * @param string     $after
	 *
	 * @return string
	 */
	public static function getTags( $product, $sep = ', ', $before = '', $after = '' ) {

		return self::wcVersionCompare( '3.0' )
			?
			wc_get_product_tag_list(
				self::getProductProperty( $product, 'id' ),
				$sep,
				$before,
				$after
			)
			:
			$product->get_tags( $sep, $before, $after );
	}

	/**
	 * Gets the filter to use to intercept the add to cart message
	 *
	 * @return string
	 */
	public static function getFilterName( $filterName ) {

		switch ( $filterName ) {
			case 'wc_add_to_cart_message_html':

				$filter = self::wcVersionCompare( '3.0' ) ? 'wc_add_to_cart_message_html' : 'wc_add_to_cart_message';
				break;
			case 'woocommerce_get_stock_html':
				$filter = self::wcVersionCompare( '3.0' ) ? 'woocommerce_get_stock_html' : 'woocommerce_stock_html';
				break;
			default:
				$filter = '';
		}

		return $filter;
	}

	/**
	 * Shortand to get a product property.
	 *
	 * @param WC_Product $product The product object
	 * @param string     $name The property name
	 * @param null       $default Optional default value
	 *
	 * @return string
	 */
	public static function getProductProperty( $product, $name, $default = null ) {

		switch ( $name ) {
			case 'id':
				$value = self::wcVersionCompare( '3.0' ) ? $product->get_id() : $product->id;
				break;
			case 'name':
				$value = self::wcVersionCompare( '3.0' ) ? $product->get_name() : $product->post_name;
				break;
			case 'product_type':
				$value = self::wcVersionCompare( '3.0' ) ? $product->get_type() : $product->product_type;
				break;
			case 'gallery_image_ids':
				$value = self::wcVersionCompare( '3.0' ) ? $product->get_gallery_image_ids() : $product->get_gallery_attachment_ids();
				break;
			default:
				$value = $default;
				break;
		}

		return $value;
	}

	/**
	 * Compute a version of a prodcut attribute name that's safe to use
	 * in all usages, including html attributes and javascript variables names.
	 *
	 * NB: must be deterministic, same attribute name must always yield same output.
	 *
	 * @param string $attributeName
	 *
	 * @return string
	 */
	public static function sanitizeAttribute( $attributeName, $short = true ) {

		// w is here to make sure result starts with a letter
		$hash = 'w' . md5( strtolower( $attributeName) );
		if ( $short ) {
			$hash = substr(
				$hash,
				0, 11
			);
		}

		return $hash;
	}
}
