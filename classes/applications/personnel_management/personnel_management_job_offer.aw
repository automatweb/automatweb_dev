<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_job_offer.aw,v 1.1 2004/04/22 12:41:53 sven Exp $
// personnel_management_job_offer.aw - Tööpakkumine 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_PERSONNEL_MANAGEMENT_JOB_OFFER, on_job_save)

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_OFFER relationmgr=yes

@default table=objects
@default group=general
@default field=meta

@tableinfo personnel_management_job index=oid master_table=objects master_index=oid

@property name type=textbox table=objects field=name group=info_about_job
@caption Ametikoht

@property navtoolbar type=toolbar no_caption=1 store=no group=kandideerinud

@property toosisu type=textarea field=about_job table=personnel_management_job  group=info_about_job
@caption Töö kirjeldus

@property noudmised type=textarea field=requirements table=personnel_management_job  group=info_about_job
@caption N&otilde;udmised kandidaadile

@property asukoht type=relpicker reltype=RELTYPE_LINN automatic=1 editonly=1 method=serialize field=meta table=objects  group=info_about_job
@caption Asukoht

@property deadline type=date_select field=deadline table=personnel_management_job
@caption Konkursi tähtaeg

@property tegevusvaldkond type=classificator field=meta method=serialize multiple=1 editonly=1 table=objects store=connect group=info_about_job reltype=RELTYPE_TEGEVUSVALDKOND orient=vertical
@caption Tegevusvaldkond

@property email type=relmanager reltype=RELTYPE_EMAIL props=mail  group=info_about_job field=meta method=serialize
@caption Meiliaadressid

@property phone type=relmanager reltype=RELTYPE_PHONE props=name  group=info_about_job field=meta method=serialize
@caption Telefoninumbrid


@property cvfail type=text subtitle=1 group=info_about_job_file
@caption Tööpakkumine failina.

@property cv_file_rel type=relmanager reltype=RELTYPE_JOBFILE props=file group=info_about_job_file no_caption=1 field=meta method=serialize


@property candits type=table group=kandideerinud no_caption=1
@caption Kandideerijad

@property kandideerin type=select field=meta method=serialize store=no group=minu_kandidatuur
@caption Vali cv kandideerimiseks

@property statistika type=table no_caption=1 group=statistika

@reltype EMAIL value=2 clid=CL_ML_MEMBER
@caption E-post

@reltype PHONE value=3 clid=CL_CRM_PHONE
@caption Telefon

@reltype LINN value=4 clid=CL_CRM_CITY
@caption Linn

@reltype CV value=5 clid=CL_CV
@caption Kandidaat

@reltype TEGEVUSVALDKOND value=6 clid=CL_META
@caption Tegevusvaldkond

@reltype JOBFILE value=7 clid=CL_FILE
@caption Tööpakkumine failina

@groupinfo info_about_job caption="Tööpakkumine" parent=info_about_job_main
@groupinfo info_about_job_main caption="Tööpakkumine"
@groupinfo info_about_job_file caption="Tööpakkumine failina" parent=info_about_job_main


@groupinfo minu_kandidatuur caption="Minu kandidatuur"
@groupinfo kandideerinud caption="Kandideerijad"
@groupinfo statistika caption="Statistika"
*/

class personnel_management_job_offer extends class_base
{
	function personnel_management_job_offer()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/personnel_management/personnel_management_job_offer",
			"clid" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER
		));
	}
	
	function on_job_save($arr)
	{
		$job_obj = &obj($arr["oid"]);
		
		if($this->my_profile["group"] == "employer")
		{
			$this->my_profile["org_obj"]->connect(array(
				"to" => $job_obj->id(),
				"reltype" => 20,
			));
				
			$cv_obj->connect(array(
				"to" => $this->my_profile["org_obj"]->id(),
				"reltype" => RELTYPE_CV_OWNER,
			));
		}
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}


	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
