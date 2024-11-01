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

class WblSystem_Date {

	const NEVER = 0;
	const CUSTOM = 0;
	const TODAY = 10;
	const YESTERDAY = 11;
	const THIS_WEEK = 20;
	const THIS_MONTH = 30;
	const THIS_YEAR = 40;
	const LAST_7_DAYS = 50;
	const LAST_30_DAYS = 60;
	const LAST_MONTH = 70;
	const LAST_YEAR = 80;

	static protected $_siteTimezoneName = '';
	static protected $_weekStartsOn     = 'sunday';
	static protected $defaultTimeZone   = null;
	static protected $intervalStrings   = array(
		'past'   => array(
			'year'    => 'a year ago',
			'years'   => '%d years ago',
			'month'   => 'a month ago',
			'months'  => '%d months ago',
			'week'    => 'a week ago',
			'weeks'   => '%d weeks ago',
			'day'     => 'a day ago',
			'days'    => '%d days ago',
			'hour'    => 'an hour ago',
			'hours'   => '%d hours ago',
			'minute'  => 'a minute ago',
			'minutes' => '%d minutes ago',
			'second'  => 'just now',
			'seconds' => '%d seconds ago',
		),
		'future' => array(
			'year'    => 'in a year',
			'years'   => 'in %d years',
			'month'   => 'in a month',
			'months'  => 'in %d months',
			'week'    => 'in a week',
			'weeks'   => 'in %d weeks',
			'day'     => 'in a day',
			'days'    => 'in %d days',
			'hour'    => 'in an hour',
			'hours'   => 'in %d hours',
			'minute'  => 'in a minute',
			'minutes' => 'in %d minutes',
			'second'  => 'just now',
			'seconds' => 'in %d seconds',
		)
	);

	/**
	 * User can set specific time zone,
	 * otherwise server timezone will be used
	 *
	 * @param string $name
	 */
	static public function setTimezoneName( $name ) {

		self::$_siteTimezoneName = $name;
	}

	static public function getTimezoneName() {

		if ( empty( self::$_siteTimezoneName ) ) {
			self::$_siteTimezoneName = date_default_timezone_get();
		}

		return self::$_siteTimezoneName;
	}

	static public function getWeekStartsOn() {

		return self::$_weekStartsOn;
	}

	/**
	 * Day the week starts on, usually either 'sunday' or 'monday'
	 *
	 * @param string $weekStartsOn
	 */
	static public function setWeekStartsOn( $weekStartsOn ) {

		self::$_weekStartsOn = $weekStartsOn;
	}

	/**
	 * Get formatted string of now, expressed in UTC time
	 *
	 * @param string $format format string to be used with date time object
	 */
	static public function getUTCNow( $format = 'Y-m-d H:i:s' ) {

		static $now = null;

		if ( is_null( $now ) ) {

			// get datetime with curren time
			$now = new DateTime();

			// set UTC timezone
			$utcZone = new DateTimeZone( 'UTC' );
			$now->setTimeZone( $utcZone );
		}

		// apply requested format
		$formated = $now->format( $format );

		return $formated;
	}

	/**
	 * Get formatted string of now, expressed in current site time
	 *
	 * @param string $format format string to be used with date time object
	 */
	static public function getSiteNow( $format = 'Y-m-d H:i:s' ) {

		$now = self::utcToSite( self::getUTCNow(), $format );

		return $now;
	}

	static public function utcToSite( $dateString, $format = 'Y-m-d H:i:s', $timezoneName = '' ) {

		if ( empty( $dateString ) ) {
			return '';
		}

		// forced time zone?
		$timezoneName = empty( $timezoneName ) ? self::getTimezoneName() : $timezoneName;

		// get site timezone
		if ( ! empty( $timezoneName ) ) {

			// create a datetime object with incoming date
			$date = new DateTime( $dateString . ' UTC' );

			// set timezone
			$timeZone = new DateTimeZone( $timezoneName );
			$date->setTimeZone( $timeZone );
		} else {
			// create a datetime object with incoming date
			$date = new DateTime( $dateString );
		}

		// format and return date
		return $date->format( $format );
	}

	static public function siteToUtc( $dateString, $format = 'Y-m-d H:i:s', $timezoneName = '' ) {

		if ( empty( $dateString ) ) {
			return '';
		}

		// forced time zone?
		$timezoneName = empty( $timezoneName ) ? self::getTimezoneName() : $timezoneName;

		// create a datetime object with incoming date
		$date = new DateTime( $dateString . ' ' . $timezoneName );

		// set UTC timezone
		$utcZone = new DateTimeZone( 'UTC' );
		$date->setTimeZone( $utcZone );

		// format and return date
		return $date->format( $format );
	}

