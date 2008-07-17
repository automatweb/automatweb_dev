<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp.aw,v 1.32 2008/07/17 09:45:26 tarvo Exp $
// rfp.aw - Pakkumise saamise palve 
/*

@classinfo syslog_type=ST_RFP relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=tarvo allow_rte=2

@tableinfo rfp index=aw_oid master_index=brother_of master_table=objects


@default table=objects
@default group=general

	@property conference_planner type=relpicker reltype=RELTYPE_WEBFORM field=meta method=serialize
	@caption Tellimuse vorm

	@property confirmed type=select table=rfp field=confirmed
	@caption Staatus

@default table=rfp
@default group=general

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

			@property data_mf_table type=textbox
			@caption Pea&uuml;ritus

			@property data_mf_event_type type=relpicker reltype=RELTYPE_EVENT_TYPE store=connect
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


	@groupinfo terms caption="Tingimused"
	@default group=terms

		@property cancel_and_payment_terms type=textarea richtext=1
		@caption Konvererntside annuleerimis- ja maksetingimused

		@property accomondation_terms type=textarea richtext=1
		@caption Majutuse annuleerimis- ja maksetingimused

	@groupinfo final_info caption="Tellimuskirjeldus"
		
		@groupinfo final_general caption="&Uuml;ldine" parent=final_info
		@default group=final_general

			@property default_currency type=relpicker reltype=RELTYPE_DEFAULT_CURRENCY store=connect
			@caption Vaikevaluuta

			@property final_rooms type=relpicker multiple=1 reltype=RELTYPE_ROOM store=connect
			@caption Ruumid
			@comment Konverentsi jaoks kasutatavad ruumid

			@property final_catering_rooms type=relpicker multiple=1 reltype=RELTYPE_CATERING_ROOM store=connect
			@caption Toitlustuse ruumid
			@comment Toitlustuse jaoks kasutatavad ruumid

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

			@property additional_information type=textarea rows=20
			@caption Lisainfo

			@property additional_admin_information type=textarea rows=20
			@caption Administraatori lisainfo

		@groupinfo final_prices caption="Ruumid" parent=final_info
		@default group=final_prices

			@property final_add_reservation_tb group=final_prices,final_resource,final_catering no_caption=1 type=toolbar

			@layout add_inf_room type=vbox closeable=1 area_caption="Lisainfo"
				
				@property additional_room_information type=textarea parent=add_inf_room rows=20
				@caption Ruumide lisainfo

			@layout prs_hsplit type=hbox width=30%:70%

				@layout prs_left parent=prs_hsplit type=vbox closeable=1 area_caption=Ruumid&nbsp;ja&nbsp;reserveeringud
					@property prices_tree parent=prs_left type=treeview store=no no_caption=1

				@layout prs_right parent=prs_hsplit type=vbox closeable=1 area_caption=Hinnad
					@property prices_tbl parent=prs_right type=text store=no no_caption=1


		@groupinfo final_catering caption="Toitlustus" parent=final_info
		@default group=final_catering

			@layout add_inf_catering type=vbox closeable=1 area_caption="Lisainfo"
				
				@property additional_catering_information type=textarea parent=add_inf_catering rows=20
				@caption Toitlustuse lisainfo


			@layout cat_hsplit type=hbox width=30%:70%

				@layout cat_left parent=cat_hsplit type=vbox closeable=1 area_caption=Ruumid&nbsp;ja&nbsp;reserveeringud
					@property products_tree parent=cat_left type=treeview store=no no_caption=1

				@layout cat_right parent=cat_hsplit type=vbox closeable=1 area_caption=Tooted
					
					@property products_tbl parent=cat_right type=text store=no no_caption=1

		@groupinfo final_resource caption="Ressursid" parent=final_info
		@default group=final_resource

			@layout add_inf_resource type=vbox closeable=1 area_caption="Lisainfo"
				
				@property additional_resource_information type=textarea parent=add_inf_resource rows=20
				@caption Ressursside lisainfo


			@layout res_hsplit type=hbox width=30%:70%

				@layout res_left parent=res_hsplit type=vbox closeable=1 area_caption=Ruumid&nbsp;ja&nbsp;reserveeringud
					@property resources_tree parent=res_left type=treeview store=no no_caption=1

				@layout res_right parent=res_hsplit type=vbox closeable=1 area_caption=Ressursid
					@property resources_tbl parent=res_right type=table store=no no_caption=1
					

                @groupinfo final_housing caption="Majutus" parent=final_info
                @default group=final_housing

			@layout add_inf_housing type=vbox closeable=1 area_caption="Lisainfo"
				
				@property additional_housing_information type=textarea parent=add_inf_housing rows=20
				@caption Majutuse lisainfo


                        @property housing_tbl type=table store=no no_caption=1



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
	
#reltypes

@reltype ROOM clid=CL_ROOM value=1
@caption Konverentsi toimumiskoht

@reltype CATERING_ROOM clid=CL_ROOM value=10
@caption Konveretsi toitlustuse ruumid

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

@reltype CATERING_RESERVATION clid=CL_RESERVATION value=10
@caption Toitlustuse broneering

@reltype DEFAULT_CURRENCY clid=CL_CURRENCY value=11
@caption Arvutuste vaikevaluuta
*/

define("RFP_STATUS_SENT", 1);
define("RFP_STATUS_CONFIRMED", 2);
define("RFP_STATUS_ON_HOLD", 3);
define("RFP_STATUS_REJECTED", 4);
define("RFP_STATUS_CANCELLED", 5);

