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

	function get_property($args = array())
        {
		$data = &$args["prop"];
		$name = $data["name"];
		$retval = PROP_OK;
		if ($name == "alias" || $name == "jrk")
		{
			return PROP_IGNORE;
		};
		switch($name)
		{
			case "preview":
				$data["value"] = html::href(array(
					"url" => $this->mk_my_orb("view",array("id" => $args["obj"]["oid"])),
                                	"caption" => "Näita",
                                	"target" => "_blank",
				));
				break;

		};
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
                $retval = PROP_OK;
                switch($data["name"])
                {
			case "show_entities":
				if ($args["form_data"]["subgroup"] == "add_entity")
				{
					$this->create_entity($args);
				}
				else
				{
					// advance existing entities - if there is anything
					// to advance at all
					$this->process_entities($args);
				}
				break;
		};
		return $retval;
	}

	function callback_mod_retval($arr = array())
	{
		$args = &$arr["args"];
		if (isset($arr["form_data"]["treeroot"]))
		{
			$args["treeroot"] = $arr["form_data"]["treeroot"];
		};
	}

	function init_callback_view(&$data,$args = array())
	{
		// try and load the configuration object
		$retval = PROP_OK;
		$this->cfg_obj = $this->get_object(array(
			"oid" => $args["obj"]["meta"]["config"],
			"clid" => CL_WORKFLOW_CONFIG,
		));

		if (!$this->cfg_obj)
		{
			$data["error"] = "Konfiguratsiooniobjekt on valimata!";
			return PROP_ERROR;
		};
		
		$this->treeview_conf = $this->get_object(array(
			"oid" => $this->cfg_obj["meta"]["treeview_conf"],
			"clid" => CL_TREEVIEW,
		));
		
		if (!$this->treeview_conf)
		{
			$data["error"] = "Puu konfiguratsioon on valimata!";
			return PROP_ERROR;
		};

		return $retval;

	}

	function satisfy_any($args = array())
	{
		$this->save_handle();
		if (isset($args["action"]) && empty($this->actions[$args["action"]]))
		{
			$this->actions[$args["action"]] = $this->get_object(array(
				"oid" => $args["action"],
				"class_id" => CL_ACTION,
			));	
		};	
		
		if (isset($args["actor"]) && empty($this->actors[$args["actor"]]))
		{
			$this->actors[$args["actor"]] = $this->get_object(array(
				"oid" => $args["actor"],
				"class_id" => CL_ACTOR,
			));	
		};	
		
		if (isset($args["process"]) && empty($this->processes[$args["process"]]))
		{
			$this->processes[$args["process"]] = $this->get_object(array(
				"oid" => $args["process"],
				"class_id" => CL_PROCESS,
			));	
		};	
		$this->restore_handle();
	}


	function callback_show_actors($args = array())
	{
		$status = $this->init_callback_view(&$data,$args);
		if ($status == PROP_ERROR)
		{
			return $status;
		};

		$actor_rootmenu = $this->get_object(array(
			"oid" => $this->cfg_obj["meta"]["actor_rootmenu"],
			"clid" => CL_PSEUDO,
		));

		if (!$actor_rootmenu)
		{
			$data["error"] = "Tegijate rootmenüü on valimata!";
			return PROP_ERROR;
		};
	
		$this->clidlist = array(CL_PSEUDO,CL_ACTOR);	
		$thtml = $this->_build_tree($actor_rootmenu["oid"]);

		load_vcl("table");
		$this->t = new aw_table(array("xml_def" => "workflow/actor_list"));

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

		$action_rootmenu = $this->get_object(array(
			"oid" => $this->cfg_obj["meta"]["action_rootmenu"],
			"clid" => CL_PSEUDO,
		));

		if (!$action_rootmenu)
		{
			$data["error"] = "Tegevuste rootmenüü on valimata!";
			return PROP_ERROR;
		};

		$this->clidlist = array(CL_PSEUDO,CL_ACTION);
		$thtml = $this->_build_tree($action_rootmenu["oid"]);

		load_vcl("table");
		$this->t = new aw_table(array("xml_def" => "workflow/action_list"));
		
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

		$entity_rootmenu = $this->get_object(array(
			"oid" => $this->cfg_obj["meta"]["entity_rootmenu"],
			"clid" => CL_PSEUDO,
		));

		if (!$entity_rootmenu)
		{
			$data["error"] = "Olemite rootmenüü on valimata!";
			return PROP_ERROR;
		};


		$this->entity_rootmenu_id = $entity_rootmenu["oid"];

		if (!empty($args["request"]["treeroot"]))
		{
			$this->req_treeroot = $args["request"]["treeroot"];
		}
		else
		{
			$this->req_treeroot = $entity_rootmenu["oid"];
		};
			
		$this->treeroot_obj = $this->get_object(array(
			"oid" => $this->req_treeroot,
		));

		$treeroot_clid = $this->treeroot_obj["class_id"];
		$this->treeurl = $this->mk_my_orb("view",array(
			"id" => $args["obj"]["oid"],
			"group" => "show_entities",
		));
		$this->clidlist = array(CL_PSEUDO,CL_ENTITY);
		$this->parent_list = array();

		$thtml = $this->_build_tree($entity_rootmenu["oid"]);

		load_vcl("table");
		$this->t = new aw_table(array("xml_def" => "workflow/entity_list"));

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
			$_tmp = $this->get_object(array(
				"oid" => $this->req_treeroot,
			));

			$types[$this->req_treeroot] = $_tmp["name"];

			// here I have to query the entities by their respective type...
			// oh man .. how do I do that?
		}
		else
		{
			$_entities = $this->get_objects_below(array(
				"parent" => $this->parent_list,
				"class" => CL_ENTITY,
			));

			foreach($_entities as $key => $val)
			{
				$types[$val["oid"]] = $val["name"];
			}
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

				if ($treeroot_clid["class_id"] == CL_PSEUDO && (empty($types[$typ])))
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
			"imgover" => "new_over.gif",
			"class" => "menuButton",
		));
		
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.changeform.submit();",
			"img" => "save.gif",
			"imgover" => "save_over.gif",
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

			if ($this->under_treeroot && ($row["class_id"] == CL_PSEUDO))
			{
				$this->parent_list[] = $row["oid"];
			};

			$row["icon_url"] = ($row["class_id"] == CL_PSEUDO) ? "" : $this->ic->get_icon_url($row["class_id"],"");

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
		$entity_obj = $this->get_object(array(
			"oid" => $data["request"]["entity_id"],
			"class_id" => CL_ENTITY,
		));
		// now I need to figure out which configuration form 
		// is used by that entity
		
		// and then load that and display the stuff to the user
		$cfgform_id = $entity_obj["meta"]["entity_cfgform"];

		$cfgform_obj = $this->get_object(array(
			"oid" => $entity_obj["meta"]["entity_cfgform"],
			"class_id" => CL_CFGFORM,
			"subclass" => CL_DOCUMENT,
		));

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

		$process_rootmenu = $this->get_object(array(
			"oid" => $this->cfg_obj["meta"]["process_rootmenu"],
			"clid" => CL_PSEUDO,
		));

		if (!$process_rootmenu)
		{
			$data["error"] = "Protsesside rootmenüü on valimata!";
			return PROP_ERROR;
		};
		
		if (!empty($args["request"]["treeroot"]))
		{
			$this->req_treeroot = $args["request"]["treeroot"];
		}
		else
		{
			$this->req_treeroot = $process_rootmenu["oid"];
		};

		$this->treeroot_obj = $this->get_object(array(
			"oid" => $this->req_treeroot,
		));

		$treeroot_clid = $this->treeroot_obj["class_id"];
		$this->treeurl = $this->mk_my_orb("view",array(
			"id" => $args["obj"]["oid"],
			"group" => "show_processes",
		));

		$this->clidlist = array(CL_PSEUDO);
		$this->parent_list = array();

		$this->items = array();
		$this->items[] = array("name" => "Põhiprotsessid");
		$this->items[] = array("name" => "Tugiprotsessid");
		
			
		$thtml = $this->_build_tree($process_rootmenu["oid"]);

		load_vcl("table");
		$this->t = new aw_table(array("xml_def" => "workflow/process_list"));
		
		$processes = $this->get_objects_below(array(
			"parent" => $this->parent_list,
			"class" => CL_PROCESS,
		));
		
		$ed_url = $this->mk_my_orb("view",array("id" => $args["obj"]["oid"],"group" => "show_processes","subgroup" => "mod_process"));


		foreach($processes as $key => $val)
		{
			$ra = $val["meta"]["root_action"];
			$this->satisfy_any(array("action" => $ra));
			$val["root_action"] = $this->actions[$ra]["name"];
			$val["name"] = html::href(array(
				"url" => $ed_url . "&oid=$val[oid]",
				"caption" => $val["name"],
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
			"url" => $this->mk_my_orb("view",array("id" => $args["obj"]["oid"],"group" => "show_processes","subgroup" => "add_process")),
			"img" => "new.gif",
			"imgover" => "new_over.gif",
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
		$this->cfg_obj = $this->get_object(array(
			"oid" => $args["obj"]["meta"]["config"],
			"clid" => CL_WORKFLOW_CONFIG,
		));
		$return_url = $this->mk_my_orb("view",array("id" => $args["obj"]["oid"],"group" => "show_processes","b1" => 1));
		$process_rootmenu_id = $this->cfg_obj["meta"]["process_rootmenu"];
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
		$this->cfg_obj = $this->get_object(array(
			"oid" => $args["obj"]["meta"]["config"],
			"clid" => CL_WORKFLOW_CONFIG,
		));
		$return_url = $this->mk_my_orb("view",array("id" => $args["obj"]["oid"],"group" => "show_processes","b1" => 1));
		$process_rootmenu_id = $this->cfg_obj["meta"]["process_rootmenu"];
		$this->vars(array(
			"add_process_link" => $this->mk_my_orb("change",array("id" => $args["oid"],"return_url" => urlencode($return_url),"id" => $args["request"]["oid"]),"process"),
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
		if (!$args["form_data"]["entity_process"])
		{
			die("you did not pick a process<br>");
		};

		$proc_obj = $this->get_object(array(
			"oid" => $args["form_data"]["entity_process"],
			"class_id" => CL_PROCESS,
		));

		// this sorts the data by tables
		$def = get_instance("doc");
		$res = $def->process_form_data(array(
			"group" => "general",
			"group_by" => "table",
			"form_data" => $args["form_data"],
		));

		$objdata = $res["objects"];
		$cldata = $res["documents"];

		$objdata["metadata"] = $objdata["meta"];
		unset($objdata["meta"]);

		// now I have to figure out the parent
		$obj = $this->get_object($args["obj"]["oid"]);
		if (!$obj)
		{
			die("could not load workflow object<br>");
		};

		$cf_obj = $this->get_object($obj["meta"]["config"]);
		if (!$cf_obj)
		{
			die("could not load configuration object for workflow<br>");
		};

		$entity_root = $cf_obj["meta"]["entity_rootmenu"];
		// I should try and load the entity objet to see whether it
		// really is a menu
		if (!$entity_root)
		{
			die("entity root menu is not set<br>");
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

		$this->upd_object(array(
			"oid" => $_entity_id,
			"metadata" => array(
				"current_logtrail" => $current_logtrail,
			),
		));
		
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

		// and update the current thingy
		$this->upd_object(array(
			"oid" => $_entity_id,
			"metadata" => array(
				"current_logtrail" => $current_logtrail,
			),
		));
	}

	function gen_view_toolbar($args = array())
	{
		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "add",
			"tooltop" => "Uus",
			"url" => "#",
			"img" => "new.gif",
			"imgover" => "new_over.gif",
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
		$to_advance = new aw_array($args["form_data"]["advance"]);
		foreach($to_advance->get() as $key => $val)
		{
			// advance those entities to the next stadium
			if ($val > 0)
			{
				$ent = $this->get_object(array(
					"oid" => $key,
				));

				$ctrail = $ent["meta"]["current_logtrail"];

				$this->process_entity(array(
					"entity_id" => $key,
					"action_id" => $val,
					"process_id" => $ctrail["process"],
					"actor_id" => $ctrail["actor"],
				));

			};
		};
	}
	

	function callback_gen_path($args = array())
	{
		$obj = $this->get_object($args["id"]);
		$name = isset($obj["name"]) ? $obj["name"] : "";
		return "Vaata workflow objekti '$name'";
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
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
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
		return $this->show(array('id' => $alias['target']));
	}

}
?>
