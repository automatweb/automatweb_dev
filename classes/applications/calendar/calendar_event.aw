<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/calendar_event.aw,v 1.30 2007/09/19 14:40:57 markop Exp $
// calendar_event.aw - Kalendri sündmus
/*
@classinfo syslog_type=ST_CALENDAR_EVENT relationmgr=yes

@default group=general
@default table=planner

@property jrk type=textbox size=4 table=objects
@caption Jrk

@property start1 type=datetime_select field=start
@caption Algab

@property end type=datetime_select field=end
@caption Lõpeb

@property project_selector type=project_selector store=no group=projects all_projects=1
@caption Projektid

@property project_selector2 type=project_selector store=no group=projects22 all_projects=1
@caption Projektid 2

@property utextbox1 type=textbox
@caption

@property utextbox2 type=textbox
@caption

@property utextbox3 type=textbox
@caption

@property utextbox4 type=textbox
@caption

@property utextbox5 type=textbox
@caption

@property utextbox6 type=textbox
@caption

@property utextbox7 type=textbox
@caption

@property utextbox8 type=textbox
@caption

@property utextbox9 type=textbox
@caption

@property utextbox10 type=textbox
@caption

@property utextarea2 type=textarea
@caption

@property utextarea3 type=textarea
@caption

@property utextarea4 type=textarea
@caption

@property utextarea5 type=textarea
@caption

@property utextvar1 type=classificator
@caption

@property utextvar2 type=classificator
@caption

@property utextvar3 type=classificator
@caption

@property utextvar4 type=classificator
@caption

@property utextvar5 type=classificator
@caption

@property utextvar6 type=classificator
@caption

@property utextvar7 type=classificator
@caption

@property utextvar8 type=classificator
@caption

@property utextvar9 type=classificator
@caption

@property utextvar10 type=classificator store=connect reltype=RELTYPE_UTEXTVAR10
@caption

@property ufupload1 type=fileupload
@caption Faili upload 1

@property title type=textarea field=title
@caption Sissejuhatus



@property short_description type=textarea allow_rte=2 field=user1
@caption L&uuml;hikirjeldus

@property description type=textarea allow_rte=2 field=user2
@caption Kirjeldus

property url type=releditor table=objects field=meta method=serialize reltype=RELTYPE_URL use_form=emb rel_id=first
caption S&uuml;ndmuse kodulehek&uuml;lg

@property sector type=relpicker multiple=1 reltype=RELTYPE_SECTOR method=serialize field=meta table=objects automatic=1 size=10
@caption Valdkonnad

@property location type=popup_search d=aw_customer reltype=RELTYPE_LOCATION clid=CL_SCM_LOCATION style=autocomplete field=ucheck5 no_edit=1
@caption Toimumiskoht

@property organizer type=popup_search d=aw_customer reltype=RELTYPE_ORGANIZER clid=CL_CRM_COMPANY style=autocomplete method=serialize field=meta table=objects no_edit=1
@caption Korraldaja

@property make_copy store=no type=checkbox ch_value=1
@caption Tee koopia

@property level type=select field=level field=ucheck4
@caption Tase

@property published type=checkbox field=ucheck2
@caption Avaldatud

@property front_event type=checkbox field=ucheck3
@caption Esilehe s&uuml;ndmus

@property event_time type=relpicker reltype=RELTYPE_EVENT_TIME store=connect
@caption Toimumisaeg

@property event_time_table type=table no_caption=1 store=no
@caption Toimumisaegade tabel


@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

@default field=meta
@default method=serialize
@default table=objects

@property uimage1 type=releditor reltype=RELTYPE_PICTURE rel_id=first use_form=emb
@caption

@property seealso type=relpicker reltype=RELTYPE_SEEALSO
@caption

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly,
@caption Kordused

@groupinfo projects caption="Projektid"
@groupinfo recurrence caption=Kordumine


@tableinfo planner index=id master_table=objects master_index=brother_of

@groupinfo transl caption=T&otilde;lgi
@default group=transl
	
	@property transl type=callback callback=callback_get_transl store=no
	@caption T&otilde;lgi


@reltype PICTURE value=1 clid=CL_IMAGE
@caption Pilt

@reltype SEEALSO value=2 clid=CL_DOCUMENT
@caption Vaata lisaks

@reltype RECURRENCE value=3 clid=CL_RECURRENCE
@caption Kordus

@reltype UTEXTVAR10 value=4 clid=CL_META
@caption RELTYPE_UTEXTVAR10

@reltype SECTOR value=5 clid=CL_CRM_SECTOR
@caption Tegevusala

@reltype ORGANIZER value=6 clid=CL_CRM_COMPANY
@caption Korraldaja

@reltype URL value=7 clid=CL_URL
@caption Korraldaja

@reltype LOCATION value=8 clid=CL_SCM_LOCATION
@caption Toimumiskoht

@reltype EVENT_TIME value=9 clid=CL_EVENT_TIME
@caption Toimumisaeg

*/


