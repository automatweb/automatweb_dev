<?php
class pgsql
{
	var $dbh; #database handle
	var $db_base; #name of the database
	var $qID; # query ID
	var $errmsg; # where we keep our error messages
	var $rec_count;

	function db_init() 
	{
		lc_load("definition");
	}
		

	////
	// !We need to be able to create multiple connections
	// even better, connections might go to different databases
	function db_connect($server,$base,$username,$password) 
	{
		global $DEBUG;
		$this->dbh = pg_pconnect("host=$server dbname=$base user=$username password=$password");
		if (!$this->dbh) 
		{
			echo "Can't connect to database";
			print "<br>";
			exit;
		};
		$this->db_base = $base;
	}

	function db_query($qtext,$errors = true) 
	{
		global $DUKE;
		if ($DUKE)
		{
			print "<pre>";
			print_r($qtext);
			print "</pre>";
			list($micro,$sec) = split(" ",microtime());
			$ts_s = $sec + $micro;
		};
		aw_global_set("qcount",aw_global_get("qcount")+1); 

		if (not($this->dbh))
		{
			// try to acquire the database handle
			$this->db_init();
			// if still not available, raise error. well, ok, we could try to re-connect to the db as well, but
			// if we couldn't do this the first time around, we probably won't be able to this time either
			if (not($this->dbh))
			{
				print "I'm not connected to the database, cannot perform the requested query. Please report this to site administrator 	immediately";
				exit;
			}
		};
		$this->qID = pg_query($this->dbh,$qtext);
		if (!$this->qID ) 
		{
			if (!$errors)
			{
				return false;
			}
			echo LC_MYSQL_ERROR_QUERY;
			// lühendame päringu. Ntx failide lisamisel voib paring olla yle mega pikk
			// ja selle ekraanile pritsimine ei anna mitte midagi.

/*			if (strlen($qtext) > 5000)
			{
				$qtext = substr($qtext,0,5000) . "....(truncated)";
			};*/

			echo $qtext . "\n";
			echo "<br>\n";
			echo pg_last_error($this->dbh);
		} 
		else 
		{
			$this->num_rows = @pg_num_rows($this->qID);
			$this->num_fields = @pg_num_fields($this->qID);
		};
		$this->rec_count = 0;
		if ($DUKE)
		{
			list($micro,$sec) = split(" ",microtime());
			$ts_e = $sec + $micro;
			echo "query took ".($ts_e - $ts_s)." seconds <br>";
		}
		return true;
	}

	////
	// !saves query handle in the internal stack
	// it's your task to make sure you call those functions in correct
	// order, otherwise weird things could happen
	function save_handle()
	{
		if (not(is_array($this->qhandles)))
		{
			$this->qhandles = array();
		};

		array_push($this->qhandles,$this->qID);
	}

	////
	// !restores query handle from internal check
	function restore_handle()
	{
		if (is_array($this->qhandles))
		{
			$this->qID = array_pop($this->qhandles);
		};
	}
		

	function db_next($deq = true) 
	{
		# this function cannot be called before a query is made
		// don't need numeric indices
		$res = @pg_fetch_array($this->qID,$this->rec_count, PGSQL_ASSOC);
		if ($res) 
		{
			$this->rec_count++;
			if ($deq)
			{
				$this->dequote($res);
			}
			$res["rec"] = $this->rec_count;
		};
		return $res;
	}

	function db_last_insert_id() 
	{
		//$res = mysql_insert_id($this->dbh);
		die("Unsupported operation! pgsql does not support db_last_insert_id()!");
		return $res;
	}

	function db_fetch_row($sql = "")
	{
		if ($sql != "")
		{
			$this->db_query($sql);
		}
		return $this->db_next();
	}
	
	# seda voib kasutada, kui on vaja teada saada mingit kindlat välja
	# a 'la cval tabelist config
	# $cval = db_fetch_field("SELECT cval FROM config WHERE ckey = '$ckey'","cval")
	function db_fetch_field($qtext,$field) 
	{
		$this->db_query($qtext);
		$row = $this->db_fetch_row();
		$val = $row[$field];
		$this->dequote($val);
		return $val;
	}

	# need 2 funktsiooni oskavad käituda nii array-de kui ka stringidega
	function quote(&$arr) 
	{
		if (is_array($arr)) 
		{
			while(list($k,$v) = each($arr)) 
			{
				if (is_array($arr[$k])) 
				{
					// do nothing
				} 
				else 
				{
					$arr[$k] = pg_escape_string($arr[$k]);
				};
			};
			reset($arr);
		} 
		else 
		{
			$arr = pg_escape_string($arr);
			return $arr;
		};
	}

	function dequote(&$arr) 
	{
		if (is_array($arr)) 
		{
			while(list($k,$v) = each($arr)) 
			{
				if (is_array($arr[$k])) 
				{
					$this->dequote(&$arr[$k]);
				} 
				else 
				{
					$arr[$k] = stripslashes($arr[$k]);
				};
			};
			reset($arr);
		} 
		else 
		{
			$arr = stripslashes($arr);
		};
	}

	function num_rows()
	{
		return $this->num_rows;//($this->qID);
	}

	function db_list_tables()
	{
//		$this->tID = mysql_list_tables($this->db_base);
//		$this->tablecount = mysql_num_rows($this->tID);
		die("db_list_tables is not supported for pgsql yet!");
	}

