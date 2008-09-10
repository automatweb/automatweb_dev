<?php
// wrapper. inkluudib saidi const.aw
// keemia on vajalik sest php resolvib symlingid 
// real pathiks enne faili parsimist
// kick ass security
$script_filename = ($SCRIPT_FILENAME) ? $SCRIPT_FILENAME : $_SERVER["SCRIPT_FILENAME"];
$site_dir = dirname($script_filename);
while (substr($site_dir, strrpos($site_dir, "/")) != "/automatweb")
{
	$site_dir = dirname($site_dir);
}
$site_dir = substr($site_dir,0,strrpos($site_dir,"/"));
include_once("$site_dir/const.aw");
?>
