<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_county.aw,v 1.5 2006/09/01 09:38:30 markop Exp $
/*
	@tableinfo kliendibaas_maakond index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general
	@default field=meta
	@property name type=textbox size=20
	@caption Maakonna nimetus
	
	@property country type=relpicker reltype=RELTYPE_COUNTRY
	@caption Riik
	
	@property area type=relpicker reltype=RELTYPE_AREA
	@caption Piirkond
	
	
@property comment type=textarea cols=40 rows=3 table=objects field=comment
@caption Kommentaar
	
@reltype COUNTRY value=1 clid=CL_CRM_COUNTRY
@caption Riik	
	
@reltype AREA value=2 clid=CL_CRM_AREA
@caption Piirkond		
	
//	@default table=kliendibaas_maakond

	@classinfo no_status=1

*/





/*



CREATE TABLE `kliendibaas_maakond` (
  `oid` int(11) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `comment` text,
  `location` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/
class crm_county extends class_base
{
	function crm_county()
	{
		$this->init(array(
			'clid' => CL_CRM_COUNTY,
		));
	}
}
?>
