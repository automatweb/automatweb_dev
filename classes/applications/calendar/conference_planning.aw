<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/conference_planning.aw,v 1.28 2007/01/05 12:11:31 tarvo Exp $
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

@property countries type=relpicker multiple=1 field=meta method=serialize reltype=RELTYPE_COUNTRIES
@caption Riigid

@property redir_doc type=relpicker field=meta method=serialize reltype=RELTYPE_REDIR_DOC
@caption Edasisuunamise dokument

@reltype REDIR_DOC value=2 clid=CL_DOCUMENT
@caption Edasisuunamise dokument

@reltype COUNTRIES value=1 clid=CL_CRM_COUNTRY
@caption Riik
*/

define(CONFIRM_ID, "confirm_submit_checkbox");
class conference_planning extends class_base
{
	function conference_planning()
	{
		$this->init(array(
			"tpldir" => "applications/conference_planning_webview",
			"clid" => CL_CONFERENCE_PLANNING
		));
		
		$this->wd = array(
			0 => t("Monday"),
			1 => t("Tuesday"),
			2 => t("Wednesday"),
			3 => t("Thursday"),
			4 => t("Friday"),
			5 => t("Saturday"),
			6 => t("Sunday"),
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
			if($ret = $this->do_actions($arr, $_GET["action"]))
			{
				header("Location:".$ret);
			}
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
		$no = $arr["sub"]?$arr["sub"]:$_GET["sub"];
		$sd = (count($arr["data"]) && is_Array($arr["data"]))?$arr["data"]:$this->get_form_data();
		switch($no)
		{
			case 1:
				$sc->read_template("sub_conference_rfp1.tpl");				
				$acc_req = ($sd["single_count"] > 0 || $sd["double_count"] > 0 || $sd["suite_count"] > 0 || $sd["accomondation_requirements"])?"CHECKED":"";
				$sc->vars(array(
					"function_name" => $sd["function_name"],
					"organisation_company" => $sd["organisation_company"],
					"response_date" => $sd["dates"][0]["response_date"],
					"decision_date" => $sd["dates"][0]["decision_date"],
					"arrival_date" => $sd["dates"][0]["arrival_date"],
					"departure_date" => $sd["dates"][0]["departure_date"],
					"open_for_alternative_dates" => ($sd["open_for_alternative_dates"])?"CHECKED":"",
					"accommondation_requirements" => $acc_req,
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
					for($i = 0; $i <= $c_obj->prop($loop_item."_count"); $i++)
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
					"needs_rooms" => $sd["accommondation_requirements"]?"CHECKED":"",
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
				$edit = false;
				$tmp = $sd["main_catering"];
				krsort($tmp);
				$catering_no = key($tmp) + 1;
				if($_GET["action"] == "edit" && strlen($_GET["id"]))
				{
					$catering_no = $_GET["id"];
					$edit = true;
				}

				foreach($sd["main_catering"] as $cat_no => $cat_data)
				{					
					$sc->vars(array(
						"catering_row_type" => ($cat_data["catering_type_chooser"] == 1)?$this->catering_types[$cat_data["catering_type_select"]]:$cat_data["catering_type_text"],
						"catering_row_start_time" => $cat_data["catering_start_time"],
						"catering_row_end_time" => $cat_data["catering_end_time"],
						"remove_url" => aw_ini_get("baseurl")."/".$ob->id()."?sub=".$no."&action=remove&id=".$cat_no,
						"edit_url" => aw_ini_get("baseurl")."/".$ob->id()."?sub=".$no."&action=edit&id=".$cat_no,
						"catering_row_attendees_no" => $cat_data["catering_attendees_no"],
					));
					$cat_rows .= $sc->parse("MAIN_CATERING_ROW");
				}

				// catering types for dropdown
				foreach($this->catering_types as $k => $capt)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $capt,
						"catering_type_select" => ($k == $sd["main_catering"][$catering_no]["catering_type_select"] && $edit)?"SELECTED":"",
					));
					$catering_types .= $sc->parse("CATERING_TYPE");
				}

				if($edit)
				{
					$sc->vars(array(
						"catering_id" => $_GET["id"],
						"catering_type_chooser_".$sd["main_catering"][$_GET["id"]]["catering_type_chooser"] => "CHECKED",
						"catering_type_text" => $sd["main_catering"][$_GET["id"]]["catering_type_text"],
						"catering_start_time" => $sd["main_catering"][$_GET["id"]]["catering_start_time"],
						"catering_end_time" => $sd["main_catering"][$_GET["id"]]["catering_end_time"],
						"catering_attendees_no" => $sd["main_catering"][$_GET["id"]]["catering_attendees_no"],
					));
				}

				$sc->vars(array(
					"EVT_TYPE" => $evt_type,
					"TABLE_FORM" => $tab_forms,
					"TECH_EQUIP" => $tech,
					"CATERING_TYPE" => $catering_types,
					"MAIN_CATERING_ROW" => $cat_rows,
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
					"catering_no" => $catering_no,
				));
				break;
			case 5:
				//arr($sd);
				$sc->read_template("sub_conference_rfp5.tpl");				
				$c_inst = get_instance(CL_CONFERENCE);
				$c_types = $c_inst->conference_types();
				$values = array();
				if(strlen($_GET["act_evt_no"]))
				{
					$values = $sd["additional_functions"][$_GET["act_evt_no"]];
					$cat_values = $values["catering"];
					if(strlen($_GET["act_cat_no"]))
					{
						$act_cat = $cat_values[$_GET["act_cat_no"]];
					}
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
						"catering_type_select" => ($k == $act_cat["catering_type_select"])?"SELECTED":"",
					));
					$catering_form_select .= $sc->parse("CATERING_TYPE");
				}
				// table rows
				//$d = $this->get_form_data();
				foreach($sd["additional_functions"] as $id => $data)
				{
					$sc->vars(array(
						"caption" => ($data["event_type_chooser"] == 1)?$c_types[$data["event_type_select"]]:$data["event_type_text"],
						"remove_url" => aw_ini_get("baseurl")."/".$ob->id()."?sub=".$no."&action=remove&evt=".$id,
						"edit_url" => aw_ini_get("baseurl")."/".$ob->id()."?sub=".$no."&act_evt_no=".$id,
					));
					$rows .= $sc->parse("ROW");
				}

