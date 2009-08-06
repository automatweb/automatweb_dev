<?php

class shop_rent_configuration_obj extends shop_matrix_obj
{
	/**
		@attrib params=name api=1
		@param row optional
		@param col optional
		@param currency optional
	**/
	public function conditions($arr = array())
	{
		$prms = array(
			"class_id" => CL_SHOP_RENT_CONDITIONS,
			"rent_configuration" => $this->id(),
			new obj_predicate_sort(array(
				"min_amt" => "ASC",
			)),
		);
		if(isset($arr["row"]))
		{
			$prms["row"] = is_oid($arr["row"]) ? $arr["row"] : 0;
		}
		if(isset($arr["col"]))
		{
			$prms["col"] = is_oid($arr["col"]) ? $arr["col"] : 0;
		}
		if(isset($arr["currency"]) && is_oid($arr["currency"]))
		{
			$prms["currency"] = $arr["currency"];
		}
		$ol = new object_list($prms);
		return $ol;
	}
}

?>
