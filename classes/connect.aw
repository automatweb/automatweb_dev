<?php
global $driver;
global $db_host;
global $db_base;
global $db_user;
global $db_pass;
include("$classdir/$driver.aw");
global $db_core;
$db_core = new db_connector;
$db_core->db_connect($db_host,$db_base,$db_user,$db_pass);
?>
