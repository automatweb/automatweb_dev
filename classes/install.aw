<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/install.aw,v 2.1 2002/04/10 01:25:18 duke Exp $
// install.aw - used for creating and configuring new sites
class install extends aw_template
{
	////
	// !Konstruktor
	function install()
	{
		$this->init("install");
	}

	function add_site($args = array())
	{
		// I don't get it. for some reason I don't get that data through ORB.
		// the array keys are there, but the values not
		extract($args);
		$this->read_adm_template("add_site.tpl");
		$this->vars($args);
		$ok = false;
		$this->vars(array(
			"awdbhost" => ($args["awdbhost"]) ? $args["awdbhost"] : "hell",
			"default_user" => ($args["default_user"]) ? $args["default_user"] : "duke",
			"default_pass" => ($args["default_pass"]) ? $args["default_pass"] : "moz211",
			"reforb" => $this->mk_reforb("add_site",array("no_reforb" => 1)),
		));
		$awdbname = str_replace(".","_",$awdbname);
		if ($awdbname && not(preg_match("/^\w*$/",$awdbname)))
		{
			$message = "dbname contains illegal characters!";
			print "wrong!";
		}
		else
		{
			$ok = ($awdbname) ? true : false;
		};
		$awdbname = str_replace("_",".",$awdbname);
		$this->vars(array(
			"message" => $message,
		));

		if ($ok)
		{
			$fp = popen("/www/automatweb_dev/scripts/install/install","w");
			if (not($fp))
			{
				print "couldn't fork install script, check the permissions!";
				return false;
			};
			fputs($fp,$awdbname . "\n");
			fputs($fp,$awdbhost . "\n");
			fputs($fp,$awdbuser . "\n");
			fputs($fp,$awdbpass . "\n");
			fputs($fp,$default_user . "\n");
			fputs($fp,$default_pass . "\n");
			pclose($fp);
			header("Location: http://$awdbname");
			exit;
		};

		return $this->parse();
	}

	function submit_add_site($args = array())
	{
		extract($args);
		print "<pre>";
		print_r($args);
		print "</pre>";
		print "executing install script!";
		$result = system("/www/automatweb_dev/scripts/install/install",$retcode);
		print "result = $result<br>";
		print "retcode = $retcode<br>";
	}

};
?>
