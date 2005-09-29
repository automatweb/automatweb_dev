<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_bill.aw,v 1.2 2005/09/29 06:38:24 kristo Exp $
// crm_bill.aw - Arve 
/*

@classinfo syslog_type=ST_CRM_BILL relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects

@tableinfo aw_crm_bill index=aw_oid master_index=brother_of master_table=objects

@default group=general

@property bill_no type=textbox table=aw_crm_bill field=aw_bill_no
@caption Number

@property bill_date type=date_select table=aw_crm_bill field=aw_date
@caption Kuup&auml;ev

@property bill_due_date type=date_select table=aw_crm_bill field=aw_due_date
@caption Makset&auml;htaeg

@property customer type=select table=aw_crm_bill field=aw_customer
@caption Klient

@property state type=select table=aw_crm_bill field=aw_state
@caption Staatus

@property notes type=textarea rows=5 cols=50 table=aw_crm_bill field=aw_notes
@caption M&auml;rkused

@property bill_rows type=table store=no 
@caption Arveread 

@reltype TASK value=1 class_id=CL_TASK
@caption &uuml;lesanne
*/

class crm_bill extends class_base
{
	function crm_bill()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_bill",
			"clid" => CL_CRM_BILL
		));

		$this->states = array(
			0 => t("Koostamisel"),
			1 => t("Saadetud"),
			2 => t("Makstud")
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "state":
				$prop["options"] = $this->states;
				break;

			case "bill_rows":
				$this->_bill_rows($arr);
				break;

			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$cust = $i->get_my_customers();
				if (count($cust))
				{
					$ol = new object_list(array("oid" => $cust));
					$prop["options"] = $ol->names();
					if (is_oid($prop["value"]) && $this->can("view", $prop["value"]) && !isset($prop["options"][$prop["value"]]))
					{
						$tmp = obj($prop["value"]);
						$prop["options"][$prop["value"]] = $tmp->name();
					}
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
			case "bill_rows":
				$arr["obj_inst"]->set_meta("bill_inf", $arr["request"]["rows"]);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _init_bill_rows_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimetus"),
		));

		$t->define_field(array(
			"name" => "unit",
			"caption" => t("&Uuml;hik"),
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
		));

		$t->define_field(array(
			"name" => "amt",
			"caption" => t("Kogus"),
		));

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
		));

		$t->define_field(array(
			"name" => "has_tax",
			"caption" => t("Lisandub k&auml;ibemaks?"),
		));
	}

	function _bill_rows($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bill_rows_t($t);

		$inf = safe_array($arr["obj_inst"]->meta("bill_inf"));

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_TASK")) as $c)
		{
			$task = $c->to();
			$id = $task->id();

			if (!isset($inf[$id]))
			{
				$inf[$id] = array(
					"name" => $task->name(),
					"unit" => t("tund"),
					"price" => $task->prop("hr_price"),
					"amt" => $task->prop("num_hrs_to_cust"),
					"sum" => $task->prop("num_hrs_to_cust") * $task->prop("hr_price")
				);
			}

			$t_inf = $inf[$id];
			$t->define_data(array(
				"name" => html::textbox(array(
					"name" => "rows[$id][name]",
					"value" => $t_inf["name"]
				)),
				"unit" => html::textbox(array(
					"name" => "rows[$id][unit]",
					"value" => $t_inf["unit"],
					"size" => 10
				)),
				"price" => html::textbox(array(
					"name" => "rows[$id][price]",
					"value" => $t_inf["price"],
					"size" => 5
				)),
				"amt" => html::textbox(array(
					"name" => "rows[$id][amt]",
					"value" => $t_inf["amt"],
					"size" => 5
				)),
				"sum" => html::textbox(array(
					"name" => "rows[$id][sum]",
					"value" => $t_inf["sum"],
					"size" => 5
				)),
				"has_tax" => html::checkbox(array(
					"name" => "rows[$id][has_tax]",
					"ch_value" => 1,
					"checked" => $t_inf["has_tax"] == 1 ? true : false
				))
			));

			// also, add all other exp rows from task
			foreach(safe_array($task->meta("other_expenses")) as $idx => $entry)
			{
				$id = $task->id()."_".$idx;
				if (!isset($inf[$id]))
				{
					$t_inf = array(
						"name" => $entry["exp"],
						"price" => $entry["cost"]
					);
				}
				else
				{
					$t_inf = $inf[$id];
				}

				$t->define_data(array(
					"name" => html::textbox(array(
						"name" => "rows[$id][name]",
						"value" => $t_inf["name"]
					)),
					"unit" => html::textbox(array(
						"name" => "rows[$id][unit]",
						"value" => $t_inf["unit"],
						"size" => 10
					)),
					"price" => html::textbox(array(
						"name" => "rows[$id][price]",
						"value" => $t_inf["price"],
						"size" => 5
					)),
					"amt" => html::textbox(array(
						"name" => "rows[$id][amt]",
						"value" => $t_inf["amt"],
						"size" => 5
					)),
					"sum" => html::textbox(array(
						"name" => "rows[$id][sum]",
						"value" => $t_inf["sum"],
						"size" => 5
					)),
					"has_tax" => html::checkbox(array(
						"name" => "rows[$id][has_tax]",
						"ch_value" => 1,
						"checked" => $t_inf["has_tax"] == 1 ? true : false
					))
				));
			}
		}
		$t->set_sortable(false);
	}
}
?>