				// catering tab 
				foreach($cat_values as $cat_no => $catering)
				{
					$sc->vars(array(
						"cat_type" => ($catering["catering_type_chooser"] == 2)?$catering["catering_type_text"]:$this->catering_types[$catering["catering_type_select"]],
						"cat_starttime" => $catering["catering_start_time"],
						"cat_endtime" => $catering["catering_end_time"],
						"cat_attendee_no" =>$catering["catering_attendee_no"],
						"cat_remove_url" => aw_ini_get("baseurl")."/".$ob->id()."?sub=".$no."&action=remove&evt=".$_REQUEST["act_evt_no"]."&cat=".$cat_no,
						"cat_edit_url" => aw_ini_get("baseurl")."/".$ob->id()."?sub=".$no."&act_evt_no=".$_REQUEST["act_evt_no"]."&act_cat_no=".$cat_no,
					));
					$cat_rows .= $sc->parse("CATERING_ROW");
				}
				$sc->vars(array(
					"CATERING_ROW" => $cat_rows,
				));
				$additional_catering = $sc->parse("ADDITIONAL_CATERING");
				$sc->vars(array(
					"TECH_EQUIP" => $tech,
					"TABLE_FORM" => $tform,
					"CATERING_TYPE" => $catering_form_select,
					"ADDITIONAL_CATERING" => $additional_catering,
					"EVT_TYPE" => $evt_type,
					"ROW" => $rows,
					"event_type_chooser_".$values["event_type_chooser"] => "CHECKED",
					"event_type_text" => $values["event_type_text"],
					"delegates_no" => $values["delegates_no"],
					"persons_no" => $values["persons_no"],
					"door_sign" => $values["door_sign"],
					"function_start_date" => $values["function_start_date"],
					"function_start_time" => $values["function_start_time"],
					"function_end_date" => $values["function_end_date"],
					"function_end_time" => $values["function_end_time"],
					"24h" => $values["24h"]?"CHECKED":"",
					"catering_type_chooser_".$act_cat["catering_type_chooser"] => "CHECKED",
					"catering_type_text" => $act_cat["catering_type_text"],
					"catering_start_time" => $act_cat["catering_start_time"],
					"catering_end_time" => $act_cat["catering_end_time"],
				));
				break;
			case 6:
				$sc->read_template("sub_conference_rfp6.tpl");				
				$addr = get_instance(CL_CRM_ADDRESS);
				foreach($addr->get_country_list() as $k => $v)
				{
					$sc->vars(array(
						"value" => $k,
						"caption" => $v,
						"billing_country" => ($k == $sd["billing_country"])?"SELECTED":"",
					));
					$ctr .= $sc->parse("COUNTRY");
				}
				// search !!!
				$altern_dates[] = array(
					"start" => $this->_gen_to_timestamp($sd["function_start_date"], $sd["function_start_time"]),
					"end" => $this->_gen_to_timestamp($sd["function_end_date"], $sd["function_end_time"]),
					"persons" => $sd["persons_no"],
				);
				foreach($sd["additional_functions"] as $fun)
				{
					$altern_dates[] = array(
						"start" => $this->_gen_to_timestamp($fun["function_start_date"], $fun["function_start_time"]),
						"end" => $this->_gen_to_timestamp($fun["function_end_date"], $fun["function_end_time"]),
						"persons" => $fun["persons_no"],
					);
				}
				$res = $this->all_mighty_search_engine(array(
					"single_rooms" => ($sd["needs_rooms"])?$sd["single_count"]:false,
					"double_rooms" => ($sd["needs_rooms"])?$sd["double_count"]:false,
					"suites" => ($sd["needs_rooms"])?$sd["suite_count"]:false,
					"attendees_count" => $sd["attendees_no"],
					"dates" => $altern_dates,
				));
				$loc_inst = get_instance(CL_LOCATION);
				$img_inst = get_instance(CL_IMAGE);
				foreach($res as $loc_id => $data)
				{
					$imgs = $loc_inst->get_images($loc_id);
					foreach($imgs as $img)
					{
						$sc->vars(array(
							"IMG_".$img->ord() => html::img(array(
								"url" => $img_inst->get_url_by_id($img->id()),
							)),
						));
					}
					$loc = obj($loc_id);
					$sc->vars(array(
						"caption" => $loc->name(),
						"address" => $loc->prop_str("address"),
						"single_count" => $loc->prop("single_count"),
						"double_count" => $loc->prop("double_count"),
						"suite_count" => $loc->prop("suite_count"),
						"value" => $loc_id,
						"selected" => in_array($loc_id, $sd["selected_search_result"])?"CHECKED":"",
						"urgent" => $sd["urgent"]?"CHECKED":"",
					));
					$s_results .= $sc->parse("SEARCH_RESULT");
					$hid_rows .= html::hidden(array(
						"name" => "sub[6][all_search][".$loc_id."]", 
						"value" => 1,
					));
					foreach($data["errors"] as $err)
					{
						$sc->vars(array(
							"caption" => $err,
						));
						$s_results .= $sc->parse("SEARCH_RESULT_ERROR");
					}
				}
				$u = get_instance(CL_USER);
				$org = $u->get_current_company();
				$per = obj($u->get_current_person());
				$addr = $per->prop("address");
				$addr = $this->can("view", $addr)?obj($addr):false;
				$def_ph = $per->prop("phone");
				$def_ph = $this->can("view", $def_ph)?obj($def_ph):false;
				$def_em = $per->prop("email");
				$def_em = $this->can("view", $def_em)?obj($def_em):false;
				$sc->vars(array(
					"COUNTRY" => $ctr,
					"SEARCH_RESULT" => $s_results,
					"billing_company" => $sd["billing_company"]?$sd["billing_company"]:call_user_func(array(obj($org), "name")),
					"billing_contact" => $sd["billing_contact"]?$sd["billing_contact"]:$per->name(),
					"billing_street" => $sd["billing_street"]?$sd["billing_street"]:($addr?$addr->prop("aadress"):""),
					"billing_city" => $sd["billing_city"]?$sd["billing_city"]:($addr?$addr->prop_str("linn"):""),
					"billing_zip" => $sd["billing_zip"]?$sd["billing_zip"]:($addr?$addr->prop("postiindeks"):""),
					"billing_name" => $sd["billing_name"]?$sd["billing_name"]:$per->name(),
					"billing_phone_number" => $sd["billing_phone_number"]?$sd["billing_phone_number"]:($def_ph?$def_ph->name():""),
					"billing_email" => $sd["billing_email"]?$sd["billing_email"]:($def_em?$def_em->name():""),
					"all_search_results" => $hid_rows,
				));
				break;
			case "7":
				$sc->read_template("sub_conference_rfp7.tpl");
				// #0
				$tmp = $this->can("view", $sd["country"])?obj($sd["country"]):false;
				$sc->vars(array(
					"country" => $tmp?$tmp->trans_get_val("name"):"",//call_user_func(array(obj($sd["country"]), "name")),
				));
				// #1
				$sc->vars(array(
					"function_name" => $sd["function_name"],
					"organisation_company" => $sd["organisation_company"],
					"response_date" => $sd["dates"][0]["response_date"],
					"decision_date" => $sd["dates"][0]["decision_date"],
					"arrival_date" => $sd["dates"][0]["arrival_date"],
					"departure_date" => $sd["dates"][0]["departure_date"],
					"open_for_alternative_dates" => ($sd["open_for_alternative_dates"])?t("Yes"):t("No"),
					"accommondation_requirements" => ($sd["accommondation_requirements"])?t("Yes"):t("No"),
				));
				// #2
				// tablerows
				foreach($sd["dates"] as $row_no => $date)
				{
					$sc->vars(array(
						"date_type" => ($date["type"] == 0)?t("Normal"):t("Alternative"),
						"arrival_date" => $date["arrival_date"],
						"departure_date" => $date["departure_date"],
					));
					$dates_rows .= $sc->parse("DATES_ROW");
				}
			
