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
 * Holds and manage data
 */
class WeeblrampClass_Model extends WeeblrampClass_Base {

	protected $router     = null;
	protected $__data     = null;
	protected $dataLoaded = false;

	/**
	 * Constructor
	 *
	 * @param   array $options An array of options.
	 *
	 */
	public function __construct( $options = array() ) {

		parent::__construct( $options );

		// add the router to all models
		$this->router = wbArrayGet( $options, 'router', WeeblrampFactory::getThe( 'WeeblrampClass_Route' ) );
	}

	/**
	 * update the data
	 */
	public function getData() {

		if ( $this->dataLoaded ) {
			return $this->__data;
		}

		return $this->loadData()->__data;
	}

	/**
	 *  prepare the data
	 *
	 * @return $this
	 */
	protected function loadData() {

		return $this;
	}
}
