<?php
// $Header: /home/cvs/automatweb_dev/scripts/prop/collect.aw,v 1.3 2002/11/26 16:36:34 duke Exp $
$basedir = realpath("../../");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
$collector = get_instance("analyzer/propcollector");
$collector->run();
?>
