<?php
$basedir = realpath("..");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini", $argv[1])));
classload("defs");
classload("aw_template","timer");
classload("core/obj/object", "core/error");
require_once 'PHPUnit.php';
$awt = new aw_timer;

if ($argc < 3)
{
	$argc = 3;
	$argv[] = "classes";
}
for($i = 2; $i < $argc; $i++)
{
	$path = realpath(".")."/".$argv[$i];

	echo "running tests in ".$argv[$i]."... \n\n";

	// get files from folder
	$p = get_instance("core/docgen/parser");
	$files = array();
	$p->_get_class_list($files, $path);

	foreach($files as $filename)
	{
		require_once($filename);
	
		$suite  = new PHPUnit_TestSuite(basename($filename,".aw")."_test");
		$result = PHPUnit::run($suite);
		echo $result -> toString();
	}
}
echo "\n";


function __disable_err()
{
	aw_global_set("__from_raise_error", 1);
}

function __is_err()
{
	aw_global_set("__from_raise_error", 0);
	if ($GLOBALS["aw_is_error"] == 1)
	{
		$GLOBALS["aw_is_error"] = 0;
		return true;
	}
	return false;
}
?>