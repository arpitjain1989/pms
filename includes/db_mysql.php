<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998-2000 NetUSE AG
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id: db_mysql.inc,v 1.2 2000/07/12 18:22:34 kk Exp $
 *
 */

class DB_Sql {

  /* public: connection parameters */
  var $Host     = "localhost";
  //var $Database = "pms_test_server";
  var $Database = "db_pms1";
  var $User     = "root";
  var $Password = "";

  /* public: configuration parameters */
  var $Auto_Free     = 0;     ## Set to 1 for automatic mysql_free_result()
  var $Debug         = 0;     ## Set to 1 for debugging messages.
  var $Halt_On_Error = "yes"; ## "yes" (halt with message), "no" (ignore errors quietly), "report" (ignore errror, but spit a warning)
  var $Seq_Table     = "db_sequence";

  /* public: result array and current row number */
  var $Record   = array();
  var $Row;

  /* public: current error number and error text */
  var $Errno    = 0;
  var $Error    = "";

  /* public: this is an api revision, not a CVS revision. */
  var $type     = "mysql";
  var $revision = "1.2";

  /* private: link and query handles */
  var $Link_ID  = 0;
  var $Query_ID = 0;



  /* public: constructor */
  function DB_Sql($query = "") {
      $this->query($query);
  }

  /* public: some trivial reporting */
  function link_id() {
    return $this->Link_ID;
  }

  function query_id() {
    return $this->Query_ID;
  }

  /* public: connection management */
  function connect($Database = "", $Host = "", $User = "", $Password = "") {
    /* Handle defaults */
    if ("" == $Database)
      $Database = $this->Database;
    if ("" == $Host)
      $Host     = $this->Host;
    if ("" == $User)
      $User     = $this->User;
    if ("" == $Password)
      $Password = $this->Password;

    /* establish connection, select database */
    if ( 0 == $this->Link_ID ) {

      $this->Link_ID=mysql_connect($Host, $User, $Password);
      if (!$this->Link_ID) {
        $this->halt("pconnect($Host, $User, \$Password) failed.");
        return 0;
      }

      if (!@mysql_select_db($Database,$this->Link_ID)) {
        $this->halt("cannot use database ".$this->Database);
        return 0;
      }
    }

    return $this->Link_ID;
  }

  /* public: discard the query result */
  function free() {
      @mysql_free_result($this->Query_ID);
      $this->Query_ID = 0;
  }

  /* public: perform a query */
  function query($Query_String) {
    /* No empty queries, please, since PHP4 chokes on them. */
    if ($Query_String == "")
      /* The empty query string is passed on from the constructor,
       * when calling the class without a query, e.g. in situations
       * like these: '$db = new DB_Sql_Subclass;'
       */
      return 0;

    if (!$this->connect()) {
      return 0; /* we already complained in connect() about that. */
    };

    # New query, discard previous result.
    if ($this->Query_ID) {
      $this->free();
    }

    if ($this->Debug)
      printf("Debug: query = %s<br>\n", $Query_String);

    $this->Query_ID = @mysql_query($Query_String,$this->Link_ID);
    $this->Row   = 0;
    $this->Errno = mysql_errno();
    $this->Error = mysql_error();
    if (!$this->Query_ID) {
      $this->halt("Invalid SQL: ".$Query_String);
    }

    # Will return nada if it fails. That's fine.
    return $this->Query_ID;
  }

  /* public: walk result set */
  function next_record() {
    if (!$this->Query_ID) {
      $this->halt("next_record called with no query pending.");
      return 0;
    }

    $this->Record = @mysql_fetch_array($this->Query_ID);
    $this->Row   += 1;
    $this->Errno  = mysql_errno();
    $this->Error  = mysql_error();

    $stat = is_array($this->Record);
    if (!$stat && $this->Auto_Free) {
      $this->free();
    }
    return $stat;
  }

  /* public: position in result set */
  function seek($pos = 0) {
    $status = @mysql_data_seek($this->Query_ID, $pos);
    if ($status)
      $this->Row = $pos;
    else {
      $this->halt("seek($pos) failed: result has ".$this->num_rows()." rows");

      /* half assed attempt to save the day,
       * but do not consider this documented or even
       * desireable behaviour.
       */
      @mysql_data_seek($this->Query_ID, $this->num_rows());
      $this->Row = $this->num_rows;
      return 0;
    }

    return 1;
  }

  /* public: table locking */
  function lock($table, $mode="write") {
    $this->connect();

    $query="lock tables ";
    if (is_array($table)) {
      while (list($key,$value)=each($table)) {
        if ($key=="read" && $key!=0) {
          $query.="$value read, ";
        } else {
          $query.="$value $mode, ";
        }
      }
      $query=substr($query,0,-2);
    } else {
      $query.="$table $mode";
    }
    $res = @mysql_query($query, $this->Link_ID);
    if (!$res) {
      $this->halt("lock($table, $mode) failed.");
      return 0;
    }
    return $res;
  }

