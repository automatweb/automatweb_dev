<?php

/*

@classinfo syslog_type=ST_CRM_SALES relationmgr=yes no_status=1 maintainer=voldemar prop_cb=1

@groupinfo settings caption="Seaded"
@groupinfo contacts caption="Kontaktid" submit_method=get
@groupinfo calls caption="K&otilde;ned" submit_method=get
@groupinfo presentations caption="Esitlused" submit_method=get
@groupinfo calendar caption="Kalender" submit=no
@groupinfo personal_calendar caption="Minu kalender" submit=no parent=calendar
@groupinfo general_calendar caption="&Uuml;ldkalender" submit=no parent=calendar
@groupinfo data_entry caption="Sisestamine"
@groupinfo data_entry_contact_person caption="Kontakt (isik)" parent=data_entry
@groupinfo data_entry_contact_co caption="Kontakt (organisatsioon)" parent=data_entry
@groupinfo data_entry_import caption="Import" parent=data_entry

@default table=objects
@default field=meta
@default method=serialize
@default group=general
	@property owner type=relpicker reltype=RELTYPE_OWNER clid=CL_CRM_COMPANY
	@caption Keskkonna omanik

@default group=settings
	@layout splitbox1 type=hbox width=50%:50% closeable=1 no_caption=1
	@layout splitbox2 type=vbox closeable=1 area_caption=Kasutajaliidese&nbsp;vaadete&nbsp;konfiguratsioonid&nbsp;rollide&nbsp;kaupa
	@layout splitbox21 type=hbox width=50%:50% parent=splitbox2
	@layout splitbox22 type=hbox width=50%:50% parent=splitbox2
	@layout folders_box type=vbox parent=splitbox1 closeable=1 area_caption=&Uuml;ldseaded
	@layout roles_box type=vbox parent=splitbox1 closeable=1 area_caption=Rollid
	@layout main_app_cfg_box type=vbox parent=splitbox21 area_caption="M&uuml;i&uuml;git&ouml;&ouml;laud"
	@layout call_cfg_box type=vbox parent=splitbox21 area_caption="K&otilde;ne"
	@layout presentation_cfg_box type=vbox parent=splitbox22 area_caption="Esitlus"

	@property calls_folder type=relpicker reltype=RELTYPE_FOLDER clid=CL_MENU parent=folders_box
	@comment Kaust kuhu salvestatakse ning kust loetakse selle m&uuml;&uuml;gikeskkonna k&otilde;neobjektid
	@caption K&otilde;nede kaust

	@property presentations_folder type=relpicker reltype=RELTYPE_FOLDER clid=CL_MENU parent=folders_box
	@comment Kaust kuhu salvestatakse ning kust loetakse selle m&uuml;&uuml;gikeskkonna esitlused
	@caption Esitluste kaust

	@property avg_call_duration_est type=textbox default=300 datatype=int parent=folders_box
	@comment Hinnanguline keskmine m&uuml;&uuml;gik&otilde;ne kestus (sekundites)
	@caption K&otilde;nekestuse hinnang (s)

	@property call_result_busy_recall_time type=textbox default=300 datatype=int parent=folders_box
	@comment Millise ajavahemiku j&auml;rel uuesti helistada kui number oli kinni (sekundites)
	@caption Uus k&otilde;ne kui nr. kinni (s)

	@property call_result_noanswer_recall_time type=textbox default=7200 datatype=int parent=folders_box
	@comment Millise ajavahemiku j&auml;rel uuesti helistada kui number ei vasta (sekundites)
	@caption Uus k&otilde;ne kui ei vasta (s)

	@property call_result_outofservice_recall_time type=textbox default=86400 datatype=int parent=folders_box
	@comment Millise ajavahemiku j&auml;rel uuesti helistada kui number on teeninduspiirkonnast v&auml;ljas (sekundites)
	@caption Uus k&otilde;ne kui tel. v&auml;ljas (s)

	@property call_result_noanswer_recall_time type=textbox default=43200 datatype=int parent=folders_box
	@comment Millise ajavahemiku j&auml;rel uuesti helistada kui numbril vastab automaatvastaja v&otilde;i k&otilde;nepost (sekundites). 0 t&auml;hendab
	@caption Uus k&otilde;ne kui automaatvastaja/k&otilde;nepost (s)

	@property call_result_recall_retries type=textbox default=5 datatype=int parent=folders_box
	@comment Mitu korda uuesti helistada kui kinni, ei vasta, on v&auml;ljas v&otilde;i automaatvastaja
	@caption Uuesti proovida enim (korda)

	@property avg_presentation_duration_est type=textbox default=7200 datatype=int parent=folders_box
	@comment Hinnanguline keskmine m&uuml;&uuml;giesinduse kestus (sekundites)
	@caption Esitluse kestuse hinnang (s)

	@property autocomplete_options_limit type=textbox default=20 datatype=int parent=folders_box
	@comment Mitu valikut pakkuda autocomplete otsingutes
	@caption Autocomplete valikuid

	@property tables_rows_per_page type=textbox default=25 datatype=int parent=folders_box
	@comment Mitu lehel rida kuvada tabelites
	@caption Ridu tabelites

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


	//// presentation cfgforms
	@property cfgf_presentation_generic type=relpicker reltype=RELTYPE_CFGFORM parent=presentation_cfg_box
	@caption K&otilde;ik kasutajad

	@property cfgf_presentation_data_entry_clerk type=relpicker reltype=RELTYPE_CFGFORM parent=presentation_cfg_box
	@caption Andmesisestaja

	@property cfgf_presentation_telemarketing_salesman type=relpicker reltype=RELTYPE_CFGFORM parent=presentation_cfg_box
	@caption Telemarketingit&ouml;&ouml;taja

	@property cfgf_presentation_telemarketing_manager type=relpicker reltype=RELTYPE_CFGFORM parent=presentation_cfg_box
	@caption Telemarketingi juht

	@property cfgf_presentation_salesman type=relpicker reltype=RELTYPE_CFGFORM parent=presentation_cfg_box
	@caption M&uuml;&uuml;giesindaja

	@property cfgf_presentation_manager type=relpicker reltype=RELTYPE_CFGFORM parent=presentation_cfg_box
	@caption Juht



@default group=contacts
	@layout contacts_vsplitbox type=hbox width=25%:75%
	@property contacts_toolbar type=toolbar store=no no_caption=1
	@layout contacts_box type=vbox parent=contacts_vsplitbox
	@layout contacts_tree_box type=vbox closeable=1 area_caption=Kontaktide&nbsp;valik parent=contacts_box
	@property contacts_tree type=treeview store=no no_caption=1 parent=contacts_tree_box
	@property contacts_list type=table store=no no_caption=1 parent=contacts_vsplitbox

	@layout contacts_search_box type=vbox closeable=1 area_caption=Kontaktide&nbsp;otsing parent=contacts_box
		@property cts_name type=textbox view_element=1 parent=contacts_search_box store=no size=33
		@caption Nimi

		@property cts_address type=textbox view_element=1 parent=contacts_search_box store=no size=33
		@caption Aadress

		@property cts_phone type=textbox view_element=1 parent=contacts_search_box store=no size=33
		@caption Telefon

		@property cts_lead_source type=textbox view_element=1 parent=contacts_search_box store=no size=33
		@caption Soovitaja

		@property cts_salesman type=select view_element=1 parent=contacts_search_box store=no
		@caption M&uuml;&uuml;gi-<br/>esindaja

		@property cts_status type=select view_element=1 parent=contacts_search_box store=no
		@caption Staatus

		@property cts_submit type=submit value=Otsi view_element=1 parent=contacts_search_box store=no
		@caption Otsi



@default group=calls
	@layout calls_vsplitbox type=hbox width=25%:75%
	@property calls_toolbar type=toolbar store=no no_caption=1
	@layout calls_box type=vbox parent=calls_vsplitbox
	@layout calls_tree_box type=vbox closeable=1 area_caption=K&otilde;nede&nbsp;valik parent=calls_box
	@property calls_tree type=treeview store=no no_caption=1 parent=calls_tree_box
	@property calls_list type=table store=no no_caption=1 parent=calls_vsplitbox

	@layout calls_search_box type=vbox closeable=1 area_caption=K&otilde;nede&nbsp;otsing parent=calls_box
		@property cs_name type=textbox view_element=1 parent=calls_search_box store=no size=33
		@caption Kontakti nimi

		@property cs_address type=textbox view_element=1 parent=calls_search_box store=no size=33
		@caption Kontakti aadress

		@property cs_phone type=textbox view_element=1 parent=calls_search_box store=no size=33
		@caption Telefon

		@property cs_lead_source type=textbox view_element=1 parent=calls_search_box store=no size=33
		@caption Soovitaja

		@property cs_salesman type=select view_element=1 parent=calls_search_box store=no
		@caption M&uuml;&uuml;gi-<br/>esindaja

		@property cs_status type=select view_element=1 parent=calls_search_box store=no
		@caption Kontakti staatus

		@property cs_submit type=submit value=Otsi view_element=1 parent=calls_search_box store=no
		@caption Otsi



@default group=presentations
	@layout presentations_vsplitbox type=hbox width=25%:75%
	@property presentations_toolbar type=toolbar store=no no_caption=1
	@layout presentations_box type=vbox parent=presentations_vsplitbox
	@layout presentations_tree_box type=vbox closeable=1 area_caption=Esitluste&nbsp;valik parent=presentations_box
	@property presentations_tree type=treeview store=no no_caption=1 parent=presentations_tree_box
	@property presentations_list type=table store=no no_caption=1 parent=presentations_vsplitbox

	@layout presentations_search_box type=vbox closeable=1 area_caption=Esitluste&nbsp;otsing parent=presentations_box
		@property ps_name type=textbox view_element=1 parent=presentations_search_box store=no size=33
		@caption Kontakti nimi

		@property ps_address type=textbox view_element=1 parent=presentations_search_box store=no size=33
		@caption Kontakti aadress

		@property ps_phone type=textbox view_element=1 parent=presentations_search_box store=no size=33
		@caption Telefon

		@property ps_lead_source type=textbox view_element=1 parent=presentations_search_box store=no size=33
		@caption Soovitaja

		@property ps_salesman type=select view_element=1 parent=presentations_search_box store=no
		@caption M&uuml;&uuml;gi-<br/>esindaja

		@property ps_status type=select view_element=1 parent=presentations_search_box store=no
		@caption Staatus

		@property ps_submit type=submit value=Otsi view_element=1 parent=presentations_search_box store=no
		@caption Otsi


@default group=personal_calendar
	@property personal_calendar type=calendar store=no no_caption=1

@default group=general_calendar
	@property general_calendar type=calendar store=no no_caption=1


@layout de_form_box type=vbox group=data_entry_contact_co,data_entry_contact_person area_caption=Uus&nbsp;kontakt
@layout de_table_box type=vbox group=data_entry_contact_co,data_entry_contact_person
@layout contact_entry_form type=vbox group=data_entry_contact_co,data_entry_contact_person parent=de_form_box


@default group=data_entry
	@property contact_entry_toolbar type=toolbar store=no group=data_entry_contact_co,data_entry_contact_person no_caption=1

@default group=data_entry_contact_co
	@property contact_entry_co type=releditor reltype=RELTYPE_CONTACT_CO store=no props=name,fake_phone,fake_mobile,fake_email parent=contact_entry_form
	@caption Kontakt (organisatsioon)


@default group=data_entry_contact_person
	@property contact_entry_person type=releditor reltype=RELTYPE_CONTACT_PERSON store=no props=lastname,firstname,gender,fake_phone,fake_email parent=contact_entry_form
	@caption Kontakt (isik)

	@property contact_entry_address_title type=text store=no parent=contact_entry_form group=data_entry_contact_co,data_entry_contact_person
	@caption Aadress:

	@property contact_entry_address type=releditor store=no props=country,location_data,location,street,house,apartment,postal_code,po_box table_fields=name,location,street,house,apartment reltype=RELTYPE_TMP1 parent=contact_entry_form group=data_entry_contact_co,data_entry_contact_person

	@property contact_entry_separator1 type=text store=no parent=contact_entry_form group=data_entry_contact_co,data_entry_contact_person
	@caption &nbsp;

	@property contact_entry_salesman type=select store=no group=data_entry_contact_co,data_entry_contact_person parent=contact_entry_form
	@caption M&uuml;&uuml;giesindaja

	@property contact_entry_lead_source type=textbox store=no group=data_entry_contact_co,data_entry_contact_person parent=contact_entry_form
	@caption Soovitaja

	@property contact_entry_lead_source_oid type=hidden store=no group=data_entry_contact_co,data_entry_contact_person

@default group=data_entry_import
	@property import_toolbar type=toolbar store=no no_caption=1

	@property import_objects type=table store=no
	@caption Seadistatud impordid

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

@reltype IMPORT value=7 clid=CL_CSV_IMPORT
@caption Kontaktide import

@reltype TMP1 value=8 clid=CL_ADDRESS
@caption tmp1

*/

