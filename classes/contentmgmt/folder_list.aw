<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/folder_list.aw,v 1.4 2004/05/06 11:19:59 kristo Exp $
// folder_list.aw - Kaustade nimekiri 
/*

@classinfo syslog_type=ST_FOLDER_LIST relationmgr=yes

@default table=objects
@default group=general

@property rootmenu type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Juurkataloog

@property template type=select field=meta method=serialize
@caption Kujundusmall

@property sort_by type=select field=meta method=serialize
@caption Mille j&auml;rgi sortida

@property show_comment type=checkbox ch_value=1 field=meta method=serialize
@caption N&auml;ita kommentaari

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

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "template":
				$tm = get_instance("templatemgr");
				$data["options"] = $tm->template_picker(array(
					"folder" => "contentmgmt/folder_list"
				));
				break;

			case "sort_by":
				$data["options"] = array(
					"objects.name" => "Nimi",
					"objects.jrk" => "J&auml;rjekord"
				);
				break;
		};
		return PROP_OK;
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

		$tpl = "show.tpl";
		if ($ob->prop("template") != "")
		{
			$tpl = $ob->prop("template");
		}

		$sby = "objects.name";
		if ($ob->prop("sort_by") != "")
		{
			$sby = $ob->prop("sort_by");
		}

		$this->read_site_template($tpl);

		$ol = new object_list(array(
			"parent" => $ob->prop("rootmenu"),
			"class_id" => CL_MENU,
			"sort_by" => $sby
		));

		$ssh = get_instance("contentmgmt/site_show");

		$fls = "";
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$this->vars(array(
				"name" => $o->name(),
				"link" => $ssh->make_menu_link($o),
				"selected" => selected($o->id() == aw_global_get("section")),
				"comment" => nl2br($o->comment())
			));

			if ($ob->prop("show_comment"))
			{
				$this->vars(array(
					"SHOW_COMMENT" => $this->parse("SHOW_COMMENT")
				));
			}
			else
			{
				$this->vars(array(
					"SHOW_COMMENT" => ""
				));
			}
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
