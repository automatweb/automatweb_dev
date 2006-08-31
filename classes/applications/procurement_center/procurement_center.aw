<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement_center.aw,v 1.6 2006/08/31 14:43:43 markop Exp $
// procurement_center.aw - Hankekeskkond 
/*

@classinfo syslog_type=ST_PROCUREMENT_CENTER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@groupinfo settings caption="Seaded" parent=general
@default group=settings
	
	@property name type=textbox
	@caption Nimetus

	@property owner type=text store=no 
	@caption Omanik

	@property offerers_folder type=relpicker field=meta reltype=RELTYPE_PROCUREMENT_CENTER_FOLDERS
	@caption Pakkujate kataloog


@default group=p

	@property p_tb type=toolbar no_caption=1 store=no
	
	@layout p_l type=hbox width=30%:70%
		
		@property p_tr type=treeview no_caption=1 store=no parent=p_l

		@property p_tbl type=table no_caption=1 store=no parent=p_l

@default group=team

	@property team_tb type=toolbar store=no no_caption=1

	@property team_table type=table store=no no_caption=1

@groupinfo p caption="Hanked" submit=no
@groupinfo team caption="Meeskond" submit=no


@groupinfo offerers caption="Pakkujad"
@default group=offerers
@groupinfo offerers_tree caption="Puuvaates" parent=offerers
@default group=offerers_tree
	
	@property offerers_tb type=toolbar no_caption=1 store=no
	
	@layout offerers_l type=hbox width=30%:70%
		@property offerers_tr type=treeview no_caption=1 store=no parent=offerers_l
		@property offerers_tbl type=table no_caption=1 store=no parent=offerers_l
	

@groupinfo offerers_find caption="Otsing" parent=offerers
@default group=offerers_find
	
	@property offerers_find_tb type=toolbar no_caption=1 store=no
	@layout offerers_find_l type=hbox width=20%:80%
		@layout offerers_find_params type=vbox parent=offerer_find_l parent=offerers_find_l
			@property offerers_find_name type=textbox store=no parent=offerers_find_params
			@caption Nimi
			@property offerers_find_address type=textbox store=no parent=offerers_find_params
			@caption Aadress
			@property offerers_find_groups type=select store=no parent=offerers_find_params
			@caption Hankijagruppid
			@property offerers_find_done type=checkbox store=no parent=offerers_find_params no_caption=1
			@caption Teostanud hankeid
			@property offerers_find_start type=date_select store=no parent=offerers_find_params
			@caption Alates
			@property offerers_find_end type=date_select store=no parent=offerers_find_params
			@caption Kuni
			@property offerers_find_product type=textbox store=no parent=offerers_find_params
			@caption pakutud Toode
			@property offerers_find_only_buy type=checkbox store=no parent=offerers_find_params no_caption=1
			@caption Ainult ostudega
			@property do_find_offerers type=submit store=no value=Otsi parent=offerers_find_params no_caption=1
			@caption Otsi

		@property offerers_find_tbl type=table no_caption=1 store=no parent=offerers_find_l

@groupinfo offers caption="Pakkumised"
@groupinfo offers_tree caption="Puuvaates" parent=offers
@default group=offers_tree
	@layout offers_l type=hbox width=30%:70%
		@property offers_tr type=treeview no_caption=1 store=no parent=offers_l
		@property offers_tbl type=table no_caption=1 store=no parent=offers_l
@groupinfo offers_find caption="Otsing" parent=offers
@default group=offers_find
	@property offers_find_tb type=toolbar no_caption=1 store=no
	@layout offers_find_l type=hbox width=20%:80%
		@layout offers_find_params type=vbox parent=offer_find_l parent=offers_find_l
			@property offers_find_name type=textbox store=no parent=offers_find_params
			@caption Hankija nimetus
			@property offers_find_address type=textbox store=no parent=offers_find_params
			@caption Aadress
			@property offers_find_groups type=select store=no parent=offers_find_params
			@caption Hankijagruppide valik
			@property offers_find_start type=date_select store=no parent=offers_find_params
			@caption Alates
			@property offers_find_end type=date_select store=no parent=offers_find_params
			@caption Kuni
			@property offers_find_product type=textbox store=no parent=offers_find_params
			@caption Pakutud Toode
			@property offers_find_only_buy type=checkbox store=no parent=offers_find_params no_caption=1
			@caption Ainult ostudega
			@property offers_find_archived type=checkbox store=no parent=offers_find_params no_caption=1
			@caption Sh arhiveeritud
			@property do_find_offers type=submit store=no value=Otsi parent=offers_find_params no_caption=1
		
		@property offers_find_tbl type=table no_caption=1 store=no parent=offers_find_l

@groupinfo buyings caption="Ostud"
@default group=buyings
@groupinfo buyings_tree caption="Puuvaates" parent=buyings
@default group=buyings_tree
	@property buyings_tb type=toolbar no_caption=1 store=no	
	@layout buyings_l type=hbox width=30%:70%
		@property buyings_tr type=treeview no_caption=1 store=no parent=buyings_l
		@property buyings_tbl type=table no_caption=1 store=no parent=buyings_l
@groupinfo buyings_find caption="Otsing" parent=buyings
@default group=buyings_find
	@property buyings_find_tb type=toolbar no_caption=1 store=no
	@layout buyings_find_l type=hbox width=20%:80%
		@layout buyings_find_params type=vbox parent=buyings_find_l parent=buyings_find_l
			@property buyings_find_name type=textbox store=no parent=buyings_find_params
			@caption Hankija nimetus
			@property buyings_find_address type=textbox store=no parent=buyings_find_params
			@caption Aadress
			@property buyings_find_groups type=select store=no parent=buyings_find_params
			@caption Hankijagruppide valik
			@property buyings_find_start type=date_select store=no parent=buyings_find_params
			@caption Alates
			@property buyings_find_end type=date_select store=no parent=buyings_find_params
			@caption Kuni
			@property buyings_find_product type=textbox store=no parent=buyings_find_params
			@caption Pakutud Toode
			@property buyings_find_archived type=checkbox store=no parent=buyings_find_params no_caption=1
			@caption Sh arhiveeritud
			@property do_find_buyings type=submit store=no value=Otsi parent=buyings_find_params no_caption=1
		@property buyings_find_tbl type=table no_caption=1 store=no parent=buyings_find_l

@groupinfo products caption="Tooted"
@default group=products
@groupinfo products_tree caption="Puuvaates" parent=products
@default group=products_tree
	@layout products_l type=hbox width=30%:70%
		@property products_tr type=treeview no_caption=1 store=no parent=products_l
		@property products_tbl type=table no_caption=1 store=no parent=products_l
@groupinfo products_find caption="Otsing" parent=products
@default group=products_find
	@property products_find_tb type=toolbar no_caption=1 store=no
	@layout products_find_l type=hbox width=20%:80%
		@layout products_find_params type=vbox parent=products_find_l
			@property products_find_product_name type=textbox store=no parent=products_find_params
			@caption Toote nimetus
			@property products_find_name type=textbox store=no parent=products_find_params
			@caption Hankija
			@property products_find_address type=textbox store=no parent=products_find_params
			@caption Aadress
			@property products_find_groups type=select store=no parent=products_find_params
			@caption Hankijagrupp
			@property products_find_apply type=checkbox store=no parent=products_find_params no_caption=1
			@caption Ainult kehtivad Ostud
			@property do_find_products type=submit store=no value=Otsi parent=products_find_params no_caption=1
		@property products_find_tbl type=table no_caption=1 store=no parent=products_find_l

@reltype MANAGER_CO value=1 clid=CL_CRM_COMPANY
@caption Haldaja firma

@reltype TEAM_MEMBER value=2 clid=CL_CRM_PERSON
@caption Meeskonna liige

@reltype PROCUREMENT_CENTER_FOLDERS value=3 clid=CL_MENU
@caption Hankekeskkonna kataloog

*/

