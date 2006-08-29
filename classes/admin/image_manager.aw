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
				"class_id" => CL_IMAGE,
				"lang_id" => array(),
				"site_id" => array(),
				"file" => "%".trim($imgname)
			));
		}
		else
		{
			$image_list = new object_list();
		}

		parse_str($arr["doc"], $params);
		$doc = obj($params["id"]);

		if ($image_list->count())
		{
			$imgo = $image_list->begin();
			$image_url = html::get_change_url($imgo->id());
		}
		else
		{
			$parent = aw_ini_get("image.default_folder");
			if (!$parent)
			{
				$parent = $doc->parent();
			}
			$image_url = html::get_new_url(CL_IMAGE, $parent);
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
			"name" => "location",
			"caption" => t("Asukoht"),
			"sortable" => 1,
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
			"site_id" => array(),
			"name" => "%".$_GET["s"]["name"]."%",
			"limit" => ($_GET["s"]["name"] == "" || $_GET["s"]["last"]) ? 30 : NULL,
			"createdby" => ($_GET["s"]["my"])?aw_global_get("uid"):"%",
			"sort_by" => "objects.created DESC",
		));

		$ii = get_instance(CL_IMAGE);
		foreach($ol->arr() as $o)
		{
			$url = $this->mk_my_orb("fetch_image_tag_for_doc", array("id" => $o->id()), CL_IMAGE);
			$image_url = $ii->get_url_by_id($o->id());
			$gen_alias_url = $this->mk_my_orb("gen_image_alias_for_doc", array(
				"img_id" => $o->id(),
				"doc_id" => $arr["docid"],
			), CL_IMAGE);
			$location = $this->gen_location_for_obj($o);
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"location" => $location,
				"sel" => html::href(array(
					"url" => "javascript:void(0)",
					"caption" => t("Vali see"),
					"onClick" => "
						FCK=window.parent.opener.FCK;
						var eSelected = FCK.Selection.GetSelectedElement() ; 
						aw_get_url_contents(\"".$gen_alias_url."\");
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
		return $this->draw_form($arr).$t->draw();
	}

	function draw_form($arr)
	{
		classload("cfg/htmlclient");
		$htmlc = new htmlclient(array(
			'template' => "default",
		));
		$htmlc->start_output();

		// search by name, url
		$htmlc->add_property(array(
			"name" => "s[name]",
			"type" => "textbox",
			"caption" => t("Nimi"),
			"value" => $_GET["s"]["name"]
		));

		$htmlc->add_property(array(
			"name" => "s[submit]",
			"type" => "submit",
			"value" => t("Otsi"),
		));

		$htmlc->add_property(array(
			"name" => "s[my]",
			"type" => "checkbox",
			"caption" => t("Minu lisatud"),
			"value" => $_GET["s"]["my"],
		));

		$htmlc->add_property(array(
			"name" => "s[last]",
			"type" => "checkbox",
			"caption" => t("Viimased 30"),
			"value" => $_GET["s"]["last"],
		));

		$htmlc->finish_output(array(
			"action" => "manager",
			"method" => "GET",
			"data" => array(
				"docid" => $arr["docid"],
				"orb_class" => "image_manager",
				"reforb" => 0
			)
		));

		$html = $htmlc->get_result();
		return $html;
	}

	function gen_location_for_obj($o)
	{
		$o = obj($o->parent());
		for($i=0;$i<3;$i++)
		{
			$ret[] = $o?$o->name():NULL;
			$o = (($o) && $s = $o->parent())?obj($s):false;
		}
		return join(" / ", array_reverse($ret));
	}

}
?>
