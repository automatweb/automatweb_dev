<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/orb.aw,v 2.40 2003/03/17 18:48:52 duke Exp $
// tegeleb ORB requestide handlimisega
lc_load("automatweb");
class orb extends aw_template 
{
	var $data;
	var $info;
	////
	//! Konstruktor. Koik vajalikud argumendid antakse url-is ette
	//  why the hell did I put all the functionality into the constructor?
	// now I can't put other useful functions into this class and used them
	// without calling the instructor
	function orb($args = array())
	{
		// peavad olema v�hemalt 
		// a) class
		// b) action
		// c) vars (sisaldab vastavalt vajadusele kas $HTTP_GET_VARS-i voi $HTTP_POST_VARS-i
		
		// optional
		// d) silent. veateateid ei v�ljastata. caller peaks kontrollima return valuet,
		// kui see on false, siis oli viga.

		extract($args);
		$action = $vars["action"];

		$fatal = true;

		$this->init("");
		$this->data = "";
		$this->info = array();
		lc_load("definition");

		$retval = true;

		$silent = 0;

		$this->silent = $silent;
		$this->fatal = $fatal;


		// class defineeritud?
		if (!isset($class))
		{
			$this->raise_error(ERR_ORB_NOCLASS,E_ORB_CLASS_UNDEF,true,$silent);
		};
		
		// laeme selle klassi siis
		$orb_defs = $this->try_load_class($class);

		$this->orb_defs = $orb_defs;

		$action = ($action) ? $action : $orb_defs[$class]["default"];

		$this->check_login(array("class" => $class,"action" => $action));

		// if the action is found in one of the classes defined by
		// the extends attribute, it should know which class was really
		// requested.
		// classload($class);
//                $this->orb_class = new $class();

		// action defineeritud?
		if (!isset($action))
		{
			$this->raise_error(ERR_ORB_AUNDEF,E_ORB_ACTION_UNDEF,true,$silent);
		};

		// create an array of class names that should be loaded.
		$cl2load = array($class);
		if (is_array($orb_defs[$class]["_extends"]))
		{
			$cl2load = array_merge($cl2load,$orb_defs[$class]["_extends"]);
		};

		$found = false;

		foreach($cl2load as $clname)
		{
			// not yet found
			if (!$found)
			{
				// only load if definitions for this class are
				// not yet loaded (master class)
				if (empty($_orb_defs[$clname]))
				{
					$_orb_defs = $this->try_load_class($clname);
				};
				$fun = isset($_orb_defs[$clname][$action]) ? $_orb_defs[$clname][$action] : false;

				// XXX: fallback to change for objects which do not have view action
				if ( ($action == "view") && (!is_array($fun)) )
				{
					$action = "change";
					$fun = $_orb_defs[$clname][$action];
				};


				if (is_array($fun))
				{
					$found = true;
					// copy the function definition from the extended class to the called class
					// this way it works like real inheritance - all the other properties, 
					// including which class is instantiated, come from the class that was called
					// and not the class in which the function was found
					$orb_defs[$class][$action] = $_orb_defs[$clname][$action];
				};
			};

		};

		// still not found?
		if (!$found)
		{
			$this->raise_error(ERR_ORB_CAUNDEF,sprintf(E_ORB_CLASS_ACTION_UNDEF,$action,$class),true,$silent);
		};

		// check acl
		$this->do_orb_acl_checks($orb_defs[$class][$action], $vars);


		if (isset($vars["reforb"]) && $vars["reforb"] == 1)
		{
			$t = new $class;
			$fname = $fun["function"];
			if (!method_exists($t,$fname))
			{
				$this->raise_error(ERR_ORB_MNOTFOUND,sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),true,$silent);
			}
 
			if ($orb_defs[$class][$action]["xmlrpc"] == 1)
			{
				$url = $this->do_orb_xmlrpc_call($orb_defs[$class][$action]["server"],$class,$action,$vars);
			}
			else
			{
				// loome �ige objekti
				$t = new $class;

				$t->set_opt("orb_class",&$this->orb_class);

				// reforbi funktsioon peab tagastama aadressi, kuhu edasi minna
				$url = $t->$fname($vars);
			}
 
			// ja tagasi main programmi
			$this->data = $url;
			return;
		};

