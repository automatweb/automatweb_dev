<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/orb.aw,v 2.23 2002/11/01 13:01:36 kristo Exp $
// tegeleb ORB requestide handlimisega
classload("aw_template","defs");
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
			$this->raise_error(ERR_ORB_NOCLASS,E_ORB_CLASS_UNDEF,$fatal,$silent);
			bail_out();
		};
		
		// laeme selle klassi siis
		$orb_defs = $this->try_load_class($class);

		$this->orb_defs = $orb_defs;

		$action = ($action) ? $action : $orb_defs[$class]["default"];

		$this->check_login(array("class" => $class,"action" => $action));

		// action defineeritud?
		if (!isset($action))
		{
			$this->raise_error(ERR_ORB_AUNDEF,E_ORB_ACTION_UNDEF,$fatal,$silent);
			bail_out();
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
			if (not($found))
			{
				// only load if definitions for this class are
				// not yet loaded (master class)
				if (not($orb_defs[$clname]))
				{
					$orb_defs = $this->try_load_class($clname);
				};

				$fun = $orb_defs[$clname][$action];

				if (is_array($fun))
				{
					$found = true;
					$class = $clname;
				};
			};

		};

		// still not found?
		if (not($found))
		{
			$this->raise_error(ERR_ORB_CAUNDEF,sprintf(E_ORB_CLASS_ACTION_UNDEF,$action,$class),$fatal,$silent);
			bail_out();

		};

		if (isset($vars["reforb"]) && $vars["reforb"] == 1)
		{
			$t = new $class;
			$fname = $fun["function"];
			if (!method_exists($t,$fname))
			{
				$this->raise_error(ERR_ORB_MNOTFOUND,sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),$fatal,$silent);
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
					$this->raise_error(ERR_ORB_CPARM,sprintf(E_ORB_CLASS_PARM,$key,$action,$class),$fatal,$silent);
					bail_out();
				};

				$vartype = $orb_defs[$class][$action]["types"][$key];
				if ($vartype == "int")
				{
					if ($vars[$key] != sprintf("%d",$vars[$key]))
					{
						$this->raise_error(ERR_ORB_NINT,sprintf(E_ORB_NOT_INTEGER,$key),$fatal,$silent);
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
						$this->raise_error(ERR_ORB_NINT,sprintf(E_ORB_NOT_INTEGER,$key),$fatal,$silent);
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
				$this->raise_error(ERR_ORB_MNOTFOUND,sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),$fatal,$silent);
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

	////
	// !laeb XML failist orbi definitsiooni
	function load_xml_orb_def($class)
	{
		$basedir = $this->cfg["basedir"];
		// klassi definitsioon sisse

		if (not($xmldef))
		{
			$xmldef = $this->get_file(array(
				"file" => "$basedir/xml/orb/$class.xml"
			));
		};

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

	////
	// !Checks whether a resource requires login and if so, asks for the password
	// also, remembers the requesterd url so we can redirect back there after the
	// login
	function check_login($args = array())
	{
		extract($args);
		if ((!aw_global_get("uid")) && (!isset($this->orb_defs[$class][$action]["nologin"])))
		{
			classload("auth");
			$auth = new auth();
			print $auth->show_login();
			// dat sucks
			exit;

			//classload("config");
			//$c = new db_config;
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
			$this->raise_error(ERR_ORB_NOTFOUND,sprintf(E_ORB_CLASS_NOT_FOUND,$class),$this->fatal,$this->silent);
			bail_out();
		}

		$ret = $this->load_xml_orb_def($class);

		// try and figure out the folder for this class 
		$folder = "";

		if ($ret[$class]["___folder"])
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
				$this->raise_error(ERR_ORB_NOTFOUND,sprintf(E_ORB_CLASS_NOT_FOUND,$class),$this->fatal,$this->silent);
				bail_out();
			}
		};
		
		// FIXME: we should cache that def instead of parsing xml every time
		return $ret;

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
		extract($arr);

		$this->fatal = true;
		$this->silent = false;

		if (!isset($class))
		{
			$this->raise_error(ERR_ORB_NOCLASS,E_ORB_CLASS_UNDEF,$this->fatal,$this->silent);
			bail_out();
		};

		if (!isset($action))
		{
			$this->raise_error(ERR_ORB_AUNDEF,E_ORB_ACTION_UNDEF,$this->fatal,$this->silent);
			bail_out();
		};

		// get orb defs for the class
		$orb_defs = $this->try_load_class($class);

			// check parameters
		$params = $this->check_method_params($orb_defs, $params, $class, $action);
		$arr["params"] = $params;

		// do the call
		if (!$method || $method == "local")
		{
			// local call
			$data = $this->do_local_call($orb_defs[$class][$action]["function"], $class, $params, $orb_defs[$class]["folder"]);
		}
		else
		{
			// log in if necessary or get the existing session for rpc call
			list($arr["remote_host"], $arr["remote_session"]) = $this->get_remote_session($arr);

			// load rpc handler
			$inst = get_instance("orb/".$method);
			if (!is_object($inst))
			{
				$this->raise_error(ERR_ORB_RPC_NO_HANDLER,"Could not load request handler for request method '".$method."'", $this->fatal,$this->silent);
				bail_out();
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
						$this->raise_error(ERR_ORB_CPARM,sprintf(E_ORB_CLASS_PARM,$key,$action,$class),$this->fatal,$this->silent);
						bail_out();
					};

					$vartype = $orb_defs[$class][$action]["types"][$key];
					if ($vartype == "int")
					{
						if (((string)($params[$key])) != ((string)((int)$params[$key])))
						{
							$this->raise_error(ERR_ORB_NINT,sprintf(E_ORB_NOT_INTEGER,$key),$this->fatal,$this->silent);
							bail_out();
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
							$this->raise_error(ERR_ORB_NINT,sprintf(E_ORB_NOT_INTEGER,$key),$this->fatal,$this->silent);
							bail_out();
						};
						$ret[$key] = $params[$key];
					}
					else
					if (isset($orb_defs[$class][$action]["defaults"][$key]))
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
				$this->raise_error(ERR_ORB_MNOTFOUND,sprintf(E_ORB_METHOD_NOT_FOUND,$func,$class),$this->fatal,$this->silent);
				bail_out();
			}
		}
		else
		{
			$this->raise_error(ERR_ORB_NOCLASS,E_ORB_CLASS_UNDEF,$this->fatal,$this->silent);
			bail_out();
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

		// load rpc handler
		$inst = get_instance("orb/".$method);
		if (!is_object($inst))
		{
			$this->raise_error(ERR_ORB_RPC_NO_HANDLER,"orb::handle_rpc_call - Could not load request handler for request method '".$method."'", true,false);
			bail_out();
		}

		// decode request
		$request = $inst->decode_request();

		// do the method calling thing
		$orb_defs = $this->try_load_class($request["class"]);
		$params = $this->check_method_params($orb_defs, $request["params"], $request["class"], $request["action"]);

		$ret = $this->do_local_call($orb_defs[$request["class"]][$request["action"]]["function"], $request["class"], $params, $orb_defs[$request["class"]]["folder"]);

		return $inst->encode_return_data($ret);
	}
}
?>