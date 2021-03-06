<?php
/*
@classinfo  maintainer=kristo
*/

// tegeleb ORB requestide handlimisega
lc_load("automatweb");

class orb extends aw_template
{
	private $data;
	private $info;
	private $_tmp;

	function orb($args = array())
	{
		$this->init();
		if (!empty($args["class"]))
		{
			$this->process_request($args);
		}
	}

	////
	//! Konstruktor. Koik vajalikud argumendid antakse url-is ette
	//  why the hell did I put all the functionality into the constructor?
	// now I can't put other useful functions into this class and used them
	// without calling the instructor
	function process_request($args = array())
	{
		// peavad olema v2hemalt
		// a) class
		// b) action
		// c) vars (sisaldab vastavalt vajadusele kas $HTTP_GET_VARS-i voi $HTTP_POST_VARS-i

		// optional
		// d) silent. veateateid ei v2ljastata. caller peaks kontrollima return valuet,
		// kui see on false, siis oli viga.
		if (isset($args["class"]) and "periods" === $args["class"])
		{
			$args["class"] = "period";
		}

		if (!empty($args["vars"]["id"]) and $this->can("view", $args["vars"]["id"]))
		{
			$o = obj($args["vars"]["id"]);
			$inst = get_instance("class_base");
			$cfgform_id = $inst->get_cfgform_for_object(array(
				"obj_inst" => $o,
				"args" => array(
					"action" => "change",
					"cfgform" => isset($args["vars"]["cfgform"]) ? $args["vars"]["cfgform"] : null
				)
			));
		}

		$action = $args["action"];
		$class = $args["class"];
		$vars = $args["vars"];

		$fatal = true;
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
			$this->raise_error("ERR_ORB_NOCLASS",E_ORB_CLASS_UNDEF,true,$silent);
		}

		// damn, I'm smart
		if (is_oid($class))
		{
			//$class = "class_visualizer";
		}

		// laeme selle klassi siis
		$orb_defs = $this->try_load_class($class);

		$this->orb_defs = $orb_defs;

		$action = ($action) ? $action : $orb_defs[$class]["default"];

		$this->check_login(array("class" => $class,"action" => $action));

		// check access for this class. access is checked by the "add tree conf" object assigned to groups
		if (!($class === "users"))
		{
			if ($action === "new" || $action === "change" || $action === "delete")
			{
				$this->check_class_access($class);
			}
		}

		// if the action is found in one of the classes defined by
		// the extends attribute, it should know which class was really
		// requested.
		// classload($class);
//                $this->orb_class = new $class();

		// action defineeritud?
		if (!isset($action))
		{
			$this->raise_error("ERR_ORB_AUNDEF",E_ORB_ACTION_UNDEF,true,$silent);
		};

		// create an array of class names that should be loaded.
		//$cl2load = array($class);
		$cl2load = array();
		if (is_array($orb_defs[$class]["_extends"]))
		{
			$cl2load = array_merge($cl2load,$orb_defs[$class]["_extends"]);
		};

		$fun = isset($orb_defs[$class][$action]) ? $orb_defs[$class][$action] : NULL;
		// oh the irony
		if (!$fun && $action === "view")
		{
			$action = "change";
		}

		if (is_array($fun))
		{
			$found = true;
		}
		else
		{
			$found = false;
		}
		foreach($cl2load as $clname)
		{
			// not yet found
			if (!$found)
			{
				// only load if definitions for this class are
				// not yet loaded (master class)
				if (empty($_orb_defs[$clname]) && $clname !== "aw_template")
				{
					$_orb_defs = $this->try_load_class($clname);
				};
				$fun = isset($_orb_defs[$clname][$action]) ? $_orb_defs[$clname][$action] : false;

				// XXX: fallback to change for objects which do not have view action
				if ( ($action == "view") && (!is_array($fun)) )
				{
					$action = "change";
					$fun = isset($_orb_defs[$clname][$action]) ? $_orb_defs[$clname][$action] : NULL;
				};


				if (is_array($fun))
				{
					$found = true;
					// copy the function definition from the extended class to the called class
					// this way it works like real inheritance - all the other properties,
					// including which class is instantiated, come from the class that was called
					// and not the class in which the function was found
					$orb_defs[$class][$action] = $_orb_defs[$clname][$action];
				}
			}
		}

