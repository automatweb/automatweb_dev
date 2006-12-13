<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/trademark_manager.aw,v 1.2 2006/12/13 15:48:38 markop Exp $
// patent_manager.aw - Kaubam&auml;rgitaotluse keskkond 
/*

@classinfo syslog_type=ST_TRADEMARK_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize
#GENERAL
	@property not_verified_menu type=relpicker reltype=RELTYPE_NOT_VERIFIED_MENU
	@caption Kinnitamata taotluste kaust
	
	@property verified_menu type=relpicker reltype=RELTYPE_VERIFIED_MENU
	@caption Kinnitatud taotluste kaust

	@property series type=relpicker reltype=RELTYPE_SERIES
	@caption Numbriseeria

	@property trademark_add type=relpicker reltype=RELTYPE_ADD
	@caption Kaubam&auml;rgitaotluste lisamine


#TAOTLUSED
@groupinfo name=applications caption=Taotlused
@default group=applications
	
	@property objects_tb type=toolbar no_caption=1 store=no
	
	@layout objects_lay type=hbox width=20%:80%

		@layout objects_l type=vbox parent=objects_lay
			
			@layout trademark_tr_l type=vbox parent=objects_l closeable=1 area_caption=Taotluste&nbsp;puu
				@property trademark_tr type=treeview no_caption=1 store=no parent=trademark_tr_l
			@layout objects_find_params type=vbox parent=objects_l closeable=1 area_caption=Objektide&nbsp;otsing
				@property trademark_find_applicant_name type=textbox store=no parent=objects_find_params captionside=top size=30
				@caption Esitaja nimi
				
				@property trademark_find_procurator_name type=textbox store=no size=30 parent=objects_find_params captionside=top
				@caption Voliniku nimi
				
				@property trademark_find_start type=date_select store=no parent=objects_find_params captionside=top
				@caption Alates
				
				@property trademark_find_end type=date_select store=no parent=objects_find_params captionside=top
				@caption Kuni
				
				@property do_find_applications type=submit store=no parent=objects_find_params captionside=top no_caption=1
				@caption Otsi
		@property objects_tbl type=table no_caption=1 store=no parent=objects_lay


#EKSPORT
@groupinfo name=export caption=Eksport
@default group=export
	
	@property exp_dest type=textbox
	@caption Ekspordifaili asukoht serveris

	@property exp_link type=text
	@caption Ekspordi

#RELTYPES

	@reltype NOT_VERIFIED_MENU clid=CL_MENU value=1
	@caption Kinnitamata taotluste kaust
	
	@reltype VERIFIED_MENU clid=CL_MENU value=2
	@caption Kinnitatud taotluste kaust

	@reltype SERIES clid=CL_CRM_NUMBER_SERIES value=3
	@caption Numbriseeria

	@reltype ADD clid=CL_DOCUMENT value=4
	@caption Kaubam&auml;rgitaotluste lisamine

*/