				// flexible dates
				if($sd["dates_are_flexible"])
				{
					if($sd["meeting_pattern"] == 1)
					{
						$cont = $sc->parse("PATTERN_NO_APP");
					}
					elseif($sd["meeting_pattern"] == 2)
					{
						$sc->vars(array(
							"wday_from" => $this->wd[$sd["pattern_wday_from"]],
							"wday_to" => $this->wd[$sd["pattern_wday_to"]],
						));
						$cont = $sc->parse("PATTERN_WDAY");
					}
					elseif($sd["meeting_pattern"] == 3)
					{
						$sc->vars(array(
							"days" => $sd["pattern_day"],
						));
						$cont = $sc->parse("PATTERN_DAYS");
					}
					$sc->vars(array(
						"PATTERN_NO_APP" => $cont,
					));
					$flexible_dates = $sc->parse("FLEXIBLE_DATES");
				}

				$sc->vars(array(
					"DATES_ROW" => $dates_rows,
					"date_comments" => $sd["date_comments"],
					"FLEXIBLE_DATES" => $flexible_dates,
				));
				// #3
				if($sd["needs_rooms"])
				{
					$sc->vars(array(
						"single_count" => $sd["single_count"],
						"double_count" => $sd["double_count"],
						"suite_count" => $sd["suite_count"],
						"arrival_date" => $sd["dates"][0]["arrival_date"],
						"departure_date" => $sd["dates"][0]["departure_date"],
					));
					$sc->vars(array(
						"NEEDS_ROOMS" => $sc->parse("NEEDS_ROOMS"),
					));
				}
				// # 4
				$c_inst = get_instance(CL_CONFERENCE);
				$conf_types = $c_inst->conference_types();
				$evt_type = ($sd["event_type_chooser"] == 1)?$conf_types[$sd["event_type_select"]]:$sd["event_type_text"];
				
				foreach($sd["tech"] as $k => $capt)
				{
					$sc->vars(array("value" => $this->tech_equip[$k]));
					$tech_equip .= $sc->parse("MAIN_TECH_EQUIP");
				}
				foreach($sd["main_catering"] as $k => $data)
				{
					if(!count($data))
					{
						continue;
					}
					$cat_type = ($data["catering_type_chooser"] == 1)?$this->catering_types[$data["catering_type_select"]]:$data["catering_type_text"];
					$sc->vars(array(
						"type" => $cat_type,
						"start_time" => $data["catering_start_time"],
						"end_time" => $data["catering_end_time"],
						"attendee_no" => $data["catering_attendees_no"],
					));
					$rows .= $sc->parse("TIMES_ROW");
				}
				$sc->vars(array("TIMES_ROW" => $rows));
				$main_catering = $sc->parse("MAIN_CATERING");
				$sc->vars(array(
					"main_event_type" => $evt_type,
					"main_delegates_no" => $sd["delegates_no"],
					"main_table_form" => $this->table_forms[$sd["table_form"]],
					"MAIN_TECH_EQUIP" => $tech_equip,
					"MAIN_CATERING" => $cats,
					"main_door_sign" => $sd["door_sign"],
					"main_person_no" => $sd["persons_no"],
					"main_start" => $sd["function_start_date"]." ".$sd["function_start_time"],
					"main_end" => $sd["function_end_date"]." ".$sd["function_end_time"],
					"main_24h" => $sd["24h"]?t("Yes"):t("No"),
					"MAIN_CATERING" => $main_catering,
				));
				unset($rows);
				// #5
				//arr($sd["additional_functions"]);
				$c_inst = get_instance(CL_CONFERENCE);
				$conf_types = $c_inst->conference_types();
				foreach($sd["additional_functions"] as $k => $data)
				{
					if(!count($data))
					{
						continue;
					}
					$cat_type = ($data["event_type_chooser"] == 1)?$conf_types[$data["event_type_select"]]:$data["event_type_text"];
					if(count($data["catering"]))
					{
						unset($caterings);
						foreach($data["catering"] as $catering)
						{
							$sc->vars(array(
								"type" => ($_t = ($catering["catering_type_chooser"] == 1)?$this->catering_types[$catering["catering_type_select"]]:$data["catering_type_text"])?$_t:t("-"),
								"start_time" => ($_t = $catering["catering_start_time"])?$_t:t("-"),
								"end_time" => ($_t = $catering["catering_end_time"])?$_t:t("-"),
								"attendee_no" => ($_t = $catering["catering_attendee_no"])?$_t:t("-"),
							));
							$caterings .= $sc->parse("ADD_FUNCTION_CATERING_ROW");
						}
						$sc->vars(array(
							"ADD_FUNCTION_CATERING_ROW" => $caterings,
						));
						$add_fun_cat = $sc->parse("ADD_FUNCTION_CATERING");
					}
					else
					{
						unset($add_fun_cat);
					}
					$sc->vars(array(
						"type" => ($_t = $cat_type)?$_t:t("-"),
						"start_time" => ($_t = trim($data["function_start_date"]." ".$data["function_start_time"]))?$_t:t("-"),
						"end_time" => ($_t = trim($data["function_end_date"]." ".$data["function_end_time"]))?$_t:t("-"),
						"attendee_no" => ($_t = $data["persons_no"])?$_t:t("-"),
						"ADD_FUNCTION_CATERING" => $add_fun_cat,
					));
					$rows .= $sc->parse("ADD_FUNCTION_ROW");
				}
				$sc->vars(array("ADD_FUNCTION_ROW" => $rows));
				$add_functions = $sc->parse("ADDITIONAL_FUNCTIONS");
				$sc->vars(array(
					"ADDITIONAL_FUNCTIONS" => $add_functions,
				));

