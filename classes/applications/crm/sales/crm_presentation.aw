<?php
/*
@classinfo syslog_type=ST_CRM_PRESENTATION relationmgr=yes no_status=1 prop_cb=1 maintainer=voldemar

@tableinfo planner index=id master_table=objects master_index=brother_of
@groupinfo customer caption="Klient"

@default table=planner
@default group=general
	@property presentation_tools type=toolbar no_caption=1
	@caption Esitluse toimingud

	@property name type=textbox table=objects field=name
	@caption Nimi

	@property comment type=textbox table=objects field=comment
	@caption Kommentaar

	@property result type=select
	@caption Tulemus

	@property start1 field=start type=datepicker
	@caption Algus

	@property end type=datepicker
	@caption L&otilde;pp

	@property real_start type=datepicker
	@comment Kui tegelik algus sisestada, arvatakse presentatsioon toimunuks.
	@caption Tegelik algus

	@property real_duration type=textbox datatype=int
	@comment Kui tegelik kestus sisestada, arvatakse presentatsioon toimunuks.
	@caption Tegelik kestus (h)

	@property customer_relation type=hidden datatype=int
	@property hr_schedule_job type=hidden datatype=int

@layout impl_bit type=vbox closeable=1 area_caption=Osalejad
	@property impl_tb type=toolbar no_caption=1 store=no parent=impl_bit
	@property parts_table type=table no_caption=1 store=no parent=impl_bit


@default group=customer
	@property customer_info type=text store=no no_caption=1


////////////// RELTYPES /////////////

@reltype ROW value=7 clid=CL_TASK_ROW
@caption Rida


*/

class crm_presentation extends task
{
	function crm_presentation()
	{
		$this->init(array(
			"tpldir" => "groupware/task",
			"clid" => CL_CRM_PRESENTATION
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _get_presentation_tools(&$arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$this_o = $arr["obj_inst"];

		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"action" => "submit",
			"img" => "save.gif"
		));

		return PROP_OK;
	}

	function _get_real_duration(&$arr)
	{
		$arr["prop"]["value"] = number_format($arr["prop"]["value"]/60, 2, ".", " ");
		return PROP_OK;
	}

	function _get_customer_info(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$cro = new crm_company_customer_data();
		$cro->form_only = true;
		$arr["prop"]["value"] = $cro->view(array(
			"id" => $this_o->prop("customer_relation"),
			"group" => "sales_data"
		));
	}

	function _get_parts_table(&$arr)
	{
		return $this->_parts_table($arr);
	}

	function _set_parts_table(&$arr)
	{
		return $this->save_parts_table($arr);
	}

	function _get_start1(&$arr)
	{
		$return = PROP_OK;
		// in sales application, a salesman can't change planned start/end
		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{
			$role = $application->get_current_user_role();
			if (crm_sales_obj::ROLE_SALESMAN === $role)
			{
				$arr["prop"]["disabled"] = 1;
			}
		}
		return $return;
	}

	function _get_end(&$arr)
	{
		$return = PROP_OK;
		// in sales application, a salesman can't change planned start/end
		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{
			$role = $application->get_current_user_role();
			if (crm_sales_obj::ROLE_SALESMAN === $role)
			{
				$arr["prop"]["disabled"] = 1;
			}
		}
		return $return;
	}

	function _set_end(&$arr)
	{
		$return = PROP_OK;
		// in sales application, a salesman can't change planned start/end
		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{
			$role = $application->get_current_user_role();
			if (crm_sales_obj::ROLE_SALESMAN === $role)
			{
				$return = PROP_IGNORE;
			}
		}
		return $return;
	}

	function _set_comment(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$val = $arr["prop"]["value"];
		if (strlen($val) > 1 and $val !== $this_o->comment() and $this_o->prop("customer_relation"))
		{
			$comm = new forum_comment();
			$commdata = $this_o->name() . ":\n" . $val;
			if (strlen($commdata["comment"]))
			{
				$comm->submit(array(
					"parent" => $this_o->prop("customer_relation"),
					"commtext" => $commdata,
					"return" => "id"
				));
			}
		}
		return PROP_OK;
	}

/* SET DONE
	if either result, real_start or real_duration is set, both must be set. Presentation will then be considered done
*/
	function _set_real_duration(&$arr)
	{
		$real_start = isset($arr["request"]["real_start"]) ? date_edit::get_timestamp($arr["request"]["real_start"]) : 0;
		if ($arr["prop"]["value"] < 1 and ($real_start > 1 or !empty($arr["request"]["result"])))
		{
			$arr["prop"]["error"] = t("Kui esitlus on tehtud, peab sisestama ka kestuse");
			$return = PROP_FATAL_ERROR;
		}
		else
		{
			$arr["prop"]["value"] = ceil($arr["prop"]["value"]*60);
			$return = PROP_OK;
		}
		return $return;
	}

	function _set_real_start(&$arr)
	{
		$return = PROP_OK;
		$real_start = date_edit::get_timestamp($arr["prop"]["value"]);
		if ($real_start < 2 and ($arr["request"]["real_duration"] > 1 or !empty($arr["request"]["result"])))
		{
			$arr["prop"]["error"] = t("Kui esitlus on tehtud, peab sisestama ka tegeliku algusaja");
			$return = PROP_FATAL_ERROR;
		}
		return $return;
	}

	function _set_start1(&$arr)
	{
		$return = PROP_OK;

		// in sales application, a salesman can't change planned start/end
		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{
			$role = $application->get_current_user_role();
			if (crm_sales_obj::ROLE_SALESMAN === $role)
			{
				return PROP_IGNORE;
			}
		}

		// check if planned start for an unstarted presentation is in the future
		$start = datepicker::get_timestamp($arr["prop"]["value"]);
		if ($arr["obj_inst"]->prop("real_start") < 2 and $start < time())
		{
			$arr["prop"]["error"] = t("Esitluse algusaeg ei saa olla minevikus");
			$return = PROP_FATAL_ERROR;
		}

		return $return;
	}

	function _get_result(&$arr)
	{
		$return = PROP_OK;
		$arr["prop"]["options"] = array("" => "") + $arr["obj_inst"]->result_names();
		return $return;
	}

	function _set_result(&$arr)
	{
		$real_start = isset($arr["request"]["real_start"]) ? date_edit::get_timestamp($arr["request"]["real_start"]) : 0;
		if (empty($arr["prop"]["value"]) and ($real_start > 1 or !empty($arr["request"]["result"])))
		{
			$arr["prop"]["error"] = t("Kui esitlus on tehtud, peab valima ka tulemuse");
			$return = PROP_FATAL_ERROR;
		}
		else
		{
			$return = PROP_OK;
		}
		return $return;
	}
/* END SET DONE */

	function submit($arr = array())
	{
		$r = parent::submit($arr);
		if ("submit" === $arr["action"] and $this->data_processed_successfully())
		{//!!! workflow definitsioon, t6sta wf objekti kui selline asi valmis saab
			$this_o = new object($arr["id"]);
			$application = automatweb::$request->get_application();
			$role = $application->get_current_user_role();
			if ($application->is_a(CL_CRM_SALES) and $this_o->prop("real_duration") < 1 and crm_sales_obj::ROLE_TELEMARKETING_SALESMAN === $role)
			{ // return to calls list
				$url = new aw_uri($arr["return_url"]);
				$r = $url->arg("return_url");
			}
		}
		return $r;
	}

	function do_db_upgrade($tbl, $field, $q, $err)
	{
		if ("planner" === $tbl)
		{
			$i = get_instance(CL_TASK);
			return $i->do_db_upgrade($tbl, $field);
		}
	}
}

?>
