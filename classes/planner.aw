<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/planner.aw,v 2.174 2004/02/23 15:26:27 duke Exp $
// planner.aw - kalender
// CL_CAL_EVENT on kalendri event
/*

EMIT_MESSAGE(MSG_EVENT_ADD);

*/

/*

EMIT_MESSAGE(MSG_EVENT_ADD);	

	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general2
	@classinfo relationmgr=yes

	@property default_view type=select rel=1
	@caption Aeg

	@property content_generator type=select rel=1 group=advanced
	@caption Näitamisfunktsioon

	property event_cfgform type=relpicker reltype=RELTYPE_EVENT_ENTRY
	caption Def. sündmuse sisetamise vorm

	@property day_start type=time_select group=time_settings rel=1
	@caption Päev algab

	@property day_end type=time_select group=time_settings rel=1
	@caption Päev lõpeb

	@property only_days_with_events type=checkbox ch_value=1 group=advanced
	@caption Näidatakse ainult sündmustega päevi

	@property navigator_visible type=checkbox ch_value=1 default=1 group=advanced
	@caption Näita navigaatorit
	
	@property navigator_months type=select group=advanced
	@caption Kuud navigaatoris

	@property my_projects type=checkbox ch_value=1 group=advanced
	@caption Näita minu projekte

	@property items_on_line type=textbox size=4 group=special rel=1
	@caption Max. cell'e reas

	@property event_direction type=chooser group=advanced rel=1
	@caption Suund

	@property range_start type=date_select group=time_settings rel=1
	@caption Alates

	@property event_time_item type=textbox size=4 group=time_settings rel=1
	@caption Mitu päeva
	
	@property event_max_items type=textbox size=4 group=advanced rel=1
	@caption Max. sündmusi 

	@property use_template type=select group=advanced rel=1
	@caption Template

	@property event_folder type=relpicker reltype=RELTYPE_EVENT_FOLDER
	@caption Sündmuste kataloog

	@property workdays type=chooser multiple=1 group=advanced
	@caption Näidatavad päevad

	@default store=no

	@property navtoolbar type=toolbar group=views no_caption=1
	@caption Nav. toolbar

	@property project type=hidden group=views
	@caption Projekti ID
	
	@property calendar_contents type=calendar group=views no_caption=1 viewtype=week
	@caption Kalendri sisu

	@property add_event callback=callback_get_add_event group=add_event 
	@caption Lisa sündmus

	@groupinfo general caption=Seaded
	@groupinfo general2 caption=Üldine parent=general
	@groupinfo advanced caption=Sisuseaded parent=general
	@groupinfo views caption=Sündmused submit=no
	groupinfo show_day caption=Päev submit=no parent=views
	groupinfo show_week caption=Nädal submit=no parent=views
	groupinfo show_month caption=Kuu submit=no default=1 parent=views
	@groupinfo time_settings caption=Ajaseaded parent=general
	@groupinfo special caption=Spetsiaalne parent=general
	@groupinfo add_event caption="Muuda sündmust"
*/

// naff, naff. I need to create different views that contain different properties. That's something
// I should have done a long time ago, so that I can create different planners
define("WEEK",DAY * 7);
define("REP_DAY",1);
define("REP_WEEK",2);
define("REP_MONTH",3);
define("REP_YEAR",4);

/*
@reltype EVENT_SOURCE value=2 clid=CL_PLANNER,CL_PROJECT
@caption võta sündmusi

@reltype EVENT value=3 clid=CL_TASK,CL_CRM_CALL,CL_CRM_MEETING
@caption sündmus

@reltype DC_RELATION value=4 clid=CL_RELATION
@caption viide kalendri väljundile

@reltype GET_DC_RELATION value=5 clid=CL_PLANNER
@caption võta kalendri väljundid

@reltype EVENT_FOLDER value=6 clid=CL_MENU
@caption sündmuste kataloog

@reltype EVENT_ENTRY value=7 clid=CL_CFGFORM,CL_CRM_CALL
@caption sündmuse sisestamise vorm

@reltype CALENDAR_OWNERSHIP value=8 clid=CL_USER
@caption Omanik

*/

define("CAL_SHOW_DAY",1);
define("CAL_SHOW_OVERVIEW",2);
define("CAL_SHOW_WEEK",3);
define("CAL_SHOW_MONTH",4);


lc_load("calendar");
// Klassi sees me kujutame koiki kuupäevi kujul dd-mm-YYYY (ehk d-m-Y date format)
classload("calendar");
class planner extends class_base
{
	function planner($args = array())
	{
		$this->init(array(
			"tpldir" => "planner",
			"clid" => CL_PLANNER,
		));
		extract($args);
		$this->date = isset($date) ? $date : date("d-m-Y");
		lc_load("definition");
		$this->lc_load("planner","lc_planner");
			
		$this->viewtypes = array(
				"1" => "day",
				"3" => "week",
				"4" => "month",
				"5" => "relative",
		);

		$this->event_entry_classes = array(CL_TASK,CL_CRM_CALL,CL_CRM_OFFER,CL_CRM_MEETING);
		$this->specialgroups = array("projects","calendars");
	}

	function get_event_classes()
	{
		return $this->event_entry_classes;
	}

	function get_calendar_for_user($arr)
	{
		$uid = $arr["uid"];
		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid($uid));

