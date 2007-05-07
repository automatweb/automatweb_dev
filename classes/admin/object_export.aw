<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/object_export.aw,v 1.17 2007/05/07 08:07:04 kristo Exp $
// object_export.aw - Objektide eksport 
/*

@classinfo syslog_type=ST_OBJECT_EXPORT relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default field=meta
@default method=serialize

@default group=general
	@property object_type type=relpicker reltype=RELTYPE_OBJECT_TYPE
	@caption Objektit&uuml;&uuml;p mida eksportida

	@property root_folder type=relpicker reltype=RELTYPE_FOLDER
	@caption Kaust, kust objektid v&otilde;tta

	@property csv_separator type=textbox size=1
	@caption CSV Faili tulpade eraldaja

@default group=mktbl

	@property mktbl type=table store=no no_caption=1

@default group=export

	@property export_link type=text store=no

	@property export_link2 type=text store=no

	@property export_table type=table store=no 
	@caption Esimesed 10 rida


@groupinfo mktbl caption="Koosta tabel"
@groupinfo export caption="Ekspordi" submit=no


@reltype OBJECT_TYPE value=1 clid=CL_OBJECT_TYPE
@caption objektit&uuml;&uuml;p

@reltype FOLDER value=2 clid=CL_MENU
@caption kaust

*/

