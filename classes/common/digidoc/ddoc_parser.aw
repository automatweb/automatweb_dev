<?php
// $Header: /home/cvs/automatweb_dev/classes/common/digidoc/ddoc_parser.aw,v 1.2 2006/11/16 12:25:47 tarvo Exp $
// ddoc_parser.aw - DigiDoc Parser 
/*

@classinfo syslog_type=ST_DDOC_PARSER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class ddoc_parser extends class_base
{
	/**
	 * DigiDoc XML faili hoidja
	 * @var       string
	 * @access    private
	 */
	var $xml;

	/**
	 * Parsitava faili formaat
	 * Description
	 * @var       array
	 * @access    private
	 */
	var $format;


	/**
	 * Parsitava faili versioon
	 * Description
	 * @var       array
	 * @access    private
	 */
	var $version;


	/**
	 * Description
	 * @var       array
	 * @access    private
	 */
	var $xmlarray;
	
	/**
	 * Kõik XML failist leitud datafailide tagid.
	 * @var       array
	 * @access    private
	 */
	var $dataFilesXML;
	
	/**
	 * Töökaust failide hoidmiseks
	 * @var       string
	 * @access    private
	 */
	var $_workPath;
	


	function ddoc_parser()
	{
		$this->init(array(
			"tpldir" => "common/digidoc/ddoc_parser",
			"clid" => CL_DDOC_PARSER
		));
		
		// ddoc crap
		//session_start(); //i dont think i need this one in here

		$this->xml = $xml;
		$this->xmlarray = $xml?$this->Parse($this->xml):false;
		$this->setDigiDocFormatAndVersion();
		$this->workPath = DD_FILES;//.session_id().'/';
		if (!is_dir($this->workPath))
			if(File::DirMake($this->workPath) != DIR_ERR_OK)
				die('Error accessing workpath:'.$this->workPath);
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
	
	/**
	 * Teisendab XML-i array kujule
	 *
	 * @param     string     $xml
	 * @param     string     $XMLPart  Parsida kas 'body' või 'header' või ''
	 * @access    public
	 * @return    array
	 */
	function Parse($xml, $XMLPart=''){
		
		$us = new XML_Unserializer();
		$us->unserialize($xml, FALSE);

		$xml2 = $us->getUnserializedData();


		$body = $xml2['SOAP-ENV:Body'];

		$body = @current($body);

		if (isset($body['SignedDocInfo']['format']))
			$this->format = $body['SignedDocInfo']['format'];

		if (isset($body['SignedDocInfo']['version']))
			$this->version = $body['SignedDocInfo']['version'];

		switch(strtolower($XMLPart)){
			case 'body':
				$xml2 = $body;
				break;
			case 'header':
				$xml2 = $xml2['SOAP-ENV:Header'];
				#$xml = current($xml);
				break;
		} //switch
		
		return $xml2;
	
	} // end func

	/**
	 * tagastab ddoc-is olevad andmefailid.
	 *
	 * Tagastab kõik digidoc failis olevad andmefailid arrayna.
	 * @param     string     $xml
	 * @access    public
	 * @return    array
	 */
	function getFilesInfo($xml){
		$fs = $this->_getFilesXML($xml);

		$us = new XML_Unserializer();

		$ret = array();
		foreach($fs as $key=>$val){
			$us->unserialize($val, FALSE);
			$ret[] = $us->getUnserializedData();
		} //foreach
		return $ret;
	} // end func


	/**
	 * Määrab digidoc-i failiformaadi ja versiooni XML põhjal.
	 *
	 * @param     string     $xml
	 * @access    public
	 * @return    array
	 */
	function setDigiDocFormatAndVersion($xml='') {
		if ($xml=='')
			$xml=$this->xml;
		if ($xml) {
			preg_match("'(\<SignedDoc.*\/SignedDoc\>)'Us", $xml, $match); 
			$content = $match[1];
			preg_match("'format=\"(.*)\"'Us", $content, $match);	$this->format = $match[1];
			preg_match("'version=\"(.*)\"'Us", $content, $match);	$this->version = $match[1];
		} else {
			$this->format = "";
			$this->version = "";
		}
	}
	

	/**
	 * Tagastab digidoc-s sisalduvad allkirjad
	 *
	 * Tagastab digidoc-s olevad allkirjad arrayna.
	 * @param     string     $xml
	 * @access    public
	 * @return    array
	 */
	function getSignaturesInfo( $xml ){
		$fs = $this->_getSignsXML( $xml );
		$us = new XML_Unserializer();
		$ret = array();
		foreach($fs as $key=>$val){
			$us->unserialize($val, FALSE);
			$ret[] = $us->getUnserializedData();
		} //foreach
		return $ret;
	} // end func

	
	
	/**
	 * Short description.
	 *
	 * Detail description
	 * @param     boolean    $withLocalFiles
	 * @access    public
	 * @return    string
	 */
	function getDigiDoc( $withLocalFiles = FALSE ){

		$files = $this->_getFilesXML($this->xml);
		$nXML = $this->xml;
		$func = $withLocalFiles ? 'file2hash' : 'hash2file';
	
		while(list(,$file) = each($files)){
			$nXML = str_replace($file, $this->$func($file), $nXML);
		} //while
		#echo '<hr><pre>'.htmlentities($nXML).'</pre><hr>';
		return $nXML;
	} // end func

	
	/**
	 * Teisendab Datafaile tagi filega kujult hash-koodiga kujule.
	 *
	 * Teisendab DigiDoc failist saadud DataFile tagides oleva faili
	 * hash/koodi sisaldavale kujule ja salvestades saadud faili kohalikule
	 * kettale määratud kausta.
	 * @param     string     $xml
	 * @access    private
	 * @return    string
	 */
	function file2hash($xml){
		if(preg_match("'ContentType\=\"HASHCODE\"'s",$xml)){ // Meil on hashcode kuju
			preg_match("'Id=\"(.*)\"'Us", $xml, $match);
			$Id = $match[1];
			preg_match("'DigestValue=\"(.*)\"'Us", $xml, $match);
			$oldHash=$match[1];
			$tempfiledata=file_get_contents($this->workPath.$_SESSION['doc_id'].'_'.$Id);
			$newHash=base64_encode(pack("H*", sha1(str_replace("\r\n","\n",$tempfiledata) ) ) );
			$xml=str_replace($oldHash, $newHash, $xml);
			return $xml;
		} else {
			preg_match("'Id=\"(.*)\"'Us", $xml, $match);	$Id = $match[1]; // Saame teada faili identifikaatori
			File::SaveLocalFile( $this->workPath.$_SESSION['doc_id'].'_'.$Id, $xml); // salvestame algfaili
			$hash = base64_encode(pack("H*", sha1(str_replace("\r\n","\n",$xml) ) ) ); // Arvutame andmefaili bloki räsi

			$hashonlyxml = preg_replace('/>((.|\n|\r)*)<\//', ' DigestValue="'.$hash.'"></', $xml); // Moodustame serverisse saadetava andmefaili bloki eemaldades andmefaili sisu
			$hashonlyxml = str_replace('ContentType="EMBEDDED_BASE64"', 'ContentType="HASHCODE"', $hashonlyxml);

			$hashonlyxml=$xml; // Urmo ajutiselt niikauaks kui teenus verifitseerimisel DigestValue väärtust korralikult ei kontrolli
			return $hashonlyxml;
		} //else
	} // end func

	
	/**
	 * Asendab Datafile tagides hash-koodid vastavate failidega
	 *
	 * Asendab antud XML-s hash-koodiga XML-i faili sisaldavaks XML tagiks
	 * @param     string     $xml
	 * @access    private
	 * @return    string
	 */
	function hash2file($xml){
		if( preg_match("'ContentType\=\"HASHCODE\"'s", $xml) ){
			 preg_match("'Id=\"(.*)\"'Us", $xml, $match);		$Id = $match[1];
			 $nXML = File::readLocalFile($this->workPath.$_SESSION['doc_id'].'_'.$Id);			 
			return $nXML;
		} else {
			return $xml;
		} //else
	} // end func
	
	
	
	/**
	 * Tagastab faili kohta HASH koodi.
	 *
	 * Genereerib failile vajaliku XML tagi ja leiab selle HASH-koodi. 
	 * Saadud faili XML salvestatakse vastavasse sessioonikausta.
	 * @param     array      $file          üleslaetud faili array
	 * @param     string     $Id            Faili ID DigiDoc-s
	 * @access    public
	 * @return    array
	 */
	function getFileHash($file, $Id='D0'){
		$xml = sprintf($this->getXMLtemplate('file'), $file['name'], $Id, $file['MIME'], $file['size'], chunk_split(base64_encode($file['content']), 64, "\n") );
		$sh = base64_encode(pack("H*", sha1( str_replace("\r\n","\n",$xml))));
		File::SaveLocalFile($this->workPath.$_SESSION['doc_id'].'_'.$Id, $xml);
		//File::SaveLocalFile($this->workPath.$_SESSION['doc_id'].'_'."test1.xml", $xml);
		$ret['Filename'] = $file['name'];
		$ret['MimeType'] = $file['MIME'];
		$ret['ContentType'] = 'HASHCODE';
		$ret['Size'] = $file['size'];
		$ret['DigestType'] = 'sha1';
		$ret['DigestValue'] = $sh;
		return $ret;
	} // end func
	
	/**
	 * Tagastab kõik andmefaili konteinerid antud XML failist.
	 *
	 * @param     string      $xml          Parsitav XML
	 * @access    private
	 * @return    array
	 */
	function _getFilesXML($xml){

		$x = array();
		$a = $b = -1;

		while(($a=strpos(&$xml, '<DataFile', $a+1))!==FALSE && ($b=strpos(&$xml, '/DataFile>', $b+1))!==FALSE){
			$x[] = preg_replace("'/DataFile>.*$'s", "/DataFile>", substr($xml, $a, $b));
		} //while

		if(!count($x)){
			$a = $b = -1;
			while(($a=strpos(&$xml, '<DataFileInfo', $a+1))!==FALSE && ($b=strpos(&$xml, '/DataFileInfo>', $b+1))!==FALSE){
				$x[] = preg_replace("'/DataFileInfo>.*$'s", "/DataFileInfo>", substr($xml, $a, $b));
			} //while
		}
		return $x;
	} // end func


	/**
	 * Tagastab kõik signatuuride konteinerid antud XML failist.
	 *
	 * @param     string      $xml          Parsitav XML
	 * @access    private
	 * @return    array
	 */
	function _getSignsXML($xml){
		if( preg_match_all("'(\<Signature.*\/Signature\>)'Us", $xml, $ret) ){
			return $ret[1];
		} elseif( preg_match_all("'(\<SignatureInfo.*\/SignatureInfo\>)'Us", $xml, $ret) ) {
			return $ret[1];
		} else {
			return array();
		} //else
	} // end func


	/**
	 * XML templiidid erinevatele päringutele
	 *
	 * @param     string     $type          Päritava XML-templiidi tüüp
	 * @access    private
	 * @return    string
	 */
	function getXMLtemplate($type){
		
		switch($type){
		case 'file':
				#File::VarDump('VER:'.$_SESSION['ddoc_version']);
			return '<DataFile'.($this->version == '1.3'?' xmlns="http://www.sk.ee/DigiDoc/v1.3.0#"':'').' ContentType="EMBEDDED_BASE64" Filename="%s" Id="%s" MimeType="%s" Size="%s"'.($this->format == 'SK-XML'?' DigestType="sha1" DigestValue="%s"':'').'>%s</DataFile>';
	    		break;
	    	case 'filesha1':
				#File::VarDump($_SESSION['ddoc_version']);
	    		return '<DataFile'.($this->version=='1.3'?' xmlns="http://www.sk.ee/DigiDoc/v1.3.0#"':'').' ContentType="HASHCODE" Filename="%s" Id="%s" MimeType="%s" Size="%s" DigestType="sha1" DigestValue="%s"></DataFile>';
	    		break;
	    	default:
	    		
	    } //switch
	} // end func

}
?>
