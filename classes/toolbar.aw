<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/toolbar.aw,v 2.18 2003/10/30 16:10:12 duke Exp $
// toolbar.aw - drawing toolbars
class toolbar extends aw_template
{
	function toolbar($args = array())
	{
		$this->init("toolbar");
		$tpl = "buttons.tpl";

		$this->read_template($tpl);
		$this->matrix = array();

		extract($args);
		if (empty($imgbase))
		{
			$imgbase = "/automatweb/images/icons";
		};

		$this->vars(array(
			"imgbase" => $this->cfg["baseurl"] . $imgbase,
		));

		$this->end_sep = array();
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
		$matrix = new aw_array($this->matrix);
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
					$this->vars($val);
					if ($side == "left")
					{
						$result .= $this->parse($val["type"]);
					}
					else
					{
						$right_side_content .= $this->parse($val["type"]);
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

};
?>