  function unlock() {
    $this->connect();

    $res = @mysql_query("unlock tables");
    if (!$res) {
      $this->halt("unlock() failed.");
      return 0;
    }
    return $res;
  }

  /* public: evaluate the result (size, width) */
  function affected_rows() {
    return @mysql_affected_rows($this->Link_ID);
  }

  function num_rows() {
    return @mysql_num_rows($this->Query_ID);
  }

  function num_fields() {
    return @mysql_num_fields($this->Query_ID);
  }

  /* public: shorthand notation */
  function nf() {
    return $this->num_rows();
  }

  function np() {
    print $this->num_rows();
  }

  function f($Name) {
    if(isset($this->Record[$Name]))
      return $this->Record[$Name];
    else
      return "";
  }

  function p($Name) {
    print $this->Record[$Name];
  }

  /* public: sequence numbers */
  function nextid($seq_name) {
    $this->connect();

    if ($this->lock($this->Seq_Table)) {
      /* get sequence number (locked) and increment */
      $q  = sprintf("select nextid from %s where seq_name = '%s'",
                $this->Seq_Table,
                $seq_name);
      $id  = @mysql_query($q, $this->Link_ID);
      $res = @mysql_fetch_array($id);

      /* No current value, make one */
      if (!is_array($res)) {
        $currentid = 0;
        $q = sprintf("insert into %s values('%s', %s)",
                 $this->Seq_Table,
                 $seq_name,
                 $currentid);
        $id = @mysql_query($q, $this->Link_ID);
      } else {
        $currentid = $res["nextid"];
      }
      $nextid = $currentid + 1;
      $q = sprintf("update %s set nextid = '%s' where seq_name = '%s'",
               $this->Seq_Table,
               $nextid,
               $seq_name);
      $id = @mysql_query($q, $this->Link_ID);
      $this->unlock();
    } else {
      $this->halt("cannot lock ".$this->Seq_Table." - has it been created?");
      return 0;
    }
    return $nextid;
  }

  /* public: return table metadata */
 /* function metadata($table='',$full=false) {
    $count = 0;
    $id    = 0;
    $res   = array();
*/
    /*
     * Due to compatibility problems with Table we changed the behavior
     * of metadata();
     * depending on $full, metadata returns the following values:
     *
     * - full is false (default):
     * $result[]:
     *   [0]["table"]  table name
     *   [0]["name"]   field name
     *   [0]["type"]   field type
     *   [0]["len"]    field length
     *   [0]["flags"]  field flags
     *
     * - full is true
     * $result[]:
     *   ["num_fields"] number of metadata records
     *   [0]["table"]  table name
     *   [0]["name"]   field name
     *   [0]["type"]   field type
     *   [0]["len"]    field length
     *   [0]["flags"]  field flags
     *   ["meta"][field name]  index of field named "field name"
     *   The last one is used, if you have a field name, but no index.
     *   Test:  if (isset($result['meta']['myfield'])) { ...
     */

    // if no $table specified, assume that we are working with a query
    // result
   /* if ($table) {
      $this->connect();
      $id = @mysql_list_fields($this->Database, $table);
      if (!$id)
        $this->halt("Metadata query failed.");
    } else {
      $id = $this->Query_ID;
      if (!$id)
        $this->halt("No query specified.");
    }

    $count = @mysql_num_fields($id);

    // made this IF due to performance (one if is faster than $count if's)
    if (!$full) {
      for ($i=0; $i<$count; $i++) {
        $res[$i]["table"] = @mysql_field_table ($id, $i);
        $res[$i]["name"]  = @mysql_field_name  ($id, $i);
        $res[$i]["type"]  = @mysql_field_type  ($id, $i);
        $res[$i]["len"]   = @mysql_field_len   ($id, $i);
        $res[$i]["flags"] = @mysql_field_flags ($id, $i);
      }
    } else { // full
      $res["num_fields"]= $count;

      for ($i=0; $i<$count; $i++) {
        $res[$i]["table"] = @mysql_field_table ($id, $i);
        $res[$i]["name"]  = @mysql_field_name  ($id, $i);
        $res[$i]["type"]  = @mysql_field_type  ($id, $i);
        $res[$i]["len"]   = @mysql_field_len   ($id, $i);
        $res[$i]["flags"] = @mysql_field_flags ($id, $i);
        $res["meta"][$res[$i]["name"]] = $i;
      }
    }

    // free the result only if we were called on a table
    if ($table) @mysql_free_result($id);
    return $res;
  }
*/
  /* private: error handling */
  function halt($msg) {
    $this->Error = @mysql_error($this->Link_ID);
    $this->Errno = @mysql_errno($this->Link_ID);
    if ($this->Halt_On_Error == "no")
      return;

    $this->haltmsg($msg);

    if ($this->Halt_On_Error != "report")
      die("Session halted.");
  }

