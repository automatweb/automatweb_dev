<?php

/*

@classinfo syslog_type=ST_CRM_SALES relationmgr=yes no_status=1 maintainer=voldemar prop_cb=1

@groupinfo settings caption="Seaded"
@groupinfo calls caption="K&otilde;ned" submit_method=get
@groupinfo presentations caption="Esitlused" submit=no
@groupinfo data_entry caption="Sisestamine"
@groupinfo data_entry_contact_co caption="Kontakt (organisatsioon)" parent=data_entry
@groupinfo data_entry_contact_person caption="Kontakt (isik)" parent=data_entry
@groupinfo data_entry_import caption="Import" parent=data_entry

@default table=objects
@default field=meta
@default method=serialize
@default group=general
	@property owner type=relpicker reltype=RELTYPE_OWNER clid=CL_CRM_COMPANY
	@caption Keskkonna omanik

@default group=settings
	@layout splitbox1 type=hbox width=50%:50% closeable=1 no_caption=1
	@layout splitbox2 type=hbox width=50%:50% closeable=1 area_caption=Kasutajaliidese&nbsp;vaadete&nbsp;konfiguratsioonid&nbsp;rollide&nbsp;kaupa
	@layout folders_box type=vbox parent=splitbox1 closeable=1 area_caption=Kaustad
	@layout roles_box type=vbox parent=splitbox1 closeable=1 area_caption=Rollid
	@layout main_app_cfg_box type=vbox parent=splitbox2 closeable=1 area_caption="M&uuml;i&uuml;git&ouml;&ouml;laud"
	@layout call_cfg_box type=vbox parent=splitbox2 closeable=1 area_caption="K&otilde;ne"

	@property calls_folder type=relpicker reltype=RELTYPE_FOLDER clid=CL_MENU parent=folders_box
	@comment Kaust kuhu salvestatakse ning kust loetakse selle m&uuml;&uuml;gikeskkonna k&otilde;neobjektid
	@caption K&otilde;nede kaust

	@property presentations_folder type=relpicker reltype=RELTYPE_FOLDER clid=CL_MENU parent=folders_box
	@comment Kaust kuhu salvestatakse ning kust loetakse selle m&uuml;&uuml;gikeskkonna presentatsioonid
	@caption Presentatsioonide kaust

	// roles
	@property role_profession_data_entry_clerk type=relpicker reltype=RELTYPE_ROLE_PROFESSION clid=CL_CRM_PROFESSION parent=roles_box
	@comment Andmesisestaja rollile vastav ametinimetus organisatsioonis
	@caption Andmesisestaja amet

	@property role_profession_telemarketing_salesman type=relpicker reltype=RELTYPE_ROLE_PROFESSION clid=CL_CRM_PROFESSION parent=roles_box
	@comment Telemarketingit&ouml;&ouml;taja rollile vastav ametinimetus organisatsioonis
	@caption Telemarketingit&ouml;&ouml;taja amet

	@property role_profession_telemarketing_manager type=relpicker reltype=RELTYPE_ROLE_PROFESSION clid=CL_CRM_PROFESSION parent=roles_box
	@comment Telemarketingi juhi rollile vastav ametinimetus organisatsioonis
	@caption Telemarketingi juhi amet

	@property role_profession_salesman type=relpicker reltype=RELTYPE_ROLE_PROFESSION clid=CL_CRM_PROFESSION parent=roles_box
	@comment M&uuml;&uuml;gimehe/m&uuml;&uuml;giesindaja rollile vastav ametinimetus organisatsioonis
	@caption M&uuml;&uuml;giesindaja amet

	@property role_profession_manager type=relpicker reltype=RELTYPE_ROLE_PROFESSION clid=CL_CRM_PROFESSION parent=roles_box
	@comment Juhi rollile vastav ametinimetus organisatsioonis
	@caption Juhi amet

	// cfgforms
	//// main application cfgforms
	@property cfgf_main_generic type=relpicker reltype=RELTYPE_CFGFORM parent=main_app_cfg_box
	@caption K&otilde;ik kasutajad

	@property cfgf_main_data_entry_clerk type=relpicker reltype=RELTYPE_CFGFORM parent=main_app_cfg_box
	@caption Andmesisestaja

	@property cfgf_main_telemarketing_salesman type=relpicker reltype=RELTYPE_CFGFORM parent=main_app_cfg_box
	@caption Telemarketingit&ouml;&ouml;taja

	@property cfgf_main_telemarketing_manager type=relpicker reltype=RELTYPE_CFGFORM parent=main_app_cfg_box
	@caption Telemarketingi juht

	@property cfgf_main_salesman type=relpicker reltype=RELTYPE_CFGFORM parent=main_app_cfg_box
	@caption M&uuml;&uuml;giesindaja

	@property cfgf_main_manager type=relpicker reltype=RELTYPE_CFGFORM parent=main_app_cfg_box
	@caption Juht


	//// call cfgforms
	@property cfgf_call_generic type=relpicker reltype=RELTYPE_CFGFORM parent=call_cfg_box
	@caption K&otilde;ik kasutajad

	@property cfgf_call_data_entry_clerk type=relpicker reltype=RELTYPE_CFGFORM parent=call_cfg_box
	@caption Andmesisestaja

	@property cfgf_call_telemarketing_salesman type=relpicker reltype=RELTYPE_CFGFORM parent=call_cfg_box
	@caption Telemarketingit&ouml;&ouml;taja

	@property cfgf_call_telemarketing_manager type=relpicker reltype=RELTYPE_CFGFORM parent=call_cfg_box
	@caption Telemarketingi juht

	@property cfgf_call_salesman type=relpicker reltype=RELTYPE_CFGFORM parent=call_cfg_box
	@caption M&uuml;&uuml;giesindaja

	@property cfgf_call_manager type=relpicker reltype=RELTYPE_CFGFORM parent=call_cfg_box
	@caption Juht



@default group=calls
	@layout calls_vsplitbox type=hbox width=25%:75%
	@property calls_toolbar type=toolbar store=no no_caption=1
	@layout calls_box type=vbox parent=calls_vsplitbox
	@layout calls_tree_box type=vbox closeable=1 area_caption=K&otilde;nede&nbsp;valik parent=calls_box
	@property calls_tree type=treeview store=no no_caption=1 parent=calls_tree_box
	@property calls_list type=table store=no no_caption=1 parent=calls_vsplitbox

	@layout calls_search_box type=vbox closeable=1 area_caption=Otsing&nbsp;kontaktidest parent=calls_box
		@property cs_name type=textbox view_element=1 parent=calls_search_box store=no size=33
		@caption Nimi

		@property cs_salesman type=textbox view_element=1 parent=calls_search_box store=no size=33
		@caption M&uuml;&uuml;gi-<br/>esindaja

		@property cs_address type=textbox view_element=1 parent=calls_search_box store=no size=33
		@caption Aadress

		@property cs_phone type=textbox view_element=1 parent=calls_search_box store=no size=33
		@caption Telefon

		@property cs_status type=textbox view_element=1 parent=calls_search_box store=no size=33
		@caption Staatus

		@property cs_submit type=submit value=Otsi view_element=1 parent=calls_search_box store=no
		@caption Otsi


@default group=presentations
	@property presentations_cal type=calendar store=no no_caption=1


@layout de_form_box type=vbox group=data_entry_contact_co,data_entry_contact_person
@layout de_table_box type=vbox group=data_entry_contact_co,data_entry_contact_person
@layout contact_entry_form type=vbox group=data_entry_contact_co,data_entry_contact_person parent=de_form_box


@default group=data_entry
@default group=data_entry_contact_co
	@property contact_entry_co type=releditor reltype=RELTYPE_CONTACT_CO store=no props=name,fake_phone,fake_mobile,fake_email,fake_address_address,fake_address_postal_code,fake_address_city,fake_address_county,fake_address_country_relp parent=contact_entry_form
	@caption Kontakt (organisatsioon)


@default group=data_entry_contact_person
	@property contact_entry_person type=releditor reltype=RELTYPE_CONTACT_PERSON store=no props=lastname,firstname,gender,fake_address_address,fake_address_postal_code,fake_address_city,fake_address_county,fake_address_country,fake_phone,fake_email parent=contact_entry_form
	@caption Kontakt (isik)


@default group=data_entry_import


@layout contact_entry_buttons type=hbox parent=de_form_box group=data_entry_contact_co,data_entry_contact_person width=10%:20%:70%
	@property contact_entry_space type=text store=no group=data_entry_contact_co,data_entry_contact_person parent=contact_entry_buttons no_caption=1

	@property contact_entry_submit type=submit store=no group=data_entry_contact_co,data_entry_contact_person parent=contact_entry_buttons
	@caption Salvesta

	@property contact_entry_reset type=text store=no group=data_entry_contact_co,data_entry_contact_person parent=contact_entry_buttons no_caption=1

@property last_entries_list type=table store=no group=data_entry_contact_co,data_entry_contact_person no_caption=1 parent=de_table_box


// --------------- RELATION TYPES ---------------------
@reltype OWNER value=1 clid=CL_CRM_COMPANY
@caption Keskkonna omanikfirma

@reltype CONTACT_CO value=2 clid=CL_CRM_COMPANY
@caption Kontakt (organisatsioon)

@reltype FOLDER value=3 clid=CL_MENU
@caption Kaust

@reltype ROLE_PROFESSION value=4 clid=CL_CRM_PROFESSION
@caption Rollile vastav amet

@reltype CONTACT_PERSON value=5 clid=CL_CRM_PERSON
@caption Kontakt (isik)

@reltype CFGFORM value=6 clid=CL_CFGFORM
@caption Seadete vorm

*/

