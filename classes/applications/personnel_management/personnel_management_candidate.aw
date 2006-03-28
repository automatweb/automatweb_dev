<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_candidate.aw,v 1.2 2006/03/28 11:52:05 ahti Exp $
// personnel_management_candidate.aw - Kandidatuur
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_CANDIDATE relationmgr=yes r2=yes no_comment=1 no_status=1 allow_rte=2

@default group=general
@default table=objects
@default field=meta

@property person type=relpicker reltype=RELTYPE_PERSON method=serialize
@caption Isik

@property intro_file type=releditor reltype=RELTYPE_FILE rel_id=first props=file,filename method=serialize
@caption Kaaskiri failina

@property intro type=textarea field=comment cols=80 rows=40 richtext=1
@caption Kaaskiri tekstina

@reltype PERSON value=1 clid=CL_CRM_PERSON
@caption Kandideerja

@reltype FILE value=2 clid=CL_FILE
@caption Kaaskiri failina

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

	/**
		@attrib name=view_intro
		@param id required type=int
	**/
	function view_intro($arr)
	{
		$obj = obj($arr["id"]);
		$intro = $obj->prop("intro");
		if(!empty($intro))
		{
			return $intro;
		}
		return "kaaskiri puudub";
	}
}
?>
