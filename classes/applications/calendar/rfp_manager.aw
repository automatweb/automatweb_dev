<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp_manager.aw,v 1.18 2008/03/13 13:26:51 kristo Exp $
// rfp_manager.aw - RFP Haldus 
/*

@classinfo syslog_type=ST_RFP_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=tarvo

@default table=objects
@default group=general

@property copy_redirect type=relpicker reltype=RELTYPE_REDIR_DOC field=meta method=serialize
@caption Edasisuunamisdokument

@groupinfo rfps caption="Pakkumise saamis palved"
@groupinfo rfps_active caption="Aktiivsed" parent=rfps
@groupinfo rfps_archive caption="Arhiiv" parent=rfps
@default group=rfps_active,rfps_archive
	@layout hsplit type=hbox
		@layout searchbox closeable=1 area_caption=Otsing type=vbox parent=hsplit
			@property s_name type=textbox parent=searchbox size=15 store=no captionside=top
			@caption &Uuml;rituse nimi

			@property s_org type=textbox parent=searchbox size=15 store=no captionside=top
			@caption Organisatsioon

			@property s_contact type=textbox parent=searchbox size=15 store=no captionside=top
			@caption Kontaktisik

			@property s_time_from type=date_select parent=searchbox store=no captionside=top
			@caption Alates

			@property s_time_to type=date_select parent=searchbox store=no captionside=top
			@caption Kuni

			@property s_submit type=submit parent=searchbox store=no no_caption=1
			@caption Otsi

		@layout main type=vbox parent=hsplit
			@property rfps type=table parent=main no_caption=1



@reltype REDIR_DOC value=1 clid=CL_DOCUMENT
@caption Konverentsiplaneerija
*/

