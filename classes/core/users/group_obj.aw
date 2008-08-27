<?php
/*
@classinfo  maintainer=kristo
*/

class group_obj extends _int_object
{
	function name()
	{
		$rv =  parent::name();
		if ($rv == "")
		{
			$rv = parent::prop("name");
		}
		return $rv;
	}

	function set_prop($k, $v)
	{
		if ($k == "name")
		{
			$this->set_name($v);
		}
		return parent::set_prop($k, $v);
	}

	function set_name($v)
	{
		parent::set_prop("name", $v);
		return parent::set_name($v);
	}

	function get_member_count()
	{
		if (!is_oid($this->id()))
		{
			return 0;
		}
		$odl = new object_data_list(
			array(
				"lang_id" => array(),
				"site_id" => array(),
				"class_id" => CL_USER,
				"parent" => $this->id()
			),
			array(
				"" => array(new obj_sql_func(OBJ_SQL_COUNT, "cnt", "oid"))
			)
		);
		$r = $odl->arr();
		return $r[0]["cnt"];
	}

	function get_group_persons()
	{
		$persons = new object_list();
		$ol = new object_list(array(
			"class_id" => CL_USER,
			"parent" => $this->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		$user_inst = get_instance(CL_USER);
		foreach($ol->arr() as $o)
		{
			$persons->add($user_inst->get_person_for_user($o));
		}
		return $persons;
	}


}

?>
