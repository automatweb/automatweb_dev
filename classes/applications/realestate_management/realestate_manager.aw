<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_manager.aw,v 1.6 2006/01/08 19:01:32 voldemar Exp $
// realestate_manager.aw - Kinnisvarahalduse keskkond
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_CRM_PROFESSION, on_connect_to_profession)

@classinfo syslog_type=ST_REALESTATE_MANAGER relationmgr=yes prop_cb=1

@groupinfo grp_settings caption="Seaded" parent=general
@groupinfo grp_users_tree caption="Kasutajad" parent=general
@groupinfo grp_users_mgr caption="Rollid" parent=general
@groupinfo grp_realestate_properties caption="Objektid"
	@groupinfo grp_realestate_properties_all caption="Kõik objektid" parent=grp_realestate_properties
	@groupinfo grp_realestate_properties_houses caption="Majad" parent=grp_realestate_properties
	@groupinfo grp_realestate_properties_rowhouses caption="Ridaelamud" parent=grp_realestate_properties
	@groupinfo grp_realestate_properties_cottages caption="Suvilad" parent=grp_realestate_properties
	@groupinfo grp_realestate_properties_houseparts caption="Majaosad" parent=grp_realestate_properties
	@groupinfo grp_realestate_properties_apartments caption="Korterid" parent=grp_realestate_properties
	@groupinfo grp_realestate_properties_commercial_properties caption="Äripinnad" parent=grp_realestate_properties
	@groupinfo grp_realestate_properties_garages caption="Garaazid" parent=grp_realestate_properties
	@groupinfo grp_realestate_properties_land_estates caption="Maatükid" parent=grp_realestate_properties
	@groupinfo grp_realestate_properties_search caption="Otsing" parent=grp_realestate_properties
@groupinfo grp_clients caption="Kliendid"
	@groupinfo grp_client_list caption="Klientide nimekiri" parent=grp_clients
	@groupinfo grp_client_selections caption="Valimid" parent=grp_clients
@groupinfo grp_clients_mailer caption="Klientidele e-posti saatmine"


@default table=objects
	@property name type=textbox group=grp_settings
	@caption Nimi


@default field=meta
@default method=serialize


@default group=grp_realestate_properties
	@property property_toolbar type=toolbar store=no no_caption=1 group=grp_realestate_properties_houses,grp_realestate_properties_rowhouses,grp_realestate_properties_cottages,grp_realestate_properties_houseparts,grp_realestate_properties_apartments,grp_realestate_properties_commercial_properties,grp_realestate_properties_garages,grp_realestate_properties_land_estates,grp_realestate_properties_all,grp_realestate_properties_search

	@property properties_list type=table store=no no_caption=1 group=grp_realestate_properties_houses,grp_realestate_properties_rowhouses,grp_realestate_properties_cottages,grp_realestate_properties_houseparts,grp_realestate_properties_apartments,grp_realestate_properties_commercial_properties,grp_realestate_properties_garages,grp_realestate_properties_land_estates,grp_realestate_properties_all,grp_realestate_properties_search

@default group=grp_realestate_properties_houses,grp_realestate_properties_rowhouses,grp_realestate_properties_cottages,grp_realestate_properties_houseparts,grp_realestate_properties_apartments,grp_realestate_properties_commercial_properties,grp_realestate_properties_garages,grp_realestate_properties_land_estates,grp_realestate_properties_all

	@property title2 type=text store=no subtitle=1
	@caption Näita ainult objekte millel on:
		@property proplist_filter_modifiedafter type=date_select store=no
		@caption muutmisaeg peale

		@property proplist_filter_modifiedbefore type=date_select store=no
		@caption muutmisaeg enne

		@property proplist_filter_createdafter type=date_select store=no
		@caption lisamisaeg peale

		@property proplist_filter_createdbefore type=date_select store=no
		@caption lisamisaeg enne

		@property proplist_filter_pricemin type=textbox store=no
		@caption hind üle

		@property proplist_filter_pricemax type=textbox store=no
		@caption hind alla

		@property proplist_filter_legal_status type=chooser multiple=1 store=no
		@caption omandivorm

		@property proplist_filter_quality_class type=chooser multiple=1 store=no
		@caption kvaliteediklass

		@property proplist_filter_closedafter type=date_select store=no
		@caption tehingu sõlmimise aeg peale

		@property proplist_filter_closedbefore type=date_select store=no
		@caption tehingu sõlmimise aeg enne

		@property proplist_filter_transaction_closed type=checkbox ch_value=1 store=no
		@caption tehing sõlmitud

		@property button1 type=submit store=no
		@caption Otsi

@default group=grp_realestate_properties_search
	@property property_search type=releditor reltype=RELTYPE_PROPERTY_SEARCH rel_id=first editonly=1 props=search_class_id,search_transaction_type,search_transaction_price_min,search_transaction_price_max,search_total_floor_area_min,search_total_floor_area_max,search_number_of_rooms,searchparam_address1,searchparam_address2,searchparam_address3,searchparam_fromdate,search_usage_purpose,search_condition,search_is_middle_floor,searchparam_onlywithpictures
	@caption Otsing

	@property button2 type=submit store=no
	@caption Otsi


@default group=grp_users_tree
	@property box type=text no_caption=1 store=no group=grp_users_tree,grp_users_mgr
	@layout vsplitbox type=hbox group=grp_users_tree,grp_users_mgr
	@property user_list_toolbar type=toolbar store=no no_caption=1
	@property user_list_tree type=treeview store=no no_caption=1 parent=vsplitbox
	@property user_list type=table store=no no_caption=1 parent=vsplitbox


@default group=grp_users_mgr
	@layout lbox type=vbox parent=vsplitbox
	@property user_mgr_tree type=treeview store=no no_caption=1 parent=lbox
	@layout tbox type=vbox parent=vsplitbox

	@property title1 type=text store=no subtitle=1 parent=tbox
	@caption Ametile antud õigused objektitüüpide järgi

	@property user_mgr_division_rights type=table store=no no_caption=1 parent=tbox

	@property title12 type=text store=no subtitle=1 parent=tbox
	@caption &Otilde;igused objekti aadressi järgi

	@property rights_administrative_structure type=relpicker reltype=RELTYPE_ADMINISTRATIVE_STRUCTURE clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE automatic=1 parent=tbox store=no
	@comment Haldusjaotus, mille kohta õigusi määratakse.
	@caption Haldusjaotus

	@property rights_admindivision type=select parent=tbox store=no
	@comment Aadressitase, mille kohta õigusi määratakse. Aadressitaseme muutmisel jäävad teiste aadressitasemete seaded samaks! Et neid muuta tuleb valida uuesti sama aadressitase.
	@caption Aadressitase

	@property rights_admindivision_current type=hidden store=no

	@property rights_adminunit type=select parent=tbox multiple=1 size=8 store=no
	@caption Lubatud piirkonnad



@default group=grp_client_list
	@property clients_toolbar type=toolbar store=no no_caption=1
	@property clients_list type=table store=no no_caption=1

	@property title4 type=text store=no subtitle=1
	@caption Näita ainult kliente kellel:
		@property clientlist_filter_name type=textbox store=no
		@caption nimi

		@property clientlist_filter_address type=textbox store=no
		@caption aadress

		@property clientlist_filter_appreciationafter type=date_select store=no
		@caption tänukirja kuupäev peale

		@property clientlist_filter_appreciationbefore type=date_select store=no
		@caption tänukirja kuupäev enne

		@property clientlist_filter_appreciationtype type=chooser multiple=1 store=no
		@caption tänukirja tüüp

		@property clientlist_filter_agent type=select multiple=1 store=no size=3
		@caption maakler

		@property button3 type=submit store=no
		@caption Otsi

		@property realestate_client_selection_name type=hidden store=no no_caption=1

@default group=grp_client_selections
	@property client_selections_toolbar type=toolbar store=no no_caption=1
	@property client_selections_list type=table store=no no_caption=1


@default group=grp_clients_mailer
	// @property asdfasdf type=textbox
	// @caption From



@default group=grp_settings
	@property almightyuser type=textbox
	@caption "Kõik lubatud" kasutaja uid

	@property houses_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption Majade kaust

	@property rowhouses_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption Ridaelamute kaust

	@property cottages_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption Suvilate kaust

	@property houseparts_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption Majaosade kaust

	@property apartments_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption Korterite kaust

	@property commercial_properties_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption &Auml;ripindade kaust

	@property garages_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption Garaazide kaust

	@property land_estates_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption Maade kaust

	@property clients_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption Klientide kaust

	@property projects_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	@caption Kinnisvaraprojektide kaust

	@property realestate_search type=relpicker reltype=RELTYPE_PROPERTY_SEARCH clid=CL_REALESTATE_SEARCH automatic=1
	@caption Kinnisvaraotsingu objekt

	@property default_date_format type=textbox default=jmYHi
	@caption Kuupäevaformaat

	@property properties_list_perpage type=textbox default=50
	@comment Objektide arv lehel kinnisvarahalduskeskkonna objektide vaates
	@caption Objekte lehel

	@property map_server_url type=textbox size=100
	@comment Võimalikud muutujad urlis: address_parsed - objekti aadress, mis antakse argumendina kaasa, save_url - url millele pärigut tehes tagastatakse kaardi andmed argumentidena
	@caption Kaardiserveri url

	@property realestatemgr_cfgmgr type=relpicker reltype=RELTYPE_REALESTATEMGR_CFGMGR clid=CL_CFGMANAGER
	@caption Keskkonna seadetehaldur

	// @property print_properties_house type=relpicker reltype=RELTYPE_PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT
	// @comment On/pole tüüpi elemendid kuvatakse prindivaates "lisainfo" all, ülejäänud kahes tulbas enne lisaandmeid. Seejuures kasutatakse ekspordi objekti vaates "Koosta tabel" leiduvat "Tulba pealkiri" väärtust elemendi suffiksi kuvamiseks (näiteks üldpinna puhul mõõtühik m2).
	// @caption Prinditavad elemendid (Maja)

	@property available_variables_names type=text store=no
	@caption Template'ites kasutada olevad muutujad

	@property title5 type=text store=no subtitle=1
	@caption Aadressid
		@property administrative_structure type=relpicker reltype=RELTYPE_ADMINISTRATIVE_STRUCTURE clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE automatic=1
		@comment Riigi haldusjaotus, milles süsteemis hallatavad kinnisvaraobjektid asuvad
		@caption Haldusjaotus

		@property address_equivalent_1 type=relpicker reltype=RELTYPE_ADDRESS_EQUIVALENT_1 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION editonly=1
		@comment Haldusjaotis aadressisüsteemis, mis vastab maakonnale
		@caption Maakond haldusjaotuses

		@property address_equivalent_2 type=relpicker reltype=RELTYPE_ADDRESS_EQUIVALENT_2 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION editonly=1
		@caption Linn haldusjaotuses

		@property address_equivalent_3 type=relpicker reltype=RELTYPE_ADDRESS_EQUIVALENT_3 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION editonly=1
		@caption Linnaosa haldusjaotuses

		@property address_equivalent_4 type=relpicker reltype=RELTYPE_ADDRESS_EQUIVALENT_4 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION editonly=1
		@caption Vald haldusjaotuses

		@property address_equivalent_5 type=relpicker reltype=RELTYPE_ADDRESS_EQUIVALENT_5 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION editonly=1
		@caption Asula haldusjaotuses





// --------------- RELATION TYPES ---------------------

@reltype REALESTATEMGR_FOLDER value=1 clid=CL_MENU
@caption Kaust

@reltype REALESTATEMGR_USER_GROUP value=2 clid=CL_GROUP
@caption Kasutajagrupp

@reltype ADMINISTRATIVE_STRUCTURE value=3 clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE
@caption Haldusjaotuse struktuur

@reltype REALESTATEMGR_VARMGR value=4 clid=CL_METAMGR
@caption Klassifikaatorite haldur

@reltype REALESTATEMGR_CFGMGR value=5 clid=CL_CFGMANAGER
@caption Keskkonna seadete haldur

@reltype REALESTATEMGR_OWNER clid=CL_CRM_COMPANY value=6
@caption Keskkonna omanik (Organisatsioon)

@reltype REALESTATEMGR_USER clid=CL_CRM_COMPANY value=7
@caption Keskkonna kasutaja (Organisatsioon)

@reltype PROPERTY_SEARCH clid=CL_REALESTATE_SEARCH value=8
@caption Kinnisvaraobjektide otsing

@reltype PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT value=11
@caption Prinditavate elementide valik (objektieksport)

@reltype CLIENT_SELECTION clid=CL_REALESTATE_CLIENT_SELECTION value=12
@caption Klientide valim

