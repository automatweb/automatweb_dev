<?php
$aw_dir = "{VAR:aw_dir}";
$site_dir = getcwd();
$lsl = strrpos($site_dir, "/");
$site_dir = substr($site_dir, 0, $lsl);

include($aw_dir."/init.aw");
init_config(array(
	"cache_file" => $site_dir."/pagecache/ini.cache",
	"ini_files" => array($aw_dir."/aw.ini",$site_dir."/aw.ini")
));
?>
