<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/minicalendar.aw,v 1.1 2003/01/09 23:04:01 duke Exp $
// minicalendar

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property day_start type=time_select group=config
	@caption Päev algab

	@property day_end type=time_select group=config
	@caption Päev lõpeb

	@property object type=relpicker clid=CL_DOCUMENT group=config
	@caption Kontrollitav objekt

	@property searches type=select group=config
	@caption Objektiga seotud otsingud

	@default group=styles
	@property header_style type=relpicker clid=CL_CSS 
	@caption Päise stiil

	@property weekday_style type=relpicker clid=CL_CSS 
	@caption Nädalapäevade stiil
	
	@property weekend_style type=relpicker clid=CL_CSS
	@caption Nädalapäevade stiil (weekend)
	
	@property day_style type=relpicker clid=CL_CSS
	@caption Päevade stiil
	
	@property act_day_style type=relpicker clid=CL_CSS
	@caption Päevade stiil
	
	@property cel_day_style type=relpicker clid=CL_CSS
	@caption Päevade stiil

	@classinfo relationmgr=yes
	@groupinfo config caption=Seaded
	@groupinfo styles caption=Stiilid

*/

class minicalendar extends class_base
{
	function minicalendar($args = array())
	{
		extract($args);
		$this->init(array(
			"tpldir" => "calendar",
			"clid" => CL_MINICALENDAR,
		));
	}

	function callback_get_rel_types()
	{
                return array("1" => "kontrollitav objekt");
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
                switch($data["name"])
                {
			case "searches":
				$data["options"] = $this->get_searches($args["obj"]["oid"]);
				break;
		}
		return PROP_OK;
	}

	function get_searches($docid)
	{
		$almgr = get_instance("aliasmgr");
		$ali = $almgr->get_oo_aliases(array(
				"oid" => $docid,
				"filter" => array(&$this,"_filter_doc_aliases"),
				"modifier" => "aliases.target",
		));
		$searches = array("0" => " -- vali --");
		// for each search alias, I need to figure out the name of the form
		// and the names of all date elements in those forms
		$form_base = get_instance("formgen/form_base");
		foreach($ali as $key => $val)
		{
			$fid = $form_base->get_form_for_entry($key);
			$form_base->load($fid);
			$name = $form_base->name;
			$els = $form_base->get_all_elements(array("typematch" => "date"));
			if (is_array($els))
			{
				foreach($els as $ekey => $eval)
				{
					$searches["$fid/$ekey"] = $name . "/" . $eval;
				};
			
			};
		};
		return $searches;
	}
	
	function _filter_doc_aliases($row = array())
	{
		$retval = false;
		if ($row["class_id"] == CL_FORM_ENTRY)
		{
			$retval[$row["target"]] = ($row["name"]) ? $row["name"] : "nimetu (oid = $row[target])";
		};
		return $retval;
	}

	function parse_alias($args = array())
	{
		$cal = $this->get_object($args["alias"]["target"]);
		if (!is_array($cal["meta"]))
		{
			$cal["meta"] = array();
		};
		$args = $cal["meta"] + array("id" => $args["oid"],"target" => $cal["oid"]);
		classload("calendar");
		$co = new calendar();
		return $co->gen_month($args);
	}
};
?>
