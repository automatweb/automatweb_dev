<?php
// $Header: /home/cvs/automatweb_dev/classes/profiil/Attic/profiil.aw,v 1.1 2004/02/05 14:58:09 jaanj Exp $
// profiil.aw - Profiil 
/*

@classinfo syslog_type=ST_PROFIIL relationmgr=yes

@default table=objects
@default group=general

@groupinfo settings caption=Seaded
@groupinfo comments caption=Kommentaarid

@tableinfo aw_profiles index=id master_table=objects master_index=brother_of

@property eyes_color type=relpicker reltype=RELTYPE_PRF_EYES_COLOR automatic=1 group=settings table=aw_profiles
@caption Silmade värv

@property hair_color type=relpicker reltype=RELTYPE_PRF_HAIR_COLOR automatic=1 group=settings table=aw_profiles
@caption Juuksevärv

@property hair_type type=relpicker reltype=RELTYPE_PRF_HAIR_TYPE automatic=1 group=settings table=aw_profiles
@caption Juuste tüüp

@property body_type type=relpicker reltype=RELTYPE_PRF_BODY_TYPE automatic=1 group=settings table=aw_profiles
@caption Keha tüüp

@property sexual_orientation type=relpicker reltype=RELTYPE_PRF_SEX_ORIENT automatic=1 group=settings table=aw_profiles
@caption Seksuaalne kalduvus   

@property alcohol type=relpicker reltype=RELTYPE_PRF_ALCOHOL automatic=1 group=settings table=aw_profiles
@caption Alkoholi tarbimine

@property tobacco type=relpicker reltype=RELTYPE_PRF_TOBACCO automatic=1 group=settings table=aw_profiles
@caption Tubaka tarbimine
  
@property org_add_comment type=releditor reltype=RELTYPE_TASK_COMMENT props=uname,commtext store=no group=comments 
@caption Lisa Kommentaar

@reltype TASK_COMMENT value=1 clid=CL_COMMENT
@caption Kommentaar

@reltype PRF_EYES_COLOR value=2 clid=CL_PROFILE_EYES_COLOR
@caption Silmade värv

@reltype PRF_HAIR_COLOR value=3 clid=CL_HAIR_COLOR
@caption Juuksevärv
 
@reltype PRF_HAIR_TYPE value=4 clid=CL_PROFILE_HAIR_TYPE
@caption Juuksetüüp
 
@reltype PRF_BODY_TYPE value=5 clid=CL_PROFILE_BODY_TYPE
@caption Keha tüüp
 
@reltype PRF_SEX_ORIENT value=6 clid=CL_SEXUAL_ORIENTATION
@caption Seksuaalne kalduvus
 
@reltype PRF_ALCOHOL value=7 clid=CL_PROFILE_ALCOHOL
@caption Alkoholi tarbimine

@reltype PRF_TOBACCO value=8 clid=CL_PROFILE_TOBACCO
@caption Tubaka tarbimine

@reltype AW_PERSON value=9 clid=CL_CRM_PERSON
@caption Isikuobjekt

*/

class profiil extends class_base
{
	function profiil()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "profiil/profiil",
			"clid" => CL_PROFIIL
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}
	*/

	/*
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
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
