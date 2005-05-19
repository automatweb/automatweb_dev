<?php

class object_test extends PHPUnit_TestCase
{
	function object_test($name)
	{
		 $this->PHPUnit_TestCase($name);
	}

	function setUp()
	{
		$this->db = get_instance("class_base");

		$this->db->db_query("SELECT oid,name FROM objects WHERE status > 0 and parent > 0 and class_id > 0");
		while ($row = $this->db->db_next())
		{
			if ($this->db->can("view", $row["oid"]))
			{
				$this->obj_id = $row["oid"];
			}
		}
	}

	function test_construct_empty()
	{
		$o = new object();
		$this->assertTrue(!is_oid($o->id()));
	}

	function test_construct_id()
	{
		// get random object
		$o = new object($this->obj_id);
		$this->assertEquals($o->id(),$this->obj_id);
	}

	function test_construct_alias()
	{
		// get random object
		$this->db->db_query("SELECT oid,name,alias FROM objects WHERE status > 0 and alias != ''");
		while ($row = $this->db->db_next())
		{
			if ($this->db->can("view", $row["oid"]))
			{
				$o = new object($row["alias"]);
				$this->assertEquals($row["oid"],$o->id());
				return;
			}
		}
		$this->assertTrue(false);
	}

	function test_construct_obj()
	{
		// get random object
		$o = new object($this->obj_id);
		$o2 = new object($o);
		$this->assertEquals($o->id(),$o2->id());
	}

	function test_construct_fail_del()
	{
		// get random object
		$this->db->db_query("SELECT oid,name FROM objects WHERE status = 0");
		while ($row = $this->db->db_next())
		{
			__disable_err();
			$o = new object($row["oid"]);
			$this->assertTrue(__is_err());
			return;
		}
	}

	function test_save()
	{
		$o = obj($this->obj_id);
		$nc = $o->comment()+1;
		$o->set_comment($nc);
		aw_disable_acl();
		$o->save();
		aw_restore_acl();

		$nv = $this->db->db_fetch_field("SELECT comment FROM objects WHERE oid = ".$this->obj_id, "comment");
		$this->assertEquals($nc, $nv);
	}

	function test_save_fail()
	{
		$o = obj();
		__disable_err();
		$o->save();
		$this->assertTrue(__is_err());
	}

	function test_save_new()
	{
		$o = obj($this->obj_id);
		$nm = $o->name();
		aw_disable_acl();
		$nid = $o->save_new();
		$no = obj($nid);
		$this->assertEquals($nm, $no->name());
		$no->delete(true);
		aw_restore_acl();
	}

	function test_implicit_save()
	{
		$o = obj($this->obj_id);
		$oldn = $o->name();
		$o->set_implicit_save(true);
		$this->assertTrue($o->get_implicit_save());
		aw_disable_acl();
		$o->set_name($oldn+1);
		$this->assertEquals($oldn+1, $this->db->db_fetch_field("SELECT name FROM objects WHERE oid = ".$o->id(), "name"));
		$o->set_implicit_save(false);
		$this->assertFalse($o->get_implicit_save());
		$o->set_name($oldn);
		$this->assertFalse($oldn == $this->db->db_fetch_field("SELECT name FROM objects WHERE oid = ".$o->id(), "name"));
		$o->save();
		$this->assertEquals($oldn, $this->db->db_fetch_field("SELECT name FROM objects WHERE oid = ".$o->id(), "name"));
		aw_restore_acl();
	}

	function test_arr()
	{
		$o = obj($this->obj_id);
		$ar = $o->arr();
		$this->assertEquals($ar["name"], $o->name());
	}


}
?>