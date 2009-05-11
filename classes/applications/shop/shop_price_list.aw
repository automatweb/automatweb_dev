<?php
/*
@classinfo syslog_type=ST_SHOP_PRICE_LIST relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=robert

@tableinfo aw_shop_price_list master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_price_list
@default group=general

@property short_name type=textbox
@caption L&uuml;hend

@property valid_from type=date_select field=valid_from
@caption Kehtib alates

@property valid_to type=date_select field=valid_to
@caption Kehtib kuni

@property jrk type=textbox size=4 table=objects
@caption Jrk

@property groups type=relpicker multiple=1 reltype=RELTYPE_GROUP store=connect
@caption Kehtib gruppidele

@property crm_categories type=relpicker multiple=1 reltype=RELTYPE_ORG_CAT store=connect
@caption Kehtib kliendigruppidele

@property orgs type=relpicker multiple=1 reltype=RELTYPE_ORG store=connect
@caption Kehtib organisatsioonidele

@property persons type=relpicker multiple=1 reltype=RELTYPE_PERSON store=connect
@caption Kehtib isikutele

@property warehouses type=relpicker multiple=1 reltype=RELTYPE_WAREHOUSE store=connect
@caption Kehtib ladudele

@property discount type=textbox field=discount
@caption Allahindlus

@property base_price type=checkbox field=base_price
@caption Baashindade alusel

@groupinfo clients_matrix caption=Kliendigrupid
@default group=clients_matrix

@property clients_tb type=toolbar no_caption=1

@property clients_matrix type=table no_caption=1

@reltype GROUP value=1 clid=CL_GROUP
@caption Grupp

@reltype ORG value=2 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype PERSON value=3 clid=CL_CRM_PERSON
@caption Isik

@reltype CATEGORY value=4 clid=CL_SHOP_PRODUCT_CATEGORY
@caption Kaubagrupp

@reltype ORG_CAT value=5 clid=CL_CRM_CATEGORY
@caption Kliendigrupp

@reltype WAREHOUSE value=6 clid=CL_SHOP_WAREHOUSE
@caption Ladu

@reltype MATRIX_CATEGORY value=7 clid=CL_SHOP_PRODUCT_CATEGORY
@caption Kaubagrupp

@reltype MATRIX_ORG_CAT value=8 clid=CL_CRM_CATEGORY
@caption Kliendigrupp
*/

class shop_price_list extends class_base
{
	function shop_price_list()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_price_list",
			"clid" => CL_SHOP_PRICE_LIST
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

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

