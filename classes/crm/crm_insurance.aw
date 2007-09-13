<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_insurance.aw,v 1.2 2007/09/13 12:33:26 markop Exp $
// crm_insurance.aw - Kindlustus 
/*

@classinfo syslog_type=ST_CRM_INSURANCE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property expires type=date_select
@caption Insurance expires

@property status type=select
@caption Insurance status

@property certificate type=fileupload form=+emb
@caption Upload insurance certificate

@property company type=relpicker field=meta method=serialize reltype=RELTYPE_COMPANY
@caption Company

@property broker type=relpicker field=meta method=serialize reltype=RELTYPE_BROKER
@caption Broker

@reltype COMPANY value=1 clid=CL_CRM_COMPANY
@caption Company

@reltype ORDER value=20 clid=CL_CRM_PERSON
@caption Broker

*/

class crm_insurance extends class_base
{
	function crm_insurance()
	{
		$this->init(array(
			"tpldir" => "crm/crm_insurance",
			"clid" => CL_CRM_INSURANCE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
