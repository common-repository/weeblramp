<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author      weeblrPress
 * @copyright   (c) WeeblrPress - Weeblr,llc - 2020
 * @package     AMP on WordPress - weeblrAMP CE
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     1.12.5.783
 * @date                2020-05-19
 */

// no direct access
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * Provides ability to cycle through custom defined values
 *
 * @since    0.2.2
 *
 */
class WblSystem_Cycle {

	private static $_cyclers = array();

	private $_values  = array();
	private $_current = array();
	private $_step    = 1;
	private $_count   = 0;

	/**
	 * Constructor
	 *
	 * @param array $values
	 * @param int   $step
	 * @param int   $start
	 */
	private function __construct( $values, $step, $start ) {

		$this->_values  = $values;
		$this->_count   = count( $values );
		$this->_step    = $step;
		$this->_current = empty( $start ) ? null : $start;
	}

	/**
	 * Get a unique instance of an object that will cycle through a provided
	 * set of values
	 *
	 * @param string $id unique id for the cycler
	 * @param int    $step index increment between each value to be returned
	 * @param int    $start if not null, numeric index in array of value to start with
	 * @param array  $values array of values to cycle through
	 *
	 * @return WblSystem_Cycle the cycler object
	 */
	public static function getInstance( $id, $step = 1, $start = null, $values = array( true, false ) ) {

		if ( empty( self::$_cyclers[ $id ] ) ) {
			self::$_cyclers[ $id ] = new self( $values, $step, $start );
		}

		return self::$_cyclers[ $id ];
	}

	/**
	 * Get next value in cycle
	 *
	 * @param string $reset if true, get first value, and reset index
	 *
	 * @return multitype: current value
	 */
	public function get( $reset = false ) {

		if ( $reset || is_null( $this->_current ) ) {
			$this->_current = 0;
		} else {
			$this->_current = $this->_current + $this->_step;
			if ( $this->_current >= $this->_count ) {
				$this->_current = $this->_current - $this->_count;
			}
		}

		return $this->_values[ $this->_current ];
	}

	/**
	 * Returns true every "step" calls,
	 * false otherwise, "step" being the parameter passed to the constructor
	 *
	 * @param string $reset
	 *
	 * @return boolean
	 */
	public function every( $reset = false ) {

		if ( $reset || is_null( $this->_current ) ) {
			$this->_current = 0;
		} else {
			$this->_current ++;
		}

		$isTime = ( $this->_current % $this->_step ) == 0;

		return $isTime;
	}
}

