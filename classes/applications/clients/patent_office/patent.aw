<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/patent.aw,v 1.8 2006/12/07 12:07:06 markop Exp $
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

	function fill_session($id)
	{
		$patent = obj($id);
		$property_vars = array("procurator" , "warrant" , "additional_info", "type","undefended_parts", "word_mark" , "colors" , "trademark_character", "element_translation", "reproduction" , "trademark_type" ,
			 "priority" , "convention_nr" , "convention_country" , "exhibition_name", "exhibition" , "request_fee" , "classes_fee",
			 "payer" , "doc_nr");
	
		foreach($property_vars as $var)
		{
			$_SESSION["patent"][$var] = $patent->prop($var);
		}
		
		foreach($this->date_vars as $var)
		{
			$_SESSION["patent"][$var] = $patent->prop($var);
			//$_SESSION["patent"][$var] = date("d",$patent->prop($var))."/".date("m",$patent->prop($var))."/".date("Y",$patent->prop($var));
		}
		
		$_SESSION["patent"]["products"] = $patent->meta("products");

		$address_inst = get_instance(CL_CRM_ADDRESS);
		$person_inst = get_instance(CL_CRM_PERSON);
	//	$_SESSION["patent"]["representer"] = $patent->prop("applicant");
		foreach($patent->connections_from(array("type" => "RELTYPE_APPLICANT")) as $key => $c)
		{
			$o = $c->to();
			$_SESSION["patent"]["applicants"][$key]["name"] = $o->name();
			if($o->class_id() == CL_SRM_COMPANY)
			{
				$_SESSION["patent"]["applicants"][$key]["applicant_type"] = 1;
				$address = $o->prop("contacts");
				$_SESSION["patent"]["applicants"][$key]["phone"] = $o->prop("phone");
				$_SESSION["patent"]["applicants"][$key]["email"] = $o->prop("email");
				$_SESSION["patent"]["applicants"][$key]["fax"] = $o->prop("fax");
				$_SESSION["patent"]["applicants"][$key]["code"] = $o->prop("reg_nr");
			}
			else
			{
				$_SESSION["patent"]["applicants"][$key]["firstname"] = $o->prop("firstname");
				$_SESSION["patent"]["applicants"][$key]["lastname"] = $o->prop("lastname");
				$address = $o->prop("address");
				$_SESSION["patent"]["applicants"][$key]["phone"] = $o->prop("phone");
				$_SESSION["patent"]["applicants"][$key]["email"] = $o->prop("email");
				$_SESSION["patent"]["applicants"][$key]["fax"] = $o->prop("fax");
				$_SESSION["patent"]["applicants"][$key]["code"] = $o->prop("personal_id");
			}
			
			if(is_oid($address))
			{
				$address_obj = obj($address);
				$_SESSION["patent"]["applicants"][$key]["street"] = $address_obj->prop("aadress");
				$_SESSION["patent"]["applicants"][$key]["index"] = $address_obj->prop("postiindeks");
				if(is_oid($address_obj->prop("linn")) && $this->can("view" , $address_obj->prop("linn")))
				{
					$city = obj($address_obj->prop("linn"));
					$_SESSION["patent"]["applicants"][$key]["city"] = $city->name();
				}
				if(is_oid($address_obj->prop("riik")))
				{
					$_SESSION["patent"]["applicants"][$key]["country"] = $address_inst->get_country_code($address_obj->prop("riik"));
				}
			}
		}
		if(is_oid($patent->prop("authorized_person")) && $this->can("view" , $patent->prop("authorized_person")))
		{
			$authorized_person = obj($patent->prop("authorized_person"));
			$_SESSION["patent"]["authorized_person_firstname"] = $authorized_person->prop("firstname");
			$_SESSION["patent"]["authorized_person_lastname"] = $authorized_person->prop("lastname");
			$_SESSION["patent"]["authorized_person_code"] = $authorized_person->prop("personal_id");
		}
	}
	
	/** 
		@attrib name=parse_alias is_public="1" caption="Change"
	**/
	function parse_alias($arr)
	{
		enter_function("patent::parse_alias");

		if($_GET["patent_id"])
		{
			$_SESSION["patent"] = null;
			$_SESSION["patent"]["id"] = $_GET["patent_id"];
			$this->fill_session($_GET["patent_id"]);
		}
		
		if(!$_SESSION["patent"]["data_type"])
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
		
		if($arr["data_type"] == 6)
		{
			return $this->my_patent_list();//$this->mk_my_orb("my_patent_list", array());
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

	function _get_applicant_data()
	{
		$n = $_SESSION["patent"]["applicant_id"];
		foreach($_SESSION["patent"]["applicants"][$n] as $var => $val)
		{
			$_SESSION["patent"][$var] = $val;
		}
	}

	function get_user_data()
	{
		if(is_array($_SESSION["patent"]["applicants"] && sizeof($_SESSION["patent"]["applicants"])))
		{
			return;
		}
		$adr = get_instance(CL_CRM_ADDRESS);
	//	arr($_SESSION["patent"]);
		$us = get_instance(CL_USER);
		$this->users_person = new object($us->get_current_person());
		if(is_object($this->users_person))
		{
			$_SESSION["patent"]["firstname"] = $this->users_person->prop("firstname");
			$_SESSION["patent"]["lastname"] = $this->users_person->prop("lastname");
			$_SESSION["patent"]["code"] = $this->users_person->prop("personal_id");
			$_SESSION["patent"]["fax"] = $this->users_person->prop_str("fax");
			$_SESSION["patent"]["email"] = $this->users_person->prop_str("email");
			$_SESSION["patent"]["phone"] = $this->users_person->prop_str("phone");
			$address = $this->users_person->get_first_obj_by_reltype("RELTYPE_ADDRESS");
			if(is_object($address))
			{
				$_SESSION["patent"]["index"] = $address->prop_str("postiindeks");
				$_SESSION["patent"]["city"] = $address->prop_str("linn");
				$_SESSION["patent"]["street"] = $address->prop("aadress");
				if($address->prop("riik"))
				{
					$_SESSION["patent"]["country_code"] = $adr->get_country_code($address->prop("riik"));
				}
			}
		}
	}
	

	function web_data($arr)
	{
		$data = $this->get_vars($arr);
		
		$data["data_type"] = $arr["data_type"];
		$data["data_type_name"] = $this->info_levels[$arr["data_type"]];
		$this->get_user_data($arr);
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
			if(!($_SESSION["patent"][$var] >1) )
			{
				$_SESSION["patent"][$var] = time();
			}
//			if($_SESSION["patent"][$var])
//			{
				$data[$var] = html::date_select(array("name" => $var, "value" => $_SESSION["patent"][$var]));
//			}
//			else
//			{
//				$val = "dd/mm/yyyy";
//			}
//			$data[$var] = html::textbox(array(
//				"name" => $var,
//				"value" => $val,
//				"size" => 40,
//			));
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
		
		if(isset($_SESSION["patent"]["applicant_id"]) && $_SESSION["patent"]["applicant_id"]!= "")
		{
			$this->_get_applicant_data();
			//$data["applicant_id"] = $_SESSION["patent"]["applicant_id"];
			$data["change_applicant"] = $_SESSION["patent"]["applicant_id"];
			$_SESSION["patent"]["change_applicant"] = null;
			$_SESSION["patent"]["applicant_id"] = null;
		}
		
		
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
			"checked" => !$_SESSION["patent"]["applicant_type"],
			"name" => "applicant_type",
		)).t("Juriidiline isik :").html::radiobutton(array(
			"value" => 1,
			"checked" => $_SESSION["patent"]["applicant_type"],
			"name" => "applicant_type",
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
		$data["add_new_applicant"] = html::radiobutton(array(
				"value" => 1,
				"checked" => 0,
				"name" => "add_new_applicant",
		));
		$data["applicant_no"] = sizeof($_SESSION["patent"]["applicants"]) + 1;
		$data["applicants_table"] = $this->_get_applicants_table();
		$data["country_popup_link"] = html::href(array(
			"caption" => t("Vali") ,
			"url"=> "javascript:void(0);",
			"onclick" => 'javascript:window.open("'.$this->mk_my_orb("country_popup", array()).'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
			
		));
		$bank_inst = get_instance("common/bank_payment");
		$data["banks"] = $bank_inst->bank_forms(array("id" =>10580 , "amount" => 10));
		$data["find_products"] = html::href(array(
			"caption" => t("Otsi klassifikaatorit") ,
			"url"=> "javascript:void(0);",
			"onclick" => 'javascript:window.open("'.$this->mk_my_orb("find_products", array("ru" => get_ru())).'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
			
		));
		$data["results_table"] = $this->get_results_table();
		return $data;
	}
	
	
	function get_results_table()
	{
		if(!is_array($_SESSION["patent"]["prod_selection"]) && !is_array($_SESSION["patent"]["products"]))
		{
			return;
		}
		
		classload("vcl/table");
		$t = new vcl_table(array(
			"layout" => "generic",
		));
		
		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
		));
		$t->define_field(array(
			"name" => "class_name",
			"caption" => t("Klassi nimi"),
		));
		$t->define_field(array(
			"name" => "prod",
			"caption" => t("Toode/teenus"),
		));
	
		//arr($_SESSION["patent"]["applicants"]);
		if(is_array($_SESSION["patent"]["prod_selection"]))
		{
			foreach($_SESSION["patent"]["prod_selection"] as $prod)
			{
	
				$product = obj($prod);
				$parent = obj($product->parent());
				$t->define_data(array(
					"prod" => html::textarea(array("name" => "products[".$prod."]" , "value" => $product->name() . "(" .$product->prop("code").  ")", )),
					"class" => $parent->comment(),
					"class_name" => $parent->name(),
	//				"oid"	=> $prod->id(),
				));
			}
			$_SESSION["patent"]["prod_selection"] = null;
		}		
		if(is_array($_SESSION["patent"]["products"]))
		{
			foreach($_SESSION["patent"]["products"] as $key=> $val)
			{
				$product = obj($key);
				$parent = obj($product->parent());
				$t->define_data(array(
					"prod" => html::textarea(array("name" => "products[".$key."]" , "value" => $val, )),
					"class" => $parent->comment(),
					"class_name" => $parent->name(),
	//				"oid"	=> $prod->id(),
				));
			}
//			$_SESSION["patent"]["prod_selection"] = null;
		}		
		
		return $t->draw();
	}
	
	/**
		@attrib name=find_products
		@param ru required type=string
	**/
	function find_products($arr)
	{
		
		if($_POST["do_post"])
		{
			$_SESSION["patent"]["prod_selection"] =  $_POST["oid"];
			die("
				<script type='text/javascript'>
				window.opener.location.href='".$arr["ru"]."';
				window.close();
				</script>"
			);
		}
		
		if($_POST["product"])
		{
			classload("vcl/table");
			$t = new vcl_table(array(
				"layout" => "generic",
			));
			
			$t->define_field(array(
				"name" => "class",
				"caption" => t("Klass"),
			));
			$t->define_field(array(
				"name" => "prod",
				"caption" => t("Toode/teenus"),
			));
			$t->define_chooser(array(
				"name" => "oid",
				"field" => "oid",
				"caption" => t("Vali"),
			));
			
			$products = new object_list(array("name" => "%".$_POST["product"]."%", "class_id" => CL_SHOP_PRODUCT , "lang_id" => array()));
			
			//arr($_SESSION["patent"]["applicants"]);
			foreach($products->arr() as $prod)
			{
				$parent = obj($prod->parent());
				$t->define_data(array(
					"prod" => $prod->name(),
					"class" => $parent->name(),
					"code" => 132245,
					"oid"	=> $prod->id(),
				));
			}
			return "<form action='' method=POST>".$t->draw()."
			<input type=hidden value=".$arr["ru"]." name=ru>
			<input type=hidden value=1 name=do_post>
			<input type=submit value='Lisa valitud terminid taotlusse'>";
		}
		//$products = nee object_list(array("class_id" => CL_SHOP_PRODUCT,"lang_id" => array()));
//		$address_inst = get_instance(CL_CRM_ADDRESS);
		$ret = "<form action='' method=POST>Klassi nr:".
		html::textbox(array("name" => "class"))." Kauba/teenuse nimetus".html::textbox(array("name" => "product"))
		
		."<input type=hidden value=".$arr["ru"]." name=ru><input type=submit value='otsi'></form>";
//		foreach($address_inst->get_country_list() as $key=> $val)
//		{
//			
//			$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.document.exhibition_country.value=".$key."'>".$val."</a><br>";
		//	$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.changeform.exhibition_country.value=".$key."'>".$val."</a><br>";
//		}
		return $ret;
	}
	
	/**
		@attrib name=country_popup
	**/
	function country_popup()
	{
		$address_inst = get_instance(CL_CRM_ADDRESS);
		$ret = "";
		foreach($address_inst->get_country_list() as $key=> $val)
		{
			
			$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.document.exhibition_country.value=".$key."'>".$val."</a><br>";
		//	$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.changeform.exhibition_country.value=".$key."'>".$val."</a><br>";
		}
		return $ret;
	}
	
	function _get_applicants_table()
	{
		classload("vcl/table");
		$t = new vcl_table(array(
			"layout" => "generic",
		));
		
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "code",
			"caption" => t("Isikukood/reg.kood"),
		));
		$t->define_field(array(
			"name" => "representer",
			"caption" => t("Esindaja"),
		));
		$t->define_field(array(
			"name" => "change",
			"caption" => t(""),
		));
		//arr($_SESSION["patent"]["applicants"]);
		foreach($_SESSION["patent"]["applicants"] as $key =>$applicant)
		{
			if($applicant["applicant_type"])
			{
				$name = $applicant["name"];
			}
			else
			{
				$name = $applicant["firstname"]." ".$applicant["lastname"];
			}
			
			$t->define_data(array(
				"name" => $name,
				"code" => $applicant["code"],
				"representer" => html::radiobutton(array(
					"value" => $key,
					"checked" => ($_SESSION["patent"]["representer"] == $key) ? 1 : 0,
					"name" => "representer",
				)),
				"change" => html::href(array(
					"url" => "javascript:changeform.applicant_id.value=".$key.";changeform.submit();",//aw_url_change_var("change_applicant" , $key , get_ru()),
				//	"onClick"=>"self.disabled=true;submit_changeform(''); return false;",
					"caption" => t("Muuda"),
					//"title" => t("Muuda"),
				)),
			));
		}
		return $t->draw();
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
		//taotleja andmed liiguvad massiivi, et saaks mitu taotlejat sisse lugeda
		//miskeid tühju taotlejait poleks vaja... niiet:
		if($_POST["code"] || $_POST["name"] || $_POST["firstname"] || $_POST["lastname"])
		{
			$this->submit_applicant();
		}
		$this->save_uploads($_FILES);
		
		if($_POST["save"])
		{
			$this->save_data();
			$_SESSION["patent"] = null;
		}
		
		if($_POST["add_new_applicant"] || $_POST["applicant_id"] != "")
		{
			$_SESSION["patent"]["add_new_applicant"] = null;
			return aw_url_change_var("patent_id" , null , $arr["return_url"]);
			//return $arr["return_url"];
		}
		
		//viimasest lehest edasi