		$conns = $user->connections_to(array(
			"type" => 8, //RELTYPE_CALENDAR_OWNERSHIP
		));
		if (sizeof($conns) == 0)
		{
			return false;
		};
		list(,$conn) = each($conns);
		$obj_id = $conn->prop("from");
		return $obj_id;
	}
	
	/**  
		
		@attrib name=my_calendar params=name is_public="1" caption="Minu kalender" all_args="1"
		
		
		@returns
		
		
		@comment

	**/
	function my_calendar($arr)
	{
		$this->init_class_base();
		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
		// now I need to figure out the calendar that is connected to the user object
		// XXX: why the fuck is it so hard to gain access to defined relation types from here?
		$conns = $user->connections_to(array(
			"type" => 8, //RELTYPE_CALENDAR_OWNERSHIP
		));
		if (sizeof($conns) == 0)
		{
			return sprintf("Kasutajal '%s' puudub default kalender",aw_global_get("uid"));
		};
		list(,$conn) = each($conns);
		$obj_id = $conn->prop("from");

		$arr["id"] = $obj_id;
		$arr["action"] = "change";
		$arr["group"] = "views";
		return $this->change($arr);
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "default_view":
				$data["options"] = $this->viewtypes;
				break;

			case "navigator_months":
				$data["options"] = array(1 => 1, 2 => 2, 3 => 3 );
				break;

			case "content_generator":
				$orb = get_instance("orb");
				$tmp = array("0" => "näita kalendri sisu") + $orb->get_classes_by_interface(array("interface" => "content"));
				$data["options"] = $tmp;
				break;

			case "navtoolbar":
				$this->gen_navtoolbar($arr);
				break;

			case "workdays":
				$daynames = explode("|",LC_WEEKDAY);
				for ($i = 1; $i <= 7; $i++)
				{
					$data["options"][$i] = $daynames[$i];
				};
				break;

			case "event_direction":
				$data["options"] = array(
					"1" => "tagasi",
					"0" => "praegu",
					"2" => "edasi",
				);
				break;

			case "use_template":
				$data["options"] = array(
					"" => "",
					"show_relative.tpl" => "Piltide üldvaade",
					"disp_day2.tpl" => "Päev",
				);
				break;
	
			case "calendar_contents":
				$this->gen_calendar_contents($arr);
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

			case "calendar_relation":
				// this is where I need to read the type of the output
				// and put it .. somewhere
				break;

		}
		return $retval;
	}

	////
	// !Returns an array of event id, start and end times in requested range
	// required arguments
	// id - calendar object
	function get_event_list($arr)
	{
		$obj = new object($arr["id"]);

		$folders = $event_ids = array();

		if ($obj->prop("event_folder") != "")
		{
			$folders[] = $obj->prop("event_folder");
		};

		if (empty($arr["start"]))
		{
			$di = get_date_range(array(
				"date" => isset($arr["date"]) ? $arr["date"] : date("d-m-Y"),
				"type" => $arr["type"],
			));

			$_start = $di["start"];
			$_end = $di["end"];
		}
		else
		{
			$_start = $arr["start"];
			$_end = $arr["end"];
		};

		// generate a list of folders from which to take events
		// both calendars and projects have "event_folder"'s
		$folderlist = $obj->connections_from(array(
			"type" => RELTYPE_EVENT_SOURCE,
		));

		foreach($folderlist as $conn)
		{
			$_tmp = $conn->to();
			if ($_tmp->prop("event_folder") != "")
			{
				$folders[] = $_tmp->prop("event_folder");
			};
		};
		
		// also include events from any projects that are connected to this calender
		// if the user wants so
		if ($obj->prop("my_projects") == 1)
		{
			$project = aw_global_get("project");
			$prj = get_instance(CL_PROJECT);
			// this is wrong, I need to figure out the users this calendar belongs to
			$owners = $obj->connections_from(array(
				"type" => RELTYPE_CALENDAR_OWNERSHIP,
			));

			// ignore projects, if there are no users connected to this calendar
			if (sizeof($owners) == 0)
			{
			}
			else
			{

				$user_ids = array();

				foreach($owners as $owner)
				{
					$user_ids[] = $owner->prop("to");
				};

				$event_ids = $event_ids + $prj->get_events_from_projects(array(
					"user_ids" => $user_ids,
					"project_id" => aw_global_get("project"),
					"type" => "my_projects",
				));
			};
		};
		
		$rv = array();
		// a project is selected, but no events in range? Just return
		// an empty array!
		if ($project && sizeof($event_ids) == 0)
		{
			return $rv;
		};

		$eidstr = $parstr = "";

		if (sizeof($event_ids) > 0)
		{
			$eidstr = " objects.oid IN (" . join(",",$event_ids) . ")";
			if ($project)
			{
				$eidstr = " AND" . $eidstr;
			}
			else
			{
				$eidstr = " OR" . $eidstr;
			};
		};

		if (sizeof($folders) > 0)
		{
			$parprefix = " AND ";
			$parstr = "objects.parent IN (" . join(",",$folders) . ")";
		};


		// that is the basic query
		// I need to add different things to it
		$q = "SELECT objects.oid AS id,objects.brother_of,objects.name,planner.start,planner.end
			FROM planner
			LEFT JOIN objects ON (planner.id = objects.brother_of)
			WHERE planner.start >= '${_start}' AND
			(planner.end <= '${_end}' OR planner.end IS NULL) AND
			objects.status != 0";


		// I could probably optimize this even further by not processing folders,
		// if events from a projects were requested.

		// if events from a project were requested, then include events
		// from that projects only - id's are in event_ids array()
		if ($project)
		{
			$q .= $eidstr;
		}
		// include events from all folders and all projects
		else
		{
			if ($parstr)
			{
				$q .= $parprefix . "(" . $parstr . $eidstr . ")";
			};
		}

		$this->db_query($q);
		while($row = $this->db_next())
		{
			$rv[$row["brother_of"]] = array(
				"id" => $row["id"],
				"start" => $row["start"],
				"end" => $row["end"],
			);
		};
		return $rv;
	}

	// this is called from calendar "properties"
	function _init_event_source($args = array())
	{
		extract($args);
		classload("icons");
		classload("date_calc");
		$prj = get_instance("groupware/project");
		$di = get_date_range(array(
			"date" => isset($date) ? $date : date("d-m-Y"),
			"type" => $type,
		));

		$obj = new object($id);
		$this->id = $id;

		$this->content_gen_class = "";
		if ($obj->meta("content_generator") != "")
		{
			list($pf,$pm) = explode("/",$obj->meta("content_generator"));
			$this->content_gen_class = $pf;
			$this->content_gen_method = $pm;
		};

		$folder = (int)$obj->meta("event_folder");
		
		$section = aw_global_get("section");


		$this->prevref = $this->mk_my_orb("change",array(
			"section" => $section,
			"id" => $id,
			"group" => "show_" . $type,
			"date" => $di["prev"],
			"id" => $id,
			"ctrl" => $ctrl,
		));

		$this->nextref = $this->mk_my_orb("change",array(
			"section" => $section,
			"id" => $id,
			"group" => "show_" . $type,
			"date" => $di["next"],
			"id" => $id,
			"ctrl" => $ctrl,
		));

		$_start = $di["start"];
		$_end = $di["end"];


		$events = $this->get_event_list($args);
		$reflist = array();
		// now, if a project has been requested from the URL, I need to do additional filtering for each object

		// we sure pass around a LOT of data
		$this->events_done = true;
		$rv = array();
		foreach($events as $event)
		{
			// fuck me. plenty of places expect different data from me .. until I'm
			// sure that nothing breaks, I can't remove this
			$row = $event + $this->get_object($event["id"]);
			$gx = date("dmY",$event["start"]);
			if ($this->content_gen_class)
			{
				$this->save_handle();
				$row["realcontent"] = $this->do_orb_method_call(array(
						"class" => $this->content_gen_class,
						"action" => $this->content_gen_method,
						"params" => array(
							"id" => $event["id"],
						),
					));
				$this->restore_handle();
			}

			$row["link"] = $this->get_event_edit_link(array(
				"cal_id" => $this->id,
				"event_id" => $event["id"],
			));

			$eo = new object($row["oid"]);
			if ($row["brother_of"] != $row["oid"])
			{
				$this->save_handle();
				$real_obj = $this->get_object($row["brother_of"]);
				if ($real_obj["status"] != 0)
				{
					$eo = $eo->get_original();
				};
				$row["name"] = $real_obj["name"];
				$row["comment"] = $real_obj["comment"];
				$row["status"] = $real_obj["status"];
				$row["flags"] = $real_obj["flags"];
				$this->restore_handle();
			};

			if ($row["status"] != 0)
			{
				$row["event_icon_url"] = icons::get_icon_url($eo);
				$rv[$gx][$row["brother_of"]] = $row;
				$reflist[] = &$rv[$gx][$row["brother_of"]];
			};
		};
		$this->day_orb_link = $this->mk_my_orb("change",array("id" => $id,"group" => "views","viewtype" => "day"));
		$this->week_orb_link = $this->mk_my_orb("change",array("id" => $id,"group" => "views","viewtype" => "week"));
		return isset($args["flatlist"]) ? $reflist : $rv;
	}

	function do_group_headers($arr)
	{
		$xtmp = $arr["t"]->groupinfo;
		$tmp = array(
			"type" => "text",
			"caption" => "header",
			"subtitle" => 1,
		);	
		unset($xtmp["calendar"]);
		$captions = array();
		// still, would be nice to make 'em _real_ second level groups
		// right now I'm simply faking 'em
		$xtmp["calendars"] = array(
			"caption" => "Kalendrid",
		);
		$xtmp["projects"] = array(
			"caption" => "Projektid",
		);
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

	function do_special_group($arr)
	{
		// yes, it looks weird, but I need to load the properties to get
		// to the groupinfo
		if (in_array($this->emb_group,$this->specialgroups))
		{
			//$event_obj = new object($emb["id"]);
			if ($this->emb_group == "projects")
			{
				$e_conns = $arr["event_obj"]->connections_to(array(
					"from.class_id" => CL_PROJECT,
				));

				$prjlist = array();
				foreach($e_conns as $conn)
				{
					$prjlist[$conn->prop("from")] = 1;
				};

				$users = get_instance("users");
				$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
				$conns = $user->connections_to(array(
					"from.class_id" => CL_PROJECT,
					"sort_by" => "from.name",
				));

				$all_props = array();

				foreach($conns as $conn)
				{
					$all_props["prj_" . $conn->prop("from")] = array(
						"type" => "checkbox",
						"name" => "prj" . "[" .$conn->prop("from") . "]",
						"caption" => html::href(array(
							"url" => $this->mk_my_orb("change",array("id" => $conn->prop("from")),"project"),
							"caption" => "<font color='black'>" . $conn->prop("from.name") . "</font>",
						)),
						"ch_value" => $prjlist[$conn->prop("from")],
						"value" => 1,
					);
				};
			}
			elseif ($this->emb_group == "calendars")
			{
				$brlist = new object_list(array(
					"brother_of" => $arr["event_obj"]->id(),
					// ignore site id's for this list
					"site_id" => array(),
				));

				for($o =& $brlist->begin(); !$brlist->end(); $o =& $brlist->next())
				{
					$plrlist[$o->parent()] = $o->id();
				};

				$all_props = array();

				foreach($this->get_planners_with_folders() as $row)
				{
					if ($row["event_folder"] != $arr["event_obj"]->parent())
					{
						$folderdat = $this->get_object($row["event_folder"]);

						$all_props["link_calendars_" . $row["oid"]] = array(
							"type" => "checkbox",
							"name" => "link_calendars" . "[" .$row["oid"] . "]",
							"caption" => html::href(array(
								"url" => $this->mk_my_orb("change",array("id" => $row["oid"]),"planner"),
								"caption" => "<font color='black'>" . $row["name"] . "</font>",
							)),
							"ch_value" => $row["oid"],
							"value" => isset($plrlist[$row["event_folder"]]) ? $row["oid"] : 0,
						);
					};
				};
			}
		}
		return $all_props;
	}

	////
	// !Displays the form for adding a new event
	function callback_get_add_event($args = array())
	{
		// yuck, what a mess
		$obj = $args["obj_inst"];
		//$obj = $this->get_object($args["request"]["id"]);
		$meta = $obj->meta();

		// use the config form specified in the request url OR the default one from the
		// planner configuration
		$event_cfgform = $args["request"]["cfgform_id"];
		// are we editing an existing event?
		if (!empty($args["request"]["event_id"]))
		{
			$event_id = $args["request"]["event_id"];
			if (true || $GLOBALS["object_loader"]->object_exists($event_id))
			{
				$event_obj = new object($event_id);
				if ($event_obj->is_brother())
				{
					$event_obj = $event_obj->get_original();
				};
				$this->event_id = $event_id;
				$clid = $event_obj->class_id();
				if ($clid == CL_DOCUMENT || $clid == CL_BROTHER_DOCUMENT)
				{
					unset($clid);
				};
			}
		}
		else
		{
			$clid = $args["request"]["clid"];
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
				// something to think about: so how do I put that event in other calendars .. only documents
				// have brother documents?

				// 1 - get an instance of that class, for this I need to 
				aw_session_set('org_action',aw_global_get('REQUEST_URI'));
				$clfile = $this->cfg["classes"][$clid]["file"];
				$t = get_instance($clfile);
				$t->init_class_base();
				$emb_group = "general";
				if ($this->event_id && $args["request"]["cb_group"])
				{
					$emb_group = $args["request"]["cb_group"];
				};
				$this->emb_group = $emb_group;
			
				$obj_to_load = $this->event_id;
				$t->id = $obj_to_load;

				$all_props = $t->get_active_properties(array(
					"group" => $emb_group,
				));
		
				if (in_array($this->emb_group,$this->specialgroups))
				{
					$all_props = $this->do_special_group(array(
						"event_obj" => &$event_obj,
					));
				};
			
				if ($this->event_id)
				{
					$t->obj_inst = $event_obj;
				};		

				$xprops = $t->parse_properties(array(
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
				if ($this->event_id)
				{
					$resprops[] = array("emb" => 1,"type" => "hidden","name" => "emb[id]","value" => $obj_to_load);	
				};
			};
		}
		else/*if ($event_cfgform)*/
		{
			aw_session_set('org_action',aw_global_get('REQUEST_URI'));
			$ev_data = $this->get_object($event_id);
			if ($ev_data["meta"]["cfgform_id"])
			{
				$event_cfgform = $ev_data["meta"]["cfgform_id"];
			};
			$frm = $this->get_object($event_cfgform);
			// events are documents
			classload("doc");
			$t = new doc();
			$t->cfgform = $frm;
			$t->cfgform_id = $frm["oid"];
			$t->init_class_base();

			$emb_group = "general";
			if ($this->event_id && $args["request"]["cb_group"])
			{
				$emb_group = $args["request"]["cb_group"];
			};

			$this->emb_group = $emb_group;

			$t->role = "obj_edit";
			
			$obj_to_load = $this->event_id;

			if ($ev_data["class_id"] == CL_BROTHER_DOCUMENT)
			{
				$obj_to_load = $ev_data["brother_of"];
			};

			$t->id = $obj_to_load;



			// now, I have a certain amount of groups that should get 
			// get their contents from this class ... or perhaps not?

			// well, there still are connections you know. just the special
			// interface for creating them is located inside the planner class ..

			// dunno, doesn't look like a very good idea but lets see how
			// it works out

			$all_props = $t->get_active_properties(array(
				"group" => $emb_group,
			));
				
			if (in_array($this->emb_group,$this->specialgroups))
			{
				$all_props = $this->do_special_group(array(
					"event_obj" => &$event_obj,
				));
			};

			if ($this->event_id)
			{
				$t->obj_inst = $event_obj;
				//$t->id = $obj_to_load;
			};

			// see gruppide tegemine on vaja kuidagi paremini tööle saada junõu
			$xprops = array();
			$this->emb_group = $emb_group;
			$bid = $obj->brother_of();
			$t->inst->set_calendars(array($bid));
			foreach($all_props as $sk => $st)
			{
				if ($st["richtext"])
				{
					unset($all_props[$sk]["richtext"]);
				};
			};
			//$t->inst->set_calendars(array($obj->id()));
			$xprops = $t->parse_properties(array(
					"properties" => $all_props,
					"name_prefix" => "emb",
			));
			$resprops = array();
				$resprops["capt"] = $this->do_group_headers(array(
					"t" => &$t,
				));
			// bad, I need a way to detect the default group. 
			// but for now this has to do.
			//$resprops["capt"] = $tmp;
			foreach($xprops as $key => $val)
			{
				$resprops[$key] = $val;
			};

			$resprops[] = array("type" => "hidden","name" => "emb[class]","value" => "doc");
			$resprops[] = array("type" => "hidden","name" => "emb[action]","value" => "submit");
			$resprops[] = array("type" => "hidden","name" => "emb[group]","value" => $emb_group);
			if ($obj_to_load)
			{
				$resprops[] = array("type" => "hidden","name" => "emb[id]","value" => $obj_to_load);	
			}
			else
			{
				$resprops[] = array("type" => "hidden","name" => "emb[cfgform]","value" => $event_cfgform);	
			};

		};
		/*
		else
		{
			$resprops[] = array(
				"type" => "text",
				"value" => "Sündmusi ei saa lisada enne, kui oled valinud eventite sisestamise vormi",
			);
		};
		*/

		return $resprops;
	}

	function register_event_with_planner($args = array())
	{
		$event_folder = $args["obj_inst"]->prop("event_folder");
		if (empty($event_folder))
		{
			return PROP_ERROR;
		};
		$emb = $args["request"]["emb"];
		$is_doc = false;
		if (!empty($emb["clid"]))
		{
			$clfile = $this->cfg["classes"][$emb["clid"]]["file"];
			$t = get_instance($clfile);
			$t->init_class_base();
		}
		else
		{
			$obj = $this->get_object($args["obj_inst"]->id());
			$event_cfgform = $obj["meta"]["event_cfgform"];
			$frm = $this->get_object($event_cfgform);
			classload("doc");
			$t = new doc();
			$is_doc = true;

			// nini. kui embedded object on document, siis saab vendade loomine special meaningu
			// sest need tehakse siis vendadega. otherwise, 
		};
		if (is_array($emb))
		{
			if (empty($emb["id"]))
			{
				$emb["parent"] = $event_folder; 
			};
		};
		$t->id_only = true;
		if (isset($emb["group"]))
		{
			$this->emb_group = $emb["group"];
		};
		// huu! Is it really that easy?
		if (in_array($this->emb_group,$this->specialgroups))
		{
			$this->event_id = $emb["id"];
			$event_obj = new object($emb["id"]);
			if ($event_obj->is_brother())
			{
				$event_obj = $event_obj->get_original();
			};
			if ($this->emb_group == "projects")
			{
				// 1) retreieve all connections that this event has to projects
				// 2) remove those that were not explicitly checked in the form
				// 3) create new connections which did not exist before
				$e_conns = $event_obj->connections_to(array(
					"from.class_id" => CL_PROJECT,
				));

				$new_ones = array();
				if (is_array($args["request"]["emb"]["prj"]))
				{
					$new_ones = $args["request"]["emb"]["prj"];
				};
				
				$prj_inst = get_instance("groupware/project");

				foreach($e_conns as $conn)
				{
					if (!$new_ones[$conn->prop("from")])
					{
						$prj_inst->disconnect_event(array(
							"id" => $conn->prop("from"),
							"event_id" => $event_obj->id(),
						));
					};
					unset($new_ones[$conn->prop("from")]);
				};

				foreach($new_ones as $new_id => $whatever)
				{
					$prj_inst->connect_event(array(
						"id" => $new_id,
						"event_id" => $emb["id"],
					));
				};

			};
			//if ($this->emb_group == "calendars" && !$is_doc)
			if ($this->emb_group == "calendars")
			{
				// 1) retrieve all connections that this event has to projects
				// 2) remove those that were not explicitly checked in the form
				// 3) create new connections which did not exist before

				// urk .. I need all brothers of the event object.

				$brlist = new object_list(array(
					"brother_of" => $event_obj->id(),
				));

				$plrlist = array();
				
				for($o =& $brlist->begin(); !$brlist->end(); $o =& $brlist->next())
				{
					if ($o->id() != $event_obj->id())
					{
						$plrlist[$o->parent()] = $o->id();
					};
				};

				$all_props = array();

				$new_ones = array();
				if (is_array($args["request"]["emb"]["link_calendars"]))
				{
					$new_ones = $args["request"]["emb"]["link_calendars"];
				};

				foreach($plrlist as $plid => $evid)
				{
					if (!$new_ones[$plid])
					{
						$this->disconnect_event(array(
							"event_id" => $evid,
						));
					};
					unset($new_ones[$plid]);
				};

		
				// now new_ones sisaldab nende kalendrite id-sid, millega ma pean seose looma
				foreach($new_ones as $plid)
				{
					$this->connect_event(array(
						"id" => $plid,
						"event_id" => $emb["id"],
					));
				};
			};
		}
		else
		{
			if (!empty($emb["id"]))
			{
				$event_obj = new object($emb["id"]);
				$emb["id"] = $event_obj->brother_of();
			};
			$this->event_id = $t->submit($emb);
			if (!empty($emb["id"]))
			{
				$this->event_id = $event_obj->id();
			};
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


		preg_match('/alias_to_org=(\w*)&/', $gl, $o);
		preg_match('/reltype_org=(\w*)&/', $gl, $r);

		if (is_numeric($o[1]) && is_numeric($r[1]))
		{
			$org_obj = new object($o[1]);
			$org_obj->connect(array(
				"to" => $this->event_id,
				"reltype" => $r[1],
			));
			aw_session_del('org_action');

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

	function callback_mod_reforb($args = array())
	{
		if (isset($this->event_id))
		{
			$args["event_id"] = $this->event_id;
		};
	}

	function callback_mod_retval($arr)
	{
		$args = &$arr["args"];
		if ($this->event_id)
		{
			$form_data = &$arr["request"];
			$args["event_id"] = $this->event_id;
			if ($this->emb_group && $this->emb_group != "general")
			{
				$args["cb_group"] = $this->emb_group;
			};
		};
		if ($arr["request"]["project"])
		{
			$args["project"] = $arr["request"]["project"];
		};
	}

	function callback_mod_tab($args = array())
	{
		//if ($args["id"] == "add_event" && empty($this->event_id))
		if ($args["activegroup"] != "add_event" && $args["id"] == "add_event")
		{
			return false;
		};

		if ($args["activegroup"] == "add_event" && $args["id"] == "add_event")
		{
			$link = &$args["link"];
			$link = $this->mk_my_orb("change",$args["request"]);
		};

	}

	////
	// !Parsib kalendrialiast
	function parse_alias($arr)
	{
		extract($arr);
		$this->cal_oid = $alias["target"];

                // if there is a relation object, then load it and apply the settings
                // it has.
                if ($alias["relobj_id"])
                {
                        $relobj = $this->get_object(array(
                                "oid" => $alias["relobj_id"],
                                "clid" => CL_RELATION,
                        ));

                        $overrides = $relobj["meta"]["values"]["CL_PLANNER"];
                        if (is_array($overrides))
                        {
                                $this->overrides = $overrides;
                        };
                        if (!empty($relobj["name"]))
                        {
                                $this->caption = $relobj["name"];
                        };

                        $this->relobj_id = $alias["relobj_id"];
                }

                $replacement = $this->change(array("id" => $alias["target"]));
                return $replacement;

	}

	////
	// !used to sort events by start date (ascending)
	function __asc_sort($el1,$el2)
	{
		return (int)($el1["start"] - $el2["start"]);
	}
	
	////
	// !used to sort events by start date (descending)
	function __desc_sort($el1,$el2)
	{
		return (int)($el2["start"] - $el1["start"]);
	}
	
	////
	// !Kuvab kalendri muutmiseks (eelkoige adminnipoolel)
	// id - millist kalendrit näidata
	// disp - vaate tüüp
	// date - millisele kuupäevale keskenduda
	function view($args = array())
	{
		return $this->change(array("id" => $args["id"]));
	}

	////
	// !tagastab eventid mingis ajavahemikus
	// argumendid:
	// start(timestamp), end(timestamp)
	// parent(int) - kalendri ID
	//  voi
	// uid(char) - kasutaja id, kui tegemist on kasutaja kalendriga
	// index_time - if set, the returned array is indexed by the event start time
	
	function get_events($args = array())
	{
		extract($args);
		$repeater = get_instance("repeater");
		if ($uid)
		{
			$selector = " AND planner.uid = '$uid'";
		}
		elseif ($parent)
		{
			$selector = " AND objects.parent = '$parent'";
		}
		elseif ($folder)
		{
			$select = " AND planner.folder = '$folder'";
		}
		
		if (!$end)
		{
			// note, the repeater parser is horribly ineffective with repeaters
			// that span over a long time period.
			$end = mktime(23,59,59,12,31,2002);
		};

		$eselect = (isset($event)) ? "AND planner.id = '$event'" : "";
		$limit = ($limit) ? $limit : 999999;
		$retval = array();
		$reps = array();
		if (isset($event))
		{
			$q = "SELECT * FROM planner
				LEFT JOIN objects ON (planner.id = objects.oid)
				WHERE objects.status = 2 AND planner.id = $event";
		}
		else
		{
			$q = "SELECT * FROM planner
			LEFT JOIN objects ON (planner.id = objects.oid)
			WHERE objects.status = 2 $select $eselect $tp
				AND ( (start >= '$start') OR (start <= '$end') OR (rep_until >= '$start'))
				ORDER BY start";
		};	
		$this->db_query($q);
		$results = array();
		$timebase = mktime(0,0,0,1,1,2001);
		$start_gdn = sprintf("%d",($start - $timebase) / 86400);
		$end_gdn = sprintf("%d",(($end - $timebase) / 86400) + 1);
		$gdn = $start_gdn;
		$range = range($start_gdn,$end_gdn);	
		while($row = $this->db_next())
		{
			$reps = aw_unserialize($row["repeaters"]);
			$meta = aw_unserialize($row["metadata"]);
			$ccounter = (int)$meta["cycle_counter"];
			for ($i = 1; $i <= $ccounter; $i++)
			{
				if ($meta["repeaters{$i}"]["own_time"])
				{
					$hour = $meta["repeaters{$i}"]["reptime"]["hour"];
					$minute = $meta["repeaters{$i}"]["reptime"]["minute"];
					list($d,$m,$y) = explode("-",date("d-m-Y",$start));
					# start from the next day?
					$_start = mktime($hour,$minute,0,$m,$d,$y);
					if ($_start < time())
					{	
						$_start = mktime($hour,$minute,0,$m,$d+1,$y);
					};
					$row["start"] = $_start;
				}
				else
				{
					$hour = $minute = 0;
				};
				if (is_array($reps))
				{
					$intersect = array_intersect($reps,$range);
				};
				// always show the event at the day it was added
				$idx = ($index_time) ? $row["start"] : date("dmy",$row["start"]);
				$retval[$idx][] = $row;
				if (is_array($intersect))
				{
					foreach($intersect as $xgdn)
					{
						$ts = mktime($hour,$minute,0,1,$xgdn,2001);
						if ($ts >= $row["rep_from"])
						{
							$gx = ($index_time) ? $ts : date("dmY",$ts);
							$retval[$gx][] = $row;
						};
					};
				};
				$intersect = "";
			};
				
			$gdn++;
		};
		return (sizeof($retval) > 0) ? $retval : false;
	}

	////
	// Takes 2 timestamps and calculates the difference between them in days
	//	args: time1, time2
	function get_day_diff($time1,$time2)
	{
		$diff = $time2 - $time1;
		$days = (int)($diff / DAY);
		return $days;
	}

	////
	// Takes 2 timestamps and calculates the difference between them in months
	function get_mon_diff($time1,$time2)
	{
		$date1 = date("d-m-Y",$time1);
		$date2 = date("d-m-Y",$time2);
		$d1 = explode('-', $date1);
		$d2 = explode('-', $date2);
		$diff = ($d2[2] * 12 + $d2[1]) - ($d1[2] * 12 + $d1[1]) - 1;
		return $diff;
	}

	function _get_event_repeaters($args = array())
	{
		extract($args);
		list($d1,$m1,$y1) = split("-",$start);
		list($d2,$m2,$y2) = split("-",$end);
		$start = mktime(0,0,0,$m1,$d1,$y1);
		$end = mktime(23,59,59,$m2,$d2,$y2);
		// I am sure this could be optimized to read only
		// those repeaters that do fall into our frame, but it would
		// be a monster SQL clause and at this moment I do not
		// think I would be able to do this.
		$q = "SELECT * FROM planner_repeaters
			WHERE cid = '$id' ORDER BY eid,type DESC";
		$this->db_query($q);
		$res = array();
		while($row = $this->db_next())
		{
			$res[$row["id"]] = $row;
		};
		return $res;
	}


	function _get_cal_target($rel_id)
	{
		if (!is_numeric($rel_id))
		{
			return false;
		};
		$q = "SELECT aliases2.source AS source FROM aliases
			LEFT JOIN aliases AS aliases2 ON (aliases.target = aliases2.relobj_id)
			WHERE aliases.relobj_id = '$rel_id'";
		return $this->db_fetch_field($q,"source");
	}

	////
	// !Tagastab timestambi mingi kuupäevastambi kohta
	// $date - d-m-Y
	function tm_convert($date)
	{
		extract($args);
		list($d,$m,$y) = split("-",$args["date"]);
		// miski modification voiks ka olla
		$retval = mktime(0,0,0,$m,$d,$y);
		return $retval;
	}


	////
	// !Näitab infot mingi eventi kohta
	function show_event($args = array())
	{
		extract($args);
		$uid = aw_global_get("uid");
		// $q = "SELECT * FROM planner WHERE id = '$id' AND uid = '$uid'";
		// $q = "SELECT * FROM msg_objects WHERE id = '$id'";
		// $this->db_query($q);
		// $row = $this->db_next();
		//$_x = unserialize($row["content"]);
		//$row = unserialize($_x["str"]);
		$row = $args;
		$this->read_template("show.tpl");
		$this->vars(array(
			"title" => $row["title"],
			"id" => $row["att_id"],
			"description" => $row["description"],
			"start" => date("d-M H:i",$row["start"]),
			"end" => date("d-M H:i",$row["end"]),
		));
		return $this->parse();
	}
		
	////
	// !Embed the repeater editor form inside the planner interface
	function event_repeaters($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		/*
		$menubar = $this->gen_menu(array(
			"activelist" => array("xxx"),
			"vars" => array("id" => $obj["parent"]),
		));
		*/
		$ce = get_instance("calendar/cal_event");
		$html = $ce->repeaters(array(
			"id" => $id,
			"cycle" => $cycle,
		));
		$this->mk_path($par_obj["parent"],"Kalender / Muuda sündmust");
		return $menubar . $html;
	}

	////
	// !Deletes a repeater.
	function delete_repeater($args = array())
	{
		extract($args);
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "repeaters" . $cycle,
			"delete_key" => true,
		));

		return $this->mk_my_orb("event_repeaters",array("id" => $id));
	}

	function bron_add_event($args = array())
	{
		extract($args);
		$id = $this->new_object(array(
			"class_id" => CL_CAL_EVENT,
			"parent" => $parent,
			"name" => $title,
		),true);

		$q = "INSERT INTO planner
			(id,uid,start,end,title,place,description)
			VALUES ('$id','$uid','$start','$end','$title','$place','$description')";
		$this->db_query($q);
		return $id;
	}


	function callback_on_addalias($args = array())
	{
		// now, $args[alias] is a reference to the thingie we are interested in
		// and $args[id] - is the source object ... I want to load the metadata
		// of the source object
		if ($args["reltype"] == RELTYPE_DC_RELATION)
		{
			$src = $this->get_object($args["id"]);
			if ($src["class_id"] == CL_RELATION)
			{
				$type = $src["meta"]["values"]["CL_PLANNER"]["content_generator"];
				if (!empty($type))
				{
					// noja kuhu phrsse ma selle siis redirectin?
					$target = $this->get_object($args["alias"]);
					$oldmeta = $target["meta"];
					// this is probably bad, I need a better approach to mark
					// content generators .. but for now this has to do
					// ... cause, what if gallery_v2 is changed to something other?
					$oldmeta["content_generator"][$type] = $args["id"];
					$this->upd_object(array(
						"oid" => $args["alias"],
						"metadata" => $oldmeta,
					));
				};
			};
		};

	}

	function gen_navtoolbar(&$arr)
	{
		$id = $arr["obj_inst"]->id();
                if ($id)
                {
			$toolbar = &$arr["prop"]["toolbar"];
			// would be nice to have a vcl component for doing drop-down menus
			$conns = $arr["obj_inst"]->connections_from(array(
				"type" => RELTYPE_EVENT_ENTRY,
			));
			$toolbar->add_menu_button(array(
                                "name" => "create_event",
                                "tooltip" => "Uus",
                        ));
			foreach($conns as $conn)
			{
				$toolbar->add_menu_item(array(
					"parent" => "create_event",
					"link" => $this->mk_my_orb("change",array(
						"id" => $id,
						"group" => "add_event",
						"cfgform_id" => $conn->prop("to"),
					)),
					"text" => $conn->prop("to.name"),
				));
			};

			// now I need to figure out which other classes are valid for that relation type
			$clidlist = $this->event_entry_classes;
			foreach($clidlist as $clid)
			{
				$toolbar->add_menu_item(array(
					"parent" => "create_event",
					"link" => $this->mk_my_orb("change",array(
						"id" => $id,
						"group" => "add_event",
						"clid" => $clid,
					)),
					"text" => $this->cfg["classes"][$clid]["name"],
				));
			};

			$dt = date("d-m-Y",time());

			$toolbar->add_button(array(
				"name" => "today",
				"tooltip" => "Täna",
				"url" => $this->mk_my_orb("change",array("id" => $id,"group" => "views","viewtype" => "day","date" => $dt)) . "#today",
				"img" => "icon_cal_today.gif",
				"class" => "menuButton",
			));

			// XXX: check acl and only show that button, if the user actually _can_
			// edit the calendar
			$toolbar->add_button(array(
				"name" => "delete",
				"tooltip" => "Kustuta märgitud sündmused",
				"url" => "javascript:document.changeform.action.value='delete_events';document.changeform.submit();",
				"img" => "delete.gif",
				"class" => "menuButton",
			));

			if ($arr["obj_inst"]->prop("my_projects") == 1)
			{
				$toolbar->add_separator();

				$prj_opts = array("" => "--filtreeri projekti järgi--");

				$users = get_instance("users");
				$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
				$conns = $user->connections_to(array(
					"from.class_id" => CL_PROJECT,
					"sort_by" => "from.name",
				));

				foreach($conns as $conn)
				{
					$prj_opts[$conn->prop("from")] = $conn->prop("from.name");
				};

				$toolbar->add_cdata("&nbsp;&nbsp;".html::select(array(
					"name" => "prj",
					"options" => $prj_opts,
					"selected" => $arr["request"]["project"],
				)));

				$toolbar->add_button(array(
					"name" => "refresh",
					"tooltip" => "Reload",
					"url" => "javascript:document.changeform.project.value=document.changeform.prj.value;document.changeform.submit();",
					"img" => "refresh.gif",
				));
			};
                };
	}

	function delete_events($args = array())
	{
		extract($args);
		if (sizeof($mark) > 0)
		{
			foreach($mark as $event)
			{
				$obj = new object($event);
				$obj->delete();
				//$this->delete_object($event);
			}
		};
		return $this->mk_my_orb("change",array("id" => $args["id"],"group" => $args["subgroup"],"date" => $args["date"]));
	}

	function connect_event($arr)
	{
		$ev_obj = new object($arr["event_id"]);
		$plr_obj = new object($arr["id"]);
		$bro = $ev_obj->create_brother($plr_obj->prop("event_folder"));
	}

	function disconnect_event($arr)
	{
		$bro = new object($arr["event_id"]);
		$bro->delete();
	}
	
	////
	// !Returns a list of planners that have event folders .. 
	function get_planners_with_folders($args = array())
	{
		$retval = array();

		$planners = new object_list(array(
			"class_id" => CL_PLANNER,
			"sort_by" => "name",
			"site_id" => array(),
		));

		for($o = $planners->begin(); !$planners->end(); $o = $planners->next())
		{
			if ($o->prop("event_folder") != 0)
			{
				$retval[] = array(
					"oid" => $o->id(),
					"name" => $o->name(),
					"event_folder" => $o->prop("event_folder"),
				);


			};


		};
		return $retval;
	}

	////
	// !Returns a link for editing an event
	// cal_id - calendar id
	// event_id - id of an event
	function get_event_edit_link($arr)
	{
		return $this->mk_my_orb("change",array(
			"id" => $arr["cal_id"],
			"group" => "add_event",
			"event_id" => $arr["event_id"],
			"return_url" => $arr["return_url"],
		));

	}

	function gen_calendar_contents($arr)
	{
		$arr["prop"]["vcl_inst"]->configure(array(
			"tasklist_func" => array(&$this,"get_tasklist"),
			"overview_func" => array(&$this,"get_overview"),
		));

		$viewtype = $this->viewtypes[$arr["obj_inst"]->prop("default_view")];

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"] ? $arr["request"]["viewtype"] : $viewtype,
		));

		$events = $this->_init_event_source(array(
			"id" => $arr["request"]["id"],
			"type" => $range["viewtype"],
			"flatlist" => 1,
			"date" => date("d-m-Y",$range["timestamp"]),
		));

		foreach($events as $event)
		{
			$arr["prop"]["vcl_inst"]->add_item(array(
				"timestamp" => $event["start"],
				"data" => array(
					"name" => $event["name"],
					"icon" => $event["event_icon_url"],
					"link" => $event["link"],
					"comment" => $event["comment"],
				),
			));
		};

		// set it, so the callback functions can use it
		$this->calendar_inst = $arr["obj_inst"];

	}

	function get_overview($arr = array())
	{
		$events = $this->get_event_list(array(
			"id" => isset($arr["id"]) ? $arr["id"] : $this->calendar_inst->id(),
			"start" => $arr["start"],
			"end" => $arr["end"],
		));
		$rv = array();
		foreach($events as $event)
		{
			$rv[$event["start"]] = 1;
		};
		return $rv;
	}

	function get_tasklist($arr = array())
	{
		$tasklist = new object_list(array(
			"class_id" => CL_TASK,
			"parent" => $this->calendar_inst->prop("event_folder"),
			"flags" => array(
				"mask" => OBJ_IS_DONE,
				"flags" => 0,
			),
			
		));

		$rv = array();

		foreach($tasklist->arr() as $task)
		{
			if ($task->is_brother())
			{
				$task = $task->get_original();
				if (($task->flags() & OBJ_IS_DONE) == OBJ_IS_DONE)
				{
					continue;
				};
			};
				

			$rv[] = array(
				"name" => $task->prop("name"),
				"url" => $this->get_event_edit_link(array(
					"cal_id" => $this->calendar_inst->id(),
					"event_id" => $task->id(),
				)),
			);
		};
		return $rv;
	}

};
?>
