<?php

// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp_manager.aw,v 1.62 2008/08/21 07:22:42 tarvo Exp $
// rfp_manager.aw - RFP Haldus 
/*

@classinfo syslog_type=ST_RFP_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=tarvo allow_rte=2

@default table=objects
@default group=general

@property default_currency type=relpicker reltype=RELTYPE_DEFAULT_CURRENCY store=connect
@caption Vaikevaluuta

@property default_language type=select field=meta mehtod=serialize
@caption Vaikekeel

@property default_conference_planner type=relpicker reltype=RELTYPE_DEFAULT_WEBFORM field=meta mehtod=serialize
@caption Vaike tellimuse vorm

@property copy_redirect type=relpicker reltype=RELTYPE_REDIR_DOC field=meta method=serialize
@caption Edasisuunamisdokument

@property room_folder type=relpicker multiple=1 reltype=RELTYPE_ROOM_FOLDER field=meta method=serialize
@caption Ruumide kaust

@property catering_room_folder type=relpicker multiple=1 reltype=RELTYPE_CATERING_ROOM_FOLDER field=meta method=serialize
@caption Toitlustuse ruumide kaust

@property prod_vars_folder type=relpicker reltype=RELTYPE_PROD_VARS_FOLDER field=meta method=serialize
@caption Tootepakettide asukoht

@property meta_folder type=relpicker reltype=RELTYPE_META_OBJECT_FOLDER store=connect
@caption Toat&uuml;&uuml;pide kaust

@property table_form_folder type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Lauaasetuste kaust

@property theme_folder type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Teemade kaust

@property event_type_folder type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption &Uuml;rituse t&uuml;&uuml;pide kaust

@property resources_price_rooms type=relpicker multiple=1 reltype=RELTYPE_RESOURCE_ROOMS field_meta method=serialize
@caption Tellimuste ruumid

@property contact_preference_folder type=relpicker multiple=1 reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Kontakteerumise eelistuste kaust

@property default_table type=table no_caption=1 store=no

@groupinfo settings caption="Seaded"

	@groupinfo packages caption="Paketid" parent=settings
		@default group=packages

		@property packages_tb type=toolbar store=no no_caption=1

		@property packages_folder type=relpicker reltype=RELTYPE_PACKAGE_FOLDER field=meta method=serialize
		@caption Pakettide kaust

		@property packages_tbl type=table store=no no_caption=1
	
	@groupinfo rooms caption="Ruumid" parent=settings
		@property rooms_table type=table store=no no_caption=1 group=rooms

	@groupinfo resources caption="Ressursid" parent=settings
		@property resources_table type=table store=no no_caption=1 group=resources

@groupinfo raports caption="Raportid"
@default group=raports
	@property raports_tb type=toolbar no_caption=1 store=no

	@layout raports_hsplit type=hbox group=raports width=25%:75%
		@layout raports_search type=vbox closeable=1 area_caption="Otsing" parent=raports_hsplit
			@property raports_search_from_date type=date_select parent=raports_search captionside=top store=no
			@caption Alates

			@property raports_search_until_date type=date_select parent=raports_search captionside=top store=no
			@caption Kuni

			@property raports_search_covering type=chooser multiple=1 orient=vertical parent=raports_search captionside=top store=no
			@caption Mille l&otilde;ikes

			@property raports_search_with_products type=chooser multiple=1 parent=raports_search captionside=top store=no
			@caption Koos toodetega

			@property raports_search_group type=chooser parent=raports_search captionside=top store=no
			@caption Grupeeri

			@property raports_search_rooms type=select multiple=1 parent=raports_search captionside=top store=no
			@caption Ruumid

			@property raports_search_rfp_status type=select parent=raports_search captionside=top store=no
			@caption Staatus

			@property raports_search_rfp_submitter type=textbox parent=raports_search captionside=top store=no
			@caption Klient

			@property raports_search_submit type=submit parent=raports_search store=no no_caption=1
			@caption Otsi

		@layout raports_table type=vbox closeable=1 area_caption="Raportid" parent=raports_hsplit
			/@property raports_table type=table no_caption=1 store=no parent=raports_table
			@property raports_table type=text no_caption=1 store=no parent=raports_table


@groupinfo rfps caption="Tellimused"
@groupinfo rfps_active caption="Aktiivsed" parent=rfps
@groupinfo rfps_archive caption="Arhiiv" parent=rfps
@default group=rfps_active,rfps_archive

	@property rfps_tb type=toolbar store=no no_caption=1

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


@groupinfo terms caption="Tingimused"
@default group=terms

	@property cancel_and_payment_terms type=textarea richtext=1 table=objects field=meta method=serialize
	@caption Konvererntside annuleerimis- ja maksetingimused

	@property accomondation_terms type=textarea richtext=1 table=objects field=meta method=serialize
	@caption Majutuse annuleerimis- ja maksetingimused

@groupinfo transl caption=T&otilde;lgi
@default group=transl

	@property transl type=callback callback=callback_get_transl store=no
	@caption T&otilde;lgi

@reltype REDIR_DOC value=1 clid=CL_DOCUMENT
@caption Konverentsiplaneerija

@reltype ROOM_FOLDER value=2 clid=CL_MENU
@caption Ruumide kaust

@reltype PACKAGE_FOLDER value=3 clid=CL_MENU,CL_META
@caption Pakettide kaust

@reltype PROD_VARS_FOLDER value=4 clid=CL_MENU,CL_META
@caption Tootepakettide kaust

@reltype CATERING_ROOM_FOLDER value=5 clid=CL_MENU
@caption Toitluste ruumide kaust

@reltype DEFAULT_CURRENCY clid=CL_CURRENCY value=6
@caption RFP vaikevaluuta

@reltype META_OBJECT_FOLDER clid=CL_FOLDER value=7
@caption Muutujate kaust

@reltype DEFAULT_WEBFORM clid=CL_CONFERENCE_PLANNING value=8
@caption RFP Veebivorm

@reltype FOLDER clid=CL_MENU,CL_META value=9
@caption Kaust

*/


define("RFP_RAPORT_TYPE_ROOMS", 1);
define("RFP_RAPORT_TYPE_HOUSING", 2);
define("RFP_RAPORT_TYPE_RESOURCES", 3);
define("RFP_RAPORT_TYPE_CATERING", 4);
define("RFP_RAPORT_TYPE_ADDITIONAL_SERVICES", 5);

