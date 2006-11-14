<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/patent.aw,v 1.2 2006/11/14 17:56:57 markop Exp $
// patent.aw - Patent 
/*

@classinfo syslog_type=ST_PATENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1




@default table=objects
@default group=general
@default field=meta
@default method=serialize

#GENERAL
//idee siis selles, et kes on connectitud, on nagu õiged inimesed ja kes üks kes valitud, see on esindaja
@property applicant type=relpicker reltype=RELTYPE_APPLICANT
@caption Taotleja

@property country type=relpicker reltype=RELTYPE_COUNTRY
@caption P&auml;riltolumaa

@property procurator type=relpicker reltype=RELTYPE_PROCURATOR
@caption Volinik

@property warrant type=fileupload
@caption Volikiri

@property phone type=relpicker reltype=RELTYPE_PHONE
@caption Telefon

@property fax type=relpicker reltype=RELTYPE_FAX
@caption Fax

@property email type=relpicker reltype=RELTYPE_EMAIL
@caption E-mail

#TRADEMARK
@groupinfo name=trademark caption=Kaubam&auml;rk
@default group=trademark

@property type type=select
@caption T&uuml;&uuml;p


@property trademark_type type=select
@caption T&uuml;&uuml;p

@property word_mark type=textbox
@caption S&otilde;nam&auml;rk

@property colors type=textarea
@caption V&auml;rvide loetelu (juhul, kui on v&auml;rviline)

@property trademark_character type=textarea
@caption Kaubam&auml;rgi iseloomustus

@property element_translation type=textarea
@caption V&otilde;&otlide;rkeelsete elementide t&otilde;lge 

@property reproduction type=fileupload
@caption Lisa reproduktsioon

@property trademark_type type=select multiple=1
@caption T&uuml;&uuml;p

#tooted ja teenused
@groupinfo products_and_services caption="Kaupade ja teenuste loetelu"
@default group=products_and_services

@property products_and_services_tbl type=table
@caption Kaupade ja teenuste loetelu

#prioriteet
@groupinfo priority caption="Prioriteet"
@default group=priority

@property childtitle110 type=text store=no subtitle=1
@caption Konventsiooniprioriteet
	
	@property convention_nr type=textbox
	@caption Taotluse number
	
	@property convention_date type=date_select
	@caption Kuup&auml;ev
	
	@property convention_country type=textbox
	@caption Riigi kood

@property childtitle111 type=text store=no subtitle=1
@caption Näituseprioriteet

	@property exhibition_name type=textbox
	@caption Näituse nimi
	
	@property exhibition_date type=textbox
	@caption Kuupäev
	
	@property exhibition_country type=textbox
	@caption Riigi kood

#riigil&otilde;iv
@groupinfo priority caption="Prioriteet"
@default group=priority

@property request_fee type=textbox 
@caption Taotlusl&otilde;iv

@property classes_fee type=textbox 
@caption Lisaklasside l&otilde;iv

@property payer type=textbox 
@caption Maksja nimi

@property doc_nr type=textbox
@caption Maksedokumendi number

@property payment_date type=date_select 
@caption Makse kuup&auml;ev




#RELTYPES
@reltype APPLICANT value=1 clid=CL_CRM_PERSON,CL_CRM_COMPANY
@caption Taotleja

@reltype PROCURATOR value=2 clid=CL_CRM_PERSON
@caption Volinik

@reltype WARRANT value=3 clid=CL_FILE
@caption Volikiri

@reltype PHONE value=4 clid=CL_CRM_PHONE
@caption Telefon

@reltype FAX value=5 clid=CL_CRM_FAX
@caption Faks

@reltype EMAIL value=6 clid=CL_CRM_EMAIL
@caption E-mail

@reltype COUNTRY value=7 clid=CL_CRM_COUNTRY
@caption P&auml;riltolumaa





*/

class patent extends class_base
{
	function patent()
	{
		$this->init(array(
			"tpldir" => "applications/clients/patent_office/patent",
			"clid" => CL_PATENT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
