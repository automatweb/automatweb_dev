<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/community.aw,v 1.8 2005/04/01 11:52:22 kristo Exp $
// community.aw - Kogukond 
/*

@classinfo syslog_type=ST_COMMUNITY relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

-------------------------------------

@groupinfo general2 caption="Üldised seaded" parent=general

@property owner type=hidden store=no newonly=1 group=general2

@property category type=classificator reltype=RELTYPE_CATEGORIES orient=vertical multiple=1 recursive=1 group=general2
@caption Kategooriad


@groupinfo forum_settings caption="Foorumi seaded" parent=general

@property forum_settings type=callback callback=callback_forum_settings group=forum_settings
@caption Foorumi seaded


@groupinfo calendar_settings caption="Kalendri seaded" parent=general

@property calendar_settings type=callback callback=callback_calendar_settings group=calendar_settings store=no
@caption Kalendri seaded


@groupinfo content caption="Sisu" submit=no

@property content type=callback callback=callback_content group=content no_caption=1
@caption Sisu


@groupinfo connected caption="Seotud isikud" submit=no


@groupinfo members caption="Liikmed" submit=no parent=connected

@property members_toolbar type=toolbar group=members no_caption=1
@caption Liikmete toolbar

@property members type=table group=members no_caption=1
@caption Liikmed


@groupinfo moderators caption="Moderaatorid" submit=no parent=connected

@property moderators_toolbar type=toolbar group=moderators no_caption=1
@caption Moderaatorite toolbar

@property moderators type=table group=moderators no_caption=1
@caption Moderaatorid


@groupinfo blocked caption="Blokeeritud" submit=no parent=connected

@property blocked_toolbar type=toolbar group=blocked no_caption=1
@caption Blokeeritute toolbar

@property blocked type=table group=blocked no_caption=1
@caption Blokeeritud

-------------------------------------

@reltype MODERATOR value=3 clid=CL_USER
@caption Moderaator

@reltype MEMBER value=4 clid=CL_USER
@caption Kogukonna liige

@reltype BLOCKED value=5 clid=CL_USER
@caption Blokeeritud kasutaja

@reltype FORUM value=1 clid=CL_FORUM_V2
@caption Foorum

@reltype CALENDAR value=2 clid=CL_PLANNER
@caption Kalender

@reltype CALENDAR_VIEW value=8 clid=CL_CALENDAR_VIEW
@caption Kalendri vaade

@reltype CATEGORIES value=6 clid=CL_META
@caption Kogukondade kategooriad

@reltype CFG_MANAGER value=7 clid=CL_CFGMANAGER
@caption Seadete haldur

*/

class community extends class_base
{
	var $calendar;
	var $self = false;
	var $types = array();
	
