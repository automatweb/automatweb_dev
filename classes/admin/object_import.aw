<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/object_import.aw,v 1.1 2004/05/07 10:48:36 kristo Exp $
// object_import.aw - Objektide Import 
/*

@classinfo syslog_type=ST_OBJECT_IMPORT relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property folder type=relpicker reltype=RELTYPE_FOLDER
@caption Kataloog, kuhu pannakse objektid

@property object_type type=relpicker reltype=RELTYPE_OBJECT_TYPE
@caption Imporditava objekti t&uuml;&uuml;p

@property unique_id type=select 
@caption Unikaalne omadus

@property import_file type=fileupload 
@caption Imporditav fail

@property import_file_show type=text 
@caption Uploaditud fail

@property file_has_header type=checkbox ch_value=1 
@caption Esimene rida on pealkrijadega

@property do_import type=checkbox ch_value=1 
@caption Teosta import

@groupinfo props caption="Omadused"
@property props type=table store=no no_caption=1 group=props

@groupinfo connect caption="Seosta tulbad"
@property connect_props type=table store=no no_caption=1 group=connect


@reltype OBJECT_TYPE value=1 clid=CL_OBJECT_TYPE
@caption imporditav klass

@reltype FOLDER value=2 clid=CL_MENU
@caption kataloog kuhu objektid panna

*/

