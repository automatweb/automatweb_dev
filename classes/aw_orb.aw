<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/aw_orb.aw,v 2.4 2002/11/07 23:03:01 kristo Exp $
// aw_orb.aw - new and improved ORB

class aw_orb extends aw_template
{
	function aw_orb($args = array())
	{
		$this->init("");
	}

	////
	// !Returns a list of all defined ORB classes
	function get_public_classes($args = array())
	{
		$basedir = $this->cfg["basedir"];
		// klassi definitsioon sisse
		$xmldef = $this->get_file(array(
			"file" => "$basedir/xml/interfaces/public.xml"
		));

		// loome parseri
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$xmldef,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);

		$pclasses = array();

		foreach($values as $key => $val)
		{
			if ( ($val["tag"] == "class") && ($val["type"] == "complete") )
			{
				$attr = $val["attributes"];
				$pm = $this->get_public_methods(array(
					"id" => $attr["id"],
					"name" => $attr["name"],
				));
				if (sizeof($pm)  > 0)
				{
					$pclasses = $pclasses + $pm;
				};

			}

		}

		return $pclasses;

	}

	function get_public_methods($args = array())
	{
		extract($args);
		classload("orb");
		$orb_defs = orb::load_xml_orb_def($id);
		$pmethods = array();
		foreach($orb_defs[$id] as $key => $val)
		{
			if ($val["public"])
			{
				$pmethods[$id . "/" . $key] = $name . "/" . $val["function"];
			}
		};

		return $pmethods;
	}
	
	function get_public_method($args = array())
	{
		extract($args);
		classload("orb");
		$orb_defs = orb::load_xml_orb_def($id);
//		echo "id = $id , action = $action , orb_defs = <pre>", var_dump($orb_defs),"</pre> <br>";
		if ($action == "default")
		{
			$action = $orb_defs[$id]["default"];
		}

		$meth = $orb_defs[$id][$action];
		$meth["values"] = array();
		$cl = get_instance($id);
		$ar = array();
		if ($id == "document")
		{
			if ($cl->get_opt("cnt_documents") == 1)
			{
				$meth["values"]["id"] = $cl->get_opt("shown_document");
			}
			$meth["values"]["period"] = aw_global_get("act_per_id");
			//$data = $cl->get_opt("data");
			$meth["values"]["parent"] = $cl->get_opt("parent");
			if ($action == "change" && $cl->get_opt("shown_document"))
			{
				$meth["values"]["id"] = $cl->get_opt("shown_document");
			}
			if ($action == "new")
			{
				$meth["values"]["parent"] = aw_global_get("section");
			}
		};
		return $meth;
	}

	// !now this is a bit tricky. It's similar to get_file
	// except that it is ment to be used for files whose
	// contents can be serialized.

	// How it works:
	// 1) find the name of the cached (serialized) file
	// 2) get the timestamp of the original (source) file
	// 3) get the timestamp of the cache file
	// 4) if cache_timestamp > orig_timestamp then unserialize_and_return
	// 5) else get file contents, _unserialize_ them - because they are in 
	//    some kind of serialized form, XML for example, serialize them
	//    again using aw_serializer, write the cached file and return the
	//    results of XML unserialization

	// site(bool) - read the source file from the site directory?
	// file(string) - fully qualified file name (incl. path)
	//      -- coderef is array($object,'function');
	// unserializer(coderef) - reference to the function which can be used
	// to unserialize the source file.
	function get_serialized_file($args = array())
	{
		extract($args);
		if ($site)
		{
			$srcpath = $this->cfg["site_basedir"];
			$cachepath = $srcpath . "/pagecache";
		}
		else
		{
			$srcpath = $this->cfg["basedir"];
			$cachepath = $srcpath . "/files/cache";
		};

		$src_f = $srcpath . "/" . $file;
		$cachename = str_replace("/","_",$file);
		$cache_f = $cachepath . "/" . $cachename;
		$srcmtime = filemtime($src_f);
		if (not($srcmtime))
		{
			$this->raise_error(ERR_CORE_NOFILE,"File $src_f not found",1);
		};
		$cachemtime = filemtime($cache_f);
		if ($cachemtime > $srcmtime)
		{
			print "returning unserialized cache<br>";
		}
		else
		{
			$contents = get_file(array("file" => $src_f));
			$cb_class = $unserializer[0];
			$cb_method = $unserializer[1];
			$cb_arg = $unserializer[2];
			$res = $cb_class->$cb_method(array($cb_arg => $contents));
			print "returning unserialized source<br>";
			print "<pre>";
			print_r($res);
			print "</pre>";
		};
		print $srcmtime;
		print "<br>";
		print $cachemtime;
	}
	
	////
	// !laeb XML failist orbi definitsiooni
	function load_xml_orb_def($args = array())
	{
		extract($args);
		// loome parseri
		$xmldef = $source;
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$xmldef,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
	
		// konteinerite tüübid
		$containers = array("class","action","function","arguments");
	
		// argumentide tüübid
		$argtypes = array("optional","required","define");
	
		// argumentide andmetüübid (int, string, whatever)
		$types = array();
	
		// ja siia moodustub loplik struktuur
		$orb_defs = array();

		foreach($values as $key => $val)
		{
			// parajasti töödeldava tag-i nimi
			$tag = $val["tag"];
	
			// on kas tyhi, "open", "close" voi "complete".
			$tagtype = $val["type"];
 
			// tagi parameetrid, array
			$attribs = isset($val["attributes"]) ? $val["attributes"] : "";
 
			// kui tegemist on nö "konteiner" tag-iga, siis...
			if (in_array($tag,$containers))
			{

				if (in_array($tagtype,array("open","complete")))
				{
					$$tag = $attribs["name"];
					if (($tag == "action") && (isset($attribs["nologin"]) && $attribs["nologin"]))
					{
						$orb_defs[$attribs["name"]]["nologin"] = 1;
					};
					if (($tag == "action") && (isset($attribs["public"]) && $attribs["public"]))
					{
						$orb_defs[$attribs["name"]]["public"] = 1;
					};
					if (($tag == "action") && (isset($attribs["all_args"]) && $attribs["all_args"]))
					{
						$orb_defs[$attribs["name"]]["all_args"] = true;
					};
					if (isset($attribs["default"]) && $attribs["default"] && ($tag == "action"))
					{
						$orb_defs["default"] = $attribs["name"];
					};
					if ($tag == "function")
					{
						$orb_defs[$action][$tag] = $$tag;
						// initsialiseerime need arrayd
						$orb_defs[$action]["required"] = array();
						$orb_defs[$action]["optional"] = array();
						$orb_defs[$action]["define"] = array();
						$orb_defs[$action]["types"] = array();

						// default values for optional arguments
						$orb_defs[$action]["defaults"] = array();

						if (!isset($attribs["xmlrpc"]))
						{
							$orb_defs[$action]["xmlrpc"] = $xmlrpc_defs["xmlrpc"];
						}

						if (!isset($attribs["xmlrpc"]))
						{
							$orb_defs[$action]["server"] = $xmlrpc_defs["server"];
						}

						// default action
						if (isset($attribs["default"]) && $attribs["default"])
						{
							$orb_defs["default"] = $action;
						};
					}
					else
					if ($tag == "class")
					{
						// klassi defauldid. kui funktsiooni juures pole, pannakse need
						$xmlrpc_defs["xmlrpc"] = $attribs["xmlrpc"];
						$xmlrpc_defs["server"] = $attribs["server"];
						if ($attribs["extends"])
						{
							$extends = explode(",",$attribs["extends"]);
							$orb_defs["_extends"] = $extends;
						};
					};
				}
				elseif ($tagtype == "close")
				{
					$$tag = "";
				};
			};
 
			// kui leidsime argumenti määrava tag-i, siis ...
			if (in_array($tag,$argtypes))
			{
				// kontroll, just in case
				if ($tagtype == "complete")
				{
					if ($tag == "define")
					{
						$val = $attribs["value"];
					}
					else
					{
						$val = 1;
					};
					$orb_defs[$action][$tag][$attribs["name"]] = $val;
					$orb_defs[$action]["types"][$attribs["name"]] = $attribs["type"];
					$orb_defs[$action]["defaults"][$attribs["name"]] = $attribs["default"];
				};
			};
		}; // foreach

		return $orb_defs;
	} // function
}
?>
