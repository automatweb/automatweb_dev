<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_manager.aw,v 1.1 2005/10/31 17:13:35 voldemar Exp $
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

	@property title3 type=text store=no subtitle=1
	@caption &nbsp;
		@property proplist_filter_transaction_closed type=checkbox ch_value=1 store=no
		@caption Kuva ainult sõlmitud tehinguga objekte

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
	// @property user_mgr_toolbar type=toolbar store=no no_caption=1
	@property user_mgr_tree type=treeview store=no no_caption=1 parent=lbox
	@layout tbox type=vbox parent=vsplitbox

	@property title1 type=text store=no subtitle=1 parent=tbox
	@caption Ametile antud õigused objektitüüpide järgi

	@property user_mgr_typerights type=table store=no no_caption=1 parent=tbox

	@property title12 type=text store=no subtitle=1 parent=tbox
	@caption &Otilde;igused objekti aadressi järgi

	@property rights_country type=relpicker reltype=RELTYPE_REALESTATEMGR_COUNTRY clid=CL_COUNTRY automatic=1 parent=tbox store=no
	@comment Riik, mille kohta õigusi määratakse.
	@caption Riik

	@property rights_adminunit_type type=select parent=tbox store=no
	@comment Aadressitase, mille kohta õigusi määratakse. Aadressitaseme muutmisel jäävad teiste aadressitasemete seaded samaks! Et neid muuta tuleb valida uuesti sama aadressitase.
	@caption Aadressitase

	@property rights_adminunit_type_current type=hidden store=no

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

		@property button3 type=submit store=no
		@caption Otsi

@default group=grp_client_selections
	@property client_selections_toolbar type=toolbar store=no no_caption=1
	@property client_selections_list type=table store=no no_caption=1


@default group=grp_clients_mailer
	// @property asdfasdf type=textbox
	// @caption From



@default group=grp_settings
	@property almightyuser type=textbox
	@caption "Kõik lubatud" kasutaja uid

	@property default_country type=relpicker reltype=RELTYPE_REALESTATEMGR_COUNTRY clid=CL_COUNTRY automatic=1
	@comment Riik, milles süsteemis hallatavad kinnisvaraobjektid asuvad
	@caption Riik

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

	// @property companies_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	// @caption Kinnisvaraettev&otilde;tete kaust

	// @property brokers_folder type=relpicker reltype=RELTYPE_REALESTATEMGR_FOLDER clid=CL_MENU
	// @caption Maaklerfirmade kaust

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

	@property print_properties_house type=relpicker reltype=RELTYPE_PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT
	@comment On/pole tüüpi elemendid kuvatakse prindivaates "lisainfo" all, ülejäänud kahes tulbas enne lisaandmeid. Seejuures kasutatakse ekspordi objekti vaates "Koosta tabel" leiduvat "Tulba pealkiri" väärtust elemendi suffiksi kuvamiseks (näiteks üldpinna puhul mõõtühik m2).
	@caption Prinditavad elemendid (Maja)

	@property print_properties_housepart type=relpicker reltype=RELTYPE_PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT
	@comment vt. kommentaari Prinditavad elemendid (Maja)
	@caption Prinditavad elemendid (Majaosa)

	@property print_properties_rowhouse type=relpicker reltype=RELTYPE_PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT
	@comment vt. kommentaari Prinditavad elemendid (Maja)
	@caption Prinditavad elemendid (Ridaelamu)

	@property print_properties_cottage type=relpicker reltype=RELTYPE_PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT
	@comment vt. kommentaari Prinditavad elemendid (Maja)
	@caption Prinditavad elemendid (Suvila)

	@property print_properties_apartment type=relpicker reltype=RELTYPE_PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT
	@comment vt. kommentaari Prinditavad elemendid (Maja)
	@caption Prinditavad elemendid (Korter)

	@property print_properties_commercial type=relpicker reltype=RELTYPE_PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT
	@comment vt. kommentaari Prinditavad elemendid (Maja)
	@caption Prinditavad elemendid (&Auml;ripind)

	@property print_properties_garage type=relpicker reltype=RELTYPE_PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT
	@comment vt. kommentaari Prinditavad elemendid (Maja)
	@caption Prinditavad elemendid (Garaaz)

	@property print_properties_land type=relpicker reltype=RELTYPE_PROPERTY_PRINT_SELECTION clid=CL_OBJECT_EXPORT
	@comment vt. kommentaari Prinditavad elemendid (Maja)
	@caption Prinditavad elemendid (Maatükk)

	@property available_variables_names type=text store=no
	@caption Template'ites kasutada olevad muutujad




