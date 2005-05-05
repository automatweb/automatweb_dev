<?php

/*

this message will get posted whenever an alias is about to be deleted
the message will get the connection object as the "connection" parameter
EMIT_MESSAGE(MSG_STORAGE_ALIAS_DELETE)

this message will get posted whenever an alias is about to be deleted
the message parameter will be the class id of the "from" object
the message will get the connection object as the "connection" parameter
EMIT_MESSAGE(MSG_STORAGE_ALIAS_DELETE_FROM)

this message will get posted whenever an alias is about to be deleted
the message parameter will be the class id of the "to" object
the message will get the connection object as the "connection" parameter
EMIT_MESSAGE(MSG_STORAGE_ALIAS_DELETE_TO)

this message will get posted after a new alias is created
the message will get the connection object as the "connection" parameter
EMIT_MESSAGE(MSG_STORAGE_ALIAS_ADD)

this message will get posted after a new alias is created
the message will have the class id of the object for the "to" end as the message parameter
the message will get the connection object as the "connection" parameter
EMIT_MESSAGE(MSG_STORAGE_ALIAS_ADD_TO)

this message will get posted after a new alias is created
the message will have the class id of the object for the "from" end as the message parameter
the message will get the connection object as the "connection" parameter
EMIT_MESSAGE(MSG_STORAGE_ALIAS_ADD_FROM)

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
				error::raise(array(
					"id" => ERR_CONNECTION,
					"msg" => sprintf(t("connection::constructior(%s): parameter must be numeric or array!"), $id)
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
			error::raise(array(
				"id" => ERR_CONNECTION,
				"msg" => t("connection::load(): parameter must be either array (connection data) or integer (connection id)!")
			));
		}
		else
		{
			$this->_int_load($param);
		}
	}

	function find($param)
	{
		if ($GLOBALS["OBJ_TRACE"])
		{
			echo "connection::find(".join(",", map2('%s => %s', $param)).") <br>";
		}
		if (!is_array($param))
		{
			error::raise(array(
				"id" => ERR_PARAM,
				"msg" => t("connection::find(): parameter must be an array of filter parameters!")
			));
		}

		if (isset($param["type"]))
		{
			if (!is_numeric($param["type"]) && substr($param["type"], 0, 7) == "RELTYPE" && is_class_id($param["from.class_id"]))
			{
				// it is "RELTYPE_FOO"
				// resolve it to numeric
				if (!is_array($GLOBALS["relinfo"][$param["from.class_id"]]))
				{
					// load class def
					_int_object::_int_load_properties($param["from.class_id"]);
				}

				if (!$GLOBALS["relinfo"][$param["from.class_id"]][$param["type"]]["value"])
				{
					$param["type"] = -1; // won't match anything
				}
				else
				{
					$param["type"] = $GLOBALS["relinfo"][$param["from.class_id"]][$param["type"]]["value"];
				}
			}
		}

		return $GLOBALS["object_loader"]->ds->find_connections($param);
	}

	function change($param)
	{
		if (!is_array($param))
		{
			error::raise(array(
				"id" => ERR_ARG,
				"msg" => sprintf(t("connection::change(%s): parameter must be an array!"), $param)
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
			error::raise(array(
				"id" => ERR_CONNECTION,
				"msg" => t("connection::delete(): no current connection to delete!")
			));
		}

		// now, check acl - both ends must be visible for the connection to be deleted
		if (!($GLOBALS["object_loader"]->ds->can("view", $this->conn["from"]) || $GLOBALS["object_loader"]->ds->can("view", $this->conn["to"])))
		{
			error::raise(array(
				"id" => ERR_ACL,
				"msg" => sprintf(t("connection::delete(): no view access for this connection (%s)!"), $this->conn["id"])
			));
		}

		post_message(
			MSG_STORAGE_ALIAS_DELETE, 
			array(
				"connection" => &$this
			)
		);

		post_message_with_param(
			MSG_STORAGE_ALIAS_DELETE_FROM, 
			$this->conn["from.class_id"],
			array(
				"connection" => &$this
			)
		);

		post_message_with_param(
			MSG_STORAGE_ALIAS_DELETE_TO, 
			$this->conn["to.class_id"],
			array(
				"connection" => &$this
			)
		);

		$GLOBALS["object_loader"]->ds->delete_connection($this->conn["id"]);
		return $this->conn["id"];
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
			error::raise(array(
				"id" => ERR_CONNECTION,
				"msg" => t("connection::to(): no current connection!")
			));
		}
		return obj($this->conn["to"]);
	}
	
	function from()
	{
		if (!$this->conn["id"])
		{
			error::raise(array(
				"id" => ERR_CONNECTION,
				"msg" => t("connection::to(): no current connection!")
			));
		}
		return obj($this->conn["from"]);
	}


	function save()
	{
		$this->_int_save();
	}

	////////////////////////////
	// private functions

	function _int_load($id)
	{
		$this->conn = $GLOBALS["object_loader"]->ds->read_connection($id);
		if ($this->conn === false)
		{
			error::raise(array(
				"id" => ERR_CONNECTION,
				"msg" => sprintf(t("connection::load(%s): no connection with id $id!"), $id)
			));
		}
		
		// now, check acl - both ends must be visible for the connection to be shown
		if (!($GLOBALS["object_loader"]->ds->can("view", $this->conn["from"]) || $GLOBALS["object_loader"]->ds->can("view", $this->conn["to"])))
		{
			error::raise(array(
				"id" => ERR_ACL,
				"msg" => sprintf(t("connection::load(%s): no view access for this connection!"), $id)
			));
		}	
	}

	function _int_save()
	{
		if (!$this->conn["from"] || !$this->conn["to"])
		{
			error::raise(array(
				"id" => ERR_CONNECTION,
				"msg" => t("connection::save(): connection must have both ends defined!")
			));
		}

		global $awt;

		// now, check acl - both ends must be visible for the connection to be changed
		if (!($GLOBALS["object_loader"]->ds->can("view", $this->conn["from"]) || $GLOBALS["object_loader"]->ds->can("view", $this->conn["to"])))
		{
			error::raise(array(
				"id" => ERR_ACL,
				"msg" => sprintf(t("connection::load(%s): no view access for this connection!"), $id)
			));
		}

		// check if this is a new connection
		$new = false;
		if (!$this->conn["id"])
		{
			$new = true;

			// now, if it is, then check if a relobj_id was passed
			if (!$this->conn["relobj_id"])
			{
				// if it wasn't, then create the relobj
				$to = obj($this->conn["to"]);

				// only create connection objects, IF
				if ($this->conn["reltype"] == RELTYPE_ACL || $to->class_id() == CL_CALENDAR_VIEW || $to->class_id() == CL_ML_LIST)
				{
					$from = obj($this->conn["from"]);

					$o = obj();
					if ($GLOBALS["object_loader"]->ds->can("add", $from->parent()))
					{
						$o->set_parent($from->parent());
					}
					else
					if ($GLOBALS["object_loader"]->ds->can("add", $from->id()))
					{
						$o->set_parent($from->id());
					}
					if ($GLOBALS["object_loader"]->ds->can("add", $to->parent()))
					{
						$o->set_parent($to->parent());
					}
					else
					if ($GLOBALS["object_loader"]->ds->can("add", $to->id()))
					{
						$o->set_parent($to->id());
					}
					else
					{
						$noc = true;
					}

					if (!$noc)
					{
						// [cs-rel-create] => 5.5006 (40.27%)
						$o->set_class_id(CL_RELATION);
						$o->set_status(STAT_ACTIVE);
						$o->set_subclass($to->class_id());
						$awt->start("cs-rel-save");
						$this->conn["relobj_id"] = $o->save();
						$awt->stop("cs-rel-save");
					}
				}
			}
		}

		// now that everything is ok, save the damn thing
		$this->conn["id"] = $GLOBALS["object_loader"]->ds->save_connection($this->conn);

		// load all connection parameters
		$this->_int_load($this->conn["id"]);

		if ($new)
		{
			// add the relation id to the connection object meta field as conn_id
			if ($this->conn["relobj_id"])
			{
				/*
				$o = obj($this->conn["relobj_id"]);
				$o->set_meta("conn_id", $this->conn["id"]);
				$o->save();
				*/
			}

			post_message(
				MSG_STORAGE_ALIAS_ADD,
				array(
					"connection" => &$this
				)
			);

			post_message_with_param(
				MSG_STORAGE_ALIAS_ADD_TO,
				$this->conn["to.class_id"],
				array(
					"connection" => &$this
				)
			);

			post_message_with_param(
				MSG_STORAGE_ALIAS_ADD_FROM,
				$this->conn["from.class_id"],
				array(
					"connection" => &$this
				)
			);

		}
	}
}

?>
