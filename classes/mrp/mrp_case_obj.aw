<?php

class mrp_case_obj extends _int_object
{
	public function get_job_count($state = array(), $resource =  array())
	{
		$params = array ("type" => "RELTYPE_MRP_PROJECT_JOB");
/* juhuks kui see connectionit otside prop param kunagi teostatakse
		if (count($resource))
		{
			$params["to.resource"] = $resource;
		}

 */
		 $connections = $this->connections_from ($params);
		return count ($connections);
	}
}

?>
