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

	@property pohitegevus type=popup_objmgr clid=CL_TEGEVUSALA height=550 width=650
	@caption põhitegevus

	@property korvaltegevused type=popup_objmgr clid=CL_TEGEVUSALA multiple=1 method=serialize field=meta table=objects
//	@property korvaltegevused type=popup_objmgr method=serialize multiple=1
	@caption kõrvaltegevused

	@property ettevotlusvorm type=popup_objmgr clid=CL_ETTEVOTLUSVORM
	@caption ettevõtlusvorm

	@property tooted type=popup_objmgr clid=CL_TOODE multiple=1 method=serialize field=meta table=objects
//	@property tooted type=popup_objmgr clid=CL_TOODE multiple=1 field=meta method=serialize table=objects
	@caption tooted

	@property kaubamargid type=textarea cols=25 rows=2
	@caption kaubamärgid

	@property contact type=popup_objmgr clid=CL_ADDRESS change=1
	@caption aadress

	@property tegevuse_kirjeldus type=textarea cols=25 rows=2
	@caption tegevuse kirjeldus

	@property firmajuht type=popup_objmgr change=1
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
/*
function callback_submit_relation_list($a)
{
print_r($a);die();
}*/
	function firma()
	{
		$this->init(array(
			'clid' => CL_FIRMA,
		));
	}
/*
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$retval = PROP_OK;
		switch($data['name'])
		{
//			case 'korvaltegevused':
///				$form['korvaltegevused'] = unserialize($this->make_keys($form['korvaltegevused']));
//				print_r($args);
//		die;
//		$retval = PROP_IGNORE;
			break;
		};
		return $retval;
	}
*/
	function get_property($args)
	{
		//print_r($args);
			//die();
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
/*			case 'korvaltegevused':
				$data['value'] = $args['objdata']['korvaltgevused'];
				//print_r($data['value']);
				//die();
			break;
*/

		};
		return $retval;
	}
}
?>
