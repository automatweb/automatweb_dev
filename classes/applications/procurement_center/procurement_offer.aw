<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement_offer.aw,v 1.6 2006/08/22 15:35:00 markop Exp $
// procurement_offer.aw - Pakkumine hankele 
/*

@classinfo syslog_type=ST_PROCUREMENT_OFFER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_procurement_offers index=aw_oid master_table=objects master_index=brother_of

@default table=objects
@default group=general

	@property procurement type=relpicker reltype=RELTYPE_PROCUREMENT table=aw_procurement_offers field=aw_procurement
	@caption Hange

	@property offerer type=relpicker reltype=RELTYPE_OFFERER table=aw_procurement_offers field=aw_offerer
	@caption Pakkuja

	@property hr_price type=textbox size=10 table=aw_procurement_offers field=aw_hr_price
	@caption Tunni hind

	@property calc_price type=text store=no
	@caption Arvutatud koguhind

	@property price type=textbox size=10 table=aw_procurement_offers field=aw_price
	@caption Hind

	@property accept_date type=date_select table=aw_procurement_offers field=aw_accept_date
	@caption Aktsepteerimistähtaeg
	
	@property shipment_date type=date_select table=aw_procurement_offers field=aw_shipment_date
	@caption Tarne tähtaeg
	
	@property completion_date type=date_select table=aw_procurement_offers field=aw_completion_date
	@caption Valmimist&auml;htaeg

	@property state type=select table=aw_procurement_offers field=aw_state
	@caption Staatus

@groupinfo products caption="Tooted"
@default group=products
@property products type=table no_caption=1


@default group=r_list

	@property p_tb type=toolbar no_caption=1 store=no
	
	@layout p_l type=hbox width=30%:70%
		
		@property p_tr type=treeview no_caption=1 store=no parent=p_l

		@property p_tbl type=table no_caption=1 store=no parent=p_l

@default group=rejected

	@property rejected_table type=table store=no no_caption=1

@groupinfo r caption="N&otilde;uded" submit=no
@groupinfo r_list caption="N&otilde;uete nimekiri" submit=no parent=r
@groupinfo rejected caption="Tagasi l&uuml;katud" submit=no parent=r

@reltype PROCUREMENT value=1 clid=CL_PROCUREMENT
@caption Hange

@reltype OFFERER value=2 clid=CL_CRM_COMPANY
@caption Pakkuja
*/


define("OFFER_STATE_NEW", 0);
define("OFFER_STATE_PUBLIC", 1);
define("OFFER_STATE_REJECTED", 2);
define("OFFER_STATE_ACCEPTED", 3);

