<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/project.aw,v 1.8 2004/09/24 09:28:31 duke Exp $
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
@caption Loe l�hemalt

@property skip_subproject_events type=checkbox ch_value=1 field=meta method=serialize
@caption �ra n�ita alamprojektide s�ndmusi

@default group=web_settings
@property project_navigator type=checkbox ch_value=1 field=meta method=serialize
@caption N�ita projektide navigaatorit

@property use_template type=select field=meta method=serialize
@caption V�limus

@default group=event_list

@property event_toolbar type=toolbar no_caption=1
@caption S�ndmuste toolbar 

@property event_list type=calendar no_caption=1
@caption S�ndmused

@default group=add_event
@property add_event callback=callback_get_add_event group=add_event store=no
@caption Lisa s�ndmus

@default group=files
@property file_editor type=releditor reltype=RELTYPE_PRJ_FILE mode=manager props=filename,file,comment
@caption Failid

@property trans type=translator store=no group=trans props=name
@caption T�lkimine

@groupinfo general2 parent=general caption="�ldine"
@groupinfo web_settings parent=general caption="Veebiseadistused"
@groupinfo event_list caption="S�ndmused" submit=no
@groupinfo add_event caption="Muuda s�ndmust"
@groupinfo files caption="Failid"
@groupinfo trans caption="T�lkimine"

@reltype SUBPROJECT clid=CL_PROJECT value=1
@caption alamprojekt

@reltype PARTICIPANT clid=CL_USER,CL_CRM_COMPANY value=2
@caption osaleja

@reltype PRJ_EVENT value=3 clid=CL_TASK,CL_CRM_CALL,CL_CRM_OFFER,CL_CRM_DEAL,CL_CRM_MEETING
@caption S�ndmus

@reltype PRJ_FILE value=4 clid=CL_FILE
@caption Fail

@reltype TAX_CHAIN value=5 clid=CL_TAX_CHAIN
@caption Maksu p�rg

@reltype PRJ_CFGFORM value=6 clid=CL_CFGFORM
@caption Seadete vorm

@reltype PRJ_DOCUMENT value=7 clid=CL_DOCUMENT
@caption Kirjeldus

*/

