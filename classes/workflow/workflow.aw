<?php
/*

	@classinfo syslog_type=ST_WORKFLOW
	@classinfo relatiomgr=yes

	@groupinfo general caption=Üldine

	@default table=objects
	@default group=general

	@property config type=objpicker clid=CL_WORKFLOW_CONFIG field=meta method=serialize
	@caption Konfiguratsioon

	@property preview type=text editonly=1 store=no
	@caption Näita

	// --views
	
	@default view=show
	
	@property id type=hidden table=objects field=oid group=show_actors,show_actions,show_entities,show_processes

	@default store=no
	
	@property show_entities type=callback callback=callback_show_entities group=show_entities
	@caption Olemid

	@property show_actions type=callback callback=callback_show_actions group=show_actions
	@caption Tegevused
	
	@property show_processes type=callback callback=callback_show_processes group=show_processes 
	@caption Protsessid
	
	@property show_actors type=callback callback=callback_show_actors group=show_actors
	@caption Tegijad
	
	@property show_documentation type=text group=show_documentation
	@caption Juhendmaterjalid
	
	@groupinfo show_entities caption=Olemid submit=no
	@groupinfo show_processes caption=Protessid submit=no
	@groupinfo show_actions caption=Tegevused submit=no
	@groupinfo show_actors caption=Tegijad submit=no
	@groupinfo show_documentation caption=Juhendmaterjalid submit=no

*/

class workflow extends class_base
{
	function workflow()
	{
		$this->init(array(
			"tpldir" => "workflow",
			"clid" => CL_WORKFLOW
		));
	}

	function get_property($arr)
        {
		$data = &$arr["prop"];
		$name = $data["name"];
		$retval = PROP_OK;
		switch($name)
		{
			case "preview":
				$data["value"] = html::href(array(
					"url" => $this->mk_my_orb("view",array("id" => $arr["obj_inst"]->id())),
                                	"caption" => "Näita",
                                	"target" => "_blank",
				));
				break;

		};
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
                $retval = PROP_OK;
                switch($data["name"])
                {
			case "show_entities":
				if ($arr["request"]["subgroup"] == "add_entity")
				{
					$this->create_entity($arr);
				}
				else
				{
					// advance existing entities - if there is anything
					// to advance at all
					$this->process_entities($arr);
				}
				break;
		};
		return $retval;
	}

	function callback_mod_retval($arr = array())
	{
		$args = &$arr["args"];
		if (isset($arr["request"]["treeroot"]))
		{
			$args["treeroot"] = $arr["request"]["treeroot"];
		};
	}

	function init_callback_view(&$data,$args = array())
	{
		// try and load the configuration object
		$retval = PROP_OK;

		$cfgid = $args["obj_inst"]->prop("config");


		if (empty($cfgid))
		{
			$data["error"] = "Konfiguratsiooniobjekt on valimata!";
			return PROP_ERROR;
		};
		
		$this->cfg_obj = new object($cfgid);

		$this->treeview_conf_id = $this->cfg_obj->prop("treeview_conf");
		
		if (empty($this->treeview_conf_id))
		{
			$data["error"] = "Puu konfiguratsioon on valimata!";
			return PROP_ERROR;
		};

		$this->treeview_conf = new object($this->treeview_conf_id);

		return $retval;

	}

	function satisfy_any($arr)
	{
		if (isset($args["action"]) && empty($this->actions[$args["action"]]))
		{
			$this->actions[$args["action"]] = new object($args["action"]);
		};	
		
		if (isset($args["actor"]) && empty($this->actors[$args["actor"]]))
		{
			$this->actors[$args["actor"]] =  new object($args["actor"]);
		};	
		
		if (isset($args["process"]) && empty($this->processes[$args["process"]]))
		{
			$this->processes[$args["process"]] = new object($args["process"]);
		};	
	}


