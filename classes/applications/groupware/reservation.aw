<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/reservation.aw,v 1.2 2006/10/13 16:13:53 markop Exp $
// reservation.aw - Broneering 
/*

@tableinfo planner index=id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_RESERVATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

#GENERAL
	
@property start1 type=datetime_select field=start table=planner
@caption Algus

@property end type=datetime_select table=planner
@caption L&otilde;peb

@property deadline type=datetime_select table=planner field=deadline
@caption T&auml;htaeg
		
@property customer type=relpicker table=planner field=customer reltype=RELTYPE_CUSTOMER
@caption Klient
			
@property project type=relpicker table=planner field=project reltype=RELTYPE_PROJECT
@caption Projekt

@property send_bill type=checkbox ch_value=1 table=planner field=send_bill 
@caption Saata arve

@property bill_no type=hidden table=planner 
@caption Arve number
	
@property content type=textarea no_caption=1 cols=50 rows=5 field=description table=planner
@caption Sisu

@property code type=hidden size=5 table=planner field=code
@caption Kood

property summary type=textarea cols=80 rows=30 table=planner field=description no_caption=1
caption Kokkuvõte

@tableinfo planner index=id master_table=objects master_index=brother_of

#RELTYPES

@reltype CUSTOMER value=1 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype PROJECT value=2 clid=CL_PROJECT
@caption Projekt

*/

class reservation extends class_base
{
	function reservation()
	{
		$this->init(array(
			"tpldir" => "applications/groupware/reservation",
			"clid" => CL_RESERVATION
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
		if($_GET["calendar"]) 
		{
			$arr["calendar"] = $_GET["calendar"];
		}
	}

	function callback_post_save($arr)
	{
		if($arr["new"]==1 && is_oid($arr["request"]["calendar"]) && $this->can("view" , $arr["request"]["calendar"]))
		{
			$cal = obj($arr["request"]["calendar"]);
			$cal->connect(array(
				"to" => $arr["obj_inst"]->id(),
				"reltype" => "RELTYPE_EVENT"
			));
		}
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
