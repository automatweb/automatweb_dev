<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/event_property_lib.aw,v 1.13 2004/10/28 09:46:24 kristo Exp $
// Shared functionality for event classes
class event_property_lib extends aw_template
{
	function event_property_lib()
	{
		$this->init(array(
			"tpldir" => "calendar",
		));
	}

	// your basic view, shows calendars from left to right
	function calendar_others($arr)
	{
		$event_obj  = $arr["obj_inst"];
		$brlist = new object_list(array(
			"brother_of" => $event_obj->id(),
		));

		$plrlist = array();

		$current_start = $event_obj->prop("start1");

		$pl = get_instance(CL_PLANNER);

		for($o =& $brlist->begin(); !$brlist->end(); $o =& $brlist->next())
		{
			//if ($o->id() != $event_obj->id())
			//{
				$menu_id = $o->parent();
				$menu_obj = new object($menu_id);

				$cal_conns = $menu_obj->connections_to(array(
					"type" => 6 // EVENT_FOLDER,
				));

				$first = reset($cal_conns);
				if (is_object($first))
				{
					$cal_obj = $first->from();
					$plrlist[$cal_obj->id()] = $cal_obj->id();
				};
			//};
		};

		if (sizeof($plrlist) == 0)
		{
			return false;
		};

		$this->read_template("others.tpl");

		$tt = $tc = "";

		// XXX: get date from url
		$use_date = aw_global_get("date");
		if (empty($use_date))
		{
			$use_date = date("d-m-Y");
		};
		list($d,$m,$y) = explode("-",$use_date);

		$tm = mktime(0,0,0,$m,$d,$y);

		classload("date_calc");

		$range = get_date_range(array(
			"time" => $tm,
			"type" => "day",
		));


		$this->vars(array(
			"date" => date("d.m Y",$tm),
			"prevlink" => aw_url_change_var("date",$range["prev"]),
			"nextlink" => aw_url_change_var("date",$range["next"]),
		));

		// XXX: arvestada kalendris määratud päeva alguse ja lõpu aegu
		$day_start = mktime(9,0,0,$m,$d,$y);
		$day_end = mktime(21,0,0,$m,$d,$y);

		$step = 60 * 60; // 1. tund

		$first = true;
		foreach($plrlist as $pl_id)
		{
			$pl_obj = new object($pl_id);
			$events = $pl->get_event_list(array(
				"id" => $pl_id,
				"start" => $day_start,
				"end" => $day_end,
			));

			$this->vars(array(
				"calendar_name" => $pl_obj->name(),
			));
			$tt .= $this->parse("one_calendar");

			$cells = "";
			for ($ts = $day_start; $ts <= $day_end; $ts = $ts + $step)
			{
				$evstr = "";
				$free = true;
				foreach($events as $event)
				{
					if (between($event["start"],$ts,$ts+$step-1))
					{
						$ev_obj = new object($event["id"]);
						$ev_obj = $ev_obj->get_original();
						if ($ev_obj->class_id() != CL_CALENDAR_VACANCY)
						{
							$evstr .= $ev_obj->name() . "<br>";
							$free = false;
						};
					};
				};

				$this->vars(array(
					"event" => $evstr,
					"time" => date("H:i",$ts),
					"selector_cell" => "",
					"bgcolor" => $free ? "#CCFFCC" : "#FFCCCC",
				));


				if ($first)
				{
					$this->vars(array(
						"time" => date("H:i",$ts),
						"checked" => checked(between($current_start,$ts,$ts+$step-1)),
						"event_sel_id" => $ts,
					));

					$this->vars(array(
						"selector_cell" => $this->parse("selector_cell"),
					));
				}

				$cells .= $this->parse("cell");

			};

			$this->vars(array(
				"cell" => $cells,
			));

			$tc .= $this->parse("one_calendar_content");

			$first = false;
		};
		
		// 

		$this->vars(array(
			"one_calendar" => $tt,
			"one_calendar_content" => $tc,
		));

		$all_props = array();
		$all_props[$arr["prop"]["name"]] = array(
			"type" => "text",
			"name" => $arr["prop"]["name"],
			"value" => $this->parse(),
			"caption" => $arr["prop"]["caption"],
			"no_caption" => $arr["prop"]["no_caption"],
		);

		return $all_props;

		
	}

	function process_other_selector($arr)
	{
		$event_obj = $arr["obj_inst"];
		$event_obj->set_prop("start1",$arr["request"]["start_time"]);

	}