class crm_sales extends class_base
{
	// calls list views
	const CLV_CURRENT = 1;
	const CLV_SEARCH = 2;

	protected $calls_list_views = array();
	protected $calls_list_view = self::CLV_CURRENT;

	function crm_sales ()
	{
		$this->calls_list_views = array(
			self::CLV_CURRENT => array(
				"caption" => t("T&auml;nased k&otilde;ned"),
				"in_tree" => true
			),
			self::CLV_SEARCH => array(
				"caption" => t("Otsingu tulemused"),
				"in_tree" => false
			)
		);
		$this->init(array(
			"tpldir" => "applications/sales/crm_sales",
			"clid" => CL_CRM_SALES
		));
	}

	function callback_on_load($arr)
	{
		if (!empty($arr["request"]["group"]) and $arr["request"]["group"] === "calls")
		{
			// determine calls list type
			if (!empty($arr["request"]["cs_submit"]))
			{
				$this->calls_list_view = self::CLV_SEARCH;
			}
		}
	}

	function get_property(&$arr)
	{
		$ret = PROP_OK;
		if (self::CLV_SEARCH === $this->calls_list_view and substr($arr["prop"]["name"], 0, 3) === "cs_" and isset($arr["request"][$arr["prop"]["name"]]))
		{
			$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
		}
		return $ret;
	}

