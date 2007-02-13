<?php
// $Header: /home/cvs/automatweb_dev/classes/common/bank_payment.aw,v 1.22 2007/02/13 13:29:43 markop Exp $
// bank_payment.aw - Bank Payment 
/*

@classinfo syslog_type=ST_BANK_PAYMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default field=meta
@default method=serialize

@default group=general
	@property default_unit_sum type=textbox
	@caption Vaikimisi &uuml;hiku summa
	
	@property expl type=textbox
	@caption Selgitus

	@property template type=select
	@caption Pangaformide template

	@property private_key type=relpicker reltype=RELTYPE_KEY
	@caption Privaatv&otilde;ti
	
	@property private_c_key type=relpicker reltype=RELTYPE_P_KEY
	@caption krediitkaardi privaatv&otilde;ti
	
	@property bank_return_url type=textbox
	@caption Url, kuhu tagasi tulla eduka makse puhul
	
	@property cancel_url type=textbox
	@caption Url, kuhu tagasi tulla eba&otilde;nnestunud makse puhul

	@property test type=checkbox
	@caption testre&#382;iim (toimib ainult nende pankadega , millel on olemas testkeskkond)

@groupinfo bank caption="Pankade info"

@default group=bank
	@property bank type=callback callback=callback_bank store=no no_caption=1

#RELTYPES

@reltype KEY value=2 clid=CL_FILE
@caption Privaatv&otilde;ti

@reltype P_KEY value=3 clid=CL_FILE
@caption Privaatv&otilde;ti

*/

class bank_payment extends class_base
{	//olemasolevad panga
	var $banks = array (
		"hansapank"		=> "Hansapank",
		"seb"			=> "SEB Eesti &Uuml;hispank",
		"nordeapank"		=> "Nordea Pank",
		"krediidipank"		=> "Krediidipank",
		"sampopank"		=> "Sampo Pank",
		"hansapank_lv"		=> "L&Auml;ti Hansapank",
		"hansapank_lt"		=> "Leedu Hansapank",
		"credit_card"		=> "Kaardikeskus (krediitkaart)",
	);
	
	//kõikidele pankadele ühine info
	var $for_all_banks = array(
		"amount"	=> "Summa",
		"expl"		=> "Selgitus",
	);
	//igal pangal on vaja selliseid asju teada
	var $bank_props = array(
		"sender_id"	=> "Kaupmehe ID",
		"stamp"		=> "Arvenumber",
	);

	//erinevate pankade lingid
	var $bank_link = array(
		"hansapank"		=> "https://www.hanza.net/cgi-bin/hanza/pangalink.jsp",
		"seb"			=> "https://www.seb.ee/cgi-bin/unet3.sh/un3min.r",
		"sampopank"		=> "https://www.sampo.ee/cgi-bin/pizza",
		"krediidipank"		=> "https://i-pank.krediidipank.ee/teller/maksa",
		"nordeapank"		=> "https://solo3.merita.fi/cgi-bin/SOLOPM01",
		"hansapank_lv"		=> "https://www.hanzanet.lv/banklink/",
		"hansapank_lt"		=> "https://www.hanzanet.lv/banklink/",
		"credit_card"		=> "https://pos.estcard.ee/test-pos/servlet/iPAYServlet",
	);

	var $merchant_id = array(
		"EYP" => "seb",
		"HP" => "Hansapank",
	);

	//mõnel pangal testkeskkond, et tore mõnikord seda kasutada proovimiseks
	var $test_link = array(
		"seb"	=> "https://www.seb.ee/cgi-bin/dv.sh/un3min.r",
	);

	//test keskkonnas läheb üldjuhul miskeid testandmeid vaja
	var $test_priv_keys = array(
		"seb"	=> "seb_test_priv.pem",
	);

	/** 
		@attrib api=1
 	**/
	function bank_payment()
	{
		$this->init(array(
			"tpldir" => "common/bank_payment",
			"clid" => CL_BANK_PAYMENT
		));
		
	}

