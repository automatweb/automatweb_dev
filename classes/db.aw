<?php
// $Header: /home/cvs/automatweb_dev/classes/db.aw,v 2.25 2005/03/22 15:32:36 kristo Exp $
// this is the class that allows us to connect to multiple datasources at once
// it replaces the mysql class which was used up to now, but still routes all
// db functions to it so that everything stays working and it also provides
// means to create and manage alternate database connections

define("DB_TABLE_TYPE_STORED_PROC", 1);
define("DB_TABLE_TYPE_TABLE", 2);

/*
	// this still works
	$this->db_query($q);
	while($row = $this->db_next())
	{
		print $row["id"];
	};

	// but we can also create a second connection
	$args = array(
		"driver" => "mysql", 
		"server" => "localhost",
		"base" => "persona",
		"username" => "guest", 
		"password" => "guest",
		"cid" => "persona"
	);
	$this->db_connect($args);
	$this->dc["persona"]->db_query($q);
	while($row = $this->dc["persona"]->db_next())
	{
		print $row["id"];
	};
	
	// of course, you can also use save_handle and restore_handle on both
	// dc-s, you can even mix the queries. in one word, you can do anything you
	// have been doing until now only with 1 + n (0..inf) database connections
	// at once

	// I think this notation is pretty good, since
	// I want to be able to use 2 or more connections at once, without
	// calling a method for switching connections or something, this class
	// should and will take care of connecting to correct sources 
*/

class db_connector
{
	var $dc; # this is where we hold connections

	////
	// !init default database connection
	function init($args = array())
	{
		// dammit, I hate it when I don't know whether the args are string or array
		if ((is_array($args) && isset($args["no_db"])) || (aw_global_get("no_db_connection")))
		{
			return;
		};
		$this->default_cid = "DBMAIN";
		
		// if no connection id is set, pretend that this is the primary data source
		$id = "db::".$this->default_cid;
		$dc = aw_global_get($id);
		if ($dc)
		{
			$this->dc[$this->default_cid] = $dc;
			// already connected, drop out
			return false;
		};

		$this->db_connect(array(
			"id" => $id,
			"dc" => $dc,
			"cid" => $this->default_cid,
			"driver" => aw_ini_get("db.driver"),
			"server" => aw_ini_get("db.host"),
			"base" => aw_ini_get("db.base"),
			"username" => aw_ini_get("db.user"),
			"password" => aw_ini_get("db.pass"),
		));
	}

	////
	// !Creates a connection with default arguments
	function db_init($args = array())
	{
		$this->init($args);
	}

	////
	// !Creates a connection to a data source
	// host, base, user, pass are self-explanatory
	// driver is the type of the SQL driver to use
	// cid is the connetion id, defaults to $this->default_cid
	function db_connect($args = array())
	{
		extract($args);
		// FIXME: validate arguments

		// dc is not an object if the $driver class had a syntax error
		$dc = get_instance("db_drivers/".$driver);
		if (!is_object($dc))
		{
			die(t("this driver is not supported"));
		};

		// FIXME: check for return value
		$dc->db_connect($server,$base,$username,$password);

		aw_global_set($id,$dc);

		if (!$cid)
		{
			$cid = "DBMAIN";
		}

		$this->dc[$cid] = $dc;

		return $dc;
	}

	// route all functions to default/primary driver
	function db_query($qtext,$errors = true)
	{
		$retval = $this->dc[$this->default_cid]->db_query($qtext,$errors);
		if (!$retval)
		{
			$this->db_last_error = $this->dc[$this->default_cid]->db_last_error;
		};
		return $retval;
	}

	function db_query_lim($qtext,$limit,$count = 0)
	{
		$retval = $this->dc[$this->default_cid]->db_query_lim($qtext,$limit, $count);
		if (!$retval)
		{
			$this->db_last_error = $this->dc[$this->default_cid]->db_last_error;
		};
		return $retval;
	}

	function db_next($dec = true)
	{
		return $this->dc[$this->default_cid]->db_next($dec);
	}

	function db_last_insert_id()
	{
		return $this->dc[$this->default_cid]->db_last_insert_id();
	}

	function db_fetch_row($sql = "")
	{
		return $this->dc[$this->default_cid]->db_fetch_row($sql);
	}

	function db_fetch_field($qtext,$field)
	{
		return $this->dc[$this->default_cid]->db_fetch_field($qtext,$field);
	}

	function db_fetch_array($qtext="") 
	{
		return $this->dc[$this->default_cid]->db_fetch_array($qtext);
	}

	function quote(&$arr)
	{
		return $this->dc[$this->default_cid]->quote($arr);
	}

	function dequote(&$arr)
	{
		return $this->dc[$this->default_cid]->dequote($arr);
	}

	function num_rows()
	{
		return $this->dc[$this->default_cid]->num_rows();
	}

	function db_list_tables()
	{
		return $this->dc[$this->default_cid]->db_list_tables();
	}

	function db_next_table()
	{
		return $this->dc[$this->default_cid]->db_next_table();
	}

	function db_get_fields()
	{
		return $this->dc[$this->default_cid]->db_get_fields();
	}

	function db_get_table($name)
	{
		return $this->dc[$this->default_cid]->db_get_table($name);
	}

	function db_show_create_table($name)
	{
		return $this->dc[$this->default_cid]->db_show_create_table($name);
	}

	function db_sync_tables($source,$dest)
	{
		return $this->dc[$this->default_cid]->db_sync_tables($source,$dest);
	}