@reltype RELTYPE_ADDRESS_EQUIVALENT_1 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION value=13
@caption Haldusjaotuse vaste 1

@reltype RELTYPE_ADDRESS_EQUIVALENT_2 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION value=14
@caption Haldusjaotuse vaste 2

@reltype RELTYPE_ADDRESS_EQUIVALENT_3 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION value=15
@caption Haldusjaotuse vaste 3

@reltype RELTYPE_ADDRESS_EQUIVALENT_4 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION value=16
@caption Haldusjaotuse vaste 4

@reltype RELTYPE_ADDRESS_EQUIVALENT_5 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION value=17
@caption Haldusjaotuse vaste 5


*/

class realestate_manager extends class_base
{
	var $realestate_search = 0;
	var $default_date_format = "j/m/Y H.i";
	var $cl_users;
	var $cl_classificator;
	var $usr_mgr_profession_group;

/* CLASSBASE METHODS */
	function realestate_manager()
	{
		$this->init(array(
			"tpldir" => "applications/realestate_management/realestate_manager",
			"clid" => CL_REALESTATE_MANAGER
		));

		$this->usrmgr_property_type_data = array (
			"can_houses" => t("Majad"),
			"can_rowhouses" => t("Ridaelamud"),
			"can_cottages" => t("Suvilad"),
			"can_houseparts" => t("Majaosad"),
			"can_apartments" => t("Korterid"),
			"can_commercial_properties" => t("Äripinnad"),
			"can_garages" => t("Garaazid"),
			"can_land_estates" => t("Maatükid"),
		);
	}

	function callback_on_load ($arr)
	{
		if (is_oid ($arr["request"]["id"]))
		{
			$this_object = obj ($arr["request"]["id"]);

			if ($this_object->prop ("default_date_format"))
			{
				$this->default_date_format = $this_object->prop ("default_date_format");
			}

			if (is_oid ($this_object->prop ("realestatemgr_cfgmgr")) and $this->can("view", $this_object->prop ("realestatemgr_cfgmgr")))
			{
				$this->cfgmanager = $this_object->prop ("realestatemgr_cfgmgr");
			}

			$this->administrative_structure = $this_object->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");

			if (!is_object ($this->administrative_structure))
			{
				echo t("Haldusjaotus määramata või puudub juurdepääs!");
			}
		}

		aw_session_set ("realsestate_usr_mgr_cat", $arr["request"]["cat"]);
		aw_session_set ("realsestate_usr_mgr_unit", $arr["request"]["unit"]);
		aw_session_set ("realsestate_usr_mgr_company", $arr["request"]["company"]);
		$this->cl_users = get_instance("users");
		$this->cl_classificator = get_instance(CL_CLASSIFICATOR);

		if (
			// ($arr["request"]["action"] == "submit") and
			($arr["request"]["group"] == "grp_users_mgr") and
			is_oid (aw_global_get ("realsestate_usr_mgr_cat"))
		)
		{
			$profession = obj (aw_global_get ("realsestate_usr_mgr_cat"));
			$this->usr_mgr_profession_group = $profession->get_first_obj_by_reltype ("RELTYPE_GROUP");

			if (!is_object ($this->usr_mgr_profession_group))
			{
				echo t("Kasutajagrupp ameti jaoks määramata.");
			}
		}

		if (($arr["request"]["group"] == "grp_realestate_properties_search") and is_array ($arr["request"]["realestate_search"]))
		{
			$this->realestate_search = $arr["request"]["realestate_search"];
		}

		if (is_oid ($arr["request"]["re_client_selection"]))
		{ ### load saved client selection
			$this->re_client_selection =  obj ($arr["request"]["re_client_selection"]);
		}
	}

