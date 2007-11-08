<?php
// event_import.aw - SĆ¼ndmuste import
/*

@classinfo syslog_type=ST_EVENT_IMPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property event_form type=relpicker reltype=RELTYPE_EVENT_FORM field=meta method=serialize
	@caption S&uuml;ndmuse vorm
	@comment Kultuuriakna s&uuml;ndmuse lisamise/muutmise vorm

	@property xml_sources type=relpicker reltype=RELTYPE_XML_SOURCE field=meta method=serialize multiple=1
	@caption XML allikad:
	@comment XML v&auml;ljundi allikad, mida kasutatakse importimisel

	@property json_sources type=relpicker reltype=RELTYPE_JSON_SOURCE field=meta method=serialize multiple=1
	@caption JSON allikad:
	@comment JSON v&auml;ljundi allikad, mida kasutatakse importimisel

	@property cb_log_changes type=checkbox ch_value=1 field=meta mehtod=serialize
	@caption Muudatuste tegemiseks k&uuml;sin luba?

	@property translatable_fields type=select multiple=1 field=meta method=serialize
	@caption V&auml;ljad, mida v&otilde;imalusel t&otilde;lgitakse

	@property last_import_text type=text store=no
	@caption Viimane import
	@comment Viimase impordi l&otilde;ppemise aeg

	@property next_import_text type=text store=no
	@caption J&auml;rgmine automaatne import
	@comment J&auml;rgmise automaatse alguse aeg

	@property import_events_all type=checkbox ch_value=1 field=meta mehtod=serialize
	@caption Impordi k&otilde;ik s&uuml;ndmused

	@property import_events type=text store=no
	@caption Impordi s&uuml;ndmused
	@comment Link s&uuml;ndmuste importimiseks

@groupinfo xml_config caption="XML seaded"
@default group=xml_config

	@property xml_config_table type=table no_caption=1

@groupinfo recurrence_config caption="Automaatne import"
@default group=recurrence_config

	@property recurrence type=relpicker reltype=RELTYPE_RECURRENCE field=meta method=serialize
	@caption Kordus

	@property auto_import_user type=textbox field=meta method=serialize
	@caption Kasutaja
	@comment Kasutajanimi, kelle &otilde;igustes automaatne import teostatakse

	@property auto_import_passwd type=password field=meta method=serialize
	@caption Parool
	@comment Kasutaja parool, kelle &otilde;igustes automaatne import teostatakse

@groupinfo import_log caption="Logi" submit=no
@default group=import_log

	@property import_log_toolbar type=toolbar no_caption=1

	@property import_log_table type=table
	@caption Muudetud s&uuml;ndmuste logi

// -------------- RELTYPES ----------------

@reltype XML_SOURCE value=1 clid=CL_XML_SOURCE
@caption XML allikas

@reltype RECURRENCE value=5 clid=CL_RECURRENCE
@caption Kordus

@reltype EVENT_FORM value=10 clid=CL_CFGFORM
@caption S&uuml;ndmuse vorm

@reltype JSON_SOURCE value=15 clid=CL_JSON_SOURCE
@caption JSON allikas

*/

