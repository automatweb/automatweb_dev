<?php
/*
@classinfo maintainer=markop
*/
class crm_company_docs_impl extends class_base
{
	function crm_company_docs_impl()
	{
		$this->init();

		$this->adds = array(
			CL_MENU => t("Kaust"),
			CL_CRM_DOCUMENT => t("CRM Dokument"),
			CL_CRM_DEAL => t("Leping"),
			CL_CRM_MEMO => t("Memo"),
			CL_DOCUMENT => t("Sisuhalduse dokument"),
			CL_FILE => t("Fail"),
			CL_CRM_OFFER => t("Pakkumine")
		);
	}

	function _init_docs_fld($o)
	{
		$fldo = $o->get_first_obj_by_reltype("RELTYPE_DOCS_FOLDER");
		if (!$fldo)
		{
			$fldo = obj();
			$fldo->set_parent($o->id());
			$fldo->set_class_id(CL_MENU);
			$fldo->set_name($o->name().t(" dokumendid"));
			$fldo->save();

			$o->connect(array(
				"to" => $fldo->id(),
				"reltype" => "RELTYPE_DOCS_FOLDER"
			));
		}

		return $fldo;
	}

	function _init_content_docs_fld($o)
	{
		$fldo = $o->get_first_obj_by_reltype("RELTYPE_CONTENT_DOCS_FOLDER");
		if (!$fldo)
		{
			$fldo = obj();
			$fldo->set_parent($o->id());
			$fldo->set_class_id(CL_MENU);
			$fldo->set_name($o->name().t(" uudised"));
			$fldo->save();

			$o->connect(array(
				"to" => $fldo->id(),
				"reltype" => "RELTYPE_CONTENT_DOCS_FOLDER"
			));
		}

		return $fldo;
	}

