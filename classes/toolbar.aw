<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/toolbar.aw,v 2.9 2003/01/16 16:46:11 kristo Exp $
// toolbar.aw - drawing toolbars
class toolbar extends aw_template
{
	function toolbar($args = array())
	{
		$this->init("toolbar");
		$styles = array(
			"smallbuttons" => "buttons.tpl",
		);

		if (!$styles[$style])
		{
			$style = "smallbuttons";
		};

		$tpl = $styles[$style];


		$this->read_template($tpl);
		$this->matrix = array();

		extract($args);
		if (!$imgbase)
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
	function add_cdata($content)
	{
		$args = array(
			"type" => "cdata",
			"data" => $content,
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
		$result = $this->parse("start");
		foreach($matrix->get() as $val)
		{
			switch($val["type"])
			{
				case "button":
					if ($args["id"])
					{
						$val["name"] .= $args["id"];
					};
					if ($args["target"])
					{
						$val["target"] = $args["target"];
					};
					if (!$val["onClick"])
					{
						$val["onClick"] = "";
					};
					$this->vars($val);
					$result .= $this->parse("button");
					break;
				
				case "separator":
					$this->vars($val);
					$result .= $this->parse("separator");
					break;
				
				case "cdata":
					$this->vars($val);
					$result .= $this->parse("cdata");
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

		$result .= $this->parse("real_end");
		return $result;
	}

};
?>
