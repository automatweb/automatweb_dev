<?php

class connection_test extends UnitTestCase
{
	function connection_test($name)
	{
		 $this->UnitTestCase($name);
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
			"reltype" => 669
		));
		$this->assertTrue($c->prop("from") == 60);
		$this->assertTrue($c->prop("to") == 70);
		$this->assertTrue($c->prop("reltype") == 669);
		// also we should check that it didn't write anything to db
		$id = $this->db->db_fetch_field("SELECT id FROM aliases WHERE source = 60 AND target = 70 AND reltype = 669", "id");
		$this->assertTrue($id == NULL);
	}

	function test_construct_arr_save()
	{
		$c = new connection(array(
			"from" => 60,
			"to" => 70,
			"reltype" => 668
		));
		$c->save();
		$this->assertTrue($c->prop("from") == 60);
		$this->assertTrue($c->prop("to") == 70);
		$this->assertTrue($c->prop("reltype") == 668);
		// also we should check that it wrote the conn
		$id = $this->db->db_fetch_field("SELECT id FROM aliases WHERE source = 60 AND target = 70 AND reltype = 668", "id");
		$this->assertTrue($id > 0);
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
			"reltype" => 667
		));
		$this->assertTrue($c->prop("from") == 60);
		$this->assertTrue($c->prop("to") == 70);
		$this->assertTrue($c->prop("reltype") == 667);
		// also we should check that it didn't write anything to db
		$id = $this->db->db_fetch_field("SELECT id FROM aliases WHERE source = 60 AND target = 70 AND reltype = 667", "id");
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

	function test_connection_change_err()
	{
		$row = $this->db->db_fetch_row("SELECT id,source,target FROM aliases LIMIT 1", "id");
		$c = new connection();
		$c->load($row["id"]);

		__disable_err();
		$c->change(1);
		$this->assertTrue(__is_err());
	}

	function test_connection_change()
	{
		$row = $this->db->db_fetch_row("SELECT id,source,target,data FROM aliases LIMIT 1", "id");
		$c = new connection();
		$c->load($row["id"]);
		$c->change(array(
			"data" => "8"
		));
		$row = $this->db->db_fetch_row("SELECT id,source,target,data FROM aliases LIMIT 1", "id");
		$this->assertTrue($row["data"] == 8);
	}

	function test_connection_delete()
	{
		// create
		$c = new connection(array(
			"from" => 60,
			"to" => 70,
			"reltype" => 660
		));
		$c->save();
		$this->assertTrue($c->prop("from") == 60);
		$this->assertTrue($c->prop("to") == 70);
		$this->assertTrue($c->prop("reltype") == 660);
		// also we should check that it wrote the conn
		$id = $this->db->db_fetch_field("SELECT id FROM aliases WHERE source = 60 AND target = 70 AND reltype = 660", "id");
		$this->assertTrue($id > 0);
		// now delete the thing
		$res = $c->delete();
		$this->assertTrue($id == $res);
		$id2 = $this->db->db_fetch_field("SELECT id FROM aliases WHERE source = 60 AND target = 70 AND reltype = 660", "id");
		$this->assertTrue($id2 < 1);
	}

	function test_connection_delete_err()
	{
		$c = new connection();
		__disable_err();
		$c->delete();
		$this->assertTrue(__is_err());
	}

	function test_connection_id()
	{
		$row = $this->db->db_fetch_row("SELECT id,source,target FROM aliases LIMIT 1", "id");
		$c = new connection();
		$c->load($row["id"]);
		$this->assertTrue($row["id"] == $c->id());
	}

	function test_connection_to()
	{
		$row = $this->db->db_fetch_row("SELECT id,source,target FROM aliases LIMIT 1", "id");
		$c = new connection();
		$c->load($row["id"]);
		$to = $c->to();
		$this->assertTrue($row["target"] == $to->id());
	}

	function test_connection_to_err()
	{
		$c = new connection();
		__disable_err();
		$to = $c->to();
		$this->assertTrue(__is_err());
	}

	function test_connection_from()
	{
		$row = $this->db->db_fetch_row("SELECT id,source,target FROM aliases LIMIT 1", "id");
		$c = new connection();
		$c->load($row["id"]);
		$from = $c->from();
		$this->assertTrue($row["source"] == $from->id());
	}

	function test_connection_from_err()
	{
		$c = new connection();
		__disable_err();
		$from = $c->from();
		$this->assertTrue(__is_err());
	}
}

?>
