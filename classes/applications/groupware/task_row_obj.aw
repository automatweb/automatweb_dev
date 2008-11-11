<?php
/*
@classinfo maintainer=markop
*/
class task_row_obj extends _int_object
{
	function set_prop($pn, $pv)
	{
		switch($pn)
		{
			case "time_guess":
			case "time_real":
			case "time_to_cust":
				$pv = str_replace("," , "." , $pv);
				break;
		}

		return parent::set_prop($pn, $pv);
	}

}
?>