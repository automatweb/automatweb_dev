<?php
// $Header: /home/cvs/automatweb_dev/classes/workflow/swot/Attic/swot_strength.aw,v 1.4 2003/12/07 15:27:49 duke Exp $
/*
@classinfo syslog_type=ST_SWOT_STRENGTH

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
			'clid' => CL_SWOT_STRENGTH
		));
	}
}
?>
