<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/gallery/mini_gallery.aw,v 1.1 2004/04/12 12:02:09 kristo Exp $
// mini_gallery.aw - Minigalerii 
/*

@classinfo syslog_type=ST_MINI_GALLERY relationmgr=yes no_status=1

@default table=objects
@default group=general

@property folder type=relpicker reltype=RELTYPE_IMG_FOLDER field=meta method=serialize
@caption Piltide kataloog

@property cols type=textbox size=5 field=meta method=serialize
@caption Tulpi

@property rows type=textbox size=5 field=meta method=serialize
@caption ridu

@reltype IMG_FOLDER value=1 clid=CL_MENU
@caption piltide kataloog
*/

class mini_gallery extends class_base
{
	function mini_gallery()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/gallery/mini_gallery",
			"clid" => CL_MINI_GALLERY
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

		$images = new object_list(array(
			"class_id" => CL_IMAGE,
			"parent" => $ob->prop("folder")
		));

		$img_c = $images->count();
		$rows = $img_c / $ob->prop("cols");
		$cols = $ob->prop("rows");
		$img = $images->begin(); 

		$ii = get_instance("image");

		$str = "";
		for ($r = 0; $r < $rows; $r++)
		{
			$l = "";
			for($c = 0; $c < $cols; $c++)
			{
				if ($imgc < $img_c)
				{
					$tmp = $ii->parse_alias(array(
						"alias" => array(
							"target" => $img->id()
						)
					));
					$this->vars(array(
						"imgcontent" => $tmp["replacement"]

					));

					$img = $images->next();
					$imgc ++;
				}
				else
				{
					$this->vars(array(
						"imgcontent" => ""
					));
				}
				$l .= $this->parse("COL");
			}

			$this->vars(array(
				"COL" => $l
			));
			$str .= $this->parse("ROW");
		}

		$this->vars(array(
			"ROW" => $str
		));

		return $this->parse();
	}
}
?>
