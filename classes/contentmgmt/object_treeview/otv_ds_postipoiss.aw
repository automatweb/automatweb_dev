<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_treeview/otv_ds_postipoiss.aw,v 1.9 2004/06/09 13:01:12 kristo Exp $
// otv_ds_postipoiss.aw - Objektinimekirja Postipoisi datasource 
/*

@classinfo syslog_type=ST_OTV_DS_POSTIPOISS relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

@property xml_fld type=textbox field=meta method=serialize
@caption Serveri kataloog, kus asuvad xml failid

@property ct_fld type=textbox field=meta method=serialize
@caption Serveri kataloog, kus asuvad sisu failid

@property subj_xml type=textbox field=meta method=serialize
@caption Teemade xml fail

@groupinfo content caption="Sisu"

@property ct type=table group=content store=no
@caption Sisu

*/

class otv_ds_postipoiss extends class_base
{
	var $all_cols = array(
/*		"dok_nr" => "Dokumendi Nr",
		"serial_nr" => "Serial Nr",
		"alg_dok_nr" => "Alg dok Nr",
		"jrk_nr" => "J&auml;rjekord",
		"juurdepaasupiirang" => "Juurdep&auml;&auml;s",
		"paritolu" => "P&auml;ritolu",
		"liik" => "Liik",
		"indeks" => "Indeks",
		"suund" => "Suund",
		"teemad" => "Teemad",
		"info" => "Info",
		"kellele" => "Kellele",
		"kellelt" => "Kellelt",
		"koostaja" => "Koostaja",
		"osakond" => "Osakond",
		"pealkiri" => "Pealkiri",
		"registreerimiskuupaev" => "Registreerimise kpv",
		"registreerimisnumber" => "Registreerimise nr",
		"saatja_indeks" => "Saatja indeks",
		"saatja_kuupaev" => "Saatja kuupaev",
		"sisu" => "Sisu",
		"tahtaeg" => "Tahtaeg",
		"toimik" => "Toimik",
		"vastamiskuupaev" => "Vastamiskuupaev"*/
		
		"regist_nr" => "Nr.",
		"indeks" => "Dokument",
		"reg_kpv" => "Registreeritud",
		"osakond" => "Valdkond",
		"toimik" => "Toimik",
		"sep1" => "",
		"saatja_kpv" => "Saatja kuup&auml;ev",
		"saatja_indeks" => "Saatja nr.",
		"sep2" => "",
		"pool1" => "Lepingu pool",
		"kellelt" => "Kellelt",
		"sep3" => "",
		"kellelt_isik" => "",
		"pool2" => "Lepingu pool",
		"Kellele" => "Kellele",
		"sep4" => "",
		"kellele_isik" => "",
		"pealkiri" => "Pealkiri",
		"sisu" => "Sisu",
		"lisad" => "Lisad",
		"resolutsioon" => "Resolutsioon",
		"sep5" => "",
		"tahtaeg" => "T&auml;htaeg",
		"vastamis_kpv" => "Vastatud",
		"sep6" => "",
		"isik" => "T&auml;itja/L&auml;bivaataja",
		"koostaja" => "Koostaja",
		"koostajad" => "Osav&otilde;tjad",
		"allkiri" => "Allkirjastaja",
		"viide" => "Failid",
	);

	function otv_ds_postipoiss()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/object_treeview/otv_ds_postipoiss",
			"clid" => CL_OTV_DS_POSTIPOISS
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "ct":
				$this->do_ct_tbl($arr);
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

