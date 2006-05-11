<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_bill_row.aw,v 1.2 2006/05/11 14:20:03 kristo Exp $
// crm_bill_row.aw - Arve rida 
/*

@classinfo syslog_type=ST_CRM_BILL_ROW relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=aw_crm_bill_rows
@default group=general

@tableinfo aw_crm_bill_rows index=aw_oid master_index=brother_of master_table=objects

@property name type=textarea rows=5 cols=40 table=objects field=name
@caption Nimi

@property comment type=textbox table=objects field=comment
@caption Tekst arvel

@property amt type=textbox size=5 field=aw_amt
@caption Kogus

@property prod type=relpicker reltype=RELTYPE_PROD field=aw_prod
@caption Toode

@property price type=textbox size=7 field=aw_price
@caption Hind

@property sum type=text store=no
@caption Summa

@property unit type=textbox field=aw_unit
@caption &Uuml;hik

@property is_oe type=checkbox ch_value=1 field=aw_is_oe
@caption Muu kulu?

@property has_tax type=checkbox ch_value=1 field=aw_has_tax
@caption Lisandub k&auml;ibemaks?

@property date type=date_select field=aw_date
@caption Kuup&auml;ev

@reltype PROD value=1 clid=CL_CHOP_PRODUCT
@caption Toode

@reltype TASK_ROW value=2 clid=CL_TASK_ROW
@caption Toimetuse rida

@reltype TASK value=3 clid=CL_TASK
@caption Toimetus
*/

class crm_bill_row extends class_base
{
	function crm_bill_row()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_bill_row",
			"clid" => CL_CRM_BILL_ROW
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "sum":
				$prop["value"] = str_replace(",", ".", $arr["obj_inst"]->prop("amt")) * str_replace(",", ".", $arr["obj_inst"]->prop("price"));
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

	function do_db_upgrade($table, $field, $q, $err)
	{
		if ($table == "aw_crm_bill_rows" && $field == "")
		{
			$this->db_query("create table aw_crm_bill_rows (aw_oid int primary key, aw_amt double,aw_prod int,aw_price double,aw_unit varchar(100),aw_is_oe int,aw_has_tax int ,aw_date int)");
			return true;
		}
	}
}
?>
