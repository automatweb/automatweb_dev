<?php

class crm_phone_obj extends _int_object
{
	/**
		@param id required type=oid,array(oid)
	**/
	function get_persons($arr)
	{
		$ret = new object_list;

		// The phone might be connected to the person via work relation.
		$cs = connection::find(array(
			"from" => array(),
			"to" => $arr["id"],
			"type" => "RELTYPE_PHONE",
			"from.class_id" => CL_CRM_PERSON_WORK_RELATION,
		));
		if(count($cs) > 0)
		{
			$wrids = array();
			foreach($cs as $c)
			{
				$wrids[] = $c["from"];
			}
			$cs = connection::find(array(
				"from" => array(),
				"to" => $wrids,
				"from.class_id" => CL_CRM_PERSON,
			));
			foreach($cs as $c)
			{
				$ret->add($c["from"]);
			}
		}
		$cs = connection::find(array(
			"from" => array(),
			"to" => $arr["id"],
			"type" => "RELTYPE_PHONE",
			"from.class_id" => CL_CRM_PERSON,
		));
		foreach($cs as $c)
		{
			$ret->add($c["from"]);
		}

		return $ret;
	}

	function set_prop($k, $v)
	{
		if($k == "number")
		{
			parent::set_prop("number_without_crap", str_replace(array(" ", "-", "(", ")") , "", $v));
		}
		return parent::set_prop($k, $v);
	}
}

?>
