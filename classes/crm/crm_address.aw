<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_address.aw,v 1.2 2003/11/20 21:21:49 duke Exp $
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
	
	@property linn type=relpicker reltype=RELTYPE_LINN 
	@caption Linn/Vald/Alev

	@property maakond type=relpicker reltype=RELTYPE_MAAKOND 
	@caption Maakond

	@property riik type=relpicker reltype=RELTYPE_RIIK 
	@caption Riik
	
	@property telefon type=relpicker reltype=RELTYPE_TELEFON
	@caption Telefon

	@property mobiil type=relpicker reltype=RELTYPE_MOBIIL
	@caption Mobiiltelefon

	@property faks type=relpicker reltype=RELTYPE_FAKS
	@caption Faks

	@property piipar type=textbox size=20 maxlength=20
	@caption Piipar
	
	@property e_mail type=relpicker reltype=RELTYPE_EMAIL
	@caption E-mail

	@property kodulehekylg type=relpicker reltype=RELTYPE_WWW
	@caption Kodulehekülg
			
	@property comment type=textarea cols=65 rows=3 table=objects field=comment
	@caption Kommentaar

	@classinfo no_status=1
	
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

/* 
@reltype LINN value=1 clid=CL_CRM_CITY
@caption Linn

@reltype RIIK value=2 clid=CL_CRM_COUNTRY
@caption Riik

@reltype MAAKOND value=3 clid=CL_CRM_COUNTY
@caption Maakond

@reltype BELONGTO value=4 clid=CL_CRM_PERSON,CL_CRM_COMPANY
@caption Seosobjekt

@reltype EMAIL value=5 clid=CL_EXTLINK
@caption E-mail

@reltype WWW value=6 clid=CL_EXTLINK
@caption Koduleht

@reltype TELEFON value=7 clid=CL_CRM_PHONE
@caption Telefon

@reltype MOBIIL value=8 clid=CL_CRM_PHONE
@caption Mobiil

@reltype FAKS value=9 clid=CL_CRM_PHONE
@caption Faks
*/

class crm_address extends class_base
{
	function crm_address()
	{
		$this->init(array(
			'clid' => CL_CRM_ADDRESS,
		));
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		$form = &$arr["request"];

		switch($data["name"])
		{
			case 'riik':
				// generate a name for the object
				$name = array();	
				if (!empty($form["aadress"]))
				{
					$name[] = $form['aadress'];
				};

				if (!empty($form["linn"]))
				{
					$city_obj = new object($form["linn"]);
					$name[] = $city_obj->name();
				};
				if (!empty($form["maakond"]))
				{
					$county_obj = new object($form["maakond"]);
					$name[] = $county_obj->name();
				};
				
				if (count($name) < 1)
				{
					if (!empty($form["e_mail"]))
					{
						$name[] = $form["e_mail"];
					};
				}
				
				if (count($name) < 1)
				{
					if (!empty($form["telefon"]))
					{
						$name[] = 'tel:'.$form["telefon"];
					};
				}

				$arr["obj_inst"]->set_name(join(", ",$name));
				break;
		};
		return $retval;
	}	
};
?>
