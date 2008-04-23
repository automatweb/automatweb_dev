<?php

class crm_section_obj extends _int_object
{
	function get_job_offers()
	{
		$r = new object_list;
		foreach($this->connections_to(array("from.class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER, "type" => "RELTYPE_SECTION")) as $conn)
		{
			$r->add($conn->prop("from"));
		}
		return $r;
	}
}

?>
