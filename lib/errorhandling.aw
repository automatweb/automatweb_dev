<?php

function aw_exception_handler($e)
{
	exit;
}

function aw_dbg_exception_handler($e)
{
	error::raise(array(
		"id" => "ERR_UNCAUGHT_EXCEPTION",
		"msg" => $e->getMessage(),
		"fatal" => true,
		"exception" => $e
	));
}

class aw_exception extends Exception {}

?>
