<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp.aw,v 1.22 2008/06/30 09:24:23 tarvo Exp $
// rfp.aw - Pakkumise saamise palve 
/*

@classinfo syslog_type=ST_RFP relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=tarvo

@tableinfo rfp index=aw_oid master_index=brother_of master_table=objects

@default table=objects
@default group=general

	@property conference_planner type=relpicker reltype=RELTYPE_WEBFORM field=meta method=serialize
	@caption Tellimuse vorm

@default table=rfp
@default group=general

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

			@property data_contactperson type=textbox table=objects field=meta method=serialize
			@caption Kontaktisik

			@property data_send_date type=date_select table=objects field=meta method=serialize
			@caption Saatmise kuup&auml;ev

			@property data_pointer_text type=textbox table=objects field=meta method=serialize
			@caption Tekst suunaviitadele

			@property data_payment_method type=textbox table=objects field=meta method=serialize
			@caption Maksmisviis

			@property pdf type=text store=no
			@caption Lae PDF

			@property submission type=text no_caption=1 store=no
	
	@groupinfo data caption="Andmed"

		@groupinfo submitter_info caption="Ankeedi t&auml;itja" parent=data
		@default group=submitter_info
			@property data_subm_name type=textbox
			@caption Ankeedi t&auml;itja

			@property data_subm_country type=textbox
			@caption Ankeedi t&auml;itja asukoht

			@property data_subm_organisation type=textbox
			@caption Organisatioon

			@property data_subm_organizer type=textbox
			@caption Organisaator
			
			@property data_subm_email type=textbox
			@caption E-mail
			
			@property data_subm_phone type=textbox
			@caption Phone

			@property data_subm_contact_preference type=relpicker reltype=RELTYPE_PREFERENCE
			@caption Kontakteerumise eelistus

		@groupinfo general_function_info caption="&Uuml;ldine &uuml;rituse info" parent=data
		@default group=general_function_info

			@property data_gen_function_name type=textbox
			@caption &Uuml;rituse nimi

			@property data_gen_attendees_no type=textbox
			@caption Osalejate arv kokku

			@property data_gen_response_date type=hidden table=objects field=meta method=serialize
			@caption Tagasiside aeg

			@property data_gen_decision_date type=hidden table=objects field=meta method=serialize
			@caption Otsuse aeg

			@property data_gen_response_date_admin type=datetime_select
			@caption Tagasiside aeg

			@property data_gen_decision_date_admin type=datetime_select
			@caption Otsuse aeg

			@property data_gen_arrival_date type=hidden table=objects field=meta method=serialize
			@caption Saabumise aeg

			@property data_gen_departure_date type=hidden table=objects field=meta method=serialize
			@caption Lahkumise aeg

			@property data_gen_arrival_date_admin type=datetime_select
			@caption Saabumise aeg

			@property data_gen_departure_date_admin type=datetime_select
			@caption Lahkumise aeg

			@property data_gen_open_for_alternative_dates type=checkbox ch_value=1 default=0
			@caption N&ouml;us alternatiivsete kuup&auml;evadega

			@property data_gen_accommodation_requirements type=checkbox ch_value=1 default=0
			@caption Majutusvajadused

			@property data_gen_multi_day type=textbox
			@caption &Uuml;rituse kestus

			@property data_gen_single_rooms type=textbox
			@caption &Uuml;hekohalised toad

			@property data_gen_double_rooms type=textbox
			@caption Kahekohalised toad

			@property data_gen_suites type=textbox
			@caption Sviidid

			@property data_gen_acc_start type=hidden table=objects field=meta method=serialize
			@caption Majutuse algusaeg

			@property data_gen_acc_end type=hidden table=objects field=meta method=serialize
			@caption Majutuse l&otilde;puaeg

			@property data_gen_acc_start_admin type=date_select
			@caption Majutuse algusaeg

			@property data_gen_acc_end_admin type=date_select
			@caption Majutuse l&otilde;puaeg

			@property data_gen_dates_are_flexible type=checkbox ch_value=1 default=0
			@caption Kuup&auml;evad on paindlikud
			
			@property data_gen_meeting_pattern type=hidden
			@caption Kuup&auml;evade muster

			@property data_gen_date_comments type=textbox
			@caption Kuup&auml;evade kommentaar

			@property data_gen_city type=relpicker reltype=RELTYPE_TOWN
			@caption Soovitud linn

			@property data_gen_hotel type=relpicker reltype=RELTYPE_LOCATION
			@caption Soovitud hotell

			@property archived type=checkbox ch_value=1 default=0
			@caption Arhiveeritud

			@property urgent type=checkbox ch_value=1 default=0
			@caption Kiire

			@property data_gen_alternative_dates type=hidden
			@caption Alternatiivsed kuup&auml;evad

			@property data_gen_package type=relpicker reltype=RELTYPE_PACKAGE
			@caption Pakett

		@groupinfo main_fun caption="P&otilde;hi&uuml;ritus" parent=data
		@default group=main_fun

			@property data_mf_table type=textbox no_caption=1
			@caption Pea&uuml;ritus

			@property data_mf_event_type type=relpicker reltype=RELTYPE_EVENT_TYPE
			@caption &Uuml;rituse t&uuml;&uuml;p

			@property data_mf_table_form type=relpicker reltype=RELTYPE_TABLES
			@caption Laudade asetus

			@property data_mf_tech type=textbox
			@caption Tehniline varustus

			@property data_mf_additional_tech type=textbox
			@caption Tehnilise varustuse erisoov

			@property data_mf_additional_decorations type=textbox
			@caption Dekoratsioonid

			@property data_mf_additional_entertainment type=textbox
			@caption Meelelahutus

			@property data_mf_additional_catering type=textbox
			@caption Erisoovid toitlustuse kohta

			@property data_mf_breakout_rooms type=checkbox ch_value=1 default=0
			@caption Puhkeruumide soov

			@property data_mf_breakout_room_setup type=textbox
			@caption Puhkeruumide asetus

			@property data_mf_breakout_room_additional_tech type=textbox
			@caption Puhkeruumide eri tehnikavajadused

			@property data_mf_door_sign type=textbox
			@caption Uksesilt

			@property data_mf_attendees_no type=textbox
			@caption Osalejate arv

			@property data_mf_start_date type=hidden field=meta method=serialize table=objects
			@caption Algusaeg

			@property data_mf_end_date type=hidden field=meta method=serialize table=objects
			@caption L&otilde;puaeg

			@property data_mf_start_date_admin type=datetime_select
			@caption Algusaeg

			@property data_mf_end_date_admin type=datetime_select
			@caption L&otilde;puaeg

			@property data_mf_24h type=textbox
			@caption Hoia 24 tundi kinni

			@property data_mf_catering type=text group=main_fun
			@caption Pea&uuml;rituse toitlustus
			
			@property data_mf_catering_type type=textbox
			@caption Pea&uuml;rituse toitlustuse t&uuml;&uuml;p
			
			@property data_mf_catering_attendees_no type=textbox
			@caption Pea&uuml;rituse toitlustuse osalejate arv

			@property data_mf_catering_start type=hidden field=meta method=serialize table=objects
			@caption Pea&uuml;rituse toitlustuse algusaeg

			@property data_mf_catering_end type=hidden field=meta method=serialize table=objects
			@caption Pea&uuml;rituse toitlustuse l&otilde;puaeg

			@property data_mf_catering_start_admin type=datetime_select
			@caption Pea&uuml;rituse toitlustuse algusaeg

			@property data_mf_catering_end_admin type=datetime_select
			@caption Pea&uuml;rituse toitlustuse l&otilde;puaeg

		@groupinfo billing caption="Arve info" parent=data
		@default group=billing
			
			@property data_billing_company type=textbox
			@caption Organisatsioon

			@property data_billing_contact type=textbox
			@caption Kontaktisik

			@property data_billing_street type=textbox
			@caption T&auml;nav

			@property data_billing_city type=textbox
			@caption Linn

			@property data_billing_zip type=textbox
			@caption Indeks

			@property data_billing_country type=textbox
			@caption Riik

			@property data_billing_name type=textbox
			@caption Nimi

			@property data_billing_phone type=textbox
			@caption Telefoninumber

			@property data_billing_email type=textbox
			@caption E-mail

		@groupinfo files caption="Failid" parent=data
		@default group=files
			@property files_tb type=toolbar store=no no_caption=1
			
			@property files_tbl type=table store=no no_caption=1

#reltypes

@reltype ROOM clid=CL_ROOM value=1
@caption Konverentsi toimumiskoht

@reltype WEBFORM clid=CL_CONFERENCE_PLANNING value=2
@caption Tellimuse vorm

@reltype RESERVATION clid=CL_RESERVATION value=3
@caption Ruumi broneering

@reltype TOWN clid=CL_META value=4
@caption Linn

@reltype LOCATION clid=CL_LOCATION value=5
@caption Asukoht

@reltype PREFERENCE clid=CL_META value=6
@caption Kontakti eelistus

@reltype PACKAGE clid=CL_META value=7
@caption Pakett

@reltype EVENT_TYPE clid=CL_META value=8
@caption S&uuml;ndmuse t&uuml;&uuml;p

@reltype TABLES clid=CL_META value=9
@caption Laudade paigutus
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

	function date_to_stamp($date)
	{
		$day = explode(".", $date["date"]);
		$time = explode(":", $date["time"]);
		$stamp = mktime($time[0], $time[1], 0, $day[1], $day[0], $day[2]);
		return $stamp;
	}

	function get_property($arr)
	{
		//$this->db_query("DROP TABLE `rfp`");die();
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
		/*if(substr($prop["name"], 0, 5) == "data_" && !in_array($prop["name"], $ignored_props))
		{
			$prop["value"] = $this->_gen_prop_autom_value($prop["value"]);
			if(trim($prop["value"]) == "")
			{
				//return PROP_IGNORE;
			}
			return $retval;
		}*/
		
		// this here deals with props with values to table
		$prop["name"] = (strstr($prop["name"], "ign_") && !strstr($prop["name"], "foreign"))?substr($prop["name"], 4):$prop["name"];
		switch($prop["name"])
		{
			case "final_rooms":
				$rfpm = get_instance(CL_RFP_MANAGER);
				$rfpm = $rfpm->get_sysdefault();
				$rfpm = obj($rfpm);
				$conns = $arr["obj_inst"]->connections_from();
				foreach($conns as $conn)
				{
					$to = $conn->to();
					$exist[$to->id()] = $to->id();
				}
				$ol = $rfpm->get_rooms_from_room_folder();
				foreach($ol->arr() as $oid => $o)
				{
					if(in_array($oid, $exist))
					{
						continue;
					}
					$arr["obj_inst"]->connect(array(
						"to" => $oid,
						"type" => "RELTYPE_ROOM",
					));
					$prop["options"][$oid] = $o->name();
				}
				break;
			// final_data thingies
			case "data_mf_catering_end_admin":
			case "data_mf_catering_start_admin":
			case "data_mf_end_date_admin":
			case "data_mf_start_date_admin":
			case "data_gen_acc_end_admin":
			case "data_gen_acc_start_admin":
			case "data_gen_departure_date_admin":
			case "data_gen_arrival_date_admin":
			case "data_gen_decision_date_admin":
			case "data_gen_response_date_admin":
				if($prop["value"] < 1)
				{
					$svar = substr($prop["name"], 0, -6);
					if($ov = $arr["obj_inst"]->prop($svar))
					{
						$prop["value"] = $this->date_to_stamp($ov);
					}
				}
				break;
			case "final_add_reservation_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_menu_button(array(
					"name" => "add",
					"img" => "new.gif",
					"tooltip" => t("Reserveering"),
				));
				$rooms = $this->get_rooms($arr);
				foreach($rooms as $room)
				{
					$url = $this->mk_my_orb("new", array(
						"start1" => time(),
						"end" => time(),
						"resource" => $room,
						"parent" => $room,
						"rfp" => $arr["obj_inst"]->id(),
						"return_url" => get_ru(),
					), CL_RESERVATION);
					
					$o = obj($room);
					$tb->add_menu_item(array(
						"parent" => "add",
						"text" => sprintf(t("Ruumi '%s'"), $o->name()),
						"url" => $url,
					));
					$tb->add_save_button();
				}
				break;
			case "products_tree":
			case "resources_tree":
			case "prices_tree":
				$t = &$prop["vcl_inst"];
				$rooms = $this->get_rooms($arr);
				foreach($rooms as $room)
				{
					if($this->can("view", $room))
					{
						$room_o = obj($room);
						$t->add_item(0, array(
							"id" => "room_".$room,
							"name" => $room_o->name(),
							"url" => aw_url_change_var(array(
								"room_oid" => $room,
							)),
						));
					}
					
				}
				$conn = $arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_RESERVATION",
				));
				foreach($conn as $c)
				{
					$oid = $c->prop("to");
					$obj = obj($oid);
					$room = $obj->prop("resource");
					$t->add_item("room_".$room, array(
						"id" => "reserv_".$oid,
						"name" => date("d.m.Y H:i", $obj->prop("start1")). " - " . date("d.m.Y H:i", $obj->prop("end")),
						"url" => aw_url_change_var(array(
							"reservation_oid" => $oid,
						)),
					));
				}
				break;
			case "products_tbl":
				if(!$arr["request"]["reservation_oid"])
				{
					$prop["value"] = $this->_get_products_tbl($arr);
				}
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

			case "prices_tbl":
				classload("vcl/table");
				$args = array(
					"request" => array(
						"class" => "reservation",
						"action" => "change",
						"id" => $bron,
					),
					"groupinfo" => array(),
					"prop" => array(
						"store" => "no",
						"name" => $arr["prop"]["name"],
						"type" => "table",
						"no_caption" => "1",
						"vcl_inst" => new vcl_table(),
					),
				);
				if($this->can("view", $arr["request"]["reservation_oid"]))
				{
					$args["obj_inst"] = obj($arr["request"]["reservation_oid"]);
				}
				else
				{
					if($room = $arr["request"]["room_oid"])
					{
						$rooms[$room] = $room;
					}
					else
					{
						$rooms = $this->get_rooms($arr);
					}
					$conn = $arr["obj_inst"]->connections_from(array(
						"type" => "RELTYPE_RESERVATION",
					));
					foreach($conn as $c)
					{
						if($this->can("view", $c->prop("to")))
						{
							$o = obj($c->prop("to"));
							$room = $o->prop("resource");
							if($rooms[$room])
							{
								$ids[] = $o->id();
							}
						}
					}
					$args["ids"] = $ids;
				}
				$inst = get_instance(CL_RESERVATION);
				$function = "_get_".$arr["prop"]["name"];
				$inst->$function(&$args);
				$prop["value"] = $args["prop"]["vcl_inst"]->get_html();
				break;

			case "tmp4":
				$prop["value"] = "Ruumi hindade/soodustuste & koguhinna/soodustuse m&auml;&auml;ramine";
				break;
			case "tmp5":
				// tmp
				$url  = get_ru();
				$url .= "&pdf=1";
				$pdf = "<a href=\"".$url."\">pdf (kohe &uuml;ldse &uuml;ldse &uuml;ldse ei vungsi)</a><br/><br/>";
				//
				$prop["value"] = $pdf.$this->rfp_reservation_description($arr["obj_inst"]->id(), $arr["request"]["pdf"]?"pdf":"html");
				break;

			// totally new propnames.. gosh

			case "data_gen_accommondation_requirements":
				$prop["value"] = $prop["value"]?1:"";
				break;

			case "data_mf_event_type":
				$prop["value"] = aw_unserialize($prop["value"]);
			/*case "data_mf_catering_type":
				$prop["value"] = ($prop["value"]["radio"] == 1)?$this->_gen_prop_autom_value($prop["value"]["select"]):$prop["value"]["text"];
				break;*/

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
						if(is_array($value))
						{
							$oid = $value["select"];
							if($this->can("view", $oid))
							{
								$o = obj($oid);
								$value = $o->name();
							}
						}
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
			case "pdf":
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("get_pdf_file", array("id" => $arr["obj_inst"]->id())),
					"caption" => t("Fail"),
				));
				break;
		};
		return $retval;
	}

	function _get_products_tbl($arr)
	{
		$rm = get_instance(CL_RFP_MANAGER);
		$def = $rm->get_sysdefault();
		if($def)
		{
			$defo = obj($def);
			$rfs = $defo->prop("prod_vars_folder");
			if($this->can("view", $rfs))
			{
				$prodvars = array(0=>" ");
				$ol = new object_list(array(
					"class_id" => CL_META,
					"parent" => $rfs,
				));
				foreach($ol->arr() as $o)
				{
					$prodvars[$o->id()] = $o->name();
				}
			}
		}
		classload("vcl/table");
		$t = new aw_table;
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Toode"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "amount",
			"caption" => t("Kogus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "discount",
			"caption" => t("Soodustus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "var",
			"caption" => t("Nimetus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "comment",
			"caption" => t("Kommentaar"),
			"align" => "center",
		));
		$conn = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_RESERVATION",
		));
		$rvi = get_instance(CL_RESERVATION);
		$prods = $arr["obj_inst"]->meta("prods");
		foreach($conn as $c)
		{
			if($this->can("view", $c->prop("to")))
			{
				$rv = $c->to();
				$prod_list = $rvi->get_room_products($rv->prop("resource"));
				$amount = $rv->meta("amount");
				$discount = $rvi->get_product_discount($rv->id());
				foreach($prod_list->arr() as $prod)
				{
					if($count = $amount[$prod->id()])
					{
						$prod_price = $rvi->get_product_price(array("product" => $prod->id(), "reservation" => $rv));
						$price = $rvi->_get_admin_price_view($prod,$prod_price);
						$disc = $discount[$prod->id()];
						$data = array(
							"name" => $prod->name(),
							"price" => html::hidden(array(
								"name" => "prods[".$prod->id().$rv->id()."][price]",
								"value" => $price,
							)).$price,
							"amount" => html::hidden(array(
								"name" => "prods[".$prod->id().$rv->id()."][amount]",
								"value" => $count,
							)).$count,
							"discount" => html::hidden(array(
								"name" => "prods[".$prod->id().$rv->id()."][discount]",
								"value" => $disc,
							)).$disc."%",
							"time" => html::textbox(array(
								"name" => "prods[".$prod->id().$rv->id()."][time]",
								"value" => $prods[$prod->id().$rv->id()]["time"],
								"size" => 6,
							)),
							"var" => html::select(array(
								"name" => "prods[".$prod->id().$rv->id()."][var]",
								"value" => $prods[$prod->id().$rv->id()]["var"],
								"options" => $prodvars,
							)),
							"comment" => html::textarea(array(
								"name" => "prods[".$prod->id().$rv->id()."][comment]",
								"value" => $prods[$prod->id().$rv->id()]["comment"],
								"cols" => 12,
								"rows" => 2,
							)),
						);
						$t->define_data($data);
					}
				}
			}
		}
		return $t->draw();
	}

	function get_rooms($arr)
	{
		$rm = get_instance(CL_RFP_MANAGER);
		$def = $rm->get_sysdefault();
		if($def)
		{
			$defo = obj($def);
			$rfs = $defo->prop("room_folder");
		}
		$rooms = array();
		unset($rfs[0]);
		if(count($rfs))
		{
			$ol = new object_list(array(
				"class_id" => CL_ROOM,
				"lang_id" => array(),
				"parent" => $rfids
			));
			foreach($ol->arr() as $oid=>$o)
			{
				$rooms[$oid] = $oid;
			}
		}
		$conn = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_ROOM",
		));
		foreach($conn as $c)
		{
			$rooms[$c->prop("to")] = $c->prop("to");
		}
		$conn = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_RESERVATION",
		));
		foreach($conn as $c)
		{
			$rv = obj($c->prop("to"));
			if($room = $rv->prop("resource"))
			{
				$rooms[$room] = $room;
			}
		}
		return $rooms;
	}

	function _get_submission($arr)
	{
		$prop = &$arr["prop"];
		
		$prop["value"] = $this->_get_submission_data($arr);
	}

	/**
	@attrib name=get_pdf_file all_args=1
	**/
	function get_pdf_file($arr)
	{
		$arr["obj_inst"] = obj($arr["id"]);
		$html = $this->_get_submission_data($arr);
		classload("core/converters/html2pdf");
		$i = new html2pdf;
		if($i->can_convert())
		{
			$i->gen_pdf(array(
				"source" => $html,
				"filename" => "kinnitus",
			));
		}
		else
		{
			die("Serveris puudub htmldoc. PDF-i ei saa genereerida");
		}
	}

	function _get_submission_data($arr)
	{
		$this->read_template("submission.tpl");
		$this->vars(array(
			"send_date" => date('d.m.Y', $arr["obj_inst"]->prop("data_send_date")),
			"contactperson" => $arr["obj_inst"]->prop("data_contactperson"),
			"payment_method" => $arr["obj_inst"]->prop("data_payment_method"),
			"pointer_text" => $arr["obj_inst"]->prop("data_pointer_text"),
			"title" => $arr["obj_inst"]->prop("data_mf_event_type.name"),
		));
		$package_id = $arr["obj_inst"]->prop("data_gen_package");
		if($this->can("view", $package_id))
		{
			$package_o = obj($package_id);
			$package = $package_o->name();
		}
		$tables = $arr["obj_inst"]->prop("data_mf_table_form.name");
		$conn = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_RESERVATION",
		));
		$brons = "";
		$currency = 745;
		$resources_total = 0;
		$colspan = 6;
		if($package)
		{
			$ph = $this->parse("HEADERS_PACKAGE");
			$this->vars(array(
				"HEADERS_PACKAGE" => $ph,
			));
			$colspan += 2;
		}
		else
		{
			$nph = $this->parse("HEADERS_NO_PACKAGE");
			$this->vars(array(
				"HEADERS_NO_PACKAGE" => $nph,
			));
		}
		$totalprice = 0;
		$bron_totalprice = 0;
		foreach($conn as $c)
		{
			$rv = obj($c->prop("to"));
			$start = $rv->prop("start1");
			$end = $rv->prop("end");
			$timefrom = date('H:i', $start);
			$timeto = date('H:i', $end);
			$datefrom = date('d.m.Y', $start);
			$dateto = date('d.m.Y', $end);
			$people = $rv->prop("people_count");
			if($roomid = $rv->prop("resource"))
			{
				$ro = obj($roomid);
				$room = $ro->name();
				$room_instance = get_instance(CL_ROOM);
				$sum = $room_instance->cal_room_price(array(
					"room" => $roomid,
					"start" => $start,
					"end" => $end,
					"people" => $people,
					"products" => $rv->meta("amount"),
					"bron" => $rv,
				));
				$price = $sum[$currency];
			}
			$comment = $rv->prop("comment");
			$this->vars(array(
				"datefrom" => $datefrom,
				"timefrom" => $timefrom,
				"timeto" => $timeto,
				"dateto" => $dateto,
				"room" => $room,
				"tables" => $tables,
				"people" => $people,
				"comments" => $comment,
				"colspan" => $colspan,
			));
			if($package)
			{
				$mgri = get_instance(CL_RFP_MANAGER);
				$mgrid = $mgri->get_sysdefault();
				if($this->can("view", $mgrid))
				{
					$mgr = obj($mgrid);
					$pk_prices = $mgr->meta("pk_prices");
					if(is_array($pk_prices))
					{
						$unitprice = $pk_prices[$package_id][$currency];
					}
				}
				$price = $unitprice*$people;
				$this->vars(array(
					"unitprice" => $unitprice,
					"package" => $package,
					"price" => $price,
				));
				$tmp = $this->parse("VALUES_PACKAGE");
				$this->vars(array(
					"VALUES_PACKAGE" => $tmp,
				));
			}
			else
			{
				$this->vars(array(
					"price" => $price,
				));
				$tmp = $this->parse("VALUES_NO_PACKAGE");
				$this->vars(array(
					"VALUES_NO_PACKAGE" => $tmp,
				));
			}
			$bron_totalprice += $price;
			$brons .= $this->parse("BRON");
			$resources_tmp = $rv->meta("resources_info");
			if(count($resources_tmp))
			{
				foreach($resources_tmp as $rid => $data)
				{
					$count = $data["count"];
					if($count)
					{
						$r = obj($rid);
						$price = $data["prices"][$currency];
						
						$total = $price*$count;
						$resources_total += $total;
						$resources[] = array(
							"rid" => $rid,
							"name" => $r->name(),
							"price" => $price,
							"count" => $count,
							"total" => $total,
							"time" => $data["time"],
							"comment" => $data["comment"],
						);
					}
				}
			}
		}
		$this->vars(array(
			"total_colspan" => $colspan - 2,
			"bron_totalprice" => $bron_totalprice,
		));
		$totalprice += $bron_totalprice;
		$res_sub = "";
		if(count($resources))
		{
			$res = "";
			foreach($resources as $r)
			{
				$this->vars(array(
					"name" => $r["name"],
					"count" => $r["count"],
					"price" => $r["price"],
					"total" => $r["total"],
					"time" => $r["time"],
					"comment" => $r["comment"],
				));
				$res .= $this->parse("RESOURCE");
			}
			$this->vars(array(
				"RESOURCE" => $res,
				"rtotal" => $resources_total,
			));
			$res_sub = $this->parse("RESOURCES");
		}
		$totalprice += $resources_total;
		$this->vars(array(
			"BRON" => $brons,
			"RESOURCES" => $res_sub,
		));
		return $this->parse();
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
			case "prices_tbl":
				foreach($arr["request"] as $var=>$val)
				{
					$tmp = explode("_", $var);
					if($tmp[0] == "discount")
					{
						$o = obj($tmp[1]);
						$o->set_prop("special_discount", $val);	
						$o->save();
					}
					elseif($tmp[0] == "custom")
					{
						$o = obj($tmp[1]);
						$o->set_prop("special_sum", $val);	
						$o->save();
					}
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

	function _get_files_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_FILE), $arr["obj_inst"]->id(), '', array());
		$tb->add_search_button(array(
			"pn" => $arr["obj_inst"]->id(),
			"multiple" => 1,
			"clid" => CL_FILE,
		));
		$tb->add_delete_button();
	}

	function _get_files_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$ol = new object_list(array(
			"class_id" => CL_FILE,
			"parent" => $arr["obj_inst"]->id(),
		));
		$t->table_from_ol($ol, array("name", "modifiedby", "modified"), CL_FILE);
	}

	function do_db_upgrade($tbl, $field, $q, $err)
	{
		if($tbl == "rfp")
		{
			if($field=="")
			{
				$fields = array(
					array("final_rooms", "varchar(255)"),
					array("final_theme", "varchar(255)"),
					array("final_international", "int"),
					array("final_native_guests", "int"),
					array("final_foreign_guests", "int"),
					array("data_subm_name", "varchar(255)"),
					array("data_subm_country", "varchar(255)"),
					array("data_subm_organisation", "varchar(255)"),
					array("data_subm_organizer", "varchar(255)"),
					array("data_subm_email", "varchar(255)"),
					array("data_subm_phone", "varchar(255)"),
					array("data_subm_contact_preference", "varchar(255)"),
					array("data_gen_function_name", "varchar(255)"),
					array("data_gen_attendees_no", "int"),
					array("data_gen_response_date_admin", "int"),
					array("data_gen_decision_date_admin", "int"),
					array("data_gen_departure_date_admin", "int"),
					array("data_gen_arrival_date_admin", "int"),
					array("data_gen_open_for_alternative_dates", "int"),
					array("data_gen_accommodation_requirements", "int"),
					array("data_gen_multi_day", "varchar(255)"),
					array("data_gen_single_rooms", "int"),
					array("data_gen_double_rooms", "int"),
					array("data_gen_suites", "int"),
					array("data_gen_acc_start_admin", "int"),
					array("data_gen_acc_end_admin", "int"),
					array("data_gen_dates_are_flexible", "int"),
					array("data_gen_meeting_pattern", "varchar(255)"),
					array("data_gen_date_comments", "text"),
					array("data_gen_city", "int"),
					array("data_gen_hotel", "int"),
					array("archived", "int"),
					array("urgent", "int"),
					array("data_gen_alternative_dates", "int"),
					array("data_gen_package", "int"),
					array("data_mf_table", "varchar(255)"),
					array("data_mf_event_type", "int"),
					array("data_mf_table_form", "int"),
					array("data_mf_tech", "varchar(255)"),
					array("data_mf_additional_tech", "text"),
					array("data_mf_additional_decorations", "text"),
					array("data_mf_additional_entertainment", "text"),
					array("data_mf_additional_catering", "text"),
					array("data_mf_breakout_rooms", "int"),
					array("data_mf_breakout_room_setup", "text"),
					array("data_mf_breakout_room_additional_tech", "text"),
					array("data_mf_door_sign", "varchar(255)"),
					array("data_mf_attendees_no", "int"),
					array("data_mf_start_date_admin", "int"),
					array("data_mf_end_date_admin", "int"),
					array("data_mf_24h", "varchar(255)"),
					array("data_mf_catering", "text"),
					array("data_mf_catering_type", "varchar(255)"),
					array("data_mf_catering_attendees_no", "int"),
					array("data_mf_catering_start_admin", "int"),
					array("data_mf_catering_end_admin", "int"),
					array("data_billing_company", "varchar(255)"),
					array("data_billing_contact", "varchar(255)"),
					array("data_billing_street", "varchar(255)"),
					array("data_billing_city", "varchar(255)"),
					array("data_billing_zip", "varchar(255)"),
					array("data_billing_country", "varchar(255)"),
					array("data_billing_name", "varchar(255)"),
					array("data_billing_phone", "varchar(255)"),
					array("data_billing_email", "varchar(255)"),
				);

				foreach($fields as $f)
				{
					$cfields[] = "`".$f[0]."` ".$f[1];
					$ifields[] = "`".$f[0]."`";
				}
				
				$cfieldsql = implode(", ", $cfields);
				$ifieldsql = implode(", ", $ifields);

				$this->db_query("CREATE TABLE rfp (`aw_oid` int primary key, ".$cfieldsql.")");

				$ol = new object_list(array(
					"class_id" => CL_RFP,
				));
				foreach($ol->arr() as $o)
				{
					$values = array();
					foreach($fields as $f)
					{
						$values[] = "'".htmlspecialchars($o->meta($f[0]), ENT_QUOTES)."'";
					}
					$valuesql = implode(",", $values);
					$this->db_query("INSERT INTO rfp(`aw_oid`, ".$ifieldsql.") VALUES('".$o->id()."', ".$valuesql.")");
					
				}
				return true;
			}
		}
	}
}
?>
