<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/development_order.aw,v 1.5 2006/12/08 07:16:03 kristo Exp $
// development_order.aw - Arendustellimus 
/*

@classinfo syslog_type=ST_DEVELOPMENT_ORDER relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo aw_dev_orders master_table=objects master_index=brother_of index=aw_oid
@default table=aw_dev_orders
@default group=general

	@property customer type=relpicker reltype=RELTYPE_CUSTOMER field=aw_customer
	@caption Klient

	@property project type=relpicker reltype=RELTYPE_PROJECT field=aw_project
	@caption Projekt

	@property orderer_co type=relpicker reltype=ORDERER_CO field=aw_orderer_co
	@caption Tellija organisatsioon

	@property orderer_unit type=relpicker reltype=UNIT field=aw_orderer_unit
	@caption Tellija &uuml;ksus

	@property content type=textarea rows=20 cols=50 field=aw_content
	@caption Sisu

	@property fileupload type=releditor reltype=RELTYPE_FILE1 rel_id=first use_form=emb field=aw_f1
	@caption Fail1

	@property fileupload2 type=releditor reltype=RELTYPE_FILE2 rel_id=first use_form=emb field=aw_f2
	@caption Fail2

	@property fileupload3 type=releditor reltype=RELTYPE_FILE3 rel_id=first use_form=emb field=aw_f3
	@caption Fail3

@default group=reqs

	@property reqs_tb type=toolbar no_caption=1 store=no
	@property reqs_table type=table store=no no_caption=1

@default group=reqs_cart

	@property reqs_cart_tb type=toolbar no_caption=1 store=no
	@property reqs_cart_table type=table store=no no_caption=1

@default group=problems

	@property problems_tb type=toolbar no_caption=1 store=no
	@property problems_table type=table store=no no_caption=1

@groupinfo reqs caption="K&otilde;ik n&otilde;uded" submit=no
@groupinfo reqs_cart caption="Tellimuste korv" submit=no
@groupinfo problems caption="Probleemid"


@reltype CUSTOMER value=1 clid=CL_CRM_COMPANY
@caption Klient

@reltype PROJECT value=2 clid=CL_PROJECT
@caption Projekt

@reltype FILE1 value=3 clid=CL_FILE
@caption Fail1

@reltype FILE2 value=4 clid=CL_FILE
@caption Fail2

@reltype FILE3 value=5 clid=CL_FILE
@caption Fail3

@reltype ORDERER_CO value=11 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype UNIT value=12 clid=CL_CRM_SECTION
@caption &Uuml;ksus

@reltype REQ value=13 clid=CL_PROCUREMENT_REQUIREMENT
@caption N&otilde;ue

@reltype PROBLEM value=14 clid=CL_CUSTOMER_PROBLEM_TICKET
@caption Probleem
*/

class development_order extends class_base
{
	function development_order()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/development_order",
			"clid" => CL_DEVELOPMENT_ORDER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "orderer_co":
				if ($arr["new"])
				{
					$co = get_current_company();
					$prop["options"] = array("" => t("--vali--"), $co->id() => $co->name());
					$prop["value"] = $co->id();
				}
				else
				{
					$co = get_current_company();
					$prop["options"][$co->id()] = $co->name();
				}
				break;

