<?php

class shop_sell_order_obj extends _int_object
{
	/** returns order price
		@attrib api=1
		@returns double
			order rows price sum
	**/
	public function get_sum()
	{
		$sum = 0;
		$odl = new object_data_list(array(
			"class_id" => CL_SHOP_ORDER_ROW,
			"lang_id" => array(),
			"site_id" => array(),
			"CL_SHOP_ORDER_ROW.RELTYPE_ROW(CL_SHOP_SELL_ORDER)" => $this->id()
		),
		array(
			CL_SHOP_ORDER_ROW => array("amount" , "price"),
		));

		foreach($odl->arr() as $od)
		{
			$sum += $od["amount"] * $od["price"];
		}
		return $sum;
	}

	/** returns all product names
		@attrib api=1
		@returns array
	**/
	public function get_product_names()
	{
		$ret = array();
		$sum = 0;
		foreach($this->connections_from(array("type" => "RELTYPE_ROW")) as $c)
		{
			$row = $c->to();
			$ret[] = $row->prod_name();
		}
		return $ret;
	}

	function save($exclusive = false, $previous_state = null)
	{
//		if(empty($this->order_status))
//		{
//			$this->set_prop("order_status" , "1");
//		}
		$r =  parent::save($exclusive, $previous_state);
		return $r;
	}

	function prop($name)
	{
		switch($name)
		{
			case "shop_delivery_type":
				if(!parent::prop("shop_delivery_type") && $this->prop("transp_type.class_id") == CL_SHOP_DELIVERY_METHOD)
				{
					$name = "transp_type";
				}
			break;
		}


		return parent::prop($name);
	}


	/** adds new row
		@attrib api=1 params=name
		@param price optional type=double
		@param product optional type=oid
		@param product_name optional type=string
		@param amount optional type=double
		@param code optional type=string
			product code
		@returns oid
			new row id
	**/
	public function add_row($data)
	{
		$o = new object();
		$o->set_class_id(CL_SHOP_ORDER_ROW);
		$o->set_name($this->name()." ".t("rida"));
		$o->set_parent($this->id());
		$o->set_prop("prod" , $data["product"]);
		if(empty($data["product_name"]))
		{
			$o->set_prop("prod_name" , get_name($data["product"]));
		}
		else
		{
			$o->set_prop("prod_name" , $data["product_name"]);
		}
		$o->set_prop("items" , $data["amount"]);
		$o->set_prop("amount" , $data["amount"]);
		$o->set_prop("price" , $data["price"]);
		$o->set_prop("other_code" , $data["code"]);
		$o->set_prop("date", time());
		$o->save();
		$this->connect(array(
			"to" => $o->id(),
			"type" => "RELTYPE_ROW"
		));
		return $o->id();
	}

	/** returns order orderer e-mail address
		@attrib api=1
		@returns string
			mail address
	**/
	public function get_orderer_mail()
	{
		$orderer = $this->prop("purchaser");
		if(is_oid($orderer))
		{
			$o = obj($orderer);
			return $o->get_mail();
		}
		return null;
	}

	/**
		@attrib name=bank_return nologin=1
	**/
	function bank_return($arr)
	{
		if($this->meta("lang_id"))
		{
			$_SESSION["ct_lang_id"] = $this->meta("lang_id");
			$_SESSION["ct_lang_lc"] = $this->meta("lang_lc");
			aw_global_set("ct_lang_lc", $_SESSION["ct_lang_lc"]);
			aw_global_set("ct_lang_id", $_SESSION["ct_lang_id"]);
		}
		$this->set_prop("order_status" , "0");
		aw_disable_acl();
		$this->save();
		aw_restore_acl();

		$order_data = $this->meta("order_data");

		$order_center = obj($order_data["oc"]);

		// send mail
		if(!$this->meta("mail_sent"))
		{
			$this->set_meta("mail_sent" , 1);
			$order_center->send_confirm_mail($this->id());
			aw_disable_acl();
			$this->save();
			aw_restore_acl();

		}

		if(is_oid($this->meta("bank_payment_id")))
		{
			$p = obj($this->meta("bank_payment_id"));
//			if(!empty($p->prop("bank_return_url")))
//			{
//				return $p->prop("bank_return_url");
//			}
		}
		return $this->mk_my_orb("show", array("id" => $this->id()), "shop_order");
	}

	/** returns customer relation object
		@attrib api=1 params=pos
		@param my_co optional
		@param crea_if_not_exists optional
			if no customer relation object, makes one
		@returns object
	**/
	public function get_customer_relation($my_co = null, $crea_if_not_exists = false)
	{
		if ($my_co === null)
		{
			$my_co = get_current_company();
		}

		if (!is_object($my_co) || !is_oid($my_co->id()) || !$this->prop("purchaser"))
		{
			return;
		}

		static $gcr_cache;
		if (!is_array($gcr_cache))
		{
			$gcr_cache = array();
		}
		if (isset($gcr_cache[$this->prop("purchaser")][$crea_if_not_exists][$my_co->id()]))
		{
			return $gcr_cache[$this->prop("purchaser")][$crea_if_not_exists][$my_co->id()];
		}

		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
			"buyer" => $this->prop("purchaser"),
			"seller" => $my_co
		));
		if ($ol->count())
		{
			$gcr_cache[$this->prop("purchaser")][$crea_if_not_exists][$my_co->id()] = $ol->begin();
			return $ol->begin();
		}
		else
		if ($crea_if_not_exists)
		{
			$my_co = obj($my_co);
			$o = obj();
			$o->set_class_id(CL_CRM_COMPANY_CUSTOMER_DATA);
			$o->set_name(t("Kliendisuhe ").$my_co->name()." => ".$this->prop("purchaser.name"));
			$o->set_parent($my_co->id());
			$o->set_prop("seller", $my_co->id());
			$o->set_prop("buyer", $this->prop("purchaser"));
			$o->save();
			$gcr_cache[$this->prop("purchaser")][$crea_if_not_exists][$my_co->id()] = $o;
			return $o;
		}
	}



}

?>
