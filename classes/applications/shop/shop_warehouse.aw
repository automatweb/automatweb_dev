<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_warehouse.aw,v 1.40 2006/04/11 11:58:44 kristo Exp $
// shop_warehouse.aw - Ladu 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_POPUP_SEARCH_CHANGE,CL_SHOP_WAREHOUSE, on_popup_search_change)


@tableinfo aw_shop_warehouses index=aw_oid master_table=objects master_index=brother_of

@classinfo syslog_type=ST_SHOP_WAREHOUSE relationmgr=yes

@default table=objects
@default group=general

@property conf type=relpicker reltype=RELTYPE_CONFIG table=aw_shop_warehouses field=aw_config 
@caption Konfiguratsioon

@property order_center type=relpicker reltype=RELTYPE_ORDER_CENTER table=objects field=meta method=serialize
@caption Tellimiskeskkond tellimuste jaoks

/////////// products tab
@groupinfo products caption="Tooted" submit=no

@property products_toolbar type=toolbar no_caption=1 group=products store=no

@property products_list type=text store=no group=products no_caption=1 
@caption Toodete nimekiri 

/////////// productgroups tab
@groupinfo productgroups caption="Tootegrupid" submit=no

@property productgroups_toolbar type=toolbar no_caption=1 group=productgroups store=no

@property productgroups_list type=table store=no group=productgroups no_caption=1 
@caption Tootegruppide nimekiri 

/////////// packets tab
@groupinfo packets caption="Paketid" submit=no

@property packets_toolbar type=toolbar no_caption=1 group=packets store=no

@property packets_list type=text store=no group=packets no_caption=1
@caption Pakettide nimekiri

/////////// storage tab
@groupinfo storage caption="Laoseis"
@groupinfo storage_storage parent=storage caption="Laoseis" submit=no
@groupinfo storage_income parent=storage caption="Sissetulekud" 
@groupinfo storage_export parent=storage caption="V&auml;ljaminekud"

@property storage_list type=table store=no group=storage_storage no_caption=1
@caption Laoseis

@property storage_income_toolbar type=toolbar no_caption=1 group=storage_income store=no

@property storage_income type=table store=no group=storage_income no_caption=1
@caption Sissetulekud

@property storage_export_toolbar type=toolbar no_caption=1 group=storage_export store=no

@property storage_export type=table store=no group=storage_export no_caption=1
@caption V&auml;ljaminekud

////////// ordering tab
@groupinfo order caption="Tellimused"
@groupinfo order_unconfirmed parent=order caption="Kinnitamata"
@groupinfo order_confirmed parent=order caption="Kinnitatud"
@groupinfo order_orderer_cos parent=order caption="Tellijad"
@groupinfo order_search parent=order caption="Otsing" submit_method=get

@property order_unconfirmed_toolbar type=toolbar no_caption=1 group=order_unconfirmed store=no
@property order_unconfirmed type=table store=no group=order_unconfirmed no_caption=1

@property order_confirmed_toolbar type=toolbar no_caption=1 group=order_confirmed store=no
@property order_confirmed type=table store=no group=order_confirmed no_caption=1

@layout hbox_oc type=hbox group=order_orderer_cos 

@property order_orderer_cos_tree type=text store=no parent=hbox_oc group=order_orderer_cos no_caption=1
@property order_orderer_cos type=table store=no parent=hbox_oc group=order_orderer_cos no_caption=1

@property osearch_uname type=textbox group=order_search store=no
@caption Tellija kasutajanimi

@property osearch_pname type=textbox group=order_search store=no
@caption Tellija isikunimi

@property osearch_oname type=textbox group=order_search store=no
@caption Organisatsiooni nimi

@property osearch_oid type=textbox size=8 group=order_search store=no
@caption Tellimuse ID

@property osearch_prodname type=textbox group=order_search store=no
@caption Toote nimi

@property osearch_from type=chooser group=order_search store=no
@caption Otsi staatuse järgi

@property osearch_odates type=date_select group=order_search store=no
@caption Tellimuse ajavahemik (alates)

@property osearch_odatee type=date_select group=order_search store=no
@caption Tellimuse ajavahemik (kuni)

@property osearch_hidden type=hidden value=1 group=order_search store=no
@caption Otsing

@property osearch_submit type=submit group=order_search
@caption Otsi

@property osearch_table type=table group=order_search no_caption=1
@caption Tulemuste tabel

// search tab
@groupinfo search caption="Otsing" submit_method=get

@groupinfo search_search caption="Otsing" parent=search submit_method=get

@default group=search_search

@property search_tb type=toolbar store=no no_caption=1
@caption Otsingu toolbar

@property search_form type=callback callback=callback_get_search_form submit_method=get store=no
@caption Otsinguvorm

@property search_res type=table store=no no_caption=1
@caption Otsingu tulemused

@property search_cur_ord_text type=text store=no no_caption=1
@caption Hetke tellimus text

@property search_cur_ord type=table store=no no_caption=1
@caption Hetke tellimus tabel


@groupinfo order_current parent=search caption="Pakkumine"

@property order_current_toolbar type=toolbar no_caption=1 group=order_current store=no
@property order_current_table type=table store=no group=order_current no_caption=1

@property order_current_org type=popup_search field=meta method=serialize group=order_current clid=CL_CRM_COMPANY
@caption Tellija organisatsioon

@property order_current_person type=popup_search field=meta method=serialize group=order_current clid=CL_CRM_PERSON
@caption Tellija isik

@property order_current_form type=callback callback=callback_get_order_current_form store=no group=order_current
@caption Tellimuse info vorm


////////// reltypes
@reltype CONFIG value=1 clid=CL_SHOP_WAREHOUSE_CONFIG
@caption konfiguratsioon

@reltype PRODUCT value=2 clid=CL_SHOP_PRODUCT
@caption toode

@reltype PACKET value=2 clid=CL_SHOP_PACKET
@caption pakett

@reltype STORAGE_INCOME value=3 clid=CL_SHOP_WAREHOUSE_RECEPTION
@caption lao sissetulek

@reltype STORAGE_EXPORT value=4 clid=CL_SHOP_WAREHOUSE_EXPORT
@caption lao v&auml;jaminek

@reltype ORDER value=5 clid=CL_SHOP_ORDER
@caption tellimus

@reltype ORDER_CENTER value=6 clid=CL_SHOP_ORDER_CENTER
@caption tellimiskeskkond

@reltype EMAIL value=7 clid=CL_ML_MEMBER
@caption saada tellimused

@reltype CFGMANAGER value=8 clid=CL_CFGMANAGER
@caption Seadete haldur
*/

