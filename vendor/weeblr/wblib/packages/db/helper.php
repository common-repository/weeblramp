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

/**
 * Needs to be rewritten more simply: single db, no caching, use global $wbdb object to get access to the db
 * Quote and namequote ourselves (mysql only so simple)
 *
 */
// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

class WblDb_Helper {

	const STRING = 1;
	const INTEGER = 2;

	const SHL_DEFAULT = '__default';

	static private $db = null;

	public static function getDb() {

		if ( is_null( self::$db ) ) {
			global $wpdb;
			self::$db = new WblDb( $wpdb );
		}

		return self::$db;
	}

	/**
	 * Prepare, set and execute a select query, returning a single result
	 *
	 * usage:
	 *
	 * $result = ShlHelperDb::selectResult( '#__sh404sef_alias', 'alias', array( 'nonsef' =>
	 * 'index.php?option=com_content&view=article&id=12')); will select the 'alias' column where nonsef column is
	 * index.php?option=com_content&view=article&id=12 Alternate where condition syntax:
	 * $result = ShlHelperDb::selectResult( '#__sh404sef_alias', 'alias', 'amount > 0 and amount < ?', array( '100'));
	 * If where condition is a string, it will be used literally, with question marks replaced by parameters as
	 * passed in the next method param. These params are escaped, but the base where condition is not
	 *
	 * @param String  $table The table name
	 * @param Array   $aColList array of strings of columns to be fetched
	 * @param String  $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array   $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array   $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 * @param Array   $orderBy , a list of columns to order the results
	 * @param Integer $offset , first line of result set to select
	 * @param Integer $lines , max number of lines to select
	 *
	 * @return mixed single value read from db
	 * @throw none (underlying database layer does throw errors)
	 */
	public static function selectResult(
		$table, $aColList = array( '*' ), $mWhere = '', $aWhereData = array(), $orderBy = array(), $offset = 0,
		$lines = 0
	) {

		$db = self::_setSelectQuery( $table, $aColList, $mWhere, $aWhereData, $orderBy, $offset, $lines );

		// if not in cache, run query
		$read = $db->loadResult();

		return $read;
	}

	/**
	 * Prepare, set and execute a select query, returning a an array of results
	 *
	 * usage:
	 *
	 * $result = ShlHelperDb::selectResult( '#__sh404sef_alias', 'alias', array( 'nonsef' =>
	 * 'index.php?option=com_content&view=article&id=12')); will select the 'alias' column where nonsef column is
	 * index.php?option=com_content&view=article&id=12 Alternate where condition syntax:
	 * $result = ShlHelperDb::selectResult( '#__sh404sef_alias', 'alias', 'amount > 0 and amount < ?', array( '100'));
	 * If where condition is a string, it will be used literally, with question marks replaced by parameters as
	 * passed in the next method param. These params are escaped, but the base where condition is not
	 *
	 * @param String  $table The table name
	 * @param Array   $aColList array of strings of columns to be fetched
	 * @param String  $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array   $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array   $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 * @param Array   $orderBy , a list of columns to order the results
	 * @param Integer $offset , first line of result set to select
	 * @param Integer $lines , max number of lines to select
	 *
	 * @return mixed single value read from db
	 * @throw none (underlying database layer does throw errors)
	 */
	public static function selectColumn(
		$table, $aColList = array( '*' ), $mWhere = '', $aWhereData = array(), $orderBy = array(), $offset = 0,
		$lines = 0
	) {

		$db = self::_setSelectQuery( $table, $aColList, $mWhere, $aWhereData, $orderBy, $offset, $lines );

		// if not in cache, run query
		$read = $db->loadColumn();

		return $read;
	}

	/**
	 * Prepare, set and execute a select query, returning a single associative array
	 *
	 * usage:
	 *
	 * $result = ShlHelperDb::selectAssoc( '#__sh404sef_alias', array('alias', 'id'), array( 'nonsef' =>
	 * 'index.php?option=com_content&view=article&id=12')); will return an array with 2 keys, alias and id, where
	 * nonsef column is index.php?option=com_content&view=article&id=12
	 *
	 * $result = ShlHelperDb::selectAssoc( '#__sh404sef_alias', array('alias', 'id'), 'amount > 0 and amount < ?',
	 * array( '100')); If where condition is a string, it will be used literally, with question marks replaced by
	 * parameters as passed in the next method param. These params are escaped, but the base where condition is not
	 *
	 * @param String  $table The table name
	 * @param Array   $aColList array of strings of columns to be fetched
	 * @param String  $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array   $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array   $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 * @param Array   $orderBy , a list of columns to order the results
	 * @param Integer $offset , first line of result set to select
	 * @param Integer $lines , max number of lines to select
	 *
	 * @return mixed single value read from db
	 * @throw none (underlying database layer does throw errors)
	 */
	public static function selectAssoc(
		$table, $aColList = array( '*' ), $mWhere = '', $aWhereData = array(), $orderBy = array(), $offset = 0,
		$lines = 0
	) {

		$db = self::_setSelectQuery( $table, $aColList, $mWhere, $aWhereData, $orderBy, $offset, $lines );

		// if not in cache, run query
		$read = $db->loadAssoc();

		return $read;
	}

