<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/prisma/Attic/prisma_center.aw,v 1.7 2004/12/08 12:23:32 voldemar Exp $
// prisma_center.aw -  
/*

@classinfo syslog_type=ST_PRISMA_CENTER relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

@property config type=relpicker reltype=RELTYPE_WORKFLOW_CONFIG field=meta method=serialize
@caption Konfiguratsioon


@groupinfo center caption="Keskus"
@default group=center

@property show_entities type=callback callback=callback_show_entities group=center store=no
@caption Tr&uuml;&uuml;kised

@groupinfo jobs caption="T&auml;nased t&ouml;&ouml;d" 
@default group=jobs

@property jobs type=table store=no no_caption=1


@groupinfo ovw caption="&Uuml;levaade" submit=no
@default group=ovw

@groupinfo ovw_day caption="P&auml;ev" submit=no parent=ovw
@default group=ovw_day

@property owv_day type=text store=no no_caption=1


@groupinfo ovw_week caption="N&auml;dal" submit=no parent=ovw
@default group=ovw_week

@property owv_week type=text store=no no_caption=1

@groupinfo ovw_lines caption="Jooned" submit=no parent=ovw
@default group=ovw_lines

@property owv_lines type=text store=no no_caption=1


@groupinfo org caption="Organisatsioon"
@default group=org

@layout hbox_others type=hbox group=org width=20%:80%

@layout vbox_contacts_left type=vbox parent=hbox_others group=org

@property unit_listing_tree type=treeview no_caption=1 store=no parent=vbox_contacts_left 
@caption Puu

@layout vbox_contacts_right type=vbox parent=hbox_others group=org

@property human_resources type=table store=no no_caption=1 parent=vbox_contacts_right group=org
@caption Inimesed


@reltype WORKFLOW_CONFIG clid=CL_WORKFLOW_CONFIG value=1
@caption Konfiguratsioon 

@reltype COMPANY clid=CL_CRM_COMPANY value=2
@caption Firma

*/

