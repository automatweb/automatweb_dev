<?php
/*
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_riik

	@property name type=textbox size=20
	@caption Nimetus (tmp)

	@property name_en type=textbox size=20
	@caption Nimetus (i.k.)

	@property name_native type=textbox size=20
	@caption Niemtus kohalikus keeles

	@property languages type=textbox size=30
	@caption Keeled
	
	@property lyhend type=textbox size=10
	@caption Lühend

	@property location type=textarea
	@caption Asukoha kirjeldus

	@classinfo objtable=kliendibaas_riik
	@classinfo objtable_index=oid
*/

class riik extends class_base
{
	function riik()
	{
//		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_RIIK,
		));
	}
}
?>
