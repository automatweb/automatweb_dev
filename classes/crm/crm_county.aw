<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_county.aw,v 1.8 2008/02/11 17:49:19 instrumental Exp $
/*
	@tableinfo kliendibaas_maakond index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=textbox size=20
	@caption Maakonna nimetus
		
	@default field=meta
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

	@classinfo no_status=1 maintainer=markop

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

	/** Returns object list of personnel_management_job_offer objects that are connected to the county.

	@attrib name=get_job_offers params=name api=1

	@param id required type=oid acl=view

	@param parent optional type=oid,array acl=view

	**/
	function get_job_offers($arr)
	{
		if(!is_array($arr["parent"]) && isset($arr["parent"]))
		{
			$arr["parent"] = array(0 => $arr["parent"]);
		}
		$o = obj($arr["id"]);
		$conns2 = $o->connections_to(array(
			"class" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
		));
		$ret = new object_list();
		foreach($conns2 as $conn2)
		{
			if(!isset($arr["parent"]) || in_array($conn2->conn["from.parent"], $arr["parent"]))
			{
				$ret->add($conn2->conn["from"]);
			}
		}
		return $ret;
	}
}
?>
