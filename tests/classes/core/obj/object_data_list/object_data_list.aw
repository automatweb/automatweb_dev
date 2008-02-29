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
	}

	function tearDown()
	{
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
}

?>
