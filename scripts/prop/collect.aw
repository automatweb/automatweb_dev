<?php
// $Header: /home/cvs/automatweb_dev/scripts/prop/collect.aw,v 1.6 2008/04/18 07:36:54 kristo Exp $
//$basedir = realpath("../../");
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
$collector = get_instance("cfg/propcollector");
$collector->run();
?>
