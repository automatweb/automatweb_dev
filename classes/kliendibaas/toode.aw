<?php
/*
	@tableinfo kliendibaas_toode index=oid master_table=objects master_index=oid
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_toode

	@property kood type=textbox size=8
	@caption Toote kood

	@property toode type=textbox size=40
	@caption Toode

	@property toode_en type=textbox size=40
	@caption Toode (i.k.)

	@property kirjeldus type=textarea
	@caption Toote kirjeldus
*/

class toode extends class_base
{
	function toode()
	{
		$this->init(array(
			'clid' => CL_TOODE,
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
