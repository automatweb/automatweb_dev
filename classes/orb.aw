<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/orb.aw,v 2.6 2001/06/05 17:40:28 kristo Exp $
// tegeleb ORB requestide handlimisega
classload("aw_template","defs");
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

		$retval = true;

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
			if ($orb_defs[$class] == "xml")
			{
				$orb_defs = load_xml_orb_def($class);
			}
			else
			{
				$this->raise_error(sprintf(E_ORB_ORB_CLASS_UNDEF,$class),$fatal,$silent);
				bail_out();
			};
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



		// loome õige objekti
		$t = new $class;
 
		// leiame actionile vastava funktsiooni
		$fun = $orb_defs[$class][$action]; 
		
		// kas asi on defineeritud xml-is?
		$xml = $orb_defs[$class][$action]["xml"];
		if (!is_array($fun))
		{
			$this->raise_error(sprintf(E_ORB_CLASS_ACTION_UNDEF,$action,$class),$fatal,$silent);
			bail_out();
		};

		if ($vars["reforb"] == 1)
		{
			$fname = $fun["function"];
			if (!method_exists($t,$fname))
			{
				$this->raise_error(sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),$fatal,$silent);
				bail_out();
			}
 
			// reforbi funktsioon peab tagastama aadressi, kuhu edasi minna
			$url = $t->$fname($vars);
 
			// ja tagasi main programmi
			$this->data = $url;
			return;
		};

		// loome parameetrite array
		$params = array();
		if ($xml)
		{
			// orb on defineeritud XML-i kaudu
			if ($orb_defs[$class][$action]["all_args"] == true)
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
					$params[$key] = $vars[$key];
				};
	 
				//optional arguments
				foreach($optional as $key => $val)
				{
					$params[$key] = $vars[$key];
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
 
		// ja kutsume funktsiooni v2lja
		$fname = $fun["function"];
		if (!method_exists($t,$fname))
		{
		        $this->raise_error(sprintf(E_ORB_METHOD_NOT_FOUND,$action,$class),$fatal,$silent);
		};
		
		if ($user)
		{
			$params["user"] = 1;
		};
		$content = $t->$fname($params);
		$this->data = $content;
		// kui klass teeb enda sisse $info nimelise array, ja kirjutab sinna mingit teksti, siis
		// see votab nad sealt välja ja caller saab get_info funktsiooni kaudu kätte kogu vajaliku info.
		// no ntx aw sees on vaja kuidagi saada string aw index.tpl-i sisse pealkirjaks
		// ilmselt see pole koige lihtsam lahendus, but hey, it works
		if (is_array($t->info))
		{
			$this->info = $t->info;
		};
		return;
	}

	function get_data()
	{
		return $this->data;
	}

	function get_info()
	{
		return $this->info;
	}
}
?>
