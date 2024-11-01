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
 * Integration with WooCommerce
 *
 */
class WeeblrampIntegration_Woocommerce extends WeeblrampClass_Integration {

	protected $id = 'woocommerce/woocommerce.php';

	protected $filters = array(
		array(
			'filter_name'   => 'weeblramp_config_set_defaults',
			'method'        => 'setConfigDefaults',
			'priority'      => 10,
			'accepted_args' => 1
		),
		array(
			'filter_name'     => 'weeblramp_amp_post_types_user_selectable_taxonomies',
			'method'          => 'filterSelectableTaxonomies',
			'priority'        => 10,
			'accepted_args'   => 1,
			'always_in_admin' => 1,
		),
	);

	/**
	 * Pull some data from Yoast to serve as default values for config
	 *
	 * @param $config
	 *
	 * @return mixed
	 */
	public function setConfigDefaults( $config ) {

		// if not active, disable by default the integration
		if ( ! $this->active && ! empty( $this->id ) ) {
			// site name
			$config['integrations_list'][ $this->id ] = 0;
		}

		return $config;
	}

	/**
	 * Adds WooCommerce custom post types taxonomies to the list
	 * of taxonomies user can select from to display AMP pages or not
	 *
	 * @param array $selectableTaxonomies
	 *
	 * @return array
	 */
	public function filterSelectableTaxonomies( $selectableTaxonomies ) {

		if ( is_array( $selectableTaxonomies ) ) {
			$selectableTaxonomies = array_merge(
				$selectableTaxonomies,
				array(
					'product' => array(
						'product_tag',
						'product_cat',
						// @TODO handle?
						//'product_type'
					),
				)
			);
		}

		return $selectableTaxonomies;
	}

	/**
	 * Returns true if this integration is available, ie if the
	 * corresponding plugin or service is installed and activated
	 *
	 * @return bool
	 */
	protected function discover() {

		return class_exists( 'WooCommerce' );
	}
}
