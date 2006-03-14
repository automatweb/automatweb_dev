<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_candidate.aw,v 1.1 2006/03/14 12:28:27 ahti Exp $
// personnel_management_candidate.aw - Kandidatuur
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_CANDIDATE relationmgr=yes r2=yes no_comment=1

@default group=general
@default table=objects
@default field=meta
@default method=serialize

@groupinfo candidate caption="Kandideerimised" submit=no
@default group=candidate

@property candidate_toolbar type=toolbar no_caption=1

@property candidate_table type=table no_caption=1
*/

class personnel_management_candidate extends class_base
{
	function personnel_management_candidate()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_PERSONNEL_MANAGEMENT_CANDIDATE
		));
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch ($prop["name"])
		{
		}
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
		return $retval;
	}
}
?>
