<?php
/*
	@tableinfo kliendibaas_ettevotlusvorm index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default table=kliendibaas_ettevotlusvorm

	@property vorm type=textbox size=10
	@caption vorm

	@classinfo objtable=kliendibaas_ettevotlusvorm
	@classinfo objtable_index=oid
*/

class ettevotlusvorm extends class_base
{

	function ettevotlusvorm()
	{
//		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_ETTEVOTLUSVORM,
		));
	}
	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = true;
		switch($data["name"])
		{
			case 'status':
				$retval=PROP_IGNORE;
			break;
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'alias':
				$retval=PROP_IGNORE;
			break;
		}

		return  $retval;
	}
}
?>
