<?php

class cfgutils_test extends PHPUnit_TestCase
{
	function cfgutils_test($name)
	{
		 $this->PHPUnit_TestCase($name);
	}

	function setUp()
	{
		$this->cfgu = get_instance("cfg/cfgutils");
		$this->props = $this->cfgu->load_properties(array("clid" => CL_PROPTEST));
	}

	function test_properties_exist()
	{
		// if it's array, then properties have probably been loaded
		$this->assertType("array",$this->props);
		$this->assertTrue(sizeof($this->props) > 0);
	}

	function test_props_derived_from_classbase()
	{
		$this->assertType("array",$this->props["name"]);
		$this->assertType("array",$this->props["comment"]);
		$this->assertType("array",$this->props["status"]);
		//arr($this->props);
	}

	function test_textbox1_exists()
	{
		$this->assertType("array",$this->props["textbox1"]);
		$this->assertEquals("textbox",$this->props["textbox1"]["type"]);
	}
	
	function test_img1_releditor_use_form()
	{
		$this->assertType("array",$this->props["img1"]);
		$this->assertEquals("releditor",$this->props["img1"]["type"]);
		$this->assertEquals("RELTYPE_IMAGE",$this->props["img1"]["reltype"]);
		$this->assertEquals("emb",$this->props["img1"]["use_form"]);
	}
	
	function test_img2_releditor_props()
	{
		$this->assertType("array",$this->props["img2"]);
		$this->assertEquals("releditor",$this->props["img2"]["type"]);
		$this->assertEquals("RELTYPE_IMAGE",$this->props["img2"]["reltype"]);
		$this->assertEquals(2,sizeof($this->props["img2"]["props"]));
		$this->assertEquals("file",$this->props["img2"]["props"][0]);
		$this->assertEquals("comment",$this->props["img2"]["props"][1]);
	}

	function test_groupinfo_exists()
	{
		$groups = $this->cfgu->get_groupinfo();
		$this->assertType("array",$groups);
		$this->assertTrue(sizeof($groups) > 0);
	}
	
	function test_groupinfo_general_exists()
	{
		$groups = $this->cfgu->get_groupinfo();
		$this->assertType("array",$groups["general"]);
	}
	
	function test_groupinfo_parent1_exists()
	{
		$groups = $this->cfgu->get_groupinfo();
		$this->assertType("array",$groups["parentgroup1"]);
	}
	
	function test_groupinfo_parent1_quoted_caption()
	{
		$groups = $this->cfgu->get_groupinfo();
		$this->assertEquals("Parent Group 1",$groups["parentgroup1"]["caption"]);
	}
	
	function test_groupinfo_childgroup1()
	{
		$groups = $this->cfgu->get_groupinfo();
		$this->assertEquals($groups["childgroup1"]["parent"],"parentgroup1");
		$this->assertEquals($groups["childgroup1"]["submit"],"no");
	}
	
	function test_groupinfo_childgroup2()
	{
		$groups = $this->cfgu->get_groupinfo();
		$this->assertEquals($groups["childgroup2"]["parent"],"parentgroup1");
		$this->assertEquals($groups["childgroup2"]["submit_method"],"get");
	}
	
	function test_classinfo_exists()
	{
		$clinf = $this->cfgu->get_classinfo();
		$this->assertType("array",$clinf);
		$this->assertTrue(sizeof($clinf) > 0);
	}
	
	function test_relinfo_exists()
	{
		$relinfo = $this->cfgu->get_relinfo();
		$this->assertType("array",$relinfo);
		$this->assertTrue(sizeof($relinfo) > 0);
	}
	
	function test_reltype_menu()
	{
		$relinfo = $this->cfgu->get_relinfo();
		$menu = $relinfo["RELTYPE_MENU"];
		$this->assertType("array",$menu);
		$this->assertTrue(sizeof($menu) > 0);
		$this->assertEquals(1,$menu["value"]);
		$this->assertEquals("Link to menu",$menu["caption"]);
		$this->assertType("array",$menu["clid"]);
		$this->assertEquals(1,sizeof($menu["clid"]));
		$this->assertEquals(CL_MENU,$menu["clid"][0]);
	}
	
	function test_reltype_multi()
	{
		$relinfo = $this->cfgu->get_relinfo();
		$menu = $relinfo["RELTYPE_MULTI"];
		$this->assertType("array",$menu);
		$this->assertTrue(sizeof($menu) > 0);
		$this->assertEquals(2,$menu["value"]);
		$this->assertEquals("Link with 2 clids",$menu["caption"]);
		$this->assertType("array",$menu["clid"]);
		$this->assertEquals(2,sizeof($menu["clid"]));
		$this->assertEquals(CL_MENU,$menu["clid"][0]);
		$this->assertEquals(CL_IMAGE,$menu["clid"][1]);
	}
	
	function test_tableinfo_exists()
	{
		$tableinfo = $this->cfgu->get_tableinfo();
		$this->assertType("array",$tableinfo);
		$this->assertTrue(sizeof($tableinfo) > 0);
	}
	
	function test_tableinfo_proptest()
	{
		$tableinfo = $this->cfgu->get_tableinfo();
		$proptest = $tableinfo["proptest"];
		$this->assertType("array",$proptest);
		$this->assertEquals(3,sizeof($proptest));
		$this->assertEquals("aw_id",$proptest["index"]);
		$this->assertEquals("objects",$proptest["master_table"]);
		$this->assertEquals("brother_of",$proptest["master_index"]);
	}

}

?>