class object_import extends class_base
{
	function object_import()
	{
		$this->init(array(
			"tpldir" => "admin/object_import",
			"clid" => CL_OBJECT_IMPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "import_file_show":
				$file = false;
				if (($tfn = $arr["obj_inst"]->meta("tmp_up_file")) != "")
				{
					if (file_exists($tfn) && is_readable($tfn))
					{
						$size = @filesize($tfn);
						if ($size > 1024)
						{
							$filesize = number_format($size / 1024, 2)."kb";
						}
						else
						if ($size > (1024*1024))
						{
							$filesize = number_format($size / (1024*1024), 2)."mb";
						}
						else
						{
							$filesize = $size." b";
						}

						$lines = count(file($tfn)) - ($arr["obj_inst"]->prop("file_has_header") ? 1 : 0);

						classload("icons");
						$file = true;
						$prop["value"] = html::img(array(
							"url" => icons::get_icon_url(CL_FILE,$name),
							"border" => "0"
						))." ".$arr["obj_inst"]->meta("tmp_up_file_name").", ".$filesize.", $lines objekt(i)";
					}
				}

				if (!$file)
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "do_import":
				$file = false;
				if (($tfn = $arr["obj_inst"]->meta("tmp_up_file")) != "")
				{
					if (file_exists($tfn) && is_readable($tfn))
					{
						$file = true;
					}
				}

				if (!$file)
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "unique_id":
				if (!$arr["obj_inst"]->prop("object_type"))
				{
					return PROP_IGNORE;
				}

				$properties = $this->get_props_from_obj($arr["obj_inst"]);

				$prop["options"] = array("" => "");
				foreach($properties as $pn => $_prop)
				{
					$prop["options"][$pn] = $_prop["caption"];
				}
				break;
			
			case "props":
				$this->do_props_table($arr);
				break;

			case "connect_props":
				$this->do_connect_props_table($arr);
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
			case "import_file":
				// save as temp file
				if (is_uploaded_file($_FILES["import_file"]["tmp_name"]))
				{
					if ($arr["obj_inst"]->meta("tmp_up_file"))
					{
						@unlink($arr["obj_inst"]->meta("tmp_up_file"));
					}
					$tmp_name = tempnam(aw_ini_get("server.tmpdir"), "aw_oi_tmp");
					move_uploaded_file($_FILES["import_file"]["tmp_name"], $tmp_name);
					$arr["obj_inst"]->set_meta("tmp_up_file", $tmp_name);
					$arr["obj_inst"]->set_meta("tmp_up_file_name", $_FILES["import_file"]["name"]);
				}
				break;

			case "props":
				$arr["obj_inst"]->set_meta("isimp", $arr["request"]["isimp"]);
				break;

			case "connect_props":
				$arr["obj_inst"]->set_meta("p2c", $arr["request"]["p2c"]);
				break;

			case "do_import":
				if ($prop["value"] == 1)
				{
					$this->do_exec_import($arr["obj_inst"]);
					$prop["value"] = 0;
				}
				break;
		}
		return $retval;
	}	

	function _init_props_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "prop",
			"caption" => "Omadus",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "isimp",
			"caption" => "Imporditav?",
			"align" => "center"
		));

		$t->set_default_sortby("prop");
	}

	function do_props_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_props_tbl($t);

		$isimp = $arr["obj_inst"]->meta("isimp");

		foreach($this->get_props_from_obj($arr["obj_inst"]) as $pn => $pd)
		{
			$t->define_data(array(
				"prop" => $pd["caption"],
				"isimp" => html::checkbox(array(
					"name" => "isimp[$pn]",
					"value" => 1,
					"checked" => $isimp[$pn]
				))
			));
		}

		$t->sort_by();
	}

	function get_props_from_obj($o)
	{
		$type_o = obj($o->prop("object_type"));
		$class_id = $type_o->prop("type");
		if (!$type_o->prop("use_cfgform"))
		{
			list($properties) = $GLOBALS["object_loader"]->load_properties(array(
				"clid" => $this->obj["class_id"]
			));
		}
		else
		{
			$class_i = get_instance($class_id);
			$properties = $class_i->load_from_storage(array(
				"id" => $type_o->prop("use_cfgform")
			));
		}

		return $properties;
	}

	function _init_connect_props_table(&$t, $cols)
	{
		$t->set_sortable(false);
		$t->define_field(array(
			"name" => "prop",
			"caption" => "",
			"align" => "right",
			"width" => "10%",
			"nowrap" => 1
		));

		foreach($cols as $idx => $cold)
		{
			$t->define_field(array(
				"name" => "col_".$idx,
				"caption" => $cold,
				"align" => "center"
			));
		}

		$t->define_field(array(
			"name" => "dontimp",
			"caption" => "&Auml;ra impordi",
			"align" => "center"
		));
	}

	function do_connect_props_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$cols = $this->get_cols_from_up_file($arr["obj_inst"]);

		$this->_init_connect_props_table($t, $cols);

		$p2c = $arr["obj_inst"]->meta("p2c");		
		$isimp = $arr["obj_inst"]->meta("isimp");

		foreach($this->get_props_from_obj($arr["obj_inst"]) as $pn => $pd)
		{
			if (!$isimp[$pn])
			{
				continue;
			}
			$issel = false;
			$fields = array(
				"prop" => $pd["caption"]
			);
			foreach($cols as $idx => $cold)
			{
				$fields["col_".$idx] = html::radiobutton(array(
					"name" => "p2c[$pn]",
					"value" => $idx,
					"checked" => ($p2c[$pn] == $idx)
				));
				$issel |= ($p2c[$pn] == $idx);
			}

			$fields["dontimp"] = html::radiobutton(array(
				"name" => "p2c[$pn]",
				"value" => "dontimp",
				"checked" => (($p2c[$pn] == "dontimp") || !$issel)
			));

			$t->define_data($fields);
		}
	}

	function get_cols_from_up_file($o)
	{
		$ret = array();
		if (file_exists($o->meta("tmp_up_file")) && is_readable($o->meta("tmp_up_file")))
		{
			$fp = fopen($o->meta("tmp_up_file"), "r");
			$line = fgetcsv($fp, 100000);
			if(is_array($line))
			{
				foreach($line as $idx => $txt)
				{
					$ret[$idx+1] = $txt;
				}
			}
		}
		
		return $ret;
	}

	function do_exec_import($o)
	{
		// for each line in the file
		// read it
		// if there is an unique column
		//		check if there already is an object with that value
		//		if true, continue
		// match col => prop
		// save
		// loop
		// js redir back to change
		if (file_exists($o->meta("tmp_up_file")) && is_readable($o->meta("tmp_up_file")) && $o->prop("folder"))
		{
			$type_o = obj($o->prop("object_type"));
			$class_id = $type_o->prop("type");
			$p2c = $o->meta("p2c");		

			$line_n = 0;

			$fp = fopen($o->meta("tmp_up_file"), "r");
			while (($line = fgetcsv($fp, 100000)))
			{
				$line_n++;
				if ($o->prop("file_has_header") && $line_n == 1)
				{
					continue;
				}

				echo "impordin rida ".($line_n)."... <br>\n";
				flush();

				if ($o->prop("unique_id"))
				{
					// get column for uniq id
					$u_col = $p2c[$o->prop("unique_id")];

					$ol = new object_list(array(
						"class_id" => $class_id,
						$o->prop("unique_id") => $line[$u_col-1]
					));
					if ($ol->count() > 0)
					{
						$tmp = $ol->begin();
						echo "leidsin juba olemasoleva objekti ".$tmp->name().", seda rida ei impordita <br>";
						continue;
					}
				}

				$dat = obj();
				$dat->set_class_id($class_id);
				$dat->set_parent($o->prop("folder"));

				// if type has cfgform, set the object to use that so that when you change it, is show the correct form
				if ($type_o->prop("use_cfgform"))
				{
					$dat->set_meta("cfgform_id", $type_o->prop("use_cfgform"));
				}
				
				foreach($p2c as $pn => $idx)
				{
					$dat->set_prop($pn, $line[$idx-1]);
				}

				$dat->save();
				echo "importisin objekti ".$dat->name()." <br>\n";
				flush();
	
			}
			echo "Valmis! <br>\n";
			echo "<script language=javascript>setTimeout(\"window.location='".$this->mk_my_orb("change", array("id" => $o->id()))."'\", 5000);</script>";
			die();
		}
	}
}
?>
