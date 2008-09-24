<?php

class personnel_management_obj extends _int_object
{
	function prop($k)
	{
		if($k == "perpage" && !is_numeric(parent::prop($k)))
		{
			return 20;
		}
		return parent::prop($k);
	}
}

?>
