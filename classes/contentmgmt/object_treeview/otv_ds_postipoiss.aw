<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_treeview/otv_ds_postipoiss.aw,v 1.2 2004/04/29 12:53:54 kristo Exp $
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
			$fsb = filesize($o->prop("ct_fld")."/".$fn);
			$ret[$fd["dok_nr"]] = array(
				"id" => $fd["dok_nr"],
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
		}
		return $ret;
	}

	function get_folders($o)
	{
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
			$this->bitch_and_die($parser,$fc);
		};
		// R.I.P. parser
		xml_parser_free($parser);

		$data = array();
		foreach($values as $key => $val)
		{
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
}
?>
