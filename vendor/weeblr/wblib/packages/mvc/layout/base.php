<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author           weeblrPress
 * @copyright        (c) WeeblrPress - Weeblr,llc - 2020
 * @package          AMP on WordPress - weeblrAMP CE
 * @license          http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version          1.12.5.783
 * @date        2020-05-19
 */

/** ensure this file is being included by a parent file */
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * Base class for rendering a display layout
 *
 * @since       0.2.1
 */
class WblMvcLayout_Base {

	/**
	 * Storage for display data
	 *
	 * @var null
	 */
	protected $__data = null;

	/**
	 * Method to render the layout.
	 *
	 * @param   object $__data Object which properties are used inside the layout file to build displayed output
	 *
	 * @return  string  The necessary HTML to display the layout
	 *
	 * @since   0.2.1
	 */
	public function render( $__data ) {

		$this->__data = $__data;

		return '';
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output has [ and ] removed, to serve as HTML id
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return string
	 */
	public function getAsId( $key, $default = '' ) {

		$id = WblSystem_Strings::asHtmlId( $this->getAsAttr( $key, $default ) );

		return $id;
	}

	/**
	 * Display the time elapsed between today in a fuzzy way (ie 2 minutes ago, in 30 seconds)
	 *
	 * @param srtring           $key
	 * @param string | DateTime $default
	 * @param string | DateTime $refDate Optional reference date, default to 'now'
	 *
	 * @return string
	 */
	public function getAsMoments( $key, $default = '', $refDate = 'now', $options = array() ) {

		$date = $this->getAsDateTime( $key, $default );

		return WblSystem_Date::getAsMoments( $date, $refDate );
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output is used to build a DateTime object
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return DateTime
	 */
	public function getAsDateTime( $key, $default = null ) {

		$date = $this->get( $key, $default );

		return WblSystem_Date::toDateTimeObject( $date );
	}

	/**
	 * Get the full display data variable
	 *
	 * @return mixed
	 */
	protected function getDisplayData() {

		return $this->__data;
	}

	/**
	 * Check if some display data has been set to render, either globally
	 * or for a specific display data key
	 *
	 * @param string $key
	 * @param string $subkey If $key item exists, and is an array, we can check for a subkey in that array
	 *
	 * @return bool
	 */
	protected function hasDisplayData( $key = '', $subkey = null ) {

		$hasDisplayData = empty( $key ) ? ! empty( $this->__data ) : ! empty( $this->__data[ $key ] );
		if ( ! empty( $subkey ) ) {
			$hasDisplayData = ! empty( $this->__data[ $key ] ) && is_array( $this->__data[ $key ] ) && ! empty( $this->__data[ $key ][ $subkey ] );
		}

		return $hasDisplayData;
	}

	/**
	 * Count items in a particular keyed data element
	 *
	 * @param string $key
	 *
	 * @return int
	 */
	protected function count( $key = '' ) {

		$data = $this->get( $key );

		return count( $data );
	}

	/**
	 * Get a display data variable, identified by its key in the
	 * data set passed to the render() method. Assumes this data is
	 * an associative array. If not, or if key not set, returns $default value
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	protected function get( $key, $default = null ) {

		return isset( $this->__data[ $key ] ) ? $this->__data[ $key ] : $default;
	}

	/**
	 * Escape a string before display. Wrapper around htmlspecialchars
	 *
	 * @param string $string
	 * @param int    $flags
	 * @param string $encoding
	 *
	 * @return string
	 */
	protected function escape( $string, $flags = ENT_COMPAT, $encoding = 'UTF-8' ) {

		return htmlspecialchars( $string, $flags, $encoding );
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output is escaped with esc_html
	 * through the @see WblMvcLayout_Base::escape method
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return string
	 */
	protected function getEscaped( $key, $default = null ) {

		return esc_html( isset( $this->__data[ $key ] ) ? $this->__data[ $key ] : $default );
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output cast to an array
	 *
	 * @param string $key
	 * @param array  $default
	 *
	 * @return array
	 */
	protected function getAsArray( $key, $default = array() ) {

		$value = $this->get( $key, $default );

		return is_array( $value ) ? $value : (array) $value;
	}

	/**
	 * Get a specific entry in an associative array
	 *
	 * @param string $key Key of the associative array to lookup
	 * @param string $keyInArray Key of the desired item in the associative array
	 * @param mixed  $default Returned if not an array, or item in array not found or empty
	 *
	 * @return mixed
	 */
	protected function getInArray( $key, $keyInArray, $default = null ) {

		$value = $default;
		if ( $this->hasDisplayData( $key ) ) {
			$array = $this->getAsArray( $key );
			$value = empty( $array[ $keyInArray ] ) ? $default : $array[ $keyInArray ];
		}

		return $value;
	}

	/**
	 * Get a specific property of an object
	 *
	 * @param string $key
	 * @param string $keyInObject
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	protected function getInObject( $key, $keyInObject, $default = null ) {

		$value = $default;
		if ( $this->hasDisplayData( $key ) ) {
			$object = $this->getAsArray( $key );
			$value  = empty( $object[ $keyInObject ] ) ? $default : $object[ $keyInObject ];
		}

		return $value;
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output cast to an integer
	 *
	 * @param string $key
	 * @param int    $default
	 *
	 * @return int
	 */
	protected function getAsInt( $key, $default = 0 ) {

		return (int) $this->get( $key, $default );
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output cast to an integer
	 * and then formatted as a string:
	 *
	 * Format into K and M for large number
	 * 0 -> 9999 : literal
	 * 10 000 -> 999999 : 10K -> 999,9K (max one decimal)
	 * > 1000 000 : 1M -> 1,9M (max 1 decimals)
	 *
	 * @param string $key
	 * @param int    $default
	 *
	 * @return string
	 */
	protected function getAsFormattedInt( $key, $default = 0 ) {

		return WblSystem_Strings::formatIntForTitle( $this->get( $key, $default ) );
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output is escaped with esc_url
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return string
	 */
	protected function getAsUrl( $key, $default = '' ) {

		return esc_url( $this->get( $key, $default ) );
	}

	/**
	 * Same as @see WblMvcLayout_Base::getAsUrl, but returned url is made absolute
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return string
	 */
	protected function getAsAbsoluteUrl( $key, $default = '', $forceDomain = false ) {

		$url = WblSystem_Route::absolutify( $this->get( $key, $default ), $forceDomain );

		return esc_url( $url );
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output is escaped with esc_js
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return string
	 */
	protected function getAsJs( $key, $default = '' ) {

		return esc_js( $this->get( $key, $default ) );
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output is json encoded
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return string
	 */
	protected function getAsJson( $key, $default = '' ) {

		return json_encode( $this->get( $key, $default ) );
	}

	/**
	 * Same as @see WblMvcLayout_Base::get, but returned output is escaped with esc_attr
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return string
	 */
	protected function getAsAttr( $key, $default = '' ) {

		return esc_attr( $this->get( $key, $default ) );
	}
}
