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

class WeeblrampModel_StructuredData extends WeeblrampClass_Model {

	/**
	 * Stores data collected about the page
	 *
	 * @var array
	 */
	private $pageData = array();

	/**
	 * Adds used objects as properties
	 *
	 * WeeblrampModel_Renderer constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );
	}

	/**
	 * Get current page meta data, optionally filtering
	 * the output to only get some parts.
	 *
	 * @param array $pageData Array of information about the page being rendered
	 *
	 * @return array
	 */
	public function getData( $pageData = array() ) {

		$this->pageData = $pageData;

		$data = parent::getData();

		return $data;
	}

	/**
	 * Collect meta data in an array, filter it to allow extension
	 * by others
	 *
	 * @return $this
	 */
	protected function loadData() {

		if ( $this->userConfig->isFalsy( 'struct_data_enabled' ) ) {
			// user disabled this
			return $this;
		}

		// build the various bits
		$this->loadSiteName()
		     ->organization();

		/**
		 * Filter the page schema.org structured data.
		 *
		 * This is an array of all schema.org structured data built/collected. It will be later on json_encoded when rendering the page.
		 *
		 * @api
		 * @package weeblrAMP\filter\seo
		 * @var weeblramp_get_structured_data
		 * @since   1.0.0
		 *
		 * @param array $data The full array of structured data.
		 * @param array $pageData The full array of data available about the current page being rendered.
		 *
		 * @return array
		 */
		$this->__data = apply_filters( 'weeblramp_get_structured_data', $this->__data, $this->pageData );

		// store
		$this->dataLoaded = true;

		return $this;
	}

	/**
	 * Build up the json data array holding the organization
	 * site name structured data
	 *
	 * @see https://developers.google.com/search/docs/data-types/sitename
	 *
	 * @return $this
	 */
	private function loadSiteName() {

		$this->__data['site_name'] = array(
			'@context' => 'http://schema.org',
			'@type'    => 'WebSite',
			'name'     => $this->pageData['site_name'],
			'url'      => $this->pageData['site_link']
		);

		return $this;
	}

	/**
	 * Build up the json data array holding social profiles
	 * and phone numbers for the organization
	 *
	 * @see https://developers.google.com/search/docs/data-types/social-profile-links
	 * @see https://developers.google.com/search/docs/data-types/corporate-contacts
	 *
	 * @return $this
	 */
	private function organization() {

		// regular publisher data
		$publisherData                = wbArrayGet(
			$this->pageData,
			'publisher',
			array()
		);
		$this->__data['organization'] = array_merge(
			array(
				'@context' => 'http://schema.org'
			),
			$publisherData
		);

		// possibly social profiles
		$profiles = WblSystem_Strings::stringToCleanedArray(
			$this->userConfig->get( 'struct_profiles_social' ),
			PHP_EOL
		);

		// store social profiles in sameAs field
		if ( ! empty( $profiles ) ) {
			$this->__data['organization']['sameAs'] = $profiles;
		}

		// any sales phone number?
		$contactPoints = array();
		if ( $this->userConfig->isTruthy( 'struct_profiles_contact_sales' ) ) {
			$contactPoint    = array(
				'@type'       => 'ContactPoint',
				'telephone'   => $this->userConfig->get( 'struct_profiles_contact_sales' ),
				'contactType' => 'sales'
			);
			$contactPoints[] = $contactPoint;
		}

		// any customer service phone number
		if ( $this->userConfig->isTruthy( 'struct_profiles_contact_customer' ) ) {
			$contactPoint    = array(
				'@type'       => 'ContactPoint',
				'telephone'   => $this->userConfig->get( 'struct_profiles_contact_customer' ),
				'contactType' => 'customer service'
			);
			$contactPoints[] = $contactPoint;
		}

		// if any phone number, add contactPoint record
		if ( ! empty( $contactPoints ) ) {
			$this->__data['organization']['contactPoint'] = $contactPoints;
		}

		return $this;
	}
}
