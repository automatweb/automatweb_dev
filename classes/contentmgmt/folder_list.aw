<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/folder_list.aw,v 1.2 2002/12/24 15:07:01 kristo Exp $
// folder_list.aw - Kaustade nimekiri 
/*

@classinfo syslog_type=ST_FOLDER_LIST relationmgr=yes

@default table=objects
@default group=general

@property rootmenu type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Juurkataloog

@reltype FOLDER clid=CL_MENU value=1
@caption juurkataloog

*/

class folder_list extends class_base
{
	function folder_list()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/folder_list",
			"clid" => CL_FOLDER_LIST
		));
	}

	/*
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}
	*/

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

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");

		$ol = new object_list(array(
			"parent" => $ob->prop("rootmenu"),
			"class_id" => CL_MENU,
			"sort_by" => "objects.name"
		));

		$ssh = get_instance("contentmgmt/site_show");

		$fls = "";
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$this->vars(array(
				"name" => $o->name(),
				"link" => $ssh->make_menu_link($o),
				"selected" => selected($o->id() == aw_global_get("section"))
			));

			$fls .= $this->parse("FOLDER");
		}

		$rm = obj($ob->prop("rootmenu"));

		$this->vars(array(
			"FOLDER" => $fls,
			"root_name" => $rm->prop("name"),
		));
		return $this->parse();
	}
}
?>
