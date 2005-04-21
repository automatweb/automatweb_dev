<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug.aw,v 1.3 2005/04/21 08:39:14 kristo Exp $
// bug.aw - Bugi 
/*

@classinfo syslog_type=ST_BUG relationmgr=yes no_comment=1 

@tableinfo aw_bug_o_matic_bugs index=id master_index=brother_of master_table=objects

@default table=aw_bug_o_matic_bugs
@default group=general

@property status type=select
@caption Staatus

@property priority type=select
@caption Prioriteet

@property severity type=select
@caption T&ouml;sidus

//////// inf 
@property reporter_browser type=classificator
@caption Brauser

@property reporter_os type=classificator
@caption OS

@property bug_class type=select
@caption Klass

@property component type=textbox 
@caption Komponent

@property url type=textbox size=100
@caption URL

@property content type=textarea rows=5 cols=80
@caption Sisu

*/

class bug extends class_base
{
	function bug()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/bug",
			"clid" => CL_BUG
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