  function haltmsg($msg) {
    printf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
    printf("<b>MySQL Error</b>: %s (%s)<br>\n",
      $this->Errno,
      $this->Error);
  }

  function table_names() {
    $this->query("SHOW TABLES");
    $i=0;
    while ($info=mysql_fetch_row($this->Query_ID))
     {
      $return[$i]["table_name"]= $info[0];
      $return[$i]["tablespace_name"]=$this->Database;
      $return[$i]["database"]=$this->Database;
      $i++;
     }
   return $return;
  }

  function fetchRow()
  {
	  return $this->Record;
  }

	/* Chandni Patel
	 * Fetches tha name of all the fields from the given table
	 * Parameters:
	 * 		@tableName: Name of the table whose fields are to be fetched
	 * Return Values
	 * 		Array: Name of fields in the table
	 *
	 *  */
	function get_table_fields($tableName)
	{
		$tableName = trim($tableName);

		try{

			$FieldsArr = array();

			if($tableName != "")
			{
				$this->connect();

				$sSQL = "SHOW COLUMNS FROM " . mysql_real_escape_string($tableName);

				$QryResource = $this->query($sSQL);

				if (!$QryResource) {
					$this->halt("Could not execute the query".$sSQL);
				}

				if (mysql_num_rows($QryResource) > 0) {
					while ($row = mysql_fetch_array($QryResource)) {
						$FieldsArr[] = $row["Field"];
					}
				}

				return $FieldsArr;

			}
			else
			{
				$this->halt("Table Name cannot be blank");
			}
		}
		catch (Exception $e) {
		}
	}

	/* Chandni Patel
	 * Inserts the given array in the given table
	 * Parameters:
	 * 		@tableName: String, Name of the table in which data is to be inserted
	 * 		@values: Array, List of key and values to be inserted
	 * 		Note: Keys should be the name of the fields of the table
	 * Return Values
	 * 		Id of the inserted record else false
	 *
	 *  */
	function insertArray($tableName, $values)
	{

		$insertSQL = "";
		$fieldsStr = "";
		$comma = "";

		$tableName = trim($tableName);

		if($tableName != "" && is_array($values))
		{
			try{

				$fieldsArr = $this->get_table_fields($tableName);

				foreach($fieldsArr as $val)
				{
					if(isset($values[$val]))
					{
						$fieldsStr .= $comma . "`" . $val . "` = '" . mysql_real_escape_string(trim($values[$val])) . "'";
						$comma = ",";
					}
				}

				if($fieldsStr != "")
				{
					$insertSQL = "INSERT INTO ".mysql_real_escape_string($tableName)." SET ".$fieldsStr;

					$this->query($insertSQL);

					return mysql_insert_id();
				}
				else
					return false;
			}
			catch(Exception $e)
			{
				$this->halt($e->getMessage());
			}
		}
		else
		{
			$this->halt("Invalid arguments");
		}
	}

	/* Chandni Patel
	 * Updates the given array in the given table
	 * Parameters:
	 * 		@tableName: String, Name of the table in which data is to be updated
	 * 		@values: Array, List of key and values to be updated
	 * 		@matchField: String, Name of the filed to be matched for updation
	 * 			Ex: where id = '1' => matchField = 'id'
	 * 		Note: Keys should be the name of the fields of the table
	 * Return Values
	 * 		matchField of the updated record else false
	 * */
	function updateArray($tableName, $values, $matchField = 'id')
	{
		$updateSQL = "";
		$fieldsStr = "";
		$comma = "";

		$tableName = trim($tableName);
		$matchField = trim($matchField);

		if($tableName != "" && is_array($values) && $matchField != "")
		{
			if(isset($values[$matchField]) && trim($values[$matchField]) != "")
			{
				try
				{
					$fieldsArr = $this->get_table_fields($tableName);

					if(in_array($matchField, $fieldsArr))
					{

						foreach($fieldsArr as $val)
						{
							if(isset($values[$val]))
							{
								$fieldsStr .= $comma . "`" . $val . "` = '" . mysql_real_escape_string(trim($values[$val])) . "'";
								$comma = ",";
							}
						}

						if($fieldsStr != "")
						{
							$id = trim($values["id"]);
							$updateSQL = "UPDATE ".mysql_real_escape_string($tableName)." SET ".$fieldsStr . "where id = '".mysql_real_escape_string($id)."'";
							$this->query($updateSQL);
							return $id;
						}
						else
							return false;
					}
					else
					{
						$this->halt("matchField does not exist in table");
					}
				}
				catch(Exception $e)
				{
					$this->halt($e->getMessage());
				}
			}
			else
			{
				echo "<br/>tableName : ".$tableName;
				print_r($values);
				$this->halt("matchField not inputed");
			}
		}
		else
		{
			$this->halt("Invalid arguments");
		}
	}

