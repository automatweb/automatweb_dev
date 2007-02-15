<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/spa_bookings/spa_customer_interface.aw,v 1.1 2007/02/15 15:35:34 kristo Exp $
// spa_customer_interface.aw - SPA Kliendi liides 
/*

@classinfo syslog_type=ST_SPA_CUSTOMER_INTERFACE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class spa_customer_interface extends class_base
{
	function spa_customer_interface()
	{
		$this->init(array(
			"tpldir" => "applications/spa_bookings/spa_customer_interface",
			"clid" => CL_SPA_CUSTOMER_INTERFACE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		return $this->_disp_bookings($arr["id"]);
	}

	function _disp_bookings($id)
	{
		$this->read_template("book_times.tpl");

		$p = get_current_person();
		$ol = new object_list(array(
			"class_id" => CL_SPA_BOOKING,
			"lang_id" => array(),
			"site_id" => array(),
			"person" => $p->id(),
			"sort_by" => "objects.created desc"
		));

		$ei = get_instance(CL_SPA_BOOKIGS_ENTRY);

		foreach($ol->arr() as $o)
		{
			// bookingul has package
			// package has products
			// rooms have products
			// so, list all the products in the package and for each product let the user select from all the rooms that have that package
			$dates = $ei->get_booking_data_from_booking($o);

			
			$booking_str = sprintf(t("Broneering %s / %s"), 
				$o->prop("person.name"),
				date("d.m.Y", $o->created())
			);

			$gl = aw_global_get("gidlist_oid");
			$booking_str .= " ".html::href(array(
				"url" => $this->mk_my_orb("add_prod_to_bron", array(
					"bron" => $o->id(), 
					"id" => $id,
					"r" => get_ru()
				)),
				"caption" => t("Lisa teenus"),
			));

			$booking_str .= " / ".html::href(array(
				"url" => $ei->mk_my_orb("print_booking", array("id" => $o->id(), "wb" => 231)),
				"caption" => t("Prindi"),
				"target" => "_blank"
			));

			$this->vars(array(
				"booking" => $booking_str,
				"booking_id" => $o->id()
			));

			$fd = array();
			$has_unc = false;
			$prod_list = array();
			$grp_list = array();
			foreach(safe_array($o->meta("extra_prods")) as $extra_item_entry)
			{
				$grp_list[] = "__ei|".$extra_item_entry["prod"];
			}
			foreach($grp_list as $prod_group)
			{
				// repeat group by the count of the first product in the group
				if (substr($prod_group, 0, 4) == "__ei")
				{
					list(, $prod_id) = explode("|", $prod_group);
					$prods_in_group = array($prod_id);
				}
				$first_item_count = max(1,$prod_list[reset($prods_in_group)]);
				for ($i = 0; $i < $first_item_count; $i++)
				{
					$prod_str = array();
					$date = "";
					$date_booking_id = null;
					$prod2room = array();
					$prod2tm = array();
					$selected_prod = false;
					foreach($prods_in_group as $prod_id)
					{
						$prod = obj($prod_id);
						foreach($dates as $_prod_id => $nums)
						{
							if ($_prod_id == $prod_id && isset($nums[$i]) && $nums[$i]["from"] > 1)
							{
								$sets = $nums[$i];
								$room = obj($sets["room"]);
								$prod2room[$_prod_id] = $room->id();
								$prod2tm[$_prod_id] = $sets["from"];
								$date .= sprintf("Ruum %s, ajal %s - %s", $room->name(), date("d.m.Y H:i", $sets["from"]), date("H:i", $sets["to"]));
								$date_booking_id = $sets["reservation_id"];
								$selected_prod = $prod_id;
							}
						}
					}


					foreach($prods_in_group as $prod_id)
					{
						$prod = obj($prod_id);
						if ($date == "")
						{
							$prod_str[] = html::popup(array(
								"url" => $ei->mk_my_orb("select_room_booking", array("booking" => $o->id(), "prod" => $prod_id, "prod_num" => "".$i, "section" => "3169")),
								"caption" => $prod->name(),
								"height" => 500,
								"width" => 750,
								"scrollbars" => 1,
								"resizable" => 1
							));
							$has_dates = false;
						}
						else
						{
							$prod_str[] = $selected_prod == $prod->id() ? "<u>".$prod->name()."</u>" : $prod->name();
						}
					}
					if ($date != "")
					{
						$ri = get_instance(CL_ROOM);
						$settings = $ri->get_settings_for_room(obj($prod2room[$prod_id]));
						if ($ri->group_can_do_bron($settings, $prod2tm[$prod_id]))
						{
							$date .= " ".html::href(array(
								"url" => $ei->mk_my_orb("clear_booking", array("return_url" => get_ru(), "booking" => $date_booking_id)),
								"caption" => t("T&uuml;hista")
							));
						}
					}
					else
					{
						$has_unc = true;
					}

					$this->vars(array(
						"booking_ln" => $booking_str,
						"name" => join("<br>", $prod_str),
						"when" => $date
					));
					$book_line .= $this->parse("BOOK_LINE");
				}
			}

			if (!$_GET["notimes"] || $has_dates)
			{
				$this->vars(array(
					"BOOK_LINE" => $book_line,
					"disp_main" => $o->modified() > (time() - 300) ? "block" : "none",
					"disp_short" => $o->modified() > (time() - 300) ? "none" : "block"
				));
				$bookings .= $this->parse("BOOKING");
			}
			$book_line = "";

		}
		$this->vars(array(
			"BOOKING" => $bookings,
			"add_pk_url" => $this->mk_my_orb("add_pkt", array("id" => $id, "r" => get_ru()))
		));
		return $this->parse();
	}

	/**
		@attrib name=add_pkt
		@param id required type=int acl=view
		@param r required 
	**/
	function add_pkt($arr)
	{
		// list prods and let the user select one
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"lang_id" => array(),
			"site_id" => array()
		));
		$p = array();
		foreach($ol->arr() as $o)
		{
			$p[$o->parent()][] = $o;
		}

		$pts = "";
		$this->read_template("add_pkt.tpl");
		foreach($p as $parent => $prods)
		{
			$po = obj($parent);
			$p_list = array();
			foreach($prods as $pr)
			{
				$p_list[] = html::href(array(
					"url" => $this->mk_my_orb("add_prod_to_new_pkt", array("prod" => $pr->id(), "id" => $arr["id"], "r" => $arr["r"])),
					"caption" => $pr->name()
				));
			}
			$this->vars(array(
				"prods" => join(", ", $p_list),
				"parent" => $po->name()
			));
			$pts .= $this->parse("PARENT");
		}

		$this->vars(array(
			"PARENT" => $pts
		));

		return $this->parse();
	}

	/**
		@attrib name=add_prod_to_new_pkt
		@param prod required type=int
		@param id required type=int
		@param r required 
	**/
	function add_prod_to_new_pkt($arr)
	{
		// create spa booking
		$b = obj();
		$b->set_parent($arr["id"]);
		$b->set_class_id(CL_SPA_BOOKING);

		$p = get_current_person();
		$b->set_prop("person", $p->id());	

		$b->save();

		$i = get_instance(CL_SPA_BOOKIGS_ENTRY);
		$i->fin_add_prod_to_bron(array(
			"bron" => $b->id(),
			"wb" => $arr["id"],
			"prod" => $arr["prod"]
		));

		return $arr["r"];
	}

	/**
		@attrib name=add_prod_to_bron
		@param id required type=int acl=view
		@param r required 
		@param bron required 
	**/
	function add_prod_to_bron($arr)
	{
		// list prods and let the user select one
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"lang_id" => array(),
			"site_id" => array()
		));
		$p = array();
		foreach($ol->arr() as $o)
		{
			$p[$o->parent()][] = $o;
		}

		$pts = "";
		$this->read_template("add_pkt.tpl");
		foreach($p as $parent => $prods)
		{
			$po = obj($parent);
			$p_list = array();
			foreach($prods as $pr)
			{
				$p_list[] = html::href(array(
					"url" => $this->mk_my_orb("fin_add_prod_to_bron", array(
						"prod" => $pr->id(), 
						"id" => $arr["id"], 
						"r" => $arr["r"],
						"bron" => $arr["bron"]
					)),
					"caption" => $pr->name()
				));
			}
			$this->vars(array(
				"prods" => join(", ", $p_list),
				"parent" => $po->name()
			));
			$pts .= $this->parse("PARENT");
		}

		$this->vars(array(
			"PARENT" => $pts
		));

		return $this->parse();
	}

	/**
		@attrib name=fin_add_prod_to_bron
		@param prod required type=int acl=view
		@param id required type=int acl=view
		@param bron required type=int acl=view
		@param r required 
	**/
	function fin_add_prod_to_bron($arr)
	{
		$i = get_instance(CL_SPA_BOOKIGS_ENTRY);
		$i->fin_add_prod_to_bron($arr);
		return $arr["r"];
	}
}
?>
