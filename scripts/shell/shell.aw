<?php
// $Header: /home/cvs/automatweb_dev/scripts/shell/shell.aw,v 1.1 2003/12/14 14:11:48 duke Exp $
echo "Welcome to AW shell!\n";
// now I have to figure out a way to execute this from site directory.
// so that I can actually parse an INI file

// print out working directory.
$cwd = getcwd();
$inifile = $cwd . "/aw.ini";
if (!file_exists($inifile))
{
	die("No aw.ini found in current directory - $cwd\n");
}
else
{
	print "Using $inifile\n";
	include("public/const.aw");
	classload("defs","aw_template","class_base");
	aw_startup();
};
$continue = true;
while ($continue)
{
	$str = readline("\n\nAW> ");
	readline_add_history($str);
	if ($str == "quit")
	{
		$continue = false;
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
		eval($str);
	};
};
echo "bye then ..\n";
?>
