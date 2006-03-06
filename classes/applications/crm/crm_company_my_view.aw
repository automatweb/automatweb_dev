<?php

class crm_company_my_view extends class_base
{
	function crm_company_my_view()
	{
		$this->init("crm");
	}

	function _get_my_view($arr)
	{
		$this->read_template("my_view.tpl");

		/*
			Teha Avalehe vaade, kus on näha tänased ja homsed sündmused, 
			mulle lisatud failid, foorumi viimased teemad, 
		*/

		classload("vcl/table");
		classload("core/date/date_calc");
		classload("core/icons");

		$this->vars(array(
			"events" => $this->_events($arr),
			"files" => $this->_files($arr),
			"forums" => $this->_forums($arr)
		));

		return $this->parse();
	}

	function _init_events_t(&$t)
	{
		$t->define_field(array(
			"name" => "icon",	
			"caption" => t(""),
			"align" => "center",
			"width" => 1,
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "name",	
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "comment",	
			"caption" => t("Kommentaar"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "when",	
			"caption" => t("Aeg"),
			"align" => "center",
			"sortable" => 1,
			"callb_pass_row" => 1,
			"callback" => array(&$this, "_format_when")
		));

		$t->define_field(array(
			"name" => "cust",	
			"caption" => t("Klient"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "proj",	
			"caption" => t("Projekt"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "parts",	
			"caption" => t("Osalejad"),
			"align" => "center",
			"sortable" => 1
		));
	}

	function _events($arr)
	{
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());

		$t = new vcl_table();
		$this->_init_events_t($t);

		// get all events where I am participant and that are today or tomorro
		// all events that have me as participant, will be in my calendar
		// so fetch from there
		$ci = get_instance(CL_PLANNER);
		$my_cal = $ci->get_calendar_for_user();
		if (!$my_cal)
		{
			return;
		}

		$pm = get_instance("applications/calendar/planner_model");
		$evts = $pm->get_event_list(array(
			"id" => $my_cal,
			"start" => get_day_start(),
			"end" => get_day_start(time() + 24*3600*2)
		));

		foreach($evts as $ev_inf)
		{
			$evt = obj($ev_inf["id"]);

			$parts = new object_list($evt->connections_to(array(
				"from.class_id" => CL_USER
			)));

			$t->define_data(array(
				"icon" => icons::get_icon($evt),
				"name" => html::obj_change_url($evt),
				"comment" => $evt->comment(),
				"from" => $ev_inf["start"],
				"to" => $ev_inf["end"],
				"parts" => html::obj_change_url($parts->ids()),
				"cust" => html::obj_change_url($evt->prop("customer")),
				"proj" => html::obj_change_url($evt->prop("project"))
			));
		}

		$t->set_default_sortby("from");
		$t->sort_by();

		return $t->draw();
	}

	function _format_when($row)
	{
		return date("d.m.Y H:i", $row["from"]).($row["to"] > 100 ? " - ".date("d.m.Y H:i", $row["to"]) : "");
	}

	function _files($arr)
	{
		$u = get_instance(CL_USER);
		$co = obj($u->get_current_company());

		$t = new vcl_table();

		$p = array(
			"obj_inst" => $co,
			"request" => array(
				"group" => "ovrv_offers"
			),
			"prop" => array(
				"vcl_inst" => &$t
			)
		);

		$i = get_instance("applications/crm/crm_company_overview_impl");
		$i->_get_my_tasks($p);

		return $t->draw();
	}

	function _forums($arr)
	{
		// get forum from co and last topics from that
		$u = get_instance(CL_USER);
		$co = obj($u->get_current_company());

		$fo = $co->get_first_obj_by_reltype("RELTYPE_FORUM");
		if (!$fo)
		{
			return;
		}		

		$f = $fo->instance();
		

		$folders = new object_tree(array(
			"class_id" => CL_MENU,
			"status" => STAT_ACTIVE,
			"parent" => $fo->prop("topic_folder")
		));

		list($t_counts, $t_list) = $f->get_topic_list(array("parents" => $folders->ids() + array($fo->prop("topic_folder"))));

		$pts = array();
		foreach($t_list as  $pt => $topics)
		{
			foreach($topics as $topic)
			{
				$pts[] = $topic;
			}
		}

		list($comm_c, $tot) = $f->get_comment_counts(array("parents" => $pts));

		$t = new vcl_table();
		$this->_init_topic_t($t);
		foreach($t_list as  $pt => $topics)
		{
			foreach($topics as $topic)
			{
				$l_c = $f->get_last_comments(array("parents" => array($topic)));

				$to = obj($topic);
				$url = $this->mk_my_orb("change", array(
					"id" => $topic,
					"group" => "contents",
					"topic" => $topic,
					"return_url" => get_ru()
				), CL_FORUM_V2);
				$t->define_data(array(
					"name" => html::href(array(
						"url" => $url,
						"caption" => $to->name()
					)),
					"num" => $comm_c[$topic],
					"last" => $l_c["created"]
				));
			}
		}

		return $t->draw();
	}

	function _init_topic_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Teema"),
			"align" => "left"
		));

		$t->define_field(array(
			"name" => "num",
			"caption" => t("Kommentaare"),
			"align" => "left"
		));

		$t->define_field(array(
			"name" => "last",
			"caption" => t("Viimane kommentaar"),
			"align" => "left",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
	}
}

?>