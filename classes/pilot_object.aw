<?php
/*
@default table=objects
@default group=general
@default field=meta
@default method=serialize

*/

class pilot_object extends class_base
{
	function pilot_object()
	{
		$this->init(array(
			'tpldir' => 'pilot_object',
			'clid' => CL_PILOT
		));
	}
};
?>