class shop_warehouse extends class_base
{
	function shop_warehouse()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_warehouse",
			"clid" => CL_SHOP_WAREHOUSE
		));
	}
	
	function callback_on_load($arr)
	{
		if(is_oid($arr["request"]["id"]) && $this->can("view", $arr["request"]["id"]))
		{
			$obj = obj($arr["request"]["id"]);
			if($cfgmanager = $obj->get_first_conn_by_reltype("RELTYPE_CFGMANAGER"))
			{
				$this->cfgmanager = $cfgmanager->prop("to");
			}
		}
	}

	function get_property($arr)
	{
		if (!$this->_init_view($arr))
		{
			return PROP_OK;
		}
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "osearch_from":
				$prop["options"] = array(
					0 => t("k&otilde;ik"),
					1 => t("kinnitatud"),
					2 => t("kinnitamata"),
				);
			case "osearch_uname":
			case "osearch_pname":
			case "osearch_oname":
			case "osearch_oid":
			case "osearch_prodname":
			case "osearch_odates":
			case "osearch_odatee":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;
				
			case "osearch_table":
				$this->do_search_tbl($arr);
				break;
				
			case "products_toolbar":
				$this->mk_prod_toolbar($arr);
				break;

			case "products_list":
				$this->do_prod_list($arr);
				break;

			case "productgroups_toolbar":
				$this->mk_prodg_toolbar($arr);
				break;

			case "productgroups_list":
				$this->do_prodg_list($arr);
				break;
				
			case "packets_toolbar":
				$this->mk_pkt_toolbar($arr);
				break;

			case "packets_list":
				$this->do_pkt_list($arr);
				break;

			case "storage_list":
				$this->do_storage_list_tbl($arr);
				break;

			case "storage_income_toolbar":
				$this->mk_storage_income_toolbar($arr);
				break;

			case "storage_income":
				$this->do_storage_income_tbl($arr);
				break;

			case "storage_export_toolbar":
				$this->mk_storage_export_toolbar($arr);
				break;

			case "storage_export":
				$this->do_storage_export_tbl($arr);
				break;

			case "order_unconfirmed_toolbar":
				$this->mk_order_unconfirmed_toolbar($arr);
				break;

			case "order_unconfirmed":
				$this->do_order_unconfirmed_tbl($arr);
				break;

			case "order_confirmed_toolbar":
				$this->mk_order_confirmed_toolbar($arr);
				break;

			case "order_confirmed":
				$this->do_order_confirmed_tbl($arr);
				break;

			case "order_orderer_cos":
				$this->do_order_orderer_cos_tbl($arr);
				break;

			case "order_orderer_cos_tree":
				$this->do_order_orderer_cos_tree($arr);
				break;

			case "order_current_toolbar":
				$this->do_order_cur_tb($arr);
				break;

			case "order_current_table":
			case "search_cur_ord":
				$this->save_ord_cur_tbl($arr);
				$this->do_order_cur_table($arr);
				break;

			case "search_res":
				$this->do_search_res_tbl($arr);
				break;

			case "search_tb":
				$this->do_search_tb($arr);
				break;
	
			case "search_cur_ord_text":
				$data["value"] = t("<br><br>Hetkel pakkumises olevad tooted:");
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "storage_income":
				$this->save_storage_inc_tbl($arr);
				break;

			case "storage_export":
				$this->save_storage_exp_tbl($arr);
				break;

			case "order_unconfirmed":
				$this->save_order_unconfirmed_tbl($arr);
				break;

			case "products_list":
				$this->do_del_prod($arr);
				break;

			case "order_current_table":
			case "search_cur_ord":
				$this->save_ord_cur_tbl($arr, true);
				break;

			case "order_current_org":
				if ($arr["obj_inst"]->prop("order_current_org") != $arr["request"]["order_current_org"])
				{
					$this->upd_ud = true;
				}
				break;

			case "order_current_person":
				if ($arr["obj_inst"]->prop("order_current_person") != $arr["request"]["order_current_person"])
				{
					$this->upd_ud = true;
				}
				break;
		}
		return $retval;
	}	

	function save_ord_cur_tbl($arr, $is_post = false)
	{
		$oc = obj($arr["obj_inst"]->prop("order_center"));
		$soc = get_instance(CL_SHOP_ORDER_CART);
		$awa = new aw_array($arr["request"]["quant"]);
		foreach($awa->get() as $iid => $quantx)
		{
			$quantx = new aw_array($quantx);
			foreach($quantx->get() as $x => $quant)
			{
				$soc->set_item(array(
					"iid" => $iid,
					"quant" => $quant,
					"oc" => $oc,
					"it" => $x,
				));
			}
		}

		if ($is_post)
		{
			// also, if we got a discount element, save that as well
			$soc = get_instance(CL_SHOP_ORDER_CENTER);

			$arr["obj_inst"]->set_meta(
				"order_cur_discount", 
				$soc->get_discount_from_order_data($arr["obj_inst"]->prop("order_center"), $arr["request"]["user_data"])
			);

			$arr["obj_inst"]->set_meta("order_cur_pages", $arr["request"]["pgnr"]);
		}
	}
	
	function do_search_tbl($arr)
	{
		$srch = $arr["request"];
		if(!$srch["osearch_hidden"])
		{
			return;
		}
		$fields = array(
			"id" => t("Tellimus"),
			"prodname" => t("Toode"),
			"uname" => t("Kasutaja"),
			"pname" => t("Isik"),
			"oname" => t("Organisatsioon"),
			"odate" => t("Telliti"),
		);
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);
		foreach($fields as $key => $val)
		{
			$t->define_field(array(
				"name" => $key,
				"caption" => $val,
				"sortable" => 1,
			));
		}
		if($srch["osearch_uname"])
		{
			$z .= " AND user.name LIKE '%".$srch["osearch_uname"]."%'";
		}
		if($srch["osearch_pname"])
		{
			$z .= " AND isik.name LIKE '%".$srch["osearch_pname"]."%'";
		}
		if($srch["osearch_oname"])
		{
			$z .= " AND com.name LIKE '%".$srch["osearch_pname"]."%'";
		}
		if($srch["osearch_oid"])
		{
			$z .= " AND objects.oid = '".$srch["osearch_oid"]."'";
		}
		if(is_array($srch["osearch_odates"]))
		{
			$d = $srch["osearch_odates"];
			$d_ts = mktime(0, 0, 0, $d["month"], $d["day"], $d["year"]);
			if ($d_ts > 100)
			{
				$z .= " AND objects.created > ".$d_ts;
			}
		}
		if(is_array($srch["osearch_odatee"]))
		{
			$d = $srch["osearch_odatee"];
			$d_ts = mktime(23, 59, 59, $d["month"], $d["day"], $d["year"]);
			if ($d_ts > 30000)
			{
				$z .= " AND objects.created < ".$d_ts;
			}
		}
		if($srch["osearch_from"] == 1)
		{
			$z .= " AND so.confirmed = 1";
		}
		elseif($srch["osearch_from"] == 2)
		{
			$z .= " AND so.confirmed = 0";
		}

		$lim = " LIMIT 200 ";
		if ($srch["osearch_prodname"] != "")
		{
			$lim = "";
		}
		$q = "
			SELECT
				objects.oid AS id,
				isik.name AS pname,
				isik.oid AS pname_id,
				user.name AS uname,
				user.oid AS uname_id,
				com.name AS oname,
				com.oid AS oname_id,
				objects.created AS odate,
				so.confirmed as confirmed
			FROM 
				objects
				LEFT JOIN aw_shop_orders so ON (so.aw_oid = objects.oid)
				LEFT JOIN objects isik ON (so.aw_orderer_person = isik.oid)
				LEFT JOIN objects com ON (so.aw_orderer_company = com.oid)
				LEFT JOIN aliases isik2user ON (isik2user.target = isik.oid AND isik2user.reltype = 2)
				LEFT JOIN users ON (isik2user.source = users.oid)
				LEFT JOIN objects user ON (users.oid = user.oid)
			WHERE
				objects.status > 0 AND
				isik.status > 0 AND
				com.status > 0 AND
				user.status > 0 AND
				objects.parent = ".$this->order_fld."
				$z
				GROUP BY objects.created DESC
				$lim	
		";
		$this->db_query($q);
		$vars = array("return_url" => get_ru());
		//$t->table_header = t("<center>Leiti ".$this->num_rows()." kirjet</center>");
		$mt = 0;
		while($w = $this->db_next())
		{
			$this->save_handle();
			if($srch["osearch_prodname"])
			{
				$z2 = " AND objects.name LIKE '%".$srch["osearch_prodname"]."%'";
			}
			$q2 = "
			SELECT objects.name AS name, objects.oid AS id
			FROM objects 
				LEFT JOIN aw_shop_products prod ON (objects.oid = prod.aw_oid)
				LEFT JOIN aliases order2prod ON (order2prod.target = objects.oid AND order2prod.reltype = 1)
			WHERE
				objects.oid > 0 AND
				order2prod.source = '".$w["id"]."'
				$z2
			";
			$this->db_query($q2);
			
			if($this->num_rows() == 0)
			{
				$this->restore_handle();
				continue;
			}
			$e = array();
			while($w2 = $this->db_next())
			{
				$e[$w2["id"]] = html::get_change_url($w2["id"], $vars, $w2["name"]);
			}
			$this->restore_handle();
			$t->define_data(array(
				"id" => html::get_change_url($w["id"], $vars, $w["id"]),
				"pname" => html::get_change_url($w["pname_id"], $vars, $w["pname"]),
				"uname" => html::get_change_url($w["uname_id"], $vars, $w["uname"]),
				"oname" => html::get_change_url($w["oname_id"], $vars, $w["oname"]),
				"prodname" => implode(", ", $e),
				"odate" => date("d-m-Y", $w["odate"]),
			));
			$mt++;
		}
		$t->table_header = t("<center>Leiti ".$mt." kirjet</center>");
		
	}

	function do_order_cur_tb($data)
	{
		$tb =& $data["prop"]["toolbar"];

		/*$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta"),
			"url" => "javascript:document.changeform.submit()"
		));*/

		$url = $this->mk_my_orb("gen_order", array("id" => $data["obj_inst"]->id(), "html" => 1));
		$url = "window.open('$url','offer','width=700,height=600,toolbar=0,location=0,menubar=1,scrollbars=1')";
		$tb->add_button(array(
			"name" => "confirm",
			"img" => "pdf_upload.gif",
			"tooltip" => t("Genereeri HTML pakkumine"),
			"onClick" => $url,
			"url" => "#"
		));

		$tb->add_button(array(
			"name" => "mail",
			"img" => "save.gif",
			"tooltip" => t("Saada meilile"),
			"action" => "send_cur_order"
		));

		$tb->add_button(array(
			"name" => "clear",
			"img" => "new.gif",
			"tooltip" => t("Uus pakkumine"),
			"action" => "clear_order"
		));
	}

	function _init_order_cur_table(&$t)
	{
		if ($_GET["group"] == "order_current")
		{
			$t->define_field(array(
				"name" => "page",
				"caption" => t("Lehek&uuml;lg"),
				"align" => "center"
			));
		}

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));

		$t->define_field(array(
			"name" => "quantity",
			"caption" => t("Kogus"),
			"align" => "center"
		));
	}

	function do_order_cur_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_order_cur_table($t);

		$pgnr = $arr["obj_inst"]->meta("order_cur_pages");

		// stick the order in the table
		$soc = get_instance(CL_SHOP_ORDER_CART);
		$soc->get_cart(obj($arr["obj_inst"]->prop("order_center")));
		foreach($soc->get_items_in_cart() as $iid => $quant)
		{
			$item = obj($iid);
			$t->define_data(array(
				"page" => html::textbox(array(
					"name" => "pgnr[$iid]",
					"value" => $pgnr[$iid],
					"size" => 5
				)),
				"name" => html::href(array(
					"caption" => $item->name(),
					"url" => $this->mk_my_orb("change", array("id" => $iid), $item->class_id())
				)),
				"quantity" => html::textbox(array(
					"name" => "quant[$iid]",
					"value" => is_array($quant) ? $quant[0]["items"] : $quant,
					"size" => 5
				))
			));
		}

		$t->set_default_sortby("page");
		$t->sort_by();
	}

	function do_del_prod($arr)
	{
		$awa = new aw_array($arr["request"]["sel"]);
		foreach($awa->get() as $oid)
		{
			$o = obj($oid);
			$o->delete();
		}
	}

	function mk_prod_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "crt_".$this->prod_type_fld,
			"tooltip" => t("Uus")
		));

		$this->_req_add_itypes($tb, $this->prod_type_fld, $data);

		$tb->add_menu_item(array(
			"parent" => "crt_".$this->prod_type_fld,
			"text" => t("Lisa kaust"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->prod_tree_root,
				"return_url" => get_ru(),
			), CL_MENU)
		));


		$tb->add_button(array(
			"name" => "del",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud"),
			"url" => "javascript:document.changeform.submit()"
		));

		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Lisa korvi"),
			"action" => "add_to_cart"
		));
	}
	
	function mk_prodg_toolbar(&$prop)
	{
		$tb =& $prop["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus tootegrupp"),
			"url" => $this->mk_my_orb("new", array(
				"parent" => $this->prod_type_fld,
				"return_url" => get_ru(),
				"cfgform" => $this->prod_type_cfgform,
			), CL_WEBFORM),
		));
		
		$tb->add_button(array(
			"name" => "del",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud"),
			"action" => "remove_prod_type",
		));
	}
	
	function do_prodg_list($arr)
	{
		$this->_init_do_prodg_list($arr);
		$t = &$arr["prop"]["vcl_inst"];
		$ol = new object_list(array(
			"parent" => $this->prod_type_fld,
			"class_id" => CL_WEBFORM,
		));
		foreach($ol->arr() as $obj)
		{
			$t->define_data(array(
				"id" => $obj->id(),
				"name" => $obj->name(),
				"change" => html::get_change_url($obj->id(), array(
					"group" => "form",
					"return_url" => get_ru(),
				), t("Muuda")),
			));
		}
	}
	
	function _init_do_prodg_list($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
		));
		$t->define_chooser(array(
			"field" => "id",
		));
	}

	function _req_add_itypes(&$tb, $parent, &$data)
	{
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => array(CL_MENU, CL_SHOP_PRODUCT_TYPE),
			"lang_id" => array(),
			"site_id" => array()
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() != CL_MENU)
			{
				$tb->add_menu_item(array(
					"parent" => "crt_".$parent,
					"text" => $o->name(),
					"link" => $this->mk_my_orb("new", array(
						"item_type" => $o->id(),
						"parent" => $this->prod_tree_root,
						"alias_to" => $data["obj_inst"]->id(),
						"reltype" => 2, //RELTYPE_PRODUCT,
						"return_url" => get_ru(),
						"cfgform" => $o->prop("sp_cfgform"),
						"object_type" => $o->prop("sp_object_type")
					), CL_SHOP_PRODUCT)
				));
			}
			else
			{
				$tb->add_sub_menu(array(
					"parent" => "crt_".$parent,
					"name" => "crt_".$o->id(),
					"text" => $o->name()
				));
				$this->_req_add_itypes($tb, $o->id(), $data);
			}
		}
	}

	function do_prod_list(&$arr)
	{
		// this has tree on left, table on right
		$this->read_template("prod_list.tpl");

		$this->vars(array(
			"tree" => $this->_prod_list_tree($arr),
			"list" => $this->_prod_list_list($arr)
		));

		$arr["prop"]["value"] = $this->parse();
	}

	function _prod_list_tree(&$arr)
	{
		$ot = new object_tree(array(
			"parent" => $this->config->prop("prod_fld"),
			"class_id" => CL_MENU,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
			"sort_by" => "objects.jrk"
		));
		
		classload("vcl/treeview");
		$tv = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "prods",
				"persist_state" => true,
			),
			"root_item" => obj($this->config->prop("prod_fld")),
			"ot" => $ot,
			"var" => "tree_filter"
		));

		return $tv->finalize_tree();
	}

	function _prod_list_list(&$arr)
	{
		classload("vcl/table");
		$tb = new aw_table(array("layout" => "generic"));
		$this->_init_prod_list_list_tbl($tb);

		// get items 
		if (!$_GET["tree_filter"])
		{
			$ot = new object_list();
		}
		else
		{
			$ot = new object_list(array(
				"parent" => $_GET["tree_filter"],
				"class_id" => array(CL_MENU,CL_SHOP_PRODUCT),
				"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
			));
		}

		classload("core/icons");

		//$ol = $ot->to_list();
		$ol = $ot->arr();
		foreach($ol as $o)
		{

			if ($o->class_id() == CL_MENU)
			{
				$tp = t("Kaust");
			}
			else
			if (is_oid($o->prop("item_type")))
			{
				$tp = obj($o->prop("item_type"));
				$tp = $tp->name();
			}
			else
			{
				$tp = "";
			}

			$get = "";
			if ($o->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => t("V&otilde;ta laost")
				));
			}

			$put = "";
			if ($o->class_id() != CL_MENU)
			{
				$put = html::href(array(
					"url" => $this->mk_my_orb("create_reception", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => t("Vii lattu")
				));
			}

			$name = $o->path_str(array("to" => $this->prod_fld));
			if ($o->class_id() == CL_MENU)
			{
				$name = html::href(array(
					"url" => aw_url_change_var("tree_filter", $o->id()),
					"caption" => $name
				));
			}

			$tb->define_data(array(
				"icon" => html::img(array("url" => icons::get_icon_url($o->class_id(), $o->name()))),
				"name" => $name,
				"cnt" => $o->prop("item_count"),
				"item_type" => $tp,
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"return_url" => get_ru()
					), $o->class_id()),
					"caption" => t("Muuda")
				)),
				"get" => $get,
				"put" => $put,
				"del" => html::checkbox(array(
					"name" => "sel[]",
					"value" => $o->id()
				)),
				"is_menu" => ($o->class_id() == CL_MENU ? 0 : 1),
				"ord" => html::textbox(array(
					"name" => "set_ord[".$o->id()."]",
					"value" => $o->ord(),
					"size" => 5
				)).html::hidden(array(
					"name" => "old_ord[".$o->id()."]",
					"value" => $o->ord()
				)),
				"hidden_ord" => $o->ord()
			));
		}

		$tb->set_numeric_field("hidden_ord");				
		$tb->set_default_sortby(array("is_menu", "hidden_ord"));
		$tb->sort_by();

		return $tb->draw(array(
			"pageselector" => "text",
			"records_per_page" => 50,
			"has_pages" => 1
		));
	}

	function _init_prod_list_list_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t("&nbsp;"),
			"sortable" => 0,
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "item_type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "cnt",
			"caption" => t("Kogus laos"),
			"align" => "center",
			"type" => "int"
		));

		$t->define_field(array(
			"name" => "get",
			"caption" => t("V&otilde;ta laost"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "put",
			"caption" => t("Vii lattu"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "del",
			"caption" => "<a href='javascript:aw_sel_chb(document.changeform,\"sel\")'>".t("Vali")."</a>",
			"align" => "center",
		));
	}

	function _init_view(&$arr)
	{
		if (!$arr["obj_inst"]->prop("conf"))
		{
			//$arr["prop"]["value"] =  t("VIGA: konfiguratsioon on valimata!");
			return false;
		}
		$this->config = obj($arr["obj_inst"]->prop("conf"));
		//"prod_type_cfgform", 
		$checks = array("prod_fld", "pkt_fld", "reception_fld", "export_fld", "prod_type_fld", "order_fld", "buyers_fld");
		foreach($checks as $check)
		{
			if(!$this->config->prop($check))
			{
				return false;
			}
		}
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on toodete kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on pakettide kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on sissetulekute kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on v&auml;jaminekute kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on toodete t&uuml;&uuml;pide kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on tellimuste kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on tellijate kataloog valimata!";

		$this->prod_fld = $this->config->prop("prod_fld");
		$this->prod_tree_root = isset($_GET["tree_filter"]) ? $_GET["tree_filter"] : $this->config->prop("prod_fld");

		$this->pkt_fld = $this->config->prop("pkt_fld");
		$this->pkt_tree_root = isset($_GET["tree_filter"]) ? $_GET["tree_filter"] : $this->config->prop("pkt_fld");

		$this->prod_type_cfgform = $this->config->prop("prod_type_cfgform");
		$this->reception_fld = $this->config->prop("reception_fld");
		$this->export_fld = $this->config->prop("export_fld");
		$this->prod_type_fld = $this->config->prop("prod_type_fld");
		$this->order_fld = $this->config->prop("order_fld");
		$this->buyers_fld = $this->config->prop("buyers_fld");
		$this->prod_conf_folder = $this->config->prop("prod_conf_folder");

		return true;
	}

	function mk_pkt_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_pkt",
			"tooltip" => t("Uus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_pkt",
			"text" => t("Lisa pakett"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->pkt_tree_root,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 2, //RELTYPE_PACKET,
				"return_url" => get_ru()
			), CL_SHOP_PACKET)
		));
	}

	function do_pkt_list(&$arr)
	{
		// this has tree on left, table on right
		$this->read_template("prod_list.tpl");

		$this->vars(array(
			"tree" => $this->_pkt_list_tree($arr),
			"list" => $this->_pkt_list_list($arr)
		));

		$arr["prop"]["value"] = $this->parse();
	}

	function _pkt_list_tree(&$arr)
	{
		$ot = new object_tree(array(
			"parent" => $this->config->prop("pkt_fld"),
			"class_id" => CL_MENU,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
			"sort_by" => "objects.jrk"
		));
		
		classload("vcl/treeview");
		$tv = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "pkts",
				"persist_state" => true,
			),
			"root_item" => obj($this->config->prop("pkt_fld")),
			"ot" => $ot,
			"var" => "tree_filter"
		));

		return $tv->finalize_tree();
	}

	function _pkt_list_list(&$arr)
	{
		classload("vcl/table");
		$tb = new aw_table(array("layout" => "generic"));
		$this->_init_pkt_list_list_tbl($tb);

		// get items 
		$ot = new object_tree(array(
			"parent" => $this->pkt_tree_root,
			"class_id" => array(CL_MENU,CL_SHOP_PACKET),
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
		));
		$ol = $ot->to_list();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() == CL_MENU)
			{
				continue;
			}

			$get = "";
			if ($o->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => t("V&otilde;ta laost")
				));
			}

			$tb->define_data(array(
				"name" => $o->path_str(array("to" => $this->pkt_fld)),
				"cnt" => $o->prop("item_count"),
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"return_url" => get_ru()
					), CL_SHOP_PACKET),
					"caption" => t("Muuda")
				)),
				"get" => $get,
				"put" => html::href(array(
					"url" => $this->mk_my_orb("create_reception", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => t("Vii lattu")
				))
			));
		}
		
		return $tb->draw();
	}

	function _init_pkt_list_list_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "name",
			"caption" => t("Nimi")
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "cnt",
			"caption" => t("Kogus laos"),
			"align" => "center",
			"type" => "int"
		));

		$t->define_field(array(
			"name" => "get",
			"caption" => t("V&otilde;ta laost"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "put",
			"caption" => t("Vii lattu"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center"
		));
	}

	function do_storage_list_tbl(&$arr)
	{
		$this->_init_storage_list_tbl($arr["prop"]["vcl_inst"]);

		$items = $this->get_packet_list(array(
			"id" => $arr["obj_inst"]->id()
		));
		foreach($items as $i)
		{
			if ($i->class_id() == CL_SHOP_PACKET)
			{
				$type = t("Pakett");
				$name = $i->path_str(array("to" => $this->config->prop("pkt_fld")));
			}
			else
			{
				$type = "";
				if (is_oid($i->prop("item_type")))
				{
					$type_o = obj($i->prop("item_type"));
					$type = $type_o->name();
				}
				$name = $i->path_str(array("to" => $this->config->prop("prod_fld")));
			}

			$get = "";
			if ($i->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $i->id()
					)),
					"caption" => t("V&otilde;ta laost")
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $name,
				"type" => $type,
				"count" => $i->prop("item_count"),
				"get" => $get,
				"put" => html::href(array(
					"url" => $this->mk_my_orb("create_reception", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $i->id()
					)),
					"caption" => t("Vii lattu")
				))
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _init_storage_list_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "name",
			"caption" => t("Nimi")
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "count",
			"caption" => t("Laoseis"),
			"type" => "int",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "get",
			"caption" => t("V&otilde;ta laost"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "put",
			"caption" => t("Vii lattu"),
			"align" => "center"
		));

	}

	function do_storage_income_tbl(&$arr)
	{
		$this->_init_storage_income_tbl($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_STORAGE_INCOME")) as $c)
		{
			$to = $c->to();

			if ($to->prop("confirm"))
			{
				$stat = t("Sissetulek kinnitatud");
			}
			else
			{
				$stat = html::checkbox(array(
					"name" => "confirm[".$to->id()."]",
					"value" => 1
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => t("Vaata"),
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_RECEPTION)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified"),
				"status" => $stat
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _init_storage_income_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "name",
			"caption" => t("Nimi")
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "modifiedby",
			"caption" => t("Kes"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "modified",
			"caption" => t("Millal"),
			"align" => "center",
			"type" => "time",
			"format" => "m.d.Y H:i"
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
			"align" => "center"
		));
	}

	function save_storage_inc_tbl(&$arr)
	{
		$re = get_instance(CL_SHOP_WAREHOUSE_RECEPTION);

		$awa = new aw_array($arr["request"]["confirm"]);
		foreach($awa->get() as $inc => $one)
		{
			if ($one == 1)
			{
				// confirm reception
				$re->do_confirm(obj($inc));
			}
		}
	}

	function mk_storage_income_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_reception",
			"tooltip" => t("Uus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_reception",
			"text" => t("Lisa sissetulek"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->reception_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 3, //RELTYPE_STORAGE_INCOME,
				"return_url" => get_ru()
			), CL_SHOP_WAREHOUSE_RECEPTION)
		));
	}


	function do_storage_export_tbl(&$arr)
	{
		$this->_init_storage_export_tbl($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_STORAGE_EXPORT")) as $c)
		{
			$to = $c->to();

			if ($to->prop("confirm"))
			{
				$stat = t("Sissetulek kinnitatud");
			}
			else
			{
				$stat = html::checkbox(array(
					"name" => "confirm[".$to->id()."]",
					"value" => 1
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => t("Vaata"),
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_EXPORT)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified"),
				"status" => $stat
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _init_storage_export_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "name",
			"caption" => t("Nimi")
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "modifiedby",
			"caption" => t("Kes"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "modified",
			"caption" => t("Millal"),
			"align" => "center",
			"type" => "time",
			"format" => "m.d.Y H:i"
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
			"align" => "center"
		));
	}

	function save_storage_exp_tbl(&$arr)
	{
		$re = get_instance(CL_SHOP_WAREHOUSE_EXPORT);

		$awa = new aw_array($arr["request"]["confirm"]);
		foreach($awa->get() as $inc => $one)
		{
			if ($one == 1)
			{
				// confirm export
				$re->do_confirm(obj($inc));
			}
		}
	}

	function mk_storage_export_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_export",
			"tooltip" => t("Uus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_export",
			"text" => t("Lisa v&auml;ljaminek"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->export_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 4, //RELTYPE_STORAGE_EXPORT,
				"return_url" => get_ru()
			), CL_SHOP_WAREHOUSE_EXPORT)
		));
	}

	/** creates a new export object and attach a product to it, then redirect user to count entry
	
		@attrib name=create_export

		@param id required type=int acl=view
		@param product required type=int acl=view

	**/
	function create_export($arr)
	{
		extract($arr);
		$o = obj($id);
		$tmp = array(
			"obj_inst" => $o
		);
		$this->_init_view($tmp);

		$p = obj($product);

		// create export object
		$e = obj();
		$e->set_parent($this->export_fld);
		$e->set_class_id(CL_SHOP_WAREHOUSE_EXPORT);
		$e->set_name(sprintf(t("Lao v&auml;ljaminek: %s"), $p->name()));
		$e->save();

		$e->connect(array(
			"to" => $p->id(),
			"reltype" => "RELTYPE_PRODUCT",
		));

		// also connect the export to warehouse
		$o->connect(array(
			"to" => $e,
			"reltype" => "RELTYPE_STORAGE_EXPORT",
		));

		return $this->mk_my_orb("change", array(
			"id" => $e->id(),
			"group" => "export",
			"return_url" => $this->mk_my_orb("change", array(
				"id" => $o->id(),
				"group" => "storage_export"
			))
		), CL_SHOP_WAREHOUSE_EXPORT);
	}

	/** creates a new reception object and attach a product to it, then redirect user to count entry
	
		@attrib name=create_reception

		@param id required type=int acl=view
		@param product required type=int acl=view

	**/
	function create_reception($arr)
	{
		extract($arr);
		$o = obj($id);
		$tmp = array(
			"obj_inst" => $o
		);
		$this->_init_view($tmp);

		$p = obj($product);

		// create export object
		$e = obj();
		$e->set_parent($this->reception_fld);
		$e->set_class_id(CL_SHOP_WAREHOUSE_RECEPTION);
		$e->set_name(sprintf(t("Lao sissetulek: %s"), $p->name()));
		$e->save();

		$e->connect(array(
			"to" => $p->id(),
			"reltype" => "RELTYPE_PRODUCT",
		));

		// also connect the reception to warehouse
		$o->connect(array(
			"to" => $e,
			"reltype" => "RELTYPE_STORAGE_INCOME",
		));

		return $this->mk_my_orb("change", array(
			"id" => $e->id(),
			"group" => "income",
			"return_url" => $this->mk_my_orb("change", array(
				"id" => $o->id(),
				"group" => "storage_income"
			))
		), CL_SHOP_WAREHOUSE_RECEPTION);
	}

	function mk_order_unconfirmed_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_order",
			"tooltip" => t("Uus tellimus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_order",
			"text" => t("Lisa tellimus"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->order_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 5, //RELTYPE_ORDER,
				"return_url" => get_ru()
			), CL_SHOP_ORDER)
		));
	}

	function do_order_unconfirmed_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_order_unconfirmed_tbl($t);

		// list orders from order folder
		$ol = new object_list(array(
			"class_id" => CL_SHOP_ORDER,
			"confirmed" => 0
		));
		foreach($ol->arr() as $o)
		{
			$mb = $o->modifiedby();
			if (is_oid($o->prop("orderer_person")) && $this->can("view", $o->prop("orderer_company")))
			{
				$_person = obj($o->prop("orderer_person"));
				$mb = $_person->name();
			}
			else
			if (is_oid($o->prop("oc")))
			{
				$oc = obj($o->prop("oc"));
				if (($pp = $oc->prop("data_form_person")))
				{
					$_ud = $o->meta("user_data");
					$mb = $_ud[$pp];
				}
			}

			if (is_oid($o->prop("orderer_company")) && $this->can("view", $o->prop("orderer_company")))
			{
				$_comp = obj($o->prop("orderer_company"));
				$mb .= " / ".$_comp->name();
			}
			else
			if (is_oid($o->prop("oc")))
			{
				$oc = obj($o->prop("oc"));
				if (($pp = $oc->prop("data_form_company")))
				{
					$_ud = $o->meta("user_data");
					$mb = $_ud[$pp];
				}
			}
			$t->define_data(array(
				"id" => $o->id(),
				"name" => $o->name(),
				"modifiedby" => $mb,
				"modified" => $o->created(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"group" => "items",
						"return_url" => get_ru(),
					), CL_SHOP_ORDER),
					"caption" => t("Vaata")
				)),
				"confirm" => html::checkbox(array(
					"name" => "confirm[".$o->id()."]",
					"value" => 1
				)),
				"price" => $o->prop("sum")
			));
		}
		$t->set_default_sortby("modified");
		$t->set_default_sorder("DESC");
		$t->sort_by();
	}

	function _init_order_unconfirmed_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "confirm",
			"caption" => t("Kinnita"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Kes"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Millal"),
			"type" => "time",
			"format" => "d.m.Y H:i",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
			"align" => "center"
		));
	}

	function mk_order_confirmed_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_order",
			"tooltip" => t("Uus tellimus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_order",
			"text" => t("Lisa tellimus"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->order_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 5, //RELTYPE_ORDER,
				"return_url" => get_ru()
			), CL_SHOP_ORDER)
		));
	}

	function do_order_confirmed_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_order_confirmed_tbl($t);

		// list orders from order folder
		$ol = new object_list(array(
			"class_id" => CL_SHOP_ORDER,
			"confirmed" => 1
		));
		foreach($ol->arr() as $o)
		{
			$mb = $o->modifiedby();
			if (is_oid($o->prop("orderer_person")) && $this->can("view", $o->prop("orderer_company")))
			{
				$_person = obj($o->prop("orderer_person"));
				$mb = $_person->name();
			}
			else
			if (is_oid($o->prop("oc")))
			{
				$oc = obj($o->prop("oc"));
				if (($pp = $oc->prop("data_form_person")))
				{
					$_ud = $o->meta("user_data");
					$mb = $_ud[$pp];
				}
			}

			if (is_oid($o->prop("orderer_company")) && $this->can("view", $o->prop("orderer_company")))
			{
				$_comp = obj($o->prop("orderer_company"));
				$mb .= " / ".$_comp->name();
			}
			else
			if (is_oid($o->prop("oc")))
			{
				$oc = obj($o->prop("oc"));
				if (($pp = $oc->prop("data_form_company")))
				{
					$_ud = $o->meta("user_data");
					$mb = $_ud[$pp];
				}
			}
			$t->define_data(array(
				"id" => $o->id(),
				"name" => $o->name(),
				"madeby" => $mb,
				"modifiedby" => $o->modifiedby(),
				"modified" => $o->modified(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"group" => "items",
						"return_url" => get_ru(),
					), CL_SHOP_ORDER),
					"caption" => t("Vaata")
				)),
				"price" => $o->prop("sum")
			));
		}
	}

	function _init_order_confirmed_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "madeby",
			"caption" => t("Kes"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Kinnitas"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Millal"),
			"type" => "time",
			"format" => "d.m.Y H:i",
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
			"align" => "center"
		));
	}

	function save_order_unconfirmed_tbl(&$arr)
	{
		$re = get_instance(CL_SHOP_ORDER);

		$awa = new aw_array($arr["request"]["confirm"]);
		foreach($awa->get() as $inc => $one)
		{
			if ($one == 1)
			{
				// confirm reception
				$re->do_confirm(obj($inc));
			}
		}
	}

	function _init_order_orderer_cos_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
		));
		$t->define_field(array(
			"name" => "who",
			"caption" => t("Kes"),
		));
		$t->define_field(array(
			"name" => "when",
			"caption" => t("Millal"),
		));
		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
		));
	}

	function do_order_orderer_cos_tbl($arr)
	{
		$t =&$arr["prop"]["vcl_inst"];
		$this->_init_order_orderer_cos_tbl($t);

		// get orders by orderer
		if ($arr["request"]["tree_worker"])
		{
			$ol = new object_list(array(
				"class_id" => CL_SHOP_ORDER,
				"orderer_person" => $arr["request"]["tree_worker"]
			));
		}
		else
		if ($arr["request"]["tree_company"])
		{
			// get workers for co
			$co = obj($arr["request"]["tree_company"]);
			$ids = array();
			$con = new connection();
			foreach($con->find(array("from.class_id" => CL_CRM_PERSON, "to" => $co->id())) as $c)
			{
				$ids[] = $c["from"];
			}
			if (!count($ids))
			{
				$ol = new object_list();
			}
			else
			{
				$ol = new object_list(array(
					"class_id" => CL_SHOP_ORDER,
					"orderer_person" => $ids
				));
			}
		}
		else
		if ($arr["request"]["tree_code"])
		{
			// get workers for co
			$categories = new object_list(array(
				"parent" => $this->buyers_fld,
				"class_id" => CL_CRM_SECTOR,
				"kood" => $arr["request"]["tree_code"]."%"
			));
			$ids = array();
			for($cat = $categories->begin(); !$categories->end(); $cat = $categories->next())
			{
				foreach($cat->connections_to(array("from.class_id" => CL_CRM_COMPANY)) as $c)
				{
					$co = $c->from();
					foreach($co->connections_from(array("type" => "RELTYPE_WORKER")) as $c)
					{
						$ids[] = $c->prop("to");
					}
				}
			}

			if (count($ids) < 1)
			{
				$ol = new object_list();
			}
			else
			{
				$ol = new object_list(array(
					"class_id" => CL_SHOP_ORDER,
					"orderer_person" => $ids
				));
			}
		}
		else
		{
			$ol = new object_list();
		}

		$oinst = get_instance(CL_SHOP_ORDER);
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$t->define_data(array(
				"name" => $o->name(),
				"price" => $o->prop("sum"),
				"who" => $oinst->get_orderer($o),
				"when" => $o->modified(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()),
					"caption" => t("Vaata")
				))
			));
		}
	}

	function do_order_orderer_cos_tree(&$arr)
	{
		// get categories
		$categories = new object_list(array(
			"parent" => $this->buyers_fld,
			"class_id" => CL_CRM_SECTOR,
		));

		$all_cos = new object_list(array(
			"parent" => $this->buyers_fld,
			"class_id" => CL_CRM_COMPANY
		));
		$this->all_cos_ids = $all_cos->names();

		$tv = $this->get_vcl_tree_from_cat_list($categories);

		// now, add all remaining cos as top level items
		foreach($this->all_cos_ids as $co_id => $con)
		{
			$tv->add_item(0, array(
				"name" => $con,
				"id" => "nocode_co".$co_id,
				"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", $co_id, aw_url_change_var("tree_worker", NULL)))
			));

			$co = obj($co_id);
			// now all people for that company
			foreach($co->connections_from(array("type" => "RELTYPE_WORKER")) as $c)
			{
				$tv->add_item("nocode_co".$co->id(), array(
					"name" => $c->prop("to.name"),
					"id" => "nocode_wk".$c->prop("to"),
					"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", NULL, aw_url_change_var("tree_worker", $c->prop("to"))))
				));
			}
		}

	
		$arr["prop"]["value"] = $tv->finalize_tree();
	}

	function get_vcl_tree_from_cat_list($categories)
	{
		// now, gotst to make tree out of them. 
		// algorithm is: sort by length, add the shortest to first level, then start adding by legth
		// prop: kood
		$ta = array();
		$ids = array();
		for($o = $categories->begin(); !$categories->end(); $o = $categories->next())
		{
			$ta[$o->prop("kood")] = $o;
			$ids[] = $o->id();
		}
		uksort($ta, array(&$this, "__ta_sb_cb"));

		// get all companies with these categories
		$cos = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"pohitegevus" => $ids
		));
		$this->cos_by_code = array();
		for($o = $cos->begin(); !$cos->end(); $o = $cos->next())
		{
			// get all type rels
			foreach($o->connections_from(array("to.class_id" => CL_CRM_SECTOR)) as $c)
			{
				$s = $c->to();
				$this->cos_by_code[$s->prop("kood")][] = $o;
			}
		}

		// now, start adding things to the tree.
		$tv = get_instance("vcl/treeview");
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "shwhordcos",
			"persist_state" => true
		));

		$this->_req_filter_and_add($tv, $ta, "", 0);

		return $tv;
	}

	function _req_filter_and_add(&$tv, $ta, $filter_code, $parent)
	{
		$nta = array();

		$fclen = strlen($filter_code);
		$minl = 1000;
		$cpta = $ta;

		foreach($cpta as $code => $code_o)
		{
			if (substr($code, 0, $fclen) == $filter_code && $code != $filter_code)
			{
				$nta[$code] = $code_o;
				if (strlen($code) < $minl)
				{
					$minl = strlen($code);
				}
			}
		}

		if (count($nta) < 1)
		{
			// we reached the end of the tree, add cos now
			$this->_do_add_cos_by_code($tv, $filter_code);
			return;
		}

		uksort($nta, array(&$this, "__ta_sb_cb"));

		reset($nta);
		list($code, $code_o) = each($nta);
		while (strlen($code) == $minl)
		{
			$tv->add_item($parent, array(
				"name" => $code_o->name(),
				"id" => $code,
				"url" => aw_url_change_var("tree_code", $code, aw_url_change_var("tree_company", NULL, aw_url_change_var("tree_worker", NULL)))
			));

			// now find the children for this. 
			// how to do this? simple, filter the list by the start of this code and sort and insert smallest length, 
			// lather, rinse, repeat
			$this->_req_filter_and_add($tv, $nta, $code, $code);

			list($code, $code_o) = each($nta);
		}
	}

	function _do_add_cos_by_code(&$tv, $code)
	{
		if (!is_array($this->cos_by_code[$code]))
		{
			return;
		}
		foreach($this->cos_by_code[$code] as $co)
		{
			$tv->add_item($code, array(
				"name" => $co->name(),
				"id" => $code."co".$co->id(),
				"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", $co->id(), aw_url_change_var("tree_worker", NULL)))
			));
			unset($this->all_cos_ids[$co->id()]);

			// now all people for that company
			foreach($co->connections_from(array("type" => "RELTYPE_WORKER")) as $c)
			{
				$tv->add_item($code."co".$co->id(), array(
					"name" => $c->prop("to.name"),
					"id" => $code."wk".$c->prop("to"),
					"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", NULL, aw_url_change_var("tree_worker", $c->prop("to"))))
				));
			}
		}
	}

	function __ta_sb_cb($a, $b)
	{
		return ($a == $b ? 0 : ((strlen($a) < strlen($b)) ? -1 : 1));
	}

	function callback_pre_edit($arr)
	{
		if (!$arr["obj_inst"]->prop("order_current_org") && 
			is_oid($arr["obj_inst"]->prop("order_current_person")) && 
			$this->can("view", $arr["obj_inst"]->prop("order_current_person"))
		)
		{
			// get the org from the person 
			$pers = obj($arr["obj_inst"]->prop("order_current_person"));
			$conn = reset($pers->connections_from(array(
				"type" => "RELTYPE_WORK"
			)));
			if ($conn)
			{
				$arr["obj_inst"]->set_prop("order_current_org", $conn->prop("to"));
				$tmp = $arr["obj_inst"]->meta("popup_search[order_current_org]");
				$tmp[$conn->prop("to")] = $conn->prop("to");
				$arr["obj_inst"]->set_meta("popup_search[order_current_org]", $tmp);
				$arr["obj_inst"]->save();
			}
		}
	}

	function callback_pre_save($arr)
	{
		if ($arr["request"]["group"] == "order_current")
		{
			$arr["obj_inst"]->set_meta("order_cur_ud", $arr["request"]["user_data"]);
		}

		if ($this->upd_ud)
		{
			$this->do_update_user_data(array(
				"oid" => $arr["obj_inst"]->id()
			));
		}
	}

	function callback_get_order_current_form($arr)
	{
		$ret = array();

		$o = $arr["obj_inst"];
		$cud = $o->meta("order_cur_ud");

		// get order center
		if (!$o->prop("order_center"))
		{
			return $ret;
		}
		$oc = obj($o->prop("order_center"));
		$oc_i = $oc->instance();

		$props = $oc_i->get_properties_from_data_form($oc, $cud);

		if ($arr["no_data"])
		{
			return $props;
		}

		if (($pp = $oc->prop("data_form_person")) && is_oid($o->prop("order_current_person")))
		{
			$po = obj($o->prop("order_current_person"));
			$props[$pp]["value"] = $po->name();
			$props[$pp]["type"] = "hidden";
			$props[$pp."_show"] = $props[$pp];
			$props[$pp."_show"]["type"] = "text";
		}

		if (($pp = $oc->prop("data_form_company")) && $o->prop("order_current_org"))
		{
			$po = obj($o->prop("order_current_org"));
			$props[$pp]["value"] = $po->name();
			$props[$pp]["type"] = "hidden";
			$props[$pp."_show"] = $props[$pp];
			$props[$pp."_show"]["type"] = "text";
		}

		return $props;
	}

	function do_search_res_tbl($arr)
	{
		if (!$arr["obj_inst"]->prop("conf"))
		{
			return;
		}
		$conf = obj($arr["obj_inst"]->prop("conf"));
		if (!$conf->prop("search_form"))
		{
			return;
		}
		$sf = obj($conf->prop("search_form"));
		$sf_i = $sf->instance();

		$sf_i->get_search_result_table(array(
			"ob" => $sf,
			"t" => &$arr["prop"]["vcl_inst"],
			"request" => $arr["request"]
		));

		// add select column
		$arr["prop"]["vcl_inst"]->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	/** finishes the order 

		@attrib name=gen_order

		@param id required type=int acl=view
		@param user_data optional
		@param html optional
	**/
	function gen_order($arr)
	{
		$ordid = $this->make_cur_order_id($arr);

		return $this->mk_my_orb("gen_pdf", array(
			"id" => $ordid,
			"html" => $arr["html"],
		), CL_SHOP_ORDER);
	}

	function make_cur_order_id($arr)
	{
		$o = obj($arr["id"]);
		$oc = $o->prop("order_center");
		error::raise_if(!$oc, array(
			"id" => ERR_NO_OC,
			"msg" => t("shop_warehouse::gen_order(): no order center object selected!")
		));

		$soc = get_instance(CL_SHOP_ORDER_CART);
		if (!aw_global_get("wh_order_cur_order_id"))
		{
			$ordid = $soc->do_create_order_from_cart($oc, $arr["id"], array(
				"pers_id" => $o->prop("order_current_person"),
				"com_id" => $o->prop("order_current_org"),
				"user_data" => $o->meta("order_cur_ud"),
				"discount" => $o->meta("order_cur_discount"),
				"prod_paging" => $o->meta("order_cur_pages"),
				"no_send_mail" => 1
			));
			aw_session_set("wh_order_cur_order_id", $ordid);
		}
		return aw_global_get("wh_order_cur_order_id");
	}

	function callback_get_search_form($arr)
	{
		if (!$arr["obj_inst"]->prop("conf"))
		{
			return;
		}
		$conf = obj($arr["obj_inst"]->prop("conf"));
		if (!$conf->prop("search_form"))
		{
			return;
		}
		$sf = obj($conf->prop("search_form"));
		$sf_i = $sf->instance();

		return $sf_i->get_callback_properties($sf);
	}

	function do_search_tb($arr)
	{
		$tb =& $arr["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "add_to_order",
			"img" => "import.gif",
			"tooltip" => t("Lisa pakkumisse"),
			"action" => "add_to_cart"
		));

		$tb->add_button(array(
			"name" => "go_to_order",
			"img" => "save.gif",
			"tooltip" => t("Moodusta pakkumine"),
			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => "order_current"))
		));
	}

	/** message handler for the MSG_POPUP_SEARCH_CHANGE message so we can update
		the person/company listboxes when one changes
	**/
	function on_popup_search_change($arr)
	{
		if ($arr["prop"] == "order_current_org")
		{
			$this->do_update_persons_from_org($arr);
		}
		else
		{
			$this->do_update_orgs_from_person($arr);
		}

		$this->do_update_user_data(array(
			"oid" => $arr["oid"]
		));	
	}

	function do_update_user_data($arr)
	{
		// also update the data form data, based on the property maps from the order center
		// first org
		if(!is_oid($arr["oid"]) || !$this->can("view", $arr["oid"]))
		{
			return;
		}
		$o = obj($arr["oid"]);

		$oc = get_instance(CL_SHOP_ORDER_CENTER);
		$personmap = $oc->get_property_map($o->prop("order_center"), "person");
		$orgmap = $oc->get_property_map($o->prop("order_center"), "org");

		$cud = $o->meta("order_cur_ud");

		// get selected person object
		if (($ps = $o->prop("order_current_person")))
		{
			$person = obj($ps);
			$ps_props = $person->get_property_list();

			foreach($personmap as $data_f_prop => $person_o_prop)
			{
				if ($ps_props[$person_o_prop]["type"] == "relmanager")
				{
					$tmp = $person->prop($person_o_prop);
					if (is_oid($tmp))
					{
						$tmp = obj($tmp);
						$cud[$data_f_prop] = $tmp->name();
					}
				}
				else
				{
					$cud[$data_f_prop] = $person->prop($person_o_prop);
				}
			}
		}

		if (($org = $o->prop("order_current_org")))
		{
			$org = obj($org);
			$org_props = $org->get_property_list();

			foreach($orgmap as $data_f_prop => $org_o_prop)
			{
				if ($org_props[$org_o_prop]["type"] == "relmanager")
				{
					$tmp = $org->prop($org_o_prop);
					if (is_oid($tmp))
					{
						$tmp = obj($tmp);
						$cud[$data_f_prop] = $tmp->name();
					}
				}
				else
				{
					$cud[$data_f_prop] = $org->prop($org_o_prop);
				}
			}
		}

		$o->set_meta("order_cur_ud", $cud);
		$o->save();
	}

	function do_update_persons_from_org($arr)
	{
		$o = obj($arr["oid"]);
		$cur_co = $o->prop($arr["prop"]);
		if (!is_oid($cur_co))
		{
			return;
		}

		$workers = array();

		$co = get_instance(CL_CRM_COMPANY);
		$co->get_all_workers_for_company(obj($cur_co), &$workers, true);

		$pop = get_instance("vcl/popup_search");
		$pop->set_options(array(
			"obj" => $o,
			"prop" => "order_current_person",
			"opts" => $workers
		));
	}

	function do_update_orgs_from_person($arr)
	{
		$o = obj($arr["oid"]);
		$cur_person = $o->prop($arr["prop"]);

		if (!is_oid($cur_person))
		{
			return;
		}

		$ps = get_instance(CL_CRM_PERSON);
		$cos = $ps->get_all_employers_for_person(obj($cur_person));

		$pop = get_instance("vcl/popup_search");
		$pop->set_options(array(
			"obj" => $o,
			"prop" => "order_current_org",
			"opts" => $cos
		));
	}

	///////////////////////////////////////////////
	// warehouse public interface functions      //
	///////////////////////////////////////////////

	/** returns an object_tree of warehouse folders

		@attrib param=name

		@param id required

	**/
	function get_packet_folder_list($arr)
	{
		$o = obj($arr["id"]);
		$config = obj($o->prop("conf"));
		$ot = new object_tree(array(
			"parent" => $config->prop("pkt_fld"),
			"class_id" => CL_MENU,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
			"sort_by" => "objects.jrk"
		));
		return array(obj($config->prop("pkt_fld")), $ot);
	}	

	/** returns a list of packets in the warehouse $id, optionally under folder $parent

		@attrib param=name

		@param id required
		@param parent optional
		@param only_active optional
	**/
	function get_packet_list($arr)
	{
		enter_function("shop_warehouse::get_packet_list");
		
		$wh = obj($arr["id"]);
		$conf = obj($wh->prop("conf"));

		$status = array(STAT_ACTIVE, STAT_NOTACTIVE);
		if (!empty($arr["only_active"]))
		{
			$status = STAT_ACTIVE;
		}

		$ret = array();

		if($conf->prop("no_packets") != 1)
		{
			$po = obj((!empty($arr["parent"]) ? $arr["parent"] : $conf->prop("pkt_fld")));
			if ($po->is_brother())
			{
				$po = $po->get_original();
			}
	
			$ol = new object_list(array(
				"parent" => $po->id(),
				"class_id" => CL_SHOP_PACKET,
				"status" => $status
			));
			$ret = $ol->arr();
		}

		$po = obj((!empty($arr["parent"]) ? $arr["parent"] : $conf->prop("prod_fld")));	
		if ($po->is_brother())
		{
			$po = $po->get_original();
		}
		enter_function("warehouse::object_list");
		$ol = new object_list(array(
			"parent" => $po->id(),
			"class_id" => CL_SHOP_PRODUCT,
		));
		$ret = array_merge($ret, $ol->arr());
		exit_function("warehouse::object_list");
		if(!$conf->prop("sell_prods"))
		{
			// now, let the classes add sub-items to the list
			$tmp = array();
			foreach($ret as $o)
			{
				$inst = $o->instance();
				foreach($inst->get_contained_products($o) as $co)
				{
					$tmp[] = $co;
				}
			}
			$ret = $tmp;
		}
		exit_function("shop_warehouse::get_packet_list");
		return $ret;
	}

	function get_order_folder($w)
	{
		error::raise_if(!$w->prop("conf"), array(
			"id" => ERR_FATAL,
			"msg" => sprintf(t("shop_warehouse::get_order_folder(%s): the warehouse has not configuration object set!"), $w)
		));

		$conf = obj($w->prop("conf"));
		$tmp = $conf->prop("order_fld");

		error::raise_if(empty($tmp), array(
			"id" => ERR_FATAL,
			"msg" => sprintf(t("shop_warehouse::get_order_folder(%s): the warehouse configuration has no order folder set!"), $w)
		));

		return $tmp;
	}

	/** adds the selected items to the basket

		@attrib name=add_to_cart

		@param id required type=int acl=view
		@param sel optional
		@param group optional
	**/
	function add_to_cart($arr)
	{
		$adc = array();
		foreach(safe_array($arr["sel"]) as $_id)
		{
			$adc[$_id] = 1;
		}
		$warehouse = obj($arr["id"]);
		$soc = get_instance(CL_SHOP_ORDER_CART);
		$soc->submit_add_cart(array(
			"oc" => $warehouse->prop("order_center"),
			"add_to_cart" => $adc
		));

		$this->do_save_prod_ord($arr);

		return $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"tree_filter" => $arr["tree_filter"],
			"group" => $arr["group"]
		));
	}

	/** checks if the company $id is a manager company for  warehouse $wh

	**/
	function is_manager_co($wh, $id)
	{
		if (!$wh->prop("conf"))
		{
			return false;
		}
		$conf = obj($wh->prop("conf"));
		$awa = new aw_array($conf->prop("manager_cos"));
		$mc = $awa->get();

		$mc = $this->make_keys($mc);
		if ($mc[$id])
		{
			return true;
		}
		return false;
	}

	/** sends the current order to the orderer's e-mail

		@attrib name=send_cur_order

	**/
	function sent_cur_order($arr)
	{
		$ordid = $this->make_cur_order_id($arr);

		$ordo = obj($ordid);

		// get e-mail address from order
		$o = obj($arr["id"]);
		$oc = obj($o->prop("order_center"));
		$mail_to_el = $oc->prop("mail_to_el");
		$ud = $o->meta("order_cur_ud");
		$to = str_replace("&gt;", "", str_replace("&lt;", "", $ud[$mail_to_el]));
		if ($to == "")
		{
			return;
		}

		$so = get_instance(CL_SHOP_ORDER);
		$html = $so->gen_pdf(array(
			"id" => $ordid,
			"html" => 1,
			"return" => 1
		));

		$us = get_instance(CL_USER);
		$cur_person = obj($us->get_current_person());

		$froma = "automatweb@automatweb.com";
		if (is_oid($cur_person->prop("email")))
		{
			$tmp = obj($cur_person->prop("email"));
			$froma = $tmp->prop("mail");
		}

		$fromn = $cur_person->prop("name");

		$awm = get_instance("protocols/mail/aw_mail");
		$awm->create_message(array(
			"froma" => $froma,
			"fromn" => $fromn,
			"subject" => sprintf(t("Tellimus laost %s"), $o->name()),
			"to" => $to,
			"body" => strip_tags(str_replace("<br>", "\n",$html)),
		));
		$awm->htmlbodyattach(array(
			"data" => $html
		));
		$awm->gen_mail();
		
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "order_current"));
	}

	/** clears the current order 

		@attrib name=clear_order

	**/
	function clear_order($arr)
	{
		$o = obj($arr["id"]);
		$oc = obj($o->prop("order_center"));
		$soc = get_instance(CL_SHOP_ORDER_CART);
		$soc->clear_cart($oc);

		$o->set_prop("order_current_person", "");
		$o->set_prop("order_current_org", "");
		$o->set_meta("order_cur_ud", "");
		$o->set_meta("order_cur_discount", "");
		$o->set_meta("order_cur_pages", "");
		$o->save();

		aw_session_del("wh_order_cur_order_id");

		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "search_search"));
	}

	function do_save_prod_ord($arr)
	{
		foreach(safe_array($arr["old_ord"]) as $oid => $o_ord)
		{
			if ($arr["set_ord"][$oid] != $o_ord)
			{
				$o = obj($oid);
				$o->set_ord($arr["set_ord"][$oid]);
				$o->save();
			}
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["tree_filter"] = $_GET["tree_filter"];
	}

	/** returns a list of config forms that can be used to enter products
	
		@comment
			takes warehouse oid as parameter

	**/
	function get_prod_add_config_forms($arr)
	{
		$wh = obj($arr["warehouse"]);
		$conf_id = $wh->prop("conf");
		$ret = array();
		if(is_oid($conf_id) && $this->can("view", $conf_id))
		{
			$conf = obj($conf_id);
			$this->_req_get_prod_add_config_forms($conf->prop("prod_type_fld"), $ret, "sp_cfgform");
		}
		return $ret;
	}

	/** returns a list of config forms that can be used to enter product packagings
	
		@comment
			takes warehouse oid as parameter

	**/
	function get_prod_packaging_add_config_forms($arr)
	{
		$wh = obj($arr["warehouse"]);
		$conf_id = $wh->prop("conf");
		$ret = array();
		if(is_oid($conf_id) && $this->can("view", $conf_id))
		{
			$conf = obj($conf_id);
			$this->_req_get_prod_add_config_forms($conf->prop("prod_type_fld"), $ret, "packaging_cfgform");
		}
		return $ret;
	}

	function _req_get_prod_add_config_forms($parent, &$ret, $prop)
	{
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => array(CL_MENU, CL_SHOP_PRODUCT_TYPE),
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			if ($o->class_id() != CL_MENU)
			{
				if (is_oid($cf_id = $o->prop($prop)) && $this->can("view", $cf_id))
				{
					$ret[$cf_id] = $cf_id;
				}
			}
			else
			{
				$this->_req_get_prod_add_config_forms($o->id(), $ret, $prop);
			}
		}
	}
}
?>
