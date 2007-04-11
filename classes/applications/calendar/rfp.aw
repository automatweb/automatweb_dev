<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp.aw,v 1.7 2007/04/11 15:07:12 tarvo Exp $
// rfp.aw - Pakkumise saamise palve 
/*

@classinfo syslog_type=ST_RFP relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property submitter type=text
	@caption Ankeedi t&auml;itja

	@property data_contact_preference type=text
	@caption Kontakteerumise eelistus

	@property country type=text
	@caption Riik

	@property data_organisation type=text
	@caption Organisatioon

	@property data_function_name type=text
	@caption &Uuml;rituse nimi

	@property attendees_no type=text
	@caption Osalejate arv

	@property data_response_date type=text
	@caption Tagasiside aeg

	@property data_decision_date type=text
	@caption Otsuse aeg

	@property data_arrival_date type=text
	@caption Saabumise aeg

	@property data_departure_date type=text
	@caption Lahkumise aeg

	@property data_open_for_alternative_dates type=checkbox ch_value=1 default=0
	@caption N&ouml;us alternatiivsete kuup&auml;evadega

	@property data_accommondation_requirements type=checkbox ch_value=1 default=0
	@caption Majutusvajadused

	@property data_multi_day type=checkbox ch_value=1 default=0
	@caption &Uuml;rituse kestus

	@property needs_rooms type=checkbox ch_value=1 default=0
	@caption Majutusvajadused

	@property data_single_rooms type=text
	@caption &Uuml;hekohalised toad

	@property data_double_rooms type=text
	@caption Kahekohalised toad

	@property data_suites type=text
	@caption Sviidid

	@property flexible_dates type=checkbox ch_value=1 default=0
	@caption Kuup&auml;evad on paindlikud

	@property data_date_comments type=text
	@caption Kuup&auml;evade kommentaar

	@property data_city type=text
	@caption Soovitud linn

	@property data_hotel type=text
	@caption Soovitud hotell

	@property archived type=checkbox ch_value=1 default=0
	@caption Arhiveeritud

	@property urgent type=checkbox ch_value=1 default=0
	@caption Kiire

	// this approach sucks bigtime, but i don't have any time to do better..
	@property data_dates_are_flexible type=hidden
	@caption Kuup&auml;evad on paindlikud

	@property data_meeting_pattern type=hidden
	@caption Kuup&auml;evade muster

	@property pattern_wday_from type=hidden
	@property pattern_wday_to type=hidden
	@property pattern_days type=hidden
	
	@property event_type_chooser type=hidden
	@property event_type_select type=hidden
	@property event_type_text type=hidden
	@property tech_equip type=hidden

	@property tech_equip_raw type=hidden

	@property main_catering type=hidden
	@property table_form_raw type=hidden
	@property start_time_raw type=hidden
	@property start_date_raw type=hidden
	@property end_time_raw type=hidden
	@property end_date_raw type=hidden

	@property data_alternative_dates type=hidden
	@caption Alternatiivsed kuup&auml;evad
	
	@property data_additional_functions type=hidden
	@caption Lisa&uuml;ritused

	@property data_additional_functions_catering type=text
	@caption Lisa&uuml;rituste toitlustus

	@property data_package type=text
	@caption Pakett
	
	@property all_search_results type=hidden
	@property selected_search_result type=hidden


// additional dates 

@groupinfo add_dates caption="Additional dates"
@default group=add_dates

	@property additional_dates type=text no_caption=1
	@caption Alternatiivsed kuup&auml;evad

// main function 

@groupinfo main_fun caption="Main function"
@default group=main_fun

	@property data_main_function type=text no_caption=1
	@caption Pea&uuml;ritus

	@property data_event_type type=text
	@caption &Uuml;rituse t&uuml;&uuml;p

	@property data_table_form type=text
	@caption Laudade asetus

	@property tech type=text
	@caption Tehniline varustus

	@property data_additonal_tech type=text
	@caption Tehnilise varustuse erisoov

	@property data_breakout_rooms type=checkbox ch_value=1
	@caption Puhkeruumide soov

	@property door_sign type=text
	@caption Uksesilt

	@property data_person_no type=text
	@caption Osalejate arv

	@property start_date type=text
	@caption Algusaeg

	@property end_date type=text
	@caption L&otilde;puaeg

	@property 24h type=text
	@caption Hoia 24 tundi kinni

	@property data_main_function_catering type=text
	@caption Pea&uuml;rituse 

@groupinfo add_fun caption="Lisa&uuml;ritused"
@default group=add_fun

	@property additional_functions type=text no_caption=1
	@caption Lisa&uuml;ritused

@groupinfo billing caption="Billing info"
@default group=billing
	@property data_billing_company type=text
	@caption Organisatsioon

	@property data_billing_contact type=text
	@caption Kontaktisik

	@property data_billing_street type=text
	@caption T&auml;nav

	@property data_billing_city type=text
	@caption Linn

	@property data_billing_zip type=text
	@caption Indeks

	@property data_billing_country type=text
	@caption Riik

	@property data_billing_name type=text
	@caption Nimi

	@property data_billing_phone type=text
	@caption Telefoninumber

	@property data_billing_email type=text
	@caption E-mail

@groupinfo search_res caption="Valitud otsingutulemused"
@default group=search_res
	@property data_search_result type=text
	@caption Otsingutulemused






*/

