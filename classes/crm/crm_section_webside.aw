<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_section_webside.aw,v 1.6 2004/10/04 10:06:39 sven Exp $
// crm_section_webside.aw - �Üksus weebis 
/*

@classinfo syslog_type=ST_CRM_SECTION_WEBSIDE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property section_picker type=relpicker reltype=RELTYPE_SECTION method=serialize field=meta table=objects
@caption �ksus

@property show_sub_sections type=checkbox ch_value=1
@caption V�ta alam�ksustest

@property cols type=textbox size=1
@caption Tulpasid

@default group=order

@property persons_order_table type=table no_caption=1 store=no

@default group=view
@default field=meta
@default method=serialize

@property show_label type=text store=1 subtitle=1
@caption Milliseid allj�rgnevaid omadusi n�idata? 


@property show_name type=checkbox ch_value=1 default=1
@caption Nimi

@property show_rank type=checkbox ch_value=1 default=1
@caption Ametikoht

@property show_picture type=checkbox ch_value=1 default=1
@caption Pilt

@property show_phone type=checkbox ch_value=1 default=1
@caption Telefon

@property show_email type=checkbox ch_value=1 default=1
@caption E-mail

@groupinfo order caption="J�rjekord" 
@groupinfo view caption="N�itamine"



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
		$this->submerge=1;
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "persons_order_table":
				$this->do_persons_order_table($arr);
			break;
			
			case "view":
				$prop["options"] = array(
					0 => "Piltidega vaade",
					1 => "Tabelina",
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
	
	function callb_ord($arr)
	{
		return html::textbox(array(
				"name" => "ord[".$arr['person_id']."]",
				"value" => $arr['ord'],
				"size" => 3
		));
	}
	

	
	function do_persons_order_table(&$arr)
	{
		$table = &$arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,	
		));
		
		$table->define_field(array(
			"name" => "ord",
			"caption" => "J�rjekord",
			"sortable" => 1,
			"callback" => array(&$this, "callb_ord"),
			"numeric" => 1,
			"callb_pass_row" => true,
			"align" => "center",
		));
		
		$section = get_instance(CL_CRM_SECTION);
		$workers = $section->get_section_workers($arr["obj_inst"]->id(), true);

		foreach ($workers->arr() as $worker)
		{
			$table->define_data(array(
				"name" => html::get_change_url($worker->id(), array() , $worker->name()),
				"ord" => $worker->ord(),
				"person_id" => $worker->id(),
			));	
		}
		$table->set_default_sortby("ord");
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
	
	function parse_person(&$person, &$ob)
	{
		if($ob->prop("cols") == 1)
		{
			$this->read_template("person_one_col.tpl");
		}
		else
		{
			$this->read_template("person.tpl");
		}
		$this->sub_merge = 1;
		$this->vars(array(
			"name" => "",
			"rank" => "",
			"phone" => "",
			"email" => "",
			"photo" => "",
		));
		
		
		if($ob->prop("show_picture"))
		{
			$img_inst = get_instance(CL_IMAGE);
			$img = $person->get_first_obj_by_reltype("RELTYPE_PICTURE");
			
			if($img)
			{
				$this->vars(array(
					"photo" => html::img(array(
						"url" => $img_inst->get_url_by_id($img->id()),
					)),
				));
			}
			$retval.= $this->parse("SHOW_PHOTO");
		}
		
		$retval .= $this->parse("header");	
		if($ob->prop("show_name"))
		{
			if($doc = $person->get_first_obj_by_reltype("RELTYPE_DESCRIPTION_DOC"))
			{
				$this->vars = array(
					"name" => html::href(array(
						"caption" => $doc->prop("name"),
						"url" => $doc->id(),
					)),
				);
			}
			else
			{
				$this->vars = array(
					"name" => $person->prop("name"),
				);
			}
			
			$retval .= $this->parse("SHOW_NAME");
		}
			
		if($ob->prop("show_rank"))
		{
			if($person->prop("rank"))
			{
				$rank = &obj($person->prop("rank"));
				$this->vars(array(
					"rank" => $rank->name(),
				));
			}
			$retval.= $this->parse("SHOW_RANK");
		}
		
		
		if($ob->prop("show_phone"))
		{
			$phone = &obj($person->prop("phone"));
			$this->vars(array(
				"phone" => $phone->name(),
			));
			
			$retval.= $this->parse("SHOW_PHONE");
		}
		
		if($ob->prop("show_email"))
		{
			$email = &obj($person->prop("email"));
			$this->vars(array(
				"email" => $email->prop("mail"),
			));
			$retval.= $this->parse("SHOW_EMAIL");
		}
		$retval .= $this->parse("footer");

		
		$this->sub_merge = 0;
		
		if($ob->prop("cols") == 1)
		{
			$this->read_template("frame_one_col.tpl");
		}
		else
		{
			$this->read_template("frame.tpl");
		}
		
		//$this->read_template("frame.tpl");
		
		return $retval;
	}
	
	function callback_post_save($arr)
	{
		$section = get_instance(CL_CRM_SECTION);
		$workers = $section->get_section_workers($arr["obj_inst"]->id(), true);
		
		if(is_array($arr["request"]["ord"]))
		{
			foreach ($workers->arr() as $worker)
			{
				$worker->set_ord($arr["request"]["ord"][$worker->id()]);
				$worker->save();
			}
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
		
		if($ob->prop("show_sub_sections"))
		{
			$workers = $section->get_section_workers($ob->id(), true);
		}
		else
		{
			$workers = $section->get_section_workers($ob->id(), false);
		}
		
		$workers->sort_by(array(
        	"prop" => "ord",
        	"order" => "asc"
    	));
    	
		$cols = $ob->prop("cols");
		$workers_count = $workers->count();
		$workers = array_values($workers->arr());
		$rows = ceil($workers_count/$cols);
		
		
		if($ob->prop("cols") == 1)
		{
			$this->read_template("frame_one_col.tpl");
		}
		else
		{
			$this->read_template("frame.tpl");
		}
		//This is amazing... i wrote this and I dont understand how it works, but it does
		for($i=0; $i<$workers_count; $i++)
		{
			$this->vars(array(
				"person" => $this->parse_person($workers[$i], $ob), 
			));
			$persondata[] = $this->parse("persons");
			$cur_row = ceil(($i + 1)/$cols);
			//It is time to parse separ ator now
			
			if((($i + 1)%$cols == 0))
			{
				if(!($cur_row == $rows))
				{
					$persondata[] = $this->parse("separator");
				}
			}
		}
		
		$this->vars(array(
			"personinfo" => join($persondata),
		));
		
		return $this->parse();	
		
	}
}
?>
