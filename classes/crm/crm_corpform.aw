<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_corpform.aw,v 1.3 2004/06/10 11:49:25 duke Exp $
/*
//	@tableinfo kliendibaas_ettevotlusvorm index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

//	@default table=kliendibaas_ettevotlusvorm

//	@property vorm type=textbox size=10
//	@caption vorm
	
	@classinfo no_status=1
*/


/* deprecated, drop this table i guess
CREATE TABLE `kliendibaas_ettevotlusvorm` (
  `oid` int(11) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `vorm` varchar(255) default NULL,
  `comment` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

class crm_corpform extends class_base
{
	function crm_corpform()
	{
		$this->init(array(
			'clid' => CL_CRM_CORPFORM,
		));
	}

};
?>
