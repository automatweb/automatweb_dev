<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/Attic/personnel_management_cv.aw,v 1.1 2004/04/22 12:41:53 sven Exp $
// personnel_management_cv.aw - CV 
/*


@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_CV relationmgr=yes

@default table=objects
@default group=general
@default field=meta

@tableinfo staff_cv master_table=objects master_index=oid index=oid

@property navtoolbar type=toolbar no_caption=1 store=no group=arvutioskus,keeleoskused,haridustee,tookogemus
@property active_until type=date_select method=serialize table=objects field=meta
@caption Aktiivne kuni
//////////////////////////////TÜ KARJÄÄRITEENISTUS\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
@property tu_education type=releditor reltype=RELTYPE_EDUCATION props=eriala,algusaasta,loppaasta,teaduskond,oppekava,oppeaste,oppevorm,lisainfo_edu group=karjaariteenistus

/////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\


@property educationtabel type=table no_caption=1 store=no group=haridustee 

@property keeleoskused type=table store=no group=keeleoskused no_caption=1
@property lang_skill_label type=text group=keeleoskused subtitle=1 value=Lisa&nbsp;uus&nbsp;keeleoskus store=no

@property computer_skills type=table store=no group=arvutioskus no_caption=1
@property comp_skill_label type=text group=arvutioskus subtitle=1 value=Arvutioskuste&nbsp;lisamine store=no



@property jobs type=table store=no group=tookogemus no_caption=1
@property comp_skill_label type=text group=arvutioskus subtitle=1 value=Lisa&nbsp;arvutioskus store=no

@property haridus_label type=text group=haridustee subtitle=1 value=Kooli&nbsp;lisamine store=no

@property education type=releditor reltype=RELTYPE_EDUCATION props=lisainfo_edu,loppaasta,algusaasta,eriala,kool group=haridustee

@property language_skills type=releditor reltype=RELTYPE_LANG props=keel,tase  group=keeleoskused
@caption Keeleoskus

@property kogemused type=releditor reltype=RELTYPE_KOGEMUS props=asutus,algus,kuni,ametikoht,tasks group=tookogemus
@caption Kogemused

@property arvutioskus type=releditor reltype=RELTYPE_ARVUTIOSKUS props=oskus,tase group=arvutioskus
@caption Arvutioskus

@property juhiload type=classificator method=serialize group=driving_licenses store=connect reltype=RELTYPE_JUHILUBA
@caption Juhiload

//////////////////// TAB TÖÖSOOV \\\\\\\\\\\\\\\\\\\\\\\\\

@property valdkond type=classificator group=toosoov method=serialize multiple=1 orient=vertical store=connect reltype=RELTYPE_TEGEVUSVALDKOND
@caption Tegevusala

@property liik type=classificator multiple=1 group=toosoov method=serialize store=connect
@caption T&ouml;&ouml; liik

@property asukoht type=relpicker multiple=1 automatic=1 reltype=RELTYPE_LINN group=toosoov method=serialize orient=vertical
@caption Linnad

@property koormus type=classificator group=toosoov method=serialize multiple=1 orient=vertical
@caption T&ouml;&ouml; koormus 

@property job_addinfo type=textarea group=toosoov field=addinfo table=staff_cv
@caption Lisainfo soovitava t&ouml;&ouml; kohta

@property soovitajad type=textarea group=toosoov field=recommenders table=staff_cv
@caption Soovitajad

@property sain_tood type=checkbox group=toosoov field=gotjob table=staff_cv
@caption Sain t&ouml;&ouml;d teie kaudu

@property stats_table type=table group=statistika no_caption=1

@property cv_view type=text no_caption=1 store=no wrapchildren=1 group=cv_view

//////////////////////////////TABID\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
@groupinfo skills caption="Oskused"
@groupinfo arvutioskus caption="Arvutioskus" parent=skills
@groupinfo keeleoskused caption="Keeleoskus" parent=skills
@groupinfo driving_licenses caption="Juhiload" parent=skills

@groupinfo hariduskaik_main caption="Haridusk&auml;ik"
@groupinfo haridustee caption="Õpingud" parent=hariduskaik_main

@groupinfo tookogemus caption="T&ouml;&ouml;kogemused"
@groupinfo toosoov caption="Soovitud töö"

@groupinfo karjaariteenistus caption="Õpingud TÜ-s" parent=hariduskaik_main

@groupinfo statistika caption="Statistika"
@groupinfo cv_view caption="Vaata CV-d" submit=no
////////////////////////////SEOSED\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
@reltype EDUCATION value=1 clid=CL_EDUCATION 
@caption Haridus

@reltype KOGEMUS value=4 clid=CL_PERSONALIHALDUS_TOOKOGEMUS
@caption Kogemus

@reltype LANG value=2 clid=CL_PERSONALIHALDUS_LANG
@caption Keeleoskus

@reltype ARVUTIOSKUS value=3 clid=CL_PERSONALIHALDUS_ARVUTIOSKUS
@caption Arvutioskus

@reltype LINN value=5 clid=CL_CRM_CITY
@caption Linn

@reltype TEGEVUSVALDKOND value=6 clid=CL_META
@caption Tegevusala

@reltype CV_OWNER value=7 clid=CL_CRM_PERSON
@caption Omanik

@reltype JUHILUBA value=8 clid=CL_META
@caption Juhiluba

*/

class personnel_management_cv extends class_base
{
	var $my_profile;
	
	function personnel_management_cv()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_PERSONNEL_MANAGEMENT_CV
		));
		$personalikeskkond = get_instance("applications/personnel_management/personnel_management");
		$this->my_profile = $personalikeskkond->my_profile;
	}

	function callback_on_load($arr)
	{
		$this->cfgmanager = aw_ini_get("personnel_management.configform_manager");
		
	}
	
	
	//When new cv is saved, this function is called by message. Creates relation betweeb current logged user and cv.
	function on_new_cv($arr)
	{
		$cv_obj = &obj($arr["oid"]);	
		if($this->my_profile["group"] == "employee")
		{
			$this->my_profile["person_obj"]->connect(array(
				"to" => $cv_obj->id(),
				"reltype" => 20,
			));
			
			$cv_obj->connect(array(
				"to" => $this->my_profile["person_obj"]->id(),
				"reltype" => RELTYPE_CV_OWNER,
			));
		}	
	}
	
	
	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}
	*/

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
