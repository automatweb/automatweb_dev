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
					"sortable" => 1,
				));
			};
		};

		// ------------
		

		foreach($conns as $conn)
		{
			$to_o = $conn->to();
			$to_prop = $to_o->properties();
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
		
		// I also need the class name, and action name .. reforb I can add myself yees?
		// and then I also need parent. And that should do it then.

		// aha put the point is that I do not need it!

		// I need to retain the current name of the thing so that I can store the value.
		// xmail needs to have the value of the setting .. otherwise it gets 
		// much too complicated. So how do I get it work. How do I make saving to work?

		// how do I get saving to work?
		// that translates into getting 
		$addline = array(
			"chooser" => html::hidden(array(
				"name" => $prefix . "[parent]",
				// XXX: There might be a need to use different parents
				"value" => $arr["obj_inst"]->parent(),
			)) . " Uus ",
		);

		foreach($proplist as $propitem)
		{
			$addline[$propitem] = html::textbox(array(
					"name" => $prefix . "[" . $propitem . "]",
			));
		};

		$this->t->define_data($addline);
	}

	function get_html()
	{
		return $this->t->draw();
	}

};
?>
