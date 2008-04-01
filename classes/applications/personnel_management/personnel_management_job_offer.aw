<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_job_offer.aw,v 1.21 2008/04/01 16:51:33 instrumental Exp $
// personnel_management_job_offer.aw - T&ouml;&ouml;pakkumine 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_PERSONNEL_MANAGEMENT_CANDIDATE, on_connect_candidate_to_job_offer)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_PERSONNEL_MANAGEMENT_CANDIDATE, on_disconnect_candidate_from_job_offer)

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_OFFER relationmgr=yes r2=yes no_comment=1 prop_cb=1 maintainer=kristo

@default table=objects
@default group=general

tableinfo personnel_management_job index=oid master_table=objects master_index=oid

@property toolbar type=toolbar no_caption=1

@property name type=textbox
@caption Nimi

@property status type=status
@caption Aktiivne

@default field=meta
@default method=serialize

@property company type=relpicker reltype=RELTYPE_ORG
@caption Organisatsioon

@property contact type=relpicker reltype=RELTYPE_CONTACT
@caption Kontaktisik

@property start type=date_select
@caption Konkursi algusaeg

@property end type=date_select
@caption Konkursi t&auml;htaeg

@property profession type=relpicker reltype=RELTYPE_PROFESSION
@caption Ametikoht

@property field type=relpicker reltype=RELTYPE_FIELD store=connect
@caption Valdkond

#@property location type=relpicker reltype=RELTYPE_LOCATION
#@caption Asukoht

@property loc_area type=relpicker reltype=RELTYPE_AREA
@caption Piirkond

@property loc_county type=relpicker reltype=RELTYPE_COUNTY
@caption Maakond

@property loc_city type=relpicker reltype=RELTYPE_CITY
@caption Linn

@property workinfo type=textarea rows=15 cols=60
@caption T&ouml;&ouml; sisu

@property requirements type=textarea rows=15 cols=60
@caption N&otilde;udmised kandidaadile

@property suplementary type=textarea rows=15 cols=60
@caption Kasuks tuleb

@property weoffer type=textarea rows=15 cols=60
@caption Omalt poolt pakume

@property info type=textarea rows=15 cols=60
@caption Lisainfo

@property motivation_letter type=checkbox ch_value=1
@caption Vajalik motivatsioonikiri

@property start_working type=chooser
@caption T&ouml;&ouml;leasumise aeg

@property job_offer_file_url type=text store=no
@caption T&ouml;&ouml;pakkumise fail

@property job_offer_file type=releditor reltype=RELTYPE_JOB_OFFER_FILE rel_id=first props=file table=objects field=meta method=serialize
@caption T&ouml;&ouml;pakkumine failina

@property job_offer_pdf type=text
@caption T&ouml;&ouml;pakkumine PDF-failina

@property apply type=text
@caption Kandideerin

@property rate_scale type=relpicker reltype=RELTYPE_RATE_SCALE
@caption Hindamise skaala

@groupinfo candidate caption="Kandideerimised" submit=no
@default group=candidate

@property candidate_toolbar type=toolbar no_caption=1

@property candidate_table type=table no_caption=1

@groupinfo custom_cfgform caption="CV v&auml;ljad" no_submit=1
@default group=custom_cfgform 

	@property offer_cfgform type=relpicker reltype=RELTYPE_CFGFORM
	@caption CV seadete vorm

	@property default_cfgform type=hidden

	@property new_cfgform_name type=hidden store=no

	@property new_cfgform_tbl type=table store=no

	@property save_cfgform type=checkbox ch_value=1 store=no
	@caption Salvesta seadetevorm

@reltype CANDIDATE value=1 clid=CL_PERSONNEL_MANAGEMENT_CANDIDATE
@caption Kandidatuur

@reltype ORG value=2 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype CONTACT value=3 clid=CL_CRM_PERSON
@caption Kontaktisik

@reltype PROFESSION value=4 clid=CL_CRM_PROFESSION
@caption Ametikoht

