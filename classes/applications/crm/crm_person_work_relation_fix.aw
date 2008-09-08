<?php
class crm_person_work_relation_fix extends _int_object
{
	function set_prop($var, $val)
	{
		$html_allowed = array();
		if(!in_array($var, $html_allowed))
		{
			$val = htmlspecialchars($val);
		}

		if($var == "org" && is_oid($this->id()))
		{
			foreach(parent::connections_from(array("type" => 1)) as $conn)
			{
				if($conn->prop("to") != $val)
				{
					$conn->delete();
				}
			}
		}
		if($var == "section" && is_oid($this->id()))
		{
			foreach(parent::connections_from(array("type" => 7)) as $conn)
			{
				if($conn->prop("to") != $val)
				{
					$conn->delete();
				}
			}
		}
		if($var == "profession" && is_oid($this->id()))
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