class prisma_center extends class_base
{
	function prisma_center()
	{
		$this->init(array(
			"tpldir" => "applications/prisma/prisma_center",
			"clid" => CL_PRISMA_CENTER
		));

		$this->c_p = array(
			"#a0e937",
			"#e9ca37",
			"#1cf31c",
			"#f2ad79",
			"#0ce5dd",
			"#469af1",
			"#d3ccf3",
		);
		reset($this->c_p);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "owv_day":
				$prop["value"] = $this->get_overview($arr["obj_inst"]);
				break;

			case "owv_week":
				$prop["value"] = $this->get_overview_week($arr["obj_inst"]);
				break;

			case "owv_lines":
				$prop["value"] = $this->get_overview_lines($arr["obj_inst"]);
				break;

			case "jobs":
				$this->do_jobs_table($arr);

			case "unit_listing_tree":
				$co = get_instance(CL_CRM_COMPANY);
				$tarr = $arr;
				$c = reset($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_COMPANY")));
				if ($c)
				{
					$tarr["obj_inst"] = $c->to();
					$co->get_property($tarr);
				}
				break;

			case "human_resources":
				$co = get_instance(CL_CRM_COMPANY);
				$tarr = $arr;
				$c = reset($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_COMPANY")));
				if ($c)
				{
					$tarr["obj_inst"] = $c->to();
					$co->get_property($tarr);
				}
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
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

			case "jobs":
				$this->do_save_resources($arr);
		}
		return $retval;
	}	


	function callback_show_entities($args = array())
	{
		$request = $args["request"];
		if (isset($request["subgroup"]) && $request["subgroup"] == "add_entity")
		{
			$retval = $this->callback_add_entity($args);
			return $retval;
		};
		
		$data = array();
		$status = $this->init_callback_view(&$data,$args);
		if ($status == PROP_ERROR)
		{
			return $status;
		};

		$tb = get_instance("vcl/toolbar");

		$tb->add_menu_button(array(
			"name" => "add",
			"tooltip" => "Uus",
		));

		$entity_list = new object_list(array(
			"class_id" => CL_ENTITY
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
					"link" => $this->mk_my_orb("change",array(
						"id" => $args["obj_inst"]->id(),
						"group" => "center",
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

		return $this->_finalize_data(array(
			"rootmenu" => $this->entity_rootmenu,
			"tb" => $tb,
			"list_html" => $list_html,
			"data" => $data,
			"class_id" => CL_ENTITY
		));
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

		$entity_rootmenu_id = $this->cfg_obj->prop("entity_rootmenu");

		if (empty($entity_rootmenu_id))
		{
			$data["error"] = "Juhtumite rootmenüü on valimata!";
			return PROP_ERROR;
		};

		$this->entity_rootmenu = new object($entity_rootmenu_id);


		$entity_instance_rootmenu_id = $this->cfg_obj->prop("entity_instance_rootmenu");

		if (empty($entity_rootmenu_id))
		{
			$data["error"] = "Juhtumite sisestuste rootmenüü on valimata!";
			return PROP_ERROR;
		};

		$this->entity_instance_rootmenu = new object($entity_instance_rootmenu_id);


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

		$actor_rootmenu_id = $this->cfg_obj->prop("actor_rootmenu");

		if (empty($actor_rootmenu_id))
		{
			$data["error"] = "Tegijate rootmenüü on valimata!";
			return PROP_ERROR;
		}
		else
		{
			$this->actor_rootmenu = new object($actor_rootmenu_id);
		};

		return $retval;

	}

	// params: filter - the object_list filter for entity_instance list	
	function _do_entity_table($arr)
	{
		load_vcl("table");
		$t = new aw_table(array(
			"xml_def" => "erp/entity_list",
			"layout" => "generic",
		));

		if (is_array($arr["filter"]["entity_type"]) && count($arr["filter"]["entity_type"]) < 1)
		{
			$ol = new object_list();
		}
		else
		{
			$filter = array("class_id" => CL_WORKFLOW_ENTITY_INSTANCE) + $arr["filter"];
			$ol = new object_list($filter);
		}

		$wfe = get_instance("workflow/workflow_entity_instance");

		for ($e = $ol->begin(); !$ol->end(); $e = $ol->next())
		{
			$type_o = obj($e->prop("entity_type"));
			$actor_o = obj($type_o->prop("entity_actor"));
			$process_o = obj($type_o->prop("entity_process"));

			$mod = $e->modifiedby();
			$crea = $e->createdby();

			// get entity instance real object
			if (!$e->prop("obj_id"))
			{
				continue;
			}
			$r_o = obj($e->prop("obj_id"));
			$clss = aw_ini_get("classes");
			$fl = $clss[$r_o->class_id()]["file"];
			if ($fl == "document")
			{
				$fl = "doc";
			}
			$name = html::href(array(
				"url" => $this->mk_my_orb("change", array("id" => $r_o->id(), "return_url" => urlencode(aw_global_get("REQUEST_URI"))), $fl),
				"caption" => parse_obj_name($r_o->name())
			));

			$cur_state = $wfe->get_current_state($e->id());

			$nacts = array(0 => "") + $wfe->get_possible_next_states($e->id());
			if (count($nacts) < 2)
			{
				$na = "Protsess on l&otilde;ppenud!";
			}
			else
			{
				// "<span class=\"reallysmall\">".   ."</span>"
				$na = html::select(array(
					"name" => "next_action[".$e->id()."]",
					"options" => $nacts,
					"class" => "reallysmall"
				));
			}
			$t->define_data(array(
				"name" => $name,
				"type" => $type_o->name(),
				"actor" => $actor_o->name(),
				"modifiedby" => $mod->name(),
				"createdby" => $crea->name(),
				"modified" => $r_o->modified(),
				"created" => $r_o->created(),
				"process" => $process_o->name(),
				"action" => $cur_state->name(),
				"next_action" => $na
			));
		}

		$t->set_default_sortby("created");
		$t->set_default_sorder("desc");
		$t->sort_by();
		return $t->draw();
	}

	function _finalize_data($arr)
	{
		extract($arr);

		classload("vcl/tabpanel");

		$this->read_template("entity_list.tpl");
		$this->vars(array(
			"toolbar" => $tb->get_toolbar(),
			"table" => $list_html,
		));

		$data["value"] = $this->parse();
		$data["type"] = "text";
		$data["no_caption"] = 1;

		return array($data);
	}

	function process_entities($args = array())
	{
		$to_advance = new aw_array($args["request"]["next_action"]);
		foreach($to_advance->get() as $key => $val)
		{
			// advance those entities to the next stadium
			if ($val > 0)
			{
				$ent = new object($key);
				$ent_type = obj($ent->prop("entity_type"));

				$this->process_entity(array(
					"entity_id" => $key,
					"action_id" => $val,
					"process_id" => $ent_type->prop("entity_process"),
					"actor_id" => $ent_type->prop("entity_actor"),
				));

			};
		};
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

		$ent_obj = new object($_entity_id);
		$ent_obj->set_prop("state", $_entity_action);
		$ent_obj->save();
	}


	function create_entity($args = array())
	{
		die("create ent");
		if (!$args["request"]["entity_id"])
		{
			die("you did not pick a process<br />");
		};

		$data = array();
		$this->init_callback_view(&$data, $args);

		// load entity_type
		$entity_type = obj($args["request"]["entity_id"]);

		// create class object
		$cl_o = obj();
		$cl_o->set_class_id($entity_type->prop("entity_cfgform"));
		$cl_o->set_parent($this->entity_instance_rootmenu->id());
		$cl_o->save();

		$en_inst = obj();
		$en_inst->set_class_id(CL_WORKFLOW_ENTITY_INSTANCE);
		$en_inst->set_parent($this->entity_instance_rootmenu->id());
		$en_inst->set_prop("entity_type", $args["request"]["entity_id"]);
		$en_inst->set_prop("obj_id", $cl_o->id());
		$en_inst->save();

		$clss = aw_ini_get("classes");
		header("Location: ".$this->mk_my_orb("change", array("id" => $cl_o->id()), $clss[$entity_type->prop("entity_cfgform")]["class"]));
		die();
	}

	function callback_add_entity(&$data)
	{
		if (!$data["request"]["entity_id"])
		{
			die("you did not pick a process<br />");
		};

		$tdata = array();
		$this->init_callback_view(&$tdata, $data);

		// load entity_type
		$entity_type = obj($data["request"]["entity_id"]);

		// load cfgform so we can get the class_id
		$cfgform = obj($entity_type->prop("entity_cfgform"));

		// create class object
		$cl_o = obj();
		$cl_o->set_class_id($cfgform->prop("subclass"));
		$cl_o->set_parent($this->entity_instance_rootmenu->id());
		$cl_o->save();

		$en_inst = obj();
		$en_inst->set_class_id(CL_WORKFLOW_ENTITY_INSTANCE);
		$en_inst->set_parent($this->entity_instance_rootmenu->id());
		$en_inst->set_prop("entity_type", $data["request"]["entity_id"]);
		$en_inst->set_prop("obj_id", $cl_o->id());
		$en_inst->save();

		$cl_o->set_meta("entity_instance", $en_inst->id());
		$cl_o->save();

		$clss = aw_ini_get("classes");
		$fl = $clss[$cfgform->prop("subclass")]["file"];
		if ($fl == "document")
		{
			$fl = "doc";
		}
		$ru = $this->mk_my_orb("change", array("id" => $data["request"]["id"], "group" => "show_entities", "cb_view" => "show"));

		header("Location: ".$this->mk_my_orb("change", array("id" => $cl_o->id(),"cfgform" => $cfgform->id(), "return_url" => urlencode($ru)), $fl));
		die();
		return;
	}

	function get_overview($o)
	{
		$this->read_template("overview.tpl");

		// get all resources 
		$resource_list = new object_list(array(
			"class_id" => CL_WORKFLOW_RESOURCE,
			"sort_by" => "objects.jrk"
		));


		$j_b_r = array();
		$pr_by_event = array();
		for($o = $resource_list->begin(); !$resource_list->end(); $o = $resource_list->next())
		{
			$j_b_r[$o->id()] = array();
			$res_i = $o->instance();
			foreach($res_i->get_events_for_resource(array("id" => $o->id())) as $evid)
			{
				$tmp = obj($evid);
				$j_b_r[$o->id()][] = $tmp;
				$job_o = obj($tmp->meta("job_id"));
				$processing = $job_o->meta("processing");
				if ($processing[$o->id()] == 1)
				{
					$pr_by_event[$evid] = 1;
				}
			}
		}

		$colors = array();

		$res = "";
		$resource_list = new object_list(array(
			"class_id" => CL_WORKFLOW_RESOURCE,
			"sort_by" => "objects.jrk"
		));
		for($o = $resource_list->begin(); !$resource_list->end(); $o = $resource_list->next())
		{
			$this->vars(array(
				"res_name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id(), "group" => "calendar"), $o->class_id()),
					"caption" => $o->name(),
				)),
				"r_date" => date("d.m.Y", time())
			));

			$shown = array();
			$hour = "";
			// get events for resource 
			$times = array(); // one entry for each hour
			$res_i = $o->instance();
			$tsp = mktime(0,0,date("m"), date("d"), date("Y"));
			foreach($res_i->get_events_for_resource(array("id" => $o->id())) as $evid)
			{
				$evo = obj($evid);
				if (!isset($colors[$evo->name()]))
				{
					$colors[$evo->name()] = $this->get_rand_color();
				}
				$start = $evo->prop("start1");
				$end = $evo->prop("end");
				$n_hr = (($end - $start) / 3600);
				for($i = 0; $i < $n_hr; $i++)
				{
					$tmp = $start + ($i * 3600);
					// round to the beginning if the hour
					$tmp -= $tmp % 3600;
					$times[$tmp] = $evo;
				}
			}

			for($i = 0; $i < 96; $i++)
			{
				$tsp = time() - (time() % (24*3600)) + ($i*(3600/4));
				$tmp = $this->get_job_for_time($tsp, $j_b_r[$o->id()]);
				$col = "#FFFFFF";
				if (is_object($tmp))
				{
					$job = $tmp->name();
					$col = ($colors[$job] ? $colors[$job]  : "#FFFFFF");
					if ($pr_by_event[$tmp->id()])
					{
						$col = "#FF0000";
					}
					$job = html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $tmp->meta("job_id")), CL_PROJECT),
						"caption" => $job."<br>".date("H:i", $tmp->prop("start1"))." - ".date("H:i", $tmp->prop("end")),
					));
				}
				else
				{
					$job = " - ";
				}

				$this->vars(array(
					"time" => date("H:i", $tsp),
					"event" => ($shown[$job] ? "" : $job),
					"color" => $col,
				));
				$hour .= $this->parse("HOUR");
				$shown[$job] = true;
			}

			$this->vars(Array(
				"HOUR" => $hour
			));
			$res .= $this->parse("RESOURCE");
		}

