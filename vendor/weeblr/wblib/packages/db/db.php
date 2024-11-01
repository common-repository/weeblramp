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

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

class WblDb {

	private $sql = '';
	private $db  = null;

	/**
	 * Get a hold on WP db object
	 */
	public function __construct( $db ) {

		$this->db = $db;
	}

	/**
	 * Quote the name of a table, replacing the prefix placeholder
	 * by its actual value
	 *
	 * @param string $tableName
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function quoteTable( $tableName, $prefix = '#__' ) {

		return $this->quoteName( str_replace( $prefix, $this->db->prefix, $tableName ) );
	}

	/**
	 * Quote a column name (mysql)
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public function quotename( $name ) {

		return '`' . $name . '`';
	}

	/**
	 * Quote and by default also escape
	 * a value (mysql)
	 *
	 * @param string|array $value
	 * @param bool         $escape
	 *
	 * @return string
	 */
	public function quote( $value, $escape = true ) {

		return '\'' . ( $escape ? $this->escape( $value ) : $value ) . '\'';
	}

	/**
	 * Escape a value, using database connection
	 *
	 * @param string | array $text
	 * @param bool           $extra
	 *
	 * @return array|string
	 */
	public function escape( $text, $extra = false ) {

		$result = $this->db->_escape( $text );

		if ( $extra ) {
			$result = addcslashes( $result, '%_' );
		}

		return $result;
	}

	/**
	 * Stores a (valid and complete) sql query, to
	 * be executed later
	 *
	 * @param $query
	 */
	public function setQuery( $query ) {

		$this->sql = $query;
	}

	/**
	 * Execute a query stored with setQuery()
	 */
	public function execute() {

		if ( empty( $this->sql ) ) {
			wbThrow( new InvalidArgumentException( 'wbLib: trying to execute an empty query' ) );
		}

		$result = $this->db->query( $this->sql );

		$this->checkError();

		return $result;
	}

	/**
	 *
	 * Prepare a query, quoting or name quoting some
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
	 * will return
	 *
	 *   select `id` from `#__table` where `counter` <> 'test'
	 *
	 *
	 * @param string $query
	 * @param array  $nameQuoted
	 * @param array  $quoted
	 * @param string $namePlaceHolder
	 * @param string $dataPlaceHolder
	 */
	public function quoteQuery( $query, $nameQuoted = array(), $quoted = array(), $namePlaceHolder = '??', $dataPlaceHolder = '?' ) {

		$newQuery = '';

		// name quoting
		if ( ! empty( $nameQuoted ) ) {
			// find placeholders
			$sqlBits = explode( $namePlaceHolder, $query );
			$i       = 0;
			// replace each place holder by the matching value
			foreach ( $nameQuoted as $data ) {
				$newQuery .= $sqlBits[ $i ];
				$newQuery .= $this->quoteName( $data );
				$i        += 1;
			}
			if ( isset( $sqlBits[ $i ] ) ) {
				$newQuery .= $sqlBits[ $i ];
			}
		}

		if ( strpos( $newQuery, $namePlaceHolder ) !== false ) {
			wbThrow( new RuntimeException( __METHOD__ . ':' . __LINE__ . ': ' . 'Invalid db query sent to queryQuote helper: ' . $query . '. Maybe missing some data.' ) );
		}

		// name quoting
		if ( ! empty( $quoted ) ) {
			// find placeholders
			$sqlBits  = explode( $dataPlaceHolder, $newQuery );
			$newQuery = '';
			$i        = 0;
			// replace each place holder by the matching value
			foreach ( $quoted as $data ) {
				$newQuery .= $sqlBits[ $i ];
				$newQuery .= $this->_prepareData( $data );
				$i        += 1;
			}
			if ( isset( $sqlBits[ $i ] ) ) {
				$newQuery .= $sqlBits[ $i ];
			}
		}

		return $newQuery;
	}

