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

class WblSystem_Log {

	const DEBUG = 'debug';
	const INFO = 'info';
	const ERROR = 'error';
	const ALERT = 'alert';

	// logging level presets
	const LOGGING_NONE = 'none';
	const LOGGING_PRODUCTION = 'production';
	const LOGGING_DETAILED = 'detailed';

	/**
	 * Detailed Logging timeout after 30mns
	 */
	const DETAILED_LOGGING_TIMEOUT = 1800;

	/**
	 * @var WblSystem_Log Static instance
	 */
	static $instance = null;

	/**
	 * @var array Predefined logging levels constants.
	 */
	static $predefinedLevels = array();

	// list of levels that must be logged (empty array will disabled logging)
	protected $config = array();
	protected $uuid   = null;

	/**
	 * WblSystem_Log constructor.
	 *
	 * Sets default logging levels values, based on current WP_DEBUG
	 * or persisted configuration
	 *
	 */
	protected function __construct() {

		self::$predefinedLevels = array(
			self::LOGGING_NONE       => array(),
			self::LOGGING_PRODUCTION => array(
				self::ERROR,
				self::ALERT
			),
			self::LOGGING_DETAILED   => array(
				self::DEBUG,
				self::INFO,
				self::ERROR,
				self::ALERT
			)
		);

		if ( WP_DEBUG ) {
			$defaultConfig =
				array(
					'preset'            => self::LOGGING_PRODUCTION,
					'preset_disable_on' => 0,
					'log_level'         => self::$predefinedLevels[ self::LOGGING_PRODUCTION ]
				);
		} else {
			$defaultConfig =
				array(
					'preset'            => self::LOGGING_DETAILED,
					'preset_disable_on' => 0,
					'log_level'         => self::$predefinedLevels[ self::LOGGING_DETAILED ]
				);
		}

		$this->config = get_option(
			'wblib_logging_config',
			$defaultConfig
		);
	}

	/**
	 * If current preset mode is "detailed", check if its timeout has expired
	 * and if so reset it, and inform back the caller with the newly selected
	 * preset value, so that upstream config can be updated as well.
	 *
	 * @return bool|string
	 */
	public static function maybeResetLoggingPresetAfterTimeout() {

		$logger        = self::getLogger();
		$currentPreset = wbArrayGet(
			$logger->config,
			'preset',
			self::LOGGING_PRODUCTION
		);

		// auto disable detailed logging after a preset time
		if ( self::LOGGING_DETAILED == $currentPreset ) {
			$presetTime = wbArrayGet(
				$logger->config,
				'preset_disable_on',
				0
			);

			// if time has elapsed, revert to LOGGING_PRODUCTION
			if (
				! empty( $presetTime )
				&&
				$presetTime < time()
			) {
				$logger->config =
					array(
						'preset'            => self::LOGGING_PRODUCTION,
						'preset_disable_on' => 0,
						'log_level'         => self::$predefinedLevels[ self::LOGGING_PRODUCTION ]
					);
				update_option(
					'wblib_logging_config',
					$logger->config
				);

				$newPreset = self::LOGGING_PRODUCTION;
			}
		}

		return empty( $newPreset ) ? false : $newPreset;
	}

	/**
	 * Store configuration, provided by main process
	 *
	 * @param string $logLevel One of the predefined logging levels
	 * @param bool   $persist
	 */
	public static function configure( $logLevel, $persist = false ) {

		if ( ! array_key_exists(
			$logLevel,
			self::$predefinedLevels
		)
		) {
			return;
		}

		// get logger and store previous preset
		$logger         = self::getLogger();
		$previousPreset = wbArrayGet(
			$logger->config,
			'preset',
			self::LOGGING_PRODUCTION
		);

		// set the new config
		$logger->config['preset']    = $logLevel;
		$logger->config['log_level'] = self::$predefinedLevels[ $logLevel ];
		// if we have changed from something to self::LOGGING_DETAILED
		// we record the time, to be able to automatically return
		// to self::LOGGING_PRODUCTION after a preset time
		if (
			self::LOGGING_DETAILED == $logLevel
			&&
			$logLevel != $previousPreset
		) {
			$logger->config['preset_disable_on'] =
				time()
				+ apply_filters(
					'wblib_detailed_logging_timeout',
					self::DETAILED_LOGGING_TIMEOUT
				);
		} else if ( self::LOGGING_DETAILED != $logLevel ) {
			// if changing config, and new log level is not "detailed",
			// then we nuke the timeout
			$logger->config['preset_disable_on'] = 0;
		}

		// persist this new config
		if ( $persist ) {
			update_option(
				'wblib_logging_config',
				$logger->config
			);
		}
	}