	function callback_mod_tab($arr)
	{
		$property_groups = array (
			"grp_realestate_properties_houses",
			"grp_realestate_properties_rowhouses",
			"grp_realestate_properties_houseparts",
			"grp_realestate_properties_cottages",
			"grp_realestate_properties_apartments",
			"grp_realestate_properties_commercial_properties",
			"grp_realestate_properties_garages",
			"grp_realestate_properties_land_estates",
		);

		if (in_array ($arr["id"], $property_groups))
		{
			$this_object =& $arr["obj_inst"];
			$groupname = str_replace ("grp_realestate_properties_", "", $arr["id"]);

			if (!$this->can ("view", $this_object->prop ($groupname . "_folder")))
			{
				return false;
			}
		}

		if ( (!$this->send_client_mail and $arr["id"] == "grp_clients_mailer") or ($this->send_client_mail and $arr["id"] != "grp_clients_mailer") )
		{
			return false;
		}

		return true;
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		if ($arr["new"] and ($prop["name"] != "name"))
		{
			return PROP_IGNORE;
		}

		switch ($prop["group"])
		{
			case "grp_realestate_properties_houses":
			case "grp_realestate_properties_rowhouses":
			case "grp_realestate_properties_houseparts":
			case "grp_realestate_properties_cottages":
			case "grp_realestate_properties_commercial_properties":
			case "grp_realestate_properties_land_estates":
			case "grp_realestate_properties_garages":
			case "grp_realestate_properties_apartments":
				$groupname = str_replace ("grp_realestate_properties_", "", $prop["group"]);

				if (!$this->can ("view", $this_object->prop ($groupname . "_folder")))
				{
					return PROP_IGNORE;
				}
				break;
		}

		switch($prop["name"])
		{
			### mailer tab
			case "mail_from":
			case "mail_subject":
			case "mail_body":
			case "mail_body":

			case "available_variables_names":
				$prop["value"] = t("
					Kõik klassi propertyd kujul <i>property_name</i> ning nende nimed kujul <i>property_name_caption</i>.  <br />
					Ostja, müüja ning maaklerite andmed kujul  <i>buyer_*</i>, <i>seller_*</i>, <i>agent_*</i> ja <i>agent2_*</i> (name, phone, email, picture_url).  <br />
					Pildid kujul <i>picture</i><b>n</b><i>_url</i> ning <i>picture</i><b>n</b><i>_city24_id</i> (n on 1 .. piltide arv). <br />
					Piltide arv: picture_count<br />
					Lisaks: <br />
					link_return_url -  tagasilink, kui on määratud <br />
					link_open - objekti veebis avamise url <br />
					class_name - kasutajale arusaadav klassi nimi <br />
				");
				break;

			case "address_equivalent_1":
			case "address_equivalent_2":
			case "address_equivalent_3":
			case "address_equivalent_4":
			case "address_equivalent_5":
				if (is_object ($this->administrative_structure))
				{
					$divisions = new object_list ($this->administrative_structure->connections_from (array (
						"type" => "RELTYPE_ADMINISTRATIVE_DIVISION",
						"class_id" => CL_COUNTRY_ADMINISTRATIVE_DIVISION,
					)));
					$prop["options"] = $divisions->names ();
				}
				else
				{
					$retval = PROP_IGNORE;
				}
				break;

			### clients tab
			case "client_selections_list":
				$this->_client_selections_list ($arr);
				break;

			case "client_selections_toolbar":
			case "clients_toolbar":
				$this->_clients_toolbar ($arr);
				break;

			case "clients_list":
				$this->_clients_list ($arr);
				break;

			case "clientlist_filter_appreciationtype":
				$prop_args = array (
					"clid" => CL_REALESTATE_PROPERTY,
					"name" => "appreciation_note_type",
				);
				list ($options, $name, $use_type) = $this->cl_classificator->get_choices($prop_args);
				$prop["options"] = $options->names();
				$prop["value"] = (is_object ($this->re_client_selection) and $this->re_client_selection->meta ("realestate_" . $prop["name"])) ? $this->re_client_selection->meta ("realestate_" . $prop["name"]) : (aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : "");
				break;

			case "clientlist_filter_agent":
				$agents_filter = array ();
				$connections = $this_object->connections_from(array(
					"type" => "RELTYPE_REALESTATEMGR_USER",
					"class_id" => CL_CRM_COMPANY,
				));

				foreach ($connections as $connection)
				{
					$company = $connection->to ();
					$employees = new object_list($company->connections_from(array(
						"type" => "RELTYPE_WORKERS",
						"class_id" => CL_CRM_PERSON,
					)));
					$agents_filter = $agents_filter + $employees->names ();
				}

				$prop["options"] = $agents_filter;
				$prop["value"] = (is_object ($this->re_client_selection) and $this->re_client_selection->meta ("realestate_" . $prop["name"])) ? $this->re_client_selection->meta ("realestate_" . $prop["name"]) : (aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : "");
				break;

			### properties tab
			case "property_search":
				break;

			case "property_toolbar":
				$this->_property_toolbar ($arr);
				break;

			case "properties_list":
				$this->_properties_list ($arr);
				break;

			case "clientlist_filter_appreciationafter":
				$prop["value"] = (is_object ($this->re_client_selection) and $this->re_client_selection->meta ("realestate_" . $prop["name"])) ? $this->re_client_selection->meta ("realestate_" . $prop["name"]) : (aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : -1);
				break;

			case "proplist_filter_modifiedafter":
			case "proplist_filter_createdafter":
			case "proplist_filter_closedafter":
				$prop["value"] = aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : -1;
				break;

			case "clientlist_filter_appreciationbefore":
				$prop["value"] = (is_object ($this->re_client_selection) and $this->re_client_selection->meta ("realestate_" . $prop["name"])) ? $this->re_client_selection->meta ("realestate_" . $prop["name"]) : (aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : (time () + 86400));
				break;

			case "proplist_filter_modifiedbefore":
			case "proplist_filter_createdbefore":
			case "proplist_filter_closedbefore":
				$prop["value"] = aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : (time () + 86400);
				break;

			case "proplist_filter_pricemin":
			case "proplist_filter_pricemax":
				$prop["value"] = aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : "";
				break;

			case "clientlist_filter_name":
			case "clientlist_filter_address":
				$prop["value"] = (is_object ($this->re_client_selection) and $this->re_client_selection->meta ("realestate_" . $prop["name"])) ? $this->re_client_selection->meta ("realestate_" . $prop["name"]) : (aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : "");
				break;

			case "proplist_filter_legal_status":
				$prop_args = array (
					"clid" => CL_REALESTATE_APARTMENT,
					"name" => "legal_status",
				);
				list ($options, $name, $use_type) = $this->cl_classificator->get_choices($prop_args);
				$prop["options"] = $options->names();
				$prop["value"] = aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : "";
				break;

			case "proplist_filter_quality_class":
				$prop_args = array (
					"clid" => CL_REALESTATE_APARTMENT,
					"name" => "quality_class",
				);
				list ($options, $name, $use_type) = $this->cl_classificator->get_choices($prop_args);
				$prop["options"] = $options->names();
				$prop["value"] = aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : "";
				break;

			case "proplist_filter_transaction_closed":
				$prop["value"] = (int) aw_global_get ("realestate_" . $prop["name"]);
				break;

			### users tab
			case "user_list_toolbar":
				$this->_user_list_toolbar($arr);
				break;

			case "user_list_tree":
				$this->_user_list_tree($arr);
				break;

			case "user_list":
				$this->_user_list($arr);
				break;

			case "user_mgr_tree":
				$this->_user_mgr_tree($arr);
				break;

			case "user_mgr_division_rights":
				$retval = $this->_user_mgr_division_rights ($arr);
				break;

			case "title1":
				if (!is_oid ($arr["request"]["cat"]))
				{
					$prop["caption"] = t("Vali amet, mille õigusi muuta");
				}
				break;

			case "rights_administrative_structure":
				### proceed only if a profession is selected
				if (!is_oid ($arr["request"]["cat"]))
				{
					return PROP_IGNORE;
				}

				if (!is_object ($this->usr_mgr_profession_group))
				{
					$prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					return PROP_ERROR;
					// return PROP_IGNORE;
				}

				$prop["value"] = $this->administrative_structure->id ();
				break;

			case "rights_admindivision_current":
			case "rights_admindivision":
				### proceed only if a profession is selected
				if (!is_oid ($arr["request"]["cat"]))
				{
					return PROP_IGNORE;
				}

				if (!is_object ($this->usr_mgr_profession_group))
				{
					$prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					return PROP_ERROR;
					// return PROP_IGNORE;
				}

				if ($retval == PROP_OK)
				{
					$divisions =& $this->administrative_structure->prop ("structure_array");

					### get admin divisions for selected administrative_structure
					foreach ($divisions as $division)
					{
						if ($division->id () == aw_global_get ("realestate_usr_mgr_rights_admindivision"))
						{
							$prop["value"] = $division->id ();
						}

						$options[$division->id ()] = $division->name ();
					}

					### options for division select
					if ($prop["name"] == "rights_admindivision")
					{
						$prop["options"] = $options;
					}

					### get value for hidden division prop
					if (!is_oid ($prop["value"]))
					{
						foreach ($options as $key => $value)
						{
							$prop["value"] = $key;
							break;
						}
					}
				}
				break;

			case "rights_adminunit":
				### proceed only if a profession is selected
				if (!is_oid ($arr["request"]["cat"]))
				{
					return PROP_IGNORE;
				}

				if (!is_object ($this->usr_mgr_profession_group))
				{
					$prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					return PROP_ERROR;
					// return PROP_IGNORE;
				}

				### get selected admin division
				if (!is_oid (aw_global_get ("realestate_usr_mgr_rights_admindivision")))
				{
					return PROP_IGNORE;
				}
				else
				{
					$admin_division = obj (aw_global_get ("realestate_usr_mgr_rights_admindivision"));
				}

				if ($retval == PROP_OK)
				{
					### get current values
					$connections = $this->usr_mgr_profession_group->connections_to ();
					$address_classes = array (
						CL_COUNTRY_ADMINISTRATIVE_UNIT,
						CL_COUNTRY_CITY,
						CL_COUNTRY_CITYDISTRICT,
					);

					foreach ($connections as $connection)
					{
						if (in_array ($connection->prop ("from.class_id"), $address_classes) and ($connection->prop ("reltype") == RELTYPE_ACL))
						{
							if (!is_oid ($connection->prop ("from")))
							{
								continue;
								// $prop["error"] .= t("Ameti kasutajagrupiga seostatud haldusüksus on katkine objekt. ");
								// return PROP_ERROR;
							}

							$gid = $this->cl_users->get_gid_for_oid($this->usr_mgr_profession_group->id ());
							$acl_current_settings = $this->get_acl_for_oid_gid(
								$connection->prop ("from"), $gid
							);

							if (
								$acl_current_settings["can_add"] and
								$acl_current_settings["can_edit"] and
								$acl_current_settings["can_admin"] and
								$acl_current_settings["can_delete"] and
								$acl_current_settings["can_view"]
							)
							{
								$prop["value"][$connection->prop ("from")] = $connection->prop ("from");
							}
						}
					}

					### get options
					$list = new object_list (array (
						"class_id" => $admin_division->prop ("type"),
						"subclass" => $admin_division->id (),
					));
					$prop["options"] = $list->names ();
				}
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch($prop["name"])
		{
			### properties tab
			case "properties_list";
				$this->save_realestate_properties ($arr);
				break;

			### filters
			#### date type filters
			case "clientlist_filter_appreciationafter":
			case "clientlist_filter_appreciationbefore":
			case "proplist_filter_modifiedafter":
			case "proplist_filter_modifiedbefore":
			case "proplist_filter_createdbefore":
			case "proplist_filter_createdafter":
			case "proplist_filter_closedafter":
			case "proplist_filter_closedbefore":
				$value = mktime (0, 0, 0, $prop["value"]["month"], $prop["value"]["day"], $prop["value"]["year"]);

				if ($value > 0)
				{
					aw_session_set ("realestate_" . $prop["name"], $value);
				}
				else
				{
					aw_session_del ("realestate_" . $prop["name"]);
				}
				break;

			#### checkbox type filters
			case "proplist_filter_transaction_closed":
				if ($prop["value"])
				{
					aw_session_set ("realestate_" . $prop["name"], 1);
				}
				else
				{
					aw_session_del ("realestate_" . $prop["name"]);
				}
				break;

			#### textbox type filters
			case "clientlist_filter_name":
			case "clientlist_filter_address":
			case "proplist_filter_pricemin":
			case "proplist_filter_pricemax":
				if (strlen ($prop["value"]))
				{
					aw_session_set ("realestate_" . $prop["name"], $prop["value"]);
				}
				else
				{
					aw_session_del ("realestate_" . $prop["name"]);
				}
				break;

			#### select type filters
			case "clientlist_filter_appreciationtype":
			case "proplist_filter_legal_status":
			case "proplist_filter_quality_class":
				if (is_array ($prop["value"]))
				{
					aw_session_set ("realestate_" . $prop["name"], $prop["value"]);
				}
				else
				{
					aw_session_del ("realestate_" . $prop["name"]);
				}
				break;

			### users tab
			case "rights_admindivision":
			case "rights_administrative_structure":
				aw_session_set ("realestate_usr_mgr_" . $prop["name"], $prop["value"]);
				return PROP_IGNORE;

			case "rights_adminunit":
				if (!is_object ($this->usr_mgr_profession_group))
				{
					$prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					return PROP_ERROR;
					// return PROP_IGNORE;
				}

				if ($retval == PROP_OK)
				{
					### get current values and revoke rights for those not found in submitted data, mark those already being had rights to
					$connections = $this->usr_mgr_profession_group->connections_to ();
					$address_classes = array (
						CL_COUNTRY_ADMINISTRATIVE_UNIT,
						CL_COUNTRY_CITY,
						CL_COUNTRY_CITYDISTRICT,
					);
					$unchanged_units = array ();

					foreach ($connections as $connection)
					{
						if (in_array ($connection->prop ("from.class_id"), $address_classes) and ($connection->prop ("reltype") == RELTYPE_ACL))
						{
							if (!is_oid ($connection->prop ("from")))
							{
								continue;
								// $prop["error"] .= t("Ameti kasutajagrupiga seostatud haldusüksus on katkine objekt. ");
								// return PROP_ERROR;
							}

							if (!$this->can ("admin", $connection->prop ("from")))
							{
								$prop["error"] .= sprintf (t("Kasutajal puudub luba seostatud haldusüksuse õigusi määrata. "));
								$retval = PROP_ERROR;
								continue;
							}

							$connected_unit = obj ($connection->prop ("from"));
							$gid = $this->cl_users->get_gid_for_oid($this->usr_mgr_profession_group->id ());
							$acl_current_settings = $this->get_acl_for_oid_gid(
								$connected_unit->id (), $gid
							);

							if ((
								$acl_current_settings["can_add"] or
								$acl_current_settings["can_edit"] or
								$acl_current_settings["can_admin"] or
								$acl_current_settings["can_delete"] or
								$acl_current_settings["can_view"]
								) and ($connected_unit->subclass () == $arr["request"]["rights_admindivision_current"]))
							{
								if (!in_array ($connected_unit->id (), $prop["value"]))
								{
									$connected_unit->acl_set ($this->usr_mgr_profession_group, array(
										"can_add" => 0,
										"can_edit" => 0,
										"can_admin" => 0,
										"can_delete" => 0,
										"can_view" => 0,
									));
								}
								else
								{
									$unchanged_units[] = $connected_unit->id ();
								}
							}
						}
					}

					foreach ($prop["value"] as $chosen_admin_unit_id)
					{
						if (!in_array ($chosen_admin_unit_id, $unchanged_units))
						{ ### grant rights to new admin units chosen
							if (!$this->can ("admin", $chosen_admin_unit_id))
							{
								$prop["error"] .= sprintf (t("Kasutajal puudub luba valitud haldusüksuse õigusi määrata. "));
								$retval = PROP_ERROR;
								continue;
							}

							$admin_unit = obj ($chosen_admin_unit_id);
							$admin_unit->acl_set ($this->usr_mgr_profession_group, array(
								"can_add" => 1,
								"can_edit" => 1,
								"can_admin" => 1,
								"can_delete" => 1,
								"can_view" => 1,
							));
						}
					}
				}
				break;
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_mod_retval ($arr)
	{
		if ( ($arr["args"]["group"] == "grp_users_mgr") and (is_oid (aw_global_get ("realsestate_usr_mgr_cat"))) )
		{
			$arr["args"]["cat"] = aw_global_get ("realsestate_usr_mgr_cat");
		}

		if ( ($arr["args"]["group"] == "grp_users_mgr") and (is_oid (aw_global_get ("realsestate_usr_mgr_unit"))) )
		{
			$arr["args"]["unit"] = aw_global_get ("realsestate_usr_mgr_unit");
		}

		if ( ($arr["args"]["group"] == "grp_users_mgr") and (is_oid (aw_global_get ("realsestate_usr_mgr_company"))) )
		{
			$arr["args"]["company"] = aw_global_get ("realsestate_usr_mgr_company");
		}

		if ($arr["args"]["group"] == "grp_realestate_properties_search")
		{
			$arr["args"]["realestate_search"] = array (
				"realestate_srch" => 1,
				"realestate_search_id" => $arr["request"]["property_search"]["id"],
				"ci" => $arr["request"]["property_search"]["search_class_id"],
				"tt" => $arr["request"]["property_search"]["search_transaction_type"],
				"tpmin" => $arr["request"]["property_search"]["search_transaction_price_min"],
				"tpmax" => $arr["request"]["property_search"]["search_transaction_price_max"],
				"tfamin" => $arr["request"]["property_search"]["search_total_floor_area_min"],
				"tfamax" => $arr["request"]["property_search"]["search_total_floor_area_max"],
				"nor" => $arr["request"]["property_search"]["search_number_of_rooms"],
				"a1" => $arr["request"]["property_search"]["searchparam_address1"],
				"a2" => $arr["request"]["property_search"]["searchparam_address2"],
				"a3" => $arr["request"]["property_search"]["searchparam_address3"],
				"at" => $arr["request"]["property_search"]["search_address_text"],
				"fd" => $arr["request"]["property_search"]["searchparam_fromdate"],
				"up" => $arr["request"]["property_search"]["search_usage_purpose"],
				"c" => $arr["request"]["property_search"]["search_condition"],
				"imf" => $arr["request"]["property_search"]["search_is_middle_floor"],
				"owp" => $arr["request"]["property_search"]["searchparam_onlywithpictures"],
			);
		}
	}

	function callback_pre_save ($arr)
	{
		$this_object = $arr["obj_inst"];

		if (is_object ($this->usr_mgr_profession_group))
		{
			foreach ($this->usrmgr_property_type_data as $property_type => $caption)
			{
				$folder_name = substr ($property_type, 4) . "_folder";

				if (!is_oid ($this_object->prop ($folder_name)))
				{
					echo sprintf (t("Seda tüüpi kinnisvaraobjektide kataloog määramata. "));
					continue;
				}

				if (!$this->can ("admin", $this_object->prop ($folder_name)))
				{
					echo sprintf (t("Kasutajal puudub luba selle kinnisvaraobjekti tüübi õigusi määrata. "));
					continue;
				}

				### connect current profession usergroup to realestateproperties folder with reltype_acl
				$can_add = (int) (boolean) $arr["request"]["re_usermgr_addrights"][$property_type];
				$can_edit = (int) (boolean) $arr["request"]["re_usermgr_editrights"][$property_type];
				// $can_admin = (int) (boolean) $arr["request"][$property . "_admin"];
				$can_admin = 0;
				$can_delete = (int) (boolean) $arr["request"]["re_usermgr_deleterights"][$property_type];
				$can_view = (int) (boolean) $arr["request"]["re_usermgr_viewrights"][$property_type];

				$folder = obj ($this_object->prop ($folder_name));
				$retval = $folder->acl_set ($this->usr_mgr_profession_group, array(
					"can_add" => $can_add,
					"can_edit" => $can_edit,
					"can_admin" => $can_admin,
					"can_delete" => $can_delete,
					"can_view" => $can_view,
				));
				$folder->save ();
			}
		}
	}

/* END CLASSBASE METHODS */

	function _user_list_toolbar($arr)
	{
		if (is_oid ($arr["request"]["unit"]))
		{
			$unit = obj ($arr["request"]["unit"]);
		}
		elseif (is_oid ($arr["request"]["company"]))
		{
			$unit = obj ($arr["request"]["company"]);
		}
		else
		{
			return;
		}

		switch ($unit->class_id ())
		{
			case CL_CRM_SECTION:
				$o =& $this->find_connection_parent_by_class ($unit->id (), CL_CRM_COMPANY);
				break;

			case CL_CRM_COMPANY:
				$o =& $unit;
				break;
		}

		$tmp = $arr["obj_inst"];
		$arr["obj_inst"] = $o;
		// $co = $o->instance();
		// $co->callback_on_load($arr);
		#$co->do_contact_toolbar($arr["prop"]['toolbar'],$arr);
		$people_impl = get_instance("applications/crm/crm_company_people_impl");
		$people_impl->_get_contact_toolbar($arr);

		$tb =& $arr["prop"]["vcl_inst"];
		$tb->remove_button("Kone");
		$tb->remove_button("Kohtumine");
		$tb->remove_button("Toimetus");
		$tb->remove_button("Search");

		$arr["obj_inst"] = $tmp;
	}

	function &find_connection_parent_by_class ($object_id, $parent_class_id)
	{
		while (is_oid ($object_id))
		{
			$object = obj ($object_id);
			$connections = $object->connections_to ();

			foreach ($connections as $connection)
			{
				$object_id = $connection->prop("from");
				$parent = obj ($object_id);

				if ($parent->class_id () == $parent_class_id)
				{
					return $parent;
				}
			}
		}

		return false;
	}

	function _user_list_tree($arr)
	{
		$this_object = $arr["obj_inst"];
		$tree = $arr["prop"]["vcl_inst"];
		$trees = array ();

		foreach($this_object->connections_from(array("type" => "RELTYPE_REALESTATEMGR_USER")) as $c)
		{
			$o = $c->to();

			if ($this->can("view", $o->id()))
			{
				$tree->add_item(0, array(
					"name" => $o->name(),
					"id" => $o->id(),
					"url" => aw_url_change_var("company", $o->id()),
				));

				$treeview = get_instance(CL_TREEVIEW);
				$treeview->init_vcl_property ($arr);
				$arr["prop"]["vcl_inst"] =& $treeview;
				$this->_delegate_co_v($arr, "_get_unit_listing_tree", $o);
				$trees[$o->id ()] = $arr["prop"]["vcl_inst"];
			}
		}

		### merge trees to one
		foreach ($trees as $id => $subtree)
		{
			foreach ($subtree->itemdata as $item)
			{
				### find item parent
				foreach ($subtree->items as $parent => $items)
				{
					foreach ($items as $itemdata)
					{
						if ($itemdata["id"] == $item["id"])
						{
							$item_parent = $parent;
							break;
						}
					}
				}

				$item_parent = $item_parent ? $id . $item_parent : $id;

				###...
				$tree->add_item($item_parent , array(
					"name" => $item["name"],
					"id" => $id . $item["id"],
					"url" => $item["url"],
					"iconurl" => $item["iconurl"],
					"class_id" => $item["class_id"],
				));
			}
		}

		if (is_oid ($arr["request"]["company"]))
		{
			$tree->set_selected_item ($arr["request"]["company"]);
		}

		$arr["prop"]["vcl_inst"] = $tree;
		unset ($tree);
		unset ($trees);
	}

	function _user_list($arr)
	{
		$this_object = $arr["obj_inst"];

		if (is_oid ($arr["request"]["unit"]))
		{
			$unit = obj ($arr["request"]["unit"]);

			switch ($unit->class_id ())
			{
				case CL_CRM_COMPANY:
					break;

				case CL_CRM_SECTION:
					$o =& $this->find_connection_parent_by_class ($unit->id (), CL_CRM_COMPANY, 28);

					if ($this->can("view", $o->id()))
					{
						$this->_delegate_co_v($arr, "_get_human_resources", $o);
					}
					break;
			}
		}
	}

	function _user_mgr_tree($arr)
	{
		$this_object = $arr["obj_inst"];
		$tree = $arr["prop"]["vcl_inst"];
		$trees = array ();

		foreach($this_object->connections_from(array("type" => "RELTYPE_REALESTATEMGR_USER")) as $c)
		{
			$o = $c->to();

			if ($this->can("view", $o->id()))
			{
				$url = aw_url_change_var("company", $o->id());
				$tree->add_item(0, array(
					"name" => $o->name(),
					"id" => $o->id(),
					"url" => $url,
				));

				$treeview = get_instance(CL_TREEVIEW);
				$treeview->init_vcl_property ($arr);
				$arr["prop"]["vcl_inst"] =& $treeview;
				$this->_delegate_co_v($arr, "_get_unit_listing_tree", $o);
				$trees[$o->id ()] = $arr["prop"]["vcl_inst"];
			}
		}

		### merge trees to one
		foreach ($trees as $id => $subtree)
		{
			foreach ($subtree->itemdata as $item)
			{
				### find item parent
				foreach ($subtree->items as $parent => $items)
				{
					foreach ($items as $itemdata)
					{
						if ($itemdata["id"] == $item["id"])
						{
							$item_parent = $parent;
							break;
						}
					}
				}

				$item_parent = $item_parent ? $id . $item_parent : $id;

				###...
				$tree->add_item($item_parent , array(
					"name" => $item["name"],
					"id" => $id . $item["id"],
					"url" => $item["url"],
					"iconurl" => $item["iconurl"],
					"class_id" => $item["class_id"],
				));
			}
		}

		if (is_oid ($arr["request"]["company"]))
		{
			$tree->set_selected_item ($arr["request"]["company"]);
		}

		$arr["prop"]["vcl_inst"] = $tree;
		unset ($tree);
		unset ($trees);
	}

	function _init_user_mgr_division_rights (&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Objekti tüüp"),
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => t("<a href='javascript:selall(\"re_usermgr_viewrights\")'>Vaatamine</a>"),
			// "caption" => t("Vaatamine"),
			"tooltip" => t("Vali kõik/kaota valik"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "add",
			"caption" => t("<a href='javascript:selall(\"re_usermgr_addrights\")'>Lisamine</a>"),
			// "caption" => t("Lisamine"),
			"tooltip" => t("Vali kõik/kaota valik"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "edit",
			"caption" => t("<a href='javascript:selall(\"re_usermgr_editrights\")'>Muutmine</a>"),
			// "caption" => t("Muutmine"),
			"tooltip" => t("Vali kõik/kaota valik"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "delete",
			"caption" => t("<a href='javascript:selall(\"re_usermgr_deleterights\")'>Kustutamine</a>"),
			// "caption" => t("Kustutamine"),
			"tooltip" => t("Vali kõik/kaota valik"),
			"align" => "center"
		));
	}

	function _user_mgr_division_rights ($arr)
	{
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_user_mgr_division_rights ($t);

		if (!is_oid ($arr["request"]["cat"]))
		{
			return PROP_IGNORE;
		}

		if (!is_object ($this->usr_mgr_profession_group))
		{
			$prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
			return PROP_ERROR;
			// return PROP_IGNORE;
		}

		foreach ($this->usrmgr_property_type_data as $property_type => $caption)
		{
			$folder_name = substr ($property_type, 4);

			if (is_oid ($this_object->prop ($folder_name . "_folder")))
			{
				// $folder = obj ($this_object->prop ($folder_name . "_folder"));
				$gid = $this->cl_users->get_gid_for_oid ($this->usr_mgr_profession_group->id ());
				$acl_current_settings = $this->get_acl_for_oid_gid ($this_object->prop ($folder_name . "_folder"), $gid);

				$can_view = (int)(bool) $acl_current_settings["can_view"];
				$can_add = (int)(bool) $acl_current_settings["can_add"];
				$can_edit = (int)(bool) $acl_current_settings["can_edit"];
				// $can_admin = (int)(bool) $acl_current_settings["can_admin"];
				$can_delete = (int)(bool) $acl_current_settings["can_delete"];

				$t->define_data(array(
					"name" => $caption,
					"view" => html::checkbox(array(
						"name" => "re_usermgr_viewrights[" . $property_type . "]",
						"value" => 1,
						"checked" => $can_view,
					)),
					"add" => html::checkbox(array(
						"name" => "re_usermgr_addrights[" . $property_type . "]",
						"value" => 1,
						"checked" => $can_add,
					)),
					"edit" => html::checkbox(array(
						"name" => "re_usermgr_editrights[" . $property_type . "]",
						"value" => 1,
						"checked" => $can_edit,
					)),
					"delete" => html::checkbox(array(
						"name" => "re_usermgr_deleterights[" . $property_type . "]",
						"value" => 1,
						"checked" => $can_delete,
					)),
				));
			}
			else
			{
				$t->define_data(array(
					"name" => sprintf (t("Kataloog määramata kinnisvaraobjektide tüübile %s."), $caption),
				));
			}
		}

		return $retval;
	}

	function _delegate_co_v($arr, $fun, &$o)
	{
		$tmp = $arr["obj_inst"];
		$arr["obj_inst"] = $o;
		// if ($fun == "_get_unit_listing_tree")
		// {
			$co = get_instance("applications/crm/crm_company_people_impl");
		// }
		// else
		// {
			// $co = $o->instance();
			// $co->callback_on_load($arr);
		// }
		$co->$fun($arr);
		$arr["obj_inst"] = $tmp;
		unset ($tmp);
	}

	function _delegate_co($arr, $fun, &$o)
	{
		$arr["return_url"] = urlencode($arr["post_ru"]);
		if ($o)
		{
			$arr["id"] = $o->id();
			$o = $o->instance();
			$o->callback_on_load($arr);
			return $o->$fun($arr);
		}
	}

	function _properties_list ($arr)
	{
		$this_object = $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];
		$table_name = str_replace ("grp_realestate_properties_", "", $arr["request"]["group"]);
		$table->name = ("grp_realestate_properties" != $table_name) ? $table_name : "all";
		$this->_init_properties_list ($arr);

		switch ($table->name)
		{
			case "houses":
				$list = new object_list (array (
					"class_id" => CL_REALESTATE_HOUSE,
					"parent" => array ($this_object->prop ("houses_folder")),
				));
				break;

			case "rowhouses":
				$list = new object_list (array (
					"class_id" => CL_REALESTATE_ROWHOUSE,
					"parent" => array ($this_object->prop ("rowhouses_folder")),
				));
				break;

			case "cottages":
				$list = new object_list (array (
					"class_id" => CL_REALESTATE_COTTAGE,
					"parent" => array ($this_object->prop ("cottages_folder")),
				));
				break;

			case "houseparts":
				$list = new object_list (array (
					"class_id" => CL_REALESTATE_HOUSEPART,
					"parent" => array ($this_object->prop ("houseparts_folder")),
				));
				break;

			case "apartments":
				$list = new object_list (array (
					"class_id" => CL_REALESTATE_APARTMENT,
					"parent" => array ($this_object->prop ("apartments_folder")),
				));
				break;

			case "commercial_properties":
				$list = new object_list (array (
					"class_id" => CL_REALESTATE_COMMERCIAL,
					"parent" => array ($this_object->prop ("commercial_properties_folder")),
				));
				break;

			case "garages":
				$list = new object_list (array (
					"class_id" => CL_REALESTATE_GARAGE,
					"parent" => array ($this_object->prop ("garages_folder")),
				));
				break;

			case "land_estates":
				$list = new object_list (array (
					"class_id" => CL_REALESTATE_LAND,
					"parent" => array ($this_object->prop ("land_estates_folder")),
				));
				break;

			case "search":
				if (is_array ($this->realestate_search))
				{
					$cl_realestate_search = get_instance (CL_REALESTATE_SEARCH);
					$cl_realestate_search->get_options (array ("id" => $this->realestate_search["realestate_search_id"]));
					$search = $cl_realestate_search->get_search_args ($this->realestate_search);
					$args = array (
						"manager" => $this_object,
						"search" => $search,
					);
					$list =& $cl_realestate_search->search ($args);
				}
				else
				{
					$list = array ();
				}
				break;

			case "all":
				$class_list = array (
					CL_REALESTATE_HOUSE,
					CL_REALESTATE_ROWHOUSE,
					CL_REALESTATE_COTTAGE,
					CL_REALESTATE_HOUSEPART,
					CL_REALESTATE_APARTMENT,
					CL_REALESTATE_COMMERCIAL,
					CL_REALESTATE_GARAGE,
					CL_REALESTATE_LAND,
				);
				$parent_list = array (
					$this_object->prop ("houses_folder"),
					$this_object->prop ("rowhouses_folder"),
					$this_object->prop ("cottages_folder"),
					$this_object->prop ("houseparts_folder"),
					$this_object->prop ("apartments_folder"),
					$this_object->prop ("commercial_properties_folder"),
					$this_object->prop ("garages_folder"),
					$this_object->prop ("land_estates_folder"),
				);
				$list = new object_list (array (
					"class_id" => $class_list,
					"parent" => $parent_list,
				));
				break;
		}

		$properties = is_array ($list) ? $list : $list->arr ();
		$return_url = urlencode(aw_global_get('REQUEST_URI'));
		$classes = aw_ini_get("classes");
		$modified_after = aw_global_get ("realestate_proplist_filter_modifiedafter") ? aw_global_get ("realestate_proplist_filter_modifiedafter") : 0;
		$modified_before = aw_global_get ("realestate_proplist_filter_modifiedbefore") ? aw_global_get ("realestate_proplist_filter_modifiedbefore") : time ();
		$created_after = aw_global_get ("realestate_proplist_filter_createdafter") ? aw_global_get ("realestate_proplist_filter_createdafter") : 0;
		$created_before = aw_global_get ("realestate_proplist_filter_createdbefore") ? aw_global_get ("realestate_proplist_filter_createdbefore") : (time () + 86400);
		$closed_after = aw_global_get ("realestate_proplist_filter_closedafter") ? aw_global_get ("realestate_proplist_filter_closedafter") : NULL;
		$closed_before = aw_global_get ("realestate_proplist_filter_closedbefore") ? aw_global_get ("realestate_proplist_filter_closedbefore") : (time () + 86400);
		$pricemin = aw_global_get ("realestate_proplist_filter_pricemin") ? aw_global_get ("realestate_proplist_filter_pricemin") : 0;
		$pricemax = aw_global_get ("realestate_proplist_filter_pricemax") ? aw_global_get ("realestate_proplist_filter_pricemax") : 999999999999999;
		$transaction_closed = (int) aw_global_get ("realestate_proplist_filter_transaction_closed");
		$legal_status = aw_global_get ("realestate_proplist_filter_legal_status");
		$quality_class = aw_global_get ("realestate_proplist_filter_quality_class");
		$first = true;
		$sum_tfa = NULL;
		$sum_tp = NULL;

		$applicable_tables = array (
			"houses",
			"houseparts",
			"cottages",
			"rowhouses",
			"apartments",
		);

		### frequently used human readable strings
		$str_yes = t("Jah");
		$str_no = t("Ei");
		$str_change = t("Muuda");
		$str_confirm_archive = t("Arhiveerida objekt?");
		$str_archive = t("Arhiveeri");
		$str_confirm_delete = t("Kustutada objekt?");
		$str_delete = t("Kustuta");

		### actions menu init
		$actions_menu_script = '<script src="http://voldemar.dev.struktuur.ee/automatweb/js/popup_menu.js" type="text/javascript">
</script>';
		echo $actions_menu_script;
		$actions_menu_icon = $this->cfg["baseurl"] . "/automatweb/images/blue/obj_settings.gif";
		$tpl = "js_popup_menu.tpl";
		$tmp = $this->template_dir;
		$this->template_dir = $this->cfg["basedir"] . "/templates/automatweb/menuedit";
		$this->set_parse_method("eval");
		$this->read_template($tpl);

		foreach ($properties as $property)
		{
			### filter by modification
			if ($property->modified () < $modified_after)
			{
				continue;
			}

			if ($property->modified () > $modified_before)
			{
				continue;
			}

			### filter by creation
			if ($property->created () < $created_after)
			{
				continue;
			}

			if ($property->created () > $created_before)
			{
				continue;
			}

			### filter by trancaction closing
			if (isset ($closed_after) and $property->prop ("transaction_date") < $closed_after)
			{
				continue;
			}

			if ($property->prop ("transaction_date") > $closed_before)
			{
				continue;
			}

			if ($transaction_closed and !$property->prop ("transaction_closed"))
			{
				continue;
			}

			### filter by trancaction price
			if ($property->prop ("transaction_price") < $pricemin)
			{
				continue;
			}

			if ($property->prop ("transaction_price") > $pricemax)
			{
				continue;
			}

			### filter by legal_status
			$applicable_classes = array (
				CL_REALESTATE_HOUSE,
				CL_REALESTATE_ROWHOUSE,
				CL_REALESTATE_COTTAGE,
				CL_REALESTATE_HOUSEPART,
				CL_REALESTATE_APARTMENT,
				CL_REALESTATE_GARAGE,
				CL_REALESTATE_LAND,
			);

			if (is_array ($legal_status) and in_array ($property->class_id (), $applicable_classes) and !in_array ($property->prop ("legal_status"), $legal_status))
			{
				continue;
			}

			### filter by quality_class
			$applicable_classes = array (
				CL_REALESTATE_HOUSE,
				CL_REALESTATE_ROWHOUSE,
				CL_REALESTATE_COTTAGE,
				CL_REALESTATE_HOUSEPART,
				CL_REALESTATE_APARTMENT,
				CL_REALESTATE_COMMERCIAL,
			);

			if (is_array ($quality_class) and in_array ($property->class_id (), $applicable_classes) and !in_array ($property->prop ("quality_class"), $quality_class))
			{
				continue;
			}

			### get owner company and unit
			$owner_company_section_oid = $property->meta ("owner_company_section");

			if (!isset ($this->realestate_company_data[$owner_company_section_oid]["name"]))
			{
				$this->realestate_company_data[$owner_company_section_oid]["name"] = "...";

				if ($this->can ("view", $owner_company_section_oid))
				{
					$company_section = obj ($owner_company_section_oid);
					$parent = $company_section;

					do
					{
						$parent = obj ($parent->parent ());
					}
					while (is_oid ($parent->parent ()) and (CL_CRM_COMPANY != $parent->class_id ()) and (CL_CRM_SECTION == $parent->class_id ()));

					if (is_object ($parent))
					{
						$company = $parent;
						$owner_company_name = $company->name () . "//" . $company_section->name ();
						$this->realestate_company_data[$owner_company_section_oid]["name"] = $owner_company_name;
					}
				}
			}

			### compose agent name
			#### agent1
			$agent1_oid = $property->prop ("realestate_agent1");

			if (!isset ($this->realestate_agent_data[$agent1_oid]["change_link"]))
			{
				if ($this->can ("view", $agent1_oid))
				{
					$agent = obj ($agent1_oid);
					$this->realestate_agent_data[$agent1_oid]["change_link"] = html::get_change_url ($agent->id (), array("return_url" => $return_url, "group" => "grp_main"), $agent->name (), $this->realestate_company_data[$owner_company_section_oid]["name"]);
				}
				else
				{
					$this->realestate_agent_data[$agent1_oid]["change_link"] = "";
				}
			}

			#### agent2
			$agent2_oid = $property->prop ("realestate_agent2");

			if (!isset ($this->realestate_agent_data[$agent2_oid]["change_link"]))
			{
				if ($this->can ("view", $agent2_oid))
				{
					$agent = obj ($agent2_oid);
					$this->realestate_agent_data[$agent2_oid]["change_link"] = html::get_change_url ($agent->id (), array("return_url" => $return_url, "group" => "grp_main"), $agent->name (), $this->realestate_company_data[$owner_company_section_oid]["name"]);
				}
				else
				{
					$this->realestate_agent_data[$agent_oid]["change_link"] = "";
				}
			}

			if (!empty ($this->realestate_agent_data[$agent1_oid]["change_link"]))
			{
				$agent_name = $this->realestate_agent_data[$agent1_oid]["change_link"];

				if (!empty ($this->realestate_agent_data[$agent2_oid]["change_link"]))
				{
					$agent_name .= ", " . $this->realestate_agent_data[$agent2_oid]["change_link"];
				}
			}
			elseif (!empty ($this->realestate_agent_data[$agent2_oid]["change_link"]))
			{
				$agent_name = $this->realestate_agent_data[$agent2_oid]["change_link"];
			}
			else
			{
				$agent_name = "...";
			}

			### compose seller name
			$seller = $property->get_first_obj_by_reltype ("RELTYPE_REALESTATE_SELLER");

			if (is_object ($seller))
			{
				$seller_name = html::get_change_url ($seller->id (), array("return_url" => $return_url, "group" => "grp_main"), $seller->name ());
			}
			else
			{
				$seller_name = "...";
			}

			### get address parts
			$address = $address_1 = $address_2 = $address_3 = $address_4 = "...";
			$address = $property->get_first_obj_by_reltype("RELTYPE_REALESTATE_ADDRESS");

			if (is_object ($address))
			{
				$address_array = $address->prop ("address_array");
				$address_1 = $address_array[$this_object->prop ("address_equivalent_1")];//maakond
				$address_2 = $address_array[$this_object->prop ("address_equivalent_2")];//linn
				$address_4 = $address_array[$this_object->prop ("address_equivalent_3")];//linnaosa
				$address_3 = $address_array[$this_object->prop ("address_equivalent_4")];//vald
				$apartment = $address->prop ("apartment") ? "-" . $address->prop ("apartment") : "";
				$address = $address_array[ADDRESS_STREET_TYPE] . " " . $address->prop ("street_address") . $apartment;
			}

			### actions menu
			$actions_menu = "";

			#### get actions
			$class = $classes[$property->class_id ()]["file"];
			$class = explode ("/", $class);
			$class = array_pop ($class);

			if ($this->can("edit", $property->id ()))
			{
				$url = $this->mk_my_orb ("change", array (
					"id" => $property->id (),
					"return_url" => $return_url,
					"group" => "grp_main",
				), $class);
				$this->vars(array(
					"link" => $url,
					"text" => $str_change,
				));
				$actions_menu .= $this->parse("MENU_ITEM");
			}

			if ($this->can("edit", $property->id ()))
			{
				$url = $this->mk_my_orb ("archive", array (
					"reforb" => 1,
					"property_id" => $property->id (),
					"id" => $arr["request"]["id"],
					"group" => $arr["request"]["group"],
					"subgroup" => $arr["request"]["subgroup"],
				), "realestate_manager", true, true);
				$url = "javascript:if(confirm('". $str_confirm_archive ."')){window.location='$url';};";
				$this->vars(array(
					"link" => $url,
					"text" => $str_archive,
				));
				$actions_menu .= $this->parse("MENU_ITEM");
			}

			if ($this->can("delete", $property->id ()))
			{
				$url = $this->mk_my_orb ("delete", array (
					"reforb" => 1,
					"property_id" => $property->id (),
					"id" => $arr["request"]["id"],
					"group" => $arr["request"]["group"],
					"subgroup" => $arr["request"]["subgroup"],
				), "realestate_manager", true, true);
				$url = "javascript:if(confirm('". $str_confirm_delete ."')){window.location='$url';};";
				$this->vars(array(
					"link" => $url,
					"text" => $str_delete,
				));
				$actions_menu .= $this->parse("MENU_ITEM");
			}

			if ($this->can("edit", $property->id ()))
			{
				$is_visible_disabled = false;
			}
			else
			{
				$is_visible_disabled = true;
			}

			#### parse actions menu
			$this->vars(array(
				"menu_id" => "js_pop_" . $property->id (),
				"menu_icon" => $actions_menu_icon,
				"MENU_ITEM" => $actions_menu,
			));
			$actions_menu = $this->parse();


			$class_name = $classes[$property->class_id ()]["name"];
			$data = array (
				"class" => html::get_change_url ($property->id (), array("return_url" => $return_url, "group" => "grp_main"), $class_name),
				"address_1" => $address_1,
				"address_2" => $address_2,
				"address_3" => $address_3,
				"address_4" => $address_4,
				"address" => $address,
				"visible" => html::checkbox (array(
					"name" => "realestatemgr-is_visible[" . $property->id () . "]",
					"checked" => $property->prop ("is_visible"),
					"disabled" => $is_visible_disabled,
				)),
				"archived" => ($property->prop ("is_archived") ? $str_yes : $str_no),
				"state" => $property->status (),
				"oid" => $property->id (),
				"transaction_type" => $property->prop_str ("transaction_type"),
				"created" => $property->created (),
				"modified" => $property->modified (),
				"agent" => $agent_name,
				"seller" => $seller_name,
				// "owner_company" => $this->realestate_company_data[$owner_company_section_oid]["name"],
				"actions" => $actions_menu . html::hidden (array(
					"name" => "realestatemgr_property_id[" . $property->id () . "]",
					"value" => $property->id (),
				)),
			);

			if ($table->name == "apartments")
			{
				$data["floor"] = $property->prop ("floor");
				$data["is_middle_floor"] = ($property->prop ("is_middle_floor")) ? $str_yes : $str_no;
			}

			if (in_array ($table->name, $applicable_tables))
			{
				$data["number_of_rooms"] = $property->prop ("number_of_rooms");
				$data["total_floor_area"] = number_format ($property->prop ("total_floor_area"), 2, ',', ' ');
				$data["transaction_price"] = number_format ($property->prop ("transaction_price"), 2, ',', ' ');
			}

			$row_added = $table->define_data ($data);

			if ($row_added)
			{
				$sum_tfa += $property->prop ("total_floor_area");
				$sum_tp += $property->prop ("transaction_price");
			}
		}

		$this->template_dir = $tmp;

		### statistics
		$prefix = sprintf ("<b>%s</b><br />", t("Kokku:"));
		$stat_data = array ();

		if (isset ($sum_tfa))
		{
			$stat_data["total_floor_area"] = $prefix . number_format ($sum_tfa, 2, ',', ' ');
		}

		if (isset ($sum_tp))
		{
			$stat_data["transaction_price"] = $prefix . number_format ($sum_tp, 2, ',', ' ');
		}

		if (count ($stat_data))
		{
			$table->define_data ($stat_data);
		}
	}

	function _init_properties_list ($arr)
	{
		$table =& $arr["prop"]["vcl_inst"];
		$prop =& $arr["prop"];
		$this_object = $arr["obj_inst"];

		// if ("all" == $table->name or "search" == $table->name)
		// {
			$table->define_field(array(
				"name" => "class",
				"caption" => t("Tüüp"),
				"sortable" => 1,
			));
		// }

		### agents filter
		$agents_filter = array ();
		$connections = $this_object->connections_from(array(
			"type" => "RELTYPE_REALESTATEMGR_USER",
			"class_id" => CL_CRM_COMPANY,
		));

		foreach ($connections as $connection)
		{
			$company = $connection->to ();
			$employees = new object_list($company->connections_from(array(
				"type" => "RELTYPE_WORKERS",
				"class_id" => CL_CRM_PERSON,
			)));
			$agents_filter += $employees->names ();
		}

		natcasesort ($agents_filter);

		$cl_user = get_instance (CL_USER);
		$oid = $cl_user->get_current_person ();
		$agent = obj ($oid);

		### address filter
		if (!is_object ($this->administrative_structure))
		{
			return;
		}

		$list =& $this->administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $this_object->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_1"),
		));
		$address_filter1 = is_object ($list) ? $list->names () : array (); ### maakond

		$list =& $this->administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $this_object->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_2"),
		));
		$address_filter2 = is_object ($list) ? $list->names () : array ();### linn

		$list =& $this->administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $this_object->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_3"),
		));
		$address_filter3 = is_object ($list) ? $list->names () : array ();### linnaosa

		### table definition
		$table->define_field(array(
			"name" => "address_1",
			"caption" => t("Maa&shy;kond"),
			"sortable" => 1,
			"filter" => $address_filter1,
		));

		$table->define_field(array(
			"name" => "address_2",
			"caption" => t("Linn"),
			"sortable" => 1,
			"filter" => $address_filter2,
		));

		$table->define_field(array(
			"name" => "address_4",
			"caption" => t("Linnaosa"),
			"sortable" => 1,
			"filter" => $address_filter3,
		));

		$table->define_field(array(
			"name" => "address_3",
			"caption" => t("Vald"),
			"sortable" => 1,
		));

		$table->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
		));

		// $table->define_field(array(
			// "name" => "state",
			// "caption" => t("Staatus"),
			// "sortable" => 1,
			// "filter" => array ("1", "2"),
		// ));

		$table->define_field (array (
			"name" => "transaction_type",
			"caption" => t("Tehing"),
			"sortable" => 1,
			"filter" => array (
				t("Müük"),
				t("Ost"),
				t("Üürile anda"),
			),
		));

		$table->define_field(array(
			"name" => "created",
			"caption" => t("Lisatud"),
			"type" => "time",
			"format" => $this->default_date_format,
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"type" => "time",
			"format" => $this->default_date_format,
			"sortable" => 1
		));

		// $table->define_field(array(
			// "name" => "owner_company",
			// "caption" => t("Omanik"),
			// "sortable" => 1
		// ));

		$table->define_field(array(
			"name" => "seller",
			"caption" => t("Müüja"),
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "agent",
			"caption" => t("Maakler"),
			"filter" => $agents_filter,
			"filter_options" => array (
				"match" => "substring",
				"selected" => $agent->name (),
			),
			"sortable" => 1,
		));

		if ($table->name == "apartments")
		{
			$table->define_field(array(
				"name" => "floor",
				"caption" => t("Korrus"),
				"sortable" => 1
			));

			$table->define_field(array(
				"name" => "is_middle_floor",
				"caption" => t(">K<"),
				"tooltip" => t("Pole esimene ega viimane korrus"),
				"filter" => array (t("Jah"), t("Ei")),
			));
		}


		$applicable_tables = array (
			"houses",
			"houseparts",
			"cottages",
			"rowhouses",
			"apartments",
		);

		if (in_array ($table->name, $applicable_tables))
		{
			$table->define_field(array(
				"name" => "number_of_rooms",
				"caption" => t("Tube"),
				"filter" => range (1, 9),
				"sortable" => 1,
			));
			$table->define_field(array(
				"name" => "total_floor_area",
				// "caption" => t("m<sup><small>2</small></sup>"),
				"caption" => t("m<sup>2</sup>"),
				"tooltip" => t("Üldpind"),
				"sortable" => 1
			));
			$table->define_field(array(
				"name" => "transaction_price",
				"caption" => t("Hind"),
				"sortable" => 1
			));
		}

		$table->define_field(array(
			"name" => "visible",
			// "caption" => t("Näh&shy;tav"),
			"caption" => t("<a href='javascript:selall(\"realestatemgr-is_visible\")'>N</a>"),
			"tooltip" => t("Nähtav. Vali/kaota valik kõgil ridadel"),
		));

		$table->define_field(array(
			"name" => "archived",
			"caption" => t("Arh."),
			"tooltip" => t("Arhiveeritud"),
			"filter" => array (t("Jah"), t("Ei")),
			"filter_options" => array (
				"selected" => t("Ei"),
			),
		));

		$table->define_field(array(
			"name" => "actions",
			"caption" => t(""),
		));
		$table->define_chooser(array(
			"name" => "selection",
			"field" => "oid",
		));

		$table->set_default_sortby ("created");
		$table->set_default_sorder ("desc");
		$table->define_pageselector (array (
			"type" => "text",
			"records_per_page" => ($this_object->prop ("properties_list_perpage") ? $this_object->prop ("properties_list_perpage") : 50),
		));
	}

	function _property_toolbar($arr)
	{
		$this_object = $arr["obj_inst"];
		$toolbar =& $arr["prop"]["vcl_inst"];
		$return_url = urlencode(aw_global_get('REQUEST_URI'));
		$table_name = str_replace ("grp_realestate_properties_", "", $arr["request"]["group"]);
		$table->name = ("grp_realestate_properties" != $table_name) ? $table_name : "all";

		### adding urls
		$add_house_url = $this->mk_my_orb("add_property", array(
			"return_url" => $return_url,
			"manager" => $this_object->id (),
			"type" => "house",
			// "parent" => $parent,
		), "realestate_manager");
		$add_rowhouse_url = $this->mk_my_orb("add_property", array(
			"return_url" => $return_url,
			"manager" => $this_object->id (),
			"type" => "rowhouse",
			// "parent" => $parent,
		), "realestate_manager");
		$add_cottage_url = $this->mk_my_orb("add_property", array(
			"return_url" => $return_url,
			"manager" => $this_object->id (),
			"type" => "cottage",
			// "parent" => $parent,
		), "realestate_manager");
		$add_housepart_url = $this->mk_my_orb("add_property", array(
			"return_url" => $return_url,
			"manager" => $this_object->id (),
			"type" => "housepart",
			// "parent" => $parent,
		), "realestate_manager");
		$add_apartment_url = $this->mk_my_orb("add_property", array(
			"return_url" => $return_url,
			"manager" => $this_object->id (),
			"type" => "apartment",
			// "parent" => $parent,
		), "realestate_manager");
		$add_commercial_url = $this->mk_my_orb("add_property", array(
			"return_url" => $return_url,
			"manager" => $this_object->id (),
			"type" => "commercial",
			// "parent" => $parent,
		), "realestate_manager");
		$add_garage_url = $this->mk_my_orb("add_property", array(
			"return_url" => $return_url,
			"manager" => $this_object->id (),
			"type" => "garage",
			// "parent" => $parent,
		), "realestate_manager");
		$add_land_url = $this->mk_my_orb("add_property", array(
			"return_url" => $return_url,
			"manager" => $this_object->id (),
			"type" => "land",
			// "parent" => $parent,
		), "realestate_manager");


		### buttons for all objects tab
		if ("all" == $table->name)
		{
			$toolbar->add_menu_button(array(
				"name" => "add",
				"img" => "new.gif",
				"tooltip" => t("Lisa uus"),
			));

			if ($this->can("view", $this_object->prop ("houses_folder")))
			{
				$toolbar->add_menu_item(array(
					"parent" => "add",
					"text" => t("Maja"),
					"link" => $add_house_url,
				));
			}

			if ($this->can("view", $this_object->prop ("rowhouses_folder")))
			{
				$toolbar->add_menu_item(array(
					"parent" => "add",
					"text" => t("Ridaelamu"),
					"link" => $add_rowhouse_url,
				));
			}

			if ($this->can("view", $this_object->prop ("cottages_folder")))
			{
				$toolbar->add_menu_item(array(
					"parent" => "add",
					"text" => t("Suvila"),
					"link" => $add_cottage_url,
				));
			}

			if ($this->can("view", $this_object->prop ("houseparts_folder")))
			{
				$toolbar->add_menu_item(array(
					"parent" => "add",
					"text" => t("Majaosa"),
					"link" => $add_housepart_url,
				));
			}

			if ($this->can("view", $this_object->prop ("apartments_folder")))
			{
				$toolbar->add_menu_item(array(
					"parent" => "add",
					"text" => t("Korter"),
					"link" => $add_apartment_url,
				));
			}

			if ($this->can("view", $this_object->prop ("commercial_properties_folder")))
			{
				$toolbar->add_menu_item(array(
					"parent" => "add",
					"text" => t("Äripind"),
					"link" => $add_commercial_url,
				));
			}

			if ($this->can("view", $this_object->prop ("garages_folder")))
			{
				$toolbar->add_menu_item(array(
					"parent" => "add",
					"text" => t("Garaaz"),
					"link" => $add_garage_url,
				));
			}

			if ($this->can("view", $this_object->prop ("land_estates_folder")))
			{
				$toolbar->add_menu_item(array(
					"parent" => "add",
					"text" => t("Maatükk"),
					"link" => $add_land_url,
				));
			}
		}

		if ("houses" == $table->name)
		{
			$toolbar->add_button(array(
				"name" => "add",
				"img" => "new.gif",
				"tooltip" => t("Lisa uus maja"),
				"url" => $add_house_url,
			));
		}

		if ("rowhouses" == $table->name)
		{
			$toolbar->add_button(array(
				"name" => "add",
				"img" => "new.gif",
				"tooltip" => t("Lisa uus ridaelamu"),
				"url" => $add_rowhouse_url,
			));
		}

		if ("cottages" == $table->name)
		{
			$toolbar->add_button(array(
				"name" => "add",
				"img" => "new.gif",
				"tooltip" => t("Lisa uus suvila"),
				"url" => $add_cottage_url,
			));
		}

		if ("houseparts" == $table->name)
		{
			$toolbar->add_button(array(
				"name" => "add",
				"img" => "new.gif",
				"tooltip" => t("Lisa uus majaosa"),
				"url" => $add_housepart_url,
			));
		}

		if ("apartments" == $table->name)
		{
			$toolbar->add_button(array(
				"name" => "add",
				"img" => "new.gif",
				"tooltip" => t("Lisa uus korter"),
				"url" => $add_apartment_url,
			));
		}

		if ("commercial_properties" == $table->name)
		{
			$toolbar->add_button(array(
				"name" => "add",
				"img" => "new.gif",
				"tooltip" => t("Lisa uus äripind"),
				"url" => $add_commercial_url,
			));
		}

		if ("garages" == $table->name)
		{
			$toolbar->add_button(array(
				"name" => "add",
				"img" => "new.gif",
				"tooltip" => t("Lisa uus garaaz"),
				"url" => $add_garage_url,
			));
		}

		if ("land_estates" == $table->name)
		{
			$toolbar->add_button(array(
				"name" => "add",
				"img" => "new.gif",
				"tooltip" => t("Lisa uus maatükk"),
				"url" => $add_land_url,
			));
		}

		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud objekt(id)"),
			"confirm" => t("Kustutada valitud objekt(id)?"),
			"action" => "delete",
		));

		$toolbar->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta muudatused"),
			"action" => "submit",
		));

		$search_url = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_realestate_properties_search",
		), "realestate_manager");

		$toolbar->add_button(array(
			"name" => "search",
			"url" => $search_url,
			"img" => "search.gif",
			"tooltip" => t("Otsing"),
		));
	}

	function save_realestate_properties ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$vla = (count ($arr["request"]["realestatemgr_property_id"]) > 9) ? true : false;

		if ($vla)
		{
			aw_global_set ("no_cache_flush", 1);
			obj_set_opt ("no_cache", 1);
		}

		foreach (safe_array ($arr["request"]["realestatemgr_property_id"]) as $oid)
		{
			if (is_oid ($oid) and $this->can ("edit", $oid))
			{
				$property = obj ($oid);
				$changed = false;

				### is_visible
				$value = (int) (bool) $arr["request"]["realestatemgr-is_visible"][$oid];

				if ($value != $property->prop ("is_visible"))
				{
					$property->set_prop ("is_visible", $value);
					$changed = true;
				}

				### save changes
				if ($changed)
				{
					$property->save ();
				}
			}
		}

		if ($vla)
		{
			if (!is_object ($this->cl_cache))
			{
				$this->cl_cache = get_instance ("cache");
			}

			$this->cl_cache->full_flush ();
		}
	}

	function _clients_toolbar ($arr)
	{
		$this_object = $arr["obj_inst"];
		$toolbar =& $arr["prop"]["vcl_inst"];
		$return_url = urlencode(aw_global_get('REQUEST_URI'));

		if ($arr["prop"]["name"] == "clients_toolbar")
		{
			$url = $this->mk_my_orb("client_selection_save_form", array(
				"id" => $this_object->id(),
			));
			$toolbar->add_button(array(
				"name" => "save_client_selection",
				"url" => "javascript:aw_popup_s('{$url}','client_selection_form',400,100);",
				"img" => "save.gif",
				"tooltip" => t("Salvesta päring kliendivalimina"),
			));
		}

		if ($arr["prop"]["name"] == "client_selections_toolbar")
		{
			$toolbar->add_button(array(
				"name" => "mail",
				"action" => "send_client_mail",
				"img" => "mail_send.gif",
				"tooltip" => t("Saada valimi(te) klientidele e-kiri"),
			));
			$toolbar->add_button(array(
				"name" => "delete",
				"action" => "delete",
				"img" => "delete.gif",
				"tooltip" => t("Kustuta valitud kliendivalim(id)"),
			));
		}
	}

	function _clients_list ($arr)
	{
		$this_object = $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];
		$table->name = "clients_list";
		$this->_init_clients_list ($arr);

		if (!is_object  ($this->cl_user))
		{
			$this->cl_user = get_instance (CL_USER);
		}

		### properties menu script
		$actions_menu_script = '<script src="http://voldemar.dev.struktuur.ee/automatweb/js/popup_menu.js" type="text/javascript">
