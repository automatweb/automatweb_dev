<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/toolbar.aw,v 2.5 2002/10/16 14:17:20 duke Exp $
// toolbar.aw - drawing toolbars
class toolbar extends aw_template
{
	function toolbar($args = array())
	{
		$this->init("toolbar");
		$this->read_template("buttons.tpl");
		$this->matrix = array();

		extract($args);
		if (!$imgbase)
		{
			$imgbase = "/automatweb/images/icons";
		};

		$this->vars(array(
			"imgbase" => $this->cfg["baseurl"] . $imgbase,
		));
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
					$this->vars($val);
					$result .= $this->parse("smallbutton");
					break;
				
				case "separator":
					$this->vars($val);
					$result .= $this->parse("smallseparator");
					break;
				
				case "cdata":
					$this->vars($val);
					$result .= $this->parse("cdata");
					break;
			};
		};
		$result .= $this->parse("end");
		return $result;
	}

};
?>
