<?php
/*
//	@tableinfo kliendibaas_ettevotlusvorm index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

//	@default table=kliendibaas_ettevotlusvorm

//	@property vorm type=textbox size=10
//	@caption vorm
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
			'clid' => CL_CORPFORM,
		));
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case 'status':
				$retval=PROP_IGNORE;
			break;
		}
		return  $retval;
	}
}
?>
