<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_table.aw,v 1.1 2005/02/28 10:45:37 duke Exp $
// property_table.aw - Tabel 
/*

@classinfo syslog_type=ST_PROPERTY_TABLE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@default group=designer

@property design_table type=table no_caption=1
@caption Veerud

@groupinfo designer caption="Tulbad"

*/

class property_table extends class_base
{
	function property_table()
	{
		$this->init(array(
			"clid" => CL_PROPERTY_TABLE
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "design_table":
				$this->generate_design_table(&$arr);
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
			));
		};

		$this->_add_row(array(
			"t" => &$t,
			"key" => "new",
			"name" => $arr["prop"]["name"],
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
		));
	}

	function generate_get_property($arr)
	{
		$el = new object($arr["id"]);
		$sys_name = $arr["name"];
		$gpblock = "";
		$gpblock .= "case \"${sys_name}\":\n";
		$gpblock .= "\t\t\t\t\$this->generate_${sys_name}(\$arr);\n";
		$gpblock .= "\t\t\t\tbreak;\n";
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
		$rv = "function $name(\$arr)\n";
		$rv .= "\t{\n";
		$rv .= "\t\t" . '$t = &$arr["prop"]["vcl_inst"];' . "\n";
		foreach($els->arr() as $el)
		{
			$sys_name = strtolower(preg_replace("/\s/","_",$el->name()));
			$rv .= "\t\t" . '$t->define_field(array(' . "\n";
			$rv .= "\t\t\t" . '"name" => "' . $sys_name . '",' . "\n";
			$rv .= "\t\t\t" . '"caption" => "' . $el->name() . '",' . "\n";
			$rv .= "\t\t\t" . '"width" => "' . $el->prop("width") . '",' . "\n";
			$rv .= "\t\t\t" . '"sortable" => "' . $el->prop("sortable") . '",' . "\n";
			$rv .= "\t\t" . ');' . "\n";
			
		};
		$rv .= "\t};\n";
		return $rv;
	}
}
?>
