<?php

class connection
{
	////////////////////////////
	// private variables

	var $ds;		// data source
	var $conn;		// int connection data
		

	////////////////////////////
	// public functions

	function connection($id = NULL)
	{
		if ($id !== NULL)
		{
			if (!(is_numeric($id) || is_array($id)))
			{
				error::throw(array(
					"id" => ERR_CONNECTION,
					"msg" => "connection::constructior($id): parameter must be numeric or array!"
				));
			}

			$this->load($id);
		}

		$this->ds = new _int_obj_ds_local_sql;
	}

	function load($param)
	{
		if (is_array($param))
		{
			$this->conn = $param;
		}
		else
		if (!is_numeric($param))
		{
			error::throw(array(
				"id" => ERR_CONNECTION,
				"msg" => "connection::load(): parameter must be either array (connection data) or integer (connection id)!"
			));
		}
		else
		{
			$this->_int_load($param);
		}
	}

	function find($from, $to)
	{
		if (!is_oid($from) || !is_oid($to))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "connection::find($from, $to): from and to parameters must be oids!"
			));
		}
		return $this->ds->finnd_connection($from, $to);
	}

	function change($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_ARG,
				"msg" => "connection::change($param): parameter must be an array!"
			));
		}

		$this->conn += $param;

		$this->_int_save();
	}

	function delete()
	{
		if (!$this->conn["id"])
		{
			error::throw(array(
				"id" => ERR_CONNECTION,
				"msg" => "connection::delete(): no current connection to delete!"
			));
		}

		// now, check acl - both ends must be visible for the connection to be deleted
		if (!($this->ds->can("view", $this->conn["source"]) || $this->ds->can("view", $this->conn["target"])))
		{
			error::throw(array(
				"id" => ERR_ACL,
				"msg" => "connection::delete(): no view access for this connection (".$this->conn["id"].")!"
			));
		}

		$this->ds->delete_connection($this->conn["id"]);
	}

	function id()
	{
		return $this->conn["id"];
	}

	function to()
	{
		if (!$this->conn["id"])
		{
			error::throw(array(
				"id" => ERR_CONNECTION,
				"msg" => "connection::to(): no current connection!"
			));
		}
		return obj($this->conn["target"]);
	}

	////////////////////////////
	// private functions

	function _int_load($id)
	{
		$this->conn = $this->ds->read_connection($id);
		if ($this->conn === false)
		{
			error::throw(array(
				"id" => ERR_CONNECTION,
				"msg" => "connection::load($id): no connection with id $id!"
			));
		}
		
		// now, check acl - both ends must be visible for the connection to be shown
		if (!($this->ds->can("view", $this->conn["source"]) || $this->ds->can("view", $this->conn["target"])))
		{
			error::throw(array(
				"id" => ERR_ACL,
				"msg" => "connection::load($id): no view access for this connection!"
			));
		}	
}

	function _int_save()
	{
		if (!$this->conn["source"] || !$this->conn["target"])
		{
			error::throw(array(
				"id" => ERR_CONNECTION,
				"msg" => "connection::save(): connection must have both ends defined!"
			));
		}

		// now, check acl - both ends must be visible for the connection to be changed
		if (!($this->ds->can("view", $this->conn["source"]) || $this->ds->can("view", $this->conn["target"])))
		{
			error::throw(array(
				"id" => ERR_ACL,
				"msg" => "connection::load($id): no view access for this connection!"
			));
		}

		// now that everything is ok, save the damn thing
		$this->ds->save_connection($this->conn);
	}
}

?>
