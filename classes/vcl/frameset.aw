<?php
// $Id: frameset.aw,v 1.6 2003/01/14 19:08:34 duke Exp $
// frameset.aw - frameset generator
/*
	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property template type=select 
	@caption Frameseti template

	@property framedata type=text editonly=1 callback=callback_get_sources 
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
class frameset extends class_base
{
	function frameset($args = array())
	{
		$this->init(array(
			"clid" => CL_FRAMESET,
		));

		$this->frame_templates = array();

	}

	function _register_frame_templates()
	{
		// XXX: puh-lease, there has to be a better way for 
		// defining framesets
		$tmp = array(
			"cols" => "20%,*",
			"frames" => array("left","right"),
		);

		$this->frame_templates["u1d2"] = array(
			"rows" => "18%,*",
			"frames" => array("top",$tmp),
		);

		$this->frame_names["u1d2"] = "1 �leval, 2 all";

		$tmp = array(
			"rows" => "20%,*",
			"frames" => array("top","right"),
		);

		$this->frame_templates["l1r2"] = array(
			"cols" => "20%,*",
			"frames" => array("left",$tmp),
		);
		
		$this->frame_names["l1r2"] = "1 vasakul, 2 paremal";
	}

	function show($args = array())
	{
		extract($args);

		$obj = $this->get_object(array(
			"oid" => $id,
			"class_id" => CL_FRAMESET,
		));

		$this->title = $title;

		$this->_register_frame_templates();
		$tpl = $this->frame_templates[$obj["meta"]["template"]];

		if ($args["sources"])
		{
			$this->sources = $args["sources"];
		}
		else
		{
			$this->sources = $obj["meta"]["sources"];
		};

		$this->framedata = $obj["meta"]["framedata"];
		$this->draw_frameset($tpl);
		print $this->content;
		exit;
	}

	function draw_frameset($data = array())
	{
		$this->level = 0;
		$this->names = array();
		$title = $this->title;
		$this->content .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
		$this->content .= "\n<HTML>\n<HEAD>\n<TITLE>$title</TITLE>\n</HEAD>\n";
		$this->req_draw_frameset($data);
		$this->content .= "</HTML>\n";
		// a frameset needs a name, comment and the template
		// also - for each frameset I should be able to select
		// the source of content

	}

	function req_draw_frameset($data = array())
	{
		// this doesn't indent property, feel free to fix it
		$tab = str_repeat("\t",$this->level);
		$rows = ($data["rows"]) ? "rows='" . $data["rows"] . "'" : "";
		$cols = ($data["cols"]) ? "cols='" . $data["cols"] . "'" : "";
		$this->content .= "$tab<frameset border=0 framespacing=0 $rows $cols>\n";
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
				$_fb = $this->framedata[$val]["frameborder"];
				$_sc = $this->framedata[$val]["scrolling"];
				$frameborder = ($_fb) ? " frameborder='1' " : " frameborder='0' ";
				$scrolling = ($_fb) ? " scrolling='$_sc' " : "";
				$this->content .= "$tab<frame name='$val' $src $frameborder $scrolling>\n";
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
				$this->_register_frame_templates();
				$data["options"] = array("" => "--vali--") + $this->frame_names;
				$tpl = $this->frame_templates[$args["obj"]["meta"]["template"]];
				$this->draw_frameset($tpl);
				break;

		};
	}

	////
	// !Returns a bunch of nodes for each frame
	function callback_get_sources($args)
	{
		$nodes = array();
		$names = new aw_array($this->names);
		foreach($names->get() as $name)
		{
			$nodes[] = array("caption" => "Raam <b>'$name'</b>");
			$nodes[] = array(
				"caption" => "Default sisu",
				"type" => "textbox",
				"size" => 50,
				"name" => "framedata[$name][source]",
				"value" => $args["prop"]["value"][$name]["source"],
			);
			$nodes[] = array(
				"caption" => "Default lehe stiil",
				"type" => "objpicker",
				"name" => "framedata[$name][style]",
				"clid" => "CL_PAGE",
				"value" => $args["prop"]["value"][$name]["style"],
			);
			$nodes[] = array(
				"caption" => "Border",
				"type" => "checkbox",
				"name" => "framedata[$name][frameborder]",
				"checked" => checked($args["prop"]["value"][$name]["frameborder"]),
			);
			$nodes[] = array(
				"caption" => "Keritav",
				"type" => "select",
				"name" => "framedata[$name][scrolling]",
				"value" => $args["prop"]["value"][$name]["scrolling"],
				"options" => array("" => "","yes" => "Jah","no" => "Ei"),
			);
				
				
		};
		return $nodes;
	}

};
?>
