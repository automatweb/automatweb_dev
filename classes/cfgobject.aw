<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/cfgobject.aw,v 2.1 2002/10/10 13:24:06 duke Exp $
// cfgobject.aw - configuration objects

class cfgobject extends aw_template
{
	function cfgobject($args = array())
	{
		$this->init("cfgobject");
	}

	////
	// !Adds a new configuration object
	function add($args = array())
	{
		extract($args);
		$this->read_template("add.tpl");
		$clid = array();
		$l = "";
		$this->get_objects_by_class(array(
			"class" => CL_CFGFORM,
		));
		while($row = $this->db_next())
		{
			$cfgforms[$row["oid"]] = $row["name"];
		};

		$this->mk_path($parent,"Lisa uus konfiobjekt");
		$toolbar = get_instance("toolbar");
		$toolbar->add_button(array(
                        "name" => "add",
                        "tooltip" => "Lisa",
                        "url" => "javascript:document.clform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));

		$this->vars(array(
			"toolbar" => $toolbar->get_toolbar(),
			"cfgforms" => $this->picker(-1,array("0" => " -- vali üks -- ") + $cfgforms),
			"line" => $l,
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent)),
		));
		return $this->parse();
	}

	////
	// !Allows to change the configuration object
	function change($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$this->mk_path($obj["parent"],"Muuda konfiobjekti");

		$toolbar = get_instance("toolbar");
		$toolbar->add_button(array(
                        "name" => "save",
                        "tooltip" => "Salvesta",
                        "url" => "javascript:document.clform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));
		$toolbar->add_button(array(
                        "name" => "search",
                        "tooltip" => "Vali objektid",
                        "url" => "javascript:show_search()",
                        "imgover" => "search_over.gif",
                        "img" => "search.gif",
                ));
		
		$this->read_template("change.tpl");

		$cfgformid = $obj["meta"]["cfgform"];
		$l = "";
		if ($cfgformid)
		{
			$cfgform = $this->get_object($cfgformid);
			$cfgproperties = new aw_array($cfgform["meta"]["properties"]);
			foreach($cfgproperties as $clid => $cl_properties)
			{
				// get_instance is cheap
				$t = get_instance($clid);
				$props = $t->get_properties();

				foreach($cl_properties as $pkey => $val)
				{
					$this->vars(array(
						"clid" => $clid,
						"pkey" => $pkey,
						"pname" => $clid . "::" . $props[$pkey]["caption"],
						"checked" => checked($obj["meta"]["properties"][$clid][$pkey]),
					));
					$l .= $this->parse("line");
				};
			};
		};

		$o = "";
		if (is_array($obj["meta"]["objects"]))
		{
			$oids = join(",",$obj["meta"]["objects"]);
			$q = "SELECT oid,name FROM objects WHERE oid IN ($oids)";
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"],
					"oid" => $row["oid"],
				));
				
				$o .= $this->parse("oline");
			};
		};

		$this->vars(array(
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"toolbar" => $toolbar->get_toolbar(),
			"line" => $l,
			"oline" => $o,
			"search_url" => $this->mk_my_orb("search",array("id" => $id)),
			"priority" => $obj["meta"]["priority"],
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !Submits the configuration object
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		$clidlist = $this->_remap_classes();
		if ($id)
		{
			$obj = $this->get_object($id);
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"properties" => $properties,
					"priority" => $priority,
				),
			));
			// this is where I need to write the code to update the properties
			// of all the objects that this configuration object affects

			// I have to cycle over all the properties that this configuration
			// object has and calculate the values
			$cfgformid = $obj["meta"]["cfgform"];
			if ($cfgformid)
			{
				$cfgform = $this->get_object($cfgformid);
				$cfgproperties = new aw_array($cfgform["meta"]["properties"]);
			};

			$props_to_set = array();

			foreach($cfgproperties->get() as $clid => $elements)
			{
				$t = get_instance($clid);
				$cl_props = $t->get_properties();
				foreach($elements as $key  => $val)
				{
					if ($properties[$clid][$key])
					{
						$value = $properties[$clid][$key];
					}
					else
					{
						$value = "";
					};
					$realclid =  $clidlist[$clid];
					if ($cl_props[$key]["store"] == "table")
					{
						$fields_to_set[$realclid][$key] = $value;
						$tables[$realclid] = $cl_props[$key]["table"];
						$idfields[$realclid] = $cl_props[$key]["idfield"];
					}
					else
					{
						$props_to_set[$realclid][$key] = $value;
					};
				};
			};