	/**
	 * Finds the start and end day of the week that contains the provided day
	 * or current date/time if none provided.
	 * Works in both php 5.2 and php 5.3+
	 *
	 * As a bonus, you get the number of days from start of range to specified date
	 *
	 * NOTE: we use the currently set default time zone. This has been set at system
	 * plugin level, and uses the tz value found in subscriptions configruation panel
	 *
	 * @param string $date a date time representation
	 * @param string $format a date format for the output
	 * @param        string day the week starts on, in plain english, usually 'sunday' or 'monday'
	 */
	static public function getWeekBoundaries( $date = '', $format = 'Y-m-d H:i:s', $weekStartsOn = '' ) {

		$boundaries = new StdClass();

		$startDay  = empty( $weekStartSOn ) ? self::$_weekStartsOn : $weekStartsOn;
		$day       = new DateTime( $date );
		$thisDay   = strtolower( $day->format( 'l' ) );  // 'sunday', 'monday', ...
		$dayNumber = $day->format( 'w' );  // 0 = Sunday, 1 = monday, ...
		if ( $startDay != $thisDay ) {  // 'last' works only if day is not start day
			// well, 'last' with a day of week does not work before php 5.3, so we have to do otherwise
			// we'll simply substract the appropriate number of days

			// calculate number of days until $date,
			$boundaries->elapsedDaysCount = $dayNumber - ( $startDay == 'sunday' ? 0 : 1 ) + 1;
			// special case: sunday
			if ( $dayNumber == 0 && $startDay == 'monday' ) {
				$boundaries->elapsedDaysCount = 7;
			}

			$mod = '- ' . ( $boundaries->elapsedDaysCount - 1 ) . ' days';
			$day->modify( $mod );
		} else {
			// if date == first day of week, # of days is 1
			$boundaries->elapsedDaysCount = 1;
		}

		$day->setTime( 0, 0, 0 );
		$boundaries->startDate = $day->format( $format );

		$mod = '+ 1 week last day';
		$day->modify( $mod );
		$day->setTime( 23, 59, 59 );
		$boundaries->endDate = $day->format( $format );

		return $boundaries;
	}

	/**
	 * Finds the start and end day of the month that contains the provided day
	 * or current date/time if none provided.
	 *
	 * As a bonus, you get the number of days from start of range to specified date
	 *
	 * NOTE: we use the currently set default time zone. This has been set at system
	 * plugin level, and uses the tz value found in subscriptions configruation panel
	 *
	 * @param string $date a date time representation
	 * @param string $format a date format for the output
	 */
	static public function getMonthBoundaries( $date = '', $format = 'Y-m-d H:i:s' ) {

		$boundaries = new StdClass();

		$day                          = new DateTime( $date );
		$boundaries->elapsedDaysCount = $day->format( 'j' );

		$day                   = new DateTime( '1 ' . $day->format( 'F' ) . ' ' . $day->format( 'Y' ) . ' 00:00:00' );
		$boundaries->startDate = $day->format( $format );

		$mod = '+ 1 month last day';
		$day->modify( $mod );
		$day->setTime( 23, 59, 59 );
		$boundaries->endDate = $day->format( $format );

		return $boundaries;
	}

	/**
	 * Finds the start and end day of the year that contains the provided day
	 * or current date/time if none provided.
	 * Works in both php 5.2 and php 5.3+, as last,next, etc are not used
	 *
	 * As a bonus, you get the number of days from start of range to specified date
	 *
	 * NOTE: we use the currently set default time zone. This has been set at system
	 * plugin level, and uses the tz value found in subscriptions configruation panel
	 *
	 * @param string $date a date time representation
	 * @param string $format a date format for the output
	 */
	static public function getYearBoundaries( $date = '', $format = 'Y-m-d H:i:s' ) {

		$boundaries = new StdClass();

		// calculate first day of year!
		$theDay = new DateTime( $date );
		$year   = $theDay->format( 'Y' );
		$month  = $theDay->format( 'n' );
		$day    = $theDay->format( 'j' );

		$firstDay              = new DateTime( $year . '-1-1' . ' 00:00:00' );
		$boundaries->startDate = $firstDay->format( $format );

		// calculate last day of year
		$lastDay             = new DateTime( $year . '-12-31' . ' 23:59:59' );
		$boundaries->endDate = $lastDay->format( $format );

		// how many days between now and start of year?
		// even with PHP 5.3.3, we cannot use diff(), as it has a bug on windows box
		// http://bugs.php.net/bug.php?id=52798
		// this does the same sort of calculation.
		// in addition, quick testing has shown this to be 10x faster than using diff()...
		// very strange
		$boundaries->elapsedDaysCount = 0;
		$m                            = 1;
		while ( $m < $month ) {
			$firstDay->setDate( $year, $m, 10 ); // calculate next month, use 10th of month to avoid timezone issues if using 1st
			$boundaries->elapsedDaysCount += $firstDay->format( 't' ); // add number of days in that month
			$m ++;
		}

		// add days on current month
		$boundaries->elapsedDaysCount += $day;

		return $boundaries;
	}

