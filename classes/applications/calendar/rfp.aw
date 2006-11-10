<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp.aw,v 1.2 2006/11/10 14:01:41 tarvo Exp $
// rfp.aw - Pakkumise saamise palve 
/*

@classinfo syslog_type=ST_RFP relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property submitter type=text
	@caption Submitter

	@property contact_preference type=text
	@caption Contact preference

	@property country type=text
	@caption Country

	@property organisation type=text
	@caption Organisation

	@property function_name type=text
	@caption Function name

	@property attendees_no type=text
	@caption Number of attendees

	@property response_date type=text
	@caption Response date

	@property decision_date type=text
	@caption Decision date

	@property arrival_date type=text
	@caption Arrival date

	@property departure_date type=text
	@caption Departure date

	@property open_for_alternative_dates type=text
	@caption Open for alternative dates

	@property accommondation_requirements type=text
	@caption I have accomonndation requirements

	@property needs_rooms type=text
	@caption Needs rooms

	@property single_rooms type=text
	@caption Single rooms

	@property double_rooms type=text
	@caption Double rooms

	@property suites type=text
	@caption Suites

	@property flexible_dates type=text
	@caption Dates are flexible

// additional dates 

@groupinfo add_dates caption="Additional dates"
@default group=add_dates

	@property additional_dates type=text no_caption=1
	@caption Additional_dates

// main function 

@groupinfo main_fun caption="Main function"
@default group=main_fun
	@property event_type type=text
	@caption Event type

	@property delegates_no type=text
	@caption Number of delegates

	@property table_form type=text
	@caption Table form

	@property tech type=text
	@caption Table form

	@property door_sign type=text
	@caption Door sign/group name

	@property person_no type=text
	@caption Number of persons

	@property start_date type=text
	@caption Start time

	@property end_date type=text
	@caption End time

	@property 24h type=text
	@caption 24h hold

	@property catering_for_main type=text
	@caption Catering for main function

@groupinfo add_fun caption="Additional functions"
@default group=add_fun

	@property additional_functions type=text no_caption=1
	@caption Additional functions

@groupinfo billing caption="Billing info"
@default group=billing
	@property billing_company type=text
	@caption Company

	@property billing_contact type=text
	@caption Contact

	@property billing_street type=text
	@caption Street

	@property billing_city type=text
	@caption City

	@property billing_zip type=text
	@caption Zip

	@property billing_country type=text
	@caption Country

	@property billing_name type=text
	@caption Name

	@property billing_phone_number type=text
	@caption Phone number

	@property billing_email type=text
	@caption E-mail

@groupinfo search_res caption="Selected results"
@default group=search_res
	@property search_result type=text
	@caption Search results






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
