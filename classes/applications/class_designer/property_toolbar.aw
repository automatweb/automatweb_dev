<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_toolbar.aw,v 1.1 2005/03/03 15:20:55 kristo Exp $
// property_toolbar.aw - Toolbar 
/*

@classinfo syslog_type=ST_PROPERTY_TOOLBAR relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize


@default group=buttons

	@property buttons type=releditor reltype=RELTYPE_BUTTON props=name,b_type no_caption=1

@groupinfo buttons caption="Nupud"

@reltype BUTTON value=1 clid=CL_PROPERTY_TOOLBAR_BUTTON
@caption nupp

*/

class property_toolbar extends class_base
{
	function property_toolbar()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/property_toolbar",
			"clid" => CL_PROPERTY_TOOLBAR
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
}
?>
