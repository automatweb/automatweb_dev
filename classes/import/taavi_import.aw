<?php
// $Header: /home/cvs/automatweb_dev/classes/import/taavi_import.aw,v 1.1 2005/12/29 08:52:36 markop Exp $
// taavi_import.aw - Taavi import 
/*

@classinfo syslog_type=ST_TAAVI_IMPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class taavi_import extends class_base
{
	function taavi_import()
	{
		$this->init(array(
			"tpldir" => "import/taavi_import",
			"clid" => CL_TAAVI_IMPORT
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
		@attrib name=import params=name default="0" nologin="1"
		@param id optional
		@returns
		@comment

	**/
	function import($arr)
	{
		include("xmlrpc_lib.aw");
		$client = new IXR_Client("ekstra.masendav.com", "/xmlrpc/index.php", 80);
		$client->query("server.getinfo");
		$data = $client->getResponse();
//		arr($data);
		$this->export_xml($data);
	}


	function export_xml($arr)
	{
		$vars = array("eesnimi","perekonnanimi","synniaeg","aadress","ia_tanav","ia_maja","ia_korter","ia_talu","ia_pindeks","ia_linn","ia_asula","ia_vald","ia_maakond","ia_riik","haridustase","eriala","oppeasutus","telefon","mobiiltelefon","lyhinumber","e_post","ametikoht_nimetus","ametijuhend_viit","ruum","palgaaste","asutus","allasutus","yksus_nimetus","yksus_id","prioriteet","on_peatumine","peatumine_pohjus","toole_tulek_kp","on_asendaja","asendamine_tookoht");
		$struct["tootajad"]["ekspordi_aeg"]="asd";
		arr($arr[0]);
		foreach($arr as $skey => $val)	
		{
			$struct["tootajad"]["tootaja"]["tootaja_id"]=$skey;
			foreach($vars as $tag)	
			{
//				if ((in_array(strtolower($tag), $vars))||(in_array($tag, $vars))) 
//				{				
				switch($tag)
				{
					case "eesnimi":$struct["tootajad"]["tootaja"][$tag]=$val["EESNIMI"];break;
					case "perekonnanimi":$struct["tootajad"]["tootaja"][$tag]=$val["PERENIMI"];break;
//					case "synniaeg":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
					case "aadress":$struct["tootajad"]["tootaja"][$tag]=$val["AADRESS1"];break;
//					case "ia_tanav":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "ia_maja":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "ia_korter":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "ia_talu":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
					//case "ia_pindeks":$struct["tootajad"]["tootaja"][$tag]=
					/*
				if(strpos($val["AADRESS1"], " ") !== 5)
				{
					$prop = explode(" ", strpos($val["AADRESS1"]);
				}
				if(strpos($val["AADRESS1"], ",") !== 5)
				{	
					$prop = explode(" ", strpos($val["AADRESS1"]);
				}
				
				
				
				{
					$prop = explode(" ", strpos($val["AADRESS1"]);
					$prop = explode(":", $obj_data);
					switch($prop[0])
					
					$val["INDEKS1"];break;
					*/
					case "ia_linn":$struct["tootajad"]["tootaja"][$tag]=$val["INDEKS1"];break;
//					case "ia_asula":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "ia_vald":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "ia_maakond":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "ia_riik":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
					case "haridustase":$struct["tootajad"]["tootaja"][$tag]=$val["HARIDUS"];break;
					case "eriala":$struct["tootajad"]["tootaja"][$tag]=$val["ERIALA"];break;
//					case "oppeasutus":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
					case "telefon":$struct["tootajad"]["tootaja"][$tag]=$val["TELEFON1"];break;
					case "mobiiltelefon":$struct["tootajad"]["tootaja"][$tag]=$val["TELEFON2"];break;
//					case "lyhinumber":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
					case "e_post":$struct["tootajad"]["tootaja"][$tag]=$val["EMAIL"];break;
//					case "ametikoht_nimetus":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "ametijuhend_viit":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "ruum":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
					case "palgaaste":$struct["tootajad"]["tootaja"][$tag]=$val["SUMMA"];break;
					case "asutus":$struct["tootajad"]["tootaja"][$tag]=$val["ASUTUS"];break;
//					case "allasutus":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "yksus_nimetus":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "yksus_id":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "prioriteet":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "on_peatumine":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "peatumine_pohjus":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
					case "toole_tulek_kp":$struct["tootajad"]["tootaja"][$tag]=$val["MEILE_TOOL"];break;
//					case "on_asendaja":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
//					case "asendamine_tookoht":$struct["tootajad"]["tootaja"][$tag]=$dat;break;
				}					
//				$struct["tootajad"]["tootaja"][strtolower($tag)]=$dat;
			}
		}
		header("Content-type: text/xml");
		print aw_serialize($struct,SERIALIZE_XML);
		die();
	}
//-- methods --//
}
?>
