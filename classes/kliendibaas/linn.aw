<?php
/*
	@tableinfo kliendibaas_linn index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@default table=kliendibaas_linn

	@property name type=textbox size=20
	@caption Nimetus

	@property location type=textarea
	@caption Asukoha kirjeldus
*/

class linn extends class_base
{
	function linn()
	{
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
