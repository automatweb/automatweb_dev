<?php
/*
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

class tegevusala extends aw_template
{
	function tegevusala()
	{
		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_TEGEVUSALA,
		));
	}
}
?>