class procurement_center extends class_base
{
	function procurement_center()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/procurement_center",
			"clid" => CL_PROCUREMENT_CENTER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "owner":
				if (is_oid($arr["obj_inst"]->id()))
				{
					$o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MANAGER_CO");
					if (!$o)
					{
						return PROP_IGNORE;
					}
					$prop["value"] = html::obj_change_url($o);
				}
				break;

			case "p_tb":
				$this->_p_tb($arr);
				break;

			case "p_tr":
				$this->_p_tr($arr);
				break;

			case "p_tbl":
				$this->_p_tbl($arr);
				break;

			case "team_tb":
				$i = get_instance(CL_PROCUREMENT_IMPLEMENTOR_CENTER);
				$i->_team_tb($arr);
				break;

			case "team_table":
				$i = get_instance(CL_PROCUREMENT_IMPLEMENTOR_CENTER);
				$i->_team_table($arr);
				break;
			
			case "offerers_tr":
				$this->_offerers_tr($arr);
				break;
				
			case "offers_tr":
				$this->_offers_tr($arr);
				break;
			case "buyings_tr":
				$this->_buyings_tr($arr);
				break;
			case "offerers_find_tbl":
			case "offerers_tbl":
				$this->_offerers_table($arr);
				break;
			case "offerers_find_tb":
			case "offerers_tb":
				$this->_offerers_tb($arr);
				break;
				
