<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_table.aw,v 1.4 2005/03/11 20:03:47 duke Exp $
// property_table.aw - Tabel 
/*

@classinfo syslog_type=ST_PROPERTY_TABLE relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@default group=designer

	@property design_table type=table no_caption=1
	@caption Veerud

@default group=data

	@property demo_data_table type=table no_caption=1
	@caption Demo andmed

@groupinfo designer caption="Tulbad"
@groupinfo data caption="Demo andmed"

*/

class property_table extends class_base
{
	function property_table()
	{
		$this->init(array(
			"clid" => CL_PROPERTY_TABLE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "design_table":
				$this->generate_design_table(&$arr);
				break;

			case "demo_data_table":
				$this->generate_demo_data_table(&$arr);
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
			case "design_table":
				$this->update_columns($arr);
				break;

			case "demo_data_table":
				$this->save_demo_data_t($arr);
				break;

		}
		return $retval;
	}	

	function generate_design_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "ord",
			"caption" => "Jrk",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "parent",
			"caption" => "&Uuml;lemtulp",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "caption",
			"caption" => "Pealkiri",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "width",
			"caption" => "Laius",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "sortable",
			"caption" => "Sorteeritav",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "align",
			"caption" => "Joondamine",
			"align" => "center",
		));

		$t->set_sortable(false);

		$ol = new object_list(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => CL_PROPERTY_TABLE_COLUMN,
		));

		foreach($ol->arr() as $o)
		{
			$this->_add_row(array(
				"t" => &$t,
				"key" => $o->id(),
				"name" => $arr["prop"]["name"],
				"data" => $o->properties(),
				"ol" => $ol
			));
		};

		$this->_add_row(array(
			"t" => &$t,
			"key" => "new",
			"name" => $arr["prop"]["name"],
			"ol" => $ol
		));


		// XXX: object_list kõigist veergudest ja lisaks üks tühi rida

	}


	function update_columns($arr)
	{
		$name = $arr["prop"]["name"];
		$coldata = $arr["request"][$name];
		foreach($coldata as $key => $coldat)
		{
			if ($key == "new" && strlen($coldat["caption"]) > 0)
			{
				$o = new object();
				$o->set_parent($arr["obj_inst"]->id());
				$o->set_class_id(CL_PROPERTY_TABLE_COLUMN);
				$o->set_status(STAT_ACTIVE);
			}
			elseif (!is_numeric($key))
			{
				continue;
			}
			else
			{
				$o = new object($key);
			};

			$o->set_name($coldat["caption"]);
			$o->set_prop("ord",$coldat["ord"]);
			$o->set_prop("width",$coldat["width"]);
			$o->set_prop("sortable",$coldat["sortable"]);
			$o->set_prop("align",$coldat["align"]);
			$o->set_prop("c_parent", $coldat["parent"]);

			//print "creating new column object from the following data<br>";
			$o->save();
		};
	}

	function _add_row($arr)
	{
		// needs t 
		// needs key
		// needs row values
		$t = &$arr["t"];
		$key = $arr["key"];
		$name = $arr["name"];
		//arr($arr["data"]);
		$t->define_data(array(
			"ord" => html::textbox(array(
				"name" => "${name}[${key}][ord]",
				"size" => 2,
				"value" => $arr["data"]["ord"],
			)),
			"parent" => html::select(array(
				"name" => "${name}[${key}][parent]",
				"value" => $arr["data"]["c_parent"],
				"options" => array("" => "") + $arr["ol"]->names()
			)),
			"width" => html::textbox(array(
				"name" => "${name}[${key}][width]",
				"size" => 4,
				"value" => $arr["data"]["width"],
			)),
			"caption" => html::textbox(array(
				"name" => "${name}[${key}][caption]",
				"size" => 30,
				"value" => $arr["data"]["name"],

			)),
			"sortable" => html::checkbox(array(
				"name" => "${name}[${key}][sortable]",
				"ch_value" => 1,
				"checked" => ($arr["data"]["sortable"] == 1),
			)),
			"align" => html::select(array(
				"name" => "${name}[${key}][align]",
				"options" => array("" => "", "left" => "Vasakul", "center" => "Keskel", "right" => "Paremal"),
				"selected" => $arr["data"]["align"],
			))
		));
	}

	function generate_demo_data_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);

		$ol = new object_list(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => CL_PROPERTY_TABLE_COLUMN,
		));
		$ol->sort_by(array(
			"prop" => "ord"
		));

		foreach($ol->arr() as $o)
		{
			$dd = array(
				"name" => $o->name(),
				"caption" => $o->name(),
				"sortable" => $o->prop("sortable"),
				"width" => $o->prop("width"),
				"nowrap" => $o->prop("nowrap"),
				"align" => $o->prop("align"),
			);
			if ($o->prop("c_parent"))
			{
				$dd["parent"] = $o->prop_str("c_parent");
			}
			$t->define_field($dd);
		}

		$dd = safe_array($arr["obj_inst"]->meta("demo_data"));
		$dd[] = array();

		$nr = 1;
		foreach($dd as $row)
		{
			$row_def = array();
			foreach($ol->arr() as $o)
			{
				$row_def[$o->prop("name")] = html::textbox(array(
					"name" => "dd[$nr][".$o->prop("name")."]",
					"value" => $dd[$nr][$o->prop("name")],
				));
			}
			$nr++;

			$t->define_data($row_def);
		}
	}

	function save_demo_data_t($arr)
	{
		$dd = array();

		$nr = 1;
		foreach(safe_array($arr["request"]["dd"]) as $nr => $row)
		{
			$data = array();
			foreach(safe_array($row) as $k => $v)
			{
				if (trim($v) != "")
				{
					$data[$k] = $v;
				}
			}

			if (count($data))
			{
				$dd[$nr] = $data;
			}
			$nr++;
		}

		$arr["obj_inst"]->set_meta("demo_data", $dd);
	}

	function generate_get_property($arr)
	{
		$el = new object($arr["id"]);
		$sys_name = $arr["name"];
		$gpblock = "";
		$gpblock .= "\t\t\tcase \"${sys_name}\":\n";
		$gpblock .= "\t\t\t\t\$this->generate_${sys_name}(\$arr);\n";
		$gpblock .= "\t\t\t\tbreak;\n\n";
		return array(
			"get_property" => $gpblock,
			"generate_methods" => array("generate_${sys_name}"),
		);
	}

	function generate_method($arr)
	{
		$name = $arr["name"];
		$obj = new object($arr["id"]);
		$els = new object_list(array(
			"parent" => $arr["id"],
			"class_id" => CL_PROPERTY_TABLE_COLUMN,
		));
		$els->sort_by(array(
			"prop" => "ord"
		));
		$rv = "\tfunction $name(\$arr)\n";
		$rv .= "\t{\n";
		$rv .= "\t\t" . '$t = &$arr["prop"]["vcl_inst"];' . "\n";
		foreach($els->arr() as $el)
		{
			$sys_name = strtolower(preg_replace("/\s/","_",$el->name()));
			$rv .= "\t\t" . '$t->define_field(array(' . "\n";
			$rv .= "\t\t\t" . '"name" => "' . $sys_name . '",' . "\n";
			$rv .= "\t\t\t" . '"caption" => "' . $el->name() . '",' . "\n";
			if ($el->prop("width"))
			{
				$rv .= "\t\t\t" . '"width" => "' . $el->prop("width") . '",' . "\n";
			}
			if ($el->prop("sortable"))
			{
				$rv .= "\t\t\t" . '"sortable" => "' . $el->prop("sortable") . '",' . "\n";
			}
			if ($el->prop("align") != "")
			{
				$rv .= "\t\t\t" . '"align" => "' . $el->prop("align") . '",' . "\n";
			}
			if ($el->prop("c_parent") != "")
			{
				$rv .= "\t\t\t" . '"parent" => "' . $el->prop_str("c_parent") . '",' . "\n";
			}
			$rv .= "\t\t" . '));' . "\n";
			
		};
		$rv .= "\t}\n\n";
		return $rv;
	}

	function get_visualizer_prop($el, &$propdata)
	{
		$t = new vcl_table();
		$table_items = new object_list(array(
			"parent" => $el->id(),
			"class_id" => CL_PROPERTY_TABLE_COLUMN
		));
		$table_items->sort_by(array(
			"prop" => "ord"
		));
		foreach($table_items->arr() as $table_item)
		{
			$sortable = $table_item->prop("sortable");
			$celldata = array(
				"name" => $table_item->name(),
				"caption" => $table_item->name(),
				"width" => $table_item->prop("width"),
			);
			if ($table_item->prop("sortable"))
			{
				$celldata["sortable"] = 1;
			};
			if ($table_item->prop("c_parent"))
			{
				$celldata["parent"] = $table_item->prop_str("c_parent");
			};

			if ($table_item->prop("align") != "")
			{
				$celldata["align"] = $table_item->prop("align");
			};
			$t->define_field($celldata);
		};

		// get demo data
		$dd = safe_array($el->meta("demo_data"));
		foreach($dd as $row)
		{
			$t->define_data($row);
		}
		$propdata["vcl_inst"] = $t;
	}
}
?>
