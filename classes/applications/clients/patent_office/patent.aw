<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/patent.aw,v 1.5 2006/12/05 16:03:40 markop Exp $
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

@property country type=textbox
@caption P&auml;riltolumaa

@property procurator type=relpicker reltype=RELTYPE_PROCURATOR
@caption Volinik

@property warrant type=fileupload
@caption Volikiri

@property authorized_person type=relpicker reltype=RELTYPE_AUTHORIZED_PERSON
@caption Volitatud isik

@property additional_info type=textarea 
@caption Lisainfo

property phone type=textbox
caption Telefon

property fax type=textbox
caption Fax

property email type=textbox
caption E-mail

#TRADEMARK
@groupinfo name=trademark caption=Kaubam&auml;rk
@default group=trademark

@property type type=select
@caption T&uuml;&uuml;p


@property undefended_parts type=textbox
@caption Mittekaitstavad osad


@property word_mark type=textbox
@caption S&otilde;nam&auml;rk

@property colors type=textarea
@caption V&auml;rvide loetelu (juhul, kui on v&auml;rviline)

@property trademark_character type=textarea
@caption Kaubam&auml;rgi iseloomustus

@property element_translation type=textarea
@caption V&otilde;&otilde;rkeelsete elementide t&otilde;lge 

@property reproduction type=fileupload reltype=RELTYPE_REPRODUCTION
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
	
	@property exhibition_date type=date_select
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

@reltype REPRODUCTION value=9 clid=CL_FILE
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

