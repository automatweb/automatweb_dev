<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/orb.aw,v 2.13 2002/01/31 00:17:33 kristo Exp $
// tegeleb ORB requestide handlimisega
classload("aw_template","defs","xml_support");
lc_load("automatweb");
class orb extends aw_template {
	////
	//! Konstruktor. Koik vajalikud argumendid antakse url-is ette
	var $data;
	var $info;
	function orb($args = array())
	{
		// peavad olema vähemalt 
		// a) class
		// b) action
		// c) vars (sisaldab vastavalt vajadusele kas $HTTP_GET_VARS-i voi $HTTP_POST_VARS-i
		
		// optional
		// d) silent. veateateid ei väljastata. caller peaks kontrollima return valuet,
		// kui see on false, siis oli viga.
		extract($args);
		$action = $vars["action"];

		$fatal = true;

		$this->db_init();
		$this->data = "";
		$this->info = array();
		lc_load("definition");

		$retval = true;

		$silent = 0;

		// class defineeritud?
		if (!isset($class))
		{
			$this->raise_error(E_ORB_CLASS_UNDEF,$fatal,$silent);
			bail_out();
		};
		
		// laeme selle klassi siis
		classload($class);

		if (!class_exists($class))
		{
			$this->raise_error(sprintf(E_ORB_CLASS_NOT_FOUND,$class),$fatal,$silent);
			bail_out();
		};

		global $orb_defs;


		if (!is_array($orb_defs[$class]))
		{
//			if ($orb_defs[$class] == "xml")
//			{
	
				$orb_defs = $this->load_xml_orb_def($class);
/*			}
			else
			{
				$this->raise_error(sprintf(E_ORB_ORB_CLASS_UNDEF,$class),$fatal,$silent);
				bail_out();
			};*/
		};

		$action = ($action) ? $action : $orb_defs[$class]["default"];

		if ((!defined("UID")) && (!isset($orb_defs[$class][$action]["nologin"])))
		{
			classload("config");
			$c = new db_config;
			$doc = $c->get_simple_config("orb_err_mustlogin");
			if ($doc != "")
			{
				header("Location: $doc");
				die();
			}
			else
			{
				$this->raise_error(E_ORB_LOGIN_REQUIRED,$fatal,$silent);
			}
		};


		// action defineeritud?
		if (!isset($action))
		{
			$this->raise_error(E_ORB_ACTION_UNDEF,$fatal,$silent);
			bail_out();
		};


		// leiame actionile vastava funktsiooni
		$fun = $orb_defs[$class][$action]; 
		
		// kas asi on defineeritud xml-is?
		$xml = $orb_defs[$class][$action]["xml"];
		if (!is_array($fun))
		{
			$this->raise_error(sprintf(E_ORB_CLASS_ACTION_UNDEF,$action,$class),$fatal,$silent);
			bail_out();
		};

		if (isset($vars["reforb"]) && $vars["reforb"] == 1)
		{
			$t = new $class;
			$fname = $fun["function"];
			if (!method_exists($t,$fname))
			{
				$this->raise_error(sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),$fatal,$silent);
				bail_out();
			}
 
			if ($orb_defs[$class][$action]["xmlrpc"] == 1)
			{
				$url = $this->do_orb_xmlrpc_call($orb_defs[$class][$action]["server"],$class,$action,$vars);
			}
			else
			{
				// loome õige objekti
				$t = new $class;

				// reforbi funktsioon peab tagastama aadressi, kuhu edasi minna
				$url = $t->$fname($vars);
			}
 
			// ja tagasi main programmi
			$this->data = $url;
			return;
		};

		// loome parameetrite array
		$params = array();
		if ($xml)
		{
			// orb on defineeritud XML-i kaudu
			if (isset($orb_defs[$class][$action]["all_args"]) && $orb_defs[$class][$action]["all_args"] == true)
			{
				$params = $GLOBALS["HTTP_GET_VARS"];
			}
			else
			{
				// required arguments
				$required = $orb_defs[$class][$action]["required"];
				$optional = $orb_defs[$class][$action]["optional"];
				$defined = $orb_defs[$class][$action]["define"];
				foreach($required as $key => $val)
				{
					if (!isset($vars[$key]))
					{
						$this->raise_error(sprintf(E_ORB_CLASS_PARM,$key,$action,$class),$fatal,$silent);
						bail_out();
					};

					$vartype = $orb_defs[$class][$action]["types"][$key];
					if ($vartype == "int")
					{
						if ($vars[$key] != sprintf("%d",$vars[$key]))
						{
							$this->raise_error(sprintf(E_ORB_NOT_INTEGER,$key),$fatal,$silent);
							bail_out();
						};
					};
					$params[$key] = $vars[$key];
				};
	 
				//optional arguments
				foreach($optional as $key => $val)
				{
					$vartype = $orb_defs[$class][$action]["types"][$key];
					if (isset($vars[$key]))
					{
						if ( ($vartype == "int") && ($vars[$key] != sprintf("%d",$vars[$key])) )
						{
							$this->raise_error(sprintf(E_ORB_NOT_INTEGER,$key),$fatal,$silent);
							bail_out();
						};
						$params[$key] = $vars[$key];
					}
					else
					if (isset($orb_defs[$class][$action]["defaults"][$key]))
					{
						$params[$key] = $orb_defs[$class][$action]["defaults"][$key];
					}
				};
				$params = array_merge($params,$defined);
			}
		}
		else
		{
			// orb on defineeritud arrayga
			reset($fun["params"]);
			while (list(,$vname) = each($fun["params"]))
			{
				if (!isset($vars[$vname]))
				{
					$this->raise_error(sprintf(E_ORB_CLASS_PARM,$vname,$action,$class),$fatal,$silent);
				};

				$params[$vname] = $vars[$vname];
			}
 
			if (is_array($fun["opt"]))
			{
				reset($fun["opt"]);
				while(list(,$vname) = each($fun["opt"]))
				{
					if (isset($vars[$vname]))
					{
						$params[$vname] = $vars[$vname];
					};
				}
			};
		};

