<?php

class link_manager extends aw_template
{
	function link_manager()
	{
		$this->init("admin/link_manager");
	}

	/**
		@attrib name=manage default=1
		@param doc required
		@param imgsrc optional
	**/
	function manage($arr)
	{
		$url = $arr["imgsrc"];
		$parts = parse_url($url);
		$path = $parts["path"];
		$imgname = substr($path, strrpos($path, "=")+1);
		if ($imgname != "")
		{
			// now get image by file name
			$image_list = new object_list(array(
				"class_id" => CL_EXTLINK,
				"lang_id" => array(),
				"site_id" => array(),
				"file" => "%".trim($imgname)
			));
		}
		else
		{
			$image_list = new object_list();
		}

		if ($image_list->count())
		{
			$imgo = $image_list->begin();
			$image_url = html::get_change_url($imgo->id());
		}
		else
		{
			$parent = aw_ini_get("links.default_folder");
			parse_str($arr["doc"], $params);
			$doc = obj($params["id"]);
			if (!$parent)
			{
				$parent = $doc->parent();
			}
			$image_url = html::get_new_url(CL_EXTLINK, $parent);
		}
		
		$this->read_template("manage.tpl");

		$this->vars(array(
			"topf" => $this->mk_my_orb("topf", $arr),
			"image" => $image_url
		));
		die($this->parse());
	}

	/**
		@attrib name=topf 
		@param doc required
	**/
	function topf($arr)
	{
		$this->read_template("top_frame.tpl");
		$parent = aw_ini_get("links.default_folder");
		parse_str($arr["doc"], $params);
		$doc = obj($params["id"]);
		if (!$parent)
		{
			$parent = $doc->parent();
		}
		$this->vars(array(
			"img_new" => html::get_new_url(CL_EXTLINK, $parent),
			"img_mgr" => $this->mk_my_orb("manager", array("docid" => $doc->id()))
		));
		return $this->parse();
	}

	function _init_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Link"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Vali"),
		));
	}

	/**
		@attrib name=manager
		@param docid required
	**/
	function manager($arr)
	{
		classload("vcl/table");
		$t = new vcl_table;
		$this->_init_t($t);

		$ol = new object_list(array(
			"class_id" => CL_EXTLINK,
			"lang_id" => array(),
			"site_id" => array()
		));
		$ii = get_instance(CL_EXTLINK);
		foreach($ol->arr() as $o)
		{
			$url = $this->mk_my_orb("fetch_file_tag_for_doc", array("id" => $o->id()), CL_FILE);
			$image_url = $o->prop("url");
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"sel" => html::href(array(
					"url" => "javascript:void(0)",
					"caption" => t("Vali see"),
					"onClick" => "
						FCK=window.parent.opener.FCK;
						var eSelected = FCK.Selection.GetSelectedElement() ; 
						if (\"\"+eSelected == \"HTMLImageElement\")
						{
							eSelected.src=\"$image_url\";
						}
						else
						{
							ct=aw_get_url_contents(\"$url\");
							FCK.InsertHtml(ct);		
						}
						window.parent.close();
					"
				))
			));
		}
		$t->set_default_sortby("name");
		$t->sort_by();
		return $t->draw();
	}
}
?>