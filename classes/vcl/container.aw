<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/Attic/container.aw,v 1.1 2004/04/07 17:18:31 duke Exp $
// this is used to use contentmgmt classes inside class_base forms

class container extends class_base
{
	function container()
	{
		$this->init("");
	}

	function init_vcl_property($arr)
	{
		$prop = &$arr["property"];
		$val = "n/a";
		$name = $prop["name"];
		switch($prop["content"])
		{
			case "poll":
				/*
				$pl = get_instance(CL_POLL);
				$val = $pl->gen_user_html(126591);
				*/
				break;
		};

		$rv = $prop;
		$rv["type"] = $text;
		$rv["value"] = $val;
		$rv["no_caption"] = 1;

		return array($name => $rv);
	}

	function process_vcl_property($arr)
	{
		/*
		$comm = get_instance(CL_COMMENT);
		$commdata = $arr["prop"]["value"];
		$nc = $comm->submit(array(
			"parent" => $arr["obj_inst"]->id(),
			"commtext" => $commdata["comment"],
			"return" => "id",
		));
		*/
	}
};
?>