</script>';
		echo $actions_menu_script;

		### filters
		$filter_appreciation_after = aw_global_get ("realestate_clientlist_filter_appreciationafter") ? aw_global_get ("realestate_clientlist_filter_appreciationafter") : 0;
		$filter_appreciation_before = aw_global_get ("realestate_clientlist_filter_appreciationbefore") ? aw_global_get ("realestate_clientlist_filter_appreciationbefore") : time ();
		$filter_name = aw_global_get ("realestate_clientlist_filter_name") ? aw_global_get ("realestate_clientlist_filter_name") : NULL;
		$filter_address = aw_global_get ("realestate_clientlist_filter_address") ? aw_global_get ("realestate_clientlist_filter_address") : NULL;
		$filter_appreciation_type = aw_global_get ("realestate_clientlist_filter_appreciationtype");
		$filter_agent = aw_global_get ("realestate_clientlist_filter_agent");

		if (is_oid ($arr["request"]["re_client_selection"]))
		{ ### load saved client selection
			$client_selection =  obj ($arr["request"]["re_client_selection"]);

			if (!is_object ($this->cl_client_selection))
			{
				$this->cl_client_selection = get_instance (CL_REALESTATE_CLIENT_SELECTION);
			}

			$list =& $this->cl_client_selection->get_clients (array (
				"this" => $client_selection,
			));
			$client_query = false;
		}
		else
		{
			$client_query = true;
			$list = new object_list (array (
				"class_id" => CL_CRM_PERSON,
				"parent" => array ($this_object->prop ("clients_folder")),
			));
		}

		$clients = $list->arr ();

		foreach ($clients as $client)
		{
			### get appreciation note data
			$connections = $client->connections_to ();
			$properties = array ();
			$realestate_classes = array (
				CL_REALESTATE_HOUSE,
				CL_REALESTATE_ROWHOUSE,
				CL_REALESTATE_COTTAGE,
				CL_REALESTATE_HOUSEPART,
				CL_REALESTATE_APARTMENT,
				CL_REALESTATE_COMMERCIAL,
				CL_REALESTATE_GARAGE,
				CL_REALESTATE_LAND,
			);

			foreach ($connections as $connection)
			{
				if (in_array ($connection->prop ("from.class_id"), $realestate_classes))
				{
					if ($this->can ("view", $connection->prop ("from")))
					{
						$properties[$connection->prop ("from")] = obj ($connection->prop ("from"));
					}
				}
			}

			$last_appreciation_sent = NULL;
			$last_appreciation_type = NULL;

			foreach ($properties as $property)
			{
				if ($property->prop ("appreciation_note_date") > $last_appreciation_sent)
				{
					$last_appreciation_sent = $property->prop ("appreciation_note_date");
					$last_appreciation_type = $property->prop_str ("appreciation_note_type");
					$last_appreciation_type_oid = $property->prop ("appreciation_note_type");
				}
			}

			### get agent
			$agent_uid = $client->createdby ();

			if ($this_object->prop ("almightyuser"))
			{
				if (!isset ($this->realestate_agent_data[$agent_uid]))
				{
					aw_switch_user (array ("uid" => $this_object->prop ("almightyuser")));
					$oid = $this->cl_users->get_oid_for_uid ($agent_uid);
					$user = obj ($oid);
					$agent_oid = $this->cl_user->get_person_for_user ($user);
					$agent = obj ($agent_oid);
					aw_restore_user ();

					$this->realestate_agent_data[$agent_uid]["object"] = $agent;
					$this->realestate_agent_data[$agent_uid]["oid"] = $agent->id ();

					if ($this->can ("edit", $agent_oid))
					{
						$agent_name = html::get_change_url ($agent->id (), array("return_url" => get_ru (), "group" => "grp_main"), $agent->name ());
					}
					elseif ($this->can ("view", $agent_oid))
					{
						$agent_name = $agent->name ();
					}
					else
					{
						$agent_name = "...";
					}

					$this->realestate_agent_data[$agent_uid]["name"] = $agent_name;
				}
			}
			else
			{
				echo t('"Kõik lubatud" kasutaja keskkonna seadetes määramata');
				return;
			}

			### filter for submitted query
			if ($client_query)
			{
				### filter by client name
				if (isset ($filter_name))
				{
					$filter_name = trim ($filter_name);

					if ($filter_name)
					{
						$search = explode (" ", strtolower ($filter_name));
						$found = 0;
						$words = 0;

						foreach ($search as $word)
						{
							$word = trim ($word);

							if ($word)
							{
								$words++;
								$pos = strpos (strtolower ($client->name ()), trim ($word));

								if ($pos !== false)
								{
									$found++;
								}
							}
						}

						if ($words != $found)
						{
							continue;
						}
					}
				}

				### filter by client's address
				if (isset ($filter_address))
				{
					$filter_address = trim ($filter_address);

					if ($filter_address)
					{
						$search = explode (" ", strtolower ($filter_address));
						$found = 0;
						$words = 0;

						foreach ($search as $word)
						{
							$word = trim ($word);

							if ($word)
							{
								$words++;
								$pos = strpos (strtolower ($client->prop ("comment")), trim ($word));

								if ($pos !== false)
								{
									$found++;
								}
							}
						}

						if ($words != $found)
						{
							continue;
						}
					}
				}

				### filter by appreciation note sent
				if (0 < $last_appreciation_sent and $last_appreciation_sent < $filter_appreciation_after)
				{
					continue;
				}

				if ($last_appreciation_sent > $filter_appreciation_before)
				{
					continue;
				}

				### filter by appreciation note type
				if (is_array ($filter_appreciation_type) and !in_array ($last_appreciation_type_oid, $filter_appreciation_type))
				{
					continue;
				}

				### filter by agent
				if (is_array ($filter_agent) and !in_array ($this->realestate_agent_data[$agent_uid]["oid"], $filter_agent))
				{
					continue;
				}
			}

			### get email & phone
			$phones = array ();

			foreach($client->connections_from (array("type" => "RELTYPE_PHONE")) as $connection)
			{
				$phones[] = $connection->prop ("to.name");
			}

			$phones = implode (", ", $phones);
			$email = $client->get_first_obj_by_reltype ("RELTYPE_EMAIL");
			$email =  is_object ($email) ? $email->prop ("mail") : "";

			if (count ($properties))
			{
				### get properties menu
				$actions_menu = "";
				$tpl = "js_popup_menu.tpl";
				$tmp = $this->template_dir;
				$this->template_dir = $this->cfg["basedir"] . "/templates/automatweb/menuedit";
				$this->read_template ($tpl);

				#### get properties/menu items
				$classes = aw_ini_get("classes");
				$properties_menu = "";

				foreach ($properties as $property)
				{
					$class = $classes[$property->class_id ()]["file"];
					$class = explode ("/", $class);
					$class = array_pop ($class);

					if ($this->can("edit", $property->id ()))
					{
						$url = $this->mk_my_orb ("change", array (
							"id" => $property->id (),
							"return_url" => get_ru (),
							"group" => "grp_main",
						), $class);
						$this->vars(array(
							"link" => $url,
							"text" => $property->name () ? $property->name () : t("(nimetu)"),
						));
						$properties_menu .= $this->parse("MENU_ITEM");
					}
				}

				#### parse properties menu
				$this->vars(array(
					"menu_id" => "js_pop_" . $client->id (),
					"menu_icon" => $this->cfg["baseurl"] . "/automatweb/images/icons/iother_homefolder.gif",
					"MENU_ITEM" => $properties_menu,
				));
				$properties_menu = $this->parse();
				$this->template_dir = $tmp;
			}
			else
			{
				$properties_menu = "";
			}

			### ...
			$data = array (
				"name" => $client->prop ("name"),
				"pid" => $client->prop ("personal_id"),
				"phone" => $phones,
				"oid" => $client->id (),
				"email" => $email,
				"address" => $client->prop ("comment"),
				"appreciation_note_date" => $last_appreciation_sent,
				"appreciation_note_type" => $last_appreciation_type,
				"agent" => $this->realestate_agent_data[$agent_uid]["name"],
				"properties" => $properties_menu,
			);

			$table->define_data ($data);
		}
	}

	function _init_clients_list ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];
		$prop =& $arr["prop"];

		### appreciation_note_type_filter
		$prop_args = array (
			"clid" => CL_REALESTATE_PROPERTY,
			"name" => "appreciation_note_type",
		);
		list ($options, $name, $use_type) = $this->cl_classificator->get_choices($prop_args);
		$appreciation_note_type_filter = $options->names();

		### table definition
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "properties",
			"caption" => t("Obj."),
			"tooltip" => t("Kliendiga seotud kinnisvaraobjektid (ostjana/müüjana)"),
		));
		$table->define_field(array(
			"name" => "pid",
			"caption" => t("Isikukood"),
		));
		$table->define_field(array(
			"name" => "phone",
			"caption" => t("Telefon"),
		));
		$table->define_field(array(
			"name" => "email",
			"caption" => t("E-post"),
		));
		$table->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
		));
		$table->define_field(array(
			"name" => "appreciation_note_date",
			"caption" => t("Tänukirja kuupäev"),
			"type" => "time",
			"format" => $this->default_date_format,
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "appreciation_note_type",
			"caption" => t("Tänukirja liik"),
			"filter" => $appreciation_note_type_filter,
		));
		$table->define_field(array(
			"name" => "agent",
			"caption" => t("Maakler"),
			"sortable" => 1,
			// "filter" => $agents_filter,
			// "filter_options" => array (
				// "match" => "substring",
			// ),
		));
		$table->define_chooser(array(
			"name" => "selection",
			"field" => "oid",
		));

		$table->set_default_sortby ("name");
		$table->set_default_sorder ("asc");

		if (!$this->realestate_clients_search)
		{
			$table->define_pageselector (array (
				"type" => "text",
				"records_per_page" => 50,
			));
		}
	}

	function _client_selections_list ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];
		$table->name = "client_selections_list";
		$this->_init_client_selections_list ($arr);

		$list = new object_list($this_object->connections_from(array(
			"type" => "RELTYPE_CLIENT_SELECTION",
			"class_id" => CL_REALESTATE_CLIENT_SELECTION,
		)));
		$selections = $list->arr ();

		foreach ($selections as $selection)
		{
			$selection_load_url = $this->mk_my_orb("change", array(
				"id" => $this_object->id (),
				"group" => "grp_client_list",
				"re_client_selection" => $selection->id (),
			));//!!! panna siia otsingu argumendid vms ka kuidagi, et valimi moodustamise parameetrid ilmuks otsinguvormi
			$name = html::href(array(
				"url" => $selection_load_url,
				"caption" => $selection->name (),
			));

			$data = array (
				"name" => $name,
				"createdby" => $selection->createdby (),
				"created" => $selection->created (),
				"modified" => $selection->modified (),
				"oid" => $selection->id (),
			);
			$table->define_data ($data);
		}
	}

	function _init_client_selections_list ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];

		// ### agents filter
		// $agents_filter = array ();
		// $connections = $this_object->connections_from(array(
			// "type" => "RELTYPE_REALESTATEMGR_USER",
			// "class_id" => CL_CRM_COMPANY,
		// ));

		// foreach ($connections as $connection)
		// {
			// $company = $connection->to ();
			// $employees = new object_list($company->connections_from(array(
				// "type" => "RELTYPE_WORKERS",
				// "class_id" => CL_CRM_PERSON,
			// )));
			// $agents_filter = $agents_filter + $employees->names ();
		// }

		### table definition
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "created",
			"type" => "time",
			"format" => $this->default_date_format,
			"caption" => t("Loodud"),
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "modified",
			"type" => "time",
			"format" => $this->default_date_format,
			"caption" => t("Muudetud"),
			"sortable" => 1,
		));
		// $table->define_field(array(
			// "name" => "agent",
			// "caption" => t("Maakler"),
			// "filter" => $agents_filter,
			// "filter_options" => array (
				// "match" => "substring",
			// ),
		// ));
		$table->define_chooser(array(
			"name" => "selection",
			"field" => "oid",
		));

		$table->set_default_sortby ("created");
		$table->set_default_sorder ("asc");
		$table->define_pageselector (array (
			"type" => "text",
			"records_per_page" => 50,
		));
	}
