<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_input.aw,v 2.1 2001/07/25 03:15:21 duke Exp $
// form_input.aw - Tegeleb vormi sisestustega, hetkel ainult XML.
global $orb_defs;
$orb_defs["form_input"] = "xml";

class form_input extends form_base 
{
	function form_input($args = array())
	{
		$this->db_init();
		$this->tpl_init("forms");
	}

	////
	// !Kuvab uue XML sisendi koostamise jaoks vajaliku ekraanivormi,
	// kust saab valida, milliseid vorme inputis kasutatakse
	function add($args = array())
	{
		extract($args);
		$this->read_template("xml_input.tpl");
		$this->mk_path($parent,"Uus XML sisend");
		$this->vars(array(
			"forms" => $this->multiple_option_list($sel, $this->get_list(FTYPE_ENTRY,true,true)),
			"reforb" => $this->mk_reforb("submit_add",array("parent" => $parent)),
		));
		return $this->parse();
	}

	////
	// !Submitib uue sisendi jaoks vajal
	function submit_add($args = array())
	{
		extract($args);
		$new_id = $this->new_object(array(
				"name" => $name,
				"comment" => $comment,
				"parent" => $parent,
				"class_id" => CL_FORM_XML_INPUT,
		));
		
		$this->set_object_metadata(array(
			"oid" => $new_id,
			"key" => "forms",
			"value" => $forms,
		));
		return $this->mk_orb("change",array("id" => $new_id));
	}

	////
	// !Kuvab XML sisendi muutmise vormi
	function change($args = array())
	{
		extract($args);
		$object = $this->get_obj_meta($id);
		$meta = $object["meta"];

		$this->read_template("xml_input.tpl");
		$this->mk_path($object["parent"],"Muuda XML sisendit");
		$sel = ($meta["forms"]) ? array_flip($meta["forms"]) : array();

		$this->vars(array(
			"adminurl" => $this->mk_orb("edit_input",array("id" => $id)),
		));

		$forms = $this->get_flist(array(
				"type" => FTYPE_ENTRY,
				"onlyactive" => true,
		));

		$this->vars(array(
			"name" => $object["name"],
			"comment" => $object["comment"],
			"admin" => $this->parse("admin"),
			"forms" => $this->multiple_option_list($sel, $forms),
			"reforb" => $this->mk_reforb("submit_change",array("id" => $id)),
		));

		return $this->parse();
	}

	////
	// !Submitib sisendi
	function submit_change($args = array())
	{
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"name" => $name,
			"comment" => $comment,
		));
		
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "forms",
			"value" => $forms,
		));
		return $this->mk_orb("change",array("id" => $id));
	}

	////
	// !Kuvab elementide muutmisvormi
	function edit_input($args = array())
	{
		extract($args);
		$this->read_template("xml_input2.tpl");
		$object = $this->get_obj_meta($id);
		$this->mk_path($object["parent"],"Muuda XML sisendit");
		// just list all the elements of all the forms
		$meta = $object["meta"];
		$f = "";
		$els = array();
		$elements = $meta["elements"];
		if (is_array($meta["forms"]))
		{
			foreach($meta["forms"] as $fid)
			{
				$c = "";
				$tmp = $this->get_form_elements(array("id" => $fid)); 
				$this->vars(array("fname" => $this->name));
				foreach($tmp as $tkey => $tval)
				{
					switch($tval["type"])
					{
						case "submit":
							break;

						case "button":
							break;

						default:
							$this->vars(array(
								"name" => $tval["name"],
								"type" => $tval["type"],
								"id" => $tval["id"],
								"extname" => $elements[$tval["id"]]["name"],
								"checked" => ($elements[$tval["id"]]) ? "checked" : "",
							));
							$c .= $this->parse("element");
					};

				};
				$this->vars(array("element" => $c));
				$forms .= $this->parse("form");
				$els = $els + $tmp;
			};
		};

		$this->vars(array(
			"form" => $forms,
			"reforb" => $this->mk_reforb("submit_edit",array("id" => $id)),
		));

		return $this->parse();
	}

	////
	// !Submitib editi
	function submit_edit($args = array())
	{
		extract($args);
		$data = array();
		foreach($exists as $key => $val)
		{
			// store only active elements
			if ($active[$key])
			{
				$data[$key] = array("name" => $extname[$key]);
			};
		};
		$this->set_object_metadata(array(
				"oid" => $id,
				"key" => "elements",
				"value" => $data,
		));
		return $this->mk_my_orb("edit_input",array("id" => $id));
	}
};
?>
