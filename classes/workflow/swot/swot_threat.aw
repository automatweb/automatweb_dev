<?php

/*

@classinfo syslog_type=ST_SWOT_THREAT

@groupinfo general caption=�ldine

@default table=objects
@default group=general

@property comment type=textarea cols=50 rows=5 field=comment
@caption Kommentaar

@property clf type=select field=meta method=serialize
@caption Klassifikaator

*/

classload("workflow/swot/swot_type");
class swot_threat extends swot_type
{
	function swot_threat()
	{
		$this->init(array(
			'clid' => CL_SWOT_THREAT
		));
	}
}
?>
