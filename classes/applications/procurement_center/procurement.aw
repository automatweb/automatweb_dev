<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement.aw,v 1.8 2006/10/05 12:17:25 markop Exp $
// procurement.aw - Hange 
/*

@classinfo syslog_type=ST_PROCUREMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_procurements index=aw_oid master_index=brother_of master_table=objects
@default table=objects


@default group=general

	@property orderer type=relpicker reltype=RELTYPE_ORDERER table=aw_procurements field=aw_orderer
	@caption Tellija

	@property state type=select table=aw_procurements field=aw_state
	@caption Staatus

	@property offerers type=relpicker multiple=1 store=connect reltype=RELTYPE_OFFERER
	@caption Pakkujad

	@property winning_offer type=relpicker reltype=RELTYPE_WINNING_OFFER table=aw_procurements field=aw_winning_offer
	@caption V&otilde;itnud pakkumine

	@property proj type=text table=aw_procurements field=aw_proj
	@caption Projekt



@default group=d
	
	@property d_tb type=toolbar no_caption=1 store=no
	
	@layout d_l type=hbox width=30%:70%
		
		@property d_tr type=treeview no_caption=1 store=no parent=d_l

		@property d_tbl type=table no_caption=1 store=no parent=d_l

@default group=products
		@property products type=table no_caption=1 table=objects field=meta method=serialize

@default group=o
	
	@property o_tb type=toolbar no_caption=1 store=no
	
	@layout o_l type=hbox width=30%:70%
		
		@property o_tr type=treeview no_caption=1 store=no parent=o_l

		@property o_tbl type=table no_caption=1 store=no parent=o_l

@default group=s_general

	@property publish_date type=date_select table=aw_procurements field=aw_publish_date default=-1
	@caption Avaldamise kuup&auml;ev

	@property offers_date type=date_select table=aw_procurements field=aw_offers_date default=-1
	@caption Pakkumiste esitamise kuup&auml;ev

	@property completion_date type=date_select table=aw_procurements field=aw_completion_date default=-1
	@caption Valmimiskuup&auml;ev

	@property compl_date_req type=checkbox ch_value=1 field=aw_compl_date_req table=aw_procurements
	@caption Lahenduse valmimist&auml;htaeg n&otilde;utud

@default group=s_pris

	@property pri_t type=releditor mode=manager reltype=RELTYPE_PRI props=name,pri table_fields=name,pri table_edit_fields=name,pri
	@caption Prioriteedid

@default group=team

	@property team type=table store=no no_caption=1

@default group=crit

	@property crit_tb type=toolbar store=no no_caption=1
	@caption Kriteeriumite toolbar

	@property crit_t type=table store=no no_caption=1
	@caption Kriteeriumid

@groupinfo d caption="N&otilde;uete nimekiri" submit=no
@groupinfo products caption="Toodete nimekiri"

@groupinfo s caption="Hanke tingimused"
	@groupinfo s_general caption="M&auml;&auml;rangud"  parent=s
	@groupinfo s_pris caption="Prioriteedid" parent=s
	@groupinfo team caption="Meeskond" parent=s
	@groupinfo crit caption="Kriteeriumid" parent=s submit=no

@groupinfo o caption="Tehtud pakkumised" submit=no

@reltype OFFERER value=1 clid=CL_CRM_COMPANY
@caption Pakkuja

@reltype TEAM_MEMBER value=2 clid=CL_CRM_PERSON
@caption Meeskonna liige

@reltype ORDERER value=3 clid=CL_CRM_COMPANY
@caption Tellija

@reltype WINNING_OFFER value=4 clid=CL_PROCUREMENT_OFFER
@caption V&otilde;tnud pakkumine

@reltype PRI value=5 clid=CL_PROCUREMENT_PRIORITY
@caption Prioriteet

@reltype CRITERIA value=6 clid=CL_PROCUREMENT_CRITERIA
@caption Kriteerium

*/


