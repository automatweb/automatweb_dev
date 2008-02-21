<?php
/*
@classinfo  maintainer=kristo
*/

class group_obj extends _int_object
{
	function name()
	{
		$rv =  parent::name();
		if ($rv == "")
		{
			$rv = parent::prop("name");
		}
		return $rv;
	}
}

?>
