<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/conference_planning.aw,v 1.2 2006/11/03 14:29:58 tarvo Exp $
// conference_planning.aw - Konverentsi planeerimine 
/*

@classinfo syslog_type=ST_CONFERENCE_PLANNING relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property single_count type=textbox field=meta method=serialize
@caption &Uuml;hekohaliste tubade arv

@property double_count type=textbox field=meta method=serialize
@caption Kahekohaliste tubade arv

@property suite_count type=textbox field=meta method=serialize
@caption Sviitide arv

@property meeting_pattern_max_days type=textbox field=meta method=serialize
@caption Koosoleku aja mustri p&auml;evade arv

*/

class conference_planning extends class_base
{
	function conference_planning()
	{
		$this->init(array(
			"tpldir" => "applications/conference_planning_webview",
			"clid" => CL_CONFERENCE_PLANNING
		));
		
		$this->wd = array(
			0 => t("Mon"),
			1 => t("Tue"),
			2 => t("Wed"),
			3 => t("Thu"),
			4 => t("Fri"),
			5 => t("Sat"),
			6 => t("Sun"),
		);

		$this->table_forms = array(
			0 => t("Theatre"),
			1 => t("Diplomat"),
			2 => t("Banquet"),
			3 => t("School"),
			4 => t("Fishbone"),
			5 => t("U-Shape"),
			6 => t("Cabaret"),
			7 => t("Dinner"),
			8 => t("Coctail"),
		);

		$this->tech_equip = array(
			0 => t("Data projector"),
			1 => t("TV/DVD"),
			2 => t("Slide projector"),
			3 => t("Mircophones"),
			4 => t("OHP"),
		);

		$this->catering_types = array(
			0 => t("Morning coffe break"),
			1 => t("Lunch"),
			2 => t("Afternoon coffe break"),
			3 => t("Fruit assortment in the room"),
			4 => t("Sodas"),
			5 => t("Non-stop coffee"),
		);

		$this->countrys = array(
			1 => t("Estionia"),
			2 => t("Latvia"),
			3 => t("Lietuva"),
		);

		$this->lc_load("conference_planning", "lc_conference_planning");
		lc_site_load("conference_planning", &$this);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
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

	function parse_alias($arr)
	{
		$arr["id"] = $arr["oid"];
		$arr["conference_planner"] = $arr["alias"]["to"];
		return $this->show($arr);
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$_GET = $GLOBALS["_GET"];
		if($_GET["action"])
		{
			$this->do_actions($arr, $_GET["action"]);
		}
		$c_obj = obj($arr["conference_planner"]);
		$ob = new object($arr["id"]);
		$this->read_template("conference_planning.tpl");

		$sc = new aw_template();
		$sc->init(array(
			"tpldir" => "applications/conference_planning_webview",
			"clid" => CL_CONFERENCE_PLANNING
		));
		$sc->lc_load("conference_planning", "lc_conference_planning");
		$sd = $this->get_form_data();
		$no = $_GET["sub"];
		if(!is_array($sd["main_catering"]))
		{
			$this->do_actions($arr, "add_first_catering");
		}
		$sd = $this->get_form_data();
		switch($no)
		{
			case 1:
				$sc->read_template("sub_conference_rfp1.tpl");				
				$sc->vars(array(
					"function_name" => $sd["function_name"],
					"organisation_company" => $sd["organisation_company"],
					"response_date" => $sd["dates"][0]["response_date"],
					"decision_date" => $sd["dates"][0]["decision_date"],
					"arrival_date" => $sd["dates"][0]["arrival_date"],
					"departure_date" => $sd["dates"][0]["departure_date"],
					"open_for_alternative_dates" => ($sd["open_for_alternative_dates"])?"CHECKED":"",
					"accommondation_requirements" => ($sd["accommondation_requirements"])?"CHECKED":"",
				));

				break;
			case 2:
				$sc->read_template("sub_conference_rfp2.tpl");
				// tablerows
				foreach($sd["dates"] as $row_no => $date)
				{
					$sc->vars(array(
						"date_no" => $row_no,
						"date_type_".(($date["type"] == 0)?"normal":"alternative") => "SELECTED",
						"arrival_date" => $date["arrival_date"],
						"departure_date" => $date["departure_date"],
						"remove_url" => aw_ini_get("baseurl")."/".$arr["id"]."?sub=".$no."&action=remove&id=".$row_no,
					));
					$tablerows .= $sc->parse("ROW");
				}
				// days from
				$sc->vars(array(
					"value" => -1,
					"caption" => t("-"),
				));
				$dayf = $sc->parse("DAY");
				for($i = 0; $i<7 ;$i++)
				{
					$sc->vars(array(
						"value" => $i,
						"caption" => $this->wd[$i],
						"pattern_wday_from" => ($sd["pattern_wday_from"] == $i)?"SELECTED":"",
					));
					$dayf .= $sc->parse("DAY_FROM");
				}
				// days to
				$sc->vars(array(
					"value" => -1,
					"caption" => t("-"),
				));
				$dayt = $sc->parse("DAY");
				for($i = 0; $i<7 ;$i++)
				{
					$sc->vars(array(
						"value" => $i,
						"caption" => $this->wd[$i],
						"pattern_wday_to" => ($sd["pattern_wday_to"] == $i)?"SELECTED":"",
					));
					$dayt .= $sc->parse("DAY_TO");
				}
				for($i = 1; $i <= $c_obj->prop("meeting_pattern_max_days");$i++)
				{
					$sc->vars(array(
						"value" => $i,
						"caption" => $i,
						"pattern_day" => ($sd["pattern_day"] == $i)?"SELECTED":"",
					));
					$day .= $sc->parse("DAY");
				}
				$sc->vars(array(
					"dates_are_flexible" => $sd["dates_are_flexible"]?"CHECKED":"",
					"date_comments" => $sd["date_comments"],
					"pattern_".$sd["meeting_pattern"] => "CHECKED",
					"ROW" => $tablerows,
					"DAY_FROM" => $dayf,
					"DAY_TO" => $dayt,
					"DAY" => $day,
				));
				break;
			case 3:
				$sc->read_template("sub_conference_rfp3.tpl");
				foreach(array("single", "double", "suite") as $loop_item)
				{
					for($i = 1; $i <= $c_obj->prop($loop_item."_count"); $i++)
					{
						$sc->vars(array(
							"value" => $i,
							"caption" => $i,
							$loop_item => ($sd[$loop_item."_count"] == $i)?"SELECTED":"",
						));
						$room_options[strtoupper($loop_item)."_OPTION"] .= $sc->parse(strtoupper($loop_item)."_OPTION");
					}
				}
				$sc->vars($room_options);
				$sc->vars(array(
					"needs_rooms" => $sd["needs_rooms"]?"CHECKED":"",
					"main_arrival_date" => $sd["dates"][0]["arrival_date"],
					"main_departure_date" => $sd["dates"][0]["departure_date"],
				));
				break;
			case 4:
				$sc->read_template("sub_conference_rfp4.tpl");				
				$c_inst = get_instance(CL_CONFERENCE);
				foreach($c_inst->conference_types() as $k => $capt)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $capt,
						"event_type_select" => ($k == $sd["event_type_select"])?"SELECTED":"",
					));
					$evt_type .= $sc->parse("EVT_TYPE");
				}
				foreach($this->table_forms as $k => $capt)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $capt,
						"table_form" => ($k == $sd["table_form"])?"SELECTED":"",
					));
					$tab_forms .= $sc->parse("TABLE_FORM");
				}
				foreach($this->tech_equip as $k => $capt)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $capt,
						"tech" => (in_array($k, array_keys($sd["tech"])))?"CHECKED":"",
					));
					$tech .= $sc->parse("TECH_EQUIP");
				}


				// catering crap
				foreach($sd["main_catering"] as $cat_no => $cat_data)
				{
					unset($catering);
					foreach($this->catering_types as $k => $capt)
					{
						$sc->vars(array(
							"value" => $k,
							"caption" => $capt,
							"catering_type_select" => ($k == $cat_data["catering_type_select"])?"SELECTED":"",
						));
						$catering .= $sc->parse("CATERING_TYPE");
					}
					$sc->vars(array(
						"catering_no" => $cat_no,
						"catering_type_chooser_".$cat_data["catering_type_chooser"] => "CHECKED",
						"catering_type_text" => $cat_data["catering_type_text"],
						"catering_start_time" => $cat_data["catering_start_time"],
						"catering_end_time" => $cat_data["catering_end_time"],
						"catering_attendees_no" => $cat_data["catering_attendees_no"],
						"remove_url" => aw_ini_get("baseurl")."/".$ob->id()."?sub=".$no."&action=remove&id=".$cat_no,
						"CATERING_TYPE" => $catering,
					));
					$cats .= $sc->parse("MAIN_CATERING");
				}
				
				$sc->vars(array(
					"EVT_TYPE" => $evt_type,
					"TABLE_FORM" => $tab_forms,
					"TECH_EQUIP" => $tech,
					"MAIN_CATERING" => $cats,
					"event_type_text" => $sd["event_type_text"],
					"event_type_chooser_".$sd["event_type_chooser"] => "CHECKED",
					"delegates_no" => $sd["delegates_no"],
					"door_sign" => $sd["door_sign"],
					"persons_no" => $sd["persons_no"],
					"function_start_date" => $sd["function_start_date"],
					"function_end_date" => $sd["function_end_date"],
					"function_start_time" => $sd["function_start_time"],
					"function_end_time" => $sd["function_end_time"],
					"24h" => $sd["24h"]?"CHECKED":"",
				));
				break;
			case 5:
				$sc->read_template("sub_conference_rfp5.tpl");				
				$c_inst = get_instance(CL_CONFERENCE);
				$c_types = $c_inst->conference_types();
				$values = array();
				if($_GET["act_evt_no"])
				{
					$values = $sd["additional_functions"][$_GET["act_evt_no"]];
				}

				foreach($c_types as $k => $capt)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $capt,
						"event_type_select" => ($k == $values["event_type_select"])?"SELECTED":"",
					));
					$evt_type .= $sc->parse("EVT_TYPE");
				}
				foreach($this->table_forms as $k => $capt)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $capt,
						"table_form" => ($k == $values["table_form"])?"SELECTED":"",
					));
					$tform .= $sc->parse("TABLE_FORM");
				}
				foreach($this->tech_equip as $k => $capt)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $capt,
						"tech" => (in_array($k, array_keys($values["tech"])))?"CHECKED":"",
					));
					$tech .= $sc->parse("TECH_EQUIP");
				}
				foreach($this->catering_types as $k => $capt)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $capt,
						"catering_type_select" => ($k == $values["catering_type_select"])?"SELECTED":"",
					));
					$catering .= $sc->parse("CATERING_TYPE");
				}
				// table rows
				$d = $this->get_form_data();
				foreach($d["additional_functions"] as $id => $data)
				{
					$sc->vars(array(
						"caption" => ($data["event_type_chooser"] == 1)?$c_types[$data["event_type_select"]]:$data["event_type_text"],
						"remove_url" => aw_ini_get("baseurl")."/".$ob->id()."?sub=".$no."&action=remove&id=".$id,
						"edit_url" => aw_ini_get("baseurl")."/".$ob->id()."?sub=".$no."&act_evt_no=".$id,
					));
					$rows .= $sc->parse("ROW");
				}
				$sc->vars(array(
					"TECH_EQUIP" => $tech,
					"TABLE_FORM" => $tform,
					"CATERING_TYPE" => $catering,
					"EVT_TYPE" => $evt_type,
					"ROW" => $rows,
					"event_type_chooser_".$values["event_type_chooser"] => "CHECKED",
					"event_type_text" => $values["event_type_text"],
					"delegates_no" => $values["delegates_no"],
					"door_sign" => $values["door_sign"],
					"function_start_date" => $values["function_start_date"],
					"function_start_time" => $values["function_start_time"],
					"function_end_date" => $values["function_end_date"],
					"function_end_time" => $values["function_end_time"],
					"24h" => $values["24h"]?"CHECKED":"",
				));
				break;
			case 6:
				$sc->read_template("sub_conference_rfp6.tpl");				
				foreach($this->countrys as $k => $v)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $v,
						"billing_country" => ($k == $sd["billing_country"])?"SELECTED":"",
					));
					$ctr .= $sc->parse("COUNTRY");
				}
				$sc->vars(array(
					"COUNTRY" => $ctr,
					"billing_company" => $sd["billing_company"],
					"billing_contact" => $sd["billing_contact"],
					"billing_street" => $sd["billing_street"],
					"billing_city" => $sd["billing_city"],
					"billing_zip" => $sd["billing_zip"],
					"billing_name" => $sd["billing_name"],
					"billing_phone_number" => $sd["billing_phone_number"],
					"billing_email" => $sd["billing_email"],
				));
				break;
			case "qa":
				$sc->read_template("sub_conference_qa.tpl");
				break;

			case 0:
			default:
				$sc->read_template("sub_conference.tpl");
				foreach(array("single", "double", "suite") as $loop_item)
				{
					for($i = 1; $i <= $c_obj->prop($loop_item."_count"); $i++)
					{
						$sc->vars(array(
							"value" => $i,
							"caption" => $i,
						));
						$room_options[strtoupper($loop_item)."_OPTION"] .= $sc->parse(strtoupper($loop_item)."_OPTION");
					}
				}
				$sc->vars($room_options);
				$sc->vars(array(
					"ATTENDES_JS" => $sc->parse("ATTENDES_JS"),
					"name" => $ob->prop("name"),
				));
				break;
		}

		$first = "DUMMY";
		if($no == 1)
		{
			$first = "FIRST";
		}
		elseif($no > 1 && $no < 7)
		{
			$first = "OTHER";
		}
		
		// yah bar
		for($i = 1; $i < 7; $i++)
		{
			$act = ($i == $no)?"ACT_":"";
			if($i == 1)
			{
				$this->vars(array(
					"step_nr" => $i,
				));
				$yah[] = $this->parse($act."YAH_FIRST_BTN");
			}
			elseif($i < 6)
			{
				$this->vars(array(
					"step_nr" => $i,
				));
				$yah[] = $this->parse($act."YAH_BTN");
			}
			else
			{
				$this->vars(array(
					"step_nr" => $i,
				));
				$yah[] = $this->parse($act."YAH_LAST_BTN");
			}
		}
		$this->vars(array(
			"YAH_BAR" => join("", $yah),
		));
		$yah_bar = $this->parse($first."_RFP_YAH");

		$this->vars(array(
			"url" => "orb.aw",
		));
		$submit = $this->parse($first."_RFP_SUBMIT");

		$act_evt_no = $_GET["act_evt_no"]?$_GET["act_evt_no"]:-1;

		$this->vars(array(
			"YAH_BAR" => join("", $yah),
			"sub_contents" => $sc->parse(),
			$first."_RFP_YAH" => $yah_bar,
			$first."_RFP_SUBMIT" => $submit,
			"reforb" => $this->mk_reforb("submit_back", array(
				"id" => $ob->id(),
				"current_sub" => $no,
				"act_event_no" => $act_evt_no,
			)),
		));
		return $this->parse();
	}