	/**
	 * Prepare, set and execute a select query, returning a an array of associative arrays
	 *
	 * usage:
	 *
	 * $result = ShlHelperDb::selectAssoc( '#__sh404sef_alias', array('alias', 'id'), array( 'nonsef' =>
	 * 'index.php?option=com_content&view=article&id=12')); will return an array of arrays with 2 keys, alias and id,
	 * where nonsef column is index.php?option=com_content&view=article&id=12
	 *
	 * $result = ShlHelperDb::selectAssoc( '#__sh404sef_alias', array('alias', 'id'), 'amount > 0 and amount < ?',
	 * array( '100')); If where condition is a string, it will be used literally, with question marks replaced by
	 * parameters as passed in the next method param. These params are escaped, but the base where condition is not
	 *
	 * @param String  $table The table name
	 * @param Array   $aColList array of strings of columns to be fetched
	 * @param String  $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array   $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array   $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 * @param Array   $orderBy , a list of columns to order the results
	 * @param Integer $offset , first line of result set to select
	 * @param Integer $lines , max number of lines to select
	 * @param string  $key a column name to index the returned array with
	 *
	 * @return mixed single value read from db
	 * @throw none (underlying database layer does throw errors)
	 */
	public static function selectAssocList(
		$table, $aColList = array( '*' ), $mWhere = '', $aWhereData = array(), $orderBy = array(), $offset = 0,
		$lines = 0, $key = ''
	) {

		$db = self::_setSelectQuery( $table, $aColList, $mWhere, $aWhereData, $orderBy, $offset, $lines );

		// if not in cache, run query
		$read = $db->loadAssocList( $key );

		return $read;
	}

	/**
	 * Prepare, set and execute a select query, returning a single object
	 *
	 * @param String  $table The table name
	 * @param Array   $aColList array of strings of columns to be fetched
	 * @param String  $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array   $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array   $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 * @param Array   $orderBy , a list of columns to order the results
	 * @param Integer $offset , first line of result set to select
	 * @param Integer $lines , max number of lines to select
	 *
	 * @return mixed single value read from db
	 * @throw none (underlying database layer does throw errors)
	 */
	public static function selectObject(
		$table, $aColList = array( '*' ), $mWhere = '', $aWhereData = array(), $orderBy = array(), $offset = 0,
		$lines = 0
	) {

		$db = self::_setSelectQuery( $table, $aColList, $mWhere, $aWhereData, $orderBy, $offset, $lines );

		// if not in cache, run query
		$read = $db->loadObject();

		return $read;
	}

	/**
	 * Prepare, set and execute a select query, returning a an object list
	 *
	 * @param String  $table The table name
	 * @param Array   $aColList array of strings of columns to be fetched
	 * @param String  $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array   $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array   $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 * @param Array   $orderBy , a list of columns to order the results
	 * @param Integer $offset , first line of result set to select
	 * @param Integer $lines , max number of lines to select
	 * @param string  $key a column name to index the returned array with
	 * @param string  $opType optional forced operation type for this operation
	 *
	 * @return mixed single value read from db
	 * @throw none (underlying database layer does throw errors)
	 */
	public static function selectObjectList(
		$table, $aColList = array( '*' ), $mWhere = '', $aWhereData = array(), $orderBy = array(), $offset = 0,
		$lines = 0, $key = ''
	) {

		// have db driver create the sql query
		$db = self::_setSelectQuery( $table, $aColList, $mWhere, $aWhereData, $orderBy, $offset, $lines );

		// if not in cache, run query
		$read = $db->loadObjectList( $key );

		return $read;
	}

	/**
	 * Prepare, set and execute a count query
	 *
	 * @param String $table The table name
	 * @param String $column optional column to be counted (defaults to *)
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 *
	 * @return object the db object
	 */
	public static function count( $table, $column = '*', $mWhere = '', $aWhereData = array() ) {

		$db = self::getDb();

		// have db driver create the sql query
		$db->setCountQuery( $table, $column, $mWhere, $aWhereData );

		// if not in cache, run query
		$read = $db->loadResult();
		$read = empty( $read ) ? 0 : $read;

		return $read;
	}

