<?php
//metas vrtused
//final_saved_sum - valuutades lplik summa mis sai makstud ka tenoliselt... ja kui see olemas siis rohkem ei arvutata
//special_sum - mratud kindel summa kigis valuutades... le kirjutamiseks objekti juurest miskitel spetsjuhtudel

//maintainer=markop  
class reservation_obj extends _int_object
{
	function task_object()
	{
		parent::_int_object();
	}

	function set_prop($pn, $pv)
	{
		switch($pn)
		{
			case "start1":
				if($pv && $this->prop("verified"))
				{
					$this->set_meta("redecleared" , 1);
				}
				break;
			case "customer":
				if(is_oid($pv))
				{
					$person = obj($pv);
					$parent = $this->get_room_setting("customer_menu");
					if($parent && $parent != $person->parent())
					{
						$person->set_parent($parent);
						$person->save();
					}
				}
				break;
			case "verified":
				if($this->get_room_setting("send_verify_mail"))
				{
					$this->send_affirmation_mail();
				}
				break;
		}
		return parent::set_prop($pn, $pv);
	}

	function get_sum()
	{
		$sum = $this->meta("final_saved_sum"); 
		//kui on salvestatud summa ja mneski valuutas omab vrtust, ning see on salvestatud ndal peale aja lbi saamist, siis lheb salvestatud variant loosi ja ei hakka uuesti le arvutama
		if(is_array($sum) && (!$this->prop("end") || ($this->prop("end") + 3600*24*7) < $this->meta("sum_saved_time")))
		{
			exit_function("sbo::_get_sum");
			return $sum;
		}

		$special_sum = $this->meta("special_sum");
		if(is_array($special_sum) && array_sum($special_sum))
		{
			$sum = $special_sum;
		}
		else
		{
			$room_instance = get_instance(CL_ROOM);
			$sum = $room_instance->cal_room_price(array(
				"room" => $this->prop("resource"),
				"start" => $this->prop("start1"),
				"end" => $this->prop("end"),
				"people" => $this->prop("people_count"),
				"products" => $this->meta("amount"),
				"bron" => $this,
			));
		}

		$this->set_meta("final_saved_sum" , $sum);
		$this->set_meta("sum_saved_time" , time());
		$this->save();
		exit_function("sbo::_get_sum");
		return $sum;
	}

	/** returns reservation price in currency
		@attrib api=1
		@param curr type=int/string
			currency id or 
		@returns double
			reservation price
	**/
	function get_sum_in_curr($curr)
	{
		if(!is_oid($curr))
		{
			$ol = new object_list(array(
				"site_id" => array(),
				"lang_id" => array(),
				"class_id" => CL_CURRENCY,
				"name" => $curr,
			));
			$curr = reset($ol->ids());
		}
		if(!is_oid($curr))
		{
			return "";
		}
		$sum = $this->get_sum();
		return $sum[$curr];
	}

	/** Returns resouces data
		@attrib api=1
	 **/
	function get_resources_data()
	{
		$inst = $this->instance();
		return $inst->get_resources_data($this->id());
	}

	/** Returns resources special prices
		@attrib api=1
	 **/
	function get_resources_price()
	{
		$inst = $this->instance();
		return $inst->get_resources_price($this->id());
	}

	/** Returns resources special discount
		@attrib api=1
	 **/
	function get_resources_discount()
	{
		$inst = $this->instance();
		return $inst->get_resources_discount($this->id());
	}

	/** returns resources sum
		@attrib api=1 params=pos
		@param special_discounts_off bool optional default=false
			
		@returns
			returns reservations resources sum in different currencies
	 **/
	function get_resources_sum($special_discounts_off = false)
	{
		$info = $this->get_resources_data();
		$price = $this->get_resources_price();
		$discount = $this->get_resources_discount();
		foreach($this->get_currencies_in_use() as $oid => $obj)
		{
			// check if special price is set
			if(strlen($price[$oid]))
			{
				$sum[$oid] = $price[$oid];
			}
			else // no special price, calc resources prices
			{

				foreach($info as $resource => $r_data) // loop over resources
				{
					if(strlen($r_data["prices"][$oid])) // if price is set
					{
						$count_total = $r_data["prices"][$oid] * $r_data["count"]; // amount * price
						$sum[$oid] += (strlen($r_data["discount"]) && $r_data["discount"] != 0)?$count_total * ((100 - $r_data["discount"]) / 100):$count_total; // discount and sum up
					}
				}
			}

			if(strlen($discount) && $discount != 0 && !$special_discounts_off) // calc special discount for all
			{
				$sum[$oid] *= ((100 - $discount) / 100);
			}
		}
		return $sum;
	}

