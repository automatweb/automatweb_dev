<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/add_tree_conf.aw,v 2.3 2003/01/27 00:01:42 duke Exp $
// add_tree_conf.aw - Lisamise puu konff

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property priority_id type=objpicker clid=CL_PRIORITY
	@caption Prioriteedi objekt

	@property grps callback=callback_get_groups group=grps
	@caption Grupid

	@groupinfo grps caption=Puu_root_objektid

*/

class add_tree_conf extends class_base
{
	function add_tree_conf()
	{
		$this->init(array(
			"tpldir" => "add_tree_conf",
			"clid" => CL_ADD_TREE_CONF,
		));
	}

	function callback_get_groups($args = array())
	{
		$data = $args["prop"]["value"];
		$nodes = array();

		$ginst = get_instance("users");
		$gdata= $ginst->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));

		$pri = get_instance("priority");
		$grps = new aw_array($pri->get_groups($args["obj"]["meta"]["priority_id"]));

		$roots = $this->list_objects(array("class" => CL_TREE_ROOT, "addempty" => true));

		//foreach($data->get() as $gid => $gpri)
		foreach($grps->get() as $gid => $gpri)
		{
			$nodes[] = array(
				"caption" => $gdata[$gid],
				"type" => "select",
				"name" => "grps[$gid]",
				"options" => $roots,
				"selected" => $data[$gid],
			);
		};
		
		return $nodes;
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

	////
	// !gets the root menu for the current user from the conf object with id $id
	function get_root_for_user($id)
	{
		$ob = $this->get_object($id);
		$gidlist = aw_global_get("gidlist");

		$root_id = 0;
	
		$max_pri = 0;
		$max_gid = 0;
		$pri_inst = get_instance("priority");
		$grps = $pri_inst->get_groups($ob["meta"]["priority_id"]);
		foreach($gidlist as $ugid)
		{
			if ($grps[$ugid])
			{
				if ($max_pri < $grps[$ugid])
				{
					$max_pri = $grps[$ugid];
					$max_gid = $ugid;
				}
			}
		}
		// now we have the gid with max priority
		if ($max_gid)
		{
			// find the root menu for this gid
			$root_oid = $ob["meta"]["grps"][$max_gid];
			if ($root_oid)
			{
				$tr_inst = get_instance("tree_root");
				$root_id = $tr_inst->get_root($root_oid);
			}
		}
		return $root_id;
	}
}
?>
