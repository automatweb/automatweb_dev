<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/add_tree_conf.aw,v 1.1 2003/11/08 08:40:15 duke Exp $
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

	@groupinfo grps caption="Puu root objektid"

*/

class add_tree_conf extends class_base
{
	function add_tree_conf()
	{
		$this->init(array(
			"clid" => CL_ADD_TREE_CONF,
		));
	}

	function callback_get_groups($args = array())
	{
		$data = $args["prop"]["value"];
		$nodes = array();

		$ginst = get_instance("users");
		$gdata = $ginst->get_group_picker(array(
			"type" => array(GRP_REGULAR,GRP_DYNAMIC),
		));

		$pri = get_instance("priority");
		$grps = new aw_array($pri->get_groups($args["obj_inst"]->prop("priority_id")));

		$rt = new object_list(array(
			"class_id" => CL_TREE_ROOT,
		));

		$roots = array("" => "") + $rt->names();

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

	////
	// !gets the root menu for the current user from the conf object with id $id
	function get_root_for_user($id)
	{
		//$ob = $this->get_object($id);
		$ob = new object($id);
		$gidlist = aw_global_get("gidlist");

		$root_id = 0;
	
		$max_pri = 0;
		$max_gid = 0;
		$pri_inst = get_instance("priority");
		$grps = $pri_inst->get_groups($ob->prop("priority_id"));
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
			$grps = $ob->meta("grps");
			// find the root menu for this gid
			$root_oid = $grps[$max_gid];
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
