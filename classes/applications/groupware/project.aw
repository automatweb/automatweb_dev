<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/project.aw,v 1.7 2004/09/09 14:11:35 duke Exp $
// project.aw - Projekt 
/*

@classinfo syslog_type=ST_PROJECT relationmgr=yes

@default table=objects
@default group=general2

@property name type=textbox
@caption Nimi

@property status type=status
@caption Staatus

@property doc type=relpicker reltype=RELTYPE_PRJ_DOCUMENT field=meta method=serialize
@caption Loe lähemalt

@default group=web_settings
@property project_navigator type=checkbox ch_value=1 field=meta method=serialize
@caption Näita projektide navigaatorit

@property use_template type=select field=meta method=serialize
@caption Välimus

@default group=event_list

@property event_toolbar type=toolbar no_caption=1
@caption Sündmuste toolbar 

@property event_list type=calendar no_caption=1
@caption Sündmused

@default group=add_event
@property add_event callback=callback_get_add_event group=add_event store=no
@caption Lisa sündmus

@default group=files
@property file_editor type=releditor reltype=RELTYPE_PRJ_FILE mode=manager props=filename,file,comment
@caption Failid

@groupinfo general2 parent=general caption="Üldine"
@groupinfo web_settings parent=general caption="Veebiseadistused"
@groupinfo event_list caption="Sündmused" submit=no
@groupinfo add_event caption="Muuda sündmust"
@groupinfo files caption="Failid"

@reltype SUBPROJECT clid=CL_PROJECT value=1
@caption alamprojekt

@reltype PARTICIPANT clid=CL_USER,CL_CRM_COMPANY value=2
@caption osaleja

@reltype PRJ_EVENT value=3 clid=CL_TASK,CL_CRM_CALL,CL_CRM_OFFER,CL_CRM_DEAL,CL_CRM_MEETING
@caption Sündmus

@reltype PRJ_FILE value=4 clid=CL_FILE
@caption Fail

@reltype TAX_CHAIN value=5 clid=CL_TAX_CHAIN
@caption Maksu pärg

@reltype PRJ_CFGFORM value=6 clid=CL_CFGFORM
@caption Seadete vorm

@reltype PRJ_DOCUMENT value=7 clid=CL_DOCUMENT
@caption Dokument

*/

class project extends class_base
{
	function project()
	{
		$this->init(array(
			"clid" => CL_PROJECT,
			"tpldir" => "applications/groupware/project",
		));

		$this->event_entry_classes = array(CL_CALENDAR_EVENT,CL_STAGING);
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "event_list":
				$this->gen_event_list($arr);
				break;

			case "event_toolbar":
				$this->gen_event_toolbar($arr);
				break;

			case "use_template":
                                $data["options"] = array(
                                        "weekview" => "Nädala vaade",
                                );
                                break;
		}
		return $retval;

		// PUC; PUC; PUC; how do I get this beast working properly?
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
                $retval = PROP_OK;

