<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp_manager.aw,v 1.3 2006/12/29 14:22:05 tarvo Exp $
// rfp_manager.aw - RFP Haldus 
/*

@classinfo syslog_type=ST_RFP_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@groupinfo rfps caption="Pakkumise saamis palved" submit=no
	@property rfps type=table group=rfps no_caption=1

*/

class rfp_manager extends class_base
{
	function rfp_manager()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/rfp_manager",
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
			case "rfps":
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "function",
					"caption" => t("&Uuml;ritus"),
				));
				$t->define_field(array(
					"name" => "org",
					"caption" => t("Organisatsioon"),
				));
				$t->define_field(array(
					"name" => "response_date",
					"caption" => t("Tagasiside aeg"),
				));
				$t->define_field(array(
					"name" => "arrival_date",
					"caption" => t("Saabumisaeg"),
				));
				$t->define_field(array(
					"name" => "departure_date",
					"caption" => t("Lahkumisaeg"),
				));
				$t->define_field(array(
					"name" => "acc_need",
					"caption" => t("Maujutusvajadus"),
				));
				$t->define_field(array(
					"name" => "delegates",
					"caption" => t("Inimeste arv"),
				));
				$t->define_field(array(
					"name" => "contact_pers",
					"caption" => t("Kontaktisik"),
				));
				$t->define_field(array(
					"name" => "contacts",
					"caption" => t("Kontaktandmed"),
				));


				foreach($this->get_rfps() as $oid => $obj)
				{
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
					$t->define_data(array(
						"function" => html::href(array(
							"caption" => ($_t = $obj->prop("function_name"))?$_t:t(" - Nimetu - "),
							"url" => "#",
							"onClick" => "aw_popup_scroll(\"".$this->mk_my_orb("show_overview", array(
								"oid" => $oid
							))."\",\"hey\",400,600);",
							/*
							"url" => $this->mk_my_orb("change", array(
								"id" => $oid,
								"return_url" => get_ru(),
							),CL_RFP),
							*/
						)),
						"org" => $obj->prop("organisation"),
						"responose_date" => $obj->prop("response_date"),
						"arrival_date" => $obj->prop("arrival_date"),
						"departure_date" => $obj->prop("departure_date"),
						"acc_need" => ($obj->prop("accomondation_requirements") == 1)?t("Jah"):t("Ei"),
						"delegates" => $obj->prop("delegates_no"),
						"contact_pers" => "",
						"contacts" => join(", ", $contacts).(strlen($_t = $obj->prop("contact_preference"))?"(".$_t.")":""),
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

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//

	function get_rfps()
	{
		$o = new object_list(array(
			"class_id" => CL_RFP,
		));
		return $o->arr();
	}

	/**
		@attrib params=name name=show_overview all_args=1
		@param oid required type=oid
	**/
	function show_overview($arr)
	{
		$c_plan= get_instance(CL_CONFERENCE_PLANNING);
		// set data .. this sucks
		$obj = obj($arr["oid"]);
		$data = array(
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
		);
		
		$data["dates"] = array_merge($data["dates"], aw_unserialize($obj->prop("additional_dates_raw")));

		$ret = $c_plan->show(array(
			"sub" => 7,
			"sub_contents_only" => true,
			"data" => $data,
		));
		die($ret);
	}
}
?>
