<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/community.aw,v 1.5 2004/09/03 15:50:12 ahti Exp $
// community.aw - Kogukond 
/*

@classinfo syslog_type=ST_COMMUNITY relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

-------------------------------------

@groupinfo forum caption="Foorum" submit=no

@property forum type=text group=forum store=no no_caption=1
@caption Foorum


@groupinfo calendar caption="Kalender"

@groupinfo calendar_view caption="Sisuvaade" parent=calendar submit=no

@property calendar_view_toolbar type=toolbar group=calendar_view no_caption=1
@caption Kalendri toolbar

@property calendar_view type=calendar group=calendar_view store=no no_caption=1
@caption Kalender

@groupinfo add_event caption="Lisa sündmus" parent=calendar

@property add_event type=callback callback=callback_add_event group=add_event no_caption=1
@caption Lisa sündmus

@groupinfo settings caption="Seaded"

@groupinfo forum_settings caption="Foorumi seaded" parent=settings

@property forum_settings type=callback callback=callback_forum_settings group=forum_settings
@caption Foorumi seaded

@groupinfo calendar_settings caption="Kalendri seaded" parent=settings

@property calendar_settings type=callback callback=callback_calendar_settings group=calendar_settings store=no
@caption Kalendri seaded

@property categories type=relpicker reltype=RELTYPE_CATEGORIES group=settings



@groupinfo moderators caption="Moderaatorid" submit=no

@property moderators_toolbar type=toolbar group=moderators no_caption=1
@caption Moderaatorite toolbar

@property moderators type=table group=moderators
@caption Moderaatorid

//@property forum_settings type=text group=forum_settings store=no
//@property forum2 type=relpicker reltype=RELTYPE_FORUM group=settings
//@property calendar_settings type=text group=calendar_settings store=no
//@property calendar2 type=relpicker reltype=RELTYPE_CALENDAR group=settings

-------------------------------------
@reltype MODERATOR value=3 clid=CL_USER
@caption moderaator

@reltype MEMBER value=4 clid=CL_PROFILE
@caption kogukonna liige

@reltype BLOCKED value=5 clid=CL_USER
@caption blokeeritud kasutaja

@reltype FORUM value=1 clid=CL_FORUM_V2
@caption foorum

@reltype CALENDAR value=2 clid=CL_PLANNER
@caption kalender

@reltype CATEGORIES value=6 clid=CL_META
@caption kogukondade kategooriad

@reltype CFG_MANAGER value=7 clid=CL_CFGMANAGER
@caption seadete haldur

*/

class community extends class_base
{
	var $calendar;
	var $self = false;
	
