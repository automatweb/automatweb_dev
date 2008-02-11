<?php

class crm_company_obj extends _int_object
{
	function set_prop($name,$value)
	{
		parent::set_prop($name,$value);
	}

	function get_undone_orders()
	{
		$filter = array(
			"class_id" => CL_SHOP_ORDER,
			"orderer_company" => $this->id(),
		);
		$ol = new object_list($filter);
		return $ol;
	}
}

?>
