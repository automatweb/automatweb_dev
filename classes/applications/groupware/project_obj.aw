<?php

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
		$rv = parent::save();
		if (!count($this->connections_from(array("type" => "RELTYPE_IMPLEMENTOR"))))
		{
			$c = get_current_company();
			$this->connect(array(
				"to" => $c->id(),
				"type" => "RELTYPE_IMPLEMENTOR"
			));
		}
		return $rv;
	}
}
?>