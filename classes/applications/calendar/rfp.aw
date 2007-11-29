<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp.aw,v 1.19 2007/11/29 09:36:37 kristo Exp $
// rfp.aw - Pakkumise saamise palve 
/*

@classinfo syslog_type=ST_RFP relationmgr=yes no_comment=1 no_status=1 prop_cb=1 mantainer=tarvo

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property conference_planner type=relpicker reltype=RELTYPE_WEBFORM
	@caption Tellimuse vorm

	@groupinfo final_info caption="Tellimuskirjeldus"
		
		@groupinfo final_general caption="&Uuml;ldine" parent=final_info
		@default group=final_general
			@property final_rooms type=relpicker multiple=1 reltype=RELTYPE_ROOM
			@caption Ruumid
			@comment Konverentsi jaoks kasutatavad ruumid

			@property final_theme type=textbox
			@caption Teema
			@comment Konverentsi valdkond(&uml;ldteema)

			@property final_international type=checkbox ch_value=1 default=0
			@caption Rahvusvaheline
			@comment Kas &uuml;ritus on rahvusvaheline

			@property final_native_guests type=textbox
			@caption Kohalike k&uuml;laliste arv
			@comment Konverentsil viibivate kohalike k&uuml;laliste arv

			@property final_foreign_guests type=textbox
			@caption V&auml;lisk&uuml;laliste arv
			@comment Konverentsil viibivate v&auml;lisk&uuml;aliste arv

		@groupinfo final_catering caption="Toitlustus" parent=final_info
		@default group=final_catering

			@property final_add_reservation_tb group=final_prices,final_resource,final_catering no_caption=1 type=toolbar

			@layout cat_hsplit type=hbox width=30%:70%

				@layout cat_left parent=cat_hsplit type=vbox closeable=1 area_caption=Ruumid&nbsp;ja&nbsp;reserveeringud
					@property products_tree parent=cat_left type=treeview store=no no_caption=1

				@layout cat_right parent=cat_hsplit type=vbox closeable=1 area_caption=Tooted
					
					@property products_tbl parent=cat_right type=text store=no no_caption=1

		@groupinfo final_resource caption="Ressursid" parent=final_info
		@default group=final_resource

			@layout res_hsplit type=hbox width=30%:70%

				@layout res_left parent=res_hsplit type=vbox closeable=1 area_caption=Ruumid&nbsp;ja&nbsp;reserveeringud
					@property resources_tree parent=res_left type=treeview store=no no_caption=1

				@layout res_right parent=res_hsplit type=vbox closeable=1 area_caption=Ressursid
					@property resources_tbl parent=res_right type=text store=no no_caption=1

		@groupinfo final_prices caption="Hinnad" parent=final_info
		@default group=final_prices
			

			@layout prs_hsplit type=hbox width=30%:70%

				@layout prs_left parent=prs_hsplit type=vbox closeable=1 area_caption=Ruumid&nbsp;ja&nbsp;reserveeringud
					@property prices_tree parent=prs_left type=treeview store=no no_caption=1

				@layout prs_right parent=prs_hsplit type=vbox closeable=1 area_caption=Hinnad
					@property prices_tbl parent=prs_right type=text store=no no_caption=1

		@groupinfo final_submission caption="Kinnitamine" parent=final_info
		@default group=final_submission
			@property tmp5 type=text no_caption=1
	
	@groupinfo data caption="Andmed"

		@groupinfo submitter_info caption="Ankeedi t&auml;itja" parent=data
		@default group=submitter_info
			@property data_subm_name type=text
			@caption Ankeedi t&auml;itja

			@property data_subm_country type=text
			@caption Ankeedi t&auml;itja asukoht

			@property data_subm_organisation type=text
			@caption Organisatioon

			@property data_subm_organizer type=text
			@caption Oranisaator
			
			@property data_subm_email type=text
			@caption E-mail
			
			@property data_subm_phone type=text
			@caption Phone

			@property data_subm_contact_preference type=text
			@caption Kontakteerumise eelistus

		@groupinfo general_function_info caption="&Uuml;ldine &uuml;rituse info" parent=data
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

			@property data_gen_accommodation_requirements type=checkbox ch_value=1 default=0
			@caption Majutusvajadused

			@property data_gen_multi_day type=text 
			@caption &Uuml;rituse kestus

			@property data_gen_single_rooms type=text
			@caption &Uuml;hekohalised toad

			@property data_gen_double_rooms type=text
			@caption Kahekohalised toad

			@property data_gen_suites type=text
			@caption Sviidid

			@property data_gen_acc_start type=text
			@caption Majutuse algusaeg

			@property data_gen_acc_end type=text
			@caption Majutuse l&otilde;puaeg

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

		@groupinfo main_fun caption="P&otilde;hi&uuml;ritus" parent=data
		@default group=main_fun

			@property data_mf_table type=text no_caption=1
			@caption Pea&uuml;ritus

			@property data_mf_event_type type=text
			@caption &Uuml;rituse t&uuml;&uuml;p

			@property data_mf_table_form type=text
			@caption Laudade asetus

			@property data_mf_tech type=text
			@caption Tehniline varustus

			@property data_mf_additional_tech type=text
			@caption Tehnilise varustuse erisoov

			@property data_mf_additional_decorations type=text
			@caption Dekoratsioonid

			@property data_mf_additional_entertainment type=text
			@caption Meelelahutus

			@property data_mf_additional_catering type=text
			@caption Erisoovid toitlustuse kohta

			@property data_mf_breakout_rooms type=checkbox ch_value=1 default=0
			@caption Puhkeruumide soov

			@property data_mf_breakout_room_setup type=text
			@caption Puhkeruumide asetus

			@property data_mf_breakout_room_additional_tech type=text
			@caption Puhkeruumide eri tehnikavajadused

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

			@property data_mf_catering type=text group=main_fun
			@caption Pea&uuml;rituse toitlustus
			
			@property data_mf_catering_type type=text
			@caption Pea&uuml;rituse toitlustuse t&uuml;&uuml;p
			
			@property data_mf_catering_attendees_no type=text
			@caption Pea&uuml;rituse toitlustuse osalejate arv

			@property data_mf_catering_start type=text
			@caption Pea&uuml;rituse toitlustuse algusaeg

			@property data_mf_catering_end type=text
			@caption Pea&uuml;rituse toitlustuse l&otilde;puaeg
		
		@groupinfo additional_functions caption="Lisa&uuml;ritused" parent=data
		@default group=additional_functions
			
			@property data_af_table type=hidden
			@caption Lisa&uuml;ritused

			@property data_af_catering type=text
			@caption Lisa&uuml;rituste toitlustus

		
		@groupinfo search_results caption="Otsingutulemused" parent=data
		@default group=search_results

			@property data_search_results type=hidden
			@caption Otsingutulemused

			@property data_search_selected type=hidden
			@caption Valitud otsingutulemused

		@groupinfo billing caption="Arve info" parent=data
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


#reltypes

@reltype ROOM clid=CL_ROOM value=1
@caption Konverentsi toimumiskoht

@reltype WEBFORM clid=CL_CONFERENCE_PLANNING value=2
@caption Tellimuse vorm

@reltype RESERVATION clid=CL_RESERVATION value=3
@caption Ruumi broneering

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
		$ignored_props = array(
			// these are just numeric values, that can't be parsed as an oid
			"data_gen_single_rooms",
			"data_gen_double_rooms",
			"data_gen_suites",
			"data_gen_attendees_no",
			
			// these are props that need special handlig below..
			"data_mf_event_type",
			"data_mf_catering",
			"data_gen_accommondation_requirements",
		);
		if(substr($prop["name"], 0, 5) == "data_" && !in_array($prop["name"], $ignored_props))
		{
			$prop["value"] = $this->_gen_prop_autom_value($prop["value"]);
			if(trim($prop["value"]) == "")
			{
				return PROP_IGNORE;
			}
			return $retval;
		}
		
		// this here deals with props with values to table
		$prop["name"] = strstr($prop["name"], "ign_")?substr($prop["name"], 4):$prop["name"];
		switch($prop["name"])
		{
			// final_data thingies
			case "final_add_reservation_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_menu_button(array(
					"name" => "add",
					"img" => "new.gif",
					"tooltip" => t("Reserveering"),
				));
				$rooms = $arr["obj_inst"]->prop("final_rooms");
				foreach($rooms as $room)
				{
					$url = $this->mk_my_orb("do_add_reservation", array(
						"id" => $room,
						"start1" => time(),
						"end" => time(),
						"resource" => $room,
						"parent" => $room,
					), CL_ROOM);
					
					$o = obj($room);
					$tb->add_menu_item(array(
						"parent" => "add",
						"text" => sprintf(t("Ruumi '%s'"), $o->name()),
						"url" => $url,
					));
				}
				break;
			case "products_tree":
			case "resources_tree":
			case "prices_tree":
				$t = &$prop["vcl_inst"];
				$rooms = $arr["obj_inst"]->prop("final_rooms");
				foreach($rooms as $room)
				{
					if($this->can("view", $room))
					{
						$room_o = obj($room);
						$t->add_item(0, array(
							"id" => "room_".$room,
							"name" => $room_o->name(),
						));
						$ol = new object_list(array(
							"class_id" => CL_RESERVATION,
							"CL_RESERVATION.RELTYPE_RESOURCE" => $room,
						));
						foreach($ol->arr() as $oid => $obj)
						{
							$t->add_item("room_".$room, array(
								"id" => "reserv_".$oid,
								"name" => date("d.m.Y H:i", $obj->prop("start1")). " - " . date("d.m.Y H:i", $obj->prop("end")),
								"url" => aw_url_change_var(array(
									"reservation_oid" => $oid,
								)),
							));
						}
					}
					
				}
				break;
			case "products_tbl":
			case "resources_tbl":
				if($this->can("view", $arr["request"]["reservation_oid"]))
				{
					$obj = obj($arr["request"]["reservation_oid"]);
					$inst = $obj->instance();
					classload("vcl/table");
					$args = array(
						"request" => array(
							"class" => "reservation",
							"action" => "change",
							"id" => $bron,
						),
						"obj_inst" => $obj,
						"groupinfo" => array(),
						"prop" => array(
							"store" => "no",
							"name" => $arr["prop"]["name"],
							"type" => "table",
							"no_caption" => "1",
							"vcl_inst" => new vcl_table(),
						),
					);
					$function = "_get_".$arr["prop"]["name"];
					$inst->$function(&$args);
					$prop["value"] = $args["prop"]["vcl_inst"]->get_html();
				}
				else
				{
					$prop["value"] = t("Palun valige reserveering");
				}
				break;


			case "tmp4":
				$prop["value"] = "Ruumi hindade/soodustuste & koguhinna/soodustuse määramine";
				break;
			case "tmp5":
				// tmp
				$url  = get_ru();
				$url .= "&pdf=1";
				$pdf = "<a href=\"".$url."\">pdf (kohe üldse üldse üldse ei vungsi)</a><br/><br/>";
				//
				$prop["value"] = $pdf.$this->rfp_reservation_description($arr["obj_inst"]->id(), $arr["request"]["pdf"]?"pdf":"html");
				break;

			// totally new propnames.. gosh

			case "data_gen_accommondation_requirements":
				$prop["value"] = $prop["value"]?1:"";
				break;

			case "data_mf_event_type":
				$prop["value"] = aw_unserialize($prop["value"]);
			case "data_mf_catering_type":
				$prop["value"] = ($prop["value"]["radio"] == 1)?$this->_gen_prop_autom_value($prop["value"]["select"]):$prop["value"]["text"];
				break;

			case "data_mf_catering":
				if(substr($arr["request"]["group"], 0, 5) == "final")
				{
					$prop["no_caption"] = 1;
				}
				$prop["value"] = aw_unserialize($prop["value"]);
				$props = $arr["obj_inst"]->get_property_list();
				classload("vcl/table");
				$t = new aw_table();
				$header = array_keys(reset($prop["value"]));
				foreach($header as $field)
				{
					$t->define_field(array(
						"name" => $field,
						"caption" => $props[$field]["caption"],
					));
				}
				$dummy_arr = $arr;
				unset($dummy_arr["prop"]);
				$dummy_arr["prop"] = $prop;
				
				foreach($prop["value"] as $data)
				{
					foreach($data as $propname => $value)
					{
						//$data[$propname] = ($value["radio"] == 1)?$this->_gen_prop_autom_value($prop["value"]["select"]):$prop["value"]["text"];
						$dummy_arr["prop"] = array(
							"name" => "ign_".$propname,
							"value" => $value,
						);
						$this->get_property(&$dummy_arr);
						$data[$propname] = $dummy_arr["prop"]["value"];
					}
					$t->define_data($data);
				}
				$prop["value"] = $t->draw();
				break;
			
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
			case "products_tbl":
				if($this->can("view", $arr["request"]["reservation_oid"]))
				{
					$res = get_instance(CL_RESERVATION); 
					$res->set_products_info($arr["request"]["reservation_oid"], $arr["request"]);
				}
			break;
			case "resources_tbl":
				$res = get_instance(CL_RESERVATION);
				if(strlen($arr["request"]["resources_total_discount"]) && $this->can("view", $arr["request"]["reservation_oid"]))
				{
					$res->set_resources_discount(array(
						"reservation" => $arr["request"]["reservation_oid"],
						"discount" => $arr["request"]["resources_total_discount"],
					));
				}
				
				if(count($arr["request"]["resources_total_price"]) && $this->can("view", $arr["request"]["reservation_oid"]))
				{
					$res->set_resources_price(array(
						"reservation" => $arr["request"]["reservation_oid"],
						"prices" => $arr["request"]["resources_total_price"],
					));
				}
				
				if(count($arr["request"]["resources_info"]) && $this->can("view", $arr["request"]["reservation_oid"]))
				{
					$res->set_resources_data(array(
						"reservation" => $arr["request"]["reservation_oid"],
						"resources_info" => $arr["request"]["resources_info"],
					));
				}
			break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		if($arr["group"] == "final_resource" || $arr["group"] == "final_catering")
		{
			$arr["reservation_oid"] = $_GET["reservation_oid"];
		}
		$arr["post_ru"] = post_ru();
	}

	function callback_mod_retval($arr)
	{
		if($arr["request"]["group"] == "final_resource" || $arr["request"]["group"] == "final_catering")
		{
			$arr["args"]["reservation_oid"] = $arr["request"]["reservation_oid"];
		}
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

	function _gen_prop_autom_value($value)
	{
		if($this->can("view", $value))
		{
			$o = obj($value);
			$value = html::href(array(
				"url" => $this->mk_my_orb("change", array(
					"id" => $value,
					"return_url" => get_ru(),
				), $o->class_id()),
				"caption" => $o->name(),
			));
		}
		return $value;
	}
	
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

	function rfp_reservation_description($oid, $type = "html")
	{
		if(!$this->can("view", $oid))
		{
			return false;
		}
		switch($type)
		{
			case "html":
				$this->tpl_init("applications/calendar");
				$this->read_template("rfp_reservation_description.tpl");
				return $this->parse();
				break;
			case "pdf":
				$html = $this->rfp_reservation_description($oid, "html");
				$pdf_gen = get_instance("core/converters/html2pdf");
				die($pdf_gen->gen_pdf(array(
					"filename" => t("Tellimuskirjeldus"),
					"source" => $html,
				)));
				break;
		}
	}
}
?>
