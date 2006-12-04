<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/patent.aw,v 1.4 2006/12/04 17:32:33 markop Exp $
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
@groupinfo fee caption="Riigil&otilde;iv"
@default group=fee

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


@groupinfo web caption="Saidilt lisamine"
@default group=web

@property procurator_menu type=relpicker reltype=RELTYPE_PROCURATOR_MENU
@caption Volinike kaust
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

@reltype PROCURATOR_MENU value=8 clid=CL_MENU
@caption Volinike kaust


*/

class patent extends class_base
{
	function patent()
	{
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_PATENT
		));
	
		$this->info_levels = array("applicant","trademark","products_and_services","priority","fee","check");
		$this->text_vars = array("firstname" , "lastname" ,  "code" , "street", "city" ,"index", "country_code" , "phone" , "email" , "fax" ,  "undefended_parts" , "wordmark");
		$this->text_area_vars = array("colors" , "trademark_character", "element_translation");
		$this->file_upload_vars = array("warrant" , "reptoduction");
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

	
	/** 
		
		@attrib name=parse_alias is_public="1" caption="Change"
	
	**/
	function parse_alias($arr)
	{
		enter_function("patent::parse_alias");
		
		if(!$_SESSION["patent"])
		{
			$_SESSION["patent"]["data_type"] = 0;
		}		
		if(isset($_GET["data_type"]))
		{
			$arr["data_type"] = $_GET["data_type"];
		}
		else
		{
			$arr["data_type"] = $_SESSION["patent"]["data_type"];
		}
		$tpl = $this->info_levels[$arr["data_type"]].".tpl";
		$this->read_template($tpl);
		lc_site_load("patent", &$this);
		$this->vars($this->web_data($arr));
		
		$this->vars(array("reforb" => $this->mk_reforb("submit_data",array(
				"data_type"	=> $arr["data_type"],
				"return_url" 	=> get_ru(),
			)),
		));
		//lõpetab ja salvestab
		if($arr["data_type"] == 5)
		{
			$this->vars(array("reforb" => $this->mk_reforb("submit_data",array(
					"save" => 1,
					"return_url" 	=> get_ru(),
				)),
			));
		}
		
		exit_function("realestate_add::parse_alias");
		return $this->parse();
	}

	function web_data($arr)
	{
		$data = $this->get_vars($arr);
		
		$data["data_type"] = $arr["data_type"];
		$data["data_type_name"] = $this->info_levels[$arr["data_type"]];
		
		$this->get_vars($arr);
		
		foreach ($this->text_vars as $var)
		{
			$data[$var] = html::textbox(array(
				"name" => $var,
				"value" => $_SESSION["patent"][$var],
				"size" => 40,
			));
		}
		foreach($this->text_area_vars as $var)
		{
			$data[$var] = html::textarea(array(
				"name" => $var,
				"value" => $_SESSION["patent"][$var],
				"height"=> 4,
			));
		}
		foreach($this->file_upload_vars as $var)
		{
			$data[$var] = html::fileupload(array("name" => $var));
		}
		foreach($_SESSION["patent"] as $key => $val)
		{
			$data[$key."_value"] =  $val;
		}
		return $data;
	}
	
	function get_vars($arr)
	{
		$data = array();
		$data["country"] = t("Eesti :").html::radiobutton(array(
			"value" => 0,
			"checked" => !$_SESSION["patent"]["country"],
			"name" => "country",
		)).t(" V&auml;lismaa :").html::radiobutton(array(
			"value" => 1,
			"checked" => $_SESSION["patent"]["country"],
			"name" => "country",
		));
		$data["type"] = t("F&uuml;&uuml;siline isik :").html::radiobutton(array(
			"value" => 0,
			"checked" => !$_SESSION["patent"]["type"],
			"name" => "type",
		)).t("Juriidiline isik :").html::radiobutton(array(
			"value" => 1,
			"checked" => $_SESSION["patent"]["type"],
			"name" => "type",
		));

		$data["unknown_stuff"] = html::radiobutton(array(
			"value" => 0,
			"checked" => !$_SESSION["patent"]["unknown_stuff"],
			"name" => "unknown_stuff",
			)).t("Sõnamärk").html::radiobutton(array(
				"value" => 1,
				"checked" => ($_SESSION["patent"]["unknown_stuff"] == 1) ? 1 : 0,
				"name" => "unknown_stuff",
			)).t("Kujutismärk").html::radiobutton(array(
				"value" => 2,
				"checked" => ($_SESSION["patent"]["unknown_stuff"] == 2) ? 1 : 0,
				"name" => "unknown_stuff",
			)).t("Kombineeritud märk").html::radiobutton(array(
				"value" => 3,
				"checked" => ($_SESSION["patent"]["unknown_stuff"] == 3) ? 1 : 0,
				"name" => "unknown_stuff",
			)).t("Ruumiline märk");
		
		$data["trademark_type"] = html::checkbox(array(
			"value" => 1,
			"checked" => $_SESSION["patent"]["co_trademark"],
			"name" => "co_trademark",
			)).t("Kollektiivkaubam&auml;rk").
			html::checkbox(array(
				"value" => 1,
				"checked" => $_SESSION["patent"]["guaranty_trademark"],
				"name" => "guaranty_trademark",
			)).t("Garantiim&auml;rk");
		$dummy = obj($arr["alias"]["to"]);
		$parent = $dummy->prop("procurator_menu");
		$procurator_l = new object_list(array(
			"lang_id" => array(),
			"parent" => $parent, 
			"class_id" => CL_CRM_PERSON,
		));
		$options = $procurator_l->names();
		$data["procurator"] = html::select(array(
			"options" => $options,
			"name" => "procurator",
			"value" => $_SESSION["patent"]["procurator"]
		));

		return $data;
	}
	
	/** 
		@attrib name=submit_data is_public="1" caption="Change" all_args=1
	**/
	function submit_data($arr)
	{
		foreach($_POST as $data => $val)
		{
			$_SESSION["patent"][$data] = $val;
		}
		if($_POST["save"])
		{
			$this->save_data();
		}
		return aw_url_change_var("data_type" , ($arr["data_type"]+1) , $arr["return_url"]);
	}

	function save_data()
	{
		;
		//unset($_SESSION["patent"]);
	}
}
?>
