<?php

class object_test extends PHPUnit_TestCase
{
	function object_test($name)
	{
		 $this->PHPUnit_TestCase($name);
	}

	function test_name()
	{
		$o = obj(1316);
		$this->assertTrue($o->name() == "Ressursi planeerimine");
	}
}
?>