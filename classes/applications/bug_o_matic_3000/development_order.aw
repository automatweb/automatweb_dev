<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/development_order.aw,v 1.1 2006/11/16 15:40:43 kristo Exp $
// development_order.aw - Arendustellimus 
/*

@classinfo syslog_type=ST_DEVELOPMENT_ORDER relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo aw_dev_orders master_table=objects master_index=brother_of index=aw_oid
@default table=aw_dev_orders
@default group=general

	@property customer type=relpicker reltype=RELTYPE_CUSTOMER field=aw_customer
	@caption Klient

	@property project type=relpicker reltype=RELTYPE_PROJECT field=aw_project
	@caption Projekt

	@property content type=textarea rows=20 cols=50 field=aw_content
	@caption Sisu

	@property fileupload type=releditor reltype=RELTYPE_FILE1 rel_id=first use_form=emb field=aw_f1
	@caption Fail1

	@property fileupload2 type=releditor reltype=RELTYPE_FILE2 rel_id=first use_form=emb field=aw_f2
	@caption Fail2

	@property fileupload3 type=releditor reltype=RELTYPE_FILE3 rel_id=first use_form=emb field=aw_f3
	@caption Fail3

@reltype CUSTOMER value=1 clid=CL_CRM_COMPANY
@caption Klient

@reltype PROJECT value=2 clid=CL_PROJECT
@caption Projekt

@reltype FILE1 value=3 clid=CL_FILE
@caption Fail1

@reltype FILE2 value=4 clid=CL_FILE
@caption Fail2

@reltype FILE3 value=5 clid=CL_FILE
@caption Fail3

*/

class development_order extends class_base
{
	function development_order()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/development_order",
			"clid" => CL_DEVELOPMENT_ORDER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
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

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_dev_orders(aw_oid int primary key, aw_customer int, aw_project int, aw_content mediumtext, aw_f1 int, aw_f2 int, aw_f3 int)");
			return true;
		}

		switch($f)
		{
			case "aw_f1":
			case "aw_f2":
			case "aw_f3":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}
?>
