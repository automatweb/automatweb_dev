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

	function add_item($arr)
	{


	}

	function init_rel_editor($arr)
	{
		$prop = $arr["prop"];
		$obj = $arr["obj_inst"];

		$clid = $arr["prop"]["clid"][0];

		$props = $arr["prop"]["props"];

		// now I have to query the target class and add the fields in here


		$t = get_instance($clid);
		$t->init_class_base();
		$emb_group = "general";

		$all_props = $t->get_active_properties(array(
			"group" => $emb_group,
		));

		$act_props = array();

		foreach($all_props as $key => $prop)
		{
			if (in_array($key,$props))
			{
				$act_props[$key] = $prop;
			};
		};
		
		$clinf = aw_ini_get("classes");
		$clname = $clinf[$clid]["name"];

		$ef = array("header" => array(
				"type" => "text",
				"subtitle" => 1,
				"value" => "Uus " . $clname,
		));


		$xprops = $ef + $t->parse_properties(array(
			"properties" => $act_props,
			"name_prefix" => "cba_emb",
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
		$emb["group"] = "general";
		$emb["parent"] = $obj->parent();

		$reltype = $arr["prop"]["reltype"];

		$clinst->id_only = true;
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
