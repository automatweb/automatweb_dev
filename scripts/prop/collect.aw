<?php
// $Header: /home/cvs/automatweb_dev/scripts/prop/collect.aw,v 1.1 2002/10/29 12:16:26 duke Exp $
$basedir = "/www/automatweb_dev";
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
$collector = get_instance("analyzer/propcollector");
$collector->run();
?>
