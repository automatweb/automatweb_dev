<?php

/*

@classinfo syslog_type=ST_SWOT_STRENGTH

@groupinfo general caption=�ldine

@default table=objects
@default group=general

@property comment type=textarea cols=50 rows=5 field=comment
@caption Kommentaar

@property clf type=select field=meta method=serialize
@caption Klassifikaator

*/

classload("workflow/swot/swot_type");
class swot_strength extends swot_type
{
	function swot_strength()
	{
		$this->init(array(
			'tpldir' => 'workflow/swot/swot_strength',
			'clid' => CL_SWOT_STRENGTH
		));
	}
}
?>