	function db_next_table()
	{
/*		static $cnt = 0;
		$res = ($cnt < $this->tablecount) ? mysql_tablename($this->tID,$cnt) : false;
		$cnt++;
		return $res;*/
		die("db_list_tables is not supported for pgsql yet!");
	}
	
	function db_get_fields()
	{
		$retval = array();
		print $this->num_fields;
		for ($i = 0; $i < $this->num_fields; $i++)
		{
			$retval[] = pg_fetch_result($this->qID, $this->rec_count, $i);
		}
		return $retval;
	}

	////
	// !returns the properties of table $name or false if it doesn't exist
	// properties are returned as array $tablename => $tableprops
	// where $tableprops is an array("name" => $table_name, "fields" => $fieldprops)
	// where $fieldprops is an array of $fieldname => $cur_props
	// where $cur_props is an array("name" => $field_name, "length" => $field_length, "type" => $field_type, "flags" => $field_flags)
	// example: CREATE TABLE tbl (id int, content text)
	// returns: array("name" => "tbl",
	//								"fields" => array("id" => array("name" => "id", "length" => 10, "type" => "int", "flags" => ""),
	//																	"content" => array("name" => "content", "length" => "65535", "type" => "text", "flags" => "")
	//																	)
	//								)
	function db_get_table($name)
	{
/*		$ret = array("name" => $name,"fields" => array());
		$fID = @mysql_list_fields($this->db_base, $name, $this->dbh);
		if (!$fID)
		{
			return false;
		}

		$numfields = mysql_num_fields($fID);
		for ($i=0; $i < $numfields; $i++)
		{
			$name = mysql_field_name($fID,$i);
			$type = mysql_field_type($fID,$i);
			$len =  mysql_field_len($fID,$i);
			$flags = mysql_field_flags($fID,$i);
			$ret["fields"][$name] = array("name" => $name, "length" => $len, "type" => $type, "flags" => "");
		}
		return $ret;*/
		die("db_get_table is not supported by pgsql driver!");
	}

	////
	// !syncs the tables, creates all fields in $dest that are not in $dest, but are in $source
	// $dest is table name, $source is table array representation
	function db_sync_tables($source,$dest)
	{
/*		$dest_t = $this->db_get_table($dest);

		// now if the table doesn't exist, create it
		if (!$dest_t)
		{
			// create
			$fls = array();
			foreach ($source["fields"] as $fname => $fdata)
			{
				$fls[] = $fdata["name"]." ".$this->mk_field_len($fdata["type"],$fdata["length"]);
			}
			$sql = "CREATE TABLE ".$dest."(".(join(",",$fls)).")";
			$this->db_query($sql);
		}
		else
		{
			// iterate over all fields and add the missing ones and convert the changed ones
			foreach($source["fields"] as $fname => $fdata)
			{
				if (is_array($dest_t["fields"][$fdata["name"]]))
				{
					// field exists, convert it if necessary
					$dest_field = $dest_t["fields"][$fdata["name"]];
					if ($dest_field["type"] != $fdata["type"] || $dest_field["length"] != $fdata["length"])
					{
						$sql = "ALTER TABLE $dest CHANGE ".$fdata["name"]." ".$fdata["name"]." ".$this->mk_field_len($fdata["type"],$fdata["length"]);
						$this->db_query($sql);
					}
				}
				else
				{
					// field does not exist, add it
					$sql = "ALTER TABLE $dest ADD ".$fdata["name"]." ".$this->mk_field_len($fdata["type"],$fdata["length"]);
					$this->db_query($sql);
				}
			}
		}*/
		die("db_sync_tables is not supported by pgsql driver!");
	}

	////
	// !this returns the sql for creating the field
	function mk_field_len($type,$length)
	{
		switch ($type)
		{
			case "tinyint":
			case "smallint":
			case "mediumint":
			case "int":
			case "integer":
			case "bigint":
			case "char":
			case "varchar":
				return $type."(".$length.")";

			default:
				return $type;
		}
	}

	////
	// !this creates a nice string from the results of db_get_table
	function db_print_table($arr)
	{
/*		$ret = "CREATE TABLE ".$arr["name"];
		$ret.="(";
		$fs = array();
		if (is_array($arr["fields"]))
		{
			foreach($arr["fields"] as $fname => $fdata)
			{
				$fs[] = $fdata["name"]." ".$fdata["type"]."(".$fdata["length"].") ".$fdata["flags"];
			}
		}
		$ret.=join(",",$fs);
		return $ret.")";*/
		die("db_print_table is not supported by pgsql driver!");
	}

	////
	// !Reads and returns the structure of the database
	function db_get_struct()
	{
		$this->db_query("SHOW TABLES");
		$tables = array();
		while($row = $this->db_next())
		{
			list($key,$val) = each($row);
			$row[0] = $val;
			// form entry tables are ignored
			if (not(preg_match("/form_(\d+?)_entries/",$row[0])))
			{
				$name = $row[0];
				$this->save_handle();
				$this->db_query("DESCRIBE $name");
				while($row = $this->db_next())
				{
					$flags = array();
					list($type,$extra) = explode(" ",$row["Type"]);
					if ($extra)
					{
						$flags[] = $extra;
					};

					if (not($row["Null"] == "YES"))
					{
						$flags[] = "NOT NULL";
					};
			
					if ($row["Extra"])
					{
						$flags[] = $row["Extra"];
					};

					$tables[$name][$row["Field"]] = array(
							"type" => $type,
							"flags" => $flags,
							"key" => $row["Key"],
					);
					
				};
				$this->restore_handle();
			};
		};
		return $tables;
	}
};
?>
