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

/*

CREATE TABLE `kliendibaas_tegevusala` (
  `oid` int(11) NOT NULL default '0',
  `kood` varchar(30) default NULL,
  `tegevusala` text,
  `tegevusala_en` text,
  `kirjeldus` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`),
  KEY `kood_i` (`kood`)
) TYPE=MyISAM
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
