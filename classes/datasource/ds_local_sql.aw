<?php
// $Id: ds_local_sql.aw,v 1.2 2002/11/26 12:38:48 duke Exp $
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
		if ($args["table"] && $args["idfield"])
		{
			$retval = $this->get_record($args["table"],$args["idfield"],$args["id"]);
		}
		else
		{
			if (!$this->can("edit", $args["id"]))
			{
				$this->acl_error("edit", $args["id"]);
			}

			$args["oid"] = $args["id"];
			$retval = $this->get_object($args);
		};
		$this->dequote($retval);
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
//                $fp = $this->cfg["basedir"];
//                $fp .= "/files/stuff";


		if ($args["table"] && $args["idfield"])
		{
			$this->db_update_record(array(
				"table" => $args["table"],
				"key" => array($args["idfield"] => $args["id"]),
				"values" => $data,
			));
			
//                        $ser = aw_serialize($data,SERIALIZE_XML);
//                        $fname = $fp . "/" . "object-" . $args["id"] . ".xml";
//                
//                        $this->put_file(array(
//                                "file" => $fname,
//                                "content" => $ser,
//                        ));
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

//                        $ser = aw_serialize($data,SERIALIZE_XML);
//                        $fname = $fp . "/" . "core-" . $args["id"] . ".xml";
//                        
//                        $this->put_file(array(
//                                "file" => $fname,
//                                "content" => $ser,
//                        ));
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
