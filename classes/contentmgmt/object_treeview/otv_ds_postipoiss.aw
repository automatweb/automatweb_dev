<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_treeview/otv_ds_postipoiss.aw,v 1.10 2004/06/11 08:40:00 kristo Exp $
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

@property update_cache type=text store=no
@caption 

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
		"kuupaev" => "Kuup&auml;ev",
		"kuupaev2" => "J&otilde;ustub"
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

			case "update_cache":
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("do_cache_update"),
					"caption" => "Uuenda cache"
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
		if (!$parent)
		{
			return array();
		}

		$this->db_query("
			SELECT 
				* 
			FROM 
				aw_otv_ds_pp_cache_file2folder ff
				LEFT JOIN  aw_otv_ds_pp_cache c on c.id = ff.aw_file
				
			WHERE 
				ff.aw_pp_id = '".$o->id()."' AND
				ff.aw_folder = '$parent'
		");
		$ret = array();
		while ($row = $this->db_next())
		{
			$tmp = array();
			foreach($row as $k => $v)
			{
				$tmp[str_replace("aw_", "", $k)] = $v;
			}
			$ret[$row["aw_dok_nr"]] = $tmp;
		}
		return $ret;

/*		enter_function("otv_ds_postipoiss::get_objects");
		classload("icons", "image");
		$xml_fld = $o->prop("xml_fld");
		$cache_fld = $this->cfg["site_basedir"]."/pagecache/otv_ds_pp";
		if (!is_dir($cache_fld))
		{
			@mkdir($cache_fld);
		}

		$dc = $this->get_directory(array(
			"dir" => $xml_fld
		));
		$ret = array();

		enter_function("otv_ds_postipoiss::get_objects::getmtimes");
		$mtime = 0;
		$mt_by_f = array();
		$c_mt_by_f = array();
		foreach($dc as $fe)
		{
			$mt = @filemtime($xml_fld."/".$fe);
			$c_mt = @filemtime($cache_fld."/".$fe.".cache");
			$mtime = max($mtime, $mt);
			$mt_by_f[$fe] = $mt;
			$c_mt_by_f[$fe] = $c_mt;
		}
		exit_function("otv_ds_postipoiss::get_objects::getmtimes");

		foreach($dc as $fe)
		{
			$cache_file = $cache_fld."/".$fe.".cache";
			if ($c_mt_by_f[$fe] >= $mt_by_f[$fe])
			{
				// read from cache
				enter_function("otv_ds_postipoiss::get_objects::cache");
				$rowd = aw_unserialize($this->get_file(array("file" => $cache_file)));
				exit_function("otv_ds_postipoiss::get_objects::cache");
			}
			else
			{
				enter_function("otv_ds_postipoiss::get_objects::unser");
				$fc = $this->get_file(array("file" => $o->prop("xml_fld")."/".$fe));
				$fd = aw_unserialize($fc);
				exit_function("otv_ds_postipoiss::get_objects::unser");

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
					if (!is_array($v))
					{
						$tmp[$k] = convert_unicode($v);
					}
					else
					{
						$tmp[$k] = $v;
					}
				}
				$rowd = $tmp;
				

				$fp = fopen($cache_file, "w");
				fwrite($fp, aw_serialize($rowd, SERIALIZE_PHP));
				fclose($fp);
				@chmod($cache_file, 0666);
			}

			$sbs = explode(",", $rowd["teemad"]);
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

			$ret[$rowd["tegevused"]["tegevus"]["dok_nr"]] = $rowd;
		}
		exit_function("otv_ds_postipoiss::get_objects");*/
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

		uasort($data, create_function('$a,$b', 'return strcmp($a["name"], $b["name"]);'));
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
		list($tmp, $real_nr) = explode("_", $ppid);
		foreach($fs as $fname)
		{
			$patt = $o->prop("ct_fld")."/*".$real_nr;
			$ret = glob($patt);
			if (count($ret) < 1)
			{
				continue;
			}
			$url = aw_ini_get("baseurl")."/".$o->id().":".$ppid.":".$cnt;
			$this->vars(array(
				"furl" => $url,
				"fname" => convert_unicode($fname)
			));
			$fstr[] = $this->parse("FILE");
			$cnt++;
		}

		$str = join(",", $fstr);
		$this->vars(array(
			"FILE" => $str
		));
		
		if (trim($str) != "")
		{
			$this->vars(array(
				"HAS_FILES" => $this->parse("HAS_FILES")
			));
		}
		return $this->parse();
	}

	function pget_file($arr)
	{
		$o = obj($arr["oid"]);
		$fnam = basename($arr["fnam"]);
		$mt = get_instance("core/aw_mime_types");
		header("Content-type: ".$mt->type_for_file($fnam));	
		header("Content-Disposition: attachment;filename=$fnam");

		$patt = $o->prop("ct_fld")."/*".$arr["real_nr"];
		$ret = glob($patt);
		readfile($ret[0]);
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
	
			list($tmp, $real_nr) = explode("_", $td[1]);
		
			return $this->pget_file(array(
				"oid" => $td[0],
				"fnam" => $files[$td[2]-1],
				"real_nr" => $real_nr
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

	/**
		
		@attrib name=do_cache_update nologin="1"

	**/
	function do_cache_update($arr)
	{
		$this->do_reschedule();
		
		// for all pp objects
			// get object
			// read all xml files for it
			// for each xml file
			// parse content
			// write to database

		$ol = new object_list(array(
			"class_id" => CL_OTV_DS_POSTIPOISS,
			"site_id" => array(),
			"lang_id" => array()
		));
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$this->do_update_ds($o);
		}
	}

	function do_reschedule()
	{
		$url = str_replace("automatweb/", "", $this->mk_my_orb("do_dache_update"));

		// automatic updates are at 06:00 AM and 06:00 PM
		$sc = get_instance("scheduler");
		$sc->remove(array(
			"event" => $url
		));

		// add 3 days worth of events
		$today = time() - (time() % (3600*24));
		for($day = 0; $day < 3; $day++)
		{
			$tm = $today + ($day * 3600 * 24) + (3*3600); // 6am
			$tm2= $today + ($day * 3600 * 24) + (15*3600); // 6pm

			if ($tm > time())
			{
				$sc->add(array(
					"event" => $url,
					"time" => $tm,
				));
				break;
			}

			if ($tm2 > time())
			{
				$sc->add(array(
					"event" => $url,
					"time" => $tm2,
				));
				break;
			}
		}
	}

	function do_update_ds($o)
	{
		classload("icons", "image");
		$xml_fld = $o->prop("xml_fld");

		$dc = $this->get_directory(array(
			"dir" => $xml_fld
		));
		$ret = array();

		$mtime = 0;
		foreach($dc as $fe)
		{
			$mtime = max(@filemtime($xml_fld."/".$fe), $mtime);
		}

		$c_mtime = $this->db_fetch_field("SELECT MAX(aw_mtime) as mtime FROM aw_otv_ds_pp_cache WHERE aw_pp_id = '".$o->id()."'", "mtime");

		if ($c_mtime > $mtime)
		{
			echo "Cache on uuem kui failid, uuendada pole vaja!<br>";
			return;
		}

		$this->db_query("DELETE FROM aw_otv_ds_pp_cache WHERE aw_pp_id = '".$o->id()."'");
		$this->db_query("DELETE FROM aw_otv_ds_pp_cache_file2folder WHERE aw_pp_id = '".$o->id()."'");
		
		echo "update ".$o->name()." <br>\n";
		flush();
		foreach($dc as $fe)
		{
			echo "file $fe <Br>\n";
			flush();
			$fc = $this->get_file(array("file" => $xml_fld."/".$fe));
			$fd = aw_unserialize($fc);

			list($_d, $_m, $_y) = explode(".", $fd["saatja_kuupaev"]);
			$add = mktime(0,0,0, $_m, $_d, $y);

			list($_d, $_m, $_y) = explode(".", $fd["tegevused"]["tegevus"]["kuupaev"]);
			$mdd = mktime(0,0,0, $_m, $_d, $y);

			list($fn) = explode(",", $fd["viide"]);
			$fsb = @filesize($o->prop("ct_fld")."/".$fn);
			$rowd = array(
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

			$data = $rowd;
			foreach($fd as $k => $v)
			{
				if (!is_array($k))
				{
					$data[$k] = $v;
				}
			}

			$data["pp_id"] = $o->id();
			$data["mtime"] = time();
			$data["dok_nr"] = $fd["tegevused"]["tegevus"]["dok_nr"];

			$tmp = array();
			foreach($data as $k => $v)
			{
				$tmp[$k] = convert_unicode($v);
			}
			$data = $tmp;

			$this->quote(&$data);
			
			$this->db_query("
				INSERT INTO aw_otv_ds_pp_cache(".join(",", map("aw_%s", array_keys($data))).") 
					VALUES(".join(",", map("'%s'", array_values($data))).")
			");
			$id = $this->db_last_insert_id();


			$sbs = explode(",", $fd["teemad"]);
			foreach($sbs as $sb)
			{
				$this->db_query("INSERT INTO aw_otv_ds_pp_cache_file2folder(aw_file, aw_folder, aw_pp_id) values('$id','$sb','".$o->id()."')");
			}
		}
		echo "all done! <br>\n";
		flush();
	}
}
?>
