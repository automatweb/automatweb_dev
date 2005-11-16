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
}
?>