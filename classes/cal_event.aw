<?php
// cal_event.aw - Kalendri event
// $Header: /home/cvs/automatweb_dev/classes/Attic/cal_event.aw,v 2.2 2002/01/16 20:27:49 duke Exp $
global $class_defs;
$class_defs["cal_event"] = "xml";

class cal_event extends aw_template {
	function cal_event($args = array())
	{	
		extract($args);
		$this->db_init();
		$this->tpl_init("cal_event");
	}

	////
	// !Fills the event editing form with data
	function _fill_event_form($args = array())
	{
		load_vcl("date_edit");
		$start = new date_edit("start");
		$start->configure(array("day" => 1,"month" => 2,"year" => 3));
		$start_ed = $start->gen_edit_form("start",$args["start"]);
		list($shour,$smin) = split("-",date("G-i",$args["start"]));
		if ($args["end"])
		{
			$dsec = $args["end"] - $args["start"];
			$dhour = (int)($dsec / (60 * 60));
			$dsec = $dsec - ($dhour * 60 * 60);
			$dmin = (int)($dsec / 60);
		}
		else
		{
			$dsec = $args["start"];
			$dhour = 1;
			$dmin = 0;
		};
		$colors = array(
			"#000000" => "must",
			"#990000" => "punane",
			"#009900" => "roheline",
			"#000099" => "sinine",
		);

		$calendars = array();
		$this->get_objects_by_class(array(
			"class" => CL_CALENDAR,
			"active" => 1,
		));
		while($row = $this->db_next())
		{
			if ($row["name"])
			{
				$calendars[$row["oid"]] = $row["name"];
			};
		};

		// nimekiri tundidest
                $h_list = range(0,23);
                // nimekiri minutitest
                $m_list = array("0" => "00", "15" => "15", "30" => "30", "45" => "45");
		$this->vars(array(
			"start" => $start_ed,
			"shour" => $this->picker($shour,$h_list),
			"smin" => $this->picker($smin,$m_list),
			"calendars" => $this->picker($args["folder"],$calendars),
			"dhour" => $this->picker($dhour,$h_list),
			"dmin" => $this->picker($dmin,$m_list),
			"color" => $this->picker($args["color"],$colors),
			"calendar_url" => $this->mk_my_orb("view",array("id" => $args["folder"]),"planner"),
			"icon_url" => get_icon_url(CL_CALENDAR,""),
		));
	}

	////
	// !Kuvab uue eventi lisamise vormi
	function add($args = array())
	{
		extract($args);
		$this->read_template("edit.tpl");
		$this->mk_path($parent,"Lisa kalendrisündmus");
		$this->_fill_event_form(array("folder" => $args["folder"]));
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent)),
		));
		return $this->parse();
	}

	////
	// !Submitib uue kalendri eventi
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		// sellest teeme timestampi
		$st = mktime($shour,$smin,0,$start["month"],$start["day"],$start["year"]);
		// lopu aeg
		$et = mktime($shour + $dhour,$smin + $dmin,59,$start["month"],$start["day"],$start["year"]);

		if ($parent)
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $title,
				"class_id" => CL_CAL_EVENT,
				"status" => 2,
			));

			$q = "INSERT INTO planner
				(id,start,end,title,place,description,color,folder)
                                VALUES ('$id','$st','$et','$title','$place','$description','$color','$folder')";
			$this->db_query($q);
		}
		else
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $title,
			));
			                         
			$q = "UPDATE planner SET
				start = '$st',
				end = '$et',
				title = '$title',
				color = '$color',
				place = '$place',
				folder = '$folder',
				description = '$description'
				WHERE id = '$id'";
			$this->db_query($q);
		}
		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !Kuvab olemasoleva eventi muutmise objekti
	function change($args = array())
	{
		extract($args);
		$object = $this->get_object($id);
		$this->mk_path($object["parent"],"Muuda kalendrisündmust");
		$q = "SELECT *,planner.* FROM objects LEFT JOIN planner ON (objects.oid = planner.id) WHERE objects.oid = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$this->read_template("edit.tpl");
		$this->_fill_event_form($row);
		$this->vars(array(
			"id" => $id,
			"title" => $row["title"],
			"place" => $row["place"],
			"description" => $row["description"],
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}
};
?>