	function _get_clients_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];

		$tb->add_search_button(array(
			"tooltip" => t("Otsi kliendi kategooria"),
			"pn" => "add_crm_cat",
			"multiple" => 1,
			"clid" => CL_CRM_CATEGORY,
		));
		$tb->add_menu_button(array(
			"name" => "del_crm_cat",
			"img" => "delete.gif",
			"tooltip" => t("Eemalda kliendigrupp"),
		));
		foreach($arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_MATRIX_ORG_CAT",
		)) as $c)
		{
			$tb->add_menu_item(array(
				"parent" => "del_crm_cat",
				"text" => $c->to()->name(),
				"link" => aw_url_change_var("del_crm_cat", $c->prop("to")),
			));
		}
		
		$tb->add_search_button(array(
			"tooltip" => t("Otsi toote kategooria"),
			"pn" => "add_prod_cat",
			"multiple" => 1,
			"clid" => CL_SHOP_PRODUCT_CATEGORY,
			"name" => "add_prod_cat",
		));
		$tb->add_menu_button(array(
			"name" => "del_prod_cat",
			"img" => "delete.gif",
			"tooltip" => t("Eemalda tootegrupp"),
		));
		foreach($arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_MATRIX_CATEGORY",
		)) as $c)
		{
			$tb->add_menu_item(array(
				"parent" => "del_prod_cat",
				"text" => $c->to()->name(),
				"link" => aw_url_change_var("del_prod_cat", $c->prop("to")),
			));
		}

		$tb->add_save_button();
	}

	function _set_clients_matrix($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRICE_LIST_CUSTOMER_DISCOUNT,
			"site_id" => array(),
			"lang_id" => array(),
			"pricelist" => $arr["obj_inst"]->id(),
		));
		foreach($ol->arr() as $oid => $o)
		{
			$data[$o->prop("crm_category")][$o->prop("prod_category")] = $oid;
		}
		foreach($arr["request"]["discount"] as $crm_cat => $data)
		{
			foreach($data as $prod_cat => $discount)
			{
				if($oid = $data[$crm_cat][$prod_cat])
				{
					$o = obj($oid);
					$o->set_prop("discount", $discount);
					$o->save();
				}
				else
				{
					$o = obj();
					$o->set_class_id(CL_SHOP_PRICE_LIST_CUSTOMER_DISCOUNT);
					$o->set_name(sprintf(t("%s kliendigrupi allahindlus"), $arr["obj_inst"]->name()));
					$o->set_parent($arr["obj_inst"]->id());
					$o->set_prop("pricelist", $arr["obj_inst"]->id());
					$o->set_prop("crm_category", $crm_cat);
					$o->set_prop("prod_category", $prod_cat);
					$o->set_prop("discount", $discount);
					$o->save();
				}
			}
		}
		if($oids = $arr["request"]["add_crm_cat"])
		{
			foreach(explode(",", $oids) as $oid)
			{
				$arr["obj_inst"]->connect(array(
					"type" => "RELTYPE_MATRIX_ORG_CAT",
					"to" => $oid,
				));
			}
		}
		if($oids = $arr["request"]["add_prod_cat"])
		{
			foreach(explode(",", $oids) as $oid)
			{
				$arr["obj_inst"]->connect(array(
					"type" => "RELTYPE_MATRIX_CATEGORY",
					"to" => $oid,
				));
			}
		}
	}

	function _get_clients_matrix($arr)
	{
		foreach(array("crm", "prod") as $var)
		{
			$c = $arr["request"]["del_".$var."_cat"];
			if($c && $arr["obj_inst"]->is_connected_to(array("to" => $c)))
			{
				$arr["obj_inst"]->disconnect(array(
					"from" => $c,
				));
				$ol = new object_list(array(
					"class_id" => CL_SHOP_PRICE_LIST_CUSTOMER_DISCOUNT,
					"site_id" => array(),
					"lang_id" => array(),
					"pricelist" => $arr["obj_inst"]->id(),
					$var."_category" => $c,
				));
				$ol->delete();
			}
		}

		$t = &$arr["prop"]["vcl_inst"];

		$t->define_field(array(
			"name" => "prod_cat",
			"caption" => t("Tootegrupp"),
			"align" => "center",
		));

		$org_cats = array();
		foreach($arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_MATRIX_ORG_CAT",
		)) as $c)
		{
			$org_cats[] = $c->to();
			$t->define_field(array(
				"name" => "org_cat".$c->prop("to"),
				"caption" => $c->to()->name(),
				"align" => "center",
			));
		}
		$t->set_sortable(false);

		$data = array();
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRICE_LIST_CUSTOMER_DISCOUNT,
			"site_id" => array(),
			"lang_id" => array(),
			"pricelist" => $arr["obj_inst"]->id(),
		));
		foreach($ol->arr() as $o)
		{
			$data[$o->prop("crm_category")][$o->prop("prod_category")] = $o->prop("discount");
		}

		foreach($arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_MATRIX_CATEGORY",
		)) as $c)
		{
			$t_data = array();
			foreach($org_cats as $o)
			{
				$t_data["org_cat".$o->id()] = html::textbox(array(
					"name" => "discount[".$o->id()."][".$c->prop("to")."]",
					"value" => $data[$o->id()][$c->prop("to")],
					"size" => 5,
				));
			}
			$t_data["prod_cat"] = $c->to()->name();
			$t->define_data($t_data);
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["add_crm_cat"] = "";
		$arr["add_prod_cat"] = "";
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_shop_price_list(aw_oid int primary key)");
			return true;
		}
		$ret = false;
		switch($f)
		{
			case "valid_from":
			case "valid_to":
			case "base_price":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				$ret = true;
				break;
			case "groups":
			case "orgs":
			case "persons":
			case "categories":
			case "org_cats":
			case "discount":
			case "short_name":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(255)"
				));
				$ret = true;
				break;
		}

		switch($f)
		{
			case "groups":
			case "orgs":
			case "persons":
			case "categories":
			case "org_cats":
				$this->db_query("ALTER TABLE aw_shop_price_list ADD INDEX(".$f.")");
		}
		return $ret;
	}
}

?>
