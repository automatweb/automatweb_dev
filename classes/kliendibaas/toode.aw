<?php
/*
	@tableinfo kliendibaas_toode index=oid master_table=objects master_index=oid
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@default table=kliendibaas_toode

	@property kood type=textbox size=8
	@caption Toote kood

	@property toode type=textbox size=40
	@caption Toode

	@property toode_en type=textbox size=40
	@caption Toode (i.k.)

	@property kirjeldus type=textarea
	@caption Toote kirjeldus
*/


/*
CREATE TABLE `kliendibaas_toode` (
  `oid` int(11) NOT NULL default '0',
  `kood` varchar(30) default NULL,
  `toode` text,
  `toode_en` text,
  `kirjeldus` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`),
  KEY `kood_i` (`kood`)
) TYPE=MyISAM

*/

class toode extends class_base
{
	function toode()
	{
		$this->init(array(
			'clid' => CL_TOODE,
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
