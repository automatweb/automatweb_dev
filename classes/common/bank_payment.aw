<?php
// $Header: /home/cvs/automatweb_dev/classes/common/bank_payment.aw,v 1.3 2006/04/21 11:28:17 markop Exp $
// bank_payment.aw - Bank Payment 
/*

@classinfo syslog_type=ST_BANK_PAYMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class bank_payment extends class_base
{
	var $banks = array (
		"hansapank"	=> "Hansapank",
		"seb"		=> "SEB Eesti &Uuml;hispank",
		"nordeapank"	=> "Nordea Pank",
		"krediidipank"	=> "Krediidipank",
		"sampopank"	=> "Sampo Pank",
	);

	var $for_all_banks = array(
		"amount"	=> "Summa",
		"expl"		=> "Selgitus",
	);

	var $bank_props = array(
		"sender_id"	=> "Kaupmehe ID",
		"stamp"		=> "Arvenumber"
	);

	function bank_payment()
	{
		$this->init(array(
			"tpldir" => "common/bank_payment",
			"clid" => CL_BANK_PAYMENT
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
	
	/**
	@attrib api=1 params=name
	@param bank_id required type=string
		bank id. possible choices: "seb", "hansapank" , "sampopank", "nordeapank" , "krediidipank" 
	@param service optional type=int default=1002
		Number of service. Length=4
	@param version optional type=int default=008
		Encryption algorithm used. Length=3	
	@param sender_id optional type=string
		ID of compiler of query (merchant's ID). Max length=10
	@param stamp optional type=string
		Query ID. Max length=20
	@param amount optional type=int
		Amount to be paid. Max length=17
	@param currency optional type=string default="EEK"
		Name of currency: EEK/DEM/FIM etc. Length=3
	@param reference_nr optional type=int
		Reference number of payment order. Max length=19
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
			"form"		=> 1,
			"test"		=> 1,
			"bank_id"	=> "seb",
			"sender_id"	=> "EXPRPOST",
			"stamp"		=> row["arvenr"],
			"amount"	=> $data["amount"],
			"reference_nr"	=> 123456,
			"expl"		=> "Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
		));
		//returns something like : 
		//	"<form name="postform" id="postform" method="post" action=https://unet.eyp.ee/cgi-bin/dv.sh/un3min.r>
		//	<input type="hidden" name=VK_SERVICE value=1002>
		//	<input type="hidden" name=VK_VERSION value=008>
		//	<input type="hidden" name=VK_SND_ID value=EXPRPOST>
		//	<input type="hidden" name=VK_STAMP value=10002050618003>
		//	<input type="hidden" name=VK_AMOUNT value=10000000>
		//	<input type="hidden" name=VK_CURR value=EEK>
		//	<input type="hidden" name=VK_REF value=1234561>
		//	<input type="hidden" name=VK_MSG value=Ajakirjade tellimus. Arve nr. 10002050618003>
		//	<input type="hidden" name=VK_MAC value=kKltfBDS9lZqN+I54diEpbr/3G5VXrkrm9I2kKxE+6qH26Fb6fTuOTgNl93mUrjo3yeR4m2QweGN5bliI0/o1Jw/hbFYuqLTREcr1s5uYuortqa/T2ChavRpU0kw/QnFZPqlAgntfFfFeDS5EPi5qXN6Yl6eeaMPxn/V1HU+66o=>
		//	<input type="hidden" name=VK_RETURN value=http://vesta.struktuur.ee/automatweb/bank_return.aw>
		//	<input type="hidden" name=VK_CANCEL value=http://vesta.struktuur.ee/automatweb/bank_return.aw>
		//	<input type="hidden" name=VK_LANG value=EST>"
	**/	
	function do_payment($arr)
	{
		switch($arr["bank_id"]) {
			case "seb":
				$arr = $this->check_args($arr);
				return $this->seb($arr);
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
		}
	}
	
	function check_args($arr)
	{
		if(!$arr["service"]) $arr["service"] = "1002";
		if(!$arr["version"]) $arr["version"] = "008";
		if(!$arr["curr"]) $arr["curr"] = "EEK";
		if(!$arr["lang"]) $arr["lang"] = "EST";
		if(!$arr["cancel_url"]) $arr["cancel_url"] = aw_ini_get("baseurl")."/automatweb/bank_return.aw";
		if(!$arr["priv_key"])
		{
			$fp = fopen( $this->cfg["site_basedir"]."/pank/vesta.key.key", "r");
			$arr["priv_key"] = fread($fp, 8192);
			fclose($fp);
		}
		$arr["reference_nr"].= (string)$this->viitenr_kontroll_731($arr["reference_nr"]);
		if(!$arr["return_url"]) $arr["return_url"] = aw_ini_get("baseurl")."/automatweb/bank_return.aw";
		return($arr);
	}
		
	//if form = 1, returns hrml form tag.
	function submit($args)
	{
		extract($args);
		$return = "";
		$return.= '<form name="postform" id="postform" method="post" action='.$link.'>
		';
		foreach($params as $key => $val)
		{
			$return.= '<input type="hidden" name='.$key.' value='.(string)$val.'>
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
			"VK_CURR"	=> $currency,	//"EEK"
			"VK_REF"	=> $reference_nr,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";	//	60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";	//	60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		
		return $this->submit(array("params" => $params , "link" => $link , "form" => $form));
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
		$link = "https://unet.eyp.ee/cgi-bin/unet3.sh/un3min.r";
		if($test) $link = "https://unet.eyp.ee/cgi-bin/dv.sh/un3min.r";
		$handler = "https://unet.eyp.ee/cgi-bin/unet3.sh/un3min.r";
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
		return $this->submit(array("params" => $params , "link" => $link , "form" => $form));
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
		return $this->submit(array("params" => $params , "link" => $link , "form" => $form));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}


	function check_nordea_args($arr)
	{
		if(!$arr["service"]) $arr["service"] = "0002";
		if(!$arr["version"]) $arr["version"] = "0001";
		if(!$arr["cunnency"]) $arr["cunnency"] = "EEK";
		if(!$arr["confirm"]) $arr["confirm"] = "NO";
		if(!$arr["recieve_account"]) $arr["recieve_account"] = "";
		if(!$arr["recieve_name"]) $arr["recieve_name"] = "";
		if(!$arr["recieve_id"]) $arr["recieve_id"] = "10354213";
		if(!$arr["date"]) $arr["date"] = 'EXPRESS';
		if(!$arr["lang"]) $arr["lang"] = "4";
		if(!$arr["priv_key"]) $arr["priv_key"] = "g94z7e7KgP6PM8av7kIF7bwX8YNZ7eFX";//suht halb mõte muidugi ... aga see on siin ajutiselt
		return($arr);
	}

	function nordea($args)
	{
		extract($args);
		$SOLOPMT_MAC      = '';
		$VK_message       = $SOLOPMT_VERSION.'&';
		$VK_message       .= $stamp.'&';
		$VK_message       .= $SOLOPMT_RCV_ID.'&';
		$VK_message       .= $amount.'&';
		$VK_message       .= $reference_nr.'&';
		$VK_message       .= $SOLOPMT_DATE.'&';
		$VK_message       .= $currency.'&';
		$VK_message       .= ''.'&';
		$SOLOPMT_MAC      = strtoupper(md5( $VK_message ));
		
		$http = get_instance("protocols/file/http");
// 		$link = "https://solo3.merita.fi/cgi-bin/SOLOPM01";
// 		$handler = "https://solo3.merita.fi/cgi-bin/SOLOPM01";
		$params = array(
			"SOLOPMT_VERSION"     => $service,// 1.    Payment Version   SOLOPMT_VERSION   "0002"   AN 4  M
			"SOLOPMT_STAMP"       => $stamp,// 2.    Payment Specifier    SOLOPMT_STAMP  Code specifying the payment   N 20  M 
			"SOLOPMT_RCV_ID"      => $recieve_id, // 3.    Service Provider ID  SOLOPMT_RCV_ID    Customer ID (in Nordea's register)  AN 15    M 
			"SOLOPMT_RCV_ACCOUNT" => $recieve_account,// 4.    Service Provider's Account    SOLOPMT_RCV_ACCOUNT  Other than the default account   AN 15    O
			"SOLOPMT_RCV_NAME"    => $recieve_name,//5.    Service Provider's Name    SOLOPMT-RCV_NAME  Other than the default name   AN 30    O 
			"SOLOPMT_LANGUAGE"    => $lang,// 6.    Payment Language  SOLOPMT_LANGUAGE  1 = Finnish 2 = Swedish 3 = English    N 1   O 
			"SOLOPMT_AMOUNT"      => $amount,// 7.    Payment Amount    SOLOPMT_AMOUNT    E.g. 990.00    AN 19    M 
			"SOLOPMT_REF"         => $reference_nr,// 8.    Payment Reference Number   SOLOPMT_REF    Standard reference number  AN 20    M 
			"SOLOPMT_DATE"        => $date,// 9.    Payment Due Date  SOLOPMT_DATE   "EXPRESS" or "DD.MM.YYYY"  AN 10    M 
			"SOLOPMT_MSG"         => $expl,// 10.   Payment Message   SOLOPMT_MSG    Service user's message  AN 234   O 
			"SOLOPMT_RETURN"      => $return_url,// 11.   Return Address    SOLOPMT_RETURN    Return address following payment    AN 60    M 
			"SOLOPMT_CANCEL"      => $cancel_url,// 12.   Cancel Address    SOLOPMT_CANCEL    Return address if payment is cancelled    AN 60    M 
			"SOLOPMT_REJECT"      => $reject_url,// 13.   Reject Address    SOLOPMT_REJECT    Return address for rejected payment    AN 60    M 
							// 14.   Solo Button OR Solo Symbol    SOLOPMT_ BUTTON SOLOPMT_IMAGE    Constant    Constant    O       // $SOLOPMT_ BUTTON SOLOPMT_IMAGE   Constant    Constant    O 			
			"SOLOPMT_MAC"         => $SOLOPMT_MAC,  // 15.   Payment MAC    SOLOPMT_MAC    MAC   AN 32    O 
			"SOLOPMT_CONFIRM"     => $confirm,// 16.   Payment Confirmation    SOLOPMT_CONFIRM   YES or NO   A 3   O 
			"SOLOPMT_KEYVERS"     => $version,// 17.   Key Version    SOLOPMT_KEYVERS   E.g. 0001   N 4   O 
			"SOLOPMT_CUR"         => $curr,// 18.   Currency Code  SOLOPMT_CUR    EUR   A 3   O 
		);
		return $this->submit(array("params" => $params , "link" => $link , "form" => $form));
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
			"VK_AMOUNT"	=> $ammount,	//$row["summa"];
			"VK_CURR"	=> $curr,	//"EEK"
			"VK_REF"	=> $reference_nr,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";	//	60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";	//	60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		return $this->submit(array("params" => $params , "link" => $link , "form" => $form));
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
	@attrib name=pay_site is_public="1" caption="Change" no_login=1 api=1
	
	@returns string/html
	@comment
		makes a list of supported banks with correct forms
		before calling this function you should fill $_SESSION["bank_payment"]
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
	
		//returned url forwards to site contains something like:"
		//	<img src="http://vesta.struktuur.ee/automatweb/images/pank/seb_pay.gif"><form name="postform" id="postform" method="post" action=https://unet.eyp.ee/cgi-bin/dv.sh/un3min.r>
		//	<input type="hidden" name=VK_SERVICE value=1002>
		//	<input type="hidden" name=VK_VERSION value=008>
		//	<input type="hidden" name=VK_SND_ID value=testvpos>
		//	<input type="hidden" name=VK_STAMP value=10002050618003>
		//	<input type="hidden" name=VK_AMOUNT value=616>
		//	<input type="hidden" name=VK_CURR value=EEK>
		//	<input type="hidden" name=VK_REF value=176332>
		//	<input type="hidden" name=VK_MSG value=616>
		//	<input type="hidden" name=VK_MAC value=Yz4FzXyX8ek76Tb68ejOF4rZH9BFRl1GmW4IxTGgq7bQaFz4wkYzX7JVcEI9We/gvxMdwBaB811Ltvd7Iu9ubDZpTFpXFuwoGH+fJNzidgHBGRZXtF+kPS3xV2SqmpwEaquogs5vbCTh1b+SM4omCB11WA9olDzv3tc09uUZOPc=>
		//	<input type="hidden" name=VK_RETURN value=http://vesta.struktuur.ee/automatweb/bank_return.aw>
		//	<input type="hidden" name=VK_CANCEL value=http://vesta.struktuur.ee/automatweb/bank_return.aw>
		//	<input type="hidden" name=VK_LANG value=EST>
		//	<br><input type="submit" value="maksma"></form>
	**/
	function pay_site($args)
	{
		if(!$_SESSION["bank_payment"]) return false;
		extract($_SESSION["bank_payment"]);
		$tpl = "bank_pay_site.tpl";
		if($this->read_template($tpl , $silent = 1))
		{
			$template_exists = 1;
		}
		$ret = "";
		foreach($this->banks as $bank => $name)
		{
			if(array_key_exists($bank , $data) 
			&& $data[$bank]["sender_id"]
			&& $data[$bank]["stamp"])
			{
				$ret.='<img src="'.aw_ini_get("baseurl").'/automatweb/images/pank/'.$bank.'_pay.gif">';
				$ret.= $this->do_payment(array(
					"form"		=> 1,
					"test"		=> 1,
					"bank_id"	=> $bank,
					"sender_id"	=> $data[$bank]["sender_id"],
					"stamp"		=> $data[$bank]["stamp"],
					"amount"	=> $data["amount"],
					"reference_nr"	=> $reference_nr,
					"expl"		=> $data["expl"],
				));
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
		return $ret;
	}
}
?>
