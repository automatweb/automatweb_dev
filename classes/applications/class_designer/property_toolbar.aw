<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_toolbar.aw,v 1.2 2005/03/03 18:03:57 kristo Exp $
// property_toolbar.aw - Toolbar 
/*

@classinfo syslog_type=ST_PROPERTY_TOOLBAR relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize


@default group=buttons

	@property buttons type=releditor reltype=RELTYPE_BUTTON props=name,b_type no_caption=1 mode=manager

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
			case "buttons":
				$prop["direct_links"] = 1;
				break;
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

	function get_visualizer_prop($el, &$propdata)
	{
		$t = get_instance("vcl/toolbar");
		
		$buttons = new object_list($el->connections_from(array(
			"type" => RELTYPE_BUTTON
		)));
		foreach($buttons->arr() as $b)
		{
			$i = $b->instance();
			$i->get_button($b, $t);
		}

		$propdata["type"] = "text";
		$propdata["value"] = $t->get_toolbar();
		$propdata["no_caption"] = 1;
	}
}
?>
