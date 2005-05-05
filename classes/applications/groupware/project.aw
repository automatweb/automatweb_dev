<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/project.aw,v 1.40 2005/05/05 14:18:34 ahti Exp $
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

@property doc type=relpicker reltype=RELTYPE_PRJ_DOCUMENT field=meta method=serialize
@caption Loe lähemalt

@property skip_subproject_events type=checkbox ch_value=1 field=meta method=serialize
@caption Ära näita alamprojektide sündmusi

@default group=web_settings
@property project_navigator type=checkbox ch_value=1 field=meta method=serialize
@caption Näita projektide navigaatorit

@property use_template type=select field=meta method=serialize
@caption Välimus

@property doc_id type=textbox size=6 field=meta method=serialize
@caption Dokumendi ID, milles asub kalendri vaade, milles sündmusi kuvatakse

@default group=prj_image
@property prj_image type=releditor reltype=RELTYPE_PRJ_IMAGE use_form=emb rel_id=first field=meta method=serialize
@caption Pilt

@default group=event_list

@property event_toolbar type=toolbar no_caption=1
@caption Sündmuste toolbar 

@property event_list type=calendar no_caption=1
@caption Sündmused

//@property event_list type=calendar group=event_list no_caption=1

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

@reltype PRJ_EVENT value=3 clid=CL_TASK,CL_CRM_CALL,CL_CRM_OFFER,CL_CRM_DEAL,CL_CRM_MEETING,CL_PARTY,CL_COMICS
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

		$this->event_entry_classes = array(CL_CALENDAR_EVENT, CL_STAGING, CL_CRM_MEETING, CL_TASK, CL_CRM_CALL, CL_PARTY, CL_COMICS);
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
			case "add_event":
				$this->register_event_with_planner($arr);
				break;

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
		if ($range["overview_start"])
		{
			$start = $range["overview_start"];
		};

		$end = $range["end"];
		classload("core/icons");

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
		$clss = aw_ini_get("classes");

		foreach($ol->arr() as $o)
		{
			$id = $o->id();
			//if ($id != $o->brother_of())
			//{
				// this will break things, but makes estonia work for now
				//continue;
			//};


			$start = $o->prop("start1");
			$end = $o->prop("end");
			$clid = $o->class_id();
			
			$clinf = $clss[$clid];

			$link = $this->mk_my_orb("change",array("id" => $id,"return_url" => $req),$clid);

			$t->add_item(array(
				"item_start" => $start,
				"item_end" => $end,
				"data" => array(
					"name" => $o->prop("name"),
					"modifiedby" => $o->modifiedby(),
					"modified" => $o->modified(),
					"created" => $o->created(),
					"createdby" => $o->createdby(),
					"icon" => icons::get_icon_url($o),
					"link" => $link,
				),
			));

			if ($start > $range["overview_start"])
			{
				// show event on all days it occurs and not only the first
				if ($start < $end)
				{
					for ($i = $start; $i <= $end; $i = $i + 86400)
					{
						$this->overview[$i] = 1;
					};
				};
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
	function get_event_folders($arr = array())
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
				"reltype" => 2 //RELTYPE_PARTICIPANT,
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
			"type" => "RELTYPE_PRJ_EVENT",
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
	
	function get_event_sources($id)
	{
		$o = new object($id);
		$orig_conns = $o->connections_from(array(
			"type" => 103,
		));
		if (sizeof($orig_conns) > 0)
		{
			$first = reset($orig_conns);
			$id = $first->prop("to");
		};
		$sources = array($id => $id);
		if ($o->prop("skip_subproject_events") != 1)
		{
			$this->used = array();
			$this->_recurse_projects(0, $id);
		};
		if (is_array($this->prj_map))
		{
			foreach($this->prj_map as $key => $val)
			{
				foreach($val as $k1 => $v1)
				{
					$sources[$k1] = $k1;
				}
			}
		}
		return $sources;
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
		$limit_num = 100;

		$parent = join(",",$parents);

		$limit = "";
		if ($arr["range"]["limit_events"])
		{
			$limit = " LIMIT ".$arr["range"]["limit_events"];
			$limit_num = $arr["range"]["limit_events"];
		}

		// ma pean lugema sündmusi sellest projektist ja selle alamprojektidest.
		$_start = $arr["range"]["start"];
		if ($arr["range"]["overview_start"])
		{
			$_start = $arr["range"]["overview_start"];
		};
		$_end = $arr["range"]["end"];
		$lang_id = aw_global_get("lang_id");
		$stat_str = "objects.status != 0";

		if(is_array($arr["status"]))
		{
			$stat_str = "objects.status IN (".implode(",", $arr["status"]).")";
		}
		elseif($arr["status"] && aw_global_get("uid") == "")
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
		
		if($arr["range"]["viewtype"] == "relative")
		{
			if($_GET["date"])
			{
				list($d, $m, $y) = split("-", $_GET["date"]);
				$_start = mktime(23, 59, 59, $m, $d, $y);
			}
			else
			{
				$_start = mktime(23, 59, 59, 12, 12, 2020);
			}
			$_start = 
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
			WHERE (planner.start - $_start) <= 0 AND
			$stat_str AND objects.parent IN (${parent}) order by ($_start - planner.start) LIMIT $limit_num";
		}


		if (aw_global_get("uid") == "duke")
		{
			print $q;
		};



		// SELECT objects.oid AS id, objects.parent, objects.class_id, objects.brother_of, objects.name, planner.start, planner.end FROM planner LEFT JOIN objects ON (planner.id = objects.brother_of) WHERE ((planner.start >= '1099260000' AND planner.start <= '1104530399') OR (planner.end >= '1099260000' AND planner.end <= '1104530399')) AND objects.status != 0 AND objects.parent IN (2186)

		enter_function("project::query");
		dbg::p1($q);
		$this->db_query($q);
		$events = array();
		$pl = get_instance(CL_PLANNER);
		$ids = array();
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
			/*$pr_image = $pr_obj->get_first_obj_by_reltype("RELTYPE_PRJ_IMAGE");


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
				"end" => $row["end"],
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
			if (!is_oid($to) || !$this->can("view", $to))
			{
				continue;
			}
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

		if (1 == $arr["project_media"])
		{
			$conns = $c->find(array(
				"from" => $projects,
				"type" => 11 //RELTYPE_PRJ_VIDEO,
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
				"type" => 8 //RELTYPE_PRJ_IMAGE,
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
					if ($project_images[$fx])
					{
						$project_images[$fxo->id()] = $project_images[$fx];
					};
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
				if (!$this->can("view", $brot->parent()))
				{
					continue;
				}
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

	function gen_event_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		/*
		$tb->add_menu_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Uus"),
		));
		*/

		$o = $arr["obj_inst"];
		$inst = $o->instance();

		$int = $GLOBALS["relinfo"][$this->clid][3]; //RELTYPE_PRJ_EVENT

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
			"caption" => t("header"),
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
				$t = get_instance(CL_DOCUMENT);
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
			"type" => "RELTYPE_SUBPROJECT",
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
	
	function get_event_overview($arr)
	{
		// saan ette project id, alguse ja lõpu
		$rv = array();
		$ol = new object_list(array(
			"parent" => $arr["id"],
			"sort_by" => "planner.start",
			"site_id" => array(),
			new object_list_filter(array("non_filter_classes" => CL_CRM_MEETING)),
		));

		foreach($ol->arr() as $o)
		{
			$id = $o->id();
			$rv[] = array(
				"url" => "/" . $o->id(),
				"start" => $o->prop("start1"),
			);
		};
		return $rv;
	}

};
?>