/* PUBLIC METHODS */

	function on_connect_to_profession ($arr)
	{
		$connection =& $arr["connection"];
		$profession = $connection->to ();
		$unit = $profession;
		$company = NULL;

		if ($connection->prop ("from.class_id") == CL_CRM_PERSON)
		{
			### get company
			do
			{
				$connections = $unit->connections_to ();
				$sections = false;

				foreach ($connections as $connection)
				{
					if ($connection->prop("from.class_id") == CL_CRM_SECTION)
					{
						$unit = obj ($connection->prop ("from"));
						$sections = true;
					}

					if ($connection->prop("from.class_id") == CL_CRM_COMPANY)
					{
						$company = obj ($connection->prop ("from"));
					}
				}
			}
			while (!isset ($company) and $sections);


			if (is_object ($company))
			{
				### see if company is connected to realestate manager
				$connections = $company->connections_to ();
				$realestate_manager = NULL;

				foreach ($connections as $connection)
				{
					if ($connection->prop("from.class_id") == CL_REALESTATE_MANAGER)
					{
						$realestate_manager = obj ($connection->prop ("from"));
					}
				}

				if (is_object ($realestate_manager))
				{ ### give group rights to access admin
					$group = $profession->get_first_obj_by_reltype ("RELTYPE_GROUP");

					if (is_object ($group))
					{
						$group->set_prop ("can_admin_interface", 1);
						$group->save ();
					}
				}
			}
		}
	}

	/** handler for person list delete. forwards to crm_company
		@attrib name=submit_delete_relations
	**/
	function submit_delete_relations($arr)
	{
		if (is_oid (reset ($arr["check"])))
		{
			$first_object = obj (reset ($arr["check"]));
			$company = $first_object->get_first_obj_by_reltype("RELTYPE_WORK");
		}

		$this->_delegate_co ($arr, "submit_delete_relations", $company);
		return urldecode($arr["return_url"]);
	}

	/** cuts the selected person objects
		@attrib name=cut_p
	**/
	function cut_p($arr)
	{
		if (is_oid (reset ($arr["check"])))
		{
			$first_object = obj (reset ($arr["check"]));
			$company = $first_object->get_first_obj_by_reltype("RELTYPE_WORK");
		}

		return $this->_delegate_co($arr, "cut_p", $company);
	}

	/** copies the selected person objects
		@attrib name=copy_p
	**/
	function copy_p($arr)
	{
		if (is_oid (reset ($arr["check"])))
		{
			$first_object = obj (reset ($arr["check"]));
			$company = $first_object->get_first_obj_by_reltype("RELTYPE_WORK");
		}
		return $this->_delegate_co($arr, "copy_p", $company);
	}

	/** pastes the cut/copied person objects
		@attrib name=paste_p
	**/
	function paste_p($arr)
	{
		if (is_oid (reset ($arr["check"])))
		{
			$first_object = obj (reset ($arr["check"]));
			$company = $first_object->get_first_obj_by_reltype("RELTYPE_WORK");
		}

		return $this->_delegate_co($arr, "paste_p", $company);
	}

	/**
		@attrib name=new_user_company
	**/
	function new_user_company ($arr)
	{
		if (!is_oid($arr["id"]))
		{
			error::raise(array(
				"msg" => t("Halduskeskkond defineerimata"),
				"fatal" => true,
				"show" => true,
			));
		}

		$this_object = obj ($arr["id"]);
		$company = obj();
		$company->set_class_id(CL_CRM_COMPANY);
		$company->set_parent($this_object->prop ("companies_folder"));
		$company->set_name("Uus organisatsioon");
		$company->save();
		$this_object->connect (array (
			"to" => $company,
			"reltype" => "RELTYPE_REALESTATEMGR_USER",
		));

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), $arr["class"]);
		return $return_url;
	}

