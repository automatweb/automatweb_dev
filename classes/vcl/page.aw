<?php
// $Id: page.aw,v 1.8 2005/03/22 16:20:04 kristo Exp $
// page.aw - Generic HTML page
/*
	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize
	@classinfo relationmgr=yes syslog_type=ST_PAGE

	@property background type=relpicker reltype=RELTYPE_BACKGROUND_IMAGE 
	@caption Taustapilt

	@property bgcolor type=colorpicker 
	@caption Tausta värv

	@property margin type=textbox size=4 
	@caption Servade laius

	@property defayltstyle type=relpicker reltype=RELTYPE_PAGESTYLE 
	@caption Default stiil

	@property preview type=text store=no editonly=1
	@caption Eelvaade

	@reltype BACKGROUND_IMAGE value=1 clid=CL_IMAGE
	@caption Taustapilt

	@reltype PAGESTYLE value=2 clid=CL_STYLE
	@caption Stiil

*/
class page extends class_base
{
	function page()
	{
		$this->init(array(
			"clid" => CL_PAGE,
			"tpldir" => "page",
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		switch($data["name"])
		{
			case "preview":
				$id = $arr["obj_inst"]->id();
				$data["value"] = html::href(array(
					"url" => $this->cfg["baseurl"] . "/orb.aw?class=page&action=show&id=$id",
					"caption" => t("Näita lehte"),
					"target" => "_blank",
				));

		};
	}

	/**  
		
		@attrib name=show params=name default="0"
		
		@param id required
		@param section optional
		
		@returns
		
		
		@comment

	**/
	function show($arr)
	{
		extract($arr);
		$this->read_template("basic.tpl");
		$obj = new object($id);
		if ($obj->class_id() != CL_PAGE)
		{
			die(t("kõtt"));
		};
		if ($section)
		{
			$m = get_instance("contentmgmt/site_content");
			$content = $m->show_documents($section,0);
		}
		else
		{
			$content = $arr["content"];
		};
		$this->vars(array(
			"margin" => (int)$obj->prop("margin"),
			"bgcolor" => $obj->prop("bgcolor"),
			"content" => $content,
		));
		print $this->parse();
		exit;
	}
};
?>
