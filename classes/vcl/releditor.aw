<?php
/*
// !Displays a table of relations and adds one line to it that allows to modify data
// For initialization I need to know the object id and relation type and properties to put 
// in the table

// then add_item does the heavy lifting

// and finally get_html returns the table. uh. wooh

// .. I need an additonal option .. multiple .. if it is not set, then it acts exactly
// like the single relpicker .. radiobutton is used for selecting data

// if it is not set, then multiple relations can be picked .. and in this case 
// a checkbox is used. But this can way .. first I need to get saving to work


*/
class releditor extends aw_template 
{
	function releditor()
	{
		$this->tpl_init();
	}

	function init_rel_editor($arr)
	{
		$prop = $arr["prop"];
		$obj = $arr["obj_inst"];

		$clid = $arr["prop"]["clid"][0];

		$props = $arr["prop"]["props"];
		$xprops = array();

		$errors = false;

		if (!is_array($props))
		{
			$errors = true;
			$xprops[] = array(
				"type" => "text",
				"caption" => " ",
				"value" => "Viga $prop[name] definitsioonis (omadused defineerimata!)",
			);
		};

		if (empty($clid))
		{
			$errors = true;
			$xprops[] = array(
				"type" => "text",
				"caption" => " ",
				"value" => "Viga $prop[name] definitsioonis (seose tüüp defineerimata!)",
			);
		};

		if ($errors)
		{
			return $xprops;
		};


		// now I have to query the target class and add the fields in here

		$t = get_instance($clid);
		$t->init_class_base();
		$emb_group = "general";

		// now then.
		$all_props = $t->get_active_properties(array(
			"group" => $emb_group,
		));

		$act_props = array();

		$obj_inst = false;

		// load the first connection.
		// It should be relatively simple to extend this so that it can load
		// a programmaticaly specified relation
		if ($prop["rel_id"] == "first")
		{
			$o = $arr["obj_inst"];
			$conns = $o->connections_from(array(
				"reltype" => $prop["reltype"],
			));
			$key = reset($conns);
			if ($key)
			{
				$obj_inst = $key->to();
			};
		};

		foreach($all_props as $key => $prop)
		{
			if (in_array($key,$props))
			{
				$act_props[$key] = $prop;
			};
		};
		
		if (is_object($obj_inst))
		{
			$act_props["id"] = array(
				"type" => "hidden",
				"name" => "id",
				"value" => $obj_inst->id(),
			);
		};


		$xprops = $t->parse_properties(array(
			"properties" => $act_props,
			"name_prefix" => "cba_emb",
			"obj_inst" => $obj_inst,
		));
		
		
		return $xprops;
	}

	function process_releditor($arr)
	{
		$prop = $arr["prop"];
		$obj = $arr["obj_inst"];

		$clid = $arr["prop"]["clid"][0];
		$clinst = get_instance($clid);

		$emb = $arr["request"]["cba_emb"];
		
		$cfgu = get_instance("cfg/cfgutils");	
		$props = $cfgu->load_properties(array(
			"clid" => $clid,
		));

		$propname = $prop["name"];
		$proplist = is_array($prop["props"]) ? $prop["props"] : array($prop["props"]);

		$el_count = 0;

		foreach($props as $item)
                {
			// if that property is in the list of the class properties, then
			// process it
                        if (in_array($item["name"],$proplist))
                        {
				if ($item["type"] == "fileupload")
				{
					$name = $item["name"];
					$_fileinf = $_FILES["cba_emb"];
					$filename = $_fileinf["name"][$name];
					$filetype = $_fileinf["type"][$name];
					$tmpname = $_fileinf["tmp_name"][$name];
					// tundub, et polnud sellist faili, eh?
					if (empty($tmpname))
					{
						return false;
					};
					$emb[$name] = array(
						"tmp_name" => $tmpname,
						"type" => $filetype,
						"name" => $filename,
					);
				}
				else
				{
					// this shit takes care of those non-empty select boxes
					if ($emb[$item["name"]] && $item["type"] != "datetime_select")
					{
						$el_count++;
					};
				};

                        };
                };
		
		if ($el_count == 0)
		{
			return false;
		};

		$emb["group"] = "general";
		$emb["parent"] = $obj->parent();
		$emb["return"] = "id";

		$reltype = $arr["prop"]["reltype"];

		$obj_id = $clinst->submit($emb);

		$obj->connect(array(
			"to" => $obj_id,
			"reltype" => $arr["prop"]["reltype"],
		));
	}

	function get_html()
	{
		return "here be releditor";
		//return $this->t->draw();
	}

	////
	// !This processes the newly added relation
	function process_relation($arr)
	{

	}

};
?>