/**
    @attrib name=add_property
	@param manager required type=int
	@param type required
	@param section optional
	@param return_url optional
	@returns If return url is specified, change_url of created object else oid of created object.
**/
	function add_property ($arr)
	{
		if ( is_oid($arr["manager"]) and $this->can ("view", $arr["manager"]) )
		{
			$this_object = obj ($arr["manager"]);
			$classificator = get_instance(CL_CLASSIFICATOR);

			switch ($arr["type"])
			{
				case "house":
					$class_id = CL_REALESTATE_HOUSE;
					$parent = $this_object->prop ("houses_folder");
					break;

				case "rowhouse":
					$class_id = CL_REALESTATE_ROWHOUSE;
					$parent = $this_object->prop ("rowhouses_folder");
					break;

				case "cottage":
					$class_id = CL_REALESTATE_COTTAGE;
					$parent = $this_object->prop ("cottages_folder");
					break;

				case "housepart":
					$class_id = CL_REALESTATE_HOUSEPART;
					$parent = $this_object->prop ("houseparts_folder");
					break;

				case "apartment":
					$class_id = CL_REALESTATE_APARTMENT;
					$parent = $this_object->prop ("apartments_folder");
					break;

				case "commercial":
					$class_id = CL_REALESTATE_COMMERCIAL;
					$parent = $this_object->prop ("commercial_properties_folder");
					break;

				case "garage":
					$class_id = CL_REALESTATE_GARAGE;
					$parent = $this_object->prop ("garages_folder");
					break;

				case "land":
					$class_id = CL_REALESTATE_LAND;
					$parent = $this_object->prop ("land_estates_folder");
					break;

				default:
					return;
			}

			if (!$this->can ("view", $this_object->prop ("clients_folder")))
			{
				echo t("Klientide kaust kinnisvarahalduskeskkonnas määramata. Uut objekti ei loodud. ");
				return;
			}

			// error::raise_if (!is_oid ($parent), array(
				// "msg" => t("Kinnisvaraobjekti kaust defineerimata"),
				// "fatal" => false,
				// "show" => true,
			// ));

			if (!is_oid ($parent))
			{
				echo t("Kinnisvaraobjekti kaust määramata. Objekt pannakse kinnisvarahalduskeskkonnaga samasse kausta. ");
				$parent = $this_object->parent ();
			}

			$property = obj ();
			$property->set_class_id ($class_id);
			$property->set_parent ($parent);
			$property->set_prop ("realestate_manager", $this_object->id ());
			$property->set_prop ("weeks_valid_for", 12);
			$property->set_name (t("..."));
			$property->save ();

			### set default prop values (for classificators)
			$need_classificator_defaults = array (
				"transaction_type",
				"visible_to",
				"priority",
			);

			foreach ($need_classificator_defaults as $prop_name)
			{
				$prop_args = array (
					"clid" => CL_REALESTATE_PROPERTY,
					"name" => $prop_name,
				);
				list ($options_tt, $name, $use_type, $default) = $classificator->get_choices($prop_args);
				$property->set_prop ($prop_name, $default);
			}

			### set object owner to organization to which user belongs
			$cl_user = get_instance(CL_USER);
			$oid = $cl_user->get_current_person ();
			$person = is_oid ($oid) ? obj ($oid) : obj ();

			if (is_oid ($arr["section"]))
			{
				$property->set_meta ("owner_company_section", $arr["section"]);
			}
			elseif ($section = $person->get_first_obj_by_reltype ("RELTYPE_SECTION"))
			{
				$property->set_meta ("owner_company_section", $section->id ());
			}
			else
			{
				echo t("Organisatsiooni üksus määramata. ");
			}

			### save & go to created object
			$property->save ();

			if ($arr["return_url"])
			{
				$property_uri = $this->mk_my_orb ("change", array (
					"id" => $property->id (),
					"return_url" => urlencode ($arr["return_url"]),
					"group" => "grp_main",
				), "realestate_" . $arr["type"]);
				return $property_uri;
			}
			else
			{
				return $property->id ();
			}
		}
	}

