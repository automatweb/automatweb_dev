<?php
$aw_dir = "{VAR:aw_dir}";
$site_dir = __FILE__;
$lsl = strrpos($site_dir, DIRECTORY_SEPARATOR);
$site_dir = substr($site_dir, 0, $lsl);
$lsl = strrpos($site_dir, DIRECTORY_SEPARATOR);
$site_dir = substr($site_dir, 0, $lsl);

include($aw_dir."/init.aw");
init_config(array(
	"cache_file" => $site_dir."/pagecache/ini.cache",
	"ini_files" => array($aw_dir."/aw.ini",$site_dir."/aw.ini")
));

if (strpos($_SERVER["REQUEST_URI"],"/automatweb") === false)
{
	// can't use classload here, cause it will be included from within a function and then all kinds of nasty
	// scoping rules come into action. blech.
	$script = basename($_SERVER["SCRIPT_FILENAME"], ".".aw_ini_get("ext"));
	$path = aw_ini_get("classdir")."/".aw_ini_get("site_impl_dir")."/".$script."_impl.".aw_ini_get("ext");
	if (file_exists($path))
	{
		include($path);
	}
}
?>
