<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/object_export.aw,v 1.4 2004/12/01 13:21:57 kristo Exp $
// object_export.aw - Objektide eksport 
/*

@classinfo syslog_type=ST_OBJECT_EXPORT relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property object_type type=relpicker reltype=RELTYPE_OBJECT_TYPE
@caption Objektit&uuml;&uuml;p mida eksportida

@property root_folder type=relpicker reltype=RELTYPE_FOLDER
@caption Kaust, kust objektid v&otilde;tta

@property csv_separator type=textbox size=1
@caption CSV Faili tulpade erladaja

@groupinfo mktbl caption="Koosta tabel"
@default group=mktbl

@property mktbl type=table store=no no_caption=1

@groupinfo export caption="Ekspordi" submit=no
@default group=export

@property export_link type=text store=no

@property export_table type=table store=no 
@caption Esimesed 10 rida

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
					"caption" => t("Ekspordi CSV fail")
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
		$arr["obj_inst"]->set_meta("dat", $arr["request"]["dat"]);
	}

	function _init_mktbl_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Element")
		));

		$t->define_field(array(
			"name" => "jrk",
			"caption" => t("J&auml;rjekord")
		));

		$t->define_field(array(
			"name" => "visible",
			"caption" => t("Eksporditav")
		));

		$t->define_field(array(
			"name" => "caption",
			"caption" => t("Tulba pealkiri")
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
				"visible" => html::checkbox(array(
					"name" => "dat[$pn][visible]",
					"value" => 1,
					"checked" => ($dat[$pn]["visible"] == 1)
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
				}
				$ret = $tmp;
			}
		}
		return $ret;
	}

	function _init_exp_table(&$t, $o)
	{
		$props = $this->get_properties_from_obj($o);

		$awa = new aw_array($o->meta("dat"));
		foreach($awa->get() as $pn => $pd)
		{
			$t->define_field(array(
				"name" => $pn,
				"caption" => $pd["caption"]
			));
		}
	}

	function do_export_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_exp_table($t, $arr["obj_inst"]);

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
		if (!$arr["request"]["do_exp"])
		{
			$filt["limit"] = 10;
		}

		$ol = new object_list($filt);
		$t->data_from_ol($ol);

		if ($arr["request"]["do_exp"] == 1)
		{
			header("Content-type: text/csv");
			header("Content-disposition: inline; filename=eksport.csv;");
			$sep = $arr["obj_inst"]->prop("csv_separator");
			die($t->get_csv_file($sep == "" ? "," : $sep));
		}
	}
}
?>
