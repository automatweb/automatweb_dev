<?php

global $orb_defs;
$orb_defs["shop_admin"] = "xml";

classload("shop_base","shop_menuedit");
classload("shop");
classload("form");
classload("objects");
class shop_admin extends shop_base
{
	function shop_admin()
	{
		$this->shop_base();
	}

	function gen_frameset($arr)
	{
		extract($arr);
		$this->read_template("shop_admin_frameset.tpl");
		$this->vars(array(
			"top_frame" => $this->mk_my_orb("show_top_frame",array("shop_id" => $shop_id),"shop_admin", false, true),
			"bottom_frame" => "#"
		));
		die($this->parse());
	}

	function show_top_frame($arr)
	{
		extract($arr);
		$this->read_template("shop_top_frame.tpl");

		global $ext;
		$items = array(1 => "General settings", 2 => "Articles", 3 => "Orders", 4 => "Users");
		foreach($items as $inum => $iname)
		{
			$this->vars(array(
				"url" => $this->mk_my_orb("show_top_frame", array("shop_id" => $shop_id, "menu_id" => $inum),"",false,true),
				"text" => $iname
			));
			if ($inum == $menu_id)
			{
				$mn.=$this->parse("L1_ITEM_SEL");
			}
			else
			{
				$mn.=$this->parse("L1_ITEM");
			}
		}
		$this->vars(array("L1_ITEM" => $mn, "L1_ITEM_SEL" => ""));
	
		$items_l2 = array(
			1 => array(
								 array("name" => "System", "url" => $this->mk_my_orb("system_general", array("shop_id" => $shop_id),"",false,true)),
								 array("name" => "Categories", "url" => $this->mk_my_orb("categories_frame", array("shop_id" => $shop_id),"",false,true)),
//								 array("name" => "Users", "url" => "#"),
								 array("name" => "Groups/rights", "url" => "#"),
								 array("name" => "Currencies", "url" => $this->mk_my_orb("currency_redirect", array("shop_id" => $shop_id),"",false,true)),
								 array("name" => "Languages", "url" => "languages.$ext"),
								 array("name" => "Formulas", "url" => $this->mk_my_orb("formula_redirect", array("shop_id" => $shop_id),"",false,true)),
								),
			2 => array(
								 array("name" => "Articles", "url" => $this->mk_my_orb("article_frame", array("shop_id" => $shop_id),"",false,true)),
								 array("name" => "Article types", "url" => $this->mk_my_orb("arttype_redirect", array("shop_id" => $shop_id),"",false,true)),
								),
			3 => array(
								 array("name" => "All reservations", "url" => $this->mk_my_orb("admin_orders", array("id" => $shop_id),"shop",true,true)),
								 array("name" => "Detailed reservations", "url" => $this->mk_my_orb("detailed_reservations", array("shop_id" => $shop_id),"shop_admin",false,true)),
								 array("name" => "Passengers", "url" => $this->mk_my_orb("passengers", array("shop_id" => $shop_id),"shop_admin",false,true)),
								 array("name" => "Detailed Passengers list", "url" => $this->mk_my_orb("passengers_detail", array("shop_id" => $shop_id),"shop_admin",false,true)),
								 array("name" => "Statistics", "url" => $this->mk_my_orb("change", array("id" => "3443", "parent" => "3430"),"shop_stat", false, true)),
								),
			4 => array(
								 array("name" => "Add new user", "url" => "#"),
//								 array("name" => "Add new group", "url" => "#"),
								 array("name" => "View users", "url" => "orb.aw?class=users&action=gen_list"),
								),
			
		);

		if ($menu_id)
		{
			foreach($items_l2[$menu_id] as $inum => $iarr)
			{
				$this->vars(array(
					"url" => $iarr["url"],
					"text" => $iarr["name"]
				));
				$mn2.=$this->parse("L2_ITEM");
			}
			$this->vars(array("L2_ITEM" => $mn2));
		}
		
/*		$this->vars(array(
			"system_url" => $this->mk_my_orb("system_general", array("shop_id" => $shop_id),"",false,true),
			"categories_url" => $this->mk_my_orb("categories_frame", array("shop_id" => $shop_id),"",false,true),
			"users_url" => "#",
			"currencies_url" => $this->mk_my_orb("currency_redirect", array("shop_id" => $shop_id),"",false,true),
			"lang_url" => "languages.$ext",
			"formula_url" => $this->mk_my_orb("formula_redirect", array("shop_id" => $shop_id),"",false,true),
			"article_url" => $this->mk_my_orb("article_frame", array("shop_id" => $shop_id),"",false,true),
			"article_types_url" => $this->mk_my_orb("arttype_redirect", array("shop_id" => $shop_id),"",false,true),
			"all_reservations_url" => $this->mk_my_orb("admin_orders", array("id" => $shop_id),"shop",false,true),
			"detailed_reservations_url" => $this->mk_my_orb("detailed_reservations", array("shop_id" => $shop_id),"shop_admin",false,true),
			"statistics" => "#"
		));*/
		return $this->parse();
	}