class procurement extends class_base
{
	function procurement()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/procurement",
			"clid" => CL_PROCUREMENT
		));

		$this->model = get_instance("applications/procurement_center/procurements_model");

		$this->proc_states = array(
			PROCUREMENT_NEW => t("Uus"),
			PROCUREMENT_PUBLIC => t("Avaldatud"),
			PROCUREMENT_INPROGRESS => t("T&ouml;&ouml;s"),
			PROCUREMENT_DONE => t("Valmis"),
			PROCUREMENT_CLOSED => t("Suletud")
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "proj":
				if (!$this->can("view", $arr["obj_inst"]->prop("winning_offer")))
				{
					return PROP_IGNORE;
				}
				$prop["value"] = html::obj_change_url($prop["value"]);
				break;

			case "crit_t":
				$this->_crit_t($arr);
				break;

			case "crit_tb":
				$this->_crit_tb($arr);
				break;

			case "team":
				$this->_team($arr);
				break;

			case "winning_offer":
				if ($arr["obj_inst"]->prop("state") < PROCUREMENT_INPROGRESS)
				{
					return PROP_IGNORE;
				}
				else
				{
					$prop["type"] = "text";
					if ($this->can("view", $prop["value"]))
					{
						$val = obj($prop["value"]);
						$prop["value"] = $val->name();
					}
					else
					{
						$prop["value"] = t("Valimata");
					}
				}
				break;

			case "state":
				$prop["options"] = $this->get_state_list();
				break;

			case "d_tb":
				$this->_d_tb($arr);
				break;

			case "d_tr":
				$this->_d_tr($arr);
				break;

			case "d_tbl":
				$this->_d_tbl($arr);
				break;

			case "o_tb":
				$this->_o_tb($arr);
				break;

			case "o_tr":
				$this->_o_tr($arr);
				break;

			case "o_tbl":
				$this->_o_tbl($arr);
				break;

			case "offerers":
				$prop["value"] = $this->make_keys(array_keys($prop["options"]));
				break;
			
			case "products":
				$prop["value"] = $this->products_table(&$arr["prop"]["vcl_inst"],$arr["obj_inst"]);
				break;
		};
		return $retval;
	}

	function products_table(&$t , $this_obj)
	{
		//see esimene vaid sorteerimiseks 
//		$t->define_field(array(
//			"name" => "jrk",
//			"caption" => t("Jrk"),
//			"numeric" => 1,
//		));
		$company_id = $this_obj->prop("orderer");
		if(is_oid($company_id) && $this->can("view", $company_id)) $co = obj($company_id);
		$t->define_field(array(
			"name" => "product",
			"caption" => t("Toode"),
		));
		$t->define_field(array(
			'name' => 'amount',
			'caption' => t('Kogus'),
		));
		$t->define_field(array(
			'name' => 'unit',
			'caption' => t('&Uuml;hik'),
		));

		$unit_list = new object_list(array(
			"class_id" => CL_UNIT
		));
		
		$unit_opts = array();
		foreach($unit_list->arr() as $unit)
		{
			$unit_opts[$unit->id()] = $unit->prop("unit_code");
		}

		$products = $this_obj->meta("products");
		$x = 0;
		foreach($products as $product)
		{
			if(!$product["product"]) continue;
			$t->define_data(array(
	//			"jrk"		=> $x+1,
				"product"	=> html::textbox(array(
							"name" => "products[".$x."][product]",
							"size" => "15",
							"value" => $product["product"],
							"autocomplete_source" => $this->mk_my_orb ("product_autocomplete_source", array("buyer" =>$co->id()), CL_PROCUREMENT, false, true),
							"autocomplete_params" => "products[".$x."][product]",
							)) ,
				"amount"	=> html::textbox(array(
							"name" => "products[".$x."][amount]",
							"size" => "6",
							"value" => $product["amount"],
							)),
				'unit'		=> html::select(array(
							"name" => "products[".$x."][unit]",
							"options" => $unit_opts,
							"value" => $product["unit"],
						)),
			));
			$x++;
		}
	
		//lisaread
		$enough = $x +10;
		$u = get_instance(CL_USER);
		if(!is_object($co))$co = obj($u->get_current_company());
		if(is_object($co))$curr_val = $co->prop("currency");
		while($x < $enough)
		{
			$t->define_data(array(
	//			"jrk"		=> $x+1,
				"product"	=> html::textbox(array(
							"name" => "products[".$x."][product]",
							"size" => "15",
							"autocomplete_source" => $this->mk_my_orb ("product_autocomplete_source", array("buyer" =>$co->id()), CL_PROCUREMENT, false, true),
							"autocomplete_params" => "products[".$x."][product]",
							)),
				"amount"	=> html::textbox(array(
							"name" => "products[".$x."][amount]",
							"size" => "6",
							)),
				'unit'		=> html::select(array(
							"name" => "products[".$x."][unit]",
							"options" => $unit_opts,
							)),
			));
			$x++;
		}
		$t->set_sortable(false);
	//	$t->set_default_sortby("jrk");
	}

	function _get_sub_folders($obj)
	{
		$parents = array();
		$ol = new object_list(array(
			"lang_id" => array(),
			"parent" => $obj->id(),
			"class_id" => CL_MENU,
		));
		$parents[] = $obj->id();
		foreach($ol->arr() as $folder)
		{
			$parents = array_merge($parents,$this->_get_sub_folders($folder));
		}
		return $parents;
	}

	/**
		@attrib name=product_autocomplete_source
		@param product optional
	**/
	function product_autocomplete_source($arr)
	{
		$ac = get_instance("vcl/autocomplete");
		$arr = $ac->get_ac_params($arr);
		
		$ol = new object_list();
		if(is_oid($_GET["buyer"]))
		{
			$co = obj($_GET["buyer"]);
			foreach($co->connections_from(array("type" => "RELTYPE_WAREHOUSE")) as $conn)
			{
				$warehouse = obj($conn->prop("to"));
				$warehouse->config = obj($warehouse->prop("conf"));
				$folder = obj($warehouse->config->prop("prod_fld"));
				$parents = $this->_get_sub_folders($folder);
			}
		}
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"name" => $arr["product"]."%",
			"lang_id" => array(),
			"site_id" => array(),
			"limit" => 2000,
			"parent" => $parents,
		));
		return $ac->finish_ac($ol->names());
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "winning_offer":
			case "proj":
				return PROP_IGNORE;

			case "team":
				$this->_save_team($arr);
				break;
			case "products":
				//liidab need tooted juurde mida veel ei eksisteeri
				$_SESSION["procurement"]["val"] = $prop["value"];
				$popup = "<script name= javascript>window.open('".$this->mk_my_orb("set_type", array("val" => $prop["value"] , "id" => $arr["obj_inst"]->id()))."','', 'toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600')
					</script>";
				die($popup);
				break;
		}
		return $retval;
	}	
	
	/**
		@attrib name=set_type
	**/
	function set_type($arr)
	{
		$this_object = obj($_GET["id"]);
		classload("vcl/table");
		$t = new aw_table(array(
			"layout" => "generic"
		));
			$t->define_field(array(
			"name" => "name",
			"sortable" => 1,
			"caption" => t("Nimi")
		));
			$t->define_field(array(
			"name" => "type",
			"sortable" => 1,
			"caption" => t("T&uuml;&uuml;p")
		));
		$t->set_default_sortby("name");
		$types = new object_list(array(
				"class_id" => array(CL_SHOP_PRODUCT_TYPE),
				"lang_id" => array(),
				"site_id" => array(),
		));
		$options = $types->names();
		asort($options);
		if($_POST["types"])
		{
			foreach($_GET["val"] as $product)
			{
				$ol = new object_list(array(
					"class_id" => array(CL_SHOP_PRODUCT),
					"name" => $product["product"],
					"lang_id" => array(),
					"site_id" => array(),
				));

				$co = obj($this_object->prop("orderer"));
				$warehouse = $co->get_first_obj_by_reltype("RELTYPE_WAREHOUSE");
				$parent = $warehouse->id();
				if (!$ol->count())
				{
					$p = obj();
					$p->set_class_id(CL_SHOP_PRODUCT);
					$p->set_parent($parent);
					$p->set_name($product["product"]);
					$p->set_prop("item_type" ,$_POST["types"][$product["product"]]);
					$p->save();
				}
			}
			$this_object->set_meta("products",$_SESSION["procurement"]["val"]);
			$this_object->save();
			$_SESSION["procurement"] = null;
			die("<script type='text/javascript'>
			window.opener.location.href='".$this->mk_my_orb("change", array("id"=>$_GET["id"] , "group" => "products"))."';
			window.close();
			</script>");
		}
		
		$new_products = 0;
		foreach($_SESSION["procurement"]["val"] as $product)
		{
			$ol = new object_list(array(
				"class_id" => array(CL_SHOP_PRODUCT),
				"name" => $product["product"],
				"lang_id" => array(),
				"site_id" => array(),
			));
			
			$co = obj($this_object->prop("orderer"));
			$warehouse = $co->get_first_obj_by_reltype("RELTYPE_WAREHOUSE");
			$parent = $warehouse->id();
			if (!$ol->count())
			{
				$new_products = 1;
				$dat = array(
					"name" => $product["product"],
					"type" => html::select(array("options" => $options, "name" => "types[".$product["product"]."]")),
				);
				if(strlen($product["product"]) > 0) $t->define_data($dat);
			}
		}
		
		if(!$new_products)
		{
			$this_object->set_meta("products",$_SESSION["procurement"]["val"]);
			$this_object->save();
			$_SESSION["procurement"] = null;
			die("<script type='text/javascript'>
			window.opener.location.href='".$this->mk_my_orb("change", array("id"=>$_GET["id"] , "group" => "products"))."';
			window.close();
			</script>");
		}
		$t->define_data(array("type" => html::submit(array("name" => "submit", "class" => "submit" , "value" => "submit" , "onclick"=>"self.disabled=true;submit_changeform(''); return false;") )));
		$t->set_sortable(false);
		return "<form action='".$this->mk_my_orb("set_type", array("id" => $_GET["id"]))."' method='POST' name='changeform' enctype='multipart/form-data' >".$t->draw()."</form>";
	}


	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _d_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$parent = $arr["request"]["d_id"] ? $arr["request"]["d_id"] : $arr["obj_inst"]->id();

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Kataloog'),
			'link'=> html::get_new_url(CL_MENU, $parent, array("return_url" => get_ru()))
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('N&otilde;ue'),
			'link'=> html::get_new_url(CL_PROCUREMENT_REQUIREMENT, $parent, array("return_url" => get_ru()))
		));

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud n&otilde;uded'),
			'action' => 'delete_procurements',
			'confirm' => t("Kas oled kindel et soovid valitud n&otilde;uded kustudada?")
		));
	}

	function _d_tr($arr)
	{
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "procurement_center",
			),
			"root_item" => $arr["obj_inst"],
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $arr["obj_inst"]->id(),
				"lang_id" => array(),
				"site_id" => array()
			)),
			"var" => "d_id"
		));
	}

	function _init_d_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("N&otilde;ude nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "createdby_person",
			"caption" => t("Looja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "modifiedby_person",
			"caption" => t("Muutja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _d_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_d_tbl($t);

		$parent = $arr["request"]["d_id"] ? $arr["request"]["d_id"] : $arr["obj_inst"]->id();

		$ol = new object_list(array(
			"class_id" => CL_PROCUREMENT_REQUIREMENT,
			"parent" => $parent,
			"lang_id" => array(),
			"site_id" => array()
		));
		$t->data_from_ol($ol, array("change_col" => "name"));
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "" && $t == "aw_procurements")
		{
			$this->db_query("CREATE TABLE aw_procurements (aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_orderer":
			case "aw_state":
			case "aw_winning_offer":
			case "aw_publish_date":
			case "aw_offers_date":
			case "aw_completion_date":
			case "aw_compl_date_req":
			case "aw_proj":
				$this->db_add_col($t, array("name" => $f, "type" => "int"));
				return true;
		}
	}
	
	/**
		@attrib name=delete_procurements
	**/
	function delete_procurements($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	function _o_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		if ($arr["obj_inst"]->prop("state") == PROCUREMENT_PUBLIC)
		{
			$tb->add_button(array(
				'name' => 'select_winner',
				'tooltip' => t('Vali v&otilde;itjapakkumine'),
				'action' => 'select_winning_offer',
				'confirm' => t("Kas oled kindel et soovid valitud pakkumise v&auml;lja valida?")
			));
		}
	}

	function _o_tr($arr)
	{
		classload("core/icons");
		$t =& $arr["prop"]["vcl_inst"];
		$coid = $arr["request"]["co_id"] ? $arr["request"]["co_id"] : "top";
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CRITERIA")) as $c)
		{
			$t->add_item(0, array(
				"id" => $c->prop("to"),
				"name" => $coid == $c->prop("to") ? "<b>".$c->prop("to.name")."</b>" : $c->prop("to.name"),
				"url" => aw_url_change_var("co_id", $c->prop("to")),
			));
		}
		$t->add_item(0, array(
			"id" => "top",
			"name" => $coid == "top" ? "<b>".t("Edetabel")."</b>" : t("Edetabel"),
			"url" => aw_url_change_var("co_id", "top"),
		));
	}

	function _init_o_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Pakkumise nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "offerer",
			"caption" => t("Pakkuja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
		));
		$t->define_field(array(
			"name" => "score",
			"caption" => t("Punktid"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _o_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_o_tbl($t);

		if ($arr["request"]["co_id"] == "")
		{
			$arr["request"]["co_id"] = "top";
		}

		$offers = $this->model->get_all_offers_for_procurement($arr["obj_inst"]);
		$scores = $this->get_scores_for_proc($arr["request"]["co_id"], $offers, $arr["obj_inst"]);
		foreach($offers->arr() as $offer)
		{
			$t->define_data(array(
				"name" => html::obj_change_url($offer),
				"offerer" => html::obj_change_url($offer->prop("offerer")),
				"price" => number_format($offer->prop("price"), 2),
				"oid" => $offer->id(),
				"score" => number_format($scores[$offer->id()], 2)
			));
		}
		$t->set_default_sortby("score");
		$t->set_default_sorder("desc");
	}

	/**
		@attrib name=select_winning_offer
	**/
	function select_winning_offer($arr)
	{
		// mark all other offers for this procurement as unaccepted
		$proc = obj($arr["id"]);
		$offers = $this->model->get_all_offers_for_procurement($proc);

		if (is_array($arr["sel"]) && count($arr["sel"]) == 1)
		{
			// the winning offer as succeeded
			$winner = obj(reset($arr["sel"]));
			$wi = $winner->instance();

			$winner->set_prop("state", OFFER_STATE_ACCEPTED);
			$winner->save();

			foreach($offers->arr() as $offer)
			{
				if ($offer->id() != $winner->id())
				{
					$offer->set_prop("state", OFFER_STATE_REJECTED);
					$offer->save();
				}
			}

			$p = obj($arr["id"]);
			$p->set_prop("state", PROCUREMENT_INPROGRESS);
			$p->set_prop("winning_offer", $winner->id());

			// create customer relation between buyer and seller
			$cc = get_instance(CL_CRM_COMPANY);
			$crel = $cc->get_cust_rel(obj($p->prop("orderer")), true, $winner->prop("offerer"));

			// create project and attach to the procurement
			$proj = obj();
			$proj->set_class_id(CL_PROJECT);
			$proj->set_parent($p->id());
			$proj->set_name($p->name());
			// add both parties as participants to proj
			$proj->set_prop("participants", array(
				$p->prop("orderer") => $p->prop("orderer"),
				$winner->prop("offerer") => $winner->prop("offerer")
			));
			$proj->set_prop("orderer", $p->prop("orderer"));
			$proj->set_prop("implementor", $winner->prop("offerer"));
			$proj->save();

			$p->set_prop("proj", $proj->id());
			$p->save();

			header("Location: ".html::get_change_url($proj->id(), array("return_url" => $arr["post_ru"])));
			die();
		}
		return $arr["post_ru"];
	}

	function get_state_list()
	{
		return $this->proc_states;
	}

	function _init_team_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Vali"),
			"align" => "center"
		));
	}

	function _team($arr)
	{	
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_team_t($t);

		$members = $this->model->get_team_from_procurement($arr["obj_inst"]);
		$center = $this->model->get_proc_center_for_co(obj($arr["obj_inst"]->prop("orderer")));
		$team = $this->model->get_team_from_center($center);
		foreach($team as $member_id => $price)
		{
			$p = obj($member_id);
			$section = $rank = "";

			$conns = $p->connections_to(array(
				"from.class_id" => CL_CRM_SECTION,
				"from" => $sections
			));
			if (count($conns))
			{
				$con = reset($conns);
				$section = $con->prop("from");
			}

			$t->define_data(array(
				"name" => html::obj_change_url($p),
				"phone" => html::obj_change_url($p->prop("phone")),
				"email" => html::obj_change_url($p->prop("email")),
				"section" => html::obj_change_url($section),
				"rank" => html::obj_change_url($p->get_first_obj_by_reltype("RELTYPE_RANK")),
				"id" => $p->id(),
				"price" => $price,
				"sel" => html::checkbox(array(
					"name" => "sel[$member_id]",
					"value" => $member_id,
					"checked" => isset($members[$member_id])
				))
			));
		}
	}

	function _save_team($arr)
	{
		$members = $this->model->get_team_from_procurement($arr["obj_inst"]);

		// add new ones
		foreach(safe_array($arr["request"]["sel"]) as $member)
		{
			if (!isset($members[$member]))
			{
				$arr["obj_inst"]->connect(array(
					"to" => $member,
					"type" => "RELTYPE_TEAM_MEMBER"
				));
			}
		}
		// remove removed ones
		foreach($members as $member => $stuff)
		{
			if (!isset($arr["request"]["sel"][$member]))
			{
				$arr["obj_inst"]->disconnect(array(
					"from" => $member
				));
			}
		}
	}

	function get_scores_for_proc($crit, $offer_list, $o)
	{
		if ($crit == "top")
		{
			$score = array();
			foreach($o->connections_from(array("type" => "RELTYPE_CRITERIA")) as $c)
			{
				$co = $c->to();
				$ci = $co->instance();
				$tmp = $ci->get_score_for_crit($co, $offer_list, $o);
				$pct = $co->prop("pct");
				foreach($tmp as $id => $val)
				{
					$score[$id] += $val * ($pct / 100.0);
				}
			}
			return $score;
		}
		else
		if ($crit)
		{
			$co = obj($crit);
			$ci = $co->instance();
			return $ci->get_score_for_crit(obj($crit), $offer_list, $o);
		}
		return 0;
	}

	function _init_crit_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Kriteeriumi nimi"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "type",
			"caption" => t("T&uuml;p"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "pct",
			"caption" => t("Protsent"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _crit_t($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_crit_t($t);

		$i = get_instance(CL_PROCUREMENT_CRITERIA);
		$types = $i->get_types();
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CRITERIA")) as $c)
		{
			$crit = $c->to();
			$t->define_data(array(
				"name" => html::obj_change_url($crit),
				"type" => $types[$crit->prop("type")],
				"pct" => $crit->prop("pct"),
				"oid" => $crit->id()
			));
		}
		$t->set_default_sortby("name");
	}

	function _crit_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Lisa kriteerium'),
			"url" => html::get_new_url(CL_PROCUREMENT_CRITERIA, $arr["obj_inst"]->id(), array("return_url" => get_ru(), "alias_to" => $arr["obj_inst"]->id(), "reltype" => 6))
		));
		$tb->add_button(array(
			'name' => 'delete',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta kriteeriumid'),
			"confirm" => t("Oled kindel et soovid valitud kriteeriumid kustutada?"),
			"action" => "delete_procurements"
		));
	}
	
	
	function callback_generate_scripts($arr)
	{
		
		$meta = $arr["obj_inst"]->meta("products"); $size = sizeof($meta);
		$ret = "
		function aw_submit_handler() {".
		"".
		// fetch list of companies with that name and ask user if count > 0
		"var url = '".$this->mk_my_orb("check_existing")."';";
		
		$x = 0;
		while($x < $size)
		{
			$ret.= "url = url + '&p[".$x."]=' + escape(document.changeform.products_".$x."__product_.value);
				";
			$x++;
		}
		
		$ret.= "num= aw_get_url_contents(url);".
		"if (num != \"\")
		{
			var ansa = confirm(num);
			if (ansa)
			{
				return true;
			}
			return false;
		}".
		"return true;}
		";
		return $ret;
	}
	
	/**
		@attrib name=check_existing
		@param p optional type=array
	**/
	function check_existing($arr)
	{
		foreach($arr["p"] as $product)
		{	
			if(!(strlen($product) > 1)) continue;
			if (mb_detect_encoding($arr["p"],"UTF-8,ISO-8859-1") == "UTF-8")
			{
				$product = iconv("UTF-8", aw_global_get("charset"), $product);
			}
			// if project exists
			$ol = new object_list(array(
				"class_id" => array(CL_SHOP_PRODUCT),
				"name" => $product,
				"lang_id" => array(),
				"site_id" => array(),
			));
			if (!$ol->count())
			{
				$ret .= sprintf(t("Toodet nimega %s ei ole olemas, kui vajutate ok, lisatakse\n"), $product);
			}
		}
		die(iconv(aw_global_get("charset"), "UTF-8", $ret));
	}
	
}
?>