class event_import extends class_base
{
	function event_import()
	{
		// change this to the folder under the templates folder, where this classes templates will be,
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "import/event_import",
			"clid" => CL_EVENT_IMPORT
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "last_import_text":
				$last_import = $arr['obj_inst']->meta("last_import");
				$prop['value'] = (empty($last_import)) ? "0" : date("d-M-y / H:i", $last_import);
				break;

			case "next_import_text":
				$next_import = $this->activate_next_auto_import(array(
					"object" => $arr['obj_inst'],
				));
				$prop['value'] = (empty($next_import)) ? "0" : date("d-M-y / H:i", $next_import);
				break;

			case "import_events":
				$message = t("Alates viimasest impordist");
				if ($arr['obj_inst']->prop("import_events_all"))
				{
					$message = t("K&otilde;ik s&uuml;ndmused");
				}
				$prop['value'] = html::href(array(
					"caption" => sprintf(t("Impordi s&uuml;ndmused (%s)"), $message),
					"url" => $this->mk_my_orb("import_events", array(
							"id" => $arr['obj_inst']->id(),
						)),
					"title" => sprintf(t("Impordi Kultuuriakna s&uuml;ndmused (%s)"), $message),
				));
				break;
		};
		return $retval;
	}

	function _get_import_log_toolbar($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "make_changes",
			"tooltip" => t("Tee muudatused"),
			"img" => "save.gif",
			"action" => "make_changes",
		));
		$t->add_button(array(
			"name" => "auto_change",
			"tooltip" => t("Muuda selle s&uuml;ndmuse seda v&auml;lja automaatselt edaspidi"),
			"img" => "class_244_done.gif",
			"action" => "auto_change",
		));
		$t->add_button(array(
			"name" => "ignore",
			"tooltip" => t("Ignoreeri selle v&auml;lja muudatusi edaspidi"),
			"img" => "class_244.gif",
			"action" => "ignore",
		));
		$t->add_delete_button();
	}

	function _get_translatable_fields($arr)
	{
		// getting properties from cfgform
		$event_form_oid = $arr['obj_inst']->prop("event_form");
		if (!is_oid($event_form_oid))
		{
			return;
		}
		$event_form_obj = obj($event_form_oid);
		$event_form_inst = $event_form_obj->instance();
		$props = $event_form_inst->get_props_from_cfgform(array(
			"id" => $event_form_obj->id(),
		));
		foreach ($props as $value)
		{
			if (empty($value['caption']))
			{
				$value['caption'] = $value['name'];
			}
			$options[$value['name']] = $value['caption'];
		}

		$arr["prop"]["options"] = $options;
	}

	function _get_import_log_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		// Let's describe the table
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
			"caption" => t("Vali"),
		));
		$t->define_field(array(
			"name" => "obj",
			"caption" => t("S&uuml;ndmus"),
		));
		$t->define_field(array(
			"name" => "field",
			"caption" => t("V&auml;li"),
		));
		$t->define_field(array(
			"name" => "content_old",
			"caption" => t("Vana sisu"),
		));
		$t->define_field(array(
			"name" => "content_new",
			"caption" => t("Uus sisu"),
		));
		$t->define_field(array(
			"name" => "timestamp",
			"caption" => t("Aeg"),
		));

		// Gathering the data.
		$ol = new object_list(array(
			"parent" => array(),
			"class_id" => CL_IMPORT_LOG,
		));
		foreach($ol->arr() as $log)
		{
			$log_parent_id = $log->parent();
			$log_parent_obj = obj($log_parent_id);
			$all_vals = $log_parent_obj->meta("translations");
			$t->define_data(array(
				"obj" => $log_parent_obj->name(),
				"field" => $log->prop("field") . (($log->meta("trans_lang") != "") ? " (" . $log->meta("trans_lang") . ")" : ""),
				"content_old" => ($log->meta("trans_lang") == "") ? $log_parent_obj->prop($log->prop("field")) : $all_vals[2][$log->prop("field")],
				"content_new" => $log->prop("content"),
				"timestamp" => date("d-M-y / H:i:s", $log->prop("timestamp")),
				"oid" => $log->id(),
			));
		}
	}

	function subt_subt($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$saved_subtag_table = $arr["saved_subtag_table"];
		$saved_arguement_table = $arr["saved_arguement_table"];
		$saved_xml_conf = $arr['obj_inst']->meta("xml_conf");
		$saved_xml_conf_time = $arr['obj_inst']->meta("xml_conf_time");
		$saved_xml_conf_time_format = $arr['obj_inst']->meta("xml_conf_time_format");

		$date_sel = array("start", "start_date", "start_time", "end", "end_date", "end_time");

		$subtags = $saved_subtag_table[$arr["parent_tag_name"]];
		$subtags = str_replace(" ", "", $subtags);
		$subtags = explode(",", $subtags);
		foreach($subtags as $subtag)
		{
			if(!empty($subtag))
			{
				$t_ptn = $arr["parent_tag_name"];
				$t_ptc = $arr["parent_tag_caption"];
				if($arr["parent_tag_name"] != "root")
				{
					$arr["parent_tag_name"] .= "_".$subtag;
					$arr["parent_tag_caption"] .= " -> ".$subtag;
				}
				else
				{
					$arr["parent_tag_name"] = $subtag;
					$arr["parent_tag_caption"] = $subtag;
				}

				$form_field = html::select(array(
					"name" => "xml_conf[".$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."]",
					"options" => $arr["options"],
					"selected" => $saved_xml_conf[$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]],
				));
				$time_field = html::select(array(
					"name" => "xml_conf_time[".$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."]",
					"options" => array(
						"do_not_save_into_db" => "-- Ei salvestata --",
						"id" => t("ID"),
						"name" => t("Nimi"),
						"comment" => t("Kommentaar"),
						"start" => t("Algab"),
						"start_date" => t("Algab (ainult kuup&auml;ev)"),
						"start_time" => t("Algab (ainult kellaaeg)"),
						"end" => t("L&otilde;peb"),
						"end_date" => t("L&otilde;peb (ainult kuup&auml;ev)"),
						"end_time" => t("L&otilde;peb (ainult kellaaeg)"),
						"location_id" => t("Toimumiskoha ID"),
						"location_name" => t("Toimumiskoha nimi"),
					),
					"selected" => $saved_xml_conf_time[$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]],
				));
				if(in_array($saved_xml_conf_time[$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]], $date_sel))
				{
					$time_format = html::textbox(array(
						"name" => "xml_conf_time_format[".$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."]",
						"size" => 20,
						"value" => $saved_xml_conf_time_format[$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]],
					));
				}
				else
				{
					$time_format = "";
				}
				$t->define_data(array(
					"xml_source" => $arr["parent_tag_source"],
					"xml_field" => $arr["parent_tag_caption"],
					"form_field" => $form_field,
					"time_field" => $time_field,
					"time_format" => $time_format,
				));
				$subt_args = $saved_arguement_table[$arr["parent_tag_name"]];
				$subt_args = str_replace(" ", "", $subt_args);
				$subt_args = explode(",", $subt_args);

				foreach($subt_args as $subt_arg)
				{
					if(!empty($subt_arg))
					{
						$form_field = html::select(array(
							"name" => "xml_conf[".$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."_args".$subt_arg."]",
							"options" => $arr["options"],
							"selected" => $saved_xml_conf[$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."_args".$subt_arg],
						));
						$time_field = html::select(array(
							"name" => "xml_conf_time[".$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."_args".$subt_arg."]",
							"options" => array(
								"do_not_save_into_db" => "-- Ei salvestata --",
								"id" => t("ID"),
								"name" => t("Nimi"),
								"comment" => t("Kommentaar"),
								"start" => t("Algab"),
								"start_date" => t("Algab (ainult kuup&auml;ev)"),
								"start_time" => t("Algab (ainult kellaaeg)"),
								"end" => t("L&otilde;peb"),
								"end_date" => t("L&otilde;peb (ainult kuup&auml;ev)"),
								"end_time" => t("L&otilde;peb (ainult kellaaeg)"),
								"location_id" => t("Toimumiskoha ID"),
								"location_name" => t("Toimumiskoha nimi"),
							),
							"selected" => $saved_xml_conf_time[$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."_args".$subt_arg],
						));
						if(in_array($saved_xml_conf_time[$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."_args".$subt_arg], $date_sel))
						{
							$time_format = html::textbox(array(
								"name" => "xml_conf_time_format[".$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."_args".$subt_arg."]",
								"size" => 20,
								"value" => $saved_xml_conf_time_format[$arr["parent_tag_source_id"]."_".$arr["parent_tag_name"]."_args".$subt_arg],
							));
						}
						else
						{
							$time_format = "";
						}
						$t->define_data(array(
							"xml_source" => $arr["parent_tag_source"],
							"xml_field" => $arr["parent_tag_caption"]." (".$subt_arg.")",
							"form_field" => $form_field,
							"time_field" => $time_field,
							"time_format" => $time_format,
						));
					}
				}

				$this->subt_subt($arr);
				$arr["parent_tag_name"] = $t_ptn;
				$arr["parent_tag_caption"] = $t_ptc;
			}
		}
	}

	function _get_xml_config_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);
		$t->define_field(array(
			"name" => "xml_source",
			"caption" => t("XML allikas"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "xml_field",
			"caption" => t("XML v&auml;li"),
		));
		$t->define_field(array(
			"name" => "form_field",
			"caption" => t("S&uuml;ndmuste vormi v&auml;li"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "time_field",
			"caption" => t("Toimumisaja vormi v&auml;li"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "time_format",
			"caption" => t("Kuup&auml;eva formaat<br>(vaikimisi Unix timestamp)"),
			"align" => "center",
		));

		// getting properties from cfgform
		$event_form_oid = $arr['obj_inst']->prop("event_form");
		if (!is_oid($event_form_oid))
		{
			return;
		}
		$event_form_obj = obj($event_form_oid);
		$event_form_inst = $event_form_obj->instance();
		$props = $event_form_inst->get_props_from_cfgform(array(
			"id" => $event_form_obj->id(),
		));
		$options = array("do_not_save_into_db" => "-- Ei salvestata --");
		foreach ($props as $value)
		{
			if (empty($value['caption']))
			{
				$value['caption'] = $value['name'];
			}
			$options[$value['name']] = $value['caption'];
		}
		$arr["options"] = $options;

		// get saved xml configuration data
		$saved_xml_conf = $arr['obj_inst']->meta("xml_conf");

		// getting the xml tags from the sources
		$o = obj($arr["request"]["id"]);
		$conns_to_xml_sources = $o->connections_from(array(
			"type" => "RELTYPE_XML_SOURCE",
		));

		foreach($conns_to_xml_sources as $conn_to_xml_source)
		{
			$xml_source = obj($conn_to_xml_source->prop("to"));

			$saved_subtag_table = $xml_source->meta("subtag_table");
			$saved_arguement_table = $xml_source->meta("arguement_table");

			$arr["parent_tag_source"] = $xml_source->name();
			$arr["parent_tag_source_id"] = $xml_source->id();
			$arr["parent_tag_name"] = "root";
			$arr["parent_tag_caption"] = "root";
			$arr["saved_arguement_table"] = $saved_arguement_table;
			$arr["saved_subtag_table"] = $saved_subtag_table;
			$this->subt_subt($arr);
		}
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			// save data from xml configuration table
			case "xml_config_table":
				if (!empty($arr['request']['xml_conf']))
				{
					$arr['obj_inst']->set_meta("xml_conf", $arr['request']['xml_conf']);
				}
				if (!empty($arr['request']['xml_conf_time']))
				{
					$arr['obj_inst']->set_meta("xml_conf_time", $arr['request']['xml_conf_time']);
				}
				if (!empty($arr['request']['xml_conf_time_format']))
				{
					$arr['obj_inst']->set_meta("xml_conf_time_format", $arr['request']['xml_conf_time_format']);
				}
				break;
		}
		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	/////////////
	// Parameters:
	// - UD_xml_source_id: (int) the id of an XML source
	// - UD_start_timestamp: (int) seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).
	//
	function load_xml_content($arr)
	{
		if (!is_oid($arr['UD_xml_source_id']))
		{
			return false;
		}
		$source = obj($arr['UD_xml_source_id']);
		$xml_file_url = $source->prop("url");

		if (empty($xml_file_url))
		{
			return false;
		}

		// The url might me something like http://....?par=var
		$url_params = (strstr($xml_file_url, "?") != "") ? "&" : "?";

		$start_timestamp = $source->prop("start_timestamp");
		$start_timestamp_unix = $source->prop("start_timestamp_unix");
		$url_params .= (!empty($start_timestamp) && !empty($arr["UD_start_timestamp"])) ? $start_timestamp . "=" . $arr["UD_start_timestamp"] . "&" : "";
		$url_params .= (!empty($start_timestamp_unix) && !empty($arr["UD_start_timestamp_unix"])) ? $start_timestamp_unix . "=" . $arr["UD_start_timestamp_unix"] . "&" : "";
		$url_params .= (!empty($arr["UD_lang_param"]) && !empty($arr["UD_lang_value"])) ? $arr["UD_lang_param"] . "=" . $arr["UD_lang_value"] . "&" : "";
		$category = $source->prop("category");
		$url_params .= (!empty($category) && !empty($arr["UD_category"])) ? $category . "=" . $arr["UD_category"] . "&" : "";

		print "<br> &nbsp; &nbsp; - URL used: ". $xml_file_url.$url_params . "<br><br>";
		$f = fopen($xml_file_url.$url_params, "r");
		if ($f === false)
		{
			return false;
		}
		while (!feof($f))
		{
			$xml_file_content .= fread($f, 4096);
		}
		fclose($f);

		return parse_xml_def(array(
			"xml" => $xml_file_content,
		));
	}

/**
	@attrib name=ignore
**/
	function ignore($arr)
	{
		$o = obj($arr["id"]);
		// The field where the data of ignored fields is saved
		if (!is_array($arr["sel"]) && is_array($arr["check"]))
		{
			$arr["sel"] = $arr["check"];
		}
		foreach(safe_array($arr["sel"]) as $ign_obj)
		{
			$obj = obj($ign_obj);

			$event = obj($obj->parent());
			$ignore_fields = $event->meta("igno_fields");
			$ignore_fields .= "," . $obj->prop("field");
			$event->set_meta("igno_fields", $ignore_fields);
			$event->save();

			$obj->delete();
		}
		return  $arr["post_ru"];
	}

/**
	@attrib name=auto_change
**/
	function auto_change($arr)
	{
		$o = obj($arr["id"]);
		// The field where the data of automatically changed fields is saved
		if (!is_array($arr["sel"]) && is_array($arr["check"]))
		{
			$arr["sel"] = $arr["check"];
		}
		foreach(safe_array($arr["sel"]) as $auto_obj)
		{
			$obj = obj($auto_obj);

			$event = obj($obj->parent());
			$auto_fields = $event->meta("auto_fields");
			$auto_fields .= "," . $obj->prop("field");
			if($obj->meta("trans_lang") == "en")
			{
				$all_vals = $event->meta("translations");
				$all_vals[2][$obj->prop("field")] = $obj->prop("content");
				$event->set_meta("translations", $all_vals);
			}
			else
			{
				$event->set_prop($obj->prop("field"), $obj->prop("content"));
			}
			$event->set_meta("auto_fields", $auto_fields);
			$event->save();

			$obj->delete();
		}
		return  $arr["post_ru"];
	}

/**
	@attrib name=make_changes
**/
	function make_changes($arr)
	{
		$o = obj($arr["id"]);
		if (!is_array($arr["sel"]) && is_array($arr["check"]))
		{
			$arr["sel"] = $arr["check"];
		}
		foreach(safe_array($arr["sel"]) as $cha_obj)
		{
			$obj = obj($cha_obj);

			$event = obj($obj->parent());
			if($obj->meta("trans_lang") == "en")
			{
				$all_vals = $event->meta("translations");
				$all_vals[2][$obj->prop("field")] = $obj->prop("content");
				$event->set_meta("translations", $all_vals);
			}
			else
			{
				$event->set_prop($obj->prop("field"), $obj->prop("content"));
			}
			$event->save();

			$obj->delete();
		}
		return  $arr["post_ru"];
	}

/**
	@attrib name=import_events nologin=1
	@param id required type=int acl=view
**/
	function import_events($arr)
	{
		if (!$this->can("view", $arr['id']))
		{
			error::raise(array(
				"msg" => t("You don't have view access to import object!"),
			));
			return $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id());
		}

		$o = obj($arr['id']);

		$event_form_id = $o->prop("event_form");
		if (!$this->can("view", $event_form_id))
		{
			error::raise(array(
				"msg" => t("You don't have view access to eventform object!"),
			));
			return $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id());
		}

		$event_form_obj = obj($event_form_id);

		$class_id = $event_form_obj->prop("subclass");

		$saved_xml_conf = $o->meta("xml_conf");
		$saved_xml_conf_time = $o->meta("xml_conf_time");
		$saved_xml_conf_time_format = $o->meta("xml_conf_time_format");
		$translatable_fields = $o->prop("translatable_fields");
		$last_import = $o->meta("last_import");

		// Gathering the IDs of events already imported
		$imported_events = array();
		$ol = new object_list(array(
//			"parent" => array(),
			"parent" => $arr["id"],
			"class_id" => $class_id,
		));
		if($ol->count() != 0)
		{
			foreach($ol->arr() as $imp_obj)
			{
				$imp_obj_source_id = $imp_obj->meta("source_id");
				if(!empty($imp_obj_source_id))
				{
					$imp_obj_org_id = $imp_obj->meta("original_id");
					$imported_events[$imp_obj_source_id][$imp_obj_org_id] = $imp_obj->id();

				}
			}
		}

		// Gathering the IDs of locations already imported
		$locations = array();
		$ol = new object_list(array(
			"parent" => array(),
			"class_id" => CL_SCM_LOCATION,
		));
		if($ol->count() != 0)
		{
			foreach($ol->arr() as $loc_obj)
			{
				$ids = $loc_obj->meta("orig_ids");
				if(!empty($ids))
				{
					foreach($ids as $source_id => $orig_id)
					{
						$locations[$source_id][$orig_id] = $loc_obj->id();
					}
				}
			}
		}

		// Gathering the IDs of the event_time objects already imported
		$imported_times = array();
		$ol = new object_list(array(
			"parent" => array(),
			"class_id" => CL_EVENT_TIME,
		));
		if($ol->count() != 0)
		{
			foreach($ol->arr() as $time_obj)
			{
				$ids = $time_obj->meta("orig_ids");
				if(!empty($ids))
				{
					foreach($ids as $source_id => $orig_id)
					{
						$imported_times[$source_id][$orig_id] = $time_obj->id();
					}
				}
			}
		}

		$conns_to_xl_sources = $o->connections_from(array(
			"type" => "RELTYPE_XML_SOURCE",
		));

		print "<strong>..:: KULTUUR.INFO EVENTS IMPORT STARTED ::..<br><br></strong>";
		flush();

		foreach($conns_to_xl_sources as $conn_to_xl_source)
		{
			$xml_source_id = $conn_to_xl_source->prop("to");
			$xml_source = obj($xml_source_id);

			print " &nbsp; - <strong>[STARTED]</strong> " . $xml_source->name() . "<br>";
			flush();

			$tag_event = $xml_source->prop("tag_event");
			$tag_id = $xml_source->prop("tag_id");
			$tag_lang = $xml_source->prop("tag_lang");
			$param_lang = $xml_source->prop("language");
			$saved_language_table = $xml_source->meta("language_table");

			$load_xml_content_params = array(
				"UD_xml_source_id" => $xml_source->id(),
				"UD_start_timestamp" => "".date("YmdHis", $o->meta("last_import")),
				"UD_start_timestamp_unix" => $o->meta("last_import"),
			);
			if($o->prop("import_events_all"))
			{
				$load_xml_content_params["UD_start_timestamp"] = "19700101000000";
				$load_xml_content_params["UD_start_timestamp_unix"] = 0;
			}
			$xml_content = $this->load_xml_content($load_xml_content_params);

			if ($xml_content === false)
			{
				print " &nbsp; &nbsp; - <strong>Could not get XML data!</strong><br>";
			}

			foreach($xml_content[0] as $v)
			{
				$xml_tag_levels[$v["level"]] = $v["tag"];

				// We put together the name for the current tag, including its parent tags
				$curtag = "";
				for($a = 0; $a <= $v["level"]; $a++)
				{
					if(!empty($curtag))
					{
						$curtag .= "_";
					}
					$curtag .= $xml_tag_levels[$a];
				}

				if($curtag == $tag_event && $v["type"] == "open")
				{
					$event_data = array();
					unset($event_id);

					$event_time_data = array();
					$time_i = 0;
					$event_time_data[$time_i] = array(
						"start_hour" => 0,
						"start_min" => 0,
						"start_sec" => 0,
						"start_year" => 0,
						"start_mon" => 0,
						"start_day" => 0,
						"end_hour" => 0,
						"end_min" => 0,
						"end_sec" => 0,
						"end_year" => 0,
						"end_mon" => 0,
						"end_day" => 0,
					);
				}

				if(!($curtag == $tag_event && $v["type"] == "close"))
				{
					$cft = $saved_xml_conf_time[$xml_source->id()."_".$curtag];
					if(!empty($saved_xml_conf[$xml_source->id()."_".$curtag]) && $saved_xml_conf[$xml_source->id()."_".$curtag] != "do_not_save_into_db")
					{
						$event_data[$saved_xml_conf[$xml_source->id()."_".$curtag]] = trim($v["value"], " \t\n\r\0");
					}
					if(!empty($cft) && $cft != "do_not_save_into_db")
					{
						if(!empty($event_time_data[$time_i][$cft]))
						{
							$time_i++;
							$event_time_data[$time_i] = array(
								"start_hour" => 0,
								"start_min" => 0,
								"start_sec" => 0,
								"start_year" => 0,
								"start_mon" => 0,
								"start_day" => 0,
								"end_hour" => 0,
								"end_min" => 0,
								"end_sec" => 0,
								"end_year" => 0,
								"end_mon" => 0,
								"end_day" => 0,
							);
						}

						$format = $saved_xml_conf_time_format[$xml_source->id()."_".$curtag];

						switch($cft)
						{
							case "start":
								if(empty($format))
								{
									$event_time_data[$time_i]["start_hour"] = date("H", $v["value"]);
									$event_time_data[$time_i]["start_min"] = date("i", $v["value"]);
									$event_time_data[$time_i]["start_sec"] = date("s", $v["value"]);

									$event_time_data[$time_i]["start_year"] = date("Y", $v["value"]);
									$event_time_data[$time_i]["start_mon"] = date("m", $v["value"]);
									$event_time_data[$time_i]["start_day"] = date("d", $v["value"]);
								}
								else
								{
									if(!(strpos($format, "hh") === false))
									{
										$event_time_data[$time_i]["start_hour"] = substr($v["value"], strpos($format, "hh"), 2);
									}
									if(!(strpos($format, "mm") === false))
									{
										$event_time_data[$time_i]["start_min"] = substr($v["value"], strpos($format, "mm"), 2);
									}
									if(!(strpos($format, "ss") === false))
									{
										$event_time_data[$time_i]["start_sec"] = substr($v["value"], strpos($format, "ss"), 2);
									}

									if(!(strpos($format, "aaaa") === false))
									{
										$event_time_data[$time_i]["start_year"] = substr($v["value"], strpos($format, "aaaa"), 4);
									}
									if(!(strpos($format, "kk") === false))
									{
										$event_time_data[$time_i]["start_mon"] = substr($v["value"], strpos($format, "kk"), 2);
									}
									if(!(strpos($format, "pp") === false))
									{
										$event_time_data[$time_i]["start_day"] = substr($v["value"], strpos($format, "pp"), 2);
									}
								}
								break;

							case "start_date":
								if(empty($format))
								{
									$event_time_data[$time_i]["start_year"] = date("Y", $v["value"]);
									$event_time_data[$time_i]["start_mon"] = date("m", $v["value"]);
									$event_time_data[$time_i]["start_day"] = date("d", $v["value"]);
								}
								else
								{
									if(!(strpos($format, "aaaa") === false))
									{
										$event_time_data[$time_i]["start_year"] = substr($v["value"], strpos($format, "aaaa"), 4);
									}
									if(!(strpos($format, "kk") === false))
									{
										$event_time_data[$time_i]["start_mon"] = substr($v["value"], strpos($format, "kk"), 2);
									}
									if(!(strpos($format, "pp") === false))
									{
										$event_time_data[$time_i]["start_day"] = substr($v["value"], strpos($format, "pp"), 2);
									}
								}
								break;

							case "start_time":
								if(empty($format))
								{
									$event_time_data[$time_i]["start_hour"] = date("H", $v["value"]);
									$event_time_data[$time_i]["start_min"] = date("i", $v["value"]);
									$event_time_data[$time_i]["start_sec"] = date("s", $v["value"]);
								}
								else
								{
									if(!(strpos($format, "hh") === false))
									{
										$event_time_data[$time_i]["start_hour"] = substr($v["value"], strpos($format, "hh"), 2);
									}
									if(!(strpos($format, "mm") === false))
									{
										$event_time_data[$time_i]["start_min"] = substr($v["value"], strpos($format, "mm"), 2);
									}
									if(!(strpos($format, "ss") === false))
									{
										$event_time_data[$time_i]["start_sec"] = substr($v["value"], strpos($format, "ss"), 2);
									}
								}
								break;

							case "end":
								if(empty($format))
								{
									$event_time_data[$time_i]["end_hour"] = date("H", $v["value"]);
									$event_time_data[$time_i]["end_min"] = date("i", $v["value"]);
									$event_time_data[$time_i]["end_sec"] = date("s", $v["value"]);

									$event_time_data[$time_i]["end_year"] = date("Y", $v["value"]);
									$event_time_data[$time_i]["end_mon"] = date("m", $v["value"]);
									$event_time_data[$time_i]["end_day"] = date("d", $v["value"]);
								}
								else
								{
									if(!(strpos($format, "hh") === false))
									{
										$event_time_data[$time_i]["end_hour"] = substr($v["value"], strpos($format, "hh"), 2);
									}
									if(!(strpos($format, "mm") === false))
									{
										$event_time_data[$time_i]["end_min"] = substr($v["value"], strpos($format, "mm"), 2);
									}
									if(!(strpos($format, "ss") === false))
									{
										$event_time_data[$time_i]["end_sec"] = substr($v["value"], strpos($format, "ss"), 2);
									}

									if(!(strpos($format, "aaaa") === false))
									{
										$event_time_data[$time_i]["end_year"] = substr($v["value"], strpos($format, "aaaa"), 4);
									}
									if(!(strpos($format, "kk") === false))
									{
										$event_time_data[$time_i]["end_mon"] = substr($v["value"], strpos($format, "kk"), 2);
									}
									if(!(strpos($format, "pp") === false))
									{
										$event_time_data[$time_i]["end_day"] = substr($v["value"], strpos($format, "pp"), 2);
									}
								}
								break;

							case "end_date":
								if(empty($format))
								{
									$event_time_data[$time_i]["end_year"] = date("Y", $v["value"]);
									$event_time_data[$time_i]["end_mon"] = date("m", $v["value"]);
									$event_time_data[$time_i]["end_day"] = date("d", $v["value"]);
								}
								else
								{
									if(!(strpos($format, "aaaa") === false))
									{
										$event_time_data[$time_i]["end_year"] = substr($v["value"], strpos($format, "aaaa"), 4);
									}
									if(!(strpos($format, "kk") === false))
									{
										$event_time_data[$time_i]["end_mon"] = substr($v["value"], strpos($format, "kk"), 2);
									}
									if(!(strpos($format, "pp") === false))
									{
										$event_time_data[$time_i]["end_day"] = substr($v["value"], strpos($format, "pp"), 2);
									}
								}
								break;

							case "end_time":
								if(empty($format))
								{
									$event_time_data[$time_i]["end_hour"] = date("H", $v["value"]);
									$event_time_data[$time_i]["end_min"] = date("i", $v["value"]);
									$event_time_data[$time_i]["end_sec"] = date("s", $v["value"]);
								}
								else
								{
									if(!(strpos($format, "hh") === false))
									{
										$event_time_data[$time_i]["end_hour"] = substr($v["value"], strpos($format, "hh"), 2);
									}
									if(!(strpos($format, "mm") === false))
									{
										$event_time_data[$time_i]["end_min"] = substr($v["value"], strpos($format, "mm"), 2);
									}
									if(!(strpos($format, "ss") === false))
									{
										$event_time_data[$time_i]["end_sec"] = substr($v["value"], strpos($format, "ss"), 2);
									}
								}
								break;
						}
						$event_time_data[$time_i][$cft] = trim($v["value"], " \t\n\r\0");
					}
					if($curtag == $tag_id)
					{
						$event_id = trim($v["value"], " \t\n\r\0");
					}
					if(!empty($v["attributes"]))
					{
						foreach($v[attributes] as $attr => $attr_value)
						{
							if(!empty($saved_xml_conf[$xml_source->id()."_".$curtag."_args".$attr]) && $saved_xml_conf[$xml_source->id()."_".$curtag."_args".$attr] != "do_not_save_into_db")
							{
								$event_data[$saved_xml_conf[$xml_source->id()."_".$curtag."_args".$attr]] = $attr_value;
							}
							if(!empty($saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr]) && $saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr] != "do_not_save_into_db")
							{
								if(!empty($event_time_data[$time_i][$saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr]]))
								{
									$time_i++;
									$event_time_data[$time_i] = array(
										"start_hour" => 0,
										"start_min" => 0,
										"start_sec" => 0,
										"start_year" => 0,
										"start_mon" => 0,
										"start_day" => 0,
										"end_hour" => 0,
										"end_min" => 0,
										"end_sec" => 0,
										"end_year" => 0,
										"end_mon" => 0,
										"end_day" => 0,
									);
								}

								$format = $saved_xml_conf_time_format[$xml_source->id()."_".$curtag."_args".$attr];

								switch($saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr])
								{
									case "start":
										if(empty($format))
										{
											$event_time_data[$time_i]["start_hour"] = date("H", $attr_value);
											$event_time_data[$time_i]["start_min"] = date("i", $attr_value);
											$event_time_data[$time_i]["start_sec"] = date("s", $attr_value);

											$event_time_data[$time_i]["start_year"] = date("Y", $attr_value);
											$event_time_data[$time_i]["start_mon"] = date("m", $attr_value);
											$event_time_data[$time_i]["start_day"] = date("d", $attr_value);
										}
										else
										{
											if(!(substr($attr_value, strpos($format, "hh")) === false))
											{
												$event_time_data[$time_i]["start_hour"] = substr($attr_value, strpos($format, "hh"), 2);
											}
											if(!(substr($attr_value, strpos($format, "mm")) === false))
											{
												$event_time_data[$time_i]["start_min"] = substr($attr_value, strpos($format, "mm"), 2);
											}
											if(!(substr($attr_value, strpos($format, "ss")) === false))
											{
												$event_time_data[$time_i]["start_sec"] = substr($attr_value, strpos($format, "ss"), 2);
											}

											if(!(substr($attr_value, strpos($format, "aaaa")) === false))
											{
												$event_time_data[$time_i]["start_year"] = substr($attr_value, strpos($format, "aaaa"), 4);
											}
											if(!(substr($attr_value, strpos($format, "kk")) === false))
											{
												$event_time_data[$time_i]["start_mon"] = substr($attr_value, strpos($format, "kk"), 2);
											}
											if(!(substr($attr_value, strpos($format, "pp")) === false))
											{
												$event_time_data[$time_i]["start_day"] = substr($attr_value, strpos($format, "pp"), 2);
											}
										}
										break;

									case "start_date":
										if(empty($format))
										{
											$event_time_data[$time_i]["start_year"] = date("Y", $attr_value);
											$event_time_data[$time_i]["start_mon"] = date("m", $attr_value);
											$event_time_data[$time_i]["start_day"] = date("d", $attr_value);
										}
										else
										{
											if(!(substr($attr_value, strpos($format, "aaaa")) === false))
											{
												$event_time_data[$time_i]["start_year"] = substr($attr_value, strpos($format, "aaaa"), 4);
											}
											if(!(substr($attr_value, strpos($format, "kk")) === false))
											{
												$event_time_data[$time_i]["start_mon"] = substr($attr_value, strpos($format, "kk"), 2);
											}
											if(!(substr($attr_value, strpos($format, "pp")) === false))
											{
												$event_time_data[$time_i]["start_day"] = substr($attr_value, strpos($format, "pp"), 2);
											}
										}
										break;

									case "start_time":
										if(empty($format))
										{
											$event_time_data[$time_i]["start_hour"] = date("H", $attr_value);
											$event_time_data[$time_i]["start_min"] = date("i", $attr_value);
											$event_time_data[$time_i]["start_sec"] = date("s", $attr_value);
										}
										else
										{
											if(!(substr($attr_value, strpos($format, "hh")) === false))
											{
												$event_time_data[$time_i]["start_hour"] = substr($attr_value, strpos($format, "hh"), 2);
											}
											if(!(substr($attr_value, strpos($format, "mm")) === false))
											{
												$event_time_data[$time_i]["start_min"] = substr($attr_value, strpos($format, "mm"), 2);
											}
											if(!(substr($attr_value, strpos($format, "ss")) === false))
											{
												$event_time_data[$time_i]["start_sec"] = substr($attr_value, strpos($format, "ss"), 2);
											}
										}
										break;

									case "end":
										if(empty($format))
										{
											$event_time_data[$time_i]["end_hour"] = date("H", $attr_value);
											$event_time_data[$time_i]["end_min"] = date("i", $attr_value);
											$event_time_data[$time_i]["end_sec"] = date("s", $attr_value);

											$event_time_data[$time_i]["end_year"] = date("Y", $attr_value);
											$event_time_data[$time_i]["end_mon"] = date("m", $attr_value);
											$event_time_data[$time_i]["end_day"] = date("d", $attr_value);
										}
										else
										{
											if(!(substr($attr_value, strpos($format, "hh")) === false))
											{
												$event_time_data[$time_i]["end_hour"] = substr($attr_value, strpos($format, "hh"), 2);
											}
											if(!(substr($attr_value, strpos($format, "mm")) === false))
											{
												$event_time_data[$time_i]["end_min"] = substr($attr_value, strpos($format, "mm"), 2);
											}
											if(!(substr($attr_value, strpos($format, "ss")) === false))
											{
												$event_time_data[$time_i]["end_sec"] = substr($attr_value, strpos($format, "ss"), 2);
											}

											if(!(substr($attr_value, strpos($format, "aaaa")) === false))
											{
												$event_time_data[$time_i]["end_year"] = substr($attr_value, strpos($format, "aaaa"), 4);
											}
											if(!(substr($attr_value, strpos($format, "kk")) === false))
											{
												$event_time_data[$time_i]["end_mon"] = substr($attr_value, strpos($format, "kk"), 2);
											}
											if(!(substr($attr_value, strpos($format, "pp")) === false))
											{
												$event_time_data[$time_i]["end_day"] = substr($attr_value, strpos($format, "pp"), 2);
											}
										}
										break;

									case "end_date":
										if(empty($format))
										{
											$event_time_data[$time_i]["end_year"] = date("Y", $attr_value);
											$event_time_data[$time_i]["end_mon"] = date("m", $attr_value);
											$event_time_data[$time_i]["end_day"] = date("d", $attr_value);
										}
										else
										{
											if(!(substr($attr_value, strpos($format, "aaaa")) === false))
											{
												$event_time_data[$time_i]["end_year"] = substr($attr_value, strpos($format, "aaaa"), 4);
											}
											if(!(substr($attr_value, strpos($format, "kk")) === false))
											{
												$event_time_data[$time_i]["end_mon"] = substr($attr_value, strpos($format, "kk"), 2);
											}
											if(!(substr($attr_value, strpos($format, "pp")) === false))
											{
												$event_time_data[$time_i]["end_day"] = substr($attr_value, strpos($format, "pp"), 2);
											}
										}
										break;

									case "end_time":
										if(empty($format))
										{
											$event_time_data[$time_i]["end_hour"] = date("H", $attr_value);
											$event_time_data[$time_i]["end_min"] = date("i", $attr_value);
											$event_time_data[$time_i]["end_sec"] = date("s", $attr_value);
										}
										else
										{
											if(!(substr($attr_value, strpos($format, "hh")) === false))
											{
												$event_time_data[$time_i]["end_hour"] = substr($attr_value, strpos($format, "hh"), 2);
											}
											if(!(substr($attr_value, strpos($format, "mm")) === false))
											{
												$event_time_data[$time_i]["end_min"] = substr($attr_value, strpos($format, "mm"), 2);
											}
											if(!(substr($attr_value, strpos($format, "ss")) === false))
											{
												$event_time_data[$time_i]["end_sec"] = substr($attr_value, strpos($format, "ss"), 2);
											}
										}
										break;
								}
								$event_time_data[$time_i][$saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr]] = trim($attr_value, " \t\n\r\0");
							}
							if($curtag."_args".$attr == $tag_id)
							{
								$event_id = trim($attr_value, " \t\n\r\0");
							}
						}
					}
				}
				else
				{
					print " &nbsp; &nbsp; - ";

					// saving the event data
					if(!array_key_exists($event_id, $imported_events[$xml_source_id]))
					{ // new event
						print "<strong>[ new ] </strong>";
						$event_obj = new object;
						$event_obj->set_class_id($class_id);
						$event_obj->set_parent($arr["id"]);
						foreach($event_data as $key => $value)
						{
							if(!empty($key))
							{
								$event_obj->set_prop($key, $value);
							}
						}
						$event_obj->set_meta("original_id", $event_id);
						$event_obj->set_meta("source_id", $xml_source_id);
						$event_obj->save();
						print $event_data["name"]." [saved]<br>";
						flush();
						$imported_events[$xml_source_id][$event_id] = $event_obj->id();
					}
					else
					{ // excisting event
						print "[ --- ] ".$event_data["name"]."<br>";
						$event_obj = new object($imported_events[$xml_source_id][$event_id]);

						$change_igno = $event_obj->meta("igno_fields");
						$change_igno = str_replace(" ", "", $change_igno);
						$change_igno = explode(",", $change_igno);

						$change_auto = $event_obj->meta("auto_fields");
						$change_auto = str_replace(" ", "", $change_auto);
						$change_auto = explode(",", $change_auto);

						foreach($event_data as $key => $value)
						{
							if(!empty($key))
							{
								// Check for any updated fields
								if($event_obj->prop($key) != $value)
								{
									if(in_array($key, $change_auto) || !($o->prop("cb_log_changes")))
									{
										$event_obj->set_prop($key, $value);
										$event_obj->save();
										print " &nbsp; &nbsp; &nbsp; -";
										print "- property: ".$key." [changed]<br>";
									}
									else if(in_array($key, $change_igno))
									{
										print " &nbsp; &nbsp; &nbsp; -";
										print "- property: ".$key." [change ignored]<br>";
									}
									else
									{
										$log = new object;
										$log->set_parent($event_obj->id());
										$log->set_class_id(CL_IMPORT_LOG);
										$log->set_prop("name", "");
										$log->set_prop("field", $key);
										$log->set_prop("content", $value);
										$log->set_prop("timestamp", time());
										$log->save();
										print " &nbsp; &nbsp; &nbsp; -";
										print "- property: ".$key." [change logged]<br>";
									}
								}
							}
						}
					}
					flush();
					foreach($event_time_data as $event_time)
					{
						if($event_time["start_day"] != 0 && $event_time["start_mon"] != 0)
						{
							if(array_key_exists($event_time["id"], $imported_times[$xml_source_id]))
							{
								$time_obj = obj($imported_times[$xml_source_id][$event_time["id"]]);
							}
							else
							{
								$time_obj = new object;
								$time_obj->set_class_id(CL_EVENT_TIME);
								$time_obj->set_parent($event_obj->id());
								print "<b>";
							}
							$tmp["start"] = 0;
							$tmp["end"] = 0;
							foreach($event_time as $key => $value)
							{
								if($key == "name" && $value != "")
								{
									$tmp["name"] = $value;
								}

								if($key == "comment" && $value != "")
								{
									$time_obj->set_comment($value);
								}

								if($key == "location_id" && $value != "")
								{
									$tmp["location_id"] = $value;
								}
							}

							if($tmp["location_id"] != "")
							{
								// connect to a location
								if(!array_key_exists($tmp["location_id"], $locations[$xml_source_id]))
								{
									$loc_obj = new object();
									$loc_obj->set_parent($arr["id"]);
									$loc_obj->set_class_id(CL_SCM_LOCATION);
									$loc_obj->set_prop("name", $event_time["location_name"]);
									$loc_obj->set_meta("orig_ids", array($xml_source_id => $tmp["location_id"]));
									$loc_obj->save();

									$locations[$xml_source_id][$tmp["location_id"]] = $loc_obj->id();
								}
								$time_obj->connect(array(
									"to" => $locations[$xml_source_id][$tmp["location_id"]],
									"type" => "RELTYPE_LOCATION",
								));
							}

							if($event_time["end_year"] < 1970)
								$event_time["end_year"] = date("Y");
							$tmp["end"] = mktime(
								$event_time["end_hour"],
								$event_time["end_min"],
								$event_time["end_sec"],
								$event_time["end_mon"],
								$event_time["end_day"],
								$event_time["end_year"]
							);

							if($event_time["start_year"] < 1970)
								$event_time["start_year"] = date("Y");
							$tmp["start"] = mktime(
								$event_time["start_hour"],
								$event_time["start_min"],
								$event_time["start_sec"],
								$event_time["start_mon"],
								$event_time["start_day"],
								$event_time["start_year"]
							);


							$tmp["end"] = ($tmp["end"] > $tmp["start"]) ? $tmp["end"] : $tmp["start"];

							$time_obj->set_prop("name", $tmp["name"]);
							$time_obj->set_prop("start", $tmp["start"]);
							$time_obj->set_prop("end", $tmp["end"]);
							$time_obj->set_meta("orig_ids", array($xml_source_id => $event_time["id"]));
							$time_obj->save();
							print " &nbsp; &nbsp; &nbsp; -";
							print "- event time: ".date("d-m-Y / H:i", $tmp["start"])." - ".date("d-m-Y / H:i", $tmp["end"])." [saved]<br></b>";
							$event_obj->connect(array(
								"to" => $time_obj->id(),
								"type" => "RELTYPE_EVENT_TIME",
							));
						}
					}
					$tmp = array();
				}
			}

			$o->set_meta("last_import", time());
			$o->save();

			print " &nbsp; - <strong>[ENDED]</strong> " . $xml_source->name() . "<br><br>";
			flush();

			if($tag_lang == "tlidbup" && $param_lang != "")
			{
				print " &nbsp; - <strong>[STARTED]</strong> " . $xml_source->name() . " [ENGLISH]<br>";
				flush();

				$load_xml_content_params = array(
					"UD_xml_source_id" => $xml_source->id(),
					"UD_start_timestamp" => "".date("YmdHis", $o->meta("last_import")),
					"UD_start_timestamp_unix" => $o->meta("last_import"),
					"UD_lang_param" => $param_lang,
					"UD_lang_value" => $saved_language_table["en"],
				);
				if($o->prop("import_events_all"))
				{
					$load_xml_content_params["UD_start_timestamp"] = "19700101000000";
					$load_xml_content_params["UD_start_timestamp_unix"] = 0;
				}
				$xml_content = $this->load_xml_content($load_xml_content_params);

				if ($xml_content === false)
				{
					print " &nbsp; &nbsp; - <strong>Could not get XML data!</strong><br>";
				}

				foreach($xml_content[0] as $v)
				{
					$xml_tag_levels[$v["level"]] = $v["tag"];

					// We put together the name for the current tag, including its parent tags
					$curtag = "";
					for($a = 0; $a <= $v["level"]; $a++)
					{
						if(!empty($curtag))
						{
							$curtag .= "_";
						}
						$curtag .= $xml_tag_levels[$a];
					}

					if($curtag == $tag_event && $v["type"] == "open")
					{
						$event_data = array();
						unset($event_id);

						$event_time_data = array();
						$time_i = 0;
						$event_time_data[$time_i] = array(
							"start_hour" => 0,
							"start_min" => 0,
							"start_sec" => 0,
							"start_year" => 0,
							"start_mon" => 0,
							"start_day" => 0,
							"end_hour" => 0,
							"end_min" => 0,
							"end_sec" => 0,
							"end_year" => 0,
							"end_mon" => 0,
							"end_day" => 0,
						);
					}

					if(!($curtag == $tag_event && $v["type"] == "close"))
					{
						$cft = $saved_xml_conf_time[$xml_source->id()."_".$curtag];
						if(!empty($saved_xml_conf[$xml_source->id()."_".$curtag]) && $saved_xml_conf[$xml_source->id()."_".$curtag] != "do_not_save_into_db")
						{
							$event_data[$saved_xml_conf[$xml_source->id()."_".$curtag]] = trim($v["value"], " \t\n\r\0");
						}
						if(!empty($cft) && $cft != "do_not_save_into_db")
						{
							if(!empty($event_time_data[$time_i][$cft]))
							{
								$time_i++;
								$event_time_data[$time_i] = array(
									"start_hour" => 0,
									"start_min" => 0,
									"start_sec" => 0,
									"start_year" => 0,
									"start_mon" => 0,
									"start_day" => 0,
									"end_hour" => 0,
									"end_min" => 0,
									"end_sec" => 0,
									"end_year" => 0,
									"end_mon" => 0,
									"end_day" => 0,
								);
							}

							$format = $saved_xml_conf_time_format[$xml_source->id()."_".$curtag];

							switch($cft)
							{
								case "start":
									if(empty($format))
									{
										$event_time_data[$time_i]["start_hour"] = date("H", $v["value"]);
										$event_time_data[$time_i]["start_min"] = date("i", $v["value"]);
										$event_time_data[$time_i]["start_sec"] = date("s", $v["value"]);

										$event_time_data[$time_i]["start_year"] = date("Y", $v["value"]);
										$event_time_data[$time_i]["start_mon"] = date("m", $v["value"]);
										$event_time_data[$time_i]["start_day"] = date("d", $v["value"]);
									}
									else
									{
										if(!(strpos($format, "hh") === false))
										{
											$event_time_data[$time_i]["start_hour"] = substr($v["value"], strpos($format, "hh"), 2);
										}
										if(!(strpos($format, "mm") === false))
										{
											$event_time_data[$time_i]["start_min"] = substr($v["value"], strpos($format, "mm"), 2);
										}
										if(!(strpos($format, "ss") === false))
										{
											$event_time_data[$time_i]["start_sec"] = substr($v["value"], strpos($format, "ss"), 2);
										}

										if(!(strpos($format, "aaaa") === false))
										{
											$event_time_data[$time_i]["start_year"] = substr($v["value"], strpos($format, "aaaa"), 4);
										}
										if(!(strpos($format, "kk") === false))
										{
											$event_time_data[$time_i]["start_mon"] = substr($v["value"], strpos($format, "kk"), 2);
										}
										if(!(strpos($format, "pp") === false))
										{
											$event_time_data[$time_i]["start_day"] = substr($v["value"], strpos($format, "pp"), 2);
										}
									}
									break;

								case "start_date":
									if(empty($format))
									{
										$event_time_data[$time_i]["start_year"] = date("Y", $v["value"]);
										$event_time_data[$time_i]["start_mon"] = date("m", $v["value"]);
										$event_time_data[$time_i]["start_day"] = date("d", $v["value"]);
									}
									else
									{
										if(!(strpos($format, "aaaa") === false))
										{
											$event_time_data[$time_i]["start_year"] = substr($v["value"], strpos($format, "aaaa"), 4);
										}
										if(!(strpos($format, "kk") === false))
										{
											$event_time_data[$time_i]["start_mon"] = substr($v["value"], strpos($format, "kk"), 2);
										}
										if(!(strpos($format, "pp") === false))
										{
											$event_time_data[$time_i]["start_day"] = substr($v["value"], strpos($format, "pp"), 2);
										}
									}
									break;

								case "start_time":
									if(empty($format))
									{
										$event_time_data[$time_i]["start_hour"] = date("H", $v["value"]);
										$event_time_data[$time_i]["start_min"] = date("i", $v["value"]);
										$event_time_data[$time_i]["start_sec"] = date("s", $v["value"]);
									}
									else
									{
										if(!(strpos($format, "hh") === false))
										{
											$event_time_data[$time_i]["start_hour"] = substr($v["value"], strpos($format, "hh"), 2);
										}
										if(!(strpos($format, "mm") === false))
										{
											$event_time_data[$time_i]["start_min"] = substr($v["value"], strpos($format, "mm"), 2);
										}
										if(!(strpos($format, "ss") === false))
										{
											$event_time_data[$time_i]["start_sec"] = substr($v["value"], strpos($format, "ss"), 2);
										}
									}
									break;

								case "end":
									if(empty($format))
									{
										$event_time_data[$time_i]["end_hour"] = date("H", $v["value"]);
										$event_time_data[$time_i]["end_min"] = date("i", $v["value"]);
										$event_time_data[$time_i]["end_sec"] = date("s", $v["value"]);

										$event_time_data[$time_i]["end_year"] = date("Y", $v["value"]);
										$event_time_data[$time_i]["end_mon"] = date("m", $v["value"]);
										$event_time_data[$time_i]["end_day"] = date("d", $v["value"]);
									}
									else
									{
										if(!(strpos($format, "hh") === false))
										{
											$event_time_data[$time_i]["end_hour"] = substr($v["value"], strpos($format, "hh"), 2);
										}
										if(!(strpos($format, "mm") === false))
										{
											$event_time_data[$time_i]["end_min"] = substr($v["value"], strpos($format, "mm"), 2);
										}
										if(!(strpos($format, "ss") === false))
										{
											$event_time_data[$time_i]["end_sec"] = substr($v["value"], strpos($format, "ss"), 2);
										}

										if(!(strpos($format, "aaaa") === false))
										{
											$event_time_data[$time_i]["end_year"] = substr($v["value"], strpos($format, "aaaa"), 4);
										}
										if(!(strpos($format, "kk") === false))
										{
											$event_time_data[$time_i]["end_mon"] = substr($v["value"], strpos($format, "kk"), 2);
										}
										if(!(strpos($format, "pp") === false))
										{
											$event_time_data[$time_i]["end_day"] = substr($v["value"], strpos($format, "pp"), 2);
										}
									}
									break;

								case "end_date":
									if(empty($format))
									{
										$event_time_data[$time_i]["end_year"] = date("Y", $v["value"]);
										$event_time_data[$time_i]["end_mon"] = date("m", $v["value"]);
										$event_time_data[$time_i]["end_day"] = date("d", $v["value"]);
									}
									else
									{
										if(!(strpos($format, "aaaa") === false))
										{
											$event_time_data[$time_i]["end_year"] = substr($v["value"], strpos($format, "aaaa"), 4);
										}
										if(!(strpos($format, "kk") === false))
										{
											$event_time_data[$time_i]["end_mon"] = substr($v["value"], strpos($format, "kk"), 2);
										}
										if(!(strpos($format, "pp") === false))
										{
											$event_time_data[$time_i]["end_day"] = substr($v["value"], strpos($format, "pp"), 2);
										}
									}
									break;

								case "end_time":
									if(empty($format))
									{
										$event_time_data[$time_i]["end_hour"] = date("H", $v["value"]);
										$event_time_data[$time_i]["end_min"] = date("i", $v["value"]);
										$event_time_data[$time_i]["end_sec"] = date("s", $v["value"]);
									}
									else
									{
										if(!(strpos($format, "hh") === false))
										{
											$event_time_data[$time_i]["end_hour"] = substr($v["value"], strpos($format, "hh"), 2);
										}
										if(!(strpos($format, "mm") === false))
										{
											$event_time_data[$time_i]["end_min"] = substr($v["value"], strpos($format, "mm"), 2);
										}
										if(!(strpos($format, "ss") === false))
										{
											$event_time_data[$time_i]["end_sec"] = substr($v["value"], strpos($format, "ss"), 2);
										}
									}
									break;
							}
							$event_time_data[$time_i][$cft] = trim($v["value"], " \t\n\r\0");
						}
						if($curtag == $tag_id)
						{
							$event_id = trim($v["value"], " \t\n\r\0");
						}
						if(!empty($v["attributes"]))
						{
							foreach($v[attributes] as $attr => $attr_value)
							{
								if(!empty($saved_xml_conf[$xml_source->id()."_".$curtag."_args".$attr]) && $saved_xml_conf[$xml_source->id()."_".$curtag."_args".$attr] != "do_not_save_into_db")
								{
									$event_data[$saved_xml_conf[$xml_source->id()."_".$curtag."_args".$attr]] = $attr_value;
								}
								if(!empty($saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr]) && $saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr] != "do_not_save_into_db")
								{
									if(!empty($event_time_data[$time_i][$saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr]]))
									{
										$time_i++;
										$event_time_data[$time_i] = array(
											"start_hour" => 0,
											"start_min" => 0,
											"start_sec" => 0,
											"start_year" => 0,
											"start_mon" => 0,
											"start_day" => 0,
											"end_hour" => 0,
											"end_min" => 0,
											"end_sec" => 0,
											"end_year" => 0,
											"end_mon" => 0,
											"end_day" => 0,
										);
									}

									$format = $saved_xml_conf_time_format[$xml_source->id()."_".$curtag."_args".$attr];

									switch($saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr])
									{
										case "start":
											if(empty($format))
											{
												$event_time_data[$time_i]["start_hour"] = date("H", $attr_value);
												$event_time_data[$time_i]["start_min"] = date("i", $attr_value);
												$event_time_data[$time_i]["start_sec"] = date("s", $attr_value);

												$event_time_data[$time_i]["start_year"] = date("Y", $attr_value);
												$event_time_data[$time_i]["start_mon"] = date("m", $attr_value);
												$event_time_data[$time_i]["start_day"] = date("d", $attr_value);
											}
											else
											{
												if(!(substr($attr_value, strpos($format, "hh")) === false))
												{
													$event_time_data[$time_i]["start_hour"] = substr($attr_value, strpos($format, "hh"), 2);
												}
												if(!(substr($attr_value, strpos($format, "mm")) === false))
												{
													$event_time_data[$time_i]["start_min"] = substr($attr_value, strpos($format, "mm"), 2);
												}
												if(!(substr($attr_value, strpos($format, "ss")) === false))
												{
													$event_time_data[$time_i]["start_sec"] = substr($attr_value, strpos($format, "ss"), 2);
												}

												if(!(substr($attr_value, strpos($format, "aaaa")) === false))
												{
													$event_time_data[$time_i]["start_year"] = substr($attr_value, strpos($format, "aaaa"), 4);
												}
												if(!(substr($attr_value, strpos($format, "kk")) === false))
												{
													$event_time_data[$time_i]["start_mon"] = substr($attr_value, strpos($format, "kk"), 2);
												}
												if(!(substr($attr_value, strpos($format, "pp")) === false))
												{
													$event_time_data[$time_i]["start_day"] = substr($attr_value, strpos($format, "pp"), 2);
												}
											}
											break;

										case "start_date":
											if(empty($format))
											{
												$event_time_data[$time_i]["start_year"] = date("Y", $attr_value);
												$event_time_data[$time_i]["start_mon"] = date("m", $attr_value);
												$event_time_data[$time_i]["start_day"] = date("d", $attr_value);
											}
											else
											{
												if(!(substr($attr_value, strpos($format, "aaaa")) === false))
												{
													$event_time_data[$time_i]["start_year"] = substr($attr_value, strpos($format, "aaaa"), 4);
												}
												if(!(substr($attr_value, strpos($format, "kk")) === false))
												{
													$event_time_data[$time_i]["start_mon"] = substr($attr_value, strpos($format, "kk"), 2);
												}
												if(!(substr($attr_value, strpos($format, "pp")) === false))
												{
													$event_time_data[$time_i]["start_day"] = substr($attr_value, strpos($format, "pp"), 2);
												}
											}
											break;

										case "start_time":
											if(empty($format))
											{
												$event_time_data[$time_i]["start_hour"] = date("H", $attr_value);
												$event_time_data[$time_i]["start_min"] = date("i", $attr_value);
												$event_time_data[$time_i]["start_sec"] = date("s", $attr_value);
											}
											else
											{
												if(!(substr($attr_value, strpos($format, "hh")) === false))
												{
													$event_time_data[$time_i]["start_hour"] = substr($attr_value, strpos($format, "hh"), 2);
												}
												if(!(substr($attr_value, strpos($format, "mm")) === false))
												{
													$event_time_data[$time_i]["start_min"] = substr($attr_value, strpos($format, "mm"), 2);
												}
												if(!(substr($attr_value, strpos($format, "ss")) === false))
												{
													$event_time_data[$time_i]["start_sec"] = substr($attr_value, strpos($format, "ss"), 2);
												}
											}
											break;

										case "end":
											if(empty($format))
											{
												$event_time_data[$time_i]["end_hour"] = date("H", $attr_value);
												$event_time_data[$time_i]["end_min"] = date("i", $attr_value);
												$event_time_data[$time_i]["end_sec"] = date("s", $attr_value);

												$event_time_data[$time_i]["end_year"] = date("Y", $attr_value);
												$event_time_data[$time_i]["end_mon"] = date("m", $attr_value);
												$event_time_data[$time_i]["end_day"] = date("d", $attr_value);
											}
											else
											{
												if(!(substr($attr_value, strpos($format, "hh")) === false))
												{
													$event_time_data[$time_i]["end_hour"] = substr($attr_value, strpos($format, "hh"), 2);
												}
												if(!(substr($attr_value, strpos($format, "mm")) === false))
												{
													$event_time_data[$time_i]["end_min"] = substr($attr_value, strpos($format, "mm"), 2);
												}
												if(!(substr($attr_value, strpos($format, "ss")) === false))
												{
													$event_time_data[$time_i]["end_sec"] = substr($attr_value, strpos($format, "ss"), 2);
												}

												if(!(substr($attr_value, strpos($format, "aaaa")) === false))
												{
													$event_time_data[$time_i]["end_year"] = substr($attr_value, strpos($format, "aaaa"), 4);
												}
												if(!(substr($attr_value, strpos($format, "kk")) === false))
												{
													$event_time_data[$time_i]["end_mon"] = substr($attr_value, strpos($format, "kk"), 2);
												}
												if(!(substr($attr_value, strpos($format, "pp")) === false))
												{
													$event_time_data[$time_i]["end_day"] = substr($attr_value, strpos($format, "pp"), 2);
												}
											}
											break;

										case "end_date":
											if(empty($format))
											{
												$event_time_data[$time_i]["end_year"] = date("Y", $attr_value);
												$event_time_data[$time_i]["end_mon"] = date("m", $attr_value);
												$event_time_data[$time_i]["end_day"] = date("d", $attr_value);
											}
											else
											{
												if(!(substr($attr_value, strpos($format, "aaaa")) === false))
												{
													$event_time_data[$time_i]["end_year"] = substr($attr_value, strpos($format, "aaaa"), 4);
												}
												if(!(substr($attr_value, strpos($format, "kk")) === false))
												{
													$event_time_data[$time_i]["end_mon"] = substr($attr_value, strpos($format, "kk"), 2);
												}
												if(!(substr($attr_value, strpos($format, "pp")) === false))
												{
													$event_time_data[$time_i]["end_day"] = substr($attr_value, strpos($format, "pp"), 2);
												}
											}
											break;

										case "end_time":
											if(empty($format))
											{
												$event_time_data[$time_i]["end_hour"] = date("H", $attr_value);
												$event_time_data[$time_i]["end_min"] = date("i", $attr_value);
												$event_time_data[$time_i]["end_sec"] = date("s", $attr_value);
											}
											else
											{
												if(!(substr($attr_value, strpos($format, "hh")) === false))
												{
													$event_time_data[$time_i]["end_hour"] = substr($attr_value, strpos($format, "hh"), 2);
												}
												if(!(substr($attr_value, strpos($format, "mm")) === false))
												{
													$event_time_data[$time_i]["end_min"] = substr($attr_value, strpos($format, "mm"), 2);
												}
												if(!(substr($attr_value, strpos($format, "ss")) === false))
												{
													$event_time_data[$time_i]["end_sec"] = substr($attr_value, strpos($format, "ss"), 2);
												}
											}
											break;
									}
									$event_time_data[$time_i][$saved_xml_conf_time[$xml_source->id()."_".$curtag."_args".$attr]] = trim($attr_value, " \t\n\r\0");
								}
								if($curtag."_args".$attr == $tag_id)
								{
									$event_id = trim($attr_value, " \t\n\r\0");
								}
							}
						}
					}
					else
					{
						print " &nbsp; &nbsp; - ";

						// saving the event data
						if(!array_key_exists($event_id, $imported_events[$xml_source_id]))
						{ // new event
							print "<strong>[ new ] </strong>";
							$event_obj = new object;
							$event_obj->set_class_id($class_id);
							$event_obj->set_parent($arr["id"]);
							foreach($event_data as $key => $value)
							{
								if(!empty($key))
								{
									$event_obj->set_prop($key, $value);
									if(in_array($key, $translatable_fields))
									{
										$all_vals[2][$key] = $value;
									}
								}
							}
							$event_obj->set_meta("original_id", $event_id);
							$event_obj->set_meta("source_id", $xml_source_id);
							$event_obj->set_meta("translations", $all_vals);
							$event_obj->save();
							print $event_data["name"]." [saved]<br>";
							flush();
							$imported_events[$xml_source_id][$event_id] = $event_obj->id();
						}
						else
						{ // existing event
							print "[ --- ] ".$event_data["name"]."<br>";
							$event_obj = new object($imported_events[$xml_source_id][$event_id]);

							// Gettin' the already existing translations
							$all_vals = $event_obj->meta("translations");

							$change_igno = $event_obj->meta("igno_fields");
							$change_igno = str_replace(" ", "", $change_igno);
							$change_igno = explode(",", $change_igno);

							$change_auto = $event_obj->meta("auto_fields");
							$change_auto = str_replace(" ", "", $change_auto);
							$change_auto = explode(",", $change_auto);

							foreach($event_data as $key => $value)
							{
								if(!empty($key))
								{
									// Check for any updated fields
									if($all_vals[2][$key] != $value && in_array($key, $translatable_fields))
									{
										if(in_array($key, $change_auto) || !($o->prop("cb_log_changes")))
										{
											$all_vals[2][$key] =  $value;
											print " &nbsp; &nbsp; &nbsp; -";
											print "- property: ".$key." [changed]<br>";
										}
										else if(in_array($key, $change_igno))
										{
											print " &nbsp; &nbsp; &nbsp; -";
											print "- property: ".$key." [change ignored]<br>";
										}
										else
										{
											$log = new object;
											$log->set_parent($event_obj->id());
											$log->set_class_id(CL_IMPORT_LOG);
											$log->set_meta("trans_lang", "en");
											$log->set_prop("name", "");
											$log->set_prop("field", $key);
											$log->set_prop("content", $value);
											$log->set_prop("timestamp", time());
											$log->save();
											print " &nbsp; &nbsp; &nbsp; -";
											print "- property: ".$key." [change logged]<br>";
										}
									}
								}
							}
							$event_obj->set_meta("translations", $all_vals);
						}
						flush();
						foreach($event_time_data as $event_time)
						{
							if($event_time["start_day"] != 0 && $event_time["start_mon"] != 0)
							{
								if(array_key_exists($event_time["id"], $imported_times[$xml_source_id]))
								{
									$time_obj = obj($imported_times[$xml_source_id][$event_time["id"]]);
								}
								else
								{
									$time_obj = new object;
									$time_obj->set_class_id(CL_EVENT_TIME);
									$time_obj->set_parent($event_obj->id());
									print "<b>";
								}
								$tmp["start"] = 0;
								$tmp["end"] = 0;
								foreach($event_time as $key => $value)
								{
									if($key == "name" && $value != "")
									{
										$tmp["name"] = $value;
									}

									if($key == "comment" && $value != "")
									{
										$time_obj->set_comment($value);
									}

									if($key == "location_id" && $value != "")
									{
										$tmp["location_id"] = $value;
									}
								}

								if($tmp["location_id"] != "")
								{
									// connect to a location
									if(!array_key_exists($tmp["location_id"], $locations[$xml_source_id]))
									{
										$loc_obj = new object();
										$loc_obj->set_parent($arr["id"]);
										$loc_obj->set_class_id(CL_SCM_LOCATION);
										$loc_obj->set_prop("name", $event_time["location_name"]);
										$loc_obj->set_meta("orig_ids", array($xml_source_id => $tmp["location_id"]));
										$loc_obj->save();

										$locations[$xml_source_id][$tmp["location_id"]] = $loc_obj->id();
									}
									$time_obj->connect(array(
										"to" => $locations[$xml_source_id][$tmp["location_id"]],
										"type" => "RELTYPE_LOCATION",
									));
								}

								if($event_time["end_year"] < 1970)
									$event_time["end_year"] = date("Y");
								$tmp["end"] = mktime(
									$event_time["end_hour"],
									$event_time["end_min"],
									$event_time["end_sec"],
									$event_time["end_mon"],
									$event_time["end_day"],
									$event_time["end_year"]
								);

								if($event_time["start_year"] < 1970)
									$event_time["start_year"] = date("Y");
								$tmp["start"] = mktime(
									$event_time["start_hour"],
									$event_time["start_min"],
									$event_time["start_sec"],
									$event_time["start_mon"],
									$event_time["start_day"],
									$event_time["start_year"]
								);


								$tmp["end"] = ($tmp["end"] > $tmp["start"]) ? $tmp["end"] : $tmp["start"];

								$time_obj->set_prop("name", $tmp["name"]);
								$time_obj->set_prop("start", $tmp["start"]);
								$time_obj->set_prop("end", $tmp["end"]);
								$time_obj->set_meta("orig_ids", array($xml_source_id => $event_time["id"]));
								$time_obj->save();
								print " &nbsp; &nbsp; &nbsp; -";
								print "- event time: ".date("d-m-Y / H:i", $tmp["start"])." - ".date("d-m-Y / H:i", $tmp["end"])." [saved]<br></b>";
								$event_obj->connect(array(
									"to" => $time_obj->id(),
									"type" => "RELTYPE_EVENT_TIME",
								));
							}
						}
						$tmp = array();
					}
				}

				$o->set_meta("last_import", time());
				$o->save();

				print " &nbsp; - <strong>[ENDED]</strong> " . $xml_source->name() . "<br><br>";
				flush();
			}
		}



		print "<strong>..:: KULTUUR.INFO EVENTS IMPORT ENDED ::..<br><br></strong>";
		flush();

		return $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id());
	}

	// this function checks if there is a recurrence object configured
	// to otv_ds_kultuuriaken import
	// if it is then put it in scheduler
	//
	// returns the timestamp of next import
	function activate_next_auto_import($arr)
	{
		$o = $arr['object'];
                if (is_oid($o->prop("recurrence")))
                {
                        $auto_import_user = $o->prop("auto_import_user");
                        $auto_import_passwd = $o->prop("auto_import_passwd");
                        if ($auto_import_user != "" && $auto_import_passwd != "")
                        {

                                $recurrence_inst = get_instance(CL_RECURRENCE);
                                $next = $recurrence_inst->get_next_event(array(
                                        "id" => $o->prop("recurrence")
                                ));
                                if ($next)
                                {
                                        // add to scheduler
                                        $sc = get_instance("scheduler");
                                        $sc->add(array(
                                                "event" => $this->mk_my_orb("import_events", array("id" => $o->id())),
                                                "time" => $next,
                                                "uid" => $auto_import_user,
                                                "password" => $auto_import_passwd,
                                        ));
                                }
                        }
                }

		return $next;

	}
}
?>