class procurement_offer extends class_base
{
	function procurement_offer()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/procurement_offer",
			"clid" => CL_PROCUREMENT_OFFER
		));

		$this->model = get_instance("applications/procurement_center/procurements_model");

		$this->offer_states = array(
			OFFER_STATE_NEW => t("Uus"),
			OFFER_STATE_PUBLIC => t("Avaldatud"),
			OFFER_STATE_REJECTED => t("Tagasi l&uuml;katud"),
			OFFER_STATE_ACCEPTED => t("Vastu v&otilde;etud")
		);

		$this->readyness_states = array(
			PO_IN_BASE => t("Kohe olemas"),
			PO_NEEDS_INSTALL => t("Vajab seadistamist"),
			PO_NEEDS_DEVELOPMENT => t("Uus arendus")
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "rejected_table":
				$this->_rejected_table($arr);
				break;

			case "state":
				$prop["options"] = $this->get_offer_states();
				if ($arr["obj_inst"]->prop("state") != OFFER_STATE_NEW)
				{
					$prop["type"] = "text";
					$prop["value"] = $prop["options"][$arr["obj_inst"]->prop("state")];
				}
				break;

			case "price":
			case "hr_price":
				if ($arr["obj_inst"]->prop("state") != OFFER_STATE_NEW)
				{
					$prop["type"] = "text";
				}
				break;

			case "calc_price":
				$prop["value"] = number_format($this->calculate_price($arr["obj_inst"]), 2);
				break;

			case "procurement":
				// list all procs for the current 
				$ol = $this->model->get_my_procurements();
				$prop["options"] = $ol->names();
				if (!is_oid($arr["obj_inst"]->id()) && $arr["request"]["proc"])
				{
					$prop["value"] = $arr["request"]["proc"];
				}
				break;

//			case "task":
//				$prop["autocomplete_source"] = $this->mk_my_orb("product_autocomplete_source");
//				$prop["autocomplete_params"] = array("product");
//				break;

			case "p_tb":
				/*if ($arr["obj_inst"]->prop("state") != OFFER_STATE_NEW)
				{
					return PROP_IGNORE;
				}*/
				$this->_p_tb($arr);
				break;

			case "p_tr":
				$this->_p_tr($arr);
				break;

			case "p_tbl":
				$this->_p_tbl($arr);
				break;
				
			case "products":
				$t = &$arr["prop"]["vcl_inst"];
				return $this->products_table($t , $arr["obj_inst"]);
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
			case "state":
			case "price":
			case "hr_price":
				if ($arr["obj_inst"]->prop("state") != OFFER_STATE_NEW)
				{
					return PROP_IGNORE;
				}
				break;
			case "products":
				foreach($arr["request"]["products"] as $key => $product)
				{
					if(is_oid($product["row_id"]))
					{
						$o = obj($product["row_id"]);
//						$o->connect(array(
//							"to" => $arr["obj_inst"]->id(),
//							"type" => "RELTYPE_OFFER",
//						));
					}
					else 
					{
						if(strlen($product["product"]) > 1)
						{
							$o = obj();
							$o->set_class_id(CL_PROCUREMENT_OFFER_ROW);
							$o->set_parent($arr["obj_inst"]->id());
							$o->set_name(sprintf(t("%s rida"), $arr["obj_inst"]->name()));
							$o->save();
							$o->connect(array(
								"to" => $arr["obj_inst"]->id(),
								"type" => "RELTYPE_OFFER",
							));
						}
						else continue;
					}
					$o->set_prop("accept", $product["accept"]);
					
					if(array_key_exists($key , $arr["request"]["accept"])) $o->set_prop("accept",1);
					else $o->set_prop("accept",null);
					
					if(!$product["shipment"]) $product["shipment"] = $arr["obj_inst"]->prop("shipment_date");
					foreach($product as $key=>$val)
					{
						switch ($key)
						{
							case "accept":
				//			case "shipment":
								break;
							default:
								if($o->is_property($key)) $o->set_prop($key, $val);
						}
					}
//					$o->disconnect(array(
//						"from" => $arr["obj_inst"]->id(),
//					));
					$o->save();
				}
				break;
		}
		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["d_id"] = $_GET["d_id"];
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "" && $t == "aw_procurement_offers")
		{
			$this->db_query("CREATE TABLE aw_procurement_offers (aw_oid int primary key, aw_price double)");
			return true;
		}

		switch($f)
		{
			case "aw_procurement":
			case "aw_offerer":
			case "aw_state":
			case "aw_completion_date":
			case "aw_accept_date":
			case "aw_shipment_date":
				$this->db_add_col($t, array("name" => $f, "type" => "int"));
				return true;

			case "aw_hr_price":
				$this->db_add_col($t, array("name" => $f, "type" => "double"));
				return true;
		}
	}

	function _p_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$parent = $arr["request"]["d_id"];
		$po = obj($parent);
		if ($po->class_id() == CL_PROCUREMENT_REQUIREMENT)
		{
			$tb->add_button(array(
				'name' => 'new',
				'img' => 'new.gif',
				'tooltip' => t('Lisa'),
				"url" => html::get_new_url(
					CL_PROCUREMENT_REQUIREMENT_SOLUTION, 
					$parent, 
					array(
						"return_url" => get_ru(),
						"set_requirement" => $parent,
						"set_offer" => $arr["request"]["id"]
					)
				)
			));
		}

		$tb->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta'),
			"action" => "save_data"
		));

		$tb->add_button(array(
			'name' => 'delete',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta'),
			"action" => "delete_solutions"
		));
	}

	function _p_tr($arr)
	{
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "procurement_offer",
			),
			"root_item" => obj($arr["obj_inst"]->prop("procurement")),
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU,CL_PROCUREMENT_REQUIREMENT),
				"parent" => $arr["obj_inst"]->prop("procurement"),
				"lang_id" => array(),
				"site_id" => array()
			)),
			"var" => "d_id"
		));
	}

	function _init_p_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "readyness",
			"caption" => t("Valmidus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center",
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "time_to_install",
			"caption" => t("Seadistamise aeg"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "solution",
			"caption" => t("Kommentaar"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "default",
			"caption" => t("Eelistatud"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _p_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_p_tbl($t);

		$parent = $arr["request"]["d_id"];
		if (!$parent)
		{
			return;
		}

		$data = $arr["obj_inst"]->meta("defaults");
		$default = $data[$parent];
		$ol = new object_list(array(
			"class_id" => CL_PROCUREMENT_REQUIREMENT_SOLUTION,
			"requirement" => $parent,
			"offer" => $arr["request"]["id"],
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"readyness" => $this->readyness_states[$o->prop("readyness")],
				"price" => number_format($o->prop("price"), 2),
				"time_to_install" => $o->prop("time_to_install"),
				"solution" => $o->prop("solution"),
				"default" => html::radiobutton(array(
					"name" => "default",
					"value" => $o->id(),
					"checked" => $default == $o->id()
				)),
				"oid" => $o->id()
			));
		}
	}

	function calculate_price($o)
	{
		$reqs = $this->model->get_requirements_from_procurement(obj($o->prop("procurement")));
		$hrs = 0;
		$pr = 0;
		$data = $o->meta("defaults");
		foreach($reqs->arr() as $req)
		{
			if ($this->can("view", $data[$req->id()]))
			{
				$of = obj($data[$req->id()]);
				$hrs += $of->prop("time_to_install");
				$pr += $of->prop("price");
			}
		}

		return $pr + ($o->prop("hr_price") * $hrs);
	}

	/**
		@attrib name=save_data
	**/
	function save_data($arr)
	{
		if ($arr["default"])
		{
			$o = obj($arr["id"]);
			$d = $o->meta("defaults");
			$d[$arr["d_id"]] = $arr["default"];
			$o->set_meta("defaults", $d);
			$o->save();
		}
		return $arr["post_ru"];
	}

	function get_offer_states()
	{
		return $this->offer_states;
	}

	/**
		@attrib name=delete_solutions
	**/
	function delete_solutions($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	function _init_rejected_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));
	}

	function _rejected_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_rejected_table($t);

		$reqs = $this->model->get_requirements_from_procurement(obj($arr["obj_inst"]->prop("procurement")));
		foreach($reqs->arr() as $req)
		{
			$ns = safe_array($req->meta("nonsuitable"));
			foreach($ns as $n => $tmp)
			{
				$t->define_data(array(
					"name" => html::obj_change_url($n)
				));
			}
		}
	}

	function products_table(&$t , $this_obj)
	{
		$t->define_field(array(
			"name" => "jrk",
			"caption" => t("Id"),
			"numeric" => 1,
		));

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
		$t->define_field(array(
        		'name' => 'price',
			'caption' => t('Hind'),
		));
		$t->define_field(array(
			'name' => 'currency',
			'caption' => t('Valuuta'),
		));
		$t->define_field(array(
			'name' => 'shipment',
			'caption' => t('Tarneaeg'),
		));
/*		$t->define_field(array(
			'name' => 'accept',
			'caption' => t('Aktsepteeritud'),
		));*/
		
		$t->define_chooser(array(
			"name" => "accept",
			"field" => "oid",
			'caption' => t('Aktsepteeritud'),
		));


		$unit_list = new object_list(array(
			"class_id" => CL_UNIT
		));
		$unit_opts = array();
		foreach($unit_list->arr() as $unit)
		{
			$unit_opts[$unit->id()] = $unit->prop("unit_code");
		}
		
		$curr_list = new object_list(array(
			"class_id" => CL_CURRENCY
		));
		$curr_opts = $curr_list->names();
		
		$conns = $this_obj->connections_to(array(
			'reltype' => 1,
			'class' => CL_PROCUREMENT_OFFER_ROW,
		));
		foreach($conns as $conn)
		{
			if(is_oid($conn->prop("from")))$row = obj($conn->prop("from"));
			else continue;
			$x = $conn->prop("from");
			$accept = "";
			if($row->prop("accept"))
			{
				$accept = html::checkbox(array(
					"name" => "products[".$x."][accept]",
					"value" => $row->prop("accept"),
					"checked" => $row->prop("accept"),
				));
			}
			$t->define_data(array(
				"jrk"		=> $x+1,
				"row_id" 	=> $row->id(),
				"product"	=> html::textbox(array(
							"name" => "products[".$x."][product]",
							"size" => "6",
							"value" => $row->prop("product"),
							)).html::hidden(array(
								"name" => "products[".$x."][row_id]", 
								"value" => $row->id())),
							
				"amount"	=> html::textbox(array(
							"name" => "products[".$x."][amount]",
							"size" => "6",
							"value" => $row->prop("amount"),
							)),
				'unit'		=> html::select(array(
							"name" => "products[".$x."][unit]",
							"options" => $unit_opts,
							"value" => $row->prop("unit"),
							)),
				'price'		=> html::textbox(array(
							"name" => "products[".$x."][price]",
							"size" => "6",
							"value" => $row->prop("price"),
							)),
				'currency'	=> html::select(array(
							"name" => "products[".$x."][currency]",
							"options" => $curr_opts,
							"value" => $row->prop("currency"),
							)),
				'shipment'	=> html::textbox(array(
							"name" => "products[".$x."][shipment]",
							"size" => "6",
							"value" => $row->prop("shipment"),
							)),
				'accept'	=> $accept,
				"oid"		=> $row->id(),
			
			));
		}
		//lisaread
		$x = -10;
		$lisa = $x + 10;
		$u = get_instance(CL_USER);
		$co = obj($u->get_current_company());
		if(is_object($co))$curr_val = $co->prop("currency");
		while($x < $lisa)
		{
			$t->define_data(array(
			//	"jrk"		=> $x+2,
				"product"	=> html::textbox(array(
							"name" => "products[".$x."][product]",
							"size" => "15",
							)),
				"amount"	=> html::textbox(array(
							"name" => "products[".$x."][amount]",
							"size" => "6",
							)),
				'unit'		=> html::select(array(
							"name" => "products[".$x."][unit]",
							"options" => $unit_opts,
							)),
				'price'		=> html::textbox(array(
							"name" => "products[".$x."][price]",
							"size" => "6",
							)),
				'currency'	=> html::select(array(
							"name" => "products[".$x."][currency]",
							"options" => $curr_opts,
							"value" => $curr_val,
							)),
				'shipment'	=> html::textbox(array(
							"name" => "products[".$x."][shipment]",
							"size" => "6",
							)),
//				'accept'	=> html::checkbox(array(
//							"name" => "products[".$x."][accept]",
//							)),
				'oid'		=> $x,
			));
			$x++;
		}
		
	/*	
Ühik (lb, süsteemi Ühikute koodidega), Valuuta (lb, süsteemi valuutadega, vaikimisi Minu Organisatsiooni vaikimisi valitud valuuta), Tarneaeg (kp tekstiväljana, kus lõpus on ?vali? link), Aktsept (cb). Tooteväli on Autocomplete põhimõttel ehitatud, loetakse tooteid seotud laost. Kui sisestatakse tootenimetus, mida varem laos ei ole, siis salvestatakse see uue tootena, kuid enne küsitakse popup aknas tootekategooria (kui mitu uut toodet, siis on küsimise tabelis mitu rida). Tootekategooria kuvatakse listboxina, erinevad tasemed on trepitud (tähestiku järjekord). Peale 10 rea salvestamist tekib võimalus uue 10 rea sisestamiseks. Juhul, kui Tarneaeg jäetakse toote taga tühjaks, kuvatakse peale salvestamist sinna sama kuupäev, kui Pakkumises määratud tarne tähtaeg. */
		$t->set_default_sortby("jrk");
	}

	function get_avg_score($offer)
	{
		// get all prefered solutions in offer and their scores
		$reqs = $this->model->get_requirements_from_procurement(obj($offer->prop("procurement")));
		$sum = 0;
		$cnt = 0;
		$def = $offer->meta("defaults");
		foreach($reqs->arr() as $req)
		{
			// get the preferred solution for this requirement in this offer
			$sol = $def[$req->id()];
			if ($sol)
			{
				$ass = $req->meta("assessments");
				$sum += $ass[$sol];
			}
			$cnt++;
		}
		return $sum / $cnt;
	}
}
?>
