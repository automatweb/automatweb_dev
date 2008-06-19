<?php

class ml_member_obj extends _int_object
{
	function set_name($v)
	{
// this breaks e-mail sending!
//		$v = htmlspecialchars($v);
		return parent::set_name($v);
	}

	function set_prop($k, $v)
	{
		$html_allowed = array();
		if(!in_array($k, $html_allowed))
		{
			$v = htmlspecialchars($v);
		}
		return parent::set_prop($k, $v);
	}

	/**
		@attrib name=get_persons api=1 params=name
		@param id required type=oid,array(oid)
	**/
	function get_persons($arr)
	{
		$ret = new object_list;

		// The e-mail might be connected to the person via work relation.
		$cs = connection::find(array(
			"from" => array(),
			"to" => $arr["id"],
			"type" => "RELTYPE_EMAIL",
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
			"type" => "RELTYPE_EMAIL",
			"from.class_id" => CL_CRM_PERSON,
		));
		foreach($cs as $c)
		{
			$ret->add($c["from"]);
		}

		return $ret;
	}
}

?>