class rfp_manager extends class_base
{
	function rfp_manager()
	{
		$this->init(array(
			"tpldir" => "applications/conference_planning_webview",
			"clid" => CL_RFP_MANAGER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "s_name":
			case "s_org":
			case "s_contact":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;
			case "s_time_to":
			case "s_time_from":
				$_t = &$arr["request"][$prop["name"]];
				$time = mktime(0,0,0,$_t["month"], $_t["day"], $_t["year"]);
				$prop["value"] = $time;
				break;
			case "rfps":
				$act = ($arr["request"]["group"] == "rfps_active" || $arr["request"]["group"] == "rfps")?true:false;
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "function",
					"caption" => t("&Uuml;ritus"),
					"chgbgcolor" => "urgent_col",
				));
				$t->define_field(array(
					"name" => "org",
					"caption" => t("Organisatsioon"),
					"chgbgcolor" => "urgent_col",
				));
				$t->define_field(array(
					"name" => "response_date",
					"caption" => t("Tagasiside aeg"),
					"chgbgcolor" => "urgent_col",
				));
				$t->define_field(array(
					"name" => "date_period",
					"caption" => t("Ajavahemik"),
					"chgbgcolor" => "urgent_col",
				));
				$t->define_field(array(
					"name" => "acc_need",
					"caption" => t("Majutus"),
					"chgbgcolor" => "urgent_col",
				));
				$t->define_field(array(
					"name" => "delegates",
					"caption" => t("Inimeste arv"),
					"chgbgcolor" => "urgent_col",
				));
				$t->define_field(array(
					"name" => "contact_pers",
					"caption" => t("Kontaktisik"),
					"chgbgcolor" => "urgent_col",
				));
				$t->define_field(array(
					"name" => "contacts",
					"caption" => t("Kontaktandmed"),
					"chgbgcolor" => "urgent_col",
				));
				$t->define_field(array(
					"name" => "popup",
					"caption" => t("Tegevus"),
					"align" => "center",
					"chgbgcolor" => "urgent_col",
				));


				$rfps = $this->get_rfps($act);
				$rfps = $this->do_filter_rfps($rfps, $arr["request"]);
				$uss = get_instance(CL_USER);
				foreach($rfps as $oid => $obj)
				{
					// end search filter
					$sres = aw_unserialize($obj->prop("search_result"));
					unset($places);
					foreach($sres as $res)
					{
						$places[] = $res["location"];
					}
					$c = array("billing_phone_number", "billing_email");
					unset($contacts);
					foreach($c as $e)
					{
						if(strlen(($cnt = $obj->prop($e))))
						{
							$contacts[] = $cnt;
						}
					}
					$urgent_col = ($obj->prop("urgent") == 1)?"#CC3333":"";
					$rooms = array();
					if($_t = $obj->prop("single_rooms"))
					{
						$rooms[] = $_t." ".t("Si");
					}
					if($_t = $obj->prop("double_rooms"))
					{
						$rooms[] = $_t." ".t("Do");
					}
					if($_t = $obj->prop("suites"))
					{
						$rooms[] = $_t." ".t("Su");
					}
					$acc_rooms = join(", ", $rooms);
					$t->define_data(array(
						"function" => html::href(array(
							"caption" => ($_t = $obj->prop("function_name"))?$_t:t(" - Nimetu - "),
							"url" => "#",
							"onClick" => "aw_popup_scroll(\"".$this->mk_my_orb("show_overview", array(
								"oid" => $oid
							))."\",\"hey\",600,600);",
							/*
							"url" => $this->mk_my_orb("change", array(
								"id" => $oid,
								"return_url" => get_ru(),
							),CL_RFP),
							*/
						)),
						"org" => $obj->prop("organisation"),
						"response_date" => $obj->prop("response_date"),
						"date_period" => $obj->prop("arrival_date")." - ".$obj->prop("departure_date"),
						"acc_need" => ($obj->prop("accommondation_requirements") == 1)?$acc_rooms:t("Ei"),
						"delegates" => $obj->prop("delegates_no"),
						"contact_pers" => $obj->prop("billing_name"),
						"contacts" => join(", ", $contacts).(strlen($_t = $obj->prop("contact_preference"))?"(".$_t.")":""),
						"popup" => $this->gen_popup($oid),
						"urgent_col" => $urgent_col,
					));
				}
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_mod_retval($arr)
	{
		$todo = array("s_name", "s_org", "s_contact", "s_time_from", "s_time_to");
		foreach($todo as $do)
		{
			$arr["args"][$do] = $arr["request"][$do];
		}
	}


	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("rfp_history.tpl");
		$rfps = $this->get_rfps(true, true);
		foreach($rfps as $oid => $obj)
		{
			$hotel = $obj->prop("data_gen_hotel");
			$this->vars(array(
				"name" => ($_t = $obj->prop("data_gen_function_name"))?$_t:$obj->name(),
				"time" => $obj->prop("data_gen_arrival_date")." - ".$obj->prop("data_gen_departure_date"),
				"attendees" => $obj->prop("data_gen_attendees_no"),
				"hotel" => $hotel,
				//"copy_url" => $this->mk_my_orb("reload_rfp", array(
				"copy_url" => $this->mk_my_orb("rfp_reload", array(
					"rfp" => $obj->id(),
					"oid" => $arr["id"],
					"return_url" => aw_ini_get("baseurl"),
				)),
				"remove_url" => $this->mk_my_orb("archive", array(
					"rfp" => $obj->id(),
					"return_url" => get_ru(),
				)),
			));
			$html .= $this->parse("RFP");
		}

		$this->vars(array(
			"RFP" => $html,	
		));
		return $this->parse();
	}

//-- methods --//

	function get_rfps($act, $cur_user = false)
	{
		$arr = array(
			"class_id" => CL_RFP,
			"archived" => !$act?1:0,
		);
		if($cur_user)
		{
			$arr["createdby"] = aw_global_get("uid");
		}
		$o = new object_list($arr);
		return $o->arr();
	}

