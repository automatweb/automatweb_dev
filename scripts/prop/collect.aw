<?php
// $Header: /home/cvs/automatweb_dev/scripts/prop/collect.aw,v 1.2 2002/11/26 16:22:32 duke Exp $
include("../../init.aw");
init_config(array("ini_files" => array("../../aw.ini")));
classload("defs");
classload("aw_template");
$collector = get_instance("analyzer/propcollector");
$collector->run();
?>
