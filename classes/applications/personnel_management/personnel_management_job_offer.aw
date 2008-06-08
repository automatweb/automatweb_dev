<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_job_offer.aw,v 1.31 2008/06/08 09:07:32 instrumental Exp $
// personnel_management_job_offer.aw - T&ouml;&ouml;pakkumine 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_PERSONNEL_MANAGEMENT_CANDIDATE, on_connect_candidate_to_job_offer)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_PERSONNEL_MANAGEMENT_CANDIDATE, notify_me)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_PERSONNEL_MANAGEMENT_CANDIDATE, on_disconnect_candidate_from_job_offer)

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_OFFER relationmgr=yes r2=yes no_comment=1 prop_cb=1 maintainer=instrumental
@tableinfo personnel_management_job_offer index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@property toolbar type=toolbar no_caption=1

@property name type=textbox
@caption Nimi

@property keywords type=textbox field=keywords table=personnel_management_job_offer
@caption M&auml;rks&otilde;nad

@property status type=status
@caption Aktiivne

@property archive type=checkbox ch_value=1 table=personnel_management_job_offer field=archive default=0
@caption Arhiveeritud

@property company type=relpicker reltype=RELTYPE_ORG store=connect
@caption Organisatsioon

@property sect type=relpicker reltype=RELTYPE_SECTION store=connect
@caption Osakond

@property contact type=relpicker reltype=RELTYPE_CONTACT store=connect
@caption Kontaktisik

@property start type=date_select table=personnel_management_job_offer field=jo_start
@caption Konkursi algusaeg

@property end type=date_select table=personnel_management_job_offer field=jo_end
@caption Konkursi t&auml;htaeg

@property profession type=relpicker reltype=RELTYPE_PROFESSION store=connect
@caption Ametikoht

@property field type=relpicker reltype=RELTYPE_FIELD store=connect
@caption Valdkond

#@property location type=relpicker reltype=RELTYPE_LOCATION store=connect
#@caption Asukoht

@property loc_country type=relpicker reltype=RELTYPE_COUNTRY store=connect
@caption Riik

@property loc_area type=relpicker reltype=RELTYPE_AREA store=connect
@caption Piirkond

@property loc_county type=relpicker reltype=RELTYPE_COUNTY store=connect
@caption Maakond

@property loc_city type=relpicker reltype=RELTYPE_CITY store=connect
@caption Linn

@default field=meta
@default method=serialize

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

@property autoinfo type=checkbox ch_value=1
@caption Kuva lisainfot automaatselt

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

@property apply type=text store=no
@caption Kandideerin

@property rate_scale type=relpicker reltype=RELTYPE_RATE_SCALE
@caption Hindamise skaala

@property notify_me type=checkbox ch_value=1
@caption Soovin teadet kandideerimisest e-postiga

@groupinfo candidate caption=Kandideerimised submit=no
@default group=candidate

	@property candidate_toolbar type=toolbar no_caption=1 no_rte_button=1

	@property candidate_add type=hidden store=no

	@property candidate_table type=table no_caption=1

@groupinfo custom_cfgform caption=CV&nbsp;v&auml;ljad no_submit=1
@default group=custom_cfgform 

	@property offer_cfgform type=relpicker reltype=RELTYPE_CFGFORM
	@caption CV seadete vorm

	@property default_cfgform type=hidden

	@property new_cfgform_name type=hidden store=no

	@property new_cfgform_tbl type=table store=no

	@property save_cfgform type=checkbox ch_value=1 store=no
	@caption Salvesta seadetevorm

@groupinfo send_email_sms caption=Tagasiside&nbsp;kandideerijatele no_submit=1
@default group=send_email_sms

	@layout send_email_sms type=hbox width=20%80%
	
		@layout send_email_sms_left type=vbox area_caption=Saajad parent=send_email_sms

			@property receivers type=select multiple=1 store=no parent=send_email_sms_left

		@layout send_email_sms_right type=vbox parent=send_email_sms area_caption=S&otilde;num

			@property send_email_sms_type type=hidden field=meta method=serialize parent=send_email_sms_right

			@property send_email_sms_select type=hidden field=meta method=serialize parent=send_email_sms_right

			@property typical_select type=relpicker no_edit=1 store=no parent=send_email_sms_right
			@caption T&uuml;&uuml;ps&otilde;num

			@property from type=textbox store=no parent=send_email_sms_right size=20 
			@caption Saatja
			
			@property add_receivers type=textbox store=no parent=send_email_sms_right size=20
			@caption Lisa saajaid

			@property subject type=textbox store=no parent=send_email_sms_right size=20
			@caption Pealkiri

			@property attachment type=releditor store=no parent=send_email_sms_right rel_id=first props=file reltype=RELTYPE_ATTACHMENT
			@caption Manus

			@property message type=textarea store=no parent=send_email_sms_right
			@caption S&otilde;num

			@property message_length type=textbox size=4 store=no parent=send_email_sms_right
			@caption T&auml;hem&auml;rke j&auml;&auml;nud

			@property save_typical type=checkbox ch_value=1 parent=send_email_sms_right store=no
			@caption Salvesta t&uuml;&uuml;ps&otilde;numina

			@property send_it type=submit store=no
			@caption Saada

@groupinfo sent_feedback caption="Saadetud tagasiside"
@default group=sent_feedback
	
	@groupinfo sent_fb_sms caption="SMS" submit=no parent=sent_feedback
	@default group=sent_fb_sms

		@property sent_fb_sms_tbl type=table store=no no_caption=1

	@groupinfo sent_fb_email caption="E-post" submit=no parent=sent_feedback
	@default group=sent_fb_email
	
		@property sent_fb_email_tbl type=table store=no no_caption=1

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

@reltype MOBI_SMS_SENT value=13 clid=CL_SMS_SENT
@caption Kandideerijale saadetud SMSi saatmine

@reltype SECTION value=14 clid=CL_CRM_SECTION
@caption Osakond

@reltype COUNTRY value=15 clid=CL_CRM_COUNTRY
@caption Riik

@reltype TYPICAL_MOBI_SMS value=16 clid=CL_SMS
@caption T&uuml;&uuml;s&otilde;num

@reltype TYPICAL_MAIL_MESSAGE value=17 clid=CL_MESSAGE_TEMPLATE
@caption T&uuml;&uuml;s&otilde;num

@reltype MAIL_SENT value=18 clid=CL_MESSAGE
@caption Kandideerijale saadetud e-kiri

@reltype ATTACHMENT value=19 clid=CL_FILE
@caption E-kirja manus

