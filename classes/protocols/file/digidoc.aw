<?php
// $Header: /home/cvs/automatweb_dev/classes/protocols/file/digidoc.aw,v 1.2 2006/11/16 12:27:12 tarvo Exp $
// digidoc.aw - DigiDoc 
/*

@classinfo syslog_type=ST_DIGIDOC relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class digidoc extends class_base
{

	/**
	 * Soap kliendi ühenduse objekt
	 */
	var $Client;

	/**
	 * WSDL faili põhjal genereeritud liides
	 */
	var $WSDL;

	/**
	 * Brauseri ja OS-i andmed
	 */
	var $browser;


	function digidoc()
	{
		$this->init(array(
			"tpldir" => "protocols/file/digidoc",
			"clid" => CL_DIGIDOC
		));
		
		// digidoc crap
		session_start(); // <-- dont think i need this here in aw ?
		$connection = $this->getconnect();
		$this->client = new soap_client ( dd_wsdl, true, false, $connection);
		
		$this->wsdl = new webservice_digidocservice_digidocservice();
				
		$this->browser = file::getbrowser();
		
		$this->ns = $this->client->_wsdl->definition['targetnamespace'];
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

	/*
	 * funktsioon class WebService_DigiDocService_DigiDocService definitsiooni
	 * laadimiseks _enne_ sessiooni alustamist et oleks võimalik Base_DigiDoc
	 * sessiooni salvestada
	 */
	function load_WSDL()
	{
			if(is_readable( DD_WSDL_FILE ) && filesize( DD_WSDL_FILE ) > 32){
				include_once DD_WSDL_FILE;
			} else {
				$wsdl = new SOAP_WSDL( DD_WSDL, $connection );
				$wcode = $wsdl->generateProxyCode();
				eval( $wcode );
				File::saveLocalFile( DD_WSDL_FILE, "<?php\n".$wcode."\n?".">");	
			} 
	}

	/**
	 * Lisab vastava parameetri ja väärtuse SOAP headerisse
	 *
	 * Parameetri lisamiseks SOAP serverile saadetavatesse XML päringuisse.
	 * Antud juhul enamasti sessiooni koodi lisamiseks, et tuvastada õige
	 * digidoc failiga tegelemist.
	 *
	 * <code>
	 * $dd->addHeader('SessionCode', '01223121');
	 * </code>
	 * <code>
	 * $x = array('SessionCode' => '123423423234', 'testVar'=>'muutuja');
	 * $dd->addHeader($x);
	 * </code>
	 * 
	 * @param     mixed    $var     Päisesse lisatavad parameetrid
	 * @param     mixed    $value   ühe muutuja lisamisel, selle väärtus
	 * @access    public
	 * @return    array
	 */
	function addHeader($var, $value=null){
		if(is_array($var)){
			while(list($key, $val) = each($var)){
				$hr = new SOAP_Header($key, NULL, $val, FALSE, FALSE);
				$hr->namespace = $this->NS;
				if(isset($hr->attributes['SOAP-ENV:actor'])) unset($hr->attributes['SOAP-ENV:actor']);
				if(isset($hr->attributes['SOAP-ENV:mustUnderstand'])) unset($hr->attributes['SOAP-ENV:mustUnderstand']);
				$this->WSDL->addHeader($hr);
			} //while
			return TRUE;
		} elseif($var && $value) {
			$hr = new SOAP_Header($var, NULL, $value, FALSE, FALSE);
			$hr->namespace = $this->NS;
			if(isset($hr->attributes['SOAP-ENV:actor'])) unset($hr->attributes['SOAP-ENV:actor']);
			if(isset($hr->attributes['SOAP-ENV:mustUnderstand'])) unset($hr->attributes['SOAP-ENV:mustUnderstand']);
			$this->WSDL->addHeader($hr);
		} else {
			return FALSE;
		} //else
	}

	
	/**
	 * Tagastab vastuvõetud DigiDoci formaadi ja versiooni
	 * @return	array
	 */
	function getDigiDocArray(){
		$us = new XML_Unserializer();
		$us->unserialize($this->WSDL->xml, FALSE);
		$xml = $us->getUnserializedData();
		return $xml;
	} //function

	/**
	 * Puhastab saadetud kuupäeva ülearustest sümbolitest
	 * @access	private
	 */
	function cleanDateString($date){
		return preg_replace("'[TZ]'"," ",$date);
	} //function

	
	/**
	 * Sertifikaadi salvestamine
	 */
	function saveCertAs($file){
		$filename = uniqid('certificate').'.cer';
		$content = "-----BEGIN CERTIFICATE-----\n".$file."\n-----END CERTIFICATE-----\n";
		File::SaveAs($filename, $content, 'application/certificate', 'utf-8');
	} //function


	/**
	 * Kehtivuskinnituse salvestamine
	 */
	function saveNotaryAs($file){
		$filename = uniqid('ocsp').'.ocsp';
		$content = base64_decode($file);
		File::SaveAs($filename, $content, 'application/notary-ocsp', 'utf-8');
	} //function

	
	/**
	 * Tagastab ddociga kaasas olnud andmefailid array-na
	 */
	function getDataFiles($result){
		$res = array();
		return $res;
	} //function


	
	/**
	 * Tagastab ddociga kaasas olnud allkirjad array-na
	 */
	function getSignatures($result){
		$res = array();
		return $res;
	} //function

	
	/**
	 * Tagastame brauseri ja OS/i info stringina
	 */
	function getBrowserStr(){
		$browser = $this->browser;
		$os = $browser['OS']=='Win'?'WIN32':'LINUX';
		$br = $browser['BROWSER_AGENT'] == 'IE' ? 'IE' : 'MOZILLA';
		return $os.'-'.$br;
	} //function
	
	/**
	 * ühenduse/proksi parameetrite vektor
	 *
	 * Detail description
	 * @access    public
	 * @return    array
	 */
	function getConnect(){
		$ret=array();
		if(defined('DD_PROXY_HOST') && DD_PROXY_HOST) $ret['proxy_host'] = DD_PROXY_HOST;
		if(defined('DD_PROXY_PORT') && DD_PROXY_PORT) $ret['proxy_port'] = DD_PROXY_PORT;
		if(defined('DD_PROXY_USER') && DD_PROXY_USER) $ret['proxy_user'] = DD_PROXY_USER;
		if(defined('DD_PROXY_PASS') && DD_PROXY_PASS) $ret['proxy_pass'] = DD_PROXY_PASS;
		if(defined('DD_TIMEOUT') && DD_TIMEOUT) $ret['timeout'] = DD_TIMEOUT;
		return $ret;
	} // end func
}
?>