		if ($user)
		{
			$params["user"] = 1;
		};

		if ($orb_defs[$class][$action]["xmlrpc"] == 1)
		{
			$content = $this->do_orb_xmlrpc_call($orb_defs[$class][$action]["server"],$class,$action,$params);
		}
		else
		{
			$t = new $class;
			// ja kutsume funktsiooni v2lja
			$fname = $fun["function"];
			if (!method_exists($t,$fname))
			{
				$this->raise_error(sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),$fatal,$silent);
			};
		
			$content = $t->$fname($params);
		}
		$this->data = $content;
		// kui klass teeb enda sisse $info nimelise array, ja kirjutab sinna mingit teksti, siis
		// see votab nad sealt välja ja caller saab get_info funktsiooni kaudu kätte kogu vajaliku info.
		// no ntx aw sees on vaja kuidagi saada string aw index.tpl-i sisse pealkirjaks
		// ilmselt see pole koige lihtsam lahendus, but hey, it works
		if (isset($t->info) && is_array($t->info))
		{
			$this->info = $t->info;
		};
		return;
	}

	// laeb XML failist orbi definitsiooni
	// why exactly is this function here and not in the orb class?
	function load_xml_orb_def($class)
	{
		global $basedir;
		// klassi definitsioon sisse
		$xmldef = get_file(array(
			"file" => "$basedir/xml/orb/$class.xml"
		));

		// loome parseri
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
						$orb_defs[$class][$attribs["name"]]["nologin"] = 1;
					};
					if (($tag == "action") && (isset($attribs["all_args"]) && $attribs["all_args"]))
					{
						$orb_defs[$class][$attribs["name"]]["all_args"] = true;
					};
					if (isset($attribs["default"]) && $attribs["default"] && ($tag == "action"))
					{
						$orb_defs[$class]["default"] = $attribs["name"];
					};
					if ($tag == "function")
					{
						$orb_defs[$class][$action][$tag] = $$tag;
						// seda saab kasutada monede lisakontrollide jaoks
						$orb_defs[$class][$action]["xml"] = 1;
						// initsialiseerime need arrayd
						$orb_defs[$class][$action]["required"] = array();
						$orb_defs[$class][$action]["optional"] = array();
						$orb_defs[$class][$action]["define"] = array();
						$orb_defs[$class][$action]["types"] = array();

						// default values for optional arguments
						$orb_defs[$class][$action]["defaults"] = array();

						if (!isset($attribs["xmlrpc"]))
						{
							$orb_defs[$class][$action]["xmlrpc"] = $xmlrpc_defs["xmlrpc"];
						}

						if (!isset($attribs["xmlrpc"]))
						{
							$orb_defs[$class][$action]["server"] = $xmlrpc_defs["server"];
						}

						// default action
						if (isset($attribs["default"]) && $attribs["default"])
						{
							$orb_defs[$class]["default"] = $action;
						};
					}
					else
					if ($tag == "class")
					{
						// klassi defauldid. kui funktsiooni juures pole, pannakse need
						$xmlrpc_defs["xmlrpc"] = $attribs["xmlrpc"];
						$xmlrpc_defs["server"] = $attribs["server"];
					}
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
					$orb_defs[$class][$action][$tag][$attribs["name"]] = $val;
					$orb_defs[$class][$action]["types"][$attribs["name"]] = $attribs["type"];
					$orb_defs[$class][$action]["defaults"][$attribs["name"]] = $attribs["default"];
				};
			};
		}; // foreach

		return $orb_defs;
	} // function
						

	function get_data()
	{
		return $this->data;
	}

	function get_info()
	{
		return $this->info;
	}

	function do_orb_xmlrpc_call($server,$class,$action,$params)
	{
		rpc_create_struct($params);
	}
}
?>