/**
	@attrib name=delete
	@param property_id optional type=int
	@param id optional
	@param group optional
	@param subgroup optional
**/
	function delete ($arr)
	{
		$sel = $arr["selection"];

		if (is_array($sel))
		{
			$ol = new object_list(array(
				"oid" => array_keys($sel),
			));

			for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				if ($this->can("delete", $o->id()))
				{
					$o->delete ();
				}
			}
		}
		elseif ($this->can("delete", $arr["property_id"]))
		{
			$o = obj ($arr["property_id"]);
			$o->delete ();
		}

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		));
		// echo $return_url;
		return $return_url;
	}

/**
	@attrib name=archive
	@param property_id required type=int
	@param id optional
	@param group optional
	@param subgroup optional
**/
	function archive ($arr)
	{
		if (is_oid ($arr["property_id"]) and $this->can("edit", $arr["property_id"]))
		{
			$o = obj ($arr["property_id"]);
			$o->set_prop ("is_archived", 1);
			$o->save ();
		}
		else
		{
			error::raise(array(
				"msg" => t("Parameeter pole oid või puudub kasutajal õigus objekti arhiveerida. Arhiveerimiseks antakse haldurist juba kontrollitud parameetrid."),
				"fatal" => false,
				"show" => true,
			));
		}

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "realestate_manager");
		// echo $return_url;
		return $return_url;
	}

