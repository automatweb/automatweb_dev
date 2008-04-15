<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/workbench.aw,v 1.12 2008/04/15 07:08:07 kristo Exp $
// workbench.aw - Töölaud 
/*

@classinfo syslog_type=ST_WORKBENCH relationmgr=yes  maintainer=kristo

@default table=objects
@default group=general

@property preview type=text store=no
@caption Näita

*/

// so, what properties do I need? .. first, I need to specify from which folder the left side menu
// starts .. this will have to be a relation with the type CL_MENU ...

// same thing goes for the right side pane ... and I need to integrate a frameset somewhere
// in here

class workbench extends class_base
{
	function workbench()
	{
		$this->init(array(
			"tpldir" => "workbench",
			"clid" => CL_WORKBENCH
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "preview":
				$data["value"] = html::href(array(
					"url" => $this->mk_my_orb("gen_workbench",array("id" => $arr["obj_inst"]->id())),
					"caption" => $data["caption"],
					"target" => "_blank",
				));
				break;

		};
		return $retval;
	}

	/*
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		}
		return $retval;
	}	
	*/

	/** It should be able to display a decent workbench withoud loading an object 
		
		@attrib name=gen_workbench params=name default="0"
		
		@param id optional type=int
		
		@returns
		
		
		@comment

	**/
	function gen_workbench($arr)
	{
		$this->read_template("default_frameset.tpl");
		// if this is called without arguments, then we generate the default
		// frameset, the one we are used to see at http://site/automatweb

		// but it should now also be relatively easy to create other
		// views for different users
		global $awt;
		$awt->start("meat");
		$this->vars(array(
			"uid" => aw_global_get("uid"),
			"navigator_url" => $this->mk_my_orb("gen_folders",array()),
			"content_url" => admin_if::get_url_for_obj(null),
		));
		print $this->parse();
		$awt->stop("meat");
		print "<pre>";
		print_r($awt->summaries());
		print "</pre>";
		die();
		/*
		print "viewing the workbench<br>";
		print "<pre>";
		print_r($arr);
		print "</pre>";
		*/
	}
}
?>
