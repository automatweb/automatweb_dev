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
	
	function test_arr_aliases()
	{
		$row = $this->db->db_fetch_row("SELECT oid, class_id FROM objects LIMIT 1");
		$odl = new object_list(
			array(
				"class_id" => $row["class_id"],
				"oid" => $row["oid"],
			),
			array(
				CL_MENU => array("name" => "object_name"),
			)
		);
		$odl_arr = $odl->arr();
		$this->assertTrue($odl->list_data[$row["id"]]["name"] == $odl_arr[$row["id"]]["object_name"]);
	}
}

?>
