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
		if (!$parent)
		{
			// parse the documents parent from the url
			$parent = $params["parent"];
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

	/**
		@attrib name=manager
		@param docid optional
	**/
	function manager($arr)
	{
		classload("vcl/table");
		$t = new vcl_table;
		$this->_init_t($t, t("Fail"));

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
				"caption" => parse_obj_name($o->name()),
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
		@attrib name=attach_to_message 
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

	/**
		@attrib name=compress all_args=1
		@param files optional
			file id's
		@returns compressed file path
	**/
	function compress($arr)
	{
		extract($arr);

		$file_inst = get_instance(CL_FILE);
		$zip = new ZipArchive;

		if($GLOBALS["sited"])
		{
			$zipfilename = $GLOBALS["sited"]."/files/".time()."files.zip";
		}
		else
		{
			$zipfilename = $GLOBALS["site_dir"]."/files/".time()."files.zip";
		}
		if ($zip->open($zipfilename, ZIPARCHIVE::CREATE)!==TRUE) {
			exit("cannot open <$zipfilename>\n");
		}

		foreach($files as $id)
		{
			if(!$this->can("view" , $id)) continue;

			$fileo = obj($id);
			if($fileo->class_id() == CL_FILE)
			{
				$this->zip_add_file(&$zip , $id);
			}
			elseif($fileo->class_id() == CL_MENU)
			{
				$this->zip_add_menu(&$zip , $id);
			}
			else
			{
				continue;
			}
		}

		$zip->close();
		return $zipfilename;
	}

	function zip_add_menu($zip,$id,$path = "")
	{
		$fileo = obj($id);
		$zip->addEmptyDir($path."/".$fileo->name());
		$files = new object_list(array(
			"class_id" => array(CL_MENU,CL_FILE),
			"site_id" => array(),
			"lang_id" => array(),
			"parent" => $id
		));
		foreach($files->arr() as $file)
		{
			if($file->class_id() == CL_MENU)
			{
				$this->zip_add_menu(&$zip,$file->id(),$path."/".$fileo->name());
			}
			if($file->class_id() == CL_FILE)
			{
				$this->zip_add_file(&$zip,$file->id(),$path."/".$fileo->name());
			}
		}
	}

	function zip_add_file($zip,$id,$path = "")
	{
		$file_inst = get_instance(CL_FILE);
		$fileo = obj($id);
		$file_data = $file_inst->get_file_by_id($id);
		$filepath = $file_data["properties"]["file"];
		$filename = $file_data["properties"]["name"];
		$filepath = str_replace("/new/" , "/" , $filepath);
		$zip->addFile($filepath,$path."/".$filename);
	}

	/**
		@attrib name=compress_submit
	**/
	function compress_submit($arr)
	{
		$field_name = "sel";
		$zip_name = "cfiles.zip";
		if($_GET["field_name"])
		{
			$field_name = $_GET["field_name"];
		}
		if($_GET["zip_name"])
		{
			$zip_name = $_GET["zip_name"];
		}
		if($arr[$field_name])
		{
			$fname = $this->compress(array(
				"files" => $arr[$field_name],
			));
			header("Content-type: application/zip");
			header("Content-length: ".filesize($fname));
 			header("Content-disposition: inline; filename=".$zip_name.";");
			readfile($fname);
			unlink($fname);
		}
		die();
	}

	/** Adds zip compress button to toolbar.
		@attrib params=name api=1
		@param tb required type=object
			Toolbar object
		@param tooltip optional type=string
			Text to be displayed for the button
		@param img optional type=string
			Icon url to display.
		@param confirm optional type=string
			If is set, asks for confirmation displaying given text as question.
		@param zip_name optional type=string
			Zip file name
		@param field_name optional type=string default=sel

		@examples
			$tmp = get_instance("vcl/toolbar");
			$tmp->add_zip_button(array(
				"name" => "asd",
				"tooltip" => t("zip"),
				"confirm" => t("Oled sa kindel et tahad seda jama pakkida?"),
			));
	**/
	function add_zip_button($args = array())
	{
		$tb = &$args["tb"];
		unset($args["tb"]);
		$args['url'] = "javascript: void(0)";
		$args["name"] = "zip";
		
		if(!$args["img"])
		{
			$args['img'] = 'archive_small.gif';
		}
		if(!$args["tooltip"])
		{
			$args["tooltip"] = t("Lae alla ZIP failina");
		}

		$url = aw_global_get("baseurl")."/orb.aw?class=file_manager&action=compress_submit";
		if(!$args["field_name"])
		{
			$url.="&files_var=".$args["field_name"];
		}
		if(!$args["zip_name"])
		{
			$url.="&zip_name=".$args["zip_name"];
		}

		unset($args["field_name"]);
		$args['onClick'] = "
			tmp = $('form').attr('action');
			win = window.open('','Window1','menubar=no,width=300,height=500,toolbar=no');
			document.changeform.action='".$url."';
			document.changeform.target='Window1';
			document.changeform.submit();
			$('form').attr('action', tmp);
		";

		$tb->add_button($args);
	}
}
?>
