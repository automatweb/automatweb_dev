<?php
// ----------------------------------
classload("aw_template");
session_name("automatweb");
session_start();

if (!$uid) {
	include("sorry.aw");
	exit;
};
$sf = new aw_template;
classload("users","defs");
$users = new users;
$users->touch(UID);

$sf->tpl_init("automatweb");
$sf->db_init();
?>