@reltype NOTIFY_ME value=20 clid=CL_CRM_PERSON
@caption Teata kandideerimisest

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
			case "notify_me":
				$prop["value"] = $arr["obj_inst"]->get_prop($prop["name"]);
				break;

			case "rate_scale":
				if(!obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->rate_candidates)
				{
					return PROP_IGNORE;
				}
				break;

			case "attachment":
				if($arr["request"]["sms"])
				{
					return PROP_IGNORE;
				}
				break;

			case "send_email_sms_select":
				$prop["value"] = $arr["request"]["sel"];
				break;

			case "add_receivers":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "typical_select":
				$prop["options"] = array();
				$prop["options"][0] = t("--vali--");
				if($arr["request"]["sms"])
				{
					foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_TYPICAL_MOBI_SMS")) as $conn)
					{
						if (trim($conn->prop("to.name")) != "")
						{
							$prop["options"][$conn->prop("to")] = $conn->prop("to.name");
						}
					}
				}
				else
				if($arr["request"]["email"])
				{
					foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_TYPICAL_MAIL_MESSAGE")) as $conn)
					{
						$prop["options"][$conn->prop("to")] = $conn->prop("to.name");
					}
				}
				$prop["onchange"] = 'typical_select_data()';
				$prop["value"] = $arr["request"][$prop["name"]];
				$prop["post_append_text"] = html::href(array(
					"url" => "#",
					"alt" => t("Kustuta valitud"),
					"caption" => html::img(array(
						"url" => aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif",
						"border" => 0
					)),
					"onClick" => "submit_changeform(\"delete_typical_sms\");"
				));
				break;

			case "message_length":
				if(!$arr["request"]["sms"])
				{
					return PROP_IGNORE;
				}
				break;

			case "message":
				if($arr["request"]["sms"])
				{
					$prop["onkeyup"] = " if(this.value.length > 160) { this.value = this.value.substr(0, 160); } aw_get_el('message_length').value = 160 - this.value.length;";
				}
				if($this->can("view", $arr["request"]["typical_select"]) && $arr["request"]["sms"])
				{
					$sms = obj($arr["request"]["typical_select"]);
					$prop["value"] = $sms->comment;
				}
				else
				if($this->can("view", $arr["request"]["typical_select"]) && $arr["request"]["email"])
				{
					$mail = obj($arr["request"]["typical_select"]);
					$prop["value"] = $mail->content;
				}
				else
				{
					$prop["value"] = $arr["request"][$prop["name"]];
				}
				break;

			case "send_it":
				$prop["onclick"] = "aw_get_el('click_send').value = 1; if(aw_get_el('save_typical').checked){ aw_get_el('typical_name').value=prompt('".t("Palun sisestage salvestatava t&uuml;&uuml;ps&otilde;numi nimi:")."'); }";
				break;

			case "send_email_sms_type":
				$prop["value"] = $arr["request"]["sms"] ? "sms" : ($arr["request"]["email"] ? "email" : "");
				break;

			case "subject":
				if($arr["request"]["sms"])
				{
					return PROP_IGNORE;
				}
				if($this->can("view", $arr["request"]["typical_select"]) && $arr["request"]["email"])
				{
					$mail = obj($arr["request"]["typical_select"]);
					$prop["value"] = $mail->subject;
				}
				else
				{
					$prop["value"] = $arr["request"][$prop["name"]];
				}
				break;

			case "from":
				if($arr["request"]["sms"])
				{
					return PROP_IGNORE;
				}
				if($arr["request"][$prop["name"]])
				{
					$prop["value"] = $arr["request"][$prop["name"]];
				}
				else
				if($this->can("view", $arr["obj_inst"]->contact))
				{
					$person = obj($arr["obj_inst"]->contact);
					$mls = $person->emails();
					foreach($mls->arr() as $ml)
					{
						$m = $ml->mail;
						break;
					}
					$prop["value"] = $person->name." &lt;".$m."&gt;";
				}
				$prop["autocomplete_source"] = $this->mk_my_orb("autocomp_from");
				$prop["autocomplete_params"] = array("id");
				break;

			case "receivers":
				if(is_array($arr["request"]["sel"]) && count($arr["request"]["sel"]) > 0)
				{
					$ol = new object_list(array(
						"class_id" => CL_CRM_PERSON,
						"oid" => $arr["request"]["sel"],
						"parent" => array(),
						"status" => array(),
						"lang_id" => array(),
						"site_id" => array(),
					));
					if($arr["request"]["sms"])
					{
						foreach($ol->names() as $k => $v)
						{
							$o = obj($k);
							$ph = $o->phones("mobile");
							foreach($ph->names() as $ph_k => $ph_v)
							{
								if($ph_v)
								{
									$ops[$ph_k] = $o->name()." &lt;".$ph_v."&gt;";
								}
							}
						}
					}
					if($arr["request"]["email"])
					{
						foreach($ol->names() as $k => $v)
						{
							$o = obj($k);
							$ml = $o->emails();
							foreach($ml->arr() as $ml_k => $ml_v)
							{
								$m = $ml_v->mail;
								$ops[$ml_k] = $o->name()." &lt;".$m."&gt;";
							}
						}
					}
					$prop["options"] = $ops;
					if(is_array($arr["request"]["receivers"]))
					{
						$prop["value"] = $arr["request"]["receivers"];
					}
					else
					{	// By default everything is selected.
						$prop["value"] = array_keys($ops);
					}
				}
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
					"asap" => t("Niipea kui v&otilde;imalik"),
					"date_select" => $date_edit->gen_edit_form("start_working_date", time()),
				);
				if(!$prop["value"])
				{
					$prop["value"] = "asap";
				}
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
				$ol = new object_list(array(
					"class_id" => CL_CFGFORM,
					"subclass" => CL_CRM_PERSON,
					"parent" => array(),
					"site_id" => array(),
					"lang_id" => array(),
					"status" => array(),
				));
				$prop["options"] = array("" => t("--vali--")) + $ol->names();
				if($arr["request"]["personnel_management_id"] && $arr["new"] == 1)
				{
					if($this->can("view", $arr["request"]["personnel_management_id"]))
					{
						$pm = obj($arr["request"]["personnel_management_id"]);
						if($this->can("view", $pm->prop("default_offers_cfgform")))
						{						
							$cfgform = obj($pm->prop("default_offers_cfgform"));
							$prop["value"] = $pm->prop("default_offers_cfgform");
							//$prop["options"] = array($cfgform->id() => $cfgform->name());
						}
					}
				}
				break;
			
			case "contact":
				if(!is_oid($prop["value"]))
				{
					unset($prop["options"]);
					$props = get_instance(CL_CFGFORM)->get_property_list(CL_PERSONNEL_MANAGEMENT_JOB_OFFER);
					$prop["value"] = "";
					$prop["post_append_text"] = "";
					$prop["type"] = "textbox";
					$prop["autocomplete_source"] = $this->mk_my_orb("autocomp_contact");
					$prop["autocomplete_params"] = array_key_exists("company", $props) ? array("company", "id") : array("id");
				}
				break;

			case "profession":
				/*
				if($this->can("view", $this->owner_org))
				{
					$section_inst = get_instance(CL_CRM_SECTION);
					$prop["options"] = $section_inst->get_all_org_proffessions($this->owner_org);
				}
				*/
			case "loc_country":
			case "loc_area":
			case "loc_county":
			case "loc_city":
				if(!is_oid($prop["value"]))
				{
					unset($prop["options"]);
					$prop["post_append_text"] = "";
					$prop["value"] = "";
					$prop["type"] = "textbox";
					$prop["autocomplete_source"] = $this->mk_my_orb("autocomp_".$prop["name"]);
					$prop["autocomplete_params"] = array();
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

	function _get_sent_fb_email_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "person",
			"caption" => t("Isik"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "ml",
			"caption" => t("E-postiaadress"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "msg",
			"caption" => t("S&otilde;num"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "user",
			"caption" => t("Saatja"),
			"align" => "center",
			"sortable" => 1,
		));
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_MAIL_SENT")) as $conn)
		{
			$to = $conn->to();
			$ml = obj();
			$ml->set_class_id(CL_ML_MEMBER);
			
			if(!$this->can("view", $to->mto_relpicker))
			{
				$this->parse_name_and_email($to->mto, &$data_person, &$data_ml);
				$data_person = strlen($data_person) > 0 ? $data_person : t("M&auml;&auml;ramata");
			}
			else
			{
				$data_person = html::obj_change_url(reset($ml->get_persons(array("id" => $to->mto_relpicker))->ids()));
				$data_ml = html::obj_change_url($to->mto_relpicker, $to->prop("mto_relpicker.mail"));
			}

			$t->define_data(array(
				"person" => $data_person,
				"ml" => $data_ml,
				"msg" => html::obj_change_url($to),
				"time" => date("Y-m-d H:i:s", $to->created()),
				"user" => $to->createdby(),
			));
		}
		$t->set_default_sortby("time");
	}

	function _get_sent_fb_sms_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "person",
			"caption" => t("Isik"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "nr",
			"caption" => t("Number"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "msg",
			"caption" => t("S&otilde;num"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "user",
			"caption" => t("Saatja"),
			"align" => "center",
			"sortable" => 1,
		));
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_MOBI_SMS_SENT")) as $conn)
		{
			$to = $conn->to();
			$sms_arr = $to->connections_to(array("from.class_id" => CL_SMS, "type" => "RELTYPE_SMS_SENT"));
			foreach($sms_arr as $cn)
			{
				$sms = $cn->from();
				break;
			}
			$ph = obj($to->prop("phone"));
			$prs_arr = $ph->connections_to(array("from.class_id" => CL_CRM_PERSON, "type" => "RELTYPE_PHONE"));
			unset($person);
			foreach($prs_arr as $cn)
			{
				$person = $cn->from();
				break;
			}
			$t->define_data(array(
				"person" => is_object($person) ? html::obj_change_url($person->id()) : t("M&auml;&auml;ramata"),
				"nr" => $to->prop("phone.name"),
				"msg" => $sms->comment,
				"time" => date("Y-m-d H:i:s", $to->created()),
				"user" => $to->createdby(),
			));
		}
		$t-> set_default_sortby("time");
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
		if($arr["new"])
		{
			return PROP_IGNORE;
		}

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
		$fields = array(
			"group" => t("Grupp"),
			"property" => t("Omadus"),
			"selected" => t("N&auml;ita vormis"),
			"mandatory" => t("Kohustuslik"),
			"jrk" => t("J&auml;rjekord"),
		);
		$pm_id = get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault();
		$pm = obj($pm_id);
		$fields_conf = $pm->job_offer_cv_tbl;
		foreach($fields_conf as $v)
		{
			$t->define_field(array(
				"name" => $v,
				"caption" => $fields[$v],
			));
		}

		$cff = get_instance(CL_CFGFORM);
		$cfgform = obj($cfgform_id);
		$cfg_proplist = $cff->get_cfg_proplist($cfgform_id);
		$controllers = $cfgform->meta("controllers");
		$mand_cont = $pm->prop("mandatory_controller");
		$pm_default_cfgform = obj($pm->default_offers_cfgform);
		//foreach($cff->get_cfg_proplist($pm->default_offers_cfgform) as $pid => $pdata)
		foreach($pm_default_cfgform->meta("cfg_proplist") as $pid => $pdata)
		//foreach(get_instance(CL_CRM_PERSON)->get_all_properties() as $pid => $pdata)
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
		$pm = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault());
		$parent = $this->can("add", $pm->persons_fld) ? $pm->persons_fld : $arr["obj_inst"]->id();
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "add",
			"tooltip" => t("Lisa uus kandideerija"),
			"img" => "new.gif",
			"url" => $this->mk_my_orb("new", array("ofr_id" => $arr["obj_inst"]->id(), "parent" => $parent, "return_url" => get_ru()), CL_CRM_PERSON),
		));
		$t->add_save_button();
		$t->add_search_button(array(
			"pn" => "candidate_add",
			"clid" => CL_CRM_PERSON,
			"multiple" => 1,
		));
		$t->add_delete_button();
		$t->add_button(array(
			"name" => "send_email",
			"tooltip" => t("Saada e-kiri"),
			"img" => "",
			"action" => "send_email",
			
		));
		$t->add_button(array(
			"name" => "send_sms",
			"tooltip" => t("Saada SMS"),
			"img" => "",
			"action" => "send_sms",
		));
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
				//"url" => $this->mk_my_orb("show", array("id" => $arr["obj_inst"]->id())),
				"url" => aw_ini_get("baseurl")."/".$arr["obj_inst"]->id(),
				"target" => "_blank",
			));
			$tb->add_button(array(
				"name" => "genpdf",
				"img" => "pdf_upload.gif",
				"tooltip" => t("Genereeri PDF"),
				"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id(), "oid" => $arr["obj_inst"]->id())),
				"target" => "_blank",
			));
			$tb->add_button(array(
				"name" => "save_copy",
				"img" => "copy.gif",
				"tooltip" => t("Salvesta koopia"),
				"action" => "save_copy",
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
		if($this->can("view", $arr["obj_inst"]->rate_scale) && obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->rate_candidates)
		{
			$t->define_field(array(
				"name" => "rate",
				"caption" => t("Hinne"),
			));
			$t->define_field(array(
				"name" => "rating",
				"caption" => t("Keskmine hinne"),
				"sortable" => 1,
			));
		}
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
					"url" => $this->mk_my_orb("show_cv", array("id" => $person->id(), "die" => 1, "cv" => "cv/eesti_post.tpl"), CL_CRM_PERSON)
//				       	http://dev.ep.cv.automatweb.com/automatweb/orb.aw?class=crm_person&action=show_cv&id=232&cv=cv%2Feesti_post.tpl&die=1
					//html::get_change_url($person->id(), array("group" => "cv_view", "return_url" => get_ru())),
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
			case "contact":
				if(!is_oid($prop["value"]) && strlen($prop["value"]) > 0 && $prop["value"] != "0")
				{
					$ol_prms = array(
						"class_id" => CL_CRM_PERSON,
						"lang_id" => array(),
						"site_id" => array(),
						"parent" => array(),
					);

					if(is_oid($arr["request"]["company"]))
					{
						$org = $arr["request"]["company"];
					}
					else
					{
						$cp = get_instance(CL_USER)->get_person_for_uid(aw_global_get("uid"));
						$org = $cp->work_contact;
					}
					if($this->can("view", $org))
					{
						$org = obj($org);
						$ids = $org->get_employees()->ids();
						if(count($ids) > 0)
						{
							$ol_prms["oid"] = $ids;
						}
					}
					$ol = new object_list($ol_prms);
					$rev_nms = array_flip($ol->names());
					if(array_key_exists($prop["value"], $rev_nms))
					{
						$arr["obj_inst"]->save();
						$arr["obj_inst"]->set_prop($prop["name"], $rev_nms[$prop["value"]]);
					}
					else
					{
						// Need to save it before I can set it as parent.
						$arr["obj_inst"]->save();
						$new_p = new object;
						$new_p->set_class_id(CL_CRM_PERSON);
						$new_p->set_parent($arr["obj_inst"]->id());
						$new_p->set_name($prop["value"]);
						$new_p->lastname = substr(strrchr($prop["value"], " "), 1);
						$new_p->firstname = substr($prop["value"], 0, strrpos($prop["value"], " "));
						if($this->can("view", $org))
						{
							$new_p->set_prop("work_contact", $org);
						}
						$new_p->save();
						$arr["obj_inst"]->set_prop($prop["name"], $new_p->id());
					}
					return PROP_IGNORE;
				}
				break;

			case "profession":
				if(!is_oid($prop["value"]) && strlen($prop["value"]) > 0)
				{
					$pm = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault());
					$ol = new object_list(array(
						"class_id" => CL_CRM_PROFESSION,
						"lang_id" => array(),
						"site_id" => array(),
						"parent" => array(),
					));
					$rev_nms = array_flip($ol->names());
					if(array_key_exists($prop["value"], $rev_nms))
					{
						$arr["obj_inst"]->save();
						$arr["obj_inst"]->set_prop($prop["name"], $rev_nms[$prop["value"]]);
					}
					else
					{
						$new_p = new object;
						$new_p->set_class_id(CL_CRM_PROFESSION);
						if(is_oid($pm->professions_fld))
						{
							$new_p->set_parent($pm->professions_fld);
						}
						else
						{
							// Need to save it before I can set it as parent.
							$arr["obj_inst"]->save();
							$new_p->set_parent($arr["obj_inst"]->id());
						}
						$new_p->set_name($prop["value"]);
						$new_p->save();
						$arr["obj_inst"]->set_prop($prop["name"], $new_p->id());
					}
					return PROP_IGNORE;
				}
				break;

			case "loc_country":
				if(!is_oid($prop["value"]) && strlen($prop["value"]) > 0)
				{
					$crm_db = obj(obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->crmdb);
					$ol = new object_list(array(
						"class_id" => CL_CRM_COUNTRY,
						"lang_id" => array(),
						"site_id" => array(),
						"parent" => array(),
					));
					$rev_nms = array_flip($ol->names());
					if(array_key_exists($prop["value"], $rev_nms))
					{
						$arr["obj_inst"]->save();
						$arr["obj_inst"]->set_prop($prop["name"], $rev_nms[$prop["value"]]);
					}
					else
					{
						$new_p = new object;
						$new_p->set_class_id(CL_CRM_AREA);
						if(is_oid($crmdb->dir_riik))
						{
							$new_p->set_parent($crmdb->dir_riik);
						}
						else
						{
							// Need to save it before I can set it as parent.
							$arr["obj_inst"]->save();
							$new_p->set_parent($arr["obj_inst"]->id());
						}
						$new_p->set_name($prop["value"]);
						$new_p->save();
						$arr["obj_inst"]->set_prop($prop["name"], $new_p->id());
					}
					return PROP_IGNORE;
				}
				break;

			case "loc_area":
				if(!is_oid($prop["value"]) && strlen($prop["value"]) > 0)
				{
					$crm_db = obj(obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->crmdb);
					$ol = new object_list(array(
						"class_id" => CL_CRM_AREA,
						"lang_id" => array(),
						"site_id" => array(),
						"parent" => array(),
					));
					$rev_nms = array_flip($ol->names());
					if(array_key_exists($prop["value"], $rev_nms))
					{
						$arr["obj_inst"]->save();
						$arr["obj_inst"]->set_prop($prop["name"], $rev_nms[$prop["value"]]);
					}
					else
					{
						$new_p = new object;
						$new_p->set_class_id(CL_CRM_AREA);
						if($this->can("add", $arr["obj_inst"]->loc_country))
						{
							$new_p->set_parent($arr["obj_inst"]->loc_country);
						}
						else
						if(is_oid($crmdb->dir_piirkond))
						{
							$new_p->set_parent($crmdb->dir_piirkond);
						}
						else
						{
							// Need to save it before I can set it as parent.
							$arr["obj_inst"]->save();
							$new_p->set_parent($arr["obj_inst"]->id());
						}
						$new_p->set_name($prop["value"]);
						$new_p->save();
						$arr["obj_inst"]->set_prop($prop["name"], $new_p->id());
					}
					return PROP_IGNORE;
				}
				break;

			case "loc_county":
				if(!is_oid($prop["value"]) && strlen($prop["value"]) > 0)
				{
					$crm_db = obj(obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->crmdb);
					$ol = new object_list(array(
						"class_id" => CL_CRM_COUNTY,
						"lang_id" => array(),
						"site_id" => array(),
						"parent" => array(),
					));
					$rev_nms = array_flip($ol->names());
					if(array_key_exists($prop["value"], $rev_nms))
					{
						$arr["obj_inst"]->save();
						$arr["obj_inst"]->set_prop($prop["name"], $rev_nms[$prop["value"]]);
					}
					else
					{
						$new_p = new object;
						$new_p->set_class_id(CL_CRM_COUNTY);
						if($this->can("add", $arr["obj_inst"]->loc_area))
						{
							$new_p->set_parent($arr["obj_inst"]->loc_area);
						}
						else
						if(is_oid($crmdb->dir_maakond))
						{
							$new_p->set_parent($crmdb->dir_maakond);
						}
						else
						{
							// Need to save it before I can set it as parent.
							$arr["obj_inst"]->save();
							$new_p->set_parent($arr["obj_inst"]->id());
						}
						$new_p->set_name($prop["value"]);
						$new_p->save();
						$arr["obj_inst"]->set_prop($prop["name"], $new_p->id());
					}
					return PROP_IGNORE;
				}
				break;

			case "loc_city":
				if(!is_oid($prop["value"]) && strlen($prop["value"]) > 0)
				{
					$crm_db = obj(obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->crmdb);
					$ol = new object_list(array(
						"class_id" => CL_CRM_CITY,
						"lang_id" => array(),
						"site_id" => array(),
						"parent" => array(),
					));
					$rev_nms = array_flip($ol->names());
					if(array_key_exists($prop["value"], $rev_nms))
					{
						$arr["obj_inst"]->save();
						$arr["obj_inst"]->set_prop($prop["name"], $rev_nms[$prop["value"]]);
					}
					else
					{
						$new_p = new object;
						$new_p->set_class_id(CL_CRM_CITY);
						if($this->can("add", $arr["obj_inst"]->loc_county))
						{
							$new_p->set_parent($arr["obj_inst"]->loc_county);
						}
						else
						if(is_oid($crmdb->dir_linn))
						{
							$new_p->set_parent($crmdb->dir_linn);
						}
						else
						{
							// Need to save it before I can set it as parent.
							$arr["obj_inst"]->save();
							$new_p->set_parent($arr["obj_inst"]->id());
						}
						$new_p->set_name($prop["value"]);
						$new_p->save();
						$arr["obj_inst"]->set_prop($prop["name"], $new_p->id());
					}
					return PROP_IGNORE;
				}
				break;

			case "status":
				if(!$prop["value"])
				{
					$prop["value"] = object::STAT_NOTACTIVE;
				}
				break;

			case "new_cfgform_name":
				if(strlen($prop["value"]) > 0)
				{
					$this->set_new_cfgform_tbl($arr);
				}
				break;

			case "candidate_table":
				$this->_set_candidate_table($arr);
				break;

			case "candidate_add":
				$this->_set_candidate_add($arr);
		}
		return $retval;
	}

	function _set_candidate_add($arr)
	{
		$o = $arr["obj_inst"];
		$ids = $o->get_candidates()->ids();
		$ps = explode(",", $arr["prop"]["value"]);
		foreach($ps as $p)
		{
			if($this->can("view", $p))
			{
				$p = obj($p);
				if(!in_array($p->id(), $ids))
				{
					$c = new object;
					$c->set_class_id(CL_PERSONNEL_MANAGEMENT_CANDIDATE);
					$c->set_status(object::STAT_ACTIVE);
					$c->set_parent($o->id());
					$c->set_name($p->name()." kandidatuur kohale ".$o->name());
					$c->save();
					$c->set_prop("person", $p->id());
					$c->save();

					// Job offer to candidate.
					$o->connect(array(
						"to" => $c->id(),
						"reltype" => "RELTYPE_CANDIDATE",
					));
					// Candidate to person.
					$c->connect(array(
						"to" => $p->id(),
						"reltype" => "RELTYPE_PERSON",
					));
				}
			}
		}
	}

	function callback_post_save($arr)
	{
		// We'll send the feedback after saving to be sure the attachment is saved.
		if($arr["request"]["click_send"])
		{
			$this->send_feedback($arr);
		}
	}

	function send_feedback($arr)
	{
		if($arr["request"]["send_email_sms_type"] == "email")
		{
			$msgrid = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->messenger;
			$msgr = obj($msgrid);

			$subject = $arr["request"]["subject"];
			$message = $arr["request"]["message"];
			$tos = is_array($arr["request"]["receivers"]) ? $arr["request"]["receivers"] : array();
			$odl = new object_data_list(
				array(
					"class_id" => CL_ML_MEMBER,
					"oid" => $tos,
					"parent" => array(),
					"lang_id" => array(),
					"site_id" => array(),
					"status" => array(),
				),
				array(
					CL_ML_MEMBER => array("mail"),
				)
			);
			$mls = array();
			foreach($odl->arr() as $oid => $odata)
			{
				$mls[$oid] = $odata["mail"];
			}
			if(strlen($arr["request"]["add_receivers"]) > 0)
			{
				foreach(explode(",", $arr["request"]["add_receivers"]) as $add_to)
				{
					$tos[] = $add_to;
				}
			}

			// FROM
			$from_nm = "";
			$from_adr = "";
			$this->parse_name_and_email($arr["request"]["from"], &$from_nm, &$from_adr);

			unset($from_id);
			$pm = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault());
			$odl = new object_data_list(
				array(
					"class_id" => CL_ML_MEMBER,
					"parent" => $this->can("view", $pm->fb_from_fld) ? $pm->fb_from_fld : $arr["obj_inst"]->id(),
					"lang_id" => array(),
					"site_id" => array(),
					"status" => array(),
				),
				array(
					CL_ML_MEMBER => array("mail"),
				)
			);
			foreach($odl->arr() as $oid => $odata)
			{
				if($odata["mail"] == $from_adr)
				{
					$from_id = $oid;
				}
			}
			if(!isset($from_id))
			{
				$from = obj();
				$from->set_class_id(CL_ML_MEMBER);
				$from->set_parent(($this->can("view", $pm->fb_from_fld) ? $pm->fb_from_fld : $arr["obj_inst"]->id()));
				$from->set_prop("mail", $from_adr);
				$from->set_prop("name", $from_nm." <".$from_adr.">");
				$from->save();
				$from_id = $from->id();
			}

			$mail = obj();
			$mail->set_class_id(CL_MESSAGE);
			if($this->can("add", $msgr->msg_outbox))
			{
				$mail->set_parent($msgr->msg_outbox);
			}
			else
			{
				$mail->set_parent($arr["obj_inst"]->id());
			}
			$mail->name = $subject;
			$mail->message = $message;
			$mail->save();

			foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ATTACHMENT")) as $conn)
			{
				$mail->connect(array(
					"to" => $conn->prop("to"),
					"type" => "RELTYPE_ATTACHMENT",
				));
				$conn->delete();
			}

			foreach($tos as $to)
			{
				$mail_tmp = obj($mail->save_new());
				if(is_oid($to))
				{
					$mail_tmp->mto_relpicker = $to;
					$to = $mls[$to];
				}
				$to = str_replace("&lt;", "<", $to);
				$to = str_replace("&gt;", ">", $to);
				$mail_tmp->mto = $to;
				$mail_tmp->mfrom = $from_id;
				$mail_tmp->save();
				get_instance(CL_MESSAGE)->send_message(array(
					"id" => $mail_tmp->id(),
				));

				$arr["obj_inst"]->connect(array(
					"to" => $mail_tmp->id(),
					"type" => "RELTYPE_MAIL_SENT",
				));
			}

			if($arr["request"]["save_typical"])
			{
				$mail_template = obj();
				$mail_template->set_class_id(CL_MESSAGE_TEMPLATE);
				if($this->can("add", $msgr->msg_drafts))
				{
					$mail_template->set_parent($msgr->msg_drafts);
				}
				else
				{
					$mail_template->set_parent($arr["obj_inst"]->id());
				}
				$mail_template->name = $arr["request"]["typical_name"];
				$mail_template->subject = $subject;
				$mail_template->content = $message;
				$mail_template->save();

				$arr["obj_inst"]->connect(array(
					"to" => $mail_template->id(),
					"type" => "RELTYPE_TYPICAL_MAIL_MESSAGE",
				));
				unset($arr["request"]["save_typical"]);
			}
		}
		else
		if($arr["request"]["send_email_sms_type"] == "sms")
		{
			$message = $arr["request"]["message"];
			//$tos = $arr["request"]["receivers"];
			$tos = array();
			if(count($arr["request"]["receivers"]) > 0)
			{
				$odl = new object_data_list(
					array(
						"class_id" => CL_CRM_PHONE,
						"oid" => $arr["request"]["receivers"],
						"parent" => array(),
						"lang_id" => array(),
						"site_id" => array(),
						"status" => array(),
					),
					array(
						CL_CRM_PHONE => array("clean_number" => "nr"),
					)
				);
				foreach($odl->arr() as $oid => $odata)
				{
					$tos[] = $odata["nr"];
					// Easier to connect the SMS to the phone this way.
					$phs[$odata["nr"]] = $oid;
				}
			}
			if(strlen($arr["request"]["add_receivers"]) > 0)
			{
				foreach(explode(",", $arr["request"]["add_receivers"]) as $add_receiver)
				{
					$tos[] = $add_receiver;
				}
			}
			$mobi = get_instance(CL_MOBI_HANDLER);
			$mobi_handler = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->prop("mobi_handler");
			
			$sms = obj();
			$sms->set_class_id(CL_SMS);
			$sms->set_parent($mobi_handler);
			$sms->comment = $message;
			$sms->save();

			foreach($tos as $to)
			{
				$to = preg_replace("/[^0-9]/", "", $to);
				$sms_sent = $mobi->send_sms(array(
					"id" => $mobi_handler,
					"phone" => $phs[$to],
					"number" => $to,
					"sms" => $sms->id(),
				));
				$arr["obj_inst"]->connect(array(
					"to" => $sms_sent->id(),
					"type" => "RELTYPE_MOBI_SMS_SENT",
				));
			}

			if($arr["request"]["save_typical"])
			{
				$sms->name = $arr["request"]["typical_name"];
				$sms->save();
				$arr["obj_inst"]->connect(array(
					"to" => $sms->id(),
					"type" => "RELTYPE_TYPICAL_MOBI_SMS",
				));
				unset($arr["request"]["save_typical"]);
			}
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["typical_name"] = "";
		$arr["click_send"] = "";
	}

	function callback_mod_retval($arr)
	{
		if($arr["request"]["sel"])
		{
			$arr["args"]["sel"] = $arr["request"]["sel"];
		}
		else
		if($arr["request"]["send_email_sms_select"])
		{
			$arr["args"]["sel"] = $arr["request"]["send_email_sms_select"];
		}
		if($arr["request"]["send_email_sms_type"] == "sms")
		{
			$arr["args"]["sms"] = 1;
		}
		else
		if($arr["request"]["send_email_sms_type"] == "email")
		{
			$arr["args"]["email"] = 1;
		}
		$arr["args"]["receivers"] = $arr["request"]["receivers"];
		$arr["args"]["message"] = $arr["request"]["message"];
		$arr["args"]["typical_select"] = $arr["request"]["typical_select"];
		$arr["args"]["from"] = $arr["request"]["from"];
		$arr["args"]["subject"] = $arr["request"]["subject"];
		$arr["args"]["add_receivers"] = $arr["request"]["add_receivers"];
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
				//$v = explode(',', str_replace($mand_cont.',', '', (join(',', $v))));
				$v = explode(',,', trim(str_replace(','.$mand_cont.',', '', ',,'.(join(',,', $v)).',,'), ','));
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
		if(is_oid($arr["cfgform_id"]))
		{
			return get_instance(CL_CFGFORM)->get_class_cfgview(array("id" => $arr["cfgform_id"], "display_mode" => "cfg_embed"));
		}

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
		if($company)
		{
			// get_first_obj_by_reltype return the obj not the connection obj!
			//$company = &obj($company->prop("from"));
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

		// Contact
		if($this->can("view", $ob->contact))
		{
			$contact_obj = obj($ob->contact);
			$c_phone_obj = $contact_obj->phones()->begin();
			if(is_object($c_phone_obj))
			{
				$c_phone = $c_phone_obj->name;
			}
			$c_email_obj = $contact_obj->emails()->begin();
			if(is_object($c_email_obj))
			{
				$c_email = $c_email_obj->mail;
			}
			$this->vars(array(
				"contact.name" => $ob->prop("contact.name"),
				"contact.firstname" => $ob->prop("contact.firstname"),
				"contact.lastname" => $ob->prop("contact.lastname"),
				"contact.phone" => $c_phone,
				"contact.email" => $c_email,
			));
		}

		if($this->can("view", $ob->sect))
		{
			$sect = obj($ob->sect);
			$tmpo = obj();
			$tmpo->set_class_id(CL_CRM_ADDRESS);
			foreach($tmpo->get_property_list() as $prop => $pdata)
			{
				if($pdata["type"] == "relpicker")
				{					
					$this->vars(array(
						"sect.contact.".$prop => $sect->prop("contact.".$prop.".name"),
					));
				}
				else
				{					
					$this->vars(array(
						"sect.contact.".$prop => $sect->prop("contact.".$prop),
					));
				}
			}
			$this->vars(array(
				"sect.description" => $sect->description,
				"sect.contact" => $sect->prop("contact.name"),
			));
		}

		$pm = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault());
		$cfgform_id = is_oid($ob->offer_cfgform) ? $ob->offer_cfgform : $pm->default_offers_cfgform;
		
		$this->vars(array(
			"name" => @$ob->prop("name"),
			"company" => $ob->prop("company.name"),
			"sect" => $ob->prop("sect.name"),
			"location" => $location,
			"loc_area" => $ob->prop("loc_area.name"),
			"loc_county" => $ob->prop("loc_county.name"),
			"loc_city" => $ob->prop("loc_city.name"),
			"sectors" => $tmp_sectors,
			"end" => get_lc_date($ob->prop("end")),
			"description" => $ob->prop("toosisu"),
			"requirements" => $ob->prop("noudmised"),
			"start_date" => $ob->prop("job_from") > 100 ? get_lc_date($ob->prop("job_from")) : " - ",
			"start_working" => $ob->start_working == "asap" ? t("Niipea kui v&otilde;imalik") : get_lc_date($ob->start_working_date),
			"tookoormused" => join(",", $ks),
			"contact_person" => $ob->prop("contact_person"),
			"job_nr" => $ob->prop("job_nr"),
			"profession" => $ob->prop("profession.name"),
			"org_description_text" => $ob->prop("company.tegevuse_kirjeldus"),
			"workinfo" => $ob->prop("workinfo"),
			"requirements" => $ob->prop("requirements"),
			"weoffer" => $ob->prop("weoffer"),
			"apply_link" => obj_link($pm->apply_doc)."?cfgform_id=".$cfgform_id."&job_offer_id=".$ob->id(),
			"keywords" => $ob->keywords,
		));
		
		$info = $ob->autoinfo ? $this->parse("AUTOINFO") : $ob->info;
		$this->vars(array(
			"info" => $info,
		));

		$props = array_keys(get_instance(CL_CFGFORM)->get_cfg_proplist(get_instance(CL_CFGFORM)->get_sysdefault(array("clid" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER))));
		// Add SUBs
		$props += array("apply");

		// 
		foreach($props as $prop)
		{
			switch ($prop)
			{
				case "apply":
					$ok[$prop] = 1;
					break;

				default:
					if($ob->prop($prop))
					{
						$ok[$prop] = 1;
					}
					break;
			}
		}

		foreach($props as $prop)
		{
			if($ok[$prop])
			{
				$this->vars(array(
					strtoupper($prop) => $this->parse(strtoupper($prop)),
				));
			}
		}
		
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
			$this->notify_me($arr);
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
		$f = '
			function typical_select_data()
			{
				$.getJSON("'.$this->mk_my_orb("typical_data").'", {id: $("#typical_select").val(), fbtype: "'.(($arr["request"]["email"]) ? "email" : "sms").'"}, function(data) {'.(($arr["request"]["email"]) ? '
					aw_get_el("subject").value = data.subject.toString();' : '').'
					aw_get_el("message").value = data.message.toString();
				});
			}
			';
		if($arr["request"]["group"] == "custom_cfgform")
		{
			$f .= "
			function save_cfgform()
			{
				if(aw_get_el('save_cfgform').checked)
				{
					aw_get_el('new_cfgform_name').value = prompt('".t("Sisestage salvestatava seadetevormi nimi:")."');
				}
			}

			aw_submit_handler = save_cfgform;";
		}
		return $f;
	}

	/**
	@attrib name=send_email
	@param sel optional type=array(oid)
	**/
	function send_email($arr)
	{
		if(!is_array($arr["sel"]) || count($arr["sel"]) == 0)
		{
			return $arr["post_ru"];
		}
		foreach(connection::find(array("from" => $arr["sel"], "type" => 1)) as $conn)		// RELTYPE_PERSON
		{
			$sel[] = $conn["to"];
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "send_email_sms", "email" => 1, "sel" => $sel));
	}

	/**
	@attrib name=send_sms
	@param sel optional type=array(oid)
	**/
	function send_sms($arr)
	{
		if(!is_array($arr["sel"]) || count($arr["sel"]) == 0)
		{
			return $arr["post_ru"];
		}
		foreach(connection::find(array("from" => $arr["sel"], "type" => 1)) as $conn)		// RELTYPE_PERSON
		{
			$sel[] = $conn["to"];
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "send_email_sms", "sms" => 1, "sel" => $sel, "return_url" => $arr["return_url"]));
	}

	function callback_pre_save($arr)
	{
		if (!is_oid($arr["request"]["company"]) && !is_oid($arr["obj_inst"]->company))
		{
			$cp = get_instance(CL_USER)->get_person_for_uid(aw_global_get("uid"));
			$arr["obj_inst"]->company = $cp->work_contact;
		}
	}

	function do_db_upgrade($tbl, $field, $q, $err)
	{
		if ($tbl == "personnel_management_job_offer" && $field == "")
		{
			$this->db_query("create table personnel_management_job_offer (oid int primary key)");
			return true;
		}
		
		$props = array(
			"jo_start" => "start",
			"jo_end" => "end",
		);

		switch($field)
		{
			case "jo_start":
			case "jo_end":
			case "archive":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "int"
				));
				if(array_key_exists($field, $props))
				{
					$ol = new object_list(array(
						"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
						"parent" => array(),
						"site_id" => array(),
						"lang_id" => array(),
						"status" => array(),
					));
					foreach($ol->arr() as $o)
					{
						$value = $o->meta($props[$field]);
						$oid = $o->id();
						$this->db_query("
							INSERT INTO
								personnel_management_job_offer (oid, $field)
							VALUES
								('$oid', '$value')
							ON DUPLICATE KEY UPDATE
								$field = '$value'
						");
					}
				}
				return true;

			case "keywords":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "text"
				));
				return true;
		}

		return false;
	}

	/**
		@attrib name=autocomp_contact all_args=1
	**/
	public function autocomp_contact($arr)
	{
		$this->autocomp($arr, "contact");
	}

	/**
		@attrib name=autocomp_profession all_args=1
	**/
	function autocomp_profession($arr)
	{
		$this->autocomp($arr, "profession");
	}

	/**
		@attrib name=autocomp_loc_country all_args=1
	**/
	function autocomp_loc_country($arr)
	{
		$this->autocomp($arr, "loc_country");
	}

	/**
		@attrib name=autocomp_loc_area all_args=1
	**/
	function autocomp_loc_area($arr)
	{
		$this->autocomp($arr, "loc_area");
	}

	/**
		@attrib name=autocomp_loc_county all_args=1
	**/
	function autocomp_loc_county($arr)
	{
		$this->autocomp($arr, "loc_county");
	}

	/**
		@attrib name=autocomp_loc_city all_args=1
	**/
	function autocomp_loc_city($arr)
	{
		$this->autocomp($arr, "loc_city");
	}

	/**
		@attrib name=autocomp_from all_args=1
	**/
	function autocomp_from($arr)
	{

		header ("Content-Type: text/html; charset=" . aw_global_get("charset"));
		$cl_json = get_instance("protocols/data/json");

		$errorstring = "";
		$error = false;
		$autocomplete_options = array();

		$option_data = array(
			"error" => &$error,// recommended
			"errorstring" => &$errorstring,// optional
			"options" => &$autocomplete_options,// required
			"limited" => false,// whether option count limiting applied or not. applicable only for real time autocomplete.
		);

		$odl_prms = array(
			"class_id" => CL_ML_MEMBER,
			"lang_id" => array(),
			"site_id" => array(),
			"limit" => 500,
		);

		$pm = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault());
		if($this->can("view", $pm->fb_from_fld))
		{
			$odl_prms["parent"] = $pm->fb_from_fld;
		}
		else
		{
			$odl_prms["parent"] = $arr["id"];
		}
		$odl = new object_data_list(
			$odl_prms,
			array(
				CL_ML_MEMBER => array("mail", "name"),
			)
		);
		foreach($odl->arr() as $k => $v)
		{
			$autocomplete_options[$k] = iconv(aw_global_get("charset"), "UTF-8", $v["mail"]);
			//$autocomplete_options[$k."_name"] = iconv(aw_global_get("charset"), "UTF-8", str_replace(array("&gt;", "&lt;"), array(">", "<"), $v["name"]));
		}

		$autocomplete_options = array_unique($autocomplete_options);
		header("Content-type: text/html; charset=utf-8");
		exit ($cl_json->encode($option_data));
	}
	
	/**
		@attrib name=autocomp all_args=1
	**/
	function autocomp($arr, $prop)
	{
		$ac = get_instance("vcl/autocomplete");
		$clids = array(
			"profession" => CL_CRM_PROFESSION,
			"contact" => CL_CRM_PERSON,
			"loc_country" => CL_CRM_COUNTRY,
			"loc_area" => CL_CRM_AREA,
			"loc_county" => CL_CRM_COUNTY,
			"loc_city" => CL_CRM_CITY,
		);

		header ("Content-Type: text/html; charset=" . aw_global_get("charset"));
		$cl_json = get_instance("protocols/data/json");

		$errorstring = "";
		$error = false;
		$autocomplete_options = array();

		$option_data = array(
			"error" => &$error,// recommended
			"errorstring" => &$errorstring,// optional
			"options" => &$autocomplete_options,// required
			"limited" => false,// whether option count limiting applied or not. applicable only for real time autocomplete.
		);

		$ol_prms = array(
			"class_id" => $clids[$prop],
			"lang_id" => array(),
			"site_id" => array(),
			"limit" => 500,
		);

		if($prop == "from")
		{
			$pm = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault());
			if($this->can("view", $pm->fb_from_fld))
			{
				$ol_prms["parent"] = $pm->fb_from_fld;
			}
		}
		else
		if($prop == "contact")
		{
			if(is_oid($arr["company"]))
			{
				$org = $arr["company"];
			}
			else
			if(is_oid($arr["id"]) && is_oid(obj($arr["id"])->company))
			{
				$org = obj($arr["id"])->company;
			}
			else
			{
				$cp = get_instance(CL_USER)->get_person_for_uid(aw_global_get("uid"));
				$org = $cp->work_contact;
			}
			$org = obj($org);
			$ids = $org->get_employees()->ids();
			if(count($ids) > 0)
			{
				$ol_prms["oid"] = $ids;
			}
		}
		
		$ol = new object_list($ol_prms);
		$autocomplete_options = $ol->names();
		foreach($autocomplete_options as $k => $v)
		{
			$autocomplete_options[$k] = iconv(aw_global_get("charset"), "UTF-8", parse_obj_name($v));
		}

		$autocomplete_options = array_unique($autocomplete_options);
		header("Content-type: text/html; charset=utf-8");
		exit ($cl_json->encode($option_data));
	}

	function callback_just_saved_msg($arr)
	{
		if($arr["group"] == "send_email_sms")
		{
			return t("S&otilde;num saadetud!");
		}
	}

	/**
		@attrib name=delete_typical_sms
	**/
	function delete_typical_sms($arr)
	{
		$o = obj($arr["id"]);
		if ($arr["typical_select"])
		{
			$o->disconnect(array(
				"from" => $arr["typical_select"]
			));
		}
		return $arr["post_ru"];
	}

	function notify_me($arr)
	{
		$conn = $arr['connection'];
		if($conn->prop("to.class_id") == CL_PERSONNEL_MANAGEMENT_JOB_OFFER)
		{
			$job_offer = $conn->to();
			$candidate = $conn->from();
		}
		else
		{
			$job_offer = $conn->from();
			$candidate = $conn->to();
		}
		foreach($job_offer->connections_from(array("type" => "RELTYPE_NOTIFY_ME")) as $cn)
		{
			$p = $cn->to();
			$ml = $p->emails()->begin();
			$pm = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault());
			$tpl_id = $pm->notify_me_tpl;
			$cv_tpl = $pm->cv_tpl ? "cv/".basename($pm->cv_tpl) : "";
			if(is_object($ml) && $this->can("view", $tpl_id))
			{
				$tpl = obj($tpl_id);
				$vars = array(
					"job_offer.name" => $job_offer->name,
					"job_offer.url" => $this->mk_my_orb("change", array("id" => $job_offer->id())),
					"candidate.person" => $candidate->prop("person.name"),
					"candidate.person.cv" => $this->mk_my_orb("show_cv", array("id" => $candidate->person, "die" => 1, "cv" => $cv_tpl)),
					"candidate.person.url" => $this->mk_my_orb("change", array("id" => $candidate->person)),
				);
				$subject = $tpl->subject;
				$message = $tpl->content;
				foreach($vars as $k => $v)
				{
					$subject = str_replace("{VAR:".$k."}", $v, $subject);
					$message = str_replace("{VAR:".$k."}", $v, $message);
				}
				mail($ml->mail, $subject, $message, "From: ".$pm->prop("messenger.fromname.name"));
			}
		}
	}

	/**
		@attrib name=save_copy api=1 params=name

		@param id required type=oid

		@param post_ru optional type=string

	**/
	function save_copy($arr)
	{
		$o = obj($arr["id"]);
		$new_oid = $o->save_new();
		return $this->mk_my_orb("change", array("id" => $new_oid, "return_url" => $arr["post_ru"]));
	}

	private function parse_name_and_email($from, $from_nm, $from_adr)
	{		
		$from_arr = explode(" ", $from);
		foreach($from_arr as $from_pc)
		{
			$from_pc = str_replace(array("<", ">", "&gt;", "&lt;"), "", $from_pc);
			if(preg_match("/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/", strtoupper($from_pc)))
			{
				$from_adr = $from_pc;
				break;
			}
		}
		$from_nm = str_replace(array("<", ">", "&gt;", "&lt;", $from_adr), "", $from);
		$from_nm = trim($from_nm);
	}
	
	/**
		@attrib name=typical_data params=name all_args=1
	**/
	function typical_data($arr)
	{
		$o = obj($arr["id"]);
		$ls = aw_ini_get("languages");
		$charset = $ls["list"][aw_global_get("lang_id")]["charset"];
		if($arr["fbtype"] == "sms")
		{
			exit(json_encode(array("message" => iconv($charset, "UTF-8", $o->comment()))));
		}
		else
		{
			exit(json_encode(array("subject" => iconv($charset, "UTF-8", $o->prop("subject")), "message" => iconv($charset, "UTF-8", $o->prop("content")))));
		}
	}
}
?>

