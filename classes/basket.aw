<?php

class basket extends aw_template
{
	function basket()
	{
		$this->init("basket");
	}

	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa korv");
		}
		else
		{
			$this->mk_path($parent,"Lisa korv");
		}
		$this->read_template("add.tpl");

		$ob = get_instance("objects");
		$fo = get_instance("formgen/form");

		$this->vars(array(
			"ftbls" => $this->picker(0,$this->list_objects(array("class" => CL_FORM_TABLE, "addempty" => true))),
			"ord_parents" => $this->picker(0,$ob->get_list()),
			"order_form" => $this->picker(0, $fo->get_flist(array("type" => FTYPE_ENTRY, "addempty" => true, "addfolders" => true, "sort" => true))),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_SHOP_BASKET
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"ftbl" => $ftbl,
				"ord_parent" => $ord_parent,
				"after_order" => $after_order,
				"mail_to" => $mail_to,
				"order_form" => $order_form,
				"order_form_op" => $order_form_op,
				"order_ftbl" => $order_ftbl
			)
		));
		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

	function change($arr)
	{
		extract($arr);
		$ob = $this->get_basket($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda korvi");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda korvi");
		}
		$this->read_template("add.tpl");

		$oj = get_instance("objects");
		$fo = get_instance("formgen/form");

		$ops = $fo->get_op_list($ob["meta"]["order_form"]);

		$this->vars(array(
			"name" => $ob["name"],
			"after_order" => $ob["meta"]["after_order"],
			"mail_to" => $ob["meta"]["mail_to"],
			"ftbls" => $this->picker($ob["meta"]["ftbl"],$this->list_objects(array("class" => CL_FORM_TABLE, "addempty" => true))),
			"ord_parents" => $this->picker($ob["meta"]["ord_parent"],$oj->get_list()),
			"order_form" => $this->picker($ob["meta"]["order_form"], $fo->get_flist(array("type" => FTYPE_ENTRY, "addempty" => true, "addfolders" => true, "sort" => true))),
			"order_form_op" => $this->picker($ob["meta"]["order_form_op"], $ops[$ob["meta"]["order_form"]]),
			"order_ftbl" => $this->picker($ob["meta"]["order_ftbl"], $this->list_objects(array("class" => CL_FORM_TABLE,"addempty" => true))),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

	function get_basket($id)
	{
		return $this->get_object($id);
	}

	////
	// !initializes the current basket - call this before using set_item_count/get_item_count
	function init_basket($id)
	{
		// if the current basket is the same, don't reload
		if ($id != $this->current_basket_id)
		{
			$this->current_basket_id = $id;

			// check if the basket is in the session
			$tmp = aw_global_get("shop_basket");
			if (is_array($tmp[$id]))
			{
				// if so, leave it there
				if (!is_array($tmp[$id]["items"]))
				{
					$tmp[$id]["items"] = array();
					aw_session_set("shop_basket", $tmp);
				}
			}
			else
			if (aw_global_get("uid") != "")
			{
				// if the basket was not in the session, 
				// load the state of the basket from the user's config space
				$us = get_instance("users");
				$tmp = aw_global_get("shop_basket");
				$tmp[$id] = $us->get_user_config(array(
					"uid" => aw_global_get("uid"),
					"key" => "current_basket".$id
				));
				if (!is_array($tmp[$id]))
				{
					$tmp[$id] = array();
				}
				if (!is_array($tmp[$id]["items"]))
				{
					$tmp[$id]["items"] = array();
				}
				aw_session_set("shop_basket", $tmp);
			}
			else
			{
				$tmp[$id] = array();
				$tmp[$id]["items"] = array();
				aw_session_set("shop_basket", $tmp);
			}
		}
	}

	////
	// !returns the number of items with id $item_id in the current basket
	function get_item_count($arr)
	{
		extract($arr);
		$tmp = aw_global_get("shop_basket");
		return $tmp[$this->current_basket_id]["items"][$item_id];
	}

	////
	// !sets the number of items with id $item_id in the current basket to $count
	// also, form_id must be specified - it sould be the form for which the entry was made
	function set_item_count($arr)
	{
		extract($arr);
		$tmp = aw_global_get("shop_basket");
		if ($count == 0)
		{
			unset($tmp[$this->current_basket_id]["items"][$item_id]);
		}
		else
		{
			$tmp[$this->current_basket_id]["items"][$item_id] = $count;
			$tmp[$this->current_basket_id]["form_ids"][$item_id] = $form_id;
		}
		aw_session_set("shop_basket", $tmp);
	}

	////
	// !sets the price for the specified item in the current basket
	// params:
	//    $item_id - the item in the current basket whose price it is 
	//    $price - the price of the item (price for one item, not multiplied with count)
	function set_item_price($arr)
	{
		extract($arr);
		$tmp = aw_global_get("shop_basket");
		$tmp[$this->current_basket_id]["prices"][$item_id] = $price;
		aw_session_set("shop_basket", $tmp);
	}

	////
	// !saves the user's current basket to the database so we can later check what's in it
	// the data is saved in the user's personal config space
	function save_user_basket()
	{
		return;
		if (aw_global_get("uid") != "")
		{
			$tmp = aw_global_get("shop_basket");

			$us = get_instance("users");
			$us->set_user_config(array(
				"uid" => aw_global_get("uid"),
				"key" => "current_basket".$this->current_basket_id,
				"value" => $tmp[$this->current_basket_id]
			));
		}
	}

	////
	// !gets called if the basket is embedded somewhere
	function parse_alias($args = array())
	{
		extract($args);
		return $this->draw_basket(array("id" => $alias["target"]));
	}

	////
	// !shows the stuff that the current user has in the basket and lets you finalize the order
	// params:
	//   id - the id of the basket
	function draw_basket($arr)
	{
		extract($arr);
		$this->read_template("show.tpl");
		// read in the state of the basket
		$this->init_basket($id);
		// read in the basket's config
		$ob = $this->get_basket($id);

		// make sure the document won't get cached
		aw_global_set("no_cache_content",1);

		// get the current basket
		$tmp = aw_global_get("shop_basket");
		$basket = $tmp[$this->current_basket_id];

		if (!$ob["meta"]["order_form"])
		{
			$this->raise_error(ERR_BASKET_NO_OF_SET,"No order form is selected for basket $id, can't continue!", true);
		}

		$ff = get_instance("formgen/form");
		$this->vars(array(
			"basket" => $this->_draw_basket_ft($ob, $basket),//$ft->finalize_table(),
			"order_form" => $ff->gen_preview(array(
				"id" => $ob["meta"]["order_form"],
				"load_entry_data" => $basket["of_based_on"],
				"reforb" => $this->mk_reforb("finalize_order", array("id" => $id, "ret_url" => aw_global_get("REQUEST_URI"))),
			))
		));
		return $this->parse();
	}

	////
	// !this gets called, when the user clicks the "submit order" button
	// it will save the order as an object under the menu specified in the basket's config,
	// process the submitted order form
	// and send email to all the necessary addresses
	// then it will clear the basket from memory and from the user's config
	// and finally redirect to the url specified in the basket's config
	// params:
	//   id - the oid of the basket that got submitted
	function finalize_order($arr)
	{
		extract($arr);
		// read in the state of the basket
		$this->init_basket($id);
		// read in the basket's config
		$ob = $this->get_basket($id);

		// get the current basket
		$tmp = aw_global_get("shop_basket");
		$basket = $tmp[$this->current_basket_id];

		$creat = time();
		$order_id = $this->new_object(array(
			"parent" => $ob["meta"]["ord_parent"],
			"class_id" => CL_SHOP_BASKET_ORDER,
		));

		// we have to calculate the total price and store to a global var so that the form controller for the total price
		// can get to it
		$t_price = 0;
		foreach($basket["items"] as $iid => $icnt)
		{
			$t_price += $basket["prices"][$iid] * $icnt;
		}
		aw_global_set("cur_price_elements_sum", $t_price);

		// now create the form entry under the order
		$finst = get_instance("formgen/form");
		$finst->process_entry(array(
			"id" => $ob["meta"]["order_form"],
			"parent" => $order_id,
		));

		// rename order and add the order form entry id to the data
		$basket["of_entry"] = $finst->entry_id;
		$basket["basket_id"] = $id;
		$basket["t_price"] = $t_price;
		$this->upd_object(array(
			"oid" => $order_id,
			"name" => $finst->entry_name,
			"metadata" => $basket,
			"subclass" => $id					// save the basket id there so that we can query for it later
		));

		// write order to db
		$this->db_query("INSERT INTO basket_orders(id, of_entry, t_price, basket_id) 
			VALUES('$order_id', '$finst->entry_id','$t_price','$id')");
		foreach($basket["items"] as $iid => $icnt)
		{
			$this->db_query("INSERT INTO basket_order2item(order_id, item_id, cnt, it_price, form_id) 
				VALUES('$order_id','$iid','$icnt','".$basket["prices"][$iid]."','".$basket["form_ids"][$iid]."')");
		}

		$mls = explode(",", $ob["meta"]["mail_to"]);
		if (is_array($mls))
		{
			// put together the mail to send to those who want it
			$this->read_template("mail.tpl");
			$its = "";

			$ff = get_instance("formgen/form");
			$ff->load($ob["meta"]["order_form"]);
			$ff->load_entry($finst->entry_id);

			$this->vars(array(
				"uid" => aw_global_get("uid"),
				"time" => $this->time2date($creat,2),
				"order_id" => $order_id,
				"of_entry" => $ff->show_text()
			));

			foreach($basket["items"] as $iid => $icnt)
			{
				$fid = $basket["form_ids"][$iid];
				$finst =&$ff->cache_get_form_instance($fid);
				$finst->load_entry($iid);
				$this->vars(array(
					"item_op" => $finst->show_text(),
					"count" => $icnt,
					"it_price" => $basket["prices"][$iid] * $icnt,
					"price" => $basket["prices"][$iid]
				));
				$its.=$this->parse("ITEM");
			}

			$this->vars(array(
				"ITEM" => $its
			));
			$mail = $this->parse();

			if (!$ob["meta"]["order_form_op"])
			{
				$this->raise_error(ERR_BASKET_NO_OOP, "No output selectes for order form - can't send HTML mail!", false, true);
			}
			else
			{
				$_ft_basket = $this->_draw_basket_ft($ob, $basket, true);
				$finst = get_instance("formgen/form");
				$finst->load($ob["meta"]["order_form"]);
				$htmlmail = $finst->show(array(
					"id" => $ob["meta"]["order_form"],
					"entry_id" => $basket["of_entry"],
					"op_id" => $ob["meta"]["order_form_op"]
				));
				$htmlmail.="<br><br>".$_ft_basket;
			}

			foreach($mls as $ml)
			{
//				mail($ml, "Tellimus korvist ".$ob["name"], $mail);
				// send html mail
				$awm = get_instance("aw_mail");
				// we set all the relevant fields later on
				$awm->create_message(array(
					"froma" => "automatweb@automatweb.com",
					"fromn" => "AutomatWeb",
					"subject" => "Tellimus korvist ".$ob["name"],
					"to" => $ml,
					"body" => $mail,
				));

				$awm->htmlbodyattach(array("data" => $htmlmail));
				$awm->gen_mail();
			}
		}

		// and now kill the current basket
		$tmp[$this->current_basket_id] = array();
		aw_session_set("shop_basket", $tmp);
		$this->save_user_basket();
		$this->current_basket_id = false;

		return $ob["meta"]["after_order"] == "" ? urldecode($ret_url) : $ob["meta"]["after_order"];
	}

	////
	// !adds item to basket and redirects to specified url
	// params:
	//   item_id - form entry id of item to add to basket
	//   form_id - form id of entry to add to basket
	//   basket_id - if of the basket to add item to
	//   count - number of items to add to basket
	//   redir - url of page to redirect to
	function orb_add_item($arr)
	{
		extract($arr);
		$this->init_basket($basket_id);
		$this->set_item_count(array("item_id" => $item_id, "count" => $count+$this->get_item_count(array("item_id" => $item_id)), "form_id" => $form_id));
		$this->save_user_basket();
		header("Location: ".$redir);
		die();
	}

	function _draw_basket_ft($ob, $basket, $is_mail = false)
	{
		// start drawing the set table
		if (!$ob["meta"]["ftbl"])
		{
			$this->raise_error(ERR_BASKET_NO_TBL_SET, "No form table set for basket $ob[oid] - can't show basket!", true);
		}

		// form factory for reading in other forms
		$ff = get_instance("formgen/form");

		// start drawing the basket
		$ft = get_instance("formgen/form_table");
		$ft->start_table($is_mail ? $ob["meta"]["order_ftbl"] : $ob["meta"]["ftbl"]);
		foreach($basket["items"] as $iid => $icnt)
		{
			$fid = $basket["form_ids"][$iid];
//			unset($finst);
//			$finst =& $ff->cache_get_form_instance($fid);
			$finst = get_instance("formgen/form");
			$finst->load($fid);
			$finst->load_entry($iid);
			$ft->form_for_entry_id = $fid;
			$ft->row_data_from_form(array($finst));
		}

		// put this into global scope, so that we can use it in a form controller
		aw_global_set("cur_price_elements_sum", $ft->get_price_elements_sum());

		return $ft->finalize_table();
	}
}
?>