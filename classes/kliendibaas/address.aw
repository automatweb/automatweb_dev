<?php
/*
	@classinfo relationmgr=yes
	@tableinfo kliendibaas_address index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=text
	@caption Nimi
	
	@default table=kliendibaas_address
	
	@property aadress type=textbox size=50 maxlength=100
	@caption Tänav/Küla
	
	@property postiindeks type=textbox size=5 maxlength=5
	@caption Postinindex
	
	@property linn type=relpicker reltype=LINN table=kliendibaas_address
	@caption Linn/Vald/Alev

	@property maakond type=relpicker reltype=MAAKOND table=kliendibaas_address
	@caption Maakond

	@property riik type=relpicker reltype=RIIK table=kliendibaas_address
	@caption Riik
	
	@property telefon type=relpicker reltype=TELEFON
	@caption Telefon

	@property mobiil type=relpicker reltype=MOBIIL
	@caption Mobiiltelefon

	@property faks type=relpicker reltype=FAKS
	@caption Faks

	@property piipar type=textbox size=20 maxlength=20
	@caption Piipar
	
	@property e_mail type=relpicker reltype=EMAIL
	@caption E-mail

	@property kodulehekylg type=relpicker reltype=WWW
	@caption Kodulehekülg
			
	@property comment type=textarea cols=65 rows=3 table=objects field=comment
	@caption Kommentaar
	
*/

/*

CREATE TABLE `kliendibaas_address` (
  `oid` int(11) NOT NULL default '0',
  `name` varchar(200) default NULL,
  `tyyp` int(11) default NULL,
  `riik` int(11) default NULL,
  `linn` int(11) default NULL,
  `maakond` int(11) default NULL,
  `postiindeks` varchar(5) default NULL,
  `telefon` varchar(20) default NULL,
  `mobiil` varchar(20) default NULL,
  `faks` varchar(20) default NULL,
  `piipar` varchar(20) default NULL,
  `aadress` text,
  `e_mail` varchar(255) default NULL,
  `kodulehekylg` varchar(255) default NULL,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

define('LINN',1);
define('RIIK',2);
define('MAAKOND',3);
define('BELONGTO',4);
define('EMAIL',5);
define('WWW',6);
define('TELEFON',7);
define('MOBIIL',8);
define('FAKS',9);
//define('',);

class address extends class_base
{
	function address()
	{
		$this->init(array(
			'clid' => CL_ADDRESS,
		));
	}

	function callback_get_rel_types()
	{
		return array(
			LINN => 'Linn',
			RIIK => 'Riik',
			MAAKOND => 'Maakond',
			BELONGTO => 'Seosobjekt',
			EMAIL => 'E-mail',
			WWW => 'Kodulehekülg',
			TELEFON => 'Telefon',
			MOBIIL => 'Mobiil',
			FAKS => 'Faks',
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
                switch($args["reltype"])
                {
			case LINN:
				$retval = array(CL_LINN);
			break;
			case RIIK:
				$retval = array(CL_RIIK);
			break;
			case MAAKOND:
				$retval = array(CL_MAAKOND);
			break;
			case BELONGTO:
				$retval = array(CL_ISIK, CL_FIRMA);
			break;
			case EMAIL:
				$retval = array(CL_EXTLINK);
			break;
			case WWW:
				$retval = array(CL_EXTLINK);
			break;
			case TELEFON:
				$retval = array(CL_PHONE);
			break;
			case MOBIIL:
				$retval = array(CL_PHONE);
			break;
			case FAKS:
				$retval = array(CL_PHONE);
			break;
		};
		return $retval;
	}

	function get_property($args)
	{
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
			case 'name':
				$retval=PROP_IGNORE;
			break;
		}
		return $retval;
	}
	
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		$form = &$args["form_data"];
		$obj = &$args["obj"];

		switch($data["name"])
		{
			case 'riik':
				
				if ($form['aadress'])
					$name[] = $form['aadress'];
				if ($form['linn'])
					$name[] = $this->db_fetch_field('select name from objects where oid="'.$form['linn'].'"','name');
				if ($form['maakond'])
					$name[] = $this->db_fetch_field('select name from objects where oid="'.$form['maakond'].'"','name');
				
				if (count($name) < 1)
				{
					if ($form['e_mail'])
						$name[] = $form['e_mail'];
				}
				
				if (count($name) < 1)
				{
					if ($form['telefon'])
						$name[] = 'tel:'.$form['telefon'];
				}
					
				$obj['name'] =  implode(', ',$name);
			break;
		};
		return $retval;
	}	
}
?>