	/**
	 * Prepare and set a query against the db object
	 *
	 * @param String $table The table name
	 * @param Array  $aData An array of field to be inserted in the db ('columnName' => 'columnValue')
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 */
	public function setInsertUpdateQuery( $table, $aData, $mWhere = '', $aWhereData = array() ) {

		if ( $this->isRecord( $table, $mWhere, $aWhereData ) ) {
			// update it
			$this->setUpdateQuery( $table, $aData, $mWhere, $aWhereData );
		} else {
			// or insert it
			$this->setInsertQuery( $table, $aData );
		}

		return $this;
	}

	/**
	 * Prepare and set a SELECT query against the db
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
	 */
	public function setSelectQuery(
		$table, $aColList = array( '*' ), $mWhere = '', $aWhereData = array(), $orderBy = array(), $offset = 0,
		$lines = 0
	) {

		// sanitize
		$aColList = empty( $aColList ) ? array( '*' ) : $aColList;
		$aColList = is_string( $aColList ) ? array( $aColList ) : $aColList;

		// which columns to fetch ?
		$quotedColList = array();
		foreach ( $aColList as $columnName ) {
			$quotedColList[] = $columnName == '*' ? '*' : $this->quoteName( $columnName );
		}
		$columns = implode( ', ', $quotedColList );

		// where to look for
		$where = $this->_buildWhereClause( $mWhere, $aWhereData );

		// order by clause
		$orderByClause = $this->_buildOrderByClause( $orderBy );

		// lines limit clause
		$limitClause = $this->_buildLimitClause( $offset, $lines );

		// set up the query
		$this->setQuery( 'SELECT ' . $columns . ' FROM ' . $this->quoteTable( $table ) . $where . $orderByClause . $limitClause . ';' );

		return $this;
	} // end of setSelectQuery

	/**
	 * Prepare and set a select/count query against the db
	 *
	 * @param String $table The table name
	 * @param String $column an optional column to be counter
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 */
	public function setCountQuery( $table, $column = '*', $mWhere = '', $aWhereData = array() ) {

		// sanitize
		$column = empty( $column ) || $column == '*' ? '*' : $this->quoteName( $column );

		// where to look for
		$where = $this->_buildWhereClause( $mWhere, $aWhereData );

		// set up the query
		$this->setQuery( 'SELECT count(' . $column . ') FROM ' . $this->quoteTable( $table ) . $where . ';' );

		return $this;
	} // end of setSelectQuery

	/**
	 * Prepare and set an UPDATE query against the db
	 *
	 * @param String $table The table name
	 * @param Array  $aData array of values pairs ( ie 'columnName' => 'columnValue')
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 */
	public function setUpdateQuery( $table, $aData, $mWhere = '', $aWhereData = array() ) {

		// which columns to set ?
		$set = '';
		if ( ! empty( $aData ) ) {
			foreach ( $aData as $columnName => $columnValue ) {
				$set .= ', ' . $this->quoteName( $columnName ) . '=' . $this->_prepareData( $columnValue );
			}
			// remove leading ', '
			$set = substr( $set, 2 );
		}

		// check result
		if ( empty( $set ) ) {
			return false;
		}

		// where to look for
		$where = $this->_buildWhereClause( $mWhere, $aWhereData );

		// set up the query
		$this->setQuery( 'UPDATE ' . $this->quoteTable( $table ) . ' SET ' . $set . $where . ';' );

		return $this;
	}

	/**
	 * Prepare and set an INSERT query against the db
	 *
	 * @param String $table The table name
	 * @param Array  $aData array of values pairs ( ie 'columnName' => 'columnValue')
	 */
	public function setInsertQuery( $table, $aData ) {

		// which columns to set ?
		$columns = '';
		$values  = '';
		if ( ! empty( $aData ) ) {
			foreach ( $aData as $columnName => $columnValue ) {
				$columns .= ', ' . $this->quoteName( $columnName );
				$values  .= ', ' . $this->_prepareData( $columnValue );
			}
			// remove leading ', '
			$columns = substr( $columns, 2 );
			$values  = substr( $values, 2 );
		}

		// set up the query
		$this->setQuery( 'INSERT INTO ' . $this->quoteTable( $table ) . ' (' . $columns . ') VALUES (' . $values . ');' );

		return $this;
	}

