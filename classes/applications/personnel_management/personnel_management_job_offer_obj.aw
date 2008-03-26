<?php

class personnel_management_job_offer_obj extends _int_object
{
	function get_candidates()
	{
		$ret = new object_list();

		foreach(parent::connections_from(array("type" => 1)) as $conn)
		{
			if(!isset($arr["status"]) || $conn->conn["to.status"] == $arr["status"])
			{
				$to = $conn->to();
				if($this->can("view", $to->prop("person")))
				{
					$ret->add($to->prop("person"));
				}
			}
		}

		return $ret;
	}
}

?> 