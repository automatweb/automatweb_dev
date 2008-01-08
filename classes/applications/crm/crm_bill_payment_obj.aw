<?php

class crm_bill_payment_obj extends _int_object
{
	function set_prop($name,$value)
	{
		if($name == "sum")
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_BILL,
				"lang_id" => array(),
				"CL_CRM_BILL.RELTYPE_PAYMENT.id" => $this->id(),
			));

			$bi = get_instance(CL_CRM_BILL);
			foreach($ol -> arr() as $o)
			{
				$sum = 0;
				foreach($o->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
				{
					$p = $conn->to();
					if($p -> id() == $this->id())
					{
						$sum = $sum + $value;
					}
					else
					{
						$sum = $sum + $p->prop("sum");
					}
				}
				$o->set_prop("partial_recieved", $sum);
				$o-> save();
			}
		}
		parent::set_prop($name,$value);
	}
}

?>