	/** Returns currencies in use
		@attrib api=1
		@returns
			array(
				cur_oid => cur_obj
			)
		@comment
			Actually what this does is just return all system currencies right now, and all the places even don't use this in reservation obj(but they should).
	 **/
	function get_currencies_in_use()
	{
		$ol = new object_list(array(
			"site_id" => array(),
			"lang_id" => array(),
			"class_id" => CL_CURRENCY,
		));
		return $ol->arr();
	}

	/** adds new project to reservation
		@attrib api=1
		@returns oid
			project id
	**/
	function set_new_project($name)
	{
		if(!strlen($name))
		{
			return;
		}
		$parent = $this->get_room_setting("projects_menu");
		if(!$parent)
		{
			$parent = $this->id();
		}
		if(!$parent)
		{
			$parent = $this->parent();
		}
		$project = new object();
		$project->set_parent($parent);
		$project->set_class_id(CL_PROJECT);
		$project->set_name($name);
		$project->save();
		$this->set_prop("project" , $project->id());
		$this->save();
		return $project->id();
	}

	/** Returns this reservation room setting
		@attrib api=1 params=pos
		@param setting required type=string
			room setting
		@return 
			room setting value , or 0
	**/
	function get_room_setting($setting)
	{
		if(!is_object($this->room))
		{
			if(!$this->prop("resource"))
			{
				return null;
			}
			$this->room = obj($this->prop("resource"));
		}
		return $this->room->get_setting($setting);
	}

	/** Sends confirmation mail for a reservation
		@attrib api=1 params=pos
		@param tpl optional type=string
			The name of the template to use for formatting the email content
		@return boolean
			1 if mail sent, 0 if not
	**/
	function send_affirmation_mail($tpl = null)
	{
		if($this->meta("mail_sent"))
		{
			return 0;
		}
		$res_inst = get_instance(CL_ROOM_RESERVATION);
		$_send_to = $this->prop("customer.email.mail");

		$email_subj = $this->get_room_setting("verify_mail_subj");
		$mail_from_addr = $this->get_room_setting("verify_mail_from");
		$mail_from_name = $this->get_room_setting("verify_mail_from_name");
		if(!$tpl)
		{
			$tpl = "preview.tpl";
		}
		$res_inst->read_site_template($tpl);
		lc_site_load("room_reservation", &$res_inst);
		$res_inst->vars($this->get_bron_data());
		$html =  $res_inst->parse();

		$awm = get_instance("protocols/mail/aw_mail");
		$awm->create_message(array(
			"froma" => $mail_from_addr,
			"fromn" => $mail_from_name,
			"subject" => $email_subj,
			"to" => $_send_to,
			"body" => strip_tags(str_replace("<br>", "\n",$html)),
		));
		$awm->htmlbodyattach(array(
			"data" => $html
		));
		$awm->gen_mail();
		$this->set_meta("mail_sent" , 1);
		$this->save();
		return 1;
	}