	/**
	 * Prepare and set a DELETE query against the db
	 *
	 * @param String $table The table name
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 */
	public function setDeleteQuery( $table, $mWhere = '', $aWhereData = array() ) {

		// where to look for
		$where = $this->_buildWhereClause( $mWhere, $aWhereData );

		// set up the query
		$this->setQuery( 'DELETE FROM ' . $this->quoteTable( $table ) . $where . ';' );

		return $this;
	}

	/**
	 * Returns true if a record exists matching 'where' condition
	 *
	 * @param String $table , the table to look into
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 */
	public function isRecord( $table, $mWhere = '', $aWhereData = array() ) {

		// where to look for
		$where = $this->_buildWhereClause( $mWhere, $aWhereData );

		if ( empty( $where ) ) {
			return false;
		}

		// set up the query and load result
		$this->setQuery( 'SELECT count(*) FROM ' . $this->quoteTable( $table ) . $where . ';' );
		$result = $this->loadResult();

		return ! empty( $result );
	}

	/**
	 * Returns true if a record exists with a given Id
	 *
	 * @param String  $table , the table to look into
	 * @param Integer $id , the id to look for
	 * @param String  $idName , default to 'id', the columns to look into, if not 'id'
	 */
	public function isRecordById( $table, $id, $idName = 'id' ) {

		$id = (int) $id;

		if ( empty( $id ) ) {
			return false;
		}

		// get db and look up record
		$this->setSelectQuery( $table, array( $idName ), array( $id ) );
		$result = $this->loadResult();

		return ! empty( $result );
	}