class rfp_manager extends class_base
{
	function rfp_manager()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/rfp_manager",
			"clid" => CL_RFP_MANAGER
		));

		$this->trans_props = array(
			"cancel_and_payment_terms", "accomondation_terms"
		);

		$this->raport_types = array(
			1 => t("Ruumid"),
			2 => t("Majutus"),
			3 => t("Ressursid"),
			4 => t("Toitlustus"),
			5 => t("Lisateenused"),
		);
		$this->tpl_subs = array(
			1 => "ROOMS",
			2 => "HOUSING",
			3 => "RESOURCES",
			4 => "CATERING",
			5 => "ADDITIONAL_SERVICES",
		);

		$this->search_param_covering = array(
			1 => t("K&otilde;ik"),
			2 => t("Ruumid"),
			3 => t("Toitlustus"),
			4 => t("Majutus"),
			5 => t("Ressursid"),
			6 => t("Lisateenused"),
		);
		$this->rfpm = obj($this->get_sysdefault());
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		// little hack, but hey.. nothing to do..
		if(substr($prop["name"], 0, 15) == "raports_search_")
		{
			$prop["value"] = $arr["request"][$prop["name"]];
		}

		switch($prop["name"])
		{
			//-- get_property --//
			case "default_language":
				$l = get_instance("core/trans/pot_scanner");
				$tl = $l->get_langs();
				foreach(aw_ini_get("languages.list") as $key => $ldat)
				{
					if(!in_array($ldat["acceptlang"], $tl))
					{
						continue;
					}
					$opts[$key] = $ldat["name"];
				}
				$prop["options"] = $opts;
			break;
			// search
			case "raports_search_covering":
				$prop["options"] = $this->search_param_covering;
				break;
			case "raports_search_with_products":
				$prop["options"] = array(
					"1" => t("Koos toodetega"),
				);
				break;
			case "raports_search_group":
				$prop["options"] = array(
					"1" => t("Ajaliselt"),
					"2" => t("Klientide l&otilde;ikes"),
				);
				break;
			case "raports_search_rooms":
				$ol = $arr["obj_inst"]->get_rooms_from_room_folder();
				$ol->add($arr["obj_inst"]->get_rooms_from_catering_room_folder());
				foreach($ol->arr() as $oid => $obj)
				{
					$prop["options"][$oid] = $obj->name(); 
				}
				break;
			case "raports_search_rfp_status":
				$rfp = get_instance(CL_RFP);
				$prop["options"] = array(
					"0" => t("K&otilde;ik"),
				);
				$prop["options"] += $rfp->get_rfp_statuses();
				break;

			// search end
			case "default_currency":
				if($prop["value"] == "")
				{
					$ol = new object_list(array(
						"class_id" => CL_CURRENCY,
						"lang_id" => array(),
					));
					$cur = reset($ol->arr());
					$prop["options"] = array(
						$cur->id() => $cur->name(),
					);
					$prop["selected"] = $cur->id();
				}
				break;
			case "s_name":
			case "s_org":
			case "s_contact":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;
			case "s_time_to":
			case "s_time_from":
				$_t = &$arr["request"][$prop["name"]];
				if($_t)
				{
					$time = mktime(0,0,0,$_t["month"], $_t["day"], $_t["year"]);
					$prop["value"] = $time;
				}
				else
				{
					$prop["value"] = -1;
				}
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
					"name" => "created",
					"caption" => t("Loodud"),
					"type" => "time",
					"numeric" => 1,
					"format" => "d.m.Y",
					"chgbgcolor" => "urgent_col",
				));
				$t->define_field(array(
					"name" => "popup",
					"caption" => t("Tegevus"),
					"align" => "center",
					"chgbgcolor" => "urgent_col",
				));
				$t->set_default_sortby("created");
				$t->set_default_sorder("desc");
				$rrr = get_instance(CL_RFP);
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
					$c = array("data_billing_phone", "data_billing_email");
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
					if(($sd = $obj->prop("data_gen_arrival_date_admin"))>1)
					{
						$date_period = date('d.m.Y, H:i', $sd);
					}
					if(($ed = $obj->prop("data_gen_departure_date_admin"))>1)
					{
						$date_period .= " - ".date('d.m.Y, H:i', $ed);
					}
					$t->define_data(array(
						"function" => html::href(array(
							"caption" => ($_t = $obj->prop("data_gen_function_name"))?$_t:(($_n = $obj->name())?$_n:t('(Nimetu)')),
							/*"url" => "#",
							"onClick" => "aw_popup_scroll(\"".$this->mk_my_orb("show_overview", array(
								"oid" => $oid
							))."\",\"hey\",600,600);",
							*/
							"url" => $this->mk_my_orb("change", array(
								"id" => $oid,
								"return_url" => get_ru(),
							),CL_RFP),
						)),
						"org" => $obj->prop("data_subm_organisation"),
						"response_date" => (($rd = $obj->prop("data_gen_response_date_admin"))>1)?date('d.m.Y, H:i', $rd):"-",
						"date_period" => $date_period,
						"acc_need" => ($obj->prop("data_gen_accommodation_requirements") == 1)?t("Jah"):t("Ei"),
						"delegates" => $obj->prop("data_gen_attendees_no"),
						"contact_pers" => $obj->prop("data_billing_contact"),
						"contacts" => join(", ", $contacts),
						"created" => $obj->created(),
						"popup" => $this->gen_popup($oid),
						"urgent_col" => $urgent_col,
					));
				}
				$t->sort_by();
				break;
		};
		return $retval;
	}

	function _get_raports_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "print",
			"url" => aw_url_change_var("action", "search_result_print_output"),
			"img" => "print.gif",
		));
		$tb->add_button(array(
			"name" => "pdf",
			"url" => aw_url_change_var("action", "search_result_export_pdf"),
			"img" => "ftype_pdf.gif",
		));
	}

	function _get_raports_table($arr)
	{
		$from = mktime(0, 0, 0, $arr["request"]["raports_search_from_date"]["month"], $arr["request"]["raports_search_from_date"]["day"], $arr["request"]["raports_search_from_date"]["year"]);
		$to = mktime(0, 0, 0, $arr["request"]["raports_search_until_date"]["month"], $arr["request"]["raports_search_until_date"]["day"], $arr["request"]["raports_search_until_date"]["year"]);
		$res = $this->search_rfp_raports(array(
			"from" => $from,
			"to" => $to,
			"search" => $arr["request"]["raports_search_covering"],
			"rooms" => (is_array($arr["request"]["raports_search_rooms"]) AND count($arr["request"]["raports_search_rooms"]))?$arr["request"]["raports_search_rooms"]:NULL,
			"rfp_status" => $arr["request"]["raports_search_rfp_status"],
			"client" => $arr["request"]["raports_search_rfp_submitter"],
		));

		$filters = false;
		if($arr["set_filters"])
		{
		// set what filters where used
			$filters = array();
			$filters[t("Kuup&auml;evavahemik")] = sprintf("%s kuni %s", date("d.m.Y", $from), date("d.m.Y", $to));
			if(is_array($arr["request"]["raports_search_covering"]))
			{
				unset($arr["request"]["raports_search_covering"][1]);
			}
			else
			{
				unset($arr["request"]["raports_search_covering"]);
			}
			if(count($arr["request"]["raports_search_covering"]))
			{
				$cov = array_intersect_key($this->search_param_covering, $arr["request"]["raports_search_covering"]);
				$filters[t("Mille l&otilde;ikes")] = join(", ", $cov);
			}
			if(is_array($arr["request"]["raports_search_rooms"]) and count($arr["request"]["raports_search_rooms"]))
			{
				foreach($arr["request"]["raports_search_rooms"] as $room)
				{
					$rms[] = obj($room)->name();
				}
				$filters[t("Ruumid")] = join(", ", $rms);
			}
			if($arr["request"]["raports_search_rfp_status"])
			{
				$rfp = get_instance(CL_RFP);
				$stats = $rfp->get_rfp_statuses();
				$filters[t("Staatus")] = $stats[$arr["request"]["raports_search_rfp_status"]];
			}
			if(strlen($arr["request"]["raports_search_rfp_submitter"]))
			{
				$filters[t("Klient")] = $arr["request"]["raports_search_rfp_submitter"];
			}
		}

		$arr["prop"]["value"] = $this->display_search_result($res, ($arr["request"]["raports_search_group"] == 2)?true:false, ($arr["request"]["raports_search_with_products"])?true:false, $filters, $arr["with_header_and_footer"]);
	}

	/** Returns nicely formatted search result 
		@attrib api=1
		@param result type=array
			Search result from search_rfp_raports()
		@returns
			Parsed html
	 **/
	public function display_search_result($result, $gr_by_client = false, $with_products = false, $show_used_filters = false, $with_header_and_footer = false)
	{
		$this->read_template("search_result.tpl");
		if(is_array($result) && count($result))
		{
			$cfgu = get_instance("cfg/cfgutils");
			$rfp_props = $cfgu->load_properties(array(
				"clid" => CL_RFP,
			));
			$min_time = mktime(0,0,0,0,0, 2000);
			$cur_time = time();
			foreach($result as $data)
			{
				$rfp = $data["rfp"]?obj($data["rfp"]):false;
				$room = $data["room"]?obj($data["room"]):false;
				$row_vars = array(
					"from_date" => date("d.m.Y", $data["start1"]),
					"from_time" => date("H:i", $data["start1"]),
					"to_date" => date("d.m.Y", $data["end"]),
					"to_time" => date("H:i", $data["end"]),
					"room" => $room?$room->name():t("-"),
					"people_count" => $data["people_count"],
					"raport_type" => ($data["result_type"] and !$data["empty_result_type"])?$this->raport_types[$data["result_type"]]:t("-"),
				);
				if($rfp)
				{
					foreach($rfp_props as $prop => $propdata)
					{
						if(substr($prop, 0, 5) == "data_")
						{
							$pval = $rfp->prop($prop);
							if($this->can("view", $pval))
							{
								$_t = obj($pval);
								$pval = $_t->name();
							}
							elseif($pval > $min_time AND $pval < $cur_time)
							{
								$rfp_prop_values[$prop."_date"] = date("d.m.Y", $pval);
								$rfp_prop_values[$prop."_time"] = date("H:i", $pval);
							}

							$rfp_prop_values[$prop] = $pval;
							$rfp_prop_captions[$prop."_caption"] = $propdata["caption"];
							$rfp_prop_empty[$prop."_date"] = "";
							$rfp_prop_empty[$prop."_time"] = "";
						}

						if($prop == "confirmed")
						{
							$inst = $rfp->instance();
							$st = $inst->get_rfp_statuses();
							$rfp_prop_values["confirmed_str"] = $st[$rfp->prop($prop)];
							$rfp_prop_captions["confirmed_caption"] = $propdata["caption"];
							$rfp_prop_empty["confirmed_str"] = "";
							$rfp_prop_empty["confirmed_caption"] = "";
						}
					}
					$ui = get_instance(CL_USER);
					$cper = $ui->get_person_for_uid($rfp->createdby());
					$mper = $ui->get_person_for_uid($rfp->modifiedby());
					$this->vars(array(
						"rfp_createdby_uid" => $rfp->createdby(),
						"rfp_modifiedby_uid" => $rfp->modifiedby(),
						"rfp_createdby_name" => $cper->name(),
						"rfp_modifiedby_name" => $mper->name(),
					));

					$this->vars($rfp_prop_values);
					$this->vars($rfp_prop_captions);
				}
				else
				{
					$rv = obj($data["reservation"]);
					$ui = get_instance(CL_USER);
					$cper = $ui->get_person_for_uid($rv->createdby());
					$mper = $ui->get_person_for_uid($rv->modifiedby());

					$d_name = $d_org = "";
					foreach($rv->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
					{
						$o = $c->to();
						if($o->class_id() == CL_CRM_PERSON)
						{
							$d_name = $o->name();
						}
						elseif($o->class_id() == CL_CRM_COMPANY)
						{
							$d_org = $o->name();
						}
					}
					$rfp_prop_empty = array(  // ugly hack for no-rfp cases
						"rfp_createdby_uid" => $rv->createdby(),
						"rfp_modifiedby_uid" => $rv->modifiedby(),
						"rfp_createdby_name" => $cper->name(),
						"rfp_modifiedby_name" => $mper->name(),
						"data_subm_organisation" => $d_org,
						"data_subm_name" => $d_name,
					);
					$rfp_prop_empty["confirmed_caption"] = t("Staatus");
					if($rv->prop("verified"))
					{
						$rfp_prop_empty["confirmed_str"] = t("Kinnitatud");
					}
					else
					{
						$rfp_prop_empty["confirmed_str"] = t("T&auml;psustamisel");
					}
					$this->vars($rfp_prop_empty);
				}
				$this->vars($row_vars);
				
				$row_type_var = "ROW_TYPE_".$this->tpl_subs[$data["result_type"]];
				if($gr_by_client && $rfp)
				{
					$clients[$rfp->prop("data_subm_name").".".$rfp->prop("data_subm_organisation")] = array(
						"data_subm_name" => $rfp->prop("data_subm_name"),
						"data_subm_organisation" => $rfp->prop("data_subm_organisation"),
					);
					$row_type_html = array();
					$row_type_html[$row_type_var] = $this->parse($row_type_var);
					$this->vars($row_type_html);
					$row_html[$rfp->prop("data_subm_name").".".$rfp->prop("data_subm_organisation")] .= $this->parse("ROW");
				}
				else
				{
					$row_type_html = array();
					$row_type_html[$row_type_var] = $this->parse($row_type_var);
					$this->vars($row_type_html);
					$row_html .= $this->parse("ROW");
				}
				// now lets empty the row
				$empty[$row_type_var] = "";
				$this->vars($empty);

				if($with_products AND ($data["result_type"] == RFP_RAPORT_TYPE_CATERING OR $data["result_type"] ==  RFP_RAPORT_TYPE_RESOURCES))
				{
					$type_ext = array(
						RFP_RAPORT_TYPE_CATERING => "CATERING",
						RFP_RAPORT_TYPE_RESOURCES => "RESOURCES",
					);
					$current_type = $type_ext[$data["result_type"]];
					$loopdata = ($data["result_type"] == RFP_RAPORT_TYPE_CATERING)?$data["products"]:$data["resources"];

					if(count($loopdata))
					{
						$_row_html = "";
						$_tmp = "";
						$this->vars(array(
							"ROW_TYPE_".$current_type."_HAS_PRODUCTS" => "",
							"ROW_TYPE_".$current_type."_PRODUCT" => "",
						));
						foreach($loopdata as $subrow_key => $subrow_data)
						{
							switch($data["result_type"])
							{
								case RFP_RAPORT_TYPE_CATERING:
									$subrow_data["price"] = (double)$subrow_data["price"];
									if($this->can("view", $subrow_data["room"]))
									{
										$subrow_data["room_name"] = obj($subrow_data["room"])->name();
									}
									$subrow_data["product_name"] = obj($subrow_key)->name();
									$subrow_data["product_from_time"] = date("H:i", $subrow_data["start1"]);
									$subrow_data["product_to_time"] = date("H:i", $subrow_data["end"]);
									if(!$subrow_data["sum"] or $subrow_data["sum"] == 0)
									{
										$subrow_data["sum"] = $subrow_data["price"] * $subrow_data["amount"];
										if($subrow_data["discount"])
										{
											$subrow_data["sum"] = $subrow_data["sum"] * ((100 - $subrow_data["discount"]) / 100);
										}
									}
									$subrow_data["sum"] = (strstr($subrow_data["sum"], ","))?$subrow_data["sum"]:number_format($subrow_data["sum"], 2);
									$subrow_data["product_event"] = $subrow_data["product_event"]?$subrow_data["product_event"]:t("Toitlustus");
									if($this->can("view", $subrow_data["var"]))
									{
										$subrow_data["product_event"] = obj($subrow_data["var"])->name();
									}
									break;
								case RFP_RAPORT_TYPE_RESOURCES:
									$subrow_data["resource_name"] = obj($subrow_data["real_resource"])->name();
									$subrow_data["resource_from_time"] = date("H:i", $subrow_data["start1"]);
									$subrow_data["resource_to_time"] = date("H:i", $subrow_data["end"]);
									if($rfp)
									{
										$default_currency = $rfp->prop("default_currency");
									}
									$subrow_data["price"] = $subrow_data["prices"][$default_currency];
									$subrow_data["sum"] = $subrow_data["price"] * $subrow_data["count"];
									break;
							}
							$this->vars($subrow_data);

							$prod_type_var = "ROW_TYPE_".$current_type."_PRODUCT";
							$prod_type_has_var = "ROW_TYPE_".$current_type."_HAS_PRODUCTS";
							$row_type_html = array();
							if($gr_by_client)
							{
								$row_type_html[$prod_type_has_var] .= $this->parse($prod_type_var);
								$this->vars($row_type_html);
								//$row_html[$rfp->prop("data_subm_name").".".$rfp->prop("data_subm_organisation")] .= $this->parse("ROW");
								$_row_html .= $this->parse("ROW");
							}
							else
							{
								$row_type_html[$prod_type_has_var] .= $this->parse($prod_type_var);
								$this->vars($row_type_html);
								//$row_html .= $this->parse("ROW");
								$_row_html .= $this->parse("ROW");
							}
							$row_type_html[$prod_type_var] = "";
							$row_type_html[$prod_type_has_var] = "";
							$this->vars($row_type_html);
						}
						// here we take the rendered product rows and put them inside has_products sub, after what whole table gets the products crap as one single row
						$this->vars(array(
							"ROW_TYPE_".$current_type."_PRODUCT" => $_row_html,
						));
						$_tmp = $this->parse("ROW_TYPE_".$current_type."_HAS_PRODUCTS");
						$this->vars(array(
							"ROW_TYPE_".$current_type."_HAS_PRODUCTS" => $_tmp,
						));
						if($gr_by_client)
						{
							$row_html[$rfp->prop("data_subm_name").".".$rfp->prop("data_subm_organisation")] .= $this->parse("ROW");
						}
						else
						{
							$row_html .= $this->parse("ROW");
						}
						$row_type_html[$prod_type_var] = "";
						$row_type_html[$prod_type_has_var] = "";
						$this->vars($row_type_html);
					}
				}
			}

				
			if($gr_by_client)
			{
				foreach($clients as $key => $data)
				{
					$this->vars($data);
					$row_html_tmp .= $this->parse("CLIENT_ROW");
					$row_html_tmp .= $row_html[$key];
				}
				$row_html = $row_html_tmp;

			}
			$this->vars(array(
				"HEADER" => $this->parse("HEADER"),
				"ROW" => $row_html,
			));
			$html = $this->parse("HAS_RESULT");
		}
		else
		{
			$no_html = $this->parse("HAS_NO_RESULT");
		}

		if($show_used_filters)
		{
			foreach($show_used_filters as $caption => $value)
			{
				$this->vars(array(
					"filter_caption" => $caption,
					"filter_value" => $value,
				));
				$filt_html .= $this->parse("FILTER");
			}
			$this->vars(array(
				"FILTER" => $filt_html,
			));
			$filt = $this->parse("HAS_FILTERS_USED");
		}
		$this->vars(array(
			"current_date" => date("d.m.Y"),
			"current_time" => date("H:i"),
		));
		$this->vars(array(
			"HAS_NO_RESULT" => $no_html,
			"HAS_RESULT" => $html,
			"HAS_FILTERS_USED" => $filt,
			"PRINT_HEADER" => $this->parse("PRINT_HEADER"),
			"PRINT_FOOTER" => $this->parse("PRINT_FOOTER"),
		));

		return $this->parse();
	}

	function _init_rooms_table(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "room",
			"caption" => t("Ruum"),
		));
		$t->define_field(array(
			"name" => "min_hours",
			"caption" => t("Min. tunde"),
		));
		/*
		$t->define_field(array(
			"name" => "min_add_price",
			"caption" => t("Lisahind"),
		));
		foreach($this->rfp_currencies() as $oid => $obj)
		{
			$t->define_field(array(
				"name" => "min_price[".$oid."]",
				"caption" => $obj->name(),
				"parent" => "min_add_price",
			));
		}
		 */
		$t->define_field(array(
			"name" => "max_hours",
			"caption" => t("Max. tunde"),
		));
		$t->define_field(array(
			"name" => "max_add_price",
			"caption" => t("Lisahind &uuml;letunnile"),
		));
		foreach($this->rfp_currencies() as $oid => $obj)
		{
			$t->define_field(array(
				"name" => "max_price[".$oid."]",
				"caption" => $obj->name(),
				"parent" => "max_add_price",
			));
		}
	}
	function _get_rooms_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_rooms_table($arr);
		$extra_data = $arr["obj_inst"]->get_extra_hours_prices();
		foreach($arr["obj_inst"]->get_rooms_from_room_folder("room_fld")->arr() as $room_oid => $room)
		{
			$room = obj($room);
			$data = array(
				"room" => html::obj_change_url($room),
				"min_hours" => html::textbox(array(
					"name" => "rooms_table[".$room_oid."][min_hours]",
					"size" => "10",
					"value" => $extra_data[$room_oid]["min_hours"],
				)),
				"max_hours" => html::textbox(array(
					"name" => "rooms_table[".$room_oid."][max_hours]",
					"size" => "10",
					"value" => $extra_data[$room_oid]["max_hours"],
				)),
			);

			foreach($this->rfp_currencies() as $oid => $obj)
			{
				/*
				$data["min_price[".$oid."]"] = html::textbox(array(
					"value" => $extra_data[$room->id()]["min_prices"][$oid],
					"name" => "rooms_table[".$room_oid."][min_prices][".$oid."]",
					"size" => 10,
				));
				 */
				$data["max_price[".$oid."]"] = html::textbox(array(
					"value" => $extra_data[$room->id()]["max_prices"][$oid],
					"name" => "rooms_table[".$room_oid."][max_prices][".$oid."]",
					"size" => 10,
				));
			}

			$t->define_data($data);
		}
	}

	function _set_rooms_table($arr)
	{
		if(is_array($arr["request"]["rooms_table"]))
		{
			$arr["obj_inst"]->set_extra_hours_prices($arr["request"]["rooms_table"]);
		}
	}

	function _init_resources_table(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "resource",
			"caption" => t("Ressurss"),
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
		));
		foreach($this->rfp_currencies() as $currency_oid => $currency)
		{
			$t->define_field(array(
				"name" => "price_".$currency_oid,
				"caption" => $currency->trans_get_val("name"),
				"parent" => "price",
				"align" => "center",
			));
		}

		$t->set_rgroupby(array(
			"room" => "room"
		));
	}

	function _get_resources_table($arr)
	{
		$this->_init_resources_table($arr);
		$t =& $arr["prop"]["vcl_inst"];
		$t->set_sortable(false);
		$prices = $arr["obj_inst"]->get_resource_default_prices();
		foreach($arr["obj_inst"]->get_rooms_from_room_folder("room_fld")->arr() as $room_oid => $room)
		{
			foreach($room->get_resources() as $resource_oid => $resource)
			{
				$d = array(
					"room" => html::obj_change_url($room),
					"resource" => html::obj_change_url($resource),
				);

				foreach($this->rfp_currencies() as $currency_oid => $currency)
				{
					$d["price_".$currency_oid] = html::textbox(array(
						"name" => sprintf("resource_prices[%s][%s][%s]", $room_oid, $resource_oid, $currency_oid),
						"value" => $prices[$room_oid][$resource_oid][$currency_oid],
						"size" => 10,
					));
				}
				$t->define_data($d);
			}
		}
	}

	function _set_resources_table($arr)
	{
		$arr["obj_inst"]->set_resource_default_prices($arr["request"]["resource_prices"]);
	}


	function _get_rfps_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_RFP), $arr["obj_inst"]->parent());
	}

	function _get_default_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_header(t("S&uuml;steemi vaikimisi objekt"));
		$t->define_field(array(
			"name" => "select",
			"caption" => t("Vali"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Objekt"),
		));
		$ol = new object_list(array(
			"class_id" => $this->clid,
			"site_id" => array(),
			"lang_id" => array(),
		));
		$active = $this->get_sysdefault();
		foreach($ol->arr() as $oid=>$o)
		{
			$t->define_data(array(
				"select" => html::radiobutton(array(
					"name" => "default",
					"value" => $oid,
					"checked" => ($oid == $active)?1:0,
				)),
				"name" => html::get_change_url($oid, array(), $o->name()),
			));
		}
	}

	function _set_default_table($arr)
	{
		$ol = new object_list(array(
			"class_id" => $this->clid,
			"site_id" => array(),
			"lang_id" => array(),
		));
		foreach ($ol->arr() as $item)
		{
			if ($item->flag(OBJ_FLAG_IS_SELECTED) && $item->id() != $arr["request"]["default"])
			{
				$item->set_flag(OBJ_FLAG_IS_SELECTED, false);
				$item->save();
			}
			elseif ($item->id() == $arr["request"]["default"] && !$item->flag(OBJ_FLAG_IS_SELECTED))
			{
				$item->set_flag(OBJ_FLAG_IS_SELECTED, true);
				$item->save();
			};
		};
	}


	function _get_packages_tb(&$arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "new",
			"image" => "new.gif",
			"tooltip" => t("Lisa uus hind"),
		));
		foreach($arr["obj_inst"]->get_packages() as $mt_oid => $pck_data)
		{
			$meta = obj($mt_oid);
			$tb->add_menu_item(array(
				"parent" => "new",
				"name" => "add_under_".$mt_oid,
				"url" => html::get_new_url(CL_ROOM_PRICE, $mt_oid, array(
					"return_url" => get_ru(),
					"pseh" => aw_register_ps_event_handler(
						CL_RFP_MANAGER,
						"handle_new_room_price",
						array(
							"rfp_manager_oid" => $arr["obj_inst"]->id(),
							"rfp_package_oid" => $mt_oid,
						),
						CL_ROOM_PRICE
					),
				)),
				"text" => sprintf(t("'%s' juurde"), $meta->name()),
			));
		}

		$tb->add_button(array(
			"name" => "rem_prcs",
			"img" => "delete.gif",
			"action" => "remove_prices",
			"tooltip" => t("Eemalda valitud hinnad"),
		));
	}

	/** Invoked by pseh, writes newly created room_price id to packages data. for internal use.
		@attrib params=name name=create_new_room_price api=1
	 **/
	function handle_new_room_price($room_price, $arr)
	{
		$rfp_man = obj($arr["rfp_manager_oid"]);
		$packages = $rfp_man->get_packages();
		$packages[$arr["rfp_package_oid"]]["prices"][$room_price->id()] = array();
		$rfp_man->set_packages($packages);
		$rfp_man->save();
	}

	function _init_packages_tbl(&$arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		/*
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
		));
		 */
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Kehtib"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind/in"),
			"align" => "center",
		));
		foreach($this->rfp_currencies() as $cur)
		{
			$t->define_field(array(
				"name" => "price".$cur->id(),
				"caption" => $cur->name(),
				"align" => "center",
				"parent" => "price",
			));
		}
		$t->set_rgroupby(array(
			"name" => "name",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "prices_sel",
		));

	}

	function _get_packages_tbl($arr)
	{
		$this->_init_packages_tbl($arr);
		$t = &$arr["prop"]["vcl_inst"];
		$pk_fld = $arr["obj_inst"]->prop("packages_folder");
		$prices = $arr["obj_inst"]->get_packages();
		$room_price_inst = get_instance(CL_ROOM_PRICE);
		foreach($prices as $meta_oid => $package_data)
		{
			$meta_obj = obj($meta_oid);
			$data = array(
				"name" => $meta_obj->name(),
			);
			$_ent = false;
			foreach(array_reverse(safe_array($package_data["prices"]), true) as $room_price => $currencies) // dont mind the array reverse here
			{
				$_ent = true;
				$room_price = obj($room_price);
				$_date = date("Y.m.d", $room_price->prop("date_from")). " - ".date("Y.m.d", $room_price->prop("date_to"));
				$time_from = $room_price->prop("time_from");
				$time_to = $room_price->prop("time_to");
				$_time = date("H:i", mktime($time_from["hour"], $time_from["minute"], 0, 0, 0, 0)). " - ".date("H:i", mktime($time_to["hour"], $time_to["minute"], 0, 0, 0, 0));
				$weekd = $room_price->prop("weekdays");
				$_weekd = array();
				foreach($weekd as $wd)
				{
					$_weekd[] = $room_price_inst->weekdays[$wd];
				}
				$time = sprintf("%s / %s / %s / %s", html::obj_change_url($room_price), $_time, $_date, join(", ", $_weekd));
				foreach($this->rfp_currencies() as $cur)
				{
					$data["price".$cur->id()] = html::textbox(array(
						"name" => "prices[".$meta_obj->id()."][prices][".$room_price->id()."][".$cur->id()."]",
						"value" => $currencies[$cur->id()],
						"size" => 5,
					));
					$data["time"] = $time;
					$data["prices_sel"] = $meta_oid."][".$room_price->id();
				}
				$t->define_data($data);
			}
			!$_ent?$t->define_data($data):NULL;
		}
	}

	function _set_packages_tbl($arr)
	{
		
		$arr["obj_inst"]->set_packages($arr["request"]["prices"]);
		$arr["obj_inst"]->save();
	}

	function get_sysdefault()
	{
		$active = false;
		$ol = new object_list(array(
			"class_id" => $this->clid,
			"site_id" => array(),
			"lang_id" => array(),
			"flags" => array(
				"mask" => OBJ_FLAG_IS_SELECTED,
				"flags" => OBJ_FLAG_IS_SELECTED
			)
		));
		if (sizeof($ol->ids()) > 0)
		{
			$first = $ol->begin();
			$active = $first->id();
		}
		else
		{
			$rfpm = obj();
			$rfpm->set_class_id(CL_RFP_MANAGER);
			$rfpm->set_name(t("RFP halduskeskkond"));
			$rfpm->set_parent(aw_ini_get("document.default_cfgform"));
			$rfpm->set_status(STAT_ACTIVE);
			$rfpm->save();
			$active = $rfpm->id();
		}
		return $active;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;
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

		// raports search 
		$todo = array("from_date", "until_date","with_products", "group", "covering", "rooms", "rfp_status", "rfp_submitter");
		foreach($todo as $do)
		{
			$arr["args"]["raports_search_".$do] = $arr["request"]["raports_search_".$do];
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
			if($_tmp_from["year"] > 0 && $_tmp_to["year"] > 0)
			{
				$comp = $obj->created();
				$s_f = mktime(0,0,0, $_tmp_from["month"], $_tmp_from["day"], $_tmp_from["year"]);
				$s_t = mktime(0,0,0, $_tmp_to["month"], $_tmp_to["day"], $_tmp_to["year"]);
				if(($s_f != -1 && $s_t <= $comp) || ($s_t != -1 && $s_f >= $comp) || ($s_f != -1 && $s_t == -1))
				{
					unset($rfps[$oid]);
				}
			}

			// func name
			if(strlen($request["s_name"]) && !stristr($obj->prop("data_gen_function_name") , $request["s_name"]) && !stristr($obj->name() , $request["s_name"]))
			{
				unset($rfps[$oid]);
			}

			// org name
			if(strlen($request["s_org"]) && !stristr($obj->prop("data_subm_organisation"), $request["s_org"]))
			{
				unset($rfps[$oid]);
			}

			// contact name
			if(strlen($request["s_contact"]))
			{
				$is = false;
				$name = $obj->prop("data_billing_contact");
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

	/** Finds and returns currencies used by rfp system
		@returns
			array(
				oid => obj
			)
	 **/
	public function rfp_currencies()
	{
		$ol = new object_list(array(
			"class_id" => CL_CURRENCY,
			"lang_id" => array(),
		));
		return $ol->arr();
	}

	/** All mighty rfp raports search engine
		@attrib params=name
		@param rfp_status optional type=int
			Rfp status or 0 for all
		@param rooms optional type=array
			From which rooms to look for (room oid's)
		@param include_products optional type=bool default=false
			Include products in search results if there are any
		@param search optional type=array
			What to search. Options:
			1 => All,
			2 => Rooms,
			3 => Catering,
			4 => Housing,
			5 => Resources,
		@param from optional type=int
			Start time
		@param to optional type=int
			End time
		@param client optional type=string
			searches from rfp submitter name and organisation
	 **/
	public function search_rfp_raports($arr = array())
	{
		$rfps = array(
			"class_id" => CL_RFP,
			"lang_id" => array(),
		);

		$raport_sub_methods = array(
			2 => "rooms",
			3 => "catering",
			4 => "housing",
			5 => "resources",
			6 => "additional_services",
		);

		if($arr["rfp_status"])
		{
			$rfps["confirmed"] = $arr["rfp_status"];
		}

		$rfp_ol = new object_list($rfps);
			
		if(!is_array($arr["search"]) OR in_array(1, $arr["search"]))
		{
			$arr["search"] = array_keys($raport_sub_methods);
		}

		$result = array();
		foreach($arr["search"] as $submethod)
		{
			$method = "_search_rfp_".$raport_sub_methods[$submethod]."_raports";
			if(method_exists($this, $method))
			{
				if(is_array($arr["rooms"]) && $raport_sub_methods[$submethod] == "housing") // housing isn't connected to any rooms.. so, no need to search
				{
					continue;
				}
				if($arr["from"])
				{
					$time = array(
						"start1" => $arr["from"],
						"end" => $arr["to"],
					);
				}
				else
				{
					$time = false;
				}
				$result = array_merge($result, $this->$method($rfp_ol, $time));
			}
		}
		$res_inst = get_instance(CL_RESERVATION);
		foreach($result as $k => $data)
		{
			if($arr["from"] && $data["start1"] < $arr["from"])
			{
				unset($result[$k]);
				continue;
			}
			if($arr["to"] && $data["end"] > $arr["to"])
			{
				unset($result[$k]);
				continue;
			}
			if(is_array($arr["rooms"]) AND $data["room"] AND !in_array($data["room"], $arr["rooms"]))
			{
				unset($result[$k]);
				continue;
			}
			if(strlen($arr["client"]) and $this->can("view", $data["rfp"])) // the smartest thing would be to take those props away from meta and use the filter on the rfp obj list..
			{
				$rfp = obj($data["rfp"]);
				if(!strstr($rfp->prop("data_subm_name"), trim($arr["client"])) AND !strstr($rfp->prop("data_subm_organisation"), trim($arr["client"])))
				{
					unset($result[$k]);
					continue;
				}
			}
			if(!$this->can("view", $data["rfp"])) // this is a separate reservation object, came from catering search function. these need to be handled differenctly. here we set the products for them
			{
				$rv = obj($data["reservation"]);
				$prod_list = $res_inst->get_room_products($rv->prop("resource"));
				foreach($prod_list->arr() as $prod_oid => $prod)
				{
					$amount = $rv->get_product_amount();
					if(!$amount[$prod_id])
					{
						continue;
					}
					$prod_price = $res_inst->get_product_price(array("product" => $prod_oid, "reservation" => $rv->id()));
					$discount = $res_inst->get_product_discount($rv->id());//meta("discount");
					$sum = ($prod_price * $amount[$prod_oid]);
					$sum = ($discount[$prod_oid] > 0 and $discount[$prod_oid])?(((100 - $discount[$prod_oid]) / 100 )* $sum):$sum;

					$result[$k]["products"][$prod_oid] = array(
						"price" => $prod_price,
						"amount" => $amount[$prod_oid],
						"discount" => $discount[$prod_oid],
						"sum" => $sum,
						"room" => $rv->prop("resource"),
						"bronid" => $rv->id(),
						"start1" => $data["start1"],
						"end" => $data["end"],
						"rfp" => false,
					);
				}

			}
		}
		uasort($result, array($this, "_sort_raport_search_result"));
		return $result;
	}
	private function _sort_raport_search_result($a, $b)
	{
		return (($t = $a["start1"] - $b["start1"]) == 0)?$a["end"] - $b["end"]:$t;
	}

	private function _search_rfp_rooms_raports($ol = array(), $time = false)
	{
		$reservations = array();
		foreach($ol->arr() as $oid => $obj)
		{
			$reservations += $obj->get_reservations();
		}
		foreach($reservations as $reservation_id => $data)
		{
			$new = array(
				"room" => $data["resource"],
				"reservation" => $reservation_id,
				"result_type" => RFP_RAPORT_TYPE_ROOMS,
			);
			unset($data["resource"], $data["reservation"]);
			$return[] = $new + $data;
		}
		return $return;
	}

	private function _search_rfp_housing_raports($ol = array(), $time = false)
	{
		$housing = array();
		foreach($ol->arr() as $oid => $obj)
		{
			$_housing[$oid] = $obj->get_housing();
			foreach($_housing[$oid] as $k => $v)
			{
				$v["rfp"] = $oid;
				$housing[] = $v;
			}
		}
		// remapping time params
		foreach($housing as $k => $data)
		{
			$new = array(
				"start1" => $data["datefrom"],
				"end" => $data["dateto"],
				"people_count" => $data["people"],
				"room" => false,
				"result_type" => RFP_RAPORT_TYPE_HOUSING,
			);
			unset($data["datefrom"], $data["dateto"], $data["people"]);
			$return[] = $new + $data;
		}
		return $return;
	}

	private function _search_rfp_resources_raports($ol = array(), $time = false)
	{
		$resources = array();
		foreach($ol->arr() as $oid => $obj)
		{
			$resources = array_merge($resources, safe_array($obj->get_resources()));
		}
		foreach($resources as $data)
		{
			$new = array(
				"room" => $data["resource"],
				"result_type" => RFP_RAPORT_TYPE_RESOURCES,
			);
			unset($data["resource"]);
			$return[] = $new + $data;
		}
		return $return;
	}

	private function _search_rfp_catering_raports($ol = array(), $time = false)
	{
		$prods = array();
		foreach($ol->arr() as $oid => $obj)
		{
			$_prods[$oid] = $obj->get_catering();
			foreach($_prods[$oid] as $k => $v)
			{
				$v["rfp"] = $oid;
				$prods[$k] = $v;
			}
		}
		foreach($prods as $prod_and_rv => $data)
		{
			if($data["amount"] <= 0)
			{
				continue;
			}
			$spl = split("[.]", $prod_and_rv);
			$product_id = $spl[0];
			if(!$this->can("view", $spl[1]) OR !$this->can("view", $spl[0])) // sometimes rel's and objects are removed, but data(oids) remain in metadata.. so we better check first
			{
				continue;
			}
			$reservation = obj($spl[1]);
			$already_used_rvs[] = $reservation->id();
			if(is_array($data["from"]))
			{
				$_from = $reservation->prop("start1");
				$from = mktime($data["from"]["hour"], $data["from"]["minute"], 0, date("m", $_from), date("d", $_from), date("Y", $_from));
			}
			else
			{
				$from = $reservation->prop("start1");
			}
			if(is_array($data["to"]))
			{
				$_to = $reservation->prop("end");
				$to = mktime($data["to"]["hour"], $data["to"]["minute"], 0, date("m", $_to), date("d", $_to), date("Y", $_to));
			}
			else
			{
				$to = $reservation->prop("end");
			}
			$new = array(
				"start1" => $from,
				"end" => $to,
				"room" => $reservation->prop("resource"),
				"people_count" => $reservation->prop("people_count"),
				"reservation" => $reservation->id(),
				"result_type" => RFP_RAPORT_TYPE_CATERING,
				"rfp" => $data["rfp"],
			);
			if(!is_array($return[$reservation->id()]))
			{
				$return[$reservation->id()] = $new;
			}
			$return[$reservation->id()]["products"][$product_id] = $data;
		}
		
		$tmplist = new object_list(array(
			"class_id" => CL_ROOM,
			"parent" => $this->rfpm->prop("catering_room_folder"),
		));
		// little silly ugly hack
		$args = array(
			"class_id" => CL_RESERVATION,
			"oid" => new obj_predicate_not($already_used_rvs),
			"resource" => $tmplist->ids(),
		);
		if($time["start1"] and $time["end"])
		{
			$args["start1"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $time["start1"], $time["end"], "int"); 
			$args["end"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $time["start1"], $time["end"], "int");
		}
		$list = new object_list($args);
		foreach($list->arr() as $oid => $obj)
		{
			$return[$oid] = array(
				"start1" => $obj->prop("start1"),
				"end" => $obj->prop("end"),
				"room" => $obj->prop("resource"),
				"people_count" => $obj->prop("people_count"),
				"reservation" => $obj->id(),
				"result_type" => RFP_RAPORT_TYPE_CATERING,
				"empty_result_type" => true,
				"rfp" => false,
			);
			// i dont put the products here right now, most of the reservations get probably filtered out anyway..
		}
		return $return;
	}
	
	private function _search_rfp_additional_services_raports($ol = array(), $time = false)
	{
		$as = array();
		foreach($ol->arr() as $oid => $obj)
		{
			$tmp = safe_array($obj->get_additional_services());
			foreach($tmp as $k =>  $v)
			{
				$as[] = $v + array(
					"start1" => $v["time"],
					"end" => $v["time"],
					"result_type" => RFP_RAPORT_TYPE_ADDITIONAL_SERVICES,
					"rfp" => $oid,
				);
			}
		}
		return $as;
	}



	/** For internal use, removes prices from packages
		@attrib name=remove_prices params=name all_args=1
	 **/
	function remove_prices($arr)
	{
		if($this->can("view", $arr["id"]))
		{
			$rfp_man = obj($arr["id"]);
			$pck = $rfp_man->get_packages();
			foreach($arr["sel"] as $meta => $room_prices)
			{
				foreach(array_keys($room_prices) as $room_price)
				{
					unset($pck[$meta]["prices"][$room_price]);
					$room_price = obj($room_price);
					$room_price->delete();
				}
			}
			$rfp_man->set_packages($pck);
			$rfp_man->save();
		}
		return $arr["post_ru"];
	}

	/** Used for outputting search results for printing
		@attrib params=name all_args=1 name=search_result_print_output
	 **/
	function search_result_print_output($arr)
	{
		foreach($arr as $k => $v)
		{
			if(substr($k, 0, 15) == "raports_search_")
			{
				$arr["request"][$k] = $v;
			}
		}
		$arr["set_filters"] = true;
		$arr["with_header_and_footer"] = true;
		$this->_get_raports_table(&$arr);
		$print = "<script language=javascript>window.print();</script>";
		die($arr["prop"]["value"].$print);
	}

	/** Used for exporting search results to pdf
		@attrib params=name all_args=1 name=search_result_export_pdf
	 **/
	function search_result_export_pdf($arr)
	{
		foreach($arr as $k => $v)
		{
			if(substr($k, 0, 15) == "raports_search_")
			{
				$arr["request"][$k] = $v;
			}
		}
		$arr["set_filters"] = true;
		$arr["with_header_and_footer"] = true;
		$this->_get_raports_table(&$arr);
		$pdf_gen = get_instance("core/converters/html2pdf");
		die($pdf_gen->gen_pdf(array(
			"filename" => t("Raportid"),
			"source" => $arr["prop"]["value"],
		)));
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}
}
?>
