<?php
/*
	@tableinfo kliendibaas_linn index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_linn

	@property name type=textbox size=20
	@caption Nimetus

	@property comment type=textarea
	@caption Keeled

	@property location type=textarea
	@caption Asukoha kirjeldus

	@classinfo objtable=kliendibaas_linn
	@classinfo objtable_index=oid
*/

class linn extends class_base
{
	function linn()
	{
//		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_LINN,
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