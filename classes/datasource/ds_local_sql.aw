<?php
// $Id: ds_local_sql.aw,v 1.6 2003/03/12 13:15:11 duke Exp $
// ds_local_sql - interface for the local SQL database
class ds_local_sql extends aw_template
{
	function ds_local_sql()
	{
		$this->init(array());
		$this->_errortext = "";
	}

	////
	// !Retrieves an object
	function ds_get_object($args = array())
	{
		extract($args);
		$retval = false;
		if ($table && $idfield && $id)
		{
			$retval = new aw_array($this->get_record($table,$idfield,$id,array_keys($fields)));
			$tmp = array();
			foreach($retval->get() as $key => $val)
			{
				if ($fields[$key] == "serialize")
				{
					if ($key == "metadata")
					{
						$key = "meta";
					};
					$tmp[$key] = aw_unserialize($val);
				}
				else
				{
					$tmp[$key] = $val;
				};
			};
			$retval = $tmp;
		}
		return $retval;
	}

	////
	// !Checks whether it is possible to add an object with the given data
	function ds_can_add($args = array())
	{
		$retval = true;
		// first, check add privileges for the parent
		if (!$this->can("add", $args["parent"]))
		{
			$this->acl_error("add", $args["parent"]);
		}

		// acl check was done in ds_can_add
		$parobj = $this->get_object(array(
			"oid" => $args["parent"],
			"class_id" => CL_PSEUDO,
		));

		if (!$parobj)
		{
			$this->_errortext = "Objekte saab lisada ainult menüüde alla";
			$retval = false;
		}

		return $retval;
	}

	////
	// !Stores an object
	// how do I know whether the action was successful?
	function ds_save_object($args = array(),$data = array())
	{

		if (isset($args["table"]) && isset($args["idfield"]))
		{
			$this->db_update_record(array(
				"table" => $args["table"],
				"key" => array($args["idfield"] => $args["id"]),
				"values" => $data,
				"replace" => $args["replace"],
			));
			
		}
		else
		{
			// don't save the object if the edit privilege is gone
			if (!$this->can("edit", $args["id"]))
			{
				$this->acl_error("edit", $args["id"]);
			}

			$data["oid"] = $data["id"];
			unset($data["id"]);
			// update an existing object
			$this->upd_object($data);
			$retval = true;

		};
	}

	function ds_new_object($args = array(),$data = array())
	{
		// create object, all checks are already done
		extract($args);
		$retval = true;
		if ($table && $idfield)
		{
			$q = sprintf("INSERT INTO %s (%s) VALUES (%d)",$table,$idfield,$id);
			$this->db_query($q);
		}
		else
		{
			if ($data["period"])
			{
				$data["periodic"] = 1;
			};
			$retval = $this->new_object($data);
		};
		return $retval;
	}

	function get_error_text()
	{
		return $this->_errortext;
	}

};
?>
