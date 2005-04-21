<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/stat_report/Attic/stat_report.aw,v 1.6 2005/04/21 08:48:48 kristo Exp $
// stat_report.aw - Stati aruannete upload 
/*

@classinfo syslog_type=ST_STAT_REPORT relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property filesize type=textbox size=6 group=settings
@caption Faili max. suurus (KB)

@property whitelist type=textarea cols=40 rows=20 group=settings
@caption Lubatud laiend (iga laiend eraldi real)

@property final type=textarea cols=60 rows=10 group=settings
@caption Õnnestunud uploadi kviteeriv tekst

@groupinfo settings caption=Seadistamine

@reltype REPORT_TYPE value=1 clid=CL_META
@caption Aruannete tyybid




*/

class stat_report extends class_base
{
	function stat_report()
	{
		$this->init(array(
			"clid" => CL_STAT_REPORT
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {
			case "filesize":
				$data["value"] = (int)$data["value"];
				break;

		}
		return $retval;
	}	
}
?>
