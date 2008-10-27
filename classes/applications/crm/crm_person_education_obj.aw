<?php

class crm_person_education_obj extends _int_object
{
	function set_name($v)
	{
		$v = htmlspecialchars($v);
		return parent::set_name($v);
	}

	function set_comment($v)
	{
		$v = htmlspecialchars($v);
		return parent::set_comment($v);
	}

	function prop($k)
	{
		if($k == "degree" && !is_numeric(parent::prop($k)))
		{
			$degree_opts = array(
				"pohiharidus" => 1,
				"keskharidus" => 2,
				"keskeriharidus" => 4,
				"diplom" => 8,
				"bakalaureus" => 9,
				"magister" => 10,
				"doktor" => 11,
				"teadustekandidaat" => 12,
			);
			return $degree_opts[parent::prop($k)];
		}
		return parent::prop($k);
	}

	function set_prop($k, $v)
	{
		$html_allowed = array();
		if($k == "degree" && !is_numeric($v))
		{
			$degree_opts = array(
				"pohiharidus" => 1,
				"keskharidus" => 2,
				"keskeriharidus" => 4,
				"diplom" => 8,
				"bakalaureus" => 9,
				"magister" => 10,
				"doktor" => 11,
				"teadustekandidaat" => 12,
			);
			$v = $degree_opts[$v];
		}
		if(!in_array($k, $html_allowed))
		{
			$v = htmlspecialchars($v);
		}
		return parent::set_prop($k, $v);
	}
}

?>
