<?php
/*
	@classinfo relationmgr=yes
	@tableinfo kliendibaas_address index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@default table=kliendibaas_address

	@property postiindeks type=textbox size=5 maxlength=5
	@caption postinindex

	@property telefon type=textbox size=10 maxlength=15
	@caption telefon

	@property mobiil type=textbox size=10 maxlength=15
	@caption telefon

	@property faks type=textbox size=10 maxlength=20
	@caption faks

	@property piipar type=textbox size=10 maxlength=20
	@caption piipar

	@property aadress type=textbox size=30 maxlength=100
	@caption aadress

	@property e_mail type=textbox size=25 maxlength=100
	@caption e-mail

	@property kodulehekylg type=textbox size=40 maxlength=300
	@caption kodulehekülg

	@property linn type=relpicker reltype=LINN
	@caption linn

	@property maakond  type=relpicker reltype=MAAKOND
	@caption maakond

	@property riik type=relpicker reltype=RIIK
	@caption riik
*/

define('LINN',1);
define('RIIK',2);
define('MAAKOND',3);

class address extends class_base
{

	function callback_get_rel_types()
	{
		return array(
			LINN => 'linn',
			RIIK => 'riik',
			MAAKOND => 'maakond',
		);
	}

	function address()
	{
		$this->init(array(
			'clid' => CL_ADDRESS,
		));
	}

	function get_property($args)
	{
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
		return $retval;
	}
}
?>
