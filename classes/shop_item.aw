<?php

global $orb_defs;
$orb_defs["shop_item"] = "xml";

define("PRICE_PER_WEEK",1);
define("PRICE_PER_2WEEK",2);

define("PR_PER_PAGE" , 12);

classload("shop_base");
class shop_item extends shop_base
{
	function shop_item()
	{
		$this->shop_base();
		lc_load("shop");
		global $lc_shop;
		if (is_array($lc_shop))
		{
			$this->vars($lc_shop);
		}
	}

	////
	// !asks the user which form to use for adding an item and then shows the form
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, LC_SHOP_ITEM_ADD_PRODUCT);
		if ($step < 2)
		{
			$this->read_template("add_item_form.tpl");

			$this->vars(array(
				"reforb" => $this->mk_reforb("new", array("parent" => $parent,"reforb" => 0,"step" => 2)),
				"types" => $this->picker(0,$this->listall_item_types(FOR_SELECT)),
				"to_shop" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$parent
			));
			return $this->parse();
		}
		else
		{
			$this->read_template("edit_item.tpl");

			classload("form");
			$f = new form;

			classload("objects");
			$o = new db_objects;

			$itt = $this->get_item_type($type);

			$this->vars(array( 
				"item" => $f->gen_preview(array(
										"id" => $itt["form_id"],
										"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "type" => $type)),
										"to_shop" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$parent
									)),
				"menus" => $this->multiple_option_list(array(),$o->get_list())
			));
			return $this->parse();
		}
	}

	////
	// !saves the data
	function submit($arr)
	{
		extract($arr);
		classload("form");
		$f = new form;

		if ($id)
		{
			$o = $this->get_item($id,true);
			$itt = $this->get_item_type($o["type_id"]);

			$f->process_entry(array("id" => $itt["form_id"],"entry_id" => $o["entry_id"]));
			$eid = $f->entry_id;
			// kui itemi nimi muutub, siis muutub vendadel ka
			$name = $f->get_element_value_by_name("nimi");
			$this->db_query("UPDATE objects SET name = '$name',modified = '".time()."', modifiedby = '".$GLOBALS["uid"]."' WHERE brother_of = $id ");
			$price = $f->get_element_value_by_type("price");

			$this->db_query("UPDATE shop_items SET price='$price',entry_id = '$eid' WHERE id = $id");
		}
		else
		{
			$itt = $this->get_item_type($type);

			$f->process_entry(array("id" => $itt["form_id"]));
			$eid = $f->entry_id;

			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_SHOP_ITEM, "status" => 2, "name" => $f->get_element_value_by_name("nimi")));
			$price = $f->get_element_value_by_type("price");
			$this->db_query("INSERT INTO shop_items(id,price,type_id,entry_id) values($id,'$price','$type','$eid')");

			// now also set the item to be a brother of itself, so we can user brother_of for joining purposes l8r
			$this->upd_object(array("oid" => $id, "brother_of" => $id));
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	////
	// !shows the form for changing the data
	function change($arr)
	{
		extract($arr);
		$o = $this->get_item($id,true);
		
		$itt = $this->get_item_type($o["type_id"]);

		$this->mk_path($o["parent"],LC_SHOP_ITEM_CHANGE_PRODUCT);

		$this->read_template("edit_item.tpl");

		classload("form");
		$f = new form;

		$shcats = array("0" => "");
		classload("shop");
		$shop = new shop;
		$shs = $shop->get_list();	// list shops
		foreach($shs as $sh_id => $sh_name)
		{
			$shcats = $shcats + $shop->get_shop_categories($sh_id);
		}

		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
			"hour" => "", 
			"minute" => ""
		));

		classload("form_base");
		$fb = new form_base;
		$fl = $fb->get_list(FTYPE_ENTRY,true);

		$eq = $this->get_eq($itt["eq_id"]);
		$this->vars(array( 
			"item" => $f->gen_preview(array(
										"id" => $itt["form_id"],
										"entry_id" => $o["entry_id"],	
										"reforb" => $this->mk_reforb("submit", array("id" => $id))
								)),
			"menus" => $this->multiple_option_list($this->get_brother_list($id),$shcats),
			"reforb" => $this->mk_reforb("submit_bros", array("id" => $id)),
			"redir" => $this->picker($o["redir"], $shcats),
			"reforb2" => $this->mk_reforb("submit_redir", array("id" => $id)),
			"reforb3" => $this->mk_reforb("submit_opts", array("id" => $id)),
			"has_max" => checked($o["has_max"]),
			"max_items" => $o["max_items"],
			"has_period" => checked($o["has_period"]),
			"has_objs" => checked($o["has_objs"]),
			"price_eq" => $o["price_eq"],
			"type" => $itt["name"],
			"price_eq" => $eq["name"],
			"per_from" => $de->gen_edit_form("per_from", $o["per_from"],2001,2010),
			"sel_period" => $this->mk_my_orb("repeaters", array("id" => $o["per_event_id"]),"planner"),
			"per_cnt" => $o["per_cnt"],
			"per_prices" => $this->mk_my_orb("set_per_prices", array("id" => $id)),
			"to_shop" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$o["parent"],
			"show_free" => $this->mk_my_orb("show_free", array("id" => $id)),
			"cnt_form" => $this->picker($o["cnt_form"], $fl),
			"item_eq" => $this->picker($o["price_eq"], $this->listall_eqs(true)),
		));
		return $this->parse();
	}

	////
	// !saves info about where to redirect after the customer orders the item
	function submit_redir($arr)
	{
		extract($arr);
		$this->db_query("UPDATE shop_items SET redir = '$redir' WHERE id = '$id'");
		return $this->mk_my_orb("change", array("id" => $id));
	}

	////
	// !returns an array of all the brothers of this item
	function get_brother_list($id)
	{
		$ret = array();
		$this->db_query("SELECT parent,oid FROM objects WHERE class_id = ".CL_SHOP_ITEM." AND status != 0 AND brother_of = $id");
		while ($row = $this->db_next())
		{
			if ($row["oid"] != $id)
			{
				$ret[$row["parent"]] = $row["parent"];
			}
		}
		return $ret;
	}

	////
	// !saves the brothers of the shop_item
	// argumendid
	// id(int) - selle objekti ID, millest me vendasid looma hakkame
	// menus(array) - nende menyyde ID-d, mille alla vennad luua tuleb
	function submit_bros($arr)
	{
		extract($arr);
		$o = $this->get_item($id);
		// ok so how do we do this? well. what if, for each brother of this, 
		// we create a CL_SHOP_ITEM and set it's brother_of to point to this item
		// let's try this shit.
		$selmenus = array();

		if (is_array($menus))
		{
			// olemasolevate vendade list?
			$brol = $this->get_brother_list($id);

			// $selmenus sisaldab koigi nende menüüde, mille alla vennastatakse
			// id-sid nii keyde kui ka väärtustena
			foreach($menus as $menu_id)
			{
				$selmenus[$menu_id] = $menu_id;
			}

		
			foreach($menus as $menu_id)
			{
				// if the menu is not a brother yet, add it
				if ($brol[$menu_id] != $menu_id)
				{
					// create a new object for the brother
					$oid = $this->new_object(array(
						"parent" => $menu_id,
						"class_id" => CL_SHOP_ITEM,
						"name" => $o["name"],
						"status" => $o["status"],
						"brother_of" => $id
					));
					// and mark it down
					$brol[$menu_id] = $menu_id;
				}
			}

			// now we must see if any elements were removed
			// and since $selmenus contains all the menus that were selected
			// and $brol contains all the menus that have brothers ($brol is greater or equal to $selmenus)
			// we simply do an array_diff on them and find out the menus that changed.
			$toremove = array_diff($brol,$selmenus); // $brol - $selmenus :)
			foreach($toremove as $menu_id)
			{
				// now we must delete the brother for this object that is present under menu $menu_id
				// kas vendasid ei peaks lihtsalt maha votma, selle asemel, et neid dekatiivseks määrata?
				$this->db_query("UPDATE objects SET status = 0 WHERE class_id = ".CL_SHOP_ITEM." AND brother_of = $id AND parent = $menu_id ");
			}
			// and now the brothers should be up to date....
		}
		else
		{
			// just delete all brothaz
			$this->db_query("UPDATE objects SET status = 0 WHERE class_id = ".CL_SHOP_ITEM." AND brother_of = $id");
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function submit_opts($arr)
	{
		extract($arr);

		// siin peame tegema uued objektid ja v2rgid ka
		$o = $this->get_item($id);

		classload("planner");
		if ($has_objs)
		{
			$num_items = $this->db_fetch_field("SELECT count(*) AS cnt FROM objects WHERE parent = $id AND class_id = ".CL_SHOP_ITEM." AND status != 0","cnt");
			// child itemid on seotu objects::parent j2rgi.
			if ($num_items > $max_items)
			{
				// enne oli seda objekti rohkem kui preagu, j2relikult kustutame osa 2ra..
				// hmh, millised? well. ntx esimesed n. 
				$diff = $num_items - $max_items;
				$this->db_query("SELECT oid FROM objects WHERE parent = $id AND class_id = ".CL_SHOP_ITEM." AND status != 0 LIMIT $diff");
				while ($row = $this->db_next())
				{
					$its[] = $row["oid"];
				}
				$its = join(",",$its);
				if ($its != "")
				{
					$this->db_query("UPDATE objects SET status = 0 WHERE oid IN($its)");
				}
			}
			else
			if ($num_items < $max_items)
			{
				$pl = new planner;

				// olemas on v2hem kui vaja, teeme uusi
				$diff = $max_items - $num_items;
				for ($i=0; $i < $diff; $i++)
				{
					// teeme uue child objekti
					$ch_id = $this->new_object(array(
						"parent" => $id,
						"class_id" => CL_SHOP_ITEM,
						"status" => 2
					));
					// paneme talle kalendri kylge.
					$pl->submit_add(array("parent" => $ch_id));
					// paneme tehtud kalendri id objekti last v2lja kirja ka
					$this->upd_object(array("oid" => $ch_id, "last" => $pl->id));
				}
			}
			// nyt peax olema 6ige arv child objekte
		}
		else
		if ($has_period)
		{
			// read the period start time
			$per_from = mktime($per_from["hour"],$per_from["minute"],0,$per_from["month"],$per_from["day"],$per_from["year"]);

			$calendar_id = $o["calendar_id"];
			$event_id = $o["per_event_id"];
			$pl = new planner;
			// has period but no objects, then it must have just one calendar attached to it. create it if it doesn't exist
			if (!$o["calendar_id"])
			{
				$pl->submit_add(array("parent" => $id));
				$calendar_id = $pl->id;

				// also add one event to the calendar as the start of the periodics
				// lengh 1 second, just to have something there
				$event_id = $pl->add_event(array("parent" => $calendar_id,"start" => $per_from, "end" => $per_from+1));			
			}
			else
			if ($per_from != $o["per_from"])
			{
				// the period start date has changed. 
				// delete the old event and add a new one with the correct start time
				$this->db_query("UPDATE objects SET status = 1 WHERE class_id = ".CL_CAL_EVENT." AND parent = ".$calendar_id);

				$event_id = $pl->add_event(array("parent" => $calendar_id,"start" => $per_from, "end" => $per_from+1));
			}
		}

		$this->db_query("UPDATE shop_items SET has_max = '$has_max',max_items = '$max_items',has_period = '$has_period' , has_objs = '$has_objs' , calendar_id = '$calendar_id',per_from = '$per_from',per_event_id = '$event_id',per_cnt = '$per_cnt',cnt_form = '$cnt_form', price_eq = '$item_eq' WHERE id = $id");

		return $this->mk_my_orb("change", array("id" => $id));
	}

	////
	// !allows the user to define different prices for item $id for different periods
	function set_per_prices($arr)
	{
		extract($arr);
		$it = $this->get_item($id);
		$this->mk_path($it["parent"],"<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Muuda</a> / Muuda hindu");
		$this->read_template("set_per_prices.tpl");

		classload("currency");
		$cur = new currency;
		$cur_list = $cur->get_list();

		load_vcl("date_edit");
		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => ""
		));

		foreach($cur_list as $oid => $name)
		{
			$this->vars(array(
				"cur_name" => $name
			));
			$this->parse("CUR_H");
		}

		$count = $this->db_fetch_field("SELECT COUNT(*) AS cnt FROM  shop_item2per_prices WHERE item_id = $id", "cnt");
		$num_pages = $count / PR_PER_PAGE;
		for ($i=0; $i < $num_pages; $i++)
		{
			$this->vars(array(
				"from" => $i*PR_PER_PAGE,
				"to" => min(($i+1)*PR_PER_PAGE,$count),
				"pageurl" => $this->mk_my_orb("set_per_prices", array("id" => $id, "page" => $i))
			));
			if ($i == $page)
			{
				$pp.=$this->parse("SEL_PAGE");
			}
			else
			{
				$pp.=$this->parse("PAGE");
			}
		}

		$this->vars(array(
			"SEL_PAGE" => $pp,
			"PAGE" => ""
		));

		$this->db_query("SELECT * FROM shop_item2per_prices WHERE item_id = $id LIMIT ".($page*PR_PER_PAGE).",".PR_PER_PAGE);
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"from" => $de->gen_edit_form("from[".$row["id"]."]",$row["tfrom"]),
				"to" => $de->gen_edit_form("to[".$row["id"]."]",$row["tto"]),
				"id" => $row["id"],
				"week_check" => checked($row["per_type"] == PRICE_PER_WEEK),
				"2week_check" => checked($row["per_type"] == PRICE_PER_2WEEK)
			));
			$prices = unserialize($row["price"]);
			$cc = "";
			foreach($cur_list as $oid => $c_name)
			{
				$price = is_array($prices) ? $prices[$oid] : $row["price"];

				$this->vars(array(
					"cur_id" => $oid,
					"price" => $price
				));
				$cc.=$this->parse("CUR");
			}
			$this->vars(array("CUR" => $cc));
			$per.=$this->parse("PERIOD");
		}

		if (($page+1)*PR_PER_PAGE >= $count)
		{
			$this->vars(array(
				"from" => $de->gen_edit_form("from[0]",time()),
				"to" => $de->gen_edit_form("to[0]",time()),
				"price" => 0,
				"id" => 0,
				"week_check" => "",
				"2week_check" => ""
			));
			$cc = "";
			foreach($cur_list as $oid => $c_name)
			{
				$this->vars(array(
					"cur_id" => $oid,
					"price" => 0
				));
				$cc.=$this->parse("CUR");
			}
			$this->vars(array("CUR" => $cc));
			$per.=$this->parse("PERIOD");
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_per_prices", array("id" => $id,"page" => $page)),
			"PERIOD" => $per
		));
		return $this->parse();
	}

	function submit_per_prices($arr)
	{
		extract($arr);
		
		$this->db_query("SELECT * FROM shop_item2per_prices WHERE item_id = $id");
		while ($row = $this->db_next())
		{
			if (isset($price_type[$row["id"]]))
			{
				$tfrom = mktime(0,0,0,$from[$row["id"]]["month"],$from[$row["id"]]["day"],$from[$row["id"]]["year"]);
				$tto = mktime(0,0,0,$to[$row["id"]]["month"],$to[$row["id"]]["day"],$to[$row["id"]]["year"]);
				$this->save_handle();
				$pricsstr = serialize($price[$row["id"]]);
				$this->quote(&$pricsstr);
				$this->db_query("UPDATE shop_item2per_prices SET tfrom = $tfrom, tto = $tto, price ='".$pricsstr."',per_type = '".$price_type[$row["id"]]."' WHERE id = ".$row["id"]);
				$this->restore_handle();
			}
		}

		if (is_array($del))
		{
			foreach($del as $did => $o)
			{
				$this->db_query("DELETE FROM shop_item2per_prices WHERE id = $did");
			}
		}

		if ($price_type[0] > 0)
		{
			// lisame uue ka
			$tfrom = mktime(0,0,0,$from[0]["month"],$from[0]["day"],$from[0]["year"]);
			$tto = mktime(0,0,0,$to[0]["month"],$to[0]["day"],$to[0]["year"]);
			$pricsstr = serialize($price[0]);
			$this->quote(&$pricsstr);
			$this->db_query("INSERT INTO shop_item2per_prices(item_id,tfrom,tto,price,per_type) VALUES($id,$tfrom,$tto,'".$pricsstr."','".$price_type[0]."')");
		}
		return $this->mk_my_orb("set_per_prices", array("id" => $id,"page" => $page));
	}

	function check_environment(&$sys, $fix = false)
	{
		$op_table = array(
			"name" => "shop_items", 
			"fields" => array(
				"id" => array("name" => "id", "length" => 11, "type" => "int", "flags" => ""),
				"form_id" => array("name" => "form_id", "length" => 11, "type" => "int", "flags" => ""),
				"entry_id" => array("name" => "entry_id", "length" => 11, "type" => "int", "flags" => ""),
				"op_id" => array("name" => "op_id", "length" => 11, "type" => "int", "flags" => ""),
				"price" => array("name" => "price", "length" => 22, "type" => "real", "flags" => ""),
				"op_id_l" => array("name" => "op_id_l", "length" => 11, "type" => "int", "flags" => ""),
				"cnt_form" => array("name" => "cnt_form", "length" => 11, "type" => "int", "flags" => ""),
				"redir" => array("name" => "redir", "length" => 11, "type" => "int", "flags" => ""),
				"has_max" => array("name" => "has_max", "length" => 11, "type" => "int", "flags" => ""),
				"max_items" => array("name" => "max_items", "length" => 11, "type" => "int", "flags" => ""),
				"has_period" => array("name" => "has_period", "length" => 11, "type" => "int", "flags" => ""),
				"has_objs" => array("name" => "has_objs", "length" => 11, "type" => "int", "flags" => ""),
				"price_eq" => array("name" => "price_eq", "length" => 65535, "type" => "blob", "flags" => ""),
				"type_id" => array("name" => "type_id", "length" => 11, "type" => "int", "flags" => ""),
				"sold_items" => array("name" => "sold_items", "length" => 11, "type" => "int", "flags" => ""),
				"calendar_id" => array("name" => "calendar_id", "length" => 11, "type" => "int", "flags" => ""),
				"per_from" => array("name" => "per_from", "length" => 11, "type" => "int", "flags" => ""),
				"per_event_id" => array("name" => "per_event_id", "length" => 11, "type" => "int", "flags" => ""),
				"per_cnt" => array("name" => "per_cnt", "length" => 11, "type" => "int", "flags" => ""),
			)
		);

		$op2_table = array(
			"name" => "shop_item2per_prices", 
			"fields" => array(
				"id" => array("name" => "id", "length" => 11, "type" => "int", "flags" => ""),
				"item_id" => array("name" => "item_id", "length" => 11, "type" => "int", "flags" => ""),
				"tto" => array("name" => "tto", "length" => 11, "type" => "int", "flags" => ""),
				"price" => array("name" => "price", "length" => 22, "type" => "real", "flags" => ""),
				"per_type" => array("name" => "per_type", "length" => 11, "type" => "int", "flags" => ""),
			)
		);

		$ret = $sys->check_admin_templates("shop", array("add_item_form.tpl","edit_item.tpl","set_per_prices.tpl"));
		$ret.= $sys->check_orb_defs(array("shop_item"));
		$ret.= $sys->check_db_tables(array($op_table,$op2_table),$fix);

		return $ret;
	}

	function show_free($arr)
	{
		extract($arr);
		$this->read_template("show_free.tpl");
		$it = $this->get_item($id);
		$this->mk_path($it["parent"], "<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Muuda</a> / Vaata vabu");

		if ($it["has_period"])
		{
			$shop = $this->find_shop_id($it["parent"]);

			$this->db_query("SELECT * FROM shop_item_period_avail WHERE item_id = $id ");
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"period" => $this->time2date($row["period"], 5),
					"period_end" => $this->time2date($row["period"]+24*7*3600, 5),
					"num_sold" => $row["num_sold"],
					"free" => $it["max_items"] - $row["num_sold"],
					"view" => $this->mk_my_orb("admin_item_orders", array("id" => $shop,"period" => $row["period"],"item_id" => $id),"shop")
				));
				$this->parse("LINE");
			}
		}
		
		$this->vars(array(
			"t_sold" => $it["sold_items"]
		));
		return $this->parse();
	}
}
?>