#@reltype LOCATION value=5 clid=CL_CRM_COUNTY,CL_CRM_COUNTRY,CL_CRM_CITY
#@caption Asukoht

@reltype FIELD value=6 clid=CL_META
@caption Valdkond

@reltype CFGFORM value=7 clid=CL_CFGFORM
@caption CV seadete vorm

@reltype AREA value=8 clid=CL_CRM_AREA
@caption Piirkond

@reltype COUNTY value=9 clid=CL_CRM_COUNTY
@caption Maakond

@reltype CITY value=10 clid=CL_CRM_CITY
@caption Linn

@reltype RATE_SCALE value=11 clid=CL_RATE_SCALE
@caption Hindamise skaala

@reltype JOB_OFFER_FILE value=12 clid=CL_FILE
@caption T&ouml;&ouml;pakkumine failina

*/

class personnel_management_job_offer extends class_base
{
	function personnel_management_job_offer()
	{
		// change this to the folder under the templates folder, where this classes templates will be,
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/personnel_management/personnel_management_job_offer",
			"clid" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			case "submit":
				arr($prop);
				break;

			case "job_offer_pdf":
				$prop["value"] = html::href(array(
					"caption" => t("T&ouml;&ouml;pakkumine PDF-formaadis"),
					"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id(), "oid" => $arr["obj_inst"]->id())),
					"target" => "_blank",
				));
				break;

			case "apply":
				$prop["value"] = html::href(array(
					"caption" => t("Kandideerin"),
					"url" => $this->mk_my_orb("new", array("alias_to" => $arr["obj_inst"]->id(), "reltype" => 1, "parent" => $arr["obj_inst"]->id(), "return_url" => get_ru()), CL_PERSONNEL_MANAGEMENT_CANDIDATE),
				));
				break;

			case "start_working":
				$date_edit = get_instance("vcl/date_edit");
				$date_edit->fields = array(
					"day" => 1,
					"month" => 1,
					"year" => 1,
				);
				$prop["options"] = array(
					"asap" => t("ASAP"),
					"date_select" => $date_edit->gen_edit_form("start_working_date", time()),
				);
				break;

			/*
			case "new_cfgform_name":
				if($this->can("view", $arr["obj_inst"]->prop("offer_cfgform")))
				{
					$cfgform = obj($arr["obj_inst"]->prop("offer_cfgform"));
					$prop["value"] = $cfgform->name();
				}
				break;
				*/

			case "default_cfgform":
				if($this->can("view", $arr["request"]["personnel_management_id"]))
				{
					$pm = obj($arr["request"]["personnel_management_id"]);
					$prop["value"] = $pm->prop("default_offers_cfgform");
				}
				break;

			case "offer_cfgform":
				if($arr["request"]["personnel_management_id"] && $arr["new"] == 1)
				{
					if($this->can("view", $arr["request"]["personnel_management_id"]))
					{
						$pm = obj($arr["request"]["personnel_management_id"]);
						if($this->can("view", $pm->prop("default_offers_cfgform")))
						{						
							$cfgform = obj($pm->prop("default_offers_cfgform"));
							$prop["value"] = $pm->prop("default_offers_cfgform");
							$prop["options"] = array($cfgform->id() => $cfgform->name());
						}
					}
				}
				break;
			
			case "profession":
				if($this->can("view", $this->owner_org))
				{
					$section_inst = get_instance(CL_CRM_SECTION);
					$prop["options"] = $section_inst->get_all_org_proffessions($this->owner_org);
				}
				break;
				
			case "location":
				$objs = new object_list(array(
					"class_id" => CL_CRM_COUNTY,
				));
				if(!is_array($prop["options"]))
					$prop["options"] = array();
				$prop["options"] += array(0 => t("--vali--")) + $objs->names();
				if(is_oid($arr["request"]["county_id"]))
					$prop["value"] = $arr["request"]["county_id"];
				break;
		}
		return $retval;
	}

	function heavy_implode($sep, $arr)
	{
		$grs = "";
		$grs_c = 0;
		foreach($arr as $gr)
		{
			if(is_array($gr))
			{
				$gr = $this->heavy_implode($sep, $gr);
			}
			$grs .= ($grs_c > 0) ? $sep.$gr : $gr;
			$grs_c++;
		}
		return $grs;
	}

	function _get_job_offer_file_url($arr)
	{
		$o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_JOB_OFFER_FILE");
		if (!$o)
		{
			return PROP_IGNORE;
		}
		
		$file_inst = get_instance(CL_FILE);
		$arr["prop"]["value"] = html::img(array(
				"url" => icons::get_icon_url(CL_FILE),
			)).html::href(array(
			"caption" => $o->name(),
			"url" => $file_inst->get_url($o->id(), $o->name()),
		));
	}

	function _get_new_cfgform_tbl($arr)
	{
		/*
		$cfgform_id = $arr["obj_inst"]->prop("default_cfgform");
		if(!is_oid($cfgform_id))
			$cfgform_id = $arr["obj_inst"]->prop("offer_cfgform");
		*/
		$cfgform_id = $arr["obj_inst"]->prop("offer_cfgform");
		if(!$this->can("view", $cfgform_id))
		{
			return false;
		}
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);
		$t->define_field(array(
			"name" => "group",
			"caption" => t("Grupp"),
		));
		$t->define_field(array(
			"name" => "property",
			"caption" => t("Omadus"),
		));
		$t->define_field(array(
			"name" => "selected",
			"caption" => t("N&auml;ita vormis"),
		));
		$t->define_field(array(
			"name" => "mandatory",
			"caption" => t("Kohustuslik"),
		));
		$t->define_field(array(
			"name" => "jrk",
			"caption" => t("J&auml;rjekord"),
		));

		$cfgform = obj($cfgform_id);
		$cfg_proplist = $cfgform->meta("cfg_proplist");
		$controllers = $cfgform->meta("controllers");
		$pm_id = get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault();
		$pm = obj($pm_id);
		$mand_cont = $pm->prop("mandatory_controller");
		//foreach($cfgform->meta("cfg_proplist") as $pid => $pdata)
		foreach(get_instance(CL_CRM_PERSON)->get_all_properties() as $pid => $pdata)
		{
			if(is_array($pdata["group"]))
			{
				$pdata["group"] = $this->heavy_implode(", ", $pdata["group"]);
			}
			$t->define_data(array(
				"group" => $pdata["group"],
				"property" => $pdata["caption"] ? $pdata["caption"] : $pdata["name"],
				"selected" => html::checkbox(array(
					"name" => "new_cfgform_tbl[selected][".$pid."]",
					"value" => 1,
//					"checked" => 1,
//					"checked" => (($pdata["disabled"] == 1) ? 0 : 1),
					"checked" => array_key_exists($pid, $cfg_proplist) ? 1 : 0,
				)),
				"mandatory" => html::checkbox(array(
					"name" => "new_cfgform_tbl[mandatory][".$pid."]",
					"value" => 1,
//					"checked" => array_key_exists($pid, $controllers),
					"checked" => in_array($mand_cont, $controllers[$pid]),
				)),
				"jrk" => html::textbox(array(
					"name" => "new_cfgform_tbl[jrk][".$pid."]",
					"value" => $cfg_proplist[$pid]["ord"],
					"size" => 4,
				)),
				"jrk_hidden" => $cfg_proplist[$pid]["ord"],
			));
		}
	}

	function _get_candidate_toolbar($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "add",
			"tooltip" => t("Lisa uus kandideerija"),
			"img" => "new.gif",
			"url" => $this->mk_my_orb("new", array("alias_to" => $arr["obj_inst"]->id(), "reltype" => 1, "parent" => $arr["obj_inst"]->id(), "return_url" => get_ru()), CL_PERSONNEL_MANAGEMENT_CANDIDATE),
		));
		$t->add_search_button();
		$t->add_delete_button();
		$t->add_button(array(
			"name" => "send_email",
			"tooltip" => t("Saada e-kiri"),
			"img" => "",
			"action" => "",
		));
		$t->add_button(array(
			"name" => "send_sms",
			"tooltip" => t("Saada SMS"),
			"img" => "",
			"action" => "",
		));
		$t->add_save_button();
	}

	function _get_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"img" => "save.gif",
			"action" => "",
		));
		if(!$arr["new"])
		{
			$tb->add_button(array(
				"name" => "preview",
				"tooltip" => t("Eelvaade"),
				"img" => "preview.gif",
				"url" => $this->mk_my_orb("show", array("id" => $arr["obj_inst"]->id())),
				"target" => "_blank",
			));
			$tb->add_button(array(
				"name" => "genpdf",
				"img" => "pdf_upload.gif",
				"tooltip" => t("Genereeri PDF"),
				"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id(), "oid" => $arr["obj_inst"]->id())),
				"target" => "_blank",
			));
		}
	}
	
	function _get_candidate_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
				
		$t->define_field(array(
			"name" => "person",
			"caption" => t("Kandideerija nimi"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kandidatuuri lisamise aeg"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "age",
			"caption" => t("Vanus"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "phones",
			"caption" => t("Telefonid"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "emails",
			"caption" => t("E-postiaadressid"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "rate",
			"caption" => t("Hinne"),
		));
		$t->define_field(array(
			"name" => "rating",
			"caption" => t("Keskmine hinne"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "intro",
			"caption" => t("Kaaskiri"),
		));
		$t->define_field(array(
			"name" => "intro_file",
			"caption" => t("Kaaskiri (failina)"),
		));
		$t->define_field(array(
			"name" => "addinfo",
			"caption" => t("Lisainfo"),
		));
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
		));			
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		$rate_inst = get_instance(CL_RATE);
		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CANDIDATE")) as $candidate)
		{
			$obj = $candidate->to();
			$id = $obj->id();
			$person = $obj->get_first_obj_by_reltype("RELTYPE_PERSON");
			$intro = $obj->prop("intro");
			$file = $obj->prop("intro_file");
			$fid = $file;
			if($this->can("view", $fid))
			{
				$file = obj($fid);
				$file_inst = get_instance(CL_FILE);
				$intro_file_url = html::img(array(
						"url" => icons::get_icon_url(CL_FILE),
					)).html::href(array(
					"caption" => t("kaaskiri"),
					"url" => $file_inst->get_url($fid, $file->name()),
				));
			}
			else
			{
				$intro_file_url = t("Puudub");
			}

			$intro_url = !empty($intro) ? html::href(array(
				"caption" => t("kaaskiri"),
				"url" => $this->mk_my_orb("view_intro", array("id" => $id), CL_PERSONNEL_MANAGEMENT_CANDIDATE),
				"target" => "_blank",
			)) : t("Puudub");

			$rate = $rate_inst->obj_rating_by_uid(array(
				"oid" => $id,
				"uid" => aw_global_get("uid"),
				"rate_id" => $arr["obj_inst"]->prop("rate_scale"),
			));

			// Phones
			$phones = "";
			foreach($person->connections_from(array("type" => "RELTYPE_PHONE")) as $conn)
			{
				if(strlen($phones) > 0)
				{
					$phones .= ", ";
				}
				$phones .= html::obj_change_url($conn->to());
			}

			// E-mails
			$emails = "";
			foreach($person->connections_from(array("type" => "RELTYPE_EMAIL")) as $conn)
			{
				if(strlen($emails) > 0)
				{
					$emails .= ", ";
				}
				$emails .= html::obj_change_url($conn->to());
			}

			$t->define_data(array(
				"person" => html::href(array(
					"caption" => $person->prop("firstname")." ".$person->prop("lastname"),
					"url" =>  html::get_change_url($person->id(), array("group" => "cv_view", "return_url" => get_ru())),
				)),
				"age" => $person->get_age(),
				"phones" => $phones,
				"emails" => $emails,
				"rate" => html::select(array(
					"name" => "rate[".$id."]",
					"options" => get_instance(CL_RATE_SCALE)->_get_scale($arr["obj_inst"]->prop("rate_scale")),
					"value" => $rate[$arr["obj_inst"]->prop("rate_scale")],
				)),
				"rating" => $rate_inst->get_rating_for_object($id, RATING_AVERAGE, $arr["obj_inst"]->prop("rate_scale")),
				"date" => get_lc_date($obj->created()),
				"id" => $id,
				"intro" => $intro_url,
				"intro_file" => $intro_file_url,
				"addinfo" => html::textarea(array(
					"name" => "addinfo[".$id."]",
					"cols" => 10,
					"rows" => 5,
					"value" => $obj->prop("addinfo"),
				)),
				"change" => html::obj_change_url($person, t("Muuda")),
			));
		}
	}
	
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "new_cfgform_name":
				if(strlen($prop["value"]) > 0)
				{
					$this->set_new_cfgform_tbl($arr);
				}
				break;

			case "candidate_table":
				$this->_set_candidate_table($arr);
				break;
		}
		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _set_candidate_table($arr)
	{
		$i = get_instance(CL_RATE);
		foreach($arr["request"]["rate"] as $c_id => $r)
		{
			$i->add_rate(array(
				"oid" => $c_id,
				"rate_id" => $arr["obj_inst"]->prop("rate_scale"),
				"rate" => array($arr["obj_inst"]->prop("rate_scale") => $r),
				"no_redir" => 1,
				"overwrite_previous" => 1,
			));
		}
		foreach($arr["request"]["addinfo"] as $c_id => $addinfo)
		{
			$o = obj($c_id);
			$o->set_prop("addinfo", $addinfo);
			$o->save();
		}
	}

	function set_new_cfgform_tbl($arr)
	{
		$data = $arr["request"]["new_cfgform_tbl"]["selected"];
		$data2 = $arr["request"]["new_cfgform_tbl"]["mandatory"];
		$data3 = $arr["request"]["new_cfgform_tbl"]["jrk"];
		$cfgform_id = $arr["obj_inst"]->prop("offer_cfgform");
		if(!$this->can("view", $cfgform_id))
		{
			return false;
		}
		$cfgform = obj($cfgform_id);
		$cfgform_inst = $cfgform->instance();
		$new_cfgform_id = $cfgform->save_new();
		$new_cfgform = obj($new_cfgform_id);
		$new_cfgform->set_name($arr["request"]["new_cfgform_name"]);
		$cfg_proplist = $new_cfgform->meta("cfg_proplist");
		$controllers = $new_cfgform->meta("controllers");

		$pm_id = get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault();
		$pm = obj($pm_id);
		$mand_cont = $pm->prop("mandatory_controller");

		foreach($cfg_proplist as $i => $v)
		{
			if(!array_key_exists($i, $data))
			{
				$cfgform_inst->remove_property(array(
					"id" => $new_cfgform_id,
					"property" => $i
				));
			}
		}
		// Remove the controller, if it's not mandatory
		foreach($controllers as $i => $v)
		{
			if(!array_key_exists($i, $data2))
			{
				$v = explode(',', str_replace($mand_cont.',', '', (join(',', $v))));
			}
		}
		// Add the controller, if it's mandatory
		foreach($data2 as $i => $v)
		{
			$controllers[$i][] = $mand_cont;
		}

		// Connect the controller to the cfgform, if any props are mandatory.
		if(count($data2) > 0)
		{
			$new_cfgform->connect(array(
				"to" => $mand_cont,
				"reltype" => "RELTYPE_CONTROLLER",
			));
		}
		$cfg_proplist = $new_cfgform->meta("cfg_proplist");
		foreach($cfg_proplist as $i => $v)
		{
			if(array_key_exists($i, $data3))
			{
				$cfg_proplist[$i]["ord"] = $data3[$i];
			}
		}

		$new_cfgform->set_meta("cfg_proplist", $cfg_proplist);
		$new_cfgform->set_meta("controllers", $controllers);
		$new_cfgform->save();
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CFGFORM")) as $conn)
		{
			$conn->delete();
		}
		$arr["obj_inst"]->set_prop("offer_cfgform", $new_cfgform_id);
	}
	
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function do_stats_table(&$arr)
	{
	
		$table=&$arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "person",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
				
		$table->define_field(array(
			"name" => "views",
			"caption" => t("Vaatamisi"),
			"sortable" => 1,
		));
		
		$query_str = "SELECT *, count(uid) as vaatamisi FROM cv_hits WHERE oid=".$arr['obj_inst']->id()." GROUP by uid";
		
		$this->db_query($query_str);
		$results = array();
		$results = $this->db_fetch_array();
		
		
		foreach($results as $row)
		{
			$user = obj(users::get_oid_for_uid($row["uid"]));
			if(!is_object($user))
			{
				continue;
			}
			
			$person = current($user->connections_from(array("type" => "RELTYPE_PERSON")));
			$person = $person->to();
			if(!is_object($person))
			{
				continue;
			}
			
			
			if($person->prop("default_cv"))
			{
				$person_link = html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $person->prop("default_cv")), CL_PERSONNEL_MANAGEMENT_CV),
						"caption" => $person->name(),
				));
			}
			else
			{
				$person_link = $person->name();
			}
			$table->define_data(array(
				"person" => $person_link,
				"views" => $row["vaatamisi"],
			));
		}
	}
	
	/**
		@attrib name=show nologin=1
		@param id required type=int
	**/
	function show($arr)
	{
		$job_parse_props["company_name"]["publicview"] = true;
		
		$job_parse_props["org_description"]["view"] = true;
		$job_parse_props["phone"]["view"] = true;
		$job_parse_props["email"]["view"] = true;
		
		//T88pakkumise objekt
		$ob = new object($arr["id"]);
		
		//Kui t88pakkumist vaatas t88otsija , siis lisame yhe HITI.
		if($this->my_profile["group"]=="employee")
		{
			$this->add_view(array("id" => $ob->id()));
		}
		
		$company = $ob->get_first_obj_by_reltype("RELTYPE_ORG");
		$company = &obj($company->prop("from"));
		$location = " - ";
		if ($ob->prop("asukoht"))
		{
			$location = &obj($ob->prop("asukoht")); 
			$location = $location->name();
		}
		$this->read_template("show.tpl");
		
		
		//ORGANISATION DESCRIPTION SUB
		if($job_parse_props["org_description"]["view"] == true && $company->prop("tegevuse_kirjeldus"))
		{
			$this->vars(array(
				"org_description" => $company->prop("tegevuse_kirjeldus"),
			));
			$org_description = $this->parse("org_description_sub");
			
			$this->vars(array(
				"org_description" => $org_description,
			));
		}
		
		//PHONE NR SUB
		if($job_parse_props["phone"]["view"] == true)
		{
			if($ob->prop("phone"))
			{
				$phone_nr = &obj($ob->prop("phone"));
				$this->vars(array(
					"phone_nr" => $phone_nr->name(),
				));
				$phone_nr_htm = $this->parse("phone_nr_sub");
				$this->vars(array(
					"phone_nr" => $phone_nr_htm,
				));
			}
		}
		
		//EMAIL SUB
		if($job_parse_props["email"]["view"] == true)
		{
			if($ob->prop("email"))
			{
				$email = &obj($ob->prop("email"));
				
				$this->vars(array(
					"email" => $email->prop("name"),
				));

				$email_htm = $this->parse("email_sub");
				$this->vars(array(
					"email" => $email_htm,
				));
			}
		}
		
		$ks = array();
		if (is_array($ob->prop("tookoormused")))
		{
			foreach($ob->prop("tookoormused") as $tkm)
			{
				$_o = obj($tkm);
				$ks[] = $_o->name();
			}
		}
		
		$this->vars(array(
			"name" => @$ob->prop("name"),
			"company" => $ob->prop("company.name"),
			"location" => $location,
			"sectors" => $tmp_sectors,
			"deadline" => get_lc_date($ob->prop("deadline")),
			"description" => $ob->prop("toosisu"),
			"requirements" => $ob->prop("noudmised"),
			"start_date" => $ob->prop("job_from") > 100 ? get_lc_date($ob->prop("job_from")) : " - ",
			"tookoormused" => join(",", $ks),
			"contact_person" => $ob->prop("contact_person"),
			"job_nr" => $ob->prop("job_nr"),
			"profession" => $ob->prop("profession.name"),
			"org_description_text" => $ob->prop("company.tegevuse_kirjeldus"),
			"about_job" => $ob->prop("workinfo"),
			"requirements" => $ob->prop("requirements"),
			"we_offer" => $ob->prop("weoffer"),
			"apply_link" => $this->mk_my_orb("new", array("alias_to" => $ob->id(), "reltype" => 1, "parent" => $ob->id(), "return_url" => get_ru()), "personnel_management_candidate", true)

		));
		
		return $this->parse();
	}
	
	
	//This funcition will be called by scheduler every day and sets jobs where deadline is over unactive.
	/**
		@attrib name=job_to_not_act
	**/
	function job_to_not_act($arr)
	{
		$not_act_list = new object_list(array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			"deadline" => new obj_predicate_compare(OBJ_COMP_LESS, time()),
		));
		foreach ($not_act_list->arr() as $ob)
		{
			$ob->set_status(STAT_NOTACTIVE);
			$ob->save();
		}
	}
	
	/**
		@attrib name=gen_job_pdf nologin=1
		@param id required type=int
	**/
	function gen_job_pdf($arr)
	{
		$job = obj($arr["id"]);
		$pdf_gen = get_instance("core/converters/html2pdf");
		session_cache_limiter("public");
		die($pdf_gen->gen_pdf(array(
			"filename" => $arr["id"],
			"source" => $this->show(array(
				"id" => $arr["id"]
			))
		)));
	}
	
	
	function add_view($arr)
	{
		if(!$_SESSION["job_view".$arr["id"]])
		{ 
			$this->add_hit($arr["id"]);
			$oid = $arr["id"];
			$uid = aw_global_get("uid");
			$ip = getenv("REMOTE_ADDR");
			$time = time();
			$this->db_query("INSERT INTO cv_hits VALUES(NULL,'$oid', '$uid', '$ip', '$time')");
			$_SESSION["job_view".$arr["id"]] = true;
		}
	}

	function request_execute($arr)
	{
		$args = $_REQUEST;
		$done = aw_global_get("pk_been_here");
		if ($done)
		{
			return false;
		};
		aw_global_set("pk_been_here",1);
		$args["id"] = $arr->id();
		$rv = $this->show($args);
		return $rv;
	}

	/**
		@attrib name=delete_rels
	**/
	function delete_rels($arr)
	{
		foreach ($arr["sel"] as $conn)
		{
			$conn=new connection($conn);
			$conn->delete();
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]), $arr["class"]);
	}

	function on_connect_candidate_to_job_offer($arr)
	{
		$conn = $arr['connection'];
		$target_obj = $conn->to();
		if($target_obj->class_id() == CL_PERSONNEL_MANAGEMENT_JOB_OFFER)
		{
			$target_obj->connect(array(
				'to' => $conn->prop('from'),
				'reltype' => "RELTYPE_CANDIDATE",
			));
		}
	}
	
	function on_disconnect_candidate_from_job_offer($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_PERSONNEL_MANAGEMENT_JOB_OFFER)
		{
			$target_obj->disconnect(array(
				"from" => $conn->prop("from"),
			));
		};
	}
	
	function callback_generate_scripts($arr)
	{
		$f = "
		function save_cfgform()
		{
			if(aw_get_el('save_cfgform').checked)
			{
				aw_get_el('new_cfgform_name').value = prompt('".t("Sisestage salvestatava seadetevormi nimi:")."');
			}
		}

		aw_submit_handler = save_cfgform;";
		return $f;
	}
}
?>