	/**
		@attrib params=name name=show_overview all_args=1
		@param oid required type=oid
	**/
	function show_overview($arr, $return_content = false)
	{
		$c_plan= get_instance(CL_CONFERENCE_PLANNING);
		// set data .. this sucks
		$obj = obj($arr["oid"]);
		$data = array(
			"country" => $obj->prop("country"),
			"function_name" => $obj->prop("function_name"),
			"organisation_company" => $obj->prop("organisation"),
			"dates" => array(
				0 => array(
					"response_date" => $obj->prop("response_date"),
					"decision_date" => $obj->prop("decision_date"),
					"arrival_date" => $obj->prop("arrival_date"),
					"departure_date" => $obj->prop("departure_date"),
				),	
			),
			"open_for_alternative_dates" => $obj->prop("open_for_alternative_dates"),
			"accommondation_requirements" => $obj->prop("accommondation_requirements"),
			"flexible_dates" => $obj->prop("flexible_dates"),
			"date_comments" => $obj->prop("date_comments"),
			"needs_rooms" => $obj->prop("needs_rooms"),
			"single_count" => $obj->prop("single_rooms"),
			"double_count" => $obj->prop("double_rooms"),
			"suite_count" => $obj->prop("suites"),
			"event_type_select" => $obj->prop("event_type_select"),
			"event_type_chooser" => $obj->prop("event_type_chooser"),
			"event_type_text" => $obj->prop("event_type_text"),
			"delegates_no" => $obj->prop("delegates_no"),
			"door_sign" => $obj->prop("door_sign"),
			"persons_no" => $obj->prop("person_no"),
			"table_form" => $obj->prop("table_form_raw"),
			"function_start_time" => $obj->prop("start_time_raw"),
			"function_start_date" => $obj->prop("start_date_raw"),
			"function_end_time" => $obj->prop("end_time_raw"),
			"function_end_date" => $obj->prop("end_date_raw"),
			"dates_are_flexible" => $obj->prop("dates_are_flexible"),
			"meeting_pattern" => $obj->prop("meeting_pattern"),
			"pattern_wday_from" => $obj->prop("pattern_wday_from"),
			"pattern_wday_to" => $obj->prop("pattern_wday_to"),
			"pattern_wdays" => $obj->prop("pattern_wdays"),
			"main_catering" => aw_unserialize($obj->prop("main_catering")),
			"tech" => aw_unserialize($obj->prop("tech_equip")),
			"additional_functions" => aw_unserialize($obj->prop("additional_functions_raw")),
			// billing
			"billing_company" => $obj->prop("billing_company"),
			"billing_contact" => $obj->prop("billing_contact"),
			"billing_street" => $obj->prop("billing_street"),
			"billing_city" => $obj->prop("billing_city"),
			"billing_zip" => $obj->prop("billing_zip"),
			"billing_country" => $obj->prop("billing_country"),
			"billing_name" => $obj->prop("billing_name"),
			"billing_phone_number" => $obj->prop("billing_phone_number"),
			"billing_email" => $obj->prop("billing_email"),
			"selected_search_result" => aw_unserialize($obj->prop("selected_search_result")),
			"all_search_results" => aw_unserialize($obj->prop("all_search_results")),
			"main_function" => aw_unserialize($obj->prop("main_function")),
			"multi_day" => aw_unserialize($obj->prop("multi_day")),
		);
		
		$data["dates"] = array_merge($data["dates"], aw_unserialize($obj->prop("additional_dates_raw")));

		$ret = $c_plan->show(array(
			"sub" => 7,
			"sub_contents_only" => true,
			"data" => $data,
		));
		$this->read_template("overview.tpl");
		$this->vars(array(
			"contents" => $ret,
		));
		$content = $this->parse();
		if($return_content)
		{
			return $content;
		}
		else
		{
			die($content);
		}
	}

