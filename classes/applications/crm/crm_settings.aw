<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_settings.aw,v 1.12 2006/04/13 10:55:11 voldemar Exp $
// crm_settings.aw - Kliendibaasi seaded
/*

@classinfo syslog_type=ST_CRM_SETTINGS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects


@default group=general
	@property s_cfgform type=relpicker reltype=RELTYPE_CFGFORM table=objects field=meta method=serialize
	@caption Kliendi seadete vorm

	@property s_p_cfgform type=relpicker reltype=RELTYPE_CFGFORM table=objects field=meta method=serialize
	@caption Eraisikust kliendi seadete vorm

	@property work_cfgform type=relpicker reltype=RELTYPE_CFGFORM table=objects field=meta method=serialize
	@caption Minu t&ouml;&ouml;koha seadete vorm

	@property coworker_cfgform type=relpicker reltype=RELTYPE_CFGFORM table=objects field=meta method=serialize
	@caption T&ouml;&ouml;kaaslaste seadete vorm

	@property customer_employer_cfgform type=relpicker reltype=RELTYPE_CFGFORM table=objects field=meta method=serialize
	@caption Kliendi t&ouml;&ouml;taja seadete vorm

	@property bill_def_prod type=relpicker reltype=RELTYPE_PROD table=objects field=meta method=serialize
	@caption Vaikimisi toode arve ridadel


@default group=tables
	@property tables_toolbar type=toolbar store=no no_caption=1

	@layout vsplitbox type=hbox no_caption=1

	@property tables_treemenu type=treeview store=no parent=vsplitbox no_caption=1
	@caption Tabelite valik

	@property table_cfg type=table store=no parent=vsplitbox
	@caption Tabeli konfiguratsioon


@default group=whom
	@property users type=relpicker multiple=1 store=connect reltype=RELTYPE_USER field=meta method=serialize
	@caption Kasutajad

	@property persons type=relpicker multiple=1 store=connect reltype=RELTYPE_PERSON field=meta method=serialize
	@caption Isikud

	@property cos type=relpicker multiple=1 store=connect reltype=RELTYPE_COMPANY field=meta method=serialize
	@caption Organisatsioonid

	@property sects type=relpicker multiple=1 store=connect reltype=RELTYPE_SECTION field=meta method=serialize
	@caption Osakonnad

	@property profs type=relpicker multiple=1 store=connect reltype=RELTYPE_PROFESSION field=meta method=serialize
	@caption Ametinimetused

	@property everyone type=checkbox ch_value=1 table=objects field=flags
	@caption K&otilde;ik


@default group=img
	@property person_img_settings type=relpicker reltype=RELTYPE_GALLERY_CONF field=meta method=serialize
	@caption Isiku piltide seaded



@groupinfo tables caption="Tabelid"
@groupinfo whom caption="Kellele kehtib"
@groupinfo img caption="Pildid"



@reltype USER value=1 clid=CL_USER
@caption Kasutaja

@reltype CFGFORM value=2 clid=CL_CFGFORM
@caption Seadete vorm

@reltype PERSON value=3 clid=CL_CRM_PERSON
@caption Isik

@reltype COMPANY value=4 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype SECTION value=5 clid=CL_CRM_SECTION
@caption Osakond

@reltype PROFESSION value=6 clid=CL_CRM_PROFESSION
@caption Ametinimetus

@reltype PROD value=7 clid=CL_SHOP_PRODUCT
@caption Toode

@reltype GALLERY_CONF value=8 clid=CL_GALLERY_CONF
@caption Galerii seaded

*/

class crm_settings extends class_base
{
	// var ${"_properties" . CL_CRM_COMPANY} = array();
	// var ${"_properties" . CL_CRM_PERSON} = array();
	var $crmcfg_defined_tables = array();
	var $crmcfg_class_index = array();

