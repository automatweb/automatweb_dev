<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cb_search.aw,v 1.1 2004/06/01 13:23:15 duke Exp $
// cb_search.aw - Classbase otsing 
/*

@classinfo syslog_type=ST_CB_SEARCH relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property root_class type=select
@caption Juurklass

@property next_connection type=select
@caption Where do you want to go from here?

@property choose_fields type=table group=props
@caption Vali omadused

@property search type=callback callback=callback_gen_search group=search
@caption Otsi

@property sbt type=submit group=search
@caption Otsi

@property results type=table group=search no_caption=1
@caption Tulemused

@groupinfo props caption="Väljad"
@groupinfo search caption="Otsi" submit_method=get

// step 1 - choose a class
// step 2 - choose a connection (might be optional)
// step 3 - choose another class (also optional)

*/

class cb_search extends class_base
{
	function cb_search()
	{
		$this->init(array(
			"clid" => CL_CB_SEARCH
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$o = $arr["obj_inst"];
		switch($prop["name"])
		{
			case "root_class":
				$this->make_class_list(&$prop);
				break;

			case "next_connection":
				$cfgx = get_instance("cfg/cfgutils");
				$tmp = $cfgx->load_class_properties(array(
					"clid" => $o->prop("root_class"),
				));
				$relx = $cfgx->get_relinfo();
				$choices = array();
				$clinf = aw_ini_get("classes");
				foreach($relx as $relkey => $relval)
				{
					if (is_numeric($relkey))
					{
						$choices[$relkey] = $relval["caption"] . " - " . $clinf[$relval["clid"][0]]["name"];
					};
				};
				$prop["options"] = $choices;
				break;

			case "choose_fields":
				$this->mk_prop_table($arr);
				break;

			case "results":
				$this->mk_result_table($arr);
				break;
		};
		return $retval;
	}

	function mk_prop_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$o = $arr["obj_inst"];

		$t->define_field(array(
			"name" => "class",
			"caption" => "Klass",
		));	

		$t->define_field(array(
			"name" => "property",
			"caption" => "Omadus",
		));	
		
		$t->define_field(array(
			"name" => "in_form",
			"caption" => "Näita vormis",
			"callback" => array(&$this, "callb_in_form"),
			"callb_pass_row" => true,
			"align" => "center",

		));	
		
		$t->define_field(array(
			"name" => "in_results",
			"caption" => "Näita tulemustes",
			"callback" => array(&$this, "callb_in_results"),
			"callb_pass_row" => true,
			"align" => "center",
		));	

		// get a list of properties in both classes
		$cfgx = get_instance("cfg/cfgutils");
		$tmp = $cfgx->load_class_properties(array(
			"clid" => $o->prop("root_class"),
		));
		$relx = $cfgx->get_relinfo();
		$clinf = aw_ini_get("classes");
		$cl1 = $clinf[$o->prop("root_class")]["name"];
		foreach($tmp as $item)
		{
			if ($item["type"] == "textbox" || $item["type"] == "textarea")
			{
				$t->define_data(array(
					"class" => $cl1,
					"property" => $item["caption"] . " / " . $item["name"],
					"xname" => $o->prop("root_class") . "/" . $item["name"],
				));
			};
		};

		$relin = $relx[$o->prop("next_connection")];
		$tgt = $relin["clid"][0];
		
		$tmp = $cfgx->load_class_properties(array(
			"clid" => $tgt,
		));
		$cl2 = $clinf[$tgt]["name"];
		$this->sets = array(
			"in_form" => $o->meta("in_form"),
			"in_results" => $o->meta("in_results"),
		);
		
		foreach($tmp as $item)
		{
			if ($item["type"] == "textbox" || $item["type"] == "textarea")
			{
				$t->define_data(array(
					"class" => $cl2,
					"property" => $item["caption"] . " / " . $item["name"],
					"xname" => $tgt . "/" . $item["name"],
				));
			};
		};

	}

	function callb_in_form($arr)
	{
		$name = $arr["xname"];
		return html::checkbox(array(
			"name" => "in_form[$name]",
			"value" => 1,
			"checked" => $this->sets["in_form"][$name],
		));
	}
	
	function callb_in_results($arr)
	{
		$name = $arr["xname"];
		return html::checkbox(array(
			"name" => "in_results[$name]",
			"value" => 1,
			"checked" => $this->sets["in_results"][$name],
		));
	}

	function make_class_list($arr)
	{
		$cl = aw_ini_get("classes");
		$names = array();
		foreach($cl as $clid => $clinf)
		{
			if (!empty($clinf["name"]))
			{
				$names[$clid] = $clinf["name"];
			};
		};
		asort($names);
		$arr["options"] = array("0" => "--vali--") + $names;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$o = $arr["obj_inst"];
		switch($prop["name"])
		{
			case "choose_fields":
				$o->set_meta("in_form",$arr["request"]["in_form"]);
				$o->set_meta("in_results",$arr["request"]["in_results"]);
				break;

		}
		return $retval;
	}	

	function callback_gen_search($arr)
	{
		// now, get a list of properties in both classes and generate the search form
		// get a list of properties in both classes
		$this->_prepare_form_data($arr);
		$this->_prepare_search($arr);
		// would be nice to separate things by blah
		foreach($this->in_form as $iname => $item)
		{
			$name = $item["name"];
			$item["name"] = "s[" . $item["clid"] . "][" . $item["name"] . "]";
			if ($this->search_data[$item["clid"]][$name])
			{
				$item["value"] = $this->search_data[$item["clid"]][$name];
			};
			$res[$iname] = $item;
		};
		return $res;
	}

	function _prepare_search($arr)
	{
		if ($this->search_prepared)
		{
			return false;
		}
		$this->search_data = array();
		$this->search_prepared = 1;
		foreach($this->in_form as $iname => $item)
		{
			if ($arr["request"]["s"][$item["clid"]][$item["name"]])
			{
				$val = $arr["request"]["s"][$item["clid"]][$item["name"]];
				$this->search_data[$item["clid"]][$item["name"]] = $val;
			};
		};

	}

	function mk_result_table($arr)
	{
		$this->_prepare_form_data($arr);
		$this->_prepare_search($arr);
		$t = &$arr["prop"]["vcl_inst"];
		foreach($this->in_results as $iname => $item)
		{
			$t->define_field(array(
				"name" => $item["name"],
				"caption" => $item["caption"],
			));
		}
		// now do the actual bloody search
		foreach($this->search_data as $clid => $data)
		{
			if (!empty($data))
			{
				$sdata = array();
				$sdata["class_id"] = $clid;
				foreach($data as $key => $val)
				{
					$sdata[$key] = "%" . $val . "%";
				};
				$olist = new object_list($sdata);
				for($o = $olist->begin(); !$olist->end(); $o = $olist->next())
				{
					$t->define_data($o->properties());
				};
			};
		};
	}
				
	function _prepare_form_data($arr)
	{
		if ($this->prepared)
		{
			return false;
		};
		$this->prepared = true;
		$o = $arr["obj_inst"];
		$cfgx = get_instance("cfg/cfgutils");
		$tmp = $cfgx->load_class_properties(array(
			"clid" => $o->prop("root_class"),
		));
		$relx = $cfgx->get_relinfo();
		$clinf = aw_ini_get("classes");
		$cl1 = $clinf[$o->prop("root_class")]["name"];

		$in_form = $o->meta("in_form");
		$in_results = $o->meta("in_results");

		$this->in_form = array();
		$this->in_results = array();

		$res = array();
		foreach($tmp as $iname => $item)
		{
			$xname = $o->prop("root_class") . "/" . $item["name"];
			if ($in_form[$xname])
			{
				$item["clid"] = $o->prop("root_class");
				$this->in_form[$xname] = $item;
			};
			if ($in_results[$xname])
			{
				$this->in_results[$xname] = $item;
			};
		};
		
		$relin = $relx[$o->prop("next_connection")];
		$tgt = $relin["clid"][0];
		
		$tmp = $cfgx->load_class_properties(array(
			"clid" => $tgt,
		));
		
		foreach($tmp as $iname => $item)
		{
			$xname = $tgt . "/" . $item["name"];
			if ($in_form[$xname])
			{
				$item["clid"] = $tgt;
				$this->in_form[$xname] = $item;
			};
			if ($in_results[$xname])
			{
				$this->in_results[$xname] = $item;
			};
		};



	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
