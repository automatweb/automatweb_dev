<?php

/*

this message will get posted whenever an alias is about to be deleted
the message will get the connection object as the "conenction" parameter
EMIT_MESSAGE(MSG_STORAGE_ALIAS_DELETE)

*/

class connection
{
	////////////////////////////
	// private variables

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

	function find($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "connection::find(): parameter must be an array of filter parameters!"
			));
		}
		return $GLOBALS["object_loader"]->ds->find_connections($param);
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

		if (!is_array($this->conn))
		{
			$this->conn = array();
		}

		foreach($param as $k => $v)
		{
			$this->conn[$k] = $v;
		}

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
		if (!($GLOBALS["object_loader"]->ds->can("view", $this->conn["from"]) || $GLOBALS["object_loader"]->ds->can("view", $this->conn["to"])))
		{
			error::throw(array(
				"id" => ERR_ACL,
				"msg" => "connection::delete(): no view access for this connection (".$this->conn["id"].")!"
			));
		}

		post_message(
			MSG_STORAGE_ALIAS_DELETE, 
			array(
				"connection" => &$this
			)
		);

		$GLOBALS["object_loader"]->ds->delete_connection($this->conn["id"]);
	}

	function id()
	{
		return $this->conn["id"];
	}

	function prop($key = NULL)
	{
		if ($key === NULL)
		{
			return $this->conn;
		}
		return $this->conn[$key];
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
		return obj($this->conn["to"]);
	}
	
	function from()
	{
		if (!$this->conn["id"])
		{
			error::throw(array(
				"id" => ERR_CONNECTION,
				"msg" => "connection::to(): no current connection!"
			));
		}
		return obj($this->conn["from"]);
	}


	////////////////////////////
	// private functions

	function _int_load($id)
	{
		$this->conn = $GLOBALS["object_loader"]->ds->read_connection($id);
		if ($this->conn === false)
		{
			error::throw(array(
				"id" => ERR_CONNECTION,
				"msg" => "connection::load($id): no connection with id $id!"
			));
		}
		
		// now, check acl - both ends must be visible for the connection to be shown
		if (!($GLOBALS["object_loader"]->ds->can("view", $this->conn["from"]) || $GLOBALS["object_loader"]->ds->can("view", $this->conn["to"])))
		{
			error::throw(array(
				"id" => ERR_ACL,
				"msg" => "connection::load($id): no view access for this connection!"
			));
		}	
	}

	function _int_save()
	{
		if (!$this->conn["from"] || !$this->conn["to"])
		{
			error::throw(array(
				"id" => ERR_CONNECTION,
				"msg" => "connection::save(): connection must have both ends defined!"
			));
		}

		// now, check acl - both ends must be visible for the connection to be changed
		if (!($GLOBALS["object_loader"]->ds->can("view", $this->conn["from"]) || $GLOBALS["object_loader"]->ds->can("view", $this->conn["to"])))
		{
			error::throw(array(
				"id" => ERR_ACL,
				"msg" => "connection::load($id): no view access for this connection!"
			));
		}

		// now that everything is ok, save the damn thing
		$this->conn["id"] = $GLOBALS["object_loader"]->ds->save_connection($this->conn);
	}
}

?>
