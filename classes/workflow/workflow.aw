<?php
/*

	@classinfo syslog_type=ST_WORKFLOW
	@classinfo relationmgr=yes

	@groupinfo general caption=Üldine

	@default table=objects
	@default group=general

	@property config type=relpicker reltype=RELTYPE_WORKFLOW_CONFIG field=meta method=serialize
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

	@reltype WORKFLOW_CONFIG clid=CL_WORKFLOW_CONFIG value=1
	@caption Konfiguratsioon 

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

		$entity_rootmenu_id = $this->cfg_obj->prop("entity_rootmenu");

		if (empty($entity_rootmenu_id))
		{
			$data["error"] = "Olemite rootmenüü on valimata!";
			return PROP_ERROR;
		};

		$this->entity_rootmenu = new object($entity_rootmenu_id);

		$action_rootmenu_id = $this->cfg_obj->prop("action_rootmenu");

		if (empty($action_rootmenu_id))
		{
			$data["error"] = "Tegevuste rootmenüü on valimata!";
			return PROP_ERROR;
		}

		$this->action_rootmenu = new object($action_rootmenu_id);

		$process_rootmenu_id = new object($this->cfg_obj->prop("process_rootmenu"));

		if (empty($process_rootmenu_id))
		{
			$data["error"] = "Protsesside rootmenüü on valimata!";
			return PROP_ERROR;
		};

		$this->process_rootmenu = new object($process_rootmenu_id);


		return $retval;

	}

	function satisfy_any($arr)
	{
		if (isset($arr["action"]) && empty($this->actions[$arr["action"]]))
		{
			$this->actions[$arr["action"]] = obj($arr["action"]);
		};	
		
		if (isset($arr["actor"]) && empty($this->actors[$arr["actor"]]))
		{
			$this->actors[$arr["actor"]] =  obj($arr["actor"]);
		};	
		
		if (isset($arr["process"]) && empty($this->processes[$arr["process"]]))
		{
			$this->processes[$arr["process"]] = obj($arr["process"]);
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


		$process_tree = new object_tree(array(
			"parent" => $this->process_rootmenu,
			"class_id" => CL_PROCESS
		));

		classload("vcl/treeview");
		$tv = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "wrkflw",
				"persist_state" => true,
			),
			"root_item" => $this->process_rootmenu,
			"ot" => $process_tree,
			"var" => "process_filter"
		));

		$thtml = $tv->finalize_tree();

		switch($_GET["sub_tab"])
		{
			default:
			case "entity":
				// get entity type list from ot
				$entity_list = $entity_tree->to_list();
				$entity_types = array();
				for ($o = $entity_list->begin(); !$entity_list->end(); $o = $entity_list->next())
				{
					if ($o->class_id() == CL_ENTITY)
					{
						$entity_types[] = $o->id();
					}
				}

				$list_html = $this->_do_entity_table(array(
					"filter" => array(
						"entity_type" => $entity_types
					)
				));
				break;

			case "process":
				// get list of processes, filtered by entity type
				$entity_list = $entity_tree->to_list();
				$processes = array();
				for ($o = $entity_list->begin(); !$entity_list->end(); $o = $entity_list->next())
				{
					if ($o->class_id() == CL_ENTITY)
					{
						$processes[$o->prop("entity_process")] = $o->prop("entity_process");
					}
				}
				$list_html = $this->_do_process_table(array(
					"filter" => array(
						"oid" => $processes
					)
				));
				break;

			case "actions":
				// get list of processes, filtered by entity type
				$entity_list = $entity_tree->to_list();
				$processes = array();
				for ($o = $entity_list->begin(); !$entity_list->end(); $o = $entity_list->next())
				{
					if ($o->class_id() == CL_ENTITY)
					{
						$processes[$o->prop("entity_process")] = $o->prop("entity_process");
					}
				}
				$list_html = $this->_do_actions_table(array(
					"filter" => array(
						"processes" => $processes
					)
				));
				break;

			case "actors":
				$entity_list = $entity_tree->to_list();
				$actors = array();
				for ($o = $entity_list->begin(); !$entity_list->end(); $o = $entity_list->next())
				{
					$actors[$o->prop("entity_actor")] = $o->prop("entity_actor");
				}
				$list_html = $this->_do_actor_table(array(
					"filter" => array(
						"oid" => $actors
					)
				));
				break;
		}

		/*$this->clidlist = array(CL_MENU,CL_ACTION);
		$thtml = $this->_build_tree($action_rootmenu->id());

		load_vcl("table");
		$this->t = new aw_table(array(
			"xml_def" => "workflow/action_list",
			"layout" => "generic",
		));
		*/

		$tb = $this->gen_view_toolbar();
		
		// gaah
		$_data = "<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td valign='top' width='100'>$thtml</td><td valign='top'>" . $tb->get_toolbar() . $list_html . "</td></tr></table>";
		

		$data["value"] = $_data;
		$data["type"] = "text";
		$data["no_caption"] = 1;
	
		return array($data);
	}
	
	function callback_show_entities($args = array())
	{
		$request = $args["request"];
		if (isset($request["subgroup"]) && $request["subgroup"] == "add_entity")
		{
			$retval = $this->callback_add_entity($args);
			return $retval;
		};
		
		// transparent redirect to the "add new entity" form
		if (isset($request["subgroup"]) && $request["subgroup"] == "entity_log")
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

		$entity_tree_filter = new object_tree(array(
			"parent" => ($_GET["entity_filter"] ? $_GET["entity_filter"] : $this->entity_rootmenu),
			"class_id" => CL_ENTITY
		));
		$entity_list = $entity_tree_filter->to_list();

		$tb = get_instance("toolbar");

		switch($_GET["sub_tab"])
		{
			default:
			case "entity":
				$tb->add_menu_button(array(
					"name" => "add",
					"tooltip" => "Uus",
				));

				// get entity type list from ot
				$entity_types = array();
				for ($o = $entity_list->begin(); !$entity_list->end(); $o = $entity_list->next())
				{
					if ($o->class_id() == CL_ENTITY)
					{
						$entity_types[] = $o->id();

						$tb->add_menu_item(array(
							"parent" => "add",
							"link" => $this->mk_my_orb("view",array(
								"id" => $args["obj_inst"]->id(),
								"group" => "show_entities",
								"subgroup" => "add_entity",
								"entity_id" => $o->id(),
							)),
							"text" => $o->name(),
						));
					}
				}

				$list_html = $this->_do_entity_table(array(
					"filter" => array(
						"entity_type" => $entity_types
					)
				));

				$tb->add_button(array(
					"name" => "save",
					"tooltip" => "Salvesta",
					"url" => "javascript:document.changeform.submit();",
					"img" => "save.gif",
					"class" => "menuButton",
				));

				break;

			case "process":
				// get list of processes, filtered by entity type
				$processes = array();
				for ($o = $entity_list->begin(); !$entity_list->end(); $o = $entity_list->next())
				{
					if ($o->class_id() == CL_ENTITY)
					{
						$processes[$o->prop("entity_process")] = $o->prop("entity_process");
					}
				}
				$list_html = $this->_do_process_table(array(
					"filter" => array(
						"oid" => $processes
					)
				));
				break;

			case "actions":
				// get list of processes, filtered by entity type
				$processes = array();
				for ($o = $entity_list->begin(); !$entity_list->end(); $o = $entity_list->next())
				{
					if ($o->class_id() == CL_ENTITY)
					{
						$processes[$o->prop("entity_process")] = $o->prop("entity_process");
					}
				}
				$list_html = $this->_do_actions_table(array(
					"filter" => array(
						"processes" => $processes
					)
				));
				break;

			case "actors":
				$actors = array();
				for ($o = $entity_list->begin(); !$entity_list->end(); $o = $entity_list->next())
				{
					if ($o->class_id() == CL_ENTITY)
					{
						$actors[$o->prop("entity_actor")] = $o->prop("entity_actor");
					}
				}
				$list_html = $this->_do_actor_table(array(
					"filter" => array(
						"oid" => $actors
					)
				));
				break;
		}

		return $this->_finalize_data(array(
			"rootmenu" => $this->entity_rootmenu,
			"tb" => $tb,
			"list_html" => $list_html,
			"data" => $data
		));
	}

	function _build_tree($parent)
	{
		$this->tree = array();
		$this->oidlist = array();

		$root_item = new object($parent);

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
		$this->tree = get_instance(CL_TREEVIEW);
		$this->tree->start_tree(array(
			"type" => TREE_DHTML,
			"root_url" => $this->treeurl,
			"tree_id" => "wrkflw",
			"persist_state" => true,
		));
		$this->tree->add_item(0,array(
			"name" => parse_obj_name($root_item->name()),
			"id" => $this->id,
			"url" => $this->treeurl,
		));

		$this->parent2id = array($parent => $this->id);
		$this->ic = get_instance("icons");
		$this->_rec_build_tree($parent);

		return $this->tree->finalize_tree();
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

			$this->tree->add_item($pid,$row);

			//$this->tree[$pid][$this->id] = $row;
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
		// yuk
		$t->obj_inst = new object();

		$frm = $this->get_object($cfgform_id);
		$t->cfgform = $frm;
		$t->cfgform_id = $frm["oid"];

		$all_props = $t->get_active_properties(array(
			"group" => "general",
		));

		$all_props["cfgform_id"] = array(
			"type" => "hidden",
			"name" => "cfgform",
			"value" => $frm["oid"],
		);
		
		unset($all_props["sbt"]);
		unset($all_props["aliasmgr"]);

		$xprops = $xprops + $t->parse_properties(array(
			"properties" => $all_props,
			"name_prefix" => "emb",
		));

		// now the list of properties inside that entity

		$xprops[] = array(
			"type" => "hidden",
			"name" => "entity_id",
			"value" => $data["request"]["entity_id"],
		);

		$xprops[] = array(
			"type" => "hidden",
			"name" => "subgroup",
			"value" => "add_entity",
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
		$this->t = new aw_table(array("xml_def" => "workflow/entity_log","layout" => "generic"));
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
				"actor" => $this->actors[$row["actor_id"]]->name(),
				"action" => $this->actions[$row["action_id"]]->name(),
				"process" => $this->processes[$row["process_id"]]->name(),
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

		$process_tree = new object_tree(array(
			"parent" => $this->process_rootmenu,
			"class_id" => CL_PROCESS
		));

		classload("vcl/treeview");
		$tv = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "wrkflw",
				"persist_state" => true,
			),
			"root_item" => $this->process_rootmenu,
			"ot" => $process_tree,
			"var" => "process_filter"
		));

		$thtml = $tv->finalize_tree();

		switch($_GET["sub_tab"])
		{
			default:
			case "entity":
				// get entity type list from ot
				$process_list = $process_tree->to_list();
				$etype_list = new object_list(array(
					"class_id" => CL_ENTITY,
					"entity_process" => $process_list->ids()
				));
				$list_html = $this->_do_entity_table(array(
					"filter" => array(
						"entity_type" => $etype_list->ids()
					)
				));
				break;

			case "process":
				// get list of processes, filtered by entity type
				$process_list = $process_tree->to_list();
				$list_html = $this->_do_process_table(array(
					"filter" => array(
						"oid" => $process_list->ids()
					)
				));
				break;

			case "actions":
				$process_list = $process_tree->to_list();
				$list_html = $this->_do_actions_table(array(
					"filter" => array(
						"processes" => $process_list->ids()
					)
				));
				break;

			case "actors":
				$process_list = $process_tree->to_list();
				$etype_list = new object_list(array(
					"class_id" => CL_ENTITY,
					"entity_process" => $process_list->ids()
				));
				$actors = array();
				for ($o = $etype_list->begin(); !$etype_list->end(); $o = $etype_list->next())
				{
					$actors[$o->prop("entity_actor")] = $o->prop("entity_actor");
				}
				$list_html = $this->_do_actor_table(array(
					"filter" => array(
						"oid" => $actors
					)
				));
				break;
		}

		
		/*if (!empty($args["request"]["treeroot"]))
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
			$ra = $o->prop("root_action");
			$this->satisfy_any(array("action" => $ra));
			$val["root_action"] = $this->actions[$ra]->name();
			$val["name"] = html::href(array(
				"url" => $ed_url . "&oid=" . $o->id(),
				"caption" => $o->name(),
			));
			$this->t->define_data($val);
		}*/
		
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

		classload("vcl/tabpanel");
		$tp = tabpanel::simple_tabpanel(array(
			"panel_props" => array("tpl" => "headeronly"),
			"var" => "sub_tab", 
			"default" => "entities",
			"opts" => array(
				"entities" => "Olemid",
				"process" => "Protsessid",
				"actions" => "Tegevused",
				"actors" => "Tegijad"
			)
		));

		// gaah
		$_data = "<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td valign='top' width='20%'>$thtml</td><td valign='top'>" . $tb->get_toolbar() . $tp->get_tabpanel(array("content" => $list_html)) . "</td></tr></table>";

		$data["value"] = $_data;
		$data["type"] = "text";
		$data["no_caption"] = 1;
		return array($data);
	}
	
	function callback_add_process($args = array())
	{
		// this is SO bad .. adding processes should go through the alias manager somehow
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

	/**  
		
		@attrib name=view params=name default="0"
		
		@param id required type=int
		@param group optional
		@param subgroup optional
		@param treeroot optional
		@param sgid optional
		@param entity_id optional
		@param oid optional type=int
		@param sub_tab optional
		@param entity_filter optional
		
		@returns
		
		
		@comment

	**/
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

		// hm, what if I use relations for all of that stuff?

		$t = get_instance("doc");
		$emb = $args["request"]["emb"];
		$emb["parent"] = $entity_root;
		
		// a hack that makes submit() return only the id of the newly created object
		$t->id_only = true;
		$_entity_id = $t->submit($emb);

		/*
		$objdata["parent"] = $entity_root;
		$objdata["name"] = $cldata["title"];
		$objdata["class_id"] = CL_DOCUMENT;
		*/

		// I need to create a new 

		//$_entity_id = $this->new_object($objdata);

		// now I need to create a bunch of relations:
		// 1 - between the object and the config form, so that I know
		// which form should be used to edit it.
		$this->addalias(array(
			"id" => $_entity_id,
			"alias" => $args["request"]["entity_id"],
			"reltype" => RELTYPE_CFGFORM,
		));
		
		// 2 - between the process and the object, so that I know
		// in which process the object currently is
		$this->addalias(array(
			"id" => $proc_obj->id(),
			"alias" => $_entity_id,
			"reltype" => RELTYPE_LINK,
		));

		// 3 - somewhere somehow I have list of actions and clickin on one
		// should list me all objects that are currently in this stage
		// but _also_ in this process

		$_entity_process = $args["request"]["entity_process"];
		$_entity_action = $proc_obj->prop("root_action");
		$_entity_actor = $args["request"]["entity_actor"];
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

		$ent_obj->save();

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

	/**  
		
		@attrib name=process_entity params=name default="0"
		
		@param id required type=int
		@param entity_id required type=int
		@param process_id required type=int
		@param actor_id required type=int
		
		@returns
		
		
		@comment

	**/
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
		$ent_obj->save();

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

		$meta = $this->processes[$process_id]->meta();

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
						$next_actions[$val] = $this->actions[$val]->name();
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

	// params: filter - the object_list filter for entity_instance list	
	function _do_entity_table($arr)
	{
		load_vcl("table");
		$t = new aw_table(array(
			"xml_def" => "workflow/entity_list",
			"layout" => "generic",
		));

		$filter = array("class_id" => CL_WORKFLOW_ENTITY_INSTANCE) + $arr["filter"];
		
		$ol = new object_list($filter);

		for ($e = $ol->begin(); !$ol->end(); $e = $ol->next())
		{
			$type_o = obj($e->prop("entity_type"));
			$actor_o = obj($type_o->prop("entity_actor"));
			$process_o = obj($type_o->prop("entity_process"));

			$mod = $e->modifiedby();

			$t->define_data(array(
				"name" => $e->name(),
				"type" => $type_o->name(),
				"actor" => $actor_o->name(),
				"modifiedby" => $mod->name(),
				"process" => $process_o->name()
			));
		}

		$t->set_default_sortby("name");
		$t->sort_by();
		return $t->draw();
	}

	// params: filter - the object_list filter for entity_instance list	
	function _do_process_table($arr)
	{
		load_vcl("table");
		$t = new aw_table(array(
			"xml_def" => "workflow/process_list",
			"layout" => "generic",
		));

		$filter = array("class_id" => CL_PROCESS) + $arr["filter"];
		
		$ol = new object_list($filter);

		for ($e = $ol->begin(); !$ol->end(); $e = $ol->next())
		{
			$ra_o = obj($e->prop("root_action"));

			$mod = $e->modifiedby();

			$t->define_data(array(
				"name" => $e->name(),
				"modifiedby" => $mod->name(),
				"root_action" => $ra_o->name(),
			));
		}

		$t->set_default_sortby("name");
		$t->sort_by();
		return $t->draw();
	}

	// params: filter - the object_list filter for actor list
	function _do_actor_table($arr)
	{
		load_vcl("table");
		$t = new aw_table(array(
			"xml_def" => "workflow/actor_list",
			"layout" => "generic",
		));

		$filter = array("class_id" => CL_ACTOR) + $arr["filter"];
		
		$ol = new object_list($filter);

		for ($e = $ol->begin(); !$ol->end(); $e = $ol->next())
		{
			$t->define_data(array(
				"actor" => $e->name(),
			));
		}

		$t->set_default_sortby("name");
		$t->sort_by();
		return $t->draw();
	}

	// params: processes - list of processes whose actions to display
	function _do_actions_table($arr)
	{
		load_vcl("table");
		$t = new aw_table(array(
			"xml_def" => "workflow/action_list",
			"layout" => "generic",
		));

		$filter = array(
			"class_id" => CL_PROCESS,
			"oid" => $arr["filter"]["processes"]
		);
		
		$ol = new object_list($filter);

		for ($e = $ol->begin(); !$ol->end(); $e = $ol->next())
		{
			$conn = $e->connections_from(array(
				"type" => 10 // RELTYPE_ACTION from process
			));
			foreach($conn as $c)
			{
				$a = $c->to();

				$mod = $e->modifiedby();

				$t->define_data(array(
					"actor" => "",
					"uid" => $mod->name(),
					"process" => $e->name(),
					"action" => $a->name()
				));
			}
		}

		$t->set_default_sortby("action");
		$t->sort_by();
		return $t->draw();
	}

	function _get_tree($arr)
	{
		$tree = new object_tree(array(
			"parent" => $arr["rootmenu"],
			"class_id" => $arr["class_id"]
		));

		classload("vcl/treeview");
		$tv = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "wrkflw",
				"persist_state" => true,
			),
			"root_item" => $arr["rootmenu"],
			"ot" => $tree,
			"var" => "tree_filter"
		));

		return $tv->finalize_tree();
	}

	function _get_tabs($content)
	{
		$tp = tabpanel::simple_tabpanel(array(
			"panel_props" => array("tpl" => "headeronly"),
			"var" => "sub_tab", 
			"default" => "entities",
			"opts" => array(
				"entities" => "Olemid",
				"process" => "Protsessid",
				"actions" => "Tegevused",
				"actors" => "Tegijad"
			)
		));

		return $tp->get_tabpanel(array("content" => $content));
	}

	function _finalize_data($arr)
	{
		extract($arr);
		// fucking hypercube thingie

		classload("vcl/tabpanel");

		$this->read_template("entity_list.tpl");
		$this->vars(array(
			"tree" => $this->_get_tree(array(
				"rootmenu" => $rootmenu,
				"class_id" => CL_ENTITY
			)),
			"toolbar" => $tb->get_toolbar(),
			"table" => $this->_get_tabs($list_html),
		));

		$data["value"] = $this->parse();
		$data["type"] = "text";
		$data["no_caption"] = 1;

		return array($data);
	}
}
?>
