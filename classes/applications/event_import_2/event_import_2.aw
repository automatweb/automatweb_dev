<?php
// event_import_2.aw - S&uuml;ndmuste import 2
/*

@classinfo syslog_type=ST_EVENT_IMPORT_2 relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@default field=meta
@default method=serialize

	@property slave_obj type=relpicker reltype=RELTYPE_SLAVE automatic=1
	@caption Allikas

	@property events_manager type=relpicker reltype=RELTYPE_EVENTS_MANAGER
	@caption S&uuml;ndmuste halduse keskkond

	@property event_cfgform type=relpicker reltype=RELTYPE_EVENT_CFGFORM
	@caption Seadete vorm
	@comment Kalendris&uuml;ndmuse seadetevorm

	@property language type=relpicker reltype=RELTYPE_EVENT_LANGUAGE
	@caption Keel
	@comment Objektide keel

	@property last_import type=text
	@caption Viimane import
	@comment Viimase impordi l&otilde;pu kellaaeg

	@property invoke type=text store=no
	@caption Import

@groupinfo update_rules caption="Muudatuste reeglid"
@default group=update_rules

	@property urt_event type=table no_caption=1

	@property urt_location type=table no_caption=1

	@property urt_organizer type=table no_caption=1

	@property urt_sector type=table no_caption=1

@reltype SLAVE value=1 clid=CL_JSON_DELFI
@caption Allikas

@reltype EVENTS_MANAGER value=2 clid=CL_EVENTS_MANAGER
@caption S&uuml;ndmuste halduse keskkond

@reltype EVENT_CFGFORM value=3 clid=CL_CFGFORM
@caption Seadete vorm

@reltype EVENT_LANGUAGE value=4 clid=CL_LANGUAGE
@caption Keel

*/

class event_import_2 extends class_base
{
	function event_import_2()
	{
		$this->init(array(
			"tpldir" => "applications/events_import/event_import_2",
			"clid" => CL_EVENT_IMPORT_2
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "last_import":
				$prop["value"] = date("Y-m-d H:i:s", $prop["value"]);
				break;

			case "invoke":
				$prop["value"] = html::href(array(
					"caption" => t("K&auml;ivita import"),
					"url" => $this->mk_my_orb("invoke", array("id" => $arr["obj_inst"]->id(), "return_url" => get_ru())),
				));
				break;
		}

		return $retval;
	}

	private function init_urt($arr, $fs)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "field",
			"caption" => t("S&uuml;ndmuse v&auml;li, kuhu salvestada"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "update",
			"caption" => t("Uuenda v&auml;lja sisu, isegi kui nii AWs kui ka allikas on v&auml;lja sisu muudetud"),
			"align" => "center",
		));
		foreach($fs as $f)
		{
			$t->define_data(array(
				"field" => $f,
				"update" => html::checkbox(array(
					"name" => $arr["prop"]["name"]."[".$f."]",
					"value" => 1,
				)),
			));
		}
	}

	function _get_urt_event($arr)
	{
		$this->init_urt(&$arr, $fs);
	}

	function _get_urt_location($arr)
	{
		$this->init_urt(&$arr, $fs);
	}

	function _get_urt_organizer($arr)
	{
		$this->init_urt(&$arr, $fs);
	}

	function _get_urt_sector($arr)
	{
		$this->init_urt(&$arr, &$fs);
	}

	function _get_fields_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "field",
			"caption" => t("V&auml;li"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "event_field",
			"caption" => t("S&uuml;ndmuse v&auml;li, kuhu salvestada"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "update_rule",
			"caption" => t("Uuenda v&auml;lja sisu, isegi kui nii AWs kui ka allikas on v&auml;lja sisu muudetud"),
			"align" => "center",
		));
		foreach($fs as $f)
		{
			$t->define_data(array(
				"field" => $$f,
				"event_field" => html::select(array(
					"name" => "fields_tbl[".$f."][event_field]",
					"options" => $ops,
				)),
				"update_rule" => html::checkbox(array(
					"name" => "fields_tbl[".$f."][update_rule]",
					"value" => 1,
				))
			));
		}
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

	/**
		@attrib name=invoke api=1

		@param id required type=oid

		@param return_url optional type=string
	**/
	function invoke($arr)
	{
		$o = obj($arr["id"]);
		
		$slave_inst = obj($o->slave_obj)->instance();
		$data = $slave_inst->make_master_import_happy(array("id" => $o->slave_obj));

		// Get configuration
		$conf = $this->get_conf(&$o);
		// Get imported objects
		$ios = $this->get_ios($conf);

		$this->import_sectors(&$conf, &$data["sector"], &$ios["sector"]);
		$this->import_locations(&$conf, &$data["location"], &$ios["location"]);
		$this->import_organizers(&$conf, &$data["organizer"], &$ios["organizer"]);
		$this->import_events(&$conf, &$data["event"], &$ios);

		$o->last_import = time();
		$o->save();
		return $arr["return_url"];
	}

	private function import_sectors($conf, $datas, $ios)
	{
		foreach($datas as $id => $data)
		{
			$new = false;
			if(array_key_exists($id, $ios))		// Existing object
			{
				$o = obj($ios[$id]);
				$oc = $o->meta("orig_content");
			}
			else
			{
				$o = obj();
				$o->class_id = $conf["cl_sector"];
				$o->parent = $conf["dir_sector"];
				$new = true;
			}
			foreach($data as $k => $v)
			{
				$p = ($k == "jrk") ? $o->ord() : $o->prop($k);
				// We'll update the content if it differs from original and it's not prohibited to do that.
				$update = ($oc[$k] != $p && !in_array("sector_".$k, $conf["do_not_update"]));
				if($new || $update)
				{
					switch($k)
					{
						case "jrk":
							$o->set_ord($v);
							break;

						case "tegevusala":
						case "ext_id":
							$o->set_prop($k, $v);
							break;
					}
				}
				// We need to save the original content. Otherwise we can't tell if the content has been changed in AW or the source.
				$oc[$k] = $v;
			}
			$o->set_meta("orig_content", $oc);
			$o->save();
			// We add the new object to the list of imported objects.
			if($new)
			{
				$this->add_to_ios($o);
			}
		}
	}

	private function add_to_ios($o)
	{
	}

	private function import_locations($conf, $datas, $ios)
	{
	}

	private function import_organizers($conf, $datas, $ios)
	{
	}

	private function import_events($conf, $datas, $ios)
	{
	}

	private function get_ios($c)
	{
		$r = array();

		$ots = array("event", "location", "organizer", "sector");

		foreach($ots as $ot)
		{
			$odl = new object_data_list(
				array(
					"class_id" => $c["cl_".$ot],
					"parent" => $c["dir_".$ot],
					"lang_id" => $c["language"],
					"status" => array(),
				),
				array(
					$cl[$o] => array("ext_id" => "ext_id"),
				)
			);
			foreach($odl->arr() as $oid => $odata)
			{
				$r[$ot][$odata["ext_id"]] = $oid;
			}
		}

		return $r;
	}

	private function get_conf($o)
	{
		$c = array();

		$em = obj($o->events_manager);
		$c["dir_event"] = $em->event_menu;
		$c["dir_location"] = $em->places_menu;
		$c["dir_organizer"] = $em->organiser_menu;
		$c["dir_sector"] = $em->sector_menu;

		$c["cl_event"] = CL_CALENDAR_EVENT;
		$c["cl_location"] = CL_SCM_LOCATION;
		$c["cl_organizer"] = CL_CRM_COMPANY;
		$c["cl_sector"] = CL_CRM_SECTOR;

		return $c;
	}
}

?>