class trademark_manager extends class_base
{
	function trademark_manager()
	{
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_TRADEMARK_MANAGER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "objects_tb":
				$this->_objects_tb($arr);
				break;
			case "objects_tbl":
				$this->_objects_tbl($arr);
				break;
				
			case "trademark_find_applicant_name":
			case "trademark_find_procurator_name":
			case "trademark_find_start":
			case "trademark_find_end":
				$search_data = $arr["obj_inst"]->meta("search_data");
				$prop["value"] = $search_data[$prop["name"]];
				break;	
			case "exp_link":
				$prop["value"] = html::href(array("url" => "www.delfi.ru" , "caption" => t("EKSPORDI!")));
				
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "trademark_find_applicant_name":	
				$arr["obj_inst"]->set_meta("search_data" , $arr["request"]);
			break;
			//-- set_property --//
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
/*
- vasakus puus: Kinnitamata taotlused, Kinnitatud taotlused

*/
		

	function _get_trademark_tr($arr)
	{
		classload("core/icons");
		//$arr["prop"]["vcl_inst"] = get_instance("vcl/treeview");
		
		$arr["prop"]["vcl_inst"]->start_tree (array (
			"type" => TREE_DHTML,
			"has_root" => 1,
			"tree_id" => "offers_tree",
			"persist_state" => 1,
			"root_name" => t("Taotlused"),
			"root_url" => "#",
//			"get_branch_func" => $this->mk_my_orb("get_tree_stuff",array(
//				"clid" => $arr["clid"], 
//				"group" => $arr["request"]["group"],
//				"oid" => $arr["obj_inst"]->id(),
//				"set_retu" => get_ru(),
//				"parent" => " ",
//			)),
		));

		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => 1,
			"name" => t('Kinnitatud'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "verified",
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => 2,
			"name" => t('Kinnitamata'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "not_verified",
			)),
		));
	}

	function search_applications($this_obj)
	{
		$ol = new object_list();
		$filter = array(
			"class_id" => array(CL_PATENT),
			"lang_id" => array(),
			"site_id" => array(),
		);
		$data = $this_obj->meta("search_data");

		if($data["trademark_find_applicant_name"])
		{
			$filter["CL_PATENT.RELTYPE_APPLICANT.name"] = "%".$data["trademark_find_applicant_name"]."%";
		}
		if($data["trademark_find_procurator_name"])
		{
			$filter["CL_PATENT.RELTYPE_PROCURATOR.name"] = "%".$data["trademark_find_procurator_name"]."%";
		}
	
 		if((date_edit::get_timestamp($data["trademark_find_start"]) > 1)|| (date_edit::get_timestamp($data["trademark_find_end"]) > 1))
 		{
 			if(date_edit::get_timestamp($data["trademark_find_start"]) > 1)
 			{
 				$from = date_edit::get_timestamp($data["trademark_find_start"]);
 			}
 			else
 			{
 				$from = 1;
 			}
 			if(date_edit::get_timestamp($data["trademark_find_end"]) > 1)
 			{
 				$to = date_edit::get_timestamp($data["trademark_find_end"]);
 			}
 			else
 			{
 				$to = time()*66;
 			}
 		 	$filter["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, ($from - 1), ($to + 1));
 		}
		$ol = new object_list($filter);
		return $ol;
	}

	function _objects_tbl($arr)
	{
		$filter = array(
			"class_id" => array(CL_PATENT),
//			"parent" => $parent,
			"lang_id" => array(),
			"site_id" => array()
		);
		
		if($arr["request"]["p_id"] == "verified")
		{
			$filter["verified"] = 1;
		}
//		if($arr["request"]["p_id"] == "not_verified")
//		{
//			$filter["verified"] = new obj_predicate_not(1);
//		}

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_objects_tbl($t);
		
		if(!$arr["request"]["p_id"])
		{
			$filter = null;
		}
		
		//otsingust
		if(sizeof($arr["obj_inst"]->meta("search_data")) > 1)
		{
			$ol = $this->search_applications($arr["obj_inst"]);
			$arr["obj_inst"]->set_meta("search_data", null);
			$arr["obj_inst"]->save();
		}
		else
		{
			$ol = new object_list($filter);
			
		}
		
		$trademark_inst = get_instance(CL_PATENT);
		$person_inst = get_instance(CL_CRM_PERSON);
		$types = $trademark_inst->types;
		foreach($ol->arr() as $o)
		{
			if($arr["request"]["p_id"] == "not_verified" && $o->prop("verified"))
			{
				continue;
			}
			$procurator = $type = $nr = $applicant_name = $applicant_data = $applicant = "";
			$procurator = $o->prop_str("procurator");
			$type = $types[$o->prop("type")];
			if($o->prop("type") == 0 && $o->prop("word_mark"))
			{
				$type.= " (".$o->prop("word_mark").")";
			}
			$nr_str = t("Number puudub");
			if($o->prop("convention_nr"))
			{
				$nr_str = $o->prop("convention_nr");
			}
			$nr = html::href(array(
				"caption" => $nr_str,
				"url" => html::get_change_url($o->id(), array("return_url" => $arr["post_ru"])),
			));
			
			if(!(is_oid($o->prop("applicant")) && ($this->can("view" ,$o->prop("applicant")))))
			{
				$applicant = $o->get_first_obj_by_reltype("RELTYPE_APPLICANT");
			}
			else
			{
				$applicant = obj($o->prop("applicant"));
			}
			if(is_object($applicant))
			{
				$applicant_name = $applicant->name();
				$applicant_data = "";
				if($applicant->class_id() == CL_CRM_PERSON)
				{
					$applicant_data = $person_inst->get_short_description($applicant->id());
				}
				else
				{
					$stuff = array();
					$stuff[] = html::obj_change_url($applicant);
					if(is_object($a_phone = $applicant->get_first_obj_by_reltype("RELTYPE_PHONE")))
					{
						$stuff[] = $a_phone->name();
					}
					
					if(is_object($a_mail = $applicant->get_first_obj_by_reltype("RELTYPE_EMAIL")))
					{
						$stuff[] = $a_mail->name();
					}
					$applicant_data = join("," , $stuff);
				}
			}

			$t->define_data(array(
				"procurator" => $procurator,
				"nr" => $nr,
				"type" => $type,
				"applicant_name" => $applicant_name,
				"applicant_data" => $applicant_data,
				"date" => date("d.m.Y",$o->created()),
				"oid" => $o->id(),
			));
		}
	}

/*
- paremal tabelis: Märgi tüüp (sõnamärk, kujutismärk jne, kui sõnamärk, siis vastava tekstivälja sisu ka sulgudes), Taotluse number (sellel klikkides avaneb ka taotluse sisestusvorm, kui number puudub, siis on klikitav tekst Number puudub), Esitaja nimi, Esitaja kontaktandmed (kõik ühes väljas komaga eraldatult, aadressi pole vaja), voliniku nimi, Esitamise kuupäev, Vali tulp.		
*/
	function _init_objects_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "type",
			"caption" => t("M&auml;rgi t&uuml;&uuml;p"),
			"align" => "center",
			"sortable" => 1
		));
		
		$t->define_field(array(
			"name" => "nr",
			"caption" => t("Taotluse number"),
			"align" => "center",
			"sortable" => 1
		));
		
		$t->define_field(array(
			"name" => "applicant_name",
			"caption" => t("Esitaja nimi"),
			"align" => "center",
			"sortable" => 1
		));
		
		$t->define_field(array(
			"name" => "applicant_data",
			"caption" => t("Esitaja kontaktandmed"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "procurator",
			"caption" => t("Voliniku nimi"),
			"align" => "center",
			"sortable" => 1
		));
		
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Esitamise kuup&aumlev"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"caption" => t("Vali"),
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _objects_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$add_inst = get_instance(CL_TRADEMARK_ADD);
		$new_url = aw_ini_get("baseurl")."/".$arr["obj_inst"] ->prop("trademark_add");
		
		//$add_inst->mk_my_orb("parse_alias", array("alias" => array("target" => $arr["obj_inst"] ->prop("trademark_add")), CL_TRADEMARK_ADD));
		
		//$prop_obj->request_execute($realest_obj);
		
		//$add_inst->mk_my_orb("parse_alias" , array("alias" => array("target" => $arr["obj_inst"] -> prop("trademark_add"))));
		
		$tb->add_button(array(
			'name'=>'add_item',
			"img" => 'new.gif',
			'tooltip'=> t('Lisa taotlus'),
	//		'action' => 'new',
			'url' => $new_url,
	//		'confirm' => t(""),
			"target" => '_blank',
		));
		
		$tb->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta'),
			'url' => "",
	//		'action' => 'delete_procurements',
	//		'confirm' => t(""),
		));
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta'),
			'action' => 'delete_applications',
			'confirm' => t("Kas oled kindel et soovid valitud taotlused kustudada?"),
		));
		$tb->add_button(array(
			'name' => 'refresh',
			'img' => 'refresh.gif',
			'tooltip' => t('V&auml;rskenda'),
			'url' => "",
		//	'action' => 'delete_procurements',
		//	'confirm' => t(""),
		));	
	}
	
	/**
		@attrib name=delete_applications
	**/
	function delete_applications($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
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
