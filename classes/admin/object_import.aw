<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/object_import.aw,v 1.12 2004/07/08 12:32:01 kristo Exp $
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

@property folder_field type=select 
@caption Tulp, mille j&auml;rgi jagatakse kataloogidesse

@property folder_is_parent type=checkbox ch_value=1
@caption Objektid samsse kataloogi, kus imporditavad

@property ds type=relpicker reltype=RELTYPE_DATASOURCE
@caption Andmeallikas

@property do_import type=checkbox ch_value=1 
@caption Teosta import

@property import_status type=text
@caption Impordi staatus

@groupinfo props caption="Omadused"
@property props type=table store=no no_caption=1 group=props

@groupinfo connect caption="Seosta tulbad"
@property connect_props type=table store=no no_caption=1 group=connect

@groupinfo folders caption="Kataloogid"
@property folders type=table store=no no_caption=1 group=folders


@reltype OBJECT_TYPE value=1 clid=CL_OBJECT_TYPE
@caption imporditav klass

@reltype FOLDER value=2 clid=CL_MENU
@caption kataloog kuhu objektid panna

@reltype DATASOURCE value=3 clid=CL_ABSTRACT_DATASOURCE
@caption andmeallikas

@reltype EXCEPTION value=4 clid=CL_OBJECT_IMPORT_EXCEPTION
@caption erand

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
			case "do_import":
				if (!$arr["obj_inst"]->prop("ds"))
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "import_status":
				if (!$arr["obj_inst"]->meta("import_status"))
				{
					return PROP_IGNORE;
				}
				$prop["value"]  = "Import k&auml;ivitati ".date("d.m.Y H:i", $arr["obj_inst"]->meta("import_started_at"));
				$prop["value"] .= ". Hetkel on imporditud ".$arr["obj_inst"]->meta("import_row_count")." rida. <br>";
				$prop["value"] .= "Viimane muudatus toimis kell ".date("d.m.Y H:i", $arr["obj_inst"]->meta("import_last_time"))." <br>";
				$prop["value"] .= html::href(array(
					"url" => $this->mk_my_orb("do_check_import"),
					"caption" => "J&auml;tka importi kohe"
				));
				break;

			case "folder_is_parent";
				if (!$arr["obj_inst"]->prop("ds"))
				{
					return PROP_IGNORE;
				}
				$ds = obj($arr["obj_inst"]->prop("ds"));
				if (!$ds->prop("ds"))
				{
					return PROP_IGNORE;
				}
				$dso = obj($ds->prop("ds"));
				if ($dso->class_id() != CL_OTV_DS_OBJ)
				{
					return PROP_IGNORE;
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

			case "folder_field":
				$prop["options"] = array("" => "") + $this->get_cols_from_ds($arr["obj_inst"]);
				break;

			case "props":
				$this->do_props_table($arr);
				break;

			case "connect_props":
				$this->do_connect_props_table($arr);
				break;

			case "folders":
				$this->do_folders_table($arr);
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
			case "props":
				$arr["obj_inst"]->set_meta("isimp", $arr["request"]["isimp"]);
				$arr["obj_inst"]->set_meta("userval", $arr["request"]["userval"]);
				$arr["obj_inst"]->set_meta("has_ex", $arr["request"]["has_ex"]);
				$arr["obj_inst"]->set_meta("ex", $arr["request"]["ex"]);
				break;

			case "connect_props":
				$arr["obj_inst"]->set_meta("p2c", $arr["request"]["p2c"]);
				break;

			case "do_import":
				if ($prop["value"] == 1)
				{
					$this->do_init_import($arr["obj_inst"]);
					$this->do_exec_import($arr["obj_inst"]);
					$prop["value"] = 0;
				}
				break;

			case "folders":
				$arr["obj_inst"]->set_meta("fld_values", $arr["request"]["values"]);
				break;
			
			case "status":
				$prop["value"] = STAT_ACTIVE;
				break;
		}
		return $retval;
	}	

	function _init_folders_table(&$t)
	{
		$t->define_field(array(
			"name" => "folder",
			"caption" => "Kataloog",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "value",
			"caption" => "V&auml;&auml;rtus, mille puhul pannakse sellesse kataloogi",
			"align" => "center",
		));

		$t->set_default_sortby("folder");
	}

	function do_folders_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_folders_table($t);

		$values = $arr["obj_inst"]->meta("fld_values");

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_FOLDER")) as $c)
		{
			$o = $c->to();

			$t->define_data(array(
				"folder" => $o->path_str(),
				"value" => html::textbox(array(
					"size" => 10,
					"name" => "values[".$o->id()."]",
					"value" => $values[$o->id()]
				))
			));

			$ot = new object_tree(array(
				"parent" => $o->id(),
				"class_id" => CL_MENU
			));
			$ol = $ot->to_list();
			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				$t->define_data(array(
					"folder" => $o->path_str(),
					"value" => html::textbox(array(
						"size" => 10,
						"name" => "values[".$o->id()."]",
						"value" => $values[$o->id()]
					))
				));
			}
		}

		$t->sort_by();
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

		$t->define_field(array(
			"name" => "userval",
			"caption" => "V&auml;&auml;rtus k&auml;sitsi",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "has_ex",
			"caption" => "Oman erandeid?",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "ex",
			"caption" => "Erandid",
			"align" => "center"
		));


		$t->set_default_sortby("prop");
	}

	function do_props_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_props_tbl($t);

		$isimp = $arr["obj_inst"]->meta("isimp");
		$userval = $arr["obj_inst"]->meta("userval");
		$has_ex = $arr["obj_inst"]->meta("has_ex");
		$ex = $arr["obj_inst"]->meta("ex");

		$exes = array("" => "--vali--");
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_EXCEPTION")) as $c)
		{
			$exes[$c->prop("to")] = $c->prop("to.name");
		}

		foreach($this->get_props_from_obj($arr["obj_inst"]) as $pn => $pd)
		{
			if ($pn == "needs_translation" || $pn == "is_translated")
			{
				continue;
			}

			$exs = "";
			if ($has_ex[$pn] == 1)
			{
				$exs = html::select(array(
					"multiple" => 1,
					"name" => "ex[$pn]",
					"options" => $exes,
					"selected" => $this->make_keys($ex[$pn])
				));
			}

			$t->define_data(array(
				"prop" => $pd["caption"],
				"isimp" => html::checkbox(array(
					"name" => "isimp[$pn]",
					"value" => 1,
					"checked" => $isimp[$pn]
				)),
				"userval" => html::textbox(array(
					"name" => "userval[$pn]",
					"value" => $userval[$pn],
					"size" => 5
				)),
				"has_ex" => html::checkbox(array(
					"name" => "has_ex[$pn]",
					"value" => 1,
					"checked" => ($has_ex[$pn] == 1)
				)),
				"ex" => $exs
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
				"clid" => $class_id
			));
		}
		else
		{
			$class_i = get_instance($class_id == CL_DOCUMENT ? "doc" : $class_id);
			$properties = $class_i->load_from_storage(array(
				"id" => $type_o->prop("use_cfgform"),
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

		$cols = $this->get_cols_from_ds($arr["obj_inst"]);

		$this->_init_connect_props_table($t, $cols);

		$p2c = $arr["obj_inst"]->meta("p2c");		
		$isimp = $arr["obj_inst"]->meta("isimp");

		foreach($this->get_props_from_obj($arr["obj_inst"]) as $pn => $pd)
		{
			if (!$isimp[$pn] || $pn == "needs_translation" || $pn == "is_translated")
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

	function get_cols_from_ds($o)
	{
		if (!$o->prop("ds"))
		{
			return array();
		}

		// let the datasource parse the data and return the first row
		$ds_o = obj($o->prop("ds"));
		$ds_i = $ds_o->instance();
		return $ds_i->get_fields($ds_o);
	}

	function do_exec_import($o, $start_from_row = 0)
	{
		// for each line in the ds
		// read it
		// if there is an unique column
		//		check if there already is an object with that value
		//		if true, use that 
		// else
		//		create new object
		// match col => prop
		// save
		// loop
		// js redir back to change
		if ($o->prop("ds"))
		{
			set_time_limit(0);
			$sc = get_instance("scheduler");
			$sc->add(array(
				"event" => $this->mk_my_orb("do_check_import"),
				"time" => time() + 4 * 60,
				"sessid" => session_id()
			));

			$type_o = obj($o->prop("object_type"));
			$class_id = $type_o->prop("type");
			$p2c = $o->meta("p2c");
			$userval = $o->meta("userval");
			$has_ex = $o->meta("has_ex");
			$ex = $o->meta("ex");

			$folder = $o->prop("folder");
			if (!$folder)
			{
				$folder = $o->parent();
			}

			$line_n = 0;

			$ds_o = obj($o->prop("ds"));
			$ds_i = $ds_o->instance();
			$data_rows = $ds_i->get_objects($ds_o);

			// read props
			list($properties, $tableinfo, $relinfo) = $GLOBALS["object_loader"]->load_properties(array(
				"clid" => $class_id
			));

			$ex_i = get_instance(CL_OBJECT_IMPORT_EXCEPTION);

			foreach($data_rows as $line)
			{
				$line_n++;
				if ($start_from_row > $line_n)
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
						$o->prop("unique_id") => $line[$u_col]
					));
					if ($ol->count() > 0)
					{
						$dat = $ol->begin();
						echo "leidsin juba olemasoleva objekti ".$dat->name().", kasutan olemasolevat objekti <br>";
					}
					else
					{
						$dat = obj();
						if ($type_o->prop("use_cfgform"))
						{
							$dat->set_meta("cfgform_id", $type_o->prop("use_cfgform"));
						}
					}
				}
				else
				{
					$dat = obj();
					// if type has cfgform, set the object to use that so that when you change it, is show the correct form
					if ($type_o->prop("use_cfgform"))
					{
						$dat->set_meta("cfgform_id", $type_o->prop("use_cfgform"));
					}
				}

				$dat->set_class_id($class_id);
				$dat->set_parent($folder);
			
				$linebak = $line;
				if (!is_array($p2c))
				{
					continue;
				}
				foreach($p2c as $pn => $idx)
				{
					if ($pn == "needs_translation" || $pn == "is_translated" || $idx == "dontimp")
					{
						continue;
					}
					$line[$idx] = convert_unicode($line[$idx]);

					if ($has_ex[$pn] && is_array($ex[$pn]))
					{
						foreach($ex[$pn] as $iexp)
						{
							$line[$idx] = $ex_i->do_replace($iexp, $line[$idx]);
						}
					}

					// now, if the property is of type classificator, then we need to make a list of all options
					// search that and then select the correct one
					if ($properties[$pn]["type"] == "classificator")
					{
						if (!is_array($classif_cache[$pn]))
						{
							$clf = get_instance("classificator");
							$classif_cache[$pn] = $clf->get_options_for(array(
								"name" => $pn,
								"clid" => $class_id
							));
						}

						foreach($classif_cache[$pn] as $clf_id => $clf_n)
						{
							if (trim(strtolower($line[$idx])) == trim(strtolower($clf_n)))
							{
								$line[$idx] = $clf_id;
								break;
							}
						}
					}

					$dat->set_prop($pn, $line[$idx]);
					if ($properties[$pn]["store"] == "connect" && $line[$idx])
					{
						$rid = $relinfo[$properties[$pn]["reltype"]]["value"];
						$dat->connect(array(
							"to" => $line[$idx],
							"reltype" => $rid
						));
					}
				}
				$line = $linebak;

				if ($o->prop("folder_is_parent"))
				{
					$dat->set_parent($line["parent"]);
				}

				// now, if we have separate folder settings, then move to correct place.
				if (($fldfld = $o->prop("folder_field")))
				{	
					$flds = new aw_array($o->meta("fld_values"));
					$fldfld_val = $line[$fldfld];
					foreach($flds->get() as $fld_id => $fld_val)
					{
						if ($fld_val == $fldfld_val)
						{
							$dat->set_parent($fld_id);
						}
					}
				}

				// also uservals
				if (is_array($userval))
				{
					foreach($userval as $uv_pn => $uv_pv)
					{
						if ($uv_pv != "")
						{
							$dat->set_prop($uv_pn, $uv_pv);	
						}
					}
				}

				$dat->save();
				echo "importisin objekti ".$dat->name()." (".$dat->id().") kataloogi ".$dat->parent()." <br>\n";
				flush();

				if (($line_n % 10) == 1)
				{
					$o->set_meta("import_last_time", time());
					$o->set_meta("import_row_count", $line_n);
					$o->save();
				}
			}
			$this->do_mark_finish_import($o);
			echo "Valmis! <br>\n";
			echo "<script language=javascript>setTimeout(\"window.location='".$this->mk_my_orb("change", array("id" => $o->id()))."'\", 5000);</script>";
			die();
		}
	}

	function do_init_import($o)
	{
		$o->set_meta("import_status", 1);
		$o->set_meta("import_started_at", time());
		$o->save();
	}

	function do_mark_finish_import($o)
	{
		$o->set_meta("import_status", 0);
		$o->save();
	}

	/**

		@attrib name=do_check_import nologin="1"

	**/
	function do_check_import()
	{
		$ol = new object_list(array(
			"class_id" => CL_OBJECT_IMPORT,
			"site_id" => array(),
			"lang_id" => array()
		));
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->meta("import_status") == 1 && $o->meta("import_last_time") < (time() - 3 * 60))
			{
				echo "restart import for ".$o->id()." <br>";
				$this->do_exec_import($o, $o->meta("import_row_count"));
			}
			echo "for o ".$o->id()." status = ".$o->meta("import_status")." last time = ".date("d.m.Y H:i", $o->meta("import_last_time"))." <br>";
		}
	}

	function callback_pre_edit($arr)
	{
		if ($arr["obj_inst"]->meta("import_status") == 1)
		{
			$sc = get_instance("scheduler");
			$sc->add(array(
				"event" => $this->mk_my_orb("do_check_import"),
				"time" => time() + 4 * 60,
				"sessid" => session_id()
			));
		}
	}
}
?>
