<?php
// $Id: frameset.aw,v 1.3 2002/11/07 10:52:37 kristo Exp $
// frameset.aw - frameset generator
/*
	@default table=objects
	@default group=general
	@property template type=select field=meta method=serialize
	@caption Frameseti template

	@property framedata type=array field=meta method=serialize getter=callback_get_sources 
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
		$this->framedata = $obj["meta"]["framedata"];
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
			$tmp = array();
			
			$name = $this->names[$i];
			$subnode0 = array(
				"caption" => "Raam <b>'$name'</b>",
			);
			$subnode1 = array(
				"caption" => "Default sisu",
				"type" => "textbox",
				"size" => "50",
				"name" => "framedata[$name][source]",
				"value" => $args["prop"]["value"][$name]["source"],
			);

			$subnode2 = array(
				"caption" => "Default lehe stiil",
				"type" => "objpicker",
				"name" => "framedata[$name][style]",
				"clid" => "CL_PAGE",
				"value" => $args["prop"]["value"][$name]["style"],
			);
		
			$value = $args["prop"]["value"][$name]["frameborder"];
			if (!isset($value))
			{
				$value = 1;
			};
			$subnode3 = array(
				"caption" => "Border",
				"type" => "checkbox",
				"name" => "framedata[$name][frameborder]",
				"value" => $value,
			);
			
			$subnode4 = array(
				"caption" => "Keritav",
				"type" => "select",
				"name" => "framedata[$name][scrolling]",
				"value" => $args["prop"]["value"][$name]["scrolling"],
				"options" => array("" => "","yes" => "Jah","no" => "Ei"),
			);

			$node = array("type" => "subnodes","content" => array($subnode0,$subnode1,$subnode2,$subnode3,$subnode4));
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
