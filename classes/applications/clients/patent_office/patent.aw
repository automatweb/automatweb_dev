<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/patent.aw,v 1.62 2007/02/28 12:50:55 markop Exp $
// patent.aw - Patent 
/*

@classinfo syslog_type=ST_PATENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1




@default table=objects
@default group=general
@default field=meta
@default method=serialize

#GENERAL
//idee siis selles, et kes on connectitud, on nagu �iged inimesed ja kes �ks kes valitud, see on esindaja
	@property applicant type=relpicker reltype=RELTYPE_APPLICANT
	@caption Taotleja
	
	@property signed type=text store=no editonly=1
	@caption Allkirja staatus
	
	@property signatures type=text store=no editonly=1
	@caption Allkirjastajad
	
	@property job type=textbox store=no editonly=1
	@caption Allkirjastaja amet

	property country type=textbox
	caption P&auml;riltolumaa
	
	@property procurator type=relpicker reltype=RELTYPE_PROCURATOR
	@caption Volinik
	
	@property warrant type=fileupload reltype=RELTYPE_WARRANT form=+emb
	@caption Volikiri
	
	@property authorized_person type=relpicker reltype=RELTYPE_AUTHORIZED_PERSON
	@caption Volitatud isik
	
	@property additional_info type=textarea 
	@caption Lisainfo
	
	@property verified type=checkbox 
	@caption Kinnitatud

	@property exported type=checkbox caption=no
	@caption Eksporditud

	@property export_date type=date_select
	@caption Ekspordi kuup&auml;ev

	@property nr type=textbox
	@caption Taotluse number

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
	
	@property reproduction type=fileupload reltype=RELTYPE_REPRODUCTION form=+emb
	@caption Lisa reproduktsioon
	
	@property g_statues type=fileupload reltype=RELTYPE_G_STATUES form=+emb
	@caption Garantiim&auml;rgi p&otilde;hikiri
	
	@property c_statues type=fileupload reltype=RELTYPE_C_STATUES form=+emb
	@caption Kollektiivm&auml;rgi p&otilde;hikiri	
	
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
@caption N&auml;ituseprioriteet

	@property exhibition_name type=textbox
	@caption N&auml;ituse nimi
	
	@property exhibition_date type=date_select
	@caption Kuup&auml;ev
	
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
	
	@property payment_order type=fileupload reltype=RELTYPE_PAYMENT_ORDER form=+emb
	@caption Maksekorraldus
	

#saidilt lisamise propertyd
groupinfo web caption="Saidilt lisamine"
default group=web

	property procurator_menu type=relpicker reltype=RELTYPE_PROCURATOR_MENU
	caption Volinike kaust
	
	property bank_payment type=relpicker reltype=RELTYPE_BANK_PAYMENT
	caption Pangamakse objekt

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

@reltype AUTHORIZED_PERSON value=10 clid=CL_CRM_PERSON
@caption Volitatud isik

@reltype BANK_PAYMENT value=11 clid=CL_BANK_PAYMENT
@caption Pangalingi objekt

@reltype C_STATUES value=12 clid=CL_FILE
@caption Kollektiivp&otilde;hikiri

@reltype G_STATUES value=13 clid=CL_FILE
@caption Garantiip&otilde;hikiri

@reltype PAYMENT_ORDER value=14 clid=CL_FILE
@caption Volikiri

@reltype TRADEMARK_STATUS value=15 clid=CL_TRADEMARK_STATUS
@caption Staatus

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
		$this->text_vars = array("name" , "firstname" , "lastname" ,  "code" , "street", "city" ,"index", "country_code" , "phone" , "email" , "fax" ,  "undefended_parts" , "word_mark", "convention_nr"  , "convention_country", "exhibition_name" , "exhibition_country" , "request_fee" , "classes_fee" , "payer" , "doc_nr","authorized_person_firstname", "authorized_person_lastname","authorized_person_code", "correspond_street","correspond_city","correspond_index","correspond_country_code", "job");
		$this->text_area_vars = array("colors" , "trademark_character", "element_translation", "additional_info");
		$this->file_upload_vars = array("warrant" , "reproduction" , "payment_order", "g_statues","c_statues");
		$this->date_vars = array("payment_date" , "exhibition_date", "convention_date");
		$this->types = array(t("S&otilde;nam&auml;rk"),t("Kujutism&auml;rk"),t("Kombineeritud m&auml;rk"),t("Ruumiline m&auml;rk"));
		$this->trademark_types = array(t("Kollektiivkaubam&auml;rk"),t("Garantiim&auml;rk"));
		$this->country_popup_link_vars = array("convention_country", "exhibition_country", "country_code");
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "signed":
				if(!aw_ini_get("file.ddoc_support"))
				{
					return PROP_IGNORE;
				}
				$ddoc_inst = get_instance(CL_DDOC);
				$res = $this->is_signed($arr["obj_inst"]->id());
				switch($res["status"])
				{
					case 1:
						$url = $ddoc_inst->sign_url(array(
							"ddoc_oid" => $res["ddoc"],
						));
						$ddoc = obj($res["ddoc"]);
						$add_sig = html::href(array(
							"url" => "#",
							"caption" => t("Lisa allkiri"),
							"onClick" => "aw_popup_scroll(\"".$url."\", \"".t("Allkirjastamine")."\", 410, 250);",
						));
						$ddoc_link = html::href(array(
							"url" => $this->mk_my_orb("change", array(
								"id" => $ddoc->id(),
								"return_url" => get_ru(),
							), CL_DDOC),
							"caption" => t("DigiDoc konteinerisse"),
						));
						$prop["value"] = $add_sig." (".$ddoc_link.")";
						break;
					case 0:
						$url = $ddoc_inst->sign_url(array(
							"ddoc_oid" => $res["ddoc"],
						));
						$ddoc = obj($res["ddoc"]);
						$add_sig = html::href(array(
							"url" => "#",
							"caption" => t("Allkirjasta"),
							"onClick" => "aw_popup_scroll(\"".$url."\", \"".t("Allkirjastamine")."\", 410, 250);",
						));
						$ddoc_link = html::href(array(
							"url" => $this->mk_my_orb("change", array(
								"id" => $ddoc->id(),
								"return_url" => get_ru(),
							), CL_DDOC),
							"caption" => t("DigiDoc konteiner"),
						));
						$prop["value"] = $add_sig." (".$ddoc_link.")";

						break;
					case -1:
						$url = $ddoc_inst->sIgn_url(array(
							"other_oid" => $arr["obj_inst"]->id(),
						));
						$prop["value"] = html::href(array(
							"url" => "#",
							"caption" => t("Allkirjasta fail"),
							"onClick" => "aw_popup_scroll(\"".$url."\", \"".t("Allkirjastamine")."\", 410, 250);",

						));
						break;
				}

				break;
			case "signatures":
				if(!aw_ini_get("file.ddoc_support"))
				{
					return PROP_IGNORE;
				}
				$re = $this->is_signed($arr["obj_inst"]->id());
				if($re["status"] != 1)
				{
					return PROP_IGNORE;
				}
				$ddoc_inst = get_instance(CL_DDOC);
				$signs = $ddoc_inst->get_signatures($re["ddoc"]);
				foreach($signs as $sig)
				{
					$sig_nice[] = sprintf(t("%s, %s (%s) - %s"), $sig["signer_ln"], $sig["signer_fn"], $sig["signer_pid"], date("H:i d/m/Y", $sig["signing_time"]));
				}
				$prop["value"] = join("<br/>", $sig_nice);
				break;
	
			case "type":
				$prop["options"] = $this->types;
				break;
			case "trademark_type":
				$prop["options"] = $this->trademark_types;
				break;
			case "products_and_services_tbl":
				$this->_get_products_and_services_tbl($arr);
				break;
			//-- get_property --//
//			case "convention_nr":
//				if ($prop["value"] == "" && $arr["obj_inst"]->prop("verified"))
//				{
//					$i = get_instance(CL_CRM_NUMBER_SERIES);
//					$prop["value"] = $i->find_series_and_get_next(CL_PATENT);
//				}
//				break;
			case "export_date":
				$status = $this->get_status($arr["obj_inst"]);
				if($status->prop("exported"))
				{
					$prop["type"] = "text";
					$prop["value"] = date("j:m:Y h:i" , $prop["value"]);
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case "exported":
				$status = $this->get_status($arr["obj_inst"]);
				if($status->prop("exported"))
				{
					$prop["type"] = "text";
					$prop["value"] = t("Eksporditud");
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case "warrant":
			case "reproduction":
			case "payment_order":
			case "g_statues":
		};
		return $retval;
	}

	
	/**
		@comment 
	**/
	function is_signed($oid)
	{
		if(!is_oid($oid))
		{
			error::raise(array(
				"msg" => t("Vale objekti id!"),
			));
		}
		$c = new connection();
		$ret = $c->find(array(
			"from.class_id" => CL_DDOC,
			"type" => "RELTYPE_SIGNED_FILE",
			"to" => $oid,
		));
		$return = array();
		if(count($ret))
		{
			$ret = current($ret);
			$ret = $ret["from"];
			$inst = get_instance(CL_DDOC);
			$tmp = $inst->is_signed($ret);
			$return["status"] = $tmp?1:0;
			$return["ddoc"] = $ret;
		}
		else
		{
			$return["status"] = -1;
		}
		return $return;
	}


	function _get_products_and_services_tbl(&$arr)
	{
		classload("vcl/table");
		$t = &$arr["prop"]["vcl_inst"];
		
		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
		));