	function _get_docs_tb($arr)
	{
		$fld = $this->_init_docs_fld($arr["obj_inst"]);

		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		foreach($this->adds as $clid => $nm)
		{
			$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text' => $nm,
				'link' => html::get_new_url($clid, is_oid($arr["request"]["tf"]) ? $arr["request"]["tf"] : $fld->id(), array("return_url" => get_ru()))
			));
		}

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			'action' => 'submit_delete_docs',
		));

		$tb->add_button(array(
			'name' => 'cut',
			'img' => 'cut.gif',
			'tooltip' => t('L&otilde;ika'),
			'action' => 'cut_docs',
		));

		if (count(safe_array($_SESSION["crm_cut_docs"])))
		{
			$tb->add_button(array(
				'name' => 'paste',
				'img' => 'paste.gif',
				'tooltip' => t('Kleebi'),
				'action' => 'submit_paste_docs',
			));
		}

		$tb->add_separator();

		$inst = get_instance(CL_ADMIN_IF);
		$id = $inst->find_admin_if_id();
		$tb->add_button(array(
			"name" => "import",
			"tooltip" => t("Impordi faile"),
			"url" => $this->mk_my_orb("change", array("integrated" => 1, "id" => $id, "group" => "fu", "parent" => is_oid($arr["request"]["tf"]) ? $arr["request"]["tf"] : $fld->id(), "return_url" => get_ru()), CL_ADMIN_IF),
			"img" => "import.gif",
		));
	}

	/**
		@attrib name=get_tree_stuff all_args=1
	**/
	function get_tree_stuff($arr)
	{
		$seti = get_instance(CL_CRM_SETTINGS);
		$sts = $seti->get_current_settings();
		$classes = array(CL_MENU);

		extract($_GET); extract($_POST); extract($arr);
		$tree = get_instance("vcl/treeview");
		$tree->start_tree(array (
			"type" => TREE_DHTML,
			"branch" => 1,
			"tree_id" => "offers_tree",
			"persist_state" => 1
		));

		$ol = new object_list(array(
			"class_id" => $classes,
			"lang_id" => array(),
			"parent" => $parent,
			"sort_by" => "objects.name ASC",
		));
		$ol->sort_by(array(
			"prop" => "ord",
			"order" => "asc"
		));
		if ($sts && $sts->prop("show_files_and_docs_in_tree"))
		{
			$classes = array(CL_DOCUMENT,CL_FILE);
			$ol3 = new object_list(array(
				"class_id" => $classes,
				"lang_id" => array(),
				"parent" => $parent,
				"sort_by" => "objects.name ASC",
			));
			$ol3->sort_by(array(
				"prop" => "ord",
				"order" => "asc"
			));
			foreach($ol3->names() as $id => $name)
			{
				$ol->add($id);
			}
		}
		classload("core/icons");
		$file_inst = get_instance(CL_FILE);
//if(aw_global_get("uid") == "marko")arr(time() - $_SESSION["asdtime"]);
		foreach($ol->arr() as $o)
		{
			if(sizeof($classes) > 1)
			{
				$classes = array(CL_MENU,CL_DOCUMENT,CL_FILE);
			}
			$d = array(
				"id" => $o->id(),
				"name" =>  ($arr["active"]==$o->id()) ? "<b>".$o->name()."</B>":$o->name(),
				"iconurl" => icons::get_icon_url($o->class_id()),
				"url" => aw_url_change_var("tf", $o->id() , $set_retu),
			);
			if($o->class_id() == CL_FILE)
			{
				$d["url"] = $file_inst->get_url($o->id()).$o->name();
				$d["url_target"] = "new window";
			}

			$tree->add_item(0,$d);
			$ol2 = new object_list(array(
				"class_id" => $classes,
				"lang_id" => array(),
				"parent" => $o->id(),
			));

//			$o4 = $ol2->begin();
//			$ol2->sort_by(array(
//				"prop" => "ord",
//				"order" => "asc"
//			));

//			if ($sts && !$o4 &&  $sts->prop("show_files_and_docs_in_tree"))
//			{
//				$classes = array(CL_DOCUMENT,CL_FILE);
//				$ol4 = new object_list(array(
//					"class_id" => $classes,
//					"lang_id" => array(),
//					"parent" => $parent,
//					"sort_by" => "objects.name ASC",
//				));
//				$ol4->sort_by(array(
//					"prop" => "ord",
//					"order" => "asc"
//				));
//				$o4 = $ol4->begin();

//				foreach($ol4->names() as $id => $name)
//				{
//					$ol2->add($id);
//				}
//			}
			if($ol2->count())
			{
				$id = reset($ol2->ids());
				$tree->add_item($o->id(), array(
					"id" => $id,
					"name" => " ",
					"url" => " ",
				));
			}

//			foreach($ol2->arr() as $o2)
//			{
//				$tree->add_item($o->id(), array(
//					"id" => $o2->id(),
//					"name" => ($arr["active"]==$o2->id()) ? "<b>".$o2->name()."</B>":$o2->name(),
//					"url" => aw_url_change_var("tf", $o2->id(),$set_retu),
//				));
//			}
		}
//if(aw_global_get("uid") == "marko"){arr(time() - $_SESSION["asdtime"]);}
		die($tree->finalize_tree());
	}

	function _get_docs_tree($arr)
	{
		if ($arr["request"]["do_doc_search"])
		{
			return PROP_IGNORE;
		}

		$seti = get_instance(CL_CRM_SETTINGS);
		$sts = $seti->get_current_settings();
		$classes = array(CL_MENU);
//		if ($sts && $sts->prop("show_files_and_docs_in_tree"))
//		{
//			$classes = array(CL_MENU, CL_DOCUMENT,CL_FILE);
//		}

		if (!$arr["request"]["tf"] && $arr["request"]["files_from_fld"] == "")
		{
			$arr["request"]["files_from_fld"] = "/";
		}
		$fld = $this->_init_docs_fld($arr["obj_inst"]);

		classload("core/icons");
		$file_inst = get_instance(CL_FILE);
		$arr["prop"]["vcl_inst"]->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "crm_docs_t",
			"get_branch_func" => $this->mk_my_orb("get_tree_stuff",array(
				"set_retu" => get_ru(),
				"active" => $_GET["tf"],
				"parent" => " ",
			)),
			"has_root" => 1,
			"persist_state" => 1,
			"root_name" => "",
			"root_url" => "#",
			//"open_path" =>
			"root_icon" => "images/transparent.gif",
		));
		$arr["prop"]["vcl_inst"]->add_item(0,array(
			"id" => $fld->id(),
			"name" => ($_GET["tf"]==$fld->id()) ? "<b>".$fld->name()."</B>":$fld->name(),
			"iconurl" => icons::get_icon_url(CL_MENU),
			"url" => aw_url_change_var("tf", $fld->id()),
			"is_open" => 1,
		));
		$ol = new object_list(array(
			"class_id" => $classes,
			"lang_id" => array(),
			"parent" => $fld->id(),
			"sort_by" => "objects.name ASC",
			//"sortby" => "objects.class_id",
		));
		$ol->sort_by(array(
			"prop" => "ord",
			"order" => "asc"
		));

		if ($sts && $sts->prop("show_files_and_docs_in_tree"))
		{
			$classes = array(CL_DOCUMENT,CL_FILE);
			$ol3 = new object_list(array(
				"class_id" => $classes,
				"lang_id" => array(),
				"parent" => $fld->id(),
				"sort_by" => "objects.name ASC",
			));
			$ol3->sort_by(array(
				"prop" => "ord",
				"order" => "asc"
			));
			foreach($ol3->names() as $id => $name)
			{
				$ol->add($id);
			}
		}

		foreach($ol->arr() as $o)
		{
			$d = array(
				"id" => $o->id(),
				"name" => ($_GET["tf"]==$o->id()) ? "<b>".$o->name()."</B>":$o->name(),
				"iconurl" => icons::get_icon_url($o->class_id()),
				"url" => aw_url_change_var("tf", $o->id() , $set_retu),
			);
			if($o->class_id() == CL_FILE)
			{
				$d["url"] = $file_inst->get_url($o->id()).$o->name();
				$d["url_target"] = "new window";
			}

			$arr["prop"]["vcl_inst"]->add_item($fld->id(),$d);
			$ol2 = new object_list(array(
				"class_id" => array(CL_MENU),
				"lang_id" => array(),
				"parent" => $o->id(),
				"sort_by" => "objects.name ASC",
			));
			$ol2->sort_by(array(
				"prop" => "ord",
				"order" => "asc"
			));
			if ($sts && $sts->prop("show_files_and_docs_in_tree"))
			{
				$classes = array(CL_DOCUMENT,CL_FILE);
				$ol4 = new object_list(array(
					"class_id" => $classes,
					"lang_id" => array(),
					"parent" => $o->id(),
					"sort_by" => "objects.name ASC",
				));
				$ol4->sort_by(array(
					"prop" => "ord",
					"order" => "asc"
				));

				foreach($ol4->names() as $id => $name)
				{
					$ol2->add($id);
				}
			}
			foreach($ol2->names() as $id => $name)
			{
				$o2 = obj($id);
				$arr["prop"]["vcl_inst"]->add_item($o->id(), array(
					"id" => $o2->id(),
					"name" => $o2->name(),
					"url" => aw_url_change_var("tf", $o2->id()),
				));
			}
		}

		if(false)
		{
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"persist_state" => true,
				"tree_id" => "crm_docs_t",
			),
			"root_item" => $fld,
			"target_url" => aw_url_change_var("files_from_fld", null),
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $fld->id(),
				"sort_by" => "objects.jrk"
			)),
			"var" => "tf",
			"persist_state" => 1,
			"icon" => icons::get_icon_url(CL_MENU)
		));

		}