				// #6
				$addr = get_instance(CL_CRM_ADDRESS);
				$countries = $addr->get_country_list();
				$sc->vars(array(
					"billing_company" => $sd["billing_company"],
					"billing_contact" => $sd["billing_contact"],
					"billing_street" => $sd["billing_street"],
					"billing_city" => $sd["billing_city"],
					"billing_zip" => $sd["billing_zip"],
					"billing_country" => $countries[$sd["billing_country"]],
					"billing_name" => $sd["billing_name"],
					"billing_phone_number" => $sd["billing_phone_number"],
					"billing_email" => $sd["billing_email"],
				));
				// search res
				unset($rows);
				foreach($sd["selected_search_result"] as $location)
				{
					$o = obj($location);
					$sc->vars(array(
						"caption" => $o->name(),
						"address" => $o->prop_str("address"),
						"single_count" => $o->prop("single_count"),
						"double_count" => $o->prop("double_count"),
						"suite_count" => $o->prop("suite_count"),
					));
					$rows .= $sc->parse("SEARCH_RESULT");
				}
				$sc->vars(array(
					"confirm_ch_id" => CONFIRM_ID,
				));
				$confirm = $sc->parse("CONFIRMATION");
				$sc->vars(array(
					"SEARCH_RESULT" => $rows,
					"CONFIRMATION" => $arr["sub_contents_only"]?"":$confirm,
				));
				break;
			case "qa":
				$sc->read_template("sub_conference_qa.tpl");
				$sc->vars(array(
					"salutation_".$sd["user_salutation"] => "SELECTED",
					"firstname" => $sd["user_firstname"],
					"lastname" => $sd["user_lastname"],
					"company_assocation" => $sd["user_company_assocation"],
					"title" => $sd["user_title"],
					"phone_number" => $sd["user_phone_number"],
					"fax_number" => $sd["user_fax_number"],
					"email" => $sd["user_email"],
					"contact_preference_".$sd["user_contact_preference"] => "SELECTED",
				));
				break;

			case 0:
			default:
				$sc->read_template("sub_conference.tpl");
				foreach($this->get_countries($c_obj->id()) as $cnt)
				{
					if(!is_oid($cnt))
					{
						break;
					}
					$o = obj($cnt);
					$sc->vars(array(
						"value" => $cnt,
						"caption" => $o->trans_get_val("name"), 
						"country" => ($cnt == $sd["country"])?"SELECTED":"",
					));
					$countries .= $sc->parse("COUNTRY");
				}
				foreach(array("single", "double", "suite") as $loop_item)
				{
					for($i = 0; $i <= $c_obj->prop($loop_item."_count"); $i++)
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
					"COUNTRY" => $countries,
				));
				break;
		}

		// this is for wierd cases, only once is this used. from rfp_manager ..
		if($arr["sub_contents_only"])
		{
			return $sc->parse();
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
		elseif($no == 7)
		{
			$first = "LAST";
		}
		$yah_caption = array(
			1 => "",
			2 => t("Alternative dates"),
			3 => t("Accomondation"),
			4 => t("Main Event"),
			5 => t("Additional Events"),
			6 => t("Booking Details"),
			7 => t("Confirmation"),
		);
		// yah bar
		for($i = 1; $i <= 7; $i++)
		{
			$act = ($i == $no)?"ACT_":"";
			$href = (strlen($no) &&  $i < $no)?"_HREF":"";
			if($i == 1)
			{
				$this->vars(array(
					"step_nr" => $i,
					"caption" => strlen($act)?$yah_caption[$i]:"",
					"url" => aw_ini_get("baseurl")."/".$arr["id"]."?sub=".$i,
				));
				$yah[] = $this->parse($act."YAH_FIRST_BTN".$href);
			}
			elseif($i <= 7 && $no != ($i-1))
			{
				unset($last);
				if($i == 7)
				{
					$last = "_LAST";
				}
				$this->vars(array(
					"step_nr" => $i,
					"caption" => $yah_caption[$i],
					"url" => aw_ini_get("baseurl")."/".$arr["id"]."?sub=".$i,
				));
				$yah[] = $this->parse($act."YAH".$last."_BTN".$href);
			}
			elseif($no == 6)
			{
				$this->vars(array(
					"step_nr" => $i,
					"caption" => strlen($act)?$yah_caption[$i]:"",
				));
				$yah[] = $this->parse("YAH_LAST_BTN_AFTER");
			}
			elseif($no != $i-1)
			{
				$this->vars(array(
					"step_nr" => $i,
					"caption" => strlen($act)?$yah_caption[$i]:"",
				));
				$yah[] = $this->parse($act."YAH_LAST_BTN".$href);
			}
			if(strlen($act) && $no != 6 && $i < 7)
			{
				$this->vars(array(
					"step_nr" => ($i+1),
					"caption" => $yah_caption[$i+1],
				));
				$yah[] = $this->parse("YAH_BTN_AFTER");
			}
		}
		$this->vars(array(
			"YAH_BAR" => join("", $yah),
		));
		$first_for_yah = ($first == "LAST")?"OTHER":$first;
		$yah_bar = $this->parse($first_for_yah."_RFP_YAH");

		$this->vars(array(
			"url" => "orb.aw",
		));
		$submit = $this->parse($first."_RFP_SUBMIT");

		$act_evt_no = strlen($_GET["act_evt_no"])?$_GET["act_evt_no"]:-1;
		$act_cat_no = strlen($_GET["act_cat_no"])?$_GET["act_cat_no"]:-1;

		$this->vars(array(
			"YAH_BAR" => join("", $yah),
			"confirm_ch_id" => CONFIRM_ID,
			"sub_contents" => $sc->parse(),
			$first_for_yah."_RFP_YAH" => $yah_bar,
			$first."_RFP_SUBMIT" => $submit,
			"action" => aw_ini_get("baseurl"),
			"reforb" => $this->mk_reforb("submit_back", array(
				"id" => $ob->id(),
				"current_sub" => $no,
				"act_event_no" => $act_evt_no,
				"act_cat_no" => $act_cat_no,
				"conference_planner" => $c_obj->id(),
			)),
		));
		return $this->parse();
	}

