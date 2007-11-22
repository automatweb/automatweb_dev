<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/spa_bookings/spa_customer_interface.aw,v 1.32 2007/11/22 12:01:19 kristo Exp $
// spa_customer_interface.aw - SPA Kliendi liides 
/*

@classinfo syslog_type=ST_SPA_CUSTOMER_INTERFACE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property prod_folders type=relpicker reltype=RELTYPE_FOLDER multiple=1 field=meta method=serialize
@caption Toodete kataloogid

@property rooms type=relpicker reltype=RELTYPE_ROOM multiple=1 field=meta method=serialize
@caption Ruumid

@property bank_payment type=relpicker reltype=RELTYPE_BANK_PAYMENT field=meta method=serialize
@caption Pangamakse objekt

@reltype FOLDER value=1 clid=CL_MENU
@caption Toodete kataloog

@reltype ROOM value=2 clid=CL_ROOM
@caption Ruum

@reltype BANK_PAYMENT value=3 clid=CL_BANK_PAYMENT
@caption Pangamakse

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
			case "prod_folders":
				uksort($prop["options"], array(&$this, "sort_menus"));
				break;
		};
		return $retval;
	}

	function sort_menus($a,$b)
	{
		if(!$this->can("view" , $a)) return -1;
		if(!$this->can("view" , $b)) return 1;
		$a_obj = obj($a);
		$b_obj = obj($b);
		return ($b_obj->ord() > $a_obj->ord()) ? -1 : 1;
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
		classload("core/date/date_calc");
		$this->read_template("book_times.tpl");
		lc_site_load("spa_customer_interface", &$this);
		$p = get_current_person();
		$ol = new object_list(array(
			"class_id" => CL_SPA_BOOKING,
			"lang_id" => array(),
			"site_id" => array(),
			"person" => $p->id(),
			"sort_by" => "objects.created desc"
		));

		$ei = get_instance(CL_SPA_BOOKIGS_ENTRY);

		$ct = obj($id);
		
		$bank_payment = $ct->prop("bank_payment");
		
		$rooms = $ct->prop("rooms");

		foreach($ol->arr() as $o)
		{
			$_min_date = null;
			$_max_date = null;
			// bookingul has package
			// package has products
			// rooms have products
			// so, list all the products in the package and for each product let the user select from all the rooms that have that package
			$dates = $ei->get_booking_data_from_booking($o);

			$booking_str = sprintf(t("Broneering %s / %s"), 
				$o->prop("person.name"),
				date("d.m.Y", $o->created())
			);

			$o_begin = 2147483647;
			$o_end = 0;
			$gl = aw_global_get("gidlist_oid");

			$confirmed = true;
			$has_times = true;
			$has_prods = false;
			foreach($o->connections_from(array("type" => "RELTYPE_ROOM_BRON")) as $c)
			{
				$has_prods = true;
				$bron = $c->to();
				$confirmed &= $bron->prop("verified");
				if ($bron->prop("start1") < 100)
				{
					$has_times = false;
				}
				$o_end = max($o_end, $bron->prop("end"));
				$o_begin = min($o_begin, $bron->prop("start1"));
			}

			if (!$has_prods)
			{
				continue;
			}

			if (!$confirmed || !$has_prods)
			{
				$booking_str .= " ".html::href(array(
				
					"url" => $this->mk_my_orb("add_prod_to_bron", array(
						"bron" => $o->id(), 
						"id" => $id,
						"r" => get_ru(),
						"section" => aw_global_get("section")
					)),
					"caption" => t("Lisa teenus"),
				));
			}

			if (!$confirmed && $has_times && $has_prods)
			{
				$pay_url = $this->mk_my_orb("pay", array("id" => $o->id(), "r" => get_ru() , "bank_payment" => $bank_payment,"section" => aw_global_get("section"),));

				$booking_str .= " / ".html::href(array(
					"url" => $pay_url,
				//"url" => $this->mk_my_orb("confirm_booking", array("id" => $o->id(), "r" => get_ru())),
				//	"caption" => t("Kinnita"),
					"caption" => t("Maksa"),
				));
			}

			if ($confirmed && $has_times && $has_prods)
			{
				$booking_str .= " / ".html::href(array(
					"url" => $this->mk_my_orb("print_booking", array("id" => $o->id(), "wb" => 231)),
					"caption" => t("Prindi"),
					"target" => "_blank"
				));
			}

			$this->vars(array(
				"booking" => $booking_str,
				"booking_id" => $o->id(),
				"person_name" => $o->prop("person.name"),
				"bron_date" => date("d.m.Y", $o->created()),
				"add_service_url" => $this->mk_my_orb("add_prod_to_bron", array(
						"bron" => $o->id(), 
						"id" => $id,
						"r" => get_ru(),
						"section" => aw_global_get("section")
					)),
				"confirm_url" => $this->mk_my_orb("confirm_booking", array("id" => $o->id(), "r" => get_ru())),
				"pay_url" => $this->mk_my_orb("pay", array("id" => $o->id(), "r" => get_ru() , "bank_payment" => $bank_payment,"section" => aw_global_get("section"))),
				"print_url" => $this->mk_my_orb("print_booking", array("id" => $o->id(), "wb" => 231)),
			));
			if (!$confirmed || !$has_prods)
			{
				$this->vars(array(
					"ADD_SERVICE" => $this->parse("ADD_SERVICE")
				));
			}
			else
			{
				$this->vars(array(
					"ADD_SERVICE" => ""
				));
			}

			if (!$confirmed && $has_times && $has_prods)
			{
				$this->vars(array(
					"CONFIRM" => $this->parse("CONFIRM")
				));
			}
			else
			{
				$this->vars(array(
					"CONFIRM" => ""
				));
			}

			if (!$confirmed || !$has_times || !$has_prods)
			{
				$this->vars(array(
					"PRINT" => ""
				));
			}
			else
			{
				$this->vars(array(
					"PRINT" => $this->parse("PRINT")
				));
			}

			$fd = array();
			$has_unc = false;
			$prod_list = array();
			$grp_list = array();
			foreach(safe_array($o->meta("extra_prods")) as $extra_item_entry)
			{
				$grp_list[] = "__ei|".$extra_item_entry["prod"];
			}


			if (count($grp_list) == 0)
			{
				continue;
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
					$rvs_obj = false;
					$this->vars(array(
						"HAS_BOOKING" => "",
						"CLEAR" => "",
						"DEL_SERVICE" => ""
					));
					foreach($prods_in_group as $prod_id)
					{
						$prod = obj($prod_id);
						foreach($dates as $_prod_id => $nums)
						{
							if ($nums[$i]["from"] > -1)
							{
								if ($_min_date == null)
								{
									$_min_date = $nums[$i]["from"];
								}
								if ($_max_date == null)
								{
									$_max_date = $nums[$i]["from"];
								}
								$_min_date = min($nums[$i]["from"],$_min_date);
								$_max_date = max($nums[$i]["from"],$_max_date);
							}
							if ($nums[$i]["to"] > -1)
							{
								if ($_min_date == null)
                                                                {
                                                                        $_min_date = $nums[$i]["to"];
                                                                }
                                                                if ($_max_date == null)
                                                                {
                                                                        $_max_date = $nums[$i]["to"];
                                                                }
                                                                $_min_date = min($nums[$i]["from"],$_min_date);
                                                                $_max_date = max($nums[$i]["from"],$_max_date);

							}
							if ($_prod_id == $prod_id && isset($nums[$i]) && $nums[$i]["from"] > 1)
							{
								$sets = $nums[$i];
								$rvs_obj = obj($sets["reservation_id"]);
								$room = obj($sets["room"]);
								$prod2room[$_prod_id] = $room->id();
								$prod2tm[$_prod_id] = $sets["from"];
								$date .= sprintf("Ruum %s, ajal %s - %s", $room->name(), date("d.m.Y H:i", $sets["from"]), date("H:i", $sets["to"]));
								$this->vars(array(
									"b_room" => $room->name(),
									"b_from" => date("d.m.Y H:i", $sets["from"]),
									"b_to" => date("H:i", $sets["to"])
								));
								$this->vars(array(
									"HAS_BOOKING" => $this->parse("HAS_BOOKING")
								));
								$date_booking_id = $sets["reservation_id"];
								$selected_prod = $prod_id;
							}
							else
							if ($_prod_id == $prod_id)
							{
								$date_booking_id = $sets["reservation_id"];
							}
						}
					}

					foreach($prods_in_group as $prod_id)
					{
						$prod = obj($prod_id);
						if ($date == "")
						{
							$prod_str[] = html::popup(array(
								"url" => $ei->mk_my_orb("select_room_booking", array("booking" => $o->id(), "prod" => $prod_id, "prod_num" => "".$i, "section" => "3169", "_not_verified" => 1, "rooms" => $rooms)),
								"caption" => $prod->trans_get_val("name"),
								"height" => 500,
								"width" => 750,
								"scrollbars" => 1,
								"resizable" => 1
							));
							$has_dates = false;
						}
						else
						{
							$prod_str[] = $selected_prod == $prod->id() ? "<u>".$prod->trans_get_val("name")."</u>" : $prod->trans_get_val("name");
						}
					}
					if ($date != "")
					{
						$ri = get_instance(CL_ROOM);
						$settings = $ri->get_settings_for_room(obj($prod2room[$prod_id]));
						if ($ri->group_can_do_bron($settings, $prod2tm[$prod_id]) && (!$rvs_obj || !$rvs_obj->prop("verified")))
						{
							$date .= " ".html::href(array(
								"url" => $ei->mk_my_orb("clear_booking", array("return_url" => get_ru(), "booking" => $date_booking_id)),
								"caption" => t("T&uuml;hista")
							));
							$this->vars(array(
								"clear_url" => $ei->mk_my_orb("clear_booking", array(
									"return_url" => get_ru(), 
									"booking" => $date_booking_id
								)),
								"delete_url" => $ei->mk_my_orb("delete_booking", array(
									"return_url" => get_ru(), 
									"booking" => $date_booking_id,
									"spa_bron" => $o->id()
								)),
							));
							$this->vars(array(
								"CLEAR" => $this->parse("CLEAR"),
							));
						}
					}
					else
					{
						$has_unc = true;
					}

                                        foreach($prods_in_group as $prod_id)
                                        {
                                                $prod = obj($prod_id);
                                                foreach($dates as $_prod_id => $nums)
                                                {
                                                        if ($_prod_id == $prod_id)
                                                        {
								$this->vars(array(
									"delete_url" => $ei->mk_my_orb("delete_booking", array(
										"return_url" => get_ru(),
										"booking" => $nums[0]["reservation_id"],
										"spa_bron" => $o->id()
									)),
								));
								$this->vars(array(
									"DEL_SERVICE" => (!$confirmed ? $this->parse("DEL_SERVICE") : "")
								));
							}
						}
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
				if ($_min_time > time() || ($_min_time == null && $o->created() > get_day_start()))
				{
					if ($bookings == "" && $f_booking == "" && $this->is_template("FIRST_BOOKING") && time() < $o_end)
					{
						$cur_f_booking .= $this->parse("FIRST_BOOKING");
					}
					else
					{
						$cur_booking .= $this->parse("BOOKING");
					}
				}
				else
				{
					if ($bookings == "" && $f_booking == "" && $this->is_template("FIRST_BOOKING") && time() < $o_end)
					{
						$f_booking = $this->parse("FIRST_BOOKING");
					}
					else
					{
						$bookings .= $this->parse("BOOKING");
					}
				}
			}
			$book_line = "";

		}
		$this->vars(array(
			"FIRST_BOOKING" => $f_booking,
			"BOOKING" => $bookings,
			"add_pk_url" => $this->mk_my_orb("add_pkt", array("id" => $id, "r" => get_ru())),
			"cur_f_booking" => $cur_f_booking,
			"cur_booking" => $cur_booking,
			"error" => ($_SESSION["reservation"]["error"]) ? $this->parse("error") : "",
		));
		if ($bookings != "")
		{
			$this->vars(array(
				"HAS_PREV_BOOKINGS" => $this->parse("HAS_PREV_BOOKINGS")
			));
		}
		return $this->parse();
	}

	function _get_prod_parents($oid)
	{
		$o = obj($oid);
		if (is_oid($o->prop("prod_folders")))
		{
			$ot = new object_tree(array(
				"parent" => $o->prop("prod_folders"),
				"lang_id" => array(),
				"site_id" => array()
			));
			$rv = array($o->prop("prod_folders"));
			foreach($ot->ids() as $id)
			{
				$rv[] = $id;
			}
			return $rv;
		}
		$rv = array();
		$menu_arr = safe_array($o->prop("prod_folders"));
	
		foreach($menu_arr as $pf)
		{
			$rv[] = $pf;
			$ot = new object_tree(array(
				"parent" => $pf,
				"lang_id" => array(),
				"site_id" => array()
			));
			foreach($ot->ids() as $id)
			{
				$rv[] = $id;
			}
		}
		return $rv;
	}

	function _get_room_products($oid)
	{
		$o = obj($oid);
		$room_inst = get_instance(CL_ROOM);
		$rv = array();
		foreach(safe_array($o->prop("rooms")) as $pf)
		{
			$ot = $room_inst->get_active_items($pf);;
			foreach($ot->ids() as $id)
			{
				$rv[$id] = $id;
			}
		}
		return $rv;
	}

        function _get_default_prod_parents()
        {
                $def_wb = aw_ini_get("spa_bookings_entry.default");
                if (!$this->can("view", $def_wb))
                {
                        return false;
                }
                $o = obj($def_wb);
                return $o->prop("products_folder_list");
        }

	/**
		@attrib name=add_pkt
		@param id required type=int acl=view
		@param r required 
		@param test optional
	**/
	function add_pkt($arr)
	{
		// list prods and let the user select one
                $tmp = obj($arr["id"]);
                if ($tmp->class_id() != CL_SPA_CUSTOMER_INTERFACE)
                {
                        $pp = $this->_get_default_prod_parents();
                }
                else
                {
                        $pp = $this->_get_prod_parents($arr["id"]);
                }

		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT_PACKAGING,
			"lang_id" => array(),
			"site_id" => array(),
			"parent" => $pp
		));
		$room_products = $this->_get_room_products($arr["id"]);
		$p = array();
		foreach($ol->arr() as $o)
		{
			if(!sizeof($room_products) || array_key_exists($o->id() , $room_products))
			{
				$p[$o->parent()][] = $o;
			}
		}

		$ol = new object_list(array(
			"class_id" => CL_CURRENCY,
			"lang_id" => array(),
			"site_id" => array()
		));
		$curs = $ol->arr();

		$pts = "";
		$this->read_template("add_pkt.tpl");
		lc_site_load("spa_customer_interface", &$this);
		uksort($p, array(&$this, "sort_menus"));
		foreach($p as $parent => $prods)
		{
			$po = obj($parent);
			$p_list = array();
			$p_str = "";
			foreach($prods as $pr)
			{
				$p_list[] = html::href(array(
					"url" => $this->mk_my_orb("add_prod_to_new_pkt", array("section" => aw_global_get("section"), "prod" => $pr->id(), "id" => $arr["id"], "r" => $arr["r"])),
					"caption" => $pr->name()
				));
				$pop_url = $this->mk_my_orb("prepare_select_new_pkt_time", array(
					"prod" => $pr->id(),
					"id" => $arr["id"],
					"r" => $arr["r"],
					"test" => $arr["test"],
				));
				$this->vars(array(
					"prod_id" => $pr->id(),
					"prod_name" => $pr->trans_get_val("name"),
					"prod_url" => $this->mk_my_orb("add_prod_to_new_pkt", array("section" => aw_global_get("section"), "prod" => $pr->id(), "id" => $arr["id"], "r" => $arr["r"])),
					"select_time_pop" => "aw_popup_scroll('$pop_url','bronner',640,480)"
				));
				$pp = $pr->meta("cur_prices");
				foreach($curs as $_id => $_nm)
				{
					$this->vars(array(
						"price_".$_id => $pp[$_id]
					));
				}
				$p_str .= $this->parse("PRODUCT");
			}
			$this->vars(array(
				"prods" => join(", ", $p_list),
				"parent" => $po->trans_get_val("name"),//$po->name(),
				"PRODUCT" => $p_str
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
		if($this->can("view" ,$arr["id"]))
		{
			$parent = obj($arr["id"]);
			if($parent->prop("start"))
			{
				$b->set_prop("start", $parent->prop("start"));
				$b->set_prop("end", $parent->prop("end"));
			}
		}
		
		$b->save();
		$this->last_bron = $b->id();

		$i = get_instance(CL_SPA_BOOKIGS_ENTRY);
		$i->fin_add_prod_to_bron(array(
			"bron" => $b->id(),
			"wb" => $arr["id"],
			"prod" => $arr["prod"],
			"_not_verified" => 1
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
			"class_id" => CL_SHOP_PRODUCT_PACKAGING,
			"lang_id" => array(),
			"site_id" => array(),
			"parent" => $this->_get_prod_parents($arr["id"])
		));
		$p = array();
		foreach($ol->arr() as $o)
		{
			$p[$o->parent()][] = $o;
		}

		$bo = obj($arr["id"]);
		$pts = "";
		$this->read_template("add_pkt.tpl");
		lc_site_load("spa_customer_interface", &$this);
		$sbe = get_instance(CL_SPA_BOOKIGS_ENTRY);
		foreach($p as $parent => $prods)
		{
			$po = obj($parent);
			$p_list = array();
			$p_str = "";
			foreach($prods as $pr)
			{
				// this makes sure no prods that can't be booked are displayed
				if (count(array_intersect(array_keys($sbe->get_rooms_for_product($pr->id())), safe_array($bo->prop("rooms")))) == 0)
				{
					continue;
				}
				$p_list[] = html::href(array(
					"url" => $this->mk_my_orb("fin_add_prod_to_bron", array(
						"prod" => $pr->id(), 
						"id" => $arr["id"], 
						"r" => $arr["r"],
						"bron" => $arr["bron"]
					)),
					"caption" => $pr->name()
				));
				$prod_url = $this->mk_my_orb("fin_add_prod_to_bron", array(
					"prod" => $pr->id(), 
					"id" => $arr["id"], 
					"r" => $arr["r"],
					"bron" => $arr["bron"]
				));
				$this->vars(array(
					"prod_id" => $pr->id(),
					"prod_name" => $pr->trans_get_val("name"),
					"prod_url" => $prod_url,
					"select_time_pop" => "aw_popup_scroll('$prod_url','bronner',640,480)"
				));
				$pp = $pr->meta("cur_prices");
				foreach($pp as $_id => $_nm)
				{
					$this->vars(array(
						"price_".$_id => $pp[$_id]
					));
				}
				$p_str .= $this->parse("PRODUCT");
			}
			$this->vars(array(
				"prods" => join(", ", $p_list),
				"PRODUCT" => $p_str,
				"parent" => $po->trans_get_val("name")
			));
			$pts .= $this->parse("PARENT");
		}

		$this->vars(array(
			"PARENT" => $pts
		));

		return "<!---->".$this->parse();
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
		$arr["_not_verified"] = 1;
		$i->fin_add_prod_to_bron($arr);
		$ei = get_instance(CL_SPA_BOOKIGS_ENTRY);
		$ct = obj($arr["id"]);
		$rooms = $ct->prop("rooms");
		return $ei->mk_my_orb("select_room_booking", array(
			"booking" => $arr["bron"], 
			"prod" => $arr["prod"], 
			"prod_num" => 0, 
			"section" => "3169", 
			"_not_verified" => 1, 
			"rooms" => $rooms,
			"retf" => $arr["r"]
		));
	}

	function get_reservations_sum($o)
	{
		$total_sum = 0;
		if(!is_oid($o) || !$this->can("view" , $o))
		{
			return $total_sum;
		}
		$o = obj($o);
		$room_res_inst = get_instance(CL_ROOM_RESERVATION);
		foreach($o->connections_from(array("type" => "RELTYPE_ROOM_BRON")) as $c)
		{
			$b = $c->to();
			$sum = $room_res_inst->get_total_bron_price(array(
				"bron" => $b,
			));
			foreach($sum as $curr => $val)
			{
				$c = obj($curr);
				if($c->name() == "EEK")
				{
					$sum = $val;
				}
			}
			$total_sum+= $sum;
		}
		return $total_sum;
	}

	//sv - calculates also verified reservations / already paid
	function get_extra_prods_sum($os,$sv)
	{
                $room_res_inst = get_instance(CL_ROOM_RESERVATION);
                $total_sum = 0;
                if(!is_array($os))
                {
                	$os = array($os);
                }
                foreach($os as $o)
                {
			if(!is_oid($o) || !$this->can("view" , $o))
			{
				return $total_sum;
			}
			$o = obj($o);
			foreach(safe_array($o->meta("extra_prods")) as $extra_item_entry)
			{
				if(!$this->can("view" , $extra_item_entry["reservation"]))
				{
					continue;
				}
				$b = obj($extra_item_entry["reservation"]);
				if ($b->prop("start1") < 100)
				{
					continue;
				}
				
				if (!$sv && $b->prop("verified"))
				{
					continue;
				}
				
				$sum = $room_res_inst->get_total_bron_price(array(
					"bron" => $b,
				));
				foreach($sum as $curr => $val)
				{
					$c = obj($curr);
					if($c->name() == "EEK")
					{
						$sum = $val;
					}
				}
				if(is_array($sum))
				{
					$sum = 0;
				}
				$total_sum+= $sum;
			}
		}
		return $total_sum;
	}

	/**
		@attrib name=pay
		@param id required acl=view
		@param r optional
		@param bank_payment required typoe=oid
		@param section optional
	**/
	function pay($arr)
	{
		extract($arr);
		if(is_array($id))
		{
			$id = reset($id);
			$o = obj($id);
			$o->set_meta("all_brons" , $arr["id"]);
		}
		else
		{
			$o = obj($arr["id"]);
		}
		
		//kui vahepeal miskit kinni pandud juba, siis parem oleks tagasi saata
		$room_inst = get_instance(CL_ROOM);
		unset($_SESSION["reservation"]["error"]);
		foreach($o->connections_from(array("type" => "RELTYPE_ROOM_BRON")) as $c)
		{
			$b = $c->to();
			if(!($b->prop("start1") > 1) && !($b->prop("end") > 1))
			{
				continue;
			}
			if(!$room_inst->check_if_available(array(
				"room" => $b->prop("resource"),
				"start" => $b->prop("start1"),
				"end" => $b->prop("end"),
				"ignore_booking" => $b->id(),
			)))
			{
				$_SESSION["reservation"]["error"].=  sprintf(t("%s - Sorry, but Your payment was made too late and another person has already booked this time!"), $b->name())."<br>\n";
				if($r) return $r;
			}
		}

		$l = get_instance("languages");
		$lang_id = $l->get_langid($_SESSION["ct_lang_id"]);
		
		$bank_inst = get_instance(CL_BANK_PAYMENT);
		if(is_oid($bank_payment))
		{
			$payment = obj($bank_payment);
			$asd = $bank_inst->bank_forms(array(
				"id" => $bank_payment,
				"amount" =>  $this->get_extra_prods_sum($arr["id"]),
				"reference_nr" => $o->id(),
				"lang" => $lang_id,
			));
		}
		$o->set_meta("ru" , $r);
		$o->save();
		return $asd;
	}

	/**
		@attrib name=confirm_booking
		@param id required type=int acl=view
		@param r optional
	**/
	function confirm_booking($arr)
	{
		$o = obj($arr["id"]);
		foreach($o->connections_from(array("type" => "RELTYPE_ROOM_BRON")) as $c)
		{
			$b = $c->to();
			$b->set_prop("verified", 1);
			$b->save();
		}
		return $arr["r"];
	}

	/**
		@attrib name=bank_return is_public=1 all_args=1
		@param id required type=int acl=view
	**/
	function bank_return($arr)
	{
		$bank_inst = get_instance(CL_BANK_PAYMENT);
		$o = obj($arr["id"]);
		if(is_array($o->meta("all_brons")))
		{
			$brons = $o->meta("all_brons");
		}
		else
		{
			$brons = array($arr["id"]);
		}
		$room_inst = get_instance(CL_ROOM);
		foreach($brons as $bron_id)
		{
			$bron = obj($bron_id);
			foreach($bron->connections_from(array("type" => "RELTYPE_ROOM_BRON")) as $c)
			{
				$b = $c->to();
				if ($b->prop("start1") < 100)
				{
					continue;
				}
				$b->set_prop("verified", 1);
				if(!$b->meta("mail_sent"))//topelt mailide v�ltimiseks
				{
					$room_res_inst = get_instance(CL_ROOM_RESERVATION);
					if($b->meta("tpl"))
					{
						$tpl = $b->meta("tpl");
					}
					$room_res_inst->send_affirmation_mail($b->id(),$tpl);
					aw_disable_acl();
					$b->set_meta("mail_sent",1);
					aw_restore_acl();
				}

				$b->set_meta("payment_info" , $bank_inst->get_payment_info());
				aw_disable_acl();
				$b->save();
				aw_restore_acl();
				
				//juhuks kui m�ni enne maksmist magama j��nud, kuid siiski seda m�ne tunni p�rast teha kavatseb
				if(!$room_inst->check_if_available(array(
					"room" => $b->prop("resource"),
					"start" => $b->prop("start1"),
					"end" => $b->prop("end"),
					"ignore_booking" => $b->id(),
				)))
				{
					$_SESSION["reservation"]["error"].=  sprintf(t("%s - Sorry, but Your payment was made too late and another person has already booked this time!"), $b->name())."<br>\n";
				}
			}
		}
		
		if($o->meta("ru"))
		{
			header("Location:".$o->meta("ru"));
			die();
			return $o->meta("ru");
		}
		return $arr["r"];
	}

	/**
		@attrib name=print_booking
		@param id required
		@param wb required
	**/
	function print_booking($arr)
	{
		$b = obj($arr["id"]);
		$wb = obj($arr["wb"]);
		$this->read_site_template("booking.tpl");
		lc_site_load("spa_bookigs_entry", &$this);

		$ei = get_instance(CL_SPA_BOOKIGS_ENTRY);

		// now, list all bookings for rooms 
		$dates = $ei->get_booking_data_from_booking($b);
		$books = "";
		$items = array();
		foreach($dates as $prod => $entries)
		{
			foreach($entries as $entry)
			{
				$items[] = $entry;
			}
		}

		$all_items = "";
		$packet_services = "";
		$additional_services = "";

		usort($items, create_function('$a,$b', 'return $a["from"] - $b["from"];'));


		$from = time() + 24*3600*1000;
		$to = 0;
		foreach($items as $entry)
		{
			if ($entry["from"] < 1)
			{
				continue;
			}
			$from = min($from, $entry["from"]);
			$to = max($to, $entry["to"]);
		}

		list($y, $m, $d) = explode("-", $b->prop("person.birthday"));
		$this->vars(array(
			"bureau" => $b->createdby(),
			"person" => $b->trans_get_val_str("person"),
			"package" => $b->trans_get_val_str("package"),
			"from" => date("d.m.Y", $from),
			"to" => date("d.m.Y", $to),
			"person_comment" => $b->prop("person.comment"),
			"person_name" => $b->prop("person.name"),
			"person_birthday" => $y > 0 ? sprintf("%02d.%02d.%04d", $d, $m, $y) : "",
			"person_ext_id" => $b->prop("person.ext_id_alphanumeric"),
			"person_gender" => $b->prop("person.gender") == 1 ? t("Mees") : ($b->prop("person.gender") === "2" ? t("Naine") : "")
		));

		foreach($items as $entry)
		{
			if ($entry["from"] < 1)
			{
				continue;
			}
			$ro = obj($entry["room"]);
			$rvs = obj($entry["reservation_id"]);
			$prod_obj = obj($rvs->meta("product_for_bron"));
			$this->vars(array(
				"r_from" => date("d.m.Y H:i", $entry["from"]),
				"r_to" =>  date("d.m.Y H:i", $entry["to"]),
				"r_room" => $ro->trans_get_val("name"),
				"r_prod" => $prod_obj->trans_get_val("name"),
				"start_time" => $entry["from"],
				"end_time" => $entry["to"],
				"price" => $prod_obj->prop("price")
			));
			$books .= $this->parse("BOOKING");

			$all_items .= $this->parse("ALL_ITEMS");
			if ($entry["is_extra"] == 1)
			{
				$additional_services .= $this->parse("ADDITIONAL_SERVICES");
			}
			else
			{
				$packet_services .= $this->parse("PACKET_SERVICES");
			}
		}


		$this->vars(array(
			"BOOKING" => $books,
			"ADDITIONAL_SERVICES" => $additional_services,
			"PACKET_SERVICES" => $packet_services,
			"ALL_ITEMS" => $all_items
		));
		$this->vars(array(
			"HAS_PACKET_SERVICES" => $packet_services != "" ? $this->parse("HAS_PACKET_SERVICES") : "",
			"HAS_ADDITIONAL_SERVICES" => $packet_services != "" ? $this->parse("HAS_ADDITIONAL_SERVICES") : "",
		));

		if ($this->can("view", $wb->prop("print_view_ctr")))
		{
			$fc = get_instance(CL_FORM_CONTROLLER);
			$fc->eval_controller($wb->prop("print_view_ctr"), $arr);
		}
		die($this->parse());
	}

	/**
		@attrib name=prepare_select_new_pkt_time
		@param prod required type=int acl=view
		@param id required type=int acl=view
		@param r optional
	**/
	function prepare_select_new_pkt_time($arr)
	{
		$this->add_prod_to_new_pkt($arr);
		$ei = get_instance(CL_SPA_BOOKIGS_ENTRY);
		$ct = obj($arr["id"]);
		$rooms = $ct->prop("rooms");
		return $ei->mk_my_orb("select_room_booking", array(
			"booking" => $this->last_bron, 
			"prod" => $arr["prod"], 
			"prod_num" => 0, 
			"section" => "3169", 
			"_not_verified" => 1, 
			"rooms" => $rooms,
			"retf" => $arr["r"]
		));
	}

	function get_image_instance()
	{
		if(!$this->image_instance)
		{
			$this->image_instance = get_instance(CL_IMAGE);
		}
		return $this->image_instance;
	}

	function _add_prod_vars($id)
	{
		
		$i = $this->get_image_instance();
		if(!$this->can("view" , $id))
		{
			return;
		}
		$o = obj($id);
		$prod = reset($o->connections_to(array("from.class_id" => CL_SHOP_PRODUCT, "type" => "RELTYPE_PACKAGING")));
		if(!is_object($prod))
		{
			return;
		}
		$prod = $prod->from();
		foreach($prod->get_property_list() as $k => $v)
		{
			$this->vars(array("product_".$k => $prod->trans_get_val($k)));
		}
		$cnt = 0;
		$imgc = $prod->connections_from(array("type" => "RELTYPE_IMAGE"));
		usort($imgc, create_function('$a,$b', 'return $a->prop("to.jrk") - $b->prop("to.jrk");'));
		foreach($imgc as $c)
		{
			$u = $i->get_url_by_id($c->prop("to"));
			$pid = $c->prop("to");
			$image_obj = $c->to();
			$this->vars_safe(array(
				"image".$cnt => image::make_img_tag_wl($image_obj->id()),
				"image_br".$cnt => "<br><br>".image::make_img_tag($u, $c->prop("to.name")),
				"image".$cnt."_comment" => "<br>".$image_obj->prop('comment'),
				//"name" => $prod->name(),
				"image".$cnt."_url" => $u,
				"image".$cnt."_onclick" => image::get_on_click_js($c->prop("to")),
				"packaging_image".$cnt => "",
				"packaging_image".$cnt."_url" => ""
			
			));
	
			if ($image_obj->prop("file2") != "")
			{
				$this->vars_safe(array(
					"IMAGE".$cnt."_HAS_BIG" => $this->parse("IMAGE".$cnt."_HAS_BIG")
				));
			}
			$this->vars_safe(array(
				"HAS_IMAGE".$cnt => $this->parse("HAS_IMAGE".$cnt)
			));
			$cnt++;
		}
	}

	/**
		@attrib name=show_prod_info 
		@param prod required
	**/
	function show_prod_info($arr)
	{
		$this->read_template("show_prod_info.tpl");
		$po = obj($arr["prod"]);
		foreach($po->get_property_list() as $k => $v)
		{
			$this->vars(array($k => $po->trans_get_val($k)));
		}
		if($po->class_id() == CL_SHOP_PRODUCT_PACKAGING)
		{
			$this->_add_prod_vars($po->id());
		}
		$i = $this->get_image_instance();
		$cnt = 1;
		$imgc = $po->connections_from(array("type" => "RELTYPE_IMAGE"));
		usort($imgc, create_function('$a,$b', 'return $a->prop("to.jrk") - $b->prop("to.jrk");'));
		foreach($imgc as $c)
		{
			$u = $i->get_url_by_id($c->prop("to"));

			$pid = $c->prop("to");
			$image_obj = $c->to();
			$this->vars_safe(array(
				"image".$cnt => image::make_img_tag_wl($image_obj->id()),
				"image_br".$cnt => "<br><br>".image::make_img_tag($u, $c->prop("to.name")),
				"image".$cnt."_comment" => "<br>".$image_obj->prop('comment'),
				//"name" => $prod->name(),
				"image".$cnt."_url" => $u,
				"image".$cnt."_onclick" => image::get_on_click_js($c->prop("to")),
				"packaging_image".$cnt => "",
				"packaging_image".$cnt."_url" => ""
			
			));
	
			if ($image_obj->prop("file2") != "")
			{
				$this->vars_safe(array(
					"IMAGE".$cnt."_HAS_BIG" => $this->parse("IMAGE".$cnt."_HAS_BIG")
				));
			}
			$this->vars_safe(array(
				"HAS_IMAGE".$cnt => $this->parse("HAS_IMAGE".$cnt)
			));
			$cnt++;
		}
		
		return $this->parse();
	}
}
?>
