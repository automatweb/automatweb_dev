<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/tree_root.aw,v 1.1 2003/11/08 09:23:27 duke Exp $
// tree_root.aw - puu rootobjekt

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property root type=relpicker reltype=RELTYPE_ROOT
	@caption Rootmenüü

	@classinfo relationmgr=yes

*/

define("RELTYPE_ROOT",1);

class tree_root extends class_base
{
	function tree_root()
	{
		$this->init(array(
			"clid" => CL_TREE_ROOT,
		));
	}

	
	function callback_pre_edit($arr)
	{
		// backwards compatibity sucks. check whether this object has a defined root
		// which is not a connection and if so, create it
		$oldroot = $arr["obj_inst"]->prop("root");
		if (!empty($oldroot))
		{
			$conns = $arr["obj_inst"]->connections_from(array(
				"type" => RELTYPE_ROOT,
				"to" => $oldroot,
			));
			if (0 == sizeof($conns))
			{
				$arr["obj_inst"]->connect(array(
					"reltype" => RELTYPE_ROOT,
					"to" => $oldroot,
				));
			};
		};
	}

	function callback_get_rel_types()
	{
                return array(
                        RELTYPE_ROOT => "juurmenüü",
		);
	}

	function callback_get_classes_for_relation($arr)
	{
		$retval = false;
		switch($arr["reltype"])
		{
			case RELTYPE_ROOT:
				$retval = array(CL_MENU);
				break;
		};
		return $retval;
	}
};
?>
