<?php
// this class handles a single event. Planner handles sets of events
class event extends aw_template
{
	function event($args = array())
	{
		// no, I don't like that name either 
		$this->init("planner");
	}

	//// 
	// !initializes the class for a drawing an event
	// we need this as a separate function because we may have
	// different types of events on the page
	// tpl - name of the template to use for drawing templates
	function start($args = array())
	{
		extract($args);
		$this->read_template($tpl);
	}
	
	////
	// !Draws a single event
	function draw($e = array())
	{
		if ($e["aid"])
		{
			$obj = $this->get_object($e["aid"]);
			$meta = aw_unserialize($e["metadata"]);	
			if ($meta["showtype"] == 0)
			{
				$link = sprintf("onClick='javascript:window.open(\"%s\",\"w%s\",\"toolbar=0,location=0,menubar=0,scrollbars=1,width=400,height=500,resizable=yes\")'","orb.aw?class=objects&action=show&id=$obj[oid]",$obj["oid"]);
				$repl = "<a href='#' $link>$obj[name]</a>";
			}
			else
			{
				$repl = $this->_show_object($obj);
			};
			//$name = sprintf("<br><img src='%s'>%s",get_icon_url($obj["class_id"],""),$obj["name"]);
			$name = "<br>" .$repl;
		}
		else
		{
			$name = "";
		};

		// this wont work
		if ($this->parent_class == CL_CALENDAR)
		{
			$ev_link = $this->mk_my_orb("change_event",array("id" => $e["id"],"date" => $this->date),"planner");
		}
		else
		{
			$ev_link = $this->mk_my_orb("change",array("id" => $e["id"],"date" => $this->date),"cal_event");
		};
		
		if ($this->actlink)
		{
			$ev_link = $this->mk_my_orb("change",array("parent" => $parent,"date" => $date,"id" => $e["id"],"return_url" => urlencode($this->actlink)),"cal_event");
		};

		$e["title"] = $e["name"];
		$e["description"] = $e["comment"];

		$this->vars(array(
			"caption" => $e["caption"],
			"color" => $e["color"],
			"time" => date("H:i",$e["start"]) . "-" . date("H:i",$e["end"]),
			"event_link" => ($e["link"]) ? $e["link"] : $ev_link,
			"target" => $e["target"],
			"id" => $e["id"],
			"title" => ($e["title"]) ? $e["title"] : "(nimetu)",
			"object" => $name,
			"contents" => nl2br($e["description"]),
		));
		return $this->parse("event");
	}

};
?>
