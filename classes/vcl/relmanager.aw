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
class relmanager extends aw_template 
{
	function relmanager()
	{
		$this->tpl_init();
	}

	function add_item($arr)
	{


	}

	function init_rel_manager($arr)
	{
		$prop = $arr["prop"];
		$obj = $arr["obj_inst"];
		$conns = $obj->connections_from(array(
			"type" => $prop["reltype"],
			// XXX: should we support multiple classes?
			"class" => $prop["clid"][0],
		));	
		
		$this->kala = "tursk";

		// load properties for the target class and then add lines to the
		// table
		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_properties(array(
			"clid" => $prop["clid"][0],
		));
		
		load_vcl("table");
		$this->t = new aw_table(array("layout" => "generic"));

		$rv = array();

		$this->t->define_field(array(
			"name" => "chooser",
			"caption" => "Vali üks",
			"align" => "center",
			"width" => 100,
		));

		$proplist = is_array($prop["props"]) ? $prop["props"] : array($prop["props"]);

		$xproplist = array();

		foreach($props as $item)
		{
			if (in_array($item["name"],$proplist))
			{
				$this->t->define_field(array(
					"name" => $item["name"],
					"caption" => $item["caption"],
					//"sortable" => 1,
				));

				$xproplist[$item["name"]] = $item;
			};
		};

		// ------------
		

		// file klassis sisaldab vastav valitud omadus faili raw nime
		// mul aga on vaja objekti nime
		foreach($conns as $conn)
		{
			$to_o = $conn->to();
			$to_prop = $to_o->properties();
			if ($to_o->class_id() == CL_FILE)
			{
				$to_prop["file"] = $to_prop["name"];
			};
			$to_prop["chooser"] = html::radiobutton(array(
				"name" => $prop["name"],
				"value" => $to_o->id(),
				"checked" => $prop["value"] == $to_o->id(),
			));
			$this->t->define_data($to_prop);
		};

		// now add items for each property

		// A new name perhaps then? Then .. how do I make sure that name is not used anywhere
		// else? By using a special prefix? Yees .. that is it.

		$prefix = "cb_emb[" . $prop["name"] . "][new]";
		
		// this has to be optional, I might now want any "Uus" captions
		$addline = array(
			"chooser" => html::hidden(array(
				"name" => $prefix . "[parent]",
				// XXX: There might be a need to use different parents
				"value" => $arr["obj_inst"]->parent(),
			)) . " Uus ",
		);

		// this _NEEDS_ to be done differently .. like for example
		// with widgets or something .. urk.

		foreach($proplist as $propitem)
		{
			$type = $xproplist[$propitem]["type"];
			switch($type)
			{
				case "fileupload":
					$widget = html::fileupload(array(
						"name" => $prefix . "[" . $propitem . "]",
					));
					break;

				default:
					$widget = html::textbox(array(
						"name" => $prefix . "[" . $propitem . "]",
					));
					break;
			};
			$addline[$propitem] = $widget;
		};

		$this->t->define_data($addline);
	}

	function get_html()
	{
		return $this->t->draw();
	}

	////
	// !This processes the newly added relation
	function process_relmanager($arr)
	{
		// now I need to load the bloody properties again and do some shit with them
		$clid = $arr["prop"]["relinfo"]["clid"][0];
		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_properties(array(
			"clid" => $clid,
		));
		$proplist = is_array($arr["prop"]["props"]) ? $arr["prop"]["props"] : array($arr["prop"]["props"]);

		$xproplist = array();

		foreach($props as $item)
		{
			if (in_array($item["name"],$proplist))
			{
				$xproplist[$item["name"]] = $item;
			};
		};

		$propname = $arr["prop"]["name"];

		$req = $arr["request"]["cb_emb"][$propname];

		$arglist = array();

		foreach($xproplist as $name => $act_prop)
		{
			if ($act_prop["type"] == "fileupload")
			{
				$_fileinf = $_FILES["cb_emb"];
				$filename = $_fileinf["name"][$propname]["new"][$name];
				$filetype = $_fileinf["type"][$propname]["new"][$name];
				$tmpname = $_fileinf["tmp_name"][$propname]["new"][$name];
				// tundub, et polnud sellist faili, eh?
				if (empty($tmpname))
				{
					return false;
				};
				$arglist[$name] = array(
					"tmp_name" => $tmpname,
					"type" => $filetype,
					"name" => $filename,
				);
			}
			else
			{
				if (!empty($req["new"][$name]))
				{
					$arglist[$name] = $req["new"][$name];
				};
			};

		};

		if (sizeof($arglist) == 0)
		{
			return false;
		};

		$arglist["parent"] = $arr["obj_inst"]->parent();

		$inst = get_instance($clid);
		$inst->id_only = true;
		$obj_id = $inst->submit($arglist);

		$arr["obj_inst"]->connect(array(
			"to" => $obj_id,
			"reltype"=> $arr["prop"]["relinfo"]["value"],
		));
	}

};
?>
