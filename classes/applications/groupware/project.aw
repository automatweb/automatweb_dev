<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/project.aw,v 1.20 2004/12/09 19:31:40 duke Exp $
// project.aw - Projekt 
/*

@classinfo syslog_type=ST_PROJECT relationmgr=yes

@default table=objects
@default group=general2

@property name type=textbox
@caption Nimi

@property status type=status
@caption Staatus

@property start type=datetime_select field=meta method=serialize
@caption Algus

@property end type=datetime_select field=meta method=serialize
@caption L&otilde;pp

@property deadline type=datetime_select field=meta method=serialize
@caption Deadline

@property priority type=textbox table=objects field=meta method=serialize size=5
@caption Prioriteet

@property doc type=relpicker reltype=RELTYPE_PRJ_DOCUMENT field=meta method=serialize
@caption Loe lähemalt

@property skip_subproject_events type=checkbox ch_value=1 field=meta method=serialize
@caption Ära näita alamprojektide sündmusi

@default group=web_settings
@property project_navigator type=checkbox ch_value=1 field=meta method=serialize
@caption Näita projektide navigaatorit

@property use_template type=select field=meta method=serialize
@caption Välimus

@default group=prj_image
@property prj_image type=releditor reltype=RELTYPE_PRJ_IMAGE use_form=emb rel_id=first field=meta method=serialize
@caption Pilt

@default group=event_list

@property event_toolbar type=toolbar no_caption=1
@caption Sündmuste toolbar 

@property event_list type=calendar no_caption=1
@caption Sündmused

@groupinfo selres caption="Vali ressursid"

@property sel_resources type=chooser orient=vertical multiple=1 store=connect reltype=RELTYPE_RESOURCE group=selres
@caption Vali ressursid


@groupinfo resources caption="Jaota ressursid"

@property resources type=table group=resources no_caption=1

@property confirm type=checkbox ch_value=1 group=resources field=meta method=serialize table=objects
@caption Kinnita ajad

@groupinfo work caption="T&ouml;&ouml;de seis"

@groupinfo work_table caption="Tabel" parent=work
@groupinfo work_cal caption="Kalender" parent=work

@property work_cal type=calendar group=work_cal no_caption=1 store=no
@property work_table  type=table group=work_table no_caption=1 store=no


@property event_list type=calendar group=event_list no_caption=1

@default group=add_event
@property add_event callback=callback_get_add_event group=add_event store=no
@caption Lisa sündmus

@default group=files
@property file_editor type=releditor reltype=RELTYPE_PRJ_FILE mode=manager props=filename,file,comment
@caption Failid

@property trans type=translator store=no group=trans props=name
@caption Tõlkimine

@groupinfo general2 parent=general caption="Üldine"
@groupinfo web_settings parent=general caption="Veebiseadistused"
@groupinfo prj_image parent=general caption="Pilt"

@groupinfo event_list caption="Sündmused" submit=no
@groupinfo add_event caption="Muuda sündmust"
@groupinfo files caption="Failid"
@groupinfo trans caption="Tõlkimine"

@groupinfo userdefined caption="Andmed"
@default group=userdefined

@property user1 type=textbox 
@caption User-defined textbox 1

@property user2 type=textbox 
@caption User-defined textbox 2

@property user3 type=textbox 
@caption User-defined textbox 3

@property user4 type=textbox 
@caption User-defined textbox 4

@property user5 type=textbox 
@caption User-defined textbox 5

@property userch1 type=checkbox ch_value=1 
@caption User-defined checkbox 1

@property userch2 type=checkbox ch_value=1 
@caption User-defined checkbox 2

@property userch3 type=checkbox ch_value=1 
@caption User-defined checkbox 3

@property userch4 type=checkbox ch_value=1 
@caption User-defined checkbox 4

@property userch5 type=checkbox ch_value=1 
@caption User-defined checkbox 5

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
@caption Kirjeldus

@reltype PRJ_IMAGE value=8 clid=CL_IMAGE
@caption Pilt

@reltype ORDERER value=9 clid=CL_CRM_COMPANY
@caption tellija

@reltype IMPLEMENTOR value=10 clid=CL_CRM_COMPANY
@caption teostaja

@reltype PRJ_VIDEO value=11 clid=CL_VIDEO
@caption Video

@reltype RESOURCE value=12 clid=CL_WORKFLOW_RESOURCE
@caption ressurrss
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

		$this->event_entry_classes = array(CL_CALENDAR_EVENT,CL_STAGING,CL_CRM_MEETING,CL_TASK,CL_CRM_CALL);
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

			case "sel_resources":
				$prop["options"] = $this->_get_resource_list($arr["obj_inst"]);
				$prop["value"] = $this->_get_sel_resource_list($arr["obj_inst"]);
				break;

			case "resources":
				$this->do_res_tbl($arr);
				break;

			case "work_table":
				$this->do_work_tbl($arr);
				break;

			case "work_cal":
				$this->gen_event_list($arr);
				break;

			case "event_toolbar":
				$this->gen_event_toolbar($arr);
				break;

			case "use_template":
				$data["options"] = array(
					"weekview" => t("Nädala vaade"),
				);
				break;
		}
		return $retval;
	}


	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "sel_resources":
				$this->save_sel_resources($arr);
				break;

			case "resources";
				$this->do_save_resources($arr);
				break;

			case "confirm":
				if ($prop["value"] == 1)
				{
					$this->do_write_times_to_cal($arr);
				}
				break;
			
			case "add_event":
				$this->register_event_with_planner($arr);
				break;

			case "priority":
				if ($prop["value"] != $arr["obj_inst"]->prop("priority") && is_oid($arr["obj_inst"]->id()) && $arr["obj_inst"]->prop("confirm"))
				{
					// write priority to all events from this
					$evids = new aw_array($arr["obj_inst"]->meta("event_ids"));
					foreach($evids->get() as $evid)
					{
						$evo = obj($evid);
						$evo->set_meta("task_priority", $prop["value"]);
						$evo->save();
					}

					// also, recalc times
					$this->do_write_times_to_cal($arr);
				}
				// also, 
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

		// event translations have the id of the object in original language
		$o = $arr["obj_inst"];
		obj_set_opt("no_auto_translation", 1);
		$fx = $o->get_first_obj_by_reltype(RELTYPE_ORIGINAL);
		if ($fx)
		{
			$o = $fx;
		};

		obj_set_opt("no_auto_translation", 0);

		$this->overview = array();
		
		$this->used = array();

		$parents = array($o->id());

		if (1 != $o->prop("skip_subproject_events"))
		{
			$this->_recurse_projects(0,$o->id());

			// create a list of all subprojects, so that we can show events from all projects
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
		};

		// aga vaat siin on mingi jama ..
		$ol = new object_list(array(
			"parent" => $parents,
			"sort_by" => "planner.start",
			"class_id" => $this->event_entry_classes,
			"CL_STAGING.start1" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start),
		));
		//new object_list_filter(array("non_filter_classes" => CL_CRM_MEETING)),


		$req = urlencode(aw_global_get("REQUEST_URI"));

		foreach($ol->arr() as $o)
		{
			$id = $o->id();
			//if ($id != $o->brother_of())
			//{
				// this will break things, but makes estonia work for now
				//continue;
			//};


			$start = $o->prop("start1");
			$clid = $o->class_id();
			
			$clss = aw_ini_get("classes");
			$clinf = $clss[$clid];

			$link = $this->mk_my_orb("change",array("id" => $id,"return_url" => $req),$clid);

			$t->add_item(array(
				"timestamp" => $start,
				"data" => array(
					"name" => $o->prop("name"),
					"icon" => icons::get_icon_url($o),
					"link" => $link,
				),
			));

			if ($start > $range["overview_start"])
			{
				$this->overview[$start] = 1;
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

	/**
		@attrib name=wtf
	**/
	function wtf($arr)
	{
		aw_disable_acl();
		$ol = new object_list(array(
			"brother_of" => 10412,
			"lang_id" => array(),
		));
		echo dbg::dump($ol);

		arr($ol);
		foreach($ol->arr() as $o)
		{
			print "id = " . $o->id();
			print "prnt = " . $o->parent();
			print "lang = " . $o->lang();
			print "<br>";
		};
		
		aw_disable_acl();
		$ol = new object_list(array(
			"brother_of" => 5602,
			"lang_id" => array(),
		));
		echo dbg::dump($ol);

		foreach($ol->arr() as $o)
		{
			print "id = " . $o->id();
			print "prnt = " . $o->parent();
			print "<br>";
		};

		die();




		// and another, english should be activated for this
		// this is original and it has start1
		$o = new object(10083);
		arr($o->properties());

		// this one is translation and it does not have start1
		$o = new object(10085);
		arr($o->properties());

		die();

	}

	function get_events($arr)
	{
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
			#obj_set_opt("no_auto_translation", 1);
			$this->_recurse_projects(0,$arr["id"]);
			#obj_set_opt("no_auto_translation", 0);
		};

		if (is_array($this->prj_map))
		{
			// ah vitt .. see project map algab ju parajasti aktiivsest projektist.

			// aga valik "näita alamprojektide sündmusi" ei oma ju üleüldse mitte mingit mõtet
			// kui mul on vennad kõigis ülemprojektides ka
			foreach($this->prj_map as $key => $val)
			{
				// nii . aga nüüd ta näitab mulle ju ka master projektide sündmusi .. which is NOT what I want

				// teisisõnu - mul ei ole sündmuste lugemisel vaja kõiki peaprojekte

				// küll aga on vaja neid näitamisel - et ma oskaksin kuvada asukohti. so there
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
			$limit_num = $arr["range"]["limit_events"];
		}

		// ma pean lugema sündmusi sellest projektist ja selle alamprojektidest.
		$_start = $arr["range"]["start"];
		$_end = $arr["range"]["end"];
		$lang_id = aw_global_get("lang_id");
		$stat_str = "objects.status != 0";

		if ($arr["status"] && aw_global_get("uid") == "")
		{
			$stat_str = "objects.status = " . $arr["status"];
		};

		$active_lang_only = aw_ini_get("project.act_lang_only");

		$q = "
			SELECT 
				objects.oid AS id,
				objects.parent,
				objects.class_id,
				objects.brother_of,
				objects.name,
				planner.start,
				planner.end
			FROM planner
			LEFT JOIN objects ON (planner.id = objects.brother_of)
			WHERE ((planner.start >= '${_start}' AND planner.start <= '${_end}')
			OR
			(planner.end >= '${_start}' AND planner.end <= '${_end}')) AND
			$stat_str AND objects.parent IN (${parent}) order by planner.start"; // $limit


		// SELECT objects.oid AS id, objects.parent, objects.class_id, objects.brother_of, objects.name, planner.start, planner.end FROM planner LEFT JOIN objects ON (planner.id = objects.brother_of) WHERE ((planner.start >= '1099260000' AND planner.start <= '1104530399') OR (planner.end >= '1099260000' AND planner.end <= '1104530399')) AND objects.status != 0 AND objects.parent IN (2186)

		

		enter_function("project::query");
		dbg::p1($q);
		$this->db_query($q);
		$events = array();
		$pl = get_instance(CL_PLANNER);
		$projects = $by_parent = array();
		obj_set_opt("no_auto_translation", 0);
		$lang_id = aw_global_get("lang_id");
		// weblingi jaoks on vaja küsida connectioneid selle projekti juurde!
		while($row = $this->db_next())
		{
			// now figure out which project this thing belongs to?
			//$web_page_id = $row["parent"];

			if (!$this->can("view",$row["brother_of"]))
			{
				//dbg::p1($row["name"]);
				//dbg::p1("skip1");
				continue;
			};

			$e_obj = new object($row["brother_of"]);
			// see leiab siis objekti originaali parenti
			$pr_obj = new object($e_obj->parent());


			if ($active_lang_only == 1 && $pr_obj->lang_id() != $lang_id)
			{
				//dbg::p1($row["name"]);
				//dbg::p1("skip2");
				continue;
			};
			
			$projects[$row["parent"]] = $row["parent"];
			
			$project_name = $pr_obj->name();

			// koostan nimekirja asjadest, mida mul vaja on? ja edasi on vaja
			// nimekirja piltidest

			//enter_function("find-original");


			// nii, see on see koht, mis tuleks tsüklist välja tõsta.
			//obj_set_opt("no_auto_translation",1);
			//$fx = $pr_obj->get_first_obj_by_reltype(RELTYPE_ORIGINAL);
			//exit_function("find-original");
			//if ($fx)
			//{
			//	$pr_obj = $fx;
			//};
			
			$prid = $pr_obj->id();

			// mida fakki .. miks see asi NII on?
			$projects[$prid] = $prid;

			// äkki ma saan siis siin ka kasutada seda tsüklite ühendamist?
			obj_set_opt("no_auto_translation",0);
			/*
			$conns = $pr_obj->connections_to(array(
				"type" => 17, // RELTYPE_CONTENT_FROM
				"from.lang_id" => aw_global_get("lang_id"),
			));

			// see on see bloody originaal ju :(
			$first = reset($conns);
			if (is_object($first))
			{
				$from = $first->from();
				//$web_page_id = $first->prop("from");
				$web_page_id = $from->id();
			};
			*/
			
			

			// but some objects have no idea about an image
			// what the hell am I going to do with those?
			/*$pr_image = $pr_obj->get_first_obj_by_reltype(RELTYPE_PRJ_IMAGE);


			if ($pr_image)
			{
				$inst = $pr_image->instance();
				$row["project_image"] = $inst->get_url_by_id($pr_image->id());
			};
			*/

			$eid = $e_obj->id();

			$event_parent = $e_obj->parent();
			$event_brother = $e_obj->brother_of();

			enter_function("assign-event");
			$events[$event_brother] = array(
				"start" => $row["start"],
				"pr" => $prid,
				"name" => $e_obj->name(),
				"parent" => $event_parent,
				"lang_id" => $e_obj->lang_id(),
				"id" => $eid,
				//"project_image" => $row["project_image"],
				"original_id" => $row["brother_of"],
				//"project_weblink" => aw_ini_get("baseurl") . "/" . $web_page_id,
				//"project_day_url" => aw_ini_get("baseurl") . "/" . $web_page_id . "?view=3&date=" . date("d-m-Y",$row["start"]),
				"project_name" => $project_name,
				"project_name_ucase" => strtoupper($project_name),
				"link" => $this->mk_my_orb("change",array(
					"id" => $eid,
				),$row["class_id"],true,true),
			);
			exit_function("assign-event");
			$ids[$row["brother_of"]] = $row["brother_of"];

			$by_parent[$event_parent][] = $event_brother;

			if (++$limit_counter >= $limit_num && $limit_num)
			{
				break;
			}
		};

		// kas ma saan pr-i hiljem arvutada?
		exit_function("project::query");

		// kuidas ma saan sellest jamast lahti?

		// now i have a list of all projects .. I need to figure out which menus connect to those projects
		$web_pages = $project_images = array();
		$c = new connection();
		obj_set_opt("no_auto_translation",1);

		$conns = $c->find(array(
			"from" => $projects,
			"type" => RELTYPE_ORIGINAL,
		));

		foreach($conns as $conn)
		{
			$from = $conn["from"];
			$to = $conn["to"];
			$xto = new object($to);
			//$xtod = $xto->id();
			//if ($projects[$from])
			//{
				//unset($projects[$from]);
				$projects[$from] = $to;
				//$projects[$to] = $from;
			//};
		};
		obj_set_opt("no_auto_translation",0);


		// nii .. ühesõnaga me diilime kogu aeg originaalprojektidega siin. eks?
		$conns = $c->find(array(
			"to" => $projects,
			"from.lang_id" => aw_global_get("lang_id"),
			"type" => 17,
		));
		foreach($conns as $conn)
		{
			$web_pages[$conn["to"]] = $conn["from"];
		};

		global $XX5;
		if ($XX5)
		{
			arr($events);
		};

		if (1 == $arr["project_media"])
		{
			$conns = $c->find(array(
				"from" => $projects,
				"type" => RELTYPE_PRJ_VIDEO,
			));

			foreach($conns as $conn)
			{

				$v_o = new object($conn["to"]);
				$project_videos[$conn["from"]] = $v_o->properties(); 

			};
		}

		if (1 == $arr["first_image"])
		{
			$conns = $c->find(array(
				"from" => $projects,
				"type" => RELTYPE_PRJ_IMAGE,
			));

			$t_img = get_instance(CL_IMAGE);
		

			foreach($conns as $conn)
			{
				$project_images[$conn["from"]] = $t_img->get_url_by_id($conn["to"]);
			};

			$conns = $c->find(array(
				"from" => $ids,
				"type" => 1, // RELTYPE_PICTURE from CL_STAGING
			));

			foreach($conns as $conn)
			{
				$project_images[$conn["from"]] = $t_img->get_url_by_id($conn["to"]);
			};

			if (is_array($ids))
			{
				foreach($ids as $id)
				{
					$fx = $id;
					$fxo = new object($fx);
					$project_images[$fxo->id()] = $project_images[$fx];
				};
			};

		};
		
		$baseurl = aw_ini_get("baseurl");

		foreach($events as $key => $event)
		{
			$prid = $event["pr"];
			if ($projects[$prid])
			{
				$prid = $projects[$prid];
			};
			if ($web_pages[$prid])
			{
				$web_page_id = $web_pages[$prid];
				$events[$key]["project_weblink"] =  $baseurl . "/" . $web_page_id;
				$events[$key]["project_day_url"] = $baseurl . "/" . $web_page_id . "?view=3&date=" . date("d-m-Y",$event["start"]);
			};

			if ($project_images[$event["id"]])
			{
				$events[$key]["first_image"] = $project_images[$event["id"]];
			}
			else if ($project_images[$event["pr"]])
			{
				$events[$key]["first_image"] = $project_images[$event["pr"]];
			}
			else
			{
				$events[$key]["first_image"] = $baseurl . "/img/trans.gif";
			};

			if ($project_videos[$event["pr"]])
			{
				$events[$key]["media"] = $project_videos[$event["pr"]];

			};
		};


		if (sizeof($events) > 0)
		{
			$mpr = $this->get_master_project($o,$level);
			$this->prj_level = 1;

			$this->prj_levels[$mpr->id()] = $this->prj_level;
			$this->prj_level++;

	
			$this->used = array();
			$prj_levels = $this->prj_levels;


			$this->_recurse_projects2($mpr->id());
			

			// aaah, see on see bloody brother_list ju

			// iga eventi kohta on vaja teada kõiki vendi
			$ol = new object_list(array(
				"brother_of" => $ids,
				"lang_id" => array(),
			));
		


			// how does it work? Events will be assigned to multiple projects
			// by creating brothers in the event folders of the other projects

			// a tree is built from the projects. While I'm showing projects
			// I don't know on which level a particular project is nor what
			// the path of from the root project is

			// so I create a tree of all projects and assign a level number to
			// each. 

			// then a list of all brothers of an event is created, which will
			// yield a list of project id's which is then matched against the 
			// project level numbers - and this gives us the desired result

			enter_function("find-parent");
			$ox = $ol->arr();
			foreach($ox as $brot)
			{
				// et siis teeme uue nimekirja kõigist objektidest, jees?
				$prnt = new object($brot->parent());
				$pid = $prnt->id();
				$prj_level = $this->_ptree[$pid];
				enter_function("get-original");
				$orig = $brot->get_original();
				exit_function("get-original");

				if ($prj_level)
				{
					enter_function("project-assign-event");
					$events[$orig->id()]["parent_" . $prj_level . "_name"] = $this->_pnames[$pid];
					exit_function("project-assign-event");
				};
			};
			exit_function("find-parent");

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
		//print "disconnecting " . $arr["event_id"];
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

	function _init_work_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "order",
			"caption" => "Rea number",
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
			"name" => "ready",
			"caption" => "Millal valmis",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "length",
			"caption" => "Kaua kestab",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
			"align" => "center"
		));
	}


	function do_work_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_work_tbl($t);
		$t->set_default_sortby("order");

		$order = $arr["obj_inst"]->meta("order");
		$length = $arr["obj_inst"]->meta("length");
		$processing = $arr["obj_inst"]->meta("processing");
		$done = $arr["obj_inst"]->meta("done");

		$srl = $this->_get_sel_resource_list($arr["obj_inst"]);
		foreach($srl as $resid)
		{
			if (!$length[$resid])
			{
				$length[$resid] = 1; // default to 1h
			}
			if (!$order[$resid])
			{
				$order[$resid] = 0; 
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
			if (!$event_ids[$resid])
			{
				continue;
			}
			$reso = obj($resid);

			$status = "";
			if ($event_ids[$resid] && $processing[$resid] && !$done[$resid])
			{
				$status = "T&ouml;&ouml;s";
			}

			if ($event_ids[$resid] && $processing[$resid] && $done[$resid])
			{
				$status = "Valmis";
			}

			if ($status == "")
			{
				continue;
			}

			$time = date("d.m.Y H:i", $this->times_by_resource[$resid]);
			$ready = date("d.m.Y H:i", $this->times_by_resource[$resid] + (3600.0 * (str_replace(",", ".", $length[$resid]))));

			$t->define_data(array(
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $resid), CL_WORKFLOW_RESOURCE),
					"caption" => $reso->name()
				)),
				"time" => $time,
				"ready" => $ready,
				"order" => $order[$resid],
				"length" => $length[$resid]." tundi",
				"status" => $status,
			));
		}

		$t->sort_by();
	}


	function do_write_times_to_cal($arr)
	{
		if (!is_array($arr["request"]["time"]))
		{
			return;
		}

		$event_ids = $arr["obj_inst"]->meta("event_ids");
		$length = $arr["request"]["length"]; //$arr["obj_inst"]->meta("length");
		$buffer = $arr["request"]["buffer"]; //$arr["obj_inst"]->meta("buffer");

		load_vcl("date_edit");
		$de = new date_edit();

		foreach($arr["request"]["time"] as $resid => $time_d)
		{
			//echo "time_d ".dbg::dump($time_d)." <br>";
			$ts = $de->get_timestamp($time_d);
			$this->glob_res_ts[$resid] = $ts;
			//echo "got ts ".date("d.m.Y H:i", $ts)." <br>";

			$reso = obj($resid);
			$end = $ts + (((float)str_replace(",", ".", $length[$resid])) * 3600);

			if ($event_ids[$resid])
			{
				$ev = obj($event_ids[$resid]);

				$dat_by_resid[$resid] = array(
					"start" => $ev->prop("start1"),
					"end" => $ev->prop("end"),
					"buffer" => $ev->meta("buffer"),
					"pred" => $ev->meta("pred")
				);

				$ev->set_prop("start1", $ts);
				$ev->set_prop("end", $end);
				$ev->set_meta("buffer", $buffer[$resid]);
				$ev->set_meta("pred", $arr["request"]["pred"][$resid]);
				$ev->set_meta("task_priority", $arr["obj_inst"]->prop("priority"));
				$ev->set_meta("job_id", $arr["obj_inst"]->id());
				$ev->save();
			}
			else
			{
				// add an event
				$dat_by_resid[$resid] = array(
					"start" => 0,
					"end" => 0,
					"buffer" => 0,
					"pred" => 0
				);

				$ev = obj();
				$ev->set_parent($resid);
				$ev->set_class_id(CL_CRM_MEETING);
				$ev->set_name($arr["obj_inst"]->name());
				$ev->set_prop("start1", $ts);
				$ev->set_prop("end", $end);
				$ev->set_meta("pred", $arr["request"]["pred"][$resid]);
				$ev->set_meta("buffer", $buffer[$resid]);
				$ev->set_meta("task_priority", $arr["obj_inst"]->prop("priority"));
				$ev->set_meta("job_id", $arr["obj_inst"]->id());
				$ev->save();

				$reso->connect(array(
					"to" => $ev->id(),
					"reltype" => 1 // RELTYPE_EVENT
				));
			}

			$event_ids[$resid] = $ev->id();

			// ach! if there are events during the one added with lower priority (if higer priority we fucked up earlier) 
			// then we must move all of them to a later date. 
			//echo "to add event to calendar, do move events lower, new event = ".date("d.m.Y H:i",$ts)." - ".date("d.m.Y H:i",$ts + (((float)str_replace(",", ".", $length[$resid])) * 3600))." <br>";
		}

		$arr["obj_inst"]->set_meta("event_ids", $event_ids);

		// change the times as the user wanted
		foreach($event_ids as $resid => $evid)
		{
			if (!$this->do_reschedule(obj($evid), $dat_by_resid[$resid]))
			{
				break;
			}
		}

		// resolve event overlaps that happened after the changes the user wanted are made
		foreach($event_ids as $resid => $evid)
		{
			$this->resolve_event_conflicts($resid);
		}
	}

	function do_save_resources($arr)
	{
		$evids = $arr["obj_inst"]->meta("event_ids");

		// if processing is checked then mark the events as not-moveable
		$newproc = new aw_array($arr["request"]["processing"]);
		$curproc = $arr["obj_inst"]->meta("processing");
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

		$arr["obj_inst"]->set_meta("pred", $arr["request"]["pred"]);
		$arr["obj_inst"]->set_meta("length", $arr["request"]["length"]);
		$arr["obj_inst"]->set_meta("buffer", $arr["request"]["buffer"]);
		$arr["obj_inst"]->set_meta("processing", $arr["request"]["processing"]);
		//echo "set processing as ".dbg::dump($arr["request"]["processing"])." <br>";
		$arr["obj_inst"]->set_meta("done", $arr["request"]["done"]);
	}

	function _init_res_tbl(&$t)
	{
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


	function do_res_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_res_tbl($t);
		$t->set_sortable(false);

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
		$srl = $this->_get_sel_resource_list($arr["obj_inst"]);
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
		if ($ord_change)
		{
			$arr["obj_inst"]->set_meta("order", $order);
			$arr["obj_inst"]->save();
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
			else
			{
				$this->rtgbr_cnt = 0;
				$this->req_get_time_for_resource($resid, $length, $order, $event_ids, $pred, $buffer);
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
				$this->ps_pred_cnt = 0;
				$ps_preds_ok = $this->do_check_ps_preds($resid, $pred, $processing, $order);
				
				if ($ps_preds_ok)
				{
					$processing_str = html::checkbox(array(
						"name" => "processing[$resid]",
						"value" => 1,
						"checked" => ($processing[$resid] == 1)
					));
				}
			}

			if ($processing_str == "")
			{
				$processing_str = html::hidden(array(
					"name" => "processing[$resid]",
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
					"name" => "done[$resid]",
					"value" => 1,
					"checked" => ($done[$resid] == 1)
				));
			}

			if ($done_str == "")
			{
				$done_str = html::hidden(array(
					"name" => "done[$resid]",
					"value" => $done[$resid],
				));
				if ($done[$resid] == 1)
				{
					$done_str .= "Jah";
				}
			}

			$time = $de->gen_edit_form("time[$resid]", $this->times_by_resource[$resid], 2004, 2008, true);
			$ready = date("d.m.Y H:i", $this->times_by_resource[$resid] + (3600 * ((float)str_replace(",",".",$length[$resid]))));
			if ($event_ids[$resid] && $processing[$resid])
			{
				$time = date("d.m.Y H:i", $this->times_by_resource[$resid]);
				$ready = date("d.m.Y H:i", $this->times_by_resource[$resid] + (3600 * ((float)str_replace(",", ".", $length[$resid]))));
			}

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
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $resid, "group" => "calendar"), CL_WORKFLOW_RESOURCE),
					"caption" => $reso->name()
				)),
				"time" => $time,
				"ready" => $ready,
				"order" => $order[$resid],
				"pred" => html::textbox(array(
					"name" => "pred[$resid]",
					"size" => 3,
					"value" => $pred[$resid]
				)),
				"length" => html::textbox(array(
					"name" => "length[$resid]",
					"size" => 3,
					"value" => $length[$resid]
				))." tundi",
				"buffer" => html::textbox(array(
					"name" => "buffer[$resid]",
					"size" => 3,
					"value" => $buffer[$resid]
				))." tundi",
				"processing" => $processing_str,
				"done" => $done_str,
				"current" => $cur
			));
		}

		//$arr["obj_inst"]->set_meta("order", $order);
	}

	function save_sel_resources($arr)
	{
		// get already connected
		$cs = array();
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_RESOURCE")) as $c)
		{
			$cs[$c->prop("to")] = $c->prop("to");
		}
		

		// go over conns and deleted the ones that are not selected
		foreach($cs as $srid)
		{
			if (!isset($arr["request"]["sel_resources"][$srid]))
			{
				$arr["obj_inst"]->disconnect(array(
					"from" => $srid
				));
			}
		}

		// go over sels and connect if not yet
		$sr = new aw_array($arr["request"]["sel_resources"]);
		foreach($sr->get() as $srid)
		{
			if (!isset($cs[$srid]))
			{
				$arr["obj_inst"]->connect(array(
					"to" => $srid,
					"reltype" => 2 // RELTYPE_RESOURCE
				));
			}
		}
	}

	function _get_resource_list($o)
	{
		error::throw_if(!$o->meta("entity_instance"), array(
			"id" => ERR_NO_ENT,
			"msg" => "prisma_order::get_resource_list(): no entity instance id!"
		));
		$e_i_o = obj($o->meta("entity_instance"));
		
		error::throw_if(!$e_i_o->prop("entity_type"), array(
			"id" => ERR_NO_ENT,
			"msg" => "prisma_order::get_resource_list(): no entity type id in entity instance (".$e_i_o->id().") !"
		));
		$entity_type = obj($e_i_o->prop("entity_type"));

		// get resrouces from entity type
		$ret = array();
		foreach($entity_type->connections_from(array("type" => "RELTYPE_RESOURCE")) as $c)
		{
			$ret[$c->prop("to")] = $c->prop("to.name");
		}
		return $ret;
	}

	function _get_sel_resource_list($o)
	{
		$ret = array();
		foreach($o->connections_from(array("type" => "RELTYPE_RESOURCE")) as $c)
		{
			$ret[$c->prop("to")] = $c->prop("to");
		}
		return $ret;
	}

	function _get_actions($o)
	{
		error::throw_if(!$o->meta("entity_instance"), array(
			"id" => ERR_NO_ENT,
			"msg" => "prisma_order::get_resource_list(): no entity instance id!"
		));
		$e_i_o = obj($o->meta("entity_instance"));
		
		$wfe = get_instance("workflow/workflow_entity_instance");
		return array("" => "") + $wfe->get_possible_next_states($e_i_o);
	}

	function _get_cur_action($o)
	{
		error::throw_if(!$o->meta("entity_instance"), array(
			"id" => ERR_NO_ENT,
			"msg" => "prisma_order::get_resource_list(): no entity instance id!"
		));
		$e_i_o = obj($o->meta("entity_instance"));

		$wfe = get_instance("workflow/workflow_entity_instance");
		$tmp = $wfe->get_current_state($e_i_o->id());

		return $tmp->name();
	}

	function req_get_time_for_resource($resid, $length, $order, $event_ids, $pred, $buffer, $min_time = NULL)
	{
		$this->rtgbr_cnt++;
		if ($this->rtgbr_cnt > 100)
		{
			echo "resources are in a cyclic dependency!";
			return;
		}
		$reso = obj($resid);
		//echo "enter rgt for resource ".$reso->name()." <br>";
		$res_i = $reso->instance();

		$real_length = (((float)str_replace(",", ".", $length[$resid])) * 3600);
			
		$params = array(
			"o" => $reso,
			"length" => $real_length,
			"ignore_events" => $event_ids,
			"priority" => $this->cur_priority
		);

		$max_t = 0;

		if ($min_time != NULL)
		{
			$max_t = $min_time;
			$this->min_times_by_resource[$resid] = $max_t;
		}

		if ($this->min_times_by_resource[$resid])
		{
			$max_t = $this->min_times_by_resource[$resid];
		}

		if ($pred[$resid] != "")
		{
			foreach(explode(",", $pred[$resid]) as $pred_num)
			{
				// pred_num is index, not resid
				$pred_id = array_search($pred_num, $order);
				if (!$pred_id)
				{
					echo "no predicate with number $pred_num! <br>";
					return;
				}
				//echo "pred_id for $pred_num = $pred_id <br>";

				if (!$pred_id)
				{
					echo "no predicate found for predicate number $pred_num <br>";
				}

				$real_length = (((float)str_replace(",", ".", $length[$pred_id])) * 3600);
				$buffer_len = (((float)str_replace(",", ".", $buffer[$pred_id])) * 3600);

				// min time is prev predicate time + prev predicate buffer time
				$__mt = NULL;
				if ($this->glob_res_ts[$pred_id])
				{
					$__mt = $this->glob_res_ts[$pred_id];
				}
				$rgtfr = $this->req_get_time_for_resource($pred_id, $length, $order, $event_ids, $pred, $buffer, $__mt);
				$max_t = max($max_t, $rgtfr + $real_length + $buffer_len);
				//echo "fir predicate $pred_id (num = $pred_num) buffer len = $buffer_len real len = $real_length rgtfr = ".date("d.m.Y H:i", $rgtfr)."<br>";
			}
				//echo "got min_t for $resid as ".date("d.m.Y H:i", $max_t)." <br>";
		}

		if ($max_t)
		{
			$params["min_time"] = $max_t;
		}

		$ts = $res_i->get_next_avail_time_for_resource($params);
		//echo "final ts for resource ".$reso->name()." = ".date("d.m.Y H:i", $ts)." <br>";
		$this->times_by_resource[$resid] = $ts;
		return $ts;
	}

	function do_reschedule($overlap, $event_data)
	{
		// must not assume that the event needs to be moved. 
		// check if the user has changed event data
		// event_data - start, length, buffer, pred
		if ($event_data["start"] == $overlap->prop("start1") &&
			$event_data["end"] == $overlap->prop("end") &&
			$event_data["buffer"] == $overlap->meta("buffer") &&
			$event_data["pred"] == $overlap->meta("pred")
		)
		{
			//return true;
		}

		//echo "no match ".$overlap->id()." ed = ".date("d.m.Y H:i", $event_data["start"])." start1 = ".date("d.m.Y H:i", $overlap->prop("start1"))." end = ".$overlap->prop("end")." buffer = ".$overlap->meta("buffer")." pred = ".$overlap->meta("pred")."<br>";

		$overlap_len = $overlap->prop("end") - $overlap->prop("start1");

		// find the job and resource for the overlap event. 
		$job_id = $overlap->meta("job_id");
		$job_o = obj($job_id);
		$j_events = $job_o->meta("event_ids");
		$j_length = $job_o->meta("length");
		$j_order = $job_o->meta("order");
		$j_pred = $job_o->meta("pred");
		$j_buffer = $job_o->meta("buffer");
		$overlap_resid = array_search($overlap->id(), $j_events);

		//echo "overlap_resid = $overlap_resid , overlap id = ".$overlap->id()." j_evs = ".dbg::dump($j_events)." <br>";


		//		find first avail time
		$this->cur_priority = $overlap->meta("task_priority");
		$this->times_by_resource = array();

		// in event ids, the vents for all the resources for the job must be given
		$j_events_keys = $this->make_keys(array_values($j_events));

		//echo "glob date = ".date("d.m.Y H:i:s", $event_data["start"])." evdate = ".date("d.m.Y H:i:s", $overlap->prop("start1"))." <br>";
				$this->rtgbr_cnt = 0;
		$ts = $this->req_get_time_for_resource($overlap_resid, $j_length, $j_order, $j_events_keys, $j_pred, $j_buffer, $overlap->prop("start1"));

		$res_obj = obj($overlap_resid);
		//echo "for resource ".$res_obj->name()." previous start was ".date("d.m.Y H:i", $overlap->prop("start1"))." end was ".date("d.m.Y H:i", $overlap->prop("end"))."<br>";
		$overlap->set_prop("start1", $ts);
		$overlap->set_prop("end", $ts + $overlap_len);

		//echo "for resource ".$res_obj->name()." got new start ".date("d.m.Y H:i", $overlap->prop("start1"))." end ".date("d.m.Y H:i", $overlap->prop("end"))."<br>";
		$overlap->save();

		//		move = true
		$moves = true;

		// also, calc the timestamps for all the other events for the job that the first event was in and move them forward.
		foreach($j_events as $j_resid => $j_evid)
		{
			if ($j_evid == $overlap->id())
			{
				continue;
			}

			$this->times_by_resource = array();
				$this->rtgbr_cnt = 0;
			$ts = $this->req_get_time_for_resource($j_resid, $j_length, $j_order, $j_events_keys, $j_pred, $j_buffer);
			$j_evo = obj($j_evid);
			//if ($ts > $j_evo->prop("start1"))
			//{
				$j_len = $j_evo->prop("end") - $j_evo->prop("start1");

				$res_obj = obj($j_resid);
				//echo "for resource ".$res_obj->name()." previous start was ".date("d.m.Y H:i", $j_evo->prop("start1"))." end was ".date("d.m.Y H:i", $j_evo->prop("end"))."<br>";

				$j_evo->set_prop("start1", $ts);
				$j_evo->set_prop("end", $ts + $j_len);

				//echo "for resource ".$res_obj->name()." got new start ".date("d.m.Y H:i", $j_evo->prop("start1"))." end ".date("d.m.Y H:i", $j_evo->prop("end"))."<br>";

				$j_evo->save();
			//}
		}

		return true;
	}

	function get_first_overlapping_event($evids)
	{
		// sort by time
		$evs = array();
		foreach($evids as $evid)
		{
			$tmp = obj($evid);
			$beg = $tmp->prop("start1");
			if (isset($evs[$beg]))
			{
				if ($evs[$beg]->meta("task_priority") > $tmp->meta("task_priority"))
				{
					return $tmp;
				}
				return $evs[$beg];
				//die("damn lapper! $beg evb4 = ".$evs[$beg]->id()." beg = ".date("d.m.Y H:i", $beg)." <br>");
			}
			$evs[$beg] = $tmp;
		}

		ksort($evs);

		$tmp = $evs;

		// for each event
		foreach($evs as $time => $event)
		{
			// check if another event overlaps with this one.
			// simple o(n*n) suck-ass search here
			foreach($tmp as $time2 => $event2)
			{
				if ($event2->id() == $event->id())
				{
					continue;
				}

				$ev1_end = $event->prop("end"); // + (((float)str_replace(",", ".", $event->meta("buffer"))) * 3600);
				$ev2_end = $event2->prop("end"); // + (((float)str_replace(",", ".", $event2->meta("buffer"))) * 3600);
				if (timespans_overlap($event->prop("start1"), $ev1_end, $event2->prop("start1"), $ev2_end))
				{
					//echo "overlap for ".$event2->id()." with ".$event->id()." (".date("d.m.Y H:i", $event2->prop("start1"))." - ".date("d.m.Y H:i", $event2->prop("end"))." vs ".date("d.m.Y H:i", $event->prop("start1"))." - ".date("d.m.Y H:i", $event->prop("end")).")<br>";
					// return the one with the lower priority
					if ($event2->meta("task_priority") > $event->meta("task_priority"))
					{
						return $event;
					}
					return $event2;
				}
			}
		}

		return false;
	}


	function resolve_event_conflicts($resid)
	{
		classload("date_calc");
		// get all events for that timespan
		$reso = obj($resid);
		$res_i = $reso->instance();

		// while $moves
		$moves = true;
		while ($moves)
		{
			$moves = false;
			$evids = $res_i->get_events_for_resource(array(
				"id" => $resid
			));

			//        if overlapping-events-exist
			//                get first overlap
			$overlap = $this->get_first_overlapping_event($evids);
			//echo "check for overlap in ".dbg::dump($evids)." got res = ".dbg::dump($overlap)." <br>";
			if ($overlap)
			{
				$overlap_len = $overlap->prop("end") - $overlap->prop("start1");

				// find the job and resource for the overlap event. 
				$job_id = $overlap->meta("job_id");
				$job_o = obj($job_id);
				$j_events = $job_o->meta("event_ids");
				$j_length = $job_o->meta("length");
				$j_order = $job_o->meta("order");
				$j_pred = $job_o->meta("pred");
				$j_buffer = $job_o->meta("buffer");
				$overlap_resid = array_search($overlap->id(), $j_events);

				$j_events_keys = $this->make_keys(array_values($j_events));

				// find first avail time
				$this->cur_priority = $overlap->meta("task_priority");
				$this->times_by_resource = array();
				$this->rtgbr_cnt = 0;
				$ts = $this->req_get_time_for_resource($overlap_resid, $j_length, $j_order, $j_events_keys, $j_pred, $j_buffer);
				//echo "got new ts as ".date("d.m.Y H:i", $ts)." len = $overlap_len <br>";
				$overlap->set_prop("start1", $ts);
				$overlap->set_prop("end", $ts + $overlap_len);
				$overlap->save();

				//                move = true
				$moves = true;

				// also, calc the timestamps for all the other events for the job that the first event was in and move them forward.
				foreach($j_events as $j_resid => $j_evid)
				{
					if ($j_evid == $overlap->id())
					{
						continue;
					}

				$this->rtgbr_cnt = 0;
					$ts = $this->req_get_time_for_resource($j_resid, $j_length, $j_order, $j_events_keys, $j_pred, $j_buffer);
					$j_evo = obj($j_evid);
					//if ($ts > $j_evo->prop("start1"))
					//{
						$j_len = $j_evo->prop("end") - $j_evo->prop("start1");
						$j_evo->set_prop("start1", $ts);
						$j_evo->set_prop("end", $ts + $j_len);
						$j_evo->save();
					//}
				}
			}
			// end while
		}
	}

	function do_check_ps_preds($resid, $pred, $processing, $order)
	{
		if ($pred[$resid] == "")
		{
			return true;
		}

		$this->ps_pred_cnt++;
		if ($this->ps_pred_cnt > 100)
		{
			echo "predicates cause a loop!";
			return false;
		}

		$ret = true;
		foreach(explode(",", $pred[$resid]) as $pred_num)
		{
			$pred_id = array_search($pred_num, $order);
			$ret &= ($processing[$pred_id] == 1);
			$ret &= $this->do_check_ps_preds($pred_id, $pred, $processing, $order);
		}

		return $ret;
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
			"tooltip" => t("Alamprojekt"),
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
			$conns = $o->connections_from(array(
				"type" => "RELTYPE_PRJ_CFGFORM",
			));

			if (sizeof($conns) > 0)
			{
				foreach($conns as $conn)
				{
					$cobj = $conn->to();
					$tb->add_menu_item(array(
						"name" => "x_" . $cobj->id(),
						"parent" => "subprj",
						"text" => $cobj->name(),
						"link" => $this->mk_my_orb("new",array(
							"parent" => $o->id(),
							"group" => "change",
							"cfgform" => $cobj->id(),
							"clid" => $cobj->subclass(),
							"return_url" => urlencode(aw_global_get("REQUEST_URI")),
						),$cobj->subclass()),
					));
				};
			}
			else
			{
				$tb->add_menu_item(array(
					"name" => "x_" . $o->id(),
					"parent" => "subprj",
					"text" => t("Etendus"),
					"link" => $this->mk_my_orb("new",array(
						"parent" => $o->id(),
						"group" => "change",
						"clid" => CL_STAGING,
						"return_url" => urlencode(aw_global_get("REQUEST_URI")),
					),CL_STAGING),
				));
			};
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

	function _recurse_projects2($parent)
	{
		$prx = new object($parent);
		$parent = $prx->id();

		$c = new connection();
		$conns = $c->find(array(
			"from.class_id" => CL_PROJECT,
			"to.class_id" => CL_PROJECT,
			"type" => 1,
		));

		$subs = array();
		$this->_pnames = array();
		$this->_fullnames = array();
		$saast = array();
		foreach($conns as $conn)
		{
			$o1 = new object($conn["from"]);
			$o2 = new object($conn["to"]);
			$subs[$conn["from"]][$conn["to"]] = $conn["to"];
			$subs[$o1->id()][$o2->id()] = $o2->id();
			$this->_pnames[$conn["from"]] = $conn["from.name"];
			$this->_pnames[$conn["to"]] = $conn["to.name"];
			$this->_pnames[$o1->id()] = $o1->name();
			$this->_pnames[$o2->id()] = $o2->name();
		};

		$this->subs = $subs;

		$this->_ptree = array();
		$this->level = 0;

		$this->name_stack = array();
		$this->done = array();
		$this->_finalize_tree($parent);


	}

	function _finalize_tree($parent)
	{
		if (!$this->subs[$parent])
		{
			return false;
		}
		if ($this->done[$parent])
		{
			return false;
		};
		$this->done[$parent] = 1;
			
		$this->_ptree[$parent] = $this->level;
		$this->level++;

		foreach($this->subs[$parent] as $item)
		{
			$this->_finalize_tree($item);
			$this->_ptree[$item] = $this->level;
		};

		$this->level--;
	}

	// I need to build a tree of names ... HOW?
	// a 2 level array where the key is the name of the project with no children
	// and the value is an array of names of the parents

	// seega .. alustades ühest projektist leiame kõik selle projekti alamprojektid
	// ma pean siis iga projekti kohta leidma et millisel tasemel ta on.

	////
	// !Gets a list of project id-s as an argument and creates a list of those in some $this variable
	// it should create a list of connections starting from those projects
	function _recurse_projects($parent,$prj_id)
	{
		#obj_set_opt("no_auto_translation", 1);
		if ($this->used[$parent])
		{
			return false;
		};
		//dbg::p1("111 recursing from " . $prj_id);
		//flush();
		$prj_obj = new object($prj_id);
		//dbg::p1("111 recursing from " . $prj_obj->name() . " / " . $prj_obj->id());
		//flush();

		/*
		$trans_conns = $prj_obj->connections_from(array(
			"type" => RELTYPE_ORIGINAL,
		));

		if (sizeof($trans_conns) > 0)
		{
			$first = reset($trans_conns);
			$prj_obj = new object($first->prop("to"));

		};
		*/

		//dbg::p1("recursing from " . $prj_obj->name());


		$prj_conns = $prj_obj->connections_from(array(
			"type" => "RELTYPE_SUBPROJECT",
		));
		foreach($prj_conns as $prj_conn)
		{
			$subprj_id = $prj_conn->prop("to");
			$to = $prj_conn->to();
			$this->prj_map[$parent][$subprj_id] = $subprj_id;
			$this->r_prj_map[$subprj_id] = $prj_id;
			$this->prj_levels[$subprj_id] = $this->prj_level;
			$this->prj_level++;
			//if (!$this->used[$subprj_id])
			//{
				$this->used[$subprj_id] = $subprj_id;
				$this->_recurse_projects($subprj_id,$subprj_id);
			//};
			$this->prj_level--;
		}
		

		#obj_set_opt("no_auto_translation", 0);

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
					"value" => t("Seda klassi ei saa kasutada sündmuste sisestamiseks"),
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
		$prj_id = $o->id();

		$prj_obj = $o;

		$obj = $o;


		$orig_conns = $o->connections_from(array(
			"type" => 103,
		));

		if (sizeof($orig_conns) > 0)
		{
			$first = reset($orig_conns);
			$prj_id = $first->prop("to");
			$prj_obj = $first->to();
		};


		$this->read_template("show.tpl");


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
			$start_from = mktime(0,0,0,date("m"), 1, date("Y"));
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

		// no need for that .. I just get the type from url

		// argh .. projekti otse vaatamin on ikka paras sitt küll

		$caldata = $cal_view->parse_alias(array(
			"obj_inst" => $project_obj,
			"use_template" => $use_template,
			"event_template" => "project_event.tpl",
			"viewtype" => $viewtype,
			"status" => STAT_ACTIVE,
			"skip_empty" => true,
			"full_weeks" => true,
			"start_from" => $start_from
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
