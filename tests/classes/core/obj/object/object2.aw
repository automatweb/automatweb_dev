<?php

class object_test extends UnitTestCase
{
	function object_test($name)
	{
		 $this->UnitTestCase($name);
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
				return;
			}
		}
	}

	function _get_temp_o()
	{
		aw_disable_acl();
		// create new object
		$o = obj();
		$o->set_parent(aw_ini_get("site_rootmenu"));
		$o->set_class_id(CL_MENU);
		$o->save();
		aw_restore_acl();

		return $o;
	}

	function test_path_str_err_cycle()
	{
		__disable_err();
		aw_disable_acl();
		$o1 = $this->_get_temp_o();
		$o2 = $this->_get_temp_o();
		$o2->set_parent($o1->id());
		$o3 = $this->_get_temp_o();
		$o3->set_parent($o2->id());
		$o1->set_parent($o3->id());
		$o1->save();
		$o2->save();
		$o3->save();
	
		$o3->path_str();

		$o1->delete(true);
		$o2->delete(true);
		$o3->delete(true);
		aw_restore_acl();
		$this->assertTrue(__is_err());
	}

	function test_path_str_2()
	{
		aw_disable_acl();
		$o = obj(aw_ini_get("site_rootmenu"));

		$o1 = $this->_get_temp_o();
		$o1->set_name("o1");
		$o1->save();

		$o2 = $this->_get_temp_o();
		$o2->set_parent($o1);
		$o2->set_name("o2");
		$o2->save();

		$o3 = $this->_get_temp_o();
		$o3->set_parent($o2);
		$o3->set_name("o3");

		$str = $o3->path_str();

		$this->assertEqual($str, $o->name()." / o1 / o2 / o3");

		$o1->delete(true);
		$o2->delete(true);
		$o3->delete(true);
	}

	function test_path_str_max_len()
	{
		aw_disable_acl();
		$o = obj(aw_ini_get("site_rootmenu"));

		$o1 = $this->_get_temp_o();
		$o1->set_name("o1");
		$o1->save();

		$o2 = $this->_get_temp_o();
		$o2->set_parent($o1);
		$o2->set_name("o2");
		$o2->save();

		$o3 = $this->_get_temp_o();
		$o3->set_parent($o2);
		$o3->set_name("o3");

		$str = $o3->path_str(array(
			"max_len" => 2
		));

		$this->assertEqual($str, "o2 / o3");

		$o1->delete(true);
		$o2->delete(true);
		$o3->delete(true);
	}

	function test_path_str_start_at()
	{
		aw_disable_acl();
		$o = obj(aw_ini_get("site_rootmenu"));

		$o1 = $this->_get_temp_o();
		$o1->set_name("o1");
		$o1->save();

		$o2 = $this->_get_temp_o();
		$o2->set_parent($o1);
		$o2->set_name("o2");
		$o2->save();

		$o3 = $this->_get_temp_o();
		$o3->set_parent($o2);
		$o3->set_name("o3");

		$str = $o3->path_str(array(
			"start_at" => $o2->id()
		));

		$this->assertEqual($str, "o2 / o3");

		$o1->delete(true);
		$o2->delete(true);
		$o3->delete(true);
	}

	function test_path_str_path_only()
	{
		aw_disable_acl();
		$o = obj(aw_ini_get("site_rootmenu"));

		$o1 = $this->_get_temp_o();
		$o1->set_name("o1");
		$o1->save();

		$o2 = $this->_get_temp_o();
		$o2->set_parent($o1);
		$o2->set_name("o2");
		$o2->save();

		$o3 = $this->_get_temp_o();
		$o3->set_parent($o2);
		$o3->set_name("o3");

		$str = $o3->path_str(array(
			"path_only" => true
		));

		$this->assertEqual($str, $o->name()." / o1 / o2");

		$o1->delete(true);
		$o2->delete(true);
		$o3->delete(true);
	}

	function test_is_property_err_param()
	{
		__disable_err();
		$o = obj(aw_ini_get("site_rootmenu"));
		$o->is_property(5);
		$this->assertTrue(__is_err());
	}

	function test_is_property_err_clid()
	{
		__disable_err();
		$o = obj();
		$o->is_property("foo");
		$this->assertTrue(__is_err());
	}

	function test_is_property_existing()
	{
		$o = obj(aw_ini_get("site_rootmenu"));
		$this->assertTrue($o->is_property("target"));
	}

	function test_is_property_new()
	{
		$o = obj();
		$o->set_class_id(CL_MENU);
		$this->assertTrue($o->is_property("target"));
	}
}
?>
