<?php
/*
	@tableinfo kliendibaas_linn index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@default table=kliendibaas_linn

	@property name type=textbox size=20
	@caption Nimetus

	@property location type=textarea
	@caption Asukoha kirjeldus
*/
/*
CREATE TABLE `kliendibaas_linn` (
  `oid` int(11) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `comment` text,
  `location` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM
*/
class linn extends class_base
{
	function linn()
	{
		$this->init(array(
			'clid' => CL_LINN,
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
