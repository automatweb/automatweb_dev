<?php

class priority extends aw_template
{
	////////////////////////////////////
	// the next functions are REQUIRED for all classes that can be added from menu editor interface
	////////////////////////////////////

	function priority()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("priority");
	}

	////
	// !called, when adding a new object 
	// parameters:
	//    parent - the folder under which to add the object
	//    return_url - optional, if set, the "back" link should point to it
	//    alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created
	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa priority");
		}
		else
		{
			$this->mk_path($parent,"Lisa priority");
		}
		$this->read_template("change.tpl");

		$uu = get_instance("users_user");

		$this->vars(array(
			"id" => 1, 
			"grps" => $this->picker(0,$uu->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)))),
			"pri" => 0
		));

		$this->vars(array(
			"GROUP" => $this->parse("GROUP"),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url))
		));
		return $this->parse();
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	//    id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_PRIORITY
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		$pr = array();
		if (is_array($grps))
		{
			foreach($grps as $g_id => $g_data)
			{
				if ($g_data["grp"])
				{
					$pr[$g_id] = $g_data;
				}
			}
		}

		uasort($pr, create_function('$a,$b','if ($a["pri"] > $b["pri"]) { return 1; } if ($a["pri"] < $b["pri"]) { return -1; } return 0;'));

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "pri",
			"value" => $pr
		));

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda priority");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda priority");
		}
		$this->read_template("change.tpl");
	
		$uu = get_instance("users_user");
		$grps = $uu->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));
		$mxpr = $mxid = 0;
		$gr = "";
		if (is_array($ob["meta"]["pri"]))
		{
			foreach($ob["meta"]["pri"] as $g_id => $g_data)
			{
				$this->vars(array(
					"id" => $g_id,
					"grps" => $this->picker($g_data["grp"],$grps),
					"pri" => $g_data["pri"]
				));
				$gr.=$this->parse("GROUP");
				$mxpr = max($mxpr, $g_data["pri"]);
				$mxid = max($mxid, $g_id);
			}
		}

		$this->vars(array(
			"id" => $mxid+1,
			"grps" => $this->picker(0,$grps),
			"pri" => $mxpri+1
		));
		$gr.=$this->parse("GROUP");

		$this->vars(array(
			"GROUP" => $gr,
			"name" => $ob["name"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
		));

		return $this->parse();
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
