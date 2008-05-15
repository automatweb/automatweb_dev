<?php
/*
@classinfo maintainer=markop
*/
class project_obj extends _int_object
{
	function set_prop($pn, $pv)
	{
		switch($pn)
		{
			case "participants":
				$set = (array)$this->prop("implementor");
				foreach((array)$this->prop("orderer") as $val)
				{
					$set[$val] = $val;
				}

				foreach($set as $id)
				{
					$this->connect(array(
						"to" => $id,
						"type" => "RELTYPE_PARTICPANT"
					));
					$pv[$id] = $id;
				}
				break;
		}

		return parent::set_prop($pn, $pv);
	}

	function save()
	{
		$new = !is_oid($this->id());
		$rv = parent::save();
		if ($new && !count($this->connections_from(array("type" => "RELTYPE_IMPLEMENTOR"))))
		{
			$c = get_current_company();
			$this->connect(array(
				"to" => $c->id(),
				"type" => "RELTYPE_IMPLEMENTOR"
			));
		}
		return $rv;
	}
		
	function get_tasks()
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_TASK,
			"project" => $this->id(),
		);
		$ol = new object_list($filter);
		return $ol;
	}

	function get_bugs()
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_BUG,
			"project" => $this->id(),
		);
		$ol = new object_list($filter);
		return $ol;
	}

	function get_calls()
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_CRM_CALL,
			"project" => $this->id(),
		);
		$ol = new object_list($filter);
		return $ol;
	}

	function get_meetings()
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_CRM_MEETING,
			"project" => $this->id(),
		);
		$ol = new object_list($filter);
		return $ol;
	}
	
	function get_products()
	{
		$ol = new object_list($this->connections_from(array(
				"type" => "RELTYPE_PRODUCT"
		)));
		// = new object_list($filter);
		return $ol;
	}
}
?>
