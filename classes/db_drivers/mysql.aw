<?php
// $Header: /home/cvs/automatweb_dev/classes/db_drivers/mysql.aw,v 1.3 2002/11/12 12:49:11 kristo Exp $
// mysql.aw - MySQL draiver
class mysql 
{
	var $dbh; #database handle
	var $db_base; #name of the database
	var $qID; # query ID
	var $errmsg; # where we keep our error messages
	var $rec_count;

	function db_init() 
	{
		/*
		global $db_core;
		$this->dbh = $db_core->dbh;
		$this->db_base = $db_core->db_base;
		$this->watch = 1;
		*/
		lc_load("definition");
	}
		

	////
	// !We need to be able to create multiple connections
	// even better, connections might go to different databases
	function db_connect($server,$base,$username,$password) 
	{
		global $DEBUG;
		$this->dbh = mysql_pconnect($server,$username,$password);
		if (!$this->dbh) 
		{
			echo "Can't connect to database";
			print "<br>";
			print mysql_error();
			exit;
		};
		if (not(@mysql_select_db($base,$this->dbh)))
		{
			echo "Can't connect to database";
			print "<br>";
			print mysql_error();
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
		$this->qID = mysql_query($qtext, $this->dbh);
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
			echo mysql_errno($this->dbh);
			print ":";
			echo mysql_error($this->dbh);
		} 
		else 
		{
			$this->num_rows = @mysql_num_rows($this->qID);
			$this->num_fields = @mysql_num_fields($this->qID);
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
		$res = @mysql_fetch_array($this->qID,MYSQL_ASSOC);
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
		$res = mysql_insert_id($this->dbh);
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
					$arr[$k] = addslashes($arr[$k]);
				};
			};
			reset($arr);
		} 
		else 
		{
			$arr = addslashes($arr);
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
		$this->tID = mysql_list_tables($this->db_base);
		$this->tablecount = mysql_num_rows($this->tID);
	}

	function db_next_table()
	{
		static $cnt = 0;
		$res = ($cnt < $this->tablecount) ? mysql_tablename($this->tID,$cnt) : false;
		$cnt++;
		return $res;
	}
	
	function db_get_fields()
	{
		$retval = array();
		print $this->num_fields;
		for ($i = 0; $i < $this->num_fields; $i++)
		{
			$retval[] = mysql_fetch_field($this->qID);
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
		$ret = array("name" => $name,"fields" => array());
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
		return $ret;
	}

	////
	// !syncs the tables, creates all fields in $dest that are not in $dest, but are in $source
	// $dest is table name, $source is table array representation
	function db_sync_tables($source,$dest)
	{
		$dest_t = $this->db_get_table($dest);

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
		}
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
		$ret = "CREATE TABLE ".$arr["name"];
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
		return $ret.")";
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

/*	function db_get_table($args = array())
	{
		extract($args);
		$tables = array();
		$this->db_query("SHOW CREATE TABLE $name");
		$row = $this->db_next();
		$def = $row["Create Table"];
		preg_match("/CREATE TABLE `(\w*)` \((.*)\) TYPE/smi",$def,$m);
		if (!$m[2])
		{
			return false;
		};
		$tokens = explode(",",str_replace("\n","",$m[2]));

		$fields = $indexes = array();
		// now figure out what each token contains
		foreach($tokens as $token)
		{
			$token = trim($token);
			$stuff = explode(" ",$token);
			// if it starst with a identifier between upper apostrophes,
			// then it's very likely a field definition
			if (preg_match("/^`(\w*)`/",$token,$m))
			{
				$name = $m[1];
				$type = $stuff[1];
				$length = "";
				if (preg_match("/(\w+?)\((\d+?)\)/",$type,$mx))
				{
					$type = $mx[1];
					$length = $mx[2];
				};
				$x = array(
					"name" => $name,
					"type" => $type,
				);
				if (strpos($token,"unsigned"))
				{
					$x["unsigned"] = 1;
				};
				if ($length)
				{
					$x["length"] = $mx["2"];
				};
				if (strpos($token,"NOT NULL"))
				{
					$x["not_null"] = 1;
				};
				if (preg_match("/default (\S*)/",$token,$m))
				{
					$x["default"] = $m[1];
				};
				if (strpos($token,"auto_increment"))
				{
					$x["sequence"] = 1;
				};
				$fields[] = $x;
				// but I also have to figure out the extra information
			};
		}

		// now retrieve information about indexes

		$q = "SHOW INDEX FROM $args[name]";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$name = $row["Key_name"];
			if ($indexes[$name])
			{
				$idx = $indexes[$name];
			}
			else
			{
				$idx = array();
			};
			$idx["name"] = $name;
			$idx["unique"] = (int)!$row["Non_unique"];
			$idx["columns"][] = $row["Column_name"];
			$idx["collation"] = $row["Collation"];
			$idx["length"] = (int)$row["Sub_part"];
			$indexes[$name] = $idx;
		};

		$definition = array(
			"fields" => $fields,
			"indexes" => array_values($indexes),
		);

		$serializer = get_instance("xml",array("ctag" => "schema"));
		$serializer->set_child_id("fields","field");
		$serializer->set_child_id("indexes","index");
		$serializer->set_child_id("columns","column");
		$result = $serializer->xml_serialize($definition);
		header("Content-Type: text/xml");
		print $result;
		exit;
		print "<pre>";
		print htmlspecialchars($result);
		print "</pre>";
	}*/
};
?>
