<?php
/*
	@tableinfo kliendibaas_linn index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=textbox size=20
	@caption Linna nimetus

	@default field=meta
	@property country type=relpicker reltype=RELTYPE_COUNTRY
	@caption Riik
	
	@property area type=relpicker reltype=RELTYPE_AREA
	@caption Piirkond
	
	@property county type=relpicker reltype=RELTYPE_COUNTY
	@caption Maakond

	@property comment type=textarea cols=40 rows=3 table=objects field=comment
	@caption Kommentaar


@reltype COUNTRY value=1 clid=CL_CRM_COUNTRY
@caption Riik

@reltype AREA value=2 clid=CL_CRM_AREA
@caption Piirkond

@reltype COUNTY value=3 clid=CL_CRM_COUNTY
@caption Maakond




	@classinfo no_status=1 syslog_type=ST_CRM_CITY maintainer=markop
	
	//@property location type=textarea
	//@caption Asukoha kirjeldus

	//@default table=kliendibaas_linn	
	
*/
/*
CREATE TABLE `kliendibaas_linn` (
  `oid` int(11) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `comment` text,
  `location` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;
*/
class crm_city extends class_base
{
	function crm_city()
	{
		$this->init(array(
			'clid' => CL_CRM_CITY,
		));
	}

	/** Returns object list of personnel_management_job_offer objects that are connected to the city.

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
};
?>
