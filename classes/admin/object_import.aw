<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/object_import.aw,v 1.3 2004/05/12 13:43:06 kristo Exp $
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

@property ds type=relpicker reltype=RELTYPE_DATASOURCE
@caption Andmeallikas

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

@reltype DATASOURCE value=3 clid=CL_ABSTRACT_DATASOURCE
@caption andmeallikas

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

		$cols = $this->get_cols_from_ds($arr["obj_inst"]);

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

	function do_exec_import($o)
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
		if ($o->prop("ds") && $o->prop("folder"))
		{
			$type_o = obj($o->prop("object_type"));
			$class_id = $type_o->prop("type");
			$p2c = $o->meta("p2c");		

			$line_n = 0;

			$ds_o = obj($o->prop("ds"));
			$ds_i = $ds_o->instance();
			$data_rows = $ds_i->get_objects($ds_o);

			foreach($data_rows as $line)
			{
				$line_n++;
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
					}
				}
				else
				{
					$dat = obj();
				}

				$dat->set_class_id($class_id);
				$dat->set_parent($o->prop("folder"));

				// if type has cfgform, set the object to use that so that when you change it, is show the correct form
				if ($type_o->prop("use_cfgform"))
				{
					$dat->set_meta("cfgform_id", $type_o->prop("use_cfgform"));
				}
				
				foreach($p2c as $pn => $idx)
				{
					$dat->set_prop($pn, $line[$idx]);
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