	function callback_generate_scripts($arr)
	{
		$js = "\n /* crm_sales scripts */ \n(function(){\n";

		if($arr["request"]["group"] === "data_entry_contact_co" or $arr["request"]["group"] === "data_entry")
		{
			load_javascript("bsnAutosuggest.js");
			$options_url = $this->mk_my_orb("get_entry_name_choices", array("id" => $arr["obj_inst"]->id()));
			$contact_details_url = $this->mk_my_orb("get_contact_details", array("id" => $arr["obj_inst"]->id()));
			$js .= <<<SCRIPTVARIABLES
var optionsUrl = "{$options_url}&";
var contactDetailsUrl = "{$contact_details_url}";
SCRIPTVARIABLES;
			$js .= file_get_contents(AW_DIR . "classes/applications/crm/sales/crm_sales_co_entry.js");
		}
		elseif($arr["request"]["group"] === "data_entry_contact_person" or $arr["request"]["group"] === "data_entry")
		{
			load_javascript("bsnAutosuggest.js");
			$js .= file_get_contents(AW_DIR . "classes/applications/crm/sales/crm_sales_person_entry.js");
		}

		$js .= "})()\n /* END crm_sales scripts */\n";
		return $js;
	}

	function parse_properties($args = array())
	{
		$r = parent::parse_properties($args);

		if (isset($r["contact_entry_co_name"]))
		{
			// disable company name std autocomplete
			unset($r["contact_entry_co_name"]["autocomplete_source"]);
			unset($r["contact_entry_co_name"]["autocomplete_params"]);
		}

		return $r;
	}

