<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement.aw,v 1.1 2006/04/27 08:14:37 kristo Exp $
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

	@property team type=relpicker multiple=1 store=connect reltype=RELTYPE_TEAM_MEMBER
	@caption Meeskond

	@property winning_offer type=relpicker reltype=RELTYPE_WINNING_OFFER table=aw_procurements field=aw_winning_offer
	@caption V&otilde;itnud pakkumine

@default group=d
	
	@property d_tb type=toolbar no_caption=1 store=no
	
	@layout d_l type=hbox width=30%:70%
		
		@property d_tr type=treeview no_caption=1 store=no parent=d_l

		@property d_tbl type=table no_caption=1 store=no parent=d_l

@default group=o
	
	@property o_tb type=toolbar no_caption=1 store=no
	
	@layout o_l type=hbox width=30%:70%
		
		@property o_tr type=treeview no_caption=1 store=no parent=o_l

		@property o_tbl type=table no_caption=1 store=no parent=o_l


@groupinfo d caption="N&otilde;uded" submit=no
@groupinfo o caption="Pakkumised" submit=no

@reltype OFFERER value=1 clid=CL_CRM_COMPANY
@caption Pakkuja

@reltype TEAM_MEMBER value=2 clid=CL_CRM_PERSON
@caption Meeskonna liige

@reltype ORDERER value=3 clid=CL_CRM_COMPANY
@caption Tellija

@reltype WINNING_OFFER value=4 clid=CL_PROCUREMENT_OFFER
@caption V&otilde;tnud pakkumine

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
			"caption" => t("Nimi"),
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
	
		$cos = array();
		$offers = $this->model->get_all_offers_for_procurement($arr["obj_inst"]);
		foreach($offers->arr() as $offer)
		{
			$cos[$offer->prop("offerer")] = $offer->prop("offerer.name");
		}

		foreach($cos as $co => $co_name)
		{
			$co = obj($co);
			$t->add_item(0, array(
				"id" => $co->id(),
				"name" => $co->name(),
				"url" => aw_url_change_var("co_id", $co->id()),
			));
		}
	}

	function _init_o_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
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
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _o_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_o_tbl($t);

		$offers = $this->model->get_all_offers_for_procurement($arr["obj_inst"]);
		foreach($offers->arr() as $offer)
		{
			if (!$arr["request"]["co_id"] || $arr["request"]["co_id"] == $offer->prop("offerer"))
			{
				$t->define_data(array(
					"name" => html::obj_change_url($offer),
					"offerer" => html::obj_change_url($offer->prop("offerer")),
					"price" => number_format($offer->prop("price"), 2),
					"oid" => $offer->id()
				));
			}
		}
	}

	/**
		@attrib name=select_winning_offer
	**/
	function select_winning_offer($arr)
	{
die(dbg::dump($arr));
		// mark all other offers for this procurement as unaccepted
		$offers = $this->model->get_all_offers_for_procurement(obj($arr["id"]));

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
			$p->save();
		}
		return $arr["post_ru"];
	}

	function get_state_list()
	{
		return $this->proc_states;
	}
}
?>