		// loome parameetrite array
		$params = array();
		// orb on defineeritud XML-i kaudu
		if (isset($orb_defs[$class][$action]["all_args"]) && $orb_defs[$class][$action]["all_args"] == true)
		{
			$_params = $GLOBALS["HTTP_GET_VARS"];
			$required = $orb_defs[$class][$action]["required"];
			// first check, whether all required arguments are set
			foreach($required as $key => $val)
			{
				if (!isset($_params[$key]))
				{
					$this->raise_error(ERR_ORB_CPARM,sprintf(E_ORB_CLASS_PARM,$key,$action,$class),true,$silent);
				};
			};
			foreach($_params as $key => $val)
			{
				$this->validate_value(array(
					"type" => $orb_defs[$class][$action]["types"][$key],
					"name" => $key,
					"value" => $val,
				));
				$params[$key] = $val;
			};
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
					$this->raise_error(ERR_ORB_CPARM,sprintf(E_ORB_CLASS_PARM,$key,$action,$class),true,$silent);
				};

				$this->validate_value(array(
					"type" => $orb_defs[$class][$action]["types"][$key],
					"name" => $key,
					"value" => $vars[$key],
				));

				$params[$key] = $vars[$key];
			};
 
			//optional arguments
			foreach($optional as $key => $val)
			{
				if (isset($vars[$key]))
				{
					$this->validate_value(array(
						"type" => $orb_defs[$class][$action]["types"][$key],
						"name" => $key,
						"value" => $vars[$key],
					));
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
			$t->set_opt("orb_class",&$this->orb_class);
			$fname = $fun["function"];
			if (!method_exists($t,$fname))
			{
				$this->raise_error(ERR_ORB_MNOTFOUND,sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),$fatal,$silent);
			};
		
			$content = $t->$fname($params);
		}
		$this->data = $content;
		// kui klass teeb enda sisse $info nimelise array, ja kirjutab sinna mingit teksti, siis
		// see votab nad sealt v�lja ja caller saab get_info funktsiooni kaudu k�tte kogu vajaliku info.
		// no ntx aw sees on vaja kuidagi saada string aw index.tpl-i sisse pealkirjaks
		// ilmselt see pole koige lihtsam lahendus, but hey, it works
		if (isset($t->info) && is_array($t->info))
		{
			$this->info = $t->info;
		};
		return;
	}

	function validate_value($args = array())
	{
		if ($args["type"] == "int")
		{
			if (!is_numeric($args["value"]))
			{
				$this->raise_error(ERR_ORB_NINT,sprintf(E_ORB_NOT_INTEGER,$args["name"]),true,$this->silent);
			};
		};
	}

	function load_xml_orb_def($class)
	{
		$fc = get_instance("cache");
		$fc->get_cached_file(array(
			"fname" => "/xml/orb/$class.xml",
			"unserializer" => array(&$this,"load_xml_orb_def_file"),
			"loader" => array(&$this,"load_serialized_orb_def"),
		));
		return $this->_tmp;
	}

	function load_serialized_orb_def($args = array())
	{
		$this->_tmp = $args["data"];
	}

	////
	// !laeb XML failist orbi definitsiooni
	function load_xml_orb_def_file($args = array())
	{
		// loome parseri
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$args["content"],&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
	
		// konteinerite t��bid
		$containers = array("class","action","function","arguments");
	
		// argumentide t��bid
		$argtypes = array("optional","required","define");
	
		// argumentide andmet��bid (int, string, whatever)
		$types = array();
	
		// ja siia moodustub loplik struktuur
		$orb_defs = array();

		foreach($values as $key => $val)
		{
			// parajasti t��deldava tag-i nimi
			$tag = $val["tag"];
	
			// on kas tyhi, "open", "close" voi "complete".
			$tagtype = $val["type"];
 
			// tagi parameetrid, array
			$attribs = isset($val["attributes"]) ? $val["attributes"] : "";
 
			// kui tegemist on n� "konteiner" tag-iga, siis...
			if (in_array($tag,$containers))
			{

				if (in_array($tagtype,array("open","complete")))
				{
					$$tag = $attribs["name"];
					if (($tag == "action") && (isset($attribs["nologin"]) && $attribs["nologin"]))
					{
						$orb_defs[$class][$attribs["name"]]["nologin"] = 1;
					};
					if (($tag == "action") && (isset($attribs["public"]) && $attribs["public"]))
					{
						$orb_defs[$class][$attribs["name"]]["public"] = 1;
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
						if ($attribs["extends"])
						{
							$extends = explode(",",$attribs["extends"]);
							$orb_defs[$class]["_extends"] = $extends;
						};
						if ($attribs["folder"])
						{
							$orb_defs[$class]["___folder"] = $attribs["folder"];
						};
					};
				}
				elseif ($tagtype == "close")
				{
					$$tag = "";
				};
			};
 
			// kui leidsime argumenti m��rava tag-i, siis ...
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
					if ($attribs["acl"] != "")
					{
						$orb_defs[$class][$action]["acl"][$attribs["name"]] = $attribs["acl"];
					}
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

	////
	// !Checks whether a resource requires login and if so, asks for the password
	// also, remembers the requesterd url so we can redirect back there after the
	// login
	function check_login($args = array())
	{
		extract($args);
		if ((!aw_global_get("uid")) && (!isset($this->orb_defs[$class][$action]["nologin"])))
		{
			$auth = get_instance("auth");
			print $auth->show_login();
			// dat sucks
			exit;

			//$c = get_instance("config");
			//$doc = $c->get_simple_config("orb_err_mustlogin");
			//if ($doc != "")
			//{
			//	header("Location: $doc");
			//	die();
			//}
			//else
			//{
			//	$this->raise_error(ERR_ORB_LOGIN,E_ORB_LOGIN_REQUIRED,$fatal,$silent);
			//}
		};
	}

	function try_load_class($class)
	{
		if (!file_exists($this->cfg["basedir"]."/xml/orb/$class.xml"))
		{
			$this->raise_error(ERR_ORB_NOTFOUND,sprintf(E_ORB_CLASS_NOT_FOUND,$class),true,$this->silent);
		}
	
		$ret = $this->load_xml_orb_def($class);

		// try and figure out the folder for this class 
		$folder = "";

		if (isset($ret[$class]["___folder"]))
		{
			$folder = $ret[$class]["___folder"]."/";
		}

		// laeme selle klassi siis
		classload($folder.$class);

		if (!class_exists($class))
		{
			// try without folder for compatibility
			classload($class);

			if (!class_exists($class))
			{
				$this->raise_error(ERR_ORB_NOTFOUND,sprintf(E_ORB_CLASS_NOT_FOUND,$class),true,$this->silent);
			}
		};

		if (!$this->orb_class)
		{
			$this->orb_class = new $class;
		};
		
		// FIXME: we should cache that def instead of parsing xml every time
		return $ret;

	}

	function do_orb_acl_checks($act, $vars)
	{
		if (is_array($act["acl"]))
		{
			foreach($act["acl"] as $varname => $varacl)
			{
				$varvalue = $vars[$varname];
				if ($varvalue)
				{
					$aclarr = explode(";", $varacl);
					foreach($aclarr as $aclid)
					{
						if (!$this->can($aclid, $varvalue))
						{
							$this->raise_error(ERR_ACL, "ORB:Teil puudub $aclid-&otilde;igus objektile id-ga $varvalue!",true, false);
						}
					}
				}
			}
		}
	}
}

class new_orb extends orb
{
	function new_orb()
	{
		$this->init("");
	}

	////
	// !executes an orb function call and returns the data that the function returns
	// params:
	// required:
	//	action - orb action to exec
	// optional
	//  class - class for the action - default the current class
	//  params - params to the action 
	//  method - the method to use when doing the function call - possible values: local / xmlrpc / (soap - not implemented yet) 
	//  server - if doing a rpc call, the server where to connect
	//  login_obj - if we must log in to a serverm the id of the CL_AW_LOGIN that will be used to login to the server
	//              if this is set, then server will be ignored
	function do_method_call($arr)
	{
		$arr["server"] = str_replace("http://","",$arr["server"]);
		extract($arr);

		$this->fatal = true;
		$this->silent = false;

		if (!isset($class))
		{
			$this->raise_error(ERR_ORB_NOCLASS,E_ORB_CLASS_UNDEF,true,$this->silent);
		};

		if (!isset($action))
		{
			$this->raise_error(ERR_ORB_AUNDEF,E_ORB_ACTION_UNDEF,true,$this->silent);
		};

		// get orb defs for the class
		$orb_defs = $this->try_load_class($class);

		// check params
		$params = $this->check_method_params($orb_defs, $params, $class, $action);
		$arr["params"] = $params;

		$this->do_orb_acl_checks($orb_defs[$class][$action], $params);

		// do the call
		if (!$method || $method == "local")
		{
			// local call
			$data = $this->do_local_call($orb_defs[$class][$action]["function"], $class, $params, $orb_defs[$class]["___folder"]);
		}
		else
		{
			// log in if necessary or get the existing session for rpc call
			list($arr["remote_host"], $arr["remote_session"]) = $this->get_remote_session($arr);

			// load rpc handler
			$inst = get_instance("orb/".$method);
			if (!is_object($inst))
			{
				$this->raise_error(ERR_ORB_RPC_NO_HANDLER,"Could not load request handler for request method '".$method."'", true,$this->silent);
			}
			// send the remote request and read the result
			$data = $inst->do_request($arr);
		}

		return $data;
	}

	////
	// !checks the parameters $params for action $action, defined in $defs and returns the matching parameters
	function check_method_params($orb_defs, $params, $class, $action)
	{
		$ret = array();
		if (isset($orb_defs[$class][$action]["all_args"]) && $orb_defs[$class][$action]["all_args"] == true)
		{
			return $params;
		}
		else
		{
			// required arguments
			$required = $orb_defs[$class][$action]["required"];
			$optional = $orb_defs[$class][$action]["optional"];
			$defined = $orb_defs[$class][$action]["define"];
			if (is_array($required))
			{
				foreach($required as $key => $val)
				{
					if (!isset($params[$key]))
					{
						$this->raise_error(ERR_ORB_CPARM,sprintf(E_ORB_CLASS_PARM,$key,$action,$class),true,$this->silent);
					};

					$vartype = $orb_defs[$class][$action]["types"][$key];
					if ($vartype == "int")
					{
						if (!is_number($params[$key]))
						{
							$this->raise_error(ERR_ORB_NINT,sprintf(E_ORB_NOT_INTEGER,$key),true,$this->silent);
						};
					};
					$ret[$key] = $params[$key];
				};
			}
			
			//optional arguments
			if (is_array($optional))
			{
				foreach($optional as $key => $val)
				{
					$vartype = $orb_defs[$class][$action]["types"][$key];
					if (isset($params[$key]))
					{
						if ( ($vartype == "int") && ($params[$key] != sprintf("%d",$vars[$key])) )
						{
							$this->raise_error(ERR_ORB_NINT,sprintf(E_ORB_NOT_INTEGER,$key),true,$this->silent);
						};
						$ret[$key] = $params[$key];
					}
					else
					// note, there seems to be some bitrot here, isset breaks things
		
					if ($orb_defs[$class][$action]["defaults"][$key])
					{
						$ret[$key] = $orb_defs[$class][$action]["defaults"][$key];
					}
				};
			}

			if (is_array($defined))
			{
				$ret += $defined;
			}
		}
		return $ret;
	}

	function do_local_call($func, $class, $params, $folder)
	{
		if ($folder != "")
		{
			$folder.="/";
		}
		$inst = get_instance($folder.$class);
		if (is_object($inst))
		{
			if (method_exists($inst, $func))
			{
				return $inst->$func($params);
			}
			else
			{
				$this->raise_error(ERR_ORB_MNOTFOUND,sprintf(E_ORB_METHOD_NOT_FOUND,$func,$class),true,$this->silent);
			}
		}
		else
		{
			$this->raise_error(ERR_ORB_NOCLASS,E_ORB_CLASS_UNDEF,true,$this->silent);
		}
	}

	////
	// !returns the session id for the rpc call 
	// params:
	// either login_obj or server must be specified
	// login_obj - the oid of the CL_AW_LOGIN object - the server is read from that
	// server - the server to use (no login)
	function get_remote_session($arr)
	{
		extract($arr);
		if ($login_obj)
		{
			$login = get_instance("remote_login");
			list($server, $cookie) = $login->login_from_obj($login_obj);
			$this->rpc_session_cookies[$server] = $cookie;
		}
		else
		{
			if ($server == "")
			{
				$this->raise_error(ERR_ORB_RPC_NO_SERVER, "No server defined for ORB RPC call!", true, false);
			}

			$login = get_instance("remote_login");
			$this->rpc_session_cookies[$server] = $login->handshake(array(
				"silent" => true,
				"host" => $server
			));
		}
		return array($server,$this->rpc_session_cookies[$server]);
	}

	////
	// !handles a rpc call - ie decodes the request and calls the right function, encodes returned data and returns the encoded data
	// params:
	//	method - request method, currently only xmlrpc is supported
	function handle_rpc_call($arr)
	{
		extract($arr);

		// now, catch all output
		ob_start();

		// load rpc handler
		$inst = get_instance("orb/".$method);
		if (!is_object($inst))
		{
			$this->raise_error(ERR_ORB_RPC_NO_HANDLER,"orb::handle_rpc_call - Could not load request handler for request method '".$method."'", true,false);
		}

		// decode request
		$request = $inst->decode_request();

		// do the method calling thing
		$orb_defs = $this->try_load_class($request["class"]);
	
		$params = $this->check_method_params($orb_defs, $request["params"], $request["class"], $request["action"]);

		if (!isset($orb_defs[$request["class"]][$request["action"]]))
		{
			$this->raise_error(ERR_ORB_MNOTFOUND,sprintf("No action with name %s defined in class %s! Malformed XML?",$request["action"],$request["class"]),true,$this->silent);
		}

		$ret = $this->do_local_call($orb_defs[$request["class"]][$request["action"]]["function"], $request["class"], $params,$orb_defs[$request["class"]]["___folder"]);

		$output = ob_get_contents();
		ob_end_clean();

		if ($output != "")
		{
			$this->raise_error(ERR_RPC_OUTPUT, "Output generated during RPC call! content: '$output'", true, false);
		}

		return $inst->encode_return_data($ret);
	}
}
?>