/*		foreach($arr["prop"]["vcl_inst"]->items as $id => $items)
		{
			foreach($items as $item)
			{
				if(aw_global_get("uid") == "marko") arr($item);
			}
		}
*/
		// if there is a server folder object attached, then get the rest of the folders from that
		$sf = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_SERVER_FILES");
		if ($sf)
		{
			$s = $sf->instance();
			$fld = $s->get_folders($sf);
			$t =& $arr["prop"]["vcl_inst"];
			$t->add_item(0, array(
				"id" => $sf->id(),
				"name" => ($arr["request"]["files_from_fld"] == "/" ? "<b>".iconv("utf-8", aw_global_get("charset"), $sf->name())."</b>" : iconv("utf-8", aw_global_get("charset"), $sf->name())),
				"url" => aw_url_change_var("files_from_fld", "/")
			));

			usort($fld, create_function('$a,$b', 'return strcmp($a["name"], $b["name"]);'));
			foreach($fld as $item)
			{
				$item["name"] = iconv("utf-8", aw_global_get("charset"), $item["name"]);
				if ($arr["request"]["files_from_fld"] == $item["id"])
				{
					$item["name"] = "<b>".$item["name"]."</b>";
				}
				$item["url"] = aw_url_change_var("files_from_fld", urlencode($item["id"]));
				$t->add_item($item["parent"] === 0 ? $sf->id() : $item["parent"], $item);
			}
		}

	}

	function _init_docs_tbl(&$t, $r)
	{
		$t->define_field(array(
			"caption" => "",
			"name" => "icon",
			"align" => "center",
			"sortable" => 0,
			"width" => 1
		));

		$t->define_field(array(
			"caption" => t("Nimi"),
			"name" => "name",
			"align" => "left",
			"sortable" => 1
		));

		if ($r["files_from_fld"] == "")
		{
			$t->define_field(array(
				"caption" => t("T&uuml;&uuml;p"),
				"name" => "class_id",
				"align" => "center",
				"sortable" => 1
			));
		}

		$t->define_field(array(
			"caption" => t("Looja"),
			"name" => "createdby",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Loodud"),
			"name" => "created",
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i"
		));

		if ($r["files_from_fld"] == "")
		{
			$t->define_field(array(
				"caption" => t("Muutja"),
				"name" => "modifiedby",
				"align" => "center",
				"sortable" => 1
			));
		}

		$t->define_field(array(
			"caption" => t("Muudetud"),
			"name" => "modified",
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i"
		));

		$t->define_field(array(
			"caption" => "",
			"name" => "pop",
			"align" => "center"
		));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function get_docs_table_header($o,$id)
	{
		$path = html::href(array(
			"url" => $this->mk_my_orb("change", array(
				"id" => $id,
				"return_url" => get_ru(),
				"group" => "documents_all",
				"docs_s_sbt" => $_GET["docs_s_sbt"],
				"docs_s_created_after" => $_GET["docs_s_created_after"],
				"tf" => $o->id(),
			),
			 CL_CRM_COMPANY),
			"caption" => $o->name()?$o->name():"",
		));

		if($this->can("view" , $o->parent()) && $o->parent() != $id)
		{
			$path = $this->get_docs_table_header(obj($o->parent()),$id).  " > " .$path;
		}
		return $path;
	}

	function _get_docs_tbl($arr)
	{
		/*if (!$arr["request"]["tf"] && !$arr["request"]["files_from_fld"])
		{
			$arr["request"]["files_from_fld"] = "/";
		}*/

		$t =& $arr["prop"]["vcl_inst"];
		$o = obj($arr["request"]["tf"]);
		if(!$arr["request"]["tf"])
		{
			$format = t("%s dokumendid");
			$format = strlen($arr["request"]["tf"])?$format.t(", kataloog: %s"):$format;
			$path =  sprintf($format, $arr['obj_inst']->name(), $o->name());
		}
		else
		{
			$path = $this->get_docs_table_header($o,$arr['obj_inst']->id());
		}
	
		$t->set_caption($path);
		$this->_init_docs_tbl($t, $arr["request"]);
		if ($arr["request"]["files_from_fld"] != "")
		{
			$sf = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_SERVER_FILES");
			if ($sf)
			{
				$i = $sf->instance();
				$ob = $i->get_objects($sf, NULL, $arr["request"]["files_from_fld"], array(
					"get_server_paths" => true
				));
				usort($ob, create_function('$a,$b', 'return strcmp($a["name"], $b["name"]);'));
				foreach($ob as $nm => $dat)
				{
					$pm = get_instance("vcl/popup_menu");
					$pm->begin_menu("sf".$dat["id"]);

					$pm->add_item(array(
						"text" => t("Ava internetist"),
						"link" => $dat["inet_url"]
					));
					$pm->add_item(array(
						"text" => t("Ava serverist"),
						"link" => $dat["url"]
					));
					$pm->add_item(array(
						"text" => t("Laadi uus versioon"),
						"link" => $dat["change_url"]
					));
					$url = $dat["url"];
					$url = iconv("utf-8", aw_global_get("charset")."//IGNORE", $url);
					$t->define_data(array(
						"name" => html::href(array("url" => $url, "caption" => iconv("utf-8", aw_global_get("charset"), $dat["name"]))),
						"created" => $dat["add_date"],
						"modified" => $dat["mod_date"],
						"createdby" => $dat["adder"],
						"pop" => $pm->get_menu(),

					));
				}
				$t->set_default_sortby("name");
				return;
			}
		}

		$fld = $this->_init_docs_fld($arr["obj_inst"]);

		if (($arr["request"]["do_doc_search"] || $arr["request"]["docs_s_sbt"] != "") && !$arr["request"]["tf"])
		{
			// get all parents to search from
			$parent_tree = new object_tree(array(
				"parent" => $fld->id(),
				"class_id" => CL_MENU
			));
			$parent_ol = $parent_tree->to_list();
			$parents = $parent_ol->ids();
			$parents[] = $fld->id();
			$f = $this->_get_doc_search_f($arr["request"], $parents);
			$f["site_id"] = array();
			$f["lang_id"] = array();
			$ol = new object_list($f);
		}
		else
		{
			$ol = new object_list(array(
				"parent" => is_oid($arr["request"]["tf"]) ? $arr["request"]["tf"] : $fld->id(),
				"class_id" => array(CL_FILE,CL_CRM_DOCUMENT, CL_CRM_DEAL, CL_CRM_MEMO, CL_CRM_OFFER,CL_MENU),
			));
		}

		classload("core/icons");
			$ol->sort_by(array(
                                "prop" => array("jrk","name"),
                                "order" => array("asc","asc")
                        ));

		$clss = aw_ini_get("classes");
		get_instance(CL_FILE);
		foreach($ol->arr() as $o)
		{
			$pm = get_instance("vcl/popup_menu");
			$pm->begin_menu("sf".$o->id());

			if ($o->class_id() == CL_FILE)
			{
				$pm->add_item(array(
					"text" => $o->name(),
					"link" => file::get_url($o->id(), $o->name()),
					"target" => 1
				));
			}
			else
			{
				foreach($o->connections_from(array("type" => "RELTYPE_FILE")) as $c)
				{
					$pm->add_item(array(
						"text" => $c->prop("to.name"),
						"link" => file::get_url($c->prop("to"), $c->prop("to.name")),
						"target" => 1
					));
				}
			}
			$t->define_data(array(
				"icon" => $o->class_id() == CL_MENU ? html::href(array("caption" => "<img border=0 src='".icons::get_icon_url($o->class_id())."'>" , "url" => aw_url_change_var("tf" , $o->id()))) : $pm->get_menu(array(
					"icon" => icons::get_icon_url($o)
				)),
				"name" => html::obj_change_url($o),
				"class_id" => $clss[$o->class_id()]["name"],
				"createdby" => $o->createdby(),
				"created" => $o->created(),
				"modifiedby" => $o->modifiedby(),
				"modified" => $o->modified(),
				"oid" => $o->id(),
                                "oname" => $o->name(),
                                "jrk" => $o->ord(),
				"is_menu" => $o->class_id() == CL_MENU ? 0 : 1
			));
		}
		if(!$arr["request"]["tf"] || $arr["request"]["tf"] == $fld->id())
		{
			$person = $arr["request"]["id"];
			$c = new connection();
			$results = $c->find(array(
				"from.class_id" => CL_CRM_OFFER,
				"to" => $person,
				"type" => RELTYPE_ORDERER
			));
			foreach($results as $result)
			{
				$o2 = obj($result['from']);
				$pm = get_instance("vcl/popup_menu");
				$pm->begin_menu("sf".$o2->id());
				foreach($o2->connections_from(array("class" => CL_FILE)) as $c)
				{
					$pm->add_item(array(
						"text" => $c->prop("to.name"),
						"link" => file::get_url($c->prop("to"), $c->prop("to.name")),
						"target" => 1
					));
				}
				$t->define_data(array(
					"icon" => $o2->class_id() == CL_MENU ? 1 : $pm->get_menu(array(
						"icon" => icons::get_icon_url($o2)
					)),
					"name" => html::obj_change_url($o2),
					"class_id" => $clss[$o2->class_id()]["name"],
					"createdby" => $o2->createdby(),
					"created" => $o2->created(),
					"modifiedby" => $o2->modifiedby(),
					"modified" => $o2->modified(),
					"oid" => $o2->id(),
					"oname" => $o2->name(),
					"jrk" => $o2->ord(),
					"is_menu" => $o2->class_id() == CL_MENU ? 0 : 1
					));
			}
		}
		/*$t->data_from_ol($ol, array(
			"change_col" => "name"
		));*/
//arr($t);
		$t->set_numeric_field("jrk");
		$t->set_default_sortby(array("is_menu","jrk","oname"));
		$t->set_default_sorder("asc");
	}

	function _get_docs_s_type($arr)
	{
		if (!$arr["request"]["do_doc_search"])
		{
			return PROP_IGNORE;
		}
		$arr["prop"]["options"] = array("" => "") + $this->adds;
		$arr["prop"]["value"] = $arr["request"]["docs_s_type"];
	}

	function _get_docs_s_created_after($arr)
	{
		$arr["prop"]["value"] = $arr["request"]["docs_s_created_after"];
	}

	function _get_doc_search_f($req, $parent)
	{
		$res = array(
			"parent" => $parent,
			"class_id" => array_keys($this->adds)
		);

		$has = false;
		if ($req["docs_s_name"] != "")
		{
			$res["name"] = "%".$req["docs_s_name"]."%";
			$has = true;
		}

		if ($req["docs_s_type"] != "")
		{
			$res["class_id"] = $req["docs_s_type"];
			$has = true;
		}

		if ($req["docs_s_created_after"] != "")
		{
			$res["created"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, (int) $req["docs_s_created_after"]);
			$has = true;
		}

		if ($req["docs_s_comment"] != "")
		{
			$res["comment"] = "%".$req["docs_s_comment"]."%";
			$has = true;
		}


		if ($req["docs_s_task"] != "")
		{
			$res[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_MEMO.task.name" => "%".$req["docs_s_task"]."%",
					"CL_CRM_DOCUMENT.task.name" => "%".$req["docs_s_task"]."%",
					"CL_CRM_DEAL.task.name" => "%".$req["docs_s_task"]."%",
					"CL_CRM_MEMO.task.content" => "%".$req["docs_s_task"]."%",
					"CL_CRM_DOCUMENT.task.content" => "%".$req["docs_s_task"]."%",
					"CL_CRM_DEAL.task.content" => "%".$req["docs_s_task"]."%",
				)
			));
			$has = true;
		}

		if ($req["docs_s_user"] != "")
		{
			// get all persons whose names match
			$pers = new object_list(array(
				"class_id" => CL_CRM_PERSON,
				"lang_id" => array(),
				"site_id" => array(),
				"name" => "%".$req["docs_s_user"]."%"
			));
			// get all users for those
			$c = new connection();
			$user_conns = $c->find(array(
				"from.class_id" => CL_USER,
				"type" => "RELTYPE_PERSON",
				"to" => $pers->ids()
			));
			$uids = array();
			foreach($user_conns as $c)
			{
				$u = obj($c["from"]);
				$uids[] = $u->prop("uid");
			}
			// filter by createdby or modifiedby by those users
			$res[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"createdby" => $uids,
					"modifiedby" => $uids
				)
			));
			$has = true;
		}

		if ($req["docs_s_customer"] != "")
		{
			$res[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_MEMO.customer.name" => "%".$req["docs_s_customer"]."%",
					"CL_CRM_DOCUMENT.customer.name" => "%".$req["docs_s_customer"]."%",
					"CL_CRM_DEAL.customer.name" => "%".$req["docs_s_customer"]."%"
				)
			));
			$has = true;
		}

		if (!$has)
		{
			$res["oid"] = -1;
		}
		return $res;
	}

	function _get_docs_news_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$fldo = $this->_init_content_docs_fld($arr["obj_inst"]);

		$tb->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Lisa dokument'),
			'url' => html::get_new_url(CL_DOCUMENT, $fldo->id(), array("return_url" => get_ru())),
		));

	}

	function _init_dn_res_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "lead",
			"caption" => t("Lead"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"sortable" => 1,
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"sortable" => 1,
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"sortable" => 1,
			"align" => "center",
		));
	}

	function _get_dn_res($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_dn_res_t($t);

		$format = t("%s siseuudised");
		$t->set_caption(sprintf($format, $arr['obj_inst']->name()));

		$ol = $this->_get_news($this->_init_content_docs_fld($arr["obj_inst"]), $arr["request"]);
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"name" => html::href(array(
					"url" => obj_link($o->id()),//$this->mk_my_orb("view", array("id" => $o->id(), "return_url" => get_ru()), CL_DOCUMENT),
					"caption" => parse_obj_name($o->name())
				)),
				"lead" => nl2br($o->prop("lead")),
				"createdby" => $o->createdby(),
				"created" => $o->created(),
				"modifiedby" => $o->modifiedby(),
				"modified" => $o->modified(),
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id(), "return_url" => get_ru()), CL_DOCUMENT),
					"caption" => t("Muuda")
				))
			));
		}
	}

	function _get_news($parent, $r)
	{
		if ($r["dn_s_sbt"] == "")
		{
			$ol = new object_list(array(
				"class_id" => CL_DOCUMENT,
				"created" => new obj_predicate_compare(OBJ_COMP_GREATER, time()- (7*24*3600)),
				"parent" => $parent->id()
			));
		}
		else
		{
			$filt = array(
				"class_id" => CL_DOCUMENT,
				"parent" => $parent->id()
			);

			if ($r["dn_s_name"] != "")
			{
				$filt["name"] = "%".$r["dn_s_name"]."%";
			}

			if ($r["dn_s_lead"] != "")
			{
				$filt["lead"] = "%".$r["dn_s_lead"]."%";
			}

			if ($r["dn_s_content"] != "")
			{
				$filt["content"] = "%".$r["dn_s_content"]."%";
			}

			$ol = new object_list($filt);
		}
		return $ol;
	}

	function _init_docs_lmod_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "files",
			"caption" => t("Failid"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "parent",
			"caption" => t("Asukoht"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muutmisaeg"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
	}

	function _get_documents_lmod($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_docs_lmod_t($t);

		$fld = $this->_init_docs_fld($arr["obj_inst"]);
		$ot = new object_tree(array(
			"class_id" => CL_MENU,
			"parent" => $fld->id()
		));
		$ol = $ot->to_list();
		$ol->add($fld);

		// search for 30 last mod docs
		$lm = new object_list(array(
			"parent" => $ol->ids(),
			"sort_by" => "objects.modified desc",
			"limit" => 30,
			"class_id" => array(CL_FILE,CL_CRM_MEMO,CL_CRM_DEAL,CL_CRM_DOCUMENT,CL_CRM_OFFER, CL_FILE)
		));
		//$t->data_from_ol($lm);
		$u = get_instance(CL_USER);
		$us = get_instance("users");
		foreach($lm->arr() as $o)
		{
			$p = obj($u->get_person_for_user(obj($us->get_oid_for_uid($o->modifiedby()))));
			$fs = new object_list($o->connections_from(array("type" => "RELTYPE_FILE")));
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"files" => html::obj_change_url($fs->ids()),
				"parent" => $o->path_str(array("path_only" => true, "max_len" => 2)),
				"modifiedby" => $p->name(),
				"modified" => $o->modified()
			));
		}
		$t->set_default_sortby("modified");
		$t->set_default_sorder("desc");
	}
}
?>
