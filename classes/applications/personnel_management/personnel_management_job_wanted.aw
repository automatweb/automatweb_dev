<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_job_wanted.aw,v 1.4 2006/03/17 15:06:30 ahti Exp $
// personnel_management_job_wanted.aw - T&ouml;&ouml; soov 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_WANTED relationmgr=yes r2=yes no_comment=1

@tableinfo personnel_management_job_wanted master_table=objects master_index=oid index=oid

@default table=personnel_management_job_wanted
@default group=general
@default table=objects
@default field=meta
@default method=serialize

@property field type=classificator multiple=1 orient=vertical store=connect field=meta method=serialize table=objects
@caption Tegevusala

@property professions type=textarea table=objects field=comment
@caption Soovitavad ametid

@property load type=classificator multiple=1 orient=vertical store=connect field=meta method=serialize table=objects
@caption Töö koormus

@layout pay type=hbox width=15%:15%:70%

@property pay type=textbox size=5 datatype=int parent=pay
@caption Palgasoov alates-kuni

@property pay2 type=textbox size=5 datatype=int parent=pay no_caption=1

@property location type=relpicker multiple=1 automatic=1 orient=vertical store=connect field=meta method=serialize table=objects
@caption Töö asukoht

@property addinfo type=textarea
@caption Lisainfo soovitava töö kohta

@property sbutton type=submit store=no
@caption Lisa

@groupinfo candidate caption="Kandideerimised" submit=no
@default group=candidate

@property candidate_toolbar type=toolbar no_caption=1

@property candidate_table type=table no_caption=1

------------SEOSED-------------------

@reltype CANDIDATE value=3 clid=CL_PERSONNEL_MANAGEMENT_CANDIDATE
@caption Kandideerimine

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

			case "candidate_toolbar":
				$prop["vcl_inst"]->add_button(array(
					"name" => "add",
					"caption" => t("Lisa"),
					"img" => "new.gif",
				));
				break;

			case "candidate_table":
				$prop["vcl_inst"]->define_field(array(
					"name" => "name",
					"caption" => t("Nimi"),
				));
				$prop["vcl_inst"]->define_data(array(
					"name" => "test",
				));
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
