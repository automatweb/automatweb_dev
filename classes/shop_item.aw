<?php

global $orb_defs;
$orb_defs["shop_item"] = "xml";

class shop_item extends aw_template
{
	function shop_item()
	{
		$this->tpl_init("shop");
		$this->db_init();
		$this->sub_merge = 1;
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
			classload("form_base");
			$fb = new form_base;

			$op_list = $fb->get_op_list();

			$fl = $fb->get_list(FTYPE_ENTRY);
			reset($fl);
			while (list($id,) = each($fl))
			{
				$this->vars(array("form_id" => $id));
				if (is_array($op_list[$id]))
				{
					reset($op_list[$id]);
					$cnt = 0;
					$fop = "";
					while (list($op_id,$op_name) = each($op_list[$id]))
					{
						$this->vars(array("cnt" => $cnt, "op_id" => $op_id, "op_name" => $op_name));
						$fop.=$this->parse("FORM_OP");
						$cnt++;
					}
					$this->vars(array("FORM_OP" => $fop));
					$this->parse("FORM");
				}
			}

			$this->vars(array(
				"reforb" => $this->mk_reforb("new", array("parent" => $parent,"reforb" => 0,"step" => 2)),
				"flist" => $this->picker(0,$fl)
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

			$this->vars(array( 
				"item" => $f->gen_preview(array(
										"id" => $form_id,
										"reforb" => $this->mk_reforb("submit", 
																	array("parent" => $parent, "fid" => $form_id,"op_id" => $op_id,"op_id_l" => $op_id_l,"cnt_form" => $cnt_form))
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
			$o = $this->get($id);
			$f->process_entry(array("id" => $o["form_id"],"entry_id" => $o["entry_id"]));
//			$this->upd_object(array("oid" => $id, "name" => $f->get_element_value_by_name("nimi")));
			// kui itemi nimi muutub, siis muutub vendadel ka
			$name = $f->get_element_value_by_name("nimi");
			$this->db_query("UPDATE objects SET name = '$name',modified = '".time()."', modifiedby = '".$GLOBALS["uid"]."' WHERE brother_of = $id ");
			$price = $f->get_element_value_by_type("price");
			$this->db_query("UPDATE shop_items SET price='$price' WHERE id = $id");
		}
		else
		{
			$f->process_entry(array("id" => $fid));
			$eid = $f->entry_id;

			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_SHOP_ITEM, "status" => 2, "name" => $f->get_element_value_by_name("nimi")));
			$price = $f->get_element_value_by_type("price");
			$this->db_query("INSERT INTO shop_items(id,form_id,entry_id,op_id,price,op_id_l,cnt_form) values($id,'$fid','$eid','$op_id','$price','$op_id_l','$cnt_form')");

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
		$o = $this->get_object($id);
		$id = $o["brother_of"];	// this is in case we clicked on a brother to change it, we must revert to the real object

		$o = $this->get($id);

		$this->mk_path($o["parent"],"Muuda kaupa");

		$this->read_template("edit_item.tpl");

		classload("form");
		$f = new form;

		classload("objects");
		$ob = new db_objects;

		$this->vars(array( 
			"item" => $f->gen_preview(array(
										"id" => $o["form_id"],
										"entry_id" => $o["entry_id"],	
										"reforb" => $this->mk_reforb("submit", array("id" => $id))
								)),
			"menus" => $this->multiple_option_list($this->get_brother_list($id),$ob->get_list()),
			"reforb" => $this->mk_reforb("submit_bros", array("id" => $id))
		));
		return $this->parse();
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
		$o = $this->get($id);
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

	function get($id)
	{
		$this->db_query("SELECT objects.*,shop_items.* FROM objects LEFT JOIN shop_items ON shop_items.id = objects.oid WHERE objects.oid = $id");
		return $this->db_next();
	}
}
?>