	function do_system_general($arr)
	{
		extract($arr);
		$this->read_template("admin_system_general.tpl");
		$sh = $this->get($shop_id);
		$this->vars(array(
			"name" => $sh["name"],
			"comment" => $sh["comment"],
			"emails" => $sh["emails"],
			"reforb" => $this->mk_reforb("submit_system_general", array("shop_id" => $shop_id))
		));
		return $this->do_system_menu($shop_id);
	}

	function submit_system_general($arr)
	{
		extract($arr);

		$this->upd_object(array("oid" => $shop_id, "name" => $name, "comment" => $comment));
		$this->db_query("UPDATE shop SET emails='$emails' WHERE id = $shop_id");

		return $this->mk_my_orb("system_general", array("shop_id" => $shop_id), "", false, true);
	}

	function do_system_requisites($arr)
	{
		extract($arr);
		$sh = $this->get($shop_id);

		classload("form");
		$f = new form;
		$ret = $f->gen_preview(array(
			"id" => $sh["owner_form"],
			"entry_id" => $sh["owner_form_entry"],
			"reforb" => $this->mk_reforb("submit_system_requisites", array("shop_id" => $shop_id)),
			"no_submit" => true
		));

		return $this->do_system_menu($shop_id,$ret);
	}

	function submit_system_requisites($arr)
	{
		extract($arr);

		$sh = new shop;
		$sh->submit_owner_data(array("id" => $shop_id));

		return $this->mk_my_orb("system_requisites", array("shop_id" => $shop_id), "",false, true);
	}

	function do_system_invoice($arr)
	{
		extract($arr);
		$this->read_template("admin_system_invoice.tpl");

		$sh = $this->get($shop_id);

		$f = new form;
		$ops = $f->get_op_list($sh["owner_form"]);

		$this->do_core_change_tables($shop_id);

		$this->vars(array(
			"owner_form_ops" => $this->picker($sh["owner_form_op"], $ops[$sh["owner_form"]]),
			"reforb" => $this->mk_reforb("submit_system_invoice", array("shop_id" => $shop_id))
		));
		return $this->do_system_menu($shop_id);
	}

	function submit_system_invoice($arr)
	{
		extract($arr);

		$this->db_query("UPDATE shop SET owner_form_op = '$owner_form_op' WHERE id = '$shop_id'");

		$arr["id"] = $shop_id;
		$s = new shop;
		$s->submit_tables($arr);

		return $this->mk_my_orb("system_invoice", array("shop_id" => $shop_id), "",false, true);
	}