class project extends class_base
{
	function project()
	{
		$this->init(array(
			"clid" => CL_PROJECT,
			"tpldir" => "applications/groupware/project",
		));
		
		lc_site_load("project",&$this);

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
                                        "weekview" => "N�dala vaade",
                                );
                                break;
		}
		return $retval;
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
			"full_weeks" => 1,
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
				// see asi peab n��d hakkama tagastame foldereid!
				$user_obj = new object($arr["user_ids"][0]);
				// this is wrong, I need to figure out the users, that this calendar belongs to
				//$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
				$conns = $user_obj->connections_to(array(
					"from.class_id" => CL_PROJECT,
				));
				// ei mingit bloody cyclet, see hakkab lihtsalt tagastame projektide id-sid, onj�!
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

	// nii, see asi tuleb n��d ringi teha nii, et ta toetaks subprojecte ka

	function get_events($arr)
	{
		// okey, I need a generic function that should be able to return events from the range I am
		// interested in
		extract($arr);
	
		$o = new object($arr["id"]);
		$orig_conns = $o->connections_from(array(
			"type" => 103,
		));

		if (sizeof($orig_conns) > 0)
		{
			$first = reset($orig_conns);
			$arr["id"] = $first->prop("to");
		};
		
		$parents = array($arr["id"]);

	
		if (1 != $o->prop("skip_subproject_events"))
		{
			$this->used = array();
			obj_set_opt("no_auto_translation", 1);
			$this->_recurse_projects(0,$arr["id"]);
			obj_set_opt("no_auto_translation", 0);
		};

		if (is_array($this->prj_map))
		{
			// ah vitt .. see project map algab ju parajasti aktiivsest projektist.

			// aga valik "n�ita alamprojektide s�ndmusi" ei oma ju �le�ldse mitte mingit m�tet
			// kui mul on vennad k�igis �lemprojektides ka
			foreach($this->prj_map as $key => $val)
			{
				// nii . aga n��d ta n�itab mulle ju ka master projektide s�ndmusi .. which is NOT what I want

				// teisis�nu - mul ei ole s�ndmuste lugemisel vaja k�iki peaprojekte

				// k�ll aga on vaja neid n�itamisel - et ma oskaksin kuvada asukohti. so there
				foreach($val as $k1 => $v1)
				{
					$parents[$k1] = $k1;
				};
			};
		};

		$parent = join(",",$parents);

		$limit = "";
		if ($arr["range"]["limit_events"])
		{
			$limit = " LIMIT ".$arr["range"]["limit_events"];
		}

		// ma pean lugema s�ndmusi sellest projektist ja selle alamprojektidest.
		$_start = $arr["range"]["start"];
		$_end = $arr["range"]["end"];
		$lang_id = aw_global_get("lang_id");
		$stat_str = "objects.status != 0";
		if ($arr["status"])
		{
			$stat_str = "objects.status = " . $arr["status"];
		};
		$q = "SELECT objects.oid AS id,objects.parent,objects.class_id,objects.brother_of,objects.name,planner.start,planner.end
                        FROM planner
                        LEFT JOIN objects ON (planner.id = objects.brother_of)
                        WHERE planner.start >= '${_start}' AND
                        (planner.end <= '${_end}' OR planner.end IS NULL) AND
                        $stat_str AND parent IN (${parent}) AND lang_id = ${lang_id} $limit";
		//dbg::p1($q);
		$this->db_query($q);
		$events = array();
		$pl = get_instance(CL_PLANNER);
		$projects = array();
		// weblingi jaoks on vaja k�sida connectioneid selle projekti juurde!
		while($row = $this->db_next())
		{
			// now figure out which project this thing belongs to?
			$pr_obj = new object($row["parent"]);
			$projects[$row["parent"]] = $row["parent"];
			$web_page_id = $row["parent"];
			if (!$this->can("view",$row["brother_of"]))
			{
				continue;
			};
			$e_obj = new object($row["brother_of"]);
			//$e_obj = new object($row["id"]);
			// XXX: can this code be made optional?
			$conns = $pr_obj->connections_to(array(
				"type" => 17, // RELTYPE_CONTENT_FROM
			));
			$first = reset($conns);
			if (is_object($first))
			{
				$web_page_id = $first->prop("from");
			};
			// aga �ks event ei saa ju ometi topelt olla?

			// siia tuleb see zhanrite asi ka panna n��d?
			$events[$e_obj->brother_of()] = array(
				"start" => $row["start"],
				"name" => $e_obj->name(),
				"parent" => $e_obj->parent(),
				//"name" => $row["name"],
				//"id" => $row["id"],
				"id" => $e_obj->id(),
				"project_weblink" => aw_ini_get("baseurl") . "/" . $web_page_id,
				"link" => $this->mk_my_orb("change",array(
					"id" => $e_obj->id(),
				),$row["class_id"],true,true),
			);
			$ids[$row["brother_of"]] = $row["brother_of"];
		};

		// ma arvan et siia tuleks teha �ks lisaflag, sest j�rgnev arvutus on suhteliselt
		// expensive ja seda on vaja ainult estonias
		if (sizeof($events) > 0)
		{
			// vaja on leida ka igale eventile k�ik vennad
		
			$mpr = $this->get_master_project($o,$level);
			$this->prj_level = 1;

			//dbg::p1("level = " . $level);

			// seega .. kui on tegemist child projektiga, siis on mul tarvis see leida
	
			$this->prj_levels[$mpr->id()] = $this->prj_level;
			$this->prj_level++;
		
			$this->_recurse_projects(0,$mpr->id());
			$prj_levels = $this->prj_levels;


			$ol = new object_list(array(
				//"brother_of" => array_keys($events),
				"brother_of" => $ids,
			));

			// nii .. aga ma ei k�si alamprojektidest s�ndmusi .. v�i tegelikult k�sin .. aga
			// ma pean teadma mis projektides see v�rk asub
			
			$byp = array();

			// kuidas kurat ma panen selle kirja? :(

			// ma tean iga s�ndmuse kohta mis projektides ta asub ...
			// n��d on mul vaja teada mitmendal tasemel mingi projekt asub

			foreach($ol->arr() as $brot)
			{
				//dbg::p1("object is " . $brot->name());
				//dbg::p1("object id is  " . $brot->id());
				$prnt = new object($brot->parent());
				$prj_level = $prj_levels[$brot->parent()];
				// I have prnt, how do I figure out which level it is?

				// vasakult paremale 2 ja siis 1
				//dbg::p1("parent is " . $prnt->id() . " " . $prnt->name());
				//dbg::p1("prj level is " . $prj_levels[$brot->parent()]);
				//$bof = $brot->brother_of();
				$bof = $brot->brother_of();
				if ($events[$bof])
				{
					$events[$bof]["parent_" . $prj_level . "_name"] = $prnt->name();
					dbg::p1("assigning " . $prnt->name());
				};
			};
		};

		// niisiis - mul on reverse funktsiooni vaja .. v�i v�hemalt mingit trikki saamaks teada 
		// kust projektist �ks konkreetne s�ndmus tuli
		
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
		print "disconnecting " . $arr["event_id"];
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
			$o->set_prop("skip_subproject_events",1);
			$o->save();
		};
		print "all done";
		exit;
		while (1 == 0)
		{
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
		$this->prj_level = 0;
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
					
		$cl_inf = aw_ini_get("classes");
		$cl_name = $cl_inf[CL_STAGING]["name"];

		$create_args = array();

		if (false && is_array($this->prj_map))
		{
			// how do I know that I'm dealing with first level items?
			foreach($this->prj_map as $parent => $items)
			{
				$level = 0;
				foreach($items as $prj_id)
				{
					$level++;
					// if first level projects are configured with skip_subproject_events off
					// then a brother of the added event is created under that first level 
					// project
					$use_parent = $parent == 0 ? "subprj" : $parent;
					$pro = new object($prj_id);
					$tb->add_sub_menu(array(
						"name" => $prj_id,
						"parent" => $use_parent,
						"text" => $pro->name(),
					));

					if (1 == $pro->prop("skip_subproject_events"))
					{
						// do nothing
					};

					// but for this to work I also need to figure out the path
					// I'm in. How do I do that?

					// right then, I need a way to create links with correct parent
					// now - how do I do that?

					if (!$this->prj_map[$prj_id])
					{
						foreach($forms as $form_id => $form_name)
						{
							$tb->add_menu_item(array(
								"name" => "x_" . $prj_id . "_" . $form_id,
								"parent" => $prj_id,
								"text" => $cl_name,
								"link" => $this->mk_my_orb("new",array(
									"parent" => $prj_id,
									"group" => "change",
								),CL_STAGING),
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

		// obviuously peab lingis olema mingi lisaargument. Mille puudumisel omadust ei n�idata ..
		// ja mille eksisteerimisel kuvatakse korrektne vorm.

		// ja siin on n��d see asi, et property pannakse eraldi tabi peale .. mis teeb asju veel
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

	// seega .. alustades �hest projektist leiame k�ik selle projekti alamprojektid
	// ma pean siis iga projekti kohta leidma et millisel tasemel ta on.

	////
	// !Gets a list of project id-s as an argument and creates a list of those in some $this variable
	// it should create a list of connections starting from those projects
	function _recurse_projects($parent,$prj_id)
	{
		$prj_obj = new object($prj_id);
		$prj_conns = $prj_obj->connections_from(array(
			"type" => "RELTYPE_SUBPROJECT",
		));


		foreach($prj_conns as $prj_conn)
		{
			global $XX5;
			if ($XX5)
			{
				print "<h2>";
				print $prj_conn->prop("from.name");
				print " - ";
				print $prj_conn->prop("to.name");
				print "</h2>";
			};
			$subprj_id = $prj_conn->prop("to");
			$to = $prj_conn->to();
			$this->used[$subprj_id] = $subprj_id;
			$this->prj_map[$parent][$subprj_id] = $subprj_id;
			$this->r_prj_map[$subprj_id] = $prj_id;
			$this->prj_levels[$subprj_id] = $this->prj_level;
			$this->prj_level++;
			$this->_recurse_projects($subprj_id,$subprj_id);
			$this->prj_level--;
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
			
		// nii - aga kuidas ma lahenda probleemi s�ndmuste panemisest teise kalendrisse?
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
					"value" => "Seda klassi ei saa kasutada s�ndmuste sisestamiseks",
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

		// tolle uue objekti juurest luuakse seos �sja loodud eventi juurde jah?

		// aga kui ma lisaks lihtsalt s�ndmuse isiku juurde?
		// ja see tekiks automaatselt parajasti sisse logitud kasutaja kalendrisse,
		// kui tal selline olemas on? See oleks ju palju parem lahendus.
		// aga kuhu kurat ma sellisel juhul selle s�ndmuse salvestan?
		// �kki ma saan seda nii teha, et isiku juures �ldse s�ndmust ei salvestata,
		// vaid broadcastitakse vastav message .. ja siis kalender tekitab selle s�ndmuse?

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
			// XXX: make that option do something
			//$rv .= "here be navigator<p>";
		};

		$prj_id = $o->id();

		$prj_obj = $o;

		$orig_conns = $o->connections_from(array(
			"type" => 103,
		));

		if (sizeof($orig_conns) > 0)
		{
			$first = reset($orig_conns);
			$prj_id = $first->prop("to");
			$prj_obj = $first->to();
		};

		// a project can be a subproject of another project which in turn
		// can be a subproject of a third project and so on.

		// the following code tries to figure out the very first project
		// in that chain - the one that is not a subproject of any other
		// projects
		$o2 = $o;
		$tmp = $o;
		$obj = $o;

		$level = 0;
		$parent_selections = array();

		// riight .. now how do I filter that thing?

		while ($o2 != false)
		{
			$level++;
			$sp = $o->connections_to(array(
				"type" => 1, // SUBPROJECT
				"from.class_id" => CL_PROJECT,
			));
			$first = reset($sp);
			if (is_object($first))
			{
				$o2 = $first->from();
				array_unshift($parent_selections,$o2->id());
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

		global $XX5;
		if ($XX5)
		{
			arr($project_tree);
		};

		$all = $project_tree[1] + $project_tree[2] + $project_tree[3];
		
		$conn = new connection();
		$conns = $conn->find(array(
			"to" => $all,
			"from.lang_id" => aw_global_get("lang_id"),
			"from.class_id" => CL_MENU,
			"type" => RELTYPE_CONTENT_FROM,
		));

		$valid = array();

		$sel_prj = array();
		$sel_prj[1] = $_REQUEST["prj1"];
		$sel_prj[2] = $_REQUEST["prj2"];

		foreach($conns as $connection)
		{
			$to_id = $connection["to"];
			$from_id = $connection["from"];
			$has_webpage[$to_id] = $from_id;
		};

		// has_webpage - key is a project id, value is a menu id

		
		$names = array();
		if (sizeof($has_webpage) > 0)
		{
			$name_list = new object_list(array(
				"oid" => $has_webpage,
			));
			$names = $name_list->names();
		};

		$step = 0;
		if ($project_tree[1][$prj_id])
		{
			$step = 1;
		}
		else
		if ($project_tree[2][$prj_id])
		{
			$step = 2;
		}
		else
		if ($project_tree[3][$prj_id])
		{
			$step = 3;
		};

		$ng[1] = array();
		$ng[2] = array();
		$ng[3] = array();

		$p_used = 0;

		// leiame selle parenti
		$filtered = array();

		$events_from = false;

		if ($step == 1)
		{
			$sel[1] = $has_webpage[$prj_id];
			$sel[2] = $has_webpage[$sel_prj[1]];
			$sel[3] = $has_webpage[$sel_prj[2]];

			$events_from = $prj_id;
			if ($sel_prj[1])
			{
				$events_from = $sel_prj[1];
			};

			if ($sel_prj[2])
			{
				$events_from = $sel_prj[2];
			};

			$items[1] = $project_tree[1];
			$filtered = array(2,3);

			$items[2] = $this->_get_subprojects(array(
				"from" => $prj_id,
			));

			$items[3] = $this->_get_subprojects(array(
				"from" => isset($sel_prj[1]) ? $sel_prj[1] : $items[2],
			));

			$sect_id = aw_url_change_var(array(
				"prj1" => "",
				"prj2" => "",
			));

			//$ng[1][$sect_id] = $this->vars["lc_project_tree_level1"];
			$ng[2][$sect_id] = $this->vars["lc_project_tree_level2"];

			$sect_id = aw_url_change_var(array(
				"prj2" => "",
			));

			$ng[3][$sect_id] = $this->vars["lc_project_tree_level3"];


		};

		if ($step == 2)
		{
			$items[1] = $project_tree[1];
			$items[2] = $project_tree[2];
			$filtered = array(3);
			$items[3] = $this->_get_subprojects(array(
				"from" => $prj_id,
			));
			
			$sect_id = aw_url_change_var(array(
				"prj1" => "",
				"prj2" => "",
			));

			$ng[1][$sect_id] = $this->vars["lc_project_tree_level1"];

			// ja teisel polegi - sest sealt ei saa v�lja liikuda. vaat nii
			$sect_id = aw_url_change_var(array(
				"prj2" => "",
			));

			$ng[3][$sect_id] = $this->vars["lc_project_tree_level3"];
			
			$events_from = $prj_id;
			if ($sel_prj[2])
			{
				$events_from = $sel_prj[2];
			};
			
			$sel[2] = $has_webpage[$prj_id];
			$sel[3] = $has_webpage[$sel_prj[2]];
		};

		if ($step == 3 || $step == 0)
		{
			$items[1] = $project_tree[1];
			$items[2] = $project_tree[2];
			$items[3] = $project_tree[3];
			
			$sect_id = aw_url_change_var(array(
				"prj1" => "",
				"prj2" => "",
			));

			$ng[1][$sect_id] = $this->vars["lc_project_tree_level1"];
			$ng[2][$sect_id] = $this->vars["lc_project_tree_level2"];

			$sel[3] = $has_webpage[$prj_id];

			$events_from = $prj_id;
			if ($step == 0)
			{
				$ng[3][$sect_id] = $this->vars["lc_project_tree_level3"];
				$filtered = array(1,2,3);
				if ($sel_prj[1])
				{
					$events_from = $sel_prj[1];
				};
				if ($sel_prj[2])
				{
					$events_from = $sel_prj[2];
				};
				if ($sel_prj[3])
				{
					$events_from = $sel_prj[3];
				};
				$sel[1] = $has_webpage[$sel_prj[1]];
				$sel[2] = $has_webpage[$sel_prj[2]];
				$sel[3] = $has_webpage[$sel_prj[3]];
			};

		};

		// ja kuidagi oleks vaja seda koodi normaliseerida ka
		for ($i = 1; $i <= 3; $i++)
		{
			if ($i != $step)
			{
				$p_used++;
			};


			foreach($items[$i] as $project_item)
			{
				//dbg::p1("project item is " . $project_item);
				if ($has_webpage[$project_item])
				{
					$webpage = $has_webpage[$project_item];
					$idx = false;
					if (in_array($i,$filtered))
					{
						$idx = aw_url_change_var("prj" . $p_used . "" ,$project_item);
						/*
						if ($i == 2 && $step != 0)
						{
							$idx = aw_url_change_var("prj2","",$idx);
						};
						*/
						//$idx = aw_url_change_var("prj" . $i,"",$idx);
					}
					else
					{
						$idx = "/" . $webpage;
					};

					if ($idx)
					{
						if ($webpage == $sel[$i])
						{
							$sel[$i] = $idx;
						};
						$ng[$i][$idx] = $names[$webpage];
					};
				}
				else
				{
					$x1 = new object($project_item);
					//dbg::p1("skipping cause " . $x1->name() . " has no web page");
				};
			};
		};

		// mida munni ma selle m�ngukava lehega teen, ah?

		$this->read_template("show.tpl");
		$this->vars(array(
			"projects1" => $this->picker($sel[1],$ng[1]),
			"projects2" => $this->picker($sel[2],$ng[2]),
			"projects3" => $this->picker($sel[3],$ng[3]),
		));

		$cal_view = get_instance(CL_CALENDAR_VIEW);

		// XXX: make the view type configurable
		$views = array(
			3 => $this->vars["lc_day"],
			2 => $this->vars["lc_week"],
			1 => $this->vars["lc_month"],
			0 => $this->vars["lc_year"],
		);

		$view_from_url = aw_global_get("view");
		if (empty($view_from_url))
		{
			$view_from_url = 0;
		};

		if (!$views[$view_from_url])
		{
			$view_from_url = 0;
		};

		$use_template = "";
		if ($view_from_url == 0)
		{
			$use_template = "year";
			$viewtype = "year";
		};

		if ($view_from_url == 1)
		{
			$use_template = "month";
			$viewtype = "month";
		};

		if ($view_from_url == 2)
		{
			$use_template = "weekview";
			$viewtype = "week";
		};

		if ($view_from_url == 3)
		{
			$use_template = "day";
			$viewtype = "day";
		};

		$project_obj = $obj;
		if ($events_from)
		{
			$project_obj = new object($events_from);
		};

		// no need for that .. I just get the type from url
		$caldata = $cal_view->parse_alias(array(
			"obj_inst" => $project_obj,
			"use_template" => $use_template,
			"event_template" => "project_event.tpl",
			"viewtype" => $viewtype,
			"status" => STAT_ACTIVE,
			"skip_empty" => true,
			"full_weeks" => true,
		));

		classload("date_calc");
		$dt = aw_global_get("date");
		if (empty($dt))
		{
			$dt = date("d-m-Y");
		};
		$rg = get_date_range(array(
			"type" => $viewtype,
			"date" => $dt,
		));

		// it is possible to attach a document containing detailed description of
		// the project to the project. If the connection is present show the document
		// in the web
		$conns = $prj_obj->connections_from(array(
			"type" => "RELTYPE_PRJ_DOCUMENT",
			"to.lang_id" => aw_global_get("lang_id"),
		));
		$description = "";
		if (is_array($conns))
		{
			$first = reset($conns);
			if ($first)
			{
				$t = get_instance("document");
				$description = $t->gen_preview(array(
					"docid" => $first->prop("to"),
					"leadonly" => -1,
				));
			};
		};
		
		$view_navigator = "";


		foreach($views as $key => $val)
		{
			$this->vars(array(
				"text" => $val,
				"url" => aw_url_change_var("view",$key),
			));
			$tpl = ($view_from_url == $key) ? "ACTIVE_VIEW" : "VIEW";
			$view_navigator .= $this->parse($tpl);
		};

		$this->vars(array(
			"VIEW" => $view_navigator,
			"calendar" => $caldata,
			"prev" => aw_url_change_var("date",$rg["prev"]),
			"next" => aw_url_change_var("date",$rg["next"]),
			"description" => $description,
		));

		$rv =  $this->parse();
		return $rv;
	}

	/** Returns an array of subproject id-s, suitable for feeding to object_list

	**/

	function _get_subprojects($arr)
	{
		if (sizeof($arr["from"]) == 0)
		{
			return array();
		};
		$conn = new connection();
		$conns = $conn->find(array(
			"from" => $arr["from"],
			"from.class_id" => CL_PROJECT,
			//"from.lang_id" => aw_global_get("lang_id"),
			"type" => RELTYPE_SUBPROJECT,
		));

		$res = array();
		if (is_array($conns))
		{
			foreach($conns as $conn)
			{
				// this way I should get the translated object
				//$to = new object($conn["to"]);
				$to = $conn["to"];
				//dbg::p1("created object instance is " . $to->name());
				//dbg::p1("created object instance is " . $to->lang_id());
				$from = $conn["from"];
				//$res[$to->id()] = $to->id();
				$res[$to] = $to;
			};
		};

		return $res;
	}

	function get_master_project($o,&$level)
	{
		$o2 = $o;
		$level = 0;
		$parent_selections = array();

		// riight .. now how do I filter that thing?

		while ($o2 != false)
		{
			$level++;
			$sp = $o->connections_to(array(
				"type" => 1, // SUBPROJECT
				"from.class_id" => CL_PROJECT,
			));
			$first = reset($sp);
			if (is_object($first))
			{
				$o2 = $first->from();
				array_unshift($parent_selections,$o2->id());
			}
			else
			{
				$o2 = false;
			};
			$tmp = $o;
			$o = $o2;
		};

		return $tmp;
	}

};
?>