	function callback_show_actors($args = array())
	{
		$status = $this->init_callback_view(&$data,$args);
		if ($status == PROP_ERROR)
		{
			return $status;
		};

		$actor_rootmenu_id = $this->cfg_obj->prop("actor_rootmenu");

		if (empty($actor_rootmenu_id))
		{
			$data["error"] = "Tegijate rootmenüü on valimata!";
			return PROP_ERROR;
		}
		else
		{
			$actor_rootmenu = new object($actor_rootmenu_id);
		};
	
		$this->clidlist = array(CL_MENU,CL_ACTOR);	
		$thtml = $this->_build_tree($actor_rootmenu->id());

		load_vcl("table");
		$this->t = new aw_table(array(
			"xml_def" => "workflow/actor_list",
			"layout" => "generic",
		));

		$tb = $this->gen_view_toolbar();
		
		// gaah
		$_data = "<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td valign='top' width='100'>$thtml</td><td valign='top'>" . $tb->get_toolbar() . $this->t->draw() . "</td></tr></table>";

		$data["value"] = $_data;
		$data["type"] = "text";
		$data["no_caption"] = 1;
		return array($data);

	}
	
	function callback_show_actions($args = array())
	{
		$data = array();
		$status = $this->init_callback_view(&$data,$args);
		if ($status == PROP_ERROR)
		{
			return $status;
		};

		$action_rootmenu_id = $this->cfg_obj->prop("action_rootmenu");

		if (empty($action_rootmenu_id))
		{
			$data["error"] = "Tegevuste rootmenüü on valimata!";
			return PROP_ERROR;
		}

		$action_rootmenu = new object($action_rootmenu_id);

		$this->clidlist = array(CL_MENU,CL_ACTION);
		$thtml = $this->_build_tree($action_rootmenu->id());

		load_vcl("table");
		$this->t = new aw_table(array(
			"xml_def" => "workflow/action_list",
			"layout" => "generic",
		));
		
		$tb = $this->gen_view_toolbar();
		
		// gaah
		$_data = "<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td valign='top' width='100'>$thtml</td><td valign='top'>" . $tb->get_toolbar() . $this->t->draw() . "</td></tr></table>";

		$data["value"] = $_data;
		$data["type"] = "text";
		$data["no_caption"] = 1;
	
		return array($data);
	}
	
	function callback_show_entities($args = array())
	{
		// transparent redirect to the "add new entity" form
		$request = $args["request"];
		if (isset($request["subgroup"]) && $request["subgroup"] == "add_entity")
		{
			$retval = $this->callback_add_entity($args);
			return $retval;
		};
		
		// transparent redirect to the "add new entity" form
		if (isset($args["subgroup"]) && $request["subgroup"] == "entity_log")
		{
			$retval = $this->callback_entity_log($args);
			return $retval;
		};

		$data = array();
		$status = $this->init_callback_view(&$data,$args);
		if ($status == PROP_ERROR)
		{
			return $status;
		};

		$entity_rootmenu_id = $this->cfg_obj->prop("entity_rootmenu");

		if (empty($entity_rootmenu))
		{
			$data["error"] = "Olemite rootmenüü on valimata!";
			return PROP_ERROR;
		};

		$entity_rootmenu = new object($entity_rootmenu_id);


		$this->entity_rootmenu_id = $entity_rootmenu->id();

		if (!empty($args["request"]["treeroot"]))
		{
			$this->req_treeroot = $args["request"]["treeroot"];
		}
		else
		{
			$this->req_treeroot = $entity_rootmenu->id();
		};

		$this->treeroot_obj = new object($this->req_treeroot);
			
		$treeroot_clid = $this->treeroot_obj->class_id();
		$this->treeurl = $this->mk_my_orb("view",array(
			"id" => $args["obj_inst"]->id(),
			"group" => "show_entities",
		));
		$this->clidlist = array(CL_MENU,CL_ENTITY);
		$this->parent_list = array();

		$thtml = $this->_build_tree($entity_rootmenu->id());

		load_vcl("table");
		$this->t = new aw_table(array(
			"xml_def" => "workflow/entity_list",
			"layout" => "generic",
		));

		$types = array();

		// first, I need to create a list of all possible processes for
		// this workflow. 

		$processes = $this->list_objects(array(
			"class" => CL_PROCESS,
		));

		$entities = array();

		$entity2process = array();

		if (is_array($processes))
		{
			$proc_keys = array_keys($processes);
			$q = sprintf("SELECT source,target FROM aliases WHERE source IN (%s) AND reltype = %d",						join(",",$proc_keys),RELTYPE_LINK);
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$entities[] = $row["target"];
				$entity2process[$row["target"]] = $row["source"];
			};
		};

