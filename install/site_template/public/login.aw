<?php
include("const.aw");
// can't use classload here, cause it will be included from within a function and then all kinds of nasty
// scoping rules come into action. blech.
$script = basename($_SERVER["SCRIPT_FILENAME"], ".".aw_ini_get("ext"));
$path = aw_ini_get("classdir")."/".aw_ini_get("site_impl_dir")."/".$script."_impl.".aw_ini_get("ext");
include($path);
?>