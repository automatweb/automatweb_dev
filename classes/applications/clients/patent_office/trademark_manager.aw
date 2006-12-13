<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/trademark_manager.aw,v 1.1 2006/12/13 10:39:26 markop Exp $
// patent_manager.aw - Kaubam&auml;rgitaotluse keskkond 
/*

@classinfo syslog_type=ST_TRADEMARK_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize


#OBJECTS
@groupinfo name=objects caption=Objektid
@default group=objects
	
	@property objects_tb type=toolbar no_caption=1 store=no
	
	@layout objects_lay type=hbox width=20%:80%
	
		@layout objects_l type=vbox parent=objects_lay
			@layout objects_find_params type=vbox parent=objects_l closeable=1 area_caption=Objektide&nbsp;otsing
				@property objects_find_name type=textbox store=no parent=objects_find_params captionside=top
				@caption Nimi
				@property objects_find_address type=textbox store=no parent=objects_find_params captionside=top
				@caption Aadress
				@property objects_find_groups type=select store=no parent=objects_find_params captionside=top
				@caption Hankijagruppid
				@property objects_find_done type=checkbox store=no parent=objects_find_params captionside=top no_caption=1
				@caption Teostanud hankeid
				@property objects_find_start type=date_select store=no parent=objects_find_params captionside=top
				@caption Alates
				@property objects_find_end type=date_select store=no parent=objects_find_params captionside=top
				@caption Kuni
				@property objects_find_product type=textbox store=no parent=objects_find_params captionside=top
				@property do_find_objects type=submit store=no parent=objects_find_params captionside=top no_caption=1
				@caption Otsi
		@property objects_tbl type=table no_caption=1 store=no parent=objects_lay



*/

