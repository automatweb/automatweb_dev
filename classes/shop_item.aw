<?php

global $orb_defs;
$orb_defs["shop_item"] = "xml";

classload("shop_base");
class shop_item extends shop_base
{
	function shop_item()
	{
		$this->shop_base();
	}

	////
	// !asks the user which form to use for adding an item and then shows the form
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, "Lisa kaup");
		if ($step < 2)
		{
			$this->read_template("add_item_form.tpl");

			$this->vars(array(
				"reforb" => $this->mk_reforb("new", array("parent" => $parent,"reforb" => 0,"step" => 2)),
				"types" => $this->picker(0,$this->listall_item_types(FOR_SELECT))
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
										"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "type" => $type))
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
			$o = $this->get_item($id);
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
		return $this->mk_orb("change", array("id" => $id));
	}

	////
	// !shows the form for changing the data
	function change($arr)
	{
		extract($arr);
		$o = $this->get_item($id);
		if ($o["brother_of"] != $id)
		{
			$id = $o["brother_of"];	// this is in case we clicked on a brother to change it, we must revert to the real object
			$o = $this->get_item($id);
		}
		
		$itt = $this->get_item_type($o["type_id"]);

		$this->mk_path($o["parent"],"Muuda kaupa");

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
			"types" => $this->picker($o["type_id"],$this->listall_item_types())
		));
		return $this->parse();
	}

	////
	// !saves info about where to redirect after the customer orders the item
	function submit_redir($arr)
	{
		extract($arr);
		$this->db_query("UPDATE shop_items SET redir = '$redir' WHERE id = '$id'");
		return $this->mk_orb("change", array("id" => $id));
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
		return $this->mk_orb("change", array("id" => $id));
	}

	function submit_opts($arr)
	{
		extract($arr);

		// siin peame tegema uued objektid ja v2rgid ka
		$o = $this->get_item($id);

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
				classload("planner");
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

		$this->db_query("UPDATE shop_items SET price='$price',has_max = '$has_max',max_items = '$max_items',has_period = '$has_period' , has_objs = '$has_objs' , price_eq = '$price_eq' WHERE id = $id");

		if ($type != $o["type_id"])
		{
			$this->db_query("UPDATE shop_items SET type_id = '$type', entry_id = '', price = '' WHERE id = $id");
		}

		return $this->mk_orb("change", array("id" => $id));
	}
}
?>
