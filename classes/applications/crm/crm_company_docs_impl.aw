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
	}

	function _init_docs_tbl(&$t)
	{
		$t->define_field(array(
			"caption" => t("Nimi"),
			"name" => "name",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("T&uuml;&uuml;p"),
			"name" => "class_id",
			"align" => "center",
			"sortable" => 1
		));

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

		$t->define_field(array(
			"caption" => t("Muutja"),
			"name" => "modifiedby",
			"align" => "center",
			"sortable" => 1
		));

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
		$fld = $this->_init_docs_fld($arr["obj_inst"]);

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_docs_tbl($t);

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
					"CL_CRM_DEAL.task.name" => "%".$req["docs_s_task"]."%"
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
}
?>