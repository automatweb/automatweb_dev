<?php
// property_group.aw - Property group object
/*
	@classinfo relationmgr=yes

	@default table=objects
	@default group=general
	
	@property rootmenu type=relpicker reltype=RELTYPE_MENU field=meta method=serialize
	@caption Rootmenüü

	@property subclass type=callback callback=callback_get_class_list field=subclass
	@caption Klassid

	@property generate editonly=1 type=text
	@caption Genereeri objektid

	@reltype MENU value=1 clid=CL_MENU
	@caption kataloog
*/
class property_group extends class_base
{
	function property_group($args = array())
	{
		$this->init(array(
			"clid" => CL_PROPERTY_GROUP,
		));
	}

	////
	// !Returns a list of checkboxes for selecting classes
	function callback_get_class_list($args = array())
	{
		// this is only shown for new objects
		if (!$args["new"])
		{
			return array();
		};

		$cx = get_instance("cfg/cfgutils");
		$class_list = new aw_array($cx->get_classes_with_properties());
		$cp = get_class_picker(array("field" => "def"));
		$nodes = array();
		$nodes[] = array("caption" => "Klassid");
		foreach($class_list->get() as $key => $val)
		{
			$ckey = $cp[$key];
			$nodes[] = array(
				"caption" => $val,
				"type" => "radiobutton",
				"name" => "subclass",
				"value" => $key,
				"checked" => checked($args["obj_inst"]->prop("subclass") == $key),
			);
		};
		return $nodes;
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
			case "generate":
				$data["value"] = html::href(array(
					"url" => $this->mk_my_orb("generate",array("id" => $args["obj_inst"]->id())),
					"caption" => "Genereeri",
				));
				break;

		}
		return PROP_OK;
	}

	/**  
		
		@attrib name=generate params=name default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function generate($args = array())
	{
		$id = $args["id"];
		$obj = obj($id);
		// retrieve a list of properties for this class_id
		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_class_properties(array(
			"clid" => $obj->subclass(),
		));
		// create a menu under the rootmenu with the name of this object
		$name = $obj->name();
		$comment = $obj->comment();
		$parent = $obj->prop("rootmenu");
		$subclass = $obj->subclass();

		$o = obj();
		$o->set_parent($parent);
		$o->set_name($name);
		$o->set_comment($comment);
		$o->set_class_id(CL_MENU);
		$o->set_status(STAT_ACTIVE);
		$o->set_prop("type", MN_CONTENT);
		$grandparent = $o->save();

		$groups = array();
		$properties = array();
		foreach($props as $val)
		{
			$groupname = $val["group"];
			$groups[$groupname] = $groupname;
			$properties[$groupname][] = $val;
		};
		foreach($groups as $group)
		{
			print "creating group $group<br />";
			$o = obj();
			$o->set_parent($grandparent);
			$o->set_name($group);
			$o->set_class_id(CL_MENU);
			$o->set_status(STAT_ACTIVE);
			$o->set_prop("type", MN_CONTENT);
			$group_id = $o->save();
			flush();
			sleep(1);

			foreach($properties[$group] as $key => $val)
			{
				print "<pre>";
				print_r($val);
				print "</pre>";
				$o = obj();
				$o->set_parent($group_id);
				$o->set_name($val["caption"]);
				$o->set_class_id(CL_PROPERTY);
				$o->set_status(STAT_ACTIVE);
				$o->set_subclass($subclass);
				if (is_array($val))
				{
					foreach($val as $k => $v)
					{
						$o->set_meta($k, $v);
					}
				}
				$o->save();
			};
		};
		print "all done!<br />";
	}
};
?>
