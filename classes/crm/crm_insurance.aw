<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_insurance.aw,v 1.3 2007/09/13 14:30:12 markop Exp $
// crm_insurance.aw - Kindlustus 
/*

@classinfo syslog_type=ST_CRM_INSURANCE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property expires type=date_select field=meta method=serialize
@caption Insurance expires

@property certificate type=relpicker field=meta method=serialize reltype=RELTYPE_FILE
@caption Upload insurance certificate

@property company type=relpicker field=meta method=serialize reltype=RELTYPE_COMPANY
@caption Company

@property broker type=relpicker field=meta method=serialize reltype=RELTYPE_BROKER
@caption Broker

@reltype COMPANY value=1 clid=CL_CRM_COMPANY
@caption Company

@reltype BROKER value=2 clid=CL_CRM_PERSON
@caption Broker

@reltype FILE value=3 clid=CL_FILE
@caption File


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
			case "insurance_status":
				$prop["options"] = array("","");
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
