<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp.aw,v 1.10 2007/04/19 09:49:56 tarvo Exp $
// rfp.aw - Pakkumise saamise palve 
/*

@classinfo syslog_type=ST_RFP relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@groupinfo submitter_info caption="Ankeedi t&auml;itja"
	@default group=submitter_info
		@property data_subm_name type=text
		@caption Ankeedi t&auml;itja

		@property data_subm_country type=text
		@caption Ankeedi t&auml;itja asukoht

		@property data_subm_organisation type=text
		@caption Organisatioon

		@property data_subm_organizer type=text
		@caption Oranisaator
		
		@property data_subm_contact_preference type=text
		@caption Kontakteerumise eelistus

	@groupinfo general_function_info caption="&Uuml;ldine &uuml;rituse info"
	@default group=general_function_info

		@property data_gen_function_name type=text
		@caption &Uuml;rituse nimi

		@property data_gen_attendees_no type=text
		@caption Osalejate arv kokku

		@property data_gen_response_date type=text
		@caption Tagasiside aeg

		@property data_gen_decision_date type=text
		@caption Otsuse aeg

		@property data_gen_arrival_date type=text
		@caption Saabumise aeg

		@property data_gen_departure_date type=text
		@caption Lahkumise aeg

		@property data_gen_open_for_alternative_dates type=checkbox ch_value=1 default=0
		@caption N&ouml;us alternatiivsete kuup&auml;evadega

		@property data_gen_accommondation_requirements type=checkbox ch_value=1 default=0
		@caption Majutusvajadused

		@property data_gen_multi_day type=checkbox ch_value=1 default=0
		@caption &Uuml;rituse kestus

		@property data_gen_single_rooms type=text
		@caption &Uuml;hekohalised toad

		@property data_gen_double_rooms type=text
		@caption Kahekohalised toad

		@property data_gen_suites type=text
		@caption Sviidid

		@property data_gen_dates_are_flexible type=type=checkbox ch_value=1 default=0
		@caption Kuup&auml;evad on paindlikud
		
		@property data_gen_meeting_pattern type=hidden
		@caption Kuup&auml;evade muster

		@property data_gen_date_comments type=text
		@caption Kuup&auml;evade kommentaar

		@property data_gen_city type=text
		@caption Soovitud linn

		@property data_gen_hotel type=text
		@caption Soovitud hotell

		@property archived type=checkbox ch_value=1 default=0
		@caption Arhiveeritud

		@property urgent type=checkbox ch_value=1 default=0
		@caption Kiire

		@property data_gen_alternative_dates type=hidden
		@caption Alternatiivsed kuup&auml;evad

		@property data_gen_package type=text
		@caption Pakett

	@groupinfo main_fun caption="Main function"
	@default group=main_fun

		@property data_mf_table type=text no_caption=1
		@caption Pea&uuml;ritus

		@property data_mf_event_type type=text
		@caption &Uuml;rituse t&uuml;&uuml;p

		@property data_mf_table_form type=text
		@caption Laudade asetus

		@property data_mf_tech type=text
		@caption Tehniline varustus

		@property data_mf_additonal_tech type=text
		@caption Tehnilise varustuse erisoov

		@property data_mf_additional_decorations type=text
		@caption Dekoratsioonid

		@property data_mf_additional_entertainment type=text
		@caption Meelelahutus

		@property data_mf_additional_catering type=text
		@caption Erisoovid toitlustuse kohta

		@property data_mf_breakout_rooms type=checkbox ch_value=1 default=0
		@caption Puhkeruumide soov

		@property door_mf_door_sign type=text
		@caption Uksesilt

		@property data_mf_attendrees_no type=text
		@caption Osalejate arv

		@property data_mf_start_date type=text
		@caption Algusaeg

		@property data_mf_end_date type=text
		@caption L&otilde;puaeg

		@property data_mf_24h type=text
		@caption Hoia 24 tundi kinni

		@property data_mf_catering type=text
		@caption Pea&uuml;rituse toitlustus
		
		@property data_mf_catering_type type=text
		@caption Pea&uuml;rituse toitlustuse t&uuml;&uuml;p
		
		@property data_mf_catering_attendees_no type=text
		@caption Pea&uuml;rituse toitlustuse osalejate arv

		@property data_mf_catering_start type=text
		@caption Pea&uuml;rituse toitlustuse algusaeg

		@property data_mf_catering_end type=text
		@caption Pea&uuml;rituse toitlustuse l&otilde;puaeg
	
	@groupinfo additional_functions caption="Lisa&uuml;ritused"
	@default group=additional_functions
		
		@property data_af_table type=hidden
		@caption Lisa&uuml;ritused

		@property data_af_catering type=text
		@caption Lisa&uuml;rituste toitlustus

	
	@groupinfo search_results caption="Otsingutulemused"
	@default group=search_results

		@property data_search_results type=hidden
		@caption Otsingutulemused

		@property data_search_selected type=hidden
		@caption Valitud otsingutulemused

	@groupinfo billing caption="Arve info"
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
