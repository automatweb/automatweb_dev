<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/obj_table_conf.aw,v 1.1 2005/03/17 18:17:07 kristo Exp $
// obj_table_conf - Objekti tabeli conf 
/*

@classinfo relationmgr=yes no_status=1 syslog_type=ST_OBJ_TABLE_CONF

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property proptoolbar type=toolbar store=no no_caption=1 group=proptab
@property proptable type=table store=no no_caption=1 group=proptab

@property sep type=textbox
@caption Eraldaja ühe tulba elementide vahel

@groupinfo proptab caption=Objektitabel

@reltype GROUP value=1 clid=CL_GROUP
@caption Kasutajagrupp

*/
class obj_table_conf extends class_base
{
	var $data = array(
		"oid" => array("name" => "ID", "type" => "int", "sortable" => true),
		"parent" => array("name" => "parent", "type" => "int", "sortable" => true),
		"name" => array("name" => "Nimi", "type" => "text", "sortable" => true),
		"createdby" => array("name" => "Looja", "type" => "text", "sortable" => true),
		"created" => array("name" => "Millal loodud", "type" => "time", "sortable" => true),
		"modifiedby" => array("name" => "Muutja", "type" => "text", "sortable" => true),
		"modified" => array("name" => "Millal muudetud", "type" => "time", "sortable" => true),
		"class_id" => array("name" => "T&uuml;&uuml;p", "type" => "int", "sortable" => true),
		"status" => array("name" => "Aktiivsus", "type" => "int"),
		"lang_id" => array("name" => "Keel", "type" => "int", "sortable" => true),
		"comment" => array("name" => "Kommentaar", "type" => "text", "sortable" => true),
		"jrk" => array("name" => "J&auml;rjekord", "type" => "int"),
		"period" => array("name" => "Periood", "type" => "int", "sortable" => true),
		"alias" => array("name" => "Alias", "type" => "text", "sortable" => true),
		"perioodiline" => array("name" => "Periodic", "type" => "int"),
		"site_id" => array("name" => "Saidi ID", "type" => "int", "sortable" => true),
		"activate_at" => array("name" => "Aktiveeri millal", "type" => "time", "sortable" => true),
		"deactivate_at" => array("name" => "Deaktiveeri millal", "type" => "time", "sortable" => true),
		"autoactivate" => array("name" => "Aktiveeri automaatselt", "type" => "int"),
		"autodeactivate" => array("name" => "Deaktiveeri automaatselt", "type" => "int"),
		"---- actions" => array("name" => "---- actions"),
		"icon" => array("name" => "Ikoon"),
		"link" => array("name" => "Link"),
		"select" => array("name" => "Vali"),
		"change" => array("name" => "Muuda"),
		"delete" => array("name" => "Kustuta"),
		"acl" => array("name" => "Muuda ACLi"),
		"java" => array("name" => "Java menu"),
		"---- use next ones with caution!" => array("name" => "---- use next ones with caution!"),
		"hits" => array("name" => "hits", "type" => "int", "sortable" => true),
		"last" => array("name" => "last", "type" => "text", "sortable" => true),
		"visible" => array("name" => "visible", "type" => "int", "sortable" => true),
		"doc_template" => array("name" => "doc_template", "type" => "int", "sortable" => true),
		"brother_of" => array("name" => "Mille vend", "type" => "int", "sortable" => true),
		"cachedirty" => array("name" => "Cache dirty", "type" => "int", "sortable" => true),
		"metadata" => array("name" => "metadata", "type" => "text", "sortable" => true),
		"meta" => array("name" => "meta", "type" => "text", "sortable" => true),
		"subclass" => array("name" => "Subclass", "type" => "int", "sortable" => true),
		"cachedata" => array("name" => "cachedata", "type" => "text", "sortable" => true),
		"flags" => array("name" => "Flags", "type" => "int", "sortable" => true),
	);
	
