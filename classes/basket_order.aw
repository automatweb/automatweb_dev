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

		$ff = get_instance("form");
		$ff->load($ob["meta"]["order_form"]);
		$ff->load_entry($order["meta"]["of_entry"]);

		$this->vars(array(
			"uid" => $ob["createdby"],
			"time" => $this->time2date($ob["created"],2),
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

		$this->vars(array(
			"ITEM" => $its,
			"t_count" => $t_count,
			"t_price" => $t_price,
			"another" => $this->mk_my_orb("another", $arr)
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