	function do_system_nrseries($arr)
	{
		extract($arr);
		$this->read_template("admin_system_nrseries.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_system_nrseries", array("shop_id" => $shop_id))
		));
		return $this->do_system_menu($shop_id);
	}

	function submit_system_nrseries($arr)
	{
		extract($arr);

		return $this->mk_my_orb("system_nrseries", array("shop_id" => $shop_id), "", false, true);
	}

	function do_system_catalogues($arr)
	{
		extract($arr);
		$this->read_template("admin_system_catalogues.tpl");

		$sh = $this->get($shop_id);

		$ob = new db_objects;
		$ol = $ob->get_list();
		$this->vars(array(
			"article_menus" => $this->multiple_option_list($this->get_article_menus($shop_id),$ol),
			"formula_menu" => $this->picker($sh["formula_menu"], $ol),
			"currency_menu" => $this->picker($sh["currency_menu"], $ol),
			"types_menu" => $this->picker($sh["types_menu"], $ol),
			"forms_menu" => $this->picker($sh["forms_menu"], $ol),
			"ops_menu" => $this->picker($sh["ops_menu"], $ol),
			"reforb" => $this->mk_reforb("submit_system_catalogues", array("shop_id" => $shop_id))
		));
		return $this->do_system_menu($shop_id);
	}

	function submit_system_catalogues($arr)
	{
		extract($arr);

		$this->db_query("UPDATE shop SET formula_menu = '$formula_menu', currency_menu = '$currency_menu', types_menu = '$types_menu' , forms_menu = '$forms_menu', ops_menu = '$ops_menu' WHERE id = '$shop_id'");

		$this->save_article_menus($shop_id, $article_menus);

		return $this->mk_my_orb("system_catalogues", array("shop_id" => $shop_id), "", false, true);
	}

	function do_system_other($arr)
	{
		extract($arr);
		$this->read_template("admin_system_other.tpl");
		$sh = $this->get($shop_id);

		$ob = new objects;
		$this->do_core_admin_ofs($shop_id);

		$shts = $this->listall_shop_tables();

		$its = $this->listall_item_types();

		$tablesfortypes = $this->get_tables_for_types();

		foreach($its as $i_id => $i_name)
		{
			$this->vars(array(
				"it_type" => $i_name,
				"type_id" => $i_id,
				"tables" => $this->picker($tablesfortypes[$i_id],$shts)
			));
			$stt.=$this->parse("STAT_TABLE");
		}

		$this->vars(array(
			"root_menu" => $this->picker($sh["root_menu"],$ob->get_list()),
			"reforb" => $this->mk_reforb("submit_system_other", array("shop_id" => $shop_id)),
			"STAT_TABLE" => $stt
		));
		return $this->do_system_menu($shop_id);
	}

	function submit_system_other($arr)
	{
		extract($arr);

		$this->db_query("UPDATE shop SET root_menu = '$root_menu' WHERE id = '$shop_id'");

		$this->do_core_save_ofs($shop_id, $order_form,$of_rep,$of_op,$of_op_long,$of_op_search);

		$this->db_query("DELETE FROM shop_table2item_type");
		if (is_array($type_tables))
		{
			foreach($type_tables as $type_id => $table_id)
			{
				$this->db_query("INSERT INTO shop_table2item_type(type_id,table_id) VALUES('$type_id','$table_id')");
			}
		}
		return $this->mk_my_orb("system_other", array("shop_id" => $shop_id), "", false, true);
	}

	function do_system_menu($shop_id,$ret = "")
	{
		if ($ret == "")
		{
			$ret = $this->parse();
		}
		$this->read_template("admin_system_menu.tpl");

		$menu_items = array(
			"system_general" => array("name" => "General", "url" => $this->mk_my_orb("system_general", array("shop_id" => $shop_id), "",false,true)),
			"system_requisites" => array("name" => "Requisites", "url" => $this->mk_my_orb("system_requisites", array("shop_id" => $shop_id), "",false,true)),
			"system_invoice" => array("name" => "Invoice", "url" => $this->mk_my_orb("system_invoice", array("shop_id" => $shop_id), "",false,true)),
			"system_nrseries" => array("name" => "Number series", "url" => $this->mk_my_orb("system_nrseries", array("shop_id" => $shop_id), "",false,true)),
			"system_catalogues" => array("name" => "Catalogues", "url" => $this->mk_my_orb("system_catalogues", array("shop_id" => $shop_id), "",false,true)),
			"system_other" => array("name" => "Other", "url" => $this->mk_my_orb("system_other", array("shop_id" => $shop_id), "",false,true))
		);

		return $this->do_menu($menu_items).$ret;
	}

	function categories_frame($arr)
	{
		extract($arr);
		$this->read_template("admin_categories_frameset.tpl");
		$this->vars(array(
			"left_url" => $this->mk_my_orb("categories_tree", array("shop_id" => $shop_id),"",false, true),
			"right_url" => "#"
		));
		die($this->parse());
	}

	function categories_tree($arr)
	{
		extract($arr);
		$this->do_core_tree($shop_id,"admin_categories_tree.tpl");
	}

	function do_core_tree($shop_id, $tpl)
	{
		$sh = $this->get($shop_id);

		$m = new menuedit;
		$m->tpl_init("shop");
		$m->read_template($tpl);

		$arr = array();
		$m->listacl("objects.status != 0 AND objects.class_id = ".CL_PSEUDO);
		// listib koik menyyd ja paigutab need arraysse
		$m->db_listall("objects.status != 0 AND menu.type != ".MN_FORM_ELEMENT,true);
		while ($row = $m->db_next())
		{
			if ($m->can("view",$row["oid"]))
			{
				$arr[$row["parent"]][] = $row;
			}
		}
		
		$root_o = $this->get_object($sh["root_menu"]);

		$tr = $m->rec_tree($arr,$root_o["parent"], 0);
		$m->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $root_o["parent"],
			"uid" => $GLOBALS["uid"],
			"date" => $this->time2date(time(),2)
		));
		die($m->parse());
	}

	function categories_right($arr)
	{
		extract($arr);
		$m = new shop_menuedit;
		die($m->gen_list_menus($parent,0));
	}

	function article_frame($arr)
	{
		extract($arr);
		$this->read_template("admin_article_frameset.tpl");
		$this->vars(array(
			"left_url" => $this->mk_my_orb("article_tree", array("shop_id" => $shop_id),"",false, true),
			"right_url" => "#"
		));
		die($this->parse());
	}

	function article_tree($arr)
	{
		extract($arr);
		$this->do_core_tree($shop_id,"admin_article_tree.tpl");
	}

	function article_right($arr)
	{
		extract($arr);
		$m = new shop_menuedit;
		die($m->gen_list_objs($parent,0));
	}

	function currency_redirect($arr)
	{
		extract($arr);
		$sh = $this->get($shop_id);
		header("Location: ".$this->mk_my_orb("article_right", array("parent" => $sh["currency_menu"])));
		die();
	}

	function formula_redirect($arr)
	{
		extract($arr);
		$sh = $this->get($shop_id);
		header("Location: ".$this->mk_my_orb("article_right", array("parent" => $sh["formula_menu"])));
		die();
	}

	function arttype_redirect($arr)
	{
		extract($arr);
		$sh = $this->get($shop_id);
		header("Location: ".$this->mk_my_orb("article_right", array("parent" => $sh["types_menu"])));
		die();
	}

	function detailed_reservations($arr)
	{
		extract($arr);
		$this->read_template("detailed_reservations.tpl");

		$shop = $this->get($shop_id);

		load_vcl("date_edit");
		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
		));
	
		$t_from = mktime($from["hour"],$from["minute"],0,$from["month"],$from["day"],$from["year"]);
		$t_to = mktime($to["hour"],$to["minute"],0,$to["month"],$to["day"],$to["year"]);

		if (is_array($art_types))
		{
			$a_types = array();
			foreach($art_types as $typid)
			{
				$a_types[$typid] = $typid;
			}
		}

		if (is_array($arts))
		{
			$a_items = array();
			foreach($arts as $aid)
			{
				$a_items[$aid] = $aid;
			}
		}

		if (!$show_type)
		{
			$show_type = 2;
		}
		$this->vars(array(
			"t_from" => $de->gen_edit_form("from", $t_from),
			"t_to"	=> $de->gen_edit_form("to", $t_to),
			"reforb" => $this->mk_reforb("detailed_reservations", array("shop_id" => $shop_id,"no_reforb" => true,"search" => true)),
			"art_types" => $this->multiple_option_list($a_types,$this->listall_item_types()),
			"arts" => $this->multiple_option_list($a_items,$this->get_item_picker()),
			"all_art_types" => checked($all_art_types==1),
			"all_arts" => checked($all_arts==1),
			"show_type_1" => checked($show_type == 1),
			"show_type_2" => checked($show_type == 2),
		));
		
		if ($search)
		{
			// whoop. the interesting bit goes here
			// first find all the shop_items for what we must do the table(s)
			$items = array();
			if ($all_arts) 
			{
				$items = $this->get_item_picker(array("type" => ALL_PROPS,));
				$by_type = false;
			}
			else
			if (is_array($arts))
			{
				$items = $this->get_item_picker(array("type" => ALL_PROPS, "constraint" => " AND id IN (".join(",",$this->map("%d",$a_items)).")"));
				$by_type = false;
			}
			else
			if ($all_art_types)
			{
				$items = $this->get_item_picker(array("type" => ALL_PROPS));
				$by_type = true;
			}
			else
			if (is_array($art_types))
			{
				$items = $this->get_item_picker(array("type" => ALL_PROPS, "constraint" => " AND type_id IN (".join(",",$this->map("%d",$a_types)).")"));
				$by_type = true;
			}
			
			$tablesfortypes = $this->get_tables_for_types();
			classload("shop_table");
			if ($show_type == 1)
			{
				// nyt kui on ajavahemike kaupa siis tuleb igale kaubale eraldi tabel
				// tabelit n2idatakse vastavalt kauba tyybile
				foreach($items as $i_id => $i_arr)
				{
					$st = new shop_table;
				
					$this->vars(array(
						"item_name" => $this->db_fetch_field("SELECT name FROM objects WHERE oid = $i_arr[parent]","name")."/".$i_arr["name"],
						"table" => $st->show(array("id" => $tablesfortypes[$i_arr["type_id"]], "item_id" => $i_id,"from" => $t_from, "to" => $t_to))
					));
					$tb.=$this->parse("DATE_TABLE");
				}
				$this->vars(array("DATE_TABLE" => $tb));
			}
			else
			{
				// kui on summeeritult, siis on iga kauba jaoks yx rida ja iga kauba tyybi kohta eraldi tabel
				// niisis k6igepealt leiame k6ik kaubatyybid
				$itypes = array();
				$i_join = array();
				foreach($items as $i_id => $i_arr)
				{
					if ($i_arr["type_id"])
					{
						$itypes[$i_arr["type_id"]] = $i_arr["type_id"];
						$i_join[] = $i_id;
					}
				}

				foreach($itypes as $type_id)
				{
					$st = new shop_table;
					$this->vars(array(
						"item_type_name" => $this->db_fetch_field("SELECT name FROM objects WHERE oid = $type_id","name"),
						"table" => $st->show(array("id" => $tablesfortypes[$type_id], "type_id" => ($by_type ? $type_id : 0),"group_by" => "item_id","clause" => "item_type = $type_id AND item_id IN(".join(",",$i_join).")","from" => $t_from, "to" => $t_to))
					));
					$tb.=$this->parse("SUM_TABLE");
				}
				$this->vars(array("SUM_TABLE" => $tb));
			}
		}
		return $this->parse();
	}

	function passengers($arr)
	{
		extract($arr);
		if ($print)
		{
			$this->read_template("admin_passenger_list_print.tpl");
		}
		else
		{
			$this->read_template("admin_passenger_list.tpl");
		}

		load_vcl("date_edit");
		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
		));
	
		$t_from = mktime($from["hour"],$from["minute"],0,$from["month"],$from["day"],$from["year"]);
		$t_to = mktime($to["hour"],$to["minute"],0,$to["month"],$to["day"],$to["year"]);

		if (is_array($art_types))
		{
			$a_types = array();
			foreach($art_types as $typid)
			{
				$a_types[$typid] = $typid;
			}
		}

		if (is_array($arts))
		{
			$a_items = array();
			foreach($arts as $aid)
			{
				$a_items[$aid] = $aid;
			}
		}

		if (!$show_type)
		{
			$show_type = 2;
		}
		$this->vars(array(
			"t_from" => $de->gen_edit_form("from", $t_from),
			"t_to"	=> $de->gen_edit_form("to", $t_to),
			"reforb" => $this->mk_reforb("passengers", array("shop_id" => $shop_id,"no_reforb" => true,"search" => true)),
			"art_types" => $this->multiple_option_list($a_types,$this->listall_item_types()),
			"arts" => $this->multiple_option_list($a_items,$this->get_item_picker()),
			"all_art_types" => checked($all_art_types==1),
			"all_arts" => checked($all_arts==1),
			"print_url" => $this->mk_my_orb("passengers", $arr+array("print" => true), "",false, true)
		));
		
		if ($search)
		{
			$sh_ofs = $this->get_ofs_for_shop($shop_id);

			$items = array();
			if ($all_arts) 
			{
				$items = $this->get_item_picker(array("type" => ALL_PROPS));
			}
			else
			if (is_array($arts))
			{
				$items = $this->get_item_picker(array("type" => ALL_PROPS, "constraint" => " AND id IN (".join(",",$this->map("%d",$a_items)).")"));
			}
			else
			if ($all_art_types)
			{
				$items = $this->get_item_picker(array("type" => ALL_PROPS));
			}
			else
			if (is_array($art_types))
			{
				$items = $this->get_item_picker(array("type" => ALL_PROPS, "constraint" => " AND type_id IN (".join(",",$this->map("%d",$a_types)).")"));
			}

			// k6igepealt leiame tellimuste id'd mille kohta n2idata tuleb ja siis alles teeme seda
			$order_ids = array();

			$i_join = array();
			foreach($items as $i_id => $i_arr)
			{
				if ($i_arr["type_id"])
				{
					$i_join[] = $i_id;
				}
			}

			$ijss = join(",",$i_join);
			if ($ijss != "")
			{
				$ijss = " AND item_id IN(".$ijss.")";
			}

			$this->db_query("SELECT order_id,MIN(period) AS period FROM order2item WHERE period >= $t_from AND period <= $t_to $ijss GROUP BY order_id");
			while ($row = $this->db_next())
			{
				$order_ids[] = $row["order_id"];
				$order_pers[$row["order_id"]] = $row["period"];
			}

			$orss = join(",",$order_ids);
			if ($orss != "")
			{
				// ni ja nyt n2itame siis nimekirja. v2ljund valitakse poe confist
				$f = new form;
				$cur_form = 0;
				$this->db_query("SELECT * FROM order2form_entries LEFT JOIN objects ON objects.oid = order2form_entries.entry_id WHERE order_id IN ($orss) ORDER BY objects.name");
				while ($row = $this->db_next())
				{
					if ($cur_form != $row["form_id"])
					{
						$cur_form = $row["form_id"];
						$f->load($cur_form);
						$f->load_output($sh_ofs[$cur_form]["op_id_search"]);
					}
					$f->reset();
					$f->load_entry($row["entry_id"]);
					$this->vars(array(
						"data" => $f->show(array("no_load_entry" => true, "id" => $cur_form, "entry_id" => $row["entry_id"], "op_id" => $sh_ofs[$cur_form]["op_id_search"],"no_load_op" => true)),
						"date" => $this->time2date($order_pers[$row["order_id"]],5),
						"view_url" => $this->mk_my_orb("show_entry", array("id" => $cur_form, "entry_id" => $row["entry_id"], "op_id" => $sh_ofs[$cur_form]["op_id_long"]),"form",false,true)
					));
					$dt.=$this->parse("PASSENGER");
				}
				$this->vars(array("PASSENGER" => $dt));
			}		
		}

		return $this->parse();
	}

	function passengers_detail($arr)
	{
		extract($arr);
		if ($print)
		{
			$this->read_template("admin_passengers_detail_print.tpl");
		}
		else
		{
			$this->read_template("admin_passengers_detail.tpl");
		}

		load_vcl("date_edit");
		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
		));
	
		$ipi = $this->get_item_picker();

		$t_from = mktime($from["hour"],$from["minute"],0,$from["month"],$from["day"],$from["year"]);
		$t_to = mktime($to["hour"],$to["minute"],0,$to["month"],$to["day"],$to["year"]);
		$this->vars(array(
			"t_from" => $de->gen_edit_form("from", $t_from),
			"t_to"	=> $de->gen_edit_form("to", $t_to),
			"reforb" => $this->mk_reforb("passengers_detail", array("shop_id" => $shop_id,"no_reforb" => true,"search" => true)),
			"arts" => $this->picker($art,$ipi),
			"all_art_types" => checked($all_art_types==1),
			"all_arts" => checked($all_arts==1),
		));

		if ($search)
		{
			$sh_ofs = $this->get_ofs_for_shop($shop_id);

			$itypes = $this->listall_item_types(ALL_PROPS);
			$all_items = $this->get_item_picker(array("type" => ALL_PROPS));

			$items = array();
			if ($all_arts) 
			{
				$items = $all_items;
			}
			else
			if ($art)
			{
				$items = $this->get_item_picker(array("type" => ALL_PROPS, "constraint" => " AND id = '$art'"));
			}

			$orders = array();
			// no distinct so we can figure out the start/end dates of the order
			// order by period so that from will always be before to. so we can avoid checking
			$this->db_query("SELECT * FROM order2item order by period");
			while ($row = $this->db_next())
			{
				// if we don't have the from date yet, then this is it
				if (!$orders[$row["order_id"]]["from"])
				{
					$orders[$row["order_id"]]["from"] = $row["period"];
				}
				else
				if ($row["period"] != $orders[$row["order_id"]]["from"])	
				{
					// if we already have it and this one is different then it must be the end date
					$orders[$row["order_id"]]["to"] = $row["period"];
				}

				// now we must check that just orders that contain the selected items go through
				if ($items[$row["item_id"]])
				{
					$orders[$row["order_id"]]["has_item"] = true;
				}

				if ($itypes[$all_items[$row["item_id"]]["type_id"]]["has_voucher"])	// it's a hotel. blerg.
				{
					$orders[$row["order_id"]]["room"] = $all_items[$row["item_id"]]["name"];
					// use parent cache and figure out the hotel name - ie the parent name
					if (!$parent_names[$row["item_id"]])
					{
						$this->save_handle();
						$parent_names[$row["item_id"]] = $this->db_fetch_field("SELECT name FROM objects WHERE oid = ".$all_items[$row["item_id"]]["parent"],"name");
						$this->restore_handle();
					}
					$orders[$row["order_id"]]["hotel"] = $parent_names[$row["item_id"]];
					$orders[$row["order_id"]]["hotel_form_id"] = $all_items[$row["item_id"]]["cnt_form"] ? $all_items[$row["item_id"]]["cnt_form"] : $itypes[$all_items[$row["item_id"]]["type_id"]]["cnt_form"];
					$orders[$row["order_id"]]["hotel_entry_id"] = $row["cnt_entry"];
					$orders[$row["order_id"]]["hotel_op_id"] = $all_items[$row["item_id"]]["cnt_extra_op"];
				}
			}

			// now leave out all the orders that don't contain any items
			$oids = array();
			foreach($orders as $oid => $odata)
			{
				// we do the time filtering here cause we can't do it in the query because then we might skip opver some important items
				if ($odata["has_item"] && $odata["from"] >= $t_from && $odata["from"] <= $t_to)
				{
					$oids[] = $oid;
				}
			}
			$oids = join(",",$oids);

			// first we must count the number of passengers for each order
			if ($oids != "")
			{
				$cnt_data = array();
				$this->db_query("SELECT count(entry_id) AS cnt,order_id as order_id FROM order2form_entries WHERE order_id IN ($oids) GROUP BY order_id");
				while ($row = $this->db_next())
				{
					$cnt_data[$row["order_id"]] = $row["cnt"];
				}

				$f = new form;

				$cur_order = 0;
				$cnt = 1;
				$this->db_query("SELECT * FROM order2form_entries WHERE order_id IN ($oids) order by order_id,name");
				while ($row = $this->db_next())
				{
					$f->reset();
					$this->vars(array(
						"nr" => $cnt++,
						"name" => $f->show(array("id" => $row["form_id"], "entry_id" => $row["entry_id"], "op_id" => $sh_ofs[$row["form_id"]]["op_id_search"])),
						"from" => $this->time2date($orders[$row["order_id"]]["from"],5),
						"to" => $this->time2date($orders[$row["order_id"]]["to"],5),
						"num_items" => (int)$cnt_data[$row["order_id"]],
						"ord_id" => $row["order_id"],
						"room" => $orders[$row["order_id"]]["room"],
						"hotel" => $orders[$row["order_id"]]["hotel"],
						"view_order" => $this->mk_my_orb("view_order", array("shop" => $shop_id, "order_id" => $row["order_id"]), "shop", false,true),
					));
					$no1 = "";
					$no2 = "";
					if ($row["order_id"] != $cur_order)
					{
						$f->reset();
						$info = "";
						if ($orders[$row["order_id"]]["hotel_op_id"] != "")
						{
							$info = $f->show(array(
								"id" => $orders[$row["order_id"]]["hotel_form_id"], 
								"entry_id" => $orders[$row["order_id"]]["hotel_entry_id"],
								"op_id" => $orders[$row["order_id"]]["hotel_op_id"]
							));
						}
						$this->vars(array("info" => $info));
						$no1 = $this->parse("N_ORD1");
						$no2 = $this->parse("N_ORD2");
						$cur_order = $row["order_id"];
					}
					$this->vars(array(
						"N_ORD1" => $no1,
						"N_ORD2" => $no2
					));
					$ps.=$this->parse("PASSENGER");
				}
			}
			$this->vars(array(
				"PASSENGER" => $ps,
				"sel_item" => $parent_names[$art]."/".$all_items[$art]["name"],
				"print_url" => $this->mk_my_orb("passengers_detail", $arr+array("print" => true), "",false, true)
			));
		}
		return $this->parse();
	}
}

?>