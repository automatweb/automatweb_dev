<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_job_wanted.aw,v 1.3 2005/04/21 08:48:49 kristo Exp $
// personnel_management_job_wanted.aw - T&ouml;&ouml; soov 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_WANTED relationmgr=yes
@tableinfo personnel_management_job_wanted master_table=objects master_index=oid index=oid

@default table=personnel_management_job_wanted
@default group=general

@property name type=textbox table=objects
@caption Ametinimetus

@property palgasoov type=textbox size=5 datatype=int
@caption Palgasoov

@property valdkond type=classificator multiple=1 orient=vertical store=connect reltype=RELTYPE_TEGEVUSVALDKOND field=meta method=serialize table=objects
@caption Tegevusala

@property liik type=classificator multiple=1 method=serialize store=connect reltype=RELTYPE_LIIK field=meta method=serialize table=objects
@caption T&ouml;&ouml; liik

@property asukoht type=relpicker multiple=1 automatic=1 reltype=RELTYPE_LINN orient=vertical store=connect field=meta method=serialize table=objects
@caption T&ouml;&ouml;tamise piirkond

@property koormus type=classificator multiple=1 orient=vertical store=connect reltype=RELTYPE_KOORMUS field=meta method=serialize table=objects
@caption T&ouml;&ouml; koormus

@property lisainfo type=textarea
@caption Lisainfo soovitava t&ouml;&ouml; kohta

@property sbutton type=submit store=no
@caption Lisa

------------SEOSED-------------------
@reltype LINN value=1 clid=CL_CRM_CITY
@caption Linn

@reltype LIIK value=2 clid=CL_META
@caption liik

@reltype TEGEVUSVALDKOND value=3 clid=CL_META
@caption Tegevusala

@reltype KOORMUS value=4 clid=CL_META
@caption Koormus

*/

class personnel_management_job_wanted extends class_base
{
	function personnel_management_job_wanted()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_PERSONNEL_MANAGEMENT_JOB_WANTED
		));
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch ($prop["name"])
		{
			case "sbutton":
				if(is_numeric($_GET["eoid"]))
				{
					$prop["caption"] = "Muuda";
				}
			break;
			
		}
	}
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
		return $retval;
	}
}
?>
