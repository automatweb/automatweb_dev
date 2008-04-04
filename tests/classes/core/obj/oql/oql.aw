<?php

class oql_test extends UnitTestCase
{
	function oql_test($name)
	{
		 $this->UnitTestCase($name);
	}

	function setUp()
	{
		$this->db = get_instance("class_base");
		aw_disable_acl();
		$this->tmp_objs = array();
	}

	function tearDown()
	{
		foreach($this->tmp_objs as $doomed_obj);
		{
			$doomed_obj->delete(true);
		}
		aw_restore_acl();
	}

	function test_execute_query_clid_without_where_clause()
	{
		$rv = oql::compile_query("
		SELECT
			name
		FROM
			CL_MENU
		");
		$rv2 = oql::execute_query($rv);
		$ok = true;
		foreach($rv2 as $oid => $data)
		{
			if($this->get_class_id($oid) != CL_MENU)
			{
				$ok = false;
				break;
			}				
		}
		$this->assertTrue($ok);
	}

	// I'm using "WHERE true" cause the darn thing won't work without WHERE clause.
	function test_execute_query_clid_with_where_clause()
	{
		$rv = oql::compile_query("
		SELECT
			name
		FROM
			CL_MENU
		WHERE
			true
		");
		$rv2 = oql::execute_query($rv);
		$ok = true;
		foreach($rv2 as $oid => $data)
		{
			if($this->get_class_id($oid) != CL_MENU)
			{
				$ok = false;
				break;
			}				
		}
		$this->assertTrue($ok);
	}

	// I'm using "WHERE true" cause the darn thing won't work without WHERE clause.
	// Check if it returns all the properties asked for in the SELECT [...] clause.
	function test_execute_query_atleast_properties_asked_for()
	{
		// Could I use LIMIT 1???
		$rv = oql::compile_query("
		SELECT
			name, firstname, lastname, balance
		FROM
			CL_CRM_PERSON
		WHERE
			true
		");
		$aks = array("name", "firstname", "lastname", "balance");
		$rv2 = oql::execute_query($rv);
		$ok = false;
		foreach($rv2 as $id => $data)
		{
			foreach($aks as $ak)
			{
				if(!array_key_exists($ak, $data))
					$ok = false;
			}
		}
		$this->assertTrue($ok);
	}

	// I'm using "WHERE true" cause the darn thing won't work without WHERE clause.
	// Check if it returns any properties NOT asked for in the SELECT [...] clause.
	function test_execute_query_only_properties_asked_for()
	{
		// Could I use LIMIT 1???
		$rv = oql::compile_query("
		SELECT
			name
		FROM
			CL_CRM_PERSON
		WHERE
			true
		");
		$rv2 = oql::execute_query($rv);
		$ok = false;
		foreach($rv2 as $id => $data)
		{
			foreach($data as $dk => $dv)
			{
				if($dk != "name")
					$ok = false;
			}
		}
		$this->assertTrue($ok);
	}

	// I'm using "WHERE true" cause the darn thing won't work without WHERE clause.
	// Test if "LIMIT n" works.
	function test_execute_query_limit_n()
	{
		$rv = oql::compile_query("
		SELECT
			name
		FROM
			CL_MENU
		WHERE
			true
		LIMIT 1
		");
		$rv2 = oql::execute_query($rv);
		assertTrue(count($rv2) == 1);
	}

	function test_execute_query_with_arguements()
	{
		$row = $this->db->db_fetch_row("SELECT oid, name, status FROM objects WHERE class_id = '".CL_MENU."' LIMIT 1");
		$rv = oql::compile_query("
		SELECT
			name, status
		FROM
			CL_MENU
		WHERE
			name = '%s' AND status = '%s'
		");
		$rv2 = oql::execute_query($rv, array($row["name"], $row["status"]));
		assertTrue($rv2[$row["oid"]]["name"] == $row["name"] && $rv2[$row["oid"]]["status"] == $row["status"]);
	}

	function test_execute_query_same_query_different_params()
	{
		$row = $this->db->db_fetch_row("SELECT oid, name, status FROM objects WHERE class_id = '".CL_MENU."' LIMIT 1");
		$rv = oql::compile_query("
		SELECT
			name, status
		FROM
			CL_MENU
		WHERE
			name = '%s' AND status = '%s'
		");
		$rv2 = oql::execute_query($rv, array($row["name"], $row["status"]));
		$ok = ($rv2[$row["oid"]]["name"] == $row["name"] && $rv2[$row["oid"]]["status"] == $row["status"]);
		$row2 = $this->db->db_fetch_row("SELECT oid, name, status FROM objects WHERE class_id = '".CL_MENU."' AND oid != ".$row["oid"]." LIMIT 1");
		$rv3 = oql::execute_query($rv, array($row2["name"], $row2["status"]));
		$ok = ($ok && $rv3[$row2["oid"]]["name"] == $row2["name"] && $rv3[$row2["oid"]]["status"] == $row2["status"]);
		assertTrue($ok);
	}

	function test_execute_query_select_props_prop()
	{
		$o1 = $this->_get_temp_o();
		$o2 = $this->_get_temp_o();
		$o3 = $this->_get_temp_o();

		$o1->set_prop("name", "This_is_very_unique_name_Foo1");
		$o1->save();

		$o2->set_prop("name", "This_is_very_unique_name_Foo2");
		$o2->set_prop("submenus_from_menu", $o1->id());
		$o2->save();

		$o3->set_prop("name", "This_is_very_unique_name_Foo3");
		$o3->set_prop("images_from_menu", $o2->id());
		$o3->save();

		$rv = oql::compile_query("
		SELECT
			images_from_menu.submenus_from_menu.name
		FROM
			CL_MENU
		WHERE
			name = '%s'
		");
		$r2 = oql::execute_query($rv, array($o3->name()));

		$this->assertTrue($rv[$o3->id()]["images_from_menu.submenus_from_menu.name"] == $o1->name());
	}

	function test_execute_query_where_props_prop()
	{
		$o1 = $this->_get_temp_o();
		$o2 = $this->_get_temp_o();
		$o3 = $this->_get_temp_o();

		$o1->set_prop("name", "This_is_very_unique_name_Foo1");
		$o1->save();

		$o2->set_prop("name", "This_is_very_unique_name_Foo2");
		$o2->set_prop("submenus_from_menu", $o1->id());
		$o2->save();

		$o3->set_prop("name", "This_is_very_unique_name_Foo3");
		$o3->set_prop("images_from_menu", $o2->id());
		$o3->save();

		$rv = oql::compile_query("
		SELECT
			name
		FROM
			CL_MENU
		WHERE
			images_from_menu.submenus_from_menu.name = '%s'
		");
		$r2 = oql::execute_query($rv, array($o1->name()));

		$this->assertTrue($rv[$o3->id()]["name"] == $o3->name());
	}

	function test_execute_query_where_reltype()
	{
		$o1 = $this->_get_temp_o();
		$o2 = $this->_get_temp_o();

		$o1->set_prop("name", "This_is_very_unique_name_Foo1");
		$o1->save();

		$o2->set_prop("name", "This_is_very_unique_name_Foo2");
		$o2->save();

		$o1->connect(array(
			"to" => $o2,
			"reltype" => "RELTYPE_SHOW_SUBFOLDERS_MENU",
		));

		$rv = oql::compile_query("
		SELECT
			name
		FROM
			CL_MENU
		WHERE
			RELTYPE_SHOW_SUBFOLDERS_MENU = '%s'
		");
		$rv2 = oql::execute_query($rv, array($o2->id()));
		$this->assertTrue($rv2[$o1->id()]["name"] == $o1->name());
	}

	function test_execute_query_where_reltypes_and_props()
	{
		$o1 = $this->_get_temp_o();
		$o2 = $this->_get_temp_o();
		$o3 = $this->_get_temp_o();

		$o1->set_prop("name", "This_is_very_unique_name_Foo1");
		$o1->save();

		$o2->set_prop("name", "This_is_very_unique_name_Foo2");
		$o2->set_prop("submenus_from_menu", $o3->id());
		$o2->save();

		$o3->set_prop("name", "This_is_very_unique_name_Foo3");
		$o3->save();

		$o1->connect(array(
			"to" => $o2,
			"reltype" => "RELTYPE_SHOW_SUBFOLDERS_MENU",
		));

		$rv = oql::compile_query("
		SELECT
			name
		FROM
			CL_MENU
		WHERE
			RELTYPE_SHOW_SUBFOLDERS_MENU.submenus_from_menu.name = '%s'
		");
		$rv2 = oql::execute_query($rv, array($o3->name()));
		$this->assertTrue($rv2[$o1->id()]["name"] == $o1->name());
	}

	function test_execute_query_where_like()
	{
	}
	
	function test_execute_query_where_calculating()
	{
	}

	function test_execute_query_where_this_or_that()
	{
	}

	function get_class_id($id)
	{
		$row = $this->db->db_fetch_row("SELECT class_id FROM objects WHERE oid = ".$id." LIMIT 1");
		return $row["class_id"];
	}

	function _get_temp_o()
	{
		// create new object
		$o = obj();
		$o->set_parent(aw_ini_get("site_rootmenu"));
		$o->set_class_id(CL_MENU);
		$o->save();
		// Easier to kill 'em this way afterwards.
		$this->tmp_objs[] = $o;

		return $o;
	}
}

?>
