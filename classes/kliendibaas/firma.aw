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
	@caption p�hitegevus

	@property korvaltegevused type=popup_objmgr clid=CL_TEGEVUSALA multiple=1 method=serialize field=meta table=objects
//	@property korvaltegevused type=popup_objmgr method=serialize multiple=1
	@caption k�rvaltegevused

	@property ettevotlusvorm type=popup_objmgr clid=CL_ETTEVOTLUSVORM
	@caption ettev�tlusvorm

	@property tooted type=popup_objmgr clid=CL_TOODE multiple=1 method=serialize field=meta table=objects
//	@property tooted type=popup_objmgr clid=CL_TOODE multiple=1 field=meta method=serialize table=objects
	@caption tooted

	@property kaubamargid type=textarea cols=25 rows=2
	@caption kaubam�rgid

	@property contact type=popup_objmgr clid=CL_ADDRESS change=1
	@caption aadress

	@property tegevuse_kirjeldus type=textarea cols=25 rows=2
	@caption tegevuse kirjeldus

	@property firmajuht type=popup_objmgr change=1
	@caption firmajuht
*/


/*

CREATE TABLE `kliendibaas_firma` (
  `oid` int(11) NOT NULL default '0',
  `firma_nim` varchar(255) default NULL,
  `reg_nr` varchar(20) default NULL,
  `ettevotlusvorm` int(11) default NULL,
  `pohitegevus` int(11) default NULL,
  `tegevuse_kirjeldus` text,
  `contact` int(11) default NULL,
  `firmajuht` int(11) default NULL,
  `korvaltegevused` text,
  `kaubamargid` text,
  `tooted` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`),
  KEY `teg_i` (`pohitegevus`)
) TYPE=MyISAM;

*/

define ('ETTEVOTLUSVORM',1);
define ('POHITEGEVUS',2);
define ('ADDRESS',3);
define ('FIRMAJUHT',4);
define ('KORVALTEGEVUSED',5);
define ('TOOTED',6);

class firma extends class_base
{
	function firma()
	{
		$this->init(array(
			'clid' => CL_FIRMA,
		));
	}

	function callback_get_rel_types()
	{
		return array(
			ETTEVOTLUSVORM => 'Ettev�tlusvorm',
			POHITEGEVUS => 'P�hitegevus',
			ADDRESS => 'Kontaktaadress',
			FIRMAJUHT => 'Firmajuht',
			KORVALTEGEVUSED => 'K�rvaltegevusalad',
			TOOTED => 'Tooted',
		);
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
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
