<?php
/*
	@tableinfo kliendibaas_toode index=oid master_table=objects master_index=oid
	@default table=objects
	@default group=general

	@property toode type=textbox size=40 table=kliendibaas_toode
	@caption Toote nimetus

	@property toode_en type=textbox size=40 table=kliendibaas_toode
	@caption Inglise keelne tootenimetus
		
	@property comment type=textarea field=comment
	@caption Kirjeldus

	@property kood type=textbox size=8 table=kliendibaas_toode
	@caption Toote kood	
	
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
				$retval = PROP_IGNORE;
			break;
			case 'alias':
				$retval = PROP_IGNORE;
			break;
			case 'status':
				$retval=PROP_IGNORE;
			break;
			case 'name':
				$retval=PROP_IGNORE;
			break;			
		}
		return  $retval;
	}
	
	
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		$form = &$args["form_data"];
		$obj = &$args["obj"];		
		switch($data["name"])
		{
			case 'kood':
				$obj['name'] =  ($form['kood'] ? ''.$form['kood'].' ' : '').$form['toode'];
			break;
		};
		return $retval;
	}		
	
}
?>
