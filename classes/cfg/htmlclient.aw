<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/htmlclient.aw,v 1.1 2002/10/30 12:25:29 duke Exp $
// htmlclient - generates HTML for configuration forms

// The idea is that if we want to implement other interfaces
// for editing objects, then we can just add other clients
// (xmlrpc, rdf, tty, etc) which take care of converting the data
// from the cfgmanager to the required form. 

class htmlclient extends aw_template
{
	function htmlclient($args = array())
	{
		$this->init("");
		$this->res = "";
		$this->style1 = "chformleftcol";
		$this->style2 = "chformrightcol";
		$this->html = get_instance("html");
	}


	////
	// !Starts the output 
	function start_output($args = array())
	{
		$this->res .= sprintf("<form action='reforb.%s' method='post' name='changeform'>",aw_ini_get("ext"));
		$this->res .= "\n<table border='0' cellspacing='1' cellpadding='1' bgcolor='#CCCCCC'>\n";
	}

	function add_property($args = array())
	{
		if ($args["type"] == "checkbox")
		{
			$args["checked"] = $args["value"];
			$args["value"] = 1;
		};

		$this->res .= "<tr>";
		$this->res .= "<td class='" . $this->style1 . "' width='150'>";
		$this->res .= $args["caption"];
		$this->res .= "</td>";

		$this->res.= "<td class='" . $this->style2 . "'>";
		$this->res .= $this->html->draw($args);
		$this->res .= "</td>";
		$this->res .= "</tr>\n";
	}

	////
	// !Finished the output
	function finish_output($args = array())
	{
		extract($args);
		$this->res .= "<tr><td class='chformleftcol' align='center'>&nbsp;</td>";
		$this->res .= "<td class='chformrightcol'>";
		$this->res.= "<input type='submit' value='Salvesta' class='small_button'>";
		$this->res .= "</td></tr>";

		$this->res .= "\n</table>\n";
		$this->res .= $this->mk_reforb($action,$data,"cfgmanager");
		$this->res .= "</form>\n";
	}

	function get_result()	
	{
		return $this->res;
	}
};
?>
