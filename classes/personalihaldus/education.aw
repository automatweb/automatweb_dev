<?php
// $Header: /home/cvs/automatweb_dev/classes/personalihaldus/Attic/education.aw,v 1.4 2004/06/07 13:02:35 sven Exp $
// education.aw - Education 
/*

@classinfo syslog_type=ST_EDUCATION relationmgr=yes no_status=1
@tableinfo personnel_management_education master_table=objects master_index=oid index=oid

@default table=personnel_management_education
@default group=general

@property kool type=textbox
@caption Haridusasutus

@property algusaasta type=select
@caption Sisseastumis aasta

@property loppaasta type=select
@caption L&otilde;petamise aasta


@property eriala type=classificator reltype=RELTYPE_ERIALA orient=vertical
@caption Eriala

@property eriala_txt type=textbox
@caption Eriala

@property teaduskond type=classificator reltype=RELTYPE_TEADUSKOND orient=vertical
@caption Teaduskond

@property oppekava type=classificator reltype=RELTYPE_OPPEKAVA orient=vertical
@caption &Otilde;ppekava

@property oppeaste type=classificator reltype=RELTYPE_OPPEASTE orient=vertical
@caption &Otilde;ppeaste

@property oppevorm type=classificator reltype=RELTYPE_OPPEVORM orient=vertical
@caption &Otilde;ppevorm

@property lisainfo_edu type=textarea
@caption Lisainfo

@property education_type type=hidden

@property date_from type=date_select
@caption Alates

@property date_to type=date_select
@caption Kuni

@property submit_edu type=submit store=no
@caption Lisa
-------------RELATIONS----------------
@reltype ERIALA value=1 clid=CL_META
@caption Tegevusvaldkond

@reltype TEADUSKOND value=2 clid=CL_META
@caption Teaduskond

@reltype OPPEKAVA value=3 clid=CL_META
@caption &Otilde;ppekava

@reltype OPPEASTE value=4 clid=CL_META
@caption &Otilde;ppeaste

@reltype OPPEVORM value=5 clid=CL_META
@caption &Otilde;pevorm

CREATE TABLE `personnel_management_education` (
`oid` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`kool` VARCHAR( 255 ) NOT NULL ,
`algusaasta` INT NOT NULL ,
`loppaasta` VARCHAR( 255 ) NOT NULL ,
`eriala` INT NOT NULL ,
`eriala_txt` VARCHAR( 255 ) NOT NULL ,
`teaduskond` INT NOT NULL ,
`oppekava` INT NOT NULL ,
`oppeaste` INT NOT NULL ,
`oppevorm` INT NOT NULL ,
`lisainfo_edu` TEXT NOT NULL ,
`education_type` INT NOT NULL ,
PRIMARY KEY ( `oid` )
);

*/

class education extends class_base
{
	function education()
	{
		$this->init(array(
			"clid" => CL_EDUCATION
		));
	}

	
	
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "submit_edu":
				if($_GET["eoid"])
				{
					$data["caption"] = "Muuda"; 
				}
			break;
			case "loppaasta":
				for($i=date("Y"); $i>date("Y") - 80; $i--){
					$data["options"][$i]=$i;
				}
			break;
			
			case "algusaasta":
				for($i=date("Y"); $i>date("Y") - 80; $i--){
					$data["options"][$i]=$i;
				}
			break;

			case "education_type":
				if(is_numeric($_GET["type"]))
				{
					$data["value"] = $_GET["type"];
				}
				else
				{	
					$data["value"] = 122002;	
				}
			break;

		};
		return $retval;
	}
	
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		}
		return $retval;
	}	
}
?>