		$this->vars(array(
			"RESOURCE" => $res
		));

		return $this->parse();
	}


	function get_overview_week($o)
	{
		$this->read_template("overview_week.tpl");

		// get all resources 
		$resource_list = new object_list(array(
			"class_id" => CL_WORKFLOW_RESOURCE
		));
		$j_b_r = array();
		for($o = $resource_list->begin(); !$resource_list->end(); $o = $resource_list->next())
		{
			$j_b_r[$o->id()] = array();
			$res_i = $o->instance();
			foreach($res_i->get_events_for_resource(array("id" => $o->id())) as $evid)
			{
				$j_b_r[$o->id()][] = obj($evid);
			}
		}

		
		classload("date_calc");
		$ws = get_week_start();
		for($i = 0; $i < 24; $i++)
		{
			$this->vars(array(
				"hour" => date("H", $ws + ($i*3600)),
			));

			$day_s = "";
			for($day = 0; $day < 7; $day++)
			{
				// do resources
				$res_s_h = "";
				$res_s = "";
				$resource_list = new object_list(array(
					"class_id" => CL_WORKFLOW_RESOURCE
				));
				for($o = $resource_list->begin(); !$resource_list->end(); $o = $resource_list->next())
				{
					$_jon = $this->get_job_for_time($ws + ($day * 3600 * 24) + $i * 3600, $j_b_r[$o->id()]);
					if (is_object($_jon))
					{
						$_jon = $_jon->name();
					}
					else
					{
						$_jon = " - ";
					}
					$this->vars(array(
						"res_name" => $o->name(),
						"job_name" => $_jon
					));

					$res_s_h .= $this->parse("RESOURCE_H");
					$res_s .= $this->parse("RESOURCE");
				}
				
				$this->vars(array(
					"RESOURCE_H" => $res_s_h,
					"RESOURCE" => $res_s
				));
				$day_s .= $this->parse("DAY");
			}

			$this->vars(array(
				"DAY" => $day_s
			));
			$hs .= $this->parse("HOUR");
		}

		$day_s_h = "";
		for($day = 0; $day < 7; $day++)
		{
			$this->vars(array(
				"day_name" => get_lc_weekday($day + 1)
			));
			$day_s_h .= $this->parse("DAY_H");
		}

		$this->vars(array(
			"DAY_H" => $day_s_h
		));

		$this->vars(array(
			"HOUR" => $hs
		));
		return $this->parse();
	}

	function get_job_for_time($ts, $joblist)
	{
		foreach($joblist as $job)
		{
			if ($ts >= $job->prop("start1") && $ts < $job->prop("end"))
			{
				return $job;
			}
		}
		return false;
	}

	function get_rand_color()
	{
		$ret = next($this->c_p);
		if (!$ret)
		{
			$ret = reset($this->c_p);
		}
		return $ret;
	}

	function get_overview_lines($o)
	{
		$this->read_template("overview_lines.tpl");

		// get all resources 
		$resource_list = new object_list(array(
			"class_id" => CL_WORKFLOW_RESOURCE,
			"sort_by" => "objects.jrk"
		));


		$j_b_r = array();
		$pr_by_event = array();
		for($o = $resource_list->begin(); !$resource_list->end(); $o = $resource_list->next())
		{
			$j_b_r[$o->id()] = array();
			$res_i = $o->instance();
			foreach($res_i->get_events_for_resource(array("id" => $o->id())) as $evid)
			{
				$tmp = obj($evid);
				$j_b_r[$o->id()][] = $tmp;
				$job_o = obj($tmp->meta("job_id"));
				$processing = $job_o->meta("processing");
				if ($processing[$o->id()] == 1)
				{
					$pr_by_event[$evid] = 1;
				}
			}
		}

		$colors = array();

		$res = "";
		$resource_list = new object_list(array(
			"class_id" => CL_WORKFLOW_RESOURCE,
			"sort_by" => "objects.jrk"
		));
		for($o = $resource_list->begin(); !$resource_list->end(); $o = $resource_list->next())
		{
			$this->vars(array(
				"res_name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id(), "group" => "calendar"), $o->class_id()),
					"caption" => $o->name(),
				)),
				"r_date" => date("d.m.Y", time())
			));

			$shown = array();
			$hour = "";
			// get events for resource 
			$times = array(); // one entry for each hour
			$res_i = $o->instance();
			$tsp = mktime(0,0,date("m"), date("d"), date("Y"));
			foreach($res_i->get_events_for_resource(array("id" => $o->id())) as $evid)
			{
				$evo = obj($evid);
				if (!isset($colors[$evo->name()]))
				{
					$colors[$evo->name()] = $this->get_rand_color();
				}
				$start = $evo->prop("start1");
				$end = $evo->prop("end");
				$n_hr = (($end - $start) / 3600);
				for($i = 0; $i < $n_hr; $i++)
				{
					$tmp = $start + ($i * 3600);
					// round to the beginning if the hour
					$tmp -= $tmp % 3600;
					$times[$tmp] = $evo;
				}
			}

			for($i = 0; $i < 96; $i++)
			{
				$tsp = time() - (time() % (24*3600)) + ($i*(3600/4));
				$tmp = $this->get_job_for_time($tsp, $j_b_r[$o->id()]);
				$col = "#FFFFFF";
				if (is_object($tmp))
				{
					$job = $tmp->name();
					$col = ($colors[$job] ? $colors[$job]  : "#FFFFFF");
					if ($pr_by_event[$tmp->id()])
					{
						$col = "#FF0000";
					}
					$job = html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $tmp->meta("job_id")), CL_PROJECT),
						"caption" => $job."<br>".date("H:i", $tmp->prop("start1"))." - ".date("H:i", $tmp->prop("end")),
					));
				}
				else
				{
					$job = " - ";
				}

				$this->vars(array(
					"time" => date("H:i", $tsp),
					"event" => ($shown[$job] ? "" : $job),
					"color" => $col,
				));
				$hour .= $this->parse("HOUR");
				$shown[$job] = true;
			}

			$this->vars(Array(
				"HOUR" => $hour
			));
			$res .= $this->parse("RESOURCE");
		}

		$hh = "";
		for($i = 0; $i < (96/4); $i++)
		{
			$tsp = time() - (time() % (24*3600)) + ($i*(3600/1));
			$this->vars(array(
				"tm" => date("H:i", $tsp)
			));
			$hh .= $this->parse("HOUR_HEAD");
		}

		$jbs = "";
		$css = new aw_array($colors);
		foreach($css->get() as $job_name => $job_c)
		{
			$this->vars(array(
				"job_name" => $job_name,
				"job_col" => $job_c
			));
			$jbs .= $this->parse("JOB");
		}

		$this->vars(array(
			"JOB" => $jbs,
			"HOUR_HEAD" => $hh,
			"RESOURCE" => $res
		));

		return $this->parse();
	}

	function _init_jobs_table(&$t)
	{
		$t->define_field(array(
			"name" => "job",
			"caption" => "T&ouml;&ouml;",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "order",
			"caption" => "Rea number",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "pred",
			"caption" => "Eeldustegevused",
			"align" => "left"
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Ressurss",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "time",
			"caption" => "Millal t&ouml;&ouml;sse l&auml;heb",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "length",
			"caption" => "Kaua kestab",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "buffer",
			"caption" => "Puhveraeg",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "ready",
			"caption" => "Millal valmis",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "processing",
			"caption" => "T&ouml;&ouml;s",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "done",
			"caption" => "Valmis",
			"align" => "center"
		));

/*		$t->define_field(array(
			"name" => "current",
			"caption" => "Praegune ressursi tegevus",
			"align" => "center",
		));*/
	}


	function do_jobs_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_jobs_table($t);
		$t->set_sortable(false);

		$ol = new object_list(array(
			"class_id" => CL_WORKFLOW_ENTITY_INSTANCE
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
		
			$this->do_jobs_table_part(array(
				"obj_inst" => obj($o->prop("obj_id")),
				"table" => &$t
			));
		}
	}

	function do_jobs_table_part($arr)
	{
		$t =& $arr["table"];

		$order = $arr["obj_inst"]->meta("order");
		$pred = $arr["obj_inst"]->meta("pred");
		$length = $arr["obj_inst"]->meta("length");
		$buffer = $arr["obj_inst"]->meta("buffer");
		$processing = $arr["obj_inst"]->meta("processing");
		//echo dbg::dump($processing);
		$done = $arr["obj_inst"]->meta("done");

		load_vcl("date_edit");
		$de = new date_edit();
		$de->configure(array(
			"day" => "",
			"month" => "",
			"year" => "",
			"hour" => "",
			"minute" => ""
		));

		if (!is_array($order))
		{
			$order = array();
		}
		$ord_change = false;
		$that = get_instance("applications/prisma/prisma_order");
		$srl = $that->_get_sel_resource_list($arr["obj_inst"]);
		foreach($srl as $resid)
		{
			if (!$length[$resid])
			{
				$length[$resid] = 1; // default to 1h
			}
			if (!$order[$resid])
			{
				if (count($order) < 1)
				{
					$order[$resid] = 1;
				}
				else
				{
					$order[$resid] = max(array_values($order)) + 1; 
				}
				$ord_change = true;
			}
		}


		$event_ids = $arr["obj_inst"]->meta("event_ids");
		$this->cur_priority = $arr["obj_inst"]->prop("priority");

		foreach($srl as $resid)
		{
			if ($event_ids[$resid])
			{
				$tmp = obj($event_ids[$resid]);
				$this->times_by_resource[$resid] = $tmp->prop("start1");
			}
		}

		foreach($srl as $resid)
		{
			$reso = obj($resid);

			$processing_str = "";
			//echo "for $resid event = ".$event_ids[$resid]." proc = ".$processing[$resid]." done = ".$done[$resid]." <br>";
			if ($event_ids[$resid] && !$processing[$resid] && !$done[$resid])
			{
				// also check preds 
				$ps_preds_ok = $that->do_check_ps_preds($resid, $pred, $processing, $order);
				
				if ($ps_preds_ok)
				{
					$processing_str = html::checkbox(array(
						"name" => "processing[".$arr["obj_inst"]->id()."][$resid]",
						"value" => 1,
						"checked" => ($processing[$resid] == 1)
					));
				}
			}

			if ($processing_str == "")
			{
				$processing_str = html::hidden(array(
					"name" => "processing[".$arr["obj_inst"]->id()."][$resid]",
					"value" => $processing[$resid],
				));
				if ($processing[$resid] == 1)
				{
					$processing_str .= "Jah";
				}
			}

			$done_str = "";
			if ($event_ids[$resid] && $processing[$resid] && !$done[$resid])
			{
				$done_str = html::checkbox(array(
					"name" => "done[".$arr["obj_inst"]->id()."][$resid]",
					"value" => 1,
					"checked" => ($done[$resid] == 1)
				));
			}

			if ($done_str == "")
			{
				$done_str = html::hidden(array(
					"name" => "done[".$arr["obj_inst"]->id()."][$resid]",
					"value" => $done[$resid],
				));
				if ($done[$resid] == 1)
				{
					$done_str .= "Jah";
				}
			}

			$time = date("d.m.Y H:i", $this->times_by_resource[$resid]);
			$ready = date("d.m.Y H:i", $this->times_by_resource[$resid] + (3600 * ((float)str_replace(",", ".", $length[$resid]))));

			$cur = "";
			// get current event from resource calendar
			$res_i = $reso->instance();
			if (($curevent = $res_i->get_current_event($reso, time())))
			{
				$cur = $curevent->name();
			}


			if (!$order[$resid])
			{
				$order[$resid] = ++$cur_cnt;
			}			
			$t->define_data(array(
				"job" => $arr["obj_inst"]->name(),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $resid, "group" => "calendar"), CL_WORKFLOW_RESOURCE),
					"caption" => $reso->name()
				)),
				"time" => $time,
				"ready" => $ready,
				"order" => $order[$resid],
				"pred" => $pred[$resid],
				"length" => $length[$resid]." tundi",
				"buffer" => ((int)$buffer[$resid])." tundi",
				"processing" => $processing_str,
				"done" => $done_str,
				"current" => $cur
			));
		}
	}
	
	function do_save_resources($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_WORKFLOW_ENTITY_INSTANCE
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$obj_inst = obj($o->prop("obj_id"));

			$evids = $obj_inst->meta("event_ids");

			// if processing is checked then mark the events as not-moveable
			$newproc = new aw_array($arr["request"]["processing"][$obj_inst->id()]);
			$curproc = $obj_inst->meta("processing");
			foreach($newproc->get() as $resid => $one)
			{
				if ($one != 1)
				{
					continue;
				}
				if (!$curproc[$resid])
				{
					// DING!
					$ev = obj($evids[$resid]);
					$ev->set_meta("no_move", 1);
					$ev->set_meta("task_priority", 2000000000);
					$ev->save();
					//echo "set event ".$ev->id()." as nomove! <br>";
				}
			}

			$obj_inst->set_meta("processing", $arr["request"]["processing"][$obj_inst->id()]);
			$obj_inst->set_meta("done", $arr["request"]["done"][$obj_inst->id()]);
			$obj_inst->save();
		}
	}
}
?>
