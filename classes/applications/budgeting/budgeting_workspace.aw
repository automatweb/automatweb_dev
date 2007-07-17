<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/budgeting/budgeting_workspace.aw,v 1.4 2007/07/17 08:45:46 kristo Exp $
// budgeting_workspace.aw - Eelarvestamise t&ouml;&ouml;laud 
/*

@classinfo syslog_type=ST_BUDGETING_WORKSPACE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@default group=accounts

	@property acct_tb type=toolbar no_caption=1 store=no

	@layout acct_split type=hbox width="20%:80%" 

		@layout acct_tree parent=acct_split type=vbox closeable=1 area_caption=Kontode&nbsp;puu

			@property acct_tree type=treeview parent=acct_tree store=no no_caption=1

		@layout acct_table parent=acct_split type=vbox

			@property acct_table type=table parent=acct_table store=no no_caption=1

@default group=taxes

	@property tax_tb type=toolbar no_caption=1 store=no

	@layout tax_split type=hbox width="20%:80%" 

		@layout tax_tree parent=tax_split type=vbox closeable=1 area_caption=Maksude&nbsp;puu

			@property tax_tree type=treeview parent=tax_tree store=no no_caption=1

		@layout tax_table parent=tax_split type=vbox

			@property tax_table type=table parent=tax_table store=no no_caption=1

@default group=taxes_grps

	@property tax_grp_tb type=toolbar no_caption=1 store=no

	@property tax_grp_table type=table store=no no_caption=1

@default group=funds

	@property fund_tb type=toolbar no_caption=1 store=no

	@layout fund_split type=hbox width="20%:80%" 

		@layout fund_tree parent=fund_split type=vbox closeable=1 area_caption=Fondide&nbsp;puu

			@property fund_tree type=treeview parent=fund_tree store=no no_caption=1

		@layout fund_table parent=fund_split type=vbox

			@property fund_table type=table parent=fund_table store=no no_caption=1

@default group=transfers

	@property transfer_tb type=toolbar no_caption=1 store=no

	@layout transfer_split type=hbox width="20%:80%" 

		@layout transfer_search parent=transfer_split type=vbox closeable=1 area_caption=Otsi&nbsp;&uuml;lekandeid

			@property tax_s_from_acct type=textbox parent=transfer_search store=no captionside=top size=20
			@caption Kontolt

			@property tax_s_to_acct type=textbox parent=transfer_search store=no captionside=top size=20
			@caption Kontole

			@property tax_s_date_from type=date_select format=day_textbox,month_textbox,year_textbox parent=transfer_search store=no captionside=top default=-1
			@caption Alates

			@property tax_s_date_to type=date_select format=day_textbox,month_textbox,year_textbox parent=transfer_search store=no captionside=top default=-1
			@caption Kuni

			@property tax_s_sbt type=submit parent=transfer_search store=no no_caption=1
			@caption Otsi

		@layout transfer_table parent=transfer_split type=vbox

			@property transfer_table type=table parent=transfer_table store=no no_caption=1

@default group=budgets

	@property budgets_tb type=toolbar store=no no_caption=1
	@property budgets_tbl type=table store=no no_caption=1

@groupinfo accounts caption="Kontod" submit=no save=no
@groupinfo taxes_main caption="Maksud" submit=no save=no
	@groupinfo taxes caption="Maksud" submit=no save=no parent=taxes_main
	@groupinfo taxes_grps caption="Maksugrupid " submit=no save=no parent=taxes_main

@groupinfo funds caption="Fondid" submit=no save=no
@groupinfo transfers caption="&Uuml;lekanded" submit=no save=no submit_method=get
@groupinfo budgets caption="Eelarved" submit=no save=no 

@reltype ACCT_TREE_PARENT value=1 clid=CL_MENU
@caption Kontode juurkaust

@reltype TAX_TREE_PARENT value=2 clid=CL_MENU
@caption Maksude juurkaust

@reltype FUND_TREE_PARENT value=3 clid=CL_MENU
@caption Fondide juurkaust

@reltype BUDGET value=4 clid=CL_BUDGET
@caption Eelarve

@reltype TAX_GROUP value=5 clid=CL_BUDGETING_TAX_GROUP
@caption Maksugrupp

*/

