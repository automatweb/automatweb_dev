<?php
/*
	@tableinfo kliendibaas_tegevusala index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_tegevusala

	@property kood type=textbox size=8
	@caption Toote kood

	@property tegevusala type=textbox size=40
	@caption Tegevusala

	@property tegevusala_en type=textbox size=40
	@caption Tegevusala (i.k.)

	@property kirjeldus type=textarea
	@caption Toote kirjeldus

	@classinfo objtable=kliendibaas_tegevusala
	@classinfo objtable_index=oid
*/

class tegevusala extends class_base
{
	function tegevusala()
	{
//		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_TEGEVUSALA,
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