class object_export extends class_base
{
	function object_export()
	{
		$this->init(array(
			"tpldir" => "admin/object_export",
			"clid" => CL_OBJECT_EXPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "csv_separator":
				if ($prop["value"] == "")
				{
					$prop["value"] = ",";
				}
				break;

			case "mktbl":
				if (!$arr["obj_inst"]->prop("object_type"))
				{
					return PROP_IGNORE;
				}
				$this->do_mktbl_tbl($arr);
				break;

			case "export_table":
				if (!$arr["obj_inst"]->prop("object_type"))
				{
					return PROP_IGNORE;
				}
				$this->do_export_table($arr);
				break;
			
			case "export_link":
				if (!$arr["obj_inst"]->prop("object_type"))
				{
					return PROP_IGNORE;
				}
				$prop["value"] = html::href(array(
					"url" => aw_url_change_var("do_exp", 1),
					"target" => "_blank",
					"caption" => t("Ekspordi CSV fail")
				));
				break;
			case "export_link2":
				if (!$arr["obj_inst"]->prop("object_type"))
				{
					return PROP_IGNORE;
				}
				$prop["value"] = html::href(array(
					"url" => aw_url_change_var("xls", 1),
					"target" => "_blank",
					"caption" => t("Ekspordi XLS fail")
				));
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
			case "mktbl":
				$this->save_mktbl_tbl($arr);
				break;
		}
		return $retval;
	}	

	function save_mktbl_tbl($arr)
	{
		$dat = $arr["request"]["dat"];
		foreach(safe_array($arr["request"]["dat"]) as $key => $value)
		{
			$dat[$key]["visible"] = $arr["request"]["visible"][$key] ? 1 : "";
		}
		$arr["obj_inst"]->set_meta("dat", $dat);
	}

	function _init_mktbl_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Element"),
			"align" => "right",
		));

		$t->define_field(array(
			"name" => "jrk",
			"caption" => t("J&auml;rjekord"),
			"align" => "center"
		));


		$t->define_field(array(
			"name" => "caption",
			"caption" => t("Tulba pealkiri"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "exp_vals",
			"caption" => t("Ekspordi v&auml;&auml;rtus"),
			"align" => "center"
		));

		$t->define_chooser(array(
			"field" => "vs",
			"name" => "visible",
			"caption" => t("Eksporditav"),
		));

		$t->set_sortable(false);
	}

	function do_mktbl_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_mktbl_tbl($t);

		$dat = $arr["obj_inst"]->meta("dat");

		$ps = $this->get_properties_from_obj($arr["obj_inst"]);
		foreach($ps as $pn => $pd)
		{
			if ($pd["type"] == "submit")
			{
				continue;
			}
			if (!is_array($dat[$pn]))
			{
				$dat[$pn] = array(
					"caption" => $pd["caption"]
				);
			}
			$t->define_data(array(
				"name" => $pd["caption"],
				"jrk" => html::textbox(array(
					"size" => 5,
					"name" => "dat[$pn][jrk]",
					"value" => $dat[$pn]["jrk"]
				)),
				"visible" => $dat[$pn]["visible"],
				"vs" => $pn,
				"exp_vals" => html::checkbox(array(
					"name" => "dat[$pn][exp_vals]",
					"value" => 1,
					"checked" => ($dat[$pn]["exp_vals"] == 1)
				)),
				"caption" => html::textbox(array(
					"name" => "dat[$pn][caption]",
					"value" => $dat[$pn]["caption"]
				))
			));
		}
	}

	function get_properties_from_obj($o)
	{
		$ret = array();
		if ($o->prop("object_type"))
		{
			$ot = obj($o->prop("object_type"));

			$clid = $ot->prop("type");

			// first, load all class props
			list($ret) = $GLOBALS["object_loader"]->load_properties(array(
				"clid" => $clid
			));
			if ($ot->prop("use_cfgform"))
			{
				$tmp = array();

				$cfid = $ot->prop("use_cfgform");
				$cff = obj($cfid);
				$class_id = $cff->prop("ctype");
				$class_i = get_instance($class_id);
				$cp = $class_i->load_from_storage(array(
					"id" => $cff->id()
				));
				foreach(safe_array($cp) as $pn => $pd)
				{
					$tmp[$pn] = $ret[$pn];
					$tmp[$pn]["caption"] = $pd["caption"];
					$tmp[$pn]["type"] = $ret[$pn]["type"];
				}
				$ret = $tmp;
			}
		}

		$ret["createdby"] = array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"type" => "textbox",
			"table" => "objects",
			"field" => "createdby",
		);
		$ret["created"] = array(
			"name" => "created",
			"caption" => t("Loodud"),
			"type" => "datetime_select",
			"table" => "objects",
			"field" => "created",
		);
		$ret["modifiedby"] = array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"type" => "textbox",
			"table" => "objects",
			"field" => "modifiedby",
		);
		$ret["modified"] = array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"type" => "datetime_select",
			"table" => "objects",
			"field" => "modified",
		);
		return $ret;
	}

	function _init_exp_table(&$t, $o, $awa, $props)
	{
		foreach($awa->get() as $pn => $pd)
		{
			if ($pd["visible"])
			{
				$prps = array(
					"name" => $pn,
					"caption" => $pd["caption"],
				);
				if($props[$pn]["type"] == "date_select")
				{
					$prps["type"] = "time";
					$prps["format"] = "d-M-y";
					$prps["numeric"] = 1;
				}
				if($props[$pn]["type"] == "datetime_select")
				{
					$prps["type"] = "time";
					$prps["format"] = "d-M-y H:i";
					$prps["numeric"] = 1;
				}
				$t->define_field($prps);
			}
		}
	}

	function do_export_table($arr)
	{
		$sep = $arr["obj_inst"]->prop("csv_separator");
		$t =& $arr["prop"]["vcl_inst"];

		$props = $this->get_properties_from_obj($arr["obj_inst"]);
		$awa = new aw_array($arr["obj_inst"]->meta("dat"));
		$settings = $awa->get();
		$this->_init_exp_table($t, $arr["obj_inst"], $awa, $props);

		if (!$arr["obj_inst"]->prop("object_type"))
		{
			return;
		}
		
		$ot = obj($arr["obj_inst"]->prop("object_type"));
		$clid = $ot->prop("type");

		$filt = array(
			"class_id" => $clid,
		);
		if ($arr["obj_inst"]->prop("root_folder"))
		{
			$filt["parent"] = $arr["obj_inst"]->prop("root_folder");
		}
		if (!$arr["request"]["do_exp"] && !$arr["request"]["xls"])
		{
			$filt["limit"] = 10;
		}
		ini_set("memory_limit","1800M");
		set_time_limit(1200);
		$ol = new object_list($filt);
//		echo "filtrd <br>\n";
//		flush();
		$d = $ol->arr() ;
//		echo "arrd() <br>\n";
//		flush();

		// go over all props and for all classificators
		// read all the conns at once so we don't have to 
		// do a query for each object.
		/*$conn_vals = array();
		foreach($props as $pn => $pd)
		{
			if ($pd["type"] == "classificator")
			{
				$c = new connection();
				$conns = $c->find(array(
					"from" => $ol->ids(),
					"from.class_id" => $clid,
					"type" => $pd["reltype"]
				));
				foreach($conns as $con)
				{
					$conn_vals[$pn][$con["from"]][] = $con;
				}
			}
		}*/
		foreach($d as $o)
		{
		//	if (++$cnt > 100)
		//	{
		//echo $o->id()." mem: ".memory_get_usage()."<br>\n";
		//flush();
		//		$cnt = 0;
		//	}
			$dat = array();
			foreach($props as $pn => $pd)
			{
				if ($settings[$pn]["exp_vals"] == 1 && $pd["type"] == "classificator")
				{
					$rt = $pd["reltype"];
					$val= array();
					foreach($o->connections_from(array("type" => $rt)) as $c)
					{
						$clsf = $c->to();
						if (($cval = $clsf->comment()) != "")
						{
							$val[] = $clsf->comment();
						}
					}
					$dat[$pn] = join(", ", $val);
				}
				else
				if (substr($pn, 0, 6) == "userim")
				{
					$imgo = $o->get_first_obj_by_reltype("RELTYPE_IMAGE".substr($pn, 6));
					if ($imgo)
					{
						$imgi = $imgo->instance();
						$dat[$pn] = $imgi->get_url_by_id($imgo->id());
					}
				}
				else
				if (substr($pn, 0, 8) == "userfile")
				{
					// link to file
					$fileo = $o->get_first_obj_by_reltype("RELTYPE_FILE".substr($pn, 8));
					if ($fileo)
					{
						$filei = $fileo->instance();
						$dat[$pn] = $filei->get_url($fileo->id(), $fileo->name());
					}
				}
				else
				{
					$dat[$pn] = $o->prop_str($pn);
				}
			}
			$t->define_data($dat);
		}
//echo "defind <br>\n";
//flush();
		if ($arr["request"]["do_exp"] == 1)
		{
			header("Content-type: application/csv");
			header("Content-disposition: inline; filename=eksport.csv;");
			die($t->get_csv_file($sep == "" ? "," : $sep));
		}
		elseif($arr["request"]["xls"] == 1)
		{
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: inline; filename=eksport.xls;");
			die($t->draw());
		}
	}
}
?>
