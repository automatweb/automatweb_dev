<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/connect.aw,v 2.3 2002/01/31 00:27:14 kristo Exp $
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