	function callb_human_name($arr)
	{
		if ($_GET["get_csv_file"] == 1)
		{
			return $arr["name"];
		}
		else
		{
			return html::href(array(
			 "url" => $this->mk_my_orb("change",array(
			    "id" => $arr["id"],
		    	"return_url" => urlencode(aw_global_get("REQUEST_URI")),
			 ),CL_CRM_PERSON),
			 "caption" => $arr["name"],
			));
		}
	}

	
	function participant_selector($arr)
	{
		classload("vcl/table");
		$rtrn = array();
		$table = new vcl_table();
		
		$table->define_field(array(
							'name' => 'name',
							'caption' => 'Nimi',
							'sortable' => '1',
							'callback' => array(&$this,'callb_human_name'),
							'callb_pass_row' => true,
		));
		$table->define_field(array(
							'name' => 'phone',
							'caption' => 'Telefon',
							'sortable' => '1',
		));

		$table->define_field(array(
							'name' => 'email',
							'caption' => 'E-post',
							'sortable' => '1',
		));

		$noshow = array('status', 'phone','firstname','lastname','name','email');

		$datas = $arr["obj_inst"]->connections_to(array(
			"from.class_id" => CL_CALENDAR_REGISTRATION_FORM,
			"type" => 3 // regform.RELTYPE_DATA
		));
		$darr = array();
		$fields = array();
		$cff = get_instance("cfg/cfgform");
		$clsfs = array();
		foreach($datas as $d_c)
		{
			$to = $d_c->from();
			$darr[$to->prop("person_id")] = $to;
			
			if (!$to->meta("cfgform_id"))
			{
				$ps = $to->get_property_list();
			}
			else
			{
				$ps = $cff->get_props_from_cfgform(array(
					"id" => $to->meta("cfgform_id")
				));
			}
			
			foreach($to->properties() as $pn => $pv)
			{
				if (in_array($pn,$noshow) || !isset($ps[$pn]))
				{
					continue;
				}

				if (trim($pv) != "" && $ps[$pn]["type"] != "hidden")
				{
					$fields[$pn] = $ps[$pn]["caption"];
				}
				if ($ps[$pn]["type"] == "classificator")
				{
					$clsfs[$pn] = 1;
				}
			}
		}

		foreach($fields as $fld => $fld_c)
		{
			$table->define_field(array(
				"name" => $fld,
				"caption" => $fld_c,
				"sortable" => 1,
				"align" => "center"
			));
		}

		$table->define_field(array(
							'name' => 'rank',
							'caption' => 'Ametinimetus',
							'sortable' => '1',
		));
		if ($arr["request"]["get_csv_file"] != 1)
		{
			$table->define_chooser(array(
							'name' => 'check',
							'field' => 'id',
							'caption' => 'X',
			));
		}

		$conns = $arr['obj_inst']->connections_to(array());
		//arr($conns);
		foreach($conns as $conn)
		{
			$person = get_instance(CL_CRM_PERSON);
			if($conn->prop('from.class_id')==CL_CRM_PERSON)
			{
				$data = $person->fetch_person_by_id(array('id'=>$conn->prop('from')));
				$dat = array(
					'id' => $conn->prop('from'),
					'name' => $data['name'],
					'phone' => $data['phone'],
					'email' => $data['email'],
					'rank' => $data['rank'],
					'reg_data' => $regd
				);

				$regd = "";
				if (($_tmp = $darr[$conn->prop("from")]))
				{
					$regd = html::href(array(
						"url" => $this->mk_my_orb("view", array(
							"id" => $_tmp->id(), 
							"cfgform" => $_tmp->meta("cfgform"),
							"return_url" => urlencode(aw_global_get("REQUEST_URI"))
						), CL_CALENDAR_REGISTRATION_FORM),
						"caption" => "Vaata"
					));
					foreach($_tmp->properties() as $pn => $pv)
					{
						if (!isset($dat[$pn]))
						{
							if ($clsfs[$pn] == 1 && is_oid($pv) && $this->can("view", $pv))
							{
								$pv = obj($pv);
								$pv = $pv->name();
							}
							$dat[$pn] = $pv;
						}
					}
				}
				$table->define_data($dat);
			}
		}

		if ($arr["request"]["get_csv_file"] == 1)
		{
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: inline; filename=osalejad.xls;");
			$table->sort_by();
			die($table->draw());
		}
		return array('tabel'=> array('type'=>'table','vcl_inst'=>&$table,'no_caption' => 1));
	}

};
?>