	/**
	 * Log a message with level Error
	 *
	 * @param string message
	 * @param mixed various params to be sprintfed into the msg
	 *
	 * @return boolean true if success
	 */
	public static function error( $prefix ) {

		$args = func_get_args();
		$d    = array_shift( $args );

		return self::getLogger()->_log( 'errors', self::ERROR, array( 'category' => $prefix ), $args );
	}

	public static function alert( $prefix ) {

		$args = func_get_args();
		$d    = array_shift( $args );

		return self::getLogger()->_log( 'alerts', self::ALERT, array( 'category' => $prefix ), $args );
	}

	public static function debug( $prefix ) {

		$args = func_get_args();
		$d    = array_shift( $args );

		return self::getLogger()->_log( 'debug', self::DEBUG, array( 'category' => $prefix ), $args );
	}

	public static function info( $prefix ) {

		$args = func_get_args();
		$d    = array_shift( $args );

		return self::getLogger()->_log( 'info', self::INFO, array( 'category' => $prefix ), $args );
	}

	public static function custom( $prefix, $level, $category ) {

		$args = func_get_args();
		$d    = array_shift( $args );

		return self::getLogger()->_log( $prefix, $level, array( 'category' => $category ), $args );
	}

	/**
	 * Singleton
	 *
	 * @return WblSystem_Log
	 */
	protected static function getLogger() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Whether a given logging level is enabled and should be logged
	 *
	 * @param String $level
	 *
	 * @return bool
	 */
	protected function levelIsEnabled( $level ) {

		$enabled = in_array(
			$level,
			wbArrayGet(
				$this->config,
				'log_level',
				array()
			)
		);

		return $enabled;
	}

	/**
	 * Prepare logging to file
	 *
	 * @param        $file
	 * @param string $level
	 * @param        $options
	 * @param null   $args
	 *
	 * @return bool
	 */
	protected function _log( $file, $level = self::INFO, $options, $args = null ) {

		// nothing to do, go away asap
		if ( ! $this->levelIsEnabled( $level ) ) {
			return true;
		}

		// something to do, process message
		if ( count( $args ) > 1 ) {
			// use sprintf
			$message = call_user_func_array( 'sprintf', $args );
		} else {
			$message = $args[0];  // no variable parts, just use first element as a string
		}

		// include user details in logging
		$user       = wp_get_current_user();
		$userString = empty( $user->ID ) ? 'guest' : $user->ID . ' (' . $user->user_email . ')';

		// do logging
		// note: cannot use Exceptions here, as one plugin throwing an exception
		// would prevent other plugins to be fired
		$params = array(
			'file'     => $file,
			'priority' => $level,
			'type'     => $level,
			'user'     => $userString,
			'message'  => $message
		);

		// merge in additional options set by caller
		// include: format and timestamp
		if ( is_array( $options ) ) {
			$params = array_merge( $params, $options );
		}
		$logStatus = $this->_logToFile( $params );

		return $logStatus;
	}

	protected function _logToFile( $params ) {

		// check params
		$defaultParams = array(
			'file'              => 'info'
			,
			'category'          => 'wbLib'
			,
			'date'              => WblSystem_Date::getSiteNow( 'Y-m-d' )
			,
			'time'              => WblSystem_Date::getSiteNow( 'H:i:s' )
			,
			'message'           => 'No logging message, probably an error'
			,
			'user'              => '-'
			,
			'priority'          => self::INFO
			,
			'text_entry_format' => "{DATE}\t{TIME}\t{TYPE}\t{C-IP}\t{USER}\t{MESSAGE}"
			,
			'timestamp'         => WblSystem_Date::getSiteNow( 'Y-m-d' )
			,
			'prefix'            => 'wblib'
		);

		$liveParams = array_merge( $defaultParams, $params );

		// files and path
		$logPath = WEEBLRAMP_LOGS_DIR . $liveParams['category'] . '/' . $liveParams['file'];
		wp_mkdir_p( $logPath );
		$logFile = $logPath . '/log_' . $liveParams['file'] . '.' . $liveParams['timestamp'] . '.log.php';

		if ( ! file_exists( $logFile ) ) {
			$header = "<?php
// wbLib log file			
defined('WPINC') || die(__FILE__);

DATE\tTIME\tTYPE\tIP\tUSER\tMESSAGE
";
		} else {
			$header = "\n";
		}

		// build up the record
		$log         = str_replace( '{DATE}', $liveParams['date'], $liveParams['text_entry_format'] );
		$log         = str_replace( '{TIME}', $liveParams['time'], $log );
		$log         = str_replace( '{TYPE}', $liveParams['type'], $log );
		$log         = str_replace( '{C-IP}', WblSystem_Http::getIpAddress(), $log );
		$log         = str_replace( '{USER}', $liveParams['user'], $log );
		$log         = str_replace( '{MESSAGE}', $liveParams['message'], $log );
		$fullMessage = $header . $log;

		// write to log file
		file_put_contents( $logFile, $fullMessage, FILE_APPEND );

		return true;
	}
}