	/**
	 * Method to get the first row of the result set from the database query as an associative array
	 * of ['field_name' => 'row_value'].
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadAssoc() {

		$read = $this->db->get_row( $this->sql, ARRAY_A );
		$this->checkError();

		return $read;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an associative array
	 * of ['field_name' => 'row_value'].  The array of rows can optionally be keyed by a field name, but defaults to
	 * a sequential numeric array.
	 *
	 * NOTE: Chosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string $key The name of a field on which to key the result array.
	 * @param   string $column An optional column name. Instead of the whole row, only this column value will be in
	 * the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadAssocList( $key = null, $column = null ) {

		$reads = $this->db->get_results( $this->sql, ARRAY_A );
		$this->checkError();

		$output = array();
		if ( ! empty( $reads ) ) {
			if ( ! empty( $key ) && ! isset( $reads[0][ $key ] ) ) {
				wbThrow( new RuntimeException( 'wbLib: trying to index row list with non-existing key.' ) );
			}
			foreach ( $reads as $assocRow ) {
				if ( empty( $key ) ) {
					$output[] = $assocRow;
				} else {
					$output[ $assocRow[ $key ] ] = $assocRow;
				}
			}
		}

		return $output;
	}

	/**
	 * Method to get an array of values from the <var>$offset</var> field in each row of the result set from
	 * the database query.
	 *
	 * @param   integer $offset The row offset to use to build the result array.
	 *
	 * @return  mixed    The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadColumn( $offset = 0 ) {

		wbThrow( new RuntimeException( 'wbLib: not implemented' ) );
		//return $read;
	}

	/**
	 * Method to get the first row of the result set from the database query as an object.
	 *
	 * @param   string $class The class name to use for the returned row object.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadObject() {

		$read = $this->db->get_row( $this->sql );
		$this->checkError();

		return $read;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an object.  The array
	 * of objects can optionally be keyed by a field name, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string $key The name of a field on which to key the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadObjectList( $key = '' ) {

		$errorState = $this->db->hide_errors();
		$reads      = $this->db->get_results( $this->sql );
		$this->db->show_errors( $errorState );
		$this->checkError();

		$output = array();
		if ( ! empty( $reads ) ) {
			if ( ! empty( $key ) && ! isset( $reads[0]->{$key} ) ) {
				wbThrow( new RuntimeException( 'wbLib: trying to index object list with non-existing key.' ) );
			}
			foreach ( $reads as $objectRow ) {
				if ( empty( $key ) ) {
					$output[] = $objectRow;
				} else {
					$output[ $objectRow->{$key} ] = $objectRow;
				}
			}
		}

		return $output;
	}

	/**
	 * Method to get the first field of the first row of the result set from the database query.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadResult() {

		$errorState = $this->db->hide_errors();
		$read       = $this->db->get_var( $this->sql );
		$this->db->show_errors( $errorState );
		$this->checkError();

		return $read;
	}

	/**
	 * Method to get the first row of the result set from the database query as an array.  Columns are indexed
	 * numerically so the first column in the result set would be accessible via <var>$row[0]</var>, etc.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadRow() {

		$read = $this->db->get_row( $this->sql, ARRAY_N );
		$this->checkError();

		return $read;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an array.  The array
	 * of objects can optionally be keyed by a field offset, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string $key The name of a field on which to key the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadRowList( $key = null ) {

		$reads = $this->db->get_results( $this->sql, ARRAY_A );
		$this->checkError();
		$output = array();
		if ( ! empty( $reads ) ) {
			if ( ! empty( $key ) && ! isset( $reads[0][ $key ] ) ) {
				wbThrow( new RuntimeException( 'wbLib: trying to index row list with non-existing key.' ) );
			}
			foreach ( $reads as $assocRow ) {
				if ( empty( $key ) ) {
					$output[] = array_values( $assocRow );
				} else {
					$output[ $assocRow[ $key ] ] = array_values( $assocRow );
				}
			}
		}

		return $output;
	}

	/**
	 * Check if last db op generated an error. Called after each db op
	 *
	 * @throws RuntimeException
	 */
	private function checkError() {

		if ( ! empty( $this->db->last_error ) ) {
			wbThrow( new RuntimeException( 'wbLib: db error - ' . $this->db->last_error ) );
		}
	}

	/**
	 * Prepare data to be inserted in an sql statement
	 *
	 * @param mixed $data
	 */
	private function _prepareData( $data ) {

		// from Ron Baldwin <ron.baldwin#sourceprose.com>
		// Only quote string types
		$type = gettype( $data );
		if ( $type == 'string' ) {
			$ret = $this->quote( $data );
		} else if ( $type == 'double' ) {
			$ret = str_replace( ',', '.', $data ); // locales fix so 1.1 does not get converted to 1,1
		} else if ( $type == 'boolean' ) {
			$ret = $data ? '1' : '0';
		} else if ( $type == 'object' ) {
			if ( method_exists( $data, '__toString' ) ) {
				$ret = $this->quote( $data->__toString() );
			} else {
				$ret = $this->quote( (string) $data );
			}
		} else if ( $data === null ) {
			$ret = 'NULL';
		} else {
			$ret = $data;
		}

		return $ret;
	}

