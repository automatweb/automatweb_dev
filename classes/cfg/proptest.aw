<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/proptest.aw,v 1.2 2005/10/31 20:54:32 duke Exp $
// proptest.aw - Property Test File, for unit tests
// Feel free to add new things and write new tests, but if you change any existing ones, then make sure
// that you update any relevant tests as well
/*

@classinfo syslog_type=ST_PROPTEST relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property textbox1 type=textbox size=40
@caption Textbox1

@property img1 type=releditor reltype=RELTYPE_IMAGE use_form=emb
@caption Image 1

@property img2 type=releditor reltype=RELTYPE_IMAGE props=file,comment
@caption Image 2

@property get_property_prop_ignore type=textbox 
@caption This should be blocked by get_property

@property get_property_prop_error type=textbox 
@caption This should be flagged as error by get_property

@reltype MENU value=1 clid=CL_MENU
@caption Link to menu

@reltype MULTI value=2 clid=CL_MENU,CL_IMAGE
@caption Link with 2 clids

@reltype IMAGE value=3 clid=CL_IMAGE
@caption Image

@groupinfo parentgroup1 caption="Parent Group 1"
@groupinfo childgroup1 caption="Child Group 1" parent=parentgroup1 submit=no
@groupinfo childgroup2 caption="Child Group 2" parent=parentgroup1 submit_method=get

@tableinfo proptest index=aw_id master_table=objects master_index=brother_of

*/

class proptest extends class_base
{
	var $on_load_args;
	var $pre_edit_called = false;
	var $on_load_called = false;
	var $mod_reforb_called = false;
	function proptest()
	{
		$this->init(array(
			"clid" => CL_PROPTEST
		));
	}

	function callback_on_load($arr)
	{
		$this->on_load_called = true;
	}

	function callback_pre_edit($arr)
	{
		$this->pre_edit_called = true;
	}

	function get_property($arr)
	{
		$retval = PROP_OK;
		$prop = &$arr["prop"];
		switch($prop["name"])
		{
			case "get_property_prop_ignore":
				$retval = PROP_IGNORE;
				break;	

			case "get_property_prop_error":
				$prop["error"] = "error!";
				$retval = PROP_ERROR;
				break;
		};
		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$this->mod_reforb_called = true;
		$arr["added_by_mod_reforb"] = "works";
	}

}
?>
