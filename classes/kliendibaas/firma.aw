<?php
/*
	@classinfo relationmgr=yes

	@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=textbox size=30 maxlenght=255
	@caption firma nimetus

	@default table=kliendibaas_firma

	@property reg_nr type=textbox size=10 maxlenght=20
	@caption registri nr

	@property pohitegevus type=relpicker reltype=POHITEGEVUS
	@caption põhitegevus

	@property ettevotlusvorm type=relpicker reltype=KORVALTEGEVUSED multiple=1
	@caption kõrvaltegevused

	@property ettevotlusvorm type=relpicker reltype=ETTEVOTLUSVORM
	@caption ettevõtlusvorm

	@property ettevotlusvorm type=relpicker reltype=TOOTED multiple=1
	@caption tooted

	@property kaubamargid type=textarea cols=25 rows=2
	@caption kaubamärgid

	@property contact type=relpicker reltype=ADDRESS
	@caption aadress

	@property tegevuse_kirjeldus type=textarea cols=25 rows=2
	@caption tegevuse kirjeldus

	@property firmajuht type=relpicker reltype=FIRMAJUHT
	@caption firmajuht
*/

define ('ETTEVOTLUSVORM',1);
define ('POHITEGEVUS',2);
define ('ADDRESS',3);
define ('FIRMAJUHT',4);
define ('KORVALTEGEVUSED',5);
define ('TOOTED',6);

class firma extends class_base
{
	function callback_get_rel_types()
	{
		return array(
			ETTEVOTLUSVORM => 'ettevõtlusvorm',
			POHITEGEVUS => 'põhitegevus',
			ADDRESS => 'kontakt aadress',
			FIRMAJUHT => 'firmajuht',
			KORVALTEGEVUSED => 'kõrvaltegevusalad',
			TOOTED => 'tooted',
		);
	}

	function firma()
	{
		$this->init(array(
			'clid' => CL_FIRMA,
		));
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = true;
		$meta=$args['obj']['meta'];
		$id=$args['obj']['oid'];
		$parent=$args['obj']['parent'];
		switch($data['name'])
		{
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'alias':
				$retval=PROP_IGNORE;
			break;
		};
		return $retval;
	}
}
?>