class trademark_manager extends class_base
{
	function trademark_manager()
	{
		$this->init(array(
			"tpldir" => "applications/clients/patent_office/",
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


	function _objects_tbl($arr)
	{
		$filter = array(
			"class_id" => array(CL_PROCUREMENT_OFFER),
//			"parent" => $parent,
			"lang_id" => array(),
			"site_id" => array()
		);
		
		if(substr_count($arr["request"]["p_id"] , "valid"))
		{
			$filter["accept_date"] = new obj_predicate_compare(OBJ_COMP_GREATER, time());
		}
		if(substr_count($arr["request"]["p_id"], "archived" ))
		{
			$filter["accept_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, 10, time());
		}
		
		//võtab igast jama eest ära... tabelis seda enam vaja pole
		$arr["request"]["p_id"] = str_replace("valid_" , "" , $arr["request"]["p_id"]);
		$arr["request"]["p_id"] = str_replace("archived_" , "" , $arr["request"]["p_id"]);

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_objects_tbl($t);
		
		if(!$arr["request"]["p_id"])
		{
			$filter = null;
		}
		
		if(is_oid($arr["request"]["p_id"]) && $this->can("view", $arr["request"]["p_id"]))
		{
			$p_obj = obj($arr["request"]["p_id"]);
			if($p_obj->class_id() == CL_MENU)
			{
				$offerers = new object_list(array(
					"class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON),
					"lang_id" => array(),
					"site_id" => array(),
					"parent" => $arr["request"]["p_id"],
				));
				$filter["offerer"] = $offerers->ids();
				if(!sizeof($filter["offerer"])) $filter["offerer"] = array(0);
			}
			if($p_obj->class_id() == CL_CRM_PERSON || $p_obj->class_id() == CL_CRM_COMPANY)
			{
				$filter["offerer"] = $arr["request"]["p_id"];
			}
			if($p_obj->class_id() == CL_PROCUREMENT)
			{
				$filter["procurement"] = $arr["request"]["p_id"];
			}
			
			if($p_obj->class_id() == CL_CRM_CATEGORY)
			{
				foreach($p_obj->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
				{
					$filter["offerer"][$c->prop("to")] = $c->prop("to");
				}
			}
			
		}
		
		//otsingust
		if(sizeof($arr["obj_inst"]->meta("search_data")) > 1)
		{
			$ol = $this->search_offers($arr["obj_inst"]);
			$arr["obj_inst"]->set_meta("search_data", null);
			$arr["obj_inst"]->save();
		}
		else
		{
			$ol = new object_list($filter);
		}
		
		$offer_inst = get_instance(CL_PROCUREMENT_OFFER);
		$statuses = $offer_inst->offer_states;
		$result = $arr["request"]["result"];
		foreach($ol->arr() as $o)
		{
//			$offerer_name = html::obj_change_url($o);
			$offerer_area = "";
			if(is_oid($o->prop("offerer")) && $this->can("view" , $o->prop("offerer")))
			{
				$offerer = obj($o->prop("offerer"));
				$offerer_name = html::obj_change_url($offerer);
				if($offerer->class_id() == CL_CRM_COMPANY) $address_id = $offerer->prop("contact");
				if($offerer->class_id() == CL_CRM_PERSON) $address_id = $offerer->prop("address");
				if(is_oid($address_id) && $this->can("view" , $address_id))
				{
					$address = obj($address_id);
					if(is_oid($address->prop("piirkond")) && $this->can("view" , $address->prop("piirkond")))
					{
						$area = obj($address->prop("piirkond"));
						$offerer_area = $area->name();
					}
				}
			}
			
			$files = "";
			$file_ol = new object_list($o->connections_from(array()));
			$file_inst = get_instance(CL_FILE);
			$pm = get_instance("vcl/popup_menu");
			foreach($file_ol->arr() as $file_o)
			{
				if(!(($file_o->class_id() == CL_FILE) || ($file_o->class_id() == CL_CRM_DOCUMENT) || ($file_o->class_id() == CL_CRM_DEAL) || ($file_o->class_id() == CL_CRM_OFFER) || ($file_o->class_id() == CL_CRM_MEMO)))
				{
					continue;
				}

				$pm->begin_menu("sf".$file_o->id());
				if ($file_o->class_id() == CL_FILE)
				{
					$pm->add_item(array(
						"text" => $file_o->name(),
						"link" => file::get_url($file_o->id(), $file_o->name())
					));
				}
				else
				{
					foreach($file_o->connections_from(array("type" => "RELTYPE_FILE")) as $c)
					{
						$pm->add_item(array(
							"text" => $c->prop("to.name"),
							"link" => file::get_url($c->prop("to"), $c->prop("to.name"))
						));
					}
				}
				$files.= $pm->get_menu(array(
					"icon" => icons::get_icon_url($file_o)
				));
			}

			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"date" => date("d.m.Y",$o->created()),//$o->prop("accept_date")),
				"offerer_name" => $offerer_name,
				"area" => $offerer_area,
				"status" => $statuses[$o->prop("state")],
				"sum" => $o->prop("price"),
				"files" => $files,
				"oid" => $o->id(),
			));
		}
	}

	function _init_objects_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimetus"),
			"align" => "center",
			"sortable" => 1
		));
		
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Pakkumise kuup&auml;ev"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "offerer_name",
			"caption" => t("Pakkuja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "area",
			"caption" => t("Piirkond"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Pakkumise staatus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Pakkumise summa"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "files",
			"caption" => t("Pakkumisega seotud failid"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _objects_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$arr["request"]["p_id"] = str_replace("valid_" , "" , $arr["request"]["p_id"]);
		$arr["request"]["p_id"] = str_replace("archived_" , "" , $arr["request"]["p_id"]);

		$parent = $arr["request"]["p_id"] ? $arr["request"]["p_id"] : $arr["obj_inst"]->prop("offerers_folder");

		$proc = $offerer = "";
		if(is_oid($parent))
		{
			$parent_obj = obj($parent);
			if($parent_obj->class_id() == CL_PROCUREMENT)
			{
				$proc = $parent;
			}
			if($parent_obj->class_id() == CL_CRM_COMPANY || $parent_obj->class_id() == CL_CRM_PERSON)
			{
				$offerer = $parent;
			}
		}
		$_SESSION["procurement_offer"]["offerer"] = $offerer;
		$_SESSION["procurement_offer"]["proc"] = $proc;
		$_SESSION["procurement_offer"]["parent"] = $parent;

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Pakkumine'),
//			'link'=> $this->mk_my_orb("insert_offer" , array("return_url" => get_ru(), "parent" => $parent)),
			'action' => "add_procurement_offer"
//			'link'=> html::get_new_url(CL_PROCUREMENT_OFFER, $parent, array(
//				"return_url" => get_ru(),
//				"proc" => $proc,
//				"offerer" => $offerer,
//			))
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Ost'),
//			'link'=> html::get_new_url(CL_PURCHASE, $parent, array("return_url" => get_ru()))
			'action'=> "insert_purchase",
		));

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud hanked'),
			'action' => 'delete_procurements',
			'confirm' => t("Kas oled kindel et soovid valitud pakkumised kustudada?")
		));
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
