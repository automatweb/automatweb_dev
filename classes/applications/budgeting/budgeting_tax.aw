<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/budgeting/budgeting_tax.aw,v 1.7 2007/11/23 14:28:43 kristo Exp $
// budgeting_tax.aw - Eelarvestamise maks 
/*

@classinfo syslog_type=ST_BUDGETING_TAX relationmgr=yes no_comment=1 no_status=1 prop_cb=1 mantainer=kristo

@tableinfo aw_budgeting_tax master_table=objects master_index=brother_of index=aw_oid

@default table=aw_budgeting_tax
@default group=general

	@property from_place type=textbox field=aw_from_place
	@caption Kust

	@property to_acct type=relpicker field=aw_to_acct reltype=RELTYPE_TO_ACCT
	@caption Kontole

	@property amount type=textbox size=5 field=aw_amt
	@caption Summa (Kui l&otilde;peb % m&auml;rgiga, siis protsentides)

	@property max_deviation_minus type=textbox size=5 field=aw_max_deviation_minus
	@caption Maksimaalne projektip&otilde;hine muudatus -

	@property max_deviation_plus type=textbox size=5 field=aw_max_deviation_plus
	@caption Maksimaalne projektip&otilde;hine muudatus +

	@property pri type=textbox size=5 field=aw_pri
	@caption Prioriteet

	@property when_type type=chooser field=aw_when_type
	@caption Aja t&uuml;&uuml;p

	@property when_date type=datetime_select default=-1 field=aw_when_date
	@caption Millal

	@property penalty_pct type=textbox size=5 field=aw_penalty_pct
	@caption Viivis (%)

	@property tax_grp type=relpicker  field=aw_tax_grp automatic=1 reltype=RELTYPE_TAX_GRP
	@caption Maksugrupp

	@property tax_scenario type=relpicker  field=aw_tax_scenario automatic=1 reltype=RELTYPE_SCENARIO
	@caption Stsenaarium

@groupinfo locs caption="Kust" submit=no
@default group=locs

	@property from_place_toolbar type=toolbar store=no no_caption=1
	@caption Kust toolbar
	
	@property from_place_table type=table store=no no_caption=1
	@caption Kust
	

@reltype FROM_ACCT value=1 clid=CL_BUDGETING_ACCOUNT
@caption Kontolt

@reltype TO_ACCT value=2 clid=CL_BUDGETING_ACCOUNT,CL_CRM_PERSON,CL_CRM_COMPANY,CL_CRM_SECTOR,CL_PROJECT,CL_BUDGETING_FUND
@caption Kontole

@reltype TAX_GRP value=3 clid=CL_BUDGETING_TAX_GROUP
@caption Maksugrupp

@reltype SCENARIO value=4 clid=CL_BUDGETING_SCENARIO
@caption Stsenaarium
*/

