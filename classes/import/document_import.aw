<?php
// $Header: /home/cvs/automatweb_dev/classes/import/document_import.aw,v 1.7 2006/04/28 10:46:08 kristo Exp $
// document_import.aw - Dokumentide import 
/*

@classinfo syslog_type=ST_DOCUMENT_IMPORT relationmgr=yes

@default table=objects
@default group=general

	@property file type=fileupload store=no
	@caption XML Fail

	@property d_period type=relpicker reltype=RELTYPE_DOCIMP_PERIOD field=meta method=serialize
	@caption Periood

	@property found type=text store=no
	@caption Leitud dokumendid

	@property do_import type=checkbox store=no ch_value=1
	@caption Impordi

@default group=settings

	@property di_cfgform type=relpicker reltype=RELTYPE_CFGFORM fielt=meta method=serialize
	@caption Seadete vorm

	@property location_tags type=textbox field=meta method=serialize 
	@caption Asukohta m&auml;&auml;ravad tagid (formaat: rubriik_aktuaalne=890,rubriik_kala=900)

	@property field_tags type=textbox field=meta method=serialize 
	@caption Sisuv&auml;lju m&auml;&auml;ravad tagid (formaat: rubriik_aktuaalne=890,rubriik_kala=900)

	@property end_tag type=textbox field=meta method=serialize 
	@caption dokumenti l&otilde;petav tag

	@property content_transform type=generated generator=generate_tag_fields

@groupinfo settings caption="Seaded"


@reltype DOCIMP_PERIOD value=1 clid=CL_PERIOD
@caption Periood

@reltype CFGFORM value=2 clid=CL_CFGFORM
@caption Seadete vorm

*/

class document_import extends class_base
{
	function document_import()
	{
		$this->init(array(
			"tpldir" => "import/document_import",
			"clid" => CL_DOCUMENT_IMPORT
		));
	}

	function get_property($args)
	{
		$prop = &$args["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "found":
				$prop["value"] = "";
				if ($args["obj_inst"]->meta("temp_file") != "")
				{
					$tf = $this->get_file(array("file" => $args["obj_inst"]->meta("temp_file")));
					if ($tf !== false)
					{
						$doc_list = $this->_do_import_from_string($tf, $args["obj_inst"]);
						
						$prop["value"] = $this->_draw_document_list_from_arr($doc_list, $args["obj_inst"]->meta("orig_filename"));
					}
				}
				if ($prop["value"] == "")
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "do_import":
				$retval = PROP_IGNORE;
				if ($args["obj_inst"]->meta("temp_file") != "")
				{
					$tf = $this->get_file(array("file" => $args["obj_inst"]->meta("temp_file")));
					if ($tf !== false)
					{
						$retval = PROP_OK;
					}
				}
				break;
		};
		return $retval;
	}

	function set_property($args = array())
	{
		$prop = &$args["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "file":
				if ($_FILES["file"]["tmp_name"] != "")
				{
					$tf = aw_ini_get("server.tmpdir")."/docimp-".gen_uniq_id().".xml";
					if (move_uploaded_file($_FILES["file"]["tmp_name"], $tf))
					{
						if ($args["obj_inst"]->meta("temp_file") != "")
						{
							@unlink($args["obj_inst"]->meta("temp_file"));
						}
						$args["obj_inst"]->set_meta("temp_file",$tf);
						$args["obj_inst"]->set_meta("orig_filename",$_FILES["file"]["name"]);
					}
					else
					{
						@unlink($tf);
					}
				}
				break;

			case "do_import":
				if ($prop["value"] == 1)
				{
					if ($args["obj_inst"]->meta("temp_file") != "")
					{
						$tf = $this->get_file(array("file" => $args["obj_inst"]->meta("temp_file")));
						if ($tf !== false)
						{
							$doc_list = $this->_do_import_from_string($tf, $args["obj_inst"]);
							$this->_save_imported_data($doc_list, $args["obj_inst"]);
							$args["obj_inst"]->set_meta("temp_file","");
							$args["obj_inst"]->set_meta("orig_filename","");
						}
					}
				}
				break;
		}
		return $retval;
	}	

	function _do_import_from_string($str, $obj_inst)
	{
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,1);
//		xml_parser_set_option($parser,XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
		xml_parse_into_struct($parser,$str,&$values,&$tags);
		if (($err = xml_get_error_code($parser)))
		{
			echo xml_error_string($err);
			return array();
		};
		xml_parser_free($parser);

		$docs = array();

