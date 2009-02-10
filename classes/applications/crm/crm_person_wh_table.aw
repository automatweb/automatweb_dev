<?php
/*
@classinfo syslog_type=ST_CRM_PERSON_WH_TABLE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_crm_person_wh_table master_index=brother_of master_table=objects index=aw_oid

@default table=aw_crm_person_wh_table
@default group=general

	@property owner type=relpicker reltype=RELTYPE_OWNER field=aw_owner
	@caption Organisatsioon

	@property ppl type=chooser reltype=RELTYPE_PERSON multiple=1 store=connect editonly=1 orient=vertical
	@caption Isikud

@default group=wanted_hours

	@property wanted_hours_tb type=toolbar store=no no_caption=1
	@property wanted_hours_table type=table store=no no_caption=1

@groupinfo wanted_hours caption="Kohustuslikud tunnid" submit=no

@reltype OWNER value=1 clid=CL_CRM_COMPANY
@caption Omanik

@reltype PERSON value=2 clid=CL_CRM_PERSON
@caption Isik
*/

class crm_person_wh_table extends class_base
{
	function crm_person_wh_table()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_person_wh_table",
			"clid" => CL_CRM_PERSON_WH_TABLE
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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
			$this->db_query("CREATE TABLE aw_crm_person_wh_table(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_owner":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function _get_ppl($arr)
	{	
		if (!$this->can("view", $arr["obj_inst"]->owner))
		{
			return PROP_IGNORE;
		}
		$arr["prop"]["options"] = obj($arr["obj_inst"]->owner)->get_workers()->names();
	}

	private function _init_wanted_hours_table($t)
	{
		$t->define_field(array(
			"name" => "dates",
			"caption" => t("Kehtib"),
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "date_from",
			"caption" => t("Kehtib alates"),
			"align" => "center",
			"sortable" => 1,
//			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y",
			"parent" => "dates"
		));
		$t->define_field(array(
			"name" => "date_to",
			"caption" => t("Kehtib kuni"),
			"align" => "center",
			"sortable" => 1,
//			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y",
			"parent" => "dates"
		));

		$t->define_field(array(
			"name" => "hours",
			"caption" => t("Tunnid"),
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "hours_total",
			"caption" => t("Kokku"),
			"align" => "right",
			"sortable" => 1,
			"numeric" => 1,
			"parent" => "hours"
		));

		$t->define_field(array(
			"name" => "hours_cust",
			"caption" => t("Muutuvad"),
			"align" => "right",
			"sortable" => 1,
			"numeric" => 1,
			"parent" => "hours"
		));

		$t->define_field(array(
			"name" => "hours_other",
			"caption" => t("P&uuml;sivad"),
			"align" => "right",
			"sortable" => 1,
			"numeric" => 1,
			"parent" => "hours"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => t("&nbsp;"),
			"align" => "center",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _get_wanted_hours_table($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_wanted_hours_table($t);

		$ppl = $arr["obj_inst"]->get_people_list();
		foreach($ppl->arr() as $person)
		{
			$wh_list = $arr["obj_inst"]->get_must_wh_list_for_person($person);
			foreach($wh_list->arr() as $entry)
			{
				$t->define_data(array(
					"oid" => $entry->id,
					"person" => html::obj_change_url($person),
					"date_from" => date("d.m.Y", $entry->from),
					"date_to" => date("d.m.Y", $entry->to),
					"sort_date_from" => $entry->from,
					"sort_date_to" => $entry->to,
					"hours_total" => $entry->hours_total,
					"hours_cust" => $entry->hours_cust,
					"hours_other" => $entry->hours_other,
					"change" => html::get_change_url($entry->id(), array("return_url" => get_ru()), t("Muuda"))
				));
			}

			$t->define_data(array(
				"oid" => null,
				"person" => html::obj_change_url($person),
				"date_from" => html::date_select(array("name" => "t[".$person->id()."][-1][from]", "month" => "text", "day" => "text", "year" => "text")),
				"date_to" => html::date_select(array("name" => "t[".$person->id()."][-1][to]", "month" => "text", "day" => "text", "year" => "text")),
				"hours_total" => html::textbox(array("name" => "t[".$person->id()."][-1][total]", "size" => 5)),
				"hours_cust" => html::textbox(array("name" => "t[".$person->id()."][-1][cust]", "size" => 5)),
				"hours_other" => html::textbox(array("name" => "t[".$person->id()."][-1][other]", "size" => 5)),
				"sort_date_from" => 3
			));
		}

		$t->set_default_sortby("sort_date_from");
		$t->set_rgroupby(array("person" => "person"));
		$t->set_caption(t("Isikute kohustuslikud t&ouml;&ouml;tunnid"));
	}

	function _set_wanted_hours_table($arr)
	{
		$t = $arr["request"]["t"];
		$ppl = $arr["obj_inst"]->get_people_list();
		foreach($ppl->arr() as $person)
		{
			$pd = $t[$person->id()];
			if (is_array($pd))
			{
				foreach($pd as $row)
				{
					if ($row["total"] > 0)
					{
						$arr["obj_inst"]->add_must_wh_entry_for_person($person, $row);
					}
				}
			}
		}
	}

	function _get_wanted_hours_tb($arr)
	{	
		$arr["prop"]["vcl_inst"]->add_delete_button();
		$arr["prop"]["vcl_inst"]->add_save_button();
	}
}

?>
