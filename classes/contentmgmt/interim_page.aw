<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/interim_page.aw,v 1.2 2006/01/26 13:20:10 markop Exp $
// interim_page.aw - Intermim page 
/*

@classinfo syslog_type=ST_INTERIM_PAGE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property template type=select rel=1 field=meta method=serialize
@caption Template

@property register_data type=relpicker reltype=RELTYPE_REGISTER_DATA
@caption Registri andmed

@property cfg_form type=relpicker reltype=RELTYPE_CFGFORM
@caption Seadete vorm

@reltype REGISTER_DATA value=1 clid=CL_REGISTER_DATA
@caption Registri andmed

@reltype CFGFORM value=2 clid=CL_CFGFORM
@caption Seadete vorm

*/

class interim_page extends class_base
{
	function interim_page()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "contentmgmt/interim_page",
			"clid" => CL_INTERIM_PAGE
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
/*			case "template":
				$dir = $this->site_template_dir;
				$template_list = array();
				sort($template_list);
				if(is_dir($dir))
				{
					$d = dir($dir);
					while (false !== ($entry = $d->read())) {
						if(substr($entry, -4) == ".tpl")
						{
							$template_list[] = $entry;
						}
					}
					$d->close();
				}
				$prop["options"] = array();
				foreach($template_list as $filename){
					$prop["options"][$filename] = t($filename);
				}
				break;			
			//-- get_property --//*/
			case "template":
				$tm = get_instance("templatemgr");
				$prop["options"] = $tm->template_picker(array(
					"folder" => "contentmgmt/interim_page"
				));
				break;		
			case "register_data":
				if (!$arr["obj_inst"]->prop("cfg_form"))
				{
					return PROP_IGNORE;
				}
				break;		
			case "cfg_form":
				$mem_list = new object_list(array(
					"class_id" => CL_CFGFORM,
					"subclass" => CL_REGISTER_DATA,
				));
				$prop["options"] = array("" => " --vali-- ");
				$member_list = array();
				foreach($mem_list->arr() as $mem)
				{
					$member_list[$mem->id()] = $mem->name();
				}
				$prop["options"] = $prop["options"] + $member_list;
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
			//-- set_property --//
			case "register_data":
				if($arr["prop"]["value"])
				{
					$register_data_obj = obj($arr["prop"]["value"]);
					$register_data_obj -> set_meta("cfgform_id" , $arr["request"]["cfg_form"]);
				}
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		$targ = obj($arr["alias"]["target"]);
		enter_function("interim_page::parse_alias");
		$tpl = $targ->prop("template");	
		$this->read_template($tpl);
		lc_site_load("interim_page", &$this);
		$connections = $targ->connections_from();
		$register_id = $targ->prop("register_data");
		if(is_oid($register_id))
		{
			$register_obj = obj($register_id);
			$this->vars($register_obj->properties());
		}
		exit_function("interim_page::parse_alias");
//		return $this->show(array("id" => $arr["alias"]["target"]));
		return $this->parse();		
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

//-- methods --//
}
?>
