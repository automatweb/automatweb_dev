<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/toolbar.aw,v 1.3 2004/10/25 12:45:02 duke Exp $
// toolbar.aw - drawing toolbars
class toolbar extends aw_template
{
	function toolbar($args = array())
	{
		$this->init("toolbar");
		$this->matrix = array();

		extract($args);
		if (empty($imgbase))
		{
			$imgbase = "/automatweb/images/icons";
		};

		$this->vars(array(
			"imgbase" => $this->cfg["baseurl"] . $imgbase,
		));

		$this->menus = array();

		$this->end_sep = array();
	}

	function _init_menu()
	{
		$this->menu_inited = true;
		$this->read_template("js_popup_menu.tpl");
	}

	function add_menu_button($arr)
	{
		if (!$this->menu_inited)
		{
			$this->_init_menu();
		};
		$name = $arr["name"];
		$arr["onClick"] = "return buttonClick(event, '${name}');";
		$arr["class"] = "menuButton";
		$arr["url"] = "";
		if (empty($arr["img"]))
		{
			$arr["img"] = "new.gif";
		};
		$arr["type"] = "button";
		$arr["ismenu"] = 1;
		$arr["id"] = $name;
		$this->matrix[$arr["name"]] = $arr;
	}

	function add_menu_item($arr)
	{
		if ($arr["onClick"])
		{
			$arr["onClick"] = " onClick=\"". $arr["onClick"] . "\"";
		};

		if (!empty($arr["link"]))
		{
			$arr["url"] = $arr["link"];
		};

		if (isset($arr["action"]))
		{
			$arr["url"] = "javascript:submit_changeform('$arr[action]');";
		};
		$this->vars($arr);
		$tpl = isset($arr["disabled"]) && $arr["disabled"] ? "MENU_ITEM_DISABLED" : "MENU_ITEM";
		$this->menus[$arr["parent"]] .= $this->parse($tpl);
	}

	function add_menu_separator($arr)
	{
		$this->menus[$arr["parent"]] .= $this->parse("MENU_SEPARATOR");
	}

	function add_sub_menu($arr)
	{
		$arr["sub_menu_id"] = $arr["name"];
		$this->vars($arr);
		$this->menus[$arr["parent"]] .= $this->parse("MENU_ITEM_SUB");
	}

	function build_menus()
	{
		foreach($this->menus as $parent => $menudata)
		{
			$this->vars(array(
				"MENU_ITEM" => $menudata,
				"id" => $parent,
			));
			$cdata = $this->parse();
			$this->add_cdata($cdata);
		};
	}

	////
	// !Adds a button to the toolbar
	function add_button($args = array())
	{
		$args["type"] = "button";
		if (empty($args["img"]))
		{
			$args["type"] = "text_button";
		};
		if (isset($args["action"]))
		{
			$args["url"] = "javascript:submit_changeform('$args[action]');";
		};
		if (empty($args["target"]) && !empty($this->button_target))
		{
			$args["target"] = $this->button_target;
		};
		if (isset($args["confirm"]))
		{
			if (substr($args["url"],0,1) == "j")
			{
				$args["url"] = substr($args["url"],strlen("javascript:"));
			};
			$args["url"] = "javascript:if(confirm('$args[confirm]'))" . "{" . $args[url] . "};";
		};
		$this->matrix[$args["name"]] = $args;
	}

	////
	// !Adds a separator to the toolbar
	function add_separator($args = array())
	{
		$args["type"] = "separator";
		$this->matrix[] = $args;
	}

	////
	// !Allows to add custom data to the boolar
	function add_cdata($content,$side = "")
	{
		$args = array(
			"type" => "cdata",
			"data" => $content,
			"side" => $side,
		);
		$this->matrix[] = $args;
	}

	////
	// !Allows the user to add cdata to the right side of the toolbar in the end - only one of these is supported
	function add_end_cdata($content)
	{
		$this->end_sep[] = array(
			'data' => $content
		);
	}

	////
	// !Returns the toolbar
	// id(string) - if set, the value if this is added to the names of all elements
	// 		This allows us to have multiple toolbars on a page
	function get_toolbar($args = array())
	{
		if ($this->menu_inited)
		{
			$this->build_menus();
		};
		$matrix = new aw_array($this->matrix);
		$tpl = "buttons.tpl";
		$this->read_template($tpl);
		$this->vars(array('align' => isset($this->align) ? $this->align : 'left'));
		$result = $this->parse("start");
		$right_side_content = "";
		foreach($matrix->get() as $val)
		{
			$side = !empty($val["side"]) ? "right" : "left";
			switch($val["type"])
			{
				case "button":
				case "text_button":
					if (isset($args["id"]))
					{
						$val["name"] .= $args["id"];
					};
					if (!$args["no_target"])
					{
						$val["target"] = isset($args["target"]) ? $args["target"] : $val["target"];
					}
					if (empty($val["onClick"]))
					{
						$val["onClick"] = "";
					};
					if (empty($val["tooltip"]))
					{
						$val["tooltip"] = "";
					};
					$this->vars($val);
					$tpl = $val["type"];
					if ($val["ismenu"])
					{
						$tpl = "menu_button";
					};
					if ($side == "left")
					{
						$result .= $this->parse($tpl);
					}
					else
					{
						$right_side_content .= $this->parse($tpl);
					};
					break;
				
				case "separator":
					$this->vars($val);
					if ($side == "left")
					{
						$result .= $this->parse("separator");
					}
					else
					{
						$right_side_content .= $this->parse("separator");
					};
					break;
				
				case "cdata":
					$this->vars($val);
					if ($side == "left")
					{
						$result .= $this->parse("cdata");
					}
					else
					{
						$right_side_content .= $this->parse("cdata");
					};
					break;
			};
		};

		$result .= $this->parse("end");
		if (count($this->end_sep) > 0)
		{
			foreach($this->end_sep as $ese)
			{
				$this->vars($ese);
				$result .= $this->parse("end_sep");
			}
		}

		if (!empty($right_side_content))
		{
			$this->vars(array(
				"right_side_content" => $right_side_content,
			));

			$result .= $this->parse("right_side");
		};

		$result .= $this->parse("real_end");
		return $result;
	}

	function init_vcl_property($arr)
	{
		$name = $arr["property"]["name"];
		$vcl_inst = $this;
		$res = $arr["property"];
		$res["vcl_inst"] = &$vcl_inst;
			// for backwards compatibility
		$res["toolbar"] = &$vcl_inst;
		$rv = array($name => $res);
		return $rv;
	}

};
?>