class crm_sales extends class_base
{
	// calls list views
	const CALLS_CURRENT = 1;
	const CALLS_SEARCH = 2;

	const CONTACTS_DEFAULT = 1;
	const CONTACTS_SEARCH = 2;

	const PRESENTATIONS_DEFAULT = 1;
	const PRESENTATIONS_SEARCH = 2;

	public static $calls_list_views = array();
	public static $calls_list_view = self::CALLS_CURRENT;

	public static $contacts_list_views = array();
	public static $contacts_list_view = self::CONTACTS_DEFAULT;

	public static $presentations_list_views = array();
	public static $presentations_list_view = self::PRESENTATIONS_DEFAULT;

	protected $contact_entry_edit_object; // object to be edited in contact entry view (crm_company or crm_person)

	function crm_sales ()
	{
		// predefined list views/searches for calls view
		self::$calls_list_views = array(
			self::CALLS_CURRENT => array(
				"caption" => t("T&auml;nased k&otilde;ned"),
				"in_tree" => true
			),
			self::CALLS_SEARCH => array(
				"caption" => t("Otsingu tulemused"),
				"in_tree" => false
			)
		);

		// predefined list views/searches for contacts view
		self::$contacts_list_views = array(
			self::CONTACTS_DEFAULT => array(
				"caption" => t("Kontaktid"),
				"in_tree" => true
			),
			self::CONTACTS_SEARCH => array(
				"caption" => t("Otsingu tulemused"),
				"in_tree" => false
			)
		);

		// predefined list views/searches for presentations view
		self::$presentations_list_views = array(
			self::PRESENTATIONS_DEFAULT => array(
				"caption" => t("Esitlused"),
				"in_tree" => true
			),
			self::PRESENTATIONS_SEARCH => array(
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
		if (!empty($arr["request"]["group"]))
		{
			if ("calls" === $arr["request"]["group"] and !empty($arr["request"]["cs_submit"]))
			{ // determine requested calls list type
				self::$calls_list_view = self::CALLS_SEARCH;
			}
			elseif ("contacts" === $arr["request"]["group"] and !empty($arr["request"]["cts_submit"]))
			{ // determine requested contacts list type
				self::$contacts_list_view = self::CONTACTS_SEARCH;
			}
			elseif ("presentations" === $arr["request"]["group"] and !empty($arr["request"]["ps_submit"]))
			{ // determine requested presentations list type
				self::$presentations_list_view = self::PRESENTATIONS_SEARCH;
			}
		}
	}

	function callback_pre_edit($arr)
	{
		if (isset($arr["request"]["contact_list_load_contact"]) and $this->can("view", $arr["request"]["contact_list_load_contact"]))
		{ // if a contact is selected from last entries list then get its id
			$o = new object($arr["request"]["contact_list_load_contact"]);
			if (("data_entry_contact_co" === $this->use_group and $o->is_a(CL_CRM_COMPANY)) or ("data_entry_contact_person" === $this->use_group and $o->is_a(CL_CRM_PERSON)))
			{
				$this->contact_entry_edit_object = $o;
			}
		}
	}

	function callback_mod_layout(&$arr)
	{
		if (is_object($this->contact_entry_edit_object) and "de_form_box" === $arr["name"])
		{ // if a contact from last entries list is being edited then change entry form container layout caption
			$arr["area_caption"] = sprintf(t("Kontakti '%s' muutmine"), $this->contact_entry_edit_object->name());
		}
		return PROP_OK;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _get_contact_entry_address(&$arr)
	{
		if (is_object($this->contact_entry_edit_object))
		{
			$contact = new object($this->contact_entry_edit_object->id());
			$address = $contact->get_first_obj_by_reltype("RELTYPE_ADDRESS_ALT");

			if ($address)
			{
				$arr["prop"]["edit_id"] = $address->id();
			}
		}
		return PROP_OK;
	}

	function _get_contact_entry_salesman(&$arr)
	{
		$r = PROP_OK;
		if (is_object($this->contact_entry_edit_object))
		{
			$owner = $arr["obj_inst"]->prop("owner");
			$customer_relation = $this->contact_entry_edit_object->get_customer_relation($owner);
			$arr["prop"]["value"] = $customer_relation->prop("salesman");
		}
		$this->set_salesman_options($arr);
		return $r;
	}

	protected function set_salesman_options(&$arr)
	{
		if (is_oid($arr["obj_inst"]->prop("role_profession_salesman")))
		{
			$profession = new object($arr["obj_inst"]->prop("role_profession_salesman"));
			$salespersons = $profession->get_workers(null, false);
			$arr["prop"]["options"] = array("" => "") + $salespersons->names();
		}
	}

	function _get_contact_entry_lead_source(&$arr)
	{
		$r = PROP_OK;
		if (is_object($this->contact_entry_edit_object))
		{
			$owner = $arr["obj_inst"]->prop("owner");
			$customer_relation = $this->contact_entry_edit_object->get_customer_relation($owner);
			$arr["prop"]["value"] = $customer_relation->prop("sales_lead_source.name");
		}
		return $r;
	}

	function _get_contact_entry_lead_source_oid(&$arr)
	{
		$r = PROP_OK;
		if (is_object($this->contact_entry_edit_object))
		{
			$owner = $arr["obj_inst"]->prop("owner");
			$customer_relation = $this->contact_entry_edit_object->get_customer_relation($owner);
			$arr["prop"]["value"] = $customer_relation->prop("sales_lead_source");
		}
		return $r;
	}

	function _get_cs_salesman(&$arr)
	{
		$this->set_salesman_options($arr);
		return PROP_OK;
	}

	function _get_cts_salesman(&$arr)
	{
		$this->set_salesman_options($arr);
		return PROP_OK;
	}

	function _get_import_toolbar(&$arr)
	{
		$toolbar = $arr["prop"]["vcl_inst"];
		$toolbar->add_new_button(array(CL_CSV_IMPORT), $arr["obj_inst"]->id(), 7 /* RELTYPE_IMPORT */);
	}

	function _get_contact_entry_toolbar(&$arr)
	{
		$toolbar = $arr["prop"]["vcl_inst"];
		$toolbar->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => aw_url_change_var("contact_list_load_contact", null),
			"tooltip" => t("Sisesta uus kontakt")
		));
	}

	function _get_import_objects(&$arr)
	{
		$table = $arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "object",
			"caption" => t("Import")
		));
		$table->define_field(array(
			"name" => "commands"
		));
		$list = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_IMPORT")));

