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
		}

		return parent::set_prop($pn, $pv);
	}

	function get_sum()
	{
		$sum = $this->meta("final_saved_sum"); 
		//kui on salvestatud summa ja mneski valuutas omab vrtust, ning see on salvestatud ndal peale aja lbi saamist, siis lheb salvestatud variant loosi ja ei hakka uuesti le arvutama
		if(is_array($sum) && array_sum($sum) && ($this->prop("end") + 3600*24*7) < $this->meta("sum_saved_time"))
		{
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
		return $sum;
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