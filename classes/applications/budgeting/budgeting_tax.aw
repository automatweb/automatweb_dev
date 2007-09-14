<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/budgeting/budgeting_tax.aw,v 1.5 2007/09/14 12:38:37 kristo Exp $
// budgeting_tax.aw - Eelarvestamise maks 
/*

@classinfo syslog_type=ST_BUDGETING_TAX relationmgr=yes no_comment=1 no_status=1 prop_cb=1

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
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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
	}
}
?>
