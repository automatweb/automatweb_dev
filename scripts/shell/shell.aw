<?php
// $Header: /home/cvs/automatweb_dev/scripts/shell/shell.aw,v 1.4 2004/01/06 11:55:23 kristo Exp $
echo "Welcome to AW shell!\n";
// now I have to figure out a way to execute this from site directory.
// so that I can actually parse an INI file

// print out working directory.
$cwd = getcwd();
$inifile = $cwd . "/aw.ini";
$use_timer = false;
if (!file_exists($inifile))
{
	die("No aw.ini found in current directory - $cwd\n");
}
else
{
	include("public/const.aw");
	classload("defs","aw_template","class_base");
	aw_startup();
};
echo "Welcome to AW shell!\n";
print "Using $inifile\n";

classload("timer");
$awt = new aw_timer;

$continue = true;
while ($continue)
{
	$str = readline("\n\nAW> ");
	readline_add_history($str);
	if ($str == "quit")
	{
		$continue = false;
	}
	if ($str == "timer")
	{
		$use_timer = true;
		classload("timer");
		$awt = new aw_timer();
		print "Using timer from now on\n";
	}
	elseif ($str == "mysql")
	{
		$db_host = aw_ini_get("db.host");
		$db_user = aw_ini_get("db.user");
		$db_pass = aw_ini_get("db.pass");
		$db_base = aw_ini_get("db.base");
		// how do make that work?
		print "Dropping out to MySQL\n";
		system("mysql -h ${db_host} -u ${db_user} --password=${db_pass} ${db_base}");
		print "missed me?\n";
	}
	else
	{
		if ($use_timer)
		{
			$awt->start("shellcommand");
		};
		eval($str);
		echo "\ncmd took ".$awt->elapsed("shellcommand")." seconds \n";
	};
};
echo "bye then ..\n";
?>
