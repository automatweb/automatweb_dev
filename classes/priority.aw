<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/priority.aw,v 2.4 2003/01/26 22:24:10 duke Exp $
// priority.aw - prioriteedi objekt
/*
	@default table=objects
	@default field=meta
	@default method=serialize

	@property pri callback=callback_get_pri_list group=pri
	@caption Prioriteedid

	@groupinfo pri caption=Prioriteedid
*/

class priority extends class_base
{
	function priority()
	{
		$this->init(array(
			"tpldir" => "priority",
			"clid" => CL_PRIORITY,
		));
	}

	function callback_get_pri_list($args = array())
	{
		$obj = $this->get_object($args["obj"]["oid"]);
		$nodes = array();
		$uu = get_instance("users_user");
		$grouplist = $uu->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));
		$prilist = new aw_array($obj["meta"]["pri"]);
		$max = 0;
		$idx = 0;
		foreach($prilist->get() as $key => $val)
		{
			$idx++;
			if ($key > $max)
			{
				$max = $key;
			};
			$nodes[] = $this->_gen_pri_line($idx,$key,$val,&$grouplist);
		};
		// add a new empty line for adding new group/priority pair
		$max++;
		$idx++;
		$nodes[] = $this->_gen_pri_line($idx,$max,array(),&$grouplist);

		return $nodes;
	}

	function _gen_pri_line($idx,$key,$val,&$grouplist)
	{
		$tmp = array();
		$tmp["caption"] = $idx;
		$tmp["items"][] = array(
				"name" => "grps[$key][grp]",
				"type" => "select",
				"options" => $grouplist,
				"selected" => $val["grp"],
		);
		$tmp["items"][] = array(
				"name" => "grps[$key][pri]",
				"type" => "textbox",
				"size" => 6,
				"maxlength" => 6,
				"value" => $val["pri"],
		);
		return $tmp;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form_data = &$args["form_data"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "pri":
				$grps = $args["form_data"]["grps"];
				if (is_array($grps))
				{
					foreach($grps as $g_id => $g_data)
					{
						if ($g_data["pri"])
						{
							$pr[$g_id] = $g_data;
						}
					}
				}

				uasort($pr, create_function('$a,$b','if ($a["pri"] > $b["pri"]) { return 1; } if ($a["pri"] < $b["pri"]) { return -1; } return 0;'));
				$form_data["pri"] = $pr;
		};
		return $retval;
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

	function get_groups($id)
	{
		$ob = $this->get_object($id);
		$ret = array();
		if (is_array($ob["meta"]["pri"]))
		{
			foreach($ob["meta"]["pri"] as $idx => $gdata)
			{
				$ret[$gdata["grp"]] = $gdata["pri"];
			}
		}
		return $ret;
	}
}
?>
