<?php
/*
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

	@classinfo objtable=kliendibaas_toode
	@classinfo objtable_index=oid
*/

class toode extends class_base
{
	function toode()
	{
//		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_TOODE,
		));
	}
}
?>