//		$t->define_field(array(
//			"name" => "class_name",
//			"caption" => t("Klassi nimi"),
//		));
		$t->define_field(array(
			"name" => "prod",
			"caption" => t("Kaup/teenus"),
		));
	
		if(is_array($arr["obj_inst"]->meta("products")))
		{
			foreach($arr["obj_inst"]->meta("products") as $key=> $val)
			{
				$product = obj($key);
			//	$parent = obj($product->parent());
				$t->define_data(array(
					"prod" => html::textarea(array("name" => "products[".$key."]" , "value" => $val, )),
					"class" => $key,
//					"class_name" => $parent->name(),
	//				"oid"	=> $prod->id(),
				));
			}
		}
		return $t->draw();
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
			case "products_and_services_tbl":
				if(is_array($arr["request"]["products"]))
				{
					$arr["obj_inst"] -> set_meta("products" , $arr["request"]["products"]);
				}
				break;
			
			case "warrant":
			case "reproduction":
			case "payment_order":
			case "g_statues":
			case "c_statues":
				$image_inst = get_instance(CL_IMAGE);
				$file_inst = get_instance(CL_FILE);
				if(array_key_exists($prop["name"] , $_FILES))
				{
					if($_FILES[$prop["name"]]['tmp_name'])
					{
						$id = $file_inst->save_file(array(
							"parent" => $arr["obj_inst"]->id(),
							"content" => $image_inst->get_file(array(
								"file" => $_FILES[$prop["name"]]['tmp_name'],
							)),
							"name" => $_FILES[$prop["name"]]['name'],
							"type" => $_FILES[$prop["name"]]['type'],
						));
						$arr["obj_inst"]->set_prop($prop["name"], $id);
						$arr["obj_inst"]->connect(array("to" => $id, "type" => "RELTYPE_".strtoupper($prop["name"])));
						$arr["obj_inst"]->save();
					}
				}
				return PROP_IGNORE;
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

	function request_execute ($this_object)
	{
		return $this->show (array (
			"id" => $this_object->id(),
		));
	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	/** Show trademark applications
		
		@attrib name=show is_public="1" all_args=1
	
	**/
	function show($arr)
	{
		$this->read_template("show.tpl");
		
		if(is_oid($arr["id"]) && $this->can("view" , $arr["id"]))
		{
			$ob = new object($arr["id"]);
			$stat_obj = $this->get_status($ob);
			$this->vars(array(
				"name" => $stat_obj->prop("name"),
			));
			$data = $this->get_data_from_object($arr["id"]);
			$prods = $ob->meta("products");
			$_SESSION["patent"]["id"] = $arr["id"];
		}
		else
		{
			$data = $this->web_data($arr);
			$prods = $_SESSION["patent"]["products"];
			$ob = obj($_SESSION["patent"]["id"]);
		}
		
		$stat_obj = $this->get_status($ob);
		if($_POST["send"])
		{
			$this->set_sent(array("add_obj" => $arr["add_obj"], ));
			
		}
		if($_POST["print"] && !$_POST["send"])
		{
			$data["print"] = "<script language='javascript'>
				window.print();
			</script>;";
		}
		else
		{
			$data["print"] = "<input type='button' value='".t("Prindi")."' class='nupp' onClick='javascript:document.changeform.submit();'>";
			$data["pdf"] = "<a href=".$this->mk_my_orb("pdf", array("print" => 1 , "id" => $_SESSION["patent"]["id"], "add_obj" => $arr["alias"]["to"]) , CL_PATENT)."><input type='button' value='Salvesta pdf' class='nupp' href=''></a><br>";

		}
		
		if($arr["sign"] && !$_POST["print"])
		{
			$ddoc_inst = get_instance(CL_DDOC);
			$url = $ddoc_inst->sign_url(array(
				"other_oid" =>$arr["id"],
			));
			$data["sign"] = "<input type='button' value='".t("Allkirjasta")."' class='nupp' onClick='javascript:window.open(\"".$url."\",\"\", \"toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600\");'>";
		}
		
		if($this->can("view" , $ob->id()))
		{
			$status = $this->is_signed($ob->id());
		}	
		if($status["status"] > 0 && !$stat_obj->prop("nr") && !$_POST["print"])
		{
			$data["send"] = '<input type="submit" value="'.t("Saadan taotluse").'" class="nupp" onClick="javascript:document.getElementById(\'send\').value=\'1\';
			document.changeform.submit();
			">';
		}
		
		if($arr["sign"] && !$_POST["print"] && !($status["status"] > 0))
		{
			$ddoc_inst = get_instance(CL_DDOC);
			$url = $ddoc_inst->sign_url(array(
				"other_oid" =>$arr["id"],
			));
			$data["sign"] = "<input type='button' value='".t("Allkirjasta")."' class='nupp' onClick='javascript:window.open(\"".$url."\",\"\", \"toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600\");'>";
		}
		
		$this->vars($data);
		$p = "";
		foreach($prods as $key => $product)
		{
			$this->vars(array("product" => $product, "class"=> $key));
			$p.=$this->parse("PRODUCTS");
		}
		$this->vars(array("PRODUCTS" => $p));
		//arr($data);
//arr($this->vars);
		foreach($data as $prop => $val)
		{
			if($val && (substr_count($prop, 'value') || substr_count($prop, 'text')) && $val!= " " && !is_array($val))
			{
				$str = strtoupper(str_replace("_value","",str_replace("_text","",$prop)));
				$this->vars(array($str => $this->parse($str)));
			}
		}
				
		if($data["convention_nr_value"])
		{
			$this->vars(array("CONVENTION" => $this->parse("CONVENTION")));
		}
		if($data["exhibition_name_value"])
		{
			$this->vars(array("EXHIBITION" => $this->parse("EXHIBITION")));
		}
		if($data["procurator_text"])
		{
			$this->vars(array("PROCURATOR_TEXT" => $this->parse("PROCURATOR_TEXT")));
		}
		if($data["authorized_person_firstname_value"] || $data["authorized_person_lastname_value"] || $data["authorized_person_code_value"])
		{
			$this->vars(array("AUTHORIZED_PERSON" => $this->parse("AUTHORIZED_PERSON")));
		}
		return $this->parse();
	}

	function get_data_from_object($id)
	{
		$o = obj($id);
		$props = array();
		$vars = array("job" , "undefended_parts" , "word_mark", "convention_nr"  , "convention_country", "exhibition_name" , "exhibition_country" , "request_fee" , "classes_fee" , "doc_nr", "payer");
		
		//siia panev miskid muutujad mille iga ringi peal �ra kustutab... et uuele taotlejale vana info ei j��ks
		$del_vars = array("name_value" , "email_value" , "phone_value" ,
				"fax_value" , "code_value" ,"email_value" , "street_value" ,"index_value" ,"country_code_value","city_value","correspond_street_value",
				"correspond_index_value" ,
				"correspond_country_code_value" ,
				"correspond_city_value");
		
		$a = "";
		$correspond_address = "";
		$address_inst = get_instance(CL_CRM_ADDRESS);
		if($this->is_template("APPLICANT"))
		{
			foreach($o->connections_from(array("type" => "RELTYPE_APPLICANT")) as $key => $c)
			{
				foreach($del_vars as $del_var)
				{
					unset($this->vars[$del_var]);
				}
				$applicant = $c->to();
				$this->vars(array(
					"name_value" => $applicant->name(),
					"email_value" => $applicant->prop("email"),
					"phone_value" => $applicant->prop("phone"),
					"fax_value" => $applicant->prop_str("fax"),
				));
				if($applicant->class_id() == CL_CRM_PERSON)
				{
					$this->vars(array(
						"code_value" => $applicant->prop("personal_id"),
					));
					$address = $applicant->prop("address");
					//$correspond_address = $o->prop("correspond_address");
					$this->vars(array(
						"email_value" => $applicant->prop("email.mail"),
						"phone_value" => $applicant->prop("phone.name"),
						"fax_value" => $applicant->prop("fax.name"),
						"name_caption" => t("Nimi"),
						"reg-code_caption" => ("Isikukood"),
						"name_value" => $applicant->prop("firstname"). " " . $applicant->prop("lastname"),
						"P_ADDRESS" => $this->parse("P_ADDRESS"),
					));
				}
				else
				{
					$this->vars(array(
						"email_value" => $applicant->prop("email_id.mail"),
						"phone_value" => $applicant->prop("phone_id.name"),
						"fax_value" => $applicant->prop("telefax_id.name"),
						"code_value" => $applicant->prop("reg_nr"),
						"name_caption" => t("Nimetus"),
						"reg-code_caption" => t("Reg.kood"),
						"CO_ADDRESS" => $this->parse("CO_ADDRESS"),
					));//arr($this->parse("P_ADDRESS"));
					
					$address = $applicant->prop("contact");
				}
				$correspond_address = $applicant->prop("correspond_address");
				if(is_oid($address) && $this->can("view" , $address))
				{
					$address_obj = obj($address);
					$this->vars(array(
						"street_value" => $address_obj->prop("aadress"),
						"index_value" => $address_obj->prop("postiindeks"),
						"country_code_value" => $address_inst->get_country_code($address_obj->prop("riik")),
						"city_value" => $address_obj->prop_str("linn"),
					));
				}
				if(is_oid($correspond_address))
				{
					$correspond_address_obj = obj($correspond_address);
					$this->vars(array(
						"correspond_street_value" => $correspond_address_obj->prop("aadress"),
						"correspond_index_value" => $correspond_address_obj->prop("postiindeks"),
						"correspond_country_code_value" => $address_inst->get_country_code($correspond_address_obj->prop("riik")),
						"correspond_city_value" => $correspond_address_obj->prop_str("linn"),
					));
				}
				
				foreach($del_vars as $var)
				{
					if($this->vars[$var])
					{
						$str = strtoupper(str_replace("_value","",str_replace("_text","",$var)));
						$this->vars(array($str => $this->parse($str)));
					}
				}
				if($this->vars["street_value"] || $this->vars["city_value"] || $this->vars["index_value"] || $this->vars["country_code_value"])
				{
					$this->vars(array("ADDRESS" => $this->parse("ADDRESS")));
				}
				if($this->vars["correspond_street_value"] || $this->vars["correspond_city_value"] || $this->vars["correspond_index_value"] || $this->vars["correspond_country_code_value"])
				{
					$this->vars(array("CORRESPOND_ADDRESS" => $this->parse("CORRESPOND_ADDRESS")));
				}
				if($this->vars["phone_value"] || $this->vars["email_value"] || $this->vars["fax_value"])
				{
					$this->vars(array("CONTACT" => $this->parse("CONTACT")));
				}
				$a.= $this->parse("APPLICANT");
			}
		}
		
		$data = array();
		$data["APPLICANT"] = $a;
		$tr = $o->prop("trademark_type");
		$data["trademark_type_text"] = $tr[0]? $this->trademark_types[0]:"";
		$data["trademark_type_text"].= " ";
		$data["trademark_type_text"].= $tr[1]? $this->trademark_types[1]:"";

		$data["type_text"] = $this->types[$o->prop("type")];
		if(is_oid($o->prop("authorized_person")))
		{
			$ap = obj($o->prop("authorized_person"));
			$data["authorized_person_firstname_value"] = $ap->prop("firstname");
			$data["authorized_person_lastname_value"] = $ap->prop("lastname");
			$data["authorized_person_code_value"] = $ap->prop("personal_id");
		}
		foreach($this->text_area_vars as $prop)
		{
			$data[$prop."_value"] = $o->prop($prop);
		}
		foreach($vars as $prop)
		{
			$data[$prop."_value"] = $o->prop($prop);
		}
		foreach($this->date_vars as $prop)
		{
			if($o->prop($prop) > 0)
			{
				$data[$prop."_value"] = date("j.m.Y" ,$o->prop($prop));
			}
		}
		
		$file_inst = get_instance(CL_FILE);
		foreach($this->file_upload_vars as $var)
		{
			if(is_oid($o->prop($var)))
			{
				$file = obj($o->prop($var));
				if($var == "reproduction")
				{
					$data[$var."_value"] = $this->get_right_size_image($file->id());
				}
				else
				{
					$data[$var."_value"] = html::href(array(
						"url" => $file_inst->get_url($file->id(), $file->name()),
						"caption" => $file->name(),
						"target" => "New window",
					));
				}
			}
		}
		$data["procurator_text"] = $o->prop_str("procurator");
		return $data;
	}

	function fill_session($id)
	{
		$patent = obj($id);
		$status = $this->get_status($patent);
		$property_vars = array("job" , "procurator" , "additional_info", "type","undefended_parts", "word_mark" , "colors" , "trademark_character", "element_translation", "trademark_type" ,
			 "priority" , "convention_nr" , "convention_country" , "exhibition_name", "exhibition_country", "exhibition" , "request_fee" , "classes_fee",
			 "payer" , "doc_nr" , "warrant" , "reproduction" , "payment_order", "g_statues","c_statues");
	
		foreach($property_vars as $var)
		{
			$_SESSION["patent"][$var] = $patent->prop($var);
		}
		
		foreach($this->date_vars as $var)
		{
			$_SESSION["patent"][$var] = $patent->prop($var);
			//$_SESSION["patent"][$var] = date("d",$patent->prop($var))."/".date("m",$patent->prop($var))."/".date("Y",$patent->prop($var));
			
		}
		if(isset($_SESSION["patent"]["trademark_type"][0]))
		{	
			$_SESSION["patent"]["co_trademark"] = 1;
		}
		if(isset($_SESSION["patent"]["trademark_type"][1]))
		{
			$_SESSION["patent"]["guaranty_trademark"] = 1;
		}
		
		$_SESSION["patent"]["products"] = $patent->meta("products");

		$address_inst = get_instance(CL_CRM_ADDRESS);
		$person_inst = get_instance(CL_CRM_PERSON);
		$_SESSION["patent"]["representer"] = $patent->prop("applicant");
		foreach($patent->connections_from(array("type" => "RELTYPE_APPLICANT")) as $key => $c)
		{
			$o = $c->to();
			$key = $o->id();
			$_SESSION["patent"]["applicant_id"] = $key;
		//	$_SESSION["patent"]["change_applicant"] = $key;
			$_SESSION["patent"]["applicants"][$key]["name"] = $o->name();
			if($o->class_id() == CL_CRM_COMPANY)
			{
				$_SESSION["patent"]["applicants"][$key]["applicant_type"] = 1;
				$address = $o->prop("contact");
				$_SESSION["patent"]["applicants"][$key]["phone"] = $o->prop("phone_id.name");
				$_SESSION["patent"]["applicants"][$key]["email"] = $o->prop("email_id.mail");
				$_SESSION["patent"]["applicants"][$key]["fax"] = $o->prop("telefax_id.name");
				$_SESSION["patent"]["applicants"][$key]["code"] = $o->prop("reg_nr");
			}
			else
			{
				$_SESSION["patent"]["applicants"][$key]["applicant_type"] = 0;
				$_SESSION["patent"]["applicants"][$key]["firstname"] = $o->prop("firstname");
				$_SESSION["patent"]["applicants"][$key]["lastname"] = $o->prop("lastname");
				$address = $o->prop("address");
				$correspond_address = $o->prop("correspond_address");
				$_SESSION["patent"]["applicants"][$key]["phone"] = $o->prop("phone.name");
				$_SESSION["patent"]["applicants"][$key]["email"] = $o->prop("email.mail");
				$_SESSION["patent"]["applicants"][$key]["fax"] = $o->prop("fax.name");
				$_SESSION["patent"]["applicants"][$key]["code"] = $o->prop("personal_id");
			}
			if(is_oid($address) && $this->can("view" , $address))
			{
				$address_obj = obj($address);
				$_SESSION["patent"]["applicants"][$key]["street"] = $address_obj->prop("aadress");
				$_SESSION["patent"]["applicants"][$key]["index"] = $address_obj->prop("postiindeks");
				if(is_oid($address_obj->prop("linn")) && $this->can("view" , $address_obj->prop("linn")))
				{
					$city = obj($address_obj->prop("linn"));
					$_SESSION["patent"]["applicants"][$key]["city"] = $city->name();
				}
				$_SESSION["patent"]["applicants"][$key]["country_code"] = $address_inst->get_country_code($address_obj->prop("riik"));
				if($_SESSION["patent"]["applicants"][$key]["country_code"] == "EE")
				{
					$_SESSION["patent"]["applicants"][$key]["country"] = 0;
				}
				else
				{
					$_SESSION["patent"]["applicants"][$key]["country"] = 1;
				}
			}
			if(is_oid($correspond_address))
			{
				$correspond_address_obj = obj($correspond_address);
				$_SESSION["patent"]["applicants"][$key]["correspond_street"] = $correspond_address_obj->prop("aadress");
				$_SESSION["patent"]["applicants"][$key]["correspond_index"] = $correspond_address_obj->prop("postiindeks");
				if(is_oid($correspond_address_obj->prop("linn")) && $this->can("view" , $correspond_address_obj->prop("linn")))
				{
					$city = obj($correspond_address_obj->prop("linn"));
					$_SESSION["patent"]["applicants"][$key]["correspond_city"] = $city->name();
				}
				 $_SESSION["patent"]["applicants"][$key]["correspond_country_code"] = $address_inst->get_country_code($correspond_address_obj->prop("riik"));
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
	
	function check_and_give_rights($oid)
	{
		$o = obj($oid);
		$uid = aw_global_get("uid");
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
//		$code = $p->prop("personal_id");
//		if($code && $code == $o->prop("authorized_person.personal_id"))
		$name = $p->name();
		if($name && $name == $o->name())
		{
			$u = get_instance("users_user");
			$gid = $u->get_gid_by_uid($uid);
			$grp = obj($u->get_oid_for_gid($gid));
			$o->acl_set($grp, array("can_view" => 1, "can_edit" => 1, "can_delete" => 0));
		}
	}
	
	/** 
		@attrib name=parse_alias is_public="1" caption="Change"
	**/
	function parse_alias($arr)
	{
		enter_function("patent::parse_alias");

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
			return $this->my_patent_list($arr);//$this->mk_my_orb("my_patent_list", array());
		}
		if($arr["data_type"] == 7)
		{
			$arr["unsigned"] = 1;
			return $this->my_patent_list($arr);//$this->mk_my_orb("my_patent_list", array());
		}

		if(isset($_GET["trademark_id"]))
		{
			$_SESSION["patent"] = null;
			if(is_oid($_GET["trademark_id"]) && $this->can("view" , $_GET["trademark_id"]))
			{
				$_SESSION["patent"]["id"] = $_GET["trademark_id"];
				$this->fill_session($_GET["trademark_id"]);
				$this->check_and_give_rights($_GET["trademark_id"]);
			}
			header("Location:".$_SERVER["SCRIPT_URI"]."?section=".$_GET["section"]."&data_type=0");
			die();
		}
		
		if(is_oid($_SESSION["patent"]["id"]) )
		{
			$o = obj($_SESSION["patent"]["id"]);
			$status = $this->get_status($o);
			if($status->prop("nr") || $status->prop("verified"))
			{
				return $this->show(array(
					"id" => $o->id(),
					"add_obj" => $arr["alias"]["to"],
				));
			}
		}
		
		$tpl = $this->info_levels[$arr["data_type"]].".tpl";
		$this->read_template($tpl);
		lc_site_load("patent", &$this);
		$this->vars($this->web_data($arr));
		
		$this->vars(array("reforb" => $this->mk_reforb("submit_data",array(
				"data_type"	=> $arr["data_type"],
				"return_url" 	=> get_ru(),
				"add_obj" 	=> $arr["alias"]["to"],
			)),
		));
	
		//l�petab ja salvestab
		if($arr["data_type"] == 5)
		{
			$this->vars(array("reforb" => $this->mk_reforb("submit_data",array(
					"save" => 1,
					"return_url" 	=> get_ru(),
					"add_obj" 	=> $arr["alias"]["to"],
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
		$us = get_instance(CL_USER);
		$this->users_person = new object($us->get_current_person());
/* 		if(is_object($this->users_person))
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
		}*/
	}
	
	function get_js($arr)
	{
		$js = "";
		if($_SESSION["patent"]["country"])
		{
			;
		}
		else
		{
			;
		}
		
		if($_GET["data_type"] == 1)
		{
			if(!$_SESSION["patent"]["type"])
			{
				$js.='document.getElementById("reproduction_row").style.display = "none";';
				$js.='document.getElementById("color_row").style.display = "none";';
			}
			if($_SESSION["patent"]["type"] == 1)
			{
				$js.='document.getElementById("wordmark_row").style.display = "none";';
			}
			if(!$_SESSION["patent"]["guaranty_trademark"])
			{
				$js.='document.getElementById("g_statues_row").style.display = "none";';
			}
			if(!$_SESSION["patent"]["co_trademark"])
			{
				$js.='document.getElementById("c_statues_row").style.display = "none";';
			}
			if($_SESSION["patent"]["type"])
			{
				$js.='document.getElementById("foreignlangelements_row").style.display = "none"';
			}
		}
		
		if(!$_GET["data_type"])
		{
			if(!is_oid($_SESSION["patent"]["procurator"]))
			{
				$js.= 'document.getElementById("warrant_row").style.display = "none";';
				$js.= 'document.getElementById("remove_procurator").style.display = "none";';
			}
			if($_SESSION["patent"]["applicant_type"])
			{
				$js.='document.getElementById("lastname_row").style.display = "none";
				document.getElementById("firstname_row").style.display = "none";
	
				document.getElementById("p_adr").style.display="none";
				document.getElementById("livingplace_type").style.display="none";
				';
			}
			else
			{
				$js.='document.getElementById("reg_code").style.display = "none";
				document.getElementById("name_row").style.display = "none";
				document.getElementById("co_adr").style.display="none";
				document.getElementById("co_livingplace_type").style.display="none";
				';
			}
		}
		return $js;
	}

	/**
		@attrib name=error_popup all_args=1
	**/
	function error_popup($arr)
	{
		die($arr["error"]."\n<br>"."<input type=button value='OK' onClick='javascript:window.close();'>");
	}

	function get_applicant_sub()
	{
		$applicant_vars = array("name", "firstname" , "lastname", "code", "street" , "city" , "index" , "country_code" , "phone" , "fax", "applicant_type" , "email", "correspond_country_code","correspond_street","correspond_index","correspond_city", "country");
		$a = "";
		foreach($_SESSION["patent"]["applicants"] as $key => $val)
		{
			foreach($applicant_vars as $var)
			{
				$this->vars(array($var."_value" => $_SESSION["patent"]["applicants"][$key][$var]));
				if($_SESSION["patent"]["applicants"][$key]["type"])
				{
					$this->vars(array("name_caption" => t("Nimetus"),
						"reg-code_caption" => t("Reg.kood"),
						"CO_ADDRESS" => $this->parse("CO_ADDRESS"),
						"P_ADDRESS" => "",
					));
				}
				else
				{
					$this->vars(array("name_caption" => t("Nimi"),
						"reg-code_caption" => t("Isikukood"),
						"P_ADDRESS" => $this->parse("P_ADDRESS"),
						"CO_ADDRESS" => "",
					));
				}
				if($_SESSION["patent"]["applicants"][$key][$var])
				{
					$str = strtoupper($var);
					$this->vars(array($str => $this->parse($str)));
				}
			}
 			if($this->vars["street_value"] || $this->vars["city_value"] || $this->vars["index_value"] || $this->vars["country_code_value"])
 			{
 				$this->vars(array("ADDRESS" => $this->parse("ADDRESS")));
 			}
 			if($this->vars["correspond_street_value"] || $this->vars["correspond_city_value"] || $this->vars["correspond_index_value"] || $this->vars["correspond_country_code_value"])
 			{
 				$this->vars(array("CORRESPOND_ADDRESS" => $this->parse("CORRESPOND_ADDRESS")));
 			}
 			if($this->vars["phone_value"] || $this->vars["email_value"] || $this->vars["fax_value"])
 			{
 				$this->vars(array("CONTACT" => $this->parse("CONTACT")));
 			}
			$a.= $this->parse("APPLICANT");
		}
		return $a;
	}

	function web_data($arr)
	{
		$data = $this->get_vars($arr);
		
		$data["data_type"] = $arr["data_type"];
		$data["data_type_name"] = $this->info_levels[$arr["data_type"]];
		$this->get_user_data($arr);
		
		if($this->is_template("APPLICANT"))
		{
			$data["APPLICANT"] = $this->get_applicant_sub();
		}
		
		$data["js"] = $this->get_js();
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
		foreach($_SESSION["patent"] as $key => $val)
		{
			$data[$key."_value"] =  $val;
		}
		
		$file_inst = get_instance(CL_FILE);
		$image_inst = get_instance(CL_IMAGE);
		foreach($this->file_upload_vars as $var)
		{
			$data[$var] = html::fileupload(array("name" => $var."_upload"));
			if(is_oid($_SESSION["patent"][$var]) && $this->can("view" , $_SESSION["patent"][$var]))
			{
				$file = obj($_SESSION["patent"][$var]);
				if($var == "reproduction")
				{
					$data[$var."_value"] = $this->get_right_size_image($_SESSION["patent"][$var]);
				}
				else
				{
					$data[$var."_value"] = html::href(array(
						"url" => $file_inst->get_url($file->id(), $file->name()),
						"caption" => $file->name(),
						"target" => "New window",
					));
				}
			}
		//	$data[$val."_value"] = $image_inst->make_img_tag_wl($_SESSION["patent"][$var]);
		}
		if($_SESSION["patent"]["reproduction"])
		{
			$data["image_set"] = 1;
		}
		foreach($this->date_vars as $var)
		{
			if(is_array($_SESSION["patent"][$var]))
			{
				$_SESSION["patent"][$var] = mktime(0,0,0,$_SESSION["patent"][$var]["month"],$_SESSION["patent"][$var]["day"],$_SESSION["patent"][$var]["year"]);
			}
			if(!is_array($_SESSION["patent"][$var]) && !($_SESSION["patent"][$var] >1))
			{
				$_SESSION["patent"][$var] = -1;
			}
			$data[$var] = html::date_select(array("name" => $var, "value" => $_SESSION["patent"][$var] , "buttons" => 1));
			if($_SESSION["patent"][$var] > 0)
			{
				$data[$var."_value"] = date("j.m.Y" ,$_SESSION["patent"][$var]);
			}
		}

		
		//siia siis miski tingimus, et on makstud jne... siis ei tohi muuta saada enam
		if(true)
		{
			if(!is_array($_SESSION["patent"]["payment_date"]) && !($_SESSION["patent"]["payment_date"]>1))
			{
			//	$data["payment_date"] = html::date_select(array("name" => $var, "value" => time() , "buttons" => 1));
				$data["payment_date_value"] = null;
			}
			$p_vars = array("request_fee" , "classes_fee" );
			foreach($p_vars as $var)
			{
				$data[$var] = $_SESSION["patent"][$var];
			}
//			if($_SESSION["patent"]["payment_date"]>1)
//			{
//				$data["payment_date"] = date("j.m.Y" , $_SESSION["patent"]["payment_date"]);
//			}
//			else
//			{
//				$data["payment_date"] = "";
//			}
		}
		if($_SESSION["patent"]["errors"])
		{
			//$js.= 'alert("'.$_SESSION['patent']['errors'].'");';
			$data["error"] = $_SESSION['patent']['errors'];
			$_SESSION["patent"]["errors"] = null;
		}
		return $data;
	}
	
	function get_right_size_image($oid)
	{
		$image_inst = get_instance(CL_IMAGE);
		$image = obj($oid);
		$fl = $image->prop("file");
		if (!empty($fl))
		{
			// rewrite $fl to be correct if site moved
			$fl = basename($fl);
			$fl = $this->cfg["site_basedir"]."/files/".$fl{0}."/".$fl;
			$sz = @getimagesize($fl);
		}
		if($sz[0] > 200)
		{
			$sz[1] = $sz[1]/($sz[0]/200);
			$sz[0] = 200;
		}
		if($sz[1] > 200)
		{
			$sz[0] = $sz[0]/($sz[1]/200);
			$sz[1] = 200;
		}
		$ret =  $image_inst->make_img_tag_wl($oid, "", "" , array(
				"height" => $sz[1],
				"width" => $sz[0],
		));
		//arr($ret);
		return $ret;
	}
	
	function get_vars($arr)
	{
		$data = array();
		
		if(isset($_SESSION["patent"]["delete_applicant"]))
		{
			unset($_SESSION["patent"]["applicants"][$_SESSION["patent"]["delete_applicant"]]);
			unset($_SESSION["patent"]["delete_applicant"]);
		}
		if(!$_SESSION["patent"]["applicant_id"] && sizeof($_SESSION["patent"]["applicants"]))
		{
			$_SESSION["patent"]["applicant_id"] = reset(array_keys($_SESSION["patent"]["applicants"]));
		}
		if($_SESSION["patent"]["add_new_applicant"])
		{
			$_SESSION["patent"]["add_new_applicant"] = null;
			$_SESSION["patent"]["change_applicant"] = null;
			$_SESSION["patent"]["applicant_id"] = null;
		}
		elseif(isset($_SESSION["patent"]["applicant_id"]))
		{
			$this->_get_applicant_data();
			$data["change_applicant"] = $_SESSION["patent"]["applicant_id"];
		//	$data["applicant_id"] = $_SESSION["patent"]["applicant_id"];
			//$data["applicant_id"] = $_SESSION["patent"]["applicant_id"];
			$_SESSION["patent"]["change_applicant"] = null;
			$_SESSION["patent"]["applicant_id"] = null;
		}
		else
		{
			$data["applicant_no"] = sizeof($_SESSION["patent"]["applicants"]) + 1;
		}
		//nendesse ka siis see tingumus, et muuta ei saa

		if(true)
		{
/*			$classes = array();
			if(is_array($_SESSION["patent"]["products"]) && sizeof($_SESSION["patent"]["products"]))
			{
				foreach($_SESSION["patent"]["products"] as $key=> $val)
				{
					if($this->can("view", $key))
					{
						$prod = obj($key);
						$classes[$prod->parent()] = $prod->parent();
					}
				}
			}*/
			if(sizeof($_SESSION["patent"]["products"]))
			{
				$_SESSION["patent"]["request_fee"]=2200;
				if($_SESSION["patent"]["co_trademark"] || $_SESSION["patent"]["guaranty_trademark"])
				{
					$_SESSION["patent"]["request_fee"]=3000;
				}
				$_SESSION["patent"]["classes_fee"]= (sizeof($_SESSION["patent"]["products"]) - 1 )*700;
			}
			else
			{
				$_SESSION["patent"]["request_fee"] = 0;
				$_SESSION["patent"]["classes_fee"] = 0;
			}
			if(sizeof($_SESSION["patent"]["applicants"]) == 1)
			{
				$_SESSION["patent"]["representer"] = reset(array_keys($_SESSION["patent"]["applicants"]));
			}
			if(!$_SESSION["patent"]["payer"])
			{
				if($_SESSION["patent"]["applicants"][$_SESSION["patent"]["representer"]]["applicant_type"]== "1")
				{
					$data["payer"] = $_SESSION["patent"]["payer"] = $_SESSION["patent"]["applicants"][$_SESSION["patent"]["representer"]]["name"];
				}
				else 
				{
					$data["payer"] =  $_SESSION["patent"]["payer"] = $_SESSION["patent"]["applicants"][$_SESSION["patent"]["representer"]]["firstname"]." ".$_SESSION["patent"]["applicants"][$_SESSION["patent"]["representer"]]["lastname"];
				}
			}
		}

		$data["country"] = t(" Eesti ").html::radiobutton(array(
			"value" => 0,
			"checked" => (!$_SESSION["patent"]["country"] && isset($_SESSION["patent"]["country"])) ? 1 : 0,
			"name" => "country",
			"onclick" => 'document.getElementById("contact_popup_link").style.display="none";
					document.getElementById("country_code").value = "EE";',
		)).t("&nbsp;&nbsp;&nbsp;&nbsp; V&auml;lismaa ").html::radiobutton(array(
			"value" => 1,
			"checked" => $_SESSION["patent"]["country"],
			"name" => "country",
			"onclick" => 'document.getElementById("contact_popup_link").style.display="";
					document.getElementById("country_code").value = "";',
		));

		$data["applicant_type"] = t("F&uuml;&uuml;siline isik ").html::radiobutton(array(
			"value" => 0,
			"checked" => (!$_SESSION["patent"]["applicant_type"]) ? 1 : 0,
			"name" => "applicant_type",
			"onclick" => 'document.getElementById("firstname_row").style.display = ""; document.getElementById("lastname_row").style.display = ""; document.getElementById("name_row").style.display = "none";
			
			document.getElementById("reg_code").style.display="none";
			document.getElementById("p_adr").style.display="";
			document.getElementById("co_adr").style.display="none";
			document.getElementById("co_livingplace_type").style.display="none";
			document.getElementById("livingplace_type").style.display="";
			',
		)).t("&nbsp;&nbsp;&nbsp;&nbsp; Juriidiline isik ").html::radiobutton(array(
			"value" => 1,
			"checked" => $_SESSION["patent"]["applicant_type"],
			"name" => "applicant_type",
			"onclick" => 'document.getElementById("firstname_row").style.display = "none"; document.getElementById("lastname_row").style.display = "none"; document.getElementById("name_row").style.display = "";
			
			document.getElementById("reg_code").style.display="";
			document.getElementById("p_adr").style.display="none";
			document.getElementById("co_adr").style.display="";
			document.getElementById("livingplace_type").style.display="none";
			document.getElementById("co_livingplace_type").style.display="";
			',
		));

		if($_SESSION["patent"]["applicant_type"])
		{
			$data["CO_ADDRESS"] = $this->parse("CO_ADDRESS");
		}
		else
		{
			$data["P_ADDRESS"] = $this->parse("P_ADDRESS");
		}


		$data["type_text"] = $this->types[$_SESSION["patent"]["type"]];
		//$data["products_value"] = $this->_get_products_and_services_tbl();
		$data["type"] = t("S&otilde;nam&auml;rk ").html::radiobutton(array(
				"value" => 0,
				"checked" => !$_SESSION["patent"]["type"],
				"name" => "type",
				"onclick" => 'document.getElementById("wordmark_row").style.display = "";
				document.getElementById("reproduction_row").style.display = "none";
				document.getElementById("color_row").style.display = "none";
				document.getElementById("wordmark_caption").innerHTML = "Kaubam&auml;rk";
				document.getElementById("foreignlangelements_row").style.display = "";
				 ',
			)).t("&nbsp;&nbsp;&nbsp;&nbsp; Kujutism&auml;rk ").html::radiobutton(array(
				"value" => 1,
		 		"checked" => ($_SESSION["patent"]["type"] == 1) ? 1 : 0,
				"name" => "type",
				"onclick" => 'document.getElementById("wordmark_row").style.display = ""; 
				document.getElementById("wordmark_row").style.display = "none";
				document.getElementById("foreignlangelements_row").style.display = "none";
				document.getElementById("reproduction_row").style.display = "";
				document.getElementById("color_row").style.display = "";
				document.getElementById("wordmark_caption").innerHTML = "S&otilde;naline osa"; ',
			)).t("&nbsp;&nbsp;&nbsp;&nbsp; Kombineeritud m&auml;rk ").html::radiobutton(array(
				"value" => 2,
				"checked" => ($_SESSION["patent"]["type"] == 2) ? 1 : 0,
				"name" => "type",
				"onclick" => 'document.getElementById("wordmark_row").style.display = "";
				document.getElementById("color_row").style.display = "";
				document.getElementById("reproduction_row").style.display = "";document.getElementById("wordmark_caption").innerHTML = "S&otilde;naline osa";
				document.getElementById("foreignlangelements_row").style.display = "";',
			)).t("&nbsp;&nbsp;&nbsp;&nbsp; Ruumiline m&auml;rk ").html::radiobutton(array(
				"value" => 3,
				"checked" => ($_SESSION["patent"]["type"] == 3) ? 1 : 0,
				"name" => "type",
				"onclick" => 'document.getElementById("wordmark_row").style.display = "";
				document.getElementById("color_row").style.display = "";
				document.getElementById("reproduction_row").style.display = "";
				document.getElementById("wordmark_caption").innerHTML = "S&otilde;naline osa";
				document.getElementById("foreignlangelements_row").style.display = "";',
			));
		
		$data["wm_caption"] = ($_SESSION["patent"]["type"]  ? t("S&otilde;naline osa:") : t("Kaubam&auml;rk:"));
		
		$data["trademark_type"] = t("(kui taotlete kollektiivkaubam&auml;rki)").html::checkbox(array(
			"value" => 1,
			"checked" => $_SESSION["patent"]["co_trademark"],
			"name" => "co_trademark",
			"onclick" => 'document.getElementById("c_statues_row").style.display = "";'
			)).'<a href="javascript:;" onClick="MM_openBrWindow(\'16340\',\'\',\'width=720,height=540\')"><img src="/img/lk/ikoon_kysi.gif" border="0" /></a><br>'.
			
			t("(kui taotlete garantiikaubam&auml;rki)").html::checkbox(array(
				"value" => 1,
				"checked" => $_SESSION["patent"]["guaranty_trademark"],
				"name" => "guaranty_trademark",
				"onclick" => 'document.getElementById("g_statues_row").style.display = "";'
			)).'<a href="javascript:;" onClick="MM_openBrWindow(\'16341\',\'\',\'width=720,height=540\')"><img src="/img/lk/ikoon_kysi.gif" border="0" />';
		$data["trademark_type_text"] = ($_SESSION["patent"]["co_trademark"]) ? t("Kollektiivkaubam&auml;rk") : "";
		$data["trademark_type_text"].= " ";
		$data["trademark_type_text"].= ($_SESSION["patent"]["guaranty_trademark"]) ? t("Garantiim&auml;rk") : "";
		
		$dummy = obj($arr["alias"]["to"]);
		$parent = $_SESSION["patent"]["parent"] = $data["parent"] = $dummy->prop("trademarks_menu");
/*		$procurator_l = new object_list(array(
			"lang_id" => array(),
			"parent" => $parent, 
			"class_id" => CL_CRM_PERSON,
		));
*/		
	//	$options = $procurator_l->names();
		
		if(is_oid($_SESSION["patent"]["procurator"]) && $this->can("view" , $_SESSION["patent"]["procurator"]))
		{
			$procurator = obj($_SESSION["patent"]["procurator"]);
			$procurator_name = $procurator->name();
			$data["procurator_text"] = $procurator_name;
		}
		
		if (aw_global_get("uid") != "")
		{
			$pop_str = t("Vali");
		}
		else
		{
			$pop_str = "";
		}
		
		$data["procurator"] = html::hidden(array(
				"name" => "procurator",
				"value" => $_SESSION["patent"]["procurator"],
			))."<span id='procurator_name'> ".$procurator_name." </span>&nbsp;".html::href(array(
			"caption" => $pop_str ,
			"url"=> "javascript:void(0);",
			"onclick" => 'javascript:window.open("'.$this->mk_my_orb("procurator_popup", array("print" => 1 , "parent" => $dummy->prop("procurator_menu"))).'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
			
		));
		;
		
		$data["remove_procurator"] = html::href(array(
			"caption" => t("Eemalda") ,
			"url"=> "javascript:void(0);",
			"onclick" => 'javascript:
				window.document.getElementById("procurator").value= "";
				window.document.getElementById("procurator_name").innerHTML= "";
				window.document.getElementById("warrant_row").style.display = "none";
				window.document.getElementById("remove_procurator").style.display = "none";'
		));
		
/*		
		html::select(array(
			"options" => $options,
			"name" => "procurator",
			"value" => $_SESSION["patent"]["procurator"]
		));*/

		$data["add_new_applicant"] = html::radiobutton(array(
				"value" => 1,
				"checked" => 0,
				"name" => "add_new_applicant",
		));
		
		if(is_array($_SESSION["patent"]["applicants"]) && sizeof($_SESSION["patent"]["applicants"]))
		{
			$data["applicants_table"] = $this->_get_applicants_table();
		}
		foreach($this->country_popup_link_vars as $var)
		{
			$data[$var."_popup_link"] = html::href(array(
				"caption" => $pop_str ,
				"url"=> "javascript:void(0);",
				"onclick" => 'javascript:window.open("'.$this->mk_my_orb("country_popup", array("print" => 1 , "var" => $var)).'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
			));
		}
		if(is_oid($dummy->prop("bank_payment")) && $this->can("view" , $dummy->prop("bank_payment")))
		{
			$bank_inst = get_instance("common/bank_payment");
			$data["banks"] = $bank_inst->bank_forms(array("id" => $dummy->prop("bank_payment") , "amount" => $this->get_payment_sum()));
		}
		$data["find_products"] = html::href(array(
			"caption" => t("Sisene klassifikaatorisse") ,
			"url"=> "javascript:void(0);",
			"onclick" => 'javascript:window.open("'.$this->mk_my_orb("find_products", array("ru" => get_ru(), "print" => 1)).'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
		));
		
		$data["payer_popup_link"] = html::href(array(
			"caption" => t("Vali") ,
			"url"=> "javascript:void(0);",
			"onclick" => 'javascript:window.open("'.$this->mk_my_orb("payer_popup", array("print" => 1)).'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
			
		));
		$data["sum"] = $this->get_payment_sum();
		$_SESSION["patent"]["prod_ru"] = get_ru();
		$data["results_table"] = $this->get_results_table();
		
		$data["show_link"] = "javascript:window.open('".$this->mk_my_orb("show", array("print" => 1 , "id" => $_SESSION["patent"]["trademark_id"], "add_obj" => $arr["alias"]["to"]))."','', 'toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=600, width=800')";

		$data["convert_link"] = $this->mk_my_orb("pdf", array("print" => 1 , 	"id" => $_SESSION["patent"]["id"], "add_obj" => $arr["alias"]["to"]) , CL_PATENT);
	
		if(sizeof($_SESSION["patent"]["applicants"]))
		{
			$data["forward"] = '<input type="submit" value="Edasi"  class="nupp">';
		}
		if(is_oid($_SESSION["patent"]["id"]))
		{
			$ddoc_inst = get_instance(CL_DDOC);
			$url = $ddoc_inst->sign_url(array(
				"other_oid" =>$_SESSION["patent"]["id"],
			));
			$status = $this->is_signed($_SESSION["patent"]["id"]);
			if($status["status"] > 0)
			{
				$data["SIGNED"] = $this->parse("SIGNED");
			}
//			$u = get_instance(CL_USER);
//			$p = obj($u->get_current_person());
//			$code = $p->prop("personal_id");
//			if($code == $_SESSION["patent"]["authorized_person_code"] || $code == $_SESSION["patent"]["applicants"][$_SESSION["patent"]["representer"]]["code"] || ($_SESSION["patent"]["applicants"][$_SESSION["patent"]["representer"]]["applicant_type"] == 1 && !$_SESSION["patent"]["authorized_person_code"]))
//			{

				if($_SESSION["patent"]["applicants"][$_SESSION["patent"]["representer"]]["applicant_type"]== "1" && !is_oid($_SESSION["patent"]["procurator"]))
				{
					$job = " " .t("Allkirjastaja ametinimetus"). " " . html::textbox(array(
						"name" => "job",
						"size" => 10,
					));
				}
				$data["sign_button"] = '<input type="button" value="3. Allkirjasta taotlus" class="nupp" onClick="aw_popup_scroll(\''.$url.'\', \''.t("Allkirjastamine").'\', 410, 250);">'.$job.'<br>';
//			}
		}
		return $data;
	}
	
	/** 
		@attrib name=pdf all_args=1
	**/
	function pdf($arr)
	{
//		header("Content-type: application/pdf");
		$conv = get_instance("core/converters/html2pdf");
		die($conv->gen_pdf(array(
			"source" => $this->show (array (
				"id" => $id,
			)),
			"filename" => "Kaubam�rgitaotlus",
		)));
	}
		
	function get_payment_sum($arr)
	{
		$classes = array();
		$sum = 0;
		if(is_array($_SESSION["patent"]["products"]) && sizeof($_SESSION["patent"]["products"]))
		{
			$classes_fee = (sizeof($_SESSION["patent"]["products"]) - 1 )*700;
			if($_SESSION["patent"]["co_trademark"] || $_SESSION["patent"]["guaranty_trademark"])
			{
				$sum = 3000;
			}
			else
			{
				$sum = 2200;
			}
			$sum = $sum + $classes_fee;
			$_SESSION["patent"]["classes_fee"] = $classes_fee;
		}
		//arr($_SESSION["patent"]);
		return $sum;	
	}
	
	/**
		@attrib name=procurator_popup
		@param parent required type=string
	**/
	function procurator_popup($arr)
	{
		$address_inst = get_instance(CL_CRM_ADDRESS);
		$ret = "";
		$procurator_l = new object_list(array(
			"lang_id" => array(),
			"parent" => $arr["parent"], 
			"class_id" => CL_CRM_PERSON,
		));

		$tpl = "procurator_popup.tpl";
		$is_tpl = $this->read_template($tpl,1);
		$c = " ";

		foreach($procurator_l->arr() as $key => $val)
		{
			$this->vars(array(
				"id"=> $val->id(),
				"name" => $val->name(),
				"code" => $val->prop("code"),
				"onclick" => 'javascript:
					window.opener.document.getElementById("procurator").value= "'.$val->id().'";
					window.opener.document.getElementById("procurator_name").innerHTML= "'.$val->name().'";
					window.opener.document.getElementById("remove_procurator").style.display = "";
					window.opener.document.getElementById("warrant_row").style.display = "";
					window.close()',
			));
			$c .= $this->parse("PROCURATOR");

			$ret .= '<a href="javascript:void(0);" onClick=\'javascript:
				window.opener.document.getElementById("procurator").value= "'.$val->id().'";
				window.opener.document.getElementById("procurator_name").innerHTML= "'.$val->name().'";
				window.opener.document.getElementById("warrant_row").style.display = "";
				window.opener.document.getElementById("remove_procurator").style.display = "";
				window.close()\'>'.$val->name().' </a><br>';
		//	$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.changeform.exhibition_country.value=".$key."'>".$val."</a><br>";
		}

		if($is_tpl)
		{
			$this->vars(array(
				"PROCURATOR" => $c,
			));
			return $this->parse();
		}
		return $ret;
	}
	
	function get_results_table()
	{//arr($_SESSION["patent"]["delete"]);arr($_SESSION['patent']['products']);
		if($_SESSION["patent"]["delete"])
		{
			//$js.= 'alert("'.$_SESSION['patent']['errors'].'");';
			unset($_SESSION['patent']['products'][$_SESSION["patent"]["delete"]]);
			$_SESSION["patent"]["delete"] = null;
		}
		
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
//		$t->define_field(array(
//			"name" => "class_name",
//			"caption" => t("Klassi nimi"),
//		));
		$t->define_field(array(
			"name" => "prod",
			"caption" => t("Toode/teenus"),
		));
		$t->define_field(array(
			"name" => "delete",
			"caption" => "",
		));
	
	
		$classes = array();
		if(is_array($_SESSION["patent"]["prod_selection"]))
		{
			foreach($_SESSION["patent"]["prod_selection"] as $prod)
			{
				if(!$this->can("view" , $prod))
				{
					continue;
				}
				
				$product = obj($prod);
				$parent = obj($product->parent());
				$classes[$parent->comment()][$product->id()] = $product->prop("userta1");

//				$t->define_data(array(
//					"prod" => html::textarea(array("name" => "products[".$prod."]" , "value" => $product->name() . "(" .$product->prop("code").  ")", )),
//					"class" => $parent->comment(),
//					"class_name" => $parent->name(),
	//				"oid"	=> $prod->id(),
//				));
			}
			$_SESSION["patent"]["prod_selection"] = null;
		}
		
	
		if(is_array($_SESSION["patent"]["products"]))
		{
			foreach($_SESSION["patent"]["products"] as $key=> $val)
			{
				$classes[$key][] = $val;
//				$t->define_data(array(
//					"prod" => html::textarea(array("name" => "products[".$key."]" , "value" => $val, )),
//					"class" => $parent->comment(),
//					"class_name" => $parent->name(),
	//				"oid"	=> $prod->id(),
//				));
			}
//			$_SESSION["patent"]["prod_selection"] = null;
		}		
		ksort($classes);
		foreach($classes as $class => $prods)
		{
			$t->define_data(array(
				"prod" => html::textarea(array("name" => "products[".$class."]" , "value" => join("\n" , $prods))),
				"class" => $class,
				"delete" => html::href(array(
					"url" => "#",
					"onclick" => 'fRet = confirm("'.t("Oled kindel, et soovid valitud klassi kustutada?").'"); if(fRet) {document.getElementById("delete").value="'.$class.'";document.getElementById("stay").value=1;
					document.changeform.submit();} else;',
					"caption" => t("Kustuta"),
				)),

			));
		}
		return $t->draw();
	}
	
	/**
		@attrib name=find_products nologin=1
		@param ru required type=string
	**/
	function find_products($arr)
	{
		if($_POST["do_post"])
		{
			$_SESSION["patent"]["prod_selection"] =  $_POST["oid"];
			die("
				<script type='text/javascript'>
					window.opener.document.getElementById('stay').value=1;
					window.opener.document.changeform.submit();
					window.close();
				</script>"
			);
		}
//				window.opener.location.href='".$_SESSION["patent"]["prod_ru"]."';
		
		if($_POST["product"] || $_POST["class"])
		{
			if($_POST["class"])
			{
				$limit = 1700;
			}
			else
			{
				$limit = 500;
			}
			$tpl = "products_res.tpl";
			$is_tpl = $this->read_template($tpl,1);
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
			
			$products = new object_list();
			if(strlen($_POST["class"]) == 1)
			{
				$_POST["class"] = "0".$_POST["class"];
			}
			$parents = new object_list(array(
				"comment" => "%".$_POST["class"]."%",
				"class_id" => CL_MENU ,
				"lang_id" => array(),
				"limit" => $limit,
			));
			$parents->sort_by(array(
				"prop" => "name",
				"order" => "asc"
			));

			foreach ($parents->ids() as $id)
			{
				$prod_list = new object_list(array(
					"userta1" => "%".$_POST["product"]."%",
					"parent" => $id,
					"class_id" => CL_SHOP_PRODUCT ,
					"lang_id" => array(),
					"limit" => $limit,
				));

				foreach($prod_list->arr() as $p)
				{
					if($p->prop("userch10"))
					{
						$prod_list->remove($p->id());
						$products->add($p->id());
					}
				}

				$prod_list->sort_by(array(
					"prop" => "name",
					"order" => "asc"
				));
				
				$products->add($prod_list);
			}

// 			$products = new object_list(array(
// 				"name" => "%".$_POST["product"]."%",
// 				"parent" => $parents->ids(),
// 				"class_id" => CL_SHOP_PRODUCT ,
// 				"lang_id" => array(),
// 				"limit" => $limit,
// 			));
			//arr(sizeof($products->ids()));
			if($is_tpl)
			{
				$c = "";
				foreach($products->arr() as $prod)
				{
					$parent = obj($prod->parent());
					if($prod->prop("userch10"))
					{
						$p = "<b>".$prod->prop("userta1")."</b>";
					}
					else
					{
						$p = $prod->prop("userta1");
					}
					$this->vars(array(
						"prod" => $p,
						"class" => $parent->name(),
						"code" => 132245,
						"oid"	=> $prod->id(),
					));
					$c .= $this->parse("PRODUCT");
				}
				$this->vars(array(
					"PRODUCT" => $c,
					"ru" => $arr["ru"]
				));
				$result_list =  $this->parse();
			}
			else
			{
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
				$result_list =  "<form action='' method=POST>".$t->draw()."
				<input type=hidden value=".$arr["ru"]." name=ru>
				<input type=hidden value=1 name=do_post>
				<input type=submit value='Lisa valitud terminid taotlusse'>";
			}
		}
		
		$tpl = "products.tpl";
		$is_tpl = $this->read_template($tpl);
		if($is_tpl)
		{
			$this->vars(array("result" => $result_list));
			return $this->parse();
		}
		//$products = nee object_list(array("class_id" => CL_SHOP_PRODUCT,"lang_id" => array()));
//		$address_inst = get_instance(CL_CRM_ADDRESS);
		$ret = "<form action='' method=POST>Klassi nr:".
		html::textbox(array("name" => "class"))."<br> Kauba/teenuse nimetus".html::textbox(array("name" => "product"))
		
		."<input type=hidden value=".$arr["ru"]." name=ru><input type=submit value='otsi'></form>";
//		foreach($address_inst->get_country_list() as $key=> $val)
//		{
//			
//			$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.document.exhibition_country.value=".$key."'>".$val."</a><br>";
		//	$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.changeform.exhibition_country.value=".$key."'>".$val."</a><br>";
//		}
		return $ret . $result_list;
	}
	
	/**
		@attrib name=country_popup
		@param var required type=string
	**/
	function country_popup($arr)
	{
		$address_inst = get_instance(CL_CRM_ADDRESS);
		$ret = "";
		
		
		$tpl = "country_popup.tpl";
		$is_tpl = $this->read_template($tpl,1);
		$c = "";
		foreach($address_inst->get_country_list() as $key=> $val)
		{
			$this->vars(array(
				"name" => $val,
				"onclick" => 'javascript:window.opener.document.changeform.'.$arr["var"].'.value="'.$key.'";window.close()',
				"code" => $key,
			));
			$c .= $this->parse("COUNTRY");
			$ret .= "<a href='javascript:void(0);' onClick='javascript:window.opener.document.changeform.".$arr["var"].".value=\"".$key."\";window.close()'>".$val." </a><br>";
		//	$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.changeform.exhibition_country.value=".$key."'>".$val."</a><br>";
		}
		if($is_tpl)
		{
			$this->vars(array(
				"COUNTRY" => $c,
				"var" => $arr["var"]
			));
			return $this->parse();
		}
		return $ret;
	}
	
	/**
		@attrib name=payer_popup
	**/
	function payer_popup()
	{
		$ret = " ";
		foreach($_SESSION["patent"]["applicants"] as $key=> $applicant)
		{
			$ret = " ";
			$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.document.changeform.payer.value=\"".$applicant["firstname"]." " . $applicant["lastname"]."\";window.close()'>".$applicant["firstname"]." " . $applicant["lastname"]."</a><br>";
		//	$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.changeform.exhibition_country.value=".$key."'>".$val."</a><br>";
		}
		return $ret;
	}
	
	function _get_applicants_table()
	{
		classload("vcl/table");
		$t = new vcl_table(array(
			"layout" => "generic",
			"id" => "patent_requesters_registered",
		));
		$t->table_tag_id = "applicant_requesters";
		
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
//		$t->define_field(array(
//			"name" => "code",
//			"caption" => t("Isikukood/reg.kood"),
//		));
		
//		if(sizeof($_SESSION["patent"]["applicants"]) > 1 && !is_oid($_SESSION["patent"]["procurator"]))
//		{
//			$t->define_field(array(
//				"name" => "representer",
//				"caption" => t("&Uuml;hine esindaja").'<a href="javascript:;" onClick="MM_openBrWindow(\'16338\',\'\',\'width=720,height=540\')"><img src="/img/lk/ikoon_kysi.gif" border="0" /></a>',
//			));
//		}
		$t->define_field(array(
			"name" => "change",
			"caption" => t(""),
		));
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
			$delete_link = $this->mk_my_orb("remove_applicant", array("key" => $key));
			
			$t->define_data(array(
				"name" => $name,
				"code" => $applicant["code"],
				"representer" => html::radiobutton(array(
					"value" => $key,
					"checked" => ($_SESSION["patent"]["representer"] == $key) ? 1 : 0,
					"name" => "representer",
				)),
				"change" => html::href(array(
			//		"url" => "#",
					"url" => "javascript:document.getElementById(\"applicant_id\").value=".$key.";document.changeform.submit();",//aw_url_change_var("change_applicant" , $key , get_ru()),
			//		"onClick" => 'javascript:window.open("'.$delete_link.'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=100, width=100")',

				//	"onClick"=>"self.disabled=true;submit_changeform(''); return false;",
					"caption" => t("Muuda"),
					//"title" => t("Muuda"),
				))." ".html::href(array(
//					"url" =>  "#",
					"url" => "javascript:document.getElementById(\"delete_applicant\").value=".$key.";document.getElementById(\"stay\").value=1;document.changeform.submit();",//aw_url_change_var("change_applicant" , $key , get_ru()),
			//		"url" => "javascript:document.getElementById(\"applicant_id\").value=".$key.";document.changeform.submit();",//aw_url_change_var("change_applicant" , $key , get_ru()),
				//	"onClick"=>"self.disabled=true;submit_changeform(''); return false;",
//					"onClick" => 'javascript:window.open("'.$delete_link.'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=100, width=100")',
					"caption" => t("Kustuta"),
					//"title" => t("Muuda"),
				)),
			));
		}
		return $t->draw();
	}
	
	/** 
		@attrib name=remove_applicant is_public="1"  all_args=1
		@param key optional type=int
	**/
	function remove_applicant($arr)
	{
		unset($_SESSION["patent"]["applicants"][$arr["key"]]);
		die('<script type="text/javascript">
			window.opener.location.reload();
			window.close();
			</script>'
		);
	}
		
	/**
		@attrib name=submit_data is_public="1" caption="Change" all_args=1
	**/
	function submit_data($arr)
	{
		$errs = "";
		$_SESSION["patent"]["errors"] =  $errs.= $this->check_fields();
		foreach($_POST as $data => $val)
		{
			$_SESSION["patent"][$data] = $val;
		}
		//arr($arr["data_type"]);
		
		if($arr["data_type"] == 1)
		{
			$_SESSION["patent"]["co_trademark"] = $_POST["co_trademark"];
			$_SESSION["patent"]["guaranty_trademark"] = $_POST["guaranty_trademark"];
			//co_trademark 'guaranty_trademark
		}
		//taotleja andmed liiguvad massiivi, et saaks mitu taotlejat sisse lugeda
		//miskeid t�hju taotlejait poleks vaja... niiet:
		if($_POST["code"] || $_POST["name"] || $_POST["firstname"] || $_POST["lastname"])
		{
			$n = $this->submit_applicant();
			if($errs || $_POST["stay"])
			{
				$_SESSION["patent"]["change_applicant"] = $n;
				$_SESSION["patent"]["applicant_id"] = $n;
			}
		}
		
		$this->save_uploads($_FILES);
		
		if($_POST["save"] && $_POST["stay"])
		{
			$object_id = $this->save_data();
		}
		if($_POST["add_new_applicant"] || $_POST["applicant_id"] != "")
		{
			if($_POST["add_new_applicant"])
			{
				$_SESSION["patent"]["add_new_applicant"] = 1;
			}
			return aw_url_change_var("trademark_id" , null , $arr["return_url"]);
			//return $arr["return_url"];
		}
		//viimasest lehest edasi
//		if($arr["data_type"] == 5)
//		{
//			return aw_url_change_var("data_type" , null , $arr["return_url"]);
//		}
		if(!$errs && !$_POST["stay"])
		{
			if($_POST["save"])
			{
				if($_SESSION["patent"]["id"] && $_SESSION["patent"]["authoirized_person_code"] && $_GET["data_type"]==5)
				{
					$status = $this->is_signed($key);
					if($status["status"] > 0)
					{
						$ddoc = obj($status["ddoc"]);
						$codes = array();
						foreach($ddoc->connections_from(array("type" => "RELTYPE_SIGNER")) as $key => $c)
						{
							$person = obj($c->to());
							$codes[] = $person->prop("personal_id");
						}
//						if(!in_array($_SESSION["patent"]["authoirized_person_code"] , $codes))//erineb 
//						{
//							$_SESSION["patent"]["errors"] = "Allkirjastama peab seaduslik esindaja";
//							return aw_url_change_var("trademark_id" , null , $arr["return_url"]);
//						}
					}
				}
				$this->set_sent($arr);//muudab staatuse... v�i noh, tegelt poogib numbrti k�lge
				$_SESSION["patent"] = null;
			}
			return aw_url_change_var("trademark_id" , null , aw_url_change_var("data_type" , ($arr["data_type"]+1) , $arr["return_url"]));
		}
		else
		{	
			$_SESSION["patent"]["stay"] = null;
			return aw_url_change_var("trademark_id" , null , $arr["return_url"]);
		}
	}
	
	function set_sent($arr)
	{
		$re = $this->is_signed($_SESSION["patent"]["id"]);
		if(!($re["status"] == 1))//et allkirjastamata taotlused saatmisele ei l�heks
		{
			return null; 
		}
		$object = obj($arr["add_obj"]);
		$num_ser = $object->prop("series");
		$ser = get_instance(CL_CRM_NUMBER_SERIES);
		$o = obj($_SESSION["patent"]["id"]);
		$tno = $ser->find_series_and_get_next(CL_PATENT,$num_ser);
		$status = $this->get_status($o);
		$status->set_prop("nr" , $tno);
		$status->set_prop("sent_date" , time());
		$status->save();
		header("Location:"."19205");
		die();
//		$o->save();
	}
	
	function check_fields()
	{
		$err = "";
		if($_GET["data_type"] == 2)
		{
			if(!is_array($_POST["products"]) && !is_array($_SESSION["patent"]["prod_selection"]) && !(is_array($_SESSION["patent"]["products"] && sizeof($_SESSION["patent"]["products"]))))
			{
				$err.= t("Kohustuslik v&auml;hemalt &uuml;he klassi lisamine")."\n<br>";
			}
		}
		if($_GET["data_type"] == 1)
		{
			if($_POST["type"] == 0 && !isset($_POST["word_mark"]))
			{
				$err.= t("S&otilde;nam&auml;rgi puhul peab olema s&otilde;naline osa t&auml;idetud")."\n<br>";
			}
			if($_POST["type"] == 1 && !$_FILES["reproduction_upload"]["name"] && !is_oid($_SESSION["patent"]["reproduction"]))
			{
				$err.= t("Peab olema lisatud ka reproduktsioon")."\n<br>";
			}
			if($_POST["type"] == 2 && !isset($_POST["word_mark"]))
			{
				$err.= t("Kombineeritud m&auml;rgi puhul peab olema s&otilde;naline osa t&auml;idetud")."\n<br>";
			}
			if($_POST["type"] == 2 && !$_FILES["reproduction_upload"]["name"] && !is_oid($_SESSION["patent"]["reproduction"]))
			{
				$err.= t("Peab olema lisatud ka reproduktsioon")."\n<br>";
			}
			if($_POST["type"] == 3 && !$_FILES["reproduction_upload"]["name"] && !is_oid($_SESSION["patent"]["reproduction"]))
			{
				$err.= t("Peab olema lisatud ka reproduktsioon")."\n<br>";
			}
		
		}
		
		
		if($_POST["code"] || $_POST["name"] || $_POST["firstname"] || $_POST["lastname"])
		{
			if(!isset($_POST["country"]))
			{
				$err.= t("Kodumaine v&otilde;i v&auml;lismaine peab olema valitud")."\n<br>";
			}
			if(!isset($_POST["applicant_type"]))
			{
				$err.= t("F&uuml;&uuml;siline v&otilde;i juriidiline isik peab olema valitud")."\n<br>";
			}
//			if(!$_POST["code"] )
//			{
//				$err.= t("Isikukood/Registri kood on kohustuslik")."\n<br>";
//			}
			
			if($_POST["applicant_type"])
			{
				if(!$_POST["name"])
				{
					$err.= t("Nimi on kohustuslik")."\n<br>";
				}
			
			}
			else
			{
				if(!$_POST["firstname"])
				{
					$err.= t("Eesnimi on kohustuslik")."\n<br>";
				}
				if(!$_POST["lastname"])
				{
					$err.= t("Perekonnanimi on kohustuslik")."\n<br>";
				}
			}
			if(!$_POST["city"])
			{
				$err.= t("Linn on kohustuslik")."\n<br>";
			}
			if(!$_POST["street"])
			{
				$err.= t("T&auml;nav on kohustuslik")."\n<br>";
			}
			if(!$_POST["country_code"])
			{
				$err.= t("Riik on kohustuslik")."\n<br>";
			}
			if(!$_POST["index"])
			{
				$err.= t("Postiindeks on kohustuslik")."\n<br>";
			}
		}
		
		if($_POST["convention_date"]["day"] || $_POST["exhibition_date"]["day"])
		{
			if(
				(
						$_POST["convention_nr"] && mktime(0,0,0,$_POST["convention_date"]["month"],$_POST["convention_date"]["day"],$_POST["convention_date"]["year"]) <
						mktime(0,0,0,date("m" , time())-6, date("j" , time())-5,date("Y" , time()))
				)
				//time() - (30*6+5)*24*3600)
				|| 
				
					(
						$_POST["exhibition_name"] && mktime(0,0,0,$_POST["exhibition_date"]["month"], $_POST["exhibition_date"]["day"],$_POST["exhibition_date"]["year"]) <   mktime(0,0,0,date("m" , time())-6, date("j" , time())-5,date("Y" , time()))
					)
				//time() - (30*6 + 5)*24*3600 )
			 )
			{
				$err.= t("Prioriteedikuup&auml;ev ei v&otilde;i olla vanem kui 6 kuud ja 5 p&auml;eva ")."\n<br>";
			}
		}
		if(!$err)
		{
			$_SESSION["patent"]["checked"] = $_GET["data_type"];
		}
		else
		{
			$_SESSION["patent"]["checked"] = $_GET["data_type"]-1;
		}
		return $err;
	}
	
	function submit_applicant()
	{
		$applicant_vars = array("name", "firstname" , "lastname", "code", "street" , "city" , "index" , "country_code" , "phone" , "fax", "applicant_type" , "email", "correspond_country_code","correspond_street","correspond_index","correspond_city", "country");
		if($_SESSION["patent"]["change_applicant"] != "")
		{
			$n = $_SESSION["patent"]["change_applicant"];
// 			$_SESSION["patent"]["applicant_id"] = null;
// 			$_SESSION["patent"]["change_applicant"] = null;
		}
		else
		{
			//otsib esimese mitte kasutatava key
			$n = 0;
			while(array_key_exists($n , $_SESSION["patent"]["applicants"]))
			{
				$n++;
				if($n > 25)
				{
					break;
				}
			}
//			$n = sizeof($_SESSION["patent"]["applicants"]);
		}
		
		foreach($applicant_vars as $var)
		{
			$_SESSION["patent"]["applicants"][$n][$var] = $_SESSION["patent"][$var];
			$_SESSION["patent"][$var] = null;
		}
		return $n;
	}
	
	function save_uploads($uploads)
	{
		$image_inst = get_instance(CL_IMAGE);
		foreach($this->file_upload_vars as $var)
		{
			if(array_key_exists($var."_upload" , $uploads))
			{
				if(!$_FILES[$var."_upload"]['tmp_name'])
				{
					continue;
				}
				$file_inst = get_instance(CL_FILE);
				$id = $file_inst->save_file(array(
					"parent" => $_SESSION["patent"]["parent"], 
					"content" => $image_inst->get_file(array(
						"file" => $_FILES[$var."_upload"]['tmp_name'],
					)),
					"name" => $_FILES[$var."_upload"]['name'],
 					"type" => $_FILES[$var."_upload"]['type'],
				));
//				$image_inst = get_instance(CL_IMAGE);
//				$upload_image = $image_inst->add_upload_image($var."_upload", $_SESSION["patent"]["parent"]);
				// if there is image uploaded:
				$_SESSION["patent"][$var] = $id;
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
			$patent->set_name(" Kinnitamata taotlus nr [".$patent->id()."]");
		}
		$this->save_trademark($patent);
		$this->save_priority($patent);
		$this->save_fee($patent);
		$this->save_applicants($patent);
		$this->fileupload_save($patent);
		$this->final_save($patent);
		$patent->set_meta("products" , $_SESSION["patent"]["products"]);
		$patent->save();

		$_SESSION["patent"]["id"] = $patent->id();
		$status = $this->get_status($patent);
		return $patent->id();
	}
	
	function get_status($patent)
	{
		if(!$this->can("add" , $patent->id())) return $patent;
		$status = $patent->get_first_obj_by_reltype("RELTYPE_TRADEMARK_STATUS");
		if(!is_object($status))
		{
			$status = new object();
			$status->set_class_id(CL_TRADEMARK_STATUS);
			$status->set_parent($patent->id());
			$status->set_name(" Kinnitamata taotlus nr [".$patent->id()."]");
			$status->save();
			$patent->connect(array("to" => $status->id() , "type" => "RELTYPE_TRADEMARK_STATUS"));
		}
		return $status;
	}
	
	function save_applicants($patent)
	{
	//	$patent->set_prop("country" ,$_SESSION["patent"]["country"]);
		$address_inst = get_instance(CL_CRM_ADDRESS);
		$conns = $patent->connections_from(array(
			"type" => "RELTYPE_APPLICANT",
		));
		foreach($conns as $conn)
		{
			$conn->delete();
		}
		foreach($_SESSION["patent"]["applicants"] as $key => $val)
		{
			if(!$_SESSION["patent"]["representer"])
			{
				$_SESSION["patent"]["representer"] = $key;
			}
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
			$address->set_prop("riik" , $address_inst->get_country_by_code($val["country_code"], $applicant->id()));
			if($val["city"])
			{
				$citys = new object_list(array("lang_id" => 1, "class_id" => CL_CRM_CITY, "name" => $val["city"]));
				if(!is_object($city = reset($citys->arr())))
				{
					$city = new object();
					$city->set_parent($applicant->id());
					$city->set_class_id(CL_CRM_CITY);
					$city->set_name($val["city"]);
					$city->save();
					
				}
				$address->set_prop("linn" ,$city->id());
			}
			
			$address->save();
	
	
			$correspond_address = "";
			if($val["correspond_country_code"] || $val["correspond_street"] || $val["correspond_index"] || $val["correspond_city"])
			{
				$correspond_address = new object();
				$correspond_address->set_class_id(CL_CRM_ADDRESS);
				$correspond_address->set_parent($applicant->id());
				
				$correspond_address->set_prop("aadress", $val["correspond_street"]);
				$correspond_address->set_prop("postiindeks" , $val["correspond_index"]);
				$correspond_address->set_prop("riik" , $address_inst->get_country_by_code($val["correspond_country_code"], $applicant->id()));
				if($val["correspond_city"])
				{
					$citys = new object_list(array("lang_id" => 1, "class_id" => CL_CRM_CITY, "name" => $val["correspond_city"]));
					if(!is_object($city = reset($citys->arr())))
					{
						$city = new object();
						$city->set_parent($applicant->id());
						$city->set_class_id(CL_CRM_CITY);
						$city->set_name($val["correspond_city"]);
						$city->save();
					}
					$correspond_address->set_prop("linn" ,$city->id());
				}
				$correspond_address->save();
			}

	
			if($type)
			{
				$applicant->set_name($val["name"]);
				$applicant->set_prop("contact" , $address->id());
				$applicant->set_prop("reg_nr",$val["code"]);
//				$applicant->connect(array("to"=> $val["warrant"], "type" => "RELTYPE_PICTURE"));
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
			
			if(is_object($correspond_address))
			{
				$applicant->set_prop("correspond_address" , $correspond_address->id());
				$applicant->connect(array("to"=> $correspond_address->id(), "type" => "RELTYPE_CORRESPOND_ADDRESS"));
			}
			
			
			if($val["phone"])
			{
				$phone = new object();
				$phone->set_class_id(CL_CRM_PHONE);
				$phone->set_name($val["phone"]);
				$phone->set_prop("type" , "mobile");
				$phone->set_parent($applicant->id());
				$phone->save();
				$applicant->connect(array("to"=> $phone->id(), "type" => "RELTYPE_PHONE"));
				if(!$type) $applicant->set_prop("phone" , $phone->id());
				else $applicant->set_prop("phone_id" , $phone->id());
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
				if(!$type) $applicant->set_prop("email" , $email->id());
				else $applicant->set_prop("email_id" , $email->id());
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
					$applicant->set_prop("telefax_id" , $phone->id());
				}
				else
				{
					$applicant->connect(array("to"=> $phone->id(), "type" => "RELTYPE_FAX"));
					$applicant->set_prop("fax" , $phone->id());
				}
			}
			$applicant->save();
			
			$patent->connect(array("to" => $applicant->id(), "type" => "RELTYPE_APPLICANT"));
			if($_SESSION["patent"]["representer"] == $key){
				$patent->set_prop("applicant" , $applicant->id());
			}
		}
		//$patent->set_prop("country" , $_SESSION["patent"]["country_code"]);
		$patent->set_prop("procurator", $_SESSION["patent"]["procurator"]);
		$patent->save();
	}
	
	
	function fileupload_save($patent)
	{
		foreach($this->file_upload_vars as $var)
		if(is_oid($_SESSION["patent"][$var]) && $this->can("view" ,$_SESSION["patent"][$var]))
		{
			$patent->set_prop($var, $_SESSION["patent"][$var]);
			$patent->connect(array("to" => $_SESSION["patent"][$var], "type" => "RELTYPE_".strtoupper($var)));
		}
		$patent->save();
	}
	
	
	function final_save($patent)
	{
		$patent->set_prop("additional_info" , $_SESSION["patent"]["additional_info"]);
		$patent->set_prop("job" , $_SESSION["patent"]["job"]);
		if(	$_SESSION["patent"]["authorized_person_firstname"] || 
			$_SESSION["patent"]["authoirized_person_person_lastname"] || 
			$_SESSION["patent"]["authoirized_person_code"])
		{
			$applicant = new object();
			$applicant->set_parent($patent->id());
			$applicant->set_class_id(CL_CRM_PERSON);
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
		$vars = array("request_fee" , "classes_fee", "payer" , "doc_nr");
		foreach($vars as $var)
		{
			if($_SESSION["patent"][$var])
			{
				$patent->set_prop($var,$_SESSION["patent"][$var]);
			}
		}
		if(is_array($_SESSION["patent"]["payment_date"]) || $_SESSION["patent"]["payment_date"] > 0)
		{
			$patent->set_prop("payment_date" , $_SESSION["patent"]["payment_date"]);
		}
		$patent->save();
	}
	
	function save_priority($patent)
	{
	//	$convention_time = explode("/" , $_SESSION["patent"]["convention_date"]);
		$patent->set_prop("convention_nr" , $_SESSION["patent"]["convention_nr"]);
		//$patent->set_prop("convention_date" , mktime(0,0,0,$convention_time[1], $convention_time[0],$convention_time[2]));
		$patent->set_prop("convention_date" , $_SESSION["patent"]["convention_date"]);
		$patent->set_prop("convention_country" , $_SESSION["patent"]["convention_country"]);
		
	//	$exhibition_time = explode("/" , $_SESSION["patent"]["exhibition_date"]);
		$patent->set_prop("exhibition_name" , $_SESSION["patent"]["exhibition_name"]);
	//	$patent->set_prop("exhibition_date" , mktime(0,0,0,$exhibition_time[1], $exhibition_time[0],$exhibition_time[2]));
		$patent->set_prop("exhibition_date" , $_SESSION["patent"]["exhibition_date"]);
		$patent->set_prop("exhibition_country" , $_SESSION["patent"]["exhibition_country"]);
		$patent->save();
	}
	
	function save_trademark($patent)
	{
		$patent->set_prop("word_mark" , $_SESSION["patent"]["word_mark"]);
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
		$patent->save();
	}
	
	/** Show patents added by user 
		
		@attrib name=my_patent_list is_public="1" caption="Minu patenditaotlused"
	
	**/
	function my_patent_list($arr)
	{
		extract($_GET);
		$uid = aw_global_get("uid");
		$section = aw_global_get("section");
		
		if(is_oid($_GET["delete_patent"]) && $this->can("delete" , $_GET["delete_patent"]))
		{
			$d = obj($_GET["delete_patent"]);
			$d->delete();
		}
		
		$obj_list = new object_list(array(
			"class_id" => CL_PATENT,
			"createdby" => $uid,
			"lang_id" => array(),
		));
		
		$obj_list->sort_by(array(
			"prop" => "created",
			"order" => "desc"
		));
		
		$tpl = "list.tpl";
		if($arr["unsigned"])
		{
			$tpl = "unsigned_list.tpl";
		}
		
		$this->read_template($tpl);
		lc_site_load("patent", $this);
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		$code = $p->prop("personal_id");
		$ddoc_inst = get_instance(CL_DDOC);
		if($code)
		{
			$persons_list = new object_list(array(
				"class_id" => CL_CRM_PERSON,
				"lang_id" => array(),
				"personal_id" => $code
			));
			foreach($persons_list->ids() as $id)
			{
				$other_list = new object_list(array(
					"class_id" => CL_PATENT,
					"applicant" => $id,
					"lang_id" => array(),
				));
				$obj_list->add($other_list);

				$other_list = new object_list(array(
					"class_id" => CL_PATENT,
					"lang_id" => array(),
					"authorized_person" => $id,
				));
				$obj_list->add($other_list);
			}
		}
		
		$objects_array = array();
		foreach($obj_list->arr() as $key => $patent)
		{
			$status = $this->get_status($patent);
			if($status->prop("nr"))
			{
				$objects_array[$status->prop("nr")] = $patent;
			}
			else
			{
				$objects_array[] = $patent;
			}
		}
		if(!$arr["unsigned"])
		{
			krsort($objects_array);
		}
//		$has = $this->is_admin();
		
		if ($this->is_template("LIST"))
		{
			$c = "";
			foreach($objects_array as $key => $patent)
			{
				$status = $this->get_status($patent);
				$re = $this->is_signed($patent->id());
				if($send_patent == $patent->id() && $re["status"] == 1 && !$status->prop("nr"))
				{
					$_SESSION["patent"]["id"] = $patent->id();
					$asd = $this->set_sent(array("add_obj" => $arr["alias"]["to"]));
				}
				if($arr["unsigned"])
				{
					if($status->prop("nr")) continue;
					$date = date("j.m.Y" , $patent->created());
				}
				else
				{
					if(!$status->prop("nr")) continue;
					if($status->prop("sent_date"))
					{
						$date = date("j.m.Y" , $status->prop("sent_date"));
					}
					else
					{
						$date = date("j.m.Y" , $patent->created());
					}
				}
				
				$url = aw_url_change_var("trademark_id", $patent->id());
				$url = aw_url_change_var("data_type", null , $url);
				//arr($re["status"]); arr($patent->prop("verified")); arr($patent->prop("nr")); arr($code); arr($patent->prop("authorized_person.personal_id")); arr($patent->prop("applicant.personal_id"));
				
				if(
					!($re["status"] == 1) && 
					!$status->prop("verified") && 
					!$status->prop("nr")// && 
//					(
//						(
//							$code &&
//							(
//								$code == $patent->prop("authorized_person.personal_id")||
//								$code == $patent->prop("applicant.personal_id")
//							)
//						) ||
//						(
//							$patent->prop("applicant.class_id") == CL_CRM_COMPANY &&
//							!$patent->prop("authorized_person")
//						)
//					)
				)
				{
					$do_sign = 1;
			        	$sign_url = $ddoc_inst->sign_url(array(
						"other_oid" =>$patent->id(),
					));
			                $sign = "<a href='javascript:void(0);' onClick='javascript:window.open(\"".$sign_url."\",\"\", \"toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600\");'>Allkirjasta</a>";
				}
				else
				{
					$do_sign = 0;
					$sign = "";
				}
				$view_url = $this->mk_my_orb("show", array(
					"print" => 1,
					"id" => $patent->id(),
					"add_obj" => $arr["alias"]["to"],
					"sign" => $do_sign,
				), CL_PATENT);
				
				$change = $del_url = $send_url= '';
				if(!($status->prop("nr") || $status->prop("verified")))
				{
					$change = '<a href="'.$url.'">Muuda</a>';
					$del_url = aw_ini_get("baseurl").aw_url_change_var("delete_patent", $patent->id());
				}
				elseif(($re["status"] == 1))
				{
					$change = "";
					$url = aw_url_change_var("send_patent", $patent->id());
					$send_url = '<a href="'.$url.'">Saada</a>';
				}
				
				$this->vars(array(
					"date" 		=> $date,
					"nr" 		=> ($status->prop("nr")) ? $status->prop("nr") : "",
					"applicant" 	=> $patent->prop_str("applicant"),
					"type" 		=> $this->types[$patent->prop("type")],
					"state" 	=> ($status->prop("verified")) ? t("Vastu v&otilde;etud") : (($status->prop("nr")) ? t("Saadetud") : ""),
					"name" 	 	=> $status->name(),
					"id" 	 	=> $patent->id(),
					"url"  		=> $url,
					"procurator"  	=> $patent->prop_str("procurator"),
					"change"	=> $change,
					"view"		=> $view_url,
					"sign"		=> $sign,
					"delete"	=> $del_url,
					"send"		=> $send_url,
				));
				$c .= $this->parse("LIST");
			}
			$this->vars(array(
				"LIST" => $c,
			));
		}
		return $this->parse();
	}
	
	/** Show patents added by user 
		
		@attrib name=my_unsigned_patent_list is_public="1" caption="Minu patenditaotlused"
	
	**//*
	function my_unsigned_patent_list($arr)
	{
		$uid = aw_global_get("uid");
		$section = aw_global_get("section");
		
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		$code = $p->prop("personal_id");
		
		$obj_list = new object_list();
		if($code)
		{
			$persons_list = new object_list(array(
				"class_id" => CL_CRM_PERSON,
				"lang_id" => array(),
				"personal_id" => $code
			));
	
			foreach($obj_list->arr() as $o)
			{
				if($o->prop("authorized_person.personal_id"))
				{
					$obj_list->remove($o);
				}
			}
			foreach($persons_list->ids() as $id)
			{
				$other_list = new object_list(array(
					"class_id" => CL_PATENT,
					"applicant" => $id,
					"lang_id" => array(),
				));
				$obj_list->add($other_list);

				$other_list = new object_list(array(
					"class_id" => CL_PATENT,
					"lang_id" => array(),
					"authorized_person" => $id,
				));
				$obj_list->add($other_list);
			}
		}
		
		foreach($obj_list->arr() as $o)
		{
			$re = $this->is_signed($o->id());
			if($re["status"] == 1)
			{
				$obj_list->remove($o);
			}
		}
		
		$tpl = "unsigned_list.tpl";
		$this->read_template($tpl);
		lc_site_load("patent", $this);
	
		if ($this->is_template("LIST"))
		{
			$c = "";
			foreach($obj_list->arr() as $key => $patent)
			{
				$url = aw_url_change_var("trademark_id", $patent->id());
				$url = aw_url_change_var("data_type", null , $url);
				$view_url = $this->mk_my_orb("show", array(
					"id" => $patent->id(),
					"sign" => 1,
					"add_obj" => $arr["alias"]["to"],
				), CL_PATENT);
				
				$ddoc_inst = get_instance(CL_DDOC);
				$sign_url = $ddoc_inst->sign_url(array(
					"other_oid" =>$patent->id(),
				));
				
				$change = '';
				if(!($patent->prop("nr") || $patent->prop("verified")))
				{
					$change = '<a href="'.$url.'">Muuda</a>';
				}

		                $sign = "<a href='javascript:void(0);' onClick='javascript:window.open(\"".$sign_url."\",\"\", \"toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600\");'>Allkirjasta</a>";


				$this->vars(array(
					"date" 		=> date("j.m.Y" , $patent->created()),
					"nr" 		=> ($patent->prop("nr")) ? $patent->prop("nr") : "",
					"applicant" 	=> $patent->prop_str("applicant"),
					"type" 		=> $this->types[$patent->prop("type")],
					"state" 	=> ($patent->prop("verified")) ? t("Vastu v&otilde;etud") : (($patent->prop("nr")) ? t("Saadetud") : ""),
					"name" 	 	=> $patent->name(),
					"id" 	 	=> $patent->id(),
					"url"  		=> $url,
					"procurator"  	=> $patent->prop_str("procurator"),
					"change"	=> $change,
					"view"		=> $view_url,
					"sign"		=> $sign,
				));
				$c .= $this->parse("LIST");
			}
			$this->vars(array(
				"LIST" => $c,
			));
		}
		return $this->parse();
	}*/
	
}
?>