		if ($list->count() > 0)
		{
			$o = $list->begin();
			do
			{
				$table->define_data(array(
					"object" => html::obj_change_url($o, $o->name()),
					"commands" => ""
				));
			}
			while ($o = $list->next());
		}
	}

	function get_property(&$arr)
	{
		$ret = PROP_OK;

		if ("cs_status" === $arr["prop"]["name"] or "cts_status" === $arr["prop"]["name"] or "ps_status" === $arr["prop"]["name"])
		{ // set search status selection options
			$arr["prop"]["options"] = array("" => "") + crm_company_customer_data_obj::sales_state_names();
		}

		if ("calls" === $arr["request"]["group"])
		{ // calls view
			if (self::CALLS_SEARCH === self::$calls_list_view and substr($arr["prop"]["name"], 0, 3) === "cs_" and isset($arr["request"][$arr["prop"]["name"]]))
			{
				$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
			}

			$method_name = "_get_{$arr["prop"]["name"]}";
			if (method_exists("crm_sales_calls_view", $method_name))
			{
				$ret = crm_sales_calls_view::$method_name($arr);
			}
		}
		elseif ("contacts" === $arr["request"]["group"])
		{ // contacts view
			if (self::CONTACTS_SEARCH === self::$contacts_list_view and substr($arr["prop"]["name"], 0, 4) === "cts_" and isset($arr["request"][$arr["prop"]["name"]]))
			{
				$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
			}

			$method_name = "_get_{$arr["prop"]["name"]}";
			if (method_exists("crm_sales_contacts_view", $method_name))
			{
				$ret = crm_sales_contacts_view::$method_name($arr);
			}
		}
		elseif ("presentations" === $arr["request"]["group"])
		{ // presentations view
			if (self::PRESENTATIONS_SEARCH === self::$presentations_list_view and substr($arr["prop"]["name"], 0, 3) === "ps_" and isset($arr["request"][$arr["prop"]["name"]]))
			{
				$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
			}

			$method_name = "_get_{$arr["prop"]["name"]}";
			if (method_exists("crm_sales_presentations_view", $method_name))
			{
				$ret = crm_sales_presentations_view::$method_name($arr);
			}
		}

		return $ret;
	}

	function callback_generate_scripts($arr)
	{
		$js = "\n /* crm_sales scripts */ \n(function(){\n";

		if (isset($this->use_group))
		{
			if("data_entry_contact_co" === $this->use_group)
			{
				load_javascript("bsnAutosuggest.js");
				$name_options_url = $this->mk_my_orb("get_entry_choices", array(
					"type" => "co_name",
					"id" => $arr["obj_inst"]->id()
				));
				$phone_options_url = $this->mk_my_orb("get_entry_choices", array(
					"type" => "co_phone",
					"id" => $arr["obj_inst"]->id()
				));
				$contact_details_url = $this->mk_my_orb("get_contact_details", array("id" => $arr["obj_inst"]->id()));
				$lead_source_options_url = $this->mk_my_orb("get_lead_source_choices", array("id" => $arr["obj_inst"]->id()));
				$contact_edit_caption = t("Kontakti '%s' muutmine");
				$js .= <<<SCRIPTVARIABLES
var optionsUrl = "{$name_options_url}&";
var phoneOptionsUrl = "{$phone_options_url}&";
var leadSourceOptionsUrl = "{$lead_source_options_url}&";
var contactDetailsUrl = "{$contact_details_url}";
var contactEditCaption = "{$contact_edit_caption}";
SCRIPTVARIABLES;
				$js .= file_get_contents(AW_DIR . "classes/applications/crm/sales/crm_sales_co_entry.js");
			}
			elseif("data_entry_contact_person" === $this->use_group)
			{
				load_javascript("bsnAutosuggest.js");
				$name_options_url = $this->mk_my_orb("get_entry_choices", array(
					"type" => "p_name",
					"id" => $arr["obj_inst"]->id()
				));
				$phone_options_url = $this->mk_my_orb("get_entry_choices", array(
					"type" => "p_phone",
					"id" => $arr["obj_inst"]->id()
				));
				$contact_details_url = $this->mk_my_orb("get_contact_details", array("id" => $arr["obj_inst"]->id()));
				$lead_source_options_url = $this->mk_my_orb("get_lead_source_choices", array("id" => $arr["obj_inst"]->id()));
				$contact_edit_caption = t("Kontakti '%s' muutmine");
				$js .= <<<SCRIPTVARIABLES
var optionsUrl = "{$name_options_url}&";
var phoneOptionsUrl = "{$phone_options_url}&";
var leadSourceOptionsUrl = "{$lead_source_options_url}&";
var contactEditCaption = "{$contact_edit_caption}";
var contactDetailsUrl = "{$contact_details_url}";
SCRIPTVARIABLES;
				$js .= file_get_contents(AW_DIR . "classes/applications/crm/sales/crm_sales_person_entry.js");
			}
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

	/** Outputs crm_company/crm_person autocomplete options matching property string in bsnAutosuggest format json
		@attrib name=get_entry_choices all_args=1 nologin=1 is_public=1
		@param id required type=oid acl=view
		@param type required type=string
			Determines if person or company searched, and by which property (name, phone nr.)
			Options: co_name, p_name, co_phone, p_phone
		@param typed_text optional type=string
	**/
	function get_entry_choices($arr)
	{
		$types_data = array(
			"co_name" => array(
				"prop" => "buyer(CL_CRM_COMPANY).name",
				"info_prop1" => "buyer.name",
				"info_prop2" => "buyer.RELTYPE_ADDRESS.name"
			),
			"co_phone" => array(
				"prop" => "buyer(CL_CRM_COMPANY).RELTYPE_PHONE.name",
				"info_prop1" => "buyer.name",
				"info_prop2" => "buyer.RELTYPE_ADDRESS.name"
			),
			"p_name" => array(
				"prop" => "buyer(CL_CRM_PERSON).lastname",
				"info_prop1" => "buyer.name",
				"info_prop2" => "buyer.RELTYPE_ADDRESS.name"
			),
			"p_phone" => array(
				"prop" => "buyer(CL_CRM_PERSON).RELTYPE_PHONE.name",
				"info_prop1" => "buyer.name",
				"info_prop2" => "buyer.RELTYPE_ADDRESS.name"
			)
		);
		$type = $arr["type"];
		$choices = array("results" => array());
		if (isset($arr["typed_text"]) and strlen($arr["typed_text"]) > 1 and isset($types_data[$type]))
		{
			$this_o = new object($arr["id"]);
			$owner = $this_o->prop("owner");
			$typed_text = $arr["typed_text"];
			$prop = $types_data[$type]["prop"];
			$limit = $this_o->prop("autocomplete_options_limit") ? $this_o->prop("autocomplete_options_limit") : 20;

			$list = new object_list(array(
				"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
				$prop => "{$typed_text}%",
				"seller" => $owner->id(),
				"site_id" => array(),
				"lang_id" => array(),
				new obj_predicate_limit($limit)
			));

			if ($list->count() > 0)
			{
				$results = array();
				$o = $list->begin();
				do
				{
					$customer = new object($o->prop("buyer"));
					$phones = new object_list($customer->connections_from(array("type" => "RELTYPE_PHONE")));
					$phones = $phones->names();
					$phone = "";

					if (strpos($type, "phone"))
					{
						foreach ($phones as $phone_oid => $phone_nr)
						{
							if (substr($phone_nr, 0, strlen($typed_text)) === $typed_text)
							{
								$phone = $phone_nr;
							}
						}
						$info1 = $customer->name();
					}
					else
					{
						$info1 = implode(", ", $phones);
					}

					$customer_name = strpos($type, "co_") !== false ? $customer->name() : ($customer->lastname . ", " . $customer->firstname);
					$value = strpos($type, "phone") !== false ? $phone : $customer_name;
					$info = $info1 . " | " . $o->prop($types_data[$type]["info_prop2"]);
					$results[] = array("id" => $o->prop("buyer"), "value" => iconv("iso-8859-4", "UTF-8", $value), "info" => $info);
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
		exit(json_encode($choices));
	}

	/** Outputs lead source autocomplete options in bsnAutosuggest format json
		@attrib name=get_lead_source_choices all_args=1 nologin=1 is_public=1
		@param id required type=oid acl=view
		@param typed_text optional type=string
	**/
	function get_lead_source_choices($arr)
	{
		$choices = array("results" => array());
		if (isset($arr["typed_text"]) and strlen($arr["typed_text"]) > 1)
		{
			$this_o = new object($arr["id"]);
			$owner = $this_o->prop("owner");
			$typed_text = $arr["typed_text"];
			$limit = $this_o->prop("autocomplete_options_limit") ? $this_o->prop("autocomplete_options_limit") : 20;

			$list = new object_list(array(
				"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
				new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_COMPANY_CUSTOMER_DATA.buyer(CL_CRM_COMPANY).name" => "{$typed_text}%",
						"CL_CRM_COMPANY_CUSTOMER_DATA.buyer(CL_CRM_PERSON).lastname" => "{$typed_text}%"
					)
				)),
				"seller" => $owner->id(),
				"site_id" => array(),
				"lang_id" => array(),
				new obj_predicate_limit($limit)
			));

			if ($list->count() > 0)
			{
				$results = array();
				$o = $list->begin();
				do
				{
					$customer = new object($o->prop("buyer"));
					$phones = new object_list($customer->connections_from(array("type" => "RELTYPE_PHONE")));
					$phones = $phones->names();
					$info = implode(", ", $phones) . " | " . $o->prop("buyer.RELTYPE_ADDRESS.name");
					$value = $customer->class_id() == CL_CRM_COMPANY ? $customer->name() : ($customer->lastname . ", " . $customer->firstname);
					$results[] = array("id" => $customer->id(), "value" => $value, "info" => $info);
				}
				while ($o = $list->next());
				$choices["results"] = $results;
			}
		}

		ob_start("ob_gzhandler");
		// header("Content-Type: application/json");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Pragma: no-cache"); // HTTP/1.0
		// header ("Content-type: text/javascript; charset: UTF-8");
		// header("Expires: ".gmdate("D, d M Y H:i:s", time()+43200)." GMT");
		exit(json_encode($choices));
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
		$r = substr(trim($r), 0, -1) . ",\"id\":\"{$arr["contact_id"]}\"}";
		exit($r);
	}

	/** Outputs crm_company/crm_person property details in json format
		@attrib name=create_calls
		@param id required type=oid acl=edit
		@param sel required type=array
		@param post_ru required type=string
	**/
	function create_calls($arr)
	{
		$this_o = new object($arr["id"]);
		foreach ($arr["sel"] as $cro_oid)
		{
			$customer_relation = new object((int) $cro_oid, array(), CL_CRM_COMPANY_CUSTOMER_DATA);
			$this_o->create_call($customer_relation);
		}

		$return_url = empty($arr["post_ru"]) ? $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "contacts")) : $arr["post_ru"];
		return $return_url;
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

	function _get_personal_calendar(&$arr)
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
		$events = new crm_task_list(array(
			"start1" => new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $start, $end)
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
		if (is_object($this->contact_entry_edit_object))
		{
			$arr["prop"]["edit_id"] = $this->contact_entry_edit_object->id();
		}
		return PROP_OK;
	}

	function _get_contact_entry_person(&$arr)
	{
		if (is_object($this->contact_entry_edit_object))
		{
			$arr["prop"]["edit_id"] = $this->contact_entry_edit_object->id();
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
		if (empty($arr["prop"]["value"]["fake_phone"]))
		{
			$return = PROP_FATAL_ERROR;
			$arr["prop"]["error"] = t("Telefoninumber on kohustuslik");
		}
		elseif (!empty($arr["request"]["contact_entry_salesman"]) and !$this->can("view", $arr["request"]["contact_entry_salesman"]))
		{
			$return = PROP_FATAL_ERROR;
			$arr["prop"]["error"] = t("Viga m&uuml;&uuml;giesindaja salvestamisel. &Otilde;igused puuduvad.");
		}
		elseif (!empty($arr["request"]["contact_entry_lead_source"]) and !$this->can("view", $arr["request"]["contact_entry_lead_source"]))
		{
			$return = PROP_FATAL_ERROR;
			$arr["prop"]["error"] = t("Viga soovitaja salvestamisel. &Otilde;igused puuduvad.");
		}
		elseif (isset($arr["prop"]["value"]["id"]))
		{
			$o = obj($arr["prop"]["value"]["id"], array(), CL_CRM_COMPANY, true);
			$o->set_name($arr["prop"]["value"]["name"]);
			$o->set_prop("fake_phone", $arr["prop"]["value"]["fake_phone"]);
			$o->set_prop("fake_mobile", $arr["prop"]["value"]["fake_mobile"]);
			$o->set_prop("fake_email", $arr["prop"]["value"]["fake_email"]);
			$o->save();
			//!!! customer relation properties are not changed for an existing contact

			// address
			if (is_oid($arr["request"]["contact_entry_address"]["location_data"]))
			{
				// edit old address or create new if not found
				$address = $o->get_first_obj_by_reltype("RELTYPE_ADDRESS_ALT");
				$new_address = false;
				if (!$address)
				{
					$address = obj(null, array(), CL_ADDRESS);
					$new_address = true;
				}

				$address->set_prop("country", $arr["request"]["contact_entry_address"]["country"]);
				$address->set_location($arr["request"]["contact_entry_address"]["location_data"]);
				$address->set_prop("street", $arr["request"]["contact_entry_address"]["street"]);
				$address->set_prop("house", $arr["request"]["contact_entry_address"]["house"]);
				$address->set_prop("apartment", $arr["request"]["contact_entry_address"]["apartment"]);
				$address->set_prop("postal_code", $arr["request"]["contact_entry_address"]["postal_code"]);
				$address->set_prop("po_box", $arr["request"]["contact_entry_address"]["po_box"]);
				$address->save();

				if ($new_address)
				{
					$o->connect(array("to" => $address, "reltype" => "RELTYPE_ADDRESS_ALT"));
				}
			}
		}
		else
		{
			try
			{
				$owner_oid = $this_o->prop("owner")->id();

				$o = obj(null, array(), CL_CRM_COMPANY);
				$o->set_parent($owner_oid);
				$o->set_name($arr["prop"]["value"]["name"]);
				$o->save();
				$o->set_prop("fake_phone", $arr["prop"]["value"]["fake_phone"]);
				$o->set_prop("fake_mobile", $arr["prop"]["value"]["fake_mobile"]);
				$o->set_prop("fake_email", $arr["prop"]["value"]["fake_email"]);
				$o->save();

				// address
				if (is_oid($arr["request"]["contact_entry_address"]["location_data"]))
				{
					$address = obj(null, array(), CL_ADDRESS);
					$address->set_prop("country", $arr["request"]["contact_entry_address"]["country"]);
					$address->set_location($arr["request"]["contact_entry_address"]["location_data"]);
					$address->set_prop("street", $arr["request"]["contact_entry_address"]["street"]);
					$address->set_prop("house", $arr["request"]["contact_entry_address"]["house"]);
					$address->set_prop("apartment", $arr["request"]["contact_entry_address"]["apartment"]);
					$address->set_prop("postal_code", $arr["request"]["contact_entry_address"]["postal_code"]);
					$address->set_prop("po_box", $arr["request"]["contact_entry_address"]["po_box"]);
					$address->save();
					$o->connect(array("to" => $address, "reltype" => "RELTYPE_ADDRESS_ALT"));
				}

				$owner = $arr["obj_inst"]->prop("owner");
				$customer_relation = $o->get_customer_relation($owner, true);

				if (!empty($arr["request"]["contact_entry_salesman"]))
				{ // set salesman
					$salesman = obj($arr["request"]["contact_entry_salesman"], array(), CL_CRM_PERSON);
					$customer_relation->set_prop("salesman", $salesman->id());
					$customer_relation->connect(array("to" => $salesman, "reltype" => "RELTYPE_SALESMAN"));
				}

				if (!empty($arr["request"]["contact_entry_lead_source_oid"]))
				{ // set lead source
					$lead_source = new object($arr["request"]["contact_entry_lead_source_oid"]);

					if (!$lead_source->is_a(CL_CRM_COMPANY) and !$lead_source->is_a(CL_CRM_PERSON))
					{
						throw new awex_obj_class("Invalid class. Lead source must be a company or a person");
					}

					$customer_relation->set_prop("sales_lead_source", $lead_source->id());
					$customer_relation->set_prop("sales_state", crm_company_customer_data_obj::SALESSTATE_LEAD);
					$customer_relation->connect(array("to" => $lead_source, "reltype" => "RELTYPE_SALES_LEAD_SOURCE"));
				}
				elseif (!empty($arr["request"]["contact_entry_lead_source"]))
				{
					$name = explode(" ", $arr["request"]["contact_entry_lead_source"]);
					foreach ($name as $key => $name_part)
					{
						$name[$key] = ucfirst($name_part);
					}

					$lastname = array_pop($name);
					$firstname = count($name) > 1 ? implode("-", $name) : array_pop($name);
					$lead_source = obj(null, array(), CL_CRM_PERSON);
					$lead_source->set_parent($owner_oid);
					$lead_source->set_prop("firstname", $firstname);
					$lead_source->set_prop("lastname", $lastname);
					$lead_source->save();
					$customer_relation->set_prop("sales_lead_source", $lead_source->id());
					$customer_relation->set_prop("sales_state", crm_company_customer_data_obj::SALESSTATE_LEAD);
					$customer_relation->connect(array("to" => $lead_source, "reltype" => "RELTYPE_SALES_LEAD_SOURCE"));
				}

				$this_o->add_contact($customer_relation);// also saves customer relation
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

		if (empty($arr["prop"]["value"]["fake_phone"]))
		{
			$return = PROP_FATAL_ERROR;
			$arr["prop"]["error"] = t("Telefoninumber on kohustuslik");
		}
		elseif (!empty($arr["request"]["contact_entry_salesman"]) and !$this->can("view", $arr["request"]["contact_entry_salesman"]))
		{
			$return = PROP_FATAL_ERROR;
			$arr["prop"]["error"] = t("Viga m&uuml;&uuml;giesindaja salvestamisel. &Otilde;igused puuduvad.");
		}
		elseif (!empty($arr["request"]["contact_entry_lead_source_oid"]) and !$this->can("view", $arr["request"]["contact_entry_lead_source_oid"]))
		{
			$return = PROP_FATAL_ERROR;
			$arr["prop"]["error"] = t("Viga soovitaja salvestamisel. &Otilde;igused puuduvad.");
		}
		elseif (isset($arr["prop"]["value"]["id"]))
		{
			$o = obj($arr["prop"]["value"]["id"], array(), CL_CRM_PERSON, true);
			$o->set_prop("firstname", ucfirst($arr["prop"]["value"]["firstname"]));
			$o->set_prop("lastname", ucfirst($arr["prop"]["value"]["lastname"]));
			$o->set_prop("gender", $arr["prop"]["value"]["gender"]);
			$o->set_prop("fake_phone", $arr["prop"]["value"]["fake_phone"]);
			$o->set_prop("fake_email", $arr["prop"]["value"]["fake_email"]);
			$o->save();
			//!!! customer relation properties are not changed for an existing contact

			// address
			if (is_oid($arr["request"]["contact_entry_address"]["location_data"]))
			{
				// edit old address or create new if not found
				$address = $o->get_first_obj_by_reltype("RELTYPE_ADDRESS_ALT");
				$new_address = false;
				if (!$address)
				{
					$address = obj(null, array(), CL_ADDRESS);
					$new_address = true;
				}

				$address->set_prop("country", $arr["request"]["contact_entry_address"]["country"]);
				$address->set_location($arr["request"]["contact_entry_address"]["location_data"]);
				$address->set_prop("street", $arr["request"]["contact_entry_address"]["street"]);
				$address->set_prop("house", $arr["request"]["contact_entry_address"]["house"]);
				$address->set_prop("apartment", $arr["request"]["contact_entry_address"]["apartment"]);
				$address->set_prop("postal_code", $arr["request"]["contact_entry_address"]["postal_code"]);
				$address->set_prop("po_box", $arr["request"]["contact_entry_address"]["po_box"]);
				$address->save();

				if ($new_address)
				{
					$o->connect(array("to" => $address, "reltype" => "RELTYPE_ADDRESS_ALT"));
				}
			}
		}
		else
		{
			try
			{
				$owner_oid = $this_o->prop("owner")->id();

				// create new contact object
				$o = obj(null, array(), CL_CRM_PERSON);
				$o->set_parent($owner_oid);
				$o->set_prop("firstname", ucfirst($arr["prop"]["value"]["firstname"]));
				$o->set_prop("lastname", ucfirst($arr["prop"]["value"]["lastname"]));
				$o->set_prop("gender", $arr["prop"]["value"]["gender"]);
				$o->save();
				$o->set_prop("fake_phone", $arr["prop"]["value"]["fake_phone"]);
				$o->set_prop("fake_email", $arr["prop"]["value"]["fake_email"]);
				$o->save();

				// address
				if (is_oid($arr["request"]["contact_entry_address"]["location_data"]))
				{
					$address = obj(null, array(), CL_ADDRESS);
					$address->set_prop("country", $arr["request"]["contact_entry_address"]["country"]);
					$address->set_location($arr["request"]["contact_entry_address"]["location_data"]);
					$address->set_prop("street", $arr["request"]["contact_entry_address"]["street"]);
					$address->set_prop("house", $arr["request"]["contact_entry_address"]["house"]);
					$address->set_prop("apartment", $arr["request"]["contact_entry_address"]["apartment"]);
					$address->set_prop("postal_code", $arr["request"]["contact_entry_address"]["postal_code"]);
					$address->set_prop("po_box", $arr["request"]["contact_entry_address"]["po_box"]);
					$address->save();
					$o->connect(array("to" => $address, "reltype" => "RELTYPE_ADDRESS_ALT"));
				}

				$owner = $arr["obj_inst"]->prop("owner");
				$customer_relation = $o->get_customer_relation($owner, true);

				if (!empty($arr["request"]["contact_entry_salesman"]))
				{ // set salesman
					$salesman = obj($arr["request"]["contact_entry_salesman"], array(), CL_CRM_PERSON);
					$customer_relation->set_prop("salesman", $salesman->id());
					$customer_relation->set_prop("sales_state", crm_company_customer_data_obj::SALESSTATE_LEAD);
					$customer_relation->connect(array("to" => $salesman, "reltype" => "RELTYPE_SALESMAN"));
				}

				if (!empty($arr["request"]["contact_entry_lead_source_oid"]))
				{ // set lead source
					$lead_source = new object($arr["request"]["contact_entry_lead_source_oid"]);

					if (!$lead_source->is_a(CL_CRM_COMPANY) and !$lead_source->is_a(CL_CRM_PERSON))
					{
						throw new awex_obj_class("Invalid class. Lead source must be a company or a person");
					}

					$customer_relation->set_prop("sales_lead_source", $lead_source->id());
					$customer_relation->connect(array("to" => $lead_source, "reltype" => "RELTYPE_SALES_LEAD_SOURCE"));
				}
				elseif (!empty($arr["request"]["contact_entry_lead_source"]))
				{
					$name = explode(" ", $arr["request"]["contact_entry_lead_source"]);
					foreach ($name as $key => $name_part)
					{
						$name[$key] = ucfirst($name_part);
					}

					$lastname = array_pop($name);
					$firstname = count($name) > 1 ? implode("-", $name) : array_pop($name);
					$lead_source = obj(null, array(), CL_CRM_PERSON);
					$lead_source->set_parent($owner_oid);
					$lead_source->set_prop("firstname", $firstname);
					$lead_source->set_prop("lastname", $lastname);
					$lead_source->save();
					$customer_relation->set_prop("sales_lead_source", $lead_source->id());
					$customer_relation->set_prop("sales_state", crm_company_customer_data_obj::SALESSTATE_LEAD);
					$customer_relation->connect(array("to" => $lead_source, "reltype" => "RELTYPE_SALES_LEAD_SOURCE"));
				}

				$this_o->add_contact($customer_relation); // also saves customer relation
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

	function _get_last_entries_list(&$arr)
	{
		$table = $arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Kliendi nimi")
		));
		$owner = $arr["obj_inst"]->prop("owner");
		$clid = $arr["request"]["group"] === "data_entry_contact_co" ? CL_CRM_COMPANY : CL_CRM_PERSON;
		$list = new object_list(array(
			"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
			"seller" => $owner->id(),
			"buyer.class_id" => $clid,
			"createdby" => aw_global_get("uid"),
			"created" => new obj_predicate_compare(OBJ_COMP_GREATER, mktime(0, 0, 0)),
			"site_id" => array(),
			"lang_id" => array(),
			new obj_predicate_sort(array("created" => "desc")),
			new obj_predicate_limit(5)
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
				$url->set_arg("contact_list_load_contact", $o->prop("buyer"));
				$table->define_data(array(
					"name" => html::href(array("caption" => $o->prop("buyer.name"), "url" => $url->get()))
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
		$arr["prop"]["value"] = "view" === automatweb::$request->action() ? $arr["prop"]["value"] : $arr["prop"]["value"]->id();
		return PROP_OK;
	}

	function _set_owner($arr)
	{
		$arr["prop"]["value"] = new object($arr["prop"]["value"]);
		return PROP_OK;
	}
}

?>
