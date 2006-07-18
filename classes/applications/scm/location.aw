<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/location.aw,v 1.1 2006/07/18 06:05:17 tarvo Exp $
// location.aw - Asukoht 
/*

@classinfo syslog_type=ST_LOCATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property address type=relpicker reltype=RELTYPE_ADDRESS
@caption Aadress

@property map type=relpicker reltype=RELTYPE_MAP
@caption Asukohakaart

@property photo type=relpicker reltype=RELTYPE_PHOTO
@caption Foto kohast

@reltype MAP value=1 clid=CL_IMAGE
@caption Kaart

@reltype PHOTO value=2 clid=CL_IMAGE
@caption Foto

@reltype ADDRESS value=3 clid=CL_CRM_ADDRESS
@caption Aadress

*/

class location extends class_base
{
	function location()
	{
		$this->init(array(
			"tpldir" => "applications/scm/location",
			"clid" => CL_LOCATION
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

	function get_locations()
	{
		$list = new object_list(array(
			"class_id" => CL_LOCATION,
		));
		return $list->arr();
	}

	function add_location($arr = array())
	{
		$obj = obj();
		$obj->set_parent($arr["parent"]);
		$obj->set_class_id(CL_LOCATION);
		$obj->set_name($arr["name"]);
		$obj->set_prop("address", $arr["address"]);
		$obj->set_prop("map", $arr["map"]);
		$obj->set_prop("photo", $arr["photo"]);
		$oid = $obj->save_new();
		return $oid;
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
