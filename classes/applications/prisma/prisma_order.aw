<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/prisma/Attic/prisma_order.aw,v 1.3 2004/06/02 10:36:36 kristo Exp $
// prisma_order.aw - Printali Tr&uuml;kis 
/*

@classinfo syslog_type=ST_PRISMA_ORDER relationmgr=yes no_status=1 no_comment=1

@tableinfo aw_prisma_orders index=id master_table=objects master_index=brother_of

@groupinfo generalinf caption="Esmased andmed" parent=general
@default table=objects
@default group=generalinf
@default table=aw_prisma_orders


@property name type=textbox table=objects field=name 
@caption Nimi

@property amount type=textbox size=5 field=aw_amount
@caption Tr&uuml;kiarv

@property paper_format type=textbox field=aw_paper_format
@caption Formaat

@property priority type=textbox table=objects field=meta method=serialize size=5
@caption Prioriteet

@property action type=text store=no
@caption K&auml;esolev tegevus

@property move_action type=select store=no
@caption Muuda aktiivset tegevust

@groupinfo selres caption="Vali ressursid"

@property sel_resources type=chooser orient=vertical multiple=1 store=connect reltype=RELTYPE_RESOURCE group=selres
@caption Vali ressursid


@groupinfo resources caption="Jaota ressursid"

@property resources type=table group=resources no_caption=1

@property confirm type=checkbox ch_value=1 group=resources store=no
@caption Kinnita ajad

@groupinfo work caption="T&ouml;&ouml;de seis"

@groupinfo work_table caption="Tabel" parent=work
@groupinfo work_cal caption="Kalender" parent=work

@property work_cal type=calendar group=work_cal no_caption=1 store=no
@property work_table  type=table group=work_table no_caption=1 store=no






////////////// other data, ignore for now
@groupinfo data caption="Tr&uuml;kise andmed" parent="general"
@default group=data

@property pages_content type=textbox size=5 field=aw_pages_content
@caption Lehek&uuml;ljed / sisu

@property paper_content type=textbox field=aw_paper_content
@caption Paber / sisu

@property colour_content type=textbox field=aw_colour_content
@caption V&auml;rvid / sisu

@property finish_content type=textbox field=aw_finish_content
@caption Lakk/Muu / sisu

@property build_content type=textbox field=aw_build_content
@caption Tr&uuml;kise ehitus / sisu

@property pages_cover type=textbox size=5 field=aw_pages_cover
@caption Lehek&uuml;ljed / kaas

@property paper_cover type=textbox field=aw_paper_cover
@caption Paber / kaas

@property colour_cover type=textbox field=aw_colour_cover
@caption V&auml;rvid / kaas

@property finish_cover type=textbox field=aw_finish_cover
@caption Lakk/Muu / kaas

@property build_cover type=textbox field=aw_build_cover
@caption Tr&uuml;kise ehitus / kaas


@property kromaliin type=checkbox ch_value=1 field=aw_kromaliin
@caption Kromaliin

@property makett type=checkbox ch_value=1 field=aw_makett
@caption Makett

@property example type=checkbox ch_value=1 field=aw_example
@caption N&auml;idis


@groupinfo montage caption="Montaaz" parent=general 
@defeult group=montage

@property plates type=textbox size=5 field=aw_plates
@caption Plaate

@property actual type=textbox size=5 field=aw_actual
@caption Tegelik

@property m_content type=textarea rows=10 cols=40 field=aw_m_content
@caption SISU

@property c_content type=textarea rows=10 cols=40 field=aw_c_content
@caption KAAS


@groupinfo print caption="Tr&uuml;kk" parent=general
@default group=print


@groupinfo postprod caption="J&auml;relt&ouml;&ouml;tlus" parent=general
@default group=postprod

@property pp_cut type=textbox field=aw_pp_cut
@caption L&otilde;igata

@property pp_trans type=textbox field=aw_pp_trans
@caption Transport

@reltype ORDERER value=1 clid=CL_CRM_COMPANY
@caption tellija

@reltype RESOURCE value=2 clid=CL_WORKFLOW_RESOURCE
@caption ressurrss
*/