	/**
	 * Check if a given date falls within 2 other dates, inclusively
	 *
	 * @param $date
	 * @param $start
	 * @param $end
	 */
	public static function isBetween( $date, $start, $end ) {

		$isBetween = true;

		// before start date ?
		if ( ! empty( $start ) && $date < $start ) {
			$isBetween = false;
		}

		// after end date ?
		if ( ! empty( $end ) && $date > $end ) {
			$isBetween = false;
		}

		return $isBetween;
	}

	public static function getAsMoments( $date, $refDate = 'now', $options = array() ) {

		$date    = self::toDateTimeObject( $date );
		$refDate = self::toDateTimeObject( $refDate );
		if ( empty( $date ) || empty( $refDate ) ) {
			return '';
		}

		// compute elapsed time
		$interval = $date->diff( $refDate );
		switch ( true ) {
			case ( $interval->y > 0 ) :
				$value        = $interval->y;
				$intervalType = 'year';
				break;
			case ( $interval->m > 0 ) :
				$value        = $interval->m;
				$intervalType = 'month';
				break;
			case ( $interval->d > 14 ) :
				$value        = (int) $interval->d / 7;
				$intervalType = 'week';
				break;
			case ( $interval->d > 7 ) :
				$value        = 1;
				$intervalType = 'week';
				break;
			case ( $interval->d > 0 ) :
				$value        = $interval->d;
				$intervalType = 'day';
				break;
			case ( $interval->h > 0 ) :
				$value        = $interval->h;
				$intervalType = 'hour';
				break;
			case ( $interval->i > 0 ) :
				$value        = $interval->i;
				$intervalType = 'minute';
				break;
			case ( $interval->s > 0 ) :
				$value        = $interval->s;
				$intervalType = 'second';
				break;
		}

		$output = '';
		if ( ! empty( $intervalType ) ) {
			$intervalType .= $value > 1 ? 's' : '';
			$stringsType  = $interval->invert ? 'future' : 'past';
			$output       = sprintf( self::$intervalStrings[ $stringsType ][ $intervalType ], $value );
		}

		return $output;
	}

	public static function toDateTimeObject( $date = 'now', $timezone = null ) {

		$dateObject = null;
		if ( is_object( $date ) && 'DateTime' == get_class( $date ) ) {
			$dateObject = $date;
		} else if ( is_string( $date ) ) {
			try {
				$tz         = wbInitEmpty( $timezone, self::getTimeZone() );
				$dateObject = new DateTime( $date, $tz );
			}
			catch ( Exception $e ) {
				$dateObject = new DateTime( 'now', self::getTimeZone() );
			}
		}

		return $dateObject;
	}

	/**
	 * Builds a DateTimeZone object for the request, based on
	 * passed timezone name, or server default if empty
	 * Default to UTC in case of error
	 *
	 * @param string $configuredZoneName
	 *
	 * @return DateTimeZone
	 */
	public static function getTimeZone( $configuredZoneName = '' ) {

		if ( is_null( self::$defaultTimeZone ) ) {
			try {
				$zoneName              = wbInitEmpty( $configuredZoneName, date_default_timezone_get() );
				self::$defaultTimeZone = new DateTimeZone( $zoneName );
			}
			catch ( Exception $e ) {
				self::$defaultTimeZone = new DateTimeZone( 'UTC' );
			}
		}

		return self::$defaultTimeZone;
	}

	/**
	 * Format a DateTimeZone object to ATOM, suitable for Schema.org markup,
	 * replacing +00:00 time zone indication with Z
	 *
	 * @param string | DateTime  $date
	 *
	 * @param null |DateTimeZone $timezone Optional timezone, used only when $date is NOT already a DateTime
	 *
	 * @return string
	 */
	public static function toAtom( $date, $timezone = null ) {

		if ( ! $date instanceof DateTime ) {
			// get a date time object
			$date = self::toDateTimeObject( $date, $timezone );
		}

		// process
		$formatted = $date->format( DateTime::ATOM );
		if ( substr( $formatted, - 6 ) == '+00:00' ) {
			$formatted = substr( $formatted, 0, - 6 ) . 'Z';
		}

		return $formatted;
	}
}

