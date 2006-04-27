<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/task_quick_entry.aw,v 1.1 2006/04/27 11:05:33 kristo Exp $
// task_quick_entry.aw - Kiire toimetuse lisamine 
/*

@classinfo syslog_type=ST_TASK_QUICK_ENTRY no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property customer type=textbox store=no
@caption Klient

@property project type=textbox store=no
@caption Projekt

@property task type=textbox store=no
@caption Toimetus

@property date type=date_select store=no
@caption Aeg

@property duration type=textbox store=no size=5
@caption Kestvus

@property content type=textarea store=no rows=10 cols=50
@caption Sisu


*/

class task_quick_entry extends class_base
{
	function task_quick_entry()
	{
		$this->init(array(
			"tpldir" => "applications/groupware/task_quick_entry",
			"clid" => CL_TASK_QUICK_ENTRY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "name":
				return PROP_IGNORE;

			case "customer":
				$prop["autocomplete_source"] = $this->mk_my_orb("cust_autocomplete_source");
				$prop["autocomplete_params"] = array("customer");
				break;

			case "project":
				$prop["autocomplete_source"] = $this->mk_my_orb("proj_autocomplete_source");
				$prop["autocomplete_params"] = array("customer", "project");
				break;

			case "task":
				$prop["autocomplete_source"] = $this->mk_my_orb("task_autocomplete_source");
				$prop["autocomplete_params"] = array("customer", "project", "task");
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

	/**
		@attrib name=cust_autocomplete_source
		@param customer optional
	**/
	function cust_autocomplete_source($arr)
	{
		header ("Content-Type: text/html; charset=" . aw_global_get("charset"));
		$cl_json = get_instance("protocols/data/json");

		if ($arr["customer"] == "")
		{
			die();
		}

		$errorstring = "";
		$error = false;
		$autocomplete_options = array();

		$option_data = array(
			"error" => &$error,// recommended
			"errorstring" => &$errorstring,// optional
			"options" => &$autocomplete_options,// required
			"limited" => false,// whether option count limiting applied or not. applicable only for real time autocomplete.
		);

		$ol = new object_list(array(
			"class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON),
			"name" => $arr["customer"]."%",
			"lang_id" => array(),
			"site_id" => array()
		));
		$autocomplete_options = $ol->names();
		exit ($cl_json->encode($option_data));
	}

	/**
		@attrib name=proj_autocomplete_source
		@param customer optional
		@param project optional
	**/
	function proj_autocomplete_source($arr)
	{
		header ("Content-Type: text/html; charset=" . aw_global_get("charset"));
		$cl_json = get_instance("protocols/data/json");

		$errorstring = "";
		$error = false;
		$autocomplete_options = array();

		$option_data = array(
			"error" => &$error,// recommended
			"errorstring" => &$errorstring,// optional
			"options" => &$autocomplete_options,// required
			"limited" => false,// whether option count limiting applied or not. applicable only for real time autocomplete.
		);

		$ol = new object_list(array(
			"class_id" => array(CL_PROJECT),
			"name" => $arr["project"]."%",
			"CL_PROJECT.RELTYPE_ORDERER.name" => $arr["customer"]."%",
			"lang_id" => array(),
			"site_id" => array()
		));
		$autocomplete_options = $ol->names();
		exit ($cl_json->encode($option_data));
	}

	/**
		@attrib name=task_autocomplete_source
		@param customer optional
		@param project optional
		@param task optional
	**/
	function task_autocomplete_source($arr)
	{
		header ("Content-Type: text/html; charset=" . aw_global_get("charset"));
		$cl_json = get_instance("protocols/data/json");

		$errorstring = "";
		$error = false;
		$autocomplete_options = array();

		$option_data = array(
			"error" => &$error,// recommended
			"errorstring" => &$errorstring,// optional
			"options" => &$autocomplete_options,// required
			"limited" => false,// whether option count limiting applied or not. applicable only for real time autocomplete.
		);

		$ol = new object_list(array(
			"class_id" => array(CL_TASK),
			"CL_TASK.project.name" => $arr["project"]."%",
			"CL_TASK.customer.name" => $arr["customer"]."%",
			"name" => $arr["task"]."%",
			"lang_id" => array(),
			"site_id" => array()
		));
		$autocomplete_options = $ol->names();
		exit ($cl_json->encode($option_data));
	}

	function callback_pre_save($arr)
	{
		// find the task referenced and add row to it.
		// if needed add customer/project/task

		$cur_co = get_current_company();
		$cur_p = get_current_person();

		$ol = new object_list(array(
			"class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON),
			"name" => $arr["request"]["customer"],
			"lang_id" => array(),
			"site_id" => array()
		));
		if (!$ol->count())
		{
			$c = obj();
			$c->set_class_id(CL_CRM_COMPANY);
			$c->set_parent($cur_co->parent());
			$c->set_name($arr["request"]["customer"]);
			$c->save();
			$cur_co->connect(array(
				"type" => "RELTYPE_CUSTOMER",
				"to" => $c->id()
			));
		}
		else
		{
			$c = $ol->begin();
		}

		// if project exists
		$ol = new object_list(array(
			"class_id" => array(CL_PROJECT),
			"name" => $arr["request"]["project"],
			"CL_PROJECT.RELTYPE_ORDERER.name" => $arr["request"]["customer"]."%",
			"lang_id" => array(),
			"site_id" => array()
		));
		if (!$ol->count())
		{
			$p = obj();
			$p->set_class_id(CL_PROJECT);
			$p->set_parent($cur_co->parent());
			$p->set_name($arr["request"]["project"]);
			$p->set_prop("orderer", array($c->id(), $c->id()));
			$p->save();
		}
		else
		{
			$p = $ol->begin();
		}

		// if task exists
		$ol = new object_list(array(
			"class_id" => array(CL_TASK),
			"name" => $arr["request"]["task"],
			"CL_TASK.project.name" => $arr["request"]["project"]."%",
			"CL_TASK.customer.name" => $arr["request"]["customer"]."%",
			"lang_id" => array(),
			"site_id" => array()
		));
		if (!$ol->count())
		{
			// set stuff as task props
			$t = obj();
			$t->set_class_id(CL_TASK);
			$t->set_parent($cur_co->parent());
			$t->set_name($arr["request"]["task"]);
			$t->set_prop("customer", $c->id());
			$t->set_prop("project", $p->id());
			$t->set_prop("start1", date_edit::get_timestamp($arr["request"]["date"]));
			$t->set_prop("end", date_edit::get_timestamp($arr["request"]["date"]) + $arr["request"]["duration"]*3600);
			$t->set_prop("content", $arr["request"]["content"]);
			$t->save();

			$t_i = $t->instance();
			$t_i->add_participant($t, $cur_p);

			header("Location: ".html::get_change_url($t->id(), array("return_url" => $arr["request"]["post_ru"])));
			die();
		}
		else
		{
			$t = $ol->begin();
			// add row to task

			$r = obj();
			$r->set_class_id(CL_TASK_ROW);
			$r->set_parent($t->id());
			$r->set_prop("content", $arr["request"]["content"]);
			$r->set_prop("date", date_edit::get_timestamp($arr["request"]["date"]));
			$r->set_prop("time_guess", $arr["request"]["duration"]);
			$r->set_prop("impl", $cur_p->id());
			$r->save();

			$t->connect(array(
				"to" => $r->id(),
				"type" => "RELTYPE_ROW"
			));
			header("Location: ".html::get_change_url($t->id(), array("group" => "rows", "return_url" => $arr["request"]["post_ru"])));
			die();
		}
	}

	function callback_generate_scripts($arr)
	{
		return
		"function aw_submit_handler() {".
		// fetch list of companies with that name and ask user if count > 0
		"var url = '".$this->mk_my_orb("check_existing")."';".
		"url = url + '&c=' + document.changeform.customer.value;".
		"url = url + '&p=' + document.changeform.project.value;".
		"url = url + '&t=' + document.changeform.task.value;".
		"num= aw_get_url_contents(url);".
		"if (num != \"\")
		{
			var ansa = confirm(num);
			if (ansa)
			{
				return true;
			}
			return false;
		}".
		"return true;}";
	}

	/**
		@attrib name=check_existing
		@param c optional
		@param p optional
		@param t optional
	**/
	function check_existing($arr)
	{
		$ret = "";
		// if customer exists
		$ol = new object_list(array(
			"class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON),
			"name" => $arr["c"],
			"lang_id" => array(),
			"site_id" => array()
		));
		if (!$ol->count())
		{
			$ret .= sprintf(t("Klienti nimega %s ei ole olemas, kui vajutate ok, lisatakse\n"), $arr["c"]);
		}

		// if project exists
		$ol = new object_list(array(
			"class_id" => array(CL_PROJECT),
			"name" => $arr["p"],
			"lang_id" => array(),
			"site_id" => array()
		));
		if (!$ol->count())
		{
			$ret .= sprintf(t("Projekti nimega %s ei ole olemas, kui vajutate ok, lisatakse\n"), $arr["p"]);
		}

		// if task exists
		$ol = new object_list(array(
			"class_id" => array(CL_TASK),
			"name" => $arr["t"],
			"lang_id" => array(),
			"site_id" => array()
		));
		if (!$ol->count())
		{
			$ret .= sprintf(t("Toimetust nimega %s ei ole olemas, kui vajutate ok, lisatakse\n"), $arr["t"]);
		}

		die($ret);
	}
}
?>