class prisma_order extends class_base
{
	function prisma_order()
	{
		$this->init(array(
			"tpldir" => "applications/printal/prisma_order",
			"clid" => CL_PRISMA_ORDER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "sel_resources":
				$prop["options"] = $this->_get_resource_list($arr["obj_inst"]);
				$prop["value"] = $this->_get_sel_resource_list($arr["obj_inst"]);
				break;

			case "resources":
				$this->do_res_tbl($arr);
				break;

			case "action":
				$prop["value"] = $this->_get_cur_action($arr["obj_inst"]);
				break;

			case "move_action":
				$prop["options"] = $this->_get_actions($arr["obj_inst"]);
				break;

			case "work_table":
				$this->do_work_tbl($arr);
				break;

			case "work_cal":
				$this->gen_event_list($arr);
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
			case "sel_resources":
				$this->save_sel_resources($arr);
				break;

			case "resources";
				$this->do_save_resources($arr);
				break;

			case "confirm":
				if ($prop["value"] == 1)
				{
					$this->do_write_times_to_cal($arr);
				}
				break;

			case "priority":
				// write priority to all events from this
				$evids = new aw_array($arr["obj_inst"]->meta("event_ids"));
				foreach($evids->get() as $evid)
				{
					$evo = obj($evid);
					$evo->set_meta("task_priority", $prop["value"]);
					$evo->save();
				}
				break;
		}
		return $retval;
	}	

	////
	// !Optionally this also needs to support date range ..
	function gen_event_list($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		$t->configure(array(
			"overview_func" => array(&$this,"get_overview"),
		));

		$range = $t->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"],
		));

		$start = $range["start"];
		$end = $range["end"];
		classload("icons");

		$this->overview = array();

