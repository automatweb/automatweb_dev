<?php
// $Header: /home/cvs/automatweb_dev/classes/db.aw,v 2.6 2002/10/10 11:10:47 duke Exp $
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



		switch($driver)
		{
			case "mysql":
				$dc = get_instance($driver);
				break;

			default:
				die("this driver is not supported");
		};

		// FIXME: check for return value
		$dc->db_connect($server,$base,$username,$password);

		aw_global_set($id,$dc);
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

		$q = sprintf("UPDATE %s SET %s WHERE %s IN (%s)",$table,$fields,$keyname,$keyvalue);

		//print "executing $q<br>";
		$this->db_query($q);

	}
		

};
?>
