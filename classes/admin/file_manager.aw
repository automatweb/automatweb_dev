<?php
/*
@classinfo maintainer=kristo
*/

class file_manager extends aw_template
{
	function file_manager()
	{
		$this->init("admin/file_manager");
	}

	/**
		@attrib name=manage default=1
		@param doc required
		@param link_url optional
	**/
	function manage($arr)
	{
		$url = $arr["link_url"];
		$parts = parse_url($url);
		$path = $parts["path"];
		$imgname = substr($path, strrpos($path, "id=")+3);
		$imgname = substr($imgname, 0, strpos($imgname, "/"));
		if ($imgname != "")
		{
			// now get image by file name
			$image_list = new object_list(array(
				"class_id" => CL_FILE,
				"lang_id" => array(),
				"site_id" => array(),
				"oid" => $imgname
			));
		}
		else
		{
			$image_list = new object_list();
		}

		// this disables the option to change file's properties with mouse right-click, a new file will be added always
		$parent = aw_ini_get("file.default_folder");
		parse_str($arr["doc"], $params);
		$doc = obj($params["id"]);
		if (!$parent)
		{
			$parent = $doc->parent();
		}
		$image_url = html::get_new_url(CL_FILE, $parent, array("in_popup"=>1, "docid" => $doc->id()));
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
		$parent = aw_ini_get("file.default_folder");
		parse_str($arr["doc"], $params);
		$doc = obj($params["id"]);
		if (!$parent)
		{
			$parent = $doc->parent();
		}
		$this->vars(array(
			"img_new" => html::get_new_url(CL_FILE, $parent, array("in_popup"=>1)),
			"img_mgr" => $this->mk_my_orb("manager", array("docid" => $doc->id())),
			"new_file_t" => t("Uus fail"),
			"existing_file_t" => t("Vali olemasolev fail")
		));
		die($this->parse());
	}

	function _init_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Fail"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "location",
			"caption" => t("Asukoht"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Vali"),
		));
	}

	/**
		@attrib name=manager
		@param docid optional
	**/
	function manager($arr)
	{
		classload("vcl/table");
		$t = new vcl_table;
		$this->_init_t($t);

		$ol = new object_list(array(
			"class_id" => CL_FILE,
			"lang_id" => array(),
			"site_id" => array(),
			"name" => "%".$_GET["s"]["name"]."%",
			"limit" => ($_GET["s"]["name"] == "" || $_GET["s"]["last"]) ? 30 : NULL,
			"createdby" => ($_GET["s"]["my"])?aw_global_get("uid"):"%",
			"sort_by" => "objects.created DESC",
		));
		
		//kui lisada tahaks maili, siis parem attatchmentina
		if(is_oid($arr["docid"]) && $this->can("view" , $arr["docid"]))
		{
			$doc = obj($arr["docid"]);
			if($doc->class_id() == CL_MESSAGE)
			{
				$doctype = "mail";
			}
		}

		$ii = get_instance(CL_FILE);
		foreach($ol->arr() as $o)
		{
			$url = $this->mk_my_orb("fetch_file_tag_for_doc", array("id" => $o->id()), CL_FILE);
			$gen_alias_url = $this->mk_my_orb("gen_file_alias_for_doc", array(
				"doc_id" => $arr["docid"],
				"file_id" => $o->id(),
			), CL_FILE);
			$image_url = $ii->get_url($o->id(), $o->name());
			$link_name = $o->name();
			$location = $this->gen_location_for_obj($o);
			
			$name = html::href(array(
				"caption" => $o->name(),
				"url" => $this->mk_my_orb("change", array(
					"id" => $o->id(),
					"return_url" => get_ru(),
					"in_popup" => 1,
				), CL_FILE),
			));
		//	arr($this->mk_my_orb("attach_to_message", array("file" => $o->id() , "message" => $arr["docid"])));
			$t->define_data(array(
				"name" => $name,
				"location" => $location,
				"sel" => html::href(array(
//					"url" => "javascript:void(0)",
					"url" => ($doctype == "mail") ? $this->mk_my_orb("attach_to_message", array("file" => $o->id() , "message" => $arr["docid"])): "javascript:void(0)",
					"caption" => t("Vali see"),
					"onClick" => ($doctype == "mail") ? null:"
						FCK=window.parent.opener.FCK;
						var eSelected = FCK.Selection.MoveToAncestorNode(\"A\") ; 
						aw_get_url_contents(\"".$gen_alias_url."\");
						if (eSelected)
						{
							eSelected.href=\"$image_url\";
							eSelected.innerHTML=\"$link_name\";
							SetAttribute( eSelected, \"_fcksavedurl\", \"$image_url\" ) ;
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
		return "<script language=javascript>function SetAttribute( element, attName, attValue ) { if ( attValue == null || attValue.length == 0 ) {element.removeAttribute( attName, 0 ) ;} else {element.setAttribute( attName, attValue, 0 ) ;}}</script> ".$this->draw_form($arr).$t->draw();
	}

	/**
		@attrib name=attach_to_message default=1
		@param message optional
		@param file optional
	**/
	function attach_to_message($arr)
	{
		extract($arr);
		if(is_oid($message) && $this->can("view" , $message) && is_oid($file) && $this->can("view" , $file))
		{
			$message = obj($message);
			$message->connect(array(
				"to" => $file,
				"type" => "RELTYPE_ATTACHMENT",
			));
			
		}
		$stuff = ".";
		$stuff.= '<script type="text/javascript">
			//alert(window.parent.opener);
			//el = window.parent.opener.parent.document.changeform.submit();
			//tmp = ""
			//i=1
			//for (key in el)
			//{
			//if (i%2==0)
			//br = "\n";
			//else
			//br = " "
			
			//  tmp+=key+br
			//i++
			//}
			//alert (tmp)
			window.parent.opener.parent.document.changeform.submit();
			//if (window.parent.opener) {window.parent.opener.document.getElementByName(\"changeform\").submit();}
			window.parent.close();
		</script>';
		die($stuff);
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
				"orb_class" => "file_manager",
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
