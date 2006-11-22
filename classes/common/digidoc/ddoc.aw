<?php
// $Header: /home/cvs/automatweb_dev/classes/common/digidoc/ddoc.aw,v 1.5 2006/11/22 14:30:47 tarvo Exp $
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
	
	@property files_tb type=toolbar no_caption=1
	@property files_tbl type=table no_caption=1


@groupinfo signatures caption="Allkirjad"
@default group=signatures

	@property signatures_tb type=toolbar no_caption=1
	@property signatures_tbl type=table no_caption=1

# hidden meta fields for 'cacheing'
@property files type=hidden field=meta method=serialize
@property signatures type=hidden field=meta method=serialize
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
			case "files_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_cdata("siia tulevad nupud");
				break;
			case "files_tbl":
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
				$files = aw_unserialize($arr["obj_inst"]->prop("files"));
				$file_inst = get_instance(CL_FILE);
				foreach($files as $id => $data)
				{
					$t->define_data(array(
						"name" => html::href(array(
							"caption" => $data["name"],
							"url" => $file_inst->get_url($data["file"], $data["name"]),
						)),
						"type" => $data["type"],
						"size" => ($data["size"] > 1024)?round(($data["size"]/1024),2).t("kB"):$data["size"].t("B"),
					));
				}
				break;
			case "signatures_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_cdata("siia siis tulevad allkirjade eemaldamise && lisamise nupud");
				break;
			case "signatures_tbl":
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "firstname",
					"caption" => t("Eesnimi"),
				));
				$t->define_field(array(
					"name" => "lastname",
					"caption" => t("Perekonnanimi"),
				));
				$t->define_field(array(
					"name" => "pid",
					"caption" => t("Isikukood"),
				));
				$t->define_field(array(
					"name" => "time",
					"caption" => t("Aeg"),
				));
				$t->define_field(array(
					"name" => "role",
					"caption" => t("Roll"),
				));
				$t->define_field(array(
					"name" => "location",
					"caption" => t("Asukoht"),
				));
				
				$signatures = aw_unserialize($arr["obj_inst"]->prop("signatures"));
				foreach($signatures as $sig_id => $sig)
				{
					$loc = array();
					$loc[] = $sig["signing_town"];
					$loc[] = $sig["signing_state"];
					$loc[] = $sig["signing_index"];
					$loc[] = $sig["signing_country"];
					$t->define_data(array(
						"firstname" => $sig["signer_fn"],
						"lastname" => $sig["signer_ln"],
						"pid" => $sig["signer_pid"],
						"time" => date("d/m/Y H:i:s" ,$sig["signing_time"]),
						"role" => $sig["signing_role"],
						"location" => (strlen($tmp = join(", ", $loc)))?$tmp:t("M&aauml;&aauml;ramata"),
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
				
				//$this->_clear_old();
				$this->_do_reset_ddoc($arr["obj_inst"]->id());
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
	function _close_ddoc_session()
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

	/**
		@attrib api=1
		@param oid required type=oid
			ddoc object oid
		@param file optional type=oid
		@param name optional type=string
		@param contents optional type=string

		@comment
			Basically this adds file to ddoc container.
			Either $file or ($name and $contents) must be set. if $file is set, then corresponding CL_FILE object is takend and added to ddoc, otherwise file with given $name and $content is added, and the CL_FILE object is created automagically.
	**/
	function add_file($arr)
	{
		if(!is_oid($arr["oid"]) || (!is_oid($arr["file"]) && !(strlen($arr["name"]) && strlen($arr["contents"]))))
		{
			return false;
		}
		// if any previous signatures occurs, drop the act(return false)
		// if file name & contents are given(not aw object oid), create new aw file object and get a oid. otherwise get file contents from aw object.
		// add file contents to ddoc container
		// write file metainfo ( file object oid & file container id .. name too, and size, and mime type?)(mime type and container id from sk?)
		// the end?
	}
	
	/**
		@param ddoc_id required type=int
			file id in the ddoc file container
		@param oid required type=oid
			aw CL_DDOC object oid
		@param file required type=oid
			aw CL_FILE object oid
		@param size optional type=int
		@param type optional type=string
		@param name optional type=string
		@comment
			adds file to files metainfo in ddoc object.
		@returns
			true on success, false otherwise
	**/
	function _write_file_metainfo($arr)
	{
		if(!is_oid($arr["oid"]) || !strlen($arr["ddoc_id"]) || !is_oid($arr["file"]))
		{
			return false;
		}
		$o = obj($arr["oid"]);
		$m = aw_unserialize($o->prop("files"));
		$m[$arr["ddoc_id"]] = array(
			"file" => $arr["file"],
			"size" => $arr["size"],
			"type" => $arr["type"],
			"name" => $arr["name"],
		);
		$o->set_prop("files", aw_serialize($m, SERIALIZE_NATIVE));
		$o->save();
		return true;
	}
	
	/**
		@comment
			does the nessecary things if uploading new ddoc.
	**/
	function _do_reset_ddoc($oid, $save = false)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		$this->_start_ddoc_session($oid);
		$ddoc_parser = get_instance(CL_DDOC_PARSER);
		$xml = $ddoc_parser->Parse($this->digidoc->WSDL->xml, 'body');

		// set files
		$files = $ddoc_parser->files($this->get_ddoc($oid));
		$file_inst = get_instance(CL_FILE);
		// i don't use the parser results here because i don't get the file contents from there so easily
		foreach($files as $ddoc_id => $data)
		{
			$id = $file_inst->create_file_from_string(array(
				"parent" => $oid,
				"content" => base64_decode($data["VALUE"]),
				"name" => $data["FILENAME"],
				"type" => $data["MIMETYPE"],
			));

			$this->_write_file_metainfo(array(
				"ddoc_id" => $ddoc_id,
				"oid" => $oid,
				"file" => $id,
				"size" => $data["SIZE"],
				"type" => $data["MIMETYPE"],
				"name" => $data["FILENAME"],
			));
		}
		
		// set signatures
		$signatures = isset($xml['SignedDocInfo']['SignatureInfo'][0]) ? $xml['SignedDocInfo']['SignatureInfo'] : (isset($xml['SignedDocInfo']['SignatureInfo']) ? array(0=>$xml['SignedDocInfo']['SignatureInfo']) : array() );
		foreach($signatures as $sign)
		{
			$name = $sign["Signer"]["CommonName"];
			$name = split(",", $name);
			// why the hell do they put the T in the middle???.. 
			$signing_time = strtotime(str_replace("T", " ",$sign["SigningTime"]));

			$p_obj = new object();
			$p_obj->set_class_id(CL_CRM_PERSON);
			$p_obj->set_parent($oid);
			$p_obj->set_name(($fn = ucfirst(strtolower($name[1])))." ".($ln = ucfirst(strtolower($name[0]))));
			$p_obj->set_prop("firstname", $fn);
			$p_obj->set_prop("lastname", $ln);
			$p_obj->set_prop("personal_id", $sign["Signer"]["IDCode"]);
			$p_obj->save();
			
			$this->_write_signature_metainfo(array(
				"ddoc_id" => $sign["Id"],
				"oid" => $oid,
				"signer" => $p_obj->id(),
				"signer_fn" => $fn,
				"signer_ln" => $ln,
				"signer_pid" => $sign["Signer"]["IDCode"],
				"signing_time" => $signing_time,
				"signing_town" => $sign["SignatureProductionPlace"]["City"],
				"signing_state" => $sign["SignatureProductionPlace"]["StateOrProvince"],
				"signing_index" => $sign["SignatureProductionPlace"]["PostalCode"],
				"signing_country" => $sign["SignatureProductionPlace"]["CountryName"],
				"signing_role" => $sign["SignerRole"]["Role"],
			));
		}

		$this->_close_ddoc_session();
	}

/// SIGNING PROCESS

	/**
		@attrib api=1
		@param oid required type=oid
			aw CL_DDOC object oid
		
		@comment
			Well, this method gives a link that pop's up the singing window!!
			This is the function to use when you want to sign something!!
	**/
	function sign_link($arr)
	{
		if(!is_oid($arr["oid"]))
		{
			return t("! Error !");
		}
	}

	/**
		@comment
			this little motherfucker does the first step of signing...
	**/
	function _prepare_signature($arr)
	{
		
	}

	/**
		@comment
			.. and this little motherfucker does the second step of signing.
	**/
	function _finalize_signature($arr)
	{
	}

	/**
		@comment
			here, the signing process will come to an end.. finally and hopefully.. 
	**/
	function _end_signature($arr)
	{
	}	


	/**
		@param ddoc_id required type=int
			signature's id in the ddoc file container
		@param oid required type=oid
			aw CL_DDOC object oid
		@param signer required type=oid
			aw CL_CRM_PERSON object oid
		@param signer_fn required type=string
			signer firtname
		@param signer_ln required type=string
			signer lastname
		@param signer_pid required type=string
			signer personal id code
		@param signing_time required type=int
			signing time
	
		@param signing_town optional
		@param signing_county optional
		@param signing_index optional
		@param signing_country optional
		@param signing_role optional

		@comment
			write sigantures metainfo into ddoc object
	**/
	function _write_signature_metainfo($arr)
	{
		if(!strlen($arr["ddoc_id"]) || !is_oid($arr["oid"]) || !is_oid($arr["signer"]) || !strlen($arr["signer_fn"]) || !strlen($arr["signer_ln"]) || !strlen($arr["signer_pid"]) || !strlen($arr["signing_time"]))
		{
			return false;
		}
		$o = obj($arr["oid"]);
		$m = aw_unserialize($o->prop("signatures"));
		$m[$arr["ddoc_id"]] = $arr;
		$o->set_prop("signatures", aw_serialize($m, SERIALIZE_NATIVE));
		$o->save();
		return true;
	}

	/**
		@comment
			clears all cached data from object. Used internally, wher new ddoc file is uploaded.
	**/
	function _clear_old($oid)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		// clear relations to files, docus
		// clear thingies from metadata
		$o->set_prop("files", aw_serialize(array(), SERIALIZE_NATIVE));
		$o->set_prop("signatures", aw_serialize(array(), SERIALIZE_NATIVE));
		$o->save();
		// realtions to persons (signatures)
	}
}
?>
