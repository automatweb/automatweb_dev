<?php
session_name("automatweb");
session_start();
foreach ($_POST as $key => $val)
{	
	$_SESSION["bank_return"]["data"][$key] = $val;
}
if($_POST["VK_SERVICE"] == 1101) $url = $_SESSION["bank_payment"]["url"];
else $url = $_SESSION["bank_payment"]["cancel"];
header("Location:".$url);
die();

?>