// --------------- RELATION TYPES ---------------------

@reltype REALESTATEMGR_FOLDER value=1 clid=CL_MENU
@caption Kaust

@reltype REALESTATEMGR_USER_GROUP value=2 clid=CL_GROUP
@caption Kasutajagrupp

@reltype REALESTATEMGR_COUNTRY value=3 clid=CL_COUNTRY
@caption Riik

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

		if ( (!$this->send_customer_mail and $arr["id"] == "grp_clients_mailer") or ($this->send_customer_mail and $arr["id"] != "grp_clients_mailer") )
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
				$prop["value"] = aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : "";
				break;

			### properties tab
			case "property_searches":
				$this->_property_searches ($arr);
				break;

			case "property_search":
				break;

			case "property_toolbar":
				$this->_property_toolbar ($arr);
				break;

			case "properties_list":
				$this->_properties_list ($arr);
				break;

			case "clientlist_filter_appreciationafter":
			case "proplist_filter_modifiedafter":
			case "proplist_filter_createdafter":
			case "proplist_filter_closedafter":
				$prop["value"] = aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : -1;
				break;

			case "clientlist_filter_appreciationbefore":
			case "proplist_filter_modifiedbefore":
			case "proplist_filter_createdbefore":
			case "proplist_filter_closedbefore":
				$prop["value"] = aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : (time () + 86400);
				break;

			case "clientlist_filter_name":
			case "clientlist_filter_address":
			case "proplist_filter_pricemin":
			case "proplist_filter_pricemax":
				$prop["value"] = aw_global_get ("realestate_" . $prop["name"]) ? aw_global_get ("realestate_" . $prop["name"]) : "";
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

			case "user_mgr_toolbar":
				$this->_user_mgr_toolbar($arr);
				break;

			case "user_mgr_tree":
				$this->_user_mgr_tree($arr);
				break;

			case "user_mgr_typerights":
				$retval = $this->_user_mgr_typerights ($arr);
				break;

			case "title1":
				if (!is_oid ($arr["request"]["cat"]))
				{
					$prop["caption"] = t("Vali amet, mille õigusi muuta");
				}
				break;

			case "can_houses":
			case "can_rowhouses":
			case "can_cottages":
			case "can_houseparts":
			case "can_apartments":
			case "can_commercial_properties":
			case "can_garages":
			case "can_land_estates":
				if (!is_oid ($arr["request"]["cat"]))
				{
					return PROP_IGNORE;
				}

				if (!is_object ($this->usr_mgr_profession_group))
				{
					// $prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					// $retval = PROP_ERROR;
					return PROP_IGNORE;
				}

				$folder_name = substr ($prop["name"], 4);

				if (!is_oid ($this_object->prop ($folder_name . "_folder")))
				{
					$prop["error"] .= sprintf (t("Seda tüüpi kinnisvaraobjektide kataloog määramata. "));
					$retval = PROP_ERROR;
				}

				if ($retval == PROP_OK)
				{
					// $folder = obj ($this_object->prop ($folder_name . "_folder"));
					$gid = $this->cl_users->get_gid_for_oid ($this->usr_mgr_profession_group->id ());
					$acl_current_settings = $this->get_acl_for_oid_gid ($this_object->prop ($folder_name . "_folder"), $gid);

					$prop["value"] = array (
						"can_view" => $acl_current_settings["can_view"],
						"can_add" => $acl_current_settings["can_add"],
						"can_edit" => $acl_current_settings["can_edit"],
						// "can_admin" => $acl_current_settings["can_admin"],
						"can_delete" => $acl_current_settings["can_delete"],
					);
					$prop["options"] = array(
						"can_view" => t("Vaatamine"),
						"can_add" => t("Lisamine"),
						"can_edit" => t("Muutmine"),
						// "can_admin" => t("Õiguste muutmine"),
						"can_delete" => t("Kustutamine"),
					);
				}
				break;

			case "rights_country":
				### proceed only if a profession is selected
				if (!is_oid ($arr["request"]["cat"]))
				{
					return PROP_IGNORE;
				}

				if (!is_object ($this->usr_mgr_profession_group))
				{
					// $prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					// $retval = PROP_ERROR;
					return PROP_IGNORE;
				}

				### see if default country is available
				if (!is_oid ($this_object->prop("default_country")))
				{
					$prop["error"] .= t("Vaikimisi kasutatav riik seadetes määramata. ");
					$retval = PROP_ERROR;
					// return PROP_IGNORE;
				}

				if (!$this->can ("view", $this_object->prop("default_country")))
				{
					$prop["error"] .= t("Valitud vaikimisi kasutatava riigiobjekti vaatamiseks puudub kasutajal õigus. ");
					$retval = PROP_ERROR;
				}

				if ($retval == PROP_OK)
				{
					### get country
					if (is_oid (aw_global_get ("realestate_usr_mgr_rights_country")) and $this->can ("view", aw_global_get ("realestate_usr_mgr_rights_country")))
					{
						$country_id = aw_global_get ("realestate_usr_mgr_rights_country");
					}
					else
					{
						$country_id = $this_object->prop("default_country");
					}

					$prop["value"] = array ($country_id => $country_id);
				}
				break;

			case "rights_adminunit_type_current":
			case "rights_adminunit_type":
				### proceed only if a profession is selected
				if (!is_oid ($arr["request"]["cat"]))
				{
					return PROP_IGNORE;
				}

				if (!is_object ($this->usr_mgr_profession_group))
				{
					// $prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					// $retval = PROP_ERROR;
					return PROP_IGNORE;
				}

				### see if default country is available
				if (!is_oid ($this_object->prop("default_country")))
				{
					$prop["error"] .= t("Vaikimisi kasutatav riik seadetes määramata. ");
					$retval = PROP_ERROR;
					// return PROP_IGNORE;
				}

				if (!$this->can ("view", $this_object->prop("default_country")))
				{
					$prop["error"] .= t("Valitud vaikimisi kasutatava riigiobjekti vaatamiseks puudub kasutajal õigus. ");
					$retval = PROP_ERROR;
				}

				if ($retval == PROP_OK)
				{
					### get country
					if (is_oid (aw_global_get ("realestate_usr_mgr_rights_country")) and $this->can ("view", aw_global_get ("realestate_usr_mgr_rights_country")))
					{
						$country = obj (aw_global_get ("realestate_usr_mgr_rights_country"));
					}
					else
					{
						$country = obj ($this_object->prop("default_country"));
					}

					### get administrative structure for selected country
					$administrative_structure = $country->get_first_obj_by_reltype("RELTYPE_ADMINISTRATIVE_STRUCTURE");

					if (!is_object ($administrative_structure))
					{
						$prop["error"] .= t("Valitud riigiobjektil pole määratud haldusjaotust või puuduvad kasutajal sellele õigused. ");
						$retval = PROP_ERROR;
					}

					$cl_administrative_structure = get_instance (CL_COUNTRY_ADMINISTRATIVE_STRUCTURE);
					$unit_types =& $cl_administrative_structure->get_structure (array(
						"id" => $administrative_structure->id(),
					));

					### get admin unit types for selected country
					foreach ($unit_types as $unit_type)
					{
						if ($unit_type->id () == aw_global_get ("realestate_usr_mgr_rights_adminunit_type"))
						{
							$prop["value"] = $unit_type->id ();
						}

						$options[$unit_type->id ()] = $unit_type->name ();
					}

					### options for unit type select
					if ($prop["name"] == "rights_adminunit_type")
					{
						$prop["options"] = $options;
					}

					### get value for hidden unit type prop
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
					// $prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					// $retval = PROP_ERROR;
					return PROP_IGNORE;
				}

				### get selected admin unit type
				if (!is_oid (aw_global_get ("realestate_usr_mgr_rights_adminunit_type")))
				{
					return PROP_IGNORE;
				}
				else
				{
					$admin_unit_type = obj (aw_global_get ("realestate_usr_mgr_rights_adminunit_type"));
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
						"class_id" => $admin_unit_type->prop ("unit_type"),
						"subclass" => $admin_unit_type->id (),
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
			case "can_houses":
			case "can_rowhouses":
			case "can_cottages":
			case "can_houseparts":
			case "can_apartments":
			case "can_commercial_properties":
			case "can_garages":
			case "can_land_estates":
				$folder_name = substr ($prop["name"], 4);

				### connect current profession usergroup to realestateproperties folder with reltype_acl
				if (!is_object ($this->usr_mgr_profession_group))
				{
					// $prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					// $retval = PROP_ERROR;
					return PROP_IGNORE;
				}

				if (!is_oid ($this_object->prop ($folder_name . "_folder")))
				{
					$prop["error"] .= sprintf (t("Seda tüüpi kinnisvaraobjektide kataloog määramata. "));
					$retval = PROP_ERROR;
				}

				if (!$this->can ("admin", $this_object->prop ($folder_name . "_folder")))
				{
					$prop["error"] .= sprintf (t("Kasutajal puudub luba selle kinnisvaraobjekti tüübi õigusi määrata. "));
					// $retval = PROP_ERROR;
					return PROP_ERROR;
				}

				if ($retval == PROP_OK)
				{
					$can_add = (int) (boolean) $prop["value"]["can_add"];
					$can_edit = (int) (boolean) $prop["value"]["can_edit"];
					// $can_admin = (int) (boolean) $prop["value"]["can_admin"];
					$can_admin = 0;
					$can_delete = (int) (boolean) $prop["value"]["can_delete"];
					$can_view = (int) (boolean) $prop["value"]["can_view"];

					$folder = obj ($this_object->prop ($folder_name . "_folder"));
					$retval = $folder->acl_set ($this->usr_mgr_profession_group, array(
						"can_add" => $can_add,
						"can_edit" => $can_edit,
						"can_admin" => $can_admin,
						"can_delete" => $can_delete,
						"can_view" => $can_view,
					));
					$folder->save ();
				}
				break;

			case "rights_adminunit_type":
			case "rights_country":
				aw_session_set ("realestate_usr_mgr_" . $prop["name"], $prop["value"]);
				return PROP_IGNORE;
				break;

			case "rights_adminunit":
				if (!is_object ($this->usr_mgr_profession_group))
				{
					// $prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
					// $retval = PROP_ERROR;
					return PROP_IGNORE;
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
								) and ($connected_unit->subclass () == $arr["request"]["rights_adminunit_type_current"]))
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
		$this_object =& $arr["obj_inst"];

		if (is_object ($this->usr_mgr_profession_group))
		{
			$typerights_properties = array (
				"can_houses",
				"can_rowhouses",
				"can_cottages",
				"can_houseparts",
				"can_apartments",
				"can_commercial_properties",
				"can_garages",
				"can_land_estates",
			);

			foreach ($typerights_properties as $property)
			{
				$folder_name = substr ($property, 4) . "_folder";

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
				$can_add = (int) (boolean) $arr["request"][$property . "_add"];
				$can_edit = (int) (boolean) $arr["request"][$property . "_edit"];
				// $can_admin = (int) (boolean) $arr["request"][$property . "_admin"];
				$can_admin = 0;
				$can_delete = (int) (boolean) $arr["request"][$property . "_delete"];
				$can_view = (int) (boolean) $arr["request"][$property . "_view"];

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

	function _user_mgr_toolbar($arr)//not used
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"caption" => t("Salvesta"),
			"action" => "submit_user_mgr_save"
		));
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

	function _init_user_mgr_typerights (&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Objekti tüüp"),
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaatamine"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "add",
			"caption" => t("Lisamine"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "edit",
			"caption" => t("Muutmine"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "delete",
			"caption" => t("Kustutamine"),
			"align" => "center"
		));
	}

	function _user_mgr_typerights ($arr)
	{
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_user_mgr_typerights ($t);

		if (!is_oid ($arr["request"]["cat"]))
		{
			return PROP_IGNORE;
		}

		if (!is_object ($this->usr_mgr_profession_group))
		{
			// $prop["error"] .= sprintf (t("Kasutajagrupp ametile määramata. "));
			// $retval = PROP_ERROR;
			return PROP_IGNORE;
		}

		$data = array (
			"can_houses" => t("Majad"),
			"can_rowhouses" => t("Ridaelamud"),
			"can_cottages" => t("Suvilad"),
			"can_houseparts" => t("Majaosad"),
			"can_apartments" => t("Korterid"),
			"can_commercial_properties" => t("Äripinnad"),
			"can_garages" => t("Garaazid"),
			"can_land_estates" => t("Maatükid"),
		);

		foreach ($data as $property => $caption)
		{
			$folder_name = substr ($property, 4);

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
						"name" => $property . "_view",
						"value" => 1,
						"checked" => $can_view,
					)),
					"add" => html::checkbox(array(
						"name" => $property . "_add",
						"value" => 1,
						"checked" => $can_add,
					)),
					"edit" => html::checkbox(array(
						"name" => $property . "_edit",
						"value" => 1,
						"checked" => $can_edit,
					)),
					"delete" => html::checkbox(array(
						"name" => $property . "_delete",
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
		$this_object =& $arr["obj_inst"];
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

		### actions menu script
		$actions_menu_script = '<script src="http://voldemar.dev.struktuur.ee/automatweb/js/popup_menu.js" type="text/javascript">
</script>';
		echo $actions_menu_script;

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

			$agent = "...";

			### compose $agent contents
			if ($this->can ("view", $property->prop ("realestate_agent1")))
			{
				$tmpagent = obj ($property->prop ("realestate_agent1"));
				$agent = html::get_change_url ($tmpagent->id (), array("return_url" => $return_url, "group" => "grp_main"), $tmpagent->name ());
			}

			if (is_oid ($property->prop ("realestate_agent2")) and $this->can ("view", $property->prop ("realestate_agent2")))
			{
				$tmpagent = obj ($property->prop ("realestate_agent2"));
				$agent .= ", " . html::get_change_url ($tmpagent->id (), array("return_url" => $return_url, "group" => "grp_main"), $tmpagent->name ());
			}

			### get owner company and unit
			$owner_company = "...";

			if (is_oid ($property->meta ("owner_company_section")) and $this->can ("view", $property->meta ("owner_company_section")))
			{
				$company_section = obj ($property->meta ("owner_company_section"));
				$parent = $company_section;

				do
				{
					$parent = obj ($parent->parent ());
				}
				while (is_oid ($parent->parent ()) and (CL_CRM_COMPANY != $parent->class_id ()) and (CL_CRM_SECTION == $parent->class_id ()));

				if (is_object ($parent))
				{
					$company = $parent;
					$owner_company = $company->name () . "//" . $company_section->name ();//!!! miks pole alamyksused yksuste all vaid on company all??
				}
			}

			### get address parts
			$address = $address_1 = $address_2 = $address_3 = $address_4 = "...";
			$address = $property->get_first_obj_by_reltype("RELTYPE_REALESTATE_ADDRESS");

			if (is_object ($address))
			{
				$cl_address = get_instance(CL_ADDRESS);
				$address_array = $cl_address->get_address_array (array (
					"id" => $address->id (),
				));

				array_pop ($address_array);
				$address_1 = array_pop ($address_array);
				$address_2 = $address_array["Linn"];
				$address_3 = $address_array["Vald"];
				$address_4 = $address_array["Linnaosa"];
				$apartment = $address->prop ("apartment") ? "-" . $address->prop ("apartment") : "";
				$address = $address_array["Tänav"] . " " . $address->prop ("street_address") . $apartment;
			}

			### get actions menu
			$actions_menu = "";
			$tpl = "js_popup_menu.tpl";
			$tmp = $this->template_dir;
			$this->template_dir = $this->cfg["basedir"] . "/templates/automatweb/menuedit";
			$this->set_parse_method("eval");
			$this->read_template($tpl);

			### get actions
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
					"text" => t("Muuda")
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
				$url = "javascript:if(confirm('".t("Arhiveerida objekt?")."')){window.location='$url';};";
				$this->vars(array(
					"link" => $url,
					"text" => t("Arhiveeri")
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
				$url = "javascript:if(confirm('".t("Kustutada objekt?")."')){window.location='$url';};";
				$this->vars(array(
					"link" => $url,
					"text" => t("Kustuta")
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

			### parse actions menu
			$this->vars(array(
				"menu_id" => "js_pop_" . $property->id (),
				"menu_icon" => $this->cfg["baseurl"] . "/automatweb/images/blue/obj_settings.gif",
				"MENU_ITEM" => $actions_menu,
			));
			$actions_menu = $this->parse();
			$this->template_dir = $tmp;


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
				"archived" => ($property->prop ("is_archived") ? t("Jah") : t("Ei")),
				"state" => $property->status (),
				"oid" => $property->id (),
				"transaction_type" => $property->prop_str ("transaction_type"),
				"created" => $property->created (),
				"modified" => $property->modified (),
				"agent" => $agent,
				"owner_company" => $owner_company,
				"actions" => $actions_menu .
				// html::get_change_url ($property->id (), array("return_url" => $return_url, "group" => "grp_main"), "Muuda") .
				html::hidden (array(
					"name" => "realestatemgr_property_id[" . $property->id () . "]",
					"value" => $property->id (),
				)),
			);

			if ($table->name == "apartments")
			{
				$data["floor"] = $property->prop ("floor");
				$data["is_middle_floor"] = ($property->prop ("is_middle_floor")) ? t("Jah") : t("Ei");
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
		$this_object =& $arr["obj_inst"];

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
			$agents_filter = $employees->names ();
		}

		natcasesort ($agents_filter);

		$cl_user = get_instance(CL_USER);
		$oid = $cl_user->get_current_person ();
		$agent = obj ($oid);

		### address filter
		$country = obj ($this_object->prop ("default_country"));
		$administrative_structure = $country->get_first_obj_by_reltype("RELTYPE_ADMINISTRATIVE_STRUCTURE");

		if (!is_object ($administrative_structure))
		{
			//!!! throw user err
			$prop["error"] = t("Haldusjaotuse struktuur riigi jaoks defineerimata.");
			return PROP_ERROR;
		}

		$cl_administrative_structure = get_instance (CL_COUNTRY_ADMINISTRATIVE_STRUCTURE);
		$unit_types =& $cl_administrative_structure->get_structure (array(
			"id" => $administrative_structure->id(),
		));
		$top_unit = reset ($unit_types);
		$list = new object_list (array (
			"class_id" => CL_COUNTRY_ADMINISTRATIVE_UNIT,
			"subclass" => $top_unit->id (),
			"parent" => $country->id (),
		));
		$address_filter1 = $list->names ();

		$list = new object_list (array (
			"class_id" => CL_COUNTRY_CITY,
			"parent" => $list->ids (),
		));
		$address_filter2 = $list->names ();

		$list = new object_list (array (
			"class_id" => CL_COUNTRY_CITYDISTRICT,
			"parent" => $list->ids (),
		));
		$address_filter4 = $list->names ();

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
			"filter" => $address_filter4,
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

		$table->define_field(array(
			"name" => "owner_company",
			"caption" => t("Omanik"),
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

	function _property_searches ($arr)
	{
		$this_object = $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "search_link",
			// "caption" => t("Otsing"),
			"align" => "left",
		));

		foreach ($this_object->connections_from (array ("type" => "RELTYPE_PROPERTY_SEARCH")) as $connection)
		{
			$search_link = html::get_change_url(
				$connection->prop ("to.oid"),
				array("return_url" => urlencode (aw_global_get("REQUEST_URI"))),
				$connection->prop ("to.name")
			);

			$table->define_data(array(
				"search_link" => $search_link,
			));
		}
	}

	function save_realestate_properties ($arr)
	{
		$this_object =& $arr["obj_inst"];

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
	}

	function _clients_toolbar ($arr)
	{
		$this_object = $arr["obj_inst"];
		$toolbar =& $arr["prop"]["vcl_inst"];
		$return_url = urlencode(aw_global_get('REQUEST_URI'));

		$toolbar->add_button(array(
			"name" => "mail",
			"action" => "send_customer_mail",
			"img" => "mail_send.gif",
			"tooltip" => t("Saada valitud klientidele e-kiri"),
		));

		if ($arr["prop"]["name"] == "clients_toolbar")
		{
			$url = $this->mk_my_orb("client_seletion_save_form", array(
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
				"name" => "delete",
				"action" => "delete",
				"img" => "delete.gif",
				"tooltip" => t("Kustuta valitud kliendivalimid"),
			));
		}
	}

	function _clients_list ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];
		$table->name = "clients_list";
		$this->_init_clients_list ($arr);

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

		$list = new object_list (array (
			"class_id" => CL_CRM_PERSON,
			"parent" => array ($this_object->prop ("clients_folder")),
		));
		$clients = is_array ($list) ? $list : $list->arr ();

		foreach ($clients as $client)
		{
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
			if ($last_appreciation_sent < $filter_appreciation_after)
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

			### get agent
			$cl_user = get_instance (CL_USER);
			$uid = $client->createdby ();
			$oid = $this->cl_users->get_oid_for_uid ($uid);

			if ($this_object->prop ("almightyuser"))
			{
				aw_switch_user (array ("uid" => $this_object->prop ("almightyuser")));
				$user = obj ($oid);
				$agent_oid = $cl_user->get_person_for_user ($user);
				$agent = obj ($agent_oid);
				aw_restore_user ();
			}
			else
			{
				echo t('"Kõik lubatud" kasutaja keskkonna seadetes määramata');
				return;
			}

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
				// $this->set_parse_method ("eval");
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
				"agent" => $agent_name,
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
			$agents_filter = $agents_filter + $employees->names ();
		}

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
			"filter" => $agents_filter,
			"filter_options" => array (
				"match" => "substring",
			),
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
			$data = array (
				"name" => $selection->name (),
				"createdby" => $selection->createdby (),
				"created" => $selection->created (),
				"modified" => $selection->modified (),
				// "agent" => $selection->meta ("realestate_clientlist_filter_agent"),
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

	/** save users permission data
		@attrib name=submit_user_mgr_save
	**/
	function submit_user_mgr_save($arr)//not used
	{
		$arr["return_url"] = urldecode($arr["return_url"]);

		if (!is_oid($arr["unit"]) || !$this->can("view", $arr["unit"]))
		{
			return $arr["return_url"];
		}

		$unit = obj($arr["unit"]);

		// get all professions for selected unit
		$professions = new object_list($unit->connections_from(array(
			"type" => "RELTYPE_PROFESSION"
		)));
		$professions = $professions->arr ();

		// create new rels for new ones
		// modify existing ones
		foreach(safe_array($professions) as $profession)
		{
			if (!isset($existing_rels[$prof]))
			{
				// create new
				$rel = obj();
				$rel->set_class_id(CL_MRP_RESOURCE_OPERATOR);
				$rel->set_parent($arr["id"]);
				$prof_o = obj($prof);
				$res_o = obj($res);
				$rel->set_name("ametinimetus ".$prof_o->name()." => ressurss ".$res_o->name());
				$rel->set_prop("profession", $prof);
				$rel->set_prop("resource", $res);
				$rel->set_prop("unit", $arr["unit"]);
				$rel->save();
			}
			elseif ($existing_rels[$prof]["res"] != $res)
			{
				// change cur
				$rel = $existing_rels[$prof]["rel"];
				$rel->set_prop("resource", $res);
				$rel->save();
			}

			unset($existing_rels[$prof]);
		}

		// delete deleted ones
		foreach($existing_rels as $prof => $rel)
		{
			if (empty($prof2res[$prof]))
			{
				$rel["rel"]->delete();
			}
		}

		$o = obj($arr["id"]);
		$oldal = safe_array($o->meta("umgr_all_resources"));
		foreach(safe_array($arr["old_all_resources"]) as $k => $v)
		{
			if ($arr["all_resources"] != $v)
			{
				$oldal[$k] = $arr["all_resources"][$k];
			}
		}

		foreach(safe_array($arr["all_resources"]) as $k => $v)
		{
				if ($arr["all_resources"] != $arr["old_all_resources"])
				{
						$oldal[$k] = $arr["all_resources"][$k];
				}
		}

		$o->set_meta("umgr_all_resources", $oldal);

		$oldal = safe_array($o->meta("umgr_dept_resources"));
		foreach(safe_array($arr["old_dept_resources"]) as $k => $v)
		{
			if ($arr["dept_resources"] != $v)
			{
				$oldal[$k] = $arr["dept_resources"][$v];
			}
		}
		$o->set_meta("umgr_dept_resources", $oldal);
		$o->save();

		// cleverly return
		return $arr["return_url"];
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
					$this->delete_connected_objects (array ("obj_inst" => $o));
					$o->delete ();
				}
			}
		}
		elseif ($this->can("delete", $arr["property_id"]))
		{
			$o = obj ($arr["property_id"]);
			$this->delete_connected_objects (array ("obj_inst" => $o));
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

	function delete_connected_objects ($arr)
	{
		$property =& $arr["obj_inst"];
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

		if (in_array ($property->class_id (), $realestate_classes))
		{
			$types = array (
				"RELTYPE_REALESTATE_ADDRESS",
				"RELTYPE_REALESTATE_PICTURE",
			);

			foreach ($types as $type)
			{
				foreach ($property->connections_from(array("type" => $type)) as $c)
				{
					$o = $c->to();

					if ($this->can("delete", $o->id()))
					{
						$o->delete ();
					}
					else
					{
						error::raise(array(
							"msg" => sprintf (t("Kustutatava kinnisvaraobjekti kaasobjekti ei lubata kasutajal kustutada. Viga õiguste seadetes. Jääb orbobjekt, mille id on %s"), $o->id ()),
							"fatal" => false,
							"show" => false,
						));
					}
				}
			}
		}
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
		$client_selection = obj ();
		$client_selection->set_class_id (CL_REALESTATE_CLIENT_SELECTION);
		$client_selection->set_parent ($this_object->id ());
		$client_selection->set_meta ("realestate_clientlist_filter_appreciationafter", aw_global_get ("realestate_clientlist_filter_appreciationafter"));
		$client_selection->set_meta ("realestate_clientlist_filter_appreciationbefore", aw_global_get ("realestate_clientlist_filter_appreciationbefore"));
		$client_selection->set_meta ("realestate_clientlist_filter_address", aw_global_get ("realestate_clientlist_filter_address"));
		$client_selection->set_meta ("realestate_clientlist_filter_agent", aw_global_get ("realestate_clientlist_filter_agent"));//!!! tblfiltrist v6tta
		$client_selection->set_meta ("realestate_clientlist_filter_appreciationtype", aw_global_get ("realestate_clientlist_filter_appreciationtype"));
		$client_selection->set_meta ("realestate_clientlist_filter_name", aw_global_get ("realestate_clientlist_filter_name"));
		$client_selection->set_name ($arr["realestate_client_selection_name"]);
		$client_selection->save ();
		$this_object->connect (array (
			"to" => $client_selection,
			"reltype" => "RELTYPE_CLIENT_SELECTION",
		));

		$return_url = $this->mk_my_orb("client_seletion_save_form", array(
			"id" => $arr["id"],
			"saved" => 1,
		));
		return $return_url;
	}

/**
    @attrib name=client_seletion_save_form
	@param id required type=int
	@param saved optional type=int
**/
	function client_seletion_save_form ($arr)
	{
		$tpl = "client_seletion_save_form.tpl";
		$this->set_parse_method ("eval");
		$this->read_template ($tpl);

		if ($arr["saved"])
		{
			echo sprintf ("<br /><center>%s</center>", t("Salvestatud"));
			echo "<script type='text/javascript'>setTimeout('window.close()',1000);</script>";
			exit;
		}
		else
		{
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
		}

		return $this->parse();
	}
/*} END PUBLIC METHODS */
}
?>
