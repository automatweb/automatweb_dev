<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/toolbar.aw,v 1.9 2005/10/14 13:11:49 duke Exp $
// toolbar.aw - drawing toolbars
class toolbar extends aw_template
{
	function toolbar($args = array())
	{
		$this->init("toolbar");
		$this->matrix = array();
		$this->custom_data = "";

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
		global $mc_counter;
		$mc_counter++;
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

		if (empty($arr["disabled"]))
		{
			$rv ='<a class="menuItem" href="'.$arr["url"].'" '.$arr["onClick"].'>'.$arr["text"]."</a>\n";
		}
		else
		{
			$rv = '<a class="menuItem" href="" title="'.$arr["title"].'" onclick="return false;" style="color:gray">'.$arr["text"]."</a>\n";
		}
		$this->menus[$arr["parent"]] .= $rv;
	}

	function add_menu_separator($arr)
	{
		$this->menus[$arr["parent"]] .= '<div class="menuItemSep"></div>'."\n";
	}

	function add_sub_menu($arr)
	{
		$arr["sub_menu_id"] = $arr["name"];
		$baseurl = $this->cfg["baseurl"];
		$rv = '<a class="menuItem" href="" onclick="return false;"
			        onmouseover="menuItemMouseover(event, \''.$arr["sub_menu_id"].'\');">
				<span class="menuItemText">'.$arr["text"].'</span>
				<span class="menuItemArrow"><img style="border:0px" src="'.$baseurl.
				'/automatweb/images/arr.gif" alt=""></span></a>';

		$this->menus[$arr["parent"]] .= $rv;
	}

	function build_menus()
	{
		static $init_done = false;
		foreach($this->menus as $parent => $menudata)
		{
			if (false == $init_done)
			{
				$this->custom_data .= $this->parse("MENU_HEADER");
				$init_done = true;
			};
			$cdata = '<div id="'.$parent.'" class="menu" onmouseover="menuMouseover(event)">'."\n${menudata}</div>\n";
			$this->custom_data .= $cdata;
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
			$args["onClick"] = "if(!confirm('$args[confirm]')) { return false; };".$args["onClick"];
		};

		$this->matrix[$args["name"]] = $args;
	}

	function remove_button($nm)
	{
		unset($this->matrix[$nm]);
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

					$disabled = $val["disabled"] ? "_disabled" : "";

					$this->vars($val);
					$tpl = $val["type"] . $disabled;

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

		$result .= $this->parse("real_end") . $this->custom_data;
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
