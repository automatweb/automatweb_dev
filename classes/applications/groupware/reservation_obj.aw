<?php
//metas v��rtused
//final_saved_sum - valuutades l�plik summa mis sai makstud ka t�en�oliselt... ja kui see olemas siis rohkem ei arvutata
//special_sum - m��ratud kindel summa k�igis valuutades... �le kirjutamiseks objekti juurest miskitel spetsjuhtudel

//maintainer=markop  
class reservation_obj extends _int_object
{
	function task_object()
	{
		parent::_int_object();
	}

	function get_sum()
	{
		$sum = $this->meta("final_saved_sum"); 
		//kui on salvestatud summa ja m�neski valuutas omab v��rtust, ning see on salvestatud n�dal peale aja l�bi saamist, siis l�heb salvestatud variant loosi ja ei hakka uuesti �le arvutama
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
}
?>