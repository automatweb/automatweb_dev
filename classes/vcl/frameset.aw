<?php
// $Id: frameset.aw,v 1.2 2002/11/04 20:53:13 duke Exp $
// frameset.aw - frameset generator
/*
	@default table=objects
	@default group=general
	@property template type=select field=meta method=serialize
	@caption Frameseti template

	@property sources type=array field=meta method=serialize getter=callback_get_sources 
	@caption Raamide sisu
*/
/*
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
<HTML>
<HEAD>
<TITLE>A simple frameset document</TITLE>
</HEAD>
<FRAMESET cols="20%, 80%">
  <FRAMESET rows="100, 200">
      <FRAME src="contents_of_frame1.html">
      <FRAME src="contents_of_frame2.gif">
  </FRAMESET>
  <FRAME src="contents_of_frame3.html">
  <NOFRAMES>
      <P>This frameset document contains:
      <UL>
         <LI><A href="contents_of_frame1.html">Some neat contents</A>
         <LI><IMG src="contents_of_frame2.gif" alt="A neat image">
         <LI><A href="contents_of_frame3.html">Some other neat contents</A>
      </UL>
  </NOFRAMES>
</FRAMESET>
</HTML>

that might create a frame layout something like this:

 ---------------------------------------
|         |                             |
|         |                             |
| Frame 1 |                             |
|         |                             |
|         |                             |
|---------|                             |
|         |          Frame 3            |
|         |                             |
|         |                             |
|         |                             |
| Frame 2 |                             |
|         |                             |
|         |                             |
|         |                             |
|         |                             |
 ---------------------------------------

If the user agent can't display frames or is configured not to, it will render the contents
of the NOFRAMES element.
*/

/* this class should allow the user to create whatever frameset she wants*/
class frameset extends aw_template
{
	function frameset($args = array())
	{
		$this->init(array(
			"clid" => CL_FRAMESET,
		));

	}

	function show($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		if ($obj["class_id"] != CL_FRAMESET)
		{
			return "fuck off";
		};
		$tpl = $this->get_frame_template(array("type" => $obj["meta"]["template"]));
		if ($args["sources"])
		{
			$this->sources = $args["sources"];
		}
		else
		{
			$this->sources = $obj["meta"]["sources"];
		};
		$this->draw_frameset($tpl);
		print $this->content;
		exit;
	}

	function get_frame_template($args = array())
	{
		switch($args["type"])
		{
			case "u1d2":
				$twopanel = array(
					"cols" => "20%,*",
					"frames" => array("left","right"),
				);
				$retval = array(
					"rows" => "18%,*",
					"frames" => array("top",$twopanel),
				);
				break;
			case "l1r2":
				$twopanel = array(
					"rows" => "20%,*",
					"frames" => array("top","right"),
				);
				$retval = array(
					"cols" => "20%,*",
					"frames" => array("left",$twopanel),
				);
				break;

		};
		return $retval;
	}

	function draw_frameset($data = array())
	{
		$this->content = "";
		$this->level = 0;
		$this->names = array();
		$this->req_draw_frameset($data);
		// a frameset needs a name, comment and the template
		// also - for each frameset I should be able to select
		// the source of content
//                print "<pre>";
//                print_r($this->names);
//                print htmlspecialchars($this->content);
//                print "</pre>";

	}

	function req_draw_frameset($data = array())
	{
		// this doesn't indent property, feel free to fix it
		$tab = str_repeat("\t",$this->level);
		$rows = ($data["rows"]) ? "rows='" . $data["rows"] . "'" : "";
		$cols = ($data["cols"]) ? "cols='" . $data["cols"] . "'" : "";
		$this->content .= "$tab<frameset $rows $cols>\n";
		$fx = new aw_array($data["frames"]);
		foreach($fx->get() as $key => $val)
		{
			if (is_array($val))
			{
				$this->level++;
				$this->req_draw_frameset($val);
				$this->level--;
			}
			else
			{
				$source = $this->sources[$val];
				$src = ($source) ? " src='$source'" : "";
				$this->content .= "$tab<frame name='$val' $src>\n";
				$this->names[] = $val;
			};
		};
		$this->content .= "$tab</frameset>\n";
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "template":
				$data["options"] = array("" => "--vali--","u1d2" => "Üleval 1, all 2","l1r2" => "vasakul 1, paremal 2");
				$tpl = $this->get_frame_template(array("type" => "u1d2"));
				$this->draw_frameset($tpl);
				break;

		};
	}

	function callback_get_sources($args)
	{
		static $i = 0;
		if ($this->names[$i])
		{
			$node = array();
			$name = $this->names[$i];
			$node["caption"] = "Raami '$name' sisu";
			$node["type"] = "textbox";
			$node["size"] = "30";
			$node["name"] = "sources[$name]";
			$node["value"] = $args["prop"]["value"][$name];
			$i++;
		}
		else
		{
			$node = false;
		};
		return $node;
	}

};
?>
