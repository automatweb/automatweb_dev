<?php

class crm_company_qv_impl extends class_base
{
	function crm_company_qv_impl()
	{
		$this->init();
	}

	function _init_qv_t(&$t)
	{
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimetus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "parts",
			"caption" => t("Osalejad"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "state",
			"caption" => t("Staatus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "hrs",
			"caption" => t("Tunde"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center"
		));
	}

	function _get_qv_t($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_qv_t($t);

		// projs
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
			"orderer" => $arr["obj_inst"]->id(),
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "aw_deadline desc",
			"state" => "%",
			"limit" => 5
		));
		$pi = get_instance(CL_PROJECT);
		foreach($ol->arr() as $o)
		{
			$parts = array();
			foreach((array)$o->prop("participants") as $_p)
			{
				$parts[] = html::obj_change_url($_p);
			}
			$sum = 0;
			$hrs = 0;
			// get all tasks for that project and calc sum and hrs
			$t_ol = new object_list(array(
				"class_id" => CL_TASK,
				"lang_id" => array(),
				"site_id" => array(),
				"project" => $o->id()
			));
			foreach($t_ol->arr() as $task)
			{
				$sum += str_replace(",", ".",$task->prop("num_hrs_to_cust")) * str_replace(",", ".",$task->prop("hr_price"));
				$hrs += str_replace(",", ".",$task->prop("num_hrs_to_cust"));
			}
			$t->define_data(array(
				"date" => date("d.m.Y", $o->prop("start"))." - ".date("d.m.Y", $o->prop("end")),
				"name" => html::obj_change_url($o),
				"parts" => join(", ", $parts),
				"hrs" => number_format($hrs, 2),
				"sum" => number_format($sum, 2),
				"grp_desc" => t("<b>Projektid (5 värskemat)</b>"),
				"grp_num" => 1,
				"state" => $pi->states[$o->prop("state")]
			));
		}
		

		// tasks
		$ol = new object_list(array(
			"class_id" => CL_TASK,
			"customer" => $arr["obj_inst"]->id(),
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "deadline desc",
			"deadline" => "%",
			"limit" => 10
		));
		foreach($ol->arr() as $o)
		{
			$parts = array();
			foreach((array)$o->prop("participants") as $_p)
			{
				$parts[] = html::obj_change_url($_p);
			}
			$sum = 0;
			$hrs = 0;
			$sum += str_replace(",", ".",$o->prop("num_hrs_to_cust")) * str_replace(",", ".",$o->prop("hr_price"));
			$hrs += str_replace(",", ".",$o->prop("num_hrs_to_cust"));
			$t->define_data(array(
				"date" => date("d.m.Y", $o->prop("start1"))." - ".date("d.m.Y", $o->prop("end")),
				"name" => html::obj_change_url($o),
				"parts" => join(", ", $parts),
				"hrs" => number_format($hrs, 2),
				"sum" => number_format($sum, 2),
				"grp_desc" => t("<b>Tegevused (10 värskemat)</b>"),
				"grp_num" => 2,
				"state" => $o->prop("is_done") == 1 ? t("Tehtud") : t("T&ouml;&ouml;s")
			));
		}

		// bills
		$ol = new object_list(array(
			"class_id" => CL_CRM_BILL,
			"customer" => $arr["obj_inst"]->id(),
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "aw_due_date desc",
			"bill_no" => "%",
			"limit" => 10
		));
		foreach($ol->arr() as $o)
		{
			$parts = array();
			foreach((array)$o->prop("participants") as $_p)
			{
				$parts[] = html::obj_change_url($_p);
			}
			$bi = $o->instance();
			$sum = $bi->get_sum($o);
			$rows = $bi->get_bill_rows($o);
			$hrs = 0;
			foreach($rows as $row)
			{
				$hrs += str_replace(",", ".",$row["amt"]);
			}
			$t->define_data(array(
				"date" => date("d.m.Y", $o->prop("bill_date")),
				"name" => html::obj_change_url($o),
				"parts" => "",
				"hrs" => number_format($hrs, 2),
				"sum" => number_format($sum, 2),
				"grp_desc" => t("<span style='font-size: 0px;'>y</span><b>Arved (10 värskemat)</b>"),
				"grp_num" => 3,
				"state" => $bi->states[$o->prop("state")]
			));
		}
		
		$t->sort_by(array(
			"rgroupby" => array("grp_num" => "grp_desc"),
			"sorder" => "asc",
			"field" => "grp_num"
		));
		$t->set_sortable(false);
	}
}

?>