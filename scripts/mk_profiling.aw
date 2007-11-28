<?php
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
aw_global_set("no_db_connection", true);

aw_set_exec_time(AW_LONG_PROCESS);
$anal = get_instance("core/aw_code_analyzer/parser");

// create a copy of the code tree and add enters to that
$new_name = dirname(aw_ini_get("basedir"))."/".basename(aw_ini_get("basedir"))."_profiled";
$cmd = "rm -rf $new_name";
echo "removing old profiled code from $new_name \n";
echo $cmd."\n";
$res = `$cmd`;

echo "making a copy of the current code\n";
$cmd = "cp -r ".aw_ini_get("basedir")." $new_name ";
echo $cmd."\n";
$res = `$cmd`;

echo "adding profiling calls to the code\n";
$files = array();
$anal->_get_class_list(&$files, $new_name);

foreach($files as $file)
{
echo "process $file \n";
	$anal->do_parse($file);
	$anal->add_enter_func($file);
}
?>
