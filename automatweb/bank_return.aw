<?php
session_name("automatweb");
session_start();
foreach ($_POST as $key => $val)
{
	$_SESSION["bank_return"]["data"][$key] = $val;
}
foreach ($_GET as $key => $val)
{
	$_SESSION["bank_return"]["data"][$key] = $val;
}
//classload("common/bank_payment");
//$bank_inst = get_instance("common/bank_payment");
//arr($bank_inst->banks);


//esimene on hansapanga, EYP, sampo ja krediidipanga positiivne vastus, teine krediitkaardikeskuse
if($_POST["VK_SERVICE"] == 1101 || ($_GET["action"] == "afb" && $_GET["respcode"] == "000"))
{
	$url = $_SESSION["bank_payment"]["url"];
}
else
{
	$url = $_SESSION["bank_payment"]["cancel"];
}
header("Location:".$url);
die();

?>



