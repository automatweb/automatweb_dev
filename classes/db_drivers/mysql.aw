<?php
// $Header: /home/cvs/automatweb_dev/classes/db_drivers/mysql.aw,v 1.23 2004/12/01 20:45:44 kristo Exp $
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
		lc_load('definition');
	}
		

	////
	// !We need to be able to create multiple connections
	// even better, connections might go to different databases
	function db_connect($server,$base,$username,$password) 
	{
		global $DEBUG;
		$this->dbh = mysql_connect($server,$username,$password);
		if (!$this->dbh) 
		{
			echo "Can't connect to database";
			print '<br />';
			print mysql_error();
			exit;
		};
		if (not(@mysql_select_db($base,$this->dbh)))
		{
			echo "Can't connect to database";
			print '<br />';
			print mysql_error();
			exit;	
		};
		$this->db_base = $base;
	}

	function db_query($qtext,$errors = true) 
	{
		global $DUKE, $INTENSE_DUKE;
		if ( (aw_ini_get("debug_mode") != 0) && $DUKE)
		{
			print '<pre>';
			print_r(preg_replace("/\t/","",$qtext));
			print '</pre>';
			list($micro,$sec) = split(' ',microtime());
			$ts_s = $sec + $micro;
		};
		if ($INTENSE_DUKE == 1)
		{
			$path = $this->_dbg_backtrace();
			print '<pre>';
			print $path."\n";
			print_r(preg_replace("/\t/","",$qtext));
			print '</pre>';
			list($micro,$sec) = split(' ',microtime());
			$ts_s = $sec + $micro;
		}
		aw_global_set('qcount',aw_global_get('qcount')+1); 

		enter_function("mysql::db_query");

		if (not($this->dbh))
		{
			// try to acquire the database handle
			$this->db_init();
			// if still not available, raise error. well, ok, we could try to re-connect to the db as well, but
			// if we couldn't do this the first time around, we probably won't be able to this time either
			if (not($this->dbh))
			{
				$eri = new class_base;
				$eri->init();
				$eri->raise_error(ERR_DB_NOTCONNECTED, "I'm not connected to the database, cannot perform the requested query. Please report this to site administratorimmediately", true, false);
			}
		};
		$this->qID = @mysql_query($qtext, $this->dbh);
		$this->log_query($qtext);
		if (!$this->qID ) 
		{
			if (!$errors)
			{
				$this->db_last_error = array(
					'error_cmd' => $qtext,
					'error_code' => mysql_errno($this->dbh),
					'error_string' => mysql_error($this->dbh)
				);
				return false;
			}
			$eri = new class_base;
			$eri->init();
			$eri->raise_error(ERR_DB_QUERY,LC_MYSQL_ERROR_QUERY."\n".$qtext."\n".mysql_errno($this->dbh)."\n".mysql_error($this->dbh),true,false);
		} 
		else 
		{
			$this->num_rows = @mysql_num_rows($this->qID);
			$this->num_fields = @mysql_num_fields($this->qID);
		};
		$this->rec_count = 0;
		if ( (aw_ini_get("debug_mode") != 0) && ($DUKE || $GLOBALS["INTENSE_DUKE"]))
		{
			list($micro,$sec) = split(' ',microtime());
			$ts_e = $sec + $micro;
			$tm = sprintf("%0.04f",$ts_e - $ts_s);
			echo "query took $tm seconds <br />";
		}
		exit_function("mysql::db_query");
		return true;
	}

	////
	// !saves query handle in the internal stack
	// it's your task to make sure you call those functions in correct
	// order, otherwise weird things could happen
	function save_handle()
	{
		if (empty($this->qhandles) || !is_array($this->qhandles))
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
		$deq = !$GLOBALS['cfg']['__default']['magic_quotes_runtime'];
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
			//$res['rec'] = $this->rec_count;
		};
		return $res;
	}

	function db_last_insert_id() 
	{
		$res = mysql_insert_id($this->dbh);
		return $res;
	}

	function db_fetch_row($sql = '')
	{
		if ($sql != '')
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
		$row = $this->db_next();
		return $row[$field];
	}

	////
	// ! fetch all rows from db_query result
	// qtext - if not set tries to fetch from previous db_query !!
	function db_fetch_array($qtext='')
	{
		if ($qtext)
		{
			$this->db_query($qtext);
		}
		$arr = array();
		while ($row=$this->db_next())
		{
			$arr[]=$row;
		}
		return $arr;
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
		$this->db_next_table(true);
	}

	function db_next_table($reset = false)
	{
		static $cnt = 0;
		if ($reset == true)
		{
			$cnt = 0;
			return;
		}
		$res = ($cnt < $this->tablecount) ? mysql_tablename($this->tID,$cnt) : false;
		$cnt++;
		return $res;
	}
	
	function db_get_fields()
	{
		$retval = array();
		// print $this->num_fields;   // kommenterisin välja, kui keegi pahandab siis süüdi on axel
		$this->num_fields;
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
		$ret = array('name' => $name,'fields' => array());
		$fID = @mysql_list_fields($this->db_base, $name, $this->dbh);
		if (!$fID)
		{
			return false;
		}

		$numfields = mysql_num_fields($fID);
		for ($i=0; $i < $numfields; $i++)
		{
			$_name = mysql_field_name($fID,$i);
			$type = mysql_field_type($fID,$i);
			$len =  mysql_field_len($fID,$i);
			$flags = mysql_field_flags($fID,$i);
			$ret['fields'][$_name] = array('name' => $_name, 'length' => $len, 'type' => $type, 'flags' => '');
		}

		$this->db_query("DESCRIBE $name");
		while ($row = $this->db_next())
		{
			$ret['fields'][$row['Field']]['name'] = $row['Field'];
			if (strpos($row['Type'],'(') === false)
			{
				$ret['fields'][$row['Field']]['length'] = $row['Type'] == 'text' ? 65535 : ($row['Type'] == 'mediumtext' ? 1024*1024*16 : 0 );
				$ret['fields'][$row['Field']]['type'] = $row['Type'];
			}
			else
			{
				preg_match('/(.*)\((.*)\)/', $row['Type'], $mt);
				$ret['fields'][$row['Field']]['length'] = $mt[2];
				$ret['fields'][$row['Field']]['type'] = $mt[1];
			}
			$ret['fields'][$row['Field']]['null'] = $row['Null'];
			$ret['fields'][$row['Field']]['default'] = $row['Default'];
			$ret['fields'][$row['Field']]['flags'] = $row['Extra'];
		}

		// indeksid
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
			foreach ($source['fields'] as $fname => $fdata)
			{
				$fls[] = $fdata['name'].' '.$this->mk_field_len($fdata['type'],$fdata['length']);
			}
			$sql = 'CREATE TABLE '.$dest.'('.(join(',',$fls)).')';
			$this->db_query($sql);
		}
		else
		{
			// iterate over all fields and add the missing ones and convert the changed ones
			foreach($source['fields'] as $fname => $fdata)
			{
				if (is_array($dest_t['fields'][$fdata['name']]))
				{
					// field exists, convert it if necessary
					$dest_field = $dest_t['fields'][$fdata['name']];
					if ($dest_field['type'] != $fdata['type'] || $dest_field['length'] != $fdata['length'])
					{
						$sql = "ALTER TABLE $dest CHANGE ".$fdata['name'].' '.$fdata['name'].' '.$this->mk_field_len($fdata['type'],$fdata['length']);
						$this->db_query($sql);
					}
				}
				else
				{
					// field does not exist, add it
					$sql = "ALTER TABLE $dest ADD ".$fdata['name'].' '.$this->mk_field_len($fdata['type'],$fdata['length']);
					$this->db_query($sql);
				}
			}
		}
	}

	////
	// !this returns the sql for creating the field
	function mk_field_len($type,$length)
	{
		$type=strtoupper($type);
		switch ($type)
		{
			CASE 'TINYINT':
			CASE 'SMALLINT':
			CASE 'MEDIUMINT':
			CASE 'INT':
			CASE 'INTEGER':
			CASE 'BIGINT':
			CASE 'CHAR':
			CASE 'VARCHAR':
				return $type.'('.$length.')';
			default:
				return $type;
		}
	}

	////
	// !this creates a nice string from the results of db_get_table
	function db_print_table($arr)
	{
		$ret = 'CREATE TABLE '.$arr['name'];
		$ret.='(';
		$fs = array();
		if (is_array($arr['fields']))
		{
			foreach($arr['fields'] as $fname => $fdata)
			{
				$fs[] = $fdata['name'].' '.$fdata['type'].'('.$fdata['length'].') '.$fdata['flags'];
			}
		}
		$ret.=join(',',$fs);
		return $ret.')';
	}

	////
	// !Reads and returns the structure of the database
	function db_get_struct()
	{
		$this->db_query('SHOW TABLES');
		$tables = array();
		while($row = $this->db_next())
		{
			list($key,$val) = each($row);
			$row[0] = $val;
			// form entry tables are ignored
			if (not(preg_match('/form_(\d+?)_entries/',$row[0])))
			{
				$name = $row[0];
				$this->save_handle();
				$this->db_query("DESCRIBE $name");
				while($row = $this->db_next())
				{
					$flags = array();
					list($type,$extra) = explode(' ',$row['Type']);
					if ($extra)
					{
						$flags[] = $extra;
					};

					if (not($row['Null'] == 'YES'))
					{
						$flags[] = 'NOT NULL';
					};
			
					if ($row['Extra'])
					{
						$flags[] = $row['Extra'];
					};

					$tables[$name][$row['Field']] = array(
						'type' => $type,
						'flags' => $flags,
						'key' => $row['Key'],
					);
					
				};
				$this->restore_handle();
			};
		};
		return $tables;
	}

	function db_show_create_table($name)
	{
		return $this->db_fetch_field('SHOW CREATE TABLE '.$name, 'Create Table');
	}


	function db_list_databases()
	{
		return $this->db_query('SHOW DATABASES');
	}

	function db_next_database()
	{
		$tmp = $this->db_next();
		return $tmp === false ? false : array('name' => $tmp['Database']);
	}

	function db_create_database($args)
	{
		extract($args);
		$this->db_query("CREATE DATABASE $name");
		$this->db_query('GRANT ALL PRIVILEGES on '.$name.'.* TO '.$user.'@'.$host." IDENTIFIED BY '".$pass."'");
	}

	function db_server_status()
	{
		$ret = array();
		$this->db_query('SHOW STATUS');
		$ret['Server_version'] = mysql_get_server_info($this->dbh);
		$ret['Protocol_version'] = mysql_get_proto_info($this->dbh);
		$ret['Host_info'] = mysql_get_host_info($this->dbh);
		while ($row = $this->db_next())
		{
			$ret[$row['Variable_name']] = $row['Value'];
		}
		$ret['Queries_per_sec'] = (int)($ret['Questions'] / $ret['Uptime']);
		return $ret;
	}

	function db_get_table_info($tbl)
	{
		return $this->db_fetch_row("SHOW TABLE STATUS LIKE '$tbl'");
	}

	function db_list_field_types()
	{
		return array('' => '',
		'VARCHAR' => 'VARCHAR',
		'TINYINT' => 'TINYINT',
		'TEXT' => 'TEXT',
		'DATE' => 'DATE',
		'SMALLINT' => 'SMALLINT',
		'MEDIUMINT' => 'MEDIUMINT',
		'INT' => 'INT',
		'BIGINT' => 'BIGINT',
		'FLOAT' => 'FLOAT',
		'DOUBLE' => 'DOUBLE',
		'DECIMAL' => 'DECIMAL',
		'DATETIME' => 'DATETIME',
		'TIMESTAMP' => 'TIMESTAMP',
		'TIME' => 'TIME',
		'YEAR' => 'YEAR',
		'CHAR' => 'CHAR',
		'TINYBLOB' => 'TINYBLOB',
		'TINYTEXT' => 'TINYTEXT',
		'BLOB' => 'BLOB',
		'MEDIUMBLOB' => 'MEDIUMBLOB',
		'MEDIUMTEXT' => 'MEDIUMTEXT',
		'LONGBLOB' => 'LONGBLOB',
		'LONGTEXT' => 'LONGTEXT',
		'ENUM' => 'ENUM',
		'SET' => 'SET');
	}

	function db_list_flags()
	{
		return array('' => '',
			'AUTO_INCREMENT' => 'AUTO_INCREMENT'
		);
	}

	function db_drop_col($tbl,$col)
	{
		$q = "ALTER TABLE $tbl DROP $col";
		$this->db_query($q);
	}

	function db_add_col($tbl,$coldat)
	{
		extract($coldat);
		if ($extra == 'AUTO_INCREMENT')
		{
			$extra = 'PRIMARY KEY AUTO_INCREMENT';
		}
		if ($length)
		{
			$len = '('.$length.')';
		}
		$q = "ALTER TABLE $tbl ADD $name $type $len $null ".($default == '' ? '' : "default '$default'")." $extra ";
		$this->db_query($q);
	}

	function db_change_col($tbl, $col, $newdat)
	{
		extract($newdat);
		$q = "ALTER TABLE $tbl CHANGE $name $name $type($length) $null ".($default == "" ? "" : "default '$default'")." $extra ";
		$this->db_query($q);
	}

	function db_list_indexes($tbl)
	{
		$this->db_query("SHOW INDEX FROM $tbl");
	}

	function db_next_index()
	{
		$dat = $this->db_next();
		if ($dat)
		{
			return array(
				'index_name' => $dat['Key_name'],
				'col_name' => $dat['Column_name'],
				'unique' => !$dat['Non_unique']
			);
		}
		return false;
	}

	function db_add_index($tbl, $idx_dat)
	{
		extract($idx_dat);
		$q = "ALTER TABLE $tbl ADD INDEX $name($col)";
		$this->db_query($q);
	}

	function db_drop_index($tbl, $name)
	{
		$q = "ALTER TABLE $tbl DROP INDEX $name";
		$this->db_query($q);
	}

	function db_get_last_error()
	{
		return $this->db_last_error;
	}

	// logs the query, if user has a cookie named log_query
	function log_query($msg)
	{
		$uid = aw_global_get("uid");
		$logfile = "/www/log/mysql-" . $uid . ".log";

		if (isset($GLOBALS["HTTP_COOKIE_VARS"]["log_query"]))
		{
			$fp = fopen($logfile,"a");
			flock($fp, LOCK_EX);
			fwrite($fp,$msg . "\n\n-----------------------------------\n\n");
			flock($fp, LOCK_UN);
			fclose($fp);
		}
	}

	function db_table_exists($tbl)
	{
		$q = "DESCRIBE $tbl";
		if (@mysql_query($q, $this->dbh))
		{
			return true;
		}
		return false;
	}

	function _dbg_backtrace()
	{
		$msg = "";
		if (function_exists("debug_backtrace"))
		{
			$bt = debug_backtrace();
			for ($i = count($bt)-1; $i > 0; $i--)
			{
				if ($bt[$i+1]["class"] != "")
				{
					$fnm = $bt[$i+1]["class"]."::".$bt[$i+1]["function"];
				}
				else
				if ($bt[$i+1]["function"] != "")
				{
					if ($bt[$i+1]["function"] != "include")
					{
						$fnm = $bt[$i+1]["function"];
					}
					else
					{
						$fnm = "";
					}
				}
				else
				{
					$fnm = "";
				}

				$msg .= $fnm.":".$bt[$i]["line"]."->";

				/*if ($bt[$i]["class"] != "")
				{
					$fnm2 = $bt[$i]["class"]."::".$bt[$i]["function"];
				}
				else
				if ($bt[$i]["function"] != "")
				{
					$fnm2 = $bt[$i]["function"];
				}
				else
				{
					$fnm2 = "";
				}

				$msg .= $fnm2;*/
			}
		}

		return $msg;
	}

	function db_fn($fn)
	{
		return $fn;
	}
};
?>