		$location_tags = $this->_explode_tags($obj_inst->prop("location_tags"));
		$field_tags = $this->_explode_tags($obj_inst->prop("field_tags"));
		$end_tag = strtoupper($obj_inst->prop("end_tag"));
		$meta = $obj_inst->meta();

		$cur_doc = array();

		$jrk = 1;
		foreach($values as $idx => $val)
		{
			if (isset($location_tags[$val["tag"]]))
			{
				$cur_doc["parent"] = $location_tags[$val["tag"]];
			}

			if (isset($field_tags[$val["tag"]]))
			{
				$vl = $meta["pre_".$val["tag"]].$val["value"].$meta["post_".$val["tag"]];
				$cur_doc[$field_tags[$val["tag"]]] .= $vl;
			}

			if ($val["tag"] == trim($end_tag) && $val["type"] == "close")
			{
				if ($cur_doc["parent"])
				{
					$cur_doc["jrk"] = $jrk++;
					$docs[] = $cur_doc;
				}
				$cur_doc = array("parent" => $cur_doc["parent"]);
			}
		}

		return $docs;
	}

	function _draw_document_list_from_arr($list, $orig_filename)
	{
		load_vcl("table");
		$t = new aw_table(array("layout" => "generic"));
		$t->define_field(array(
			"name" => "parent",
			"caption" => t("Asukoht"),
		));
		$t->define_field(array(
			"name" => "title",
			"caption" => t("Pealkiri"),
		));
		$t->define_field(array(
			"name" => "author",
			"caption" => t("Autor"),
		));
		$t->define_field(array(
			"name" => "content",
			"caption" => t("Sisu"),
		));

		foreach($list as $doc)
		{
			$o = obj($doc["parent"]);
			$doc["parent"] = $o->path_str(array(
				"max_len" => 3
			));
			$doc["content"] = str_replace("\n","<br><br>",trim($doc["lead"]))."<br>".str_replace("\n","<br><br>",trim($doc["content"]));
			$t->define_data($doc);
		}
		
		return "Fail: ".$orig_filename." <br>".$t->draw();
	}

	function _save_imported_data($docs, $obj)
	{
		$per = get_instance(CL_PERIOD);
		if ($obj->prop("d_period"))
		{
			$perid = $per->get_id_for_oid($obj->prop("d_period"));
		}
		foreach($docs as $doc)
		{
			$doc["lead"] = str_replace("\n", "<br><br>", trim($doc["lead"]));
			$doc["content"] = str_replace("\n", "<br><br>", trim($doc["content"]));
			$o = obj($doc);
			$o->set_name($doc["title"]);
			if ($perid)
			{
				$o->set_period($perid);
			}
			$o->set_class_id(CL_DOCUMENT);
			$o->set_status(STAT_ACTIVE);
			$o->set_ord($doc["jrk"]);
			$o->set_site_id(aw_ini_get("site_id"));
			$o->set_meta("cfgform_id", $obj->prop("di_cfgform"));
			$o->set_meta("show_print", 1);
			$props = $o->get_property_list();
			foreach($props as $prop)
			{
				if (isset($doc[$prop["name"]]))
				{
					$o->set_prop($prop["name"],nl2br(trim($doc[$prop["name"]])));
				}
			}
			$id = $o->save();
			$this->db_query("UPDATE documents SET modified = '".time()."' WHERE docid = $id");
		}
	}

	function generate_tag_fields($arr)
	{
		$obj = obj($arr["id"]);
		$ret = array();
		
		$tags = $this->_explode_tags($obj->meta("field_tags"));
		foreach($tags as $tag => $fld)
		{
			$rt = 'pre_'.$tag;

			$ret[$rt] = array(
				'name' => $rt,
				'caption' => sprintf(t("Tagi %s sisu ette pane "), $tag),
				'type' => 'textbox',
				'table' => 'objects',
				'field' => 'meta',
				'method' => 'serialize',
				'group' => 'settings'
			);

			$rt = 'post_'.$tag;

			$ret[$rt] = array(
				'name' => $rt,
				'caption' => sprintf(t("Tagi %s sisu taha pane "), $tag),
				'type' => 'textbox',
				'table' => 'objects',
				'field' => 'meta',
				'method' => 'serialize',
				'group' => 'settings'
			);
		
		}
		return $ret;
	}
	
	function _explode_tags($str)
	{
		$tags = array();
		$_tags = explode(",", $str);
		foreach($_tags as $tg)
		{
			list($tag, $fld) = explode("=", $tg);
			$tag = strtoupper($tag);
			$tags[$tag] = $fld;
		}
		return $tags;
	}
}
?>
