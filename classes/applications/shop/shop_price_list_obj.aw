<?php

class shop_price_list_obj extends _int_object
{
	public function awobj_get_matrix_cols()
	{
		return (int)parent::prop("matrix_cols");
	}

	public function awobj_get_matrix_rows()
	{
		return (int)parent::prop("matrix_rows");
	}

	public function awobj_set_matrix_infrastructure($v)
	{
		foreach(array("country", "area", "county", "city") as $k)
		{
			$this->set_prop("matrix_".$k, (int)!empty($v[$k]));
		}
	}

	public function awobj_get_matrix_infrastructure()
	{
		$v = array();
		foreach(array("country", "area", "county", "city") as $k)
		{
			if($this->prop("matrix_".$k))
			{
				$v[$k] = $k;
			}
		}
		return $v;
	}

	/**
		@attrib params=name
		@param valid optional type=bool
	**/
	public static function get_price_lists($arr = array())
	{
		$prms = array(
			"class_id" => CL_SHOP_PRICE_LIST,
			"lang_id" => array(),
			"site_id" => array(),
		);

		if(!empty($arr["valid"]))
		{	// VALID
			$prms["valid_from"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, time());
			$prms["valid_to"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, time());
		}
		elseif(isset($arr["valid"]))
		{	// INVALID
			$prms[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_SHOP_PRICE_LIST.valid_from" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, time()),
					"CL_SHOP_PRICE_LIST.valid_to" => new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, time()),
				)
			));
		}

		$ol = new object_list($prms);
		return $ol;
	}
}

?>
