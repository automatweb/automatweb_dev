<?php

class crm_db_obj extends _int_object
{
	function prop($k)
	{
		$dirs = array("dir_comment");
		if(in_array($k) && !is_oid(parent::prop($k)))
		{
			return parent::prop("dir_default");
		}
		return parent::prop($k);
	}
}

?>