	/** 
		@attrib name=form_test_case nologin=1 is_public=1 all_args=1

 	**/
	function form_test_case($arr)
	{/*
		die('<form name="postform" id="postform" method="post" action=https://pos.estcard.ee/test-pos/servlet/iPAYServlet>
			<input type="hidden" name=action value="gaf">
			<input type="hidden" name=ver value="002">
			<input type="hidden" name=id value="Reval">
			<input type="hidden" name=ecuno value="112309">
			<input type="hidden" name=eamount value="100000">
			<input type="hidden" name=cur value="EEK">
			<input type="hidden" name=datetime value="20070104165623">
			<input type="hidden" name=mac value="RG8JJGd0QRIBFQW845eRjG2EyfNTQSleommHuldYQdtooZxIzHDhHJaLm+wYhVHI2E2LqV76faI0bro7iObPBR8C1wcTXqSRKNuTBobxb0SPfZ3/nnbdQ51svMXNdDBNQTqn9gawwxxcOz1PuoRunYA+v1n7cyxekqkS4ZIxkWQ=">
			<input type="hidden" name=lang value="et">
			<input type=submit value="maksa ilgelt pappi">
			</form>
		');
		/*die('
			<form name="makse" id="makse" method="post" action="http://marko.dev.struktuur.ee/orb.aw?class=bank_payment&id=10580">
			<br>
			<input type="textbox" name="amount" value=3000000>
			<input type=submit value="maksa ilgelt pappi">
			</form>'
		);*/
		//veel üks teist imiteerimaks panka
		die('<form name="postform2" id="postform2" method="post" action=http://www.revalhotels.com/automatweb/bank_return.aw>
			<input type="hidden" name=VK_SERVICE value="1101">
			<input type="hidden" name=VK_VERSION value="008">
			<input type="hidden" name=VK_SND_ID value="EYP">
			<input type="hidden" name=VK_REC_ID value="testvpos">
			<input type="hidden" name=VK_STAMP value="10002050618003">
			<input type="hidden" name=VK_T_NO value="23888">
			<input type="hidden" name=VK_REF value="285906">
			<input type="hidden" name=VK_MAC value="b0Msf4RJn97KeESEPMK4S+t7DTszxdPxfOBTGSWhn2b+o71hv6rzMQq97+uBt5HILxHNxBpHv1aoywXjRA4/4q9XRAjP28vZ9mPUo0W/pBaI/tC6eteb6Cp6w443+mMadf6emb2rAtSDaod6pdwSxnIEzkMD6OzSccFI1TiuzjU=">
			<input type="hidden" name=lang value="et">
			<input type=submit value="maksa ilgelt pappi">
			</form>
		');
	}
	
	/** 
		@attrib name=bank_forms api=1 default=1 nologin=1 is_public=1 all_args=1
	/**
	@attrib api=1 params=name
	@param id optional type=oid
		bank_payment object ID 
	@param amount optional type=int
		Amount to be paid. Max length=17
	@param units optional type=int
		if amount is not set, you give how many units, ... payment_id must be set then and payment objects prop default_unit_price also
	@param reference_nr optional type=int
		Reference number of payment order. Max length=19
	@param service optional type=int default=1002
		Number of service. Length=4
	@param sender_id optional type=string
		if no ID is set, can find a payment object by sender_id
	@param stamp optional type=string
		Query ID. Max length=20
	@param expl optional type=string
		Explanation of payment order. Max length=70
	@param return_url optional type=string
		URL to which response is sent in performing the transaction. Max length=60. 
	@param cancel_url optional type=string default=$return_url
		URL to which response is sent when the transaction is unsuccessful. Max length=60
	@param lang optional type=string default="EST"
		Preferred language of communication. Length=3
	@returns String/html - the bank payment site or correct form.

	@comment
		calculates the reference number and digital signature VK_MAC
		Returns the bank payment site or correct form.
	@example
		<form name="makse" id="makse" method="post" action="http://marko.dev.struktuur.ee/orb.aw?class=bank_payment&id=10580">
		<input type="textbox" name="amount" value=3000000>
		<input type=submit value="maksa ilgelt pappi">
		</form>'
	**/

	function bank_forms($arr = array())
	{
		$data = $_GET+$_POST+$arr;
		$payment = $this->_get_payment_object($data);
		if(!is_object($payment))
		{
			return "";
		}
		
		if($payment->prop("template"))
		{
			$tpl = $payment->prop("template");
		}
		else
		{
			$tpl = "bank_forms.tpl";
		}
		
		if(!$this->read_template($tpl, $silent=1))
		{
			return "";	
		}
		//võtab objekti seest mõningad puuduvad väärtused
		$data = $this->_add_object_data($payment,$data);
		//lisab puuduvad default väärtused
		$data = $this->_add_default_data($data);
		//paneb panga crapi templatesse
		$this->_init_banks($payment,$data);

		return $this->parse();
	}

	function _add_object_data($payment,$data)
	{
		if($payment->prop("test"))
		{
			$data["test"] = 1;
		}
		if(!$data["priv_key"] && $payment->prop("private_key"))
		{
			$file_inst = get_instance(CL_FILE);
			$file = $file_inst->get_file_by_id($payment->prop("private_key"));
			$data["priv_key"] = $file["content"];
		}
		if($payment->prop("private_c_key") && $data["bank_id"] == "credit_card")
		{
			$file_inst = get_instance(CL_FILE);
			$file = $file_inst->get_file_by_id($payment->prop("private_c_key"));
			$data["priv_key"] = $file["content"];
		}
		if($data["test"] &&  $this->test_link[$data["bank_id"]])
		{
			$fp = fopen($this->cfg["site_basedir"]."/pank/".$data["bank_id"]."_test_priv.pem", "r");
			$data = fread($fp, 8192);
			fclose($fp);
			if($data)
			{
				$arr["priv_key"] = $data;
			}
		}
		if(!$data["expl"])
		{
			$data["expl"] = $payment->prop("expl").$data["expl"];
		}
		if(!$data["return_url"])
		{
			//$data["return_url"] = $payment->prop("return_url");
		}
		if(!$data["cancel_url"])
		{
			$data["cancel_url"] = $payment->prop("cancel_url");
			$_SESSION["bank_payment"]["cancel"] = $payment->prop("cancel_url");
		}
		
		if(!$data["amount"] && $data["units"] && $payment->prop("default_unit_sum"))
		{
			$data["amount"] = $data["units"]*$payment->prop("default_unit_sum");
		}
		
		$payment_data = $payment->meta("bank");
		$data["sender_id"] = $payment_data[$data["bank_id"]]["sender_id"];
		$data["stamp"] = $payment_data[$data["bank_id"]]["stamp"];

		if($data["units"])
		{
			$data["amount"] = $data["units"]*$payment->prop("default_unit_sum");
		}
		
		return $data;
	}
	
	function _add_default_data($data)
	{
		return $data;
	}

	function _init_banks($payment,$data)
	{
		$bank_data = $payment->meta("bank");
		foreach($this->banks as $bank => $name)
		{
			if(array_key_exists($bank , $bank_data) && $bank_data[$bank]["sender_id"])
			{
				$c = "";
				$bank_form = $this->do_payment(array(
					"form"		=> 1,
					"bank_id"	=> $bank,
					"service"	=> $data["service"],
					"lang" 		=> $data["lang"],
					"sender_id"	=> $bank_data[$bank]["sender_id"],
					"stamp"		=> $bank_data[$bank]["stamp"],
					"amount"	=> $data["amount"],
					"reference_nr"	=> $data["reference_nr"],
					"expl"		=> $data["expl"],
					"priv_key" 	=> $data["priv_key"],
					"cancel_url"	=> $data["cancel_url"],
					"return_url"	=> $data["return_url"],
				));
				$link = $this->bank_link[$bank];
				$this->vars(array(
					"data" => $bank_form,
					"link" => $link,
				));
				$c .= $this->parse($bank);
				$this->vars(array(
					$bank => $c,
				));
			}
		}
	}

	function _get_payment_object($arr)
	{
		extract($arr);
				
		if(is_oid($id) && $this->can("view" , $id))
		{
			$payment_object = obj($id);
		}
		else 
		{
			$ol = new object_list(array(
				"class_id" => CL_BANK_PAYMENT,
				"lang_id" => array(),
				"site_id" => array(),
				"name" => $name,
			));
			if(sizeof($ol->arr() == 1))
			{
				$payment_object = reset($ol->arr());
			}
			else
			{
				$ol = new object_list(array(
					"class_id" => CL_BANK_PAYMENT,
					"lang_id" => array(),
					"site_id" => array(),
				));
				foreach($ol->arr() as $payment)
				{
					$meta = $payment->meta("bank");
					foreach($meta as $data)
					{
						if($data["sender_id"] == $sender_id)
						{
							$payment_object = $payment;
							break;
						}
					}
				}
			}
		}
		return $payment_object;
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "template":
				$tm = get_instance("templatemgr");
				$prop["options"] = $tm->template_picker(array(
					"folder" => "common/bank_payment"
				));
				if(!sizeof($prop["options"]))
				{
					$prop["caption"] .= t("\n".$this->site_template_dir."");
				}
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
					case "bank":
				$this->submit_meta($arr);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
	
	function submit_meta($arr = array())
	{
		$meta = $arr["request"]["meta"];
		if (is_array($meta))
		{
			$arr["obj_inst"]->set_meta($arr["prop"]["name"], $meta);
			$arr["obj_inst"]->save();
		};
	}
	
	//tekitab võimalike pankade ja propertyte nimekirja
	function callback_bank($arr)
	{
		$bank_payment = get_instance(CL_BANK_PAYMENT);
		$meta = $arr["obj_inst"]->meta("bank");
		foreach($bank_payment->banks as $key => $val)
		{
			$ret[] = array(
				"name" => "meta[".$key."][use]",
				"type" => "chechbox" ,
				"ch_value" => 1 ,
				"value" => $meta["key"],
				"caption" => $val,
			);
			foreach($bank_payment->bank_props as $prop=>$caption)
			{
				$ret[] = array(
					"name" => "meta[".$key."][".$prop."]",
					"type" => "textbox",
					"value" => $meta[$key][$prop],
					"caption" => $caption
				);
			}
		}
		return $ret;
	}
	
	/**
	@attrib api=1 params=name
	@param bank_id required type=string
		bank id. possible choices: "seb", "hansapank" , "sampopank", "nordeapank" , "krediidipank" 
	@param amount optional type=int
		Amount to be paid. Max length=17
	@param units optional type=int
		if amount is not set, you give how many units, ... payment_id must be set then and payment objects prop default_unit_price also
	@param reference_nr optional type=int
		Reference number of payment order. Max length=19
	@param payment_id optional type=oid
		if set, takes sender_id,explanation and ...  data from bank payment object	
	@param service optional type=int default=1002
		Number of service. Length=4
	@param version optional type=int default=008
		Encryption algorithm used. Length=3	
	@param sender_id optional type=string
		ID of compiler of query (merchant's ID). Max length=10
	@param stamp optional type=string
		Query ID. Max length=20
	@param currency optional type=string default="EEK"
		Name of currency: EEK/DEM/FIM etc. Length=3
	@param expl optional type=string
		Explanation of payment order. Max length=70
	@param return_url optional type=string default=aw_ini_get("baseurl")."/automatweb/bank_return.aw"
		URL to which response is sent in performing the transaction. Max length=60. If it is not set, you must set $_SESSION["bank_payment"]["url"].
	@param cancel_url optional type=string default=$return_url
		URL to which response is sent when the transaction is unsuccessful. Max length=60
	@param lang optional type=string default="EEK"
		Preferred language of communication. Length=3
	@param priv_key optional type=string
		Query compiler's private key (merchant's private key)
	@param form optional type=int
		If form is set, function returns html form, else returns to bank site.
	@param test optional type=int
		If test is set, the function uses the bank test site if it exists
	@returns bank web page, or string/html form
	
	@comment
		calculates the reference number and digital signature VK_MAC
		Returns the bank payment site or correct form.
		sender_id, stamp, amount, reference_nr, stamp , amount and msg must be set and have to have private key for correct form.
		Have to set $_SESSION["bank_payment"]["url"] if you want to get response from the bank, return_url is only for url with no parameters.
	
	@example
		$bank_payment = get_instance(CL_BANK_PAYMENT);
		$_SESSION["bank_payment"]["url"] = post_ru();
		return $bank_payment->do_payment(array(
			"test"		=> 1,
			"bank_id"	=> "seb",
			"sender_id"	=> "EXPRPOST",
			"stamp"		=> row["arvenr"],
			"amount"	=> $data["amount"],
			"reference_nr"	=> 123456,
			"expl"		=> "Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
		));

	**/	
	function do_payment($arr)
	{
		switch($arr["bank_id"]) {
			case "seb":
				$arr = $this->check_args($arr);
				return $this->seb($arr);
				break;
			case "hansapank_lv":
				$arr = $this->check_args($arr);
				return $this->hansa_lv($arr);
				break;
			case "hansapank_lt":
				$arr = $this->check_args($arr);
				return $this->hansa_lt($arr);
				break;
			case "hansapank":
				$arr = $this->check_args($arr);
				return $this->hansa($arr);
				break;
			case "sampopank":
				$arr = $this->check_args($arr);		
				return $this->sampo($arr);
				break;
			case "nordeapank":
				$arr = $this->check_nordea_args($arr);
				return $this->nordea($arr);
				break;
			case "krediidipank":
				$arr = $this->check_args($arr);
				return $this->krediidi($arr);
				break;
			case "credit_card":
				$arr = $this->check_cc_args($arr);
				return $this->credit_card($arr);
				break;
		}
	}
	
	function check_args($arr)
	{
		if(is_oid($arr["payment_id"]))
		{
			$payment = obj($arr["payment_id"]);
			$arr = $this->_add_object_data($payment , $arr);
		}
		if(!$arr["service"]) $arr["service"] = "1002";
		if(!$arr["version"]) $arr["version"] = "008";
		if(!$arr["curr"]) $arr["curr"] = "EEK";
		if(!$arr["lang"]) $arr["lang"] = "EST";
		if(!$arr["stamp"]) $arr["stamp"] = "666";
		if(!$arr["cancel_url"]) $arr["cancel_url"] = aw_ini_get("baseurl")."/automatweb/bank_return.aw";
		if(!$arr["return_url"]) $arr["return_url"] = aw_ini_get("baseurl")."/automatweb/bank_return.aw";
		if(!$arr["priv_key"])
		{
			if($arr["test"] && $this->test_priv_keys[$arr["bank_id"]])
			{
				$file = $this->test_priv_keys[$arr["bank_id"]];
			}
			else
			{
				$file = "privkey.pem";
			}
			$fp = fopen($this->cfg["site_basedir"]."/pank/".$file, "r");
			$arr["priv_key"] = fread($fp, 8192);
			fclose($fp);
		}
		$arr["reference_nr"].= (string)$this->viitenr_kontroll_731($arr["reference_nr"]);
		return($arr);
	}
		
	//if form = 1, returns hrml input tags in form.
	function submit_bank_info($args)
	{
		extract($args);
		$return = "";
		if(!$form) $return.= '<form name="postform" id="postform" method="post" action='.$link.'>
		';
		foreach($params as $key => $val)
		{
			$return.= '<input type="hidden" name='.$key.' value="'.(string)$val.'">
			';
		};
		if($form) return $return;
		print $return.'<p class="text">Kui suunamist mingil p&otilde;hjusel ei toimu, palun vajutage <a href="#" onClick="document.postform.submit();">siia</a></p>
		</form>
		<script type="text/javascript">
			function pform() {
				document.postform.submit();
			}
			function WindowOnload(f){
				var prev=window.onload;
				window.onload=function(){
					if(prev)prev();
					f();
				}
			}
			WindowOnload(pform);
		</script>';
		die();	
	}
	
	function hansa($args) 
	{
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$curr;
		$VK_message.= sprintf("%03d",strlen($reference_nr)).$reference_nr;
		$VK_message.= sprintf("%03d",strlen($expl)).$expl;
		$VK_signature = "";
		$pkeyid = openssl_get_privatekey($priv_key);
		openssl_sign($VK_message, $VK_signature, $pkeyid);
		openssl_free_key($pkeyid);
		$VK_MAC = base64_encode( $VK_signature);

		$http = get_instance("protocols/file/http");
		$link = "https://www.hanza.net/cgi-bin/hanza/pangalink.jsp";
		$handler = "https://www.hanza.net/cgi-bin/hanza/pangalink.jsp";
		$params = array(
			"VK_SERVICE"	=> $service,	//"1002"
			"VK_VERSION"	=> $version,	//"008"
			"VK_SND_ID"	=> $sender_id,	//"EXPRPOST"
			"VK_STAMP"	=> $stamp,	//row["arvenr"]
			"VK_AMOUNT"	=> $amount,	//$row["summa"];
			"VK_CURR"	=> $curr,	//"EEK"
			"VK_REF"	=> $reference_nr,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";//60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";//60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		return $this->submit_bank_info(array("params" => $params , "link" => $link , "form" => $form));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}

	function hansa_lv($args) 
	{
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$curr;
		$VK_message.= sprintf("%03d",strlen($reference_nr)).$reference_nr;
		$VK_message.= sprintf("%03d",strlen($expl)).$expl;
		$VK_signature = "";
		$pkeyid = openssl_get_privatekey($priv_key);
		openssl_sign($VK_message, $VK_signature, $pkeyid);
		openssl_free_key($pkeyid);
		$VK_MAC = base64_encode( $VK_signature);

		$http = get_instance("protocols/file/http");
		$link = $this->bank_link["hansapank_lv"];
		$link = "https://www.hanzanet.lv/cgi-bin/hanza/pangalink.jsp";
		$handler = $link;
		$params = array(
			"VK_SERVICE"	=> $service,	//"1002"
			"VK_VERSION"	=> $version,	//"008"
			"VK_SND_ID"	=> $sender_id,	//"EXPRPOST"
			"VK_STAMP"	=> $stamp,	//row["arvenr"]
			"VK_AMOUNT"	=> $amount,	//$row["summa"];
			"VK_CURR"	=> $curr,	//"EEK"
			"VK_REF"	=> $reference_nr,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";//60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";//60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		return $this->submit_bank_info(array("params" => $params , "link" => $link , "form" => $form));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}

	function hansa_lt($args) 
	{
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$curr;
		$VK_message.= sprintf("%03d",strlen($reference_nr)).$reference_nr;
		$VK_message.= sprintf("%03d",strlen($expl)).$expl;
		$VK_signature = "";
		$pkeyid = openssl_get_privatekey($priv_key);
		openssl_sign($VK_message, $VK_signature, $pkeyid);
		openssl_free_key($pkeyid);
		$VK_MAC = base64_encode( $VK_signature);

		$http = get_instance("protocols/file/http");
		$link = $this->bank_link["hansapank_lt"];;
		$handler = $link;
		$params = array(
			"VK_SERVICE"	=> $service,	//"1002"
			"VK_VERSION"	=> $version,	//"008"
			"VK_SND_ID"	=> $sender_id,	//"EXPRPOST"
			"VK_STAMP"	=> $stamp,	//row["arvenr"]
			"VK_AMOUNT"	=> $amount,	//$row["summa"];
			"VK_CURR"	=> $curr,	//"EEK"
			"VK_REF"	=> $reference_nr,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";//60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";//60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		return $this->submit_bank_info(array("params" => $params , "link" => $link , "form" => $form));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}

	function seb($args)
	{
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$curr;
		$VK_message.= sprintf("%03d",strlen($reference_nr)).$reference_nr;
		$VK_message.= sprintf("%03d",strlen($expl)).$expl;
		$VK_signature = "";
		$pkeyid = openssl_get_privatekey($priv_key);
		openssl_sign($VK_message, $VK_signature, $pkeyid);
		openssl_free_key($pkeyid);
		$VK_MAC = base64_encode($VK_signature);
		$http = get_instance("protocols/file/http");
		$link = "https://www.seb.ee/cgi-bin/unet3.sh/un3min.r";
		if($test) $link = "https://www.seb.ee/cgi-bin/dv.sh/un3min.r";
		$handler = "https://www.seb.ee/cgi-bin/unet3.sh/un3min.r";
		$params = array(
			"VK_SERVICE"	=> $service,	//"1002"
			"VK_VERSION"	=> $version,	//"008"
			"VK_SND_ID"	=> $sender_id,	//"EXPRPOST" //	15	Päringu koostaja ID (Kaupluse ID)
			"VK_STAMP"	=> $stamp,	//row["arvenr"]
			"VK_AMOUNT"	=> $amount,	//$row["summa"];
			"VK_CURR"	=> $curr,	//"EEK"
			"VK_REF"	=> $reference_nr,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";	//	60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";	//	60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		return $this->submit_bank_info(array("params" => $params , "link" => $link , "form" => $form));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}

	function sampo($args)
	{
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$curr;
		$VK_message.= sprintf("%03d",strlen($reference_nr)).$reference_nr;
		$VK_message.= sprintf("%03d",strlen($expl)).$expl;
		$VK_signature = "";
		$pkeyid = openssl_get_privatekey($priv_key);
		openssl_sign($VK_message, $VK_signature, $pkeyid);
		openssl_free_key($pkeyid);
		$VK_MAC = base64_encode( $VK_signature);

		$http = get_instance("protocols/file/http");
		$link = "https://www.sampo.ee/cgi-bin/pizza";
		$handler = "https://www.sampo.ee/cgi-bin/pizza";
		$params = array(
			"VK_SERVICE"	=> $service,	//"1002"
			"VK_VERSION"	=> $version,	//"008"
			"VK_SND_ID"	=> $sender_id,	//"EXPRPOST" //	15	Päringu koostaja ID (Kaupluse ID)
			"VK_STAMP"	=> $stamp,	//row["arvenr"]
			"VK_AMOUNT"	=> $amount,	//$row["summa"];
			"VK_CURR"	=> $curr,	//"EEK"
			"VK_REF"	=> $reference_nr,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";	//	60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";	//	60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		return $this->submit_bank_info(array("params" => $params , "link" => $link , "form" => $form));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}

	function krediidi($args)
	{
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$curr;
		$VK_message.= sprintf("%03d",strlen($reference_nr)).$reference_nr;
		$VK_message.= sprintf("%03d",strlen($expl)).$expl;
		$VK_signature = "";
		$pkeyid = openssl_get_privatekey($priv_key);
		openssl_sign($VK_message, $VK_signature, $pkeyid);
		openssl_free_key($pkeyid);
		$VK_MAC = base64_encode( $VK_signature);

		$http = get_instance("protocols/file/http");
		$link = "https://i-pank.krediidipank.ee/teller/maksa";
		$handler = "https://i-pank.krediidipank.ee/teller/maksa";
		$params = array(
			"VK_SERVICE"	=> $service,	//"1002"
			"VK_VERSION"	=> $version,	//"008"
			"VK_SND_ID"	=> $sender_id,	//"EXPRPOST" //	15	Päringu koostaja ID (Kaupluse ID)
			"VK_STAMP"	=> $stamp,	//row["arvenr"]
			"VK_AMOUNT"	=> $amount,	//$row["summa"];
			"VK_CURR"	=> $curr,	//"EEK"
			"VK_REF"	=> $reference_nr,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";	//	60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";	//	60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		return $this->submit_bank_info(array("params" => $params , "link" => $link , "form" => $form));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}	

	function check_cc_args($arr)
	{
		if(is_oid($arr["payment_id"]))
		{
			$payment = obj($arr["payment_id"]);
			$arr = $this->_add_object_data($payment , $arr);
		}
		if(!$arr["curr"]) $arr["curr"] = "EEK";
		if(!$arr["lang"]) $arr["lang"] = "et";
		if(!$arr["cancel_url"]) $arr["cancel_url"] = aw_ini_get("baseurl")."/automatweb/bank_return.aw";
		if(!$arr["return_url"]) $arr["return_url"] = aw_ini_get("baseurl")."/automatweb/bank_return.aw";
		if(!$arr["priv_key"])
		{
			$file = "privkey.pem";
			$fp = fopen($this->cfg["site_basedir"]."/pank/".$file, "r");
			$arr["priv_key"] = fread($fp, 8192);
			fclose($fp);
		}
		$arr["reference_nr"].= (string)$this->viitenr_kontroll_731($arr["reference_nr"]);
		$arr["amount"] = $arr["amount"]*100; //sentides
		$arr["datetime"] = date("YmdHis", time());
		if(!$arr["service"]) $arr["service"] = "gaf";
		if(!$arr["version"]) $arr["version"] = "002";
		return($arr);
	}
	
	function credit_card($args)
	{
		extract($args);
		//test:
		$action="$service";
		$ver="$version";
		$id="$sender_id";
		$idnp = $id;
		$ecuno='123456';
		$eamount='1000';
		$cur='EEK';
		$datetime=date("YmdHis");
		$lang='et';
		$id=sprintf("%-10s", "$id");
		$ecuno=sprintf("%012s", "$reference_nr");
		$eamount=sprintf("%012s", "$amount");
		$data = $ver . $id . $ecuno . $eamount . $cur . $datetime;
		$signature=sha1($data);
	//	echo "signatuur: <pre>$data</pre><br>";
		$pkeyid = openssl_get_privatekey($priv_key);
		openssl_sign($data, $signature, $pkeyid);
		openssl_free_key($pkeyid);
		$mac=bin2hex($signature);
		echo "https://pos.estcard.ee/test-pos/servlet/iPAYServlet?action=$action&amp;ver=$ver&amp;id=$idnp&amp;ecuno=$ecuno&amp;eamount=$eamount&amp;cur=$cur&amp;datetime=$datetime&amp;mac=$mac&amp;lang=en";
		//testi lõpp

		$VK_message = $version;
		$VK_message.= sprintf("%-10s", $sender_id);
		$VK_message.= sprintf("%012s",$reference_nr);
		$VK_message.= sprintf("%012s",$amount);
		$VK_message.= $curr;
		$VK_message.= $datetime;

		$signature=sha1($VK_message);
		$pkeyid = openssl_get_privatekey($priv_key);
		openssl_sign($data, $VK_signature, $pkeyid);
		openssl_free_key($pkeyid);
		$VK_MAC = bin2hex($VK_signature);//base64_encode( $VK_signature);

		$link = $this->bank_link["credit_card"];
		$params = array(
			"action"	=> $service,		//"gaf"
			"ver"		=> $version,		//Protokolli versioon, Fikseeritud väärtus: 002
			"id"		=> $sender_id,		//Kaupmehe kasutajanimi süsteemis
			"ecuno"		=> $reference_nr,	//Tehingu unikaalne number kaupmehe süsteemis,min. lubatud väärtus 100000
			"eamount"	=> $amount,		//Kaupmehe süsteemi poolt antav tehingu summa sentides.;
			"cur"		=> $curr,		//Tehingu valuuta nimi . Fikseeritud: EEK
			"datetime"	=> $datetime,		//AAAAKKPPTTmmss 	Tehingu kuupäev,kellaaeg
			"mac" 		=> $VK_MAC,		//Sõnumi signatuur (MAC)*
			"lang" 		=> $lang,		//et,en . Süsteemis kasutatav keel. et - Eesti, en - Inglise
		);
		return $this->submit_bank_info(array("params" => $params , "link" => $link , "form" => $form));
	}

	function check_nordea_args($arr)
	{
		if(!$arr["service"]) $arr["service"] = "0002";
		if(!$arr["version"]) $arr["version"] = "0001";
		if(!$arr["curr"]) $arr["curr"] = "EEK";
		if(!$arr["confirm"]) $arr["confirm"] = "NO";
		if(!$arr["recieve_account"]) $arr["recieve_account"] = "";
		if(!$arr["recieve_name"]) $arr["recieve_name"] = "";
		if(!$arr["recieve_id"]) $arr["recieve_id"] = "10354213";
		if(!$arr["date"]) $arr["date"] = 'EXPRESS';
		if(!$arr["lang"]) $arr["lang"] = "4";
//		if(!$arr["priv_key"]) $arr["priv_key"] = "g94z7e7KgP6PM8av7kIF7bwX8YNZ7eFX";//suht halb mõte muidugi ... aga see on siin ajutiselt
		
		if(!$arr["cancel_url"]) $arr["cancel_url"] = aw_ini_get("baseurl")."/automatweb/bank_return.aw";
		if(!$arr["return_url"]) $arr["return_url"] = aw_ini_get("baseurl")."/automatweb/bank_return.aw";
		if(!$arr["priv_key"])
		{
			if($arr["test"] && $this->test_priv_keys[$arr["bank_id"]]) $file = $this->test_priv_keys[$arr["bank_id"]];
			else $file = "privkey.pem";
			$fp = fopen($this->cfg["site_basedir"]."/pank/".$file, "r");
			$arr["priv_key"] = fread($fp, 8192);
			fclose($fp);
		}
		return($arr);
	}

	function nordea($args)
	{
		extract($args);
		$SOLOPMT_MAC      = '';
		$VK_message       = $service.'&';
		$VK_message       .= $stamp.'&';
		$VK_message       .= $sender_id.'&';
		$VK_message       .= $amount.'&';
		$VK_message       .= $reference_nr.'&';
		$VK_message       .= $date.'&';
		$VK_message       .= $curr.'&';
		$VK_message       .= ''.'&';
		$SOLOPMT_MAC      = strtoupper(md5( $VK_message ));
		
		$http = get_instance("protocols/file/http");
 		$link = "https://solo3.merita.fi/cgi-bin/SOLOPM01";
 		$handler = "https://solo3.merita.fi/cgi-bin/SOLOPM01";
		$params = array(
			"SOLOPMT_VERSION"     => $service,// 1.    Payment Version   SOLOPMT_VERSION   "0002"   AN 4  M
			"SOLOPMT_STAMP"       => $stamp,// 2.    Payment Specifier    SOLOPMT_STAMP  Code specifying the payment   N 20  M 
			"SOLOPMT_RCV_ID"      => $sender_id, // 3.    Service Provider ID  SOLOPMT_RCV_ID    Customer ID (in Nordea's register)  AN 15    M 
			"SOLOPMT_RCV_ACCOUNT" => $stamp,// 4.    Service Provider's Account    SOLOPMT_RCV_ACCOUNT  Other than the default account   AN 15    O
			"SOLOPMT_RCV_NAME"    => $recieve_name,//5.    Service Provider's Name    SOLOPMT-RCV_NAME  Other than the default name   AN 30    O 
			"SOLOPMT_LANGUAGE"    => $lang,// 6.    Payment Language  SOLOPMT_LANGUAGE  1 = Finnish 2 = Swedish 3 = English    N 1   O 
			"SOLOPMT_AMOUNT"      => $amount,// 7.    Payment Amount    SOLOPMT_AMOUNT    E.g. 990.00    AN 19    M 
			"SOLOPMT_REF"         => $reference_nr,// 8.    Payment Reference Number   SOLOPMT_REF    Standard reference number  AN 20    M 
			"SOLOPMT_DATE"        => $date,// 9.    Payment Due Date  SOLOPMT_DATE   "EXPRESS" or "DD.MM.YYYY"  AN 10    M 
			"SOLOPMT_MSG"         => $expl,// 10.   Payment Message   SOLOPMT_MSG    Service user's message  AN 234   O 
			"SOLOPMT_RETURN"      => $return_url,// 11.   Return Address    SOLOPMT_RETURN    Return address following payment    AN 60    M 
			"SOLOPMT_CANCEL"      => $cancel_url,// 12.   Cancel Address    SOLOPMT_CANCEL    Return address if payment is cancelled    AN 60    M 
			"SOLOPMT_REJECT"      => $cancel_url,// 13.   Reject Address    SOLOPMT_REJECT    Return address for rejected payment    AN 60    M 
							// 14.   Solo Button OR Solo Symbol    SOLOPMT_ BUTTON SOLOPMT_IMAGE    Constant    Constant    O       // $SOLOPMT_ BUTTON SOLOPMT_IMAGE   Constant    Constant    O 			
			"SOLOPMT_MAC"         => $SOLOPMT_MAC,  // 15.   Payment MAC    SOLOPMT_MAC    MAC   AN 32    O 
			"SOLOPMT_CONFIRM"     => $confirm,// 16.   Payment Confirmation    SOLOPMT_CONFIRM   YES or NO   A 3   O 
			"SOLOPMT_KEYVERS"     => $version,// 17.   Key Version    SOLOPMT_KEYVERS   E.g. 0001   N 4   O 
			"SOLOPMT_CUR"         => $curr,// 18.   Currency Code  SOLOPMT_CUR    EUR   A 3   O 
		);
		return $this->submit_bank_info(array("params" => $params , "link" => $link , "form" => $form));
	//	return $http->post_request($link, $handler, $params, $port = 80);	
	}

	function viitenr_kontroll_731($nr)
	{
		$nr = (string)$nr;
		$count = strlen($nr);
		$sum = 0;
		$x = 7;
		while($count > 0)
		{
			$count = $count - 1;
			$sum = $sum + (integer)$nr[$count]*$x;
			if($x == 7) $x = 3;
			elseif($x == 3) $x = 1;
			elseif($x == 1) $x = 7;
		}
		return (10 - ($sum%10))%10;
	}
	
	/**
	@attrib name=pay_site is_public="1" caption="Change" no_login=1 api=1 params=name
	@param die optional type=bool
		if set, dies instead of return
	@returns string/html
	@comment
		makes a list of supported banks with correct forms
		before calling this function you should fill $_SESSION["bank_payment"]
		uses template file bank_pay_site.tpl, if it exists , then every sub gets vars:
			"data" - hidden input fields needed in form 
			"link" - url to banklink
	@example
		$targ = obj($arr["alias"]["target"]);
		$_SESSION["bank_payment"] = array(
			"data"		=> $targ->meta("bank")// Array(
				//	[amount] //Amount to be paid. Max length=17
				//	[expl] //Explanation of payment order. Max length=70
				//	[bank_id] => Array//bank id. possible choices: "seb", "hansapank" , "sampopank", "nordeapank" , "krediidipank"
				//	(
				//		[sender_id]//ID of compiler of query (merchant's ID). Max length=10
				//		[stamp]//Query ID. Max length=20
				//	)
				//	[bank_id2] => Array
				//	(
				//		[sender_id]
				//		[stamp]
				//	))
			"reference_nr"	=> $_SESSION["realestate_input_data"]["realestate_id"],//Reference number of payment order. Max length=19
			"url" 		=> post_ru(),//optional
			"cancel"	=> post_ru(),//optional 
		);
		$bank_payment = get_instance(CL_BANK_PAYMENT);
		$ret.= '<a href="';
		$ret.= $bank_payment->mk_my_orb("pay_site", array());
		$ret.= '"> Maksma </a>';
	**/
	function pay_site($args)
	{
		global $die;
		extract($args);
		if(!$_SESSION["bank_payment"]) return false;
		extract($_SESSION["bank_payment"]);
		$tpl = "bank_pay_site.tpl";
		if($this->read_template($tpl, $silent=1))
		{
			$template_exists = 1;
		}
		$ret = "";
		foreach($this->banks as $bank => $name)
		{
			if(array_key_exists($bank , $data) && $data[$bank]["sender_id"])
			{
				$ret.='<img src="'.aw_ini_get("baseurl").'/automatweb/images/pank/'.$bank.'_pay.gif">';
				$bank_form = $this->do_payment(array(
					"form"		=> 1,
					"test"		=> $test,
					"bank_id"	=> $bank,
					"sender_id"	=> $data[$bank]["sender_id"],
					"stamp"		=> $data[$bank]["stamp"],
					"amount"	=> $data["amount"],
					"reference_nr"	=> $reference_nr,
					"expl"		=> $data["expl"],
				));
				if(($template_exists) && ($this->is_template($bank)))
				{
					if($test && $this->test_link[$bank]) $link = $this->test_link[$bank];
					else $link = $this->bank_link[$bank];
					
					$this->vars(array(
						"data" => $bank_form,
						"link" => $link,
					));
					$c .= $this->parse($bank);
					$this->vars(array(
						$bank => $c,
					));
					$c = "";
				}
				$ret.= $bank_form;
				$ret.= '<br><input type="submit" value="maksma"></form>';
			}
		}
		if($template_exists)
		{
			$this->vars(array(
				"data" => $ret,
			));
			return $this->parse();
		}
		if($die) die($ret);
		return $ret;
	}
	
	/**
	@attrib name=check_response is_public="1" caption="Change" no_login=1 api=1
	@returns 1 if the signature is correct, 0 if it is incorrect, and -1 on error
	@comment
		checks if the response from a bank is correct
		reads data from $_SESSION["bank_return"]["data"]
	**/
	function check_response()
	{
		extract($_SESSION["bank_return"]["data"]);
		
		if($action == "afb")//selliselt tulevad krediitkaardimakse tagasipöördumised
		{
			return $this->check_cc_response();
		}
		
		$data = substr("000".strlen($VK_SERVICE),-3).$VK_SERVICE
		.substr("000".strlen($VK_VERSION),-3).$VK_VERSION
		.substr("000".strlen($VK_SND_ID),-3).$VK_SND_ID
		.substr("000".strlen($VK_REC_ID),-3).$VK_REC_ID
		.substr("000".strlen($VK_STAMP),-3).$VK_STAMP
		.substr("000".strlen($VK_T_NO),-3).$VK_T_NO
		.substr("000".strlen($VK_AMOUNT),-3).$VK_AMOUNT
		.substr("000".strlen($VK_CURR),-3).$VK_CURR
		.substr("000".strlen($VK_REC_ACC),-3).$VK_REC_ACC
		.substr("000".strlen($VK_REC_NAME),-3).$VK_REC_NAME
		.substr("000".strlen($VK_SND_ACC),-3).$VK_SND_ACC
		.substr("000".strlen($VK_SND_NAME),-3).$VK_SND_NAME
		.substr("000".strlen($VK_REF),-3).$VK_REF
		.substr("000".strlen($VK_MSG),-3).$VK_MSG
		.substr("000".strlen($VK_T_DATE),-3).$VK_T_DATE;

		$signature = base64_decode($VK_MAC);

		$fp = fopen($this->cfg["site_basedir"]."/pank/".$_SESSION["bank_return"]["data"]["VK_SND_ID"]."_pub.pem", "r");
		$cert = fread($fp, 8192);
		fclose($fp);
		
		$pubkeyid = openssl_get_publickey($cert);
		$ok = openssl_verify($data, $signature, $pubkeyid);
		openssl_free_key($pubkeyid);
		
		return $ok;
		if ($ok == 1)
		{
			echo "good";
		}
		elseif ($ok == 0) 
		{
			echo "bad";
		}
		else {
			echo "ugly, error checking signature";
		}	
	}
	
	function hex2str($hex) {
		for($i=0;$i<strlen($hex);$i+=2) $str.=chr(hexdec(substr($hex,$i,2)));
		return $str;
	}
	
	function check_cc_response()
	{
		extract($_SESSION["bank_return"]["data"]);
		$data = sprintf("%03s", $ver) . sprintf("%-10s", "$id") .
		sprintf("%012s", $ecuno) . sprintf("%06s", $receipt_no) . sprintf("%012s",
		$eamount) . sprintf("%3s", $cur) . $respcode . $datetime . sprintf("%-40s",
		$msgdata) . sprintf("%-40s", $actiontext);

		$mac = $this->hex2str($mac);
		$signature = sha1($data);
		$fp = fopen($this->cfg["site_basedir"]."/pank/80_ecom.crt", "r");
		$cert = fread($fp, 8192);
		fclose($fp);
		$pubkeyid = openssl_get_publickey($cert);
		$ok = openssl_verify($data, $mac, $pubkeyid);
		openssl_free_key($pubkeyid);
		return $ok;
	}
}
?>