	function _init_ct_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi"
		));
	}

	function do_ct_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_ct_tbl($t);

		$t->define_data(array(
			"name" => "<b>Kataloogid</b>"
		));

		foreach($this->get_folders($arr["obj_inst"]) as $fld)
		{
			$t->define_data(array(
				"name" => $fld["name"]
			));
		}

		$t->define_data(array(
			"name" => "<b>Failid</b>"
		));

		foreach($this->get_objects($arr["obj_inst"]) as $fld)
		{
			$t->define_data(array(
				"name" => $fld["name"]
			));
		}

		$t->set_sortable(false);
	}

	function get_objects($o, $fld = NULL, $parent = NULL)
	{
		classload("icons", "image");
		$dc = $this->get_directory(array(
			"dir" => $o->prop("xml_fld")
		));
		$ret = array();
		foreach($dc as $fe)
		{
			$fc = $this->get_file(array("file" => $o->prop("xml_fld")."/".$fe));
			$fd = aw_unserialize($fc);

			$sbs = explode(",", $fd["teemad"]);
			$show = $parent !== NULL ? false : true;
			foreach($sbs as $sb)
			{
				if ($sb == $parent)
				{
					$show = true;
				}
			}
			if (!$show)
			{
				continue;
			}

			list($_d, $_m, $_y) = explode(".", $fd["saatja_kuupaev"]);
			$add = mktime(0,0,0, $_m, $_d, $y);

			list($_d, $_m, $_y) = explode(".", $fd["tegevused"]["tegevus"]["kuupaev"]);
			$mdd = mktime(0,0,0, $_m, $_d, $y);

			list($fn) = explode(",", $fd["viide"]);
			$fsb = @filesize($o->prop("ct_fld")."/".$fn);
			$rowd = $fd + array(
				"id" => $fd["tegevused"]["tegevus"]["dok_nr"],
				"name" => $fd["pealkiri"],
				"url" => aw_ini_get("baseurl")."/".$o->id().":".str_replace(".xml", "", $fe) ,
				"target" => "",
				"comment" => "",
				"type" => "Postipoisti dokument",
				"add_date" => $add,
				"mod_date" => $mdd,
				"adder" => $fd["tegevused"]["tegevus"]["kellelt"],
				"modder" => $fd["tegevused"]["tegevus"]["kellele"],
				"icon" => image::make_img_tag(icons::get_icon_url(CL_OTV_DS_POSTIPOISS, $fd["pealkiri"])),
				"fileSizeBytes" => $fsb,
				"fileSizeKBytes" => number_format($fsb / 1024, 2),
				"fileSizeMBytes" => number_format($fsb / (1024 * 1024))
			);
			$tmp = array();
			foreach($rowd as $k => $v)
			{
				$tmp[$k] = convert_unicode($v);
			}
			$ret[$fd["tegevused"]["tegevus"]["dok_nr"]] = $tmp;
		}
		return $ret;
	}

	function get_folders($o)
	{
		if (!file_exists($o->prop("subj_xml")))
		{
			error::throw(array(
				"id" => ERR_NO_FILE,
				"msg" => "the subject xml file (".$o->prop("subj_xml").") does not exist!"
			));
		}
		// parse the subject xml file
		$fc = $this->get_file(array(
			"file" => $o->prop("subj_xml")
		));
		$dat = aw_unserialize($fc);
	
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$fc,&$values,&$tags);
		if (xml_get_error_code($parser))
		{
			echo dbg::dump($parser);
			echo dbg::dump($fc);
		};
		// R.I.P. parser
		xml_parser_free($parser);

		$data = array();
		foreach($values as $key => $val)
		{
			$val["value"] = convert_unicode($val["value"]);
			if ($val["tag"] == "teema" && $val["type"] == "open")
			{
				$cur = array();
			}
			else
			if ($val["tag"] == "teema" && $val["type"] == "close")
			{
				$data[$cur["id"]] = $cur;
			}
			else
			if ($val["tag"] == "nimetus" && $val["type"] == "complete")
			{
				$cur["name"] = trim($val["value"]);
			}
			else
			if ($val["tag"] == "teema_kood" && $val["type"] == "complete")
			{
				$cur["id"] = trim($val["value"]);
			}
			else
			if ($val["tag"] == "ylem_teema" && $val["type"] == "complete")
			{
				$cur["parent"] = trim($val["value"]);
			}
			else
			if ($val["tag"] == "jrk_nr" && $val["type"] == "complete")
			{
				$cur["ord"] = (int)trim($val["value"]);
			}
		}

		return $data;
	}

	function check_acl()
	{
		return true;
	}

	function show($arr)
	{
		extract($arr);
		list($oid, $ppid) = explode(":", $id);
		$ppid = basename($ppid);

		$o = obj($oid);

		$fp = $o->prop("xml_fld")."/".$ppid.".xml";
		$fc = $this->get_file(array(
			"file" => $fp
		));
		$fd = aw_unserialize($fc);
		
		$this->read_template("show.tpl");

		$this->vars($fd);
		$lineno = 0;
		foreach($this->all_cols as $f_n => $f_v)
		{
			if (substr($f_n, 0, 3) == "sep" && $hasc)
			{
				$lines .= $this->parse("LINE_SEP");
				$lineno = 0;
				$hasc = false;
			}

			if (!empty($fd[$f_n]))
			{
				$this->vars(array(
					"desc" => $f_v,
					"value" => convert_unicode($fd[$f_n])
				));
				if ($lineno % 2)
				{
					$lines .= $this->parse("LINE_ODD");
				}
				else
				{
					$lines .= $this->parse("LINE_EVEN");
				}
				$hasc = true;
				$lineno ++;
			}
		}
		$this->vars(array(
			"LINE_ODD" => $lines,
			"LINE_EVEN" => "",
			"LINE_SEP" => ""
		));


		$fs = explode(",", $fd["viide"]);
		$fstr = array();
		$cnt = 1;
		foreach($fs as $fname)
		{
			$url = aw_ini_get("baseurl")."/".$o->id().":".$ppid.":".$cnt;
			$this->vars(array(
				"furl" => $url,
				"fname" => $fname
			));
			$fstr[] = $this->parse("FILE");
			$cnt++;
		}

		$this->vars(array(
			"FILE" => join(",", $fstr)
		));
		return $this->parse();
	}

	function pget_file($arr)
	{
		$o = obj($arr["oid"]);
		$fnam = basename($arr["fnam"]);
		$mt = get_instance("core/aw_mime_types");
		header("Content-type: ".$mt->type_for_file($fnam));	
		header("Content-Disposition: attachment;filename=$fnam");
		readfile($o->prop("ct_fld")."/".$fnam);
		die();
	}

	function request_execute($obj)
	{
		$td = explode(":", aw_global_get("section"));
		if (count($td) == 3)
		{
			// get file name
			$o = obj($td[0]);
			$fp = $o->prop("xml_fld")."/".basename($td[1]).".xml";
			$fc = aw_unserialize($this->get_file(array("file" => $fp)));
			$files = explode(",", $fc["viide"]);
			
			return $this->pget_file(array(
				"oid" => $td[0],
				"fnam" => $files[$td[2]-1]
			));
		}
		
		return $this->show(array(
			"id" => aw_global_get("section")
		));
	}

	function get_fields()
	{
		return $this->all_cols;
	}
	
	function get_add_types()
	{
		return array();
	}
}
?>
