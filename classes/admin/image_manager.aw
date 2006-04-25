<?php

class image_manager extends aw_template
{
	function image_manager()
	{
		$this->init("admin/image_manager");
	}

	/**
		@attrib name=manage default=1
		@param doc required
	**/
	function manage($arr)
	{
		$this->read_template("manage.tpl");
		$parent = aw_ini_get("image.default_folder");
		parse_str($arr["doc"], $params);
		$doc = obj($params["id"]);
		if (!$parent)
		{
			$parent = $doc->parent();
		}

		$this->vars(array(
			"topf" => $this->mk_my_orb("topf", $arr),
			"image" => html::get_new_url(CL_IMAGE, $parent)
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
		$parent = aw_ini_get("image.default_folder");
		parse_str($arr["doc"], $params);
		$doc = obj($params["id"]);
		if (!$parent)
		{
			$parent = $doc->parent();
		}
		$this->vars(array(
			"img_new" => html::get_new_url(CL_IMAGE, $parent),
			"img_mgr" => $this->mk_my_orb("manager", array("docid" => $doc->id()))
		));
		return $this->parse();
	}

	function _init_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Pilt"),
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
			"class_id" => CL_IMAGE,
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			$url = $this->mk_my_orb("fetch_image_tag_for_doc", array("id" => $o->id()), CL_IMAGE);

			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"sel" => html::href(array(
					"url" => "javascript:void(0)",
					"caption" => t("Vali see"),
					"onClick" => "ct=aw_get_url_contents(\"$url\");FCK=window.parent.opener.FCK;FCK.Focus();FCK.InsertHtml(ct);"
				))
			));
		}
		$t->set_default_sortby("name");
		$t->sort_by();
		return $t->draw();
	}
}
?>