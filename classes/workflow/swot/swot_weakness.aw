<?php

/*

@classinfo syslog_type=ST_SWOT_WEAKNESS

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property comment type=textarea cols=50 rows=5 field=comment
@caption Kommentaar

@property clf type=select field=meta method=serialize
@caption Klassifikaator


@property status type=status field=status
@caption Staatus

*/

classload("workflow/swot/swot_type");
class swot_weakness extends swot_type
{
	function swot_weakness()
	{
		$this->init(array(
			'clid' => CL_SWOT_WEAKNESS
		));
	}
}
?>
