<?php
if ($uid != "")
	$uid  = "";
session_name("automatweb");
session_start();
include("const.aw");
classload("timer","aw_template","users","acl","images","defs");
$awt = new aw_timer;
$users = new users_user;
$gidlist = $users->get_gids_by_uid($uid);
session_register("error");

if ($uid == "")
{
	$ab = new acl_base;
	$ab->auth_error();
}

switch($action) 
{
	default:
		$ab = new acl_base;
		$ab->auth_error();
	};	
?>