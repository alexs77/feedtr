<?php
/* ===============================================================================
 * Subversion information:
 * $LastChangedDate: 2008-09-07 20:21:43 +0200 (So, 07 Sep 2008) $
 * $LastChangedRevision: 64 $
 * $LastChangedBy: a.skwar $
 * $HeadURL: https://pas3.googlecode.com/svn/trunk/paras3/src/paras3.py $
 * $Id: paras3.py 64 2008-09-07 18:21:43Z a.skwar $
 * ===============================================================================
 */

	/**
	 * @copyright Copyright (C) 2005 by Christian Land / tagdocs
	 * @author Christian Land
	 * @link http://www.tagdocs.biz
	 * @package tdBoard
	 */

	/**
	 * This class handles all MySQL-database operations, using the new MySQLi-Interface.
	 *
	 * @author Christian Land
	 * @package tinyDB
	 * @version v1.0.0 - first release
	 * @version v1.0.1 - added debug-routines, added query-counter
	 * @version v1.0.2 - changed "tinyDB::"-calls to "parent::"-calls, added Query-Log
	 * @version v1.0.3 - added getTableList
	 * @version v1.0.4 - added numFields, getFields - replaced _isExtensionThere calls with extension_loaded()
	 * @version v1.1.0 - changed constructor ($dbType removed), some minor code-changes, changed connect&pconnect to use the $this->_db* Variables to connect
	 */

	class tinyMySQLi extends tinyDB
	{

		/**
		 * Constructor
		 *
		 * @author Christian Land
		 * @param integer $resultType Default-Result type.
		 * @return void
		 */

		function tinyMySQLi($resultType=DB_UNKNOWN)
		{
    			parent::tinyDB($resultType);
    			$this->_dbType	= 'mysqli';			// overwrite 'tinyDB'-dbType with subclass-dbType
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

			parent::connect($dbhost, $dbname, $dbuser, $dbpassword);	// MUST be called to fill $this->_db**** variables !!!

			if (extension_loaded('mysqli')===true)
			{

    				if ($this->_dbPort>0)
					$this->_dbHandle	= @mysqli_connect($this->_dbHost, $this->_dbUsername, $this->_dbPassword, '', $this->_dbPort);
    				else
					$this->_dbHandle	= @mysqli_connect($this->_dbHost, $this->_dbUsername, $this->_dbPassword);

				if ($this->_dbHandle)
				{
					if (@mysqli_select_db($this->_dbHandle,$this->_dbName))
						return true;
					else
					{
						if ($this->_debug)
							error_log(date('[d.m.Y H:i:s]').' - Couldn\'t select database. Databasename: ['.$dbname.']'."\n",3,$this->_logfile);

						return false;
					}
				}
				else
				{
					if ($this->_debug)
						error_log(date('[d.m.Y H:i:s]').' - Couldn\'t connect to database'."\n",3,$this->_logfile);

					return false;
				}

		}
			else
			{
				if ($this->_debug)
					error_log(date('[d.m.Y H:i:s]').' - MySQLi-Extension not found'."\n",3,$this->_logfile);

				return false;
			}

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

			parent::pconnect($dbhost, $dbname, $dbuser, $dbpassword);	// MUST be called to fill $this->_db**** variables !!!

			if (extension_loaded('mysqli')===true)
			{

    				if ($this->_dbPort>0)
					$this->_dbHandle	= @mysqli_pconnect($this->_dbHost, $this->_dbUsername, $this->_dbPassword, '', $this->_dbPort);
    				else
					$this->_dbHandle	= @mysqli_pconnect($this->_dbHost, $this->_dbUsername, $this->_dbPassword);

				if ($this->_dbHandle)
				{
					@mysqli_select_db($this->_dbHandle,$this->_dbName);
					return true;
				}
				else
				{
					if ($this->_debug)
						error_log(date('[d.m.Y H:i:s]').' - Couldn\'t pconnect to database'."\n",3,$this->_logfile);
					return false;
				}

			}
			else
			{
				if ($this->_debug)
					error_log(date('[d.m.Y H:i:s]').' - MySQLi-Extension not found'."\n",3,$this->_logfile);
				return false;
			}

		}

		/**
		 * <i>disconnect</i> closes the current connection.
		 *
 		 * @author Christian Land
		 * @return boolean
		 */

		function disconnect()
		{
			if (!is_null($this->_dbHandle))
    			{
				@mysqli_close($this->_dbHandle);
        			parent::disconnect();
        			return true;
    			}

			return false;
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
			parent::query($query);

			if (!is_null($this->_dbHandle))
    			{
        			$this->_dbResult						= @mysqli_query($this->_dbHandle,$query);
				$this->_dbQueryLog[count($this->_dbQueryLog)-1]['duration']	= ($this->_debugGetTime()-$this->_curQueryStart);
				$this->_dbCurQueryEnd						= $this->_debugGetTime();
				if ($this->_dbResult)
				{
					$this->_dbQueryCount++;
					return true;
				}
				else
				{
					$this->_dbError	= @mysqli_error($this->_dbHandle);
					$this->_dbQueryLog[count($this->_dbQueryLog)-1]['error']	= $this->_dbError;

					if ($this->_debug)
						error_log(date('[d.m.Y H:i:s]').' - Couldn\'t query database. SQL: ['.preg_replace('/\s{2,}/',' ',preg_replace('/[\n\r\s]/', ' ', trim($query))).'] Error: ['.$this->_dbError.']'."\n",3,$this->_logfile);

					return false;
				}
			}

			return false;
		}

		/**
		 * Performs multiple database-queries. Returns TRUE if the queries could be executed, otherwise FALSE.
		 *
 		 * @author Christian Land
		 * @param array $sqlqueries Array with SQL-Commands
		 * @param bool $ignoreerrors If ignoreerrors = false, the function stops as soon as an error occurs
		 * @return boolean
		 */

		function queries($sqlqueries=null,$ignoreerrors=true)
		{
			if ((!is_null($this->_dbHandle)) && is_array($sqlqueries) && (count($sqlqueries)>0))
			{
				$errorcount	= 0;
				foreach ($sqlqueries as $sqlquery)
				{
					$result	= $this->query($sqlquery);
					if (($result===false) && !$ignoreerrors)
						return false;
					elseif ($result===false)
						$errorcount++;
				}
				return ($errorcount == 0);
			}

			return false;
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

			parent::nextResult();

			if ($resultType == DB_UNKNOWN)
			{
				if ($this->_dbResType != DB_UNKNOWN)
					$resultType	= $this->_dbResType;
				else
					$resultType	= DB_DEFAULT_ARRAY;
			}

			if (!is_null($this->_dbResult))
			{
				// $this->_dbQueryCount++;
				switch ($resultType)
				{
					case DB_BOTH_ARRAYS:
						return @mysqli_fetch_array($this->_dbResult, MYSQLI_BOTH);
						break;
					case DB_ASSOC_ARRAY:
						return @mysqli_fetch_array($this->_dbResult, MYSQLI_ASSOC);
						break;
					case DB_NUMERIC_ARRAY:
						return @mysqli_fetch_array($this->_dbResult, MYSQLI_NUM);
						break;
				}
			}
			else
			{
				if ($this->_debug)
					error_log(date('[d.m.Y H:i:s]').' - Couldn\'t perform "nextResult". Last SQL-Query: ['.preg_replace('/\s{2,}/',' ',preg_replace('/[\n\r\s]/', ' ', trim($this->_dbLastQuery))).']'."\n",3,$this->_logfile);

				return false;
			}

		}

		/**
		 * <b>freeResults</b> will free all memory associated with the current result handle.
		 *
		 * @author Christian Land
		 * @return boolean
		 */

		function freeResults()
		{
			if (!is_null($this->_dbResult))
				return @mysqli_free_result($this->_dbResult);
			else
			{
				if ($this->_debug)
					error_log(date('[d.m.Y H:i:s]').' - Trying to call "freeResults" with $this->_dbResult=Null'."\n",3,$this->_logfile);
			}

		}

		/**
		 * <b>numRows</b> returns the number of rows in a result set.
		 *
		 * @author Christian Land
		 * @return integer
		 */

		function numRows()
		{

			if (!is_null($this->_dbResult))
				return @mysqli_num_rows($this->_dbResult);
			else
			{
				if ($this->_debug)
					error_log(date('[d.m.Y H:i:s]').' - Trying to call "numRows" with $this->_dbResult=Null'."\n",3,$this->_logfile);

				return false;
			}

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

			if (!is_null($this->_dbResult))
				return @mysqli_num_fields($this->_dbResult);
			else
			{
				if ($this->_debug)
					error_log(date('[d.m.Y H:i:s]').' - Trying to call "numFields" with $this->_dbResult=Null'."\n",3,$this->_logfile);

				return false;
			}

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

			if (!is_null($this->_dbResult))
			{

				$fieldcount	= @mysqli_num_fields($this->_dbResult);
				$result		= array();

				for ($i=0;$i<$fieldcount;$i++)
				{
					$thisfield	= mysqli_fetch_field($this->_dbResult, $i);
					$result[]	= array(
								'name'		=> $thisfield->name,
								'table'		=> $thisfield->table,
								'def'		=> $thisfield->def,
								'max_length'	=> $thisfield->max_length,
								'not_null'	=> $thisfield->not_null,
								'primary_key'	=> $thisfield->primary_key,
								'multiple_key'	=> $thisfield->multiple_key,
								'unique_key'	=> $thisfield->unique_key,
								'numeric'	=> $thisfield->numeric,
								'blob'		=> $thisfield->blob,
								'type'		=> $thisfield->type,
								'unsigned'	=> $thisfield->unsigned,
								'zerofill'	=> $thisfield->zerofill,
								);

				}

				return ((count($result)>0) ? $result : false);

			}
			else
			{
				if ($this->_debug)
					error_log(date('[d.m.Y H:i:s]').' - Trying to call "getFields" with $this->_dbResult=Null'."\n",3,$this->_logfile);

				return false;
			}

		}

		/**
		 * Get number of affected rows in previous MySQLi operation
		 *
		 * @author Christian Land
		 * @return integer
		 */

		function affectedRows()
		{

			if (!is_null($this->_dbHandle))
				return @mysqli_affected_rows($this->_dbHandle);
			else
			{
				if ($this->_debug)
					error_log(date('[d.m.Y H:i:s]').' - Trying to call "affectedRows" with $this->_dbResult=Null'."\n",3,$this->_logfile);

				return false;
			}

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

			$sqlquery	= 'SHOW TABLES FROM '.$this->_dbName;

			if (!is_null($this->_dbHandle))
			{
				$dummy	= @mysqli_query($this->_dbHandle,$sqlquery) or die(mysqli_error());
				$this->_dbQueryCount++;

				if ($dummy)
				{
					$result	= array();
					while ($data = @mysqli_fetch_array($dummy, MYSQLI_NUM))
					{
						$result[]	= $data[0];
					}
					asort($result);
					return $result;
				}
			}

			return false;

		}

		/**
		 * Gets the ID of the last INSERT Statement
		 *
		 * @author Christian Land
		 * @return integer
		 */

		function getInsertID()
		{
			if (!is_null($this->_dbHandle))
				return @mysqli_insert_id($this->_dbHandle);
			return false;
		}

	}

?>
