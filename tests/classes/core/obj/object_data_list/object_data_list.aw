<?php

class object_data_list_test extends UnitTestCase
{
	function object_data_list_test($name)
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

	function test_construct_aliases()
	{
		$odl = new object_data_list(
			array(
				"class_id" => CL_MENU,
				"limit" => 1
			),
			array(
				CL_MENU => array("name" => "oid"),		// I'm trying to confuse it. :P
			)
		);
		foreach($odl->list_data as $oid => $odata)
		{
			$row = $this->db->db_fetch_row("SELECT name FROM objects WHERE oid = ".$oid." LIMIT 1");
			break;
		}
		$this->assertTrue($odl->list_data[$row["id"]]["name"] == $row["name"]);
	}
	
	function test_arr_aliases()
	{
		$row = $this->db->db_fetch_row("SELECT oid, class_id FROM objects LIMIT 1");
		$odl = new object_data_list(
			array(
				"class_id" => $row["class_id"],
				"oid" => $row["oid"],
			),
			array(
				$row["class_id"] => array("name" => "oid"),		// I'm trying to confuse it. :P
			)
		);
		$odl_arr = $odl->arr();
		$this->assertTrue($odl->list_data[$row["id"]]["name"] == $odl_arr[$row["id"]]["oid"]);
	}

	function test_arr_no_aliases()
	{
		$row = $this->db->db_fetch_row("SELECT oid, class_id FROM objects LIMIT 1");
		$odl = new object_data_list(
			array(
				"class_id" => $row["class_id"],
				"oid" => $row["oid"],
			),
			array(
				$row["class_id"] => array("name"),
			)
		);
		$odl_arr = $odl->arr();
		$this->assertTrue($odl->list_data[$row["id"]]["name"] == $odl_arr[$row["id"]]["name"]);
	}

	function test_construct_no_aliases()
	{
		$odl = new object_data_list(
			array(
				"class_id" => CL_MENU,
				"limit" => 1
			),
			array(
				CL_MENU => array("name"),
			)
		);
		foreach($odl->list_data as $oid => $odata)
		{
			$row = $this->db->db_fetch_row("SELECT name FROM objects WHERE oid = ".$oid." LIMIT 1");
			break;
		}
		$this->assertTrue($odl->list_data[$row["id"]]["name"] == $row["name"]);
	}

	function test_filter_props_props()
	{
		$o1 = obj();
		$o1->set_parent(aw_ini_get("site_rootmenu"));
		$o1->set_class_id(CL_CRM_PERSON);
		$o1->save();
		$this->tmp_objs[] = $o1;

		$o2 = obj();
		$o2->set_parent(aw_ini_get("site_rootmenu"));
		$o2->set_class_id(CL_LANGUAGE);
		$o2->name = "This_is_very_unique_name_Foo_Fighters";
		$o2->save();

		$o1->mlang = $o1->id();
		$o1->save();

		$this->tmp_objs[] = $o2;

		$odl = new object_data_list(
			array(
				"class_id" => CL_CRM_PERSON,
				"mlang.name" => "%his_is_very_unique_name_Foo_Fighter%",
			),
			array(
				CL_CRM_PERSON => array("oid"),
			)
		);
		$v = $odl->arr();
		$ok = count($v) > 0;
		foreach($v as $d)
		{
			$o = obj($d["oid"]);
			if(!preg_match("his_is_very_unique_name_Foo_Fighter", $o->prop("mlang.name")))
			{
				$ok = false;
				break;
			}
		}
		$this->assertTrue($ok);
	}

	function test_filter_props_n_reltypes()
	{
		$o1 = $this->_get_temp_o(array("name" => "This_is_very_unique_name_Foo_Fighters"));
		$o2 = $this->_get_temp_o();

		$o1->connect(array(
			"to" => $o2,
			"type" => "RELTYPE_SHOW_SUBFOLDERS_MENU",
		));

		$odl = new object_data_list(
			array(
				"class_id" => CL_MENU,
				"name" => "%his_is_very_unique_name_Foo_Fighter%",
				"RELTYPE_SHOW_SUBFOLDERS_MENU" => $o2->id(),
			),
			array(
				CL_MENU => array("oid"),
			)
		);
		$v = $odl->arr();
		$ok = count($v) > 0;
		foreach($v as $od)
		{
			$o = obj($od["oid"]);
			if(!preg_match("his_is_very_unique_name_Foo_Fighter", $o->name))
			{
				$ok = false;
				break;
			}
			$conn_ok = false;
			foreach($o->connections_from(array("type" => "RELTYPE_SHOW_SUBFOLDERS_MENU")) as $conn)
			{
				if($conn->prop("to") == $o2->id())
				{
					$conn_ok = true;
				}
			}
			$ok = $ok && $conn_ok;
		}
		$this->assertTrue($ok);
	}

	function test_filter_reltypes_props()
	{
		$o1 = $this->_get_temp_o();
		$o2 = $this->_get_temp_o(array("name" => "This_is_very_unique_name_Foo_Fighters"));
		$o1->connect(array(
			"to" => $o2,
			"type" => "RELTYPE_SHOW_SUBFOLDERS_MENU",
		));
		$odl = new object_data_list(
			array(
				"class_id" => CL_MENU,
				"RELTYPE_SHOW_SUBFOLDERS_MENU.name" => "%his_is_very_unique_name_Foo_Fighter%",
			),
			array(
				CL_MENU => array("oid"),
			)
		);
		$v = $odl->arr();
		$ok = count($v) > 0;
		foreach($v as $d)
		{
			$o = obj($d["oid"]);
			foreach($o->connections_from(array("type" => "RELTYPE_SHOW_SUBFOLDERS_MENU")) as $conn)
			{
				if(!preg_match("his_is_very_unique_name_Foo_Fighter", $conn->prop("to.name")))
				{
					$ok = false;
					break;
				}
			}
		}
		$this->assertTrue($ok);
	}

	function test_props_props_parent_name()
	{
		/*
		Not complete.
		$odl = new object_data_list(
			array(
				"class_id" => CL_MENU,
			),
			array(
				CL_MENU => array("parent.name" => "parent")
			)
		);
		*/
	}

	function test_props_props_foo_foo()
	{
		// Ask for property.property. Something like test_props_props_parent_name
	}

	function _get_temp_o($arr = array())
	{
		// create new object
		$o = obj();
		$o->set_parent(isset($arr["parent"]) ? $arr["parent"] : aw_ini_get("site_rootmenu"));
		$o->set_class_id(isset($arr["class_id"]) ? $arr["class_id"] : CL_MENU);
		$o->name = $arr["name"];
		$o->save();
		// Easier to kill 'em this way afterwards.
		$this->tmp_objs[] = $o;

		return $o;
	}
}

?>