class calendar_event extends class_base
{
	function calendar_event()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/calendar_event",
			"clid" => CL_CALENDAR_EVENT
		));

		$this->level_options = array("&Uuml;leriikliku t&auml;htsusega", "Kohaliku t&auml;htsusega","V&auml;lismaal toimuv");
		$this->trans_props = array(
			"name", "title",  "short_description", "description"
		);
	}

	function do_db_upgrade($tbl, $field, $q, $err)
	{
		if ("planner" == $tbl)
		{
			switch($field)
			{
				case "level":
					$this->db_add_col($tbl, array(
						"name" => $field,
						"type" => "int",
					));
					return true;
			}
		}
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "event_time":
			case "make_copy":
				return PROP_IGNORE;
			case "event_time_table":
				$this->id = $arr["obj_inst"]->id();
				$this->save_event_times($arr["request"]["event_time"]);
				break;
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;
		}
		$meta = $arr["obj_inst"]->meta();
		if (substr($prop["name"],0,1) == "u")
		{
			if ($meta[$prop["name"]])
			{
				$arr["obj_inst"]->set_meta($prop["name"],"");
			};
		};
		return $retval;
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
	//		return false;
		}
		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	function get_property($arr)
	{
		$retval = PROP_OK;
		$prop = &$arr["prop"];
		switch($prop["name"])
		{
			case "level":
				$prop["options"] = $this->level_options;
				break;
			case "event_time":
				return PROP_IGNORE;
			case "event_time_table":
				$this->do_event_time_table($arr);
				break;
		}
		if ($arr["obj_inst"])
		{
			$meta = $arr["obj_inst"]->meta();
			if (substr($prop["name"],0,1) == "u")
			{
				if (!empty($meta[$prop["name"]]))
				{
					$prop["value"] = $meta[$prop["name"]];
				};
			};
		};
		return $retval;
	}

	function check_format($t)
	{
		if(!$t["start"] || !$t["location"])
		{
			return null;
		}
		$start_dt = explode(" ", $t["start"]);
		$end_dt = explode(" ", $t["end"]);

		$start_d = explode(".", $start_dt[0]);
		$end_d = explode(".", $end_dt[0]);

		$start_t = explode(":", $start_dt[1]);
		$end_t = explode(":",$end_dt[1]);

		if(!($start_d[0] > -1 && $start_d[0] < 32 && $start_d[1] > 0 && $start_d[1] < 13 && $start_d[2] > 100 && $start_d[2] < 3000 && $start_t[0] > -1 && $start_t[0]< 61 && $start_t[1] > -1 && $start_t[0] <61)) return t("Algusaeg ei vasta formaadile");
		if(!($end_d[0] > -1 && $end_d[0] < 32 && $end_d[1] > 0 && $end_d[1] < 13 && $end_d[2] > 100 && $end_d[2] < 3000 && $end_t[0] > -1 && $end_t[0]< 61 && $end_t[1] > -1 && $end_t[0] <61)) return t("L&otilde;puaeg ei vasta formaadile");

		return null;
	}

	function save_event_times($times)
	{
		$event = obj($this->id);
		foreach($times as $id => $val)
		{
			$error[$id] = $this->check_format($val);
			if(is_oid($id) && $this->can("view" , $id))
			{
				$o = obj($id);
			}
			else
			{
				if($val["start"] && $val["location"])
				{
					$o = new object();
					$o->set_name("");
					$o->set_class_id(CL_EVENT_TIME);
					$o->set_parent($this->id);
				}
				else
				{
					continue;
				}
			}
			$loc_list = new object_list(array(
				"lang_id" => array(),
				"site_id" => array(),
				"name" => $val["location"],
				"class_id" => CL_SCM_LOCATION,
			));
			if($loc_list->count())
			{
				$location = reset($loc_list->arr());
			}
			else
			{
//				$location = new object();
//				$location->set_name($val["location"]);
//				$location->set_class_id(CL_SCM_LOCATION);
//				$location->set_parent($id);
				$error[$id] = t("Sellist toimumiskohta pole");
			}
			
			$start_dt = explode(" ", $val["start"]);
			$end_dt = explode(" ", $val["end"]);
			$start_d = explode(".", $start_dt[0]);
			$end_d = explode(".", $end_dt[0]);
			$start_t = explode(":", $start_dt[1]);
			$end_t = explode(":",$end_dt[1]);

			if(!$error[$id])
			{
				$o->set_prop("start" , mktime($start_t[0] , $start_t[1] , 0  , $start_d[1],$start_d[0],$start_d[2]));
				$o->set_prop("end" , mktime($end_t[0] , $end_t[1] , 0 ,$end_d[1],$end_d[0],$end_d[2]));
				$o->set_prop("location" , $location->id());
				$o->save();
				$event->connect(array("to" => $o->id(), "reltype" => 9));
			}
		}
		$_SESSION["event_time_save_errors"] = $error;
	}

	function do_event_time_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "start",
			"caption" => t("Algus"),
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("L&otilde;pp"),
		));
		$t->define_field(array(
			"name" => "location",
			"caption" => t("Asukoht"),
		));
		$t->define_field(array(
			"name" => "delete",
			"caption" => "X",
		));
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_EVENT_TIME")) as $c)
		{
			$o = $c->to();
			$t->define_data(array(
				"start" => html::textbox(array(
					"name" => "event_time[".$o->id()."][start]",
					"size" => 15,
					"value" => date("d.m.Y H:i" , $o->prop("start")),
				)).'<a href="javascript:void(0);" onClick="var cal = new CalendarPopup();
cal.select(changeform.event_time_'.$o->id().'__start_,\'anchornew\',\'dd.MM.yyyy HH:mm\'); return false;" title="Vali kuupäev" name="anchornew" id="anchornew">vali</a><font color=red> '.$_SESSION["event_time_save_errors"][$o->id()]." </font>",
				"end" => html::textbox(array(
					"name" => "event_time[".$o->id()."][end]",
					"size" => 15,
					"value" => date("d.m.Y H:i" , $o->prop("end")),
				)).'<a href="javascript:void(0);" onClick="var cal = new CalendarPopup();
cal.select(changeform.event_time_'.$o->id().'__end_,\'anchornew\',\'dd.MM.yyyy HH:mm\'); return false;" title="Vali kuupäev" name="anchornew" id="anchornew">vali</a>',
				"location" => html::textbox(array(
					"name" => "event_time[".$o->id()."][location]",
					"size" => 30,
					"value" => $o->prop("location.name"),
					"autocomplete_source" => $this->mk_my_orb ("locations_autocomplete_source", array(), CL_CALENDAR_EVENT, false, true),
					"autocomplete_params" => "event_time[".$o->id()."][location]",
				)),
				"delete" => html::href(array(
					"caption" => t("Kustuta"),
					"url" =>  $this->mk_my_orb("remove_event_time", array(
						"id" => $o->id(),
						"return_url" => get_ru(),
					)),
				)),
			));
		}

		$t->define_data(array(
			"start" => html::textbox(array(
					"name" => "event_time[new][start]",
					"size" => 15,
					"value" => "",
				)).'<a href="javascript:void(0);" onClick="var cal = new CalendarPopup();
cal.select(changeform.event_time_new__start_,\'anchornew\',\'dd.MM.yyyy HH:mm\'); return false;" title="Vali kuupäev" name="anchornew" id="anchornew">vali</a><font color=red> '.$_SESSION["event_time_save_errors"]["new"]." </font>",
			"end" => html::textbox(array(
					"name" => "event_time[new][end]",
					"size" => 15,
					"value" => "",
				)).'<a href="javascript:void(0);" onClick="var cal = new CalendarPopup();
cal.select(changeform.event_time_new__end_,\'anchornew\',\'dd.MM.yyyy HH:mm\'); return false;" title="Vali kuupäev" name="anchornew" id="anchornew">vali</a>',
			"location" => html::textbox(array(
				"name" => "event_time[new][location]",
				"size" => 30,
				"value" => $arr["obj_inst"]->prop("location") ? $arr["obj_inst"]->prop("location.name"):"",
				"autocomplete_source" => $this->mk_my_orb ("locations_autocomplete_source", array(), CL_CALENDAR_EVENT, false, true),
				"autocomplete_params" => "event_time[new][location]",
			)),
		));
		unset($_SESSION["event_time_save_errors"]);
	}

	/**
		@attrib name=locations_autocomplete_source
		@param location optional
	**/
	function locations_autocomplete_source($arr)
	{
		$ac = get_instance("vcl/autocomplete");
		$arr = $ac->get_ac_params($arr);

		$ol = new object_list(array(
			"class_id" => CL_SCM_LOCATION,
			"name" => "%".$arr["location"]."%",
			"lang_id" => array(),
			"site_id" => array(),
			"limit" => 2000,
		));
		return $ac->finish_ac($ol->names());
	}

	/**
		@attrib name=remove_event_time
		@param id required
		@param return_url required
	**/
	function delete_items($arr)
	{
		extract($arr);
		$o = obj($id);
		$o->delete();
		return $return_url;
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

	function request_execute($o)
	{
		//if ($_GET["exp"] == 1)
		//{
			return $this->show2(array("id" => $o->id()));
		//}
		//else
		//{
		//	return $this->show(array("id" => $o->id()));
		//};
	}

	function show2($arr)
	{
		$ob = new object($arr["id"]);
		$cform = $ob->meta("cfgform_id");
		// feega hea .. nüüd on vaja veel nimed saad
		$cform_obj = new object($cform);
		$output_form = $cform_obj->prop("use_output");
		if (is_oid($output_form))
		{
			$cform = $output_form;
		};
		$t = get_instance(CL_CFGFORM);
		$props = $t->get_props_from_cfgform(array("id" => $cform));

		// also get view controllers from cfgform and apply those
		$cform_o = obj($cform);
		$ctrs = safe_array($cform_o->meta("view_controllers"));

		$htmlc = get_instance("cfg/htmlclient",array("template" => "webform.tpl"));
		$htmlc->start_output();
		$aliasmgr = get_instance("aliasmgr");
		$prop_list = $ob->get_property_list();
		foreach($props as $propname => $propdata)
		{
			$ok = true;
			if (is_array($ctrs[$propname]))
			{
				foreach($ctrs[$propname] as $v_ctr_oid)
				{
					if ($this->can("view", $v_ctr_oid))
					{
						$vco = obj($v_ctr_oid);
						$vci = $vco->instance();
						$prop_list[$propname]["value"] = $ob->prop($propname);
						$ok &= ($vci->check_property($prop_list[$propname], $v_ctr_oid, array("obj" => $ob)) == PROP_OK);
					}
				}
			}

			if (!$ok)
			{
				continue;
			}

		  	$value = $ob->prop_str($propname);
			if ($propdata["type"] == "datetime_select")
			{
				if($value == -1)
				{
					continue;
				}
				$_v = $value;
				$value = date("Hi", $_v);
				if($value == "0000")
				{
					$value = date("d-m-Y", $_v);
				}
				else
				{
					$value = date("d-m-Y H:i", $_v);
				}
				//$value = date("d-m-Y H:i",$value);
			};

			if (!empty($value))
			{
				$value = nl2br(create_links($value));
				if(strpos($value, "#") !== false)
				{
					$aliasmgr->parse_oo_aliases($arr["id"], $value);
				}
				$htmlc->add_property(array(
					"name" => $propname,
					"caption" => $propdata["caption"],
					"value" => $value,
					"type" => "text",
				));
			}
		}
		$htmlc->finish_output(array("submit" => "no"));

		$html = $htmlc->get_result(array(
			"form_only" => 1
		));

		return $html;
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		// nii .. kuidas ma siin saan ära kasutada classbaset mulle vajaliku vormi genereerimiseks?
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		$vars = $ob->properties();
		$data = array();
		foreach($vars as $k => $v)
		{
			$data[$k] = nl2br($v);
		}
		$this->vars($data);
		return $this->parse();
	}
}
?>