		// still not found?
		if (!$found)
		{
			$this->raise_error("ERR_ORB_CAUNDEF",sprintf(E_ORB_CLASS_ACTION_UNDEF,$action,$class),true,$silent);
		};

		// check acl
		$this->do_orb_acl_checks($orb_defs[$class][$action], $vars);

		// handle reforb
		if (isset($vars["reforb"]) && $vars["reforb"] == 1)
		{
			$t = $this->orb_class;
			$fname = $fun["function"];
			if (!method_exists($t,$fname))
			{
				$this->raise_error("ERR_ORB_MNOTFOUND",sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),true,$silent);
			}

			if (isset($orb_defs[$class][$action]["xmlrpc"]) and $orb_defs[$class][$action]["xmlrpc"] == 1)
			{
				$url = $this->do_orb_xmlrpc_call($orb_defs[$class][$action]["server"],$class,$action,$vars);
			}
			else
			{
				$t->set_opt("orb_class",&$this->orb_class);

				// reforbi funktsioon peab tagastama aadressi, kuhu edasi minna
				$url = $t->$fname($vars);
			}

			// ja tagasi main programmi
			$this->data = $url;
			return;
		}

		// loome parameetrite array
		$params = array();
		// orb on defineeritud XML-i kaudu
		if (isset($orb_defs[$class][$action]["all_args"]) && $orb_defs[$class][$action]["all_args"] == true)
		{
			$required = $orb_defs[$class][$action]["required"];
			// first check, whether all required arguments are set
			$_params = $_GET;
			foreach(safe_array($vars) as $k => $v)
			{
				$_params[$k] = $v;
			}
			foreach($required as $key => $val)
			{
				if (!isset($_params[$key]))
				{
					$this->raise_error("ERR_ORB_CPARM",sprintf(E_ORB_CLASS_PARM,$key,$action,$class),true,$silent);
				};
			};
			foreach($_params as $key => $val)
			{
				$this->validate_value(array(
					"type" => isset($orb_defs[$class][$action]["types"][$key]) ? $orb_defs[$class][$action]["types"][$key] : NULL,
					"name" => $key,
					"value" => $val,
				));
				$params[$key] = $val;
			};
		}
		else
		{
			if ($_SERVER["REQUEST_METHOD"] === "POST")
			{
				$params = $_POST;
			}
			// required arguments
			$required = $orb_defs[$class][$action]["required"];
			$optional = $orb_defs[$class][$action]["optional"];
			$defined = $orb_defs[$class][$action]["define"];
			$_r = new aw_array($required);
			foreach($_r->get() as $key => $val)
			{
				if (!isset($vars[$key]))
				{
					$this->raise_error("ERR_ORB_CPARM",sprintf(E_ORB_CLASS_PARM,$key,$action,$class),true,$silent);
				};

				$this->validate_value(array(
					"type" => isset($orb_defs[$class][$action]["types"][$key]) ? $orb_defs[$class][$action]["types"][$key] : null,
					"name" => $key,
					"value" => $vars[$key],
				));

				$params[$key] = $vars[$key];
			};

			//optional arguments
			$_o = new aw_array($optional);
			foreach($_o->get() as $key => $val)
			{
				if (!empty($vars[$key]))
				{
					$this->validate_value(array(
						"type" => isset($orb_defs[$class][$action]["types"][$key]) ? $orb_defs[$class][$action]["types"][$key] : null,
						"name" => $key,
						"value" => $vars[$key],
					));
					$params[$key] = $vars[$key];
				}
				else
				if (isset($orb_defs[$class][$action]["defaults"][$key]))
				{
					if ($orb_defs[$class][$action]["defaults"][$key] === "true")
					{
						$orb_defs[$class][$action]["defaults"][$key] = true;
					}
					else
					if ($orb_defs[$class][$action]["defaults"][$key] === "false")
					{
						$orb_defs[$class][$action]["defaults"][$key] = false;
					}
					$params[$key] = $orb_defs[$class][$action]["defaults"][$key];
				}
			};
			$params = array_merge($params,$defined);
		}
		if (isset($user))
		{
			$params["user"] = 1;
		};
		// there are some variables in the url that if present should be placed in all the url-s generated by AW
		$tracked_var_names = array("cal","date","trid");
		$tracked_vars = array();
		foreach($tracked_var_names as $tvar)
		{
			$tvar_val = aw_global_get($tvar);
			if ($tvar_val)
			{
				$tracked_vars[$tvar] = $tvar_val;
			};
		};

		$GLOBALS["tracked_vars"] = $tracked_vars;

		if (!empty($orb_defs[$class][$action]["xmlrpc"]))
		{
			$content = $this->do_orb_xmlrpc_call($orb_defs[$class][$action]["server"],$class,$action,$params);
		}
		else
		{
			//$t = new $class;
			// ja kutsume funktsiooni v2lja
			$t = $this->orb_class;
			if (method_exists($t, "set_opt"))
			{
				$t->set_opt("orb_class",&$this->orb_class);
			}

			$fname = $fun["function"];

			if (!method_exists($t,$fname))
			{
				$this->raise_error("ERR_ORB_MNOTFOUND",sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),$fatal,$silent);
			};
			// this is perhaps the single most important place in the code ;)
			$content = $t->$fname($params);
		}
		$this->data = $content;

		// kui klass teeb enda sisse $info nimelise array, ja kirjutab sinna mingit teksti, siis
		// see votab nad sealt v2lja ja caller saab get_info funktsiooni kaudu k2tte kogu vajaliku info.
		// no ntx aw sees on vaja kuidagi saada string aw index.tpl-i sisse pealkirjaks
		// ilmselt see pole koige lihtsam lahendus, but hey, it works
		if (isset($t->info) && is_array($t->info))
		{
			$this->info = $t->info;
		}
	}

	function validate_value($args = array())
	{
		if ($args["type"] == "int")
		{
			// check for http: in it and don't error if it is there
			if (strpos($args["value"], "http:") !== false)
			{
				die("silly robot!");
			}

			if (strpos($args["value"], "Result") !== false)
			{
				die("Silly robot!");
			}

			if (!is_numeric($args["value"]))
			{
				$this->raise_error("ERR_ORB_NINT",sprintf(E_ORB_NOT_INTEGER,$args["name"]),true,$this->silent);
			};
		};
	}

	function load_xml_orb_def($class)
	{
		$fc = get_instance("cache");
		$fname = "/xml/orb/$class.xml";
		if(function_exists("get_file_version") && is_file(AW_DIR .($fqfn_version = get_file_version($fname))))
		{
			$fname = $fqfn_version;
		}
		$fc->get_cached_file(array(
			"fname" => $fname,
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

		// konteinerite tyybid
		$containers = array("class","action","function","arguments");

		// argumentide tyybid
		$argtypes = array("optional","required","define");

		// argumentide andmetyybid (int, string, whatever)
		$types = array();

		// ja siia moodustub loplik struktuur
		$orb_defs = array();
		foreach($values as $key => $val)
		{
			// parajasti t88deldava tag-i nimi
			$tag = $val["tag"];

			// on kas tyhi, "open", "close" voi "complete".
			$tagtype = $val["type"];

			// tagi parameetrid, array
			$attribs = isset($val["attributes"]) ? $val["attributes"] : array();

			// kui tegemist on n8 "konteiner" tag-iga, siis...
			if (in_array($tag,$containers))
			{

				if ((sizeof($attribs) > 0) && in_array($tagtype,array("open","complete")))
				{
					$$tag = $attribs["name"];

					if ("action" === $tag)
					{
						if (!empty($attribs["nologin"]))
						{
							$orb_defs[$class][$attribs["name"]]["nologin"] = 1;
						};
						if (!empty($attribs["is_public"]))
						{
							$orb_defs[$class][$attribs["name"]]["is_public"] = 1;
						};
						if (!empty($attribs["is_content"]))
						{
							$orb_defs[$class][$attribs["name"]]["is_content"] = 1;
						};
						if (!empty($attribs["all_args"]))
						{
							$orb_defs[$class][$attribs["name"]]["all_args"] = true;
						};
						if (!empty($attribs["caption"]))
						{
							$orb_defs[$class][$attribs["name"]]["caption"] = $attribs["caption"];
						};
						if (!empty($attribs["default"]))
						{
							$orb_defs[$class]["default"] = $attribs["name"];
						}
					}

					if ($tag === "function")
					{
						$orb_defs[$class][$action][$tag] = $$tag;
						// initsialiseerime need arrayd
						$orb_defs[$class][$action]["required"] = array();
						$orb_defs[$class][$action]["optional"] = array();
						$orb_defs[$class][$action]["define"] = array();
						$orb_defs[$class][$action]["types"] = array();

						// default values for optional arguments
						$orb_defs[$class][$action]["defaults"] = array();

						if (!isset($attribs["xmlrpc"]) && isset($xmlrpc_defs["xmlrpc"]))
						{
							$orb_defs[$class][$action]["xmlrpc"] = $xmlrpc_defs["xmlrpc"];
						}

						if (!isset($attribs["xmlrpc"]) && isset($xmlrpc_defs["server"]))
						{
							$orb_defs[$class][$action]["server"] = $xmlrpc_defs["server"];
						}

						// default action
						if (isset($attribs["default"]) && $attribs["default"])
						{
							$orb_defs[$class]["default"] = $action;
						};
					}
					elseif ($tag === "class")
					{
						// klassi defauldid. kui funktsiooni juures pole, pannakse need
						if (isset($attribs["xmlrpc"]))
						{
							$xmlrpc_defs["xmlrpc"] = $attribs["xmlrpc"];
						};
						if (isset($attribs["server"]))
						{
							$xmlrpc_defs["server"] = $attribs["server"];
						};
						if (isset($attribs["extends"]))
						{
							$extends = explode(",",$attribs["extends"]);
							$orb_defs[$class]["_extends"] = $extends;
						};
						if (isset($attribs["folder"]))
						{
							$orb_defs[$class]["___folder"] = $attribs["folder"];
						};
					}
				}
				elseif ($tagtype === "close")
				{
					$$tag = "";
				};
			}

			// kui leidsime argumenti m22rava tag-i, siis ...
			if (in_array($tag,$argtypes))
			{
				// kontroll, just in case
				if ($tagtype === "complete")
				{
					if ($tag === "define")
					{
						$val = $attribs["value"];
					}
					else
					{
						$val = 1;
					}

					$orb_defs[$class][$action][$tag][$attribs["name"]] = $val;
					if (isset($attribs["type"]))
					{
						$orb_defs[$class][$action]["types"][$attribs["name"]] = $attribs["type"];
					}

					if(isset($attribs["class_id"]))
					{
						$orb_defs[$class][$action]["class_ids"][$attribs["name"]] = explode(",", $attribs["class_id"]);
					}

					if (isset($attribs["default"]))
					{
						$orb_defs[$class][$action]["defaults"][$attribs["name"]] = $attribs["default"];
					}

					if (isset($attribs["acl"]))
					{
						$orb_defs[$class][$action]["acl"][$attribs["name"]] = $attribs["acl"];
					}
				}
			}
		}; // foreach;
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
			$auth = get_instance("core/users/auth/auth_config");
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
		if (is_oid($class))
		{
			$ret = array();
			$ret[$class]["_extends"][0] = "class_base";
			$ret[$class]["___folder"] = "designedclass";
			$cl = new object($class);
			if ($cl->prop("from_existing_class") == 1)
			{
				$gen_class_name = basename($GLOBALS["cfg"]["classes"][$cl->prop("reg_class_id")]["orig_file"]);
			}
			else
			{
				$gen_class_name = strtolower(preg_replace("/\s/","_",$cl->name()));
			}
			$GLOBALS["gen_class_name"] = $gen_class_name;
			//print "loading numeric class";
		}
		else
		{
			if (!is_readable(AW_DIR."xml/orb/{$class}.xml") && !is_readable(aw_ini_get("site_basedir")."/xml/orb/{$class}.xml"))

			{
				$this->raise_error("ERR_ORB_NOTFOUND",sprintf(E_ORB_CLASS_NOT_FOUND,$class),true,$this->silent);
			}
			$ret = $this->load_xml_orb_def($class);
		}

		if (isset($ret[$class]["_extends"]))
		{
			$extname = $ret[$class]["_extends"][0];
			$tmp = $this->load_xml_orb_def($extname);
			$ret[$class] = array_merge(safe_array(isset($tmp[$extname]) ? $tmp[$extname] : null ),safe_array($ret[$class]));

			//$ret = array_merge($tmp[$extname],$ret);
		};

		// try and figure out the folder for this class
		$folder = "";

		if (isset($ret[$class]["___folder"]))
		{
			$folder = $ret[$class]["___folder"]."/";
		}


		// laeme selle klassi siis
		classload($folder.$class);

		if (empty($gen_class_name) && !class_exists($class))
		{
			// try without folder for compatibility
			classload($class);

			if (!class_exists($class))
			{
				$this->raise_error("ERR_ORB_NOTFOUND",sprintf(E_ORB_CLASS_NOT_FOUND,$class),true,$this->silent);
			}
		};

		if (!isset($this->orb_class))
		{
			//print "trying to get instance $folder $class<br>";
			if ($folder === "designedclass/")
			{
				$folder = "";
			}
			$this->orb_class = get_instance($folder.$class);
			unset($GLOBALS["gen_class_name"]);
			//print "got";
			//var_dump($this->orb_class);
		};

		// FIXME: we should cache that def instead of parsing xml every time
		return $ret;

	}

	function do_orb_acl_checks($act, $vars)
	{
		if (isset($act["acl"]) && is_array($act["acl"]))
		{
			foreach($act["acl"] as $varname => $varacl)
			{
				if (isset($vars[$varname]))
				{
					$varvalue = (int) $vars[$varname];
					if ($varvalue)
					{
						$aclarr = explode(";", $varacl);
						foreach($aclarr as $aclid)
						{
							if (strpos($varvalue, "http") !== false)
							{
								die("silly robot!");
							}

							if (!$this->can($aclid, $varvalue))
							{
								$this->raise_error("ERR_ACL", "ORB:Teil puudub $aclid-&otilde;igus objektile id-ga $varvalue!", true, false);
							}

							if(isset($act["class_ids"][$varname]) && is_array($act["class_ids"][$varname]))
							{
								$true = false;
								$obj = obj($varvalue);
								foreach($act["class_ids"][$varname] as $val)
								{
									if($obj->class_id() == constant($val))
									{
										$true = true;
										break;
									}
								}
								if(!$true)
								{
									error::raise(array(
										"id" => "ERR_ORB_WRONG_CLASS",
										"msg" => $vars["class"]."::".$vars["action"].": class id of argument ".$varname." is not ".implode(" or ", $act["class_ids"][$varname]),
									));
								}
							}
						}
					}
				}
			}
		}
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
//		$arr['server'] = isset($arr['server']) ? str_replace('http://','',$arr['server']) : NULL;

		extract($arr);

		$this->fatal = true;
		$this->silent = false;

		if (!isset($class))
		{
			$this->raise_error("ERR_ORB_NOCLASS",E_ORB_CLASS_UNDEF,true,$this->silent);
		};

		if (!isset($action))
		{
			$this->raise_error("ERR_ORB_AUNDEF",E_ORB_ACTION_UNDEF,true,$this->silent);
		};

		// get orb defs for the class

		// check params
		if (!isset($method) || (isset($method) && ($method == "local")))
		{
			$orb_defs = $this->try_load_class($class);
			$params = $this->check_method_params($orb_defs, $params, $class, $action);
			$arr["params"] = $params;
			$this->do_orb_acl_checks($orb_defs[$class][$action], $params);
		}


		// do the call
		if (!isset($method) || (isset($method) && ($method == "local")))
		{
			// local call
			$___folder = isset($orb_defs[$class]["___folder"]) ? $orb_defs[$class]["___folder"] : NULL;
			$data = $this->do_local_call($orb_defs[$class][$action]["function"], $class, $params, $___folder);
		}
		else
		{
			// log in if necessary or get the existing session for rpc call
			list($arr["remote_host"], $arr["remote_session"]) = $this->get_remote_session($arr);

			// load rpc handler
			$inst = get_instance("core/orb/".$method);
			if (!is_object($inst))
			{
				$this->raise_error("ERR_ORB_RPC_NO_HANDLER",sprintf(t("Could not load request handler for request method '%s'"), $method), true,$this->silent);
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
						$this->raise_error("ERR_ORB_CPARM",sprintf(E_ORB_CLASS_PARM,$key,$action,$class),true,$this->silent);
					};

					$vartype = $orb_defs[$class][$action]["types"][$key];
					if ($vartype == "int")
					{
						if (!is_numeric($params[$key]))
						{
							$this->raise_error("ERR_ORB_NINT",sprintf(E_ORB_NOT_INTEGER,$key),true,$this->silent);
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
					if (!empty($params[$key]))
					{
						if ( ($vartype == "int") && ($params[$key] != sprintf("%d",$vars[$key])) )
						{
							$this->raise_error("ERR_ORB_NINT",sprintf(E_ORB_NOT_INTEGER,$key),true,$this->silent);
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
				$this->raise_error("ERR_ORB_MNOTFOUND",sprintf(E_ORB_METHOD_NOT_FOUND,$func,$class),true,$this->silent);
			}
		}
		else
		{
			$this->raise_error("ERR_ORB_NOCLASS",E_ORB_CLASS_UNDEF,true,$this->silent);
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
			$login = get_instance("protocols/file/http");
			list($server, $cookie) = $login->login_from_obj($login_obj);
			$this->rpc_session_cookies[$server] = $cookie;
		}
		else
		{
			if ($server == "")
			{
				$this->raise_error("ERR_ORB_RPC_NO_SERVER", "No server defined for ORB RPC call!", true, false);
			}

			$login = get_instance("protocols/file/http");
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
		$inst = get_instance("core/orb/".$method);
		if (!is_object($inst))
		{
			$this->raise_error("ERR_ORB_RPC_NO_HANDLER",sprintf(t("orb::handle_rpc_call - Could not load request handler for request method '%s'"), $method), true,false);
		}

		// decode request
		$request = $inst->decode_request();

		// do the method calling thing
		$orb_defs = $this->try_load_class($request["class"]);

		$params = $this->check_method_params($orb_defs, $request["params"], $request["class"], $request["action"]);

		if (!isset($orb_defs[$request["class"]][$request["action"]]))
		{
			$this->raise_error("ERR_ORB_MNOTFOUND",sprintf("No action with name %s defined in class %s! Malformed XML?",$request["action"],$request["class"]),true,$this->silent);
		}

		$ret = $this->do_local_call($orb_defs[$request["class"]][$request["action"]]["function"], $request["class"], $params,$orb_defs[$request["class"]]["___folder"]);


		$output = ob_get_contents();
		ob_end_clean();

		if ($output != "")
		{
			$this->raise_error("ERR_RPC_OUTPUT", "Output generated during RPC call! content: '$output'", true, false);
		}

		return $inst->encode_return_data($ret);
	}

	////
	// !Returns a list of all defined ORB classes
	// interface(string) - name of the interface file
	function get_classes_by_interface($args = array())
	{
		if (empty($args["interface"]))
		{
			// wuh los, man?
			return false;
		};

		switch($args["interface"])
		{
			case "content":
				$ifile = "content.xml";
				$flag = "is_content";
				break;

			case "interface":
			default:
				$ifile = "public.xml";
				$flag = "is_public";
		};

		// klassi definitsioon sisse
		$xmldef = $this->get_file(array(
			"file" => $this->cfg["basedir"] . "/xml/interfaces/$ifile"
		));

		// loome parseri
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		// XXX: I need some kind of error checking here!! -- duke
		xml_parse_into_struct($parser,$xmldef,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);

		$pclasses = array();

		foreach($values as $key => $val)
		{
			$attr = isset($val["attributes"]) ? $val["attributes"] : array();
			if ( ($val["tag"] == "class") && ($val["type"] == "complete") && $attr['id'] != '')
			{
				$pm = $this->get_methods_by_flag(array(
					"flag" => $flag,
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

	////
	// !Returns a list of methods inside a class matching a flag
	// added a param: if no_id is set to true, then it will not added that ugly class id to key -- ahz
	function get_methods_by_flag($args = array())
	{
		extract($args);
		$orbclass = get_instance("core/orb/orb");
		$orb_defs = $orbclass->load_xml_orb_def($id);
		$methods = array();
		foreach(safe_array($orb_defs[$id]) as $key => $val)
		{
			if (is_array($val) && isset($val[$flag]))
			{
				$caption = isset($val["caption"]) ? $val["caption"] : $val["function"];
				$methods[(($no_id) ? "" : ($id . "/")) . $key] = $name . " / " . $caption;
			}
		};

		return $methods;
	}

	/** Returns a list of all actions defined for a class
		@attrib api=1 params=name

		@param class required type=string
			The class to return actions for
	**/
	function get_class_actions($arr)
	{
		$methods = array();
		$cur_class = $arr["class"];

		do
		{
			$orb_defs = $this->load_xml_orb_def($cur_class);
			foreach(safe_array($orb_defs[$cur_class]) as $key => $val)
			{
				if ($key === "_extends" || $key === "___folder")
				{
					continue;
				}
				$methods[$key] = $val["function"];
			};
		}
		while(($cur_class = $orb_defs[$cur_class]["_extends"][0]) != "");

		return $methods;
	}

	function get_public_method($args = array())
	{
		extract($args);
		$orbclass = get_instance("core/orb/orb");
		$orb_defs = $orbclass->load_xml_orb_def($id);
//		echo "id = $id , action = $action , orb_defs = <pre>", var_dump($orb_defs),"</pre> <br />";
		if ($action === "default")
		{
			$action = $orb_defs[$id]["default"];
		}

		$meth = $orb_defs[$id][$action];
		$meth["values"] = array();

		if ($orb_defs[$id]["___folder"] != "")
		{
			$fld = $orb_defs[$id]["___folder"]."/";
		}
		$cl = get_instance($fld.$id);
		$ar = array();
		if ($id === "document")
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
				if ($this->can("view", aw_global_get("section")))
				{
					$tmp = obj(aw_global_get("section"));
					if ($tmp->class_id() == CL_DOCUMENT)
					{
						$tmp = obj($tmp->parent());
					}
					$meth["values"]["parent"] = $tmp->id();
				}
			}
		};
		if ($id === "doc")
		{
			$cl = get_instance("document");
			if ($cl->get_opt("cnt_documents") == 1)
			{
				$meth["values"]["id"] = $cl->get_opt("shown_document");
			}
			$meth["values"]["period"] = aw_global_get("act_per_id");
			if ($action === "change" && $cl->get_opt("shown_document"))
			{
				$meth["values"]["id"] = $cl->get_opt("shown_document");
			}
			if ($action === "new")
			{
				$meth["values"]["parent"] = aw_global_get("section");
			}
		};
		if ($id === "menu")
		{
			if ($this->can("view", aw_global_get("section")))
			{
				$so = obj(aw_global_get("section"));
				if ($so->class_id() != CL_MENU)
				{
					$so = obj($so->parent());
				}
				$meth["values"]["parent"] = $so->id();
				$meth["values"]["id"] = $so->id();
			}
		}
		else
		if ($id === "file")
		{
			$meth["values"]["parent"] = aw_global_get("section");
			$meth["values"]["id"] = aw_global_get("section");
		}
		//echo $obj;
		if($id === "method")
		{
			if($obj)
			{
				$meth["values"]["mid"] = $obj;
			}
		}
		return $meth;
	}

	function check_class_access($class)
	{
		$atc = get_instance("admin/add_tree_conf");
		$conf = $atc->get_current_conf();
		if (!$conf)
		{
			// by default you can add all types of objects
			return true;
		}

		error::raise_if(!$atc->can_access_class(obj($conf), $class),array(
			"id" => "ERR_ACL",
			"msg" => sprintf(t("orb::check_class_access(%s): no permissions to access the class! (denied by %s)"), $class, $conf)
		));
	}
}
?>
