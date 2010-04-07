<?php
/*
@classinfo syslog_type=ST_CRM_PRESENTATION relationmgr=yes no_status=1 prop_cb=1 maintainer=voldemar

@tableinfo planner index=id master_table=objects master_index=brother_of

@default table=planner
@default group=general
	@property name type=textbox table=objects field=name
	@caption Nimi

	@property comment type=textbox table=objects field=comment
	@caption Kommentaar

	@property result type=select
	@caption Tulemus

	@property start type=datetime_select
	@caption Algus

	@property end type=datetime_select
	@caption L&otilde;pp

	@property real_start type=datetime_select
	@comment Kui tegelik algus sisestada, arvatakse presentatsioon toimunuks.
	@caption Tegelik algus

	@property real_duration type=textbox datatype=int
	@comment Kui tegelik kestus sisestada, arvatakse presentatsioon toimunuks.
	@caption Tegelik kestus (h)

	@property customer_relation type=relpicker reltype=RELTYPE_CUSTOMER_RELATION
	@caption Kliendisuhe

	@property sales_schedule_job type=hidden datatype=int

@layout impl_bit type=vbox closeable=1 area_caption=Osalejad
	@property impl_tb type=toolbar no_caption=1 store=no parent=impl_bit
	@property parts_table type=table no_caption=1 store=no parent=impl_bit



@default table=objects
@default field=meta
@default method=serialize
	@property comment_list type=comments form_before_comments=1
	@caption Lisakommentaarid


////////////// RELTYPES /////////////

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

@reltype CUSTOMER_RELATION value=6 clid=CL_CRM_COMPANY_CUSTOMER_DATA
@caption Kliendisuhe

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype PROJECT value=4 clid=CL_PROJECT
@caption Projekt

@reltype PREDICATE value=9 clid=CL_TASK,CL_CRM_CALL,CL_CRM_MEETING
@caption Eeldustegevus

@reltype FILE value=2 clid=CL_FILE
@caption Fail

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

	function _get_real_duration(&$arr)
	{
		$arr["prop"]["value"] = number_format($arr["prop"]["value"]/60, 2, ".", " ");
		return PROP_OK;
	}

	function _get_parts_table(&$arr)
	{
		return $this->_parts_table($arr);
	}

	function _set_parts_table(&$arr)
	{
		return $this->save_parts_table($arr);
	}

/* SET DONE
	if either result, real_start or real_duration is set, both must be set. Presentation will then be considered done
*/
	function _set_real_duration(&$arr)
	{
		$real_start = isset($arr["request"]["real_start"]) ? date_edit::get_timestamp($arr["request"]["real_start"]) : 0;
		if ($arr["prop"]["value"] < 1 and ($real_start > 1 or !empty($arr["request"]["result"])))
		{
			$arr["prop"]["error"] = t("Kui presentatsioon on tehtud, peab sisestama ka kestuse");
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
		$real_start = date_edit::get_timestamp($arr["prop"]["value"]);
		if ($real_start < 2 and ($arr["request"]["real_duration"] > 1 or !empty($arr["request"]["result"])))
		{
			$arr["prop"]["error"] = t("Kui presentatsioon on tehtud, peab sisestama ka tegeliku algusaja");
			$return = PROP_FATAL_ERROR;
		}
		else
		{
			$arr["prop"]["value"] = ceil($arr["prop"]["value"]*60);
			$return = PROP_OK;
		}
		return $return;
	}

	function _set_result(&$arr)
	{
		$real_start = isset($arr["request"]["real_start"]) ? date_edit::get_timestamp($arr["request"]["real_start"]) : 0;
		if (empty($arr["prop"]["value"]) and ($real_start > 1 or !empty($arr["request"]["result"])))
		{
			$arr["prop"]["error"] = t("Kui presentatsioon on tehtud, peab valima ka tulemuse");
			$return = PROP_FATAL_ERROR;
		}
		else
		{
			$return = PROP_OK;
		}
		return $return;
	}
/* END SET DONE */

	function do_db_upgrade($tbl, $field)
	{
		if ("planner" === $tbl)
		{
			$i = get_instance(CL_TASK);
			return $i->do_db_upgrade($tbl, $field);
		}
	}
}

?>
