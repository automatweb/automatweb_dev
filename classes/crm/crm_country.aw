<?php
/*
	@tableinfo kliendibaas_riik index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_riik

	@property name type=textbox size=20
	@caption Riigi nimetus

	@property name_en type=textbox size=20
	@caption Nimetus inglise keeles

	@property name_native type=textbox size=20
	@caption Niemtus kohalikus keeles

//	@property languages type=textbox size=30
//	@caption Keeled

//	@property lyhend type=textbox size=10
//	@caption Lühend

//	@property location type=textarea
//	@caption Asukoha kirjeldus
*/

/*

CREATE TABLE `kliendibaas_riik` (
  `oid` int(11) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `comment` text,
  `name_en` text,
  `name_native` text,
  `languages` text,
  `location` text,
  `lyhend` varchar(20) default NULL,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

class crm_country extends class_base
{
	function crm_country()
	{
		$this->init(array(
			'clid' => CL_CRM_COUNTRY,
		));
	}
}
?>