		if ($treeroot_clid == CL_ENTITY)
		{
			$_tmp = new object($this->req_treeroot);

			$types[$this->req_treeroot] = $_tmp->name();

			// here I have to query the entities by their respective type...
			// oh man .. how do I do that?
		}
		else
		{
			$entity_list = new object_list(array(
				"parent" => $this->parent_list,
				"class_id" => CL_ENTITY,
			));

			for($o = $member_list->begin(); !$member_list->end(); $o = $member_list->next())
			{
				$types[$o->id()] = $o->name();
			};

		};

		$typelist = array_keys($types);
		$entity2type = array();

		// create a list of config forms
		if (sizeof($entities) > 0)
		{
			$q = sprintf("SELECT source,objects.name,objects.oid FROM aliases LEFT JOIN objects ON (aliases.target = objects.oid) WHERE source IN (%s) AND reltype = %d",
				join(",",$entities),RELTYPE_CFGFORM);
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$typenames[$row["oid"]] = $row["name"];
				$entity2type[$row["source"]] = $row["oid"];
			};	
		};

		if (sizeof($entities) > 0)
		{
			$q = sprintf("SELECT * FROM objects WHERE oid IN (%s)",join(",",$entities));
			$this->db_query($q);
			while($row = $this->db_next())
			{

				$typ = $entity2type[$row["oid"]];
				if ($treeroot_clid == CL_ENTITY && ($typ != $this->req_treeroot))
				{
					continue;
				};

				if ($treeroot_clid["class_id"] == CL_MENU && (empty($types[$typ])))
				{
					continue;
				};

				// now, for each object I also have to figure out which config form it uses
				// or in this context entity type
				$meta = aw_unserialize($row["metadata"]);
				$ctrail = $meta["current_logtrail"];

				$next_action_list = array("0" => "");

				$next_action_list = $next_action_list + $this->get_next_action_for_process(array(
					"process_id" => $ctrail["process"],
					"action_id" => $ctrail["action"],
				));

				if (sizeof($next_action_list) > 1)
				{
					$next_action = html::select(array(
						"name" => "advance[$row[oid]]",
						"options" => $next_action_list,
					));	
				}
				else
				{
					$next_action = "Protsess on lõppenud";
				};	

				$this->satisfy_any($ctrail);

				$this->t->define_data(array(
					"name" => html::href(array(
						"url" => $this->treeurl . "&subgroup=entity_log&oid=$row[oid]",
						"caption" => $row["name"],
					)),
					"process" => $this->processes[$entity2process[$row["oid"]]]["name"],
					"modifiedby" => $row["modifiedby"],
					"actor" => $this->actors[$ctrail["actor"]]["name"],
					"action" => $this->actions[$ctrail["action"]]["name"],
					"type" => $typenames[$entity2type[$row["oid"]]],
					"next_action" => $next_action,
				));

			}


		}

		$this->t->sort_by(array("field" => "type"));	

		$this->read_template("js_popup_menu.tpl");

		$menudata = "";

		foreach($types as $key => $val)
		{
			$this->vars(array(
				"link" => $this->mk_my_orb("view",array("id" => $args["obj"]["oid"],"group" => "show_entities","subgroup" => "add_entity","entity_id" => $key)),
				"text" => $val,
			));

			$menudata .= $this->parse("MENU_ITEM");
		};

		$this->vars(array(
			"MENU_ITEM" => $menudata,
			"id" => "add_entity",
		));

		$menu = $this->parse();
		
		$tb = get_instance("toolbar");
		$tb->add_cdata($menu);

		$tb->add_button(array(
			"name" => "add",
			"tooltip" => "Uus",
			"url" => "",
			"onClick" => "return buttonClick(event, 'add_entity');",
			"img" => "new.gif",
			"class" => "menuButton",
		));
		
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.changeform.submit();",
			"img" => "save.gif",
			"class" => "menuButton",
		));
		$this->read_template("entity_list.tpl");

		classload("vcl/tabpanel");
		$tp = new tabpanel(array("tpl" => "headeronly"));
		$tp->add_tab(array(
			"link" => "#",
			"caption" => "Olemid",
			"active" => 1,
		));
		$tp->add_tab(array(
			"link" => "#",
			"caption" => "Protsessid",
			"active" => 0,
		));
		$tp->add_tab(array(
			"link" => "#",
			"caption" => "Tegevused",
			"active" => 0,
		));
		$tp->add_tab(array(
			"link" => "#",
			"caption" => "Tegijad",
			"active" => 0,
		));

		$this->vars(array(
			"add_entity_url" => $this->mk_my_orb("view",array("id" => $args["obj"]["oid"],"group" => "show_entities","subgroup" => "add_entity")),
			"tree" => $thtml,
			"toolbar" => $tb->get_toolbar(),
			"table" => $tp->get_tabpanel(array("content" => $this->t->draw())),
		));

		$data["value"] = $this->parse();
		$data["type"] = "text";
		$data["no_caption"] = 1;

		$tmp = array(
			"type" => "hidden",
			"name" => "treeroot",
			"value" => $this->treeroot,
		);
	
		return array($data,$tmp);
	}

	function _build_tree($parent)
	{
		$this->tree = array();
		$this->oidlist = array();

		$row = $this->get_object(array(
			"oid" => $parent,
		));

		if ($this->req_treeroot == $parent)
		{
			$this->parent_list[] = $parent;
			$this->under_treeroot = true;
			$this->treeroot = $parent;
		}
		else
		{
			$this->under_treeroot = false;
		};
	
		$row["link"] = $this->treeurl;
		$this->id = 1;
		/*
		if (is_array($this->items))
		{
			foreach($this->items as $key => $val)
			{
				$this->tree[0][$this->id] = $val;
				$this->id++;
			};
		};
		*/
		$this->tree[0][$this->id] = $row;
		$this->parent2id = array($parent => $this->id);
		$this->ic = get_instance("icons");
		$this->_rec_build_tree($parent);
		
		/*
		print "<pre>";
		print_r($this->tree);
		print "</pre>";
		*/
	
		$treeview = get_instance("vcl/treeview");
		return $treeview->create_tree_from_array(array(
			"parent" => 0,
			"data" => $this->tree,
			"shownode" => $this->treeroot,
			"linktarget" => "_self",
		));

	}
	
	function _rec_build_tree($parent)
	{
		$_objlist = $this->get_objects_by_class(array(
			"parent" => $parent,
			"class" => $this->clidlist,
			"fields" => "oid,name,parent,class_id",
		));

                while ($row = $this->db_next())
                {
                        $row["name"] = str_replace("\"","&quot;", $row["name"]);
			$this->id++;
			$this->parent2id[$row["oid"]] = $this->id;
			$pid = $this->parent2id[$row["parent"]];

			$row["link"] = $this->treeurl . "&treeroot=" . $row["oid"];
			if ($row["oid"] == $this->req_treeroot)
			{
				$row["name"] = "<span style='background: #ccc'>" . $row["name"] . "</span>";
			};

			if ($row["oid"] == $this->req_treeroot)
			{
				$this->treeroot = $this->id;
				$this->under_treeroot = true;
			};

			if ($this->under_treeroot && ($row["class_id"] == CL_MENU))
			{
				$this->parent_list[] = $row["oid"];
			};

			$row["icon_url"] = ($row["class_id"] == CL_MENU) ? "" : $this->ic->get_icon_url($row["class_id"],"");

                        $this->tree[$pid][$this->id] = $row;
			$this->oidlist[] = $row["oid"];
                        $this->save_handle();
                        $this->_rec_build_tree($row["oid"]);
			if ($row["oid"] == $this->req_treeroot)
			{
				$this->under_treeroot = false;
			};
                        $this->restore_handle();
                }

	}	

	function callback_add_entity(&$data)
	{
		// load the entity
		$entity_obj = new object($data["request"]["entity_id"]);
		// now I need to figure out which configuration form 
		// is used by that entity
		
		// and then load that and display the stuff to the user
		$cfgform_id = $entity_obj->prop("entity_cfgform");

		$cfgform_obj = new object($cfgform_id);

		// and somehow I have to stick the contents of the process list
		// and actor list to the top of that list

		$xprops = array(
			"entity_process" => array(
				"name" => "entity_process",
				"type" => "objpicker",
				"caption" => "Protsess",
				"clid" => "CL_PROCESS",
			),
			"entity_actor" => array(
				"name" => "entity_actor",
				"type" => "objpicker",
				"caption" => "Tegija",
				"clid" => "CL_ACTOR",
			),
		);

		$t = get_instance("doc");
		$xprops = $xprops + $t->get_properties_by_group(array(
			"content" => $cfgform_obj["meta"]["xml_definition"],
			"group" => "general",
			//"values" => array("id" => $event_id) + $row,
		));


		// now the list of properties inside that entity
		unset($xprops["sbt"]);
		unset($xprops["aliasmgr"]);

		$xprops[] = array(
			"type" => "hidden",
			"name" => "entity_id",
			"value" => $data["request"]["entity_id"],
		);

		$xprops[] = array(
			"type" => "submit",
			"value" => "Salvesta",
		);

		return $xprops;
	}
	
	function callback_entity_log($args = array())
	{
		$data = array();
		$data["type"] = "text";
		$data["caption"] = "Log";
		$oid = $args["request"]["oid"];
		load_vcl("table");
		$this->t = new aw_table(array("xml_def" => "workflow/entity_log"));
		$q = "SELECT * FROM logtrail WHERE obj_id = $oid";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->satisfy_any(array(
				"actor" => $row["actor_id"],
				"action" => $row["action_id"],
				"process" => $row["process_id"],
			));

			$this->t->define_data(array(
				"tm" => $this->time2date($row["tm"]),
				"actor" => $this->actors[$row["actor_id"]]["name"],
				"action" => $this->actions[$row["action_id"]]["name"],
				"process" => $this->processes[$row["process_id"]]["name"],
				"uid" => $row["uid"],
			));
		};
		$data["value"] = $this->t->draw();
		return array($data);

	}

	function callback_show_processes($args = array())
	{
		// transparent redirect to the "add new entity" form
		if (isset($args["request"]["subgroup"]))
		{
			if ($args["request"]["subgroup"] == "add_process")
			{
				$retval = $this->callback_add_process($args);
				return $retval;
			};
			if ($args["request"]["subgroup"] == "mod_process")
			{
				$retval = $this->callback_mod_process($args);
				return $retval;
			};
		};
		$data = array();
		$status = $this->init_callback_view(&$data,$args);
		if ($status == PROP_ERROR)
		{
			return $status;
		};

		$process_rootmenu_id = new object($this->cfg_obj->prop("process_rootmenu"));

		if (empty($process_rootmenu_id))
		{
			$data["error"] = "Protsesside rootmenüü on valimata!";
			return PROP_ERROR;
		};

		$process_rootmenu = new object($process_rootmenu_id);

		
		if (!empty($args["request"]["treeroot"]))
		{
			$this->req_treeroot = $args["request"]["treeroot"];
		}
		else
		{
			$this->req_treeroot = $process_rootmenu->id();
		};

		$this->treeroot_obj = new object($this->req_treeroot);

		$treeroot_clid = $this->treeroot_obj->class_id();
		$this->treeurl = $this->mk_my_orb("view",array(
			"id" => $args["obj_inst"]->id(),
			"group" => "show_processes",
		));

		$this->clidlist = array(CL_MENU);
		$this->parent_list = array();

		$this->items = array();
		$this->items[] = array("name" => "Põhiprotsessid");
		$this->items[] = array("name" => "Tugiprotsessid");
		
			
		$thtml = $this->_build_tree($process_rootmenu->id());

		load_vcl("table");
		$this->t = new aw_table(array(
			"xml_def" => "workflow/process_list",
			"layout" => "generic",
		));

		$processes =  new object_list(array(
			"parent" => $this->parent_list,
			"class_id" => CL_PROCESS,
		));
		
		$ed_url = $this->mk_my_orb("view",array("id" => $args["obj_inst"]->id(),"group" => "show_processes","subgroup" => "mod_process"));
                        
		for($o = $processes->begin(); !$processes->end(); $o = $processes->next())
		{
			$ra = $val->prop("root_action");
			$this->satisfy_any(array("action" => $ra));
			$val["root_action"] = $this->actions[$ra]["name"];
			$val["name"] = html::href(array(
				"url" => $ed_url . "&oid=" . $o->id(),
				"caption" => $o->name(),
			));
			$this->t->define_data($val);
		}
		
		$tb = get_instance("toolbar");

		if (isset($menu))
		{
			$tb->add_cdata($menu);
		};

		$tb->add_button(array(
			"name" => "add",
			"tooltip" => "Uus protsess",
			"url" => $this->mk_my_orb("view",array("id" => $args["obj_inst"]->id(),"group" => "show_processes","subgroup" => "add_process")),
			"img" => "new.gif",
			"class" => "menuButton",
		));

		// gaah
		$_data = "<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td valign='top' width='100'>$thtml</td><td valign='top'>" . $tb->get_toolbar() . $this->t->draw() . "</td></tr></table>";

		$data["value"] = $_data;
		$data["type"] = "text";
		$data["no_caption"] = 1;
		return array($data);
	}
	
	function callback_add_process($args = array())
	{
		$this->read_template("process_wrapper.tpl");
		$this->cfg_obj = new object($args["obj_inst"]->prop("config"));
		$return_url = $this->mk_my_orb("view",array("id" => $args["obj_inst"]->id(),"group" => "show_processes","b1" => 1));
		$process_rootmenu_id = $this->cfg_obj->prop("process_rootmenu");
		$this->vars(array(
			"add_process_link" => $this->mk_my_orb("new",array("parent" => $process_rootmenu_id,"return_url" => urlencode($return_url)),"process"),
		));
		$data = array(
			"value" => $this->parse(),
			"type" => "text",
			"no_caption" => 1,
		);
		return array($data);
	}
	
	function callback_mod_process($args = array())
	{
		$this->read_template("process_wrapper.tpl");
		$this->cfg_obj = new object($args["obj_inst"]->prop("config"));
		$return_url = $this->mk_my_orb("view",array("id" => $args["obj_inst"]->id(),"group" => "show_processes","b1" => 1));
		$process_rootmenu_id = $this->cfg_obj->prop("process_rootmenu");
		$this->vars(array(
			"add_process_link" => $this->mk_my_orb("change",array("id" => $args["obj_inst"]->id(),"return_url" => urlencode($return_url)),"process"),
		));
		$data = array(
			"value" => $this->parse(),
			"type" => "text",
			"no_caption" => 1,
		);
		return array($data);
	}

	function view($args = array())
	{
		return $this->change(array(
			"id" => $args["id"],
			"oid" => $args["oid"],
			"cb_view" => "show",
			"action" => "change",
			"group" => $args["group"],
			"sgid" => $args["sgid"],
			"subgroup" => $args["subgroup"],
			"entity_id" => $args["entity_id"],
			"treeroot" => $args["treeroot"],
		));
	}

	function create_entity($args = array())
	{
		// now I have to process $args[form_data] and create the entity object

		// then load the chosen process object and create a relation between
		// that and the entity

		// then figure out the root process of chosen process object 
		// and create a relation between that and the entity

		// and then I should be on my way 
		if (!$args["request"]["entity_process"])
		{
			die("you did not pick a process<br />");
		};

		$proc_obj = new object($args["request"]["entity_process"]);

		// this sorts the data by tables

		/*
		$def = get_instance("doc");
		$res = $def->process_form_data(array(
			"group" => "general",
			"group_by" => "table",
			"request" => $args["rquest"],
		));
		*/

		$objdata = $res["objects"];
		$cldata = $res["documents"];

		$objdata["metadata"] = $objdata["meta"];
		unset($objdata["meta"]);

		// now I have to figure out the parent
		$obj = new object($args["obj_inst"]->id());

		$cf_obj = new object($obj->prop("config"));

		$entity_root = $cf_obj->prop("entity_rootmenu");
		// I should try and load the entity objet to see whether it
		// really is a menu
		if (!$entity_root)
		{
			die("entity root menu is not set<br />");
		}
		
		// but how do I know which config form was used to create this particular object?
		// and what do I do, if the original config form changes? Well do that I say - 
		// tough luck.

		// but that relation really has nothing to do with neither workflow or the
		// document class. That relation type should be defined somewhere in the base
		// types. But where? Since 0 has already been used .. what do I have left?

		// but that relation object .. what if I have multiple relations to one config form?
		// deem, this is more complicated than it should be

		// and I also have to bind this new object to our process, so that I can
		// properly show it, where needed.

		// hm, what I use relations for all of that stuff?

		$objdata["parent"] = $entity_root;
		$objdata["name"] = $cldata["title"];
		$objdata["class_id"] = CL_DOCUMENT;

		$_entity_id = $this->new_object($objdata);

		// now I need to create a bunch of relations:
		// 1 - between the object and the config form, so that I know
		// which form should be used to edit it.
		$this->addalias(array(
			"id" => $_entity_id,
			"alias" => $args["form_data"]["entity_id"],
			"reltype" => RELTYPE_CFGFORM,
		));
		
		// 2 - between the process and the object, so that I know
		// in which process the object currently is
		$this->addalias(array(
			"id" => $proc_obj["oid"],
			"alias" => $_entity_id,
			"reltype" => RELTYPE_LINK,
		));

		// 3 - somewhere somehow I have list of actions and clickin on one
		// should list me all objects that are currently in this stage
		// but _also_ in this process

		$_entity_process = $args["form_data"]["entity_process"];
		$_entity_action = $proc_obj["meta"]["root_action"];
		$_entity_actor = $args["form_data"]["entity_actor"];
		$_entity_uid = aw_global_get("uid");
		$_entity_tm = time();

		$q = "INSERT INTO logtrail (obj_id,actor_id,action_id,process_id,tm,uid)
			VALUES ('$_entity_id','$_entity_actor','$_entity_action','$_entity_process',
				'$_entity_tm','$_entity_uid')";

		$this->db_query($q);

		$current_logtrail = array(
			"actor" => $_entity_actor,
			"action" => $_entity_action,
			"process" => $_entity_process,
		);

		$ent_obj = new object($_entity_id);

		$ent_obj->set_meta("current_logtrail",$current_logtrail);

		// now let's retrieve the process object

		// now I have to create the relations between
		// a) process and the newly created entity
		// b) root action of the process and newly created entity
		// c) actor and newly created entity

		// AND. I need to be able to query that information very
		// fast.

		// so, how the hell do I store the relations in a way
		// that I can ask for all of them in .. what is possibly
		// one query.

		// xx - yy - zz

		// well, for one .. I can first get a list of all objects
		// to be shown.

		// the retrieve the relations for each of those

		// this is the bloody audit trail, that needs to stay
		// behind

		// and then there is the relation object, that needs to
		// know the current state of the object active relations

		// for an entity those relations are:

		// 1 - the process that handles the entity
		// 2 - the action inside that process where the entity is currently assigned to
		// 3 - and remember, I need to store the ID-s

		/*
		print "<h1>---</h1>";
		print "<pre>";
		print_r($args);
		print "</pre>";
		*/
	}

	function process_entity($args = array())
	{
		extract($args);
		// create a new record in logtrail database
		$_entity_tm = time();
		$_entity_uid = aw_global_get("uid");
		$_entity_id = $args["entity_id"];
		$_entity_process = $args["process_id"];
		$_entity_actor = $args["actor_id"];
		$_entity_action = $args["action_id"];
		
		$q = "INSERT INTO logtrail (obj_id,actor_id,action_id,process_id,tm,uid)
			VALUES ('$_entity_id','$_entity_actor','$_entity_action','$_entity_process',
				'$_entity_tm','$_entity_uid')";

		$this->db_query($q);

		$current_logtrail = array(
			"actor" => $_entity_actor,
			"action" => $_entity_action,
			"process" => $_entity_process,
		);

		$ent_obj = new object($_entity_id);
		$ent_obj->set_meta("current_logtrail",$current_logtrail);

	}

	function gen_view_toolbar($args = array())
	{
		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "add",
			"tooltop" => "Uus",
			"url" => "#",
			"img" => "new.gif",
			"class" => "menuButton",
		));
		return $tb;

	}

	////
        // !Returns the ID of the next action in this process
        // this is where we should use the heavy logic of relation objects
        // to follow branches and stuff
        function get_next_action_for_process($args = array())
        {
		$this->save_handle();
                $process_id = $args["process_id"];
                $action_id = $args["action_id"];
		// load the processes on demand
		$this->satisfy_any(array("process" => $args["process_id"]));

		$proc_data = $this->processes[$process_id];

		$meta = aw_unserialize($proc_data["metadata"]);

		$action_info = $meta["action_info"];
		$next_actions = array();
		$next = false;

		if (isset($action_info[$args["action_id"]]))
		{
			$next = $action_info[$args["action_id"]];

			if (is_array($next))
			{
				foreach($next as $val)
				{
					$this->satisfy_any(array("action" => $val));

					if (isset($this->actions[$val]))
					{
						$next_actions[$val] = $this->actions[$val]["name"];
					};	
				};	
			};	
		};

		return $next_actions;
        }

	function process_entities($args = array())
	{
		$to_advance = new aw_array($args["request"]["advance"]);
		foreach($to_advance->get() as $key => $val)
		{
			// advance those entities to the next stadium
			if ($val > 0)
			{
				$ent = new object($key);

				$ctrail = $ent->meta("current_logtrail");

				$this->process_entity(array(
					"entity_id" => $key,
					"action_id" => $val,
					"process_id" => $ctrail["process"],
					"actor_id" => $ctrail["actor"],
				));

			};
		};
	}
	

}
?>
