<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_section_webside.aw,v 1.1 2004/09/15 06:29:14 sven Exp $
// crm_section_webside.aw - ÃÃœksus weebis 
/*

@classinfo syslog_type=ST_CRM_SECTION_WEBSIDE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property section_picker type=relpicker reltype=RELTYPE_SECTION method=serialize field=meta table=objects
@caption Üksus

@property show_sub_sections type=checkbox ch_value=1
@caption Võta alamüksustest

@property view type=chooser
@caption Näitamine

@property cols type=textbox
@caption Tulpasid

@reltype SECTION value=1 clid=CL_CRM_SECTION
@caption &uuml;ksus
*/

class crm_section_webside extends class_base
{
	function crm_section_webside()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "crm/crm_section_webside",
			"clid" => CL_CRM_SECTION_WEBSIDE
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
			case "view":
				$prop["options"] = array(
					0 => "Tabeli vaade",
					1 => "Ühel lehel",
				);
			break;
			case "cols":
				if(!$prop["value"])
				{
					$prop["value"] = 2;
				}
			break;
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

	function parse_person($person)
	{
		$this->read_template("show_table.tpl");
		
		$this->vars(array(
			"email" => "",
			"phone" => "",
			"rank" => "",
			"photo" => "",
		));
		
		if(is_oid($person->prop("email")))
		{
			$email = &obj($person->prop("email"));
			$this->vars(array(
				"email" => $email->prop("mail"),
			));
		}
	
		if(is_oid($person->prop("rank")))
		{
			$rank = &obj($person->prop("rank"));
			$this->vars(array(
				"rank" => $rank->prop("name"),
			));
			//rank;
		}
		
		if(is_oid($person->prop("phone")))
		{
			$phone = &obj($person->prop("phone"));
			
			$this->vars(array(
				"phone" => $phone->prop("name"),
			));
		}
		
		if($img = $person->get_first_obj_by_reltype("RELTYPE_PICTURE"))
		{
			$img_inst = get_instance(CL_IMAGE);
			
			
			$this->vars(array(
				"photo" => html::img(array(
					"url" => $img_inst->get_url_by_id($img->id()),
				)),
			));
		}
		
		$this->vars(array(
			"name" => $person->prop("name"),
		));
		return  $this->parse();
	}
	
	function generate_tableview(&$ob)
	{
		$section = get_instance(CL_CRM_SECTION);
		//$company_inst = get_instance(CL_CRM_COMPANY);
		$workers = &$section->get_section_workers($ob->id(), true);
		
		foreach ($workers->arr() as $worker)
		{
			
		}
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
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = &obj($arr["id"]);
		$section = get_instance(CL_CRM_SECTION);
		$workers = $section->get_section_workers($ob->id(), true);
		
		$cols = $ob->prop("cols");
		
		if(!is_numeric($cols))
		{
			$cols = 2;
		}
		
		$ob = new object($arr["id"]);
		if($ob->prop("view") == 0)
		{
			$this->read_template("frame.tpl");
			foreach ($workers->arr() as $worker)
			{
				$cur_col++;
				$this->vars(array(
					"person" => $this->parse_person($worker),
				));
				$this->read_template("frame.tpl");
				$person_list .= $this->parse("persons");
				//Parse new row
				if($cur_col == $cols)
				{
					$person_list.= $this->parse("ROW_SEP");
					$cur_col = 0;	
				}
			}
			$this->vars(array(
				"persons" => $person_list,
			));			
		}
		return $this->parse();
	}
}
?>
