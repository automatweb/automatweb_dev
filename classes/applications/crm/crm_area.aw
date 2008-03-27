<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_area.aw,v 1.6 2008/03/27 09:18:04 instrumental Exp $
// crm_area.aw - Piirkond 
/*

@classinfo syslog_type=ST_CRM_AREA relationmgr=yes no_status=1 prop_cb=1 maintainer=markop

@default table=objects
@default group=general

@default field=meta
@property country type=relpicker reltype=RELTYPE_COUNTRY
@caption Riik

@property comment type=textarea cols=40 rows=3 table=objects field=comment
@caption Kommentaar

@reltype COUNTRY value=1 clid=CL_CRM_COUNTRY
@caption Riik

*/

class crm_area extends class_base
{
	function crm_area()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_area",
			"clid" => CL_CRM_AREA
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

	/** Returns object list of personnel_management_job_offer objects that are connected to the area.

	@attrib name=get_job_offers params=name api=1

	@param id required type=oid acl=view

	@param parent optional type=oid,array acl=view

	**/
	function get_job_offers($arr)
	{
		if(!is_array($arr["parent"]) && isset($arr["parent"]))
		{
			$arr["parent"] = array(0 => $arr["parent"]);
		}
		$o = obj($arr["id"]);
		$conns2 = $o->connections_to(array(
			"class" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
		));
		$ret = new object_list();
		foreach($conns2 as $conn2)
		{
			if(!isset($arr["parent"]) || in_array($conn2->conn["from.parent"], $arr["parent"]))
			{
				$ret->add($conn2->conn["from"]);
			}
		}
		return $ret;
	}

//-- methods --//
}
?>
