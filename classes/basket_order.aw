<?php

classload("basket");
class basket_order extends basket
{
	function basket_order()
	{
		$this->basket();
	}

	////
	// !shows the order $id
	function change($arr)
	{
		extract($arr);
		$this->read_template("show_order.tpl");
		$order = $this->get_object($id);
		$this->mk_path($order["parent"], "Vaata telimust");
		$ob = $this->get_basket($order["meta"]["basket_id"]);

		$sbs = aw_global_get("shop_basket");
		$sbs[$order['meta']['basket_id']] = $order['meta'];
		aw_global_set("shop_basket", $sbs);

		$ff = get_instance("formgen/form");
		$ff->load($ob["meta"]["order_form"]);
		$ff->load_entry($order["meta"]["of_entry"]);

		$this->vars(array(
			"uid" => $order["createdby"],
			"time" => $this->time2date($order["created"],2),
			"order_id" => $id,
			"of_entry" => $ff->show_text()
		));

		foreach($order["meta"]["items"] as $iid => $icnt)
		{
			$fid = $order["meta"]["form_ids"][$iid];
			$finst =&$ff->cache_get_form_instance($fid);
			$finst->load_entry($iid);
			$this->vars(array(
				"item_op" => $finst->show_text(),
				"count" => $icnt,
				"it_price" => $order["meta"]["prices"][$iid] * $icnt,
				"price" => $order["meta"]["prices"][$iid]
			));
			$its.=$this->parse("ITEM");
			$t_count += $icnt;
			$t_price += $order["meta"]["prices"][$iid] * $icnt;
		}
		aw_global_set("cur_price_elements_sum", $t_price);

		if (!$ob["meta"]["order_form_op"])
		{
			$this->raise_error(ERR_BASKET_NO_OOP, "No output selectes for order form - can't send HTML mail!", false, true);
		}
		else
		{
			$finst = get_instance("formgen/form");
			$finst->load($ob["meta"]["order_form"]);
			$htmlmail = $finst->show(array(
				"id" => $ob["meta"]["order_form"],
				"entry_id" => $order["meta"]["of_entry"],
				"op_id" => $ob["meta"]["order_form_op"]
			));
			$htmlmail.="<br><br>".$this->_draw_basket_ft($ob, $order["meta"], true);
		}

		$this->vars(array(
			"ITEM" => $its,
			"t_count" => $t_count,
			"t_price" => $t_price,
			"another" => $this->mk_my_orb("another", $arr),
			"content" => $htmlmail
		));
		return $this->parse();
	}

	/////
	// !starts a new order based on order $id
	function another($arr)
	{
		extract($arr);
		$order = $this->get_object($id);
		$this->mk_path($order["parent"], "Uus tellimus");

		$bid = $order["meta"]["basket_id"];
		$ob = $this->get_basket($bid);
		$this->init_basket($bid);

		foreach($order["meta"]["items"] as $iid => $icnt)
		{
			$this->set_item_count(array("item_id" => $iid, "form_id" => $order["meta"]["form_ids"][$iid], "count" => $icnt));
			$this->set_item_price(array("item_id" => $iid, "price" => $order["meta"]["prices"][$iid]));
		}

		// set the of_entry on which the new one will be based
		$tmp = aw_global_get("shop_basket");
		$tmp[$this->current_basket_id]["of_based_on"] = $order["meta"]["of_entry"];
		aw_session_set("shop_basket", $tmp);

		$this->save_user_basket();

		header("Location: ".$this->mk_my_orb("show", array("id" => $order["meta"]["basket_id"])));
	}
}
?>