	/**
	 * Prepare, set and execute a delete query
	 *
	 * @param String $table The table name
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 *
	 * @return object the db object
	 */
	public static function delete( $table, $mWhere = '', $aWhereData = array() ) {

		$db = self::getDb();
		$db->setDeleteQuery( $table, $mWhere, $aWhereData )->execute();

		return $db;
	}

	/**
	 * Prepare, set and execute a delete query based on a
	 * list of column value
	 *
	 * @param String $table The table name
	 * @param String $mwhereColumn name of column to compare to list of values
	 * @param Array  $aWhereData List of column values that should be deleted
	 * @param        Integer if self::INTEGER, list will be 'intvaled', else quoted
	 *
	 * @return object the db object
	 */
	public static function deleteIn( $table, $mwhereColumn, $aWhereData, $type = self::STRING ) {

		if ( empty( $mwhereColumn ) || empty( $aWhereData ) ) {
			return;
		}

		// build a list of ids to read
		$wheres = $type == self::INTEGER ? self::arrayToIntvalList( $aWhereData ) : self::arrayToQuotedList( $aWhereData );

		// perform deletion
		$db = self::getDb();

		return self::delete( $table, $db->quoteName( $mwhereColumn ) . ' in (' . $wheres . ')' );
	}

	/**
	 * Prepare, set and execute and insert query
	 *
	 * @param String $table The table name
	 * @param Array  $aData array of values pairs ( ie 'columnName' => 'columnValue')
	 *
	 * @return object the db object
	 */
	public static function insert( $table, $aData ) {

		$db = self::getDb();
		$db->setInsertQuery( $table, $aData )->execute();

		return $db;
	}

	/**
	 * Prepare, set and execute an update query
	 *
	 * @param String $table The table name
	 * @param Array  $aData array of values pairs ( ie 'columnName' => 'columnValue')
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 *
	 * @return object the db object
	 */
	public static function update( $table, $aData, $mWhere = '', $aWhereData = array() ) {

		$db = self::getDb();
		$db->etUpdateQuery( $table, $aData, $mWhere, $aWhereData )->execute();

		return $db;
	}

	/**
	 * Prepare, set and execute an update query on a list
	 * of items
	 *
	 * @param String $table The table name
	 * @param Array  $aData array of values pairs ( ie 'columnName' => 'columnValue')
	 * @param String $mwhereColumn name of column to compare to list of values
	 * @param Array  $aWhereData List of column values that should be updated
	 * @param        Integer if self::INTEGER, list will be 'intvaled', else quoted
	 *
	 * @return object the db object
	 */
	public static function updateIn( $table, $aData, $mwhereColumn, $aWhereData, $type = self::STRING ) {

		if ( empty( $mwhereColumn ) || empty( $aWhereData ) ) {
			return;
		}

		// build a list of ids to read
		$wheres = $type == self::INTEGER ? self::arrayToIntvalList( $aWhereData ) : self::arrayToQuotedList( $aWhereData );

		// perform deletion
		$db = self::getDb();

		return self::update( $table, $aData, $db->quoteName( $mwhereColumn ) . ' in (' . $wheres . ')' );
	}

	/**
	 * Prepare, set and execute an insert or update query
	 *
	 * @param String $table The table name
	 * @param Array  $aData An array of field to be inserted in the db ('columnName' => 'columnValue')
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 *
	 * @return object the db object
	 */
	public static function insertUpdate( $table, $aData, $mWhere = '', $aWhereData = array() ) {

		$db = self::getDb();
		$db->setInsertUpdateQuery( $table, $aData, $mWhere, $aWhereData )->execute();

		return $db;
	}

	/**
	 * Prepare, set and execute a custom database query
	 *
	 * @param String $query A litteral sql query
	 * @param string $opType optional forced operation type for this operation
	 *
	 * @return object the db object
	 */
	public static function query( $query, $opType = '' ) {

		$db = self::setQuery( $query, $opType )->execute();

		return $db;
	}

	/**
	 * Set a custom database query, so that
	 * another method can be chained to execute it
	 *
	 * @param String $query A litteral sql query
	 *
	 * @return object the db object
	 */
	public static function setQuery( $query ) {

		$db = self::getDb();
		$db->setQuery( $query );

		return $db;
	}

