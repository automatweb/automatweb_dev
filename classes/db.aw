<?php
// $Header: /home/cvs/automatweb_dev/classes/db.aw,v 2.9 2002/11/24 15:21:03 duke Exp $
// this is the class that allows us to connect to multiple datasources at once
// it replaces the mysql class which was used up to now, but still routes all
// db functions to it so that everything stays working and it also provides
// means to creat and manage alternate database connections


/*
	// this still works
	$this->db_query($q);
	while($row = $this->db_next())
	{
		print $row["id"];
	};

	// but we can also create a second connection
	$args = array("driver" => "mysql", "server" => "localhost","base" => "persona",
		"username" => "guest", "password" => "guest","cid" => "persona");
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

// we dont really need that root class or do we?
classload("root");
class db_connector extends root
{
	var $dc; # this is where we hold connections
	function db($args = array())
	{
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

		// FIXME: dc is not an object if the $driver class had a syntax error
		$dc = get_instance("db_drivers/".$driver);
		if (!is_object($dc))
		{
			die("this driver is not supported");
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

	////
	// !Creates a connection with default arguments
	function db_init($args = array())
	{
		$cid = "DBMAIN";
		$this->default_cid = $cid;
		
		// if no connection id is set, pretend that this is the primary data source
		$id = "db::$cid";
		$dc = aw_global_get($id);
		
		if ($dc)
		{
			$this->dc[$cid] = $dc;
			// already connected, drop out
			return false;
		};

		$this->db_connect(array(
			"id" => $id,
			"dc" => $dc,
			"cid" => $cid,
			"driver" => aw_ini_get("db.driver"),
			"server" => aw_ini_get("db.host"),
			"base" => aw_ini_get("db.base"),
			"username" => aw_ini_get("db.user"),
			"password" => aw_ini_get("db.pass"),
		));
		
	}

	// route all functions to default/primary driver
	function db_query($qtext,$errors = true)
	{
		return $this->dc[$this->default_cid]->db_query($qtext,$errors);
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
	// ! Creates an UPDATE sql clause
	// key(array) name => value (oid => 666)
	// values(array) name => value pairs of fields that need to be updated
	// table(string) - name of the table to update
	// can be updated to support multiple keys
	// Example:
	// $this->db_update_record(array
	//	"table" => "documents",
	//	"key" => array("docid"  => array(36,35,95)),
	//	"values" => array("show_lead" =>  1,"show_title" => 1),
	//  ));

	//  creates and executes the following query:
	// UPDATE documents SET show_lead = 1, show_title = 1 WHERE docid IN (36,35,95)

	function db_update_record($args = array())
	{
		extract($args);
		if (!is_array($key) || !is_array($values))
		{
			return false;
		};

		list($keyname,$keyvalue) = each($key);

		if (is_array($keyvalue))
		{
			$keyvalue =  join(",",$keyvalue);
		};

		$fields = join(",",map2(" %s = '%s' ",$values));

		if ($fields)
		{
			$q = sprintf("UPDATE %s SET %s WHERE %s IN (%s)",$table,$fields,$keyname,$keyvalue);
			$this->db_query($q);
		};
	}

	////
	// !fetchib kirje suvalisest tabelist
	function get_record($table,$field,$selector)
	{
		$q = "SELECT * FROM $table WHERE $field = '$selector'";
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
};
?>
