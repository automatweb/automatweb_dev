<?php
// wrapper. inkluudib saidi const.aw
// keemia on vajalik sest php resolvib symlingid 
// real pathiks enne faili parsimist
// kick ass security
$site_dir = dirname($SCRIPT_FILENAME);
$site_dir = substr($site_dir,0,strrpos($site_dir,"/"));
include_once("$site_dir/const.aw");
?>