	/**
	 * Build a where clause
	 *
	 * @param String $mWhere Conditions. Taken as a litteral where clause ( WHERE `amount` > 100 ).
	 * @param Array  $mWhere ( ie 'columnName' => 'columnValue') : a where clause is created like so : WHERE
	 *     `columnName` = 'columnValue'. columnValue is escaped before being used
	 * @param Array  $aWhereData Used only if $aWhere is a string. In such case, '?' place holders will be replaced by
	 *     this array values, escaped
	 */
	private function _buildWhereClause( $mWhere = '', $aWhereData = array() ) {

		// where clause
		if ( is_string( $mWhere ) ) {
			// litteral clause, find ? place holders
			if ( ! is_array( $aWhereData ) ) {
				$aWhereData = array( $aWhereData );
			}
			$holderCount = substr_count( $mWhere, '?' );
			if ( $holderCount > 0 && ! empty( $aWhereData ) && $holderCount != count( $aWhereData ) ) {
				// the number of ? placehlders does not match the data array passed
				wbThrow(
					new RuntimeException(
						__METHOD__ . ':' . __LINE__ . ': ' . 'Internal error: trying to build invalid db query where clause [ ' . json_encode( $mWhere ) . ' ] [ '
						. json_encode( $aWhereData ) . ' ]', 500
					)
				);
			} else {
				// we have ? placeholders and their number equals that of data passed
				$where = '';

				// find placeholders
				if ( empty( $aWhereData ) ) {
					$where = $mWhere;
				} else {
					$sqlBits = explode( '?', $mWhere );
					$i       = 0;
					// replace each place holder by the matching value
					foreach ( $aWhereData as $data ) {
						$where .= $sqlBits[ $i ];
						$where .= $this->_prepareData( $data );
						$i     += 1;
					}
					if ( isset( $sqlBits[ $i ] ) ) {
						$where .= $sqlBits[ $i ];
					}
				}
			}
		} elseif ( is_array( $mWhere ) ) {
			// an array of columns/values, we must turn into a where clause
			$where = '';
			foreach ( $mWhere as $columns => $value ) {
				$where .= ' AND ' . $this->quoteName( $columns ) . '=' . $this->_prepareData( $value );
			}
			// remove initial AND
			$where = substr( $where, 5 );
		} else {
			$where = '';
		}

		return empty( $where ) ? '' : ' WHERE ' . $where;
	}

	/**
	 * Builds an ORDER BY sql statement
	 *
	 * $orderBy = 'title';
	 * $orderBy = array( 'extension' => '', 'title' => 'desc');
	 * $orderBy = array( 'extension', 'title');
	 *
	 * @param String $orderBy name of unique column to sort with, direction is always ASC
	 * @param Array  $orderBy a list of column names to order by, direction is always asc
	 * @param Array  $orderBy a list of key => values, where key is a column name, and value is either '', 'asc' or
	 *     'desc'
	 */
	private function _buildOrderByClause( $orderBy ) {

		if ( empty( $orderBy ) ) {
			return '';
		}

		$clause = '';

		// 1: $orderBy is a string
		if ( is_string( $orderBy ) ) {
			$clause = $this->quoteName( $orderBy );
		} else if ( is_array( $orderBy ) ) {
			foreach ( $orderBy as $key => $value ) {
				if ( is_int( $key ) ) {
					// 2 : $orderBy is an array of strings
					// use directly, always with no direction
					$clause .= ', ' . $this->quoteName( (string) $value );
				} else {
					// 3 : $orderBy is an array of column names with direction
					$clause .= ', ' . $this->quoteName( $key ) . ( empty( $value ) ? '' : $this->escape( $value ) );
				}
			}
			$clause = empty( $clause ) ? '' : substr( $clause, 2 );
		}

		// put everything together
		$clause = empty( $clause ) ? '' : ' ORDER BY ' . $clause;

		return $clause;
	}

	/**
	 * Builds a LIMIT sql statement
	 *
	 * @param Integer $offset , the line in result set to start with
	 * @param Integer $lines , the max number of lines in result set to return
	 */
	private function _buildLimitClause( $offset, $lines ) {

		if ( empty( $offset ) && empty( $lines ) ) {
			return '';
		}

		$clause = ' LIMIT ';
		if ( ! empty( $offset ) ) {
			$clause .= $this->_prepareData( $offset );
		}
		if ( ! empty( $lines ) ) {
			$clause .= ( empty( $offset ) ? '' : ', ' ) . $this->_prepareData( $lines );
		}

		return $clause;
	}
}
