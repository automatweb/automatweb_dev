<?php
/*
	@tableinfo kliendibaas_linn index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=textbox size=20
	@caption Linna nimetus

	@property comment type=textarea cols=40 rows=3 table=objects field=comment
	@caption Kommentaar

	@classinfo no_status=1 syslog_type=ST_CRM_CITY
	
	//@property location type=textarea
	//@caption Asukoha kirjeldus

	//@default table=kliendibaas_linn	
	
*/
/*
CREATE TABLE `kliendibaas_linn` (
  `oid` int(11) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `comment` text,
  `location` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;
*/
class crm_city extends class_base
{
	function crm_city()
	{
		$this->init(array(
			'clid' => CL_CRM_CITY,
		));
	}
};
?>