		$lds = $this->get_events_for_order(array(
			"id" => $arr["obj_inst"]->id(),
		));
		if (sizeof($lds) > 0)
		{
			$ol = new object_list(array(
				"oid" => $lds,
				"sort_by" => "planner.start",
				new object_list_filter(array("non_filter_classes" => CL_CRM_MEETING)),
			));


			for($o =& $ol->begin(); !$ol->end(); $o =& $ol->next())
			{
				$clinf = $this->cfg["classes"][$o->class_id()];
				$t->add_item(array(
					"timestamp" => $o->prop("start1"),
					"data" => array(
						"name" => " - ".date("H:i", $o->prop("end")).": ".$o->prop("name"),
						"icon" => "", //icons::get_icon_url($o),
						"link" => $this->mk_my_orb("change",array("id" => $o->id()),$clinf["file"]),
					),
				));

				if ($o->prop("start1") > $range["overview_start"])
				{
					$this->overview[$o->prop("start1")] = 1;
				};
			};
		};
	}

	function get_overview($arr = array())
	{
		return $this->overview;
	}
	
	function get_events_for_order($arr)
	{
		$o = obj($arr["id"]);
		$ret = array();
		if (is_array($o->meta("event_ids")))
		{
			return $this->make_keys(array_values($o->meta("event_ids")));
		}

		return $ret;
	}

	function _init_work_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "order",
			"caption" => "J&auml;jekord",
			"align" => "left"
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Ressurss",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "time",
			"caption" => "Millal t&ouml;&ouml;sse l&auml;heb",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "length",
			"caption" => "Kaua kestab",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
			"align" => "center"
		));
	}


	function do_work_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_work_tbl($t);
		$t->set_default_sortby("order");

		$order = $arr["obj_inst"]->meta("order");
		$length = $arr["obj_inst"]->meta("length");
		$processing = $arr["obj_inst"]->meta("processing");
		$done = $arr["obj_inst"]->meta("done");

		$srl = $this->_get_sel_resource_list($arr["obj_inst"]);
		foreach($srl as $resid)
		{
			if (!$length[$resid])
			{
				$length[$resid] = 1; // default to 1h
			}
			if (!$order[$resid])
			{
				$order[$resid] = 0; 
			}
		}


		$lut = $this->get_lut_by_pri($order, $length);
		$event_ids = $arr["obj_inst"]->meta("event_ids");
		$this->cur_priority = $arr["obj_inst"]->prop("priority");

		foreach($srl as $resid)
		{
			if ($event_ids[$resid])
			{
				$tmp = obj($event_ids[$resid]);
				$this->times_by_resource[$resid] = $tmp->prop("start1");
			}
		}

		foreach($srl as $resid)
		{
			if (!$event_ids[$resid])
			{
				continue;
			}
			$reso = obj($resid);

			$status = "";
			if ($event_ids[$resid] && $processing[$resid] && !$done[$resid])
			{
				$status = "T&ouml;&ouml;s";
			}

			if ($event_ids[$resid] && $processing[$resid] && $done[$resid])
			{
				$status = "Valmis";
			}

			if ($status == "")
			{
				continue;
			}

			$time = date("d.m.Y H:i", $this->times_by_resource[$resid])." - ".date("d.m.Y H:i", $this->times_by_resource[$resid] + (3600 * $length[$resid]));

			$t->define_data(array(
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $resid), CL_WORKFLOW_RESOURCE),
					"caption" => $reso->name()
				)),
				"time" => $time,
				"order" => $order[$resid],
				"length" => $length[$resid]." tundi",
				"status" => $status,
			));
		}

		$t->sort_by();
	}


	function do_write_times_to_cal($arr)
	{
		if (!is_array($arr["request"]["time"]))
		{
			return;
		}

		$event_ids = $arr["obj_inst"]->meta("event_ids");
		$length = $arr["obj_inst"]->meta("length");

		load_vcl("date_edit");
		$de = new date_edit();

		foreach($arr["request"]["time"] as $resid => $time_d)
		{
			$ts = $de->get_timestamp($time_d);
			$reso = obj($resid);

			if ($event_ids[$resid])
			{
				$ev = obj($event_ids[$resid]);
				$ev->set_prop("start1", $ts);
				$ev->set_prop("end", $ts + ($length[$resid] * 3600));
				$ev->set_meta("task_priority", $arr["obj_inst"]->prop("priority"));
				$ev->set_meta("job_id", $arr["obj_inst"]->id());
				$ev->save();
			}
			else
			{
				// add an event
				$ev = obj();
				$ev->set_parent($resid);
				$ev->set_class_id(CL_CRM_MEETING);
				$ev->set_name($arr["obj_inst"]->name());
				$ev->set_prop("start1", $ts);
				$ev->set_prop("end", $ts + ($length[$resid] * 3600));
				$ev->set_meta("task_priority", $arr["obj_inst"]->prop("priority"));
				$ev->set_meta("job_id", $arr["obj_inst"]->id());
				$ev->save();

				$reso->connect(array(
					"to" => $ev->id(),
					"reltype" => 1 // RELTYPE_EVENT
				));
			}

			$event_ids[$resid] = $ev->id();

			// ach! if there are events during the one added with lower priority (if higer priority we fucked up earlier) 
			// then we must move all of them to a later date. 
			//echo "to add event to calendar, do move events lower, new event = ".date("d.m.Y H:i",$ts)." - ".date("d.m.Y H:i",$ts + ($length[$resid] * 3600))." <br>";
		}

		$arr["obj_inst"]->set_meta("event_ids", $event_ids);

		foreach($event_ids as $resid => $evid)
		{
			$this->do_move_events_lower($arr["obj_inst"], $resid,$event_ids[$resid]);
		}
	}

	function do_save_resources($arr)
	{
		$evids = $arr["obj_inst"]->meta("event_ids");

		// if processing is checked then mark the events as not-moveable
		$newproc = new aw_array($arr["request"]["processing"]);
		$curproc = $arr["obj_inst"]->meta("processing");
		foreach($newproc->get() as $resid => $one)
		{
			if ($one != 1)
			{
				continue;
			}
			if (!$curproc[$resid])
			{
				// DING!
				$ev = obj($evids[$resid]);
				$ev->set_meta("no_move", 1);
				$ev->set_meta("task_priority", 2000000000);
				$ev->save();
				//echo "set event ".$ev->id()." as nomove! <br>";
			}
		}

		$arr["obj_inst"]->set_meta("pred", $arr["request"]["pred"]);
		$arr["obj_inst"]->set_meta("length", $arr["request"]["length"]);
		$arr["obj_inst"]->set_meta("processing", $arr["request"]["processing"]);
		//echo "set processing as ".dbg::dump($arr["request"]["processing"])." <br>";
		$arr["obj_inst"]->set_meta("done", $arr["request"]["done"]);
	}

	function _init_res_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "order",
			"caption" => "J&auml;jekord",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "pred",
			"caption" => "Eeldustegevused",
			"align" => "left"
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Ressurss",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "time",
			"caption" => "Millal t&ouml;&ouml;sse l&auml;heb",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "length",
			"caption" => "Kaua kestab",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "processing",
			"caption" => "T&ouml;&ouml;s",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "done",
			"caption" => "Valmis",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "current",
			"caption" => "Praegune ressursi tegevus",
			"align" => "center",
		));
	}


	function do_res_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_res_tbl($t);
		$t->set_sortable(false);

		$order = $arr["obj_inst"]->meta("order");
		$pred = $arr["obj_inst"]->meta("pred");
		$length = $arr["obj_inst"]->meta("length");
		$processing = $arr["obj_inst"]->meta("processing");
		//echo dbg::dump($processing);
		$done = $arr["obj_inst"]->meta("done");

		load_vcl("date_edit");
		$de = new date_edit();
		$de->configure(array(
			"day" => "",
			"month" => "",
			"year" => "",
			"hour" => "",
			"minute" => ""
		));

		if (!is_array($order))
		{
			$order = array();
		}
		$ord_change = false;
		$srl = $this->_get_sel_resource_list($arr["obj_inst"]);
		foreach($srl as $resid)
		{
			if (!$length[$resid])
			{
				$length[$resid] = 1; // default to 1h
			}
			if (!$order[$resid])
			{
				if (count($order) < 1)
				{
					$order[$resid] = 1;
				}
				else
				{
					$order[$resid] = max(array_values($order)) + 1; 
				}
				$ord_change = true;
			}
		}
		if ($ord_change)
		{
			$arr["obj_inst"]->set_meta("order", $order);
			$arr["obj_inst"]->save();
		}


		$lut = $this->get_lut_by_pri($order, $length);
		$event_ids = $arr["obj_inst"]->meta("event_ids");
		$this->cur_priority = $arr["obj_inst"]->prop("priority");

		foreach($srl as $resid)
		{
			if ($event_ids[$resid])
			{
				$tmp = obj($event_ids[$resid]);
				$this->times_by_resource[$resid] = $tmp->prop("start1");
			}
			else
			{
				$reso = obj($resid);
				$res_i = $reso->instance();

				$params = array(
					"o" => $reso,
					"length" => $length[$resid] * 3600,
					"ignore_events" => $event_ids,
					"priority" => $this->cur_priority
				);

				if ($pred[$resid] != "")
				{
					$max_t = 0;
					foreach(explode(",", $pred[$resid]) as $pred_num)
					{
						// pred_num is index, not resid
						$pred_id = array_search($pred_num, $order);
						$max_t = max($max_t, $this->req_get_time_for_resource($pred_id, $length, $order, $lut, $event_ids, $pred) + ($length[$pred_id] * 3600));
					}
					$params["min_time"] = $max_t;
				}
	
				$ts = $res_i->get_next_avail_time_for_resource($params);
				$this->times_by_resource[$resid] = $ts;
			}
		}
