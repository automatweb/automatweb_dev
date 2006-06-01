<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement.aw,v 1.2 2006/06/01 15:10:01 kristo Exp $
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

@default group=s_general

	@property publish_date type=date_select table=aw_procurements field=aw_publish_date default=-1
	@caption Avaldamise kuup&auml;ev

	@property offers_date type=date_select table=aw_procurements field=aw_offers_date default=-1
	@caption Pakkumiste esitamise kuup&auml;ev

	@property completion_date type=date_select table=aw_procurements field=aw_completion_date default=-1
	@caption Valmimiskuup&auml;ev

	@property compl_date_req type=checkbox ch_value=1 field=aw_compl_date_req
	@caption Lahenduse valmimist&auml;htaeg n&otilde;utud

@default group=s_pris

	@property pri_t type=releditor mode=manager reltype=RELTYPE_PRI props=name,pri table_fields=name,pri table_edit_fields=name,pri
	@caption Prioriteedid

@default group=team

	@property team type=table store=no no_caption=1

@default group=crit

	@property crit_t type=releditor mode=manager reltype=RELTYPE_CRITERIA props=name table_fields=name 
	@caption Kriteeriumid

@groupinfo d caption="N&otilde;uded" submit=no
@groupinfo s caption="M&auml;&auml;rangud"
	@groupinfo s_general caption="M&auml;&auml;rangud"  parent=s
	@groupinfo s_pris caption="Prioriteedid" parent=s
	@groupinfo team caption="Meeskond" parent=s
	@groupinfo crit caption="Kriteeriumid" parent=s submit=no

@groupinfo o caption="Pakkumised" submit=no

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
			case "crit_t":
				$prop["direct_links"] = 1;
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
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "team":
				$this->_save_team($arr);
				break;
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
			case "aw_publish_date":
			case "aw_offers_date":
			case "aw_completion_date":
			case "compl_date_req":
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

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CRITERIA")) as $c)
		{
			$t->add_item(0, array(
				"id" => $c->prop("to"),
				"name" => $c->prop("to.name"),
				"url" => aw_url_change_var("co_id", $c->prop("to")),
			));
		}
		$t->add_item(0, array(
			"id" => "top",
			"name" => t("Edetabel"),
			"url" => aw_url_change_var("co_id", "top"),
		));
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
				"score" => $scores[$offer->id()]
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
}
?>
