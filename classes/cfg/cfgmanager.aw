<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgmanager.aw,v 1.1 2002/10/29 13:29:30 duke Exp $
// cfgmanager.aw - Object configuration manager
class cfgmanager extends aw_template
{
	function cfgmanager($args = array())
	{
		$this->init("cfgmanager");
	}

	function add($args = array())
	{
		extract($args);
		$cfgform = get_instance("cfgform");
		$reforb = $this->mk_reforb("submit",array("parent" => $parent));
		$this->create_toolbar();
		$xf = $cfgform->change_properties(array(
			"clid" => &$this,
			"parent" => $parent,
			"reforb" => $reforb,
                ));
		return $this->toolbar->get_toolbar() . $xf;
	}

	function change($args = array())
	{
		extract($args);
		$this->obj = $this->get_object($id);
		$this->create_toolbar();
		$cfgform = get_instance("cfgform");
		$reforb = $this->mk_reforb("submit",array("id" => $id));
		$xf = $cfgform->change_properties(array(
			"clid" => &$this,
			"obj" => $this->obj,
			"reforb" => $reforb,
                ));
		return $this->toolbar->get_toolbar() . $xf;
	}

	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"priobj" => $priobj,
					"cfgform" => $cfgform,
				),
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => CL_CFGMANAGER,
				"metadata" => array(
					"priobj" => $priobj,
				),
			));
		};
		// XXX: log the action
		return $this->mk_my_orb("change",array("id" => $id));
	}

	function create_toolbar($args = array())
	{
		$this->toolbar = get_instance("toolbar");
		$this->toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.changeform.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));
	}

	////
	// !Metainfo for the class
	function get_metainfo($key)
	{
		// XXX: figure out a better way to load those strings
		$params = array(
			"title_add" => "Lisa konfiguratsioonihaldur",
			"title_change" => "Muuda konfiguratsioonihaldurit",
			"class_id" => CL_CFGMANAGER,
		);

		return $params[$key];
	}

	////
	// !Reference implementation of get_properties
	// $obj contains the object - from which the values for fields can be aquired
	// $fields - contains a list of fields that should be returned.

	// But does $fields based filtering make things more complicated?
	// I also need to take "private" property into account - in cases where
	// it does exist.

	// the problem is also that I don't really want to put filtering into
	// each get_properties - that's would mean way too much duplicated code

	// but I also don't want to execute code which I do not need.
	// for example - in this method I might not want to show the "priobj"
	// dropdown - and then there should not be a need to execute the list_objects function

	// then - what about a function called add_property - which would take care
	// of filtering for me?

	function get_properties($obj = array(),$fields = array())
	{
		/*
		// but I don't want to do that either!
		$this->set_property_filter($fields);

		but this doesn't solve any of my problems, because the contents
		of options are calculated before add_property is called

		then what about an completey different function get_property -
		which would only return one property at a time - then we can
		do whatever we want with those.
		$this->add_property("priobj",array(
				"type" => "select",
				"options" => "blabla",
		));

		// this could also be used, if some of the properties
		// are to be defined in a XML file

		// those add_property function should then be done
		// somewhere inside the class

		// and somehow I will have to figure out how to
		// execute the stuff only once.

		// I really start to like the idea of reading the stuff
		// out of the ORB definition, cause that would give us
		// a lot of additional functionality in the future.

		// returning properties should not be too deeply nested
		// nor overly complicated. I want to be able to return
		// the stuff in a simple array if it needs to be.
		// cause providing a function for each god damn property
		// is just bad karma!

		// I don't want to assign all the possible properties in the
		// class constructor either, because I might want to access
		// stuff from that class that does not need any of those properties
		// and then loading those would mean unneccessary work

		*/

		$props = array();
		// generate the picker for choosing priority objects
		$props["priobj"] = array(
			"type" => "select",
			"options" => $this->list_objects(array("class" => CL_PRIORITY,"addempty" => true)),
			"caption" => "Prioriteedi objekt",
			"selected" => $obj["priobj"],
			"store" => "meta",
                );
	
		// since I dont want to clutter this method more than necessary,
		// I use a callback to retrieve a list of groups
		$props["cfgform"] = "get_groups";
		return $props;
	}

	// but then - isn't there a better way for storing properties?
	// key - name of the property
	function get_property($args = array())
	{
		$key = $args["key"];
		$prp = false;
		/*
		you can also to stuff like
		if (in_array($key,$props))
		{
			return $props[$key];
		}
		else
		{
			// return something_else
			// creativity is encouraged. You can do whatever you
			// want as long as the stuff you return is a valid property
			// definition
		}

		// or just return $this->props[$key] - e.g. do whatever you want however
		// you want.
		*/

		// but I also need to figure out a way to return the names of _all_
		// the properties - so that configuration forms can work at all.
		// and so that I can check whether the requested property exists
		// or whether access to it is denied

		// then what about providing simple means to access the properties?

		// so then - I need a kind of register, which has the list of all
		// property names and for each of those describes a way to access
		// all those properties

		if ($key == "priobj")
		{
			$prp = array(
				"type" => "select",
				"options" => $this->list_objects(array("class" => CL_PRIORITY,"addempty" => true)),
				"caption" => "Prioriteedi objekt",
				"selected" => $obj["priobj"],
				"store" => "meta",
			);
		}
		elseif ($key == "cfgform")
		{
			$prp = "get_groups";
		};
		return $prp;
	}

	function get_groups($args = array())
	{
		$fields = array();
		// now, if the object is loaded AND has a priority object assigned to it, 
		// generate fields for each member group of the priority object
		if (!$args["priobj"])
		{
			return false;
		};
	
		$ginst = get_instance("users");
		$gdata= $ginst->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));
		// $gdata now contains a list of gid => name pairs

		$pri = get_instance("priority");
		$grps = new aw_array($pri->get_groups($args["priobj"]));
		// $grps now contains a list of gid => priority pairs

		// now we need to create a select element for each
		// member of the group. haha. god dammit, I love this

		// and I also need a list of all configuration forms.
		$cfgforms = $this->list_objects(array("class" => CL_CFGFORM,"addempty" => true));
		$keycount = 0;

		foreach($grps->get() as $gid => $pri)
		{
			$keycount++;
			$fields[$gid] = array(
					"type" => "select",
					"options" => $cfgforms,
					"caption" => $gdata[$gid],
					"selected" => $args["cfgform"][$gid],
					"store" => "meta",
			);
		};
		return $fields;
	}
	
	function get_active_cfg_object($id)
	{
		$ob = $this->get_object($id);
		$gidlist = aw_global_get("gidlist");

		$root_id = 0;
	
		$max_pri = 0;
		$max_gid = 0;
		$pri_inst = get_instance("priority");
		$grps = $pri_inst->get_groups($ob["meta"]["priobj"]);
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
			$max_obj = $ob["meta"]["cfgform"][$max_gid];
		}
		return $max_obj;
	}
};
?>
