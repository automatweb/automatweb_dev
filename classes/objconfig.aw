<?php
// $Header
// objconfig.aw - objekti konfiguratsiooniobjekt
class objconfig extends aw_template
{
	function objconfig() 
	{
		$this->init("objconfig");
		global $lc_objconfig;
		lc_load("objconfig");

		$this->lc_load("objconfig","lc_objconfig");

		// add all clid's which support config objects here
		$this->baseclasses = array(CL_CALENDAR);
	}

	////
	// !Used for adding of changing  config object
	function add($args = array())
	{
		extract($args);


		$caption = "Lisa konfiguratsiooniobjekt";
		$this->read_template("add.tpl");
		foreach($this->baseclasses as $clid)
		{
			$this->vars(array(
				"clid" => $clid,
				"name" => $this->cfg["classes"][$clid]["name"],
				"selected" => "checked",
			));

			$block .= $this->parse("classlist");
		};
		
		$this->mk_path($parent,$caption);


		$this->vars(array(
			"name" => $obj["name"],
			"classlist" => $block,
			"comment" => $obj["comment"],
			"reforb" => $this->mk_reforb("submit_add",array("parent" => $parent)),
		));
		return $this->parse();
	}

	////
	// !Submits a new config object
	function submit_add($args = array())
	{
		$this->quote($args);
		extract($args);
		$parent = (int)$parent;
		$id = $this->new_object(array(
			"parent" => $parent,
			"name" => $name,
			"comment" => $comment,
			"class_id" => CL_OBJCONFIG,
			"subclass" => $baseclass,
		));
		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !Wrapper for object configuration
	function change($args = array())
	{
		extract($args);
		global $orb_defs;
		$obj = $this->get_object($id);
		$this->read_template("change.tpl");
		$this->mk_path($obj["parent"],"Muuda konfiguratsiooniobjekti");
		$bc = get_instance($this->cfg["classes"][$obj["subclass"]]["file"]);
		$keys = $bc->get_config_keys();
		$conflines = "";
		foreach($keys as $key => $val)
		{
			if (is_array($val))
			{
				$element = "<select name='conf[$key]'>" . $this->picker($obj["meta"][$key],$val) . "</select>";
				$this->vars(array(
					"name" => $key,
					"element" => $element,
				));
				$conflines .= $this->parse("confline");
			};
			if ($val == "time")
			{
				load_vcl("date_edit");
				$de = new date_edit("conf[$key]");
				$de->minute_step = 30;
				$de->configure(array("hour" => 1,"minute" => 2));
				$ts = ($obj["meta"][$key]["hour"] * 3600) + ($obj["meta"][$key]["minute"] * 60);
				list($d,$m,$y) = explode("-",date("d-m-Y"));
				$ts += mktime(0,0,0,$m,$d,$y);
				$element = $de->gen_edit_form("conf[$key]",$ts);
				$this->vars(array(
					"name" => $key,
					"element" => $element,
				));
				$conflines .= $this->parse("confline");
			};

		}

		$this->vars(array(
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"confline" => $conflines,
			"baseclass" => $this->cfg["classes"][$obj["subclass"]]["name"],
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !Submits a new or changed iframe object
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"name" => $name,
			"comment" => $comment,
		));

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => $conf,
		));

		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !Parses an iframe alias
	function parse_alias($args = array())
	{
		extract($args);
		if (not($alias["target"]))
		{
			return "";
		};
			
		$obj = $this->_get_iframe($alias["target"]);

		$this->read_adm_template("iframe.tpl");

		$align = array(
			"" => "",
			"v" => "left",
			"k" => "center",
			"p" => "right",
		);

		$this->vars(array(
			"url" => $obj["meta"]["url"],
			"width" => $obj["meta"]["width"],
			"height" => $obj["meta"]["height"],
			"scrolling" => $obj["meta"]["scrolling"],
			"frameborder" => $obj["meta"]["frameborder"],
			"comment" => $obj["meta"]["comment"],
			"align" => $align[$matches[4]], // that's where the align char is
		));

		return $this->parse();
	}
	
}
?>