	/** Outputs crm_company/crm_person autocomplete options matching string
		@attrib name=get_entry_name_choices all_args=1 nologin=1 is_public=1
		@param id required type=oid acl=view
		@param typed_text optional type=string
	**/
	function get_entry_name_choices($arr)
	{
		$choices = array("results" => array());
		if (isset($arr["typed_text"]) and strlen($arr["typed_text"]) > 1)
		{
			$this_o = new object($arr["id"]);
			$owner = $this_o->prop("owner");
			$typed_text = $arr["typed_text"];
			$list = new object_list(array(
				"class_id" => CL_CRM_COMPANY,
				"name" => "{$typed_text}%",
				"CL_CRM_COMPANY.RELTYPE_BUYER(CL_CRM_COMPANY_CUSTOMER_DATA).seller" => $owner->id(),
				"site_id" => array(),
				"lang_id" => array(),
				new obj_predicate_limit(25)
			));
			if ($list->count() > 0)
			{
				$results = array();
				$o = $list->begin();
				do
				{
					$results[] = array("id" => $o->id(), "value" => $o->name());
				}
				while ($o = $list->next());
				$choices["results"] = $results;
			}
		}

		ob_start("ob_gzhandler");
		header("Content-Type: application/json");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Pragma: no-cache"); // HTTP/1.0
		// header ("Content-type: text/javascript; charset: UTF-8");
		// header("Expires: ".gmdate("D, d M Y H:i:s", time()+43200)." GMT");
		$choices = json_encode($choices);
		exit($choices);
	}


	/** Outputs crm_company/crm_person property details in json format
		@attrib name=get_contact_details all_args=1 nologin=1 is_public=1
		@param id required type=oid acl=view
		@param contact_id required type=oid acl=view
	**/
	function get_contact_details($arr)
	{
		$o = new object($arr["contact_id"]);
		$class = basename(aw_ini_get("classes." . $o->class_id() . ".file"));
		$i = new crm_sales();
		$i->output_client = "jsonclient";
		$group = (CL_CRM_COMPANY == $o->class_id()) ? "data_entry_contact_co" : "data_entry_contact_person";
		$args = array(
			"id" => $arr["id"],
			"contact_list_load_contact" => $arr["contact_id"],
			"action" => "change",
			"group" => $group,
			"class" => "crm_sales"
		);
		$r = $i->change($args);
		$r = substr(trim($r), 0, -1) . ",\"id\":{$arr["contact_id"]}}";
		exit($r);
	}

	function _get_presentations_cal(&$arr)
	{
		// cfg calendar
		$cal = &$arr["prop"]["vcl_inst"];
		$viewtype = empty($arr["request"]["viewtype"]) ? "month" : $arr["request"]["viewtype"];
		$date = empty($arr["request"]["date"]) ? null/* ///!!! default mis on? */ : $arr["request"]["date"];
		$range = $cal->get_range(array(
			"date" => $date,
			"viewtype" => $viewtype
		));
		$start = $range["start"];
		$end = $range["end"];

		// get events
		$this_o = $arr["obj_inst"];
		$owner = $arr["obj_inst"]->prop("owner");
		$events = new crm_presentation_list(array(
			"start" => new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $start, $end)
		));

