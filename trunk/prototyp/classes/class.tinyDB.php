<?php

	/**
	 * @copyright Copyright (C) 2003,2004,2005 by Christian Land / tagdocs
	 * @author Christian Land
	 * @link http://www.tagdocs.biz
	 * @package tdBoard
	 */

	/**
	 * Used in several functions to decide if the user passed one of the DB_* constants while fetching results or if the DB_DEFAULT_ARRAY constant should be used instead.
	 */
	define('DB_UNKNOWN',		0);

	/**
	 * Columns are returned into the array having the fieldname as the array index.
	 */
	define('DB_ASSOC_ARRAY',	1);

	/**
	 * Columns are returned into the array having a numerical index to the fields. This index starts with 0, the first field in the result.
	 */
	define('DB_NUMERIC_ARRAY',	2);

	/**
	 * Columns are returned into the array having both a numerical index and the fieldname as the array index.
	 */
	define('DB_BOTH_ARRAYS',	3);

	/**
	 * Default fetch-mode.
	 */
	define('DB_DEFAULT_ARRAY',	DB_ASSOC_ARRAY);

	/**
	 * This class is the base-class for all tinyDB database-classes. It mostly handles the result-type
	 * management and defines the prototypes of all methods.
	 *
	 * @copyright Copyright (C) 2005, Christian Land / tagdocs
	 * @author Christian Land
	 * @link http://www.tagdocs.biz
	 * @package tinyDB
	 * @version v1.0.0 - first release
	 * @version 1.0.1 - added debug-routines, added query-counter
	 * @version 1.0.2 - added Query-Log
	 * @version 1.0.3 - added getResultType(), setResultType(), getTableList() - changed connect/pconnect to store connection-infos
	 * @version 1.0.4 - added numFields() & getFields(), removed _isExtensionThere()-function (replaced by direct calls to extension_loaded())
	 * @version 1.1.0 - changed constructor ($dbType removed), some minor code changes, added clearQueryLog(),getDatabaseUsername(),getDatabasePassword(),getDatabaseHost(),getDatabasePort(),getDatabaseName(),getQueryLog(), removed freeResults()-call from query-function
	 * @version 1.1.1 - fixed minor Bug in pconnect()
	 */

	class tinyDB
	{

		/**
		 * Internal database handle
		 *
		 * @var integer
		 */
		var $_dbHandle		= null;

		/**
		 * Internal handle for databse-results.
		 *
		 * @var integer
		 */
		var $_dbResult		= null;

		/**
		 * The last SQL-Query tinyDB performed.
		 *
		 * @var string
		 */
		var $_dbLastQuery	= '';

		/**
		 * An array with all SQL-Queries tinyDB performed.
		 *
		 * @var array
		 */
		var $_dbQueryLog	= array();

		/**
		 * Start-Time of the current/last query.
		 *
		 * @var float
		 */
		var $_dbCurQueryStart	= 0;

		/**
		 * End-Time of the current/last query.
		 *
		 * @var float
		 */
		var $_dbCurQueryEnd	= 0;

		/**
		 * This string contains the text of the last database-error.
		 *
		 * @var string
		 */
		var $_dbError		= '';

		/**
		 * A string containing the identifier of the database-type for which the class was written.
		 * ('mysql' for class.tdb.mysql.php, 'pgsql' for class.tdb.pgsql.php, ...)
		 *
		 * @var string
		 */
		var $_dbType		= '';

		/**
		 * If you define a result-type when creating a tinyDB-object, this variable stores the result type
		 * you defined (DB_ASSOC_ARRAY, DB_NUMERIC_ARRAY, ...)
		 *
		 * @var integer
		 */
		var $_dbResType		= -1;

		/**
		 * If 'true', tinyDB logs all errors in a file.
		 *
		 * @var boolean
		 * @since v1.0.1
		 */
		 var $_debug		= false;

		/**
		 * A string containing the filename of the log-file.
		 *
		 * @var string
		 * @since v1.0.1
		 */
		 var $_logfile		= '';

		/**
		 * This variable stores the number of performed queries.
		 *
		 * @var integer
		 * @since v1.0.1
		 */
		var $_dbQueryCount	= 0;

		/**
		 * This variable stores the port, we're trying to connect to.
		 *
		 * @var integer
		 */
		var $_dbPort		= -1;

		/**
		 * This variable stores the name of the database.
		 *
		 * @var string
		 * @since v1.0.3
		 */
		var $_dbName		= '';

		/**
		 * This variable stores the hostname of the server.
		 *
		 * @var string
		 * @since v1.0.3
		 */
		var $_dbHost		= '';

		/**
		 * This variable stores the username which is used to connect to the database.
		 *
		 * @var string
		 * @since v1.0.3
		 */
		var $_dbUsername	= '';

		/**
		 * This variable stores the password which is used to connect to the database.
		 *
		 * @var string
		 * @since v1.0.3
		 */
		var $_dbPassword	= '';

		/**
		 * <i>Constructor</i>
		 *
		 * @author Christian Land
		 * @param string $dbType String containing the class-identifier.
		 * @param integer $resultType Default-Result type.
		 * @return void
		 */

		function tinyDB($resultType=DB_UNKNOWN)
		{
			$this->_dbType		= 'tinyDB';
			$this->_dbResType	= $resultType;
		}

		/**
		 * By using <i>setDebugMode</i> you can activate the tinyDB error-logging.
		 *
		 * @author Christian Land
		 * @param boolean $debug_active If 'true', tinyDB will log all errors to the log-file.
		 * @param string $logfile Full path of the log-file.
		 * @return void
		 * @since v1.0.1
		 */

		function setDebugMode($debug_active=false,$logfile='/tmp/tinyDB.log')
		{
			if (($logfile && $debug_active) || (!$debug_active))
			{
				$this->_debug	= $debug_active;
				$this->_logfile	= $logfile;
			}
			else
				$this->_debug	= false;
		}

		/**
		 * <i>connect</i> creates a new connection to a database. Returns TRUE if a connection was established.
		 *
		 * @author Christian Land
		 * @param string $dbhost Database-Host. Can be a URL/IP-Adress/... - if you want to specify a special Port to connect to, use the following syntax: "hostname:port" (for example: localhost:1234)
		 * @param string $dbname Name of the Database to connect to.
		 * @param string $dbuser Name of the Database-User
		 * @param string $dbpassword Password of the Database-User
		 * @return boolean
		 */

		function connect($dbhost, $dbname, $dbuser, $dbpassword)
		{
			// check if a port was passed in $dbhost

			if (substr_count($dbhost,':')==1)
				list($dbhost, $dbport)	= split(':',$dbhost,2);
			else
				$dbport		= -1;

			// OK, fill variables

			$this->_dbPort		= (int)$dbport;
			$this->_dbName		= $dbname;
			$this->_dbHost		= $dbhost;
			$this->_dbUsername	= $dbuser;
			$this->_dbPassword	= $dbpassword;

			return true;
		}

		/**
		 * <i>pconnect</i> creates a new persistant connection to a database. Returns TRUE if a connection was established.
		 *
		 * @author Christian Land
		 * @param string $dbhost Database-Host. Can be a URL/IP-Adress/... - if you want to specify a special Port to connect to, use the following syntax: "hostname:port" (for example: localhost:1234)
		 * @param string $dbname Name of the Database to connect to.
		 * @param string $dbuser Name of the Database-User
		 * @param string $dbpassword Password of the Database-User
		 * @return boolean
		 */

		function pconnect($dbhost, $dbname, $dbuser, $dbpassword)
		{
			// check if a port was passed in $dbhost

			if (substr_count($dbhost,':')==1)
				list($dbhost, $dbport)	= split(':',$dbhost,2);
			else
				$dbport		= -1;

			// OK, fill variables

			$this->_dbPort		= (int)$dbport;
			$this->_dbName		= $dbname;
			$this->_dbHost		= $dbhost;
			$this->_dbUsername	= $dbuser;
			$this->_dbPassword	= $dbpassword;

			return true;
		}


		/**
		 * <i>disconnect</i> closes the current connection.
		 *
		 * @author Christian Land
 		 * @return boolean
		 */

		function disconnect()
		{
			$this->_dbHandle	= null;
			return true;
		}

		/**
		 * Performs a database-query. Returns TRUE if the query could be executed.
		 *
		 * @author Christian Land
 		 * @param string $query SQL-Command
		 * @return boolean
		 */

		function query($query)
		{
			$this->_curQueryStart		= $this->_debugGetTime();
			$this->_dbQueryLog[]['SQL']	= $query;
		}

		/**
		 * Gets the next result of a query. Returns an array with the data.
		 *
		 * @author Christian Land
		 * @param integer $resultType By passing one of the DB_*_ARRAY Constants you can define the type of the returned array.
		 * @return array
		 */

		function nextResult($resultType = DB_UNKNOWN)
		{
		}

		/**
		 * Alias for <i>nextResult</i>.
		 *
		 * @author Christian Land
		 * @param integer $resultType By passing one of the DB_*_ARRAY Constants you can define the type of the returned array.
		 * @return array
		 */

		function getResult($resultType = DB_UNKNOWN)
		{
			// Alias for nextResult
			return $this->nextResult($resultType);
		}

		/**
		 * Returns all Results of a query. <br /><br />For example: If you perform a "<b>SELECT * FROM users</b>" query, <i>getResults</i> would return an array with all fields and all record of the table "<b>users</b>"
		 *
		 * @author Christian Land
		 * @param integer $resultType By passing one of the DB_*_ARRAY Constants you can define the type of the returned array.
		 * @return array
		 */

		function getResults($resultType  = DB_UNKNOWN)
		{

			$resultset	= array();

			if ($resultType == DB_UNKNOWN)
			{
				if ($this->_dbResType != DB_UNKNOWN)
					$resultType	= $this->_dbResType;
				else
					$resultType	= DB_DEFAULT_ARRAY;
			}

			while ($curResult = $this->nextResult($resultType))
				array_push($resultset, $curResult);

			return $resultset;

		}

		/**
		 * <b>freeResults</b> will free all memory associated with the current result handle.
		 *
		 * @author Christian Land
		 * @return boolean
		 */

		function freeResults()
		{
			return true;
		}

		/**
		 * <b>numRows</b> returns the number of rows in a result set.
		 *
		 * @author Christian Land
		 * @return integer
		 */

		function numRows()
		{
		}

		/**
		 * <b>numFields</b> returns the number of fields in a result set.
		 *
		 * @author Christian Land
		 * @return integer
		 * @since v1.0.4
		 */

		function numFields()
		{
		}

		/**
		 * <b>getFields</b> returns an array with informations regarding all fields in a result set.
		 *
		 * @author Christian Land
		 * @return array
		 * @since v1.0.4
		 */

		function getFields()
		{
		}

		/**
		 * Get number of affected rows in previous operation
		 *
		 * @author Christian Land
		 * @return integer
		 */

		function affectedRows()
		{
		}

		/**
		 * Return the identifier of the class.
		 *
		 * @author Christian Land
		 * @return string
		 */

		function getDBType()
		{
			return $this->_dbType;
		}

		/**
		 * Return the last performed SQL-Query.
		 *
		 * @author Christian Land
		 * @return string
		 */

		function getLastQuery()
		{
			return $this->_dbQueryLog[count($this->_dbQueryLog)-1];
		}

		/**
		 * Returns the number of Queries that were performed.
		 *
		 * @author Christian Land
		 * @return integer
		 */

		function getQueryCount()
		{
			return count($this->_dbQueryLog);
		}

		/**
		 * Clears the Query-Log
		 *
		 * @author Christian Land
		 * @return integer
		 * @since v1.1.0
		 */

		function clearQueryLog()
		{
			$this->_dbQueryLog	= array();
		}

		/**
		 * Returns the Query-Log
		 *
		 * @author Christian Land
		 * @return array
		 * @since v1.1.0
		 */

		function getQueryLog()
		{
			return $this->_dbQueryLog;
		}


		/**
		 * Returns the contents of the _dbError Variable.
		 *
		 * @author Christian Land
		 * @return string
		 */

		function getError()
		{
			return $this->_dbError;
		}

		/**
		 * Returns the contents of the _dbName Variable.
		 *
		 * @author Christian Land
		 * @return string
		 * @since v1.1.0
		 */

		function getDatabaseName()
		{
			return $this->_dbName;
		}

		/**
		 * Returns the contents of the _dbPort Variable.
		 *
		 * @author Christian Land
		 * @return integer
		 * @since v1.1.0
		 */

		function getDatabasePort()
		{
			return (int)$this->_dbPort;
		}

		/**
		 * Returns the contents of the _dbHost Variable.
		 *
		 * @author Christian Land
		 * @return string
		 * @since v1.1.0
		 */

		function getDatabaseHost()
		{
			return $this->_dbHost;
		}

		/**
		 * Returns the contents of the _dbUsername Variable.
		 *
		 * @author Christian Land
		 * @return string
		 * @since v1.1.0
		 */

		function getDatabaseUsername()
		{
			return $this->_dbUsername;
		}

		/**
		 * Returns the contents of the _dbPassword Variable.
		 *
		 * @author Christian Land
		 * @return string
		 * @since v1.1.0
		 */

		function getDatabasePassword()
		{
			return $this->_dbPassword;
		}

		/**
		 * Returns a list of all Tables in the current database
		 *
		 * @author Christian Land
		 * @return array
		 * @since v1.0.3
		 */

		function getTableList()
		{
			return false;
		}

		/**
		 * Gets the ID of the last INSERT Statement
		 *
		 * @author Christian Land
		 * @return array
		 */

		function getInsertID()
		{
		}

		/**
		 * Returns the current Result-Type
		 *
		 * @author Christian Land
		 * @return integer
		 * @since v1.0.3
		 */

		function getResultType()
		{
			return (int)$this->_dbResType;
		}

		/**
		 * Sets the Result-Type
		 *
		 * @author Christian Land
		 * @param integer $mode By passing one of the DB_*_ARRAY Constants you can define the type of the returned array.
		 * @return void
		 * @since v1.0.3
		 */

		function setResultType($mode=DB_DEFAULT_ARRAY)
		{
			$this->_dbResType	= (int)$mode;
		}

		/**
		 * Returns the duration of ALL queries in the Log
		 *
		 * @author Christian Land
		 * @return float
		 */

		function getQueryTimeTotal()
		{
			$duration	= 0;
			foreach($this->_dbQueryLog as $curdata)
				$duration	= $duration+$curdata['duration'];
			return (float)$duration;
		}

		/**
		 * This function returns the current time
		 *
		 * @author Christian Land
		 * @return float
		 */

		function _debugGetTime()
		{
			list($usec, $sec)		= explode(' ',microtime());
			return ((float)$usec + (float)$sec);
		}

	}

?>