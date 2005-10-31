<?php
// change does a lot of work and I want to write tests for the resulting data structures,
// there is no point in doing the same work over and over again in setUp

// any ideas on how do it this in OO Style? TestDecorator?
$cb = get_instance("cfg/proptest");
$cb->change(array(
	"id" => 1,
	"cbcli" => "debugclient",
));
// tests output of proptest class
class proptest_test extends PHPUnit_TestCase
{
	var $cb;
	function proptest_test($name)
	{
		$this->PHPUnit_TestCase($name);

	}

	function setUp()
	{
		$this->testclass = "proptest";
		$this->id = 1;
		global $cb;
		$this->cb = &$cb;
	}

	function test_reforb_classname()
	{
		// class should be the same that was requested
		$this->assertEquals($this->testclass,$this->cb->cli->formdata["class"]);
	}
	
	function test_reforb_action()
	{
		$this->assertEquals("submit",$this->cb->cli->formdata["action"]);
	}
	
	function test_reforb_group()
	{
		$this->assertEquals("general",$this->cb->cli->formdata["group"]);
	}
	
	function test_reforb_id()
	{
		$this->assertEquals($this->id,$this->cb->cli->formdata["id"]);
	}
	
	function test_tab_general_exists()
	{
		$this->assertType("array",$this->cb->cli->tabs["general"]);
	}
	
	function test_tab_general_active()
	{
		$this->assertEquals(true,$this->cb->cli->tabs["general"]["active"]);
	}
	
	function test_list_aliases()
	{
		$this->assertType("array",$this->cb->cli->tabs["list_aliases"]);
	}

	function test_focus_el()
	{
		$this->assertEquals("name",$this->cb->cli->focus_el);
	}
	
	function test_submit_method()
	{
		$this->assertEquals("POST",$this->cb->cli->formdata["method"]);
	}

	function test_prop_ignore()
	{
		$this->assertFalse(is_array($this->cb->cli->proplist["get_property_prop_ignore"]),"get_property_prop_ignore should not reach output client.");


	}
	
	function test_prop_error()
	{
		$this->assertTrue(is_array($this->cb->cli->proplist["get_property_prop_error"]));
		$this->assertEquals("text",$this->cb->cli->proplist["get_property_prop_error"]["type"]);
		$this->assertTrue(isset($this->cb->cli->proplist["get_property_prop_error"]["error"]));
	}
	
	function test_callback_on_load()
	{
		$this->assertTrue($this->cb->inst->on_load_called);
		
	}
	
	function test_callback_pre_edit()
	{
		$this->assertTrue($this->cb->inst->pre_edit_called);
	}
	
	function test_callback_mod_reforb()
	{
		$this->assertTrue($this->cb->inst->mod_reforb_called);
		$this->assertEquals("works",$this->cb->cli->formdata["data"]["added_by_mod_reforb"]);
		
	}

}

?>
