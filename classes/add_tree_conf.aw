<?php

class add_tree_conf extends aw_template
{
	////////////////////////////////////
	// the next functions are REQUIRED for all classes that can be added from menu editor interface
	////////////////////////////////////

	function add_tree_conf()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("add_tree_conf");
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
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa add_tree_conf");
		}
		else
		{
			$this->mk_path($parent,"Lisa add_tree_conf");
		}
		$this->read_template("change.tpl");

		$this->vars(array(
			"priorities" => $this->picker(0,$this->list_objects(array("class" => CL_PRIORITY, "addempty" => true))),
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
				"class_id" => CL_ADD_TREE_CONF
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"priority_id" => $priority_id,
				"grps" => $root_ids
			)
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
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda add_tree_conf");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda add_tree_conf");
		}
		$this->read_template("change.tpl");
	
		$gs = "";
		if ($ob["meta"]["priority_id"])
		{
			$ginst = get_instance("users");
			$gdata= $ginst->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));

			$pri = get_instance("priority");
			$grps = $pri->get_groups($ob["meta"]["priority_id"]);
			foreach($grps as $gid => $gpri)
			{
				$this->vars(array(
					"grp" => $gdata[$gid],
					"gid" => $gid,
					"roots" => $this->picker($ob["meta"]["grps"][$gid], $this->list_objects(array("class" => CL_TREE_ROOT, "addempty" => true)))
				));
				$gs.=$this->parse("GROUP");
			}
		}
		$this->vars(array(
			"name" => $ob["name"],
			"priorities" => $this->picker($ob["meta"]["priority_id"],$this->list_objects(array("class" => CL_PRIORITY,"addempty" => true))),
			"GROUP" => $gs,
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob["name"]
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