class budgeting_workspace extends class_base
{
	function budgeting_workspace()
	{
		classload("core/icons");
		$this->init(array(
			"tpldir" => "applications/budgeting/budgeting_workspace",
			"clid" => CL_BUDGETING_WORKSPACE
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

	function callback_mod_reforb($arr)
	{
		if ($arr["group"] != "transfers")
		{
			$arr["post_ru"] = post_ru();
		}
	}

	function _get_acct_tree($arr)
	{
		$pt = $this->get_acct_tree_parent($arr["obj_inst"]);
		classload("vcl/treeview");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "budgeting_accts",
			),
			"root_item" => obj($pt),
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $pt,
				"lang_id" => array(),
				"site_id" => array()
			)),
			"var" => "acct_fld",
			"icon" => icons::get_icon_url(CL_MENU)
		));
	}

	function _get_acct_table($arr)
	{
		$pt = $arr["request"]["acct_fld"] ? $arr["request"]["acct_fld"] : $this->get_acct_tree_parent($arr["obj_inst"]);
		$arr["prop"]["vcl_inst"]->table_from_ol(
			new object_list(array(
				"class_id" => CL_BUDGETING_ACCOUNT,
				"parent" => $pt,
				"lang_id" => array(),
				"site_id" => array()
			)),
			array("name", "comment"),
			CL_BUDGETING_ACCOUNT
		);
	}

	function get_acct_tree_parent($o)
	{
		$fld = $o->get_first_obj_by_reltype("RELTYPE_ACCT_TREE_PARENT");
		if (!$fld)
		{
			$fld = obj();
			$fld->set_class_id(CL_MENU);
			$fld->set_parent($o->id());
			$fld->set_name(sprintf(t("%s kontode kataloog"), $o->name()));
			$fld->save();
			$o->connect(array(
				"to" => $fld->id(),
				"type" => "RELTYPE_ACCT_TREE_PARENT"
			));
		}
		return $fld->id();
	}

	function _get_acct_tb($arr)
	{
		$pt = $arr["request"]["acct_fld"] ? $arr["request"]["acct_fld"] : $this->get_acct_tree_parent($arr["obj_inst"]);
		$arr["prop"]["vcl_inst"]->add_new_button(array(CL_MENU,CL_BUDGETING_ACCOUNT), $pt);
		$arr["prop"]["vcl_inst"]->add_delete_button();
	}

	function _get_tax_tree($arr)
	{
		$co = get_current_company();
		$tv = &$arr["prop"]["vcl_inst"];
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"has_root" => 1,
			"tree_id" => "budgeting_taxes",
			"persist_state" => 1,
			"root_name" => $co->name(),
			"root_url" => aw_url_change_var("tax_fld", null),
			"get_branch_func" => $this->mk_my_orb("get_tax_tree_root_items", array(
				"r" => $arr["request"],
				"parent" => " "
			)),
		));

		$s = t("&Uuml;ldine");
		$tv->add_item(0, array(
			"id" => "main",
			"name" => $arr["request"]["tax_fld"] == "main" ? "<b>".$s."</b>" : $s,
			"url" => aw_url_change_var("tax_fld", "main")
		));
		$s = t("Valdkonnad");
		$tv->add_item("main", array(
			"id" => "area",
			"name" => $arr["request"]["tax_fld"] == "area" ? "<b>".$s."</b>" : $s,
			"url" => aw_url_change_var("tax_fld", "area")
		));
	}

	function _get_tax_table($arr)
	{
		$pt = $arr["request"]["tax_fld"] ? $arr["request"]["tax_fld"] : $this->get_tax_tree_parent($arr["obj_inst"]);
		$arr["prop"]["vcl_inst"]->table_from_ol(
			new object_list(array(
				"class_id" => CL_BUDGETING_TAX,
				"from_place" => $pt,
				"lang_id" => array(),
				"site_id" => array()
			)),
			array("name", "comment", "pri"),
			CL_BUDGETING_TAX
		);
	}

	function get_tax_tree_parent($o)
	{
		$fld = $o->get_first_obj_by_reltype("RELTYPE_TAX_TREE_PARENT");
		if (!$fld)
		{
			$fld = obj();
			$fld->set_class_id(CL_MENU);
			$fld->set_parent($o->id());
			$fld->set_name(sprintf(t("%s maksude kataloog"), $o->name()));
			$fld->save();
			$o->connect(array(
				"to" => $fld->id(),
				"type" => "RELTYPE_TAX_TREE_PARENT"
			));
		}
		return $fld->id();
	}

	function _get_tax_tb($arr)
	{
		$arr["prop"]["vcl_inst"]->add_new_button(array(CL_BUDGETING_TAX), $arr["obj_inst"]->id(), null, array("place" => $arr["request"]["tax_fld"]));
		$arr["prop"]["vcl_inst"]->add_delete_button();
	}

	function _get_transfer_tb($arr)
	{
		$arr["prop"]["vcl_inst"]->add_new_button(array(CL_BUDGETING_TRANSFER), $arr["obj_inst"]->id());
		$arr["prop"]["vcl_inst"]->add_delete_button();
	}

	function _get_transfer_table($arr)
	{
		if ($arr["request"]["MAX_FILE_SIZE"] && false)
		{
		}
		else
		{
			// last 30 transfers
			$data = new object_list(array(
				"class_id" => CL_BUDGETING_TRANSFER,
				"lang_id" => array(),
				"site_id" => array(),
				"limit" => 30,
				"sort_by" => "objects.created desc"
			));
		}
		$arr["prop"]["vcl_inst"]->table_from_ol(
			$data,
			array("name", "comment"),
			CL_BUDGETING_TRANSFER
		);
	}

	function _get_tax_s_from_acct($arr)
	{
		$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
	}

	function _get_fund_tree($arr)
	{
		$pt = $this->get_fund_tree_parent($arr["obj_inst"]);
		classload("vcl/treeview");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "budgeting_funds",
			),
			"root_item" => obj($pt),
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $pt,
				"lang_id" => array(),
				"site_id" => array()
			)),
			"var" => "fund_fld",
			"icon" => icons::get_icon_url(CL_MENU)
		));
	}

	function _get_fund_table($arr)
	{
		$pt = $arr["request"]["fund_fld"] ? $arr["request"]["fund_fld"] : $this->get_fund_tree_parent($arr["obj_inst"]);
		$arr["prop"]["vcl_inst"]->table_from_ol(
			new object_list(array(
				"class_id" => CL_BUDGETING_FUND,
				"parent" => $pt,
				"lang_id" => array(),
				"site_id" => array()
			)),
			array("name", "comment"),
			CL_BUDGETING_FUND
		);
	}

	function get_fund_tree_parent($o)
	{
		$fld = $o->get_first_obj_by_reltype("RELTYPE_FUND_TREE_PARENT");
		if (!$fld)
		{
			$fld = obj();
			$fld->set_class_id(CL_MENU);
			$fld->set_parent($o->id());
			$fld->set_name(sprintf(t("%s fondide kataloog"), $o->name()));
			$fld->save();
			$o->connect(array(
				"to" => $fld->id(),
				"type" => "RELTYPE_FUND_TREE_PARENT"
			));
		}
		return $fld->id();
	}

	function _get_fund_tb($arr)
	{
		$pt = $arr["request"]["fund_fld"] ? $arr["request"]["fund_fld"] : $this->get_fund_tree_parent($arr["obj_inst"]);
		$arr["prop"]["vcl_inst"]->add_new_button(array(CL_MENU,CL_BUDGETING_FUND), $pt);
		$arr["prop"]["vcl_inst"]->add_delete_button();
	}

	/**
		@attrib name=get_tax_tree_root_items
		@param r required
		@param parent required
	**/
	function get_tax_tree_root_items($arr)
	{
		$tax_fld = $arr["r"]["tax_fld"];
		$pt = trim($arr["parent"]);
		classload("core/icons");
		$tv = get_instance("vcl/treeview");
		$tv->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "budgeting_taxes",
			"branch" => 1,
		));
		if ($pt == "main")
		{
			$arr["r"]["tax_fld"] = "area";
			$s = t("Valdkonnad");
			$tv->add_item(0, array(
				"id" => "area",
				"name" => $tax_fld == "area" ? "<b>".$s."</b>" : $s,
				"url" => $this->mk_my_orb("change", $arr["r"])
			));
			$tv->add_item("area", array("id" => "tmp"));

			$s = t("Fondid");
			$arr["r"]["tax_fld"] = "funds";
			$tv->add_item(0, array(
				"id" => "funds",
				"name" => $tax_fld == "funds" ? "<b>".$s."</b>" : $s,
				"url" => $this->mk_my_orb("change", $arr["r"])
			));
			$tv->add_item("funds", array("id" => "tmp"));

			$s = t("Tegija");
			$arr["r"]["tax_fld"] = "worker";
			$tv->add_item(0, array(
				"id" => "worker",
				"name" => $tax_fld == "worker" ? "<b>".$s."</b>" : $s,
				"url" => $this->mk_my_orb("change", $arr["r"])
			));
			$tv->add_item("worker", array("id" => "tmp"));

			$s = t("Teenuse liik");
			$arr["r"]["tax_fld"] = "service_type";
			$tv->add_item(0, array(
				"id" => "service_type",
				"name" => $tax_fld == "service_type" ? "<b>".$s."</b>" : $s,
				"url" => $this->mk_my_orb("change", $arr["r"])
			));
			$tv->add_item("service_type", array("id" => "tmp"));

			$s = t("Tooteperekonnad");
			$arr["r"]["tax_fld"] = "prod_families";
			$tv->add_item(0, array(
				"id" => "prod_families",
				"name" => $tax_fld == "prod_families" ? "<b>".$s."</b>" : $s,
				"url" => $this->mk_my_orb("change", $arr["r"])
			));
			$tv->add_item("prod_families", array("id" => "tmp"));

			$s = t("Kontod");
			$arr["r"]["tax_fld"] = "accts";
			$tv->add_item(0, array(
				"id" => "accts",
				"name" => $tax_fld == "accts" ? "<b>".$s."</b>" : $s,
				"url" => $this->mk_my_orb("change", $arr["r"])
			));
			$tv->add_item("accts_families", array("id" => "tmp"));

		}
		else
		if (substr($pt, 0, 4) == "area")
		{
			list(, $area_id) = explode("_", $pt);
			if (!$area_id)
			{
				$sect = get_current_company();
			}
			else
			{
				$sect = obj($area_id);
			}
			$curf = $arr["r"]["tax_fld"];
			foreach($sect->connections_from(array("type" => "RELTYPE_CATEGORY", "sort_by" => "to.name")) as $c)
			{
				$sc = $c->to();
				$s = $sc->name();
				$ti = "area_".$sc->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $curf == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
				));
				$tv->add_item($ti, array("id" => "tmp"));
			}

			foreach($sect->connections_from(array("type" => "RELTYPE_CUSTOMER", "sort_by" => "to.name")) as $c)
			{
				$sc = $c->to();
				$s = $sc->name();
				$ti = "cust_".$sc->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $curf == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
				));
				$tv->add_item($ti, array("id" => "tmp"));
			}
		}
		else
		if (substr($pt, 0, 5) == "cust_")
		{
			list(, $cust_id) = explode("_", $pt);
			$s = t("Projektid");
			$ti = "projects_".$cust_id;
			$arr["r"]["tax_fld"] = $ti;
			$tv->add_item(0, array(
				"id" => $ti,
				"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
				"url" => $this->mk_my_orb("change", $arr["r"]),
			));
			$tv->add_item($ti, array("id" => "tmp"));

			$s = t("Teenuse liigid");
			$ti = "custstypes_".$cust_id;
			$arr["r"]["tax_fld"] = $ti;
			$tv->add_item(0, array(
				"id" => $ti,
				"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
				"url" => $this->mk_my_orb("change", $arr["r"]),
			));
			$tv->add_item($ti, array("id" => "kmp"));

			$s = t("Tooteperekonnad");
			$ti = "custprodcats_".$cust_id;
			$arr["r"]["tax_fld"] = $ti;
			$tv->add_item(0, array(
				"id" => $ti,
				"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
				"url" => $this->mk_my_orb("change", $arr["r"]),
			));
			$tv->add_item($ti, array("id" => "tmp3"));
		}
		else
		if (substr($pt, 0, 8) == "projects")
		{
			list(, $cust_id) = explode("_", $pt);
			$ol = new object_list(array(
				"class_id" => CL_PROJECT,
				"lang_id" => array(),
				"site_id" => array(),
				"CL_PROJECT.RELTYPE_ORDERER" => $cust_id,
				"sort_by" => "objects.name"
			));
			foreach($ol->arr() as $proj)
			{
				$s = $proj->name();
				$ti = "proj_".$proj->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
				));
				$tv->add_item($ti, array("id" => "tmp"));
			}
		}
		else
		if (substr($pt, 0, 4) == "proj")
		{
			list(, $proj_id) = explode("_", $pt);
			$ol = new object_list(array(
				"class_id" => array(CL_TASK),
				"lang_id" => array(),
				"site_id" => array(),
				"CL_TASK.RELTYPE_PROJECT" => $proj_id
			));
			foreach($ol->arr() as $task)
			{
				$s = $task->name();
				$ti = "task_".$task->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
					"icon" => icons::get_icon_url(CL_TASK)
				));
			}
		}
		else
		if ($pt == "service_type")
		{
			$co = get_current_company();
			$ol = new object_list(array(
				"class_id" => array(CL_CRM_SERVICE_TYPE_CATEGORY,CL_CRM_SERVICE_TYPE),
				"lang_id" => array(),
				"site_id" => array(),
				"parent" => $co->id()
			));
			foreach($ol->arr() as $cat)
			{
				$s = $cat->name();
				$ti = "stypecat_".$cat->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
					"icon" => icons::get_icon_url($cat->id())
				));
				if ($cat->class_id() == CL_CRM_SERVICE_TYPE_CATEGORY)
				{
					$tv->add_item($ti, array("id" => "tmp"));
				}
			}
		}
		else
		if (substr($pt, 0, 10)  == "custstypes")
		{
			list(, $cust_id) = explode("_", $pt);
			$co = get_current_company();
			$ol = new object_list(array(
				"class_id" => array(CL_CRM_SERVICE_TYPE_CATEGORY,CL_CRM_SERVICE_TYPE),
				"lang_id" => array(),
				"site_id" => array(),
				"parent" => $co->id()
			));
			foreach($ol->arr() as $cat)
			{
				$s = $cat->name();
				$ti = "custstypecat_".$cust_id."_".$cat->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
					"icon" => icons::get_icon_url($cat->id())
				));
				if ($cat->class_id() == CL_CRM_SERVICE_TYPE_CATEGORY)
				{
					$tv->add_item($ti, array("id" => "tmp"));
				}
			}
		}
		else
		if (substr($pt, 0, 8)  == "stypecat")
		{
			list(, $cat_id) = explode("_", $pt);
			$ol = new object_list(array(
				"class_id" => array(CL_CRM_SERVICE_TYPE_CATEGORY,CL_CRM_SERVICE_TYPE),
				"lang_id" => array(),
				"site_id" => array(),
				"parent" => $cat_id
			));
			foreach($ol->arr() as $cat)
			{
				$s = $cat->name();
				$ti = "stypecat_".$cat->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
					"icon" => icons::get_icon_url($cat->id())
				));
				if ($cat->class_id() == CL_CRM_SERVICE_TYPE_CATEGORY)
				{
					$tv->add_item($ti, array("id" => "tmp"));
				}
			}
		}
		else
		if (substr($pt, 0, 12)  == "custstypecat")
		{
			list(, $cust_id, $cat_id) = explode("_", $pt);
			$ol = new object_list(array(
				"class_id" => array(CL_CRM_SERVICE_TYPE_CATEGORY,CL_CRM_SERVICE_TYPE),
				"lang_id" => array(),
				"site_id" => array(),
				"parent" => $cat_id
			));
			foreach($ol->arr() as $cat)
			{
				$s = $cat->name();
				$ti = "custstypecat_".$cust_id."_".$cat->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
					"icon" => icons::get_icon_url($cat->id())
				));
				if ($cat->class_id() == CL_CRM_SERVICE_TYPE_CATEGORY)
				{
					$tv->add_item($ti, array("id" => "tmp"));
				}
			}
		}
		else
		if ($pt  == "prod_families")
		{
			$co = get_current_company();
			$wh = $co->get_first_obj_by_reltype("RELTYPE_WAREHOUSE");
			if (!$wh)
			{
				die($tv->finalize_tree());
			}
			$wh_i = $wh->instance();
			list($fld, $ot) = $wh_i->get_packet_folder_list(array(
				"id" => $wh->id()
			));
			foreach($ot->level($fld) as $cat)
			{
				$s = $cat->name();
				$ti = "prodfamily_".$cat->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
					"icon" => icons::get_icon_url($cat->id())
				));
				$tv->add_item($ti, array("id" => "tmp"));
			}
		}
		else
		if (substr($pt, 0, 12)  == "custprodcats")
		{
			list(, $cust_id) = explode("_", $pt);
			$co = get_current_company();
			$wh = $co->get_first_obj_by_reltype("RELTYPE_WAREHOUSE");
			if (!$wh)
			{
				die($tv->finalize_tree());
			}
			$wh_i = $wh->instance();
			list($fld, $ot) = $wh_i->get_packet_folder_list(array(
				"id" => $wh->id()
			));
			foreach($ot->level($fld) as $cat)
			{
				$s = $cat->name();
				$ti = "custprodfamily_".$cust_id."_".$cat->id();
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
					"icon" => icons::get_icon_url($cat->id())
				));
				$tv->add_item($ti, array("id" => "tmp"));
			}
		}
		else
		if (substr($pt, 0, 10)  == "prodfamily")
		{
			list(, $cat_id) = explode("_", $pt);
			$ol = new object_list(array(
				"class_id" => array(CL_MENU,CL_SHOP_PRODUCT),
				"lang_id" => array(),
				"site_id" => array(),
				"parent" => $cat_id
			));
			foreach($ol->arr() as $cat)
			{
				$s = $cat->name();
				if ($cat->class_id() == CL_MENU)
				{
					$ti = "prodfamily_".$cat->id();
				}
				else
				{
					$ti = "prod_".$cat->id();
				}
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
					"icon" => icons::get_icon_url($cat->id())
				));
				if ($cat->class_id() == CL_MENU)
				{
					$tv->add_item($ti, array("id" => "tmp"));
				}
			}
		}
		else
		if (substr($pt, 0, 14)  == "custprodfamily")
		{
			list(, $cust_id, $cat_id) = explode("_", $pt);
			$ol = new object_list(array(
				"class_id" => array(CL_MENU,CL_SHOP_PRODUCT),
				"lang_id" => array(),
				"site_id" => array(),
				"parent" => $cat_id
			));
			foreach($ol->arr() as $cat)
			{
				$s = $cat->name();
				if ($cat->class_id() == CL_MENU)
				{
					$ti = "custprodfamily_".$cust_id."_".$cat->id();
				}
				else
				{
					$ti = "custprod_".$cust_id."_".$cat->id();
				}
				$arr["r"]["tax_fld"] = $ti;
				$tv->add_item(0, array(
					"id" => $ti,
					"name" => $tax_fld == $ti ? "<b>".$s."</b>" : $s,
					"url" => $this->mk_my_orb("change", $arr["r"]),
					"icon" => icons::get_icon_url($cat->id())
				));
				if ($cat->class_id() == CL_MENU)
				{
					$tv->add_item($ti, array("id" => "tmp"));
				}
			}
		}

		die($tv->finalize_tree());
	}

	function _get_budgets_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_BUDGET), $arr["obj_inst"]->id(), 4 /* RELTYPE_BUDGET */);
		$tb->add_delete_button();
	}

	function _get_budgets_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->table_from_ol(
			new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_BUDGET"))),
			array("name"),
			CL_BUDGET
		);
	}

	function _get_tax_grp_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_BUDGETING_TAX_GROUP), $arr["obj_inst"]->id(), 5 /* RELTYPE_TAX_GROUP */);
		$tb->add_delete_button();
	}

	function _get_tax_grp_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->table_from_ol(
			new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_TAX_GROUP"))),
			array("name", "comment"),
			CL_BUDGETING_TAX_GROUP
		);
	}
}
?>
