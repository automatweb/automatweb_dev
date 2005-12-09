<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/planner.aw,v 1.105 2005/12/09 16:44:37 ahti Exp $
// planner.aw - kalender
// CL_CAL_EVENT on kalendri event
/*

EMIT_MESSAGE(MSG_EVENT_ADD);
EMIT_MESSAGE(MSG_MEETING_DELETE_PARTICIPANTS);

	@default table=objects
	@default group=general2

	@property name type=textbox
	@caption Nimi

	@property status type=status
	@caption Staatus
	
	@default field=meta
	@default method=serialize
	@classinfo relationmgr=yes syslog_type=ST_PLANNER

	@property default_view type=select rel=1
	@caption Aeg

	property event_cfgform type=relpicker reltype=RELTYPE_EVENT_ENTRY
	caption Def. s&uuml;ndmuse sisetamise vorm

	@property day_start type=time_select group=time_settings rel=1
	@caption Päev algab

	@property day_end type=time_select group=time_settings rel=1
	@caption Päev lõpeb

	@property minute_step type=select group=time_settings
	@caption Sündmuse sisestamise minutite täpsus
	
	@property navigator_visible type=checkbox ch_value=1 default=1 group=advanced
	@caption Näita navigaatorit
	
	@property navigator_months type=select group=advanced
	@caption Kuud navigaatoris

	@property my_projects type=checkbox ch_value=1 group=advanced
	@caption Näita projekte
	@comment Kui valitud, siis näidatakse ka sündmusi kõigist projektidest, milles kalendri omanik osaleb.

	@property event_folder type=relpicker reltype=RELTYPE_EVENT_FOLDER
	@caption S&uuml;ndmuste kataloog
	@comment Sellest kataloogist võetakse ja ka salvestatakse siia kalendrisse lisatavaid sündmusi

	@property no_event_folder type=checkbox store=no newonly=1 ch_value=1
	@caption Ära tee sündmuste kataloogi

	@property workdays type=chooser multiple=1 group=advanced
	@caption Tööpäevad
	
	@property tab_views type=chooser multiple=1 group=advanced
	@caption Vaate tabid
	
	@property del_views type=select multiple=1 group=advanced
	@caption Kustutamisvõimalusega vaated
	
	@property event_entry_classes type=chooser multiple=1 orient=vertical group=advanced
	@caption Sündmuste klassid
	@comment Siit valitud klasse saab kasutada kalendrisse sündmuste sisestamiseks

	@property month_week type=checkbox ch_value=1 group=advanced
	@caption Näita kuuvaadet samamoodi nagu nädalavaadet
	
	@property multi_days type=checkbox ch_value=1 group=advanced
	@caption Näita mitmepäevaseid sündmusi ainult esimesel päeval
	
	@property vac_count type=textbox size=2 group=vac_settings default=10
	@caption Vabu aegu

	@property vac_length type=textbox size=2 group=vac_settings default=90
	@caption Vaba aja pikkus (minutid)

	@default store=no

	@property navtoolbar type=toolbar group=views no_caption=1
	@caption Nav. toolbar

	@property project type=hidden group=views
	@caption Projekti ID
	
	@property calendar_contents type=calendar group=views no_caption=1 viewtype=week
	@caption Kalendri sisu

	@property add_event callback=callback_get_add_event group=add_event 
	@caption Lisa s&uuml;ndmus

	@property create_event_table type=table group=create_events no_caption=1
	@caption Loo s&uuml;ndmused

	@property vacancies type=text group=create_vacancies type=table no_caption=1
	@caption Ajad

	@property confirm_vacancies type=checkbox group=create_vacancies store=no no_caption=1 
	@caption Kinnita vabad ajad

	@property vacancies_cal group=create_vacancies_cal type=calendar no_caption=1
	@caption Ajad

	@property vacancies_cal_sbt group=create_vacancies_cal type=submit no_caption=1 value="Kinnita ajad"
	@caption Kinnita ajad

	@default group=views
	@default store=no
	
	@default group=search
	@property event_search_name type=textbox 
	@caption S&uuml;ndmuse nimi
	
	@property event_search_content type=textbox 
	@caption S&uuml;ndmuse sisu
	
	@property event_search_comment type=textbox 
	@caption S&uuml;ndmuse kommentaar
	
	@property event_search_type type=chooser multiple=1 ch_value=1 orient=vertical 
	@caption S&uuml;ndmuse t&uuml;&uuml;p
	
	@property event_search_add type=chooser multiple=1 orient=vertical 
	@caption Lisatingimused
	
	@property event_search_button type=submit 
	@caption Otsi
	
	@property event_search_tb type=toolbar no_caption=1

	@property event_search_results_table type=table store=no no_caption=1 

	@property task_list type=table store=no no_caption=1 group=tasks
	
	@groupinfo general caption=Seaded
	@groupinfo general2 caption=&Uuml;ldine parent=general
	@groupinfo advanced caption=Sisuseaded parent=general
	@groupinfo vac_settings caption="Vabad ajad" parent=general
	@groupinfo views caption=S&uuml;ndmused submit=no submit_method=get
	@groupinfo vacancies caption="Vabad ajad" 
	@groupinfo create_events caption="S&uuml;ndmuste lisamine" parent=vacancies
	@groupinfo create_vacancies caption="Vabad ajad" parent=vacancies
	@groupinfo create_vacancies_cal caption="Vabad ajad (kalendrivaade)" parent=vacancies
	@groupinfo time_settings caption=Ajaseaded parent=general
	@groupinfo add_event caption="Muuda s&uuml;ndmust"
	@groupinfo search caption="Otsing" submit_method=get
	@groupinfo tasks caption="Toimetused" submit=no
*/

// naff, naff. I need to create different views that contain different properties. That's something
// I should have done a long time ago, so that I can create different planners
define("DAY",86400);
define("WEEK",DAY * 7);
define("REP_DAY",1);
define("REP_WEEK",2);
define("REP_MONTH",3);
define("REP_YEAR",4);

/*
@reltype EVENT_SOURCE value=2 clid=CL_PLANNER,CL_PROJECT
@caption võta s&uuml;ndmusi

@reltype EVENT value=3 clid=CL_TASK,CL_CRM_CALL,CL_CRM_MEETING
@caption s&uuml;ndmus

@reltype DC_RELATION value=4 clid=CL_RELATION
@caption viide kalendri väljundile

@reltype GET_DC_RELATION value=5 clid=CL_PLANNER
@caption võta kalendri väljundid

@reltype EVENT_FOLDER value=6 clid=CL_MENU,CL_PLANNER
@caption s&uuml;ndmuste kataloog

@reltype EVENT_ENTRY value=7 clid=CL_CFGFORM,CL_CRM_CALL
@caption s&uuml;ndmuse sisestamise vorm

@reltype CALENDAR_OWNERSHIP value=8 clid=CL_USER
@caption Omanik

@reltype OTHER_CALENDAR value=9 clid=CL_PLANNER
@caption Teised

*/

define("CAL_SHOW_DAY",1);
define("CAL_SHOW_OVERVIEW",2);
define("CAL_SHOW_WEEK",3);
define("CAL_SHOW_MONTH",4);