			// and now I need to update all the relevant objects
			$objects = $obj["meta"]["objects"];
			if (is_array($objects))
			{
				$oidlist = join(",",$objects);
				$q = "SELECT oid,name,class_id FROM objects WHERE oid IN ($oidlist) ORDER BY class_id";
				$oclist = array();
				$this->db_query($q);
				// first I'll create a list of objects which is sorted by class
				while($row = $this->db_next())
				{
					$oclist[$row["class_id"]][] = $row["oid"];
				};

				foreach($oclist as $okey => $oitems)
				{
					// update the tables with one query
					if ($fields_to_set[$okey])
					{
						$ref = $fields_to_set[$okey];
						$this->db_update_record(array(
							"table" => $tables[$okey],
							"key" => array($idfields[$okey]  => $oitems),
							"values" => $ref,
						));
					};

					// unluckily I cannot to the same for metadata
					// so I have to cycle over all the objects
					/*
					if ($props_to_set[$row["class_id"]])
					{
						$this->upd_object(array(
							"oid" => $row["oid"],
							"metadata" => $props_to_set[$row["class_id"]],
						));
					};
					*/


				};
			};

		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => CL_CFGOBJECT,
				"metadata" => array(
					"cfgform" => $cfgform,
				),
			));
		};

		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !Shows the form for assigning multiple configuration objects to selected objects
	function assign($args = array())
	{
		$this->quote($args);
		extract($args);
		
		$toolbar = get_instance("toolbar");
		$toolbar->add_button(array(
                        "name" => "save",
                        "tooltip" => "Rakenda",
                        "url" => "javascript:document.assignform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));

		$this->mk_path(-1,"Vali konfiguratsiooniobjektid");
	
		$this->read_template("assign.tpl");


		$this->vars(array(
			"reforb" => $this->mk_reforb("assign",array("no_reforb" => 1,"parent" => $parent)),
			"toolbar" => $toolbar->get_toolbar(),
		));

		return $this->parse();
	}

	function search($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);

		$search = get_instance("search");
		$args["clid"] = "cfgobject";
		$form = $search->show($args);

		$this->read_template("search.tpl");
		$this->mk_path($obj["parent"],"Muuda konfiobjekti");
		
		$toolbar = get_instance("toolbar");
		$toolbar->add_button(array(
                        "name" => "save",
                        "tooltip" => "Salvesta",
                        "url" => "javascript:document.searchform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));

		$toolbar->add_button(array(
                        "name" => "search",
                        "tooltip" => "Otsi",
                        "url" => "javascript:document.queryform.submit()",
                        "imgover" => "search_over.gif",
                        "img" => "search.gif",
                ));

		$toolbar->add_button(array(
                        "name" => "edit",
                        "tooltip" => "Muuda",
                        "url" => $this->mk_my_orb("change",array("id" => $id)),
                        "imgover" => "edit_over.gif",
                        "img" => "edit.gif",
                ));

		$this->vars(array(
			"toolbar" => $toolbar->get_toolbar(),
			"form" => $form,
			"results" => $search->get_results(),
			"reforb" => $this->mk_reforb("search",array("id" => $id,"no_reforb" => 1,"search" => 1)),
		));

		return $this->parse();
	}

	function search_callback_table_header($args = array())
	{
		return "<form name='searchform' method='post' action='reforb.aw'>";
	}
	
	function search_callback_table_footer($args = array())
	{
		$ref = $this->mk_reforb("save_objects",array("id" => $args["id"]));
		$ref .= "</form>";
		return $ref;
	}

	function search_callback_modify_data($row,$args)
	{
		$row["change"] = "<input type='checkbox' name='sel[]' value='$row[oid]'>";
	}

	function save_objects($args = array())
	{
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"metadata" => array(
				"objects" => $sel,
			),
		));
		return $this->mk_my_orb("change",array("id" => $id));
	}

	function _remap_classes($args = array())
	{
		$res = array();
		foreach($this->cfg["classes"] as $id => $data)
		{
			$res[$data["file"]] = $id;
		};
		return $res;
	}

};
?>
