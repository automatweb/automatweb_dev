<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_input.aw,v 2.6 2002/06/10 15:50:53 kristo Exp $
// form_input.aw - Tegeleb vormi sisestustega, hetkel ainult XML.

class form_input extends form_base 
{
	function form_input($args = array())
	{
		$this->form_base();
	}

	////
	// !Kuvab uue XML sisendi koostamise jaoks vajaliku ekraanivormi,
	// kust saab valida, milliseid vorme inputis kasutatakse
	function add($args = array())
	{
		extract($args);
		$this->read_template("xml_input.tpl");
		$this->mk_path($parent,"Uus XML sisend");

		$forms = $this->get_list(FTYPE_ENTRY,true,true);
		
		$c = "";
		foreach($forms as $key => $val)
		{
			$this->vars(array(
				"oid" => $key,
				"ename" => $val,
			));

			$c .= $this->parse("line");
		};

		$chains = $this->get_objects_below(array(
			"class" => CL_FORM_CHAIN,
		));

		foreach($chains as $key => $val)
		{
			$this->vars(array(
				"oid" => $key,
				"ename" => $val["name"],
			));

			$c2 .= $this->parse("line2");
		};

		$this->vars(array(
			"forms" => $this->multiple_option_list($sel, $this->get_list(FTYPE_ENTRY,true,true)),
			"reforb" => $this->mk_reforb("submit_add",array("parent" => $parent)),
			"line" => $c,
			"line2" => $c2,
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
			"key" => "form",
			"value" => $select,
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
		$sel = ($meta["form"]) ? $meta["form"] : 0;

		$forms = $this->get_list(FTYPE_ENTRY,true,true);
		
		$c = "";
		foreach($forms as $key => $val)
		{
			$this->vars(array(
				"oid" => $key,
				"ename" => $val,
				"checked" => checked($key == $sel),
			));

			$c .= $this->parse("line");
		};

		$chains = $this->get_objects_below(array(
			"class" => CL_FORM_CHAIN,
		));

		foreach($chains as $key => $val)
		{
			$this->vars(array(
				"oid" => $key,
				"ename" => $val["name"],
				"checked" => checked($key == $sel),
			));

			$c2 .= $this->parse("line2");
		};

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
			"reforb" => $this->mk_reforb("submit_change",array("id" => $id)),
			"line" => $c,
			"line2" => $c2,
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
			"key" => "form",
			"value" => $select,
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
		$cs = $this->get_object($meta["form"]);

		$farr = array();
		if ($cs["class_id"] == CL_FORM)
		{
			$farr[$cs["oid"]] = $cs["name"];
		}
		elseif ($cs["class_id"] == CL_FORM_CHAIN)
		{
			classload("form_chain");
			$t = new form_chain();
			$t->load_chain($cs["oid"]);
			$farr = $t->chain["forms"];
		};
			
		foreach($farr as $key => $val)
		{
			$c = "";
			$tmp = $this->get_form_elements(array("id" => $key)); 

			$this->vars(array(
				"fname" => $this->name,
				"form_id" => $key,
			));

			foreach($tmp as $tkey => $tval)
			{
				switch($tval["type"])
				{
					case "submit":
						break;

					case "button":
						break;

					default:
						if ($tval["name"])
						{
							$name = $tval["name"];
						}
						else
						{
							$name = $elements[$tval["id"]]["name"];
						};

						$this->vars(array(
							"name" => $name,
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

		$this->vars(array(
			"form" => $forms,
			"reforb" => $this->mk_reforb("submit_edit",array("id" => $id)),
			"edurl" => $this->mk_orb("change",array("id" => $id)),
		));

		return $this->parse();
	}

	////
	// !Submitib editi
	function submit_edit($args = array())
	{
		extract($args);
		$data = array();
				
		$loaded_forms = array();

		$el_data = array();

		// get element information for all forms
		foreach($form as $fid)
		{
			// each form is loaded only once
			if (!$loaded_forms[$fid])
			{
				$tmp = $this->get_form_elements(array("id" => $fid,"key" => "id"));
				$loaded_forms[$fid] = 1;
				// mommy, we can add arrays
				$el_data = $el_data + $tmp;
			};
		};

		foreach($exists as $key => $val)
		{
			// store only active elements
			if ($active[$key])
			{
				$data[$key] = array(
					"name" => $extname[$key],
					"type" => $type[$key],
					"form" => $form[$key],
					"group" => $el_data[$key]["group"],
					"lb_items" => $el_data[$key]["lb_items"],
				);
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
