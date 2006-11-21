<?php
// $Header: /home/cvs/automatweb_dev/classes/common/digidoc/ddoc.aw,v 1.3 2006/11/21 14:29:43 tarvo Exp $
// ddoc.aw - DigiDoc 
/*

@classinfo syslog_type=ST_DDOC relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property name type=text field=name
	@caption Nimi

	@property ddoc type=fileupload field=meta mehtod=serialize
	@caption Vali fail

	@property ddoc_location type=hidden field=meta method=serialize
	@caption asukoht

@groupinfo files caption="Failid"
@default group=files
	
	@property files type=table no_caption=1

@groupinfo signatures caption="Allkirjad"
@default group=signatures

	@property sig_tb type=toolbar no_caption=1
	@property signatures type=table no_caption=1
*/

class ddoc extends class_base
{
	function ddoc()
	{
		$this->init(array(
			"tpldir" => "common/digidoc//ddoc",
			"clid" => CL_DDOC
		));

		// a temporary fix here.. i dont know how or where i'm gonna put the fucking conf file
		$loc = aw_ini_get("basedir")."/../public/vv_files/digidoctest/conf.php";
		include_once($loc);
		include_once(aw_ini_get("basedir")."/addons/pear/SOAP/WSDL.php");

		// total mess.. sick fuck, etc..
		classload("protocols/file/digidoc");
		classload("common/digidoc/ddoc_parser");
		digidoc::load_WSDL();
		$this->digidoc = get_instance("protocols/file/digidoc");
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "name":
				$prop["value"] = $prop["value"]." @ ".$arr["obj_inst"]->prop("ddoc_location");
				break;
			case "files":
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "name",
					"caption" => t("Fail"),
				));
				$t->define_field(array(
					"name" => "type",
					"caption" => t("T&uuml;&uuml;p"),
				));
				$t->define_field(array(
					"name" => "size",
					"caption" => t("Suurus"),
				));
				include_once("automatweb/vv_digidoc/conf.php");
				$ddoc_parser = get_instance(CL_DDOC_PARSER);

				$files = isset($xml['SignedDocInfo']['DataFileInfo'][0]) ? $xml['SignedDocInfo']['DataFileInfo'] : (isset($xml['SignedDocInfo']['DataFileInfo'])? array(0=>$xml['SignedDocInfo']['DataFileInfo']): array() ); 
				$files = $ddoc_parser->files($this->get_ddoc($arr["obj_inst"]->id()), "");
				foreach($files as $id => $data)
				{
					$t->define_data(array(
						"name" => $data["FILENAME"],
						"type" => $data["MIMETYPE"],
						"size" => ($data["SIZE"] > 1024)?round(($data["SIZE"]/1024),2).t("kB"):$data["SIZE"].t("B"),
					));
				}
				break;
			case "sig_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_cdata("siia siis tulevad allkirjade eemaldamise && lisamise nupud");
				break;
			case "signatures":
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "name",
					"caption" => t("Allkirjastaja"),
				));
				$t->define_field(array(
					"name" => "time",
					"caption" => t("Allkirjastamise aeg"),
				));
				$t->define_field(array(
					"name" => "location",
					"caption" => t("Allkirjastamise asukoht"),
				));
				
				// well, basically what this does is that it initializes wsdl session with webservice(if not done already)
				// thing is that maybe i should put a better check here.. not just session variable check? ... in the other hand, this is tehe most fool-proof
				unset($_SESSION["scode"]);
				if(!$_SESSION["scode"])
				{
					$this->_start_ddoc_session($arr["obj_inst"]->id());
				}

				$ddoc_parser = get_instance(CL_DDOC_PARSER);

				$xml = $ddoc_parser->Parse($this->digidoc->WSDL->xml, 'body');
				//$this->digidoc->addHeader("SessionCode", $_SESSION["scode"]);
				//$ret = $this->digidoc->WSDL->GetSignedDocInfo();
				if(PEAR::isError($ret))
				{
					arr();arr();arr();
					arr("ERROR: " . htmlentities($ret->getMessage()) . "<br>\n", FALSE);
					return PROP_IGNORE;
					//echo back(TRUE);
				}
				//$signs = $ddoc_parser->getSignaturesInfo($this->get_ddoc($arr["obj_inst"]->id()), "");
				//$signs = $ddoc_parser->getSignaturesInfo($ret);


				$signatures = isset($xml['SignedDocInfo']['SignatureInfo'][0]) ? $xml['SignedDocInfo']['SignatureInfo'] : (isset($xml['SignedDocInfo']['SignatureInfo']) ? array(0=>$xml['SignedDocInfo']['SignatureInfo']) : array() );
				foreach($signatures as $sig)
				{
					$t->define_data(array(
						"name" => $sig["Signer"]["CommonName"],
						"time" => $sig["SigningTime"],
						"location" => strlen($sig["SignatureProductionPlace"]["City"])?$sig["SignatureProductionPlace"]["City"]:t("M&aauml;&aauml;ramata"),
					));
				}
				break;

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
			case "ddoc":
				// see asi eeldab ajutise faili tegemist eksole?
				//arr($prop);
				//arr($_FILES);

				// actually i should check wheather it is a correct ddoc file
				if (is_array($data["value"]))
				{
					$file = $data["value"]["tmp_name"];
					$file_type = $data["value"]["type"];
					$file_name = $data["value"]["name"];
				}
				else
				{
					$file = $_FILES["ddoc"]["tmp_name"];
					$file_name = $_FILES["ddoc"]["name"];
					$file_type = $_FILES["ddoc"]["type"];
				};

				$parser = get_instance(CL_DDOC_PARSER);
				$parser->setDigiDocFormatAndVersion(file_get_contents($file));
				if(!strlen($parser->format) || !strlen($parser->version))
				{
					return PROP_IGNORE;
				}

				$cl_file = get_instance(CL_FILE);

				if (is_uploaded_file($file))
				{
					
					$pathinfo = pathinfo($file_name);
					if (empty($file_type))
					{
						$mimeregistry = get_instance("core/aw_mime_types");
						$realtype = $mimeregistry->type_for_ext($pathinfo["extension"]);
						$file_type = $realtype;
					};
					$final_name = $cl_file->generate_file_path(array(
						"type" => $file_type,
					));
						
					move_uploaded_file($file, $final_name);
					$arr["obj_inst"]->set_name($file_name);
					$arr["obj_inst"]->set_prop("ddoc_location", $final_name);
					//$arr["obj_inst"]->set_prop("type", $file_type);
					$this->file_type = $file_type;
				}
				else
				if (is_array($data["value"]) && $data["value"]["content"] != "")
				{
					$final_name = $cl_file->generate_file_path(array(
						"type" => "text/html",
					));
					$fc = fopen($final_name, "w");
					fwrite($fc, $data["value"]["content"]);
					fclose($f);
					$arr["obj_inst"]->set_prop("ddoc_location", $final_name);
					$arr["obj_inst"]->set_name($data["value"]["name"]);
					//$arr["obj_inst"]->set_prop("type", "text/html");
					$this->file_type = "text/html";
				}
				else
				{
					return PROP_IGNORE;
					//$retval = PROP_IGNORE;
				};

				$this->_clear_old();
				$this->_create_aw_files_from_ddoc($arr["obj_inst"]->id());
				break;
			case "ddoc_location":
				$retval = PROP_IGNORE;
				break;

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

	function get_ddoc($oid)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		return file_get_contents($o->prop("ddoc_location"));
	}

	/**
		@comment
			starts ddoc session
		@returns
			if everything is ok, returns false. otherwise error string will be returned.
	**/
	function _start_ddoc_session($oid)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		# laadime sisu digidoc parserisse
		$ddoc = new ddoc_parser($this->get_ddoc($oid));
		# alustame sessiooni, saates parsitud digidoci
		$ret = $this->digidoc->WSDL->startSession($ddoc->getDigiDoc( LOCAL_FILES ), TRUE, '');
		if(!PEAR::isError($ret))
		{ # kui operatsioon oli edukas
			$_SESSION['scode'] = $xml['sesscode'];
			$_SESSION["ddoc_name"] = $o->name();
			return false;
		}
		else
		{
			return htmlentities($ret->getMessage());;
		} //else
	}
	function close_ddoc_session()
	{
		$ret = $this->digidoc->WSDL->closeSession($_SESSION["scode"]);
		if(!PEAR::isError($ret))
		{
			unset($_SESSION["scode"], $_SESSION["ddoc_name"]);
			return false;
		}
		else
		{
			return htmlentities($ret->getMessage());
		}
	}

	function _create_aw_files_from_ddoc($oid)
	{
		$this->_start_ddoc_session($oid);
		$parser = get_instance(CL_DDOC_PARSER);
		$data = $parser->Parse($this->digidoc->WSDL->xml, 'body');
		arr($data);
		/*
		file::create_file_from_string(array(
			id,
			parent,
			content,
			name,
		));
		*/
		$this->_close_ddoc_session();
	}

	function _clear_old()
	{
		// clear relations to files, docus
		// clear thingies from metadata
		// realtions to persons (signatures)
	}
}
?>
