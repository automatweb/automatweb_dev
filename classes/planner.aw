<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/planner.aw,v 2.172 2004/02/17 13:25:50 duke Exp $
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
			$q .= $parprefix . "(" . $parstr . $eidstr . ")";
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
			$obj = $this->get_object($args["obj"]["oid"]);
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

                $replacement = $this->view(array("id" => $alias["target"]));
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
		$date = $args["date"];
		$id = $args["id"];
		$ctrl = $args["ctrl"];
		$type = $args["type"];

		$obj = $this->get_object($id);
		$parent = $obj["parent"];

		// kui kuupäeva pole defineeritud, siis defaultime tänasele
		$dt = aw_global_get("date");
		if ($dt)
		{
			list($xd,$xm,$xy) = explode("-",$dt);
			$rev = date("d-m-Y",mktime(0,0,0,$xm,$xd,$xy));
			if ($rev == $dt)
			{
				$date = $dt;
			};
		};
		if (!$date)
		{
			$date = date("d-m-Y");
		};

		$this->date = $date;

		
		$actlink = $this->mk_my_orb("view",array("id" => $id,"date" => $date,"ctrl" => $ctrl,"type" => $act));

		$this->conf = $obj["meta"];
		if (is_array($this->overrides))
		{
			foreach($this->overrides as $key => $val)
			{
				if (!empty($val))
				{
					$this->conf[$key] = $val;
				};
			};
		};

		if (not($type))
		{
			$type = $this->viewtypes[$this->conf["default_view"]];
		}

		if (!$type)
		{
			$type = "day";
		};
		
		if ($type == "day")
		{
			// lame check whether to show today or just day
			// I don't know why those are different anyway
			$act = ($date == date("d-m-Y")) ? "today" : "day";
			$act = "day";
		}
		else
		{
			$act = $type;
		};

		if (!$act)
		{
			$act = "day";
		};
		$this->actlink = $actlink;
		
		$today = date("d-m-Y");

		// ctrl is a form controller object
		// if it's set, we get the information about possible ranges from that form

		$this->id = $id;
		$this->ctrl = $ctrl;

		$object = $this->get_object($id);

		//$this->conf = $args["config"];


		// parent_class ?
		$this->parent_class = $object["class_id"];

		$xdate = $d . $m . $y;

		$_cal = get_instance("calendar",array("tpldir" => "planner"));
		classload("date_calc");
		$di = get_date_range(array(
			"date" => $date,
			"type" => $type,
			"direction" => $this->conf["event_direction"],
			"event_time_item" => $this->conf["event_time_item"],
			"range_start" => $this->conf["range_start"],
		));

		if ($args["events"] || $this->events_done)
		{
			$events = $args["events"];
		}
		else
		// otherwise just load the plain old event objects
		{
			$folder = isset($this->conf["event_folder"]) ? $this->conf["event_folder"] : $id;
			$events = $this->get_events2(array(
				"start" => $di["start"],
				"end" => $di["end"],
				"id" => $id,
				"folder" => $folder,
				"conf" => $this->conf,
				"type" => $type,
			));


			// recalculate di, it can change if we honor the 
			// only_days_with_events setting
			if (isset($this->start_time))
			{
				$di = get_date_range(array(
					"time" => $this->start_time,
					"type" => $type,
				));
			};

		};
		
		$ddiff1 = $this->get_day_diff($di["start"],$di["end"]);
		
		// tsükkel yle koigi selles perioodis asuvate päevade, et
		// leida ja paigutada events massiivi koik korduvad üritused
		
		list($d,$m,$y) = split("-",$date);


		$this->events = $events;

		$this->ev = get_instance("event");
		// template has to come from calendar config
		$this->ev->actlink = $this->actlink;
		$this->ev->start(array("tpl" => "simple_event.tpl"));


		switch($type)
		{
			case "week":
				$this->type = CAL_SHOW_WEEK;
				$tpl = "disp_week.tpl";

				// sucky sucky 
				// siin on mingi värk sellega, et on 2 erinevat nädalavaadet ..
				// ühe saidi piires .. I need to fix this, yes.
				// 1010 on hollar
				if ($this->cfg["site_id"] == "1010")
				{
					$tpl = "week.tpl";
				};


				$content = $this->disp_week(array("events" => $events,"di" => $di,"tpl" => $tpl));

				// I really hate creating captions this way, that format should be specified
				// in some other way
				$caption = sprintf("%s - %s",$this->time2date($di["start"],8),$this->time2date($di["end"],8));

				$start = $date;
				break;
			
			case "month":
				$this->type = CAL_SHOW_MONTH;
		
				$content = $this->disp_month(array("events" => $events,"di" => $di,"tpl" => "disp_week.tpl"));
				$caption = sprintf("%s",$this->time2date($di["start"],7));
				$start = $date;
				break;

			case "day":
				$this->type = CAL_SHOW_DAY;
				$tpl = is_array($this->conf) ? "disp_day2.tpl" : "disp_day.tpl";
				if (!empty($this->conf["use_template"]))
				{
					$tpl = $this->conf["use_template"];
				};
				$this->read_template($tpl);
				if ($this->is_template("CELL"))
				{
					$content = $this->disp_relative(array(
						"events" => $events,
						"di" => $di,
					));	
					$caption = "";
				}
				else
				{
					for ($i = $di["start"]; $i <= $di["end"]; $i = $i + 86400)
					{
						$tmp_di = $di;
						$tmp_di["start"] = $i;
						$content .= $this->disp_day(array(
							"events" => $events,
							"di" => $tmp_di,
							"tpl" => $tpl,
						));
					}
					$caption = sprintf("%s, %d.%s %d",
						ucfirst(get_lc_weekday($di["start_wd"])),
						date("d",$di["start"]),
						get_lc_month($m),
						date("Y",$di["start"])
					);
				};
				$start = $date;
				break;

			case "relative":
				$content = $this->disp_relative(array(
					"events" => $events,
					"di" => $di,
				));
				// sue me
				$caption = $this->caption;
				break;
		};

		// that is the outer frame
		$navigator = $navi1 = $navi2 = "";

		$this->read_template("planner.tpl");

		if ($this->conf["navigator_visible"])
		{
			if (empty($this->day_orb_link))
			{
				$this->day_orb_link = $this->mk_my_orb("view",array("id" => $id,"type" => "day","ctrl" => $ctrl,"section" => aw_global_get("section")));
			};
			if ($this->conf["navigator_months"] == 3)
			{
				list($_thismon,$_thisyear) = explode("-",date("m-Y",$di["start"]));
				$_prevmon = mktime(0,0,0,$_thismon-1,1,$_thisyear);
				$navi0 = $_cal->draw_calendar(array(
					"tm" => $_prevmon,
					"caption" => get_lc_month((int)date("m",$_prevmon)) . " $y",
					"width" => 7,
					"type" => "month",
					"day_orb_link" => $this->day_orb_link,
					"marked" => $events,
					"caption_url" => $this->mk_my_orb("change",array("id" => $id,"group" => "show_month","ctrl" => $ctrl,"section" => aw_global_get("section"),"date" => date("d-m-Y",$_prevmon))),
				));
			};
			$navi1 = $_cal->draw_calendar(array(
				"tm" => $di["start"],
				"caption" => get_lc_month((int)$m) . " $y",
				"width" => 7,
				"now" => mktime(0,0,0,$m,$d,$y),
				"type" => "month",
				"day_orb_link" => $this->day_orb_link,
				"caption_url" => $this->mk_my_orb("change",array("id" => $id,"group" => "show_month","ctrl" => $ctrl,"section" => aw_global_get("section"),"date" => "1-${_thismon}-${_thisyear}")),
				"marked" => $events,
			));
			if ($this->conf["navigator_months"] >= 2)
			{
				list($_thismon,$_thisyear) = explode("-",date("m-Y",$di["start"]));
				$_nextmon = mktime(0,0,0,$_thismon+1,1,$_thisyear);
				$navi2 = $_cal->draw_calendar(array(
					"tm" => $_nextmon,
					"caption" => get_lc_month((int)date("m",$_nextmon)) . " $y",
					"width" => 7,
					"type" => "month",
					"day_orb_link" => $this->day_orb_link,
					"caption_url" => $this->mk_my_orb("change",array("id" => $id,"group" => "show_month","ctrl" => $ctrl,"section" => aw_global_get("section"),"date" => date("d-m-Y",$_nextmon))),
					"marked" => $events,
				));

			};
			
			$this->vars(array(
				"navi0" => $navi0,
				"navi1" => $navi1,
				"navi2" => $navi2,
			));
			$navigator = $this->parse("navigator");
		};

		$summary_pane = $this->mk_summary_pane($this->conf);

		$this->vars(array(
			"navi1" => "",
			"navi2" => "",
			"summary_line" => "",
			"cell" => "",
			"week" => "",
		));

		$ylist = array(
			"2001" => "2001",
			"2002" => "2002",
			"2003" => "2003",
			"2004" => "2004",
			"2005" => "2005",
			"2006" => "2006",
			"2007" => "2007",
			"2008" => "2008",
			"2009" => "2009",
			"2010" => "2010",
		);
		$mlist = explode("|",LC_MONTH);
		unset($mlist[0]);
		$section = aw_global_get("section");

		if (!empty($this->prevref))
		{
			$prev = $this->prevref;
		}
		else
		{
			$prev = $this->mk_my_orb("view",array(
				"section" => $section,
				"id" => $id,
				"type" => $type,
				"date" => $di["prev"],
				"id" => $id,
				"ctrl" => $ctrl,
			));
		};	

		if (!empty($this->nextref))
		{
			$next = $this->nextref;
		}
		else
		{
			$next = $this->mk_my_orb("view",array(
				"section" => $section,
				"id" => $id,
				"type" => $type,
				"date" => $di["next"],
				"id" => $id,
				"ctrl" => $ctrl,
			));

		};

		$this->vars(array(
			"menudef" => $menudef,
			"caption" => $caption,
			"navigator" => $navigator,
			"summary_pane" => $summary_pane,
			"disp"	=> $disp,
			"month_name" => $mlist[(int)$m],
                        "year_name" => $ylist[$y],
			"id" => $id,
			"content" => $content,
			"mreforb" => $this->mk_reforb("redir",array("day" => $d,"disp" => $disp,"id" => $id,"type" => $type,"ctrl" => $ctrl)),
			"mlist" => $this->picker($m,$mlist),
			"ylist" => $this->picker($y,$ylist),
			"date" => $date,
			"prev" => $prev,
			"next" => $next,
		));

		if ( (strlen($navigator) > 0) || (strlen($summary_pane) > 0))
		{
			$this->vars(array("NAVPANEL" => $this->parse("NAVPANEL")));
		}

		if ($this->is_template("HAS_NEXT"))
		{
			if (!empty($next))
			{
				$this->vars(array(
					"HAS_NEXT" => $this->parse("HAS_NEXT"),
				));
			}
			else
			{
				$this->vars(array(
					"NO_NEXT" => $this->parse("NO_NEXT"),
				));
			};
		}
		
		if ($this->is_template("HAS_PREV"))
		{
			if (!empty($prev))
			{
				$this->vars(array(
					"HAS_PREV" => $this->parse("HAS_PREV"),
				));
			}
			else
			{
				$this->vars(array(
					"NO_PREV" => $this->parse("NO_PREV"),
				));
			};
		}

		if ($this->no_wrapper)
		{
			$retval = $content;
		}
		else
		{
			$retval = $this->parse();
		};

		return $retval;
	}


	////
	// !Draws a single event
	function _draw_event($e = array())
	{
		if ($e["aid"])
		{
			$obj = $this->get_object($e["aid"]);
			$meta = aw_unserialize($e["metadata"]);	
			if ($meta["showtype"] == 0)
			{
				$link = sprintf("onClick='javascript:window.open(\"%s\",\"w%s\",\"toolbar=0,location=0,menubar=0,scrollbars=1,width=400,height=500,resizable=yes\")'","orb.aw?class=objects&action=show&id=$obj[oid]",$obj["oid"]);
				$repl = "<a href='#' $link>$obj[name]</a>";
			}
			else
			{
				$repl = $this->_show_object($obj);
			};
			//$name = sprintf("<br /><img src='%s'>%s",get_icon_url($obj["class_id"],""),$obj["name"]);
			$name = "<br />" .$repl;
		}
		else
		{
			$name = "";
		};


		if ($this->parent_class == CL_CALENDAR)
		{
			$ev_link = $this->mk_my_orb("change_event",array("id" => $e["id"],"date" => $this->date),"planner");
		}
		else
		{
			$ev_link = $this->mk_my_orb("change",array("id" => $e["id"],"date" => $this->date),"cal_event");
		};

		if ($this->actlink)
		{
			$ev_link = $this->mk_my_orb("new",array("parent" => $parent,"date" => $date,"alias_to" => $id,"return_url" => urlencode($this->actlink)),"cal_event");
		};

		$e["title"] = $e["name"];
		$e["description"] = $e["comment"];

		$vars = array(
			"color" => $e["color"],
			"time_start" => date("H:i",$e["start"]), 
			"time" => date("H:i",$e["start"]) . "-" . date("H:i",$e["end"]),
			"event_link" => $ev_link,
			"id" => $e["id"],
			"title" => ($e["title"]) ? $e["title"] : "(nimetu)",
			"object" => $name,
			"contents" => nl2br($e["description"]),
		);
		return $vars;
	}

	function get_events2($args = array())
	{
		$de = get_instance("doc_event");
		if (!empty($this->relobj_id))
		{
			$args["relobj_id"] = $this->relobj_id;
		};

		// first things first, I need to sync the the date ranges given for day 
		// and relative. That really is the tricky part

		$evx = $de->get_events_in_range($args);

		// oh, but this really sucks!
		$this->start_time = $de->start_time;
		$this->prevref = $de->prev_event;
		$this->nextref = $de->next_event;

		return $evx;

		/*
		$q = "SELECT *,planner.oid AS aid FROM planner
			LEFT JOIN objects ON (planner.id = objects.oid)
			WHERE objects.status = 2 AND planner.folder = '$folder'
			AND ( (rep_from >= '$start') OR (rep_from <= '$end') OR (rep_until >= '$start')
			OR (start >= '$start') OR (start <= '$end') )
			ORDER BY rep_from";
		$this->db_query($q);
		*/
		$timebase = mktime(0,0,0,1,1,2001);
		$start_gdn = sprintf("%d",($start - $timebase) / 86400);
		$end_gdn = sprintf("%d",(($end - $timebase) / 86400) + 1);
		$gdn = $start_gdn;
		$range = range($start_gdn,$end_gdn);	
		$results = array();
		// I  need to find all the day number for the time period the client asked
		while($row = $this->db_next())
		{
			$reps = aw_unserialize($row["repeaters"]);
			if (is_array($reps))
			{
				$intersect = array_intersect($reps,$range);
			};
			// always show the event at the day it was added
			$results[date("dmY",$row["start"])][] = $row;
			if (is_array($intersect))
			{
				foreach($intersect as $xgdn)
				{
					$ts = mktime(0,0,0,1,$xgdn,2001);
					if ($ts >= $row["rep_from"])
					{
						$gx = date("dmY",$ts);
						$results[$gx][] = $row;
					};
				};
			};
			$intersect = "";
					
			$gdn++;
		}
		return $results;
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

	function redir($args = array())
	{
		extract($args);
		$max_day = date("d",mktime(0,0,0,$month,$day,$year));
		if ($day > $max_day)
		{
			$day = $max_day;
		};
		$date = "$day-$month-$year";
		$params = array();
		$params["date"] = $date;
		$params["id"] = $id;
		$params["id"] = $id;
		$params["disp"] = $disp;
		$action = "view";
		$retval = $this->mk_my_orb("view",array("type" => $type,"date" => $date,"id" => $id,"ctrl" => $ctrl),"",false,true);
		return $retval;
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


	function disp_relative($args = array())
	{
		$retval = "";
		$this->read_template("show_relative.tpl");
		$empty = $this->parse("CELL");
		$cells = array();

		$_ev_count = 0;

		foreach($args["events"] as $key => $val)
		{
			foreach($val as $vkey => $vval)
			{
				if ($vval["lead"])
				{
					if (isset($vval["ev_link"]))
					{
						$ev_link = $vval["ev_link"];
					}
					else
					{
						$ev_link = "/" . $this->_get_cal_target($vval["meta"]["calendar_relation"]);
					};
					$this->vars(array(
						"lead" => $vval["lead"],
						"date" => date("d.m.Y",$vval["start"]),
						"time" => date("H:i",$vval["start"]),
						"title" => $vval["title"],
						"ev_link" => $ev_link,
					));
					$cells[] = $this->parse("CELL");
					$_ev_count++;
					if ($_ev_count >= $this->conf["event_max_items"])
					{
						break;
					};
				}
			};
			if ($_ev_count >= $this->conf["event_max_items"])
			{
				break;
			};
		}

		$items_on_line = is_numeric($this->conf["items_on_line"]) ? $this->conf["items_on_line"] : 1;
		if (sizeof($cells) < $items_on_line)
		{
			$items_on_line = sizeof($cells);
		};
		if ($items_on_line == 0)
		{
			$items_on_line = 1;
		};
		
		$linecount = ceil(sizeof($cells) / $items_on_line);
		$lines = $line = "";

		for ($i = 0; $i < $linecount; $i++)
		{
			for ($j = 0; $j < $items_on_line; $j++)
			{
				$idx = ($i * $items_on_line) + $j;
				if (isset($cells[$idx]))
				{
					$line .= $cells[$idx];
				}
				else
				{
					$line .= $empty;
				};
			};
			$this->vars(array(
				"CELL" => $line,
			));
			$lines .= $this->parse("LINE");
			$line = "";

		}

		$this->prevlink = $this->nextlink = "";
		return $lines;
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


	// new display functions

	////
	// !Draws a single day inside the calendar
	// dx - day id 
	// it needs an argument which shows how to draw a single event. or day. or whatever
	function _disp_day($args = array())
	{
		// drop out if this day has no events
		if (not(is_array($this->events[$args["dx"]])))
		{
			$this->vars(array(
				"event_content" => "",
			));
			return false;
		};

		$events = $this->events[$args["dx"]];

		// moo. miks neid eventeid varem ära ei sorteerita?
		// sort the events by start date
		uasort($events,array($this,"__asc_sort"));
		$c = "";

		$section = aw_global_get("section");
		$d = get_instance("document");
		$cntr = 0;

		foreach($events as $key => $e)
		{
			$cntr++;
			$pv = "";
			$objlink = "";
			$this->day_event_count++;
			$tgt = $this->_get_cal_target($e["meta"]["calendar_relation"]);
			if (empty($tgt))
			{
				$daylink = $this->mk_my_orb("view",array(
					"section" => $section,
					"id" => $this->id,
					"type" => "day",
					"date" => date("d-m-Y",$e["start"]),
				));
			}
			else
			{
				$daylink = "/" . $tgt;
			};

			if (!$this->is_template("CELL") && (($e["class_id"] == CL_DOCUMENT) || ($e["class_id"] == CL_BROTHER_DOCUMENT)))
			{
				$daylink = "";

				// figure out prev and next events
				if ($tgt)
				{
					$daylink = "/section=" . $tgt . "/date=" . date("d-m-Y",$e["start"]);
				};
				$section = $e["id"];
				if ($this->type == CAL_SHOW_DAY)
				{
					if (substr(aw_global_get("REQUEST_URI"),0,11) != "/automatweb")
					{
						$this->no_wrapper = true;
						$e["docid"] = $e["id"];
						$e["showlead"] = 1;
						$pv = $d->gen_preview(array(
							  "tpl" => "doc_event.tpl",
							  "docid" => $e["id"],
							  "doc" => $e,
							  "vars" => array(
								"edate" => date("d-m-Y",$e["start"]),
								"prevref" => $this->prevref,
								"nextref" => $this->nextref,
							),
						));
					};

				};
			};

			$this->vars(array(
				"event_content" => $pv,
				"lead" => $e["lead"],
				"moreinfo" => $e["moreinfo"],
				"title" => $e["title"],
				"id" => $e["id"],
				"content" => $e["content"],
				"icon" => $e["icon"],
				"daylink" => $daylink,
				"imgurl" => isset($e["imgurl"]) ? $e["imgurl"] : "/img/trans.gif",
			));
			if ($daylink && !empty($e["content"]))
			{
				$this->vars(array(
					"xlink" => $this->parse("xlink"),
					"no_xlink" => "",
				));
			}
			else
			{
				$this->vars(array(
					"no_xlink" => $this->parse("no_xlink"),
					"xlink" => "",
				));
			};

			if (isset($e["realcontent"]))
			{
				$c .= $e["realcontent"];
			}
			else
			{
				if ($this->type == CAL_SHOW_DAY)
				{
					$e["color"] = ($cntr % 2) ? "#FFF" : "#F6F6F6";
				};
				if ($this->is_template("event"))
				{
					$e["time_start"] = date("H:i",$e["start"]);
					$this->vars($e);
					$c .= $this->parse("event");
				}
				else
				{
					$c .= $this->ev->draw($e);
				};
			};
		};

		return $c;
	}
	////
	// !Tegelikult joonistamise primitiivid võiksid asuda kalendri klassi
	// ning ma peaksin saama neile kaasa anda callbacke, et too kalendri-
	// joonistaja siis kutsuks välja mingi funktsiooni minu klassist, mis
	// tagastakse joonistajale eventi html-i

	
	////
	// !Displays the month view
	function disp_month($args = array())
	{ 
		extract($args);
		$title = CAL_WEEK;
		list($d1,$m1,$y1) = split("-",date("d-m-Y",$di["start"]));
		list($d2,$m2,$y2) = split("-",date("d-m-Y",$di["end"]));
		$mon1 = get_lc_month($m1);
		$mon2 = get_lc_month($m2);
		
		$caption = sprintf("%d.%s %d - %d.%s %d",$d1,$mon1,$y1,$d2,$mon2,$y2);

		// load the frame for the month
		$this->read_template($tpl);
		$c = "";
		$head = "";
		$cnt = "";
		$d1 = date("d",$di["start"]);
		
		list($mon,$year) = explode("-",date("m-Y",$di["start"]));	
		$_cal = get_instance("calendar");
		list($start_wday,$end_wday) = $_cal->get_weekdays_for_month($mon,$year);

		// ma pean leidma timestambi selle nädala esmaspäevaks, mil minu kuu algab,
		$day = 86400;
		$week = 7 * $day;

		$realstart = ($di["start"] - ($start_wday - 1) * $day);

		
		// ja siis selle nädala pühapäevaks, mil minu kuu lõpeb
		$realend = ($di["end"] + (7 - $end_wday) * $day);

		// ja siis pean tegema tsükli üle kõigi nende nädalate, mis minu kuu sisse
		// jäävad, ning iga nädala jaoks callima disp_week-i
		$header = true;
		for ($i = $realstart; $i <= $realend; $i = $i + $week)
		{
			$di = array(
				"start" => $i,
				"end" => $i + $week - 1,
			);

			$c .= $this->disp_week(array(
				"di" => $di,
				"tpl" => "disp_week.tpl",
				"header" => $header,
			));

			$header = false;

		};

		return $c;

		// finishing, compile the table

		$this->vars(array(
			"header_cell" => $head,
			"content_cell" => $c,
		));

		$this->vars(array(
			"content_row" => $this->parse("content_row"),
			"header" => $this->parse("header"),
		));
		
		$retval =  $this->parse();
		return $retval;
	}

	////
	// !Displays the week view
	function disp_week($args = array())
	{ 
		extract($args);
		$title = CAL_WEEK;
		$header = isset($header) ? $header : true;
		list($d1,$m1,$y1) = split("-",date("d-m-Y",$di["start"]));
		list($d2,$m2,$y2) = split("-",date("d-m-Y",$di["end"]));
		$mon1 = get_lc_month($m1);
		$mon2 = get_lc_month($m2);
		
		$caption = sprintf("%d.%s %d - %d.%s %d",$d1,$mon1,$y1,$d2,$mon2,$y2);

		$workdays = explode(",",$this->cfg["workdays"]);
		// load the frame for the week
		$this->read_template($tpl);
		$c = "";
		$head = "";
		$cnt = "";
		$d1 = date("d",$di["start"]);
		for ($i = 0; $i <= 6; $i++)
		{
			$thisday = strtotime("+$i days",$di["start"]);
			$dx = date("dmY",$thisday);
			$d = date("d",$thisday);
			$w = date("w",$thisday);

			if ($w == 0)
			{
				$w = 7;
			};
			
			if ($this->conf["workdays"])
			{
				$draw = false;
				if (isset($this->conf["workdays"][$w]))
				{
					$draw = true;
				};
			}
			else
			{
				$draw = true;
			};

			$size = sizeof($this->conf["workdays"]);

			if (($size == 0) || !is_array($this->conf["workdays"]))
			{
				$size = 7;
			};

			$width = (int)(100 / $size);

			if ($draw)
			{
				// draws day
				$this->vars(array(
					"imgurl" => "/img/trans.gif",
				));

				$this->day_event_count = 0;
				$c1 = $this->_disp_day(array("dx" => $dx));
				
				list($day,$mon,$year) = explode("-",date("d-m-Y",$thisday));

				$day_orb_link = ($this->day_orb_link) ? $this->day_orb_link : $this->mk_my_orb("view",array("id" => $this->id,"ctrl" => $this->ctrl, "type" => "day"));
				$day_orb_link .= "&date=$day-$mon-$year";

				// draw header
				$wd_name = get_lc_weekday($w);

				$lcw = substr($wd_name,0,1);
				$_days = array("P","E","T","K","N","R","L");
				$this->vars(array(
					"cellwidth" => $width . "%",
					"hcell" => strtoupper($lcw) . " " . date("d-M",$thisday),
					"weekday_name" => ucfirst($wd_name),
					"month_name" => get_lc_month($mon),
					"hcell_weekday" => $_days[date("w",$thisday)],
					"hcell_weekday_en" => date("D",$thisday),
					"day_message" => in_array($w,$workdays) ? $this->cfg["workday_message"] : $this->cfg["freeday_message"],
					"daynum" => $d,
					"hcell_date" =>  date("d.m.",$thisday),
					"dayorblink" => $day_orb_link,
					"cell" => $c1,
				));

				if ($header)
				{
					$head .= $this->parse("header_cell");
				};

				$tpl = "content_cell";

				if (date("dmY",$thisday) == date("dmY"))
				{
					if ($this->templates["content_cell_today_empty"] && ($this->day_event_count == 0))
					{
						$tpl = "content_cell_today_empty";
					};
					
					if ($this->templates["content_cell_today"] && ($this->day_event_count > 0))
					{
						$tpl = "content_cell_today";
					};
				}
				else
				{
					if ( $this->templates["content_cell_empty"] && ($this->day_event_count == 0))
					{
						$tpl = "content_cell_empty";
					};
				};
				$c .= $this->parse($tpl);

				$this->vars(array(
                                        "lead" => "",
                                        "moreinfo" => "",
                                        "title" => "",
					"link" => "",
					"imgurl" => "/img/trans.gif",
                                ));

				$this->vars(array(
					"event" => $c1,
					"head" => strtoupper(substr(get_lc_weekday($i+1),0,1)),
					"did" => $id,
					"hid" => $args["id"],
					"type" => "week",
					"date" => date("d-m-Y",$thisday),
					"dateinfo" => "$d. " . get_lc_month(date("m",$thisday)),
					"bgcolor" => $bgcolor
				));
			};
		};
		// finishing, compile the table
		$this->vars(array(
			"header_cell" => $head,
			"content_cell" => $c,
		));

		$this->vars(array(
			"content_row" => $this->parse("content_row"),
			"header" => ($header) ? $this->parse("header") : "",
		));
		
		$retval =  $this->parse();
		return $retval;
	}

	////
	// !Displays the day view
	function disp_day($args = array())
	{
		extract($args);
		// load the frame for the day
		$c = "";
		$head = "";
		$cnt = "";
		$d1 = date("d",$di["start"]);
		$i = date("w",$di["start"]);
		if ($i == 0)
		{
			$i = 7;
		};

		$thisday = $di["start"];
		$dx = date("dmY",$thisday);
		$d = date("d",$thisday);
		$dm = date("d-m-Y",$thisday);
		$m = date("m",$thisday);
				
		$dcell = "";

		if (is_array($this->conf) && $this->is_template("dcell"))
		{
			$colors = new aw_array(array("#FFFF00","#FF9900","#FF3300","#99CCFF","#996600",
								"#6600FF","#339999","#336600","#A04CBC","#DCD00C"));
			$events = $this->events[$dx];

			$step = 15*60; // pool tundi
			$ref_matrix = array();
			for ($i = $di["start"]; $i < $di["end"]; $i = $i + $step)
			{
				$ref_matrix[$i] = array();
				$color_arr[$i] = array();
			};


			if (is_array($events))
			{
				uasort($events,array($this,"__desc_sort"));
				$dps = array();

				foreach($events as $key => $val)
				{
					// ma pean arvestama ka lõpuaega ja paljundama eventi igasse
					// slotti, mis asub enne requestitud vahemiku lõppu
					$rstart = $val["start"] - ($val["start"] % $step);

					// let's fool around a bit
					$end = !empty($val["end"]) ? $val["end"] : $val["start"] + 2700;

					$nextcolor = $colors->next();
					if (!$nextcolor)
					{
						$nextcolor = $colors->first();
					};

					$color = $nextcolor["value"];
					$dpos = 0;
					
					for ($i = $rstart; $i <= $end; $i = $i + $step)
					{
						$cont = ($i > $rstart) ? 1 : 0;

						// this check makes us ignore slots outside of the
						// requested time frame
						if (is_array($ref_matrix[$i]))
						{
							array_unshift($ref_matrix[$i],array(
								"oid" => $val["brother_of"],
								"cont" => $cont,
							));
						};

						$dps[$i]++;
					
						if ($dpos <= $dps[$i])
						{
							$dpos = $dps[$i];
						};
						

						$color_arr[$i][] = $val["brother_of"];
					};
				
					$events[$val["brother_of"]]["dpos"] = $dpos;
					$events[$val["brother_of"]]["color"] = $color;

				}
			}
			
			// figure out the slot with most events
			$max_items_per_slot = 0;
			foreach($ref_matrix as $ts => $items)
			{
				if (sizeof($items) > $max_items_per_slot)
				{
					$max_items_per_slot = sizeof($items);
				}
			}

			list($d,$m,$y) = explode("-",$dm);
			$this->ts_daystart = mktime(0,0,0,$m,$d,$y);
			if (is_array($this->conf["day_start"]))
			{
				$this->ts_daystart += ($this->conf["day_start"]["hour"] * 3600) + ($this->conf["day_start"]["minute"] * 60);
			};
			$this->ts_dayend = mktime(0,0,0,$m,$d,$y);
			if (is_array($this->conf["day_end"]) && $this->conf["day_end"]["hour"] != 0)
			{
				$this->ts_dayend += ($this->conf["day_end"]["hour"] * 3600) + ($this->conf["day_end"]["minute"] * 60);
			}
			else
			{
				$this->ts_dayend += 86399;
			};
			$this->vars(array(
				"color" => "#FFFFFF",
			));

			$empty_dcell = $this->parse("duration_cell");
			// create an array of empty cells
			$empty_arr = array();
			for ($i = 1; $i <= $max_items_per_slot; $i++)
			{
				$empty_arr[$i] = $empty_dcell;
			};

			for ($ts = $this->ts_daystart; $ts <= $this->ts_dayend; $ts = $ts + $step)
			{
				$d_event = "";
				$dslots = $empty_arr;

				// overwrite empty cells where needed
				for ($i = $max_items_per_slot; $i > 0; $i--)
				{
					$tv = $color_arr[$ts][$i-1];
					$this->vars(array(
						"color" => $events[$tv]["color"],
					));

					if (isset($events[$tv]["dpos"]))
					{
						$dslots[$events[$tv]["dpos"]] = $this->parse("duration_cell");
					};
				};

				$min = date("i",$ts);
				$dch = ($min == 0) ? date("H",$ts) . ":$min" : "<small>$min</small>";

				$this->vars(array(
					"dcellheader" => $dch,
					"duration_cell" => join("",$dslots),

				));

				$made = 0;

				foreach($ref_matrix[$ts] as $_event)
				{
					$d_event = "";
					if ($_event["cont"] == 0)
					{
						$made++;
						$this->vars($events[$_event["oid"]]);
						$d_event = $this->parse("d_event");

						$this->vars(array(
							"d_event" => $d_event,
						));

						$dcell .= $this->parse("dcell");
					};
				};

				if ($made == 0)
				{
					$this->vars(array(
						"d_event" => "",
					));
					$dcell .= $this->parse("dcell");
				};
			};
		}
		else
		{
			// draws day
			$c1 = $this->_disp_day(array("dx" => $dx));
		};
		
		$i = date("w",$di["start"]);
		if ($i == 0)
		{
			$i = 7;
		};
		$wd_name = get_lc_weekday($i);
		$lcw = substr($wd_name,0,1);

		// draw header
		$this->vars(array(
			"hcell" => strtoupper(substr(get_lc_weekday($i),0,1)) . " " . date("d-M",$thisday),
			"cell" => $c1,
			"weekday_name" => ucfirst($wd_name),
			"daynum" => $d,
			"month_name" => get_lc_month($m),
		));

		$this->vars(array(
			"event" => $c1,
			"head" => strtoupper(substr(get_lc_weekday($i),0,1)),
			"did" => $id,
			"hid" => $args["id"],
			"type" => "week",
			"date" => date("d-m-Y",$thisday),
			"dateinfo" => "$d. " . get_lc_month(date("m",$thisday)),
			"dcell" => $dcell,

		));
		
		$this->vars(array(
			"content" => $this->parse("content"),
			"header" => $this->parse("header"),
		));

		return $this->parse();
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