	function obj_table_conf()
	{
		$this->init(array(
			"clid" => CL_OBJ_TABLE_CONF
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			case "proptable":
				$this->gen_proptable($arr);
			break;
			case "proptoolbar":
				$this->gen_proptoolbar($arr);
			break;
		};
		return $retval;
	}
	
	function gen_proptoolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
   			"name" => "save",
    		"img" => "save.gif",
    		"action" => "submit",
    		"tooltip" => "Salvesta muudatused",
    	));
	}
	
	function get_options()
	{
		$retval[] = "";
		foreach ($this->data as $key => $value)
		{
			$retval[$key] = $value["name"];
		}
		return $retval;
	}
	
	function gen_proptable($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "name",
			"caption" => "Pealkiri",
			"align" => "center",	
		));
		
		$table->define_field(array(
			"name" => "jrk",
			"caption" => "Järjekord",
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "sortable",
			"caption" => "Sorteeritav",	
		));
		
		$table->define_field(array(
			"name" => "property",
			"caption" => "Omadus",	
		));
		
		if($arr["obj_inst"]->meta("cols"))
		{
			$i = 0;
			foreach ($arr["obj_inst"]->meta("cols") as $col => $item)
			{
				unset($selects);
				foreach ($item['col'] as $fieldname)
				{
					if(!$fieldname)
					{
						continue;
					}
					$selects .= html::select(array(
						"name" => "col_info[$i][col][]",
						"options" => $this->get_options(),
						"value" => $fieldname,
					));
				}
				
				$selects .= html::select(array(
						"name" => "col_info[$i][col][]",
						"options" => $this->get_options(),
				));
				
				$table->define_data(array(
					"name" => html::textbox(array(
						"name"  => "col_info[$i][title]",
						"value" => $item['title'],
					)),
					"jrk" => html::textbox(array(
						"name"  => "col_info[$i][ord]",
						"size" => 2,
						"value" => $item['ord'],
					)),
					"sortable" => html::checkbox(array(
						"name" => "col_info[$i][sortable]",
						"checked" => $item["sortable"],
					)),
					"property" => $selects,
					"ord" => $item['ord'],
					"fieldname" => $i,
				));
				$i++;
			}	
		}
		
		$table->define_data(array(
			"name" => html::textbox(array(
				"name"  => "new_name",
			)),
			"jrk" => html::textbox(array(
				"name"  => "new_jrk",
				"size" => 2,
			)),
			"sortable" => html::checkbox(array(
				"name" => "new_sortable",
				"value" => 1,
			)),
			"property" => html::select(array(
				"options" => $this->get_options(),
				"name" => "new_prop",
			)),
		));
		$table->set_sortable(false);
	}
	
	function callback_pre_save($arr)
	{
		if($arr["request"]['new_prop'] && $arr["request"]['new_name'])
		{
			$arr["request"]["col_info"][] = array(
				'title' => $arr["request"]['new_name'],
				'ord' => $arr["request"]['new_jrk'],
				'sortable' => $arr["request"]['new_sortable'],
				'col' => array(1 => $arr["request"]['new_prop']),
			);
		}
		if($arr["request"]["col_info"])
		{
			//Fuck this sort... 
			foreach ($arr["request"]["col_info"] as $key => $value)
			{
				if($value["title"] && is_array($value["col"]))
				{
					unset($cols);
					foreach ($value["col"] as $col)
					{
						if($col)
						{
							$cols[] = $col;
						}
					}
					$value['col'] = $cols;
					$sortedarr[$value['ord']] = $value;
				}
			}
			
			
			ksort($sortedarr);
			if(is_array($arr["request"]["col_info"]))
			{
				$arr["obj_inst"]->set_meta("cols", array_values($sortedarr));
			}
		}
	}
	
	function table_row($row, &$tbl_ref)
	{
		$dat = array();
		foreach($this->ob->meta("cols") as $clid => $cldat)
		{
			$str = array();
			foreach($cldat["col"] as $idx => $colname)
			{
				$str[] =$row[$colname];
			}
			$dat["col_".$clid] = join($this->ob->meta("sep"), $str);
		}
		$tbl_ref->define_data($dat);
	}
	
	function init_table($id, &$tbl_ref)
	{
		$this->ob = obj($id);
		if (is_array($this->ob->meta("cols")))
		{
			foreach($this->ob->meta("cols") as $clid => $cldat)
			{
				// pick the first element's type as the type for the column
				reset($cldat["col"]);
				list(,$clname) = each($cldat["col"]);

				// XXX: make the align of fields configurable
				$align = ($clname == "name") ? "left" : "center";

				$row = array(
					"name" => "col_".$clid,
					"caption" => ($cldat["title"] == "" ? $clname : $cldat["title"]),
					"talign" => "center",
					"align" => $align,
					"sortable" => $cldat["sortable"],
					"numeric" => ($this->data[$clname]["type"] == "int" || $this->data[$clname]["type"] == "time"),
				);
				if ($this->data[$clname]["type"] == "time")
				{
					$row["type"] = "time";
					$row["format"] = "d.m.y / H:i";
				}
				$tbl_ref->define_field($row);
			}
		}
	}
}
?>