class rfp extends class_base
{
	function rfp()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/rfp",
			"clid" => CL_RFP
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "submitter":
				if(!$prop["value"])
				{
					RETURN PROP_OK;
				}
				$u = get_instance(CL_USER);
				$p = $u->get_person_for_user(obj($prop["value"]));
				$prop["value"] = html::href(array(
					"caption" => call_user_func(array(obj($p), "name")),
					"url" => $this->mk_my_orb("change" ,array(
						"id" => $p,
						"return_url" => get_ru(),
					), CL_CRM_PERSON),
				));
				break;
			case "open_for_alternative_dates":
			case "accommondation_requirements":
			case "needs_rooms":
			case "24h":
				$prop["value"] = ($prop["value"] == 1)?t("Yes"):t("No");
				break;

			case "start_date":
			case "end_date":
				$prop["value"] = date("d.m.Y H:i", $prop["value"]);
				break;
			case "catering_for_main":
				$data = aw_unserialize($prop["value"]);

				classload("vcl/table");
				$t = new vcl_table();
				$t->define_field(array(
					"name" => "catering_type",
					"caption" => t("Type"),
				));
				$t->define_field(array(
					"name" => "start",
					"caption" => t("Start_time"),
				));
				$t->define_field(array(
					"name" => "end",
					"caption" => t("End time"),
				));
				$t->define_field(array(
					"name" => "attendees",
					"caption" => t("Number of attendees"),
				));
				foreach($data as $k => $data)
				{
					$t->define_data(array(
						"catering_type" => $data["type"],
						"start" => date("H:i", $data["start"]),
						"end" => date("H:i", $data["end"]),
						"attendees" => $data["attendees"],
					));
				}
				$prop["value"] = $t->draw();
				break;
			case "additional_dates":
			case "additional_functions":
			case "search_result":
				$data = aw_unserialize($prop["value"]);
				classload("vcl/table");
				$t = new vcl_table();
				$fun = "_gen_table_".$prop["name"];
				$this->$fun($data, &$t);
				$prop["value"] = $t->draw();
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

	function _gen_table_additional_dates($data, $t)
	{
		$t->define_field(array(
			"name" => "type",
			"caption" => t("Type"),
		));
		$t->define_field(array(
			"name" => "start",
			"caption" => t("Arrival date"),
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("Departure date"),
		));
		foreach($data as $k => $tmp)
		{
			$t->define_data(array(
				"type" => $tmp["type"],
				"start" => date("d.m.Y", $tmp["start"]),
				"end" => date("d.m.Y", $tmp["end"]),
			));
		}
	}
	function _gen_table_additional_functions($data, $t)
	{
		$t->define_field(array(
			"name" => "type",
			"caption" => t("Type"),
		));
		$t->define_field(array(
			"name" => "delegates_no",
			"caption" => t("No. of delegates"),
		));
		$t->define_field(array(
			"name" => "table_form",
			"caption" => t("Table form"),
		));
		$t->define_field(array(
			"name" => "tech",
			"caption" => t("Tech. equipment"),
		));
		$t->define_field(array(
			"name" => "door_sign",
			"caption" => t("Door sign"),
		));
		$t->define_field(array(
			"name" => "persons_no",
			"caption" => t("No. of persons"),
		));
		$t->define_field(array(
			"name" => "24h",
			"caption" => t("24h Hold"),
		));
		$t->define_field(array(
			"name" => "start",
			"caption" => t("Arrival date"),
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("Departure date"),
		));
		$t->define_field(array(
			"name" => "catering_type",
			"caption" => t("Catering type"),
		));
		$t->define_field(array(
			"name" => "catering_start",
			"caption" => t("Catering start"),
		));
		$t->define_field(array(
			"name" => "catering_end",
			"caption" => t("Catering end"),
		));

		foreach($data as $k => $tmp)
		{
			$t->define_data($tmp);
		}
	}
	
	function _gen_table_search_result($data, $t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Location"),
		));
		$t->define_field(array(
			"name" => "selected",
			"caption" => t("Selected by user"),
		));
		foreach($data as $tmp)
		{
			$t->define_data(array(
				"name" => $tmp["location"],
				"selected" => ($tmp["selected"]==1)?t("Yes"):t("No"),
			));
		}
	}
}
?>
