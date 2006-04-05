<?php
// $Header: /home/cvs/automatweb_dev/classes/common/bank_payment.aw,v 1.1 2006/04/05 15:31:18 markop Exp $
// bank_payment.aw - Bank Payment 
/*

@classinfo syslog_type=ST_BANK_PAYMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class bank_payment extends class_base
{
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
	
	function do_payment($arr)
	{
		switch($arr["bank_id"]) {
			case "seb":
				$arr = $this->check_args($arr);
				$this->seb($arr);
				break;
			case "hansapank":
				$arr = $this->check_args($arr);
				$this->hansa($arr);
				break;
			case "sampopank":
				$arr = $this->check_args($arr);		
				$this->sampo($arr);
				break;
			case "nordeapank":
				$arr = $this->check_nordea_args($arr);		
				$this->nordea($arr);
				break;
			case "krediidipank":
				$arr = $this->check_args($arr);		
				$this->krediidi($arr);
				break;
		}
	}
	
	function check_args($arr)
	{
		if(!$arr["service"]) $arr["service"] = "1002";
		if(!$arr["version"]) $arr["version"] = "008";
		if(!$arr["cunnency"]) $arr["cunnency"] = "EEK";
		if(!$arr["lang"]) $arr["lang"] = "EST";
		if(!$arr["priv_key"])
		{
			$fp = fopen( $this->cfg["site_basedir"]."/pank/vesta.key.key", "r");
			$arr["priv_key"] = fread($fp, 2048);
			fclose($fp);
		}
		$arr["viitenumber"].= $this->viitenr_kontroll_731($arr["viitenumber"]);
		return($arr);
	}
		
	function submit($args)
	{
		extract($args);
		print '<table border="0" cellpadding="0" cellspacing="1" width="690" class="table">
		<tr><td class="tablecell1" bgcolor="#efefef">
		<form name="postform" id="postform" method="post" action='.$link.'>';
		foreach($params as $key => $val)
		{
			print '<input type="hidden" name='.$key.' value='.$val.'>';
		};
		print '<p class="text">Kui suunamist mingil p&otilde;hjusel ei toimu, palun vajutage <a href="#" onClick="document.postform.submit();">siia</a></p>
		</form></td></tr></table>
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
	
	function hansa($args) {
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$currency;
		$VK_message.= sprintf("%03d",strlen($viitenumber)).$viitenumber;
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
			"VK_REF"	=> $viitenumber,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";	//	60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";	//	60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		
		$this->submit(array("params" => $params , "link" => $link));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}

	function seb($args) {
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$currency;
		$VK_message.= sprintf("%03d",strlen($viitenumber)).$viitenumber;
		$VK_message.= sprintf("%03d",strlen($expl)).$expl;
		$VK_signature = "";
		$pkeyid = openssl_get_privatekey($priv_key);
		openssl_sign($VK_message, $VK_signature, $pkeyid);
		openssl_free_key($pkeyid);
		$VK_MAC = base64_encode( $VK_signature);

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
			"VK_CURR"	=> $currency,	//"EEK"
			"VK_REF"	=> $viitenumber,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";	//	60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";	//	60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		$this->submit(array("params" => $params , "link" => $link));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}

	function sampo($args) {
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$currency;
		$VK_message.= sprintf("%03d",strlen($viitenumber)).$viitenumber;
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
			"VK_CURR"	=> $currency,	//"EEK"
			"VK_REF"	=> $viitenumber,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";	//	60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";	//	60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		$this->submit(array("params" => $params , "link" => $link));
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

	function nordea($args) {
		extract($args);
		$SOLOPMT_MAC      = '';
		$VK_message       = $SOLOPMT_VERSION.'&';
		$VK_message       .= $stamp.'&';
		$VK_message       .= $SOLOPMT_RCV_ID.'&';
		$VK_message       .= $amount.'&';
		$VK_message       .= $viitenumber.'&';
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
			"SOLOPMT_REF"         => $viitenumber,// 8.    Payment Reference Number   SOLOPMT_REF    Standard reference number  AN 20    M 
			"SOLOPMT_DATE"        => $date,// 9.    Payment Due Date  SOLOPMT_DATE   "EXPRESS" or "DD.MM.YYYY"  AN 10    M 
			"SOLOPMT_MSG"         => $expl,// 10.   Payment Message   SOLOPMT_MSG    Service user's message  AN 234   O 
			"SOLOPMT_RETURN"      => $return_url,// 11.   Return Address    SOLOPMT_RETURN    Return address following payment    AN 60    M 
			"SOLOPMT_CANCEL"      => $cancel_url,// 12.   Cancel Address    SOLOPMT_CANCEL    Return address if payment is cancelled    AN 60    M 
			"SOLOPMT_REJECT"      => $reject_url,// 13.   Reject Address    SOLOPMT_REJECT    Return address for rejected payment    AN 60    M 
							// 14.   Solo Button OR Solo Symbol    SOLOPMT_ BUTTON SOLOPMT_IMAGE    Constant    Constant    O       // $SOLOPMT_ BUTTON SOLOPMT_IMAGE   Constant    Constant    O 			
			"SOLOPMT_MAC"         => $SOLOPMT_MAC,  // 15.   Payment MAC    SOLOPMT_MAC    MAC   AN 32    O 
			"SOLOPMT_CONFIRM"     => $confirm,// 16.   Payment Confirmation    SOLOPMT_CONFIRM   YES or NO   A 3   O 
			"SOLOPMT_KEYVERS"     => $version,// 17.   Key Version    SOLOPMT_KEYVERS   E.g. 0001   N 4   O 
			"SOLOPMT_CUR"         => $currency,// 18.   Currency Code  SOLOPMT_CUR    EUR   A 3   O 
		);
		$this->submit(array("params" => $params , "link" => $link));
	//	return $http->post_request($link, $handler, $params, $port = 80);	
	}

	function krediidi($args) {
		extract($args);
		$VK_message = sprintf("%03d",strlen($service)).$service;
		$VK_message.= sprintf("%03d",strlen($version)).$version;
		$VK_message.= sprintf("%03d",strlen($sender_id)).$sender_id;
		$VK_message.= sprintf("%03d",strlen($stamp)).$stamp;
		$VK_message.= sprintf("%03d",strlen($amount)).$amount;
		$VK_message.= sprintf("%03d",strlen($curr)).$currency;
		$VK_message.= sprintf("%03d",strlen($viitenumber)).$viitenumber;
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
			"VK_CURR"	=> $currency,	//"EEK"
			"VK_REF"	=> $viitenumber,
			"VK_MSG"	=> $expl,	//"Ajakirjade tellimus. Arve nr. ".$row["arvenr"];
			"VK_MAC" 	=> $VK_MAC,
			"VK_RETURN"	=> $return_url, //$this->burl."/tellimine/makse/tanud/";	//	60	URL, kuhu vastatakse edukal tehingu sooritamisel
			"VK_CANCEL"	=> $cancel_url,	//this->burl."/tellimine/makse/";	//	60	URL, kuhu vastatakse ebaõnnestunud tehingu puhul
			"VK_LANG" 	=> $lang,	//"EST"
		);
		$this->submit(array("params" => $params , "link" => $link));
	//	return $http->post_request($link, $handler, $params, $port = 80);
	}	

	function viitenr_kontroll_731($nr) {
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
}
?>