lc_load("calendar");
// Klassi sees me kujutame koiki kuupäevi kujul dd-mm-YYYY (ehk d-m-Y date format)
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
		$this->vt = array(
			"day" => t("Päev"),
			"week" => t("Nädal"),
			"month" => t("Kuu"),
			"relative" => t("Ülevaade"),
			"search" => t("Otsing"),
		);


		//  list all clids here, that can be added to the calendar (those should at least have a referenfe to planner.start)
		$this->event_entry_classes = array(CL_TASK, CL_CRM_CALL, CL_CRM_OFFER, CL_CRM_MEETING, CL_CALENDAR_VACANCY, CL_CALENDAR_EVENT, CL_PARTY , CL_COMICS, CL_STAGING);
		// list all clids, that should be shown by default
		$this->default_entry_classes = array(CL_TASK, CL_CRM_CALL, CL_CRM_MEETING);

		$this->default_day_start = array(
			"hour" => 9,
			"minute" => 0,
		);

		$this->default_day_end = array(
			"hour" => 17,
			"minute" => 0,
		);
	}
	
	/**

      @attrib name=submit_delete_participants_from_calendar
      @param id required type=int acl=view

	**/
	function submit_delete_participants_from_calendar($arr)
	{
		post_message_with_param(
			MSG_MEETING_DELETE_PARTICIPANTS,
			CL_CRM_MEETING,
			&$arr
		);
		// XXX: miks tyhi?
		return $arr['return_url'];
	}

	function get_event_entry_classes($o)
	{
		$rv = $o->prop("event_entry_classes");
		if (!is_array($rv) || empty($rv))
		{
			$rv = $this->make_keys($this->default_entry_classes);
		};
		return $rv;
	}

	function get_event_classes()
	{
		return $this->event_entry_classes;
	}

	function get_calendar_for_person($p)
	{
		// get user for person
		$conn = $p->connections_to(array(
			"from.class_id" => CL_USER,
			"type" => "RELTYPE_PERSON"
		));
		if (!count($conn))
		{
			return false;
		}
		$c = reset($conn);
		$u = $c->from();

		return $this->get_calendar_for_user(array(
				"uid" => $u->prop("uid")
		));
	}

	function get_calendar_for_user($arr = array())
	{
		$uid = $arr["uid"];
		if (empty($uid))
		{
			$uid = aw_global_get("uid");
		};
		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid($uid));

		$conns = $user->connections_to(array(
			"type" => 8 //RELTYPE_CALENDAR_OWNERSHIP,
		));
		if (sizeof($conns) == 0)
		{
			return false;
		};
		list(,$conn) = each($conns);
		$obj_id = $conn->prop("from");
		return $obj_id;
	}

	function get_calendar_obj_for_user($arr)
	{
		return new object($this->get_calendar_for_user($arr));
	}
	
	/**  
		
		@attrib name=my_calendar params=name is_public="1" caption="Minu kalender" all_args="1"
		
		
		@returns
		
		
		@comment

	**/
	function my_calendar($arr)
	{
		$this->init_class_base();
		$user = new object(aw_global_get("uid_oid"));
		// now I need to figure out the calendar that is connected to the user object
		$conns = $user->connections_to(array(
			"type" => "RELTYPE_CALENDAR_OWNERSHIP",
			"from.class_id" => CL_PLANNER,
		));
		if (sizeof($conns) == 0)
		{
			return sprintf(t("Kasutajal '%s' puudub default kalender"),aw_global_get("uid"));
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
			case "tab_views":
				$data["options"] = array(
					"today" => t("Täna"),
					"day" => t("Päev"),
					"week" => t("Nädal"),
					"month" => t("Kuu"),
					"relative" => t("Ülevaade"),
				);
				break;
				
			case "minute_step":
				$data["options"] = array(
					1 => 1,
					5 => 5,
					10 => 10,
					15 => 15,
					30 => 30,
					60 => 60,
				);
				break;
				
			case "del_views":
				$data["options"] = $this->vt;
				break;
				
			case "default_view":
				$data["options"] = $this->viewtypes;
				break;

			case "navigator_months":
				$data["options"] = array(1 => 1, 2 => 2, 3 => 3 );
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

			case "calendar_contents":
				$this->gen_calendar_contents($arr);
				break;

			case "vacancies":
				$this->gen_vacancies($arr);
				break;

			case "vacancies_cal":
				return $this->gen_vacancies_cal($arr);
				break;

			case "create_event_table":
				$this->create_event_table($arr);
				break;

			case "event_search_name":
			case "event_search_content":
			case "event_search_comment":
				$data["value"] = $arr["request"][$data["name"]];
				break;
			

			case "event_search_type":
				
				$data["options"] = array(
					CL_CRM_OFFER => t("Pakkumine"),
					CL_CRM_CALL => t("Kõne"),
					CL_TASK => t("Toimetus"),
					CL_CRM_MEETING => t("Kohtumine"),
				);
				
				if($arr["request"]["event_search_type"])
				{
					$data["value"] = $arr["request"]["event_search_type"];
				}
				else
				{
					$data["value"] = array(
						CL_CRM_OFFER => 1,
						CL_CRM_CALL => 1,
						CL_TASK => 1,
						CL_CRM_MEETING => 1,
					);
				}
				
				break;
			case "event_search_results_table":
				if(($arr["request"]["event_search_name"] or $arr["request"]["event_search_content"] or $arr["request"]["event_search_type"]))
				{
					$results = $this->do_event_search($arr["request"]);
					if($results->count() > 0)
					{
						$this->do_events_results_table($arr, $results);
					}
					else
					{
						$data["value"] = t("Otsingu tulemusena ei leitud ühtegi objekti");
					}
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
				
			case "event_search_tb":
				if(!$arr["request"]["event_search_name"] && !$arr["request"]["event_search_content"] && !$arr["request"]["event_search_type"])
				{
					return PROP_IGNORE;
				}
				$tab_view = array("search");
				$tb = safe_array($arr["obj_inst"]->prop("del_views"));
				if(count($tb) > 0)
				{
					$tab_view = $tb;
				}
				if(in_array($arr["request"]["group"], $tab_view) && $this->can("edit", $arr["obj_inst"]->id()))
				{
					$data["vcl_inst"]->add_button(array(
						"name" => "delete",
						"tooltip" => t("Kustuta märgitud sündmused"),
						"action" => "delete_events",
						"confirm" => t("Oled kindel, et soovid valitud sündmused kustutada?"),
						"img" => "delete.gif",
						"class" => "menuButton",
					));
				}
				break;
				
			case "search":
				$data["value"] = $arr["request"]["search"];
				break;
				
			case "event_search_add":
				if($arr["request"]["event_search_add"])
				{
					$data["value"] = $arr["request"]["event_search_add"];
				}
				else
				{
					$data["value"] = array(
						"done" => 1,
						"not_done" => 1,
						"all_cal" => 1,
					);
				}
				
				$data["options"] = array(
					"done" => t("Otsi tehtud sündmusi"),
					"not_done" => t("Otsi tegemata sündmusi"),
					"all_cal" => t("Otsi kõigist kalendritest"),
				);
				break;

			case "task_list":
				$this->do_task_list($arr);
				break;

			case "event_entry_classes":
				$clinf = aw_ini_get("classes");
				// if property has no value, then use the defaults
				if (empty($data["value"]))
				{
					$data["value"] = $this->make_keys($this->default_entry_classes);
				};
				foreach($this->event_entry_classes as $clid)
				{
					$data["options"][$clid] = $clinf[$clid]["name"];
				};
				break;
		}
		return $retval;
	}

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "add_event":
				$this->register_event_with_planner($arr);
				break;

			case "calendar_relation":
				// this is where I need to read the type of the output
				// and put it .. somewhere
				break;

			case "vacancies":
			case "vacancies_cal":
				$this->update_vacancies($arr);
				break;

			// by default we create a folder for events as well, since this seems to be
			// the most common scenario, but we let the user skip this step, if she wants
			// too
			case "no_event_folder":
				if (empty($prop["value"]))
				{
					$f_o = new object();
					$o = $arr["obj_inst"];
					$f_o->set_class_id(CL_MENU);
					$f_o->set_parent($o->parent());
					$f_o->set_name($o->name());
					$f_o->set_status(STAT_ACTIVE);
					$f_o->save();

					$o->connect(array(
						"to" => $f_o->id(),
						"reltype" => "RELTYPE_EVENT_FOLDER",
					));
					$o->set_prop("event_folder",$f_o->id());
				};

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
		static $m;
		if (!$m)
		{
			$m = get_instance("applications/calendar/planner_model");
		}
		$ret = $m->get_event_list($arr);
		$this->recur_info = $m->recur_info;
		return $ret;
	}
	
	function get_event_sources($id)
	{
		static $m;
		if (!$m)
		{
			$m = get_instance("applications/calendar/planner_model");
		}
		$ret = $m->get_event_sources($id);
		$this->recur_info = $m->recur_info;
		return $ret;
	}

	// this is called from calendar "properties"
	function _init_event_source($args = array())
	{
		static $m;
		if (!$m)
		{
			$m = get_instance("applications/calendar/planner_model");
		}
		$this->id = $args["id"];
		return $m->_init_event_source($args);
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
			$gmp = $arr["t"]->get_property_group(array(
				"group" => $key,
				"cfgform_id" => $arr["cfgform_id"],
			));

			if (sizeof($gmp) == 0)
			{
				continue;
			};


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

	////
	// !Displays the form for adding a new event
	function callback_get_add_event($arr)
	{
		// yuck, what a mess
		$obj = $arr["obj_inst"];
		$meta = $obj->meta();
		
		$event_folder = $obj->prop("event_folder");
		if (!is_oid($event_folder))
		{
			return array(array(
				"type" => "text",
				"value" => t("Sündmuste kataloog on valimata"),
			));
			return PROP_ERROR;
		};
		
		if (!$this->can("add", $event_folder))
		{
			return array(array(
				"type" => "text",
				"value" => t("Sul pole õigusi sündmuste kataloogi kirjutamiseks"),
			));
			return PROP_ERROR;
		};

		// use the config form specified in the request url OR the default one from the
		// planner configuration
		$event_cfgform = $arr["request"]["cfgform_id"];
		// are we editing an existing event?
		$cf = get_instance(CL_CFGFORM);
		if (empty($event_cfgform))
		{
			$sys_default_form = $cf->get_sysdefault(array("clid" => $arr["request"]["clid"]));
			$event_cfgform = $sys_default_form;
		};
		$event_id = $arr["request"]["event_id"];
		if (is_oid($event_id) && $this->can("view", $event_id))
		{
			$event_obj = new object($event_id);
			if ($event_obj->is_brother())
			{
				$event_obj = $event_obj->get_original();
			};
			//$event_cfgform = $event_obj->meta("cfgform_id");
			$this->event_id = $event_id;
			$clid = $event_obj->class_id();
			$event_i = $event_obj->instance();
			$event_cfgform = $event_i->get_cfgform_for_object(array(
				"obj_inst" => &$event_obj,
				"action" => "change",
			));
			if ($clid == CL_DOCUMENT || $clid == CL_BROTHER_DOCUMENT)
			{
				unset($clid);
			};
		}
		else
		{
			if (!empty($arr["request"]["clid"]))
			{
				$clid = $arr["request"]["clid"];
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
			$tmp = aw_ini_get("classes");
			$entry_classes = $this->get_event_entry_classes($obj);
			if (!in_array($clid,$entry_classes))
			{
				return array(array(
					"type" => "text",
					"value" => sprintf(t("Klassi '%s/%s' ei saa kasutada sündmuste sisestamiseks"),$tmp[$clid]["name"],$tmp[$clid]["def"]),
				));
			}
			else
			{
				// 1 - get an instance of that class, for this I need to 
				aw_session_set('org_action',aw_global_get('REQUEST_URI'));
				$clfile = $tmp[$clid]["file"];
				$t = get_instance($clfile);
				$t->init_class_base();
				$emb_group = "general";
				if ($this->event_id && $arr["request"]["cb_group"])
				{
					$emb_group = $arr["request"]["cb_group"];
				};
				$this->emb_group = $emb_group;
			
				$t->id = $this->event_id;
				
				// the request has to be handled the other way also, in order to use things, that should be used.. -- ahz
				
				$t->request = $arr["request"] + array("group" => $arr["request"]["cb_group"], "class" => $clfile);
				// aga vaata see koht siin peaks arvestama ka seadete vormi, nicht war?

				$all_props = $t->get_property_group(array(
					"group" => $emb_group,
					"cfgform_id" => $event_cfgform,
				));

				$this->do_handle_cae_props($all_props, $arr["request"]);

				$xprops = $t->parse_properties(array(
						"obj_inst" => $event_obj,
						"properties" => $all_props,
						"name_prefix" => "emb",
				));

				//$resprops = array();
				$resprops["capt"] = $this->do_group_headers(array(
					"t" => &$t,
					"cfgform_id" => $event_cfgform,
				));

				foreach($xprops as $key => $val)
				{
					if(($key == "emb_end" || $key == "emb_start1" || $key == "emb_deadline") && $obj->prop("minute_step") > 1)
					{
						$val["minute_step"] = $obj->prop("minute_step");
					}
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
				if ($arr["request"]["cb_group"])
				{
					$resprops[] = array("emb" => 1,"type" => "hidden","name" => "emb[cb_group]","value" => $arr["request"]["cb_group"]);	
				}
			};
		}
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
		if (is_array($_FILES["emb"]))
		{
			foreach($_FILES["emb"] as $ekey => $eval)
			{
				foreach($eval as $ekey1 => $eval1)
				{
					foreach($eval1 as $eval2 => $ekey2)
					{
						$emb[$ekey1][$eval2][$ekey] = $ekey2;
					}
				}
			}
		}
		if($t)
		{
			$this->event_id = $t->submit($emb);
		}
		
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
		$r[1] = str_replace("&", "", $r[1]);
		$o[1] = str_replace("&", "", $o[1]);
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

	function callback_mod_reforb($args = array())
	{
		if (isset($this->event_id))
		{
			$args["event_id"] = $this->event_id;
		};
		//$args['return_url'] = aw_global_get('REQUEST_URI');
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
			$relobj = new object($alias["relobj_id"]);
			$tmp = $relobj->meta("values");
			$overrides = $tmp["CL_PLANNER"];

			//$overrides = $relobj["meta"]["values"]["CL_PLANNER"];
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

		$replacement = $this->change(array(
			"id" => $alias["target"],
			"group" => "views",
			"viewtype" => $_REQUEST["viewtype"],
		));
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
				if ($meta["repeaters".$i]["own_time"])
				{
					$hour = $meta["repeaters".$i]["reptime"]["hour"];
					$minute = $meta["repeaters".$i]["reptime"]["minute"];
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
	// !Embed the repeater editor form inside the planner interface
	function event_repeaters($args = array())
	{
		extract($args);
		$obj = new object($id);
		$par_obj = new object($obj->parent());
		$ce = get_instance(CL_CAL_EVENT);
		$html = $ce->repeaters(array(
			"id" => $id,
			"cycle" => $cycle,
		));
		$this->mk_path($par_obj->parent(),t("Kalender / Muuda sündmust"));
		return $menubar . $html;
	}

	////
	// !Deletes a repeater.
	function delete_repeater($args = array())
	{
		extract($args);
		$o = obj($id);
		$o->set_meta("repeaters" . $cycle, NULL);
		$o->save();
		return $this->mk_my_orb("event_repeaters",array("id" => $id));
	}

	function gen_navtoolbar(&$arr)
	{
		$id = $arr["obj_inst"]->id();
		if ($id)
		{
			$toolbar = &$arr["prop"]["vcl_inst"];
			// would be nice to have a vcl component for doing drop-down menus
			$conns = $arr["obj_inst"]->connections_from(array(
				"type" => "RELTYPE_EVENT_ENTRY",
			));
			$toolbar->add_menu_button(array(
				"name" => "create_event",
				"tooltip" => t("Uus"),
			));
			$conn_count = 0;
			foreach($conns as $conn)
			{
				$conn_count++;
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
			//$clidlist = $this->event_entry_classes;
			$clidlist = $this->get_event_entry_classes($arr["obj_inst"]);
			$tmp = aw_ini_get("classes");
			if ($conn_count == 0)
			{
				foreach($clidlist as $clid)
				{	//Show only if has configform
				if(($clid == CL_CALENDAR_EVENT) && ($arr["obj_inst"]->get_first_conn_by_reltype("RELTYPE_EVENT_ENTRY") == false))
				{
					continue;
				}
				//Dont show at all
				// this is bogus, you can turn it off from the settings
				/*
				if($clid == CL_CRM_OFFER)
				{
					continue;
				}
				*/
				$toolbar->add_menu_item(array(
					"parent" => "create_event",
					"link" => $this->mk_my_orb("change",array(
						"id" => $id,
						"group" => "add_event",
						"clid" => $clid,
					)),
					"text" => $tmp[$clid]["name"],
				));
			};
			};

			$dt = date("d-m-Y",time());

			/*
			$toolbar->add_button(array(
				"name" => "search",
				"tooltip" => t("Otsi kalendri sündmust"),
				"url" => aw_url_change_var(array("search" => 1)),
				"img" => "search.gif",
			));
			*/
			
			$toolbar->add_button(array(
				"name" => "today",
				"tooltip" => t("Täna"),
				"url" => $this->mk_my_orb("change",array("id" => $id,"group" => "views","viewtype" => "day","date" => $dt)) . "#today",
				"img" => "icon_cal_today.gif",
				"class" => "menuButton",
			));

			// XXX: check acl and only show that button, if the user actually _can_
			// edit the calendar
			$prp = safe_array($arr["obj_inst"]->prop("del_views"));
			$view = $_REQUEST["viewtype"] ? $_REQUEST["viewtype"] : $this->viewtypes[$arr["obj_inst"]->prop("default_view")];
			$views = array("week");
			if(count($prp) > 0)
			{
				$views = array();
				foreach($this->viewtypes as $val)
				{
					if(in_array($val, $prp))
					{
						$views[] = $val;
					}
				}
			}
			if($this->can("edit", $arr["obj_inst"]->id()) && in_array($view, $views))
			{
				$toolbar->add_button(array(
					"name" => "delete",
					"tooltip" => t("Kustuta märgitud sündmused"),
					"action" => "delete_events",
					"confirm" => t("Oled kindel, et soovid valitud sündmused kustutada?"),
					"img" => "delete.gif",
					"class" => "menuButton",
				));
			}
			

			if ($arr["obj_inst"]->prop("my_projects") == 1)
			{
				$toolbar->add_separator();

				$prj_opts = array("" => t("--filtreeri projekti järgi--"));
			
				$owners = $arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_CALENDAR_OWNERSHIP",
				));

				// ignore projects, if there are no users connected to this calendar
				// UPDATE: assume, that you would still want to sort by project
				if (sizeof($owners) == 0)
				{
					$conns = $arr["obj_inst"]->connections_from(array(
						"type" => "RELTYPE_EVENT_SOURCE",
						"to.class_id" => CL_PROJECT,
						"sort_by" => "to.name",
					));
					foreach($conns as $conn)
					{
						$prj_opts[$conn->prop("to")] = $conn->prop("to.name");
					}
				}
				else
				{
					$user_ids = array();

					foreach($owners as $owner)
					{
						$user_obj = $owner->to();
						$conns = $user_obj->connections_to(array(
							"from.class_id" => CL_PROJECT,
							"sort_by" => "from.name",
						));

						foreach($conns as $conn)
						{
							$prj_opts[$conn->prop("from")] = $conn->prop("from.name");
						};
					};
				};

				$toolbar->add_cdata("&nbsp;&nbsp;".html::select(array(
					"name" => "prj",
					"options" => $prj_opts,
					"selected" => $arr["request"]["project"],
				)));

				$toolbar->add_button(array(
					"name" => "refresh",
					"tooltip" => t("Reload"),
					"url" => "javascript:document.changeform.project.value=document.changeform.prj.value;document.changeform.submit();",
					"img" => "refresh.gif",
				));
			};

			$toolbar->add_button(array(
				"name" => "only_vac",
				"tooltip" => t("Ainult vabad ajad"),
				"url" => aw_url_change_var("clid_filt", CL_CALENDAR_VACANCY),
				"img" => "qmarks.gif",
				"class" => "menuButton",
			));
		};
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
		$wds = safe_array($arr["obj_inst"]->prop("workdays"));
		$full_weeks = false;
		// if no workdays are defined, use all of them
		for($wd = 1; $wd <= 7; $wd++)
		{
			if(!$wds[$wd])
			{
				$full_weeks = false;
				break;
			}
			else
			{
				$full_weeks = true;
			}
		}

		$o = $arr["obj_inst"];


		$arr["prop"]["vcl_inst"]->configure(array(
			"tasklist_func" => array(&$this,"get_tasklist"),
			"overview_func" => array(&$this,"get_overview"),
			"full_weeks" => $full_weeks,
			"month_week" => $o->prop("month_week"),
			// päeva algus ja päeva lõpp on vaja ette anda!
			"day_start" => $o->prop("day_start"),
			"day_end" => $o->prop("day_end"),
			"filt_views" => $o->prop("tab_views"),
		));
		
		$prp = safe_array($arr["obj_inst"]->prop("del_views"));
		$view = $_REQUEST["viewtype"] ? $_REQUEST["viewtype"] : $this->viewtypes[$arr["obj_inst"]->prop("default_view")];
		$views = array("week");
		if(count($prp) > 0)
		{
			$views = array();
			foreach($this->viewtypes as $val)
			{
				if(in_array($val, $prp))
				{
					$views[] = $val;
				}
			}
		}
		if($this->can("edit", $arr["obj_inst"]->id()) && in_array($view, $views))
		{
			$arr["prop"]["vcl_inst"]->adm_day = 1;
		}
		$viewtype = $this->viewtypes[$arr["obj_inst"]->prop("default_view")];

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"] ? $arr["request"]["viewtype"] : $viewtype,
		));

		global $awt;
		$awt->start("init-event-source");
		$events = $this->_init_event_source(array(
			"id" => $arr["request"]["id"],
			"type" => $range["viewtype"],
			"flatlist" => 1,
			"date" => date("d-m-Y",$range["timestamp"]),
		));
		$awt->stop("init-event-source");
		$multi_e = $arr["obj_inst"]->prop("multi_days");

		foreach($events as $event)
		{
			if (!empty($arr["request"]["clid_filt"]) && $event["class_id"] != $arr["request"]["clid_filt"])
			{
				continue;
			}
			// recurrence information on lihtsalt nimekiri timestampidest, millel sündmus esineb
			if (!$_GET["XX6"])
			{
				$varx = array(
					"item_start" => $event["start"],
					
					"data" => array(
						"id" => $event["id"],
						"name" => $event["name"],
						"icon" => $event["event_icon_url"],
						"link" => $event["link"],
						"comment" => $event["comment"],
						"created" => $event["created"],
						"createdby" => $event["createdby"],
						"modified" => $event["modified"],
						"modifiedby" => $event["modifiedby"],
					),
					"recurrence" => $this->recur_info[$event["id"]],
				);
				if(empty($multi_e))
				{
					$varx["item_end"] = $event["end"];
				}
				$arr["prop"]["vcl_inst"]->add_item($varx);
			}
			else
			{
				$arr["prop"]["vcl_inst"]->add_item(array(
					"timestamp" => $event["start"],
					"data" => array(
						"id" => $event["id"],
						"name" => $event["name"],
						"icon" => $event["event_icon_url"],
						"link" => $event["link"],
						"comment" => $event["comment"],
					),
					"recurrence" => $this->recur_info[$event["id"]],
				));
			};
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
		if (is_array($this->recur_info))
		{
			foreach($this->recur_info as $eid => $tm)
			{
				foreach($tm as $item)
				{
					$rv[$item] = 1;
				};
			};
		};
		return $rv;
	}

	function get_tasklist($arr = array())
	{
		// right, this thing only takes tasks from the calendar's own folder
		// but should take events from all folders
		enter_function("gen-tasklist-1");
		if (aw_global_get("uid") == "duke")
		{
			print "parent = ";
			var_dump($this->calendar_inst->prop("event_folder"));
		};
		$tasklist = new object_list(array(
			"class_id" => CL_TASK,
			"parent" => $this->calendar_inst->prop("event_folder"),
			//"parent" => $this->folder,
			"flags" => array(
				"mask" => OBJ_IS_DONE,
				"flags" => 0,
			),
			"site_id" => array(),
		));
		exit_function("gen-tasklist-1");

		if (0 == $tasklist->count())
		{
			return array();
		};

		enter_function("gen-tasklist-2");
		$tasklist = new object_list(array(
			new object_list_filter(array(
				"logic" => "AND",
				"conditions" => array(
					"brother_of" => $tasklist->brother_ofs(),
				)
			)),
			new object_list_filter(array(
				"logic" => "AND",
				"conditions" => array(
					"brother_of" => new obj_predicate_prop("id"),
				)
			)),
			"flags" => array(
				"mask" => OBJ_IS_DONE,
				"flags" => 0
			),
			"site_id" => array(),

		));
		exit_function("gen-tasklist-2");

		$rv = array();

		enter_function("gen-tasklist-3");
		foreach($tasklist->names() as $task => $name)
		{
			$rv[] = array(
				"name" => $name,
				"url" => $this->get_event_edit_link(array(
					"cal_id" => $this->calendar_inst->id(),
					"event_id" => $task,
				)),
			);
		};
		exit_function("gen-tasklist-3");
		return $rv;
	}

	function _gen_vac_slots($arr)
	{
		$vac_count = $arr["obj_inst"]->prop("vac_count");
		if (!is_numeric($vac_count))
		{
			$vac_count = 10;
		};
		$span_length = $arr["obj_inst"]->prop("vac_length");
		if (!is_numeric($span_length))
		{
			$span_length = 90;
		};
		$span = $span_length * 60; // X minutes for one slot
		$this->span_length = $span;
		$day_start = $arr["obj_inst"]->prop("day_start");
		$day_end = $arr["obj_inst"]->prop("day_end");

		if (!is_array($day_start))
		{
			$day_start = $this->default_day_start;
		};

		if (!is_array($day_end))
		{
			$day_end = $this->default_day_end;
		};

		$real_day_start = ($day_start["hour"] * 3600) + ($day_start["minute"] * 60);
		$real_day_end = ($day_end["hour"] * 3600) + ($day_end["minute"] * 60);

		// now, I will start from the current moment and cycle until the end of the day
		// to figure out which slots are free

		// I do have a number of slots here .. and the length of one slot.
		// now .. at first I am going to assume that there are no events in my calculation,
		// this makes things a bit easier.

		$tstamp = time();
		$today = true;

		$slots = array();

		$wds = safe_array($arr["obj_inst"]->prop("workdays"));
		// if no workdays are defined, use all of them
		if (count($wds) < 1)
		{
			for($wd = 0; $wd < 7; $wd++)
			{
				$wds[$wd] = 1;
			}
		}

		while((isset($arr["until"]) && $tstamp < $arr["until"]) || sizeof($slots) < $vac_count)
		{
			// event list eh?
			if ($wds[date("w", $tstamp)])
			{
				$slots = $slots + $this->_get_free_slots_for_day(array(
					"id" => $arr["obj_inst"]->id(),
					"tstamp" => $tstamp,
					"today" => $today,
				));
			}
			$tstamp += 86400;
			$today = false;
		
			if (++$cnt > 500)
			{
				break;
			}
		};

		return $slots;
	}

	function gen_vacancies($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		// mis väljad peavad olema
		// jrk, algus, lõpp, checkbox kinnita
		$t->define_field(array(
			"name" => "ord",
			"caption" => t("Jrk"),
			"numeric" => 1,
		));	
		$t->define_field(array(
			"name" => "start",
			"caption" => t("Algus"),
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("Lõpp"),
		));
		$t->define_chooser(array(
			"name" => "confirm",
			"field" => "start_tm",
			"caption" => t("Vali"),
		));

		$slots = $this->_gen_vac_slots($arr);

		foreach($slots as $slot)
		{
			$start = $slot["start"];
			$end = $slot["end"];
			$ord++;
			$t->define_data(array(
				"ord" => $ord,
				"start_tm" => $start,
				"start" => date("d-m-Y H:i",$start),
				"end" => date("H:i",$end),
			));
		};
		$t->set_sortable(false);

	}

	function gen_vacancies_cal($arr)
	{
		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"] ? $arr["request"]["viewtype"] : $viewtype,
		));

		$arr["until"] = $range["end"];
		$slots = $this->_gen_vac_slots($arr);

		foreach($slots as $slot)
		{
			$arr["prop"]["vcl_inst"]->add_item(array(
				"timestamp" => $slot["start"],
				"data" => array(
					"name" => "Vaba aeg".html::checkbox(array(
						"name" => "confirm[]",
						"value" => $slot["start"]
					)),
					"link" => "#",
					"comment" => "",
				),
			));
		};

		return PROP_OK;
	}

	function _get_free_slots_for_day($arr)
	{
		// antakse ette timestamp
		// antakse ette kalendri id
		$obj = new object($arr["id"]);
		$day_start = $obj->prop("day_start");
		$day_end = $obj->prop("day_end");

		if (!is_array($day_start))
		{
			$day_start = $this->default_day_start;
		};

		if (!is_array($day_end))
		{
			$day_end = $this->default_day_end;
		};

		$span = $this->span_length;

		if ($day_start["hour"] == 0)
		{
			$day_start["hour"] = 9;
			$day_start["minute"] = 0;
		};

		if ($day_end["hour"] < $day_start["hour"])
		{
			$day_end["hour"] = 17;
			$day_end["minute"] = 0;
		};

		$real_day_start = ($day_start["hour"] * 3600) + ($day_start["minute"] * 60);
		$real_day_end = ($day_end["hour"] * 3600) + ($day_end["minute"] * 60);

		$tstamp = $arr["tstamp"] ? $arr["tstamp"] : time();

		$loc = localtime($tstamp);
		$start = mktime(0,0,0,$loc[4]+1,$loc[3],$loc[5]);
		$real_day_start += $start;
		$real_day_end += $start;
		$ord = 0;


		// now I need events in that range and take them into account to do my calculations

		$existing_events = $this->get_event_list(array(
			"id" => $arr["id"],
			"start" => $real_day_start,
			"end" => $real_day_end,
		));

		// now, I need to divide that information into slots

		$slots = array();
		for ($i = $real_day_start; $i <= ($real_day_end - $span); $i = $i + $span)
		{
			// now cycle over events to see whether that slot can be added
			$available = true;
			$slot_start = $i;
			$slot_end = $i + $span - 1;
			//print "slot start = " . date("H:i",$slot_start) . "<br>";
			//print "slot end = " . date("H:i",$slot_end) . "<br>";
			foreach($existing_events as $event)
			{
				$estart = date("H:i",$event["start"]);
				//print "event start = $estart<br>";
				if (between($event["start"],$slot_start,$slot_end))
				{
					//print "excluding because of start<br>";
					$available = false;
				}
				else
				if (between($event["end"],$slot_start,$slot_end))
				{
					//print "excluding because of end<br>";
					$available = false;
				};
			};

			if ($arr["today"] && $i <= $arr["tstamp"])
			{
				$available = false;
			};

			if ($available)
			{
				$slots[$i] = array(
					"start" => $i,
					"end" => $i + $span - 1,
				);
			};
		};

		return $slots;
	}

	function update_vacancies($arr)
	{
		// now I need to create a bunch of vacancy objects
		$event_folder = $arr["obj_inst"]->prop("event_folder");
		$evo = new object($event_folder);

		// now I now the parent .. I also need start and end dates
		$confirmed = $arr["request"]["confirm"];
		
		$span_length = $arr["obj_inst"]->prop("vac_length");
		if (!is_numeric($span_length))
		{
			$span_length = 90;
		};
		$span = $span_length * 60; // X minutes for one slot

		if (($arr["request"]["confirm_vacancies"] || $arr["request"]["group"] == "create_vacancies_cal") && is_array($confirmed))
		{
			foreach($confirmed as $start_tm)
			{
				$vaco = new object();
				$vaco->set_parent($event_folder);
				$vaco->set_class_id(CL_CALENDAR_VACANCY);
				$vaco->set_status(STAT_ACTIVE);
				$vaco->set_name("Vaba aeg");
				$vaco->set_prop("start",$start_tm);
				$em = $start_tm + ($span) - 1;
				$vaco->set_prop("end",$start_tm + ($span) - 1);
				$vaco->save();
			};
		};

	}

	function create_event_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "start",
			//"type" => "time",
			//"format" => "H:i d-M",
			"caption" => t("Algus"),
		));
		$t->define_field(array(
			"name" => "end",
			//"type" => "time",
			//"format" => "H:i d-M",
			"caption" => t("Lõpp"),
		));
		$t->define_field(array(
			"name" => "meeting",
			"caption" => t("Uus kohtumine"),
		));

		/*
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
			"caption" => t("Vali"),
		));
		*/

		$limit = $arr["obj_inst"]->prop("vac_count");
		if (!is_numeric($limit))
		{
			$limit = 10;
		};

		$vac_list = new object_list(array(
			"parent" => $arr["obj_inst"]->prop("event_folder"),
			"class_id" => CL_CALENDAR_VACANCY,
			"limit" => $limit,
			"sort_by" => "planner.start",
			new object_list_filter(array("non_filter_classes" => CL_CRM_MEETING)),
		));

		foreach($vac_list->arr() as $vac)
		{
			/*
			print "st = ";
			print $vac->prop("start");
			print "<br>";
			*/
			$t->define_data(array(
				"start1" => $vac->prop("start"),
				"start" => date("d-M H:i",$vac->prop("start")),
				"end" => date("d-M H:i",$vac->prop("end")),
				"id" => $vac->id(),
				"meeting" => html::href(array(
					"url" => $this->mk_my_orb("reserve_slot",array(
						"id" => $vac->id(),
						"clid" => CL_CRM_MEETING,
						"cal_id" => $arr["obj_inst"]->id(),
					),CL_CALENDAR_VACANCY),
					"caption" => t("Uus kohtumine"),
				))." | ".html::href(array(
					"url" => $this->mk_my_orb("reserve_slot",array(
						"id" => $vac->id(),
						"clid" => CL_CRM_CALL,
						"cal_id" => $arr["obj_inst"]->id(),
					),CL_CALENDAR_VACANCY),
					"caption" => t("Uus k&otilde;ne"),
				))." | ".html::href(array(
					"url" => $this->mk_my_orb("reserve_slot",array(
						"id" => $vac->id(),
						"clid" => CL_TASK,
						"cal_id" => $arr["obj_inst"]->id(),
					),CL_CALENDAR_VACANCY),
					"caption" => t("Uus toimetus"),
				)),
			));
		};

		// okey, I think the vacancy thingie class needs a method to create

		$t->set_sortable(false);

		/*
		$t->sort_by(array(
			"field" => "start1",
		));
		*/
	}

	function do_events_results_table(&$arr, &$data)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "icon",
		));
			
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Sündmuse nimi"),
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "createdby",
			"caption" => t("Sündmuse looja"),
			"sortable" => "1",
			"align" => "center",
		));

		$table->define_field(array(
			"name" => "participants",
			"caption" => t("Sündmusel osalejad"),
			"sortable" => "1",
			"align" => "center",
		));
			
		$table->define_field(array(
			"name" => "date",
			"caption" => t("Algus"),
			"sortable" => "1",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y - H:m",
			"align" => "center",
			"nowrap" => 1,
		));
		
		$table->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"sortable" => "1",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y - H:m",
			"align" => "center",
			"nowrap" => 1,
		));
		if($this->can("edit", $arr["obj_inst"]->id()))
		{
			$table->define_chooser(array(
				"field" => "id",
				"name" => "sel",
			));
		}
		
		get_instance("core/icons");
		
		$user_inst = get_instance(CL_USER);
		$users_i = get_instance("users");
		foreach ($data->arr() as $result)
		{
		
			$participants = $result->connections_to(array(
				"type" => array(8,9,10),
				"from.class_id" => CL_CRM_PERSON,
			));
			
			$par_href = "";
			$sep = "";
			foreach ($participants as $participant)
			{
				if(!$tmp_not_first)
				{
					$par_href.= $sep.html::href(array(
						"caption" => $participant->prop("from.name"),
						"url" => html::get_change_url($participant->prop("from")),
					));
				}
				$sep = ", ";
			}
			
			$iconurl = icons::get_icon_url($result->class_id());
			if($result->flags() & OBJ_IS_DONE)
			{
				$iconurl = str_replace(".gif", "_done.gif", $iconurl);
			}
			
			$author_user = &obj($users_i->get_oid_for_uid($result->createdby()));
			$author_person_id = $user_inst->get_person_for_user($author_user);
			$author_person_obj = &obj($author_person_id);
			
			$comment = "";
			if($result->comment())
			{
				$comment = " /<i>".$result->comment()."</i>";
			}
			
			if($result->class_id() != CL_CRM_OFFER)
			{
				$href_to_event = html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"group" => "add_event", 
						"event_id" => $result->brother_of(), 
						"id" => $arr["obj_inst"]->id()),
						CL_PLANNER),
					"caption" => $result->name(),
				));
			}
			else
			{
				$href_to_event = html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $result->brother_of(),
					), CL_CRM_OFFER),
					"caption" => $result->name(),
				));
			}
			
			$table->define_data(array(
				"id" => $result->brother_of(),
				"name" => $href_to_event.$comment,
				"date" => $result->prop("start1"),
				"createdby" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $author_person_obj->id()), CL_CRM_PERSON),
					"caption" => $author_person_obj->name(),
				)),
				"modified" => $result->modified(),
				"icon" => html::img(array(
					"url" => $iconurl,
				)),
				"participants" => $par_href,
			));
		}
		$table->set_default_sortby("date");
		$table->set_default_sorder("desc");
	}
	
	/**
		@attrib name=do_event_search
	**/
	function do_event_search($arr)
	{
	
		//IF search from all calenders
		if($arr["event_search_add"]["all_cal"])
		{
			$calender_list = new object_list(array(
				"class_id" => CL_PLANNER,
				"lang_id" => array(),
				"site_id" => array(),
			));
			$list = array();
			foreach ($calender_list->arr() as $cal)
			{
				//echo $cal->prop("event_folder")."X<br>";
				if($cal->prop("event_folder"))
				{
					$parents[] = $cal->prop("event_folder");
				}
			}
		}
		else
		{
			$obj_cal = &obj($arr["id"]);
			$parents[] = $obj_cal->prop("event_folder");
		}
		
		$params = array(
			"name" => "%".$arr["event_search_name"]."%",
			"comment" => "%".$arr["event_search_comment"]."%",
			"content" => "%".$arr["event_search_content"]."%",
			"site_id" => array(),
		);
		
		if (is_array($parents))
		{
			$params["parent"] = $parents;
		}
		
		if($arr["event_search_add"]["done"] && !$arr["event_search_add"]["not_done"])
		{
			$params["is_done"] = OBJ_IS_DONE;
		}
		if($arr["event_search_add"]["not_done"] && !$arr["event_search_add"]["done"])
		{
			$params["is_done"] = new obj_predicate_not(8);
		}
		
		if($arr["event_search_type"])
		{
			$params["class_id"] = $arr["event_search_type"];
		}
		else
		{
			$params["class_id"] = $this->event_entry_classes;
		}
		
		//echo dbg::dump($params);
		/*
		if(aw_global_get("uid") == "svewwn")
		{
			arr($params);
		}*/
		
		/*obj_set_opt("no_cache", 1);
		$GLOBALS["DUKE"] = 1;
		*/
		$event_ol = new object_list($params);

		if($event_ol->count() == 0)
		{
			return $event_ol;
		}

		/*obj_set_opt("no_cache", 0);
		$GLOBALS["DUKE"] = 0;
		*/
		//Now lets get orginal objects ,not brothers
		foreach ($event_ol->arr() as $item)
		{
			$ids[$item->brother_of()] = $item->brother_of();
		}
		
		$retval = new object_list(array(
			"oid" => $ids,
			"site_id" => array(),
		));

		return $retval;
	}

	/**
		@attrib name=search_contacts
	**/
	function search_contacts($arr)
	{
		return $this->mk_my_orb(
			'change',
			array(
				'id' => $arr['id'],
				'group' => $arr['group'],
				'search_contact_firstname' => urlencode($arr["emb"]['search_contact_firstname']),
				'search_contact_lastname' => urlencode($arr["emb"]['search_contact_lastname']),
				'search_contact_code' => urlencode($arr["emb"]['search_contact_code']),
				'search_contact_company' => urlencode($arr["emb"]['search_contact_company']),
				"event_id" => $arr["event_id"],
				"cb_group" => $arr["emb"]["cb_group"]
			),
			$arr['class']
		);
	}

	/** checks if we are searching for event participants and adds a table with search results
	**/
	function do_handle_cae_props(&$props, $request)
	{
		if ($request["cb_group"] == "participants")
		{
			if ($request["search_contact_firstname"] != "" || $request["search_contact_lastname"] || $request["search_contact_code"] || $request["search_contact_company"])
			{
				$props["search_contact_firstname"]["value"] = $request["search_contact_firstname"];
				$props["search_contact_lastname"]["value"] = $request["search_contact_lastname"];
				$props["search_contact_company"]["value"] = $request["search_contact_company"];
				$props["search_contact_code"]["value"] = $request["search_contact_code"];

				$props["search_contact_results"] = array(
					"name" => "search_contact_results",
					"store" => "no",
					"type" => "text",
					"no_caption" => 1,
					"caption" => t("Tulemuste tabel"),
					"value" => $this->do_search_contact_results_tbl($request)
				);
			}
		}
	}

	function _init_search_res_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "phone",
			"caption" => t("Telefon"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "email",
			"caption" => t("E-post"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "position",
			"caption" => t("Ametinimetus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "co",
			"caption" => t("Organisatsioon"),
			"align" => "center"
		));

		$t->define_chooser(array(
			"name" => "sel_search",
			"field" => "oid",
		));
	}

	function do_search_contact_results_tbl($r)
	{
		classload("vcl/table");
		$t = new aw_table();
		$this->_init_search_res_t($t);

		$filter = array("class_id" => CL_CRM_PERSON);
		if ($r["search_contact_firstname"] != "")
		{
			$filter["firstname"] = "%".$r["search_contact_firstname"]."%";
		}
		if ($r["search_contact_lastname"] != "")
		{
			$filter["lastname"] = "%".$r["search_contact_lastname"]."%";
		}
		if ($r["search_contact_company"] != "")
		{
			$t_filt = array();
			foreach(explode(",", $r["search_contact_company"]) as $bit)
			{
				$t_filt[] = "%".$bit."%";
			}
			$filter["CL_CRM_PERSON.work_contact.name"] = $t_filt;
		}

		$ol = new object_list($filter);

		foreach($ol->arr() as $o)
		{
			$org_name = "";
			if (is_oid($o->prop("work_contact")) && $this->can("view", $o->prop("work_contact")))
			{
				$or = obj($o->prop("work_contact"));
				$org_name = $or->name();
			}
			$phone = "";
			if (is_oid($o->prop("phone")) && $this->can("view", $o->prop("phone")))
			{
				$tmp = obj($o->prop("phone"));
				$phone = $tmp->name();
			}
			$email = "";
			if (is_oid($o->prop("email")) && $this->can("view", $o->prop("email")))
			{
				$tmp = obj($o->prop("email"));
				$email = $tmp->prop("mail");
			}
			$rank = "";
			if (is_oid($o->prop("rank")) && $this->can("view", $o->prop("rank")))
			{
				$tmp = obj($o->prop("rank"));
				$rank = $tmp->name();
			}
			$_co = "";
			if (is_oid($o->prop("work_contact")) && $this->can("view",$o->prop("work_contact")))
			{
				$_co = html::get_change_url($o->prop("work_contact"), array(), $org_name);
			}
			$t->define_data(array(
				"name" => html::get_change_url($o->id(), array(), parse_obj_name($o->name())),
				"co" => $_co,
				"phone" => $phone,
				"email" => $email,
				"position" => $rank,
				"oid" => $o->id()
			));
		}

		return $t->draw();
	}

	/**

		@attrib name=save_participant_search_results

	**/
	function save_participant_search_results($arr)
	{
		extract($arr);
		foreach($sel_search as $part)
		{
			if (is_oid($part) && $this->can("view", $part))
			{
				// connect the person to the event.. it seems that's it?
				$o = obj($part);

				$event_obj = obj($arr["event_id"]);
				switch($event_obj->class_id())
				{
					case CL_TASK:
						$rt = "RELTYPE_PERSON_TASK";
						break;
					case CL_CRM_CALL:
						$rt = "RELTYPE_PERSON_CALL";
						break;
					default:
					case CL_CRM_MEETING:
						$rt = "RELTYPE_PERSON_MEETING";
						break;
				}

				if (!$o->is_connected_to(array("to" => $part, "type" => $rt)))
				{
					$o->connect(array(
						"to" => $arr["event_id"],
						"reltype" => $rt,
					));

					if (($cal = $this->get_calendar_for_person($o)))
					{
						$this->add_event_to_calendar(obj($cal), $event_obj);
					}
				}
			}
		}
		return $this->mk_my_orb("change", array(
			'id' => $arr['id'],
			'group' => $arr['group'],
			'search_contact_firstname' => urlencode($arr["emb"]['search_contact_firstname']),
			'search_contact_lastname' => urlencode($arr["emb"]['search_contact_lastname']),
			'search_contact_code' => urlencode($arr["emb"]['search_contact_code']),
			'search_contact_company' => urlencode($arr["emb"]['search_contact_company']),
			"event_id" => $arr["event_id"],
			"cb_group" => $arr["emb"]["cb_group"]
		), $arr["class"]);
	}

	/*
		@attrib name=wtf
	*/
	function wtf()
	{
		$ol = new object_list(array(
			"parent" => 437,
			"class_id" => CL_CRM_MEETING,
		));
		foreach($ol->arr() as $o)
		{
			arr($o->properties());
		};

	}
	
	/**
		@attrib name=delete_events
		@param sel required
		@param id required type=int acl=edit
		@param group optional
	**/
	function delete_events($arr)
	{
		foreach(safe_array($arr["sel"]) as $event)
		{
			if(is_oid($event) && $this->can("delete", $event))
			{
				$objz = obj($event);
				$orig = $objz->brother_of();
				if(is_oid($orig) && $this->can("delete", $orig))
				{
					$orig = obj($orig);
					$orig->delete();
				}
			}
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"], "date" => $arr["date"]));
	}

	/** returns a list of folders for a specified calendar, this is more efficient that returning a list of events
	
	**/
	function get_event_folders($arr)
	{
		static $m;
		if (!isset($m))
		{
			$m = get_instance("applications/calendar/planner_model");
		}
		return $m->get_event_folders($arr);
	}

	function _get_open_tasks($arr)
	{
		$folders = $this->get_event_folders(array(
			"id" => $arr["id"],
			"names" => 1,
		));

		$cal_obj = new object($arr["id"]);

		$tasklist = new object_list(array(
			"class_id" => CL_TASK,
			"parent" => $cal_obj->prop("event_folder"),
			"flags" => array(
				"mask" => OBJ_IS_DONE,
				"flags" => 0,
			),
		));
		if (0 == $tasklist->count())
		{
			return array();
		};

		$tasklist = new object_list(array(
			new object_list_filter(array(
				"logic" => "AND",
				"conditions" => array(
					"brother_of" => $tasklist->brother_ofs(),
				)
			)),
			new object_list_filter(array(
				"logic" => "AND",
				"conditions" => array(
					"brother_of" => new obj_predicate_prop("id"),
				)
			)),
				"flags" => array(
					"mask" => OBJ_IS_DONE,
					"flags" => 0
			)
		));

		$rv = array();

		// we do it in 2 passes
		$parents = array();

		foreach($tasklist->arr() as $task_o)
		{
			$id = $task_o->id();
			$parents[$id] = $task_o->parent();
			$rv[$id] = array(
				"name" => $task_o->name(),
				"start" => $task_o->prop("start1"),
				"modified" => $task_o->modified(),
				"content" => $task_o->prop("content"),
				"url" => $this->get_event_edit_link(array(
					"cal_id" => $arr["id"],
					"event_id" => $id,
				)),
			);
		};

		if (sizeof($parents) > 0)
		{
			// now lets figure out the parents
			$parent_list = new object_list(array(
				"oid" => $parents,
				"site_id" => array(),
			));
			
			$locations = $parent_list->names();
		
			// now find all planners
			$conn = new connection();
			$all_conns = $conn->find(array(
				"from.class_id" => CL_PLANNER,
				"to" => $parent_list->ids(),
			));

			foreach($all_conns as $conn)
			{
				if ($locations[$conn["to"]])
				{
					$locations[$conn["to"]] = $conn["from.name"];
				};
			};


			foreach($parents as $key => $val)
			{
				// how do I know into which calendar this 
				$rv[$key]["location"] = $locations[$val];
			};
		};
		return $rv;
	}

	/** generates task overview list
	**/
	function do_task_list(&$arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		/*
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
		));
		*/
		
		$t->define_field(array(
			"name" => "start",
			"caption" => t("Algus"),
			"type" => "time",
			"format" => "H:i d.m",
			"sortable" => 1,
			"nowrap" => 1,
		));
		
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"type" => "time",
			"format" => "H:i d.m",
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Pealkiri"),
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "content",
			"caption" => t("Sisu"),
		));
	
		$t->define_field(array(
			"name" => "location",
			"caption" => t("Asukoht"),
			"sortable" => 1,
		));

		$t->define_chooser(array(
			"field" => "id",
			"caption" => t("Vali"),
		));
		
		$t->set_default_sortby("start");
		$t->set_default_sorder("asc");

		$open_tasks = $this->_get_open_tasks(array("id" => $arr["obj_inst"]->id()));

		foreach($open_tasks as $task_id => $task_data)
		{
			$t->define_data(array(
				"id" => $task_id,
				"name" => html::href(array(
					"url" => $task_data["url"],
					"caption" => parse_obj_name($task_data["name"]),
				)),
				"start" => $task_data["start"],
				"location" => $task_data["location"],
				"content" => substr($task_data["content"],0,100),
				"modified" => $task_data["modified"],
			));


		};


	}

	/** create and configure calendars for users that do not have one yet
		@attrib name=create_calendars_for_users
	**/
	function create_calendars_for_users($arr)
	{
		// CALENDAR_OWNERSHIP planner->user
		// 1. create list of users
		// 2. check whether they have calendars
		// 3. create calendars for users that do not have one?

		$create_parent = 973;

		print "Creating calendars under $create_parent";

		$user_list = new object_list(array(
			"class_id" => CL_USER,
			"site_id" => array(),
			"brother_of" => new obj_predicate_prop("id")
		));

		$users_need_calendars = array();
		foreach($user_list->arr() as $user_o)
		{
			// dunno .. it seems that there are some test objects lying around that
			// do not have an uid, I just want to ignore them
			$uid = $user_o->prop("uid");
			if (empty($uid))
			{
				continue;
			};
			$user_oid = $user_o->id();
			$users_need_calendars[$user_oid] = 1;
		};
		$c = new connection();
		$existing = $c->find(array(
			"from.class_id" => CL_PLANNER,
			"to.class_id" => CL_USER,
			"type" => 8 // RELTYPE_CALENDAR_OWNERSHIP,
		));
		foreach($existing as $conn)
		{
			unset($users_need_calendars[$conn["to"]]);
		};

		print "the following users need calendars";
		foreach($users_need_calendars as $user_oid => $check)
		{
			$user_o = new object($user_oid);
			$uid = $user_o->prop("uid");

			print "uid = $uid<br>";


			$t = get_instance(CL_PLANNER);
			$new_cal_id = $t->submit(array(
				"parent" => $create_parent,
				"name" => $uid . " kalender",
				"return" => "id",
			));

			$cal_obj = new object($new_cal_id);
			$cal_obj->connect(array(
				"to" => $user_o->id(),
				//"reltype" => "RELTYPE_CALENDAR_OWNERSHIP",
				"reltype" => 8,
			));

		};
		print "all done<br>";

	}

	function add_event_to_calendar($calendar, $event)
	{
		$evf = $calendar->prop("event_folder");
		if (!$this->can("add", $evf))
		{
			return false;
		}
		$bid = $event->create_brother($evf);
	}

	function post_submit_event($event)
	{
		$gl = aw_global_get('org_action',aw_global_get('REQUEST_URI'));
		if ($gl == "")
		{
			return;
		}
		preg_match('/add_to_cal=(\w*|\d*)&/', $gl, $o);
		if ($this->can("view", $o[1]))
		{
			$this->add_event_to_calendar(obj($o[1]), $event);
		}

		preg_match('/alias_to_org=(\w*|\d*)&/', $gl, $o);
		preg_match('/reltype_org=(\w*|\d*)&/', $gl, $r);
		preg_match('/alias_to_org_arr=(.*)$/', $gl, $s);
		$r[1] = str_replace("&", "", $r[1]);
		$o[1] = str_replace("&", "", $o[1]);
		if (is_numeric($o[1]) && is_numeric($r[1]))
		{
			$org_obj = new object($o[1]);
			$org_obj->connect(array(
				"to" => $event->id(),
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
						'to' => $event->id(),
						'reltype' => $r[1],
					));
				}
			}
			post_message_with_param(
				MSG_EVENT_ADD,
				$org_obj->class_id(),
				array(
					"source_id" => $org_obj->id(),
					"event_id" => $event->id(),
				)
			);
		}
	}
};
?>
