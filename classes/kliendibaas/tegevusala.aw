<?php
/*
	@tableinfo kliendibaas_tegevusala index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_tegevusala

	@property kood type=textbox size=8
	@caption Tegevusala kood

	@property tegevusala type=textbox size=40
	@caption Tegevusala

	@property tegevusala_en type=textbox size=40
	@caption Tegevusala (i.k.)

	@property kirjeldus type=textarea
	@caption Tegevusala kirjeldus
*/

class tegevusala extends class_base
{
	function tegevusala()
	{
		$this->init(array(
			'clid' => CL_TEGEVUSALA,
		));
	}
	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
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