class rfp extends class_base
{
	function rfp()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/rfp",
			"clid" => CL_RFP
		));

		$this->rfp_status = array(
			1 => t("Saadetud"),
			2 => t("Kinnitatud"),
			3 => t("T&auml;psustamisel"),
			4 => t("Tagasi l&uuml;katud"),
			5 => t("T&uuml;histatud"),
		);
	}

	private function date_to_stamp($date)
	{
		$day = explode(".", $date["date"]);
		$time = explode(":", $date["time"]);
		$stamp = mktime($time[0], $time[1], 0, $day[1], $day[0], $day[2]);
		return $stamp;
	}

	private function arr_to_date($date)
	{
		$return["date"] = (is_numeric($date["day"])?$date["day"]:0).".".(is_numeric($date["month"])?$date["month"]:0).".".(is_numeric($date["year"])?$date["year"]:0);
		$return["time"] = $date["hour"].":".$date["minute"];
		return $return;
	}

	function get_property($arr)
	{
		//$this->db_query("DROP TABLE `rfp`");die();
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		/*$ignored_props = array(
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
				//return PROP_IGNORE;
			}
			return $retval;
		}*/
		
		// this here deals with props with values to table
		$prop["name"] = (strstr($prop["name"], "ign_") && !strstr($prop["name"], "foreign"))?substr($prop["name"], 4):$prop["name"];
		switch($prop["name"])
		{
			case "confirmed":
				$prop["options"] = $this->get_rfp_statuses();
			break;
			case "cancel_and_payment_terms":
			case "accomondation_terms":
				if($prop["value"] == "")
				{
					$inst = get_instance(CL_RFP_MANAGER);
					$rfpm = $inst->get_sysdefault();
					$rfpmo = obj($rfpm);
					$prop["value"] = $rfpmo->prop($prop["name"]);
				}
				break;
			case "default_currency":
				if($prop["value"] == "")
				{
					$inst = get_instance(CL_RFP_MANAGER);
					$rfpm = $inst->get_sysdefault();
					$rfpmo = obj($rfpm);
					$cur = obj($rfpmo->prop($prop["name"]));
					$prop["options"] = array(
						$cur->id() => $cur->name(),
					);
					$prop["selected"] = $cur->id();
				}
			break;
			case "final_rooms":
			case "final_catering_rooms":
				$prop["selected"] = $arr["obj_inst"]->prop($prop["name"]);
				$type = ($prop["name"] == "final_rooms")?"":"catering_";


				$rfpm = get_instance(CL_RFP_MANAGER);
				$rfpm = $rfpm->get_sysdefault();
				$rfpm = obj($rfpm);
				$conns = $arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_".strtoupper($type)."ROOM",
				));
				foreach($conns as $conn)
				{
					$to = $conn->to();
					$exist[$to->id()] = $to->id();
				}
				$fun = "get_rooms_from_".$type."room_folder";
				$ol = $rfpm->$fun();
				foreach($ol->arr() as $oid => $o)
				{
					if(in_array($oid, $exist))
					{
						continue;
					}
					$arr["obj_inst"]->connect(array(
						"to" => $oid,
						"type" => "RELTYPE_".strtoupper($type)."ROOM",
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
				//if($prop["value"] < 1)
				//{
					$svar = substr($prop["name"], 0, -6);
					if($ov = $arr["obj_inst"]->prop($svar))
					{
						$prop["value"] = $this->date_to_stamp($ov);
					}
				//}
				break;
			case "final_add_reservation_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_menu_button(array(
					"name" => "add",
					"img" => "new.gif",
					"tooltip" => t("Reserveering"),
				));
				$rooms = $this->get_rooms($arr);
				

				$data_name = $arr["obj_inst"]->prop("data_subm_name");
				$data_name = split("[ ]", $data_name);
				$new_reservation_args["person_rfp_fname"] = $data_name[0];
				unset($data_name[0]);
				$new_reservation_args["person_rfp_lname"] = count($data_name)?join(" ", $data_name):"";
				$new_reservation_args["person_rfp_email"] = $arr["obj_inst"]->prop("data_subm_email");
				$new_reservation_args["person_rfp_phone"] = $arr["obj_inst"]->prop("data_subm_phone");
				$new_reservation_args["start1"] = $this->date_to_stamp($arr["obj_inst"]->prop("data_mf_start_date"));
				$new_reservation_args["end"] = $this->date_to_stamp($arr["obj_inst"]->prop("data_mf_end_date"));
				$new_reservation_args["return_url"] = get_ru();
				$new_reservation_args["rfp"] = $arr["obj_inst"]->id();

				
				foreach($rooms as $room)
				{
					$new_reservation_args["resource"] = $room;
					$new_reservation_args["parent"] = $room;
					$url = $this->mk_my_orb("new", $new_reservation_args, CL_RESERVATION);
					$o = obj($room);
					
					$tb->add_menu_item(array(
						"parent" => "add",
						"text" => sprintf(t("Ruumi '%s'"), $o->name()),
						"url" => $url,
					));
					$tb->add_save_button();
				}

				$ol = new object_list(array(
					"class_id" => CL_SPA_BOOKINGS_OVERVIEW,
				));
				$o = $ol->begin();
				$url = $this->mk_my_orb("show_cals_pop", array(
					//"id" => $o->id(),
					"class" => "spa_bookings_overview",
					"pseh" => aw_register_ps_event_handler(
						CL_RFP,
						"handle_new_reservation",
						array(
							"rfp_manager_oid" => $arr["obj_inst"]->id(),
							"rfp_package_oid" => "juhuu",
						),
						CL_RESERVATION
					),
					"rooms" => "0",

				));
				$tb->add_button(array(
					"name" => "cal",
					"tooltip" => t("Kalender"),
					"img" => "icon_cal_today.gif",
					"onClick" => "vals='';f=document.changeform.elements;l=f.length;num=0;for(i=0;i<l;i++){ if(f[i].name.indexOf('sel') != -1 && f[i].checked) {vals += ','+f[i].value;}};if (vals != '') {aw_popup_scroll('$url'+vals,'mulcal',700,500);} else { alert('".t("Valige palun v&auml;hemalt &uuml;ks ruum!")."');} return false;",
				));
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
				elseif($prop["name"] == "products_tbl")
				{
					$prop["value"] = $this->get_products_tbl($arr);
				}
				elseif($prop["name"] == "resources_tbl")
				{
					$prop["value"] == $this->get_resources_tbl($arr);
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
						"default_currency" => $arr["obj_inst"]->prop("default_currency"),
						"define_chooser" => 1,
						"chooser" => "room",
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
				// wtff???
				$prop["selected"] = $prop["value"];
				//$prop["value"] = aw_unserialize($prop["value"]);
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

	function _init_resources_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "resource",
			"caption" => t("Ressurss"),
			"chgbgcolor" => "split",
		));
		
		$curs = $this->gather_reservation_currencys($arr["obj_inst"]);
		
		foreach($curs as $cur)
		{
			$cur = obj($cur);
			$t->define_field(array(
				"name" => "price[".$cur->id()."]",
				"caption" => $cur->name(),
				"chgbgcolor" => "split",
			));
		}
		$t->define_field(array(
			"name" => "discount",
			"caption" => t("Allahindlus %"),
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "amount",
			"caption" => t("Kogus"),
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "comment",
			"caption" => t("Kommentaar"),
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"chgbgcolor" => "split",
		));

		$t->set_rgroupby(array(
			"reservation" => "reservation",
		));

	}
	
	/** Finds and returns currency oids used by rfp system
		@param obj required
			RFP obj
	 **/
	public function gather_reservation_currencys($obj)
	{
		$cs = $obj->connections_from(array(
			"type" => "RELTYPE_RESERVATION",
		));
		foreach($cs as $c)
		{
			$r = $c->to();
			$ress[$r->id()] = $r;
		}
		return $this->_gather_resources_currencys($ress);
	}
	
	function _gather_resources_currencys($reservations = array())
	{
		$curs = array();
		foreach($reservations as $id => $res)
		{
			$room = obj($res->prop("resource"));
			$curs += $room->prop("currency");
		}
		return array_unique($curs);
	}

	function get_resources_tbl(&$arr)
	{
		$this->_init_resources_tbl($arr);
		$total_price = array();
		$t =& $arr["prop"]["vcl_inst"];
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_RESERVATION",
		));

		$currencys_in_use = $this->gather_reservation_currencys($arr["obj_inst"]);
		foreach($conns as $c)
		{
			$res = $c->to();
			$res_inst = get_instance(CL_RESERVATION);
			$resources_calculated_price = $res->get_resources_sum();
			$resources_calculated_price_without_special_discount = $res->get_resources_sum(true);

			$resources_data = $res_inst->get_resources_data($res->id());

			$reservation_room = $res->prop("resource");
			$room_inst = get_instance(CL_ROOM);
			$room_resources = $room_inst->get_room_resources($reservation_room);
			// rows for every resource in reservations
			foreach($room_resources as $k => $resource)
			{
				$data = array(
					"resource_room" => $reservation_room,
					"room_resource" => $k,
					"amount" => $resources_data[$k]["count"],
					"resource" => html::obj_change_url($resource),
					"reservation" => html::obj_change_url($res),
					"discount" => $resources_data[$k]["discount"],
					"comment" => $resources_data[$k]["comment"],
					"time" => date("H:i", $resources_data[$k]["start1"]).t(" - ").date("H:i", $resources_data[$k]["end"]),
					/*
					"time" => $this->_gen_time_form(array(
						"varname" => "time[".$k."]",
						"start1" => $resources_data[$k]["start1"],
						"end" => $resources_data[$k]["end"],
					)),
					 */
					//$resources_data[$k]["time"],
				);

				foreach($resources_data[$k]["prices"] as $oid => $price)
				{
					$cur_reservation_price_from_resources[$oid] += $price;
					$data["price[".$oid."]"] = $price;
				}

				$t->define_data($data);
			}
			//totalpricecalc.com
			foreach($currencys_in_use as $cur)
			{
				$total_price[$cur] += $resources_calculated_price[$cur];
			}

			// special row for every resevation
			$data = array(
				"price" => $resources_price[$k],
				"reservation" => html::obj_change_url($c->to()),
				"resource" => t("Kokku"),
				//"split" => "#CCCCCC",
				"discount" => $resources_discount,
			);
			foreach($resources_calculated_price_without_special_discount as $k => $price)
			{
				$price = $price;
				$data["price[".$k."]"] = $price;
			}
			$t->define_data($data);

		}
		$t->set_sortable(false);
		
		// total-total
		$t->define_data(array(
			"split" => "#CCCCCC",
		));
		$data = array(
			"resource" => html::strong(t("Kokku:")),
		);
		foreach($currencys_in_use as $cur)
		{
			$data["price[".$cur."]"] = $total_price[$cur];
		}
		$t->define_data($data);
	}

	function update_products_info($rvid, $obj)
	{
		if($this->can("view", $rvid))
		{
			$prods = $obj->meta("prods");
			$rvi = get_instance(CL_RESERVATION);
			$rv = obj($rvid);
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
					$prod_sum = $price * $count;
					$prod_sum = number_format($prod_sum - ($prod_sum * $disc)/100,2);
					$key = $prod->id().".".$rvid;
					$prods[$key]["price"] = $price;
					$prods[$key]["amount"] = $count;
					$prods[$key]["discount"] = $disc;
					$prods[$key]["sum"] = $prod_sum;
				}
			}
			$obj->set_meta("prods", $prods);
			$obj->save();
		}
	}

	function get_products_tbl($arr)
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
			$rf = $defo->prop("catering_room_folder");
			$rooms = array(0=>" ");
			$ol = new object_list(array(
				"class_id" => CL_ROOM,
				"parent" => $rf,
			));
			foreach($ol->arr() as $o)
			{
				$rooms[$o->id()] = $o->name();
			}
		}
		classload("vcl/table");
		$t = new aw_table;
		$t->set_sortable(false);
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "amount",
			"caption" => t("Kogus"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "discount",
			"caption" => t("Soodus"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "var",
			"caption" => t("Nimetus"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "room",
			"caption" => t("Ruum"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "comment",
			"caption" => t("Kommentaar"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->set_rgroupby(array(
			"reserv_group" => "reserv_group",
		));
		$conn = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_RESERVATION",
		));
		$rvi = get_instance(CL_RESERVATION);
		$prods = $arr["obj_inst"]->meta("prods");
		foreach($conn as $c)
		{
			if($this->can("view", $c->prop("to")) && in_array($c->prop("to.parent"), $arr["obj_inst"]->prop("final_catering_rooms"))) // that parent & catering is fishy
			{
				$rv = $c->to();
				$rvo = obj($c->to());
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
						$prod_sum = $price * $count;
						$prod_sum = number_format($prod_sum - ($prod_sum * $disc)/100,2);
						$prvid = $prods[$prod->id().".".$rv->id()]["bronid"];
						$times = array();
						if($this->can("view", $prvid))
						{
							$take_times = $prvid;
						}
						else
						{
							$take_times = $rv;
						}
						$prv = obj($take_times);
						$elem_id = $prod->id().".".$rv->id();
						$res_start = $prods[$elem_id]["start1"]?$prods[$elem_id]["start1"]:$prv->prop("start1");
						$res_end = $prods[$elem_id]["end"]?$prods[$elem_id]["end"]:$prv->prop("end");

						$room = $prv->prop("resource");
						$data = array(
							"name" => $prod->name(),
							"price" => html::hidden(array(
								"name" => "prods[".$prod->id().".".$rv->id()."][price]",
								"value" => $price,
							)).$price,
							"amount" => html::hidden(array(
								"name" => "prods[".$prod->id().".".$rv->id()."][amount]",
								"value" => $count,
							)).$count,
							"discount" => html::hidden(array(
								"name" =>  "prods[".$prod->id().".".$rv->id()."][discount]",
								"value" => $disc,
							)).$disc."%",
							"sum" => html::hidden(array(
								"name" => "prods[".$prod->id().".".$rv->id()."][sum]",
								"value" => $prod_sum,
							)).$prod_sum,
							"time" => $this->gen_time_form(array(
								"varname" => "prods[".$elem_id."]",
								"start1" => $res_start,
								"end" => $res_end,
							)),
							"room" => html::select(array(
								"name" => "prods[".$prod->id().".".$rv->id()."][room]",
								"width" => 70,
								//"value" => $room,
								//"selected" => $room,
								"options" => $rooms,
								"selected" => ($_t = $prods[$prod->id().".".$rv->id()]["room"])?$_t:$room,
							)),
							"var" => html::select(array(
								"name" => "prods[".$prod->id().".".$rv->id()."][var]",
								"width" => 70,
								"value" => $prods[$prod->id().".".$rv->id()]["var"],
								"options" => $prodvars,
							)),
							"comment" => html::textarea(array(
								"name" => "prods[".$prod->id().".".$rv->id()."][comment]",
								"value" => $prods[$prod->id().".".$rv->id()]["comment"],
								"cols" => 12,
								"rows" => 2,
							)).html::hidden(array(
								"name" => "prods[".$prod->id().".".$rv->id()."][bronid]",
								"value" => strlen($_t = $prods[$prod->id().".".$rv->id()]["bronid"])?$_t:$rv->id(),
							)),
							"reserv_group" => html::obj_change_url($rvo),
						);
						$t->define_data($data);
					}
				}
			}
		}
		return $t->draw();
	}

	function set_products_tbl($arr)
	{
		$prods = $arr["request"]["prods"];
		if(count($prods))
		{
			$date = $arr["obj_inst"]->prop("data_gen_arrival_date_admin");
			//if($date > 1)
			//{
				foreach($prods as $tmp1 => $data)
				{
					$tmp2 = explode(".", $tmp1);
					if($data["room"] && strlen($data["to"]["hour"]) && strlen($data["to"]["minute"]) && strlen($data["from"]["hour"]) && strlen($data["from"]["minute"]))
					{
						$start1 = mktime($data["from"]["hour"], $data["from"]["minute"], 0, date('m', $date), date('d', $date), date('Y', $date));
						$end = mktime($data["to"]["hour"], $data["to"]["minute"], 0, date('m', $date), date('d', $date), date('Y', $date));
						if(!$data["bronid"])
						{
							$bron = obj();
							$bron->set_class_id(CL_RESERVATION);
							$bron->set_parent($arr["obj_inst"]->id());
							$bron->set_name(date('d.m.Y H:i', $start1)." - ".date('d.m.Y H:i', $end));
							$bron->set_prop("start1", $start1);
							$bron->set_prop("end", $end);
							$bron->set_prop("resource", $data["room"]);
							$bron->save();
							$prods[$tmp1]["bronid"] = $bron->id();
							$bri = $bron->instance();
							$bri->set_products_info($bron->id(), array(
								"amount" => array(
									$tmp2[0] => $data["amount"],
								),
								"change_discount" => array(
									$tmp2[0] => $data["discount"],
								),
							));
							$arr["obj_inst"]->connect(array(
								"to" => $bron->id(),
								"type" => "RELTYPE_CATERING_RESERVATION",
							));
						}
						elseif($this->can("view", $data["bronid"]))
						{
							$bron = obj($data["bronid"]);
							$bron->set_prop("start1", $start1);
							$bron->set_prop("end", $end);
							$bron->set_prop("resource", $data["room"]);
							$bron->save();
							$bri = $bron->instance();
							$params = array(
								"amount" => array(
									$tmp2[0] => $data["amount"],
								),
								"change_discount" => array(
									$tmp2[0] => $data["discount"],
								),
							);
							$bri->set_products_info($bron->id(), $params);
						}
						$prods[$tmp1]["start1"] = $start1;
						$prods[$tmp1]["end"] = $end;
					}
				}
			//}
			$arr["obj_inst"]->set_meta("prods", $prods);
			$arr["obj_inst"]->save();
		}
	}

	function on_save_reservation($arr)
	{
		die(arr($arr));
	}

	function _get_housing_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		
		$t->define_field(array(
			"name" => "datefrom",
			"caption" => t("Alates"),
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "dateto",
			"caption" => t("Kuni"),
			"align" => "center",
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "type",
			"caption" => t("Toat&uuml;&uuml;p"),
			"align" => "center",
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "rooms",
			"caption" => t("Tubade arv"),
			"align" => "center",
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "people",
			"caption" => t("Inimeste arv"),
			"align" => "center",
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center",
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "discount",
			"caption" => t("Soodus"),
			"align" => "center",
			"chgbgcolor" => "split",
		));
		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center",
			"chgbgcolor" => "split",
		));
		
		$rooms = array_reverse($arr["obj_inst"]->meta("housing"));
		if(is_array($rooms))
		{
			$totalsum = 0;
			foreach($rooms as $id => $room)
			{
				$totalsum += $room["sum"];
			}
			$t->define_data(array(
				"discount" => "<strong>".t("Kokku:")."</strong>",
				"sum" => $totalsum,
			));
			$t->define_data(array(
				"split" => "#CCCCCC",
			));
		}
		$t->define_data($this->_get_housing_row($t));
		if(is_array($rooms))
		{
			foreach($rooms as $id => $room)
			{
				$t->define_data($this->_get_housing_row($t, $room, $id));
			}
		}
	}

	function _set_housing_tbl($arr)
	{
		$housing = $arr["request"]["housing"];
		if(is_array($housing))
		{
			$output = array();
			foreach($housing as $id => $row)
			{
				$key = $id;
				if($id == "new")
				{
					$key = count($housing) + 1;
				}
				if(!$row["price"] && !$row["rooms"])
				{
					continue;
				}
				$sum = $row["rooms"] * $row["price"];
				$start = mktime(0,0,1, $row["datefrom"]["month"], $row["datefrom"]["day"], $row["datefrom"]["year"]);
				$end = mktime(0,0,2, $row["dateto"]["month"], $row["dateto"]["day"], $row["dateto"]["year"]);
				$days = ceil(($end - $start) / (60*60*24));
				$sum = $sum*$days;
				if($dc = $row["discount"])
				{
					$sum = round($sum - ($sum*$dc)/100);
				}
				$row["datefrom"] = $start;
				$row["dateto"] = $end;
				$row["sum"] = $sum;
				$output[] = $row;
			}
			$arr["obj_inst"]->set_meta("housing", $output);
		}
	}

	function _get_housing_row(&$t, $room = array(), $id = "new")
	{
		$data = array(
			"datefrom" => html::date_select(array(
				"name" => "housing[".$id."][datefrom]",
				"value" => $room["datefrom"],
				"size" => 12,
				"month_as_numbers" => true,
			)),
			"dateto" => html::date_select(array(
				"name" => "housing[".$id."][dateto]",
				"value" => $room["dateto"],
				"size" => 12,
				"month_as_numbers" => true,
			)),
			"type" => html::select(array(
				"name" => "housing[".$id."][type]",
				"value" => $room["type"],
				"options" => $this->_get_room_types(),
			)),
			"rooms" => html::textbox(array(
				"name" => "housing[".$id."][rooms]",
				"value" => $room["rooms"],
				"size" => 3,
			)),
			"people" => html::textbox(array(
				"name" => "housing[".$id."][people]",
				"value" => $room["people"],
				"size" => 3,
			)),
			"price" => html::textbox(array(
				"name" => "housing[".$id."][price]",
				"value" => $room["price"],
				"size" => 4,
			)),
			"discount" => html::textbox(array(
				"name" => "housing[".$id."][discount]",
				"value" => $room["discount"],
				"size" => 3,
			))."%",
			"sum" => html::hidden(array(
				"name" => "housing[".$id."][sum]",
				"value" => $room["sum"],
			)).$room["sum"],
		);
		return $data;
	}

	function _get_room_types()
	{
		$rfp_admin = get_instance(CL_RFP_MANAGER);
		$s = $rfp_admin->get_sysdefault();
		if(!$s)
		{
			warning(t("Teil on valimata s&uuml;steemi vaike-RFP Halduskeskkond."));
			return array();
		}
		else
		{
			$o = obj($s);
			if(!$this->can("view", $o->prop("meta_folder")))
			{
				warning("Objektis ".html::obj_change_url($o)." on m&auml;&auml;ramata muutujate kataloog.");
				return array();
			}
			$ol = new object_list(array(
				"class_id" => CL_META,
				"parent" => $o->prop("meta_folder"),
			));
			foreach($ol->arr() as $oid => $obj)
			{
				$ret[$oid] = $obj->name();
			}
			return $ret;
		}
		/* old solution
		$types = array(
			1 => t("&Uuml;hekohaline"),
			2 => t("Kahekohaline"),
			3 => t("Sviit"),
		);
		return $types;
		 */
	}

	function get_rooms($arr)
	{
		$type = split("[_]",$arr["request"]["group"]);
		$type = end($type);
		
		if($type == "prices")
		{
			$prop = "final_rooms";
		}
		elseif($type == "catering")
		{
			$prop = "final_catering_rooms";
		}
		else
		{
			$prop = "final_rooms";
		}
		$rm = get_instance(CL_RFP_MANAGER);
		$def = $rm->get_sysdefault();
		if($def)
		{
			$defo = obj($def);
			$rfs = $defo->prop($prop);
		}
		/*
		$rooms = array();
		if(count($rfs))
		{
			$ol = new object_list(array(
				"class_id" => CL_ROOM,
				"lang_id" => array(),
				"parent" => $rfs
			));
			foreach($ol->arr() as $oid=>$o)
			{
				$rooms[$oid] = $oid;
			}
		}
		 */
		$rooms = $arr["obj_inst"]->prop($prop);
		// wtf is this here for??

		/*
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
		 */
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
			die(t("Serveris puudub htmldoc. PDF-i ei saa genereerida"));
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
			"data_contact" => $arr["obj_inst"]->prop("data_billing_contact"),
			"data_street" => $arr["obj_inst"]->prop("data_billing_street"),
			"data_city" => $arr["obj_inst"]->prop("data_billing_street"),
			"data_zip" => $arr["obj_inst"]->prop("data_billing_city"),
			"data_country" => $arr["obj_inst"]->prop("data_billing_country"),
			"data_name" => $arr["obj_inst"]->prop("data_billing_name"),
			"data_phone" => $arr["obj_inst"]->prop("data_billing_phone"),
			"data_email" => $arr["obj_inst"]->prop("data_billing_email"),
			"additional_information" => $arr["obj_inst"]->prop("additional_information"),
			"additional_admin_information" => $arr["obj_inst"]->prop("additional_admin_information"),
			"additional_room_information" => $arr["obj_inst"]->prop("additional_room_information"),
			"additional_catering_information" => $arr["obj_inst"]->prop("additional_catering_information"),
			"additional_resource_information" => $arr["obj_inst"]->prop("additional_resource_information"),
			"additional_housing_information" => $arr["obj_inst"]->prop("additional_housing_information"),
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
		$reservated_rooms = $arr["obj_inst"]->prop("final_rooms");
		$brons = "";
		//$currency = 745;
		$currency = $arr["obj_inst"]->prop("default_currency");
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
		$mgri = get_instance(CL_RFP_MANAGER);
		$mgrid = $mgri->get_sysdefault();
		$mgro = obj($mgrid);
		$extra_bron_prices = $mgro->get_extra_hours_prices();
		foreach($conn as $c)
		{
			$rv = obj($c->prop("to"));
			if(!in_array($rv->prop("resource"), $reservated_rooms))
			{
				continue;
			}
			// roomcrap
			$start = $rv->prop("start1");
			$end = $rv->prop("end");
			$timefrom = date('H:i', $start);
			$timeto = date('H:i', $end);
			$datefrom = date('d.m.Y', $start);
			$dateto = date('d.m.Y', $end);
			$tot_time = ($end - $start) / 3600;

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
				// lets check for min & max hours and their extra prices
				if($tot_time < $extra_bron_prices[$roomid]["min_hours"] && $price < $extra_bron_prices[$roomid]["min_prices"][$currency])
				{
					$price = $extra_bron_prices[$roomid]["min_prices"][$currency];
				}
				if($tot_time > $extra_bron_prices[$roomid]["max_hours"])
				{
					$over = floor($tot_time - $extra_bron_prices[$roomid]["max_hours"]);
					$price += $extra_bron_prices[$roomid]["max_prices"][$currency] * $over;
				}
			}
			$comment = $rv->prop("comment");
			$room_data = array(
				"datefrom" => $datefrom,
				"timefrom" => $timefrom,
				"timeto" => $timeto,
				"dateto" => $dateto,
				"room" => $room,
				"tables" => $tables,
				"people" => $people,
				"comments" => $comment,
				"colspan" => $colspan,
				"separate_price" => $price,
				"price" => $price,
			);
			//$this->vars($room_data);
			if($package)
			{
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
				$room_data["package_data"] = array(
					"unitprice" => $unitprice,
					"package" => $package,
					"price" => $price,
				);
				$room_data["separate_price"] = $price;
				/*
				$this->vars($room_data["package_data"]);
				$tmp = $this->parse("VALUES_PACKAGE");
				$this->vars(array(
					"VALUES_PACKAGE" => $tmp,
				));
				 */
			}
			else
			{
				/*
				$this->vars(array(
					"price" => $price,
				));
				$tmp = $this->parse("VALUES_NO_PACKAGE");
				$this->vars(array(
					"VALUES_NO_PACKAGE" => $tmp,
				));
				 */
			}
			//$bron_totalprice += $price;
			$bronnings[] = $room_data;
			//$brons .= $this->parse("BRON");

			// resources
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
							"from_hour" => date("H", $data["start1"]),
							"from_minute" => date("i", $data["start1"]),
							"to_hour" => date("H", $data["end"]),
							"to_minute" => date("i", $data["end"]),
							"comment" => $data["comment"],
						);
					}
				}
			}
		}

		// roomcrap to be sorted and parseod
		uasort($bronnings, array($this, "_sort_submission_rooms"));
		$mgri = get_instance(CL_RFP_MANAGER);
		$mgrid = $mgri->get_sysdefault();
		foreach($bronnings as $dat)
		{
			$this->vars($dat);
			if($package)
			{
				if($this->can("view", $mgrid))
				{
					$mgr = obj($mgrid);
					$pk_prices = $mgr->meta("pk_prices");
					if(is_array($pk_prices))
					{
						$unitprice = $pk_prices[$package_id][$currency];
					}
				}
				$this->vars($dat["package_data"]);
				$tmp = $this->parse("VALUES_PACKAGE");
				$this->vars(array(
					"VALUES_PACKAGE" => $tmp,
				));
			}
			else
			{
				$this->vars(array(
					"price" => $dat["price"],
				));
				$tmp = $this->parse("VALUES_NO_PACKAGE");
				$this->vars(array(
					"VALUES_NO_PACKAGE" => $tmp,
				));
			}
			$bron_totalprice += $dat["separate_price"];
			$brons .= $this->parse("BRON");


		}


		$this->vars(array(
			"total_colspan" => $colspan - 2,
			"bron_totalprice" => $bron_totalprice,
		));
		$res_sub = "";

		// brons
		uasort($resources, array($this, "_sort_submission_resources"));
		if(count($resources))
		{
			$res = "";
			foreach($resources as $r)
			{
				$this->vars(array(
					"res_name" => $r["name"],
					"res_count" => $r["count"],
					"res_price" => $r["price"],
					"res_total" => $r["total"],
					"res_from_hour" => $r["from_hour"],
					"res_from_minute" => $r["from_minute"],
					"res_to_hour" => $r["to_hour"],
					"res_to_minute" => $r["to_minute"],
					"res_comment" => $r["comment"],
				));
				$res .= $this->parse("RESOURCE");
			}
			$this->vars(array(
				"RESOURCE" => $res,
				"res_total" => $resources_total,
			));
			$res_sub = $this->parse("RESOURCES");
			$totalprice += $resources_total;
		}
		$prods = $arr["obj_inst"]->meta("prods");
		$pd_sub = "";
		uasort(&$prods, array($this, "_sort_submission_products"));
		if(count($prods))
		{
			$prod_total = 0;
			$pds = "";
			foreach($prods as $oids=>$prod)
			{
				$tmp = explode(".", $oids);
				$po = obj($tmp[0]);
				$varname = "";
				$varid = $prod["var"];
				if(is_oid($varid))
				{
					$var = obj($varid);
					$varname = $var->name();
				}
				$room = obj($prod["room"]);
				//gen nice event/room combo
				$evt_room = array();
				if(strlen($varname))
				{
					$evt_room[] = $varname;
				}
				if(strlen($room->name()))
				{
					$evt_room[] = $room->name();
				}
				$this->vars(array(
					"prod_from_hour" => date("H", $prod["start1"]),
					"prod_from_minute" => date("i", $prod["start1"]),
					"prod_to_hour" => date("H", $prod["end"]),
					"prod_to_minute" => date("i", $prod["end"]),
					"prod_event" => $varname,
					"prod_count" => $prod["amount"],
					"prod_prod" => $po->name(),
					"prod_price" => $this->_format_price($prod["price"]),
					"prod_sum" => round($this->_format_price($prod["sum"])),
					"prod_comment" => $prod["comment"],
					"prod_event_and_room" => join(", ",$evt_room),
					"prod_room_name" => $room->name(),
				));
				$pds .= $this->parse("PRODUCT");
				$prod_total += round($this->_format_price($prod["sum"]));
			}
			$this->vars(array(
				"PRODUCT" => $pds,
				"prod_total" => $prod_total,
			));
			$pd_sub = $this->parse("PRODUCTS");
			$totalprice += $prod_total;
		}
		$housing = $arr["obj_inst"]->meta("housing");
		$hs_sub = "";
		uasort($housing, array($this, "_sort_submission_housing"));
		if(count($housing))
		{
			$housing_total = 0;
			$hss = "";
			$types = $this->_get_room_types();
			foreach($housing as $rooms)
			{
				$this->vars(array(
					"hs_from" => date('d.m.Y', $rooms["datefrom"]),
					"hs_to" => date('d.m.Y', $rooms["dateto"]),
					"hs_type" => $types[$rooms["type"]],
					"hs_rooms" => $rooms["rooms"],
					"hs_people" => $rooms["people"],
					"hs_price" => $rooms["price"],
					"hs_discount" => strlen($rooms["discount"])?sprintf("%s %%", $rooms["discount"]):"-",
					"hs_sum" => $rooms["sum"],
				));
				$hss .= $this->parse("ROOMS");
				$housing_total += $rooms["sum"];
			}
			$this->vars(array(
				"ROOMS" => $hss,
				"hs_total" => $housing_total,
			));
			$hs_sub = $this->parse("HOUSING");
			$totalprice += $housing_total;
		}
		$totalprice = round($totalprice, -1);
		$this->vars(array(
			"cancel_and_payment_terms" => $arr["obj_inst"]->prop("cancel_and_payment_terms"),
			"accomondation_terms" => $arr["obj_inst"]->prop("accomondation_terms"),
			"BRON" => $brons,
			"RESOURCES" => $res_sub,
			"PRODUCTS" => $pd_sub,
			"HOUSING" => $hs_sub,
			"totalprice" => $totalprice,
		));
		return $this->parse();
	}

	private function _sort_submission_rooms($a, $b)
	{
		return (join("", array_reverse(split("[.]", $a["datefrom"]))).join(split(":", $a["timefrom"]))) - (join("", array_reverse(split("[.]", $b["datefrom"]))).join(split(":", $b["timefrom"])));
	}

	private function _sort_submission_housing($a, $b)
	{
		return $a["datefrom"].$a["dateto"] - $b["datefrom"].$b["dateto"];
	}

	private function _sort_submission_resources($a, $b)
	{
		return ($a["from_hour"].str_pad($a["from_minute"], 2, "0", STR_PAD_LEFT)) - ($b["from_hour"].str_pad($b["from_minute"], 2, "0", STR_PAD_LEFT));
	}

	private function _sort_submission_products($a, $b)
	{
		return ($a["from"]["hour"].str_pad($a["from"]["minute"], 2, "0", STR_PAD_LEFT)) - ($b["from"]["hour"].str_pad($b["from"]["minute"], 2, "0", STR_PAD_LEFT));
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "final_rooms":
			case "final_catering_rooms":
				break;

			//-- set_property --//
			case "products_tbl":
				if($this->can("view", $arr["request"]["reservation_oid"]))
				{
					$res = get_instance(CL_RESERVATION); 
					$res->set_products_info($arr["request"]["reservation_oid"], $arr["request"]);
					$this->update_products_info($arr["request"]["reservation_oid"], $arr["obj_inst"]);
				}
				else
				{
					$this->set_products_tbl($arr);
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
					foreach($arr["request"]["resources_info"] as $k => $dat)
					{
						$arr["request"]["resources_info"][$k]["start1"] = mktime($dat["from"]["hour"], $dat["from"]["minute"], 0, 0, 0, 0);
						$arr["request"]["resources_info"][$k]["end"] = mktime($dat["to"]["hour"], $dat["to"]["minute"], 0, 0, 0, 0);
					}
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
	
			// tsiisas, these date thingies are really shitty
			// this must be the ugliest solution EVER and this may be the ugliest class EVER!!
			case "data_mf_catering_end":
			case "data_mf_catering_start":
			case "data_mf_end_date":
			case "data_mf_start_date":
			case "data_gen_acc_end":
			case "data_gen_acc_start":
			case "data_gen_departure_date":
			case "data_gen_arrival_date":
			case "data_gen_decision_date":
			case "data_gen_response_date":
				return PROP_IGNORE;
				break;
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
				if(is_array($prop["value"]))
				{
					$new_val = $this->arr_to_date($prop["value"]);
					$svar = substr($prop["name"], 0, -6);
					$arr["obj_inst"]->set_prop($svar, $new_val);
					$arr["obj_inst"]->save();
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


	private function _format_price($price)
	{
		return join("", split("[,]", $price));
	}

	function do_db_upgrade($tbl, $field, $q, $err)
	{

		$fields = array(
			array("additional_information", "text"),
			array("additional_admin_information", "text"),
			array("additional_room_information", "text"),
			array("additional_catering_information", "text"),
			array("additional_resource_information", "text"),
			array("additional_housing_information", "text"),
			array("confirmed", "int"),
			array("cancel_and_payment_terms", "text"),
			array("accomondation_terms", "text"),
			array("final_rooms", "text"),
			array("final_catering_rooms", "text"),
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
		if(strlen($field))
		{
			foreach($fields as $fafa)
			{
				if($field == $fafa[0])
				{
					$this->db_add_col($tbl, array(
						"name" => $field,
						"type" => $fafa[1],
					));
					return true;

				}
			}
		}
		if($tbl == "rfp")
		{
			if($field=="")
			{


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

	/**
		@attirb api=1
		@comment
			generates time select form... resrvation class uses this
	 **/
	function gen_time_form($arr)
	{
		$ret = html::time_select(array(
			"name" => $arr["varname"]."[from]",
			"value" => array(
				"hour" => date("H", $arr["start1"]),
				"minute" => date("i", $arr["start1"]),
			),
		))."<br />".t("kuni")."<br />".html::time_select(array(
			"name" => $arr["varname"]."[to]",
			"value" => array(
				"hour" => date("H", $arr["end"]),
				"minute" => date("i", $arr["end"]),
			),
		));
		return $ret;
	}

	public function get_rfp_statuses()
	{
		return $this->rfp_status;
	}

	/**
		@attrib name=handle_new_reservation all_args=1 params=name
	 **/
	public function handle_new_reservation($arr)
	{
		arr($arr);
		die();
	}
}
?>