//		if($arr["data_type"] == 5)
//		{
//			return aw_url_change_var("data_type" , null , $arr["return_url"]);
//		}
		return aw_url_change_var("patent_id" , null , aw_url_change_var("data_type" , ($arr["data_type"]+1) , $arr["return_url"]));
	}
	
	function submit_applicant()
	{
		$applicant_vars = array("name", "firstname" , "lastname", "code", "street" , "city" , "index" , "country_code" , "phone" , "fax", "applicant_type" , "email");
//		$this->file_upload_vars = array("warrant" , "reproduction");
//		arr($_SESSION["patent"]);
		if($_SESSION["patent"]["change_applicant"] != "")
		{
			$n = $_SESSION["patent"]["change_applicant"];
// 			$_SESSION["patent"]["applicant_id"] = null;
// 			$_SESSION["patent"]["change_applicant"] = null;
		}
		else
		{
			$n = sizeof($_SESSION["patent"]["applicants"]);
		}
		foreach($applicant_vars as $var)
		{
			$_SESSION["patent"]["applicants"][$n][$var] = $_SESSION["patent"][$var];
			$_SESSION["patent"][$var] = null;
		}
//		arr($_SESSION["patent"]);
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
		if(is_oid($_SESSION["patent"]["id"]))
		{
			$patent = obj($_SESSION["patent"]["id"]);
		}
		else
		{
			$patent = new object();
			$patent->set_class_id(CL_PATENT);
			$patent->set_parent($_SESSION["patent"]["parent"]);
			$patent->save();
			$patent->set_name("Patent nr. ".$patent->id());
		}
		$this->save_trademark($patent);
		$this->save_priority($patent);
		$this->save_fee($patent);
		$this->save_applicants($patent);
		$this->final_save($patent);
		$patent->set_meta("products" , $_SESSION["patent"]["products"]);
		$patent->save();
		//unset($_SESSION["patent"]);
	}
	
	function save_applicants($patent)
	{
		$patent->set_prop("country" ,$_SESSION["patent"]["country"]);
		$address_inst = get_instance(CL_CRM_ADDRESS);
		foreach($_SESSION["patent"]["applicants"] as $key => $val)
		{
			$applicant = new object();
			$applicant->set_parent($patent->id());
			if($val["applicant_type"])
			{
				$applicant->set_class_id(CL_CRM_COMPANY);
				$type=1;
			}
			else
			{
				$type=0;
				$applicant->set_class_id(CL_CRM_PERSON);
			}
			$applicant->save();
		
			$address = new object();
			$address->set_class_id(CL_CRM_ADDRESS);
			$address->set_parent($applicant->id());
			
			$address->set_prop("aadress", $val["street"]);
			$address->set_prop("postiindeks" , $val["index"]);
			$address->set_prop("riik" , $address_inst->get_country_by_code($val["country_code"]));
			if($_SESSION["patent"]["city"])
			{
				$citys = new object_list(array("lang_id" => 1, "class_id" => CL_CRM_CITY, "name" => $val["city"]));
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
				$applicant->set_name($val["name"]);
				$applicant->set_prop("contact" , $address->id());
				$applicant->set_prop("reg_nr",$val["code"]);
			}
			else
			{
				$applicant->set_prop("firstname" , $val["firstname"]);
				$applicant->set_prop("lastname" , $val["lastname"]);
				$applicant->set_name($val["firstname"]." ".$val["lastname"]);
				$applicant->set_prop("address" , $address->id());
				$applicant->set_prop("personal_id" , $val["code"]);
			}
			$applicant->connect(array("to"=> $address->id(), "type" => "RELTYPE_ADDRESS"));
			$applicant->save();
			
			if($val["phone"])
			{
				$phone = new object();
				$phone->set_class_id(CL_CRM_PHONE);
				$phone->set_name($val["phone"]);
				$phone->set_prop("type" , "mobile");
				$phone->set_parent($applicant->id());
				$phone->save();
				$applicant->connect(array("to"=> $phone->id(), "type" => "RELTYPE_PHONE"));
			}
			if($val["email"])
			{
				$email = new object();
				$email->set_class_id(CL_ML_MEMBER);
				$email->set_name($val["email"]);
				$email->set_prop("mail" , $val["email"]);
				$email->set_parent($applicant->id());
				$email->save();
				$applicant->connect(array("to"=> $email->id(), "type" => "RELTYPE_EMAIL"));
			}
			if($val["fax"])
			{
				$phone = new object();
				$phone->set_class_id(CL_CRM_PHONE);
				$phone->set_name($val["fax"]);
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
			$patent->connect(array("to" => $applicant->id(), "type" => "RELTYPE_APPLICANT"));
			if($val["representer"] = $key){
				$patent->set_prop("applicant" , $applicant->id());
			}
			$patent->set_prop("applicant" , $applicant->id());
		}
		
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
		$patent->set_prop("additional_info" , $_SESSION["patent"]["additional_info"]);
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
			$patent->connect(array("to" => $applicant->id(), "type" => "RELTYPE_AUTHORIZED_PERSON"));
		}
		$patent->save();
	}
	
	
	
	function save_fee($patent)
	{
		$vars = array("request_fee" , "classes_fee" , "payer" , "doc_nr");
		foreach($vars as $var)
		{
			if($_SESSION["patent"][$var])
			{
				$patent->set_prop($var,$_SESSION["patent"][$var]);
			}
		}
		$payment_time = explode("/" , $_SESSION["patent"]["payment_date"]);

		$patent->set_prop("payment_date" , mktime(0,0,0,$payment_time[1], $payment_time[0],$payment_time[2]));
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
	
	/** Show patents added by user 
		
		@attrib name=my_patent_list is_public="1" caption="Minu patenditaotlused"
	
	**/
	function my_patent_list($args)
	{
		$uid = aw_global_get("uid");
	
		$section = aw_global_get("section");
		
		$obj_list = new object_list(array(
			"class_id" => CL_PATENT,
			"createdby" => $uid,
			"lang_id" => array(),
		));
		$tpl = "list.tpl";
		$this->read_template($tpl);
		lc_site_load("patent", $this);


//		$has = $this->is_admin();
		
		if ($this->is_template("LIST"))
		{

			$c = "";
			foreach($obj_list->arr() as $key => $patent)
			{
				$url = $section."/?patent_id=".$patent->id();
				$this->vars(array(
					"name" 	 	=> $patent->name(),
					"id"	 	=> $patent->id(),
					"url" 		=> $url,
				));
				$c .= $this->parse("LIST");
			}
			$this->vars(array(
				"LIST" => $c,
			));
		}
		return $this->parse();
	}



}
?>