			case "offers_tbl":
			case "offers_find_tbl":
				$this->_offers_tbl($arr);
				break;
			case "offers_tb":
			case "offers_find_tb":
				$this->_offers_tb($arr);
				break;
			case "buyings_tb":
			case "buyings_find_tb":
				$this->_buyings_tb($arr);
				break;
			case "buyings_tbl":
			case "buyings_find_tbl":
				$this->_buyings_tbl($arr);
				break;
			case "offerers_find_name":
			case "offerers_find_address":
			case "offerers_find_done":
			case "offerers_find_start":
			case "offerers_find_end":
			case "offerers_find_product":
			case "offerers_find_only_buy":
			case "offers_find_name":
			case "offers_find_address":
			case "offers_find_start":
			case "offers_find_end":
			case "offers_find_product":
			case "offers_find_only_buy":
			case "offers_find_archived":
			case "buyings_find_name":
			case "buyings_find_address":
			case "buyings_find_start":
			case "buyings_find_end":
			case "buyings_find_product":
			case "buyings_find_archived":
			case "products_find_product_name":
			case "products_find_name":
			case "products_find_address":
			case "products_find_apply":
				$search_data = $arr["obj_inst"]->meta("search_data");
				$prop["value"] = $search_data[$prop["name"]];
				break;
			case "offers_find_groups":
			case "offerers_find_groups":
			case "buyings_find_groups":
			case "products_find_groups":
				$search_data = $arr["obj_inst"]->meta("search_data");
				$prop["value"] = $search_data[$prop["name"]];
				$ol = new object_list(array("parent" => $arr["obj_inst"]->prop("offerers_folder") , "class_id" => array(CL_MENU)));
				$prop["options"][""] = "";
				$prop["options"] += $ol->names();

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
			case "team_tb":
				if ($this->can("view", $arr["request"]["add_member"]))
				{
					$arr["obj_inst"]->connect(array("to" => $arr["request"]["add_member"], "type" => "RELTYPE_TEAM_MEMBER"));
				}
				$arr["obj_inst"]->set_meta("team_prices", $arr["request"]["prices"]);
				break;
			case "offerers_find_name":
			case "offers_find_name":
			case "buyings_find_name":
			case "products_find_name":
				$arr["obj_inst"]->set_meta("search_data" , $arr["request"]);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["add_member"] = "0";
	}

	function _p_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$parent = $arr["request"]["p_id"] ? $arr["request"]["p_id"] : $arr["obj_inst"]->id();

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Kataloog'),
			'link'=> html::get_new_url(CL_MENU, $parent, array("return_url" => get_ru()))
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Hange'),
			'link'=> html::get_new_url(CL_PROCUREMENT, $parent, array("return_url" => get_ru()))
		));

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud hanked'),
			'action' => 'delete_procurements',
			'confirm' => t("Kas oled kindel et soovid valitud hanked kustudada?")
		));
	}

	/**
		@attrib name=delete_procurements
	**/
	function delete_procurements($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	function _p_tr($arr)
	{
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "procurement_center",
			),
			"root_item" => $arr["obj_inst"],
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $arr["obj_inst"]->id(),
				"lang_id" => array(),
				"site_id" => array()
			)),
			"var" => "p_id",
			"icon" => icons::get_icon_url(CL_MENU)
		));
	}

	function _offerers_tr($arr)
	{
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "procurement_center_offerers",
			),
			"root_item" => obj($arr["obj_inst"]->prop("offerers_folder")),
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $arr["obj_inst"]->prop("offerers_folder"),
				"lang_id" => array(),
				"site_id" => array()
			)),
			"var" => "p_id",
			"icon" => icons::get_icon_url(CL_MENU)
		));
		
		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => 1,
			"name" => t('Aadressid'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "offerers",
				"p_id" => 1,
			)),
		));
		
		$countrys = new object_list(array(
			"class_id" => CL_CRM_COUNTRY,
			"sort_by" => "name",
			"lang_id" => array(),
		));
		foreach($countrys->names() as $id => $name)
		{
			$arr["prop"]["vcl_inst"]->add_item(1, array(
				"id" => $id,
				"name" => $name,
				"url" => $this->mk_my_orb("change",array(
					"id" => $arr["obj_inst"]->id(),
					"group" => "offerers",
					"country" => $id,
					"p_id" => $id,
					)),
			));
		}
		if(is_oid($arr["request"]["country"]))
		{
			$areas = new object_list(array(
				"class_id" => CL_CRM_AREA,
				"sort_by" => "name",
				"lang_id" => array(),
//				"parent" => $arr["request"]["country_id"]
			));
			foreach($areas->names() as $id => $name)
			{
				$arr["prop"]["vcl_inst"]->add_item($arr["request"]["country"], array(
					"id" => $id,
					"name" => $name,
					"url" => $this->mk_my_orb("change",array(
						"id" => $arr["obj_inst"]->id(),
						"group" => "offerers",
						"country" => $arr["request"]["country"],
						"area" => $id,
						"p_id" => $id,
					)),
				));
			}
		}
		
		if(is_oid($arr["request"]["area"]))
		{
			$city = new object_list(array(
				"class_id" => CL_CRM_CITY,
				"sort_by" => "name",
				"lang_id" => array(),
//				"parent" => $arr["request"]["country_id"]
			));
			foreach($city->names() as $id => $name)
			{
				$arr["prop"]["vcl_inst"]->add_item($arr["request"]["area"], array(
					"id" => $id,
					"name" => $name,
					"url" => $this->mk_my_orb("change",array(
						"id" => $arr["obj_inst"]->id(),
						"group" => "offerers",
						"country" => $arr["request"]["country"],
						"area" => $arr["request"]["area"],
						"city" => $id,
						"p_id" => $id,
					)),
				));
			}
		}
	}

	function _offers_tr($arr)
	{
		classload("core/icons");
		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => 1,
			"name" => t('Kehtivad'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "offers",
				"result" => "valid",
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => 2,
			"name" => t('Arhiveeritud'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "offers",
				"result" => "archived",
			)),
		));
		
		$menu_objects = new object_list(array(
			"class_id" => CL_MENU,
			"parent" => $arr["obj_inst"]->prop("offerers_folder")
			));
		
		$x = 3;
		//Hankijagrupid
		foreach($menu_objects->arr() as $menu)
		{
			
			$ol = new object_list(array(
				"class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON),
				"parent" => $menu->id(),
				"lang_id" => array(),
				"site_id" => array()
			));
			$arr["prop"]["vcl_inst"]->add_item(1, array(
				"id" => $x,
				"name" => $menu->name(),
				"url" => $this->mk_my_orb("change",array(
					"id" => $arr["obj_inst"]->id(),
					"group" => "offers",
					"p_id" => $menu->id(),
					"result" => "valid",
				)),
			));
			$menu_tree_id = $x;	
			$x++;
			
			//hankijad puusse
			foreach($ol->arr() as $o)
			{
				$offers = new object_list(array(
					"class_id" => array(CL_PROCUREMENT_OFFER),
					"offerer" => $o->id(),
					"lang_id" => array(),
					"site_id" => array()
				));
				$sum = sizeof($offers->arr());
				$arr["prop"]["vcl_inst"]->add_item($menu_tree_id, array(
					"id" => $x,
					"name" => $o->name()." (".$sum.")",
					"url" => $this->mk_my_orb("change",array(
						"id" => $arr["obj_inst"]->id(),
						"group" => "offers",
						"p_id" => $o->id(),
						"result" => "valid",
					)),
				));
				$x++;
			}
			
			$arr["prop"]["vcl_inst"]->add_item(2, array(
				"id" => $x,
				"name" => $menu->name(),
				"url" => $this->mk_my_orb("change",array(
					"id" => $arr["obj_inst"]->id(),
					"group" => "offers",
					"p_id" => $menu->id(),
					"result" => "archived",
				)),
			));
			$menu_tree_id = $x;
			$x++;
			
			//hankijad puusse
			foreach($ol->arr() as $o)
			{
				$offers = new object_list(array(
					"class_id" => array(CL_PROCUREMENT_OFFER),
					"offerer" => $o->id(),
					"lang_id" => array(),
					"site_id" => array()
				));
				$sum = sizeof($offers->arr());
				
				$arr["prop"]["vcl_inst"]->add_item($menu_tree_id, array(
					"id" => $x,
					"name" => $o->name()." (".$sum.")",
					"url" => $this->mk_my_orb("change",array(
						"id" => $arr["obj_inst"]->id(),
						"group" => "offers",
						"p_id" => $o->id(),
						"result" => "archived",
					)),
				));
				$x++;
			}
		}
	}

	function search_offers($this_obj)
	{
		$ol = new object_list();
		$filter = array("class_id" => array(CL_PROCUREMENT_OFFER));
		$data = $this_obj->meta("search_data");
		if($data["offers_find_name"]) $filter["CL_PROCUREMENT_OFFER.offerer.name"] = "%".$data["offers_find_name"]."%";
//		if($data["offers_find_groups"]) $filter["CL_PROCUREMENT_OFFER.offerer.parent"] = $data["offers_find_groups"];
		
 		if((date_edit::get_timestamp($data["offers_find_start"]) > 1)|| (date_edit::get_timestamp($data["offers_find_end"]) > 1))
 		{
 			if(date_edit::get_timestamp($data["offers_find_start"]) > 1) $from = date_edit::get_timestamp($data["offers_find_start"]); else $from = 0;
 			if(date_edit::get_timestamp($data["offers_find_end"]) > 1) $to = date_edit::get_timestamp($data["offers_find_end"]); else $to = time()*666;
 		 	$filter["accept_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, ($from - 1), ($to + 1));
 		}
		if(!$data["offers_find_archived"]) $filter["accept_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, 1,  time());

		if($data["offers_find_product"])
		{
			$owner = $this_obj->get_first_obj_by_reltype("RELTYPE_MANAGER_CO");
			if(is_object($owner))
			{
				$procurements = new object_list(array(
					"class_id" => array(CL_PROCUREMENT),
					"parent" => $this_obj->id(),
					"lang_id" => array(),
				));
				foreach($procurements->arr() as $procurement)
				{
					$offers = new object_list(array(
						"class_id" => array(CL_PROCUREMENT_OFFER),
						"CL_PROCUREMENT_OFFER.procurement" => $procurement->id(),
						"lang_id" => array(),
					));
					foreach($offers->arr() as $offer)
					{
						$row_conns = $offer->connections_to(array(
							'reltype' => 1,
							'class' => CL_PROCUREMENT_OFFER_ROW,
						));
						foreach($row_conns as $row_conn)
						{
							if(is_oid($row_conn->prop("from")))$row = obj($row_conn->prop("from"));
							else continue;
							if((substr_count($row->prop("product") , $data["offers_find_product"]) > 0))
							{
								//kui pole seotud ühtegi ostu
								$ps = $offer->connections_to(array(
									'reltype' => 2,
									'class' => CL_PURCHASE,
								));
								if($data["offers_find_only_buy"] && !(sizeof($ps)>0)) break;
								$filter["oid"][$offer->id] = $offer->id();
							}
						}
					}
				}
				
			}
			if(!sizeof($filter["oid"]) > 0) return $ol;
		}
		if($data["offers_find_address"])
		{
			$offerers = new object_list(array(new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
				"CL_CRM_COMPANY.contact.name" => "%".$data["offers_find_address"]."%",
				"CL_CRM_PERSON.address.name" => "%".$data["offers_find_address"]."%",
				)
			))));
			$filter["offerer"] = $offerers->ids();
		}
		$ol = new object_list($filter);
		return $ol;
	}

	function _offers_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_offers_tbl($t);

		$filter = array(
			"class_id" => array(CL_PROCUREMENT_OFFER),
//			"parent" => $parent,
			"lang_id" => array(),
			"site_id" => array()
		);
		
		if(is_oid($arr["request"]["p_id"]) && $this->can("view", $arr["request"]["p_id"]))
		{
			$p_obj = obj($arr["request"]["p_id"]);
			if($p_obj->class_id() == CL_CRM_PERSON || $p_obj->class_id() == CL_CRM_COMPANY) $filter["offerer"] = $arr["request"]["p_id"];
			if($p_obj->class_id() == CL_CRM_MENU)
			{
				$offerers = new object_list(array(
					"class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON),
					"lang_id" => array(),
					"site_id" => array(),
					"parent" => $arr["request"]["p_id"],
				));
				$filter["offerer"] = $offerers->ids();
			}
		}
		
		//otsingust
		if(sizeof($arr["obj_inst"]->meta("search_data")) > 0)
		{
			$ol = $this->search_offers($arr["obj_inst"]);
			$arr["obj_inst"]->set_meta("search_data", null);
			$arr["obj_inst"]->save();
		}
		else $ol = new object_list($filter);
		$offer_inst = get_instance(CL_PROCUREMENT_OFFER);
		$statuses = $offer_inst->offer_states;
		$result = $arr["request"]["result"];
		foreach($ol->arr() as $o)
		{
			if($o->prop("accept_date") < time() && $result == "valid" && $o->prop("accept_date")>0) continue;
			if($o->prop("accept_date") > time() && $result == "archived") continue;
			$offerer_name = html::obj_change_url($o);
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
						$area = obj($address->prop("linn"));
						$offerer_area = $area->name();
					}
				}
			}
			
			$t->define_data(array(
				"date" => date("d.m.Y",$o->prop("accept_date")),
				"name" => $offerer_name,
				"area" => $offerer_area,
				"status" => $statuses[$o->prop("state")],
				"sum" => $o->prop("price"),
				"files" => $o->prop("files"),
				"oid" => $o->id(),
			));
		}
	}

	function _init_offers_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Pakkumise kuupäev"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Hankija nimetus"),
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

	function _offers_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$parent = $arr["request"]["p_id"] ? $arr["request"]["p_id"] : $arr["obj_inst"]->id();

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Pakkumine'),
			'link'=> html::get_new_url(CL_PROCUREMENT_OFFER, $parent, array("return_url" => get_ru()))
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Ost'),
			'link'=> html::get_new_url(CL_PURCHASE, $parent, array("return_url" => get_ru()))
		));

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud hanked'),
			'action' => 'delete_procurements',
			'confirm' => t("Kas oled kindel et soovid valitud hanked kustudada?")
		));
	}
	
	function _buyings_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_buyings_tbl($t);
		$filter = array(
			"class_id" => array(CL_PURCHASE),
			"parent" => $parent,
			"lang_id" => array(),
			"site_id" => array()
		);
		if(is_oid($arr["request"]["p_id"]) && $this->can("view", $arr["request"]["p_id"]))
		{
			$p_obj = obj($arr["request"]["p_id"]);
			if($p_obj->class_id() == CL_CRM_PERSON || $p_obj->class_id() == CL_CRM_COMPANY) $filter["offerer"] = $arr["request"]["p_id"];
			if($p_obj->class_id() == CL_CRM_MENU)
			{
				$offerers = new object_list(array(
					"class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON),
					"lang_id" => array(),
					"site_id" => array(),
					"parent" => $arr["request"]["p_id"],
				));
				$filter["offerer"] = $offerers->ids();
			}
		}
		
		//otsingust
		if(sizeof($arr["obj_inst"]->meta("search_data")) > 0)
		{
			$ol = $this->search_buyings($arr["obj_inst"]);
			$arr["obj_inst"]->set_meta("search_data", null);
			$arr["obj_inst"]->save();
		}
		else $ol = new object_list($filter);
		
		$offer_inst = get_instance(CL_PURCHASE);
		$statuses = $offer_inst->stats;
		
		foreach($ol->arr() as $o)
		{
			$offerer_name = html::obj_change_url($o);
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
						$area = obj($address->prop("linn"));
						$offerer_area = $area->name();
					}
				}
			}
		
			$t->define_data(array(
				"date" => date("d.m.Y",$o->prop("date")),
				"name" => $offerer_name,
				"area" => $offerer_area,
				"status" => $statuses[$o->prop("stat")],
//				"sum" => ,
				"address" => $adress,
				"contacts" => $contacts,
				"oid" => $o->id()
			));
		}
	}
	function search_buyings($this_obj)
	{
		$ol = new object_list();
		$filter = array("class_id" => array(CL_PURCHASE), "lang_id" => array());
		$data = $this_obj->meta("search_data");
//		if($data["buyings_find_name"]) $filter["CL_PURCHASE.RELTYPE_OFFER.name"] = "%".$data["buyings_find_name"]."%";

//		if($data["buyings_find_groups"]) $filter["CL_PURCHASE.offerer.parent"] = $data["buyings_find_groups"];
		
 		if((date_edit::get_timestamp($data["buyings_find_start"]) > 1)|| (date_edit::get_timestamp($data["buyings_find_end"]) > 1))
 		{
 			if(date_edit::get_timestamp($data["buyings_find_start"]) > 1) $from = date_edit::get_timestamp($data["buyings_find_start"]); else $from = 0;
 			if(date_edit::get_timestamp($data["buyings_find_end"]) > 1) $to = date_edit::get_timestamp($data["buyings_find_end"]); else $to = time()*666;
 		 	$filter["CL_PURCHASE.RELTYPE_OFFER.accept_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, ($from - 1), ($to + 1));
 		}
 		
		if(!$data["offers_find_archived"]) $filter["CL_PURCHASE.RELTYPE_OFFER.accept_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, 1,  time());

		if($data["buyings_find_product"])
		{
			$offers = new object_list(array(
				"class_id" => array(CL_PROCUREMENT_OFFER),
				"lang_id" => array(),
			));
			foreach($offers->arr() as $offer)
			{
				$row_conns = $offer->connections_to(array(
					'reltype' => 1,
					'class' => CL_PROCUREMENT_OFFER_ROW,
				));
				foreach($row_conns as $row_conn)
				{
					if(is_oid($row_conn->prop("from")))$row = obj($row_conn->prop("from"));
					else continue;
					if((substr_count($row->prop("product") , $data["offers_find_product"]) > 0))
					{
						$filter["CL_PURCHASE.RELTYPE_OFFER"][$offer->id] = $offer->id();
					}
				}
			}
			if(!sizeof($filter["CL_PURCHASE.RELTYPE_OFFER"]) > 0) return $ol;
		}

		if($data["buyings_find_address"])
		{
			$offerers = new object_list(array(new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
				"CL_CRM_COMPANY.contact.name" => "%".$data["buyings_find_address"]."%",
				"CL_CRM_PERSON.address.name" => "%".$data["buyings_find_address"]."%",
				)
			))));
			$filter["CL_PURCHASE.offerer"] = $offerers->ids();
			if(!(sizeof($filter["CL_PURCHASE.offerer"]) > 0)) return $ol;
		}
		$ol = new object_list($filter);
		return $ol;
	}
	function _buyings_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$parent = $arr["request"]["p_id"] ? $arr["request"]["p_id"] : $arr["obj_inst"]->id();

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Ost'),
			'link'=> html::get_new_url(CL_PURCHASE, $parent, array("return_url" => get_ru()))
		));

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta ost'),
			'action' => 'delete_purchases',
			'confirm' => t("Kas oled kindel et soovid valitud ostud kustudada?")
		));
	}

	function _init_buyings_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Ostu kuupäev"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Hankija nimetus"),
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
			"caption" => t("Ostu staatus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Ostu summa"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "offers",
			"caption" => t("Ostuga seotud pakkumised"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}
	function _buyings_tr($arr)
	{
		classload("core/icons");

		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => $arr["obj_inst"]->prop("offerers_folder"),
			"name" => t('Pakkujad'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "buyings",
				"p_id" => 1,
			)),
		));
		
		$menus = new object_list(array(
			"class_id" => CL_MENU,
			"sort_by" => "name",
			"lang_id" => array(),
			"parent" => $arr["obj_inst"]->prop("offerers_folder"),
		));
		
		foreach($menus->names() as $id => $name)
		{
			$arr["prop"]["vcl_inst"]->add_item($arr["obj_inst"]->prop("offerers_folder"), array(
				"id" => $id,
				"name" => $name,
				"url" => $this->mk_my_orb("change",array(
					"id" => $arr["obj_inst"]->id(),
					"group" => "buyings",
					"p_id" => $id,
					)),
			));
			
			$offerers = new object_list(array(
				"class_id" => array(CL_CRM_PERSON,CL_CRM_COMPANY),
				"sort_by" => "name",
				"lang_id" => array(),
				"parent" => $id,
			));
			
			foreach($offerers->arr() as $offerer)
			{
				$arr["prop"]["vcl_inst"]->add_item($id, array(
					"id" => $offerer->id(),
					"name" => $offerer->name(),
					"url" => $this->mk_my_orb("change",array(
						"id" => $arr["obj_inst"]->id(),
						"group" => "buyings",
						"p_id" => $offerer->id(),
						)),
				));
			}
		}
	}

	function _init_impl_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimetus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "contacts",
			"caption" => t("Kontaktandmed"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _offerers_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_impl_tbl($t);

		if(is_oid($arr["request"]["p_id"]) && $this->can("view", $arr["request"]["p_id"]))
		{
			$p_obj = obj($arr["request"]["p_id"]);
			if($p_obj->class_id() == CL_MENU) $parent = $arr["request"]["p_id"] ? $arr["request"]["p_id"] : $arr["obj_inst"]->prop("impl_folder");
		}
		$ccs = new object_list(array(
			"class_id" => CL_PROCUREMENT_IMPLEMENTOR_CENTER,
			"lang_id" => array(),
			"site_id" => array()
		));
		$cd = array();
		foreach($ccs->arr() as $cc)
		{
			$co = $cc->get_first_obj_by_reltype("RELTYPE_MANAGER_CO");
			if ($co)
			{
				$cd[$co->id()] = $cc;
			}
		}
		//otsingust
		if(sizeof($arr["obj_inst"]->meta("search_data")) > 0)
		{
			$ol = $this->search_offerers($arr["obj_inst"]);
			$arr["obj_inst"]->set_meta("search_data", null);
			$arr["obj_inst"]->save();
		}
		else $ol = new object_list(array(
			"class_id" => array(CL_FOLDER, CL_CRM_COMPANY, CL_CRM_PERSON),
			"parent" => $parent,
			"lang_id" => array(),
			"site_id" => array()
			));
		$p_inst = get_instance(CL_CRM_PERSON);
		foreach($ol->arr() as $o)
		{
			if(($arr["request"]["country"]) && !$this->is_in_area(array("o" => $o, "req" => $arr["request"]))) continue;
			$adress = "";
			$contacts = "";
			if($o->class_id() == CL_CRM_PERSON)
			{
				$contacts = $p_inst->get_short_description($o->id());
				if(is_oid($o->prop("address")) && $this->can("view" , $o->prop("address")))
				{
					
					$address_obj = obj($o->prop("address"));
					$adress = $address_obj->name();
				}
			}
			if($o->class_id() == CL_CRM_COMPANY)
			{
				if(is_oid($o->prop("contact")) && $this->can("view" , $o->prop("contact")))
				{
					$address_obj = obj($o->prop("contact"));
					$adress = $address_obj->name();
				}
				$contacts = $this->get_company_contacts($o);
			}
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"address" => $adress,
				"contacts" => $contacts,
				"oid" => $o->id()
			));
		}
	}

	function search_offerers($this_obj)
	{
		$ol = new object_list();
		$filter = array("class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON));
		$data = $this_obj->meta("search_data");
		if($data["offerers_find_name"]) $filter["name"] = "%".$data["offerers_find_name"]."%";
		if($data["offerers_find_address"]) $filter[] = new object_list_filter(array(
			"logic" => "OR",
			"conditions" => array(
			"CL_CRM_COMPANY.contact.name" => "%".$data["offerers_find_address"]."%",
			"CL_CRM_PERSON.address.name" => "%".$data["offerers_find_address"]."%",
			)
		));
		if($data["offerers_find_groups"]) $filter["parent"] = $data["offerers_find_groups"];
		if($data["offerers_find_done"])
		{
			$owner = $this_obj->get_first_obj_by_reltype("RELTYPE_MANAGER_CO");
			if(is_object($owner))
			{
				$buyings = new object_list(array(
					"class_id" => array(CL_PURCHASE),
					"CL_PURCHASE.RELTYPE_BUYER" => $owner->id(),
					"lang_id" => array(),
				));
				foreach($buyings->arr() as $buying)
				{
					if((!((date_edit::get_timestamp($data["offerers_find_start"] > 1)) && date_edit::get_timestamp($data["offerers_find_start"]) > $buying->prop("date"))) && (!(date_edit::get_timestamp($data["offerers_find_end"]) > 1 && date_edit::get_timestamp($data["offerers_find_end"]) < $buying->prop("date")))) 
					$filter["oid"][$buying->prop("offerer")] = $buying->prop("offerer");
				}
				if(!sizeof($filter["oid"]) > 0) return $ol;
			}
		}
		if($data["offerers_find_product"])
		{
			$owner = $this_obj->get_first_obj_by_reltype("RELTYPE_MANAGER_CO");
			if(is_object($owner))
			{
				if(sizeof($filter["oid"]) > 0)
				{
					$ids = $filter["oid"];
					$filter["oid"] = null;
				}
				$procurements = new object_list(array(
					"class_id" => array(CL_PROCUREMENT),
					"parent" => $this_obj->id(),
					"lang_id" => array(),
				));
				foreach($procurements->arr() as $procurement)
				{
					$offers = new object_list(array(
						"class_id" => array(CL_PROCUREMENT_OFFER),
						"CL_PROCUREMENT_OFFER.procurement" => $procurement->id(),
						"lang_id" => array(),
					));
					foreach($offers->arr() as $offer)
					{
						$row_conns = $offer->connections_to(array(
							'reltype' => 1,
							'class' => CL_PROCUREMENT_OFFER_ROW,
						));
						foreach($row_conns as $row_conn)
						{
							if(is_oid($row_conn->prop("from")))$row = obj($row_conn->prop("from"));
							else continue;
							if((substr_count($row->prop("product") , $data["offerers_find_product"]) > 0)
							&&(!(sizeof($ids) && !in_array($offer->prop("offerer") , $ids))))
							{
								//kui pole seotud ühtegi ostu
								$ps = $offer->connections_to(array(
									'reltype' => 2,
									'class' => CL_PURCHASE,
								));
								if($data["offerers_find_only_buy"] && !(sizeof($ps)>0)) break;
								$filter["oid"][$offer->prop("offerer")] = $offer->prop("offerer");
							}
						}
					}
				}
				
			}
			if(!sizeof($filter["oid"]) > 0) return $ol;
		}
		$ol = new object_list($filter);
		return $ol;
	}

	function is_in_area($args)
	{
		extract($args);
		extract($req);
		if($o->class_id() == CL_CRM_PERSON && is_oid($o->prop("address")) && $this->can("view", $o->prop("address")))
		{
			$adress = obj($o->prop("address"));
		}
		if($o->class_id() == CL_CRM_COMPANY && is_oid($o->prop("contact")) && $this->can("view", $o->prop("contact")))
		{
			$adress = obj($o->prop("contact"));
		}
		if(!is_object($adress)) return false;
		if(is_oid($city))
		{
			if($adress->prop("linn") == $city) return true;
		}
		elseif(is_oid($area))
		{
			if($adress->prop("piirkond")  == $area) return true;
		}
		elseif(is_oid($country))
		{
			if($adress->prop("riik")  == $country) return true;
		}
		return false;
	}

	function get_company_contacts($company)
	{
		$ret = "";
		if(is_oid($company->prop("phone_id")))
		{
			$phone = obj($company->prop("phone_id"));
			$ret .= " " . $phone->name();
		}
		if(is_oid($company->prop("email_id")))
		{
			$email = obj($company->prop("email_id"));
			$ret .= " " . $email->name();
		}
		return $ret;
	}



	function _init_p_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _p_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_p_tbl($t);

		$parent = $arr["request"]["p_id"] ? $arr["request"]["p_id"] : $arr["obj_inst"]->id();

		$ol = new object_list(array(
			"class_id" => CL_PROCUREMENT,
			"parent" => $parent,
			"lang_id" => array(),
			"site_id" => array()
		));
		$t->data_from_ol($ol, array("change_col" => "name"));
	}
	
	function _offerers_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$parent = $arr["request"]["p_id"] ? $arr["request"]["p_id"] : $arr["obj_inst"]->prop("impl_folder");

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Kataloog'),
			'link'=> html::get_new_url(CL_MENU, $parent, array("return_url" => get_ru()))
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Pakkuja/Organisatsioon'),
			'link'=> html::get_new_url(CL_CRM_COMPANY, $parent, array(
				"return_url" => get_ru(),
				"pseh" => aw_register_ps_event_handler(
						CL_PROCUREMENT_CENTER, 
						"handle_impl_submit", 
						array("id" => $arr["obj_inst"]->id()),
						CL_CRM_COMPANY
				)
			))
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Pakkuja/eraisik'),
			'link'=> html::get_new_url(CL_CRM_PERSON, $parent, array(
				"return_url" => get_ru(),
				"pseh" => aw_register_ps_event_handler(
						CL_PROCUREMENT_CENTER, 
						"handle_impl_submit", 
						array("id" => $arr["obj_inst"]->id()),
						CL_CRM_PERSON
				)
			))
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Pakkumine'),
			'link'=> html::get_new_url(CL_PROCUREMENT_OFFER, $parent, array(
				"return_url" => get_ru(),
				"pseh" => aw_register_ps_event_handler(
						CL_PROCUREMENT_CENTER, 
						"handle_impl_submit", 
						array("id" => $arr["obj_inst"]->id()),
						CL_PROCUREMENT_OFFER
				)
			))
		));

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud pakkujad'),
			'action' => 'delete_cos',
			'confirm' => t("Kas oled kindel et soovid valitud pakkujad kustudada?")
		));
	}
	
	function handle_impl_submit($new_obj, $arr)
	{
		// so here we need to set a bunch of stuff for the company to work right
		// people are users, groups and stuff
/*		$new_obj->set_prop("do_create_users", 1);
		$new_obj->save();

		// apply the group creator
			// seems it is applied automatically

		// create a procurement center for it
		$pc = obj();
		$pc->set_parent($new_obj->id());
		$pc->set_class_id(CL_PROCUREMENT_IMPLEMENTOR_CENTER);
		$pc->set_name(sprintf(t("%s pakkumiste keskkond"), $new_obj->name()));
		$pc->save();
		$pc->connect(array(
			"to" => $new_obj->id(),
			"type" => "RELTYPE_MANAGER_CO"
		));

		// define an user redirect url for the company group
		$co_grp = $new_obj->get_first_obj_by_reltype("RELTYPE_GROUP");

		$cfg = get_instance("config");		
		$es = $cfg->get_simple_config("login_grp_redirect");
		$this->dequote(&$es);
		$lg = aw_unserialize($es);
		$lg[$co_grp->prop("gid")]["pri"] = 1000000;
		$lg[$co_grp->prop("gid")]["url"] = html::get_change_url($pc->id(), array("group" => "p"));

		$ss = aw_serialize($lg, SERIALIZE_XML);
		$this->quote(&$ss);
		$cfg->set_simple_config("login_grp_redirect", $ss);
	*/
	}
	
	/**
		@attrib name=delete_cos
	**/
	function delete_cos($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}
}
?>