//-- methods --//
	/**
		@attrib params=name name=submit_back all_args=1
	**/
	function submit_back($arr)
	{
		$this->save_form_data($arr);
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=".($arr["current_sub"]-1);
	}
	
	/**
		@attrib params=name name=submit_forward all_args=1
	**/
	function submit_forward($arr)
	{
		$this->save_form_data($arr);
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=".($arr["current_sub"]+1);
	}

	/**
		@attrib params=name name=add_catering all_args=1
	**/
	function add_catering($arr)
	{
		$this->save_form_data($arr);
		$this->do_actions($arr, "add_catering");
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=".$arr["current_sub"];
	}

	/**
		@attrib params=name name=add_dates all_args=1
	**/
	function add_dates($arr)
	{
		$this->do_actions($arr, "add_dates");
		$this->save_form_data($arr);
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=".$arr["current_sub"];
	}

	/**
		@attrib params=name name=add_function all_args=1
	**/
	function add_function($arr)
	{
		$this->save_form_data($arr);
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=".$arr["current_sub"];
	}

	function do_actions($arr, $action = false)
	{
		$_GET = $GLOBALS["_GET"];
		$no = $_GET["sub"]?$_GET["sub"]:$arr["current_sub"];
		$data = aw_global_get("tmp_conference_data");
		$act = $action?$action:$_GET["action"];
		switch($no)
		{
			case 2:
				if($act == "remove")
				{
					unset($data["dates"][$_GET["id"]]);
				}
				elseif($act == "add_dates")
				{
					$val = $arr["sub"][$no];
					if(is_numeric($val["no_dates_to_add"]) && $val["no_dates_to_add"] > 0)
					{
						for($i=0;$i<$val["no_dates_to_add"];$i++)	
						{
							$data["dates"][] = array(
								"type" => "0"
							);
						}
					}
				}
				break;

			case 4:
				if($act == "add_first_catering")
				{
					$data["main_catering"] = array(
						0 => array()	
					);
				}
				if($act == "add_catering")
				{
					$data["main_catering"][] = array();
				}
				if($act == "remove")
				{
					unset($data["main_catering"][$_GET["id"]]);
				}
				break;
			case 5:
				if($act == "remove")
				{
					unset($data["additional_functions"][$_GET["id"]]);
				}
				break;
		}
		aw_session_set("tmp_conference_data", $data);
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=".$no;
	}

	function save_form_data($arr)
	{
		$form_data = $arr["sub"];
		$_get = $GLOBALS["_GET"];
		$data = aw_global_get("tmp_conference_data");
		foreach($form_data as $k => $val)
		{
			// new method
			switch($k)
			{
				case 0:
					$data["country"] = $val["country"];
					$data["attendees_no"] = $val["attendees_no"];
					$data["single_count"] = $val["single_count"];
					$data["double_count"] = $val["double_count"];
					$data["suite_count"] = $val["suite_count"];
					break;
				case 1:
					$data["function_name"] = $val["function_name"];
					$data["organisation_company"] = $val["organisation_company"];
					$data["dates"][0]["response_date"] = $val["response_date"];
					$data["dates"][0]["decision_date"] = $val["decision_date"];
					$data["dates"][0]["arrival_date"] = $val["arrival_date"];
					$data["dates"][0]["departure_date"] = $val["departure_date"];
					$data["dates"][0]["type"] = 0;
					$data["open_for_alternative_dates"] = $val["open_for_alternative_dates"];
					$data["accommondation_requirements"] = $val["accommondation_requirements"];
					break;
				case 2:

					$data["dates_are_flexible"] = $val["dates_are_flexible"];
					$data["meeting_pattern"] = $val["meeting_pattern"];
					$data["pattern_wday_from"] = $val["pattern_wday_from"];
					$data["pattern_wday_to"] = $val["pattern_wday_to"];
					$data["pattern_day"] = $val["pattern_day"];
					$data["date_comments"] = $val["date_comments"];
					foreach($val["table_rows"] as $row_no => $row_data)
					{
						$data["dates"][$row_no]["type"] = $row_data["date_type"];
						$data["dates"][$row_no]["arrival_date"] = $row_data["arrival_date"];
						$data["dates"][$row_no]["departure_date"] = $row_data["departure_date"];
					}

					break;
				case 3:
					$data["needs_rooms"] = $val["needs_rooms"];
					$data["single_count"] = $val["single_count"];
					$data["double_count"] = $val["double_count"];
					$data["suite_count"] = $val["suite_count"];
					$data["dates"][0]["arrival_date"] = $val["main_arrival_date"];
					$data["dates"][0]["departure_date"] = $val["main_departure_date"];
					break;
				case 4:
					$data["event_type_chooser"] = $val["event_type_chooser"];
					$data["event_type_select"] = $val["event_type_select"];
					$data["event_type_text"] = $val["event_type_text"];
					$data["delegates_no"] = $val["delegates_no"];
					$data["table_form"] = $val["table_form"];
					$data["tech"] = $val["tech"];
					$data["door_sign"] = $val["door_sign"];
					$data["persons_no"] = $val["persons_no"];
					$data["function_start_date"] = $val["function_start_date"];
					$data["function_start_time"] = $val["function_start_time"];
					$data["function_end_date"] = $val["function_end_date"];
					$data["function_end_time"] = $val["function_end_time"];
					$data["24h"] = $val["24h"];
					// actually some catering shit is missing
					$data["main_catering"] = $val["main_catering"];
					break;
				case 5:
					$additional_function["event_type_chooser"] = $val["event_type_chooser"];
					$additional_function["event_type_select"] = $val["event_type_select"];
					$additional_function["event_type_text"] = $val["event_type_text"];
					$additional_function["delegates_no"] = $val["delegates_no"];
					$additional_function["table_form"] = $val["table_form"];
					$additional_function["tech"] = $val["tech"];
					$additional_function["door_sign"] = $val["door_sign"];
					$additional_function["persons_no"] = $val["persons_no"];
					$additional_function["function_start_date"] = $val["function_start_date"];
					$additional_function["function_start_time"] = $val["function_start_time"];
					$additional_function["function_end_date"] = $val["function_end_date"];
					$additional_function["function_end_time"] = $val["function_end_time"];
					$additional_function["24h"] = $val["24h"];

					$additional_function["catering_type_chooser"] = $val["catering_type_chooser"];
					$additional_function["catering_type_select"] = $val["catering_type_select"];
					$additional_function["catering_type_text"] = $val["catering_type_text"];
					$additional_function["catering_start_time"] = $val["catering_start_time"];
					$additional_function["catering_end_time"] = $val["catering_end_time"];
					$no = $arr["act_event_no"];
					if($no < 0)
					{
						if($val["event_type_chooser"] || $val["delegates_no"] || is_array($val["tech"]) || $val["door_sign"] || $val["persons_no"] || $val["function_starting_date"] || $val["function_end_date"] || $val["catering_type_chooser"])
						{
							$data["additional_functions"][] = $additional_function;
						}
					}
					else
					{
						$data["additional_functions"][$no] = $additional_function;
					}
					break;
				case 6:
					$data["billing_company"] = $val["billing_company"];
					$data["billing_contact"] = $val["billing_contact"];
					$data["billing_street"] = $val["billing_street"];
					$data["billing_city"] = $val["billing_city"];
					$data["billing_zip"] = $val["billing_zip"];
					$data["billing_country"] = $val["billing_country"];
					$data["billing_name"] = $val["billing_name"];
					$data["billing_phone_number"] = $val["billing_phone_number"];
					$data["billing_email"] = $val["billing_email"];
					break;
			}
		}
		aw_session_set("tmp_conference_data", $data);
	}

	function get_form_data()
	{
		return aw_global_get("tmp_conference_data");
	}

}
?>
