<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/workbench.aw,v 1.5 2004/12/01 13:21:57 kristo Exp $
// workbench.aw - Töölaud 
/*

@classinfo syslog_type=ST_WORKBENCH relationmgr=yes

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
			//"content_url" => $this->mk_my_orb("right_frame",array(),"admin_menus"),
			"content_url" => $this->mk_my_orb("right_frame",array(),"admin_menus"),
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

	/**  
		
		@attrib name=gen_folders params=name default="0"
		
		@param id optional type=int
		@param parent optional type=int
		@param period optional
		
		@returns
		
		
		@comment

	**/
	function gen_folders($arr)
	{
		$this->read_template("index_folders.tpl");
		$t = get_instance("languages");
		$afd = get_instance("admin/admin_folders");
		$par = (int)$arr["parent"];
		$afd->use_parent = $par;
		global $awt;
		$awt->start("ng-gen-folders");
		$this->vars(array(
			"charset" => $t->get_charset(),
			"content" => $afd->gen_folders($arr["period"])
		));
		$awt->stop("ng-gen_folders");
		/*print "<!--";
		print_r($awt->summaries());
		print "-->";*/
		die($this->parse());
	}

	// here is the functionality that I need .. some kind of function that returns
	// a list of properties .. which will then be sent to the user	
}
?>