	function crm_settings()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_settings",
			"clid" => CL_CRM_SETTINGS
		));

		$this->crmcfg_defined_tables = array(
			CL_CRM_COMPANY => array(
				// "human_resources",
				// "contacts_search_results",
				// "prof_search_results",
				// "personal_offers_table",
				// "personal_candidates_table",
				"my_customers_table" => array(
					"fields_class" => "applications/crm/crm_company_cust_impl",
					"fields_method" => "_org_table_header",
				),
				// "customer_t",
				// "projects_listing_table",
				// "my_projects",
				// "report_list",
				// "docs_tbl",
				// "dn_res",
				// "documents_lmod",
				// "bill_proj_list",
				// "bill_task_list",
				// "bills_list",
				// "my_tasks",
				// "stats_s_res",
				// "stats_list",
				// "qv_t",
			),
			CL_CRM_PERSON => array(),
		);

		$this->crmcfg_class_index = array(
			"work" => CL_CRM_COMPANY,
			"s" => CL_CRM_COMPANY,
			"s_p" => CL_CRM_PERSON,
			"coworker" => CL_CRM_PERSON,
			"customer_employer" => CL_CRM_PERSON,
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function _get_tables_toolbar($arr = array ())
	{
		$this_object = $arr["obj_inst"];
		$toolbar =& $arr["prop"]["toolbar"];

		### save button
		$toolbar->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta tabeli seaded"),
			"action" => "submit",
		));

		### load defaults button
		list($usecase, $cfg_table_name) = explode("-", $arr["request"]["tables_treemenu_item"], 2);
		$class_id = $this->crmcfg_class_index[$usecase];

		if (is_array($this->crmcfg_defined_tables[$class_id][$cfg_table_name]))
		{
			if (is_array($this_object->meta($usecase . "-" . $cfg_table_name)))
			{
				$load_defaults_url = $this->mk_my_orb("delete_table_cfg", array(
					"id" => $this_object->id (),
					"table" => $usecase . "-" . $cfg_table_name,
					"return_url" => get_ru(),
				));

				$toolbar->add_button(array(
					"name" => "defaults",
					"img" => "delete.gif",
					"tooltip" => t("Taasta algseaded"),
					"confirm" => t("Muudetud seaded kaovad kui algseaded taastada, kas jätkata?"),
					"url" => $load_defaults_url,
				));
			}
		}
	}

	function _get_tables_treemenu($arr)
	{
		$tree =& $arr["prop"]["vcl_inst"];

		$tree->add_item(0, array(
			"id" => "work",
			"name" => t("Minu t&ouml;&ouml;koht"),
			"url" => "",
		));
		$this->_add_tables($tree, CL_CRM_COMPANY, "work");

		$tree->add_item(0, array(
			"id" => "s",
			"name" => t("Klient"),
			"url" => "",
		));
		$this->_add_tables($tree, CL_CRM_COMPANY, "s");

		$tree->add_item(0, array(
			"id" => "s_p",
			"name" => t("Eraisikust klient"),
			"url" => "",
		));
		$this->_add_tables($tree, CL_CRM_PERSON, "s_p");

		$tree->add_item(0, array(
			"id" => "coworker",
			"name" => t("T&ouml;&ouml;kaaslane"),
			"url" => "",
		));
		$this->_add_tables($tree, CL_CRM_PERSON, "coworker");

		$tree->add_item(0, array(
			"id" => "customer_employer",
			"name" => t("Kliendi t&ouml;&ouml;taja"),
			"url" => "",
		));
		$this->_add_tables($tree, CL_CRM_PERSON, "customer_employer");

		$tree->set_selected_item($arr["request"]["tables_treemenu_item"]);
	}

	function _add_tables(&$tree, $cl_id, $parent)
	{
		$properties_var = "_properties" . $cl_id;

		if (!is_object ($this->cl_cfgu))
		{
			$this->cl_cfgu = get_instance("cfg/cfgutils");
		}

		if (!isset($this->$properties_var))
		{
			$this->$properties_var = $this->cl_cfgu->load_properties(array ("clid" => $cl_id));
		}

		foreach ($this->$properties_var as $name => $property)
		{
			if (("table" == $property["type"]) and array_key_exists($name, $this->crmcfg_defined_tables[$cl_id]))
			{
				$id = $parent . "-" . $name;
				$tree->add_item($parent, array(
					"id" => $id,
					"name" => $name,
					"url" => aw_url_change_var("tables_treemenu_item", $id),
				));
			}
		}
	}

	function _get_table_cfg($arr)
	{
		list($usecase, $cfg_table_name) = explode("-", $arr["request"]["tables_treemenu_item"], 2);
		$class_id = $this->crmcfg_class_index[$usecase];

		if (!is_array($this->crmcfg_defined_tables[$class_id][$cfg_table_name]))
		{
			return PROP_IGNORE;
		}


		$table =& $arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Veeru nimi"),
		));
		$table->define_field(array(
			"name" => "order",
			"caption" => t("Järjekord"),
		));
		$table->define_field(array(
			"name" => "visible",
			"caption" => t("Kasutusel"),
		));
		$table->define_field(array(
			"name" => "caption",
			"caption" => t("Pealkiri"),
		));
		$table->set_numeric_field ("order_nr");
		$table->set_default_sortby ("order_nr");
		$table->set_default_sorder ("asc");

		# get default fields
		$i = get_instance($this->crmcfg_defined_tables[$class_id][$cfg_table_name]["fields_class"]);
		$function = $this->crmcfg_defined_tables[$class_id][$cfg_table_name]["fields_method"];
		classload("vcl/table");
		$default_cfg = new vcl_table(array(
			"layout" => "generic"
		));
		$i->$function(&$default_cfg);

		# get saved cfg
		$this_object =& $arr["obj_inst"];
		$table_cfg = $this_object->meta($usecase . "-" . $cfg_table_name);
		$cfg_not_defined = is_array($table_cfg) ? false : true;

		# ...
		foreach ($default_cfg->rowdefs as $key => $rd)
		{
			$table->define_data(array(
				"name" => $rd["name"],
				"order_nr" => $cfg_not_defined ? $key : $table_cfg[$rd["name"]]["definicion"]["order"],
				"order" => html::textbox(array(
					"name" => "tablecfg-order[" . $rd["name"] . "]",
					"size" => "3",
					"textsize" => "11px",
					"value" => $table_cfg[$rd["name"]]["definicion"]["order"],
					)
				) . html::hidden(array("name" => "tablecfgkey[" . $rd["name"] . "]", "value" => $rd["name"])),
				"visible" => html::checkbox (array(
					"name" => "tablecfg-visible[" . $rd["name"] . "]",
					"checked" => $cfg_not_defined ? 1 : $table_cfg[$rd["name"]]["visible"],
				)),
				"caption" => html::textbox(array(
					"name" => "tablecfg-caption[" . $rd["name"] . "]",
					"size" => "30",
					"textsize" => "11px",
					"value" => $cfg_not_defined ? $rd["caption"] : $table_cfg[$rd["name"]]["definicion"]["caption"],
					)
				),
			));
		}

		return PROP_OK;
	}

	function apply_table_cfg($this_object, $usecase, $cfg_table_name, $table)
	{
		if (!is_object($this_object))
		{
			if ($this->can("view", $this_object))
			{
				$this_object = new object($this_object);
			}
			else
			{
				return false;
			}
		}

		$class_id = $this->crmcfg_class_index[$usecase];

		if (!is_array($this->crmcfg_defined_tables[$class_id][$cfg_table_name]))
		{
			return false;
		}

		$table_cfg = $this_object->meta($usecase . "-" . $cfg_table_name);

		if (!is_array($table_cfg))
		{
			return false;
		}

		# apply cfg
		foreach ($table_cfg as $field_name => $field_cfg)
		{
			if ($field_cfg["visible"])
			{
				$table->update_field($field_cfg["definicion"]);
			}
			else
			{
				$table->remove_field($field_name);
			}
		}

		return true;
	}

	// returns array of field names configured in this settings object to be used in specified usecase, false on error.
	function get_visible_fields($this_object, $usecase, $cfg_table_name)
	{
		if (!is_object($this_object))
		{
			if ($this->can("view", $this_object))
			{
				$this_object = new object($this_object);
			}
			else
			{
				return false;
			}
		}

		$class_id = $this->crmcfg_class_index[$usecase];

		if (!is_array($this->crmcfg_defined_tables[$class_id][$cfg_table_name]))
		{
			return false;
		}

		$table_cfg = $this_object->meta($usecase . "-" . $cfg_table_name);

		if (!is_array($table_cfg))
		{
			return false;
		}

		$fields = array();

		foreach ($table_cfg as $field_name => $field_cfg)
		{
			if ($field_cfg["visible"])
			{
				$fields[] = $field_name;
			}
		}

		return $fields;
	}

	function _set_table_cfg($arr)
	{
		list($usecase, $cfg_table_name) = explode("-", $arr["request"]["tables_treemenu_item"], 2);
		$class_id = $this->crmcfg_class_index[$usecase];

		if (!is_array($this->crmcfg_defined_tables[$class_id][$cfg_table_name]))
		{
			return PROP_IGNORE;
		}

		$this_object =& $arr["obj_inst"];
		$table_cfg = array();

		foreach (safe_array ($arr["request"]["tablecfgkey"]) as $field_name)
		{
			$table_cfg[$field_name]["definicion"]["name"] = $field_name;
			$table_cfg[$field_name]["definicion"]["caption"] = trim($arr["request"]["tablecfg-caption"][$field_name]);

			if (strlen(trim($arr["request"]["tablecfg-order"][$field_name])))
			{
				$table_cfg[$field_name]["definicion"]["order"] = (int) $arr["request"]["tablecfg-order"][$field_name];
			}

			$table_cfg[$field_name]["visible"] = (int) $arr["request"]["tablecfg-visible"][$field_name];
		}

		$this_object->set_meta($usecase . "-" . $cfg_table_name, $table_cfg);
		return PROP_OK;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}
		return $retval;
	}

	function callback_mod_retval($arr)
	{
		$arr["args"]["tables_treemenu_item"] = $arr["request"]["tables_treemenu_item"];
	}

	function callback_mod_reforb($arr, $request)
	{
		$arr["post_ru"] = post_ru();

		if ($request["tables_treemenu_item"])
		{
			$arr["tables_treemenu_item"] = $request["tables_treemenu_item"];
		}
	}

	function get_current_settings()
	{
		$u = get_instance(CL_USER);
		$curp = $u->get_current_person();
		$curco = $u->get_current_company();

		$cd = get_instance("applications/crm/crm_data");
		$cursec = $cd->get_current_section();
		$curprof = $cd->get_current_profession();

		$ol = new object_list(array(
			"class_id" => CL_CRM_SETTINGS,
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_SETTINGS.RELTYPE_USER" => aw_global_get("uid_oid"),
					"CL_CRM_SETTINGS.RELTYPE_PERSON" => $curp,
					"CL_CRM_SETTINGS.RELTYPE_COMPANY" => $curco,
					"CL_CRM_SETTINGS.RELTYPE_SECTION" => $cursec,
					"CL_CRM_SETTINGS.RELTYPE_PROFESSION" => $curprof,
					"CL_CRM_SETTINGS.everyone" => 1
				)
			))
		));

		if ($ol->count() > 1)
		{
			// the most accurate setting SHALL Prevail!
			$has_co = $has_p = $has_u = $has_all = $has_sec = $has_prof = false;
			foreach($ol->arr() as $o)
			{
				if ($cursec && $o->is_connected_to(array("to" => $cursec)))
				{
					$has_sec = $o;
				}
				if ($curprof && $o->is_connected_to(array("to" => $curprof)))
				{
					$has_prof = $o;
				}

				if ($o->is_connected_to(array("to" => $curco)))
				{
					$has_co = $o;
				}
				if ($o->is_connected_to(array("to" => $curp)))
				{
					$has_p = $o;
				}
				if ($o->is_connected_to(array("to" => aw_global_get("uid_oid"))))
				{
					$has_u = $o;
				}
				if ($o->prop("everyone"))
				{
					$has_all = $o;
				}
			}

			if ($has_u)
			{
				return $has_u;
			}
			if ($has_p)
			{
				return $has_p;
			}
			if ($has_prof)
			{
				return $has_prof;
			}
			if ($has_sec)
			{
				return $has_sec;
			}
			if ($has_co)
			{
				return $has_co;
			}
			if ($has_all)
			{
				return $has_all;
			}
		}

		if ($ol->count())
		{
			return $ol->begin();
		}
	}

/*{ PUBLIC METHODS */

/**
    @attrib name=delete_table_cfg
	@param id required type=int
	@param table required
	@param return_url optional
**/
	function delete_table_cfg ($arr)
	{
		if ($this->can("view", $arr["id"]))
		{
			list($usecase, $cfg_table_name) = explode("-", $arr["table"], 2);
			$class_id = $this->crmcfg_class_index[$usecase];

			if (is_array($this->crmcfg_defined_tables[$class_id][$cfg_table_name]))
			{
				$this_object = new object($arr["id"]);
				$this_object->set_meta($arr["table"], NULL);
				$this_object->save();
			}
		}

		return $arr["return_url"];
	}

/*} END PUBLIC METHODS */
}
?>
