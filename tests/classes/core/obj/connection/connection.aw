<?php

class connection_test extends PHPUnit_TestCase
{
	function connection_test($name)
	{
		 $this->PHPUnit_TestCase($name);
	}

	function setUp()
	{
		$this->db = get_instance("class_base");
	}

	function test_construct()
	{
		$c = new connection();
		$this->assertTrue(!$c->prop("to"));
	}

	function test_construct_id()
	{
		$row = $this->db->db_fetch_row("SELECT id,source,target FROM aliases LIMIT 1", "id");
		$c = new connection($row["id"]);
		$this->assertTrue($c->prop("from") == $row["source"]);
		$this->assertTrue($c->prop("to") == $row["target"]);
	}

	function test_construct_arr()
	{
		$c = new connection(array(
			"from" => 60,
			"to" => 70,
			"reltype" => 666
		));
		$this->assertTrue($c->prop("from") == 60);
		$this->assertTrue($c->prop("to") == 70);
		$this->assertTrue($c->prop("reltype") == 666);
		// also we should check that it didn't write anything to db
		$id = $this->db->db_fetch_field("SELECT id FROM aliases WHERE source = 60 AND target = 70 AND reltype = 666", "id");
		$this->assertTrue($id == NULL);
	}

	function test_construct_err()
	{
		__disable_err();
		$c = new connection(new object());
		$this->assertTrue(__is_err());
	}

	function test_load_id()
	{
		$row = $this->db->db_fetch_row("SELECT id,source,target FROM aliases LIMIT 1", "id");
		$c = new connection();
		$c->load($row["id"]);
		$this->assertTrue($c->prop("from") == $row["source"]);
		$this->assertTrue($c->prop("to") == $row["target"]);
	}

	function test_load_arr()
	{
		$c = new connection();
		$c->load(array(
			"from" => 60,
			"to" => 70,
			"reltype" => 666
		));
		$this->assertTrue($c->prop("from") == 60);
		$this->assertTrue($c->prop("to") == 70);
		$this->assertTrue($c->prop("reltype") == 666);
		// also we should check that it didn't write anything to db
		$id = $this->db->db_fetch_field("SELECT id FROM aliases WHERE source = 60 AND target = 70 AND reltype = 666", "id");
		$this->assertTrue($id == NULL);
	}

	function test_load_err()
	{
		__disable_err();
		$c = new connection();
		$c->load(new object());
		$this->assertTrue(__is_err());
	}

	function test_find()
	{
		
	}
}

?>