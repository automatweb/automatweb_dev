<?php
// $Header: /home/cvs/automatweb_dev/classes/sys.aw,v 2.0 2001/05/30 00:29:34 duke Exp $
// system.aw - various system related functions
global $orb_defs;
$orb_defs["system"] = "xml";

class sys {
	function sys($args = array())
	{
		
	}

	////
	// !Calls the check_enviroment methods of all listed modules
	// could be handy to check whether the system is configured correctly
	function check($args = array())
	{
		// modules whose check_environment functions are called
		$modules = array("file");
		$messages = "";
		foreach($modules as $module)
		{
			$t = new $module;
			$_msg = $t->check_environment();
			if ($_msg)
			{
				$messages .= "Module '$module' reported the following errors<br>\n";
				$messages .= $_msg;
				$messages .= "<br>\n<br>\n";
			};
		};
		if ($messages)
		{
			print $messages;
			print "Please correct the above errors before proceeding<br>";
		}
		else
		{
			print "Everything seems to be ok!";
		};
	}
};
?>