		// insert events
		if ($events->count() > 0)
		{
			$event = $events->begin();
			do
			{
				$cal->add_item(array(
					"timestamp" => $event->prop("start"),
					"item_start" => $event->prop("start"),
					"item_end" => $event->prop("end"),
					"data" => array(
						"name" => $event->name(),
						"link" => html::get_change_url($event->id(), array("return_url" => get_ru())),
						"modifiedby" => $event->prop("modifiedby"),
						"comment" => $event->comment()
					)
				));
			}
			while ($event = $events->next());
		}
		return PROP_OK;
	}

	function _get_contact_entry_co(&$arr)
	{
		if (!empty($arr["request"]["contact_list_load_contact"]))
		{
			$contact = obj($arr["request"]["contact_list_load_contact"], array(), CL_CRM_COMPANY, true);
			if ($contact->createdby() === aw_global_get("uid") and $contact->created() > mktime(0, 0, 0))
			{
				$arr["prop"]["edit_id"] = $contact->id();
			}
		}
		return PROP_OK;
	}

	function _get_contact_entry_reset(&$arr)
	{
		$arr["prop"]["value"] = html::button(array(
			"type" => "reset",
			"id" => "button",
			"onclick" => "document.forms['changeform'].reset();$('form[name=changeform]').reset();",
			"value" => t("T&uuml;hjenda")
		));
		return PROP_OK;
	}

	function _set_contact_entry_co(&$arr)
	{
		$return = PROP_IGNORE;
		$this_o = $arr["obj_inst"];
		if (isset($arr["prop"]["value"]["id"]))
		{
			$o = obj($arr["prop"]["value"]["id"], array(), CL_CRM_COMPANY, true);
			$o->set_name($arr["prop"]["value"]["name"]);
			$o->set_prop("fake_phone", $arr["prop"]["value"]["fake_phone"]);
			$o->set_prop("fake_mobile", $arr["prop"]["value"]["fake_mobile"]);
			$o->set_prop("fake_email", $arr["prop"]["value"]["fake_email"]);
			$o->set_prop("fake_address_address", $arr["prop"]["value"]["fake_address_address"]);
			$o->set_prop("fake_address_postal_code", $arr["prop"]["value"]["fake_address_postal_code"]);
			$o->set_prop("fake_address_city", $arr["prop"]["value"]["fake_address_city"]);
			$o->set_prop("fake_address_county", $arr["prop"]["value"]["fake_address_county"]);
			$o->set_prop("fake_address_country_relp", $arr["prop"]["value"]["fake_address_country_relp"]);
			$o->save();
		}
		else
		{
			try
			{
				$o = obj(null, array(), CL_CRM_COMPANY);
				$o->set_parent($arr["obj_inst"]->prop("owner")->id());
				$o->set_name($arr["prop"]["value"]["name"]);
				$o->set_prop("fake_phone", $arr["prop"]["value"]["fake_phone"]);
				$o->set_prop("fake_mobile", $arr["prop"]["value"]["fake_mobile"]);
				$o->set_prop("fake_email", $arr["prop"]["value"]["fake_email"]);
				$o->set_prop("fake_address_address", $arr["prop"]["value"]["fake_address_address"]);
				$o->set_prop("fake_address_postal_code", $arr["prop"]["value"]["fake_address_postal_code"]);
				$o->set_prop("fake_address_city", $arr["prop"]["value"]["fake_address_city"]);
				$o->set_prop("fake_address_county", $arr["prop"]["value"]["fake_address_county"]);
				$o->set_prop("fake_address_country_relp", $arr["prop"]["value"]["fake_address_country_relp"]);
				$o->save();
				$owner = $arr["obj_inst"]->prop("owner");
				$customer_relation = $o->get_customer_relation($owner, true);
				$this_o->add_contact($customer_relation);
			}
			catch (Exception $e)
			{
				$o->delete();
				$customer_relation->delete();
				throw $e;
			}
		}
		return PROP_IGNORE;
	}

	function _set_contact_entry_person(&$arr)
	{
		$return = PROP_IGNORE;
		$this_o = $arr["obj_inst"];
		if (isset($arr["prop"]["value"]["id"]))
		{
			$o = obj($arr["prop"]["value"]["id"], array(), CL_CRM_COMPANY, true);
			$o->set_prop("firstname", $arr["prop"]["value"]["firstname"]);
			$o->set_prop("lastname", $arr["prop"]["value"]["lastname"]);
			$o->set_prop("gender", $arr["prop"]["value"]["gender"]);
			$o->set_prop("fake_phone", $arr["prop"]["value"]["fake_phone"]);
			$o->set_prop("fake_email", $arr["prop"]["value"]["fake_email"]);
			$o->set_prop("fake_address_address", $arr["prop"]["value"]["fake_address_address"]);
			$o->set_prop("fake_address_postal_code", $arr["prop"]["value"]["fake_address_postal_code"]);
			$o->set_prop("fake_address_city", $arr["prop"]["value"]["fake_address_city"]);
			$o->set_prop("fake_address_county", $arr["prop"]["value"]["fake_address_county"]);
			$o->set_prop("fake_address_country", $arr["prop"]["value"]["fake_address_country"]);
			$o->save();
		}
		else
		{
			try
			{
				$o = obj(null, array(), CL_CRM_PERSON);
				$o->set_parent($arr["obj_inst"]->prop("owner")->id());
				$o->set_prop("firstname", $arr["prop"]["value"]["firstname"]);
				$o->set_prop("lastname", $arr["prop"]["value"]["lastname"]);
				$o->set_prop("gender", $arr["prop"]["value"]["gender"]);
				$o->set_prop("fake_phone", $arr["prop"]["value"]["fake_phone"]);
				$o->set_prop("fake_email", $arr["prop"]["value"]["fake_email"]);
				$o->set_prop("fake_address_address", $arr["prop"]["value"]["fake_address_address"]);
				$o->set_prop("fake_address_postal_code", $arr["prop"]["value"]["fake_address_postal_code"]);
				$o->set_prop("fake_address_city", $arr["prop"]["value"]["fake_address_city"]);
				$o->set_prop("fake_address_county", $arr["prop"]["value"]["fake_address_county"]);
				$o->set_prop("fake_address_country", $arr["prop"]["value"]["fake_address_country"]);
				$o->save();
				$owner = $arr["obj_inst"]->prop("owner");
				$customer_relation = $o->get_customer_relation($owner, true);
				$this_o->add_contact($customer_relation);
			}
			catch (Exception $e)
			{
				$o->delete();
				$customer_relation->delete();
				throw $e;
			}
		}
		return PROP_IGNORE;
	}

	function _get_calls_tree(&$arr)
	{
		$tree = $arr["prop"]["vcl_inst"];

		foreach ($this->calls_list_views as $key => $data)
		{
			if ($data["in_tree"])
			{
				$url = automatweb::$request->get_uri();

				if (self::CLV_CURRENT === $key)
				{
					$url->unset_arg(array(
						"ft_page",
						"cs_submit",
						"cs_name",
						"cs_salesman",
						"cs_address",
						"cs_phone",
						"cs_status"
					));
				}

				$tree->add_item (0, array (
					"name" => $data["caption"],
					"id" => $key,
					"parent" => 0,
					"url" => $url->get()
				));
			}
		}

		$tree->set_selected_item ($this->calls_list_view);
		return PROP_OK;
	}

	function _get_calls_toolbar(&$arr)
	{
		$toolbar = $arr["prop"]["vcl_inst"];
		$toolbar->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"action" => "submit",
			"tooltip" => t("Salvesta")
		));
		return PROP_OK;
	}

	function get_calls_list(&$arr)
	{
		// @property cs_name
		// @property cs_salesman
		// @property cs_address
		// @property cs_phone
		// @property cs_status

		$owner = $arr["obj_inst"]->prop("owner");
		$per_page = 30;
		$start = 0;
		$filter = array(
			new obj_predicate_limit($per_page, $start),
		);

		if (!empty($arr["request"]["cs_submit"]))
		{
			if (!empty($arr["request"]["cs_name"]))
			{
				$filter["customer.name"] = "%{$arr["request"]["cs_name"]}%";
			}

			if (!empty($arr["request"]["cs_phone"]))
			{
				$filter["customer.RELTYPE_PHONE.name"] = "%{$arr["request"]["cs_phone"]}%";
			}

			if (!empty($arr["request"]["cs_salesman"]))
			{
				// "buyer" => $this->id()
				// "seller" => $my_co
				// $filter["customer.salesman.name"] = "%{$arr["request"]["cs_name"]}%";
				$filter["customer.salesman.name"] = "%{$arr["request"]["cs_salesman"]}%";
			}

			if (!empty($arr["request"]["cs_address"]))
			{
				$filter["customer.RELTYPE_ADDRESS.name"] = "%{$arr["request"]["cs_address"]}%";
			}

			if (!empty($arr["request"]["cs_status"]))
			{
				$filter["customer.name"] = "%{$arr["request"]["cs_name"]}%";
			}
		}

		$calls = new crm_call_list($filter);
		return $calls;
	}

	function _get_calls_list(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$table = $arr["prop"]["vcl_inst"];
		$this->define_calls_list_tbl_header($arr);
		$owner = $arr["obj_inst"]->prop("owner");
		$calls = $this->get_calls_list($arr);
		if ($calls->count())
		{
			$call = $calls->begin();
			do
			{
				$customer_relation = $call->get_first_obj_by_reltype("RELTYPE_CUSTOMER_RELATION");
				if (is_object($customer_relation))
				{
					$customer = $customer_relation->get_first_obj_by_reltype("RELTYPE_BUYER");
					$salesman = $customer_relation->prop("salesman");
					$salesman = is_oid($salesman) ? new object($salesman) : obj(null, array(), CL_CRM_PERSON);
					$call_timestamp =  $call->prop("start1");
					$call_time =  date("d.m.Y H:i", $call_timestamp);

					$calls_made = $this_o->get_calls_count($customer_relation);

					if ($calls_made > 0)
					{
						$last_call = $this_o->get_last_call($customer_relation);
						$last_call_result = crm_call_obj::result_names($last_call->prop("result"));
						$last_call_result = reset($last_call_result);
						$last_call = locale::get_lc_date($last_call->prop("real_start"), LC_DATE_FORMAT_SHORT_FULLYEAR);
					}
					else
					{
						$last_call = $last_call_result = t("-");
					}


					$lead_source = $customer_relation->prop("lead_source");
					if ($lead_source)
					{
						$lead_source = new object($lead_source);
						$lead_source = $lead_source->name();
					}
					else
					{
						$lead_source = t("-");
					}

					$unit = $salesman->get_org_section();
					if ($unit)
					{
						$unit = new object($unit);
						$unit = $unit->name();
					}
					else
					{
						$unit = t("-");
					}

					$phones = new object_list($customer->connections_from(array("type" => "RELTYPE_PHONE")));
					$phones_str = array();
					$phone = $phones->begin();
					do
					{
						$number = trim($phone->name());
						if (strlen($number) > 1)
						{
							$request = (array) $arr["request"];
							$request["return_url"] = get_ru();
							unset($request["action"]);
							$url = $this->mk_my_orb("make_call", array("phone_id" => $phone->id(), "call_id" => $call->id()) + $request, "crm_sales");
							$phones_str[] = html::href(array("caption" => $phone->name(), "url" => $url));
						}
					}
					while ($phone = $phones->next());

					$table->define_data(array(
						"name" => $customer->name(),
						"phones" => implode(", ", $phones_str),
						"address" => $customer->get_address_string(),
						"unit" => $unit,
						"lead_source" => $lead_source,
						"last_call" => $last_call,
						"call_time" => $call_time,
						"call_timestamp" => $call_timestamp,
						"last_call_result" => $last_call_result,
						"calls_made" => $calls_made,
						"salesman" => $salesman->name()
					));
				}
			}
			while ($call = $calls->next());

			if (!empty($arr["request"]["cs_submit"]))
			{
				$table->set_caption(t("Otsingu tulemused"));
			}
		}
		return PROP_OK;
	}

	function define_calls_list_tbl_header(&$arr)
	{
		$table = $arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Kliendi nimi")
		));
		$table->define_field(array(
			"name" => "phones",
			"caption" => t("Telefon(id)")
		));
		$table->define_field(array(
			"name" => "unit",
			"caption" => t("Osakond")
		));
		$table->define_field(array(
			"name" => "address",
			"caption" => t("Aadress")
		));
		$table->define_field(array(
			"name" => "lead_source",
			"caption" => t("Allikas/soovitaja")
		));
		$table->define_field(array(
			"name" => "last_call",
			"caption" => t("Viimase k&otilde;ne aeg")
		));
		$table->define_field(array(
			"name" => "call_time",
			"sorting_field" => "call_timestamp",
			"caption" => t("Helistada")
		));
		$table->define_field(array(
			"name" => "last_call_result",
			"caption" => t("Kontakti staatus")
		));
		$table->define_field(array(
			"name" => "calls_made",
			"caption" => t("K&otilde;nesid")
		));
		$table->define_field(array(
			"name" => "salesman",
			"caption" => t("M&uuml;&uuml;giesindaja")
		));
		$table->set_numeric_field(array("call_timestamp"));
		$table->set_default_sortby("call_timestamp");
		$table->set_default_sorder("asc");
	}

	function _get_last_entries_list(&$arr)
	{
		$table = $arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Kliendi nimi")
		));
		$owner = $arr["obj_inst"]->prop("owner");
		$clid = $arr["request"]["group"] === "data_entry_contact_person" ? CL_CRM_PERSON : CL_CRM_COMPANY;
		$list = new object_list(array(
			"class_id" => $clid,
			"parent" => $owner->id(),//!!! liiga tinglik. cro kaudu n2iteks teha
			"createdby" => aw_global_get("uid"),
			"created" => new obj_predicate_compare(OBJ_COMP_GREATER, mktime(0, 0, 0)),
			new obj_predicate_sort(array("created" => "desc")),
			new obj_predicate_limit(5),
			"site_id" => array(),
			"lang_id" => array()
		));
		$list->sort_by(array(
			"prop" => "created",
			"order" => "asc"
		));

		if ($list->count())
		{
			$url = automatweb::$request->get_uri();
			$o = $list->begin();
			do
			{
				$url->set_arg("contact_list_load_contact", $o->id());
				$table->define_data(array(
					"name" => html::href(array("caption" => $o->name(), "url" => $url->get()))
				));
			}
			while ($o = $list->next());
		}
		$table->set_caption(t("Viimased sisestused"));
		return PROP_OK;
	}

	function _get_role_profession_data_entry_clerk(&$arr)
	{
		$arr["prop"]["options"] = $arr["obj_inst"]->prop("owner")->get_company_professions()->names();
		return PROP_OK;
	}

	function _get_role_profession_salesman(&$arr)
	{
		$arr["prop"]["options"] = $arr["obj_inst"]->prop("owner")->get_company_professions()->names();
		return PROP_OK;
	}

	function _get_role_profession_telemarketing_salesman(&$arr)
	{
		$arr["prop"]["options"] = $arr["obj_inst"]->prop("owner")->get_company_professions()->names();
		return PROP_OK;
	}

	function _get_role_profession_telemarketing_manager(&$arr)
	{
		$arr["prop"]["options"] = $arr["obj_inst"]->prop("owner")->get_company_professions()->names();
		return PROP_OK;
	}

	function _get_owner($arr)
	{
		$arr["prop"]["value"] = $arr["prop"]["value"]->id();
		return PROP_OK;
	}

	function _set_owner($arr)
	{
		$arr["prop"]["value"] = new object($arr["prop"]["value"]);
		return PROP_OK;
	}

	/**
		@attrib name=make_call all_args=1
		@param id required type=oid
		@param call_id required type=oid
		@param phone_id required type=oid
		@param return_url optional type=string
	**/
	function make_call($arr)
	{
		$this_o = obj($arr["id"], array(), CL_CRM_SALES, true);
		$call = obj($arr["call_id"], array(), CL_CRM_CALL, true);
		$phone = obj($arr["phone_id"], array(), CL_CRM_PHONE, true);
		$arr2 = $arr;
		$arr2["id"] = $arr["call_id"];
		$arr2["return_url"] = post_ru();//!!! kysitav
		unset($arr2["call_id"]);
		$this_o->make_call($call, $phone);
		return $this->mk_my_orb("start", $arr2, "crm_call");
	}

	/**
		@attrib name=end_call all_args=1
		@param id required type=oid
		@param call_id required type=oid
		@param return_url optional type=string
	**/
	function end_call($arr)
	{
		$this_o = obj($arr["id"], array(), CL_CRM_SALES, true);
		$call = obj($arr["call_id"], array(), CL_CRM_CALL, true);
		$this_o->end_call($call);
		$arr["id"] = $arr["call_id"];
		unset($arr["call_id"]);

		if (isset($arr["return_url"]))
		{
			$url = new aw_uri($arr["return_url"]); // call was made from calls list tab in crm_sales thru make_call. take two steps back
			$r = $url->arg_isset("return_url") ? $url->arg("return_url") : $url->get();
		}
		else
		{
			$arr2 = array(
				"action" => "change",
				"id" => $this_o->id(),
				"group" => "calls"
			);
			$r = $this->mk_my_orb($arr2["action"], $arr2);
		}
		return $r;
	}
}

?>
