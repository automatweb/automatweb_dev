<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/banner/banner_client.aw,v 1.4 2005/04/21 08:39:14 kristo Exp $

/*

@tableinfo banner_clients index=id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_BANNER_LOCATION relationmgr=yes
@default table=objects
@default group=general

@property html type=textarea cols=80 rows=10 table=banner_clients 
@caption Asukoha HTML

*/

class banner_client extends class_base
{
	function banner_client()
	{
		$this->init(array(
			"tpldir" => "banner",
			"clid" => CL_BANNER_CLIENT
		));

		$this->def_html = "<a href='/orb.aw?class=banner&action=proc_banner&gid=%s&click=1&ss=[ss]'><img src='/orb.aw?class=banner&action=proc_banner&gid=%s&ss=[ss]' border=0></a>";
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
		}

		return PROP_OK;
	}

	function callback_post_save($arr)
	{
		if ($arr["obj_inst"]->prop("html") == "")
		{
			$html = sprintf($this->def_html, $arr["obj_inst"]->id(),$arr["obj_inst"]->id());
			$arr["obj_inst"]->set_prop("html", $html);
			$arr["obj_inst"]->save();
		}
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		// return is html for banner area
		return str_replace("[ss]", "[ss".$arr["id"]."]",$ob->prop("html"));
	}	
}
?>
