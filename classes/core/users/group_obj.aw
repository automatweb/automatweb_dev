<?php
/*
@classinfo  maintainer=kristo
*/

class group_object extends _int_object
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