	function gen_popup($oid)
	{
		$pm = get_instance("vcl/popup_menu");
		$pm->begin_menu("aif_".$oid);
		$obj = obj($oid);
		$act = ($obj->prop("archived") == 1)?false:true;
		$prefix = $act?"":"un";
		$pm->add_item(array(
			"text" => !$act?t("Aktiviseeri"):t("Arhiveeri"),
			"link" => $this->mk_my_orb($prefix."archive", array(
				"rfp" => $oid,
				"return_url" => get_ru(),
			)),
		));
		$pm->add_item(array(
			"text" => t("Kustuta"),
			"link" => $this->mk_my_orb("del_rfp", array(
				"rfp" => $oid,
				"return_url" => get_ru(),
			)),
		));
		return $pm->get_menu();
	}

	/**
		@attrib params=name name=unarchive all_args=1
	**/
	function unarchive($arr)
	{
		$o = obj($arr["rfp"]);
		$o->set_prop("archived", 0);
		$o->save();
		return $arr["return_url"];
	}

	/**
		@attrib params=name name=archive all_args=1
	**/
	function archive($arr)
	{
		$o = obj($arr["rfp"]);
		$o->set_prop("archived", 1);
		$o->save();
		return $arr["return_url"];
	}

	/**
		@attrib params=name name=del_rfp all_args=1
	**/
	function del_rfp($arr)
	{
		$o = obj($arr["rfp"]);
		$o->delete();
		return $arr["return_url"];
	}

	function do_filter_rfps($rfps, $request)
	{
		$_tmp_from = str_replace("---", 0, $request["s_time_from"]);
		$_tmp_to = str_replace("---", 0, $request["s_time_to"]);
		foreach($rfps as $oid => $obj)
		{
			// time
			if(is_array($_tmp_from) && is_array($_tmp_to))
			{
				$comp = $obj->created();
				$s_f = mktime(0,0,0, $_tmp_from["month"], $_tmp_from["day"], $_tmp_from["year"]);
				$s_t = mktime(0,0,0, $_tmp_to["month"], $_tmp_to["day"], $_tmp_to["year"]);
				if(($s_f == -1 && $s_t <= $comp) || ($s_t == -1 && $s_f >= $comp) || ($s_f == -1 && $s_t == -1))
				{
					unset($rfps[$oid]);
				}
			}

			// func name
			if(strlen($request["s_name"]) && !stristr($obj->name("organisation") , $request["s_name"]))
			{
				unset($rfps[$oid]);
			}

			// org name
			if(strlen($request["s_org"]) && !stristr($obj->prop(), $request["s_org"]))
			{
				unset($rfps[$oid]);
			}

			// contact name
			if(strlen($request["s_contact"]))
			{
				$is = false;
				$name = $obj->prop("billing_contact");
				foreach(split(" ", $request["s_contact"]) as $part)
				{
					$is = stristr($name, $part)?true:$is;
				}
				if(!$is)
				{
					unset($rfps[$oid]);
				}
			}
		}

		return $rfps;
	}

	/**
		@attrib params=name name=rfp_reload all_args=1
	**/
	function rfp_reload($arr)
	{
		$obj = obj($arr["rfp"]);
		$inst = get_instance(CL_CONFERENCE_PLANNING);
		$cfg = get_instance("cfg/cfgutils");
		$list = $cfg->load_properties(array(
			"clid" => CL_RFP
		));
		$grinfo = $cfg->get_groupinfo();
		$list = array_filter($list, array($inst, "__callback_filter_prplist"));
		foreach($list as $prp => $t)
		{
			if($obj->prop($prp))
			{
				$data[$prp] = $obj->prop($prp);
			}
		}
		$inst->store_data($obj->prop("conference_planner"), $data, false);
		$o = $this->can("view", $obj->prop("conference_planner"))?obj($obj->prop("conference_planner")):false;

		return aw_ini_get("baseurl")."/".($o?$o->prop("document"):"");
	}

