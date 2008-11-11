<?php

$basedir = realpath(".");
include($basedir . "/automatweb.aw");

automatweb::start();
include AW_DIR . "const" . AW_FILE_EXT;
$awt = new aw_timer();
aw_global_set("no_db_connection", 1);
aw_ini_set("baseurl", "automatweb");
$scanner = new scanner();
$scanner->run();
automatweb::shutdown();

?>
