<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/tree_root.aw,v 2.3 2002/12/17 18:09:09 duke Exp $
// tree_root.aw - puu rootobjekt

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property root type=select
	@caption Rootmenüü

*/


class tree_root extends aw_template
{
	function tree_root()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init(array(
			"clid" => CL_TREE_ROOT,
			"tpldir" => "tree_root",
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
                        case "root":
				$ob = get_instance("objects");
                                $data["options"] = $ob->get_list();
                                break;
		}
		return PROP_OK;
	}


	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		return aw_serialize($row);
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = unserialize($str);
		$row["parent"] = $parent;
		$id = $this->new_object($row);
		return true;
	}

	function get_root($id)
	{
		$ob = $this->get_object($id);
		return $ob["meta"]["root"];
	}
}
?>
