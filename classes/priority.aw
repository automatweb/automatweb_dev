<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/priority.aw,v 2.7 2005/04/21 08:32:46 kristo Exp $
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
			"clid" => CL_PRIORITY,
		));
	}

	function callback_get_pri_list($arr)
	{
		$obj = $arr["obj_inst"];
		$nodes = array();
		$uu = get_instance("users_user");
		$grouplist = $uu->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));
		$prilist = new aw_array($obj->meta("pri"));
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

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$form_data = &$arr["request"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "pri":
				$grps = $form_data["grps"];
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
				$data["value"] = $pr;
		};
		return $retval;
	}

	function get_groups($id)
	{
		$ob = new object($id);
		$meta = $ob->meta();
		$ret = array();
		if (is_array($meta["pri"]))
		{
			foreach($meta["pri"] as $idx => $gdata)
			{
				$ret[$gdata["grp"]] = $gdata["pri"];
			}
		}
		return $ret;
	}
};
?>