class budgeting_tax extends class_base
{
	function budgeting_tax()
	{
		$this->init(array(
			"tpldir" => "applications/budgeting/budgeting_tax",
			"clid" => CL_BUDGETING_TAX
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "from_place":
				if ($arr["request"]["place"])
				{
					$prop["value"] = $arr["request"]["place"];
				}
				else
				{
					$prop["type"] = "text";
					$m = get_instance("applications/budgeting/budgeting_model");
					$prop["value"] = /*$prop["value"]." ".*/$m->get_cat_id_description($prop["value"]);
					$prop["value"] .= " ".html::popup(array(
						"url" => $this->mk_my_orb("select_tax_cat", array("id" => $arr["obj_inst"]->id()), "budgeting_workspace"),
						"caption" => html::img(array(
							"url" => aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif",
							"border" => 0
						)),
						"scrollbars" => "auto"
					));
				}
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "from_place":
				if (strlen($arr["request"]["set_tax_fld"]) < 2)
				{
					return PROP_IGNORE;
				}
				$prop["value"] = $arr["request"]["set_tax_fld"];
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["set_tax_fld"] = "0";
		foreach($this->_add_vars as $var)
		{
			$arr[$var] = "0";
		}
	}

	function do_db_upgrade($t,$f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_budgeting_tax (aw_oid int primary key,aw_from_acct int, aw_to_acct int,aw_amt double,aw_when_type int,aw_when_date int, aw_penalty_pct double)");
			return true;
		}

		switch($f)
		{
			case "aw_from_place":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(255)"
				));
				return true;

			case "aw_pri":
			case "aw_tax_grp":
			case "aw_tax_scenario":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;

			case "aw_max_deviation":
			case "aw_max_deviation_minus":
			case "aw_max_deviation_plus":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "double"
				));
				return true;
		}
	}

	function _get_when_type($arr)
	{
		$arr["prop"]["options"] = $this->get_when_types();
	}

	function get_when_types()
	{
		return array(
			1 => t("&Uuml;hekordne"),
			2 => t("Korduv")
		);
	}

	function callback_post_save($arr)
	{	
		$ol = new object_list(array(
			"class_id" => CL_BUDGETING_TAX_FOLDER_RELATION,
			"tax" => $arr["obj_inst"]->id()
		));
		if (!$ol->count())
		{
			$o = obj();
			$o->set_parent($arr["obj_inst"]->id());
			$o->set_class_id(CL_BUDGETING_TAX_FOLDER_RELATION);
			$o->set_name(sprintf(t("Seos maksu %s ja kausta %s vahel"), $arr["obj_inst"]->name(), $arr["obj_inst"]->prop("from_place")));
			$o->set_prop("tax", $arr["obj_inst"]->id());
			$o->set_prop("folder", $arr["obj_inst"]->prop("from_place"));
			$o->save();
		}
		else
		{
			$o = $ol->begin();
			$o->set_prop("folder", $arr["obj_inst"]->prop("from_place"));
			$o->save();
		}

		foreach($arr["request"] as $k => $v)
		{
			if (substr($k, 0, 7) == "taxfld_" && strlen($v) > 1)
			{
				list(, $_id) = explode("_", $k);
				$o = obj($_id);
				$o->set_prop("folder", $v);
				$o->save();
			}
		}
	}

	function _init_from_place_table(&$t)
	{
		$t->define_field(array(
			"name" => "hfrom",
			"caption" => t("Kust"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "hedit",
			"caption" => t("Muuda"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _get_from_place_table($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_BUDGETING_TAX_FOLDER_RELATION,
			"lang_id" => array(),
			"site_id" => array(),
			"tax" => $arr["obj_inst"]->id()
		));
		$t = new vcl_table();
		$this->_init_from_place_table($t);

		$m = get_instance("applications/budgeting/budgeting_model");
		foreach($ol->arr() as $o)
		{
			$this->_add_vars[] = "taxfld_".$o->id();
			$t->define_data(array(
				"hfrom" => $m->get_cat_id_description($o->prop("folder")),
				"hedit" => html::popup(array(
					"url" => $this->mk_my_orb("select_tax_cat", array("id" => $arr["obj_inst"]->id(), "var" => "taxfld_".$o->id()), "budgeting_workspace"),
					"caption" => html::img(array(
						"url" => aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif",
						"border" => 0
					)),
					"scrollbars" => "auto"
				)),
				"oid" => $o->id()
			));
		}
		$arr["prop"]["vcl_inst"] = $t;
	}

	function _get_from_place_toolbar($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"action" => "create_new_rel"
		));
		$tb->add_delete_button();
	}

	/**
		@attrib name=create_new_rel
	**/
	function create_new_rel($arr)
	{
		$f = obj($arr["id"]);

		$o = obj();
		$o->set_parent($arr["id"]);
		$o->set_class_id(CL_BUDGETING_TAX_FOLDER_RELATION);
		$o->set_name(sprintf(t("Seos maksu %s ja kausta %s vahel"), $f->name(), ""));
		$o->set_prop("tax", $f->id());
		$o->set_prop("folder", "");
		$o->save();

		return $arr["post_ru"];
	}
}
?>
