<?php
// $Header: /home/cvs/automatweb_dev/classes/chat/Attic/chat_list.aw,v 1.2 2002/12/19 18:04:25 duke Exp $
// chat_list.aw - Jutuka listiobjekt
/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property host type=textbox
	@caption Deemoni host

	@property port type=textbox
	@caption Kuulatav port

*/


class chat_list extends class_base
{
	function chat_list()
	{
		$this->init(array(
			"tpldir" => "chat_list",
			"clid" => CL_CHAT_LIST,
		));
	}

	function parse_alias($arr)
	{
		extract($arr);

		$dat = $this->get_object($alias["target"]);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $dat["name"],
			"uid" => aw_global_get("uid"),
			"host" => $dat["meta"]["host"],
			"port" => $dat["meta"]["port"],
			"nimi" => $dat["name"],
		));

		return $this->parse();
	}
}
?>
