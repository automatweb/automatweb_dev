<?php
$basedir = "/www/automatweb_dev";
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
$an = get_instance("analyzer/mysql_analyzer");
$an->get_status($argv[1]);
?>