	/** Returns object data for printing or sending mail ...
		@attrib api=1 params=pos
		@param tpl optional type=string
			The name of the template to use for formatting the email content
		@return boolean
			1 if mail sent, 0 if not
	**/
	function get_bron_data()
	{
		$ret = array();
		$room = obj($this->prop("resource"));
		$ret["room_name"] = $room->name();
		$ret["time_str"] = $this->get_time_str(array(
			"start" => $this->prop("start1"),
			"end" => $this->prop("end"),
		));
		$ret["hours"] = ($this->prop("end")-$this->prop("start1"))/3600;
		$ret["people_value"] = $this->prop("people_count");

		$room_inst = get_instance(CL_ROOM);
		$sum = $room_inst->cal_room_price(array(
			"room" => $this->prop("resource"),
			"people" => $ret["people_value"],
			"start" => $this->prop("start1"),
			"end" => $this->prop("end"),
			"products" => -1,
		//	"products" => $bron->meta("amount"),
		));
		$data["sum"] = $data["sum_wb"] = $data["bargain"] = $data["menu_sum"] = $data["menu_sum_left"] = array();

		$prod_discount = $room_inst->get_prod_discount(array(
			"room" => $this->prop("resource"),
			"start" => $this->prop("start1"),
			"end" => $this->prop("end"))
		);
		foreach($sum as $curr => $val)
		{
			$currency = obj($curr);
	//		$data["sum"][] =  $val." ".$currency->name();
			$data["bargain"][] = (0+$room_inst->bargain_value[$curr])." ".$currency->name();
			$data["sum_wb"][] = ((double) $val + (double)$room_inst->bargain_value[$curr]) ." ".$currency->name();
		}
		foreach ($this->meta("amount") as $prod => $amount)
		{
			if($amount)
			{
				$product = obj($prod);
				$prices = $product->meta("cur_prices");
				foreach ($sum as $curr=> $val)
				{
					if($prices[$curr] || $prices[$curr] === 0)
					{
						$data["menu_sum"][$curr] = $data["menu_sum"][$curr] + $prices[$curr]*$amount*(100-$prod_discount)*0.01;
					}
					else
					{
						$data["menu_sum"][$curr] = $data["menu_sum"][$curr]+$product->prop("price")*$amount*(100-$prod_discount)*0.01;
					}
				}
			}
		}
		foreach ($sum as $curr=> $val)
		{
			$currency = obj($curr);
			if(!$data["menu_sum"][$curr])
			{
				$data["menu_sum"][$curr] = 0;
			}
			$data["menu_sum"][$curr] = $data["menu_sum"][$curr]." ".$currency->name();
		}

		$sum = $this->get_sum();

		foreach($sum as $curr => $val)
		{
			$currency = obj($curr);
			$data["sum"][] =  $val." ".$currency->name();
			$min_prices = $room->meta("web_room_min_price");
			$min_sum = $min_prices[$curr] - $val;
			if($min_sum < 0)
			{
				$min_sum = 0;
			}
			$data["min_sum_left"][] = $min_sum." ".$currency->name();
		}
		$ret["sum"] = join("/" , $data["sum"]);
		$ret["bargain"] = join("/" , $data["bargain"]);
		$ret["sum_wb"] = join("/" , $data["sum_wb"]);
		$ret["menu_sum"] = join("/" , $data["menu_sum"]);
		$ret["comment_value"] = $this->prop("content");
		$ret["min_sum_left"] = join("/" , $data["min_sum_left"]);

		$ret["status"] = ($this->prop("verified") ? t("Kinnitatud") : t("Kinnitamata"));
		$ret["bank_value"] = $this->meta("bank_name");
		foreach ($this->meta("amount") as $prod => $amount)
		{
			if($amount)
			{
				$product = obj($prod);

				$this->vars(array(
					"prod_name" => $product->name(), "prod_amount" => $amount  , "prod_value"=> $product->prop("price") ,
				));
				$p.= $this->parse("PROD");
			}
		}

		if(is_oid($this->prop("customer")))
		{
			$customer = obj($this->prop("customer"));
			$ret["phone_value"] = $customer->prop("phone.name");
			$ret["email_value"] = $customer->prop("email.mail");;
		}
		$ret["name_value"] = $this->prop_str("customer");
		$ret["PROD"] = $p;
		return $ret;
	}

	private function get_time_str($arr)
	{
		$room_inst = get_instance(CL_ROOM);
		extract($arr);
		$res = "";
		$res.= $room_inst->weekdays[(int)date("w" , $arr["start"])];
		$res.= ", ";
		$res.= date("d.m.Y" , $arr["start"]);
		$res.= ", ";
		$res.= date("H:i" , $arr["start"]);
		$res.= " - ";
		$res.= date("H:i" , $arr["end"]);
		return $res;
	}
}
?>
