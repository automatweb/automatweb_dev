<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/cfgmanager.aw,v 2.1 2002/10/18 11:32:01 duke Exp $
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
		$strings = array(
			"title_add" => "Lisa konfiguratsioonihaldur",
			"title_change" => "Muuda konfiguratsioonihaldurit",
		);

		return $strings[$key];
	}

	function get_properties($args = array())
	{
		$fields = array();
		// first and foremost, we need generate the picker for choosing priority objects
		$fields["priobj"] = array(
			"type" => "select",
			"options" => $this->list_objects(array("class" => CL_PRIORITY,"addempty" => true)),
			"caption" => "Prioriteedi objekt",
			"selected" => $args["priobj"],
			"store" => "meta",
                );
	
		// since I dont want to clutter this method more than necessary,
		// I use a callback to retrieve a list of groups
		$fields["cfgform"] = "get_groups";
		return $fields;
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