	function community()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/commune/community",
			"clid" => CL_COMMUNITY
		));
		$types = array(
			"members" => "MEMBER",
			"moderators" => "MODERATOR",
			"blocked" => "BLOCKED",
		);
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
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "blocked_toolbar":
			case "members_toolbar":
			case "moderators_toolbar":
				$var = array(
					"members_toolbar" => array(
						"blocked" => t("Blokeeri"), 
						"moderator" => t("Tee moderaatoriks"),
					),
					"blocked_toolbar" => array(
						"member" => t("Tee liikmeks"), 
						"moderator" => t("Tee moderaatoriks"),
					),
					"moderators_toolbar" => array(
						"member" => t("Tee liikmeks"), 
						"blocked" => t("Blokeeri"),
					),
				);
				$tb = &$prop["vcl_inst"];
				foreach($var[$prop["name"]] as $key => $opt)
				{
					$tb->add_button(array(
						"name" => "add_".$key,
						"tooltip" => $opt,
						"img" => "new.gif",
						"action" => "add_".$key,
					));
					$tb->add_separator();
				}
				$tb->add_button(array(
					"name" => "delete",
					"tooltip" => t("Eemalda ").$var[$prop["name"]],
					"img" => "delete.gif",
					"action" => "remove_con",
				));
				break;
			case "members":
				$this->show_mod_table($arr, "MEMBER");
				break;
			case "blocked":
				$this->show_mod_table($arr, "BLOCKED");
				break;
			case "moderators":
				$this->show_mod_table($arr, "MODERATOR");
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
			case "owner":
				$arr["obj_inst"]->connect(array(
					"to" => aw_global_get("uid_oid"),
					"reltype" => "RELTYPE_MODERATOR",
				));
				$forum = new object();
				$forum->set_class_id(CL_FORUM);
				$forum->set_status(STAT_ACTIVE);
				$forum->set_parent($arr["obj_inst"]->id());
				$forum->save();
				$arr["obj_inst"]->connect(array(
					"to" => $forum->id(),
					"reltype" => "RELTYPE_FORUM",
				));
				$cal_view = new object();
				$cal_view->set_class_id(CL_CALENDAR_VIEW);
				$cal_view->set_status(STAT_ACTIVE);
				$cal_view->set_parent($arr["obj_inst"]->id());
				$cal_view->save();
				$arr["obj_inst"]->connect(array(
					"to" => $cal_view->id(),
					"reltype" => "RELTYPE_CALENDAR_VIEW",
				));
				$calendar = new object();
				$calendar->set_class_id(CL_CALENDAR);
				$calendar->set_status(STAT_ACTIVE);
				$calendar->set_parent($arr["obj_inst"]->id());
				$calendar->save();
				$arr["obj_inst"]->connect(array(
					"to" => $calendar->id(),
					"reltype" => "RELTYPE_CALENDAR",
				));
				$cal_view->connect(array(
					"to" => $forum->id(),
					"reltype" => "RELTYPE_EVENT_SOURCE",
				));
				break;
			case "category":
				$this->add_category_connections($arr);
				break;
			case "forum_settings":
				$this->save_forum_settings($arr);
				break;
			case "calendar_settings":
				$this->save_calendar_settings($arr);
				break;
		}
		return $retval;
	}
	function callback_content($arr)
	{
		$this->read_template("show_content.tpl");
		
		$forum = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_FORUM");
		$forum_i = $forum->instance();
		$args = array(
			"alias" => array(
				//"relobj_id" => rel_id, 
				"target" => $forum->id(),
			),
		);
		// yeehaw
		$cal_view = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR_VIEW");
		$cal_view_i = $cal_view->instance(); 
		$this->vars(array(
			"forum" => $forum_i->parse_alias($args),
			"calendar" => $cal_view_i->parse_alias(array("obj_inst" => $cal_view)),
		));
		//
		return array("el1" => array("type" => "text", "value" => $this->parse(), "no_caption" => 1));
	}
	
	function add_category_connections($arr)
	{
		$cons = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_CATEGORIES",
		));
		foreach($cons as $con)
		{
			$arr["obj_inst"]->disconnect(array(
				"from" => $con->prop("to"),
				"reltype" => "RELTYPE_CATEGORIES", 
			));
		}
		foreach($arr["prop"]["value"] as $value)
		{
			$arr["obj_inst"]->connect(array(
				"to" => $value,
				"reltype" => "RELTYPE_CATEGORIES",
			));
		}
		//arr($arr);
	}
	
	function show_mod_table($arr, $type)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"type" => "int",
		));
		$t->define_field(array(
			"name" => "user",
			"caption" => t("Kasutaja"),
		));
		$t->define_field(array(
			"name" => "person",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "add_time",
			"caption" => t("Lisamisaeg"),
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		
		$moderators = &$arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_".$type,
		));
		$u = get_instance("users");
		$tmp = obj($u->get_oid_for_uid($arr["obj_inst"]->createdby()));
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
				
				//arr($profile);
				$active_profile = $person->meta("active_profile");
				//echo $active_profile;
				$t->define_data(array(
					"id" => $moderator->id(),
					"user" => $moderator->name(),
					"person" => html::href(array(
						"caption" => $person->name(),
						"url" => html::get_change_url($active_profile),
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
		if($forum = obj($arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_FORUM")))
		{
			$t = $forum->instance();
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
		}
		return $xprops;
	}
	
	function save_forum_settings($arr)
	{
		if($forum = obj($arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_FORUM")))
		{
			$forum_i = $forum->instance();
			$props = $arr["request"]["forum"];
			$props["id"] = $forum->id();
			$props["return"] = "id";
			$props["cfgform"] = $this->get_cfgform(211);
			$props["group"] = "general";
			$forum_i->submit($props);
		}
	}
	
	function callback_calendar_settings($arr)
	{
		$props = "";
		if($calendar = obj($arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR")))
		{
			$t = $calendar->instance();
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
		}
		return $xprops;
	}

	function save_calendar_settings($arr)
	{
		if($cal = obj($arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR")))
		{
			$cal_i = $cal->instance();
			$props = $arr["request"]["calendar"];
			$props["id"] = $cal->id();
			//arr($cal->properties());
			$props["return"] = "id";
			$props["cfgform"] = $this->get_cfgform(126);
			$props["group"] = "advanced";
			$cal_i->submit($props);
		}
	}

	/**
		@attrib name=add_blocked all_args="1"
		@param sel required type=int acl=view
		@param id required type=int acl=view
		@param group optional
	**/
	function add_blocked($arr)
	{
		return $this->_add_con($arr, "blocked");
	}

	/**
		@attrib name=add_moderator all_args="1"
		@param sel required type=int acl=view
		@param id required type=int acl=view
		@param group optional
	**/
	function add_moderator($arr)
	{
		return $this->_add_con($arr, "moderators");
	}
	
	/**
		@attrib name=add_member all_args="1"
		@param sel required type=int acl=view
		@param id required type=int acl=view
		@param group optional
	**/
	function add_member($arr)
	{
		return $this->_add_con($arr, "members");
	}
	
	function _add_con($arr, $opt)
	{
		$opts = array(
			"moderators" => array(4, 5),
			"blocked" => array(3, 4),
			"members" => array(3, 5),
		);
		$rels = array(
			"moderators" => "MODERATOR",
			"blocked" => "BLOCKED",
			"members" => "MEMBER",
		);
		if(is_array($arr["sel"]))
		{
			foreach($arr["sel"] as $sel)
			{
				$com = obj($arr["id"]);
				$sel_o = obj($sel);
				$cons = $sel_o->connections_to(array(
					"type" => $opts[$opt],
					"class_id" => CL_COMMUNITY,
				));
				
				// if community is connected to user, disconnect him before -- ahz
				if(count($cons) > 0)
				{
					
					foreach($cons as $con)
					{
						$com->disconnect(array(
							"from" => $con->prop("to"),
							"reltype" => $con->prop("reltype"),
						));
					}
				}
				
				$com->connect(array(
					"to" => $sel,
					"reltype" => "RELTYPE_".$rels[$opt],
				));
			}
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}
	
	/**
		@attrib name=remove_con all_args="1"
		@param sel required type=int acl=view
		@param id required type=int acl=view
		@param group optional
	**/
	function remove_con($arr)
	{
		$types = array(
			"members" => "MEMBER", //4
			"moderators" => "MODERATOR", //3
			"blocked" => "BLOCKED", //5
			4 => "MEMBER", //4
			3 => "MODERATOR", //3
			5 => "BLOCKED", //5
		);
		if(is_array($arr["sel"]))
		{
			foreach($arr["sel"] as $sel)
			{
				$com = obj($arr["id"]);
				$com->disconnect(array(
					"reltype" => "RELTYPE_".$types[$arr["group"]],
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
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		//arr($arr);
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
	
	/**
		@attrib name=change nologin=1 all_args=1 is_public=1 caption="Kogukonna sisu"
		@param id required type=int acl=view
		@param group optional
	**/
	function change($args = array())
	{
		if(strpos($_SERVER["REQUEST_URI"],"/automatweb") !== false)
		{
			return parent::change($args);
        }
		
		enter_function("cb-change");
		$this->init_class_base();

		$this->subgroup = $this->reltype = "";
		$this->is_rel = false;

		$this->orb_action = $args["action"];
		
		$this->is_translated = 0;

		if (empty($args["action"]))
		{
			$args["action"] = "change";
		};
		
		if (method_exists($this->inst,"callback_on_load"))
		{
			$this->inst->callback_on_load(array(
				"request" => $args,
			));
		}

		if ($args["no_active_tab"])
		{
			$this->no_active_tab = 1;
		};

		if (empty($args["form"]))
		{
			if (($args["action"] == "change") || ($args["action"] == "view"))
			{
				$this->load_storage_object($args);
				if ($this->obj_inst->class_id() == CL_RELATION)
				{
					// this is a relation!
					$this->is_rel = true;
					$def = $this->_ct[$this->clid]["def"];
					$meta = $this->obj_inst->meta("values");
					$this->values = $meta[$def];
					$this->values["name"] = $this->obj_inst->name();
				};

			};
		}

		$this->use_form = $use_form;

		$filter = array(
			"clid" => $this->clid,
			"clfile" => $this->clfile,
			"group" => $args["group"],
		);

		$properties = $this->get_property_group($filter);
		
		if(array_key_exists("name", $properties))
		{
			header("location:".aw_ini_get("baseurl"));
			die();
		}

		$this->set_classinfo(array("name" => "hide_tabs","value" => 1));
		$this->set_classinfo(array("name" => "layout", "value" => ""));
		
		if (!empty($args["form"]))
		{
			$onload_method = $this->forminfo(array(
				"form" => $args["form"],
				"attr" => "onload",
			));

			if (method_exists($this->inst, $onload_method))
			{
				$this->inst->$onload_method($args);
			}
		};
	
		$this->request = $args;

		if(method_exists($this->inst,"callback_pre_edit"))
		{
			$fstat = $this->inst->callback_pre_edit(array(
				"id" => $this->id,
				"request" => $this->request,
				"obj_inst" => &$this->obj_inst,
				"group" => $this->use_group,
			));

			if (is_array($fstat) && !empty($fstat["error"]))
			{
				$properties = array();
				$properties["error"] = array(
					"type" => "text",
					"error" => $fstat["errmsg"],
				);
				$gdata["submit"] = "no";
			}
		}
		
		$resprops = $this->parse_properties(array(
			"properties" => &$properties,
		));
		if(array_key_exists("submit", $this->groupinfo[$args["group"]]))
		{
			if($this->groupinfo[$args["group"]]["submit"] == "no")
			{
				$lm = 1;
			}
		}
		foreach($resprops as $prop)
		{
			if($prop["type"] == "toolbar")
			{
				$lm = 1;
				break;
			}
		}

		if (!empty($lm))
		{
			$gdata["submit"] = "no";
		};
		
		$template = $this->forminfo(array(
			"form" => $args["form"],
			"attr" => "template",
		));
		$o_arr = array(
			"tpldir" => "applications/commune/commune", 
			"tabs" => false,
		);
		if (!empty($template))
		{
			$o_arr["template"] = $template;
		}
		$cli = get_instance("cfg/htmlclient", $o_arr);

		if (is_array($this->layoutinfo) && method_exists($cli,"set_layout"))
		{
			$tmp = array();
			// export only layout information for the current group
			foreach($this->layoutinfo as $key => $val)
			{
				if ($val["group"] == $this->use_group)
				{
					$tmp[$key] = $val;


				};
			};
			$cli->set_layout($tmp);
		};

		$this->inst->relinfo = $this->relinfo;

		enter_function("parse-properties");

		exit_function("parse-properties");
		enter_function("add-property");

		foreach($resprops as $val)
		{
			$cli->add_property($val);
		};
		exit_function("add-property");
		
		$argblock = array(
			"id" => $this->id,
			"group" => isset($this->request["group"]) ? $this->request["group"] : $this->use_group,
			"orb_class" => "community",
			"section" => $_REQUEST["section"],
		);

		if (method_exists($this->inst,"callback_mod_reforb"))
		{
			$this->inst->callback_mod_reforb(&$argblock,$this->request);

		};

		$submit_action = "submit";

		$form_submit_action = $this->forminfo(array(
			"form" => $use_form,
			"attr" => "onsubmit",
		));

		if (!empty($form_submit_action))
		{
			$submit_action = $form_submit_action;
		}

		// forminfo can override form post method
		$form_submit_method = $this->forminfo(array(
			"form" => $use_form,
			"attr" => "method",
		));

		$method = "POST";
		if (!empty($form_submit_method))
		{
			$method = "GET";
		};

		if (!empty($gdata["submit_method"]))
		{
			$method = "GET";
			$submit_action = $args["action"];
		};

		if (!empty($gdata["submit_action"]))
		{
			$submit_action = $gdata["submit_action"];
		}	

		if ($method == "GET")
		{
			$argblock["no_reforb"] = 1;
		};

		enter_function("final-bit");
		
		$cli->finish_output(array(
			"method" => $method,
			"action" => $submit_action,
			// hm, dat is weird!
			"submit" => isset($gdata["submit"]) ? $gdata["submit"] : "",
			"data" => $argblock,
		));
		$rv = $cli->get_result();
		
		exit_function("final-bit");
		exit_function("cb-change");
		return $rv;
	}
}
?>
