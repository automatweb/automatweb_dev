<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/customer_satisfaction_center/customer_feedback_manager.aw,v 1.3 2006/09/05 09:40:13 kristo Exp $
// customer_feedback_manager.aw - Kliendi tagasiside 
/*

@classinfo syslog_type=ST_CUSTOMER_FEEDBACK_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general


@property fb_tb type=toolbar store=no no_caption=1 group=unsolved,solved
@property fb_t type=table store=no no_caption=1 group=unsolved,solved



@property contact_text type=text store=no no_caption=1 group=contact






@groupinfo fb caption="Tagasiside"
	@groupinfo unsolved caption="Lahendamisel" parent=fb submit=no
	@groupinfo solved caption="Lahendatud" parent=fb submit=no

@groupinfo contact caption="Kontaktid" submit=no

*/

class customer_feedback_manager extends class_base
{
	function customer_feedback_manager()
	{
		$this->init(array(
			"tpldir" => "applications/customer_satisfaction_center/customer_feedback_manager",
			"clid" => CL_CUSTOMER_FEEDBACK_MANAGER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "contact_text":
				$prop["value"] = t("Struktuur Meedia<br>Automatweb<br><br>P&auml;rnu maantee 145, Tallinn, Estonia<br><a href='http://www.struktuur.ee'>http://www.struktuur.ee</a><Br><a href='mailto:support@automatweb.com'>support@automatweb.com</a>");
				break;

			case "fb_tb":
				return PROP_IGNORE;
				$this->_fb_tb($arr);
				break;

			case "fb_t":
				$this->_fb_t($arr);
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

	function init_manager()
	{
		// find default center
		$ol = new object_list(array(
			"class_id" => CL_CUSTOMER_FEEDBACK_MANAGER,
			"lang_id" => array(),
			"site_id" => array()
		));
		if ($ol->count() > 0)
		{
			return $ol->begin();
		}
		$o = obj();
		$o->set_class_id(CL_CUSTOMER_FEEDBACK_MANAGER);
		$o->set_parent(aw_ini_get("amenustart"));
		$o->set_name("Kliendi tagasiside");
		$o->save();
		return $o;
	}

	function _fb_tb($arr)
	{
	}

	function _init_fb_t(&$t)
	{
		$t->define_field(array(
			"name" => "person",
			"caption" => t("Isik"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "co",
			"caption" => t("Organisatsioon"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "site",
			"caption" => t("Sait"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "object",
			"caption" => t("Objekt"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "severity",
			"caption" => t("T&otilde;sidus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "dev_status",
			"caption" => t("Arendajapoolne staatus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "solve_date",
			"caption" => t("Lahendamise aeg"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i",
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
			"align" => "center",
		));
		if ($_SESSION["authenticated_as_customer_care_personnell"])
		{
			$t->define_field(array(
				"name" => "change",
				"caption" => t("Muuda"),
				"align" => "center",
			));
		}
	}

	function _fb_t($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_fb_t($t);

		if ($arr["request"]["group"] == "unsolved")
		{
			$ol = new object_list(array(
				"class_id" => CL_CUSTOMER_FEEDBACK_ENTRY,
				"lang_id" => array(),
				"site_id" => array(),	
				"dev_status" => array(1,2)
			));
		}
		else
		{
			$ol = new object_list(array(
				"class_id" => CL_CUSTOMER_FEEDBACK_ENTRY,
				"lang_id" => array(),
				"site_id" => array(),
				"dev_status" => new obj_predicate_not(array(1,2))
			));
		}

		$e  = get_instance(CL_CUSTOMER_FEEDBACK_ENTRY);
		$clss = aw_ini_get("classes");
		$sl = get_instance("install/site_list");
		foreach($ol->arr() as $o)
		{
			$p = $o->get_first_obj_by_reltype("RELTYPE_PERSON");
			$co = $o->get_first_obj_by_reltype("RELTYPE_CO");
			$ob = $o->get_first_obj_by_reltype("RELTYPE_OBJECT");
	
			$t->define_data(array(
				"person" => html::obj_change_url($p),
				"co" => html::obj_change_url($co),
				"class" => $clss[$ob->class_id()]["name"],
				"object" => html::obj_change_url($ob),
				"severity" => $e->severities[$o->prop("seriousness")],
				"dev_status" => $e->statuses[$o->prop("dev_status")],
				"solve_date" => $o->prop("dev_deadline"),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("view", array("id" => $o->id(), "return_url" => get_ru()), $o->class_id()),
					"caption" => t("Vaata")
				)),
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id(), "return_url" => get_ru()), $o->class_id()),
					"caption" => t("Muuda")
				)),
				"site" => $sl->get_url_for_site($o->site_id())
			));
		}
	}

	/**
		@attrib name=redir_m
		@param url optional
	**/
	function redir_m($arr)
	{
		$m = $this->init_manager();
		return html::get_change_url($m->id(), array("group" => "fb", "return_url" => $arr["url"]));
	}

}
?>
