<?php

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

define(ORD_FILLED,1);

class shop extends aw_template
{
	function shop()
	{
		$this->tpl_init("shop");
		$this->sub_merge = 1;
		$this->db_init();
	}

	////
	// !generates the form for adding shops
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,"Lisa pood");
		$this->read_template("add.tpl");

		classload("objects");
		$ob = new db_objects;

		classload("form_base");
		$fb = new form_base;

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"root" => $this->picker($parent,$ob->get_list()),
			"of" => $this->picker(0,$fb->get_list(FORM_ENTRY))
		));
		return $this->parse();
	}

	////
	// !generates the form for changing shop $id
	function change($arr)
	{
		extract($arr);
		$oba = $this->get($id);
		$this->mk_path($oba["parent"], "Muuda poodi");
		$this->read_template("add.tpl");

		classload("objects");
		$ob = new db_objects;

		classload("form_base");
		$fb = new form_base;

		$this->vars(array(
			"name" => $oba["name"],
			"comment" => $oba["comment"],
			"root" => $this->picker($oba["root_menu"],$ob->get_list()),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"of" => $this->picker($oba["order_form"],$fb->get_list(FORM_ENTRY)),
			"stat_by_turnover" => $this->mk_orb("turnover_stat", array("id" => $id)),
			"orders" => $this->mk_orb("admin_orders", array("id" => $id)),
			"emails" => $oba["emails"]
		));
		$this->parse("CHANGE");
		return $this->parse();
	}

	////
	// !saves the data for the shop
	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
			$this->db_query("UPDATE shop SET root_menu = $root, order_form = $order_form,emails='$emails' WHERE id = $id");
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_SHOP, "status" => 1, "name" => $name, "comment" => $comment));
			$this->db_query("INSERT INTO shop(id,root_menu,order_form,emails) VALUES($id,'$root','$order_form','$emails')");
		}

		return $this->mk_orb("change", array("id" => $id));
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
		// some of the shop categories
		$this->db_query("SELECT objects.name,objects.oid FROM objects WHERE parent = $parent AND class_id = ".CL_PSEUDO." AND status = 2 ");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"name" => $row["name"], 
				"id" => $row["oid"],
				"cat_link" => $this->mk_my_orb("show", array("id" => $shop, "parent" => $row["oid"]))
			));
			$this->parse("CAT");
		}

		// make yah link
		$p = $parent;
		$op = $this->get_object($p);
		while ($p != $s["root_menu"] && $p)
		{
			$this->vars(array(
				"id" => $op["oid"],
				"name" => $op["name"],
				"yah_link" => $this->mk_my_orb("show", array("id" => $shop, "parent" => $op["oid"]))
			));
			$y = $this->parse("YAH").$y;
			$p = $op["parent"];
			$op = $this->get_object($p);
		}

		$this->vars(array(
			"YAH" => $y, 
			"fp" => $s["root_menu"],
			"s_name" => $s["name"],
			"section" => $parent,
			"location" => $this->mk_my_orb("show", array("id" => $shop, "parent" => $s["root_menu"]))
		));
		return $parent;
	}

	////
	// !shows the shop $id on the suer side, items under category $parent
	function show($arr)
	{
		extract($arr);
		$this->read_template("show_shop.tpl");

		global $shopping_cart;
		$parent = $this->do_shop_menus($id,$parent);

		classload("form");

		$this->db_query("SELECT objects.brother_of as oid,shop_items.* FROM objects LEFT JOIN shop_items ON shop_items.id = objects.brother_of WHERE parent = $parent AND class_id = ".CL_SHOP_ITEM." AND status = 2");
		while ($row = $this->db_next())
		{
			$f = new form;
			$this->vars(array(
				"item" => $f->show(array("id" => $row["form_id"], "entry_id" => $row["entry_id"],"op_id" => $row["op_id"])),
				"item_id" => $row["oid"],
				"price" => $row["price"],
				"it_cnt"	=> $shopping_cart["items"][$row["oid"]]["cnt"],
				"order_item" => $this->mk_my_orb("order_item", array("item_id" => $row["oid"], "shop" => $id, "section" => $parent))
			));
			$tp+=(double)$shopping_cart["items"][$row["oid"]]["cnt"]*(double)$row["price"];	// selle arvutame p2rast kogusummast maha
																																			// et saada korvi hind = baashind + selle lehe asjade hind
			$this->parse("ITEM");
		}

		$this->vars(array(
			"tot_price" => (double)$shopping_cart["price"]-(double)$tp,	
			"reforb" => $this->mk_reforb("add_cart", array("shop_id" => $id, "section" => $parent)),
			"cart" => $this->mk_site_orb(array("action" => "view_cart", "shop_id" => $id, "section" => $parent))
		));
		return $this->parse();
	}

	////
	// !adds items to user's shopping cart
	function add_to_cart($arr)
	{
		extract($arr);
		$this->upd_cart_from_arr($sh_it);
		return $GLOBALS["baseurl"]."/shop.".$GLOBALS["ext"]."/shop=$shop_id/section=$section";
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
		$images = new db_images;

		$items = false;
		if (is_array($shopping_cart["items"]))
		{
			reset($shopping_cart["items"]);
			while (list($item_id,$ar) = each($shopping_cart["items"]))
			{
				if ($ar["cnt"] > 0)
				{
					// now here we must show the name of the item followed by
					// the rows from the cnt_form for that item that have selectrow checked

					// so, get the item
					$it = $this->get_item($item_id);
					// load it's cnt_form
					$f->load($it["cnt_form"]);
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
						$this->vars(array("row" => $f->mk_row_html($rownum,$images,"entry_".$ar["cnt_entry"]."_")));
						$rowhtml.=$this->parse("F_ROW");
					}

					$this->vars(array(
						"item_link" => $this->mk_my_orb("order_item", array("item_id" => $item_id, "shop" => $shop_id, "section" => $section)),
						"name" => $ar["name"],
						"F_ROW" => $rowhtml
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
			"order"	=> $this->mk_site_orb(array("action" => "order", "shop_id" => $shop_id, "section" => $section)),
			"order_hist" => $this->mk_my_orb("order_history", array("id" => $shop_id)),
			"to_shop" => $this->mk_my_orb("show", array("id" => $shop_id, "parent" => $section))
		));
		if ($items)
		{
			$this->parse("CAN_ORDER");
		}
		return $this->parse();
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

		global $shopping_cart;
		if (is_array($shopping_cart["items"]))
		{
			reset($shopping_cart["items"]);
			while (list($item_id,$ar) = each($shopping_cart["items"]))
			{
				if ($ar["cnt"] > 0)
				{
					$it = $this->get_item($item_id);

					// process the entry
					$f->process_entry(array(
						"id" => $it["cnt_form"],
						"entry_id" => $ar["cnt_entry"],
						"prefix" => "entry_".$ar["cnt_entry"]."_"
					));

					// also update the count in the cart
					$selrows = $this->get_selected_rows_in_form(&$f);
					
					$count = 0;
					foreach($selrows as $i)
					{
						$rowels = $f->get_elements_for_row($i);
						// this row's count must be added to this item's count in the shopping cart
						$count+=$rowels["mitu"];
					}
					$GLOBALS["shopping_cart"]["items"][$item_id]["cnt"] = $count;
					$GLOBALS["shopping_cart"]["items"][$item_id]["name"] = $it["name"];
					$GLOBALS["shopping_cart"]["items"][$item_id]["cnt_entry"] = $ar["cnt_entry"];
				}
			}
		}
		return $this->mk_site_orb(array("action" => "view_cart", "shop_id" => $shop_id, "section" => $section));
	}

	function upd_cart_from_arr($sh_it)
	{
		if (is_array($sh_it))	// shop_items 
		{
			reset($sh_it);
			while (list($item_id,$count) = each($sh_it))
			{
				$this->db_query("SELECT price,name FROM shop_items LEFT JOIN objects ON objects.oid = shop_items.id WHERE id = $item_id");
				$r = $this->db_next();
				$GLOBALS["shopping_cart"]["items"][$item_id]["cnt"] = max($count,0);
				$GLOBALS["shopping_cart"]["items"][$item_id]["price"] = $r["price"];
				$GLOBALS["shopping_cart"]["items"][$item_id]["name"] = $r["name"];
			}
		}											// ;)

		$price = 0.0;
		global $shopping_cart;
		if (is_array($shopping_cart["items"]))
		{
			reset($shopping_cart["items"]);
			while (list($item_id,$ar) = each($shopping_cart["items"]))
			{
				$price+=(double)$ar["cnt"] * (double)$ar["price"];
			}
			$GLOBALS["shopping_cart"]["price"] = $price;
		}
		else
		{
			$GLOBALS["shopping_cart"] = array();
		}
	}

	function get($id)
	{
		$this->db_query("SELECT shop.*,objects.* FROM objects LEFT JOIN shop ON shop.id = objects.oid WHERE objects.oid = $id");
		return $this->db_next();
	}

	////
	// !shows the ordering form that the user must fill to order stuff
	function order($arr)
	{
		extract($arr);
		$this->read_template("order.tpl");

		$sh = $this->get($shop_id);

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
		$this->vars(array(
			"form" => $f->gen_preview(array(
									"elvalues" => $elvals,
									"id" => $sh["order_form"], 
									"reforb" => $this->mk_reforb("submit_order", array("shop_id" => $shop_id, "section" => $section))
								)),
			"shop_id" => $shop_id,
			"section" => $section,
			"cart" => $this->mk_site_orb(array("action" => "view_cart", "shop_id" => $shop_id, "section" => $section)),
			"to_shop" => $this->mk_my_orb("show", array("id" => $shop_id, "parent" => $section))
		));
		return $this->parse();
	}

	////
	// !submits the order for the customer.
	function submit_order($arr)
	{
		extract($arr);
		$sh = $this->get($shop_id);

		// save the user info
		classload("form");
		$f = new form;
		$url = $f->process_entry(array("id" => $sh["order_form"]));
		$eid = $f->entry_id;

		global $uid;
		
		// ok. a prize for anybody (except terryf and duke) who figures out why the next line is like it is :)
		$day = mktime(8,42,17,date("n"),date("d"),date("Y"));
		$wd = date("w");
		$hr = date("G");
		$month = mktime(8,42,17,date("n"),1,date("Y"));

		// and also log the order
		$ord_id = $this->db_fetch_field("SELECT MAX(id) AS id FROM orders","id")+1;
		$this->db_query("INSERT INTO orders(id,entry_id,tm,user,ip,shop_id,day,month,wd,hr) VALUES($ord_id,$eid,".time().",'$uid','".get_ip()."','$shop_id','$day','$month','$wd','$hr')");

		// now we must also send an email to somebody notifying them of the new order.
		// the email must contain all the info about the purchase, including all the
		// items and their counts and also the order form data.
		$mail = "Tere!\n\nStatisikameti poest ".$sh["name"]." tellis kasutaja $uid (ip aadress: ".get_ip().") kell ".$this->time2date(time(),2)." järgmised kaubad: \n\n";

		classload("form");
		$f = new form;

		global $shopping_cart;
		if (is_array($shopping_cart["items"]))
		{
			reset($shopping_cart["items"]);
			while (list($item_id,$ar) = each($shopping_cart["items"]))
			{
				if ($ar["cnt"] > 0)
				{
					// so, get the item
					$it = $this->get_item($item_id);
					// load it's cnt_form
					$f->load($it["cnt_form"]);
					// now find all the rows that are selected in the entry from the shopping cart
					$f->load_entry($ar["cnt_entry"]);
					$selrows = $this->get_selected_rows_in_form(&$f);

					$mail.="Nimi: ".$it["name"]."\n";
					$mail.="Kogus ja tüüp: \n";
					$rowhtml = "";
					foreach($selrows as $rownum)
					{
						// here we must add the elements of row $rownum to the email we are assembling of the selected rows
						$mail.=$f->mk_show_text_row($rownum)."\n";
					}
					$mail.="\n";
					$this->db_query("INSERT INTO order2item(order_id,item_id,count,price,cnt_entry) VALUES($ord_id,$item_id,'".$ar["cnt"]."','".$ar["price"]."','".$ar["cnt_entry"]."')");
					$t_p += (double)$ar["cnt"] * (double)$ar["price"];
				}
			}
		}

		$this->db_query("UPDATE orders SET t_price = '$t_p' WHERE id = $ord_id");
		
		$mail.="\n\nTellija sisestas enda kohta järgmised andmed:\n\n";
		$f->load($sh["order_form"]);
		$f->load_entry($eid);
		$mail.=$f->show_text();

		$emails = explode(",",$sh["emails"]);
		foreach($emails as $email)
		{
			mail($email,"Tellimus Poest", $mail,"From: automatweb@automatweb.com\n");
		}

		// zero out the customers shopping cart as well.
		$GLOBALS["shopping_cart"] = array();
		return $url;
	}
	
	////
	// !shows the general turnover stats for shop $id and lets the user pick a more specific stat
	function turnover_stat($arr)
	{
		extract($arr);
		$this->read_template("turnover_stat.tpl");
		$sh = $this->get($id);
		$this->mk_path($sh["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda poodi</a> / Statistika");

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
	// !shows turnover stats for shop $id based on $stat_type, orders from $from until $to
	function do_stat($arr)
	{
		extract($arr);

		$from = mktime($from["hour"],$from["minute"],0,$from["month"],$from["day"],$from["year"]);
		$to = mktime($to["hour"],$to["minute"],0,$to["month"],$to["day"],$to["year"]);
		
		switch($stat_type)
		{
			case "by_day":
				return $this->to_stat_by_day($id,$from,$to);

			case "by_month":
				return $this->to_stat_by_month($id,$from,$to);

			case "by_wd":
				return $this->to_stat_by_wd($id,$from,$to);

			case "by_hr":
				return $this->to_stat_by_hr($id,$from,$to);
		}
	}

	////
	// !turnover stats by day, for shop $id from $from to $to
	function to_stat_by_day($id,$from,$to)
	{
		$this->read_template("to_stat_by_day.tpl");
		$sh = $this->get($id);
		$this->mk_path($sh["parent"],"<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda poodi</a> / <a href='".$this->mk_orb("turnover_stat", array("id" => $id))."'>Statistika</a> / P&auml;evade kaupa");

		$this->db_query("SELECT COUNT(*) AS cnt, SUM(t_price) AS sum, AVG(t_price) AS avg,day FROM orders WHERE tm >= $from AND tm <= $to AND shop_id = $id GROUP BY day");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"day" => $this->time2date($row["day"],3),
				"sum" => $row["sum"],
				"avg" => $row["avg"],
				"cnt" => $row["cnt"]
			));
			$this->parse("DAY");
			$days[] = $this->time2date($row["day"],3);
			$m_sum = max($m_sum,$row["sum"]);
			$sum[] = $row["sum"];
			$avg[] = $row["avg"];
			$cnt[] = $row["cnt"];
			$t_sum += $row["sum"];
			$t_avg = $row["avg"];
			$t_cnt += $row["cnt"];
		}
		$this->vars(array(
			"name" => $sh["name"],
			"from" => $this->time2date($from,3),
			"to" => $this->time2date($to,3),
			"chart" => $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$days)),
																									 "yvals" => urlencode(join(",",array(0,$m_sum))),
																									 "data"  => urlencode(join(",",$sum)),
																									 "data2"  => urlencode(join(",",$avg)),
																									 "data3"  => urlencode(join(",",$cnt)),
																									 "title" => "Käive päevade kaupa",
																									 "xtitle" => "Päev",
																									 "ytitle" => "Käive"),"banner"),
			"t_sum" => $t_sum,
			"t_avg" => $t_avg,
			"t_cnt" => $t_cnt
		));
		return $this->parse();
	}

	////
	// !turnover stats by month, for shop $id from $from to $to
	function to_stat_by_month($id,$from,$to)
	{
		$this->read_template("to_stat_by_month.tpl");
		$sh = $this->get($id);
		$this->mk_path($sh["parent"],"<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda poodi</a> / <a href='".$this->mk_orb("turnover_stat", array("id" => $id))."'>Statistika</a> / Kuude kaupa");

		$this->db_query("SELECT COUNT(*) AS cnt, SUM(t_price) AS sum, AVG(t_price) AS avg,month FROM orders WHERE tm >= $from AND tm <= $to AND shop_id = $id GROUP BY month");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"mon" => $this->time2date($row["month"],7),
				"sum" => $row["sum"],
				"avg" => $row["avg"],
				"cnt" => $row["cnt"]
			));
			$this->parse("MONTH");
			$days[] = $this->time2date($row["month"],7);
			$m_sum = max($m_sum,$row["sum"]);
			$sum[] = $row["sum"];
			$avg[] = $row["avg"];
			$cnt[] = $row["cnt"];
			$t_sum += $row["sum"];
			$t_avg = $row["avg"];
			$t_cnt += $row["cnt"];
		}
		$this->vars(array(
			"name" => $sh["name"],
			"from" => $this->time2date($from,3),
			"to" => $this->time2date($to,3),
			"chart" => $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$days)),
																									 "yvals" => urlencode(join(",",array(0,$m_sum))),
																									 "data"  => urlencode(join(",",$sum)),
																									 "data2"  => urlencode(join(",",$avg)),
																									 "data3"  => urlencode(join(",",$cnt)),
																									 "title" => "Käive kuude kaupa",
																									 "xtitle" => "Kuu",
																									 "ytitle" => "Käive"),"banner"),
			"t_sum" => $t_sum,
			"t_avg" => $t_avg,
			"t_cnt" => $t_cnt
		));
		return $this->parse();
	}

	////
	// !turnover stats by weekday, for shop $id from $from to $to
	function to_stat_by_wd($id,$from,$to)
	{
		$this->read_template("to_stat_by_wd.tpl");
		$sh = $this->get($id);
		$this->mk_path($sh["parent"],"<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda poodi</a> / <a href='".$this->mk_orb("turnover_stat", array("id" => $id))."'>Statistika</a> / N&auml;dalap&auml;evade kaupa");

		$days = array(0 => LC_SUNDAY, 1 => LC_MONDAY, 2 => LC_TUESDAY, 3 => LC_WEDNESDAY, 4 => LC_THURSDAY, 5 => LC_FRIDAY, 6 => LC_SATURDAY);

		$this->db_query("SELECT COUNT(*) AS cnt, SUM(t_price) AS sum, AVG(t_price) AS avg,wd FROM orders WHERE tm >= $from AND tm <= $to AND shop_id = $id GROUP BY wd");
		while ($row = $this->db_next())
		{
			$m_sum = max($m_sum,$row["sum"]);
			$sum[$row["wd"]] = $row["sum"];
			$avg[$row["wd"]] = $row["avg"];
			$cnt[$row["wd"]] = $row["cnt"];
			$t_sum += $row["sum"];
			$t_avg = $row["avg"];
			$t_cnt += $row["cnt"];
		}

		for ($i=0; $i < 7; $i++)
		{
			$this->vars(array(
				"wd" => $days[$i],
				"sum" => $sum[$i],
				"avg" => $avg[$i],
				"cnt" => $cnt[$i]
			));
			$this->parse("WD");
		}

		$this->vars(array(
			"name" => $sh["name"],
			"from" => $this->time2date($from,3),
			"to" => $this->time2date($to,3),
			"chart" => $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",array("Puhapaev","Esmaspaev","Teisipaev","Kolmapaev","Neljapaev","Reede","Laupaev"))),
																									 "yvals" => urlencode(join(",",array(0,$m_sum))),
																									 "data"  => urlencode(join(",",map("%s",$sum))),
																									 "data2"  => urlencode(join(",",map("%s",$avg))),
																									 "data3"  => urlencode(join(",",map("%s",$cnt))),
																									 "title" => "Käive nädalapäevade kaupa",
																									 "xtitle" => "Päev",
																									 "ytitle" => "Käive"),"banner"),
			"t_sum" => $t_sum,
			"t_avg" => $t_avg,
			"t_cnt" => $t_cnt
		));
		return $this->parse();
	}

	////
	// !turnover stats by hr, for shop $id from $from to $to
	function to_stat_by_hr($id,$from,$to)
	{
		$this->read_template("to_stat_by_hr.tpl");
		$sh = $this->get($id);
		$this->mk_path($sh["parent"],"<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda poodi</a> / <a href='".$this->mk_orb("turnover_stat", array("id" => $id))."'>Statistika</a> / Tundide kaupa");

		$this->db_query("SELECT COUNT(*) AS cnt, SUM(t_price) AS sum, AVG(t_price) AS avg,hr FROM orders WHERE tm >= $from AND tm <= $to AND shop_id = $id GROUP BY hr");
		while ($row = $this->db_next())
		{
			$m_sum = max($m_sum,$row["sum"]);
			$sum[$row["hr"]] = $row["sum"];
			$avg[$row["hr"]] = $row["avg"];
			$cnt[$row["hr"]] = $row["cnt"];
			$t_sum += $row["sum"];
			$t_avg = $row["avg"];
			$t_cnt += $row["cnt"];
		}

		for ($i=0; $i < 24; $i++)
		{
			$this->vars(array(
				"hr" => $i,
				"sum" => $sum[$i],
				"avg" => $avg[$i],
				"cnt" => $cnt[$i]
			));
			$this->parse("HR");
			$hrs[] = $i;
		}

		$this->vars(array(
			"name" => $sh["name"],
			"from" => $this->time2date($from,3),
			"to" => $this->time2date($to,3),
			"chart" => $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$hrs)),
																									 "yvals" => urlencode(join(",",array(0,$m_sum))),
																									 "data"  => urlencode(join(",",map("%s",$sum))),
																									 "data2"  => urlencode(join(",",map("%s",$avg))),
																									 "data3"  => urlencode(join(",",map("%s",$cnt))),
																									 "title" => "Käive tundide kaupa",
																									 "xtitle" => "Tund",
																									 "ytitle" => "Käive"),"banner"),
			"t_sum" => $t_sum,
			"t_avg" => $t_avg,
			"t_cnt" => $t_cnt
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
			// now here's the possibility that $shop was omitted. therefore we must figure it out ourself
			// we do that by loading all the root folders for all the shops
			// and then traversing the object tree from the current point upwards until we hit a shop root folder.
			// what if we don't ? hm. well. error message sounds l33t :p
			$shfolders = array();
			$this->db_query("SELECT id,root_menu FROM objects,shop WHERE objects.oid = shop.id AND objects.status != 0 AND objects.class_id = ".CL_SHOP);
			while ($row = $this->db_next())
			{
				$shfolders[$row["root_menu"]] = $row["id"];
			}

			$oc = $this->get_object_chain($section);
			foreach($oc as $oid => $orow)
			{
				if ($shfolders[$oid])
				{
					// and we found a matching root folder!
					$shop = $shfolders[$oid];
					break;
				}
			}

			if (!$shop)
			{
				$this->raise_error("can't find the matching shop for the item!", true);
			}
		}


		classload("form");

		$row = $this->get_item($item_id,true);

		global $shopping_cart,$ext;

		$this->read_template("order_item.tpl");
		$parent = $this->do_shop_menus($shop,$section);
		$f = new form;
		$this->vars(array(
			"item" => $f->show(array("id" => $row["form_id"], "entry_id" => $row["entry_id"],"op_id" => $row["op_id_l"])),
			"item_id" => $row["oid"],
			"price" => $row["price"],
			"it_cnt"	=> $shopping_cart["items"][$row["oid"]]["cnt"],
			"order_item" => $this->mk_my_orb("order_item", array("item_id" => $row["oid"], "shop" => $id, "section" => $parent)),
			"cnt_form" => $f->gen_preview(array(
												"id" => $row["cnt_form"],
												"entry_id" => $shopping_cart["items"][$row["oid"]]["cnt_entry"],
												"reforb" => $this->mk_reforb("submit_order_item", array("item_id" => $row["oid"], "shop" => $shop, "section" => $parent)),
												"form_action" => "/index.".$ext)),
			"cart" => $this->mk_my_orb("view_cart", array("shop_id" => $shop, "section" => $parent))
		));
		return $this->parse();
	}

	////
	// !returns the shop item $id, if $check is true we detect if the object is a brother and revert to the real one
	function get_item($id,$check = false)
	{
		if ($check)
		{
			$o = $this->get_object($id);
			$id = $o["brother_of"];
		}
		$this->db_query("SELECT objects.*,shop_items.* FROM objects LEFT JOIN shop_items ON shop_items.id = objects.oid WHERE id = $id");
		return $this->db_next();
	}

	////
	// !adds the number of items in the count form to cart and also remembers the form_entry of the cnt_form so we know what type
	// of item was ordered. damn. this is like complicated and shit 
	function submit_order_item($arr)
	{
		extract($arr);
		global $shopping_cart;
	
		$it = $this->get_item($item_id,true);

		classload("form");
		$f = new form;
		$f->process_entry(array(
			"id" => $it["cnt_form"], 
			"entry_id" => $shopping_cart["items"][$item_id]["cnt_entry"]
		));
		$entry_id = $f->entry_id;

		// now figure out the correct row(s) in the count form and mark them in the shopping cart.
		// rows are figured out based on elements named selectrow - if it's checked on that row, then that row
		// nost be added to the cart. 
		// here we just calculate the amount of items based on the selected rows and numbers entered in "mitu" elements

		$selrows = $this->get_selected_rows_in_form(&$f);

		$count = 0;
		foreach($selrows as $i)
		{
			$rowels = $f->get_elements_for_row($i);
			// this row's count must be added to this item's count in the shopping cart
			$count+=$rowels["mitu"];
		}
		$GLOBALS["shopping_cart"]["items"][$item_id]["cnt"] = $count;
		$GLOBALS["shopping_cart"]["items"][$item_id]["name"] = $it["name"];
		$GLOBALS["shopping_cart"]["items"][$item_id]["cnt_entry"] = $entry_id;

		return $this->mk_my_orb("order_item", array("item_id" => $item_id, "shop" => $shop, "section" => $section));
	}

	////
	// !returns an array of rows that are selected in the entry loaded in the form $f (reference to object)
	function get_selected_rows_in_form(&$f)
	{
		$ret = array();
		$nr = $f->get_num_rows();
		for ($i=0; $i < $nr; $i++)
		{
			$rowels = $f->get_elements_for_row($i);
			// now check if this row's selectrow is checked
			if ($f->is_checked_value($rowels["selectrow"]))
			{
				// if it is, add it to the return
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
		$this->mk_path($sh["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda poodi</a> / Tellimused");

		$ss = "";
		if ($filter_uid)
		{
			global $uid;
			$ss = " AND user = '$uid' ";
		}

		$this->db_query("SELECT * FROM orders WHERE shop_id = $id $ss ORDER BY tm DESC");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"when" => $this->time2date($row["tm"], 2),
				"user" => $row["user"],
				"ip" => $row["ip"],
				"price" => $row["price"],
				"view"	=> $this->mk_my_orb("view_order", array("shop" => $id, "order_id" => $row["id"])),
				"fill"	=> $this->mk_my_orb("mark_order_filled", array("shop" => $id, "order_id" => $row["id"]))
			));
			$is_f = "";
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
		return $this->parse();
	}

	////
	// !marks the order as filled
	function mark_order_filled($arr)
	{
		extract($arr);
		$this->db_query("UPDATE orders SET status = ".ORD_FILLED." WHERE id = $order_id");
		header("Location: ".$this->mk_orb("admin_orders", array("id" => $shop)));
		die();
	}

	////
	// !shows the order $order_id for shop $shop
	function view_order($arr)
	{
		extract($arr);
		$this->read_template("view_order.tpl");
		$sh = $this->get($shop);
		$this->mk_path($sh["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda poodi</a> / <a href='".$this->mk_orb("admin_orders", array("id" => $shop))."'>Tellimused</a> / Vaata tellimust");

		// load the entry from the database
		$this->db_query("SELECT * FROM orders WHERE id = $order_id");
		$order = $this->db_next();

		$this->db_query("SELECT * FROM order2item WHERE order_id = $order_id");
		while ($row = $this->db_next())
		{
			$o_items[$row["item_id"]] = $row;
		}

		classload("form");
		$f = new form;
		$images = new db_images;

		$items = false;
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
					// load it's cnt_form
					$f->load($it["cnt_form"]);
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
						"name" => $it["name"],
						"F_ROW" => $rowhtml
					));
					$itm.=$this->parse("ITEM");
				}
			}
		}
		$f->load($sh["order_form"]);
		$f->load_entry($order["entry_id"]);

		$this->vars(array(
			"ITEM" => $itm,
			"user" => $order["user"],
			"time" => $this->time2date($order["tm"],2),
			"ip"	=> $order["ip"],
			"inf_form" => $f->show_text()
		));
		return $this->parse();
	}
}
?>
