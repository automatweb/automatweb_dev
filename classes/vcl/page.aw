<?php
// $Id: page.aw,v 1.1 2002/11/07 12:40:13 duke Exp $
// page.aw - Generic HTML page
/*
	@default table=objects
	@default group=general

	@property background type=imgupload field=meta method=serialize
	@caption Taustapilt

	@property bgcolor type=colorpicker field=meta method=serialize
	@caption Tausta värv

	@property margin type=textbox size=4 field=meta method=serialize
	@caption Servade laius

	@property defayltstyle type=objpicker clid=CL_STYLE field=meta method=serialize
	@caption Default stiil

	@property preview type=text
	@caption Eelvaade

*/
class page extends aw_template
{
	function page()
	{
		$this->init(array(
			"clid" => CL_PAGE,
			"tpldir" => "page",
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "preview":
				classload("html");
				$id = $args["obj"]["oid"];
				if ($id)
				{
					$data["value"] = html::href(array("url" => $this->cfg["baseurl"] . "/orb.aw?class=page&action=show&id=$id","caption" => "Näita lehte","target" => "_blank"));
				};

		};
	}

	function show($args)
	{
		extract($args);
		$this->read_template("basic.tpl");
		$obj = $this->get_object($id);
		if ($obj["class_id"] != CL_PAGE)
		{
			die("kõtt");
		};
		if ($section)
		{
			$m = get_instance("menuedit");
			$content = $m->show_documents($section,0);
		}
		else
		{
			$content = $args["content"];
		};
		$this->vars(array(
			"margin" => (int)$obj["meta"]["margin"],
			"bgcolor" => $obj["meta"]["bgcolor"],
			"content" => $content,
		));
		print $this->parse();
		exit;
	}
	


};
?>
