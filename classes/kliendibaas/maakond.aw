<?php
/*
	@tableinfo kliendibaas_maakond index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_maakond

	@property name type=textbox size=20
	@caption Nimetus (tmp)

	@property comment type=textarea
	@caption Keeled

	@property location type=textarea
	@caption Asukoha kirjeldus
*/

class maakond extends class_base
{
	function maakond()
	{
		$this->init(array(
			'clid' => CL_MAAKOND,
		));
	}
	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = true;
		switch($data["name"])
		{
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
