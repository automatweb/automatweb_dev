<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_category.aw,v 1.8 2007/05/04 10:34:55 kristo Exp $
// crm_category.aw - Kategooria 
/*

@tableinfo aw_account_balances master_index=oid master_table=objects index=aw_oid

@classinfo syslog_type=ST_CRM_CATEGORY relationmgr=yes

@default table=objects
@default group=general

@property jrk type=textbox size=5 table=objects field=jrk
@caption J&auml;rjekord

@property img_upload type=releditor reltype=RELTYPE_IMAGE props=file,file_show
@caption Pilt

@property extern_id type=hidden field=meta method=serialize 

//@property jrk type=textbox size=4
//@caption Järk

@property balance type=hidden table=aw_account_balances field=aw_balance

@reltype IMAGE value=1 clid=CL_IMAGE
@caption Pilt

@reltype CATEGORY value=2 clid=CL_CRM_CATEGORY
@caption Alam kategooria

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY
@caption Klient

*/

class crm_category extends class_base
{
	function crm_category()
	{
		$this->init(array(
			"tpldir" => "crm/crm_category",
			"clid" => CL_CRM_CATEGORY
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

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($t == "aw_account_balances" && $f == "")
		{
			$this->db_query("CREATE TABLE $t (aw_oid int primary key, aw_balance double)");
			// also, create entries in the table for each existing object
			$this->db_query("SELECT oid FROM objects WHERE class_id IN (".CL_CRM_CATEGORY.",".CL_CRM_COMPANY.",".CL_PROJECT.",".CL_TASK.",".CL_CRM_PERSON.",".CL_BUDGETING_FUND.",".CL_SHOP_PRODUCT.",".CL_BUDGETING_ACCOUNT.")");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				$this->db_query("INSERT INTO $t(aw_oid, aw_balance) values($row[oid], 0)");
				$this->restore_handle();
			}
			return true;
		}
	}
}
?>
