<?php
// $Header: /home/cvs/automatweb_dev/classes/sys.aw,v 2.4 2001/07/26 16:49:57 duke Exp $
// system.aw - various system related functions
lc_load("syslog");
global $orb_defs;
$orb_defs["sys"] = "xml";

class sys extends aw_template
{
	function sys($args = array())
	{
		$this->db_init();
		global $lc_syslog;
		if (is_array($lc_syslog))
		{
			$this->vars($lc_syslog);
	}
	}

	////
	// !Calls the check_enviroment methods of all listed modules
	// could be handy to check whether the system is configured correctly
	function check($args = array())
	{
		// modules whose check_environment functions are called
		$modules = array(
			"file",
			"form_output",
			"accessmgr",
			"acl",
			"acl_base",
			"variables",
			"users_user",
			"users",
			"table",
			"syslog",
			"style",
			"stat",
			"shop_stat",
			"shop_item",
			"shop_eq"
		);
		$messages = "";
		foreach($modules as $module)
		{
			echo "checking $module ... <br>";
			flush();
			classload($module);
			$t = new $module;
			$_msg = $t->check_environment(&$this,$args["fix"]);
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
		exit;
	}

	////
	// !this function takes any number of table definition arrays and checks if the tables really exist in the database
	// if $fix is true, then it also creates the missing tables and tries to fix the broken ones
	function check_db_tables($arr,$fix=false)
	{
		foreach($arr as $op_table)
		{
			$cur_table = $this->db_get_table($op_table["name"]);

			if ($cur_table != $op_table)
			{
				$s1 = $this->db_print_table($op_table); 
				$s2 = $this->db_print_table($cur_table); 
				if (!$fix)
				{
					return "Table ".$op_table["name"]." differs from correct version, must have: <br>".$s1."<br>got:<br>".$s2."<br>";
				}
				else
				{
					$this->db_sync_tables($op_table,$op_table["name"]);
					return "Table ".$op_table["name"]." differs from correct version, modified from: <br>".$s1."<br>to:<br>".$s2."<br>";
				}
			}
		lc_load("definition");}
	}

	////
	// !this function tries to read all the templates in $arr and complains if it cannot do that
	// it tries to find the templates in the automatweb folder
	function check_admin_templates($td, $arr)
	{
		global $basedir;
		$tpldir = $basedir . "/templates/".$td;
		$ret = "";
		foreach($arr as $tpl)
		{
			if (!(file_exists($tpldir."/".$tpl) && is_readable($tpldir."/".$tpl)))
			{
				$ret.="Cannot open template ".$tpldir."/".$tpl." <br>";
			}
		}
		return $ret;
	}

	////
	// !this function tries to read all the templates in $arr and complains if it cannot do that
	// it tries to find the templates in the site's folder
	function check_site_templates($td,$arr)
	{
		global $tpldir;
		$td=$tpldir."/".$td;
		$ret = "";
		foreach($arr as $tpl)
		{
			if (!(file_exists($td."/".$tpl) && is_readable($td."/".$tpl)))
			{
				$ret.="Cannot open template ".$td."/".$tpl." <br>";
			}
		}
		return $ret;
	}

	////
	// !this function checks if all the files are readable and exist
	function check_site_files($arr)
	{
		global $site_basedir;
		$ret = "";
		foreach($arr as $tpl)
		{
			if (!(file_exists($site_basedir."/public/".$tpl) && is_readable($site_basedir."/public/".$tpl)))
			{
				$ret.="Cannot find file ".$site_basedir."/public/".$tpl." <br>";
			}
		}
		return $ret;
	}

	////
	// !this function checks if all the xml defs are readable and exist
	function check_orb_defs($arr)
	{
		global $basedir;
		$ret = "";
		foreach($arr as $tpl)
		{
			if (!(file_exists($basedir."/xml/orb/".$tpl.".xml") && is_readable($basedir."/xml/orb/".$tpl.".xml")))
			{
				$ret.="Cannot find file ".$basedir."/xml/orb/".$tpl.".xml <br>";
			}
		}
		return $ret;
	}
};
?>
