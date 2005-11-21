<?php

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

		$tb->add_button(array(
			'name' => 'Search',
			'img' => 'search.gif',
			'tooltip' => t('Otsi'),
			'url' => aw_url_change_var("do_doc_search", 1)
		));
	}

	function _get_docs_tree($arr)
	{
		if ($arr["request"]["do_doc_search"])
		{
			return PROP_IGNORE;
		}

		$fld = $this->_init_docs_fld($arr["obj_inst"]);

		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "crm_docs_t",
			),
			"root_item" => $fld,
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $fld->id(),
			)),
			"var" => "tf",
			"icon" => icons::get_icon_url(CL_MENU)
		));

		// if there is a server folder object attached, then get the rest of the folders from that
		$sf = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_SERVER_FILES");
		if ($sf)
		{
			$s = $sf->instance();
			$fld = $s->get_folders($sf);

			$t =& $arr["prop"]["vcl_inst"];
			$t->add_item(0, array(
				"id" => $sf->id(),
				"name" => $sf->name(),
				"url" => aw_url_change_var("files_from_fld", "/")
			));
			foreach($fld as $item)
			{
				$item["url"] = aw_url_change_var("files_from_fld", $item["id"]);
				$t->add_item($item["parent"] === 0 ? $sf->id() : $item["parent"], $item);
			}
		}
	}

	function _init_docs_tbl(&$t)
	{
		$t->define_field(array(
			"caption" => t("Nimi"),
			"name" => "name",
			"align" => "center",
			"sortable" => 1
		));

		if ($_GET["files_from_fld"] == "")
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

		if ($_GET["files_from_fld"] == "")
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

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_docs_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_docs_tbl($t);
		if ($_GET["files_from_fld"] != "")
		{
			$sf = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_SERVER_FILES");
			$i = $sf->instance();
			$ob = $i->get_objects($sf, NULL, $_GET["files_from_fld"]);
			foreach($ob as $nm => $dat)
			{
				$t->define_data(array(
					"name" => html::href(array("url" => $dat["url"], "caption" => $dat["name"])),
					"created" => $dat["add_date"],
					"modified" => $dat["mod_date"],
					"createdby" => $dat["adder"]
				));
			}
			return;
		}

		$fld = $this->_init_docs_fld($arr["obj_inst"]);


		if ($arr["request"]["do_doc_search"])
		{
			// get all parents to search from 
			$parent_tree = new object_tree(array(
				"parent" => $fld->id(),
				"class_id" => CL_MENU
			));
			$parent_ol = $parent_tree->to_list();
			$parents = $parent_ol->ids();
			$parents[] = $fld->id();
			$ol = new object_list($this->_get_doc_search_f($arr["request"], $parents));
		}
		else
		{
			$ol = new object_list(array(
				"parent" => is_oid($arr["request"]["tf"]) ? $arr["request"]["tf"] : $fld->id()
			));
		}

		$t->data_from_ol($ol, array(
			"change_col" => "name"
		));
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
}
?>