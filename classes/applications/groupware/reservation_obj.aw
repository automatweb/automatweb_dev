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

	function get_room_setting($setting)
	{
		if(!$this->prop("resource"))
		{
			return null;
		}
		$room = obj($this->prop("resource"));
		return $room->get_setting($setting);
	}
}
?>
