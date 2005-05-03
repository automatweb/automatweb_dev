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

	function test_find_err()
	{
		__disable_err();
		$c = new connection();
		$c->find();
		$this->assertTrue(__is_err());
	}

	function test_find_from()
	{
		// get some conns and check those
		$row = $this->db->db_fetch_row("SELECT id,source FROM aliases");
		
		$c = new connection();
		$res = $c->find(array(
			"from" => $row["source"]
		));
		$first = reset($res);
		$this->assertTrue($first["from"] == $row["source"]);
	}

	function test_find_to()
	{
		// get some conns and check those
		$row = $this->db->db_fetch_row("SELECT id,target FROM aliases");
		
		$c = new connection();
		$res = $c->find(array(
			"to" => $row["target"]
		));
		$first = reset($res);
		$this->assertTrue($first["to"] == $row["target"]);
	}

	function test_find_type()
	{
		// get some conns and check those
		$row = $this->db->db_fetch_row("SELECT id,target,reltype FROM aliases");
		
		$c = new connection();
		$res = $c->find(array(
			"to" => $row["target"]
		));
		$first = reset($res);
		$this->assertTrue($first["reltype"] == $row["reltype"]);
	}

	function test_find_to_obj()
	{
		// get some conns and check those
		$row = $this->db->db_fetch_row("SELECT id,target,o.name as name FROM aliases left join objects o on o.oid = aliases.target");
		
		$c = new connection();
		$res = $c->find(array(
			"to" => $row["target"]
		));
		$first = reset($res);
		$this->assertTrue($first["to.name"] == $row["name"]);
	}
}

?>