	function mk_field_len($type,$length)
	{
		return $this->dc[$this->default_cid]->mk_field_len($type,$length);
	}

	function db_print_table($arg)
	{
		return $this->dc[$this->default_cid]->db_print_table($args);
	}

	function db_get_struct()
	{
		return $this->dc[$this->default_cid]->db_get_struct();
	}

	////
	// !Returns current query handle
	function save_handle()
	{
		return $this->dc[$this->default_cid]->save_handle();
	}

	////
	// !Sets current query handle
	function restore_handle()
	{
		return $this->dc[$this->default_cid]->restore_handle();
	}

	////
	// !fetchib kirje suvalisest tabelist
	function get_record($table,$field,$selector,$fields = array())
	{
		if (sizeof($fields) > 0)
		{
			$fields = join(",",$fields);
		}
		else
		{
			$fields = "*";
		};
		$q = "SELECT $fields FROM $table WHERE $field = '$selector'";
		$this->db_query($q);
		return $this->db_fetch_row();
	}

	////
	// !selects a list of databases from the server, the list can be retrieved by calling db_next_database()
	function db_list_databases()
	{
		return $this->dc[$this->default_cid]->db_list_databases();
	}

	////
	// !returns the next database from the list created by db_list_databases()
	function db_next_database()
	{
		return $this->dc[$this->default_cid]->db_next_database();
	}

	////
	// !tries to create the database in the server
	// parameters:
	//   name - the name of the database
	//   user - the user that will get access to the database
	//   host - the host from where the access will be granted
	//   pass - the password for the database
	function db_create_database($arr)
	{
		return $this->dc[$this->default_cid]->db_create_database($arr);
	}

	////
	// !returns a list of all available database drivers on the system
	function list_db_drivers()
	{
		$ret = array();
		if ($dir = @opendir($this->cfg["classdir"]."/db_drivers")) 
		{
			while (($file = readdir($dir)) !== false) 
			{
				if (substr($file, strlen($file) - (strlen($this->cfg["ext"])+1)) == ".".$this->cfg["ext"])
				{
					$cln = basename($file,".".$this->cfg["ext"]);
					$ret[$cln] = $cln;
				}
			}  
			closedir($dir);
		}
		asort($ret);
		return $ret;
	}

	////
	// !returns server status - the fields returned are server-specific
	function db_server_status()
	{
		return $this->dc[$this->default_cid]->db_server_status();
	}

	////
	// !returns information about the specified table - the fields returned are server specific
	function db_get_table_info($tbl)
	{
		return $this->dc[$this->default_cid]->db_get_table_info($tbl);
	}

	function db_list_flags()
	{
		return $this->dc[$this->default_cid]->db_list_flags();
	}

	function db_list_field_types()
	{
		return $this->dc[$this->default_cid]->db_list_field_types();
	}

	////
	// !adds a column to table $tbl
	// params
	//   tbl - the table to add to
	//   coldat - new column properties array(name, type, length, null, default, extra)
	function db_add_col($tbl,$coldat)
	{
		return $this->dc[$this->default_cid]->db_add_col($tbl,$coldat);
	}

	////
	// !change a column in table $tbl
	// params
	//   tbl - the table where the column is
	//   col - the column to change
	//   coldat - new column properties array(name, type, length, null, default, extra)
	function db_change_col($tbl, $col, $newdat)
	{
		return $this->dc[$this->default_cid]->db_change_col($tbl, $col, $newdat);
	}

	////
	// !drops column $col from table $tbl 
	function db_drop_col($tbl,$col)
	{
		return $this->dc[$this->default_cid]->db_drop_col($tbl,$col);
	}

	////
	// !lists indexes for table $tbl
	function db_list_indexes($tbl)
	{
		return $this->dc[$this->default_cid]->db_list_indexes($tbl);
	}

	////
	// !fetches next index from list created by db_list_indexes
	// returns:
	//  array - 
	//		index_name - the name of the index
	//		col_name - the name of the column that the index is created on
	//		unique - if true, values in index must be unique
	function db_next_index()
	{
		return $this->dc[$this->default_cid]->db_next_index();
	}

	////
	// !adds an index to table $tbl
	// idx_dat must be an array that defines index properties - 
	//   name - the name of the index
	//   col - the column on what to create the index
	function db_add_index($tbl, $idx_dat)
	{
		return $this->dc[$this->default_cid]->db_add_index($tbl, $idx_dat);
	}

	////
	// !drops index $name from table $tbl
	function db_drop_index($tbl, $name)
	{
		return $this->dc[$this->default_cid]->db_drop_index($tbl, $name);
	}

	////
	// !if no error has occurred, returns false
	// otherwise, returns array -
	//  error_cmd - the query that produced the error
	//  error_code - the db-specific error code 
	//	error_string - the error string returned by the database
	function db_get_last_error()
	{
		return $this->dc[$this->default_cid]->db_get_last_error();
	}

	////
	// !returns true if the specified table exists in the database
	function db_table_exists($tbl)
	{
		return $this->dc[$this->default_cid]->db_table_exists($tbl);
	}

	/** escapes a database table field name based on the db driver
	**/
	function db_fn($fn)
	{
		return $this->dc[$this->default_cid]->db_fn($fn);
	}

	/** returns the type of the table, as one of the DB_TABLE_TYPE constants
	**/
	function db_get_table_type($tbl)
	{
		return $this->dc[$this->default_cid]->db_get_table_type($tbl);
	}
};
?>
