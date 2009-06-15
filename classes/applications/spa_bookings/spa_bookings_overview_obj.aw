<?php

class spa_bookings_overview_obj extends _int_object
{
	public function get_category_names()
	{
		$ol = new object_list(array(
			"class_id" => CL_ROOM_CATEGORY,
			"site_id" => array(),
			"lang_id" => array(),
			"parent" => $this->id(),
		));
		return $ol->names();
	}
	
	public function get_rooms($arr = array())
	{
		$filter = array(
			"class_id" => CL_ROOM,
			"site_id" => array(),
			"lang_id" => array(),
		);
		if(is_oid($arr["cat"]))
		{
			$filter["CL_ROOM.RELTYPE_CATEGORY"] = $arr["cat"];

		}

		if($arr["name"])
		{
			$filter["name"] = "%".$arr["name"]."%";

		}

//t&uuml;ra , see saast vaja metast v&auml;lja saada
		if(is_numeric($arr["cap_to"]) && is_numeric($arr["cap_from"]))
		{
			$filter["max_capacity"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $arr["cap_from"] , $arr["cap_to"]);
		}
		else
		{
			if(is_numeric($arr["cap_to"]))
			{
				$filter["max_capacity"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, (int)$arr["cap_to"]);
			}
			if(is_numeric($arr["cap_from"]))
			{
				$filter["max_capacity"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, (int)$arr["cap_from"]);
			}
		}
		$ol = new object_list($filter);
		return $ol;
	}


}

?>
