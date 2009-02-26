<?php

class shop_purchase_manager_workspace_obj extends _int_object
{
	public function get_warehouse_ids()
	{
		$warehouse_ids = array();
		foreach ($this->connections_from(array("type" => "RELTYPE_WAREHOUSE")) as $c)
		{
			$warehouse_ids[] = $c->prop("to");
		}
		return $warehouse_ids;
	}
}

?>