//-- methods --//
	/**
		@attrib params=name name=submit_back all_args=1 nologin=1
	**/
	function submit_back($arr)
	{
		$this->save_form_data($arr);
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=".($arr["current_sub"]-1);
	}
	
	/**
		@attrib params=name name=submit_forward all_args=1 nologin=1
	**/
	function submit_forward($arr)
	{
		//arr($arr);
		$this->save_form_data($arr);
		if($arr["current_sub"] == 0 && strlen(trim(aw_global_get("uid"))) == 0)
		{
			// in case the user hasn't logged in yet.. 
			return aw_ini_get("baseurl")."/".$arr["id"]."?sub=qa";
		}
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=".($arr["current_sub"]+1);
	}

	
	/**
		@attrib params=name name=submit_final all_args=1
	**/
	function submit_final($arr)
	{
		$data = $this->get_form_data();
		//arr($data);
		//die();
		$obj = new object();
		$obj->set_class_id(CL_RFP);
		$obj->set_parent($arr["conference_planner"]);
		$obj->save();
		$users = get_instance("users");
		$obj->set_name($data["function_name"]);
		$obj->set_prop("submitter", $users->get_oid_for_uid(aw_global_get("uid")));
		$obj->set_prop("contact_preference", $data["user_contact_preference"]);
		$obj->set_prop("country", $data["country"]);
		$obj->set_prop("organisation", $data["organisation_company"]);
		$obj->set_prop("function_name", $data["function_name"]);
		$obj->set_prop("attendees_no", $data["attendees_no"]);
		$obj->set_prop("response_date", $data["dates"][0]["response_date"]);
		$obj->set_prop("decision_date", $data["dates"][0]["decision_date"]);
		$obj->set_prop("arrival_date", $data["dates"][0]["arrival_date"]);
		$obj->set_prop("departure_date", $data["dates"][0]["departure_date"]);
		$obj->set_prop("open_for_alternative_dates", $data["open_for_alternative_dates"]?1:0);
		$obj->set_prop("accommondation_requirements", $data["accommondation_requirements"]?1:0);
		$obj->set_prop("needs_rooms", $data["needs_rooms"]?1:0);
		$obj->set_prop("single_rooms", $data["single_count"]);
		$obj->set_prop("double_rooms", $data["double_count"]);
		$obj->set_prop("suites", $data["suite_count"]);
		$obj->set_prop("date_comments", $data["date_comments"]);
	/*
	*/
		$obj->set_prop("dates_are_flexible", $data["dates_are_flexible"]?1:0);
		$obj->set_prop("meeting_pattern", $data["meeting_pattern"]);
		$obj->set_prop("pattern_wday_from", $data["pattern_wday_from"]);
		$obj->set_prop("pattern_wday_to", $data["pattern_wday_to"]);
		$obj->set_prop("pattern_days", $data["pattern_days"]);

		if($data["dates_are_flexible"])
		{
			if($data["meeting_pattern"] == 1)
			{
				$flex = t("Not Applicable");
			}
			elseif($data["meeting_pattern"] == 2)
			{
				$flex = sprintf(t("From %s to %s"), $this->wd[$data["pattern_wday_from"]], $this->wd[$data["pattern_wday_to"]]);
			}
			elseif($data["meeting_pattern"] == 3)
			{
				$flex = sprintf(t("%s days"), $data["pattern_days"]);
			}
			else
			{
				$flex = t("No");
			}
		}
		else
		{
			$flex = t("No");
		}

		$obj->set_prop("flexible_dates", $flex);
		// main fun

		$conf_inst = get_instance(CL_CONFERENCE);
		$evt_type = $conf_inst->conference_types();
		$tmptech = array();
		foreach($data["tech"] as $k => $pnt)
		{
			$tmptech[$k] = $this->tech_equip[$k];
		}
		$obj->set_prop("tech_equip", aw_serialize($tmptech, SERIALIZE_NATIVE));
		$obj->set_prop("main_catering", aw_serialize($data["main_catering"], SERIALIZE_NATIVE));

		$tmpcatering = array();
		foreach($data["main_catering"] as $k => $pnt)
		{
			$tmpcatering[] = array(
				"type" => ($pnt["catering_type_chooser"] == 1)?$this->catering_types[$pnt["catering_type_select"]]:$pnt["catering_type_text"],
				"start" => $this->_gen_to_timestamp(false, $pnt["catering_start_time"]),
				"end" => $this->_gen_to_timestamp(false, $pnt["catering_end_time"]),
				"attendees" => $pnt["catering_attendees_no"],
			);
		}

		$obj->set_prop("event_type", ($data["event_type_chooser"] == 1)?$evt_type[$data["event_type_select"]]:$data["event_type_text"]);
		// data separately also
		$obj->set_prop("event_type_chooser", $data["event_type_chooser"]);
		$obj->set_prop("event_type_select", $data["event_type_select"]);
		$obj->set_prop("event_type_text", $data["event_type_text"]);
		//
		$obj->set_prop("delegates_no", $data["delegates_no"]);
		$obj->set_prop("table_form", $this->table_forms[$data["table_form"]]);
		$obj->set_prop("tech", join(", ", $tmptech));
		$obj->set_prop("door_sign", $data["door_sign"]);
		$obj->set_prop("person_no", $data["persons_no"]);
		// duplicated raw_data again
		$obj->set_prop("table_form_raw", $data["table_form"]);
		$obj->set_prop("start_time_raw", $data["function_start_time"]);
		$obj->set_prop("start_date_raw", $data["function_start_date"]);
		$obj->set_prop("end_time_raw", $data["function_end_time"]);
		$obj->set_prop("end_date_raw", $data["function_end_date"]);

		$obj->set_prop("start_date", $this->_gen_to_timestamp($data["function_start_date"], $data["function_start_time"]));
		$obj->set_prop("end_date", $this->_gen_to_timestamp($data["function_end_date"], $data["function_end_time"]));
		$obj->set_prop("24h", $data["24h"]?1:0);

		$obj->set_prop("catering_for_main", aw_serialize($tmpcatering, SERIALIZE_NATIVE));

		// additional dates
		unset($data["dates"][0]);
		$obj->set_prop("additional_dates_raw", aw_serialize($data["dates"], SERIALIZE_NATIVE));
		if(count($data["dates"]))
		{
			foreach($data["dates"] as $tmp)
			{
				$tmpdates[] = array(
					"type" => ($tmp["type"] == 0)?t("Normal"):t("Alternative"),
					"start" =>  $this->_gen_to_timestamp($tmp["arrival_date"]),
					"end" => $this->_gen_to_timestamp($tmp["departure_date"]),
				);
			}
			$obj->set_prop("additional_dates", aw_serialize($tmpdates, SERIALIZE_NATIVE));
		}
		// additional functions
		$obj->set_prop("additional_functions_raw", aw_serialize($data["additional_functions"], SERIALIZE_NATIVE));
		foreach($data["additional_functions"] as $tmp)
		{
			$tmptech = array();
			foreach($tmp["tech"] as $k => $pnt)
			{
				$tmptech[] = $this->tech_equip[$k];
			}
			$cats = array();
			foreach($tmp["catering"] as $cat)
			{
				$cats[] = array(
					"catering_type" => ($cat["catering_type_chooser"] == 1)?$this->catering_types[$cat["catering_type_select"]]:$cat["catering_type_text"],
					"catering_start" => $cat["catering_start_time"],
					"catering_end" => $cat["catering_end_time"],
				);
			}
			$tmpfunctions[] = array(
				"type" => ($tmp["event_type_chooser"] == 1)?$evt_type[$tmp["event_type_select"]]:$tmp["event_type_text"],
				"delegates_no" => $tmp["delegates_no"],
				"table_form" => $this->table_forms[$tmp["table_form"]],
				"tech" => join(", ", $tmptech),
				"door_sign" => $tmp["door_sign"],
				"persons_no" => $tmp["persons_no"],
				"start" => $tmp["function_start_date"]." ".$tmp["function_start_time"],
				"end" => $tmp["function_end_date"]." ".$tmp["function_end_time"],
				"24h" => ($tmp["24h"])?t("Yes"):t("No"),
				"catering" => $cats,
			);
		}
		$obj->set_prop("additional_functions", aw_serialize($tmpfunctions, SERIALIZE_NATIVE));


		// billing

		$obj->set_prop("billing_company", $data["billing_company"]);
		$obj->set_prop("billing_contact", $data["billing_contact"]);
		$obj->set_prop("billing_street", $data["billing_street"]);
		$obj->set_prop("billing_city", $data["billing_city"]);
		$obj->set_prop("billing_zip", $data["billing_zip"]);
		$obj->set_prop("billing_country", $data["billing_country"]);
		$obj->set_prop("billing_name", $data["billing_name"]);
		$obj->set_prop("billing_phone_number", $data["billing_phone_number"]);
		$obj->set_prop("billing_email", $data["billing_email"]);
		$obj->set_prop("urgent", $data["urgent"]?1:0);

		// search_results
		$tmpsearch = array();
		foreach($data["all_search_results"] as $id)
		{
			$tmpsearch[] = array(
				"location" => call_user_func(array(obj($id), "name")),
				"selected" => in_array($id ,$data["selected_search_result"])?1:0,
			);
		}
		$obj->set_prop("all_search_results", aw_serialize($data["all_search_results"], SERIALIZE_NATIVE));
		$obj->set_prop("selected_search_result", aw_serialize($data["selected_search_result"], SERIALIZE_NATIVE));


		$obj->set_prop("search_result", aw_serialize($tmpsearch, SERIALIZE_NATIVE));
		$obj->save();
		$url = aw_ini_get("baseurl");
		$c_obj = obj($arr["conference_planner"]);
		$url .= ($c_obj->prop("redir_doc"))?"/".$c_obj->prop("redir_doc"):"";
		aw_session_set("tmp_conference_data", array());
		return $url;
	}

	/**
		@attrib params=name name=submit_user_data all_args=1 nologin=1
	**/
	function submit_user_data($arr)
	{
		if(strlen(trim(aw_global_get("uid"))))
		{
			return aw_ini_get("baseurl")."/".$arr["id"]."?sub=1";
		}
		$data = $arr["sub"]["qa"];
		$this->save_form_data($arr);
		if(!strlen($data["email"]) || !strstr($data["email"], "@"))
		{
			return aw_ini_get("baseurl")."/".$arr["id"]."?sub=qa";
		}
		$us = get_instance(CL_USER);
		classload("users");
		$password = substr(gen_uniq_id(),0,8);

		$user = $us->add_user(array(
			"uid" => $data["email"],
			"email" => $data["email"],
			"password" => $password,
			"real_name" => $data["firstname"]." ".$data["lastname"],
		));

		$person_obj = new object();
		$person_obj->set_class_id(145);
		$person_obj->set_parent(2);
		$person_obj->set_name($data["firstname"]." ".$data["lastname"]);
		$person_obj->set_prop("firstname",$data["firstname"]);
		$person_obj->set_prop("lastname",$data["lastname"]);
		$person_obj->set_prop("title", ($data["salutation"]-1));
		$person_obj->save_new();

		if($data["company_assoction"])
		{
			$org = new object();
			$org->set_class_id(CL_CRM_COMPANY);
			$org->set_parent($person_obj->id());
			$org->set_name($data["company_assocation"]);
			$org->save();
			$person_obj->connect(array(
				"to" => $org->id(),
				"type" => "RELTYPE_WORK",
			));
			$person_obj->set_prop("work_contact", $org->id());
		}
		
		$phone = new object();
		$phone->set_class_id(219);
		$phone->set_parent($person_obj->id());
		$phone->set_name($data["phone_number"]);
		$phone->set_prop("type", "work");
		$phone->save();

		$fax = new object();
		$fax->set_class_id(219);
		$fax->set_parent($person_obj->id());
		$fax->set_name($data["fax_number"]);
		$fax->set_prop("type", "fax");
		$fax->save();

		$email = new object();
		$email->set_class_id(73);
		$email->set_parent($person_obj->id());
		$email->set_name($data["email"]);
		$email->set_prop("mail", $data["email"]);
		$email->save();

		$person_obj->connect(array(
			"to" => $phone->id(),
			"type" => "RELTYPE_PHONE",
		));
		$person_obj->connect(array(
			"to" => $fax->id(),
			"type" => "RELTYPE_PHONE",
		));
		$person_obj->connect(array(
			"to" => $email->id(),
			"type" => "RELTYPE_EMAIL",
		));
		// i have to create company also :S
		// we are logging in
		$hash = gen_uniq_id();
		$q = "INSERT INTO user_hashes (hash, hash_time, uid) VALUES('".$hash."','".(time()+60)."','".$data["email"]."')";
		$res = $this->db_query($q);
		$users =get_instance("users");
		return $users->login(array(
			"hash" => $hash ,
			"uid" => $data["email"],
			"return_url" => aw_ini_get("baseurl")."/".$arr["id"]."?sub=1",
		));
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=1";
	}

	/**
		@attrib params=name name=add_catering all_args=1
	**/
	function add_catering($arr)
	{
		$this->save_form_data($arr);
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

	/**
		@attrib params=name name=add_fun_catering all_args=1
	**/
	function add_fun_catering($arr)
	{
		$this->save_form_data($arr);
		return aw_ini_get("baseurl")."/".$arr["id"]."?sub=".$arr["current_sub"]."&act_evt_no=".$arr["act_event_no"];
	}

	function do_actions($arr, $action = false)
	{
		$_GET = $GLOBALS["_GET"];
		$no = $_GET["sub"]?$_GET["sub"]:$arr["current_sub"];
		$data = aw_global_get("tmp_conference_data");
		$act = $action?$action:$_GET["action"];
		$ret = false;
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
				if($act == "remove")
				{
					unset($data["main_catering"][$_GET["id"]]);
				}
				break;
			case 5:
				if($act == "remove")
				{
					if(strlen($_GET["evt"]) && strlen($_GET["cat"]))
					{
						unset($data["additional_functions"][$_GET["evt"]]["catering"][$_GET["cat"]]);
						$ret = aw_ini_get("baseurl")."/".$arr["id"]."?sub=".$no."&act_evt_no=".$_GET["evt"];
					}
					elseif(strlen($_GET["evt"]) && !strlen($_GET["cat"]))
					{
						unset($data["additional_functions"][$_GET["evt"]]);
					}
				}
				break;
		}
		aw_session_set("tmp_conference_data", $data);
		return $ret;
	}

	function save_form_data($arr)
	{
		$form_data = $arr["sub"];
		$_get = $GLOBALS["_GET"];
		$data = $this->get_form_data();
		foreach($form_data as $k => $val)
		{
			// new method
			switch($k)
			{
				case "0":
					$data["country"] = $val["country"];
					$data["delegates_no"] = $val["attendees_no"];
					$data["single_count"] = $val["single_count"];
					$data["double_count"] = $val["double_count"];
					$data["suite_count"] = $val["suite_count"];
					break;
				case "qa":
					$data["user_firstname"] = $val["firstname"];
					$data["user_lastname"] = $val["lastname"];
					$data["user_salutation"] = $val["salutation"];
					$data["user_company_assocation"] = $val["company_assocation"];
					$data["user_title"] = $val["title"];
					$data["user_phone_number"] = $val["phone_number"];
					$data["user_fax_number"] = $val["fax_number"];
					$data["user_email"] = $val["email"];
					$data["user_contact_preference"] = $val["contact_preference"];
					break;
				case "1":
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
				case "2":

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
				case "3":
					$data["needs_rooms"] = $val["needs_rooms"];
					$data["single_count"] = $val["single_count"];
					$data["double_count"] = $val["double_count"];
					$data["suite_count"] = $val["suite_count"];
					$data["dates"][0]["arrival_date"] = $val["main_arrival_date"];
					$data["dates"][0]["departure_date"] = $val["main_departure_date"];
					break;
				case "4":
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
					// catering shit 
					foreach($val["main_catering"] as $k => $tmp)
					{
						if(!strlen($tmp["catering_start_time"]) && !strlen($tmp["catering_end_time"]) && !strlen($tmp["catering_attendees_no"]))
						{
							continue;
						}
						$data["main_catering"][$k] = $tmp;
					}
					break;
				case "5":
					$no = $arr["act_event_no"];
					$cat_no = $arr["act_cat_no"];
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
					$additional_function["catering"] = ($no < 0)?array():$data["additional_functions"][$no]["catering"];


					$additional_function_catering["catering_type_chooser"] = $val["catering_type_chooser"];
					$additional_function_catering["catering_type_select"] = $val["catering_type_select"];
					$additional_function_catering["catering_type_text"] = $val["catering_type_text"];
					$additional_function_catering["catering_start_time"] = $val["catering_start_time"];
					$additional_function_catering["catering_end_time"] = $val["catering_end_time"];
					$additional_function_catering["catering_attendee_no"] = $val["catering_attendee_no"];

					if($no < 0)
					{
						if($val["event_type_chooser"] || $val["delegates_no"] || is_array($val["tech"]) || $val["door_sign"] || $val["persons_no"] || $val["function_starting_date"] || $val["function_end_date"] || $val["catering_type_chooser"])
						{
							$t = $additional_function_catering;
							if($t["catering_type_chooser"] || $t["catering_type_text"] || $t["catering_start_time"] || $t["catering_end_time"])
							{
								$additional_function["catering"][] = $additional_function_catering;
							}
							$data["additional_functions"][] = $additional_function;
						}
					}
					else
					{
						if($cat_no < 0)
						{
							$t = $additional_function_catering;
							if($t["catering_type_chooser"] || $t["catering_type_text"] || $t["catering_start_time"] || $t["catering_end_time"] || $t["catering_attendee_no"])
							{
								array_push($additional_function["catering"], $t);
							}
						}
						else
						{
							$additional_function["catering"][$cat_no] = $additional_function_catering;
						}
						$data["additional_functions"][$no] = $additional_function;
					}
					break;
				case "6":
					$data["billing_company"] = $val["billing_company"];
					$data["billing_contact"] = $val["billing_contact"];
					$data["billing_street"] = $val["billing_street"];
					$data["billing_city"] = $val["billing_city"];
					$data["billing_zip"] = $val["billing_zip"];
					$data["billing_country"] = $val["billing_country"];
					$data["billing_name"] = $val["billing_name"];
					$data["billing_phone_number"] = $val["billing_phone_number"];
					$data["billing_email"] = $val["billing_email"];
					$data["selected_search_result"] = array_keys($val["search_result"]);
					$data["all_search_results"] = array_keys($val["all_search"]);
					$data["urgent"] = $val["urgent"];
					break;
				case "7":
					break;
			}
		}
		aw_session_set("tmp_conference_data", $data);
	}

	function get_form_data()
	{
		return aw_global_get("tmp_conference_data");
	}
	
	function get_countries($oid)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		return $o->prop("countries");
	}

	/**
		@param country
		@param single_rooms
		@param double_rooms
		@param suites
		@param attendees_count
		@param dates optional type=array
			array(
				start => function start time
				end => function end time
				persons => number of persons in this function
			)
		@param pattern_type
		@param pattern_wday_from
		@param pattern_wday_to
		@param pattern_days

	**/
	function all_mighty_search_engine($arr)
	{
		//arr($arr);
		// okay, i need to get all locations, then check the locations single, double and suite counts
		// from these, which match, .. i need to get all ther rooms ... and check availability..
		// if there are any available rooms.. i need to check if the rooms can accommodate needed peoples
		// locations, which rooms can do this.. are the search results ???


		// tsiish, basically i can't filter out any locations... but i have to put warings to them(there are not enough suites, or these rooms aren't available in these periods etc.. 
		$room_inst = get_instance(CL_ROOM);
		$ol = new object_list(array(
			"class_id" => CL_LOCATION,
		));
		$biggest_event = $arr["attendees_count"];
		// loop over locations
		foreach($arr["dates"] as $data)
		{
			$biggest_event = ($biggest_event < $data["persons"])?$data["persons"]:$biggest_event;
		}
		foreach($ol->arr() as $location_oid => $location)
		{
			$locations[$location_oid] = array();
			$element = &$locations[$location_oid];
			if($arr["single_rooms"] && $location->prop("single_count") < $arr["single_rooms"])
			{
				$element["errors"][] = t("There aren't enough single rooms");
			}
			if($arr["double_rooms"] && $location->prop("double_count") < $arr["double_rooms"])
			{
				$element["errors"][] = t("There aren't enough double rooms");
			}
			if($arr["suites"] && $location->prop("suite_count") < $arr["suites"])
			{
				$element["errors"][] = t("There aren't enough suites");
			}
			// find rooms
			$rooms = new object_list(array(
				"class_id" => CL_ROOM,
				"location" => $location_oid,
			));
			// loop over location rooms
			$biggest_room = false;
			foreach($rooms->arr() as $room_id => $room)
			{
				/*
					actually this is the place where sould be a really nasty code, which checks if these events can be put into those roooms somehow.
					If they can be put, then when .. etc..
					Congratualations to anyone who's gonna do this.. and i do hope i'm not congratulating myself
						taiu
				*/

				$biggest_room = (!$biggest_room || ($biggest_room < $room->prop("normal_capacity")))?$room->prop("normal_capacity"):$biggest_room;
			}
			if($biggest_event > $biggest_room)
			{
				$element["errors"][] = t("There aren't as big rooms as needed");
				break;
			}

		}
		return $locations;
	}

	function _gen_to_timestamp($date = false, $time = false)
	{
		$spl = $date?split("[.]", $date):array(1,1,1970);
		$splt = $time?split(":", $time):array(0,0);
		return mktime($splt[0], $splt[1], 0, $spl[1], $spl[0], $spl[2]);
	}
}
?>