			case "orderer_unit":
				$co = get_current_company();
				$co_i = $co->instance();
				$sects = $co_i->get_all_org_sections($co);
				$prop["options"] = array("" => t("--vali--"));
				if (count($sects))
				{
					$ol = new object_list(array("oid" => $sects));
					$prop["options"] += $ol->names();
				}
				$p = get_current_person();
				if ($arr["new"])
				{
					$prop["value"] = $p->prop("org_section");
				}
				break;
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
		$arr["post_ru"] = post_ru();
		$arr["set_req"] = "0";
		$arr["set_problems"] = "0";
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_dev_orders(aw_oid int primary key, aw_customer int, aw_project int, aw_content mediumtext, aw_f1 int, aw_f2 int, aw_f3 int)");
			return true;
		}

		switch($f)
		{
			case "aw_f1":
			case "aw_f2":
			case "aw_f3":
			case "aw_orderer_co":
			case "aw_orderer_unit":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function _get_reqs_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$ps = get_instance("vcl/popup_search");
		$tb->add_cdata($ps->get_popup_search_link(array(
			"pn" => "set_req",
			"clid" => CL_PROCUREMENT_REQUIREMENT
		)));
		$tb->add_delete_rels_button();
		$tb->add_separator();
		$tb->add_button(array(
			"name" => "export",
			"tooltip" => t("Ekspordi"),
			"img" => "export.gif",
			"action" => "export_req",
		));
		$tb->add_separator();
		$tb->add_button(array(
			"name" => "add_to_cart",
			"tooltip" => t("Lisa korvi"),
//			"img" => "export.gif",
			"action" => "add_to_cart",
		));
	}

	function _get_reqs_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_REQ")));
		$t->table_from_ol($ol, array("name", "created", "pri", "req_co", "req_p", "project", "process", "planned_time"), CL_PROCUREMENT_REQUIREMENT);
	}

	function _set_reqs_table($arr)
	{
		$ps = get_instance("vcl/popup_search");
		$ps->do_create_rels($arr["obj_inst"], $arr["request"]["set_req"], "RELTYPE_REQ");
	}

	function _get_problems_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$ps = get_instance("vcl/popup_search");
		$tb->add_cdata($ps->get_popup_search_link(array(
			"pn" => "set_problems",
			"clid" => CL_CUSTOMER_PROBLEM_TICKET
		)));
		$tb->add_delete_rels_button();
	}

	function _get_problems_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PROBLEM")));
		$t->table_from_ol($ol, array("name", "createdby", "created", "orderer_co", "orderer_unit", "customer", "project", "requirement", "from_dev_order", "from_bug"), CL_CUSTOMER_PROBLEM_TICKET);
	}

	function _set_problems_table($arr)
	{
		$ps = get_instance("vcl/popup_search");
		$ps->do_create_rels($arr["obj_inst"], $arr["request"]["set_problems"], "RELTYPE_PROBLEM");
	}

	/**
		@attrib name=export_req
	**/
	function export_req($arr)
	{
		$o = obj($arr["id"]);
		$ol = new object_list($o->connections_from(array("type" => "RELTYPE_REQ")));
		classload("vcl/table");
		$t = new vcl_table();
		$t->table_from_ol($ol, array("name", "created", "pri", "req_co", "req_p", "project", "process", "planned_time", "desc", "state", "budget"), CL_PROCUREMENT_REQUIREMENT);
		header('Content-type: application/octet-stream');
		header('Content-disposition: root_access; filename="req.csv"');
		print $t->get_csv_file();
		die();
	}

	function _get_reqs_cart_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "remove_from_cart",
			"tooltip" => t("Eemalda korvist"),
			"img" => "delete.gif",
			"action" => "remove_from_cart",
		));
		$tb->add_save_button();
	}

	function _set_reqs_cart_table($arr)
	{
		$arr["obj_inst"]->set_meta("cart", $arr["request"]["d"]);
	}

	function _get_reqs_cart_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$all_ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_REQ")));
		$ol = new object_list();
		$cart = $arr["obj_inst"]->meta("cart");
		foreach($all_ol->arr() as $o)
		{
			if (isset($cart[$o->id()]))
			{
				$ol->add($o);
			}
		}
		$t->table_from_ol($ol, array("name", "created", "pri", "req_co", "req_p", "project", "process", "planned_time"), CL_PROCUREMENT_REQUIREMENT);
		$t->define_field(array(
			"name" => "hrs",
			"caption" => t("T&ouml;&ouml;tunde"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => t("L&otilde;ppt&auml;htaeg"),
			"align" => "center"
		));
		foreach($t->get_data() as $idx => $row)
		{
			$row["hrs"] = html::textbox(array(
				"name" => "d[".$row["oid"]."][hrs]",
				"size" => 5,
				"value" => $cart[$row["oid"]]["hrs"]
			));
			$row["price"] = html::textbox(array(
				"name" => "d[".$row["oid"]."][price]",
				"size" => 5,
				"value" => $cart[$row["oid"]]["price"]
			));
			$row["date"] = html::date_select(array(
				"format" => array("day_textbox", "month_textbox","year_textbox"),
				"value" => $cart[$row["oid"]]["date"] > 10 ? $cart[$row["oid"]]["date"] : -1,
				"name" => "d[".$row["oid"]."][date]"
			));
			$t->set_data($idx, $row);
		}
	}

	/**
		@attrib name=add_to_cart
	**/
	function add_to_cart($arr)
	{
		$o = obj($arr["id"]);
		$cart = $o->meta("cart");
		foreach(safe_array($arr["sel"]) as $id)
		{
			if (!isset($cart[$id]))
			{
				$cart[$id] = array("price" => 0);
			}
		}
		$o->set_meta("cart", $cart);
		$o->save();
		return $arr["post_ru"];
	}

	/**
		@attrib name=remove_from_cart
	**/
	function remove_from_cart($arr)
	{
		$o = obj($arr["id"]);
		$cart = $o->meta("cart");
		foreach(safe_array($arr["sel"]) as $id)
		{
			unset($cart[$id]);
		}
		$o->set_meta("cart", $cart);
		$o->save();
		return $arr["post_ru"];
	}
}
?>
