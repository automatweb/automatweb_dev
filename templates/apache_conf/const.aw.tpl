<?php
$aw_dir = "{VAR:aw_dir}";
$site_dir = "{VAR:site_dir}";

include($aw_dir."/init.aw");
init_config(array(
	"cache_file" => $site_dir."/pagecache/ini.cache",
	"ini_files" => array($aw_dir."/aw.ini",$site_dir."/aw.ini")
));
?>
