<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/shop.aw,v 2.36 2001/10/02 10:05:53 kristo Exp $
// shop.aw - Shop
lc_load("shop");
global $orb_defs;
$orb_defs["shop"] = "xml";

session_register("shopping_cart");

// the layout of $shopping_cart is this:
// 
//	$shopping_cart["items"][$oid]["cnt"] - count of item $oid
//	$shopping_cart["items"][$oid]["price"] -  price of item $oid , 
//																						not to be trusted when calculating final payment, good enough
//																						for showing the user the total in cart at the moment
//	$shopping_cart["items"][$oid]["name"] - name of item $oid
//	$shopping_cart["items"][$oid]["cnt_entry"] - form_entry id of the form filled to specify count and type of items
//  $shopping_cart["price"] = total price of items in cart, not to be trusted, payment total should be calculated from database

define("ORD_FILLED",1);

define("SHOP_PER_PAGE",10);

//lc_load("shop");
classload("shop_base");
classload("shop_item");
class shop extends shop_base
{
	function shop()
	{
		$this->shop_base();
		lc_site_load("shop",&$this);
		lc_load("shop");
		$this->shop_menus = "";
		global $lc_shop;
		if (is_array($lc_shop))
		{
			$this->vars($lc_shop);
		
	lc_load("definition");
	}
	}

