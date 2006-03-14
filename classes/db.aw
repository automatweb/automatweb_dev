<?php
// $Header: /home/cvs/automatweb_dev/classes/db.aw,v 2.26 2006/03/14 13:25:07 markop Exp $
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

	/**
	@attrib api=1 params=name
	
	@param driver required type=string
	the type of the SQL driver to use
	@param server required type=string
	@param base required type=string
	@param username required type=string
	@param password required type=string
	@param cid optional type=oid default=$this->default_cid
	connetion id
	
	@errors
		die(t("this driver is not supported")) - if that driver doesn't exist
	
	@returns db connection object
	
	@comment 
		Creates a connection to a data source
	
	@examples
		$db->db_connect(array(
			'driver' => 'mysql',
			'server' => aw_ini_get('install.mysql_host'),
			'base' => 'mysql',
			'username' => aw_ini_get('install.mysql_user'),
			'password' => aw_ini_get('install.mysql_pass')
		));
	**/
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
	/**
	@attrib api=1 params=pos
	
	@param qtext required type=string
	@param errors optional type=bool default=true
	
	@returns true - if query was successful, else returns DB error
	
	@comment Makes a DB query
	
	@examples
		$q = "UPDATE ml_queue SET status = 0 WHERE qid = '$qid'";
		$this->db_query($q);
	**/
	function db_query($qtext,$errors = true)
	{
		$retval = $this->dc[$this->default_cid]->db_query($qtext,$errors);
		if (!$retval)
		{
			$this->db_last_error = $this->dc[$this->default_cid]->db_last_error;
		};
		return $retval;
	}
	
	/**
	@attrib api=1 params=pos
	
	@param qtext required type=string
	@param limit required type=int default=0
	@param count optional type=int default=0
	
	@returns true - if query was successful, else returns DB error
	
	@comment Changes a query text a little
	
	@examples
			$per_page = 100;
			$page = 12;
			$q = 'SELECT * FROM '.$db_table.');
			$db->db_query_lim($q, ($page*$per_page),($per_page));
	**/
	function db_query_lim($qtext,$limit,$count = 0)
	{
		$retval = $this->dc[$this->default_cid]->db_query_lim($qtext,$limit, $count);
		if (!$retval)
		{
			$this->db_last_error = $this->dc[$this->default_cid]->db_last_error;
		};
		return $retval;
	}
	/**
	@attrib api=1 params=pos
	
	@param dec optional type=bool default=true
		it is useless.....
	@returns next row of a query result
	
	@examples
		$q = "SELECT * FROM table";
		$data = array();
		$this->db_query($q);
		while($w = $this->db_next())
		{
			$data[] = $w["column"];
		}
	**/
	function db_next($dec = true)
	{
		return $this->dc[$this->default_cid]->db_next($dec);
	}
	
	/**
	@attrib api=1
	
	@returns oid
	
	@examples
	$oid = $this->db_last_insert_id();
	**/	
	function db_last_insert_id()
	{
		return $this->dc[$this->default_cid]->db_last_insert_id();
	}

	/**
	@attrib params=pos api=1
	
	@param sql optional type=string default=""
	
	@returns row of a query result
	
	@comment 
		makes a query and returns a row
	
	@examples
		$row = $this->db_fetch_row("SELECT * FROM my_table WHERE status = "kopp ees");
	**/	
	function db_fetch_row($sql = "")
	{
		return $this->dc[$this->default_cid]->db_fetch_row($sql);
	}
	
	/**
	@attrib params=pos api=1
	
	@param qtext optional type=string default=""
	@param field optional type=string default=""
	
	@returns field of a DB query row
	
	@comment 
		makes a query and returns a row
	
	@examples
		$id = $this->db_fetch_field("SELECT id FROM forms WHERE id = '$row[oid]'", "id");
	**/
	function db_fetch_field($qtext,$field)
	{
		return $this->dc[$this->default_cid]->db_fetch_field($qtext,$field);
	}

	/**
	@attrib params=pos api=1
	
	@param $qtext optional type=string default=""
	if not set tries to fetch from previous db_query
	
	@returns array
	
	@comment fetch all rows from db_query result
	
	@examples
		$arr = $this->db_fetch_array('select id , name , parent from users');
	**/
	function db_fetch_array($qtext="") 
	{
		return $this->dc[$this->default_cid]->db_fetch_array($qtext);
	}

	/**
	@attrib params=pos api=1
	
	@param arr required type=string/array
	
	@returns string/array
	
	@comment
		Quote string or stings in array with slashes
	**/
	function quote(&$arr)
	{
		return $this->dc[$this->default_cid]->quote($arr);
	}

	/**
	@attrib params=pos api=1
	
	@param arr required type=string/array
	
	@returns string/array
	
	@comment
		Un-quote quoted string
	**/
	function dequote(&$arr)
	{
		return $this->dc[$this->default_cid]->dequote($arr);
	}
	
	/**
	@attrib api=1
	
	@returns int , number of rows
	**/
	function num_rows()
	{
		return $this->dc[$this->default_cid]->num_rows();
	}

	/**
	@attrib api=1
	
	@comment
		Retrieves a list of table names from a database.($this->tID)
		Retrieves the number of rows from a result set ($this->tablecount)
	**/
	function db_list_tables()
	{
		return $this->dc[$this->default_cid]->db_list_tables();
	}
	//------------------------------------------------------------------------------------
	/**
	@attrib api=1
	
	@returns String , table name of a field
	**/
	function db_next_table()
	{
		return $this->dc[$this->default_cid]->db_next_table();
	}
	
	/**
	@attrib api=1

	@returns an array of objects containing field information.
	**/
	function db_get_fields()
	{
		return $this->dc[$this->default_cid]->db_get_fields();
	}

	/**
	@attrib params=pos api=1
	
	@param $name required type=string
	table name
	
	@returns array - the properties of table $name or false if it doesn't exist
	properties are returned as array $tablename => $tableprops
	where $tableprops is an array("name" => $table_name, "fields" => $fieldprops)
	where $fieldprops is an array of $fieldname => $cur_props
	where $cur_props is an array("name" => $field_name, "length" => $field_length, "type" => $field_type, "flags" => $field_flags)
	
	@examples
	CREATE TABLE tbl (id int, content text)
	db_get_table("tbl") returns:
	array("name" => "tbl",
		"fields" => array("id" => array("name" => "id", "length" => 10, "type" => "int", "flags" => ""),
		"content" => array("name" => "content", "length" => "65535", "type" => "text", "flags" => "")
		))
	**/
	function db_get_table($name)
	{
		return $this->dc[$this->default_cid]->db_get_table($name);
	}
	
	/**
	@attrib params=pos api=1
	
	@param $name required type=string
	table name

	@returns field "create table" of a query result
	
	@comment makes query SHOW CREATE TABLE $name and returns field "create table" of a result
	**/
	function db_show_create_table($name)
	{
		return $this->dc[$this->default_cid]->db_show_create_table($name);
	}

	/**
	@attrib params=pos api=1
	
	@param $source required type=array
	table array representation
	@param $dest required type=string
	destination table name

	@comment syncs the tables, creates all fields in $dest that are not in $dest, but are in $source

	**/
	function db_sync_tables($source,$dest)
	{
		return $this->dc[$this->default_cid]->db_sync_tables($source,$dest);
	}

	/**
	@attrib params=pos api=1
	
	@param $type required type=string
	@param $length required type=int

	@comment this returns the sql for creating the field

	@examples 
		if driver is mysql
		$str = $this->mk_field_len(varchar , 100);
		($str = "VARCHAR(100)")
	**/
	function mk_field_len($type,$length)
	{
		return $this->dc[$this->default_cid]->mk_field_len($type,$length);
	}

	/**
	@attrib params=name api=1
	
	@param name required type=string
	@param fields optional type=array
		array('name' = .. , 'type' = .. , 'length' => .. , flags => ..)
	
	@returns string 
	
	@comment
	this creates a nice string from the results of db_get_table
	**/	
	function db_print_table($args)
	{
		return $this->dc[$this->default_cid]->db_print_table($args);
	}
	
	/**
	@attrib api=1
	
	@returns array
	
	@comment
	Reads and returns the structure of the database
	
	**/	
	function db_get_struct()
	{
		return $this->dc[$this->default_cid]->db_get_struct();
	}

	/**
	@attrib api=1
	
	@comment
	saves query handle in the internal stack
	it's your task to make sure you call those functions in correct
	order, otherwise weird things could happen	
	
	@examples ${restore_handle}
	**/
	function save_handle()
	{
		return $this->dc[$this->default_cid]->save_handle();
	}

	////
	// !Sets current query handle
	/**
	@attrib api=1
	
	@comment restores query handle from internal check
	
	@examples
	function mark_queue_locked($qid)
	{
		$this->save_handle();
		$this->db_query("UPDATE ml_queue SET status = 3 WHERE qid = '$qid'");
		$this->restore_handlE();
	}
	**/
	function restore_handle()
	{
		return $this->dc[$this->default_cid]->restore_handle();
	}

	////
	// !fetchib kirje suvalisest tabelist
	/**
	@attrib params=pos api=1
	
	@param table required type=string
	@param field required type=string
	@param selector required type=string
	@param fields optional type=array default="*"
	
	@returns array 
	
	@comment
		fetch record from table
	**/	
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

	/**
	@attrib api=1
	
	@comment
	selects a list of databases from the server, the list can be retrieved by calling db_next_database()
	
	@examples
	$dbi->db_list_databases();
	while ($db = $dbi->db_next_database())
	{
		echo $db['name']
	}
	**/
	function db_list_databases()
	{
		return $this->dc[$this->default_cid]->db_list_databases();
	}

	/**
	@attrib api=1
	
	@return array - the next database from the list created by db_list_databases()
	
	@examples ${db_list_databases}
	**/
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
	/**
	@attrib params=name api=1
	
	@param foo [optional|required] type=[int|var|oid|object|string|float] default="foo"
	
	@errors 
	
	@returns 
	
	@comment
	
	@examples

	**/	
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
