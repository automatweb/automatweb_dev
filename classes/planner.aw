<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/planner.aw,v 2.130 2003/08/01 12:48:16 axel Exp $
// planner.aw - kalender
// CL_CAL_EVENT on kalendri event

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general2
	@classinfo relationmgr=yes
	//@classinfo toolbar_type=menubar

	@property default_view type=select rel=1
	@caption Aeg

	@property content_generator type=select rel=1 group=advanced
	@caption Näitamisfunktsioon

	@property event_cfgform type=relpicker reltype=RELTYPE_EVENT_ENTRY
	@caption Def. sündmuse sisetamise vorm

	@property day_start type=time_select group=time_settings rel=1
	@caption Päev algab

	@property day_end type=time_select group=time_settings rel=1
	@caption Päev lõpeb

	@property only_days_with_events type=checkbox ch_value=1 group=advanced
	@caption Näidatakse ainult sündmustega päevi

	@property tab_add_visible type=checkbox ch_value=1 default=1 group=advanced
	@caption "Lisa event" nähtav

	@property tab_day_visible type=checkbox ch_value=1 default=1 group=time_settings
	@caption "Päev" nähtav

	@property tab_week_visible type=checkbox ch_value=1 default=1 group=time_settings
	@caption "Nädal" nähtav

	@property tab_month_visible type=checkbox ch_value=1 default=1 group=advanced
	@caption "Kuu" nähtav

	@property navigator_visible type=checkbox ch_value=1 default=1 group=advanced
	@caption Näita navigaatorit
	
	@property navigator_months type=select group=advanced
	@caption Kuud navigaatoris

	@property use_tabpanel type=checkbox ch_value=1 default=1 group=advanced
	@caption Kalendri näitamisel kasutatakse 'tabpanel' komponenti

	@property items_on_line type=textbox size=4 group=special rel=1
	@caption Max. cell'e reas

	@property use_menubar type=checkbox group=special rel=1 table=objects field=meta method=serialize ch_value=1
	@caption Kasuta menubari

	@property event_direction type=callback callback=cb_get_event_direction group=advanced rel=1
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

	@property workdays callback=callback_get_workday_choices group=advanced
	@caption Näidatavad päevad

	@default store=no

	@property navtoolbar type=toolbar group=show_day,show_week,show_month no_caption=1
	@caption Nav. toolbar

	@property show_day callback=callback_show_day group=show_day 
	@caption Päev
	
	@property show_week callback=callback_show_week group=show_week
	@caption Nädal
	
	@property show_month callback=callback_show_month group=show_month
	@caption Kuu
	
	@property add_event callback=callback_get_add_event group=add_event 
	@caption Lisa sündmus

	@groupinfo general caption=Seaded
	@groupinfo general2 caption=Üldine parent=general
	@groupinfo advanced caption=Sisuseaded parent=general
	@groupinfo views caption=Vaated
	@groupinfo show_day caption=Päev submit=no parent=views
	@groupinfo show_week caption=Nädal submit=no parent=views
	@groupinfo show_month caption=Kuu submit=no default=1 parent=views
	@groupinfo time_settings caption=Ajaseaded parent=general
	@groupinfo special caption=Spetsiaalne parent=general
	@groupinfo add_event caption=Lisa_sündmus
*/

// when and if I need to display an "add event" form inside another config form, I need to do this
// in some weird way. query the properties of the embeddable object and put them where I want them
// to be.

// naff, naff. I need to create different views that contain different properties. That's something
// I should have done a long time ago, so that I can create different planners
define("WEEK",DAY * 7);
define("REP_DAY",1);
define("REP_WEEK",2);
define("REP_MONTH",3);
define("REP_YEAR",4);