	////
	// !generates the form for adding shops
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,"Add target");
		$this->read_template("add.tpl");

		classload("objects");
		$ob = new db_objects;

		classload("form_base");
		$fb = new form_base;
		$fl = $fb->get_list(FORM_ENTRY);

		classload("currency");
		$cu = new currency;

		$eq = $this->listall_eqs(false);
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"root" => $this->picker($parent,$ob->get_list()),
			"of" => $this->multiple_option_list(0,$fl),
			"forms" => $this->picker(0,$fl),
			"commission_eq" => $this->picker(0, $eq),
			"nump_eq" => $this->picker(0, $eq),
			"def_cur" => $this->picker(0,$cu->get_list())
		));
		return $this->parse();
	}

	////
	// !generates the form for changing shop $id
	function change($arr)
	{
		extract($arr);
		$oba = $this->get($id);
		$this->mk_path($oba["parent"], "Change target");
		$this->read_template("add.tpl");

		classload("objects");
		$ob = new db_objects;

		classload("form_base");
		$fb = new form_base;

		classload("form_base");
		$fb = new form_base;

		$op_list = $fb->get_op_list();

		$fl = $fb->get_list(FTYPE_ENTRY);
		reset($fl);
		while (list($tid,) = each($fl))
		{
			if (!$form_id)
			{
				$form_id = $tid;
			}
			$this->vars(array("form_id" => $tid));
			if (is_array($op_list[$tid]))
			{
				reset($op_list[$tid]);
				$cnt = 0;
				$fop = "";
				while (list($op_id,$op_name) = each($op_list[$tid]))
				{
					$this->vars(array("cnt" => $cnt, "op_id" => $op_id, "op_name" => $op_name));
					$fop.=$this->parse("FORM_OP");
					$cnt++;
				}
				$this->vars(array("FORM_OP" => $fop));
				$this->parse("FORM");
			}
		}

		classload("currency");
		$cu = new currency;

		$eq = $this->listall_eqs(false);
		$this->vars(array(
			"name" => $oba["name"],
			"comment" => $oba["comment"],
			"root" => $this->picker($oba["root_menu"],$ob->get_list()),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"stat_by_turnover" => $this->mk_my_orb("turnover_stat", array("id" => $id)),
			"orders" => $this->mk_my_orb("admin_orders", array("id" => $id)),
			"emails" => $oba["emails"],
			"forms" => $this->picker($oba["owner_form"], $fl),
			"o_form_id" => $oba["owner_form"],
			"o_op_id" => $oba["owner_form_op"],
			"ch_own" => $this->mk_my_orb("ch_owner_data", array("id" => $id)),
			"tables" => $this->mk_my_orb("change_tables", array("id" => $id)),
			"o_op_id_voucher" => $oba["owner_form_op_voucher"],
			"o_op_id_issued" => $oba["owner_form_op_issued"],
			"commission_eq" => $this->picker($oba["commission_eq"], $eq),
			"nump_eq" => $this->picker($oba["nump_eq"], $eq),
			"def_cur" => $this->picker($oba["def_cur"],$cu->get_list())
		));
		if ($oba["owner_form"])
		{
			$this->parse("CH_OWN");
		}
		$this->parse("CHANGE");
		$this->parse("CHANGE2");

		$this->do_core_admin_ofs($id);

		return $this->parse();
	}

	////
	// !saves the data for the shop
	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$sh = $this->get($id);
			if ($sh["owner_form"] != $owner_form)
			{
				// if owner form has changed, zero out the form entry, cause it belongs to the prev. form
				$ss = ", owner_form_entry = '' ";
			}
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
			$this->db_query("UPDATE shop SET root_menu = $root, emails='$emails',owner_form = '$owner_form',owner_form_op = '$owner_form_op',owner_form_op_voucher = '$owner_form_op_voucher',owner_form_op_issued = '$owner_form_op_issued',commission_eq ='$commission_eq',nump_eq = '$nump_eq',def_cur='$def_cur' $ss WHERE id = $id");
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_SHOP, "status" => 1, "name" => $name, "comment" => $comment));
			$this->db_query("INSERT INTO shop(id,root_menu,emails,owner_form,owner_form_op,commission_eq,nump_eq,def_cur) VALUES($id,'$root','$emails','$owner_form','$owner_form_op','$commission_eq','$nump_eq','$def_cur')");
		}

		// update order_forms
		$this->do_core_save_ofs($id,$order_form);

		return $this->mk_my_orb("change", array("id" => $id));
	}

	////
	// !shows the form for entering shop owner data
	function ch_owner_data($arr)
	{
		extract($arr);
		$sh = $this->get($id);
		$this->mk_path($sh["parent"],"<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Change target</a> / Change shop owners data");

		classload("form");
		$f = new form;
		return $f->gen_preview(array(
							"id" => $sh["owner_form"],
							"entry_id" => $sh["owner_form_entry"],
							"reforb" => $this->mk_reforb("submit_owner_data", array("id" => $id))
						));
	}

	////
	// !saves the sop owner data
	function submit_owner_data($arr)
	{
		extract($arr);
		$sh = $this->get($id);

		classload("form");
		$f = new form;
		$f->process_entry(array("id" => $sh["owner_form"], "entry_id" => $sh["entry_id"]));

		$this->db_query("UPDATE shop SET owner_form_entry = ".$f->entry_id." WHERE id = $id");

		return $this->mk_my_orb("ch_owner_data", array("id" => $id));
	}

	////
	// !returns a list of available shops
	function get_list()
	{
		$ret = array();
		$this->db_query("SELECT oid,name FROM objects WHERE class_id = ".CL_SHOP." AND status != 0");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}

	function do_shop_menus($shop,$parent)
	{
		$s = $this->get($shop);
		$this->vars(array("shop" => $shop));
		if (!$parent)
		{
			$parent = $s["root_menu"];
		}

		// we must detect if we are outside of the shop menu hierarchy
		// make yah link
		global $baseurl,$ext;
		$p = $parent;
		$y = "";
		$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE oid=$p");
		$op = $this->db_next();
		while ($p != $s["root_menu"] && $p)
		{
			if ($op["link"] != "")
			{
				$link = $op["link"];
			}
			else
			{
				$link = $baseurl."/";
				$link .= ($op["alias"] != "") ? $op["alias"] : "index." . $ext . "/section=" . $op["oid"];
			}
			$this->vars(array(
				"id" => $op["oid"],
				"name" => $op["name"],
				"yah_link" => $link
			));
			$y = $this->parse("YAH").$y;
			$p = $op["parent"];
			$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE oid=$p");
			$op = $this->db_next();
		}

		if ($p != $s["root_menu"])
		{
			// we are outside of the menu hierarchy and therefore must redo the YAH link
			$y = "";
			$parent = $s["root_menu"];
		}

		// some of the shop categories
		$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE parent = $parent AND class_id = ".CL_PSEUDO." AND status = 2 ORDER BY objects.jrk");
		while ($row = $this->db_next())
		{
			if (!$this->can("view",$row["oid"]))
			{
				continue;
			};

			if ($row["link"] != "")
			{
				$link = $row["link"];
			}
			else
			{
				$link = $baseurl."/";
				$link .= ($row["alias"] != "") ? $row["alias"] : "index." . $ext . "/section=" . $row["oid"];
			}
			$this->vars(array(
				"name" => $row["name"], 
				"id" => $row["oid"],
				"cat_link" => $link
			));
			if ($row["clickable"])
			{
				$this->shop_menus.=$this->parse("CAT");
			}
			else
			{
				$this->shop_menus.=$this->parse("CAT_NOCLICK");
			}
		}
		$this->vars(array(
				"SHOW_CAT" => $this->shop_menus,
				"CAT" => "",
				"CAT_NOCLICK" => ""
		));

		$this->vars(array(
			"YAH" => $y, 
			"fp" => $s["root_menu"],
			"s_name" => $s["name"],
			"section" => $parent,
			"location" => $this->mk_my_orb("show", array("id" => $shop, "section" => $s["root_menu"]))
		));
		return $parent;
	}

	////
	// !returns the currently active currency
	function get_act_cur($shop)
	{
		classload("users");
		$u = new users;
		$ucur = $u->get_user_config(array("uid" => $GLOBALS["uid"], "key" => "user_currency"));
		if (!$ucur)
		{
			$ucur = $shop["def_cur"];
		}
		return $ucur;
	}

	////
	// !shows the shop $id on the user side, items under category $parent
	function show($arr)
	{
		extract($arr);
		$this->read_template("show_shop.tpl");

		if (!$id)
		{
			$id = $this->find_shop_id($section);
		}

		// tshekime et kas 2kki on menyy propertites 8eldud et see menyy on selline kus on poe asjad paralleelselt
		// ja kui on, siis joonistame paralleelselt or something
		$spl = $this->db_fetch_field("SELECT shop_parallel FROM menu WHERE id = $section","shop_parallel");
		if ($spl)
		{
			return $this->order_item(array("shop" => $id, "section" => $section, "parallel" => true));
		}


		$shop = $this->get($id);

		global $shopping_cart;
		$section = $this->do_shop_menus($id,$section);

		classload("form");
		classload("item_type");
		$ityp = new item_type;
		$ityp->mk_cache();		// cacheme k6ik tyybid selle klassi sees, et p2rast ei peaks iga itemi kohta eraldi p2ringut tegema

		$min_p = $this->get_cart_min_period();
		$has_items = false;
		$q = "SELECT objects.brother_of as oid,shop_items.* FROM objects LEFT JOIN shop_items ON shop_items.id = objects.brother_of WHERE parent = $section AND class_id = ".CL_SHOP_ITEM." AND status = 2";
		$this->db_query($q);

		$ucur = $this->get_act_cur($shop);

		$c = "";
		$has_items = 0;
		while ($row = $this->db_next())
		{
			$this->save_handle();
			if ($this->is_item_available($row,1,new form(), $min_p))
			{
				$itt = $ityp->get_item_type($row["type_id"]);

				$it_price = $this->get_list_price($row["oid"],$row["price"],$shop);

				$f = new form;
				$f->set_active_currency($ucur);
				$f->load($itt["form_id"]);
				$f->load_entry($row["entry_id"]);
				$f->set_element_value_by_type("price",$it_price);

				$this->vars(array(
					"item" => $f->show(array("id" => $itt["form_id"], "entry_id" => $row["entry_id"],"op_id" => $itt["short_op"],"no_load_entry" => true)),
					"item_id" => $row["oid"],
					"price" => $it_price, 
					"it_cnt"	=> $shopping_cart["items"][$row["oid"]]["cnt"],
					"order_item" => $this->mk_my_orb("order_item", array("item_id" => $row["oid"], "shop" => $id, "section" => $section)),
					"change" => $this->mk_my_orb("change", array("id" => $row["oid"]),"shop_item")
				));
				$tp+=(double)$shopping_cart["items"][$row["oid"]]["cnt"]*(double)$row["price"];	// selle arvutame p2rast kogusummast maha
																																				// et saada korvi hind = baashind + selle lehe asjade hind
				$c .= $this->parse("ITEM");
				$has_items = 1;
			}
			else
			{
				if ($has_items == 0)
				{
					$has_items = 2;
				}
			}
			$this->restore_handle();
		}
		if ($has_items == 2)
		{
			$c = $this->parse("NOT_AVAILABLE");
		}
		$this->vars(array(
			"tot_price" => (double)$shopping_cart["price"]-(double)$tp,	
			"reforb" => $this->mk_reforb("add_cart", array("shop_id" => $id, "section" => $section)),
			"cart" => $this->mk_site_orb(array("action" => "view_cart", "shop_id" => $id, "section" => $section)),
			"ITEM" => $c,
			"NOT_AVAILABLE" => "",
			"add_item" => $this->mk_my_orb("new", array("parent" => $section),"shop_item"),
			"HAS_ITEMS" => ($has_items ? $this->parse("HAS_ITEMS") : ""),
			"cancel_current" => $this->mk_my_orb("cancel_current", array("shop" => $id, "section" => $section))
		));
		return $this->parse();
	}

	////
	// !displays the users shopping cart contents
	function view_cart($arr)
	{
		extract($arr);
		global $shopping_cart;
		$this->read_template("show_cart.tpl");

		classload("form");
		$f = new form;

		$t_price = 0;
	
		$shop = $this->get($shop_id);
		$ucur = $this->get_act_cur($shop);

		$items = false;
		if (is_array($shopping_cart["items"]))
		{
			reset($shopping_cart["items"]);
			while (list($item_id,$ar) = each($shopping_cart["items"]))
			{
				if ($ar["cnt"] > 0)
				{
					$f = new form;
					$f->set_active_currency($ucur);
					// now here we must show the name of the item followed by
					// the rows from the cnt_form for that item that have selectrow checked
					// so, get the item
					$it = $this->get_item($item_id);
					$itt = $this->get_item_type($it["type_id"]);
					// load it's cnt_form
					$f->load($it["cnt_form"] ? $it["cnt_form"] : $itt["cnt_form"]);
					// now find all the rows that are selected in the entry from the shopping cart
					$f->load_entry($ar["cnt_entry"]);
					$selrows = $this->get_selected_rows_in_form(&$f);

					$rowhtml = "";
					foreach($selrows as $rownum)
					{
						// here we must add the elements of row $rownum to the form we are assembling of the selected rows
						// oh what a mindjob.
						// we add prefix entry_.$entry_id._ to all elements in this entry and later when processing
						// entry, we add the same prefix so we process the elements from the right form.
						$this->vars(array("row" => $f->mk_row_html($rownum,"entry_".$ar["cnt_entry"]."_",array(),true)));
						$rowhtml.=$this->parse("F_ROW");
					}
	
					$el_price = $this->get_list_price($item_id, $it["price"],$shop);

					if ($ar["period"])
					{
						// if there is a period set in the cart for this item, we must replace the date in the item's form 
						// with the selected period
						$f->load($itt["form_id"]);
						$f->load_entry($it["entry_id"]);
						$el = $f->get_element_by_type("date", "from");
						$f->set_element_value($el->get_id(),$ar["period"]);
						$f->set_element_value_by_type("price", $el_price);
						$item = $f->show(array("id" => $itt["form_id"], "entry_id" => $it["entry_id"], "op_id" => $itt["cart_op"],"no_load_entry" => true));
					}
					else
					{
						$f->load($itt["form_id"]);
						$f->load_entry($it["entry_id"]);
						$f->set_element_value_by_type("price",$el_price);

						$item = $f->show(array("id" => $itt["form_id"], "entry_id" => $it["entry_id"], "op_id" => $itt["cart_op"],"no_load_entry" => true));
					}
					$t_price += $ar["price"];
					$this->vars(array(
						"item_link" => $this->mk_my_orb("order_item", array("item_id" => $item_id, "shop" => $shop_id, "section" => $section)),
						"name" => $ar["name"],
						"item" => $item,
						"F_ROW" => $rowhtml,
						"item_parent" => ($itt["has_voucher"] ? $this->db_fetch_field("SELECT name FROM objects WHERE oid = ".$it["parent"],"name") : "")
					));
					$this->parse("ITEM");
					$items = true;
				}
			}
		}
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_cart", array("shop_id" => $shop_id,"section" => $section)),
			"shop_id" => $shop_id,
			"section" => $section,
			"order"	=> $this->mk_site_orb(array("action" => "order", "shop_id" => $shop_id, "section" => $section,"first" => 1)),
			"order_hist" => $this->mk_my_orb("order_history", array("id" => $shop_id,"section" => $section)),
			"t_price" => $t_price,
			"to_shop" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$section,
			"cancel_current" => $this->mk_my_orb("cancel_current", array("shop" => $shop_id, "section" => $section))
		));
		if ($items)
		{
			$this->parse("CAN_ORDER");
		}
		return $this->parse();
	}

	function get_cart_min_period()
	{
		global $shopping_cart;
		$min_p = 2147483648;
		if (!is_array($shopping_cart["items"]))
		{
			return 1;
		}
		foreach($shopping_cart["items"] as $iid => $idata)
		{
			if ($idata["period"] && $min_p > $idata["period"] && $idata["period"] > 1)
			{
				$min_p = $idata["period"];
			}
		}
		return $min_p;
	}

	////
	// !saves the changes the user made to quantities in the shopping cart view
	function submit_cart($arr)
	{
		extract($arr);

		// here we must update the cnt_form entries from the entered data, 
		// forms are separated by prefixes entry_.$entry_id._

		classload("form");
		$f = new form;

		$shop_o = $this->get($shop_id);

		global $shopping_cart;
		if (is_array($shopping_cart["items"]))
		{
			// leiame perioodi alguse
			$min_p = $this->get_cart_min_period();
			reset($shopping_cart["items"]);
			while (list($item_id,$ar) = each($shopping_cart["items"]))
			{
				if ($ar["cnt"] > 0)
				{
					$it = $this->get_item($item_id);
					$itt = $this->get_item_type($it["type_id"]);

					$old_entry = $f->get_entry($it["cnt_form"] ? $it["cnt_form"] : $itt["cnt_form"],$ar["cnt_entry"]);

					// process the entry
					$f->process_entry(array(
						"id" => $it["cnt_form"] ? $it["cnt_form"] : $itt["cnt_form"],
						"entry_id" => $ar["cnt_entry"],
						"prefix" => "entry_".$ar["cnt_entry"]."_"
					));

					// also update the count in the cart
					$selrows = $this->get_selected_rows_in_form(&$f);
					
					$count = 0;
					foreach($selrows as $i)
					{
						$rowels = $f->get_elements_for_row($i,ARR_ELID);
						// this row's count must be added to this item's count in the shopping cart
						foreach($rowels as $rowelid => $rowelvalue)
						{
							$rowelref = $f->get_element_by_id($rowelid);
							if ($rowelref->get_type() == "textbox" && $rowelref->get_subtype() == "count")
							{
								$count+=$rowelvalue;
							}
						}
					}

					if ($count > 0)
					{
						// nyyd tshekime et kas j2lle on vaba see item kyllalt palju
						if (!$this->is_item_available($it,$count,$f,$min_p))
						{
							// pold vabu itemeid sel ajal
							global $status_msg;
							$status_msg = E_SHOP_NO_FREE_ITEMS_DATE;
							session_register("status_msg");
							// kui ei old kyllalt kohti, siis rollime tellimuse sisestuse tagasi ka
							$f->restore_entry($it["cnt_form"] ? $it["cnt_form"] : $itt["cnt_form"],$ar["cnt_entry"],$old_entry);
							return $this->mk_my_orb("view_cart", array("shop_id" => $shop_id, "section" => $section));
						}

						// now calc price
						$price = $this->calc_price($itt,$it,$count,$f,$shop_o);

						$GLOBALS["shopping_cart"]["items"][$item_id]["cnt"] = $count;
						$GLOBALS["shopping_cart"]["items"][$item_id]["price"] = $price;
						$GLOBALS["shopping_cart"]["items"][$item_id]["name"] = $it["name"];
						$GLOBALS["shopping_cart"]["items"][$item_id]["cnt_entry"] = $ar["cnt_entry"];
					}
					else
					{
						unset($GLOBALS["shopping_cart"]["items"][$item_id]);
					}
				}
				else
				{
					unset($GLOBALS["shopping_cart"]["items"][$item_id]);
				}
			}
		}
		return $this->mk_site_orb(array("action" => "view_cart", "shop_id" => $shop_id, "section" => $section));
	}

	////
	// !calculates the price for item $it of type $itt , count of items is $count and the form filled for the order is loaded in $f
	// uses the equasion specified in $itt and if none is specified, the price specified in form $f or the item's form that is loaded
	function calc_price($itt,$it,$count,$f,$shop)
	{
		$eq = $this->get_eq($it["price_eq"] ? $it["price_eq"] : $itt["eq_id"]);

		// the price might be different based on the date
		// so if the item purchace has a date attached
		if ($it["has_period"] && $it["has_objs"])
		{
			// find the start and end dates of the purchase
			$from = $f->get_element_value_by_type("date","from");
			$to = $f->get_element_value_by_type("date","to");
			// now check if any price alterations fall in this timespan
		}

		$el_price = $this->get_list_price($it["oid"], $it["price"],$shop);
		if ($eq["comment"] == "")
		{
			$price = (double)$el_price * (double)$count;
		}
		else
		{
			// parsime price_eq'st v2lja formi elementide nimed ja asendame need numbritega ja siis laseme evali()'i peale
			$f_kaup = new form;
			$f_kaup->load($itt["form_id"]);
			$f_kaup->load_entry($it["entry_id"]);
			$f_kaup->set_element_value_by_type("price", $el_price);
			$f->set_element_value_by_type("price", $el_price);

			$price = $this->do_parse_eq($eq,array($f,$f_kaup));
		}

		return (int)($price+0.5);
	}

	function do_parse_eq($eq,$farr,$num_weeks = 0)
	{
		$els = $this->parse_eq_variables($eq["comment"]);

		$replaces = array();
		$eq = $eq["comment"];
		foreach($els as $elname)
		{
			$el = false;
			$elval = "0.0";
			foreach($farr as $f)
			{
				$el = $f->get_element_by_name($elname);
				if ($el && $el->get_value(true) > 0)	// try to find element with value
				{
					break;
				}
			}
			if ($el)
			{
				$elval = $el->get_value(true);
			}
			if ($elval == 0)
			{
				$elval = "0.0";
			}
			$replaces[$elname] = $elval;
//			$eq = str_replace($elname,$elval,$eq);
		}

		$replaces["num_weeks"] = $num_weeks;
//		echo "weeks = $num_weeks <br>";
		uksort($replaces, array($this,"_sort_repl"));
		foreach($replaces as $k => $v)
		{
//			echo "$k => $v <br>";
			$eq = str_replace($k,$v,$eq);
		}
//		echo "eq = $eq <br>";
		eval($eq.";");
//		echo "calc price = $price <br>";
		return $price;
	}

	function _sort_repl($a,$b)
	{
		return strlen($b) - strlen($a);
	}

	////
	// !shows the ordering form that the user must fill to order stuff
	function order($arr)
	{
		extract($arr);
		$this->read_template("order.tpl");


		classload("form");
		$f = new form;

		// read the default values for this form from the join form that the user filled when he joined
		$elvals = array();
		classload("users");
		$u = new users;
		$udata = $u->get_user(array("uid" => UID));
		$jf = unserialize($udata["join_form_entry"]);
		if (is_array($jf))
		{
			$f = new form();
			foreach($jf as $joinform => $joinentry)
			{
				$f->load($joinform);
				$f->load_entry($joinentry);
				$elvals = array_merge($elvals, $f->get_element_values());
			};
		};

		global $order_forms;
		if ($first)
		{
			if (!$GLOBALS["shopping_cart"]["order_id"])
			{
				$order_forms = false;
			}
			$num = 0;
		}
		else
		if ($num >= $order_forms["max_cnt"])
		{
			// but that was the last form so continue to the real submit function
			header("Location: ".$this->mk_my_orb("submit_order", array("shop_id" => $shop_id, "section" => $section)));
			die();
		}

		// now! we must show all the selected forms in a row, and repeat the ones that are marked as repeating
		// get all the forms
		$ofs = $this->get_ofs_for_shop($shop_id);
		if (!is_array($order_forms) )
		{
			// first time, so fill arrays
			$order_forms["entries"] = array();

			$cnt = 0;
			foreach($ofs as $fid => $row)
			{
				$order_forms["order"][$cnt]["form"] = $fid;
				$order_forms["order"][$cnt]["chk"] = $row["repeat"];
				$order_forms["order"][$cnt]["op_id"] = $row["op_id"];
				$cnt++;
			}
			$order_forms["max_cnt"] = $cnt;
			$order_forms["current_cnt"] = 0;
			session_register("order_forms");
		}

		if (!$order_forms["order"][$num]["form"])
		{
			// if this form doesh not exist
			header("Location: ".$this->mk_my_orb("submit_order", array("shop_id" => $shop_id, "section" => $section)));
			die();
		}

		if ($order_forms["current_cnt"] > 0 || $num > 0)
		{
			$pe = "";
			foreach($order_forms["entries"] as $a_id => $ar)
			{
				$f->reset();
				$prev_entry = $f->show(array("id" => $ar["form"], "entry_id" => $ar["entry"], "op_id" => $order_forms["order"][$num]["op_id"]));
				$this->vars(array(
					"entry" => $prev_entry,
					"chentry" => $this->mk_my_orb("order", array("shop_id" => $shop_id, "section" => $section, "num" => $ar["num"],"eid" => $ar["entry"])),
					"delentry" => $this->mk_my_orb("del_pass_entry", array("shop_id" => $shop_id, "section" => $section, "num" => $a_id, "eid" => $ar["entry"],"c_num" => $num))
				));
				$pe.=$this->parse("prev_entry");
			}
		}
		if ($num < 1)
		{
			$num = 0;
		}
		$this->vars(array(
			"prev_entry" => $pe,
			"form" => $f->gen_preview(array(
									"elvalues" => $elvals,
									"entry_id" => $eid,
									"id" => $order_forms["order"][$num]["form"], 
									"reforb" => $this->mk_reforb("pre_submit_order", array("shop_id" => $shop_id, "section" => $section,"num" => $num,"prev_eid" => $eid)),
									"form_action" => "/index.".$GLOBALS["ext"]
								)),
			"shop_id" => $shop_id,
			"section" => $section,
			"cart" => $this->mk_site_orb(array("action" => "view_cart", "shop_id" => $shop_id, "section" => $section)),
			"to_shop" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$section,
			"next"		=> $this->mk_my_orb("order", array("shop_id" => $shop_id, "section" => $section, "num" => ($num+1))),
			"cnt"			=> $order_forms["current_cnt"]
		));
		return $this->parse();
	}

	function pre_submit_order($arr)
	{
		extract($arr);
		global $order_forms;
		classload("form");
		$f = new form;
		$f->process_entry(array("id" => $order_forms["order"][$num]["form"],"entry_id" => $prev_eid));
		if ($prev_eid)
		{
			foreach($order_forms["entries"] as $nnum => $ar)
			{
				if ($ar["entry"] == $prev_eid)
				{
					$order_forms["entries"][$num]["name"] = $f->entry_name;
				}
			}
		}
		else
		{
			$order_forms["entries"][] = array("num" => $num, "form" => $order_forms["order"][$num]["form"], "entry" => $f->entry_id,"name" => $f->entry_name);

			// now check if we sould move on
			if ($order_forms["order"][$num]["chk"] != 1)
			{
				// yes we should
				$num++;
				$order_forms["current_cnt"] = 0;
				if ($num == $order_forms["max_cnt"])
				{
					// but that was the last form so continue to the real submit function
					header("Location: ".$this->mk_my_orb("submit_order", array("shop_id" => $shop_id, "section" => $section)));
					die();
				}
			}
			else
			{
				// increment this forms entry count
				$order_forms["current_cnt"]++;
			}
		}
		return $this->mk_my_orb("order", array("shop_id" => $shop_id, "section" => $section, "num" => $num));
	}

	////
	// !submits the order for the customer.
	function submit_order($arr)
	{
		extract($arr);

		$sh = $this->get($shop_id);

		classload("form");
		$f = new form;

		global $shopping_cart;
		if (is_array($shopping_cart["items"]))
		{
			$canceled_order_id = 0;
			if ($shopping_cart["order_id"])
			{
				$this->do_cancel_order($shopping_cart["order_id"]);
				$canceled_order_id = $shopping_cart["order_id"];
			}
			// leiame perioodi alguse
			$min_p = 2147483648;
			foreach($shopping_cart["items"] as $iid => $idata)
			{
				if ($idata["period"] && $min_p > $idata["period"] && $idata["period"] > 1)
				{
					$min_p = $idata["period"];
				}
			}

			$has_items = false;
			reset($shopping_cart["items"]);
			while (list($item_id,$ar) = each($shopping_cart["items"]))
			{
				if ($ar["cnt"] > 0)
				{
					$has_items = true;
				}
			}	
			if (!$has_items)
			{
				die(LC_NO_ITEMS_IN_CART);
			}

			reset($shopping_cart["items"]);
			while (list($item_id,$ar) = each($shopping_cart["items"]))
			{
				if ($ar["cnt"] > 0)
				{
					// so, get the item
					$it = $this->get_item($item_id);
					$itt = $this->get_item_type($it["type_id"]);

					// load it's cnt_form
					$f->load($it["cnt_form"] ? $it["cnt_form"] : $itt["cnt_form"]);
					// now find all the rows that are selected in the entry from the shopping cart
					$f->load_entry($ar["cnt_entry"]);
	
					// if the period is specified for the item use that one, so we do the friggin back flights correctly
					if (!($free_items = $this->is_item_available($it,$ar["cnt"],$f,$ar["period"] ? $ar["period"] : $min_p)))
					{
						// if no item is available, abort the order
						global $status_msg;
						$status_msg = sprintf(E_SHOP_ITEMS_ORDER_CHANGED,$it["name"]);
						session_register("status_msg");
						return $this->mk_my_orb("view_cart", array("shop_id" => $shop_id, "section" => $section));
					}
					else
					{
						$this->do_order_item($it,$ar["cnt"],$free_items);
					}

					$selrows = $this->get_selected_rows_in_form(&$f);

					$mail.=LC_SHOP_ORDER_NAME.$it["name"]."\n";
					$mail.=LC_SHOP_ORDER_TYPE;
					$rowhtml = "";

					if ($itt["has_voucher"])
					{
						$mail.="Hotel: ".($this->db_fetch_field("SELECT name FROM objects WHERE oid = ".$it["parent"],"name"))."\n";
					}
					foreach($selrows as $rownum)
					{
						// here we must add the elements of row $rownum to the email we are assembling of the selected rows
						$mail.=$f->mk_show_text_row($rownum)."\n";
					}
					$mail.="Hind: ".$ar["price"]."\n";
					if ($ar["period"] > 1)
					{
						$mail.="Kuupäev: ".$this->time2date($ar["period"], 5)."\n\n";
					}
					$t_price += $ar["price"];
				}
			}
		}
		else
		{
			die(LC_ORDER_EMPTY);
		}

		// save the user info
		classload("form");
		$f = new form;

		global $uid;
		
		// ok. a prize for anybody (except terryf and duke) who figures out why the next line is like it is :)
		$day = mktime(8,42,17,date("n"),date("d"),date("Y"));
		$wd = date("w");
		$hr = date("G");
		$month = mktime(8,42,17,date("n"),1,date("Y"));

		// and also log the order
		if (!$canceled_order_id)
		{
			$ord_id = $this->db_fetch_field("SELECT MAX(id) AS id FROM orders","id")+1;
		}
		else
		{
			$ord_id = $canceled_order_id;
		}

		$this->db_query("INSERT INTO orders(id,tm,user,ip,shop_id,day,month,wd,hr) VALUES($ord_id,".time().",'$uid','".get_ip()."','$shop_id','$day','$month','$wd','$hr')");

		// kirjutame baasi alles siin, p2rast kontrollimist et kas k6ik ikka olemas on
		global $shopping_cart;
		if (is_array($shopping_cart["items"]))
		{
			reset($shopping_cart["items"]);
			while (list($item_id,$ar) = each($shopping_cart["items"]))
			{
				if ($ar["cnt"] > 0)
				{
					$it = $this->get_item($item_id);
					$itt = $this->get_item_type($it["type_id"]);
					$this->db_query("INSERT INTO order2item(order_id,item_id,count,price,cnt_entry,period,item_type,item_type_order) VALUES($ord_id,$item_id,'".$ar["cnt"]."','".$ar["price"]."','".$ar["cnt_entry"]."','".($ar["period"] < 2 ? $min_p : $ar["period"])."','".$it["type_id"]."','".$itt["jrk"]."')");
				}
			}
		}

		// nyt paneme tellimusega kaasa tulnud order formid ka kirja
		global $order_forms;
		$ename = "";
		if (is_array($order_forms["entries"]))
		{
			foreach($order_forms["entries"] as $ar)
			{
				if ($ename == "")
				{
					$ename = $ar["name"];
				}
				$this->db_query("INSERT INTO order2form_entries(order_id,form_id,entry_id,num,name) values($ord_id,'".$ar["form"]."','".$ar["entry"]."','".$ar["num"]."','".$ar["name"]."')");
			}
		}

		// now we must also send an email to somebody notifying them of the new order.
		// the email must contain all the info about the purchase, including all the
		// items and their counts and also the order form data.
		$mail = sprintf(LC_SHOP_HI_USER,$uid).get_ip().LC_SHOP_TIME.$this->time2date(time(),2).LC_SHOP_ORDERED_PRODUCT.$mail.LC_SHOP_TOTAL_PRICE.$t_price.LC_SHOP_BILL_NR.$ord_id;

		$this->db_query("UPDATE orders SET t_price = '$t_price', name='$ename' WHERE id = $ord_id");
		
		$mail.=LC_SHOP_ORDERES_DATA;
		if (is_array($order_forms["entries"]))
		{
			foreach($order_forms["entries"] as $ar)
			{
				$f->load($ar["form"]);
				$f->load_entry($ar["entry"]);
				$mail.=$f->show_text();
			}
		}

		$emails = explode(",",$sh["emails"]);
		foreach($emails as $email)
		{
			mail($email,LC_SHOP_ORDER, $mail,"From: automatweb@automatweb.com\n");
		}

		// zero out the customers shopping cart as well.
		$GLOBALS["shopping_cart"] = array();
		$GLOBALS["order_forms"] = array();

		// now show the order 
		return $this->mk_my_orb("view_order", array("shop" => $shop_id, "order_id" => $ord_id, "section" => $section));
	}
	
	////
	// !shows the general turnover stats for shop $id and lets the user pick a more specific stat
	function turnover_stat($arr)
	{
		extract($arr);
		$this->read_template("turnover_stat.tpl");
		$sh = $this->get($id);
		$this->mk_path($sh["parent"], "<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Change target</a> / Statistics");

		load_vcl("date_edit");
		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
			"hour" => "",
			"minute" => ""
		));

		$t_o = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM orders WHERE shop_id = $id","cnt");
		$t_t = $this->db_fetch_field("SELECT SUM(t_price) as sum FROM orders WHERE shop_id = $id","sum");
		$this->vars(array(
			"name"	=> $sh["name"],
			"t_orders" => $t_o,
			"t_turnover" => $t_t,
			"avg_order"	=> ($t_o == 0 ? 0 : floor(((double)$t_t / (double)$t_o)*100.0+0.5)/100.0),
			"t_from" => $de->gen_edit_form("from", 0),
			"t_to"	=> $de->gen_edit_form("to", time()),
			"reforb" => $this->mk_reforb("do_stat", array("id" => $id,"reforb" => 0))
		));

		return $this->parse();
	}

	////
	// !show the longer definition of item and lets the user order it
	function order_item($arr)
	{
		extract($arr);

		if (!$shop)
		{
			$shop = $this->find_shop_id($section);
		}

		classload("form");
		global $shopping_cart,$ext;
		$f = new form;

		$shop_o = $this->get($shop);
		$ucur = $this->get_act_cur($shop_o);
		$f->set_active_currency($ucur);

		// ok, now, if $parallel is set then we must show several items side by side, but 
		// that only makes sense for isems that has_periods and !has_objs and has_max
		if ($item_id)
		{
			$row = $this->get_item($item_id,true);
			$itt = $this->get_item_type($row["type_id"]);
			$el_price = $this->get_list_price($row["oid"], $row["price"],$shop_o);
		}

		//($row["has_period"] && !$row["has_objs"] && $row["has_max"]) || 
		if ($parallel)
		{
			$this->read_template("order_item_rep.tpl");
			$parent = $this->do_shop_menus($shop,$section);
			$ignoregoto = $this->db_fetch_field("SELECT shop_ignoregoto FROM menu WHERE id = $parent","shop_ignoregoto");

			classload("planner");

			// so we check for it here
			if ($parallel)
			{
				// and if it is so, then select all the items for showing
				$this->db_query("SELECT shop_items.*, objects.* FROM objects LEFT JOIN shop_items ON objects.brother_of = shop_items.id WHERE objects.parent = $parent AND status != 0 AND objects.class_id = ".CL_SHOP_ITEM);
			}
			else
			{
				$this->db_query("SELECT shop_items.*, objects.* FROM objects LEFT JOIN shop_items ON objects.brother_of = shop_items.id WHERE objects.oid = $item_id");
			}
			$item_id = 0;
			while ($row = $this->db_next())
			{
				if (!$item_id)
				{
					$item_id = $row["brother_of"];
				}
				$this->save_handle();
				$itt = $this->get_item_type($row["type_id"]);
				$ir = "";
				// this object has a repeater and can therefore have a repeat count
				$rep_cnt = $row["per_cnt"];
				$pl = new planner;
				// get the events for this planner from today to 800 days, limiting the number of events to $rep_num
				$reps = $pl->get_events(array( "start" => time(), "limit" => $rep_cnt,"index_time" => true,"event" => $row["per_event_id"],"end" => time()+800*24*3600));
				if (is_array($reps))
				{
					$f = new form;
					$f->set_active_currency($ucur);
					$f->load($itt["form_id"]);
					$f->load_entry($row["entry_id"]);
					foreach($reps as $time => $evnt)
					{
						$f->reset();

						$el = $f->get_element_by_type("date", "from");
						if (!$el)
						{
							$this->raise_error("No from date element in item form! ", true);
						}
						$f->set_element_value($el->get_id(),$time);

						if ($this->is_item_available($row,1,new form(),$time))
						{
							$item = $f->show(array("id" => $itt["form_id"], "entry_id" => $row["entry_id"],"op_id" => $itt["long_op"],"no_load_entry" => true));
						}
						else
						{
							$item = "No items available in this period (".$this->time2date($time,5).")";
						}
						$this->vars(array(
							"item" => $item,
							"perd_val" => $time,
							"perd_check" => checked($shopping_cart["items"][$row["brother_of"]]["period"] == $time),
							"item_id" => $row["brother_of"]
						));
						$ir.= $this->parse("ITEM_REP_N");
					}
				}
				$this->vars(array(
					"ITEM_REP_N" => $ir,
					"change" => $this->mk_my_orb("change", array("id" => $row["brother_of"]), "shop_item")
				));
				$pari .= $this->parse("PAR_ITEM");
				$this->restore_handle();
			}
			$this->vars(array(
				"cnt_form" => $f->gen_preview(array(
												"id" => $row["cnt_form"] ? $row["cnt_form"] : $itt["cnt_form"],
												"entry_id" => $shopping_cart["items"][$item_id]["cnt_entry"],
												"form_action" => "/index.".$ext,
												"tpl" => "show_noform.tpl")),
				"reforb" => $this->mk_reforb("submit_order_item", array("item_id" => $item_id, "shop" => $shop, "section" => $parent,"ignoregoto" => $ignoregoto)),
				"PAR_ITEM" => $pari
			));
		}
		else
		{
			$this->read_template("order_item.tpl");
			$parent = $this->do_shop_menus($shop,$section);
			$ignoregoto = $this->db_fetch_field("SELECT shop_ignoregoto FROM menu WHERE id = $parent","shop_ignoregoto");

			$f->load($itt["form_id"]);
			$f->load_entry($row["entry_id"]);
			$f->set_element_value_by_type("price", $el_price);

			$this->vars(array("item" => $f->show(array("id" => $itt["form_id"], "entry_id" => $row["entry_id"],"op_id" => $itt["long_op"],"no_load_entry" => true))));
			$this->vars(array(
				"cnt_form" => $f->gen_preview(array(
													"id" => $row["cnt_form"] ? $row["cnt_form"] : $itt["cnt_form"],
													"entry_id" => $shopping_cart["items"][$row["oid"]]["cnt_entry"],
													"reforb" => $this->mk_reforb("submit_order_item", array("item_id" => $row["oid"], "shop" => $shop, "section" => $parent,"ignoregoto" => $ignoregoto)),
													"form_action" => "/index.".$ext)),
			));
		}

		$this->vars(array(
			"item_id" => $row["oid"],
			"price" => $el_price ? $el_price : $row["price"],
			"it_cnt"	=> $shopping_cart["items"][$row["oid"]]["cnt"],
			"order_item" => $this->mk_my_orb("order_item", array("item_id" => $row["oid"], "shop" => $shop, "section" => $parent)),
			"cart" => $this->mk_my_orb("view_cart", array("shop_id" => $shop, "section" => $parent)),
			"add_item" => $this->mk_my_orb("new", array("parent" => $parent),"shop_item"),
			"cancel_current" => $this->mk_my_orb("cancel_current", array("shop" => $shop, "section" => $parent))
		));
		return $this->parse();
	}

	////
	// !adds the number of items in the count form to cart and also remembers the form_entry of the cnt_form so we know what type
	// of item was ordered. damn. this is like complicated and shit 
	function submit_order_item($arr)
	{
		extract($arr);
		global $shopping_cart;
	
		$is_period_item = true;
		if (!is_array($period))
		{
			// if period is not set then fake it to be the date from the shopping cart
			$period[$item_id] = $this->get_cart_min_period();
			$is_period_item = false;
		}

		$shop_o = $this->get($shop);

		foreach($period as $item_id => $iperiod)
		{
			$it = $this->get_item($item_id,true);
			$itt = $this->get_item_type($it["type_id"]);

			classload("form");
			$f = new form;

			$old_entry = false;
			if ($shopping_cart["items"][$item_id]["cnt_entry"])
			{
				$old_entry = $f->get_entry($it["cnt_form"] ? $it["cnt_form"] : $itt["cnt_form"],$shopping_cart["items"][$item_id]["cnt_entry"]);
			}

			$f->process_entry(array(
				"id" => $it["cnt_form"] ? $it["cnt_form"] : $itt["cnt_form"], 
				"entry_id" => $shopping_cart["items"][$item_id]["cnt_entry"]
			));
			$entry_id = $f->entry_id;

			// now figure out the correct row(s) in the count form and mark them in the shopping cart.
			// rows are figured out based on elements named selectrow - if it's checked on that row, then that row
			// nost be added to the cart. 
			// here we just calculate the amount of items based on the selected rows and numbers entered in textbox/count elements

			$selrows = $this->get_selected_rows_in_form(&$f);

			$count = 0;
			foreach($selrows as $i)
			{
				$rowels = $f->get_elements_for_row($i,ARR_ELID);
				// this row's count must be added to this item's count in the shopping cart
				foreach($rowels as $rowelid => $rowelvalue)
				{
					$rowelref = $f->get_element_by_id($rowelid);
					if ($rowelref->get_type() == "textbox" && $rowelref->get_subtype() == "count")
					{
						$count+=$rowelvalue;
					}
				}
			}

			if (!$this->is_item_available($it,$count,$f,$iperiod))
			{
				// pold vabu itemeid sel ajal
				global $status_msg;
				$status_msg = E_SHOP_NO_FREE_ITEMS_DATE;
				session_register("status_msg");
				// kui ei old kyllalt kohti, siis rollime tellimuse sisestuse tagasi ka
				$f->restore_entry($it["cnt_form"] ? $it["cnt_form"] : $itt["cnt_form"],$shopping_cart["items"][$item_id]["cnt_entry"],$old_entry);
				return $this->mk_my_orb("show", array("id" => $shop, "section" => $section));
			}

			// ei broneeri asju tegelt 2ra, bronnime alles siis kui tyyp p2ris tellimuse teeb.
			// tegelt tuleks siinkohal teha miski temp-record selle kohta, et see asi on sel kellal bronnitud
			// et keegi teine samal ajal ei saax seda sama aja peale bronnida a minu vaene v2ike pea ei v6ta seda praegusel hetkel

			// calculate price
			$price = $this->calc_price($itt,$it,$count,$f,$shop_o);
		
			$GLOBALS["shopping_cart"]["items"][$item_id]["cnt"] = $count;
			$GLOBALS["shopping_cart"]["items"][$item_id]["price"] = $price;
			$GLOBALS["shopping_cart"]["items"][$item_id]["name"] = $it["name"];
			$GLOBALS["shopping_cart"]["items"][$item_id]["cnt_entry"] = $entry_id;
			$GLOBALS["shopping_cart"]["items"][$item_id]["period"] = $iperiod;
		}

		// if the item specifies, where to go after ordering it, then go there. otherwise don't go anywhere
		if ($it["redir"] && !$ignoregoto)
		{
			return $this->mk_my_orb("show", array("section" => $it["redir"]));
		}
		else
		{
			return $this->mk_my_orb("view_cart", array("shop_id" => $shop, "section" => $section));
		}
	}

	////
	// !returns an array of rows that are selected in the entry loaded in the form $f (reference to object)
	function get_selected_rows_in_form(&$f)
	{
		$ret = array();
		$nr = $f->get_num_rows();
		$srs = $f->get_element_by_name("selectrow", RET_ALL);
		if (!$srs)
		{
			// if there are no selectrow elements in the form, return all rows
			for ($i=0; $i < $nr; $i++)
			{
				$ret[$i] = $i;
			}
			return $ret;
		}

		$sel_srs = array();
		// k2ime k6ik selectrow elemendid l2bi 
		foreach($srs as $el)
		{
			// leiame need, mis on valitud. 
			if ($f->is_checked_value($f->get_element_value($el->get_id())))
			{
				$sel_srs[$el->get_srow_grp()] = true;
			}
		}

		// nyd k2ime k6ik read l2bi
		for ($i=0; $i < $nr; $i++)
		{
			$hasels = false;
			$rowels = $f->get_elements_for_row($i,ARR_ELID);
			// tshekime kas m6ni selle rea peal olev element on sama grupiga, mis m6ni checkitud selectrow
			foreach($rowels as $elid => $elval)
			{
				$el = $f->get_element_by_id($elid);
				if (isset($sel_srs[$el->get_srow_grp()]))
				{
					// kui on, siis j2relikult n2itame selle rea elemente
					$hasels = true;
				}
			}

			if ($hasels)
			{
				$ret[$i] = $i;
			}
		}
		return $ret;
	}

	////
	// !shows the orders for shop $id on the admin side and lets you  manage them
	function admin_orders($arr)
	{
		extract($arr);
		$this->read_template("admin_orders.tpl");
		$sh = $this->get($id);
		$this->mk_path($sh["parent"], "<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Change target</a> / Orders");

		$ss = "";
		if ($filter_uid && !is_admin())
		{
			global $uid;
			$ss = " AND user = '$uid' ";
		}

		$this->vars(array(
			"s_name" => $s_name,
			"s_agent" => $s_agent,
			"s_id" => $s_id,
			"reforb" => $this->mk_reforb("admin_orders", array("id" => $id, "no_reforb" => true,"search" => true))
		));

		if ($search)
		{
			$ss.=" AND (name LIKE '%".$s_name."%' AND id LIKE '%".$s_id."%' AND	user LIKE '%".$s_agent."%') ";
		}

		// make pageselector
		$cnt = $this->db_fetch_field("SELECT count(*) as cnt FROM orders WHERE shop_id = $id $ss","cnt");
		$num_pages = $cnt / SHOP_PER_PAGE;

		for ($i=0; $i < $num_pages; $i++)
		{
			$this->vars(array(
				"from" => $i*SHOP_PER_PAGE,
				"to" => min(($i+1)*SHOP_PER_PAGE,$cnt),
				"goto_page" => $this->mk_my_orb($GLOBALS["action"], array("id" => $id, "section" => $section, "page" => $i,"s_id" => $s_id,"search" => $search, "s_name" => $s_name, "s_agent" => $s_agent))
			));
			if ($i == $page)
			{
				$pg.=$this->parse("SEL_PAGE");
			}
			else
			{
				$pg.=$this->parse("PAGE");
			}
		}
		$this->vars(array(
			"PAGE" => $pg,
			"SEL_PAGE" => ""
		));

		$this->db_query("SELECT * FROM orders WHERE shop_id = $id $ss ORDER BY tm DESC LIMIT ".($page*SHOP_PER_PAGE).",".SHOP_PER_PAGE);
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"when" => $this->time2date($row["tm"], 2),
				"user" => $row["user"],
				"ip" => $row["ip"],
				"price" => $row["t_price"],
				"view"	=> $this->mk_my_orb("view_order", array("shop" => $id, "order_id" => $row["id"],"section" => $section,"page" => $page)),
				"fill"	=> $this->mk_my_orb("mark_order_filled", array("shop" => $id, "order_id" => $row["id"],"section" => $section,"page" => $page)),
				"bill" => $this->mk_my_orb("view_bill", array("shop" => $id, "order_id" => $row["id"], "section" => $section)),
				"confirmation" => $this->mk_my_orb("view_confirmation", array("shop" => $id, "order_id" => $row["id"], "section" => $section)),
			"vouchers" => $this->mk_my_orb("list_vouchers", array("shop" => $id, "order_id" => $row["id"], "section" => $section)),
				"change" => $this->mk_my_orb("change_order", array("shop" => $id, "order_id" => $row["id"], "section" => $section,"page" => $page)),
				"cancel"	=> $this->mk_my_orb("cancel_order", array("shop" => $id, "order_id" => $row["id"],"section" => $section,"page" => $page)),
				"order_id" => $row["id"],
				"name" => $row["name"]
			));
			$is_f = "";
			if (!isset($row["status"]))
			{
				$row["status"] = 0;
			}
			if ($row["status"] != ORD_FILLED)
			{
				$is_f = $this->parse("IS_F");
				$isp = "";
			}
			else
			{
				$is_f = $this->parse("FILLED");
				$isp = $this->parse("IS_PAID");
			}
			$this->vars(array("IS_F" => $is_f,"FILLED" => "","IS_PAID" => $isp));
			$this->parse("LINE");
		}
		$this->vars(array(
			"to_shop" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$section
		));
		return $this->parse();
	}

	////
	// !shows the orders for shop $id , item $item_id and period $period on the admin side and lets you  manage them
	function admin_item_orders($arr)
	{
		extract($arr);
		$this->read_template("admin_orders.tpl");
		$sh = $this->get($id);
		$this->mk_path($sh["parent"], "<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Change target</a> / Orders");

		// make pageselector
		$cnt = $this->db_fetch_field("SELECT count(distinct(order_id)) as cnt FROM order2item WHERE item_id = $item_id AND period = $period","cnt");
		$num_pages = $cnt / SHOP_PER_PAGE;

		for ($i=0; $i < $num_pages; $i++)
		{
			$this->vars(array(
				"from" => $i*SHOP_PER_PAGE,
				"to" => min(($i+1)*SHOP_PER_PAGE,$cnt),
				"goto_page" => $this->mk_my_orb("admin_item_orders", array("id" => $id, "section" => $section, "page" => $i,"item_id" => $item_id, "period" => $period))
			));
			if ($i == $page)
			{
				$pg.=$this->parse("SEL_PAGE");
			}
			else
			{
				$pg.=$this->parse("PAGE");
			}
		}
		$this->vars(array(
			"PAGE" => $pg,
			"SEL_PAGE" => ""
		));

		// figure out the order id's
		$oids = array();
		$this->db_query("SELECT distinct(order_id) as order_id FROM order2item WHERE item_id = $item_id AND period = $period");
		while ($row = $this->db_next())
		{
			$oids[] = $row["order_id"];
		}
		$oidstr = join(",",$oids);

		if ($oidstr != "")
		{
			$this->db_query("SELECT * FROM orders WHERE id IN ($oidstr) ORDER BY tm DESC LIMIT ".($page*SHOP_PER_PAGE).",".SHOP_PER_PAGE);
		}
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"when" => $this->time2date($row["tm"], 2),
				"user" => $row["user"],
				"ip" => $row["ip"],
				"price" => $row["t_price"],
				"view"	=> $this->mk_my_orb("view_order", array("shop" => $id, "order_id" => $row["id"],"section" => $section,"page" => $page)),
				"fill"	=> $this->mk_my_orb("mark_order_filled", array("shop" => $id, "order_id" => $row["id"],"section" => $section,"page" => $page)),
				"bill" => $this->mk_my_orb("view_bill", array("shop" => $id, "order_id" => $row["id"], "section" => $section)),
				"confirmation" => $this->mk_my_orb("view_confirmation", array("shop" => $id, "order_id" => $row["id"], "section" => $section)),
				"vouchers" => $this->mk_my_orb("list_vouchers", array("shop" => $id, "order_id" => $row["id"], "section" => $section)),
				"change" => $this->mk_my_orb("change_order", array("shop" => $id, "order_id" => $row["id"], "section" => $section,"page" => $page)),
				"cancel"	=> $this->mk_my_orb("cancel_order", array("shop" => $id, "order_id" => $row["id"],"section" => $section,"page" => $page)),
				"order_id" => $row["id"],
				"name" => $row["name"]
			));
			$is_f = "";
			if (!isset($row["status"]))
			{
				$row["status"] = 0;
			}
			if ($row["status"] != ORD_FILLED)
			{
				$is_f = $this->parse("IS_F");
			}
			else
			{
				$is_f = $this->parse("FILLED");
			}
			$this->vars(array("IS_F" => $is_f,"FILLED" => ""));
			$this->parse("LINE");
		}
		$this->vars(array(
			"to_shop" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$section
		));
		return $this->parse();
	}

	////
	// !marks the order as filled
	function mark_order_filled($arr)
	{
		extract($arr);
		$this->db_query("UPDATE orders SET status = ".ORD_FILLED." WHERE id = $order_id");
		header("Location: ".$this->mk_my_orb("order_history", array("id" => $shop,"section" => $section,"page" => $page)));
		die();
	}

	////
	// !shows the order $order_id for shop $shop
	function view_order($arr)
	{
		extract($arr);
		$this->read_template("view_order.tpl");
		$this->vars(array(
			"to_shop" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$section
		));
		$sh = $this->get($shop);
		if (!isset($id))
		{
			$id = 0;
		}
		$this->mk_path($sh["parent"], "<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Change target</a> / <a href='".$this->mk_my_orb("admin_orders", array("id" => $shop))."'>Orders</a> / View order");

		// load the entry from the database
		$this->db_query("SELECT * FROM orders WHERE id = $order_id");
		$order = $this->db_next();

		$this->db_query("SELECT * FROM order2item WHERE order_id = $order_id ORDER BY item_type_order");
		while ($row = $this->db_next())
		{
			$o_items[$row["item_id"]] = $row;
		}

		classload("form");
		$f = new form;

		$items = false;
		$itm = "";
		if (is_array($o_items))
		{
			reset($o_items);
			while (list($item_id,$ar) = each($o_items))
			{
				if ($ar["count"] > 0)
				{
					// now here we must show the name of the item followed by
					// the rows from the cnt_form for that item that have selectrow checked

					// so, get the item
					$it = $this->get_item($item_id);
					$itt = $this->get_item_type($it["type_id"]);
					// load it's cnt_form
					$f->load($it["cnt_form"] ? $it["cnt_form"] : $itt["cnt_form"]);
					// now find all the rows that are selected in the entry from the shopping cart
					$f->load_entry($ar["cnt_entry"]);
					$selrows = $this->get_selected_rows_in_form(&$f);

					$rowhtml = "";
					foreach($selrows as $rownum)
					{
						// here we must add the elements of row $rownum to the form we are assembling of the selected rows
						// oh what a mindjob.
						// we add prefix entry_.$entry_id._ to all elements in this entry and later when processing
						// entry, we add the same prefix so we process the elements from the right form.
						$this->vars(array("row" => $f->mk_show_text_row($rownum)));
						$rowhtml.=$this->parse("F_ROW");
					}

					$this->vars(array(
						"item_link" => $this->mk_my_orb("change", array("id" => $item_id),"shop_item"),
						"view_item" => $this->mk_my_orb("order_item", array("item_id" => $item_id, "shop" => $shop)),
						"parent_name" => ($itt["has_voucher"] ? $this->db_fetch_field("SELECT name FROM objects WHERE oid = ".$it["parent"],"name") : ""),
						"name" => $it["name"],
						"period" => $ar["period"] > 1? $this->time2date($ar["period"],5) : "",
						"F_ROW" => $rowhtml,
						"is_hotel" => $itt["has_voucher"] ? "<br>" : ""
					));
					$itm.=$this->parse("ITEM");
				}
			}
		}

		$tx = "";
		$this->db_query("SELECT entry_id,form_id FROM order2form_entries WHERE order_id = $order_id");
		while ($row = $this->db_next())
		{
			$f->load($row["form_id"]);
			$f->load_entry($row["entry_id"]);
			$tx.=$f->show_text();
		}

		$this->vars(array(
			"ITEM" => $itm,
			"user" => $order["user"],
			"time" => $this->time2date($order["tm"],2),
			"ip"	=> $order["ip"],
			"inf_form" => $tx,
			"bill" => $this->mk_my_orb("view_bill", array("shop" => $shop, "order_id" => $order_id, "section" => $section)),
			"confirmation" => $this->mk_my_orb("view_confirmation", array("shop" => $shop, "order_id" => $order_id, "section" => $section)),
			"order_hist" => $this->mk_my_orb("order_history", array("id" => $shop, "section" => $section, "page" => $page)),
			"price" => $order["t_price"]
		));
		return $this->parse();
	}

	function get_shop_categories($id,$prep_store_name = true)
	{
		$sh = $this->get($id);
		classload("objects");
		$o = new objects;
		$oar = $o->gen_rec_list_noprint(array("start_from" => $sh["root_menu"]));

		// now make menu array
		$ret = array();
		foreach ($oar as $oid => $row)
		{
			$str = "";
			$p = $oid;
			while ($p && is_array($oar[$p]))
			{
				$str="/".$oar[$p]["name"].$str;
				$p = $oar[$p]["parent"];
			}

			if ($prep_store_name)
			{
				$str = $sh["name"].$str;
			}
			$ret[$oid] = $str;
		}
		return $ret;
	}

	////
	// !parses the equasion $str and returns an array of all variables used in the equasion
	function parse_eq_variables($str)
	{
		$ret = array();
		$len = strlen($str);
		$in_var = false;

		$varchars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","z","u","v","w","x","y","z","1","2","3","4","5","6","7","8","9","0","_","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","ä","ö","ü","õ","Ö","Ä","Ü","Õ");

		$varbeginchars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","z","u","v","w","x","y","z","_","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","ä","ö","ü","õ","Ö","Ä","Ü","Õ");

		$in_phpvar = false;
		for ($i=0; $i < $len; $i++)
		{
			if ($inphp_var)
			{
				if (in_array($str[$i],$varchars))
				{
				}
				else
				{
					$inphp_var = false;
				}
			}
			else
			if ($in_var)
			{
				if (in_array($str[$i],$varchars))
				{
					// muutuja nimes oleme
					$cur_var.=$str[$i];
				}
				else
				{
					// muutuja nimi l6ppes
					$in_var = false;
					if ($cur_var != "echo")
					{
						$ret[] = $cur_var;
					}
					$cur_var = "";
				}
			}
			else
			{
				if (in_array($str[$i],$varbeginchars))
				{
					// muutuja algas
					$cur_var.=$str[$i];
					$in_var = true;
				}
				else
				{
					if ($str[$i] == "\$")
					{
						$inphp_var= true;
						$in_var = false;
						$cur_var = "";
					}
				}
			}
		}
		if ($in_var)
		{
			if ($cur_var != "echo")
			{
				$ret[] = $cur_var;
			}
		}
		return $ret;
	}

	////
	// !this checks if $count items $it are still available for the dates specified int $f's loaded entry
	// returns the data to be passed to do_order_item or false if not enough items are available
	// note that data has no defined type, it must not be manipulated, just passed to do_order_item 
	function is_item_available(&$it, $count,&$f,$period)
	{
		global $shopping_cart;
		// this thing also has to figure out if we are changing an existing order and then not count the items ordered in that
		// of course only if the order dates are the same. phuq. 
		if ($it["has_max"])
		{
			// check if there still are $count items available. 
			$hm = $this->db_fetch_field("SELECT max_items FROM shop_item2per_prices WHERE tfrom <= $period AND tto >= $period AND item_id = ".$it["oid"],"max_items");
			if ($hm > 0)
			{
				$it["max_items"] = $hm;
			}
			if ($it["has_period"])
			{
				// if the item has periods and has objects for each period then we figure out for which time span the
				// item was requested and check if in that span there are some free objects
				if ($it["has_objs"])
				{
					// check if the user entered some dates in the form, if not, then show error message and go back.
					$from = $f->get_element_value_by_type("date","from");
					$to = $f->get_element_value_by_type("date","to");

					$free_items = array();
					$free_item_count = 0;
					classload("planner");
					$pl = new planner;
					// we must repeat this whole operation $count number of times too.
					// so we must load the items one by one and check their calendars for events during the time specified in the form. 
					$this->db_query("SELECT * FROM objects WHERE parent = ".$it["oid"]." AND class_id = ".CL_SHOP_ITEM." AND status != 0");
					while ($row = $this->db_next())
					{
						// objekti last v2ljas on kalendri id
						// vaatame kas selle objekti kohta on eventeid valitud vahemikus
						if (!$pl->get_events(array("start" => $from, "end" => $to,"parent" => $row["last"])))
						{
							// leidsime vaba itemi
							$free_items[] = $row;
							$free_item_count++;

							// kui oleme leidnud kyllalt palju vabu itemeid, siis l6petame otsimise
							if ($free_item_count == $count)
							{
								break;
							}
						}
					}
					if ($free_item_count < $count)
					{
						return false;
					}
					else
					{
						return array("items" => $free_items, "from" => $from, "to" => $to);
					}
				}
				else
				{
					// if the item is periodic, but doesn't have objects for it, then it 
					// is periodic - and therefore must have some periods defined
					// so we find the period for which the order was placed and check if for that period there are still some available

					$num_sold = $this->db_fetch_field("SELECT num_sold FROM shop_item_period_avail WHERE period = $period AND item_id = ".$it["oid"],"num_sold");

					// nini. siin leiame kas me muudame olemasolevat tellimust ja kas selles on see sama kaup samal ajal tellitud
					if ($shopping_cart["order_id"] && $shopping_cart["old_items"][$it["oid"]]["period"] == $period)
					{
						// kui on samal ajal tellitud siis lisame vanas tellimuses tellitud asjade arvu juurde max itemsitele
						$it["max_items"] += $shopping_cart["old_items"][$it["oid"]]["cnt"];
					}

					if (($it["max_items"] - $num_sold) >= $count)
					{
						return $period;
					}
					else
					{
						return false;
					}
				}
			}
			else
			{
				// if the item doesn't have periods then we just check the item's free count
				if (($it["max_items"] - $it["sold_items"]) >= $count)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			// if the item count is not limited, then there area lways items available
			return true;
		}
	}

	function do_order_item($it,$cnt,$free_items)
	{
		// update the item's sold count
		$this->db_query("UPDATE shop_items SET sold_items = sold_items + ".$cnt." WHERE id = ".$it["oid"]);

		if ($it["has_period"] && $it["has_objs"])
		{
			// if the item has periods and objects then free_items will contain an array
			// of objects to order for period $free_items["from"] to $free_items["to"]

			$pl = new planner;
			// broneerime itemid
			foreach($free_items["items"] as $row)
			{
				// lisame itemi kalenddrisse evendi selle aja peale
				$pl->add_event(array("parent" => $row["last"],"start" => $free_items["from"], "end" => $free_items["to"]));
			}
		}
		else
		if ($it["has_period"])
		{
			// period but no objects, then $free_items contains the period for which to order the items
			// we must update the free count in the correct period
			$this->db_query("SELECT * FROM shop_item_period_avail WHERE item_id = ".$it["oid"]." AND period = $free_items");
			if (!($row = $this->db_next()))
			{
				// there is no record and we must create one
				$this->db_query("INSERT INTO shop_item_period_avail(item_id,period,num_sold) VALUES(".$it["oid"].",$free_items,$cnt)");
			}
			else
			{
				$this->db_query("UPDATE shop_item_period_avail SET num_sold = num_sold + $cnt WHERE item_id = ".$it["oid"]." AND period = $free_items");
			}
		}
	}

	////
	// !shows the bill for order $order_id to user
	function view_bill($arr)
	{
		extract($arr);
		$sh = $this->get($shop);
		$this->read_template("bill.tpl");

		// load the entry from the database
		$this->db_query("SELECT * FROM orders WHERE id = $order_id");
		$order = $this->db_next();

		$type2table = $this->get_tables_for_types($shop);

		$cur_type = 0;
		$first = true;

		classload("form_table");
		$ft = new form_table;

		$allitemsarr = array();

		$item_form = new form;
		$cnt_form = new form;
		$periods = array();
		$this->db_query("SELECT * FROM order2item WHERE order_id = $order_id ORDER BY item_type_order");
		while ($row = $this->db_next())
		{
			$periods[$row["period"]] = $row["period"];
			$this->save_handle();
			if ($cur_type != $row["item_type"])
			{
				if (!$first)
				{
					// l6petame vana tabeli
					$tx.=$ft->finish_table();
				}
				// ja alustame uut
				$ft->start_table($type2table[$row["item_type"]],array("shop" => $shop, "order_id" => $order_id, "section" => $section,"class" =>  "shop", "action" => "view_bill"));

				$itt = $this->get_item_type($row["item_type"]);
				// j2relikult muutus ka itemi sisestamise form, loadime
				$item_form->load($itt["form_id"]);
				$cur_type = $row["item_type"];
			}

			$item = $this->get_item($row["item_id"]);
			$cnt_form->load($item["cnt_form"] ? $item["cnt_form"] : $itt["cnt_form"]);
			$item_parent_name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = ".$item["parent"],"name");
			$item_form->load_entry($item["entry_id"]);
			$item_form->set_element_value_by_type("price",$row["price"]);

			$cnt_form->load_entry($row["cnt_entry"]);

			if ($row["period"] > 1)
			{
				// if there is a period set in the cart for this item, we must replace the date in the item's form 
				// with the selected period
				$el = $item_form->get_element_by_type("date", "from");
				$item_form->set_element_value($el->get_id(),$row["period"]);
			}

			$tmp = $cnt_form;
			$tmp->ee = "aa".$item["entry_id"];	// make fucking sure we get a copy

			$allitemsarr[] = $tmp;

			$tmp = $item_form;
			$tmp->ee = "cc".$item["entry_id"];	// make fucking sure we get a copy
			$allitemsarr[] = $tmp;

			$ft->row_data_from_form(array(&$item_form,&$cnt_form),$item_parent_name);

			$first = false;
			$this->restore_handle();
		}
		$tx.=$ft->finish_table();

		$min_p = 2000000000;
		foreach($periods as $ip)
		{
			if ($ip > 1 && $min_p > $ip)
			{
				$min_p = $ip;
			}
		}
		$max_p = 0;
		foreach($periods as $ip)
		{
			if ($ip > 1 && $max_p < $ip)
			{
				$max_p = $ip;
			}
		}
		$weeks = ($max_p - $min_p)/(24*3600*7);

		$f = new form;
		$txx = "";
		$this->db_query("SELECT entry_id,form_id FROM order2form_entries WHERE order_id = $order_id");
		while ($row = $this->db_next())
		{
			$f->reset();
			$this->save_handle();
			$op_id = $this->db_fetch_field("SELECT op_id FROM shop2order_form where shop_id = $shop AND of_id = ".$row["form_id"],"op_id");
			$txx.=$f->show(array("id" => $row["form_id"],"entry_id" => $row["entry_id"], "op_id" => $op_id));
			$this->restore_handle();
		}

		classload("users");
		$u = new users;
		$ar = $u->get_join_entries();
		foreach($ar as $foid => $eid)
		{
			$f = new form;
			$f->load($foid);
			$f->load_entry($eid);
			$tmp = $f;
			$tmp->allah = $eid;
			$allitemsarr[] = $tmp;
		}

		// calc commission eq
		if ($sh["commission_eq"])
		{
			$eq = $this->get_eq($sh["commission_eq"]);
			$commission = $this->do_parse_eq($eq,$allitemsarr,$weeks);
		}
		$f->reset();
		$this->vars(array(
			"ITEM" => $tx,
			"user" => $order["user"],
			"time" => $this->time2date($order["tm"],3),
			"ip"	=> $order["ip"],
			"number" => $order["id"],
			"inf_form" => $txx,
			"bill" => $this->mk_my_orb("view_bill", array("shop" => $shop, "order_id" => $order_id, "section" => $section)),
			"date" => $this->time2date($order["tm"],3),
			"owner_data" => $f->show(array("id" => $sh["owner_form"], "entry_id" => $sh["owner_form_entry"], "op_id" => $sh["owner_form_op"])),
			"price" => $order["t_price"],
			"log_info" => $u->show_join_data(array("tpl" => "join_data_nopwd.tpl")),
			"commission" => (int)(($commission)+0.5),
			"t_price" => (int)(($order["t_price"] - $commission)+0.5)
		));
		die($this->parse());
	}


////
	// !shows the confirmation for order $order_id to user
	function view_confirmation($arr)
	{
		extract($arr);
		$sh = $this->get($shop);
		$this->read_template("confirmation.tpl");

		// load the entry from the database
		$this->db_query("SELECT * FROM orders WHERE id = $order_id");
		$order = $this->db_next();

		$type2table = $this->get_tables_for_types($shop);

		$cur_type = 0;
		$first = true;

		classload("form_table");
		$ft = new form_table;

		$allitemsarr = array();

		$item_form = new form;
		$cnt_form = new form;
		$this->db_query("SELECT * FROM order2item WHERE order_id = $order_id ORDER BY item_type_order");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			if ($cur_type != $row["item_type"])
			{
				if (!$first)
				{
					// l6petame vana tabeli
					$tx.=$ft->finish_table();
				}
				// ja alustame uut
				$ft->start_table($type2table[$row["item_type"]],array("shop" => $shop, "order_id" => $order_id, "section" => $section,"class" =>  "shop", "action" => "view_confirmation"));

				$itt = $this->get_item_type($row["item_type"]);
				// j2relikult muutus ka itemi sisestamise form, loadime
				$item_form->load($itt["form_id"]);
				$cur_type = $row["item_type"];
			}

			$item = $this->get_item($row["item_id"]);
			$cnt_form->load($item["cnt_form"] ? $item["cnt_form"] : $itt["cnt_form"]);
			$item_parent_name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = ".$item["parent"],"name");
			$item_form->load_entry($item["entry_id"]);
			$item_form->set_element_value_by_type("price",$row["price"]);

			$cnt_form->load_entry($row["cnt_entry"]);

			if ($row["period"] > 1)
			{
				// if there is a period set in the cart for this item, we must replace the date in the item's form 
				// with the selected period
				$el = $item_form->get_element_by_type("date", "from");
				$item_form->set_element_value($el->get_id(),$row["period"]);
			}

			$tmp = $cnt_form;
			$tmp->ee = "aa".$item["entry_id"];	// make fucking sure we get a copy

			$allitemsarr[] = $tmp;

			$tmp = $item_form;
			$tmp->ee = "cc".$item["entry_id"];	// make fucking sure we get a copy
			$allitemsarr[] = $tmp;

			$ft->row_data_from_form(array(&$item_form,&$cnt_form),$item_parent_name);

			$first = false;
			$this->restore_handle();
		}
		$tx.=$ft->finish_table();

		$f = new form;
		$txx = "";
		$this->db_query("SELECT entry_id,form_id FROM order2form_entries WHERE order_id = $order_id");
		while ($row = $this->db_next())
		{
			$f->reset();
			$this->save_handle();
			$op_id = $this->db_fetch_field("SELECT op_id FROM shop2order_form where shop_id = $shop AND of_id = ".$row["form_id"],"op_id");
			$txx.=$f->show(array("id" => $row["form_id"],"entry_id" => $row["entry_id"], "op_id" => $op_id));
			$this->restore_handle();
		}

		classload("users");
		$u = new users;
		$ar = $u->get_join_entries();
		foreach($ar as $foid => $eid)
		{
			$f = new form;
			$f->load($foid);
			$f->load_entry($eid);
			$tmp = $f;
			$tmp->allah = $eid;
			$allitemsarr[] = $tmp;
		}
		// calc commission eq
		if ($sh["commission_eq"])
		{
			$eq = $this->get_eq($sh["commission_eq"]);
			$commission = $this->do_parse_eq($eq,$allitemsarr);
		}
		$f->reset();
		$this->vars(array(
			"ITEM" => $tx,
			"user" => $order["user"],
			"time" => $this->time2date($order["tm"],3),
			"ip"	=> $order["ip"],
			"number" => $order["id"],
			"inf_form" => $txx,
			"confirmation" => $this->mk_my_orb("view_confirmation", array("shop" => $shop, "order_id" => $order_id, "section" => $section)),
			"date" => $this->time2date($order["tm"],3),
			"owner_data" => $f->show(array("id" => $sh["owner_form"], "entry_id" => $sh["owner_form_entry"], "op_id" => $sh["owner_form_op"])),
			"price" => $order["t_price"],
			"log_info" => $u->show_join_data(array("tpl" => "join_data_nopwd.tpl")),
			"commission" => (int)(($commission)+0.5),
			"t_price" => (int)(($order["t_price"] - $commission)+0.5)
		));

		die($this->parse());
	}




	////
	// !lets the user change the tables with what the bill is created for shop $id
	function change_tables($arr)
	{
		extract($arr);
		$sh = $this->get($id);
		$this->mk_path($sh["parent"],"<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Change target</a> / Change tables");
		$this->read_template("change_tables.tpl");

		$this->do_core_change_tables($id);

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_tables", array("id" => $id))
		));
		return $this->parse();
	}

	////
	// !saves the tables that the user selected with what to sho items on the bill
	function submit_tables($arr)
	{
		extract($arr);
		
		$this->db_query("DELETE FROM shop2table WHERE shop_id = '$id'");
		if (is_array($tables))
		{
			foreach($tables as $typeid => $tableid)
			{
				$this->db_query("INSERT INTO shop2table(shop_id,table_id,type_id) VALUES($id,'$tableid','$typeid')");
			}	
		}

		return $this->mk_my_orb("change_tables", array("id" => $id));
	}

	////
	// !returns an array $type_id => $table_id as defined for the shop
	function get_tables_for_types($shop)
	{
		$ret = array();
		$this->db_query("SELECT * FROM shop2table WHERE shop_id = $shop");
		while ($row = $this->db_next())
		{
			$ret[$row["type_id"]] = $row["table_id"];
		}
		return $ret;
	}

	////
	// !generates a list of vouchers for order $order_id
	function list_vouchers($arr)
	{
		extract($arr);
		$this->read_template("list_vouchers.tpl");

		$itypes = $this->listall_item_types(ALL_PROPS);

		$this->db_query("select * from order2item where order_id = $order_id");
		while ($row = $this->db_next())
		{
			if ($itypes[$row["item_type"]]["has_voucher"])
			{
				$this->save_handle();
				$it = $this->get_item($row["item_id"]);
				$parent_name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = ".$it["parent"],"name");

				$this->vars(array(
					"view_voucher" => $this->mk_my_orb("view_voucher", array("order_id" => $order_id, "shop" => $shop, "section" => $section, "item_id" => $row["item_id"])),
					"parent_name" => $parent_name
				));
				$vc.=$this->parse("VOUCHER");
				$this->restore_handle();
			}
		}
		$this->vars(array(
			"to_list" => $this->mk_my_orb("order_history", array("id" => $shop, "order_id" => $order_id, "section" => $section)),
			"VOUCHER" => $vc
		));
		return $this->parse();
	}

	////
	// !shows the voucher for item $item_id of order $order_id to user
	function view_voucher($arr)
	{
		extract($arr);
		$this->read_template("voucher.tpl");

		$sh = $this->get($shop);

		$item = $this->get_item($item_id);
		$parent_name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = ".$item["parent"],"name");

		// yritame leida alguse ja l6pu kuup2evad.
		$pers = array();
		$this->db_query("SELECT period FROM order2item WHERE order_id = $order_id AND period > 1");
		$row = $this->db_next();
		$begin = $row["period"];
		$row = $this->db_next();
		$end = $row["period"];
		if ($begin > $end)
		{
			$tmp = $end;
			$end = $begin;
			$begin = $tmp;
		}

		$f = new form;
		$txx = "";
		$this->db_query("SELECT entry_id,form_id FROM order2form_entries WHERE order_id = $order_id");
		while ($row = $this->db_next())
		{
			$f->reset();
			$this->save_handle();
			$op_id = $this->db_fetch_field("SELECT op_id FROM shop2order_form where shop_id = $shop AND of_id = ".$row["form_id"],"op_id");
			$txx.=$f->show(array("id" => $row["form_id"],"entry_id" => $row["entry_id"], "op_id" => $op_id));
			$this->restore_handle();
		}

		$f = new form;
		$issuedby = $f->show(array("id" => $sh["owner_form"], "entry_id" => $sh["owner_form_entry"], "op_id" => $sh["owner_form_op_issued"]));
		$f->reset();

		classload("users");
		$u = new users;
		$pt =  $u->show_join_data(array("tpl" => "join_data_nopwd.tpl","second" => true));

		$this->vars(array(
			"shop_owner_info" => $f->show(array("id" => $sh["owner_form"], "entry_id" => $sh["owner_form_entry"], "op_id" => $sh["owner_form_op_voucher"])),
			"parent_name" => $parent_name,
			"clients" => $txx,
			"issuedby" => $issuedby,
			"paythrough" => $pt,
			"from" => $this->time2date($begin,3),
			"to" => $this->time2date($end,3),
			"voucher_no" => $order_id."_".$item_id,
			"itemname" => $item["name"]
		));
		die($this->parse());
	}

	function get_list_price($item_id,$def,$shop)
	{
		if (!is_array($GLOBALS["shopping_cart"]["items"]))
		{
			return $def;
		}

		$tmpar = $GLOBALS["shopping_cart"]["items"];	// copy so we won't mess up the internal pointer

		foreach ($tmpar as $iid => $idata)
		{
			if ($idata["period"] && !$start)
			{
				$start = $idata["period"];
			}
			else
			if ($idata["period"] && !$end)
			{
				$end = $idata["period"];
			}
		}
		if ($end < $start)
		{
			$tmp = $start;
			$start = $end;
			$end = $tmp;
		}
	
		if (!$end || !$start)
		{
			return $def;
		}

		classload("users");
		$u = new users;
		$u_cur = $u->get_user_config(array("uid" => $GLOBALS["uid"], "key" => "user_currency"));

		$this->save_handle();
		$this->db_query("SELECT * FROM shop_item2per_prices WHERE item_id = $item_id AND tfrom <= $start AND tto >= $start ");
		while ($row = $this->db_next())
		{
			if ($row["per_type"] == PRICE_PER_WEEK)
			{
				$pp_week =  $row["price"];
				$tp = unserialize($pp_week);
				if (is_array($tp))
				{
					if ($u_cur)
					{
						$pp_week = $tp[$u_cur];
					}
					else
					{
						$pp_week = $tp[$shop["def_cur"]];
					}
				}
			}
			else
			if ($row["per_type"] == PRICE_PER_2WEEK)
			{
				$pp_2week = $row["price"];
				$tp = unserialize($pp_2week);
				if (is_array($tp))
				{
					if ($u_cur)
					{
						$pp_2week = $tp[$u_cur];
					}
					else
					{
						$pp_2week = $tp[$shop["def_cur"]];
					}
				}
			}
		}
		$this->restore_handle();
		$diff = $end - $start;

		if (($end - $start <= 24*3600*7) && $pp_week)
		{
			return $pp_week;
		}
		else
		if ((($end - $start > 24*3600*7) && ($end - $start <= 24*3600*14)) && $pp_2week)
		{
			return $pp_2week;
		}
		return $def;
	}

	////
	// !reads in the order from the database and redirects to the shopping cart
	function change_order($arr)
	{
		extract($arr);

		$GLOBALS["order_forms"] = array();

		// load the entry from the database
		$this->db_query("SELECT * FROM orders WHERE id = $order_id");
		$order = $this->db_next();

		$GLOBALS["shopping_cart"] = array("order_id" => $order_id,"price" => $t_price);

		$this->db_query("SELECT order2item.*,shop_items.cnt_form as cnt_form, shop_item_types.cnt_form as type_cnt_form FROM order2item LEFT JOIN shop_items ON shop_items.id = order2item.item_id LEFT JOIN shop_item_types ON shop_item_types.id = shop_items.type_id WHERE order_id = $order_id ORDER BY item_type_order");
		while ($row = $this->db_next())
		{
			// siinkohal paneme kirja ka selle et mis kuup2evade kohta eelnev tellimus tehtud on et siis olex p2rast is_item_available
			// sees lihtsam tshekkida et kas on vaja selle tellimuse sees tellitud arv vaja maha lahutada v6i ei
			// ja yldse, teeme koopia eelnevast tellimusest ka et saax kohe selle j2rgi v6rrelda
			$GLOBALS["shopping_cart"]["old_items"][$row["item_id"]]["cnt"] = $row["count"];
			$GLOBALS["shopping_cart"]["old_items"][$row["item_id"]]["price"] = $row["price"];
			$GLOBALS["shopping_cart"]["old_items"][$row["item_id"]]["period"] = $row["period"];
			$GLOBALS["shopping_cart"]["old_items"][$row["item_id"]]["cnt_entry"] = $row["entry_id"];

			$GLOBALS["shopping_cart"]["items"][$row["item_id"]]["cnt"] = $row["count"];
			$GLOBALS["shopping_cart"]["items"][$row["item_id"]]["price"] = $row["price"];
			$GLOBALS["shopping_cart"]["items"][$row["item_id"]]["period"] = $row["period"];
			$GLOBALS["shopping_cart"]["items"][$row["item_id"]]["cnt_entry"] = $row["entry_id"];

			// nini. nyt teeme trikka. nimelt on probleem selles et kui tellimust muuta a mitte kinnitada, 
			// siis ei tohi baasis olev tellimus muutuda - sellex teeme pisikse h2ki ja teeme koopia 
			// k6igist tellimusega seotud formi sisestustest niiet kui neid muudetakse siis vana tellimus j22b ikka samax
			// ja kui tehakse uus, siis lithsalt vana tellimuse sisestused j22vad ajaloo hammasrataste vahele
			// k6igepealt loadime formi
			$f = new form;
			$f->load($row["cnt_form"] ? $row["cnt_form"] : $row["type_cnt_form"]);
			// nyyd loadime praeguse entry
			$f->load_entry($row["cnt_entry"]);
			// ja nyt salvestame ta kohe 2ra aga id'd ette ei anna, nii saamegi koopia
			$f->process_entry(array("id" => $row["cnt_form"] ? $row["cnt_form"] : $row["type_cnt_form"],"no_process_entry" => true,"no_load_form" => true));
	
			$GLOBALS["shopping_cart"]["items"][$row["item_id"]]["cnt_entry"] = $f->entry_id;
		}


		$ofs = $this->get_ofs_for_shop($shop);
		// first time, so fill arrays
		$cnt = 0;
		foreach($ofs as $fid => $row)
		{
			$GLOBALS["order_forms"]["order"][$cnt]["form"] = $fid;
			$GLOBALS["order_forms"]["order"][$cnt]["chk"] = $row["repeat"];
			$GLOBALS["order_forms"]["order"][$cnt]["op_id"] = $row["op_id"];
			$cnt++;
		}
		$GLOBALS["order_forms"]["max_cnt"] = $cnt;
		$GLOBALS["order_forms"]["current_cnt"] = 0;

		$cur_form = 0;
		$this->db_query("SELECT * FROM order2form_entries WHERE order_id = $order_id");
		while ($row = $this->db_next())
		{
			// ja siin tuleb nende formide sisestustest samuti koopiad teha et neid ka muuta saax. ohjah. tegelt see on n6me matean
			// et need tulex tegelt m2llu (sessiooni) loadida ja siis seal muuta kuni tellimus kinnitataxe
			// but hell, this works and this shit has got to be ready in the morning
			$f = new form;
			$f->load($row["form_id"]);
			$f->load_entry($row["entry_id"]);
			$f->process_entry(array("id" => $row["form_id"], "no_process_entry" => true,"no_load_form" => true));

			$GLOBALS["order_forms"]["entries"][] =array("num" => $row["num"], "form" => $row["form_id"], "entry" => $f->entry_id, "name" => $row["name"]);
			if ($cur_form != $row["form_id"])
			{
				$cur_form = $row["form_id"];
				$GLOBALS["order_forms"]["current_cnt"]=0;
			}
			$GLOBALS["order_forms"]["current_cnt"]++;
		}

		global $order_forms;
		session_register("order_forms");

		return $this->view_cart(array("shop_id" => $shop, "section" => $section));
	}

	function do_cancel_order($order_id)
	{
		$this->db_query("SELECT * FROM orders WHERE id = $order_id");
		$ret = $this->db_next();

		$this->db_query("SELECT * FROM order2item WHERE order_id = $order_id ORDER BY item_type_order");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$it = $this->get_item($row["item_id"]);
			if ($it["has_period"] && !$it["has_objs"])
			{
				$this->db_query("UPDATE shop_item_period_avail SET num_sold=num_sold - ".$row["count"]." WHERE item_id = ".$row["item_id"]." AND period = ".$row["period"]);
			}
			$this->db_query("UPDATE shop_items SET sold_items = sold_items - ".$row["count"]." WHERE id = ".$row["item_id"]);
			$this->restore_handle();
		}
		$this->db_query("DELETE FROM order2item WHERE order_id = $order_id");
		$this->db_query("DELETE FROM orders WHERE id = $order_id");
		$this->db_query("DELETE FROM order2form_entries WHERE order_id = $order_id");
		return $ret;
	}

	function cancel_order($arr)
	{
		extract($arr);
		$order = $this->do_cancel_order($order_id);

		$sh = $this->get($shop);

		$mail = LC_SHOP_ORDER_USER.$GLOBALS["uid"].sprintf(LC_SHOP_ABOLISH_ORDER,$order_id);
		$mail.= $this->time2date(time(),2);

		$emails = explode(",",$sh["emails"]);
		foreach($emails as $email)
		{
			mail($email,LC_SHOP_ORDER, $mail,"From: automatweb@automatweb.com\n");
		}

		return $this->mk_my_orb("order_history", array("id" => $shop, "section" => $section));
	}

	function cancel_current($arr)
	{
		extract($arr);
		$GLOBALS["shopping_cart"] = array();
		$GLOBALS["order_forms"] = array();
		return $this->mk_my_orb("order_history", array("id" => $shop, "section" => $section));
	}

	function delentry($arr)
	{
		extract($arr);
		global $order_forms;
		if ($c_num == $order_forms["entries"][$num]["num"])
		{
			$order_forms["current_cnt"] = max($order_forms["current_cnt"]-1,0);
		}
		unset($order_forms["entries"][$num]);
		header("Location: ".$this->mk_my_orb("order", array("shop_id" => $shop_id, "section" => $section,"num" => 0)));
	}
}
?>
