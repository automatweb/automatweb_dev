<?php
/*
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_riik

	@property name type=textbox size=20
	@caption Nimetus (tmp)

	@property comment type=textarea
	@caption Keeled

	@property location type=textarea
	@caption Asukoha kirjeldus

	@classinfo objtable=kliendibaas_riik
	@classinfo objtable_index=oid
*/

class riik extends aw_template
{
	function riik()
	{
		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_RIIK,
		));
	}
}
?>