	function community()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/commune/community",
			"clid" => CL_COMMUNITY
		));
	}
	
	function callback_on_load($arr)
	{
		$this->self = $arr["request"]["id"];
		$com = obj($this->self);
		if($cfgmanager = $com->get_first_obj_by_reltype("RELTYPE_CFG_MANAGER"))
		{
			$this->cfgmanager = $cfgmanager->id();
		}
	}
	
	function callback_mod_retval($arr)
	{
		$arr["args"]["event_id"] = aw_global_get("event_id");
	}
	
	function callback_mod_tab($args)
	{
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
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "calendar_view":
				$this->gen_events($arr);
				break;
			case "forum":
				$prop["value"] = $this->gen_forum($arr);
				break;
			case "moderators_toolbar":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name" => "add",
					"tooltip" => "Lisa moderaator",
					"img" => "new.gif",
					"url" => html::get_change_url($arr["obj_inst"]->id(),array(
						"group" => "moderators",
					)),
				));
				$tb->add_separator();
				$tb->add_button(array(
					"name" => "delete",
					"tooltip" => "Eemalda moderaator",
					"img" => "delete.gif",
					"action" => "delete_mod",
				));
				break;
			case "moderators":
				$this->show_moderators_table($arr);
				break;
			case "calendar_view_toolbar":
				$this->gen_navtoolbar($arr);
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
			case "forum_settings":
				$this->save_forum_settings($arr);
				break;
			case "calendar_settings":
				$this->save_calendar_settings($arr);
				break;
			case "add_event":
				$cal_o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR");
				$cal = $cal_o->instance();
				$cal->register_event_with_planner(array(
					"obj_inst" => &$cal_o,
					"request" => &$arr["request"],
				));
				break;
		}
		return $retval;
	}
	
	function gen_navtoolbar($arr)
	{
		$id = $this->self;
		$toolbar = &$arr["prop"]["vcl_inst"];
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
		
		
		$this->event_entry_classes = array(CL_TASK,CL_CRM_CALL,CL_CRM_OFFER,CL_CRM_MEETING,CL_CALENDAR_VACANCY,CL_CALENDAR_EVENT);
		
		// now I need to figure out which other classes are valid for that relation type
		$clidlist = $this->event_entry_classes;
		$tmp = aw_ini_get("classes");
		foreach($clidlist as $clid)
		{	//Show only if has configform
			if(($clid == CL_CALENDAR_EVENT) && ( $arr["obj_inst"]->get_first_conn_by_reltype("RELTYPE_EVENT_ENTRY") == false))
			{
				continue;
			}
			//Dont show at all
			if($clid == CL_CRM_OFFER)
			{
				continue;
			}
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
		
		$dt = date("d-m-Y",time());
		
		$toolbar->add_button(array(
			"name" => "today",
			"tooltip" => "Täna",
			"url" => $this->mk_my_orb("change",array(
				"id" => $id,
				"group" => $arr["request"]["group"],
				"viewtype" => "day",
				"date" => $dt,
				)
			) . "#today",
			"img" => "icon_cal_today.gif",
			"class" => "menuButton",
		));
	}
	
	function callback_add_event($arr)
	{
		$cal_o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR");
		$cal = get_instance(CL_PLANNER);
		//arr($cal_o->properties());
		//$cal_o->set_meta("cfgform_id", "");
		//$cal_o->save();
		return $cal->callback_get_add_event(array(
			"obj_inst" => $cal_o,
			"request" => array(
				//"cfgform_id" => $this->get_cfgform(126),
				"cb_group" => $arr["request"]["cb_group"],
				"clid" => $arr["request"]["clid"],
				"event_id" => $arr["request"]["event_id"],
			),
		));
	}
	
	function gen_forum($arr)
	{
		$cforum = reset($arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_FORUM",
		)));
		$forumi = get_instance(CL_FORUM_V2);
		arr($cforum);
		return $forumi->change(array(
			"id" => $cforum->prop("to"),
			"action" => isset($arr["request"]["action"]) ? $arr["request"]["action"] : "view",
			"rel_id" => $cforum->prop("relobj_id"),
			"folder" => $_GET["folder"],
			"topic" => $_GET["topic"],
			"page" => $_GET["page"],
			"c" => $_GET["c"],
			"cb_part" => 1,
			"fxt" => 1,
			"group" => "contents",
			//"group" => isset($_GET["group"]) ? $_GET["group"] : "contents",
		));
	}
	
	function show_moderators_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"type" => "int",
		));
		$t->define_field(array(
			"name" => "user",
			"caption" => "Kasutaja",
		));
		$t->define_field(array(
			"name" => "person",
			"caption" => "Nimi",
		));
		$t->define_field(array(
			"name" => "add_time",
			"caption" => "Lisamisaeg",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		
		$moderators = &$arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_MODERATOR",
		));
		$tmp = $arr["obj_inst"]->createdby();
		foreach($moderators as $mod)
		{
			if($tmp->id() != $mod->prop("to"))
			{
				// in case you're wondering wtf, then there should be a situation, that user ALWAYS has a person and a profile, when it's needed... so, how to create that situation? -- ahz
				$moderator = $mod->to();
				$con = obj($mod->prop("relobj_id"));
				//arr($mod);
				$person = $moderator->get_first_obj_by_reltype("RELTYPE_PERSON");
				$profile = $person->get_first_obj_by_reltype("RELTYPE_PROFILE");
				$active_profile = $person->meta("active_profile");
				$t->define_data(array(
					"id" => $moderator->id(),
					"user" => $moderator->name(),
					"person" => html::href(array(
						"caption" => $person->name(),
						"url" => $this->mk_my_orb("change", array(
							"id" => $active_profile,
						), CL_PROFILE),
					)),
					"add_time" => $this->time2date($con->created(), 2),
				));
			}
		}
	}
	
	function get_cfgform($clid)
	{
		$form = false;
		if (is_oid($this->cfgmanager))
		{
			$cfg_loader = new object($this->cfgmanager);
			$mxt = $cfg_loader->meta("use_form");
			$form = reset($mxt[$clid]);
		}
		return $form;
	}
	
	function callback_forum_settings($arr)
	{
		$forum = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_FORUM");
		$t = get_instance(CL_FORUM_V2);
		$all_props = $t->get_property_group(array(
			"cfgform_id" => $this->get_cfgform(211),
			"group" => "general",
		));
		
		$xprops = $t->parse_properties(array(
			"obj_inst" => $forum,
			"properties" => $all_props,
			"name_prefix" => "forum",
		));
		
		$xprops["forum_comments_on_page"]["options"] = array(5 => 5,10 => 10,15 => 15,20 => 20,25 => 25,30 => 30);
		$xprops["forum_topics_on_page"]["options"] = array(5 => 5,10 => 10,15 => 15,20 => 20,25 => 25,30 => 30);
		$xprops["forum_topic_depth"]["options"] = array("0" => "0","1" => "1","2" => "2","3" => "3","4" => "4","5" => "5"); 
		
		return $xprops;
	}
	
	function save_forum_settings($arr)
	{
		$forum = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_FORUM");
		$forum_i = $forum->instance();
		$props = $arr["request"]["forum"];
		$props["id"] = $forum->id();
		$props["return"] = "id";
		$props["cfgform"] = $this->get_cfgform(211);
		$props["group"] = "general";
		$forum_i->submit($props);
	}
	
	function callback_calendar_settings($arr)
	{
		$calendar = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR");
		$t = get_instance(CL_PLANNER);
		$all_props = $t->get_property_group(array(
			"cfgform_id" => $this->get_cfgform(126),
			"group" => "advanced",
		));
		$xprops = $t->parse_properties(array(
			"obj_inst" => $calendar,
			"properties" => $all_props,
			"name_prefix" => "calendar",
		));
		$xprops["calendar_navigator_months"]["options"] = array(1 => 1, 2 => 2, 3 => 3 );
		$daynames = explode("|",LC_WEEKDAY);
		for ($i = 1; $i <= 7; $i++)
		{
			$xprops["calendar_workdays"]["options"][$i] = $daynames[$i];
		}
		return $xprops;
	}

	function save_calendar_settings($arr)
	{
		$cal = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR");
		$cal_i = $cal->instance();
		$props = $arr["request"]["calendar"];
		$props["id"] = $cal->id();
		//arr($cal->properties());
		$props["return"] = "id";
		$props["cfgform"] = $this->get_cfgform(126);
		$props["group"] = "advanced";
		$cal_i->submit($props);
	}

	function get_tasklist()
	{
		$tasklist = $this->get_messages();
		$rv = array();
		foreach($tasklist->arr() as $task)
		{
			$rv[] = array(
				"name" => $task->prop("name"),
				"url" => html::get_change_url($this->self, array(
					"group" => "calendar_view",
					"viewtype" => "day",
					"date" => date("d-m-Y", $task->prop("start1")),
				)),
			);
		}
		return $rv;
	}
	
	function get_overview($arr = array())
	{
		$rv = array();
		//$folder = $this->calendar->get_first_obj_by_reltype("RELTYPE_EVENT_FOLDER");
		$messages = $this->get_messages();
		/*
		$messages = new object_list(array(
			"parent" => $folder->id(),
		));
		*/
		foreach($messages->arr() as $element)
		{
			//if($element->prop("start1") >= $arr["start"] && $element->prop("start1") <= $arr["end"])
			//{
			$rv[$element->prop("start1")] = 1;
			//} 
		}
		return $rv;
	}
	function get_messages()
	{
		return new object_list(array(
			"class_id" => CL_TASK,
			"parent" => $this->calendar->prop("event_folder"),
			"flags" => array(
				"mask" => OBJ_IS_DONE,
				"flags" => 0,
			),
		));
	}
	function get_me_my_messages($arr)
	{
		$rv = array();
		$messages = $this->get_messages();
		classload("icons");
		foreach($messages->arr() as $element)
		{
			if($element->prop("start1") >= $arr["start"] && $element->prop("start1") <= $arr["end"])
			{
				$rv[] = array(
					"start" => $element->prop("start1"),
					"name" => $element->name(),
					"icon" => icons::get_icon_url($element->class_id()),
					"link" => aw_url_change_var(array(
						"group" => "add_event",
						"event_id" => $element->id(),
					)),
					"comment" => $element->comment(),
				);
			}
		}
		return $rv;
	}
	function gen_events($arr)
	{
		$this->calendar = &$arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR");
		$t = &$arr["prop"]["vcl_inst"];
		$t->configure(array(
			"tasklist_func" => array(&$this,"get_tasklist"),
			"overview_func" => array(&$this,"get_overview"),
		));
		$range = $t->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"] ? $arr["request"]["viewtype"] : "month",
		));
		
		$events = $this->get_me_my_messages($range);
		foreach($events as $event)
		{
			$t->add_item(array(
				"timestamp" => $event["start"],
				"data" => array(
					"name" => $event["name"],
					"icon" => $event["icon"],
					"link" => $event["link"],
					"comment" => $event["comment"],
				),
			));
		}
	}
	
	/**
		@attrib name=delete_mod all_args="1"
		@param sel required type=int acl=view
		@param id required type=int
		@param group optional
	**/
	function delete_mod($arr)
	{
		if(is_array($arr["sel"]))
		{
			foreach($arr["sel"] as $sel)
			{
				$com = obj($arr["id"]);
				$com->disconnect(array(
					"reltype" => "RELTYPE_MODERATOR",
					"from" => $sel,
				));
			}
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}
	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of 0alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		arr($arr);
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