                switch($data["name"])
                {
                        case "add_event":
                                $this->register_event_with_planner($arr);
                                break;

		}
		return $retval;

	}

	////
	// !Optionally this also needs to support date range ..
	function gen_event_list($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		$arr["prop"]["vcl_inst"]->configure(array(
                        "overview_func" => array(&$this,"get_overview"),
                ));

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"],
		));

		$start = $range["start"];
		$end = $range["end"];
		classload("icons");

		$o = $arr["obj_inst"];

		$this->overview = array();
		
		$this->used = array();
		enter_function("recurse_projects");
		$this->_recurse_projects(0,$o->id());
		exit_function("recurse_projects");

		// create a list of all subprojects, so that we can show events from all projects
		$parents = array($arr["obj_inst"]->id());
		if (is_array($this->prj_map))
		{
			foreach($this->prj_map as $key => $val)
			{
				foreach($val as $k1 => $v1)
				{
					$parents[$k1] = $k1;
				};
			};
		};

		// aga vaat siin on mingi jama ..
		$ol = new object_list(array(
			"parent" => $parents,
			"sort_by" => "planner.start",
			new object_list_filter(array("non_filter_classes" => CL_CRM_MEETING)),
		));

		for($o =& $ol->begin(); !$ol->end(); $o =& $ol->next())
		{
			$clinf = $this->cfg["classes"][$o->class_id()];
			$link = $this->mk_my_orb("change",array(
				"id" => $o->id(),
				"return_url" => urlencode(aw_global_get("REQUEST_URI")),
			),$o->class_id());
			/*
			$link = html::get_change_url(array(
				"oid" => $o->id(),
				"params" => array(
					"return_url" => aw_global_get("REQUEST_URI"),
				),
			));
			*/
			/*
			$link = $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"event_id" => $o->id(),
				"group" => "add_event",
			),$this->clid);
			*/
			$t->add_item(array(
				"timestamp" => $o->prop("start1"),
				"data" => array(
					"name" => $o->prop("name"),
					"icon" => icons::get_icon_url($o),
					"link" => $link,
				),
			));

			if ($o->prop("start1") > $range["overview_start"])
			{
				$this->overview[$o->prop("start1")] = 1;
			};
		};
	}

	function get_overview($arr = array())
        {
                return $this->overview;
        }


	////
	// !returns a list of events from the projects the user participates in
	// project_id (optional) - id of the project, if specified we get events
	// from that project only

	// XXX: split this into separate methods
	function get_events_from_projects($arr = array())
	{
		$ev_ids = array();
		if (!empty($arr["project_id"]))
		{
			global $awt;
			$awt->start("project-event-loader");
			#$ev_ids = $this->get_events_for_project(array("project_id" => $arr["project_id"]));
			$ev_ids = $arr["project_id"];
			$awt->stop("project-event-loader");
		}
		else
		if ($arr["type"] == "my_projects")
		{
			// this returns a list of events from "My projects"
			$users = get_instance("users");
			if (aw_global_get("uid"))
			{
				// see asi peab nüüd hakkama tagastame foldereid!
				$user_obj = new object($arr["user_ids"][0]);
				// this is wrong, I need to figure out the users, that this calendar belongs to
				//$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
				$conns = $user_obj->connections_to(array(
					"from.class_id" => CL_PROJECT,
				));
				// ei mingit bloody cyclet, see hakkab lihtsalt tagastame projektide id-sid, onjä!
				$ev_ids = array();
				foreach($conns as $conn)
				{
					$ev_ids[] = $conn->prop("from");
					//$ev_ids = array_merge($ev_ids,$this->get_events_for_project(array("project_id" => $conn->prop("from"))));
				};
			};
		};
		return $ev_ids;
	}

	////
	// !id - participant id
	function get_events_for_participant($arr = array())
	{
		$ev_ids = array();
		$projects = array();
		$obj = new object($arr["id"]);
		if ($obj->class_id() == CL_CRM_COMPANY)
		{
			$conns = $obj->connections_to(array(
				"reltype" => RELTYPE_PARTICIPANT,
			));
			foreach($conns as $conn)
			{
				$ev_ids = $ev_ids + $this->get_events_for_project(array(
					"project_id" => $conn->prop("from"),
					"class_id" => $arr["clid"],
				));
			};
		};

		return $ev_ids;
	}

	////
	// !Returns a list of event id-s for a given project
	function get_events_for_project($arr)
	{
		$pr_obj = new object($arr["project_id"]);
		$args = array(
			"type" => RELTYPE_PRJ_EVENT,
		);

		if (!empty($arr["class_id"]))
		{
			$args["to.class_id"] = $arr["class_id"];
		};

		$event_connections = $pr_obj->connections_from($args);

		$ev_id_list = array();
		foreach($event_connections as $conn)
		{
			$ev_id_list[$conn->prop("to")] = $conn->prop("to");
		};

		return $ev_id_list;
	}

	// nii, see asi tuleb nüüd ringi teha nii, et ta toetaks subprojecte ka

	function get_events($arr)
	{
		// okey, I need a generic function that should be able to return events from the range I am
		// interested in
		extract($arr);
		$parents = array($arr["id"]);
		
		$this->used = array();
		enter_function("recurse_projects");
		$this->_recurse_projects(0,$arr["id"]);
		exit_function("recurse_projects");
		
		if (is_array($this->prj_map))
		{
			foreach($this->prj_map as $key => $val)
			{
				foreach($val as $k1 => $v1)
				{
					$parents[$k1] = $k1;
				};
			};
		};

		$parent = join(",",$parents);


		$_start = $arr["range"]["start"];
		$_end = $arr["range"]["end"];
		$q = "SELECT objects.oid AS id,objects.parent,objects.class_id,objects.brother_of,objects.name,planner.start,planner.end
                        FROM planner
                        LEFT JOIN objects ON (planner.id = objects.brother_of)
                        WHERE planner.start >= '${_start}' AND
                        (planner.end <= '${_end}' OR planner.end IS NULL) AND
                        objects.status != 0 AND parent IN (${parent})";
		$this->db_query($q);
		$events = array();
		$pl = get_instance(CL_PLANNER);
		while($row = $this->db_next())
		{
			// now figure out which project this thing belongs to?
			$events[] = array(
				"start" => $row["start"],
				"name" => $row["name"],
				"id" => $row["id"],
				"project_weblink" => aw_ini_get("baseurl") . "/" . $row["parent"],
				"link" => $this->mk_my_orb("change",array(
					"id" => $row["id"],
				),$row["class_id"],true,true),
			);
			$ids[$row["id"]] = $row["id"];
		};
		return $events;
	}

	////
	// !connects an event to a project
	// id - id of the project
	// event_id - id of the event 
	function connect_event($arr)
	{
		$evt_obj = new object($arr["event_id"]);
		// create a brother under the project object
		$evt_obj->create_brother($arr["id"]);
	}

	////
	// !Disconnects and event from a project
	// id - id of the project
	// event_id - id of the event
	function disconnect_event($arr)
	{
		#$evt_obj = new object($arr["event_id"]);
		#$evt_obj->delete();
		// deleting is broken now until I can figure out something
		//$evt_obj 
		/*
		$prj_obj = new object($arr["id"]);
		$prj_obj->disconnect(array(
			"from" => $arr["event_id"],
		));
		*/
	}

	/**
		@attrib name=test_it_out all_args="1"

	**/
	function test_it_out($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
		));
		set_time_limit(0);
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			/*
			if ($o->id() != 87738)
			{
				continue;
			};
			*/
			$subs = new object_list(array(
				"parent" => $o->id(),
				"site_id" => array(),
			));
			for ($sub_o = $subs->begin(); !$subs->end(); $sub_o = $subs->next())
			{
				$orig = $sub_o->get_original();
				#print_r($sub_o);
				$sub2parent[$orig->id()] = $sub_o->id();
			};
			#arr($sub2parent);
			#arr($subs);
			$brother_parent = $o->id();
			// now I have to create brothers for each object
			print "projekt " . $o->name(). "<br>";
			print "id = " . $o->id() . "<br>";
			print "connections = ";
			$conns = $o->connections_from(array(
				"type" => "RELTYPE_PRJ_EVENT",
			));
			// create_brother
			print sizeof($conns);
			print "<br><br>";
			foreach($conns as $conn)
			{
				$to_obj = $conn->to();
				$tmp = $to_obj->get_original();
				$to_oid = $tmp->id();
				print "# ";
				print $conn->prop("reltype") . " ";
				print $to_obj->name() . " ";
				$p_obj = new object($to_obj->prop("parent"));
				print $p_obj->name() . "<bR>";
				// but first check, whether I already have an object with that parent!

				print_r($to_obj);
				if ($sub2parent[$to_oid])
				{
					print "brother already exists under $brother_parent<br>";
				}
				else
				{
					print "creating a brother under $brother_parent<br>";
				};
				print "<hr>";
				$to_obj->create_brother($brother_parent);
			};
			// I have to clone those, you know
			print "<br><br>";
		};
		print "oh, man, this is SO cool!";


	}

	function gen_event_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		/*
		$tb->add_menu_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => "Uus",
		));
		*/

		$o = $arr["obj_inst"];
		$inst = $o->instance();

		$int = $GLOBALS["relinfo"][$this->clid]["RELTYPE_PRJ_EVENT"];

		$clinf = aw_ini_get("classes");

		foreach($clinf as $key => $val)
		{
			if (in_array($key,$int["clid"]))
			{
				/*
				$tb->add_menu_item(array(
					"parent" => "new",
					"text" => $val["name"],
					"link" => "link",
				));
				*/


			};
		};

		//$tb->add_separator();

		$tb->add_menu_button(array(
			"name" => "subprj",
			"img" => "new.gif",
			"tooltip" => "Alamprojekt",
		));

		// see nupp peaks kuvama ka alamprojektid

		$this->used = array();
		enter_function("recurse_projects");
		$this->_recurse_projects(0,$o->id());
		exit_function("recurse_projects");

		$form_connections = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_PRJ_CFGFORM",
		));

		$forms = array();
		foreach($form_connections as $form_connection)
		{
			$forms[$form_connection->prop("to")] = $form_connection->prop("to.name");
		};

		// now, I need information about what confi
		if (is_array($this->prj_map))
		{
			foreach($this->prj_map as $parent => $items)
			{
				foreach($items as $prj_id)
				{
					$use_parent = $parent == 0 ? "subprj" : $parent;
					$pro = new object($prj_id);
					$tb->add_sub_menu(array(
						"name" => $prj_id,
						"parent" => $use_parent,
						"text" => $pro->name(),
					));

					// right then, I need a way to create links with correct parent
					// now - how do I do that?

					if (!$this->prj_map[$prj_id])
					{
						foreach($forms as $form_id => $form_name)
						{
							$tb->add_menu_item(array(
								"name" => "x_" . $prj_id . "_" . $form_id,
								"parent" => $prj_id,
								"text" => $form_name,
								"link" => $this->mk_my_orb("new",array(
									"parent" => $prj_id,
									"group" => "change",
								),CL_STAGING),
								/*
								"link" => $this->mk_my_orb("change",array(
									"id" => $o->id(),
									"group" => "add_event",
									"clid" => CL_CALENDAR_EVENT,
									"cfgform_id" => $form_id,
								)),
								*/
							));
						};
					};
				};
			};
		}
		else
		{
			$tb->add_menu_item(array(
				"name" => "x_" . $o->id(),
				"parent" => "subprj",
				"text" => "Etendus",
				"link" => $this->mk_my_orb("change",array(
					"id" => $o->id(),
					"group" => "add_event",
					"clid" => CL_STAGING,
				)),
			));
		};

		// and now .. to the lowest level ... I need to add configuration forms .. or that other stuff

		//arr($this->prj_map);

		// obviuously peab lingis olema mingi lisaargument. Mille puudumisel omadust ei näidata ..
		// ja mille eksisteerimisel kuvatakse korrektne vorm.

		// ja siin on nüüd see asi, et property pannakse eraldi tabi peale .. mis teeb asju veel
		// palju-palju raskemaks.

		// embedded form looks somewhat like a releditor .. but it can actually have multiple groups..
		// but now, when I think of that, a releditor might also want to use multiple groups

		// so how do I display those forms inside my form?
		//@reltype PRJ_EVENT value=3 clid=CL_TASK,CL_CRM_CALL,CL_CRM_OFFER,CL_CRM_DEAL,CL_CRM_MEETING

		/*
			1. how do I access that information
			2. 



		*/


	}

	////
	// !Gets a list of project id-s as an argument and creates a list of those in some $this variable
	// it should create a list of connections starting from those projects
	function _recurse_projects($parent,$prj_id)
	{
		$prj_obj = new object($prj_id);
		$prj_conns = $prj_obj->connections_from(array(
			"type" => "RELTYPE_SUBPROJECT",
		));

		// now, I have to keep track of the used projects
		foreach($prj_conns as $prj_conn)
		{
			$subprj_id = $prj_conn->prop("to");
			if (empty($this->used[$subprj_id]))
			{
				// krt, kuidagi peab ju 
				$this->used[$subprj_id] = $subprj_id;
				$this->prj_map[$parent][$subprj_id] = $subprj_id;
				$this->_recurse_projects($subprj_id,$subprj_id);
			};
		}
		


	}
	
	function callback_get_add_event($args = array())
	{
		// yuck, what a mess
		$obj = $args["obj_inst"];
		$meta = $obj->meta();
		
		$event_folder = $obj->id();

		// use the config form specified in the request url OR the default one from the
		// planner configuration
		$event_cfgform = $args["request"]["cfgform_id"];
		// are we editing an existing event?
		if (!empty($args["request"]["event_id"]))
		{
			$event_id = $args["request"]["event_id"];
			$event_obj = new object($event_id);
			if ($event_obj->is_brother())
			{
				$event_obj = $event_obj->get_original();
			};
			$event_cfgform = $event_obj->meta("cfgform_id");
			$this->event_id = $event_id;
			$clid = $event_obj->class_id();
			if ($clid == CL_DOCUMENT || $clid == CL_BROTHER_DOCUMENT)
			{
				unset($clid);
			};
		}
		else
		{
			if (!empty($args["request"]["clid"]))
                        {
                                $clid = $args["request"]["clid"];
                        }
                        elseif (is_oid($event_cfgform))
                        {
                                $cfgf_obj = new object($event_cfgform);
                                $clid = $cfgf_obj->prop("subclass");
                        };
		};

		$res_props = array();
			
		// nii - aga kuidas ma lahenda probleemi sündmuste panemisest teise kalendrisse?
		// see peaks samamoodi planneri funktsionaalsus olema. wuhuhuuu

		// no there are 3 possible scenarios.
		// 1 - if a clid is in the url, check whether it's one of those that can be used for enterint events
		//  	then load the properties for that
		// 2 - if cfgform_id is the url, let's presume it belongs to a document and load properties for that
		// 3 - load the default entry form ...
		// 4 - if that does not exist either, then return an error message

		if (isset($clid))
		{
			if (!in_array($clid,$this->event_entry_classes))
			{
				return array(array(
					"type" => "text",
					"value" => "Seda klassi ei saa kasutada sündmuste sisestamiseks",
				));
			}
			else
			{
				// 1 - get an instance of that class, for this I need to 
				aw_session_set('org_action',aw_global_get('REQUEST_URI'));
				$tmp = aw_ini_get("classes");
				$clfile = $tmp[$clid]["file"];
				$t = get_instance($clfile);
				$t->init_class_base();
				$emb_group = "general";
				if ($this->event_id && $args["request"]["cb_group"])
				{
					$emb_group = $args["request"]["cb_group"];
				};
				$this->emb_group = $emb_group;
			
				$t->id = $this->event_id;

				$all_props = $t->get_property_group(array(
                                        "group" => $emb_group,
                                        "cfgform_id" => $event_cfgform,
                                ));

				$xprops = $t->parse_properties(array(
						"obj_inst" => $event_obj,
						"properties" => $all_props,
						"name_prefix" => "emb",
				));

				//$resprops = array();
				$resprops["capt"] = $this->do_group_headers(array(
					"t" => &$t,
				));

				foreach($xprops as $key => $val)
				{
					$val["emb"] = 1;
					$resprops[$key] = $val;
				};

				$resprops[] = array("emb" => 1,"type" => "hidden","name" => "emb[class]","value" => basename($clfile));
				$resprops[] = array("emb" => 1,"type" => "hidden","name" => "emb[action]","value" => "submit");
				$resprops[] = array("emb" => 1,"type" => "hidden","name" => "emb[group]","value" => $emb_group);
				$resprops[] = array("emb" => 1,"type" => "hidden","name" => "emb[clid]","value" => $clid);
				$resprops[] = array("emb" => 1,"type" => "hidden","name" => "emb[cfgform]","value" => $event_cfgform);
				if ($this->event_id)
				{
					$resprops[] = array("emb" => 1,"type" => "hidden","name" => "emb[id]","value" => $this->event_id);	
				};
			};
		}
		return $resprops;
	}

	function do_group_headers($arr)
	{
                $xtmp = $arr["t"]->groupinfo;
                $tmp = array(
                        "type" => "text",
                        "caption" => "header",
                        "subtitle" => 1,
                );
                $captions = array();
                // still, would be nice to make 'em _real_ second level groups
                // right now I'm simply faking 'em
                // now, just add another
                foreach($xtmp as $key => $val)
                {
                        if ($this->event_id && ($key != $this->emb_group))
                        {
                                $new_group = ($key == "general") ? "" : $key;
                                $captions[] = html::href(array(
                                                "url" => aw_url_change_var("cb_group",$new_group),
                                                "caption" => $val["caption"],
                                ));
                        }
                        else
                        {
                                $captions[] = $val["caption"];
                        };
                };
                $this->emb_group = $emb_group;
                $tmp["value"] = join(" | ",$captions);
                return $tmp;
        }


	
	function register_event_with_planner($args = array())
	{
		$event_folder = $args["obj_inst"]->id();
		$emb = $args["request"]["emb"];
		$is_doc = false;
		if (!empty($emb["clid"]))
		{
			$tmp = aw_ini_get("classes");
			$clfile = $tmp[$emb["clid"]]["file"];
			$t = get_instance($clfile);
			$t->init_class_base();
		}

		if (is_array($emb))
		{
			if (empty($emb["id"]))
			{
				$emb["parent"] = $event_folder; 
			};
		};
		if (isset($emb["group"]))
		{
			$this->emb_group = $emb["group"];
		};

		if (!empty($emb["id"]))
		{
			$event_obj = new object($emb["id"]);
			$emb["id"] = $event_obj->brother_of();
		};

		$emb["return"] = "id";

		$this->event_id = $t->submit($emb);
		if (!empty($emb["id"]))
		{
			$this->event_id = $event_obj->id();
		};

		//I really don't like this hack //axel
		$gl = aw_global_get('org_action');

		// so this has something to do with .. connectiong some obscure object to another .. eh?

		// this deals with creating of one additional connection .. hm. I wonder whether
		// there is a better way to do that.

		// tolle uue objekti juurest luuakse seos äsja loodud eventi juurde jah?

		// aga kui ma lisaks lihtsalt sündmuse isiku juurde?
		// ja see tekiks automaatselt parajasti sisse logitud kasutaja kalendrisse,
		// kui tal selline olemas on? See oleks ju palju parem lahendus.
		// aga kuhu kurat ma sellisel juhul selle sündmuse salvestan?
		// äkki ma saan seda nii teha, et isiku juures üldse sündmust ei salvestata,
		// vaid broadcastitakse vastav message .. ja siis kalender tekitab selle sündmuse?

		preg_match('/alias_to_org=(\w*|\d*)&/', $gl, $o);
		preg_match('/reltype_org=(\w*|\d*)&/', $gl, $r);
		preg_match('/alias_to_org_arr=(.*)$/', $gl, $s);

		if (is_numeric($o[1]) && is_numeric($r[1]))
		{
			$org_obj = new object($o[1]);
			$org_obj->connect(array(
				"to" => $this->event_id,
				"reltype" => $r[1],
			));
			aw_session_del('org_action');
			if(strlen($s[1]))
			{
				$aliases = unserialize(urldecode($s[1]));
				foreach($aliases as $key=>$value)
				{
					$tmp_o = new object($value);
					$tmp_o->connect(array(
						'to' => $this->event_id,
						'reltype' => $r[1],
					));
				}
			}
			post_message_with_param(
				MSG_EVENT_ADD,
				$org_obj->class_id(),
				array(
					"source_id" => $org_obj->id(),
					"event_id" => $this->event_id,
				)
			);
		}
		return PROP_OK;
	}

	function callback_mod_tab($args)
	{
		if ($args["activegroup"] != "add_event" && $args["id"] == "add_event")
		{
			return false;
		};
	}

	function callback_mod_retval($arr)
	{
		$args = &$arr["args"];
		if ($this->event_id)
		{
			$args["event_id"] = $this->event_id;
			if ($this->emb_group && $this->emb_group != "general")
			{
				$args["cb_group"] = $this->emb_group;
			};
		};
	}

	function request_execute($o)
	{
		$rv = "";
		if ($o->prop("project_navigator") == 1)
		{
			//$rv .= "here be navigator<p>";
		};

		$prj_id = $o->id();

		// a project can be a subproject of another project which in turn
		// can be a subproject of a third project and so on.

		// the following code tries to figure out the very first project
		// in that chain - the one that is not a subproject of any other
		// projects
		$o2 = $o;
		$tmp = $o;
		$obj = $o;

		while ($o2 != false)
		{
			$sp = $o->connections_to(array(
				"type" => 1, // SUBPROJECT
				"from.class_id" => CL_PROJECT,
			));
			$first = reset($sp);
			if (is_object($first))
			{
				$o2 = $first->from();
			}
			else
			{
				$o2 = false;
			};
			$tmp = $o;
			$o = $o2;
		};

		$super_project = $tmp;

		$project_tree = array();

		$project_tree[1] = $this->_get_subprojects(array(
			"from" => array($super_project->id()),
		));

		$project_tree[2] = $this->_get_subprojects(array(
			"from" => $project_tree[1],
		));
	
		$project_tree[3] = $this->_get_subprojects(array(
			"from" => $project_tree[2],
		));

		// create a list of all links between web pages and projects
		// (connections from menu to project) 
		$all = $project_tree[1] + $project_tree[2] + $project_tree[3];
		
		$conn = new connection();
		$conns = $conn->find(array(
			"to" => $all,
			"from.class_id" => CL_MENU,
			"reltype" => RELTYPE_CONTENT_FROM,
		));

		$valid = array();

		foreach($conns as $connection)
		{
			$to_id = $connection["to"];
			$from_id = $connection["from"];
			$has_webpage[$to_id] = $from_id;
		};
		
		$names = array();
		if (sizeof($has_webpage) > 0)
		{
			$name_list = new object_list(array(
				"oid" => $has_webpage,
			));
			$names = $name_list->names();
		};

		$all = array();

		// 1. remove all projects that do not have an attached webpage
		// 2. set web page id as value for projects which have one - this allows for creating
		//  navigation menus for the site
		for ($i = 1; $i <= 3; $i++)
		{
			foreach($project_tree[$i] as $project_id)
			{
				unset($project_tree[$i][$project_id]);
				if ($has_webpage[$project_id])
				{
					$project_tree[$i][$has_webpage[$project_id]] = $names[$has_webpage[$project_id]];
					$all[$project_id] = $project_id;
				};
			};
		};

		$sel_id = $has_webpage[$prj_id];


		$this->read_template("show.tpl");
		$this->vars(array(
			"projects1" => $this->picker($sel_id,array("0" => "--vali--") + $project_tree[1]),
			"projects2" => $this->picker($sel_id,array("0" => "--vali--") + $project_tree[2]),
			"projects3" => $this->picker($sel_id,array("0" => "--vali--") + $project_tree[3]),
		));

		$cal_view = get_instance(CL_CALENDAR_VIEW);
		$caldata = $cal_view->parse_alias(array(
			"obj_inst" => $obj,
			"use_template" => "weekview",
		));

		$this->vars(array(
			"calendar" => $caldata,
		));

		return $this->parse();
	}

	/** Returns an array of subproject id-s, suitable for feeding to object_list

	**/

	function _get_subprojects($arr)
	{
		$conn = new connection();
		$conns = $conn->find(array(
			"from" => $arr["from"],
			"from.class_id" => CL_PROJECT,
			"reltype" => RELTYPE_SUBPROJECT,
		));

		$res = array();
		if (is_array($conns))
		{
			foreach($conns as $conn)
			{
				$to = $conn["to"];
				$from = $conn["from"];
				$res[$to] = $to;
			};
		};

		return $res;
	}

};
?>
