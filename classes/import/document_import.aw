<?php
// $Header: /home/cvs/automatweb_dev/classes/import/document_import.aw,v 1.1 2003/08/26 13:56:15 kristo Exp $
// document_import.aw - Dokumentide import 
/*

@classinfo syslog_type=ST_DOCUMENT_IMPORT relationmgr=yes

@default table=objects
@default group=general

@property file type=fileupload store=no
@caption XML Fail

@property period type=relpicker reltype=RELTYPE_PERIOD field=meta method=serialize
@caption Periood

@property found type=text store=no
@caption Leitud dokumendid

@property do_import type=checkbox store=no ch_value=1
@caption Impordi

@groupinfo settings caption="Seaded"

@property location_tags type=textbox field=meta method=serialize group=settings
@caption Asukohta m&auml;&auml;ravad tagid (formaat: rubriik_aktuaalne=890,rubriik_kala=900)

@property field_tags type=textbox field=meta method=serialize group=settings
@caption Sisuv&auml;lju m&auml;&auml;ravad tagid (formaat: rubriik_aktuaalne=890,rubriik_kala=900)

@property end_tag type=textbox field=meta method=serialize group=settings
@caption dokumenti l&otilde;petav tag

*/

define("RELTYPE_PERIOD", 1);

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
				if ($args["obj"]["meta"]["temp_file"] != "")
				{
					$tf = $this->get_file(array("file" => $args["obj"]["meta"]["temp_file"]));
					if ($tf !== false)
					{
						$doc_list = $this->_do_import_from_string($tf, $args["obj"]);
						
						$prop["value"] = $this->_draw_document_list_from_arr($doc_list, $args["obj"]["meta"]["orig_filename"]);
					}
				}
				if ($prop["value"] == "")
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "do_import":
				$retval = PROP_IGNORE;
				if ($args["obj"]["meta"]["temp_file"] != "")
				{
					$tf = $this->get_file(array("file" => $args["obj"]["meta"]["temp_file"]));
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
						if ($args["obj"]["meta"]["temp_file"])
						{
							@unlink($args["obj"]["meta"]["temp_file"]);
						}
						$args["metadata"]["temp_file"] = $tf;
						$args["metadata"]["orig_filename"] = $_FILES["file"]["name"];
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
					if ($args["obj"]["meta"]["temp_file"] != "")
					{
						$tf = $this->get_file(array("file" => $args["obj"]["meta"]["temp_file"]));
						if ($tf !== false)
						{
							$doc_list = $this->_do_import_from_string($tf, $args["obj"]);
							$this->_save_imported_data($doc_list, $args["obj"]);
							$args["metadata"]["temp_file"] = "";
							$args["metadata"]["orig_filename"] = "";
						}
					}
				}
				break;
		}
		return $retval;
	}	

	function _do_import_from_string($str, $obj)
	{
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,1);
		xml_parse_into_struct($parser,$str,&$values,&$tags);
		if (($err = xml_get_error_code($parser)))
		{
			echo xml_error_string($err);
			return array();
		};
		xml_parser_free($parser);

		$docs = array();

		$location_tags = array();
		$_location_tags = explode(",", $obj["meta"]["location_tags"]);
		foreach($_location_tags as $tg)
		{
			list($tag, $loc) = explode("=", $tg);
			$tag = strtoupper($tag);
			$location_tags[$tag] = $loc;
		}

		$field_tags = array();
		$_field_tags = explode(",", $obj["meta"]["field_tags"]);
		foreach($_field_tags as $tg)
		{
			list($tag, $fld) = explode("=", $tg);
			$tag = strtoupper($tag);
			$field_tags[$tag] = $fld;
		}

		$end_tag = strtoupper($obj["meta"]["end_tag"]);

		$cur_doc = array();

		foreach($values as $idx => $val)
		{
			if (isset($location_tags[$val["tag"]]))
			{
				$cur_doc["parent"] = $location_tags[$val["tag"]];
			}

			if (isset($field_tags[$val["tag"]]))
			{
				$cur_doc[$field_tags[$val["tag"]]] = $val["value"];
			}

			if ($val["tag"] == trim($end_tag) && $val["type"] == "close")
			{
				$docs[] = $cur_doc;
				$cur_doc = array();
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
			"caption" => "Asukoht",
		));
		$t->define_field(array(
			"name" => "title",
			"caption" => "Pealkiri",
		));
		$t->define_field(array(
			"name" => "author",
			"caption" => "Autor",
		));
		$t->define_field(array(
			"name" => "content",
			"caption" => "Sisu",
		));

		foreach($list as $doc)
		{
			$o = obj($doc["parent"]);
			$doc["parent"] = $o->path_str(array(
				"max_len" => 3
			));
			$t->define_data($doc);
		}
		
		return "Fail: ".$orig_filename." <br>".$t->draw();
	}

	function _save_imported_data($docs, $obj)
	{
		foreach($docs as $doc)
		{
			$o = obj($doc);
			$o->set_name($doc["title"]);
			if ($obj["meta"]["period"])
			{
				$o->set_period($obj["meta"]["period"]);
			}
			$o->set_class_id(CL_DOCUMENT);
			$o->set_status(STAT_ACTIVE);
			$props = $o->get_property_list();
			foreach($props as $prop)
			{
				if (isset($doc[$prop["name"]]))
				{
					$o->set_prop($prop["name"],$doc[$prop["name"]]);
				}
			}
			$o->save();
		}
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_PERIOD => 'periood',
		);
	}
	
	function callback_get_classes_for_relation($args = array())
	{
		switch($args["reltype"])
		{
			case RELTYPE_PERIOD:
			return array(CL_PERIOD);
			break;
		};
	}

}
?>
