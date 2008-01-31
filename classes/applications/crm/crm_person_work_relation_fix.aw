<?php
class crm_person_work_relation_fix extends _int_object
{
	function __construct($param)
	{
		parent::_int_object($param);
	}
	
	function set_prop($var, $val)
	{
		if($var == "org")
		{
			foreach(parent::connections_from(array("type" => 1)) as $conn)
			{				
				if($conn->prop("to") != $val)
				{
					$conn->delete();
				}
			}
		}
		if($var == "section")
		{
			foreach(parent::connections_from(array("type" => 7)) as $conn)
			{
				if($conn->prop("to") != $val)
				{
					$conn->delete();
				}
			}
		}
		if($var == "profession")
		{
			foreach(parent::connections_from(array("type" => 3)) as $conn)
			{
				if($conn->prop("to") != $val)
				{
					$conn->delete();
				}
			}
		}
		if($var == "section2")
		{
			parent::set_prop("section", $val);
		}
		return parent::set_prop($var, $val);
	}
}
?>
