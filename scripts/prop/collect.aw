<?php
// $Header: /home/cvs/automatweb_dev/scripts/prop/collect.aw,v 1.4 2003/07/11 15:58:53 duke Exp $
//$basedir = realpath("../../");
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
$collector = get_instance("analyzer/propcollector");
$collector->run();
?>
