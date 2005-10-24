<?php
/*
	@tableinfo kliendibaas_riik index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=textbox size=20
	@caption Riigi nimetus

	@property area_code type=textbox size=20 field=meta method=serialize
	@caption Suunakood

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_riik

	@property name_en type=textbox size=20
	@caption Nimetus inglise keeles

	@property name_native type=textbox size=20
	@caption Nimetus kohalikus keeles

	@classinfo no_status=1 syslog_type=ST_SRM_COUNTRY

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
