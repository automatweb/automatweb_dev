<?php
// tegeleb ORB requestide handlimisega
classload("aw_template","defs");
lc_load("automatweb");
class orb extends aw_template {
	////
	//! Konstruktor. Koik vajalikud argumendid antakse url-is ette
	var $data;
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
			
		$fatal = true;

		$this->db_init();
		$this->data = "";

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
			// required arguments
 
			//
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
		return;
	}

	function get_data()
	{
		return $this->data;
	}
}
?>