	/**
	 *
	 * Prepare a query for running, quoting or name quoting some
	 * of its constituents
	 * ?? will be replaced with name quoted data from the $nameQuoted parameter
	 * ? will be replaced with quoted data from the $quoted parameter
	 *
	 * Example:
	 *   $query = 'select ?? from ?? where ?? <> ?'
	 *   with
	 *     $nameQuoted = array( 'id', '#__table', 'counter')
	 *     $quoted = array( 'test')
	 *
	 * will result in running
	 *
	 *   select `id` from `#__table` where `counter` <> 'test'
	 *
	 *
	 * @param string $query
	 * @param array  $nameQuoted
	 * @param array  $quoted
	 * @param string $namePlaceHolder
	 * @param string $dataPlaceHolder
	 *
	 * @return object the db object
	 */
	public static function quoteQuery( $query, $nameQuoted = array(), $quoted = array(), $namePlaceHolder = '??', $dataPlaceHolder = '?' ) {

		// get a db
		$db = self::getDb();

		// save query for error message
		$newQuery = $db->quoteQuery( $query, $nameQuoted, $quoted, $namePlaceHolder, $dataPlaceHolder );
		$db->setQuery( $newQuery );

		return $db;
	}

	/**
	 *
	 * Runs a query, after quoting or name quoting some
	 * of its constituents
	 * ?? will be replaced with name quoted data from the $nameQuoted parameter
	 * ? will be replaced with quoted data from the $quoted parameter
	 *
	 * Example:
	 *   $query = 'select ?? from ?? where ?? <> ?'
	 *   with
	 *     $nameQuoted = array( 'id', '#__table', 'counter')
	 *     $quoted = array( 'test')
	 *
	 * will result in running
	 *
	 *   select `id` from `#__table` where `counter` <> 'test'
	 *
	 *
	 * @param string $query
	 * @param array  $nameQuoted
	 * @param array  $quoted
	 * @param string $namePlaceHolder
	 * @param string $dataPlaceHolder
	 * @param string $opType optional forced operation type for this operation
	 *
	 * @return object the db object
	 */
	public static function runQuotedQuery( $query, $nameQuoted = array(), $quoted = array(), $namePlaceHolder = '??', $dataPlaceHolder = '?' ) {

		// get a db
		$db = self::getDb();

		// save query for error message
		$newQuery = $db->quoteQuery( $query, $nameQuoted, $quoted, $namePlaceHolder, $dataPlaceHolder );

		return self::query( $newQuery );
	}

	/**
	 *
	 * Asks db to name quote a string
	 *
	 * @param string $string
	 */
	public static function nameQuote( $string ) {

		$db = self::getDb();

		return $db->quoteName( $string );
	}

	/**
	 *
	 * Asks DB to quote a string
	 *
	 * @param string $string
	 */
	public static function quote( $string ) {

		$db = self::getDb();

		return $db->quote( $string );
	}

	/**
	 * Quote an array of value and turn it into a list
	 * of separated, name quoted elements
	 *
	 * @param array  $data
	 * @param string $glue
	 *
	 * @return string
	 */
	public static function arrayToNameQuotedList( $data, $glue = ',' ) {

		return self::_arrayToQuotedList( $data, $nameQuote = true, $glue );
	}

	/**
	 * Quote an array of value and turn it into a list
	 * of separated, quoted elements
	 *
	 * @param array  $data
	 * @param string $glue
	 *
	 * @return string
	 */
	public static function arrayToQuotedList( $data, $glue = ',' ) {

		return self::_arrayToQuotedList( $data, $nameQuote = false, $glue );
	}

	/**
	 * Intval an array of value and turn it into a list
	 * of separated, quoted elements
	 *
	 * @param array  $data
	 * @param string $glue
	 *
	 * @return string
	 */
	public static function arrayToIntvalList( $data, $glue = ',' ) {

		$list = '';
		if ( empty( $data ) || ! is_array( $data ) ) {
			return $list;
		}

		$values = array();
		foreach ( $data as $value ) {
			$values[] = (int) $value;
		}

		$list = implode( $glue, $values );

		return $list;
	}

	protected static function _setSelectQuery(
		$table, $aColList = array( '*' ), $mWhere = '', $aWhereData = array(), $orderBy = array(), $offset = 0,
		$lines = 0
	) {

		$db = self::getDb();
		$db->setSelectQuery( $table, $aColList, $mWhere, $aWhereData, $orderBy, $offset, $lines );

		return $db;
	}

	/**
	 * Quote an array of value and turn it into a list
	 * of separated, quoted elements
	 *
	 * @param array   $data
	 * @param boolean $nameQuote if true, data is namedQuoted, otherwise Quoted
	 * @param string  $glue
	 *
	 * @return string
	 */
	private static function _arrayToQuotedList( $data, $nameQuote = false, $glue = ',' ) {

		$list = '';
		if ( empty( $data ) || ! is_array( $data ) ) {
			return $list;
		}

		$db     = self::getInstance();
		$values = array();
		foreach ( $data as $value ) {
			$values[] = $nameQuote ? $db->quoteName( $value ) : $db->quote( $value );
		}

		$list = implode( $glue, $values );

		return $list;
	}
}
