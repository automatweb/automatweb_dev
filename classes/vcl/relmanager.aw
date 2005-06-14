<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/relmanager.aw,v 1.12 2005/06/14 10:34:51 duke Exp $
/*
// !Displays a table of relations and adds one line with edit fields to allow adding
// of new objects

// .. I need an additonal option .. multiple .. if it is not set, then it acts exactly
// like the single relpicker .. radiobutton is used for selecting data

// can I perhaps merge those things?

*/
class relmanager extends aw_template 
{
	function relmanager()
	{
		$this->tpl_init();
	}

	function init_rel_manager($arr)
	{
		$view = !empty($arr["view"]) ? $arr["view"] : 0;
		$prop = $arr["prop"];
		$arr["prop"]["_done"] = 1;
		$obj = $arr["obj_inst"];

		if ($view)
		{
			$prop["type"] = "text";
			return PROP_OK;
		};

		load_vcl("table");
		$this->t = new aw_table(array(
			"layout" => "generic",
		));

		// XX: wtf is this?
		if ($arr["new"])
		{
			return false;
		};

		if (!is_object($obj))
		{
			return false;
		};

		if (!is_oid($obj->id()))
		{
			return false;
		};

		$params = array(
			"type" => $prop["reltype"],
		);

		if (!empty($prop["clid"]))
		{
			$params["class"] = $prop["clid"][0];
		};

		// XXX: should multiple classes be supported?
		$conns = $obj->connections_from($params);

		// alright, I need an alternative way to figure this thing out

		/*
		$conns = $obj->connections_from(array(
			"type" => $prop["reltype"],
			"class" => $prop["clid"][0],
		));	
		*/


		$rv = array();

		// adding a chooser should be optional
		// so that I don't confuse the user

		$use_chooser = true;

		// and I need a way to specify multiple add fields
		if (isset($prop["chooser"]) && $prop["chooser"] == "no")
		{
			$use_chooser = false;
		};

		if ($use_chooser)
		{
			$this->t->define_field(array(
				"name" => "chooser",
				"caption" => t("Vali üks"),
				"align" => "center",
				"width" => 100,
			));
		};



		if (empty($prop["props"]))
		{
			return false;
		};

		$proplist = is_array($prop["props"]) ? $prop["props"] : array($prop["props"]);

		// first set the keys, this makes the properties be in the same order you
		// define them, and then figure out property data for each
		
		$xproplist = array_flip(array_values($proplist));
		
		// load properties for the target class and then add lines to the
		// table
		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_properties(array(
			"clid" => $prop["clid"][0],
		));

		foreach($props as $item)
		{
			if (in_array($item["name"],$proplist))
			{
				$xproplist[$item["name"]] = $item;
			};
		};

		foreach($xproplist as $name => $item)
		{
			$this->t->define_field(array(
				"name" => $item["name"],
				"caption" => $item["caption"],
			));
		};

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
			if ($to_o->class_id() == CL_IMAGE)
			{
				$to_prop["file"] = "";
			};
			
			if ($use_chooser)
			{
				$to_prop["chooser"] = html::radiobutton(array(
					"name" => $prop["name"],
					"value" => $to_o->id(),
					"checked" => $prop["value"] == $to_o->id(),
				));
			};
			$this->t->define_data($to_prop);
		};

		// now add items for each property

		// A new name perhaps then? Then .. how do I make sure that name is not used anywhere
		// else? By using a special prefix? Yees .. that is it.


		if (strpos($prop["name"],"[") !== false)
		{
			$bracket = strpos($prop["name"],"[");
			$pre = substr($prop["name"],0,$bracket);
			$aft = substr($prop["name"],$bracket);
			$prefix = "cb_emb[" . $pre . "]" . $aft . "[new]";
				//$newname = $args["name_prefix"] . "[$pre]" . $aft;
		}
		else
		{
			$prefix = "cb_emb[" . $prop["name"] . "][new]";


		};

		$addline = array();
		
		// this has to be optional, I might now want any "Uus" captions
		if ($use_chooser)
		{
			$addline["chooser"] = "Uus";
		};

		// this _NEEDS_ to be done differently .. like for example
		// with widgets or something .. urk.

		// how do I make this thing support different fields then?

		// add an object reference to the cell and the use it in get_html? yees?

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
		if ($this->t)
		{
			return $this->t->draw();
		};
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
		$arglist["return"] = "id";

		$inst = get_instance($clid);
		$obj_id = $inst->submit($arglist);

		// TODO: Implement error checking! What if the relinfo value is not valid?
		$arr["obj_inst"]->connect(array(
			"to" => $obj_id,
			"reltype"=> $arr["prop"]["relinfo"]["value"],
		));

		// now check, how many connections we have
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => $arr["prop"]["relinfo"]["value"],
		));

		// set the newly created connection as the value of the element, if this was the first entry
		if (sizeof($conns) == 1)
		{
			$arr["prop"]["value"] = $obj_id;
		}

	}

};
?>
