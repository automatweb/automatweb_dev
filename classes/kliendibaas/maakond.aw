<?php
/*
	@tableinfo kliendibaas_maakond index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=textbox size=20
	@caption Maakonna nimetus
	
@property comment type=textarea cols=40 rows=3 table=objects field=comment
@caption Kommentaar
	
//	@default table=kliendibaas_maakond

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
class maakond extends class_base
{
	function maakond()
	{
		$this->init(array(
			'clid' => CL_MAAKOND,
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
			case 'status':
				$retval=PROP_IGNORE;
			break;
			
		}
		return  $retval;
	}
}
?>
