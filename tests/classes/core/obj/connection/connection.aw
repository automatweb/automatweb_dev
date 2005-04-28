<?php

class connection_test extends PHPUnit_TestCase
{
	function connection_test($name)
	{
		 $this->PHPUnit_TestCase($name);
	}

	function test_construct()
	{
		$c = new connection();
		$this->assertTrue(!$c->prop("to"));
	}
}

?>