define("RELTYPE_SUMMARY_PANE",1);
define("RELTYPE_EVENT_SOURCE",2);
define("RELTYPE_EVENT",3);
define("RELTYPE_DC_RELATION",4);
define("RELTYPE_GET_DC_RELATION",5);
define("RELTYPE_EVENT_FOLDER",6);
define("RELTYPE_EVENT_ENTRY",7);

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
	}

	function callback_get_rel_types()
        {
                return array(
			RELTYPE_SUMMARY_PANE => "näita kokkuvõtte paanis",
			RELTYPE_EVENT_SOURCE => "võta sündmusi teistest kalendritest",
			RELTYPE_EVENT => "sündmus",
			RELTYPE_DC_RELATION => "viide kalendri väljundile",
			RELTYPE_GET_DC_RELATION => "võta kalendri väljundid",
			RELTYPE_EVENT_FOLDER => "sündmuste kataloog",
			RELTYPE_EVENT_ENTRY => "sündmuse sisestamise vorm",	
		);
        }

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
		switch($args["reltype"])
		{
			case RELTYPE_DC_RELATION:
				$retval = array(CL_RELATION);
				break;

			case RELTYPE_EVENT_SOURCE:
			case RELTYPE_GET_DC_RELATION:
				$retval = array(CL_PLANNER);
				break;

			case RELTYPE_EVENT_FOLDER:
				$retval = array(CL_PSEUDO);
				break;

			case RELTYPE_EVENT_ENTRY:
				$retval = array(CL_CFGFORM);
				break;
		};
		return $retval;
        }


	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "default_view":
				$data["options"] = $this->viewtypes;
				break;

			case "navigator_months":
				$data["options"] = array("1" => "1","2" => "2");
				break;

			case "event_folder":
				$alist = $this->get_aliases_for($args["obj"]["oid"],-1,"","","",RELTYPE_EVENT_FOLDER);
				// this used to be a menu list, and the following code should
				// create a relation for the menu
				if ( (sizeof($alist) == 0) && !empty($data["value"]))
				{
					$this->addalias(array(
						"id" => $args["obj"]["oid"],
						"alias" => $data["value"],
						"reltype" => RELTYPE_EVENT_FOLDER,
					));
				}
				break;

			case "event_cfgform":
				$alist = $this->get_aliases_for($args["obj"]["oid"],-1,"","","",RELTYPE_EVENT_ENTRY);

				if ( (sizeof($alist) == 0) && !empty($data["value"]))
				{
					$this->addalias(array(
						"id" => $args["obj"]["oid"],
						"alias" => $data["value"],
						"reltype" => RELTYPE_EVENT_ENTRY,
					));
				}
				break;

			case "content_generator":
				$orb = get_instance("orb");
				$tmp = array("0" => "näita kalendri sisu") + $orb->get_classes_by_interface(array("interface" => "content"));
				$data["options"] = $tmp;
				break;

			case "navtoolbar":
				$this->gen_navtoolbar($args);
				break;

			case "use_template":
				$data["options"] = array(
					"" => "",
					"show_relative.tpl" => "Piltide üldvaade",
					"disp_day2.tpl" => "Päev",
				);
				break;

		}
		return $retval;
	}

	function set_property($args = array())
	{
                $data = &$args["prop"];
                $retval = PROP_OK;
                switch($data["name"])
                {
			case "event_cfgform":
				// try and check the config form
				$frm = $this->get_object($data["value"]);
				if (!$frm["meta"]["cfg_proplist"])
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "add_event":
				$this->create_planner_event($args);
				break;

			case "calendar_relation":
				// this is where I need to read the type of the output
				// and put it .. somewhere
				break;

		}
		return $retval;
	}

	function callback_get_workday_choices($args = array())
	{
		$tmp = $args["prop"];
		$daynames = explode("|",LC_WEEKDAY);
		$wd = isset($args["obj"]["meta"]["workdays"]) ? $args["obj"]["meta"]["workdays"] : array();
		for($i = 1; $i <= 7; $i++)
		{
			$tmp["items"][] = array(
				"type" => "checkbox",
				"name" => "workdays[$i]",
				"label" => $daynames[$i],
				"ch_value" => 1,
				"value" => isset($wd[$i]) ? $wd[$i] : 0,
			);
		};
		$retval = array("workdays" => $tmp);
		return $retval;
	}

	// this is called from calendar "properties"
	function _init_event_source($args = array())
	{
		extract($args);
		classload("date_calc");
		$di = get_date_range(array(
			"date" => isset($date) ? $date : date("d-m-Y"),
			"type" => $type,
		));

		$obj = $this->get_object($id);
		$this->id = $id;
		$this->content_gen_class = "";
		if (!empty($obj["meta"]["content_generator"]))
		{
			list($pf,$pm) = explode("/",$obj["meta"]["content_generator"]);
			$this->content_gen_class = $pf;
			$this->content_gen_method = $pm;
		};

		$folder = (int)$obj["meta"]["event_folder"];
		
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

		$q = "SELECT metadata FROM aliases LEFT JOIN objects ON (aliases.target = objects.oid) WHERE source = '$id' AND reltype = " . RELTYPE_EVENT_SOURCE;
		$this->db_query($q);
		$folders = array($folder);
		while($row = $this->db_next())
		{
			$mx = aw_unserialize($row["metadata"]);
			if (!empty($mx["event_folder"]))
			{
				$folders[] = $mx["event_folder"];
			};
		};

		$q = sprintf("SELECT * FROM planner LEFT JOIN objects ON (planner.id = objects.brother_of) WHERE objects.parent IN (%s) AND objects.status != 0",join(",",$folders));
		$this->db_query($q);
		$events = array();
		// we sure pass around a LOT of data
		while($row = $this->db_next())
		{
			$gx = date("dmY",$row["start"]);
			$row["meta"] = aw_unserialize($row["metadata"]);
			unset($row["metadata"]);
			if ($this->content_gen_class)
			{
				$this->save_handle();
				$row["realcontent"] = $this->do_orb_method_call(array(
						"class" => $this->content_gen_class,
						"action" => $this->content_gen_method,
						"params" => array(
							"id" => $row["oid"],
						),
					));
				$this->restore_handle();
			}
			$row["link"] = $this->mk_my_orb("change",array(
				"id" => $id,
				"group" => "add_event",
				"event_id" => $row["oid"],
			));
			if ($row["class_id"] == CL_BROTHER_DOCUMENT)
			{
				$this->save_handle();
				$real_obj = $this->get_object($row["brother_of"]);
				$row["name"] = $real_obj["name"];
				$this->restore_handle();
			};
			$events[$gx][$row["brother_of"]] = $row;
		};
		$this->day_orb_link = $this->mk_my_orb("change",array("id" => $id,"group" => "show_day"));
		$this->week_orb_link = $this->mk_my_orb("change",array("id" => $id,"group" => "show_week"));
		return $events;
	}

	////
	// !Day view
	// Basically, how they should work, is that I want to define a datasource
	// for events (and also provide different callbacks for setting up the configuration)
	// and then let those functions display thos events based on the datasource configuration
	function callback_show_day($args = array())
	{
		$nodes = array();
		$events = $this->_init_event_source(array(
			"id" => $args["request"]["id"],
			"type" => "day",
			"date" => $args["request"]["date"],
		));
		$nodes[] = array(
			"no_caption" => 1,
			"value" => $this->view(array(
				"type" => "day",
				"id" => $args["request"]["id"],
				"date" => $args["request"]["date"],
				"no_tabs" => 1,
				"events" => $events,
			)),
		);
		return $nodes;
	}

	////
	// !Week view	
	function callback_show_week($args = array())
	{
		$nodes = array();
		$events = $this->_init_event_source(array(
			"id" => $args["request"]["id"],
			"type" => "week",
			"date" => $args["request"]["date"],
		));
		$nodes[] = array(
			"no_caption" => 1,
			"value" => $this->view(array(
				"type" => "week",
				"id" => $args["request"]["id"],
				"date" => $args["request"]["date"],
				"no_tabs" => 1,
				"events" => $events,
			)),
		);
		return $nodes;
	} 

	////
	// !Month view	
	function callback_show_month($args = array())
	{
		$nodes = array();
		$events = $this->_init_event_source(array(
			"id" => $args["request"]["id"],
			"type" => "month",
			"date" => $args["request"]["date"],
		));
		$nodes[] = array(
			"no_caption" => 1,
			"value" => $this->view(array(
				"type" => "month",
				"id" => $args["request"]["id"],
				"date" => $args["request"]["date"],
				"no_tabs" => 1,
				"events" => $events,
			)),
		);
		return $nodes;
	}

	////
	// !Displays the form for adding a new event
	function callback_get_add_event($args = array())
	{
		// yuck, what a mess
		$obj = $this->get_object($args["request"]["id"]);
		$meta = $obj["meta"];
		$event_cfgform = empty($args["request"]["cfgform_id"]) ? $meta["event_cfgform"] : $args["request"]["cfgform_id"];
		$event_folder = $meta["event_folder"];

		$event_id = $args["request"]["event_id"];
		$this->event_id = $event_id;

		$res_props = array();

		if ($event_cfgform)
		{
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

			$t->role = "obj_edit";
			
			$obj_to_load = $this->event_id;

			if ($ev_data["class_id"] == CL_BROTHER_DOCUMENT)
			{
				$obj_to_load = $ev_data["brother_of"];
			};

			$t->id = $obj_to_load;

			$all_props = $t->get_active_properties(array(
				"group" => $emb_group,
			));

			if ($this->event_id)
			{
				$t->load_obj_data(array("id" => $obj_to_load));
				//$t->id = $obj_to_load;
			};

			// sorry ass attempt to get editing of linked documents to work
			$xprops = array();
			$xtmp = $t->groupinfo;
			$tmp = array(
				"type" => "text",
				"caption" => "header",
				"subtitle" => 1,
			);	
			$captions = array();
			foreach($xtmp as $key => $val)
			{
				if ($this->event_id && ($key != $emb_group))
				{
					$url = aw_global_get("QUERY_STRING");
					if (strpos($url,"cb_group"))
					{
						$url = preg_replace("/&cb_group=\w*/","",$url);
					};
					$url .= "&cb_group=$key";
					$url = "orb.aw?" . $url;
					$captions[] = html::href(array(
							"url" => $url,
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
			$t->inst->set_calendars(array($obj["oid"]));
			$xprops = $t->parse_properties(array(
					"properties" => $all_props,
			));
			$resprops = array();
			// bad, I need a way to detect the default group. 
			// but for now this has to do.
			$resprops["capt"] = $tmp;
			foreach($xprops as $key => $val)
			{
				// a põmst, kui nimes on [ sees, siis peab lahutama
				$bracket = strpos($val["name"],"[");
				if ($bracket > 0)
				{
					$pre = substr($val["name"],0,$bracket);
					$aft = substr($val["name"],$bracket);
					$newname = "emb[$pre]" . $aft;
				
				}
				else
				{
					$newname = "emb[" . $val["name"] . "]";
				};	
				$xprops[$key]["name"] = $newname;
				$resprops["emb_$key"] = $xprops[$key];
			};	
			$resprops[] = array(
				"type" => "hidden",
				"name" => "emb[class]",
				"value" => "doc",
			);
			$resprops[] = array(
				"type" => "hidden",
				"name" => "emb[action]",
				"value" => "submit",
			);
			$resprops[] = array(
				"type" => "hidden",
				"name" => "emb[group]",
				"value" => $emb_group,
			);
			if ($obj_to_load)
			{
				$resprops[] = array(
					"type" => "hidden",
					"name" => "emb[id]",
					"value" => $obj_to_load,
				);	
			}
			else
			{
				$resprops[] = array(
					"type" => "hidden",
					"name" => "emb[cfgform]",
					"value" => $event_cfgform,
				);	
			};

		}
		else
		{
			$resprops[] = array(
				"type" => "text",
				"value" => "Sündmusi ei saa lisada enne, kui oled valinud eventite sisestamise vormi",
			);
		};

		// but, before I can add a new event, I need to know where to put those new objects
		return $resprops;
	}

	function create_planner_event($args = array())
	{
		$obj = $this->get_object($args["obj"]["oid"]);
		$event_cfgform = $obj["meta"]["event_cfgform"];
		$event_folder = $obj["meta"]["event_folder"];
		if (empty($event_folder))
		{
			return PROP_ERROR;
		};
		$frm = $this->get_object($event_cfgform);
		classload("doc");
		$t = new doc();
		$emb = $args["form_data"]["emb"];
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
		$this->event_id = $t->submit($emb);
		return PROP_OK;
	}

	function callback_mod_reforb($args = array())
	{
		if (isset($this->event_id))
		{
			$args["event_id"] = $this->event_id;
		};
	}

       function callback_mod_retval($args = array())
       {
                if ($this->event_id)
                {
                        $form_data = &$args["form_data"];
                        $args = &$args["args"];
			$args["event_id"] = $this->event_id;
			if ($this->emb_group)
			{
				$args["cb_group"] = $this->emb_group;
			};
                };
        }

	function callback_mod_tab($args = array())
	{
		if ($args["id"] == "add_event")
		{
			$args["caption"] = isset($this->event_id) ? "Muuda sündmust" : "Lisa sündmus";
		};
	}

	////
	// !Parsib kalendrialiast
	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->calaliases) || ($oid != $this->cal_oid) )
		{
			$this->calaliases = $this->get_aliases(array(
				"oid" => $oid,
				"type" => CL_PLANNER,
			));
			$this->cal_oid = $oid;
		};

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
		
		$use_tabpanel = false;

		if (!empty($this->conf["use_tabpanel"]))
		{
			$use_tabpanel = true;
		};
		
		$today = date("d-m-Y");

		if ($use_tabpanel)
		{
			// generate a menu bar
			// tabpanel really should be in the htmlclient too
			$this->tp = get_instance("vcl/tabpanel");			
		
			if ($this->conf["tab_day_visible"] == 1)
			{	
				$this->tp->add_tab(array(
					"link" => $this->mk_my_orb("view",array("id" => $id,"date" => $today,"ctrl" => $ctrl,"type" => "day")),
					"caption" => "Täna",
					"active" => ($act == $today),
				));

				$this->tp->add_tab(array(
					"link" => $this->mk_my_orb("view",array("id" => $id,"date" => $date,"ctrl" => $ctrl,"type" => "day")),
					"caption" => "Päev",
					"active" => ($act == "day"),
				));
			};

			if ($this->conf["tab_week_visible"] == 1)
			{
				$this->tp->add_tab(array(
					"link" => $this->mk_my_orb("view",array("id" => $id,"date" => $date,"ctrl" => $ctrl,"type" => "week")),
					"caption" => "Nädal",
					"active" => ($act == "week"),
				));
			};
		
			if ($this->conf["tab_month_visible"] == 1)
			{	
				$this->tp->add_tab(array(
					"link" => $this->mk_my_orb("view",array("id" => $id,"date" => $date,"ctrl" => $ctrl,"type" => "month")),
					"caption" => "Kuu",
					"active" => ($act == "month"),
				));
			};

			if ($this->conf["tab_add_visible"] == 1)
			{
				$this->tp->add_tab(array(
					"link" => $this->mk_my_orb("new",array("parent" => $parent,"date" => $date,"alias_to" => $id,"return_url" => urlencode($actlink)),"cal_event"),
					"caption" => "Lisa sündmus",
					"active" => 0,
				));
			};
		};

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

		/// XXX: check whether that object has OBJ_HAS_CALENDAR flag
		if ($object["class_id"] == CL_FORM_CHAIN)
		{
			$fch = get_instance("formgen/form_chain");
			$fch->load_chain($object["oid"]);

			$vac_cont = (int)$fch->chain["cal_controller"];

			$q = "SELECT ev_table FROM calendar2forms WHERE cal_id = '$object[oid]'";
			$this->db_query($q);
			$row = $this->db_next();
			$ev_table = $row["ev_table"];


		}
		elseif ($object["class_id"] == CL_FORM)
		{
			$vac_cont = $object["oid"];
			$q = "SELECT ev_table FROM calendar2forms WHERE cal_id = '$object[oid]'";
			$this->db_query($q);
			$row = $this->db_next();
			$ev_table = $row["ev_table"];

		}
		else
		if ($args["events"])
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

		if ($ev_table)
		{
			// retrieve all entries that belong to this calendar.
			$fc = get_instance("formgen/form_calendar");
			$events = $fc->get_events(array(
				"eid" => $object["oid"],
				"start" => $di["start"],
				"end" => $di["end"],
				"eform" => $vac_cont,
				"ctrl" => $ctrl,
			));

			$this->raw_events = $fc->raw_events;
			$this->raw_headers = $fc->raw_headers;
			$this->cached_chain_ids = array();
			$this->ft = get_instance("formgen/form_table");
			$this->table_id = $ev_table;
			// event_display_table can be empty
			if ($this->table_id)
			{
				$this->ft->load_table($this->table_id);
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
			$navi1 = $_cal->draw_calendar(array(
				"tm" => $di["start"],
				"caption" => get_lc_month((int)$m) . " $y",
				"width" => 7,
				"now" => mktime(0,0,0,$m,$d,$y),
				"type" => "month",
				"day_orb_link" => $this->day_orb_link,
				"marked" => $events,
			));
			if ($this->conf["navigator_months"] == 2)
			{
				list($_thismon,$_thisyear) = explode("-",date("m-Y",$di["start"]));
				$_nextmon = mktime(0,0,0,$_thismon+1,1,$_thisyear);
				$_nm = date("m",$_nextmon);
				$navi2 = $_cal->draw_calendar(array(
					"tm" => $_nextmon,
					"caption" => get_lc_month((int)$_nm) . " $y",
					"width" => 7,
					"type" => "month",
					"day_orb_link" => $this->day_orb_link,
					"marked" => $events,
				));

			};
			
			$this->vars(array(
				"navi1" => $navi1,
				"navi2" => $navi2,
			));
			$navigator = $this->parse("navigator");
		};

		$summary_pane = $this->mk_summary_pane($this->conf);

		$this->vars(array(
			"navi1" => "",
			"navi2" => "",
			"summary_header" => $summary_pane,
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

		if (isset($this->prevref))
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

		if (isset($this->nextref))
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
		if ($use_tabpanel)
		{
			$vars["content"] = $this->parse();
                	$retval = $this->tp->get_tabpanel($vars);
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
		if ($this->table_id)
		{
			$c = $this->raw_headers[$args["dx"]];
			if (not(is_array($this->raw_events[$args["dx"]])))
			{
				return $c;
			}

			$this->ft->start_table($this->table_id);
			foreach($this->raw_events[$args["dx"]] as $row)
			{
				$__ch = $this->ft->get_chains_for_form($row["form_id"]);
				list($_ch,) = each($__ch);

				$mx = $this->get_object((int)$row[form_id]);

				$cx = $this->get_object((int)$_ch);

				//if ($mx["meta"]["calendar_chain"])
				if ($cx["flags"] & OBJ_HAS_CALENDAR)
				{
					$cctrl = $row["chain_id"];
				}
				else
				{
					$cctrl = $this->ctrl;
				};
				$this->ft->row_data($row,$row["form_id"],$section,0,$_ch,$cctrl);
			}
			$c .= $this->ft->finalize_table();

		}
		else
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

	function mk_summary_pane($args = array())
	{
		$summary_objects = array();
		$alias_reltype = new aw_array($args["alias_reltype"]);
		foreach($alias_reltype->get() as $key => $val)
		{
			if ($val == 1)
			{
				$summary_objects[] = $key;
			};
		};
		// now cycle over all the summary_objects and generate the previews
		$summary = "";
		$sc = get_instance("search");
		if (sizeof($summary_objects) > 0)
		{
			// right now I only support searches
			$q = sprintf("SELECT oid,name FROM objects WHERE class_id = %d AND status = 2 AND oid IN (%s)",CL_SEARCH,join(",",$summary_objects));
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$this->vars(array(
					"caption" => $row["name"],
					"url" => $this->mk_my_orb("view",array("id" => $row["oid"]),"search"),
				));
				$summary .= $this->parse("summary_header");
				// I need to execute search for each search object
				$results = new aw_array($sc->get_search_results(array("id" => $row["oid"])));
				foreach($results->get() as $obj)
				{
					$use_class = $this->cfg["classes"][$obj["class_id"]]["file"];
					$this->vars(array(
						"caption" => ($obj["name"]) ? $obj["name"] : "(nimetu)",
						"desc" => $obj["comment"],
						"url" => $this->mk_my_orb("view",array("id" => $obj["oid"]),$use_class),
					));
					$summary .= $this->parse("summary_line");
				};


			};
		


		};
		return $summary;


	}

	function cb_get_event_direction($args = array())
	{
		$items = array();
		$items[] = array(
			"type" => "radiobutton",
			"caption" => "Tagasi",
			"name" => $args["prop"]["name"],
			"rb_value" => 1,
			"value" => $args["prop"]["value"],
		);
		$items[] = array(
			"type" => "radiobutton",
			"caption" => "Praegu &nbsp;",
			"rb_value" => 0,
			"name" => $args["prop"]["name"],
			"value" => $args["prop"]["value"],
		);
		$items[] = array(
			"type" => "radiobutton",
			"caption" => "Edasi &nbsp;",
			"rb_value" => 2,
			"name" => $args["prop"]["name"],
			"value" => $args["prop"]["value"],
		);
		$retval = array(
			"type" => "text",
			"caption" => $args["prop"]["caption"],
			"items" => $items,
		);
		return array($retval);
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
		$id = $arr["obj"]["oid"];
                if ($id)
                {
			$toolbar = &$arr["prop"]["toolbar"];

			$this->read_template("js_popup_menu.tpl");
			$menudata = "";
			$alist = $this->get_aliases_for($id,-1,"","","",RELTYPE_EVENT_ENTRY);
			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					$this->vars(array(
						"link" => $this->mk_my_orb("change",array("id" => $id,"group" => "add_event","cfgform_id" => $val["oid"])),
						"text" => $val["name"],
					));

					$menudata .= $this->parse("MENU_ITEM");
				};
			};

			$this->vars(array(
				"MENU_ITEM" => $menudata,
				"id" => "create_event",
			));

			$menu = $this->parse();

                	$toolbar->add_cdata($menu);
	
			$toolbar->add_button(array(
                                "name" => "add",
                                "tooltip" => "Uus",
				"url" => "",
				"onClick" => "return buttonClick(event, 'create_event');",
                                "img" => "new.gif",
                                "imgover" => "new_over.gif",
                                "class" => "menuButton",
                        ));

			$dt = date("d-m-Y",time());

			$toolbar->add_button(array(
				"name" => "today",
				"tooltip" => "Täna",
				"url" => $this->mk_my_orb("change",array("id" => $id,"group" => "show_day","date" => $dt)),
				"img" => "icon_cal_today.gif",
				"imgover" => "icon_cal_today_over.gif",
				"class" => "menuButton",
			));
			
			$toolbar->add_button(array(
				"name" => "delete",
				"tooltip" => "Kustuta märgitud sündmused",
				"url" => "javascript:document.changeform.action.value='delete_events';document.changeform.submit();",
				"img" => "delete.gif",
				"imgover" => "delete_over.gif",
				"class" => "menuButton",
			));
			
                };
	}

	function delete_events($args = array())
	{
		extract($args);
		if (sizeof($mark) > 0)
		{
			foreach($mark as $event)
			{
				$this->delete_object($event);
			}
		};
		return $this->mk_my_orb("change",array("id" => $args["id"],"group" => $args["subgroup"],"date" => $args["date"]));
	}



};
?>