@reltype AUTHORIZED_PERSON calue=10 clid=CL_CRM_PERSON
@caption Volitatud isik


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
		$this->text_vars = array("name" , "firstname" , "lastname" ,  "code" , "street", "city" ,"index", "country_code" , "phone" , "email" , "fax" ,  "undefended_parts" , "wordmark", "convention_nr"  , "convention_country", "exhibition_name" , "exhibition_country" , "request_fee" , "classes_fee" , "payer" , "doc_nr","authorized_person_firstname", "authorized_person_lastname","authorized_person_code");
		$this->text_area_vars = array("colors" , "trademark_character", "element_translation", "additional_info");
		$this->file_upload_vars = array("warrant" , "reproduction");
		$this->date_vars = array("payment_date" , "exhibition_date", "convention_date");
		$this->types = array(t("Sõnamärk"),t("Kujutismärk"),t("Kombineeritud märk"),t("Ruumiline märk"));
		$this->trademark_types = array(t("Kollektiivkaubam&auml;rk"),t("Garantiim&auml;rk"));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "type":
				$prop["options"] = $this->types;
				break;
			case "trademark_type":
				$prop["options"] = $this->trademark_types;
				break;
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
			$data[$var] = html::fileupload(array("name" => $var."_upload"));
		}
		foreach($this->date_vars as $var)
		{
			if($_SESSION["patent"][$var])
			{
				$val = $_SESSION["patent"][$var];
			}
			else
			{
				$val = "dd/mm/yyyy";
			}
			$data[$var] = html::textbox(array(
				"name" => $var,
				"value" => $val,
				"size" => 40,
			));
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
		$data["applicant_type"] = t("F&uuml;&uuml;siline isik :").html::radiobutton(array(
			"value" => 0,
			"checked" => !$_SESSION["patent"]["type"],
			"name" => "type",
		)).t("Juriidiline isik :").html::radiobutton(array(
			"value" => 1,
			"checked" => $_SESSION["patent"]["type"],
			"name" => "type",
		));

		$data["type"] = html::radiobutton(array(
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
		$data["parent"] = $arr["alias"]["to"];
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
		$this->save_uploads($_FILES);
		
		if($_POST["save"])
		{
			$this->save_data();
			$_SESSION["patent"] = null;
		}
		//viimasest lehest edasi
		if($arr["data_type"] == 5)
		{
			return aw_url_change_var("data_type" , null , $arr["return_url"]);
		}
		return aw_url_change_var("data_type" , ($arr["data_type"]+1) , $arr["return_url"]);
	}
	
	function save_uploads($uploads)
	{
		foreach($this->file_upload_vars as $var)
		{
			if(array_key_exists($var."_upload" , $uploads))
			{
				$image_inst = get_instance(CL_IMAGE);
				$upload_image = $image_inst->add_upload_image($var."_upload", $_SESSION["patent"]["parent"]);
				// if there is image uploaded:
				$_SESSION["patent"][$var] = $upload_image['id'];
			}
		}
	}

	function save_data()
	{
		$patent = new object();
		$patent->set_class_id(CL_PATENT);
		$patent->set_parent($_SESSION["patent"]["parent"]);
		$patent->save();
		$patent->set_name("Patent nr. ".$patent->id());
		$this->save_trademark($patent);
		$this->save_priority($patent);
		$this->save_fee($patent);
		$this->save_applicants($patent);
		$this->final_save($patent);
		$patent->save();arr($patent->id());
		//unset($_SESSION["patent"]);
	}
	
	function save_applicants($patent)
	{
		$patent->set_prop("country" ,$_SESSION["patent"]["country"]);
		$applicant = new object();
		$applicant->set_parent($patent->id());
		if($_SESSION["patent"]["applicant_type"])
		{
			$applicant->set_class_id(CL_CRM_COMPANY);
			$type=1;
		}
		else
		{
			$applicant->set_class_id(CL_CRM_PERSON);
		}
		$applicant->save();

	
		$address = new object();
		$address->set_class_id(CL_CRM_ADDRESS);
		$address->set_parent($applicant->id());
		
		$address->set_prop("aadress", $_SESSION["patent"]["street"]);
		$address->set_prop("postiindeks" , $_SESSION["patent"]["index"]);
		if($_SESSION["patent"]["city"])
		{
			$citys = new object_list(array("lang_id" => 1, "class_id" => CL_CRM_CITY, "name" => $_SESSION["patent"]["city"]));
			if(!is_object($city = reset($citys->arr())))
			{
				$city = new object();
				$city->set_parent($applicant->id());
				$city->set_class_id(CL_CRM_CITY);
				$city->save();
			}
			$address->set_prop("linn" ,$city->id());
		}
		
		$address->save();

		if($type)
		{
			$applicant->set_name($_SESSION["patent"]["name"]);
			$applicant->set_prop("contact" , $address->id());
			$applicant->set_prop("reg_nr",$_SESSION["patent"]["code"]);
		}
		else
		{
			$applicant->set_prop("firstname" , $_SESSION["patent"]["firstname"]);
			$applicant->set_prop("lastname" , $_SESSION["patent"]["lastname"]);
			$applicant->set_name($_SESSION["patent"]["firstname"]." ".$_SESSION["patent"]["lastname"]);
			$applicant->set_prop("address" , $address->id());
			$applicant->set_prop("personal_id" , $_SESSION["patent"]["code"]);
		}
		$applicant->connect(array("to"=> $address->id(), "type" => "RELTYPE_ADDRESS"));
		$applicant->save();
		
		if($_SESSION["patent"]["phone"])
		{
			$phone = new object();
			$phone->set_class_id(CL_CRM_PHONE);
			$phone->set_name($_SESSION["patent"]["phone"]);
			$phone->set_prop("type" , "mobile");
			$phone->set_parent($applicant->id());
			$phone->save();
			$applicant->connect(array("to"=> $phone->id(), "type" => "RELTYPE_PHONE"));
		}
		if($_SESSION["patent"]["email"])
		{
			$email = new object();
			$email->set_class_id(CL_ML_MEMBER);
			$email->set_name($_SESSION["patent"]["email"]);
			$email->set_prop("mail" , $_SESSION["patent"]["email"]);
			$email->set_parent($applicant->id());
			$email->save();
			$applicant->connect(array("to"=> $email->id(), "type" => "RELTYPE_EMAIL"));
		}
		if($_SESSION["patent"]["fax"])
		{
			$phone = new object();
			$phone->set_class_id(CL_CRM_PHONE);
			$phone->set_name($_SESSION["patent"]["fax"]);
			$phone->set_parent($applicant->id());
			$phone->save();
			if($type)
			{
				$applicant->connect(array("to"=> $phone->id(), "type" => "RELTYPE_TELEFAX"));
			}
			else
			{
				$applicant->connect(array("to"=> $phone->id(), "type" => "RELTYPE_FAX"));
				$applicant->set_prop("fax" , $phone->id());
			}
		}
		
		
		$patent->set_prop("applicant" , $applicant->id());
		$patent->set_prop("country" , $_SESSION["patent"]["country_code"]);
		$patent->set_prop("procurator", $_SESSION["patent"]["procurator"]);
		
		if(is_oid($_SESSION["patent"]["warrant"]))
		{
			$patent->set_prop("warrant", $_SESSION["patent"]["warrant"]);
			$patent->connect(array("to" => $_SESSION["patent"]["warrant"], "type" => "RELTYPE_WARRANT"));
		}
		$patent->save();
	}
	
	function final_save($patent)
	{
		if(	$_SESSION["patent"]["authorized_person_firstname"] || 
			$_SESSION["patent"]["authoirized_person_person_lastname"] || 
			$_SESSION["patent"]["authoirized_person_code"])
		{
			$applicant = new object();
			$applicant->set_parent($patent->id());
			$applicant->set_class_id(CL_CRM_PERSON);
			$applicant->save();
			$applicant->set_prop("firstname" , 	$_SESSION["patent"]["authorized_person_firstname"]);
			$applicant->set_prop("lastname" , 	$_SESSION["patent"]["authorized_person_lastname"]);
			$applicant->set_prop("personal_id" , 	$_SESSION["patent"]["authorized_person_code"]);
			$applicant->set_name($_SESSION["patent"]["authorized_person_firstname"]." ".$_SESSION["patent"]["authorized_person_lastname"]);
			$applicant->save();
			$patent->set_prop("authorized_person" , $applicant->id());
			$parent->connect(array("to" => $applicant->id(), "type" => "RELTYPE_AUTHORIZED_PERSON"));
			$patent->save();
		}
	}
	
	
	
	function save_fee($patent)
	{
		$vars = array("request_fee" , "classes_fee" , "payer" , "doc_nr" , "payment_date");
		foreach($vars as $var)
		{
			if($_SESSION["patent"][$var])
			{
				$patent->set_prop($var,$_SESSION["patent"][$var]);
			}
		}
		
		$patent->save();
	}
	
	
	
	function save_priority($patent)
	{
		$convention_time = explode("/" , $_SESSION["patent"]["convention_date"]);
		$patent->set_prop("convention_nr" , $_SESSION["patent"]["convention_nr"]);
		$patent->set_prop("convention_date" , mktime(0,0,0,$convention_time[1], $convention_time[0],$convention_time[2]));
		$patent->set_prop("convention_country" , $_SESSION["patent"]["convention_country"]);
	
		$exhibition_time = explode("/" , $_SESSION["patent"]["exhibition_date"]);
		$patent->set_prop("exhibition_name" , $_SESSION["patent"]["exhibition_name"]);
		$patent->set_prop("exhibition_date" , mktime(0,0,0,$exhibition_time[1], $exhibition_time[0],$exhibition_time[2]));
		$patent->set_prop("exhibition_country" , $_SESSION["patent"]["exhibition_country"]);
		
		$patent->save();
	}
	
	function save_trademark($patent)
	{
		$patent->set_prop("word_mark" , $_SESSION["patent"]["wordmark"]);
		$patent->set_prop("colors" , $_SESSION["patent"]["colors"]);
		$patent->set_prop("trademark_character" , $_SESSION["patent"]["trademark_character"]);
		$patent->set_prop("element_translation" , $_SESSION["patent"]["element_translation"]);
		$patent->set_prop("type" , $_SESSION["patent"]["type"]);
		$patent->set_prop("undefended_parts" , $_SESSION["patent"]["undefended_parts"]);
		$tr_type = array();
		if($_SESSION["patent"]["co_trademark"])
		{
			$tr_type[] = 0;
		}
		if($_SESSION["patent"]["guaranty_trademark"])
		{
			$tr_type[] = 1;
		}
		$patent->set_prop("trademark_type" , $tr_type);
		if(is_oid($_SESSION["patent"]["reproduction"]))
		{
			$patent->set_prop("reproduction" , $_SESSION["patent"]["reproduction"]);
			$patent->connect(array("to" => $_SESSION["patent"]["reproduction"], "type" => "RELTYPE_REPRODUCTION"));
		}
		$patent->save();
	}
}
?>
