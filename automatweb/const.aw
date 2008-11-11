<?php

if (isset($_SERVER["DOCUMENT_ROOT"]))
{
	$site_dir = str_replace(array("\\", "//"), "/", ($_SERVER["DOCUMENT_ROOT"] . "/"));
}
else
{
	exit("Server variables not defined.");
}

include_once($site_dir . "const.aw");

?>