/*		foreach($this->times_by_resource as $resid => $ts)
		{
			echo "resid = ".$resid." ts = ".date("d.m.Y H:i", $ts)." <br>";
		}*/

		foreach($srl as $resid)
		{
			$reso = obj($resid);

			$processing_str = "";
			//echo "for $resid event = ".$event_ids[$resid]." proc = ".$processing[$resid]." done = ".$done[$resid]." <br>";
			if ($event_ids[$resid] && !$processing[$resid] && !$done[$resid])
			{
				$processing_str = html::checkbox(array(
					"name" => "processing[$resid]",
					"value" => 1,
					"checked" => ($processing[$resid] == 1)
				));
			}
			else
			{
				$processing_str = html::hidden(array(
					"name" => "processing[$resid]",
					"value" => $processing[$resid],
				));
			}

			$done_str = "";
			if ($event_ids[$resid] && $processing[$resid] && !$done[$resid])
			{
				$done_str = html::checkbox(array(
					"name" => "done[$resid]",
					"value" => 1,
					"checked" => ($done[$resid] == 1)
				));
			}
			else
			{
				$done_str = html::hidden(array(
					"name" => "done[$resid]",
					"value" => $done[$resid],
				));
			}

			$time = $de->gen_edit_form("time[$resid]", $this->times_by_resource[$resid], 2004, 2008, true)." - ".date("d.m.Y H:i", $this->times_by_resource[$resid] + (3600 * $length[$resid]));
			if ($event_ids[$resid] && $processing[$resid])
			{
				$time = date("d.m.Y H:i", $this->times_by_resource[$resid])." - ".date("d.m.Y H:i", $this->times_by_resource[$resid] + (3600 * $length[$resid]));
			}

			$cur = "";
			// get current event from resource calendar
			$res_i = $reso->instance();
			if (($curevent = $res_i->get_current_event($reso, time())))
			{
				$cur = $curevent->name();
			}


			if (!$order[$resid])
			{
				$order[$resid] = ++$cur_cnt;
			}			
			$t->define_data(array(
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $resid, "group" => "calendar"), CL_WORKFLOW_RESOURCE),
					"caption" => $reso->name()
				)),
				"time" => $time,
				"order" => $order[$resid],
				"pred" => html::textbox(array(
					"name" => "pred[$resid]",
					"size" => 5,
					"value" => $pred[$resid]
				)),
				"length" => html::textbox(array(
					"name" => "length[$resid]",
					"size" => 5,
					"value" => $length[$resid]
				))." tundi",
				"processing" => $processing_str,
				"done" => $done_str,
				"current" => $cur
			));
		}

		//$arr["obj_inst"]->set_meta("order", $order);
	}

	function save_sel_resources($arr)
	{
		// get already connected
		$cs = array();
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_RESOURCE")) as $c)
		{
			$cs[$c->prop("to")] = $c->prop("to");
		}
		

		// go over conns and deleted the ones that are not selected
		foreach($cs as $srid)
		{
			if (!isset($arr["request"]["sel_resources"][$srid]))
			{
				$arr["obj_inst"]->disconnect(array(
					"from" => $srid
				));
			}
		}

		// go over sels and connect if not yet
		$sr = new aw_array($arr["request"]["sel_resources"]);
		foreach($sr->get() as $srid)
		{
			if (!isset($cs[$srid]))
			{
				$arr["obj_inst"]->connect(array(
					"to" => $srid,
					"reltype" => 2 // RELTYPE_RESOURCE
				));
			}
		}
	}

	function _get_resource_list($o)
	{
		error::throw_if(!$o->meta("entity_instance"), array(
			"id" => ERR_NO_ENT,
			"msg" => "prisma_order::get_resource_list(): no entity instance id!"
		));
		$e_i_o = obj($o->meta("entity_instance"));
		
		error::throw_if(!$e_i_o->prop("entity_type"), array(
			"id" => ERR_NO_ENT,
			"msg" => "prisma_order::get_resource_list(): no entity type id in entity instance (".$e_i_o->id().") !"
		));
		$entity_type = obj($e_i_o->prop("entity_type"));

		// get resrouces from entity type
		$ret = array();
		foreach($entity_type->connections_from(array("type" => "RELTYPE_RESOURCE")) as $c)
		{
			$ret[$c->prop("to")] = $c->prop("to.name");
		}
		return $ret;
	}

	function _get_sel_resource_list($o)
	{
		$ret = array();
		foreach($o->connections_from(array("type" => "RELTYPE_RESOURCE")) as $c)
		{
			$ret[$c->prop("to")] = $c->prop("to");
		}
		return $ret;
	}

	function _get_actions($o)
	{
		error::throw_if(!$o->meta("entity_instance"), array(
			"id" => ERR_NO_ENT,
			"msg" => "prisma_order::get_resource_list(): no entity instance id!"
		));
		$e_i_o = obj($o->meta("entity_instance"));
		
		$wfe = get_instance("workflow/workflow_entity_instance");
		return array("" => "") + $wfe->get_possible_next_states($e_i_o);
	}

	function _get_cur_action($o)
	{
		error::throw_if(!$o->meta("entity_instance"), array(
			"id" => ERR_NO_ENT,
			"msg" => "prisma_order::get_resource_list(): no entity instance id!"
		));
		$e_i_o = obj($o->meta("entity_instance"));

		$wfe = get_instance("workflow/workflow_entity_instance");
		$tmp = $wfe->get_current_state($e_i_o->id());

		return $tmp->name();
	}

	function get_lut_by_pri($order, $length)
	{
		$lut = array();
		$awa = new aw_array($order);
		foreach($awa->get() as $t_resid => $ov)
		{
			if (isset($lut[$ov]))
			{
				// if there already is a priority then check length and the longer one wins
				$prev_len = $length[$lut[$ov]];
				$cur_len = $length[$t_resid];
				if ($cur_len > $prev_len)
				{
					$lut[$ov] = $t_resid;
				}
			}
			else
			{
				$lut[$ov] = $t_resid;
			}
		}
		return $lut;
	}

	function req_get_time_for_resource($resid, $length, $order, $lut, $event_ids, $pred)
	{
		$reso = obj($resid);
		$res_i = $reso->instance();

		$params = array(
			"o" => $reso,
			"length" => $length[$resid] * 3600,
			"ignore_events" => $event_ids,
			"priority" => $this->cur_priority
		);

		if ($pred[$resid] != "")
		{
			$max_t = 0;
			foreach(explode(",", $pred[$resid]) as $pred_num)
			{
				// pred_num is index, not resid
				$pred_id = array_search($pred_num, $order);
				//echo "pred_id for $pred_num = $pred_id <br>";
				$max_t = max($max_t, $this->req_get_time_for_resource($pred_id, $length, $order, $lut, $event_ids, $pred) + ($length[$pred_id] * 3600));
				//echo "got max_t for $resid predecessor $pred_num : ".date("d.m.Y H:i", $max_t)." <br>";
			}
			$params["min_time"] = $max_t;
		}

		$ts = $res_i->get_next_avail_time_for_resource($params);
		$this->times_by_resource[$resid] = $ts;
		return $ts;
	}

	function do_move_events_lower($o, $resid, $cur_event_id = false)
	{
		classload("date_calc");
		// get all events for that timespan
		$reso = obj($resid);
		$res_i = $reso->instance();

		$order = $o->meta("order");
		$length = $o->meta("length");
		$lut = $this->get_lut_by_pri($order, $length);

		// while $moves
		$moves = true;
		while ($moves)
		{
			$moves = false;
			$evids = $res_i->get_events_for_resource(array(
				"id" => $resid
			));

			//	if overlapping-events-exist
			//		get first overlap
			$overlap = $this->get_first_overlapping_event($evids);
			//echo "check for overlap in ".dbg::dump($evids)." got res = ".dbg::dump($overlap)." <br>";
			if ($overlap)
			{
				$overlap_len = $overlap->prop("end") - $overlap->prop("start1");

				// find the job and resource for the overlap event. 
				$job_id = $overlap->meta("job_id");
				$job_o = obj($job_id);
				$j_events = $job_o->meta("event_ids");
				$j_length = $job_o->meta("length");
				$j_order = $job_o->meta("order");
				$j_lut = $this->get_lut_by_pri($j_order, $j_length);
				$j_pred = $job_o->meta("pred");
				//echo "pred = ".dbg::dump($j_pred)." order = ".dbg::dump($j_order)." for $job_id meta = ".dbg::dump($job_o->meta())."<br>";
				$overlap_resid = array_search($overlap->id(), $j_events);
/*				if (!$overlap_resid)
				{
					continue;
				}*/
//				echo "overlap_resid = $overlap_resid , overlap id = ".$overlap->id()." j_evs = ".dbg::dump($j_events)." <br>";


				//		find first avail time
				$this->cur_priority = $overlap->meta("task_priority");
				$this->times_by_resource = array();
				$ts = $this->req_get_time_for_resource($overlap_resid, $j_length, $j_order, $j_lut, array($overlap->id() => $overlap->id()), $j_pred);
				//echo "got new ts as ".date("d.m.Y H:i", $ts)." len = $overlap_len <br>";
				$overlap->set_prop("start1", $ts);
				$overlap->set_prop("end", $ts + $overlap_len);
				$overlap->save();

				//		move = true
				$moves = true;

				// also, calc the timestamps for all the other events for the job that the first event was in and move them forward.
				foreach($j_events as $j_resid => $j_evid)
				{
					if ($j_evid == $overlap->id())
					{
						continue;
					}

					$ts = $this->req_get_time_for_resource($j_resid, $j_length, $j_order, $j_lut, array($j_evid => $j_evid), $j_pred);
					$j_evo = obj($j_evid);
					if ($ts > $j_evo->prop("start1"))
					{
						$j_len = $j_evo->prop("end") - $j_evo->prop("start1");
						$j_evo->set_prop("start1", $ts);
						$j_evo->set_prop("end", $ts + $j_len);
						$j_evo->save();
					}
				}
			}
			// end while
		}
	}

	function get_first_overlapping_event($evids)
	{
		// sort by time
		$evs = array();
		foreach($evids as $evid)
		{
			$tmp = obj($evid);
			$beg = $tmp->prop("start1");
			if (isset($evs[$beg]))
			{
				if ($evs[$beg]->meta("task_priority") > $tmp->meta("task_priority"))
				{
					return $tmp;
				}
				return $evs[$beg];
				//die("damn lapper! $beg evb4 = ".$evs[$beg]->id()." beg = ".date("d.m.Y H:i", $beg)." <br>");
			}
			$evs[$beg] = $tmp;
		}

		ksort($evs);

		$tmp = $evs;

		// for each event
		foreach($evs as $time => $event)
		{
			// check if another event overlaps with this one.
			// simple o(n*n) suck-ass search here
			foreach($tmp as $time2 => $event2)
			{
				if ($event2->id() == $event->id())
				{
					continue;
				}

				if (timespans_overlap($event->prop("start1"), $event->prop("end"), $event2->prop("start1"), $event2->prop("end")))
				{
					//echo "overlap for ".$event2->id()." with ".$event->id()." (".date("d.m.Y H:i", $event2->prop("start1"))." - ".date("d.m.Y H:i", $event2->prop("end"))." vs ".date("d.m.Y H:i", $event->prop("start1"))." - ".date("d.m.Y H:i", $event->prop("end")).")<br>";
					// return the one with the lower priority
					if ($event2->meta("task_priority") > $event->meta("task_priority"))
					{
						return $event;
					}
					return $event2;
				}
			}
		}

		return false;
	}
}
?>
