<?php
$siteconfig = dirname($SCRIPT_FILENAME);
$last = strrpos($siteconfig,"/");
print strlen($siteconfig);
print "<br>";
print $last;
print "<br>";

$site_dir = dirname($SCRIPT_FILENAME);
$site_dir = substr($site_dir,0,strrpos($site_dir,"/"));
print $site_dir;

?>
