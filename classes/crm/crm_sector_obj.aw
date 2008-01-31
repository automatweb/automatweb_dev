<?php
/*
@classinfo  maintainer=markop
*/

class crm_sector_obj extends _int_object
{
	function trans_get_val($prop)
	{
		return parent::trans_get_val($prop == "name" ? "tegevusala" : $prop);
	}
}

?>