	/**
		@attrib params=name name=reload_rfp all_args=1
	**/
	function reload_rfp($arr)
	{
		// data = session["tmp_conference_data"]
		$obj = obj($arr["rfp"]);
		$data = array();
		
		$data["function_name"] = $obj->name();
		$data["user_contact_preference"] = $obj->prop("contact_preference");
		$data["country"] = $obj->prop("country");

		$data["organisation_company"] = $obj->prop("organisation");
		$data["attendees_no"]  = $obj->prop("attendees_no");
		// dates = data["dates"]
		$first_date["response_date"] = $obj->prop("response_date");
		$first_date["decision_date"] = $obj->prop("decision_date");
		$first_date["arrival_date"] = $obj->prop("arrival_date");
		$first_date["departure_date"] = $obj->prop("departure_date");
		
		$data["open_for_alternative_dates"] = $obj->prop("open_for_alternative_dates");
		$data["accommondation_requirements"] = $obj->prop("accommondation_requirements");
		$data["needs_rooms"] = $obj->prop("needs_rooms");
		$data["single_count"] = $obj->prop("single_rooms");
		$data["double_count"] = $obj->prop("double_rooms");
		$data["suite_count"] = $obj->prop("suites");
		$data["date_comments"] = $obj->prop("date_comments");
		$data["dates_are_flexible"] = $obj->prop("dates_are_flexible");
		$data["meeting_pattern"] = $obj->prop("meeting_pattern");
		$data["pattern_wday_from"] = $obj->prop("pattern_wday_from");
		$data["pattern_wday_to"] = $obj->prop("pattern_wday_to");
		$data["pattern_days"] = $obj->prop("pattern_days");
		$data["tech"] = aw_unserialize($obj->prop("tech_equip_raw"));
		$data["main_catering"] = aw_unserialize($obj->prop("main_catering"));

		$data["event_type_chooser"] = $obj->prop("event_type_chooser");
		$data["event_type_select"] = $obj->prop("event_type_select");
		$data["event_type_text"] = $obj->prop("event_type_text");
		//
		$data["delegates_no"] = $obj->prop("delegates_no");
		$data["door_sign"] = $obj->prop("door_sign");
		$data["persons_no"] = $obj->prop("person_no");
		$data["table_form"] = $obj->prop("table_form_raw");
		$data["function_start_time"] = $obj->prop("start_time_raw");
		$data["function_start_date"] = $obj->prop("start_date_raw");
		$data["function_end_time"] = $obj->prop("end_time_raw");
		$data["function_end_date"] = $obj->prop("end_date_raw");
		$data["24h"] = $obj->prop("24h");
		$dates = aw_unserialize($obj->prop("additional_dates_raw"));
		$dates[0] = $first_date;
		$data["dates"] = $dates;

		$data["additional_functions"] = aw_unserialize($obj->prop("additional_functions_raw"));

		// billing stuff
		$data["billing_company"] = $obj->prop("billing_company");
		$data["billing_contact"] = $obj->prop("billing_contact");
		$data["billing_street"] = $obj->prop("billing_street");
		$data["billing_city"] = $obj->prop("billing_city");
		$data["billing_zip"] = $obj->prop("billing_zip");
		$data["billing_country"] = $obj->prop("billing_country");
		$data["billing_name"] = $obj->prop("billing_name");
		$data["billing_phone_number"] = $obj->prop("billing_phone_number");
		$data["billing_email"] = $obj->prop("billing_email");
		$data["urgent"] = $obj->prop("urgent");

		$data["all_search_results"] = aw_unserialize($obj->prop("all_search_results"));
		$data["selected_search_result"] = aw_unserialize($obj->prop("selected_search_result"));
		aw_session_set("tmp_conference_data", $data);

		$self = obj($arr["oid"]);
		$c = $self->prop("copy_redirect");

		return aw_ini_get("baseurl")."/".$c."?sub=1";
	}
}
?>
