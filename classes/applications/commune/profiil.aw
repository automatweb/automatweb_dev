<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/profiil.aw,v 1.1 2004/06/02 10:21:36 duke Exp $
// profiil.aw - Profiil 
/*

@classinfo syslog_type=ST_PROFIIL relationmgr=yes

@default table=objects
@default group=general

@groupinfo settings caption=Seaded
@groupinfo comments caption=Kommentaarid

@tableinfo aw_profiles index=id master_table=objects master_index=brother_of

@default group=settings
@default table=aw_profiles

@property height type=classificator reltype=RELTYPE_PRF_HEIGHT orient=vertical 
@caption Kasv

@property weight type=classificator reltype=RELTYPE_PRF_WEIGHT orient=vertical 
@caption Kaal

@property eyes_color type=classificator reltype=RELTYPE_PRF_EYES_COLOR orient=vertical 
@caption Silmade värv

@property hair_color type=classificator reltype=RELTYPE_PRF_HAIR_COLOR orient=vertical 
@caption Juuksevärv

@property hair_type type=classificator reltype=RELTYPE_PRF_HAIR_TYPE orient=vertical 
@caption Juuste tüüp

@property body_type type=classificator reltype=RELTYPE_PRF_BODY_TYPE orient=vertical 
@caption Keha tüüp

@property sexual_orientation type=classificator reltype=RELTYPE_PRF_SEX_ORIENT orient=vertical 
@caption Seksuaalne orientatsioon

property alcohol type=classificator reltype=RELTYPE_PRF_ALCOHOL orient=vertical  
caption Alkoholi tarbimine

@property tobacco type=classificator reltype=RELTYPE_PRF_TOBACCO orient=vertical 
@caption Tubaka tarbimine

@property user_field1 type=classificator 
@caption Tegevusala

@property user_field2 type=classificator 
@caption Haridustase

@property user_check1 type=checkbox value=1 ch_value=1 
@caption E-post varjatud

@property user_check2 type=checkbox value=1 ch_value=1
@caption Kinnine postkast

@property user_text1 type=textbox 
@caption Telefon

@property user_text2 type=textbox
@caption ICQ

@property user_text3 type=textbox
@caption MSN

@property user_text4 type=textbox
@caption Elukutse

@property user_text5 type=textbox
@caption Koduleht

@property user_blob1 type=textbox
@caption Lisainfo

@property user_blob2 type=textbox
@caption Lisainfo sõpradele



// this I do not need
@reltype TASK_COMMENT value=1 clid=CL_COMMENT
@caption Kommentaar

@reltype PRF_EYES_COLOR value=2 clid=CL_META
@caption Silmade värv

@reltype PRF_HAIR_COLOR value=3 clid=CL_META
@caption Juuksevärv
 
@reltype PRF_HAIR_TYPE value=4 clid=CL_META
@caption Juuksetüüp
 
@reltype PRF_BODY_TYPE value=5 clid=CL_META
@caption Keha tüüp
 
@reltype PRF_SEX_ORIENT value=6 clid=CL_META
@caption Seksuaalne kalduvus
 
@reltype PRF_ALCOHOL value=7 clid=CL_META
@caption Alkoholi tarbimine

@reltype PRF_TOBACCO value=8 clid=CL_META
@caption Tubaka tarbimine

@reltype AW_PERSON value=9 clid=CL_CRM_PERSON
@caption Isikuobjekt

@reltype PRF_HEIGHT value=10 clid=CL_META
@caption Pikkus

@reltype PRF_WEIGHT value=11 clid=CL_META
@caption Kaal

@reltype IMAGE value=12 clid=CL_IMAGE
@caption Pilt

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
