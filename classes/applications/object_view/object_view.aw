<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/object_view/Attic/object_view.aw,v 1.1 2004/08/17 11:33:40 ahti Exp $
// object_view.aw - Object view 
/*

@classinfo syslog_type=ST_OBJECT_VIEW no_comment=1 relationmgr=yes

@default table=objects
@default group=general


@property object_type type=select
@caption Objekti tyyp

@property object_settings type=callback callback=make_object_setting
@caption Seaded
*/

class object_view extends class_base
{
	function object_view()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/object_view/object_view",
			"clid" => CL_OBJECT_VIEW
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them


	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'object_type':
				$prop['options'] = array(
					CL_PILDID => "pildid",
				);
		};
		return $retval;
	}

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function make_object_settings($arr)
	{
		
	}

	function object_type_is_selected($arr)
	{
		
	}
}
?>