	/* Chandni Patel
	 * Displayes the list of record
	 * Parameters:
	 * 		@SQL: String, Query to display the records
	 * 		@noOfRecords: int, Number of records to be displayed on the page
	 * 			Default 10 records
	 * Return Values
	 * 		matchField of the updated record else false
	 * */
	function displayList($SQL, $noOfRecords = 10, $fn = "")
	{
		global $tpl;

		$SQL = trim($SQL);
		$noOfRecords = trim($noOfRecords);

		try{
			if($SQL != "" && $noOfRecords != "" && (int)$noOfRecords > 0)
			{
				/* Set all the blocks to blank for the first display */
				$tpl->set_var("DisplayRecordBlock","");
				$tpl->set_var("DisplayNoRecordBlock","");
				$tpl->set_var("DisplayNavigationBlock","");
				$tpl->set_var("DisplayNavigationFirstPage","");
				$tpl->set_var("DisplayNavigationIPageDisable","");
				$tpl->set_var("DisplayNavigationIPage","");
				$tpl->set_var("DisplayNavigationNextPage","");

				$this->query($SQL);
				$queryRows = $this->num_rows();

				if($queryRows > 0)
				{

					/* Create Pagination */
					$page=0;
					if(isset($_GET['page']))
						$page = (int) $_GET['page'];
					if ($page < 1) $page = 1;

					$numberOfPages = 5;
					$resultsPerPage = $noOfRecords;
					$startResults = ($page - 1) * $resultsPerPage;
					$halfPages = floor($numberOfPages / 2);

					$totalPages = ceil($queryRows / $resultsPerPage);

					$range = array('start' => 1, 'end' => $totalPages);
					$isEven = ($numberOfPages % 2 == 0);
					$atRangeEnd = $totalPages - $halfPages;

					if($isEven) $atRangeEnd++;

					if($totalPages > $numberOfPages)
					{
						if($page <= $halfPages)
							$range['end'] = $numberOfPages;
						elseif ($page >= $atRangeEnd)
							$range['start'] = $totalPages - $numberOfPages + 1;
						else
						{
							$range['start'] = $page - $halfPages;
							$range['end'] = $page + $halfPages;
							if($isEven) $range['end']--;
						}
					}

					if($totalPages > 1)
					{
						if($page > 1)
						{
							$tpl->set_var("displaylist_previouspage",($page - 1));
							$tpl->parse("DisplayNavigationFirstPage",false);
						}
						for ($i = $range['start']; $i <= $range['end']; $i++)
						{
							$tpl->set_var("displaylist_ith",$i);

							if($i == $page)
							{
								$tpl->set_var("displaylist_href","_");
							}
							else
							{
								$tpl->set_var("displaylist_href","");
							}
							$tpl->parse("DisplayNavigationIPage",true);
						}
						if ($page < $totalPages)
						{
							$tpl->set_var("displaylist_nextpage",($page + 1));
							$tpl->parse("DisplayNavigationNextPage",false);
						}

						$tpl->parse("DisplayNavigationBlock",false);
					}

					/* Search query for limit */

					$pos = strpos($SQL," limit");
					if($pos)
						$SQL = substr($SQL,0,$pos);

					/* Display data */
					$sSQL = $SQL . " limit $startResults,$resultsPerPage";
					$this->query($sSQL);
					$queryRows1 = $this->num_rows();

					if($queryRows1 > 0)
					{
						while($this->next_record())
						{
							$tpl = $tpl->SetAllValues($this->fetchRow());

							if(trim($fn) != "")
							{
								if(function_exists($fn))
									$fn();
							}

							$tpl->parse("DisplayRecordBlock",true);
						}
					}
					else
					{
						/* If query fetched no records then parse Display No Records Block */
						$tpl->parse("DisplayNoRecordBlock",false);
					}
				}
				else
				{
					/* If query fetched no records then parse Display No Records Block */
					$tpl->parse("DisplayNoRecordBlock",false);
				}
			}
			else
			{
				$this->halt("Invalid arguments");
			}
		}
		catch(Exception $e)
		{
			$this->halt($e->getMessage());
		}
	}
}
?>