/**
    @attrib name=new_profession all_args=1
**/
	function new_profession ($arr)
	{
		// 'parent'=>$tmp->id(),
		// 'alias_to'=>$alias_to,
		// 'reltype'=> $co->reltype_professions,
		// 'return_url'=>urlencode(aw_global_get('REQUEST_URI'))

		// crm_profession::new;
	}

/**
    @attrib name=save_client_selection
	@param id required type=int
**/
	function save_client_selection ($arr)
	{
		$this_object = obj ($arr["id"]);
		$name = $arr["realestate_client_selection_name"];

		### create selection object
		$client_selection = obj ();
		$client_selection->set_class_id (CL_REALESTATE_CLIENT_SELECTION);
		$client_selection->set_parent ($this_object->id ());
		$client_selection->set_meta ("realestate_clientlist_filter_appreciationafter", aw_global_get ("realestate_clientlist_filter_appreciationafter"));
		$client_selection->set_meta ("realestate_clientlist_filter_appreciationbefore", aw_global_get ("realestate_clientlist_filter_appreciationbefore"));
		$client_selection->set_meta ("realestate_clientlist_filter_address", aw_global_get ("realestate_clientlist_filter_address"));
		$client_selection->set_meta ("realestate_clientlist_filter_agent", aw_global_get ("realestate_clientlist_filter_agent"));
		$client_selection->set_meta ("realestate_clientlist_filter_appreciationtype", aw_global_get ("realestate_clientlist_filter_appreciationtype"));
		$client_selection->set_meta ("realestate_clientlist_filter_name", aw_global_get ("realestate_clientlist_filter_name"));
		$client_selection->set_prop ("client_ids", $arr["selection"]);
		$client_selection->set_name ($arr["realestate_client_selection_name"]);
		$client_selection->save ();
		$this_object->connect (array (
			"to" => $client_selection,
			"reltype" => "RELTYPE_CLIENT_SELECTION",
		));

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		));
		return $return_url;
	}

/**
    @attrib name=client_selection_save_form
	@param id required type=int
	@param saved optional type=int
**/
	function client_selection_save_form ($arr)
	{
		$tpl = "client_selection_save_form.tpl";
		$this->set_parse_method ("eval");
		$this->read_template ($tpl);

		$prop = html::textbox (array (
			"name" => "realestate_client_selection_name",
		));
		$this->vars(array(
			"caption" => t("Valimi nimi"),
			"element" => $prop,
		));
		$name_prop = $this->parse("LINE");

		$this->vars(array(
			"value" => t("Salvestatava valimi andmed"),
		));
		$subtitle = $this->parse("SUB_TITLE");

		$this->vars(array(
			"form" => $subtitle . $name_prop,
			"button_name" => t("Salvesta"),
			"reforb" => $this->mk_reforb("save_client_selection", array(
				"id" => $arr["id"],
				"exit" => 1,
			)),
		));

		return $this->parse ();
	}
/*} END PUBLIC METHODS */
}
?>
