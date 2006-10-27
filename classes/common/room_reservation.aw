<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room_reservation.aw,v 1.1 2006/10/27 15:03:04 markop Exp $
// room_reservation.aw - Ruumi broneerimine 
/*
@default table=objects
@default group=general
@default field=meta
@default method=serialize

@classinfo syslog_type=ST_ROOM_RESERVATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@property rooms type=relpicker multiple=1 reltype=RELTYPE_ROOM
@caption Ruumid

@property reservation_template type=select 
@caption Broneeringu template

@property levels type=table no_caption=1 
@Tasemed

@reltype ROOM value=1 clid=CL_ROOM
@Ruum mida broneerida


*/

class room_reservation extends class_base
{
	function room_reservation()
	{
		$this->init(array(
			"tpldir" => "common/room",
			"clid" => CL_ROOM_RESERVATION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
				
				case "levels":
					$this->do_table($arr);
				break;	
				case "reservation_template":
					$tm = get_instance("templatemgr");
					$prop["options"] = $tm->template_picker(array(
						"folder" => "common/room"
					));
					if(!sizeof($prop["options"]))
					{//arr($prop);
						$prop["caption"] .= t("\n".$this->site_template_dir."");
		//				$prop["type"] = "text";
		//				$prop["value"] = t("Template fail peab asuma kataloogis :".$this->site_template_dir."");
					}
					break;
						
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
			case "levels":
				$this->submit_meta($arr);
				break;
		}
		return $retval;
	}	
	function submit_meta($arr = array())
	{
		$meta = $arr["request"]["meta"];
		//praagib välja tasemed, kus ei ole kas adekvaatset template faili või nime
		if(($arr["prop"]["name"] == "levels") && is_array($meta))
		{
			$temp_arr = array();
			foreach($meta as $metadata)
			{
				if((strlen($metadata["name"]) > 0) && strlen($metadata["template"]) > 4)
				{
					$temp_arr[] = $metadata;
				}
			}
			$meta = $temp_arr;
		}
		if (is_array($meta))
		{
			$so = new object($arr["obj_inst"]->id());
			$so->set_name($arr["obj_inst"]->name());
			$so->set_meta($arr["prop"]["name"], $meta);
			$so->save();
		};
	}
	
	function do_table($arr)
	{
		$levels = $arr["obj_inst"]->meta("levels");
		$t = &$arr["prop"]["vcl_inst"];
		
		$t->define_field(array(
			"name" => "id",
			"caption" => t("Tase"),
//			"sortable" => 1,
		));		
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Etapi nimi"),
		));

		aw_global_set("output_charset", "utf-8");
		$lg = get_instance("languages");
		$langdata = $lg->get_list();
		foreach($langdata as $id => $lang)
		{
			if($arr["obj_inst"]->lang_id() != $id)
			{
				$t->define_field(array(
					"name" => $id,
					"lang_id" => $id,
					"caption" => t($lang),
				));
			}
		}

		$t->define_field(array(
			"name" => "template",
			"caption" => t("Template"),
		));
		
		
		$tm = get_instance("templatemgr");
		$options = $tm->template_picker(array(
			"folder" => "common/room"
			));
		
		$transyes = $arr["obj_inst"]->prop("transyes");
//		$langdata = array();
		$count = 1;
		foreach($levels as $level)
		{
			$data = array(
				"id" => $count,
			);
		
			$data["name"] = html::textbox(array(
				"name" => "meta[".$count."][name]",
				"size" => 30,
				"value" => $level["name"],
			));
			
			foreach($langdata as $lid => $lang)
			{
				 $data[$lid] = html::textbox(array(
					"name" => "meta[".$count."][tolge][".$lid."]",
					"size" => 15,
					"value" => $level["tolge"][$lid],
				));
			}

			$data["template"] = html::select(array(
				"name" => "meta[".$count."][template]",
				"options" => $options,
				"value" => $level["template"],
			));
			$t->define_data($data);
			$count++;

		}
		$new_data = array(
			"id" => $count,
		);
		
		 $new_data["name"] = html::textbox(array(
			"name" => "meta[".$count."][name]",
			"size" => 30,
			"value" => "",
		));
		
		foreach($langdata as $lid => $lang)
		{
			 $new_data[$lid] = html::textbox(array(
				"name" => "meta[".$count."][tolge][".$lid."]",
				"size" => 15,
				"value" => "",
			));
		}


		$new_data["template"] = html::select(array(
			"name" => "meta[".$count."][template]",
			"options" => $options,
			"value" => "",
		));

//		$new_data["template"] = html::textbox(array(
//			"name" => "meta[".$count."][template]",
//			"size" => 30,
//			"value" => "",
//		));
		$t->define_data($new_data);
		$t->set_sortable(false);
	}
	
	
	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//

	/** Change the realestate object info.
		
		@attrib name=parse_alias is_public="1" caption="Change"
	
	**/
	function parse_alias($arr)
	{
		global $level;
		enter_function("oom_reservation::parse_alias");
		$targ = obj($arr["alias"]["target"]);
		$room = $targ->get_first_obj_by_reltype(array("type" => "RELTYPE_ROOM"));
		if(!is_object($room))
		{
			return "";
		}
		$levels = $targ->meta("levels");
		if(!isset($level))
		{
			$level=0;
		}
		else 
		{
			$level++;
		}
		//arr()
		$tpl = $levels[$level]["template"];

		$this->read_template($tpl);
		lc_site_load("room_reservation", &$this);
		
		
		$data = array("joga" => "jogajoga");

		$this->vars($this->get_site_props(array(
			"room" => $room,
		)));
		$this->vars($data);
		
		$this->vars(array(
			"reforb" => $this->mk_reforb("parse_alias",array(
				"section"	=> aw_global_get("section"),
				"level"		=> $level,
				"return_to"	=> post_ru(),
				"id"		=> $arr["alias"]["target"],
				"do"		=> $do,
				"parent"	=> $parent,
				"clid"		=> $clid,
				"default"	=> $default,
			)),
			"url" => aw_global_get("section")."?level=".$level,
			"submit" => $this->mk_my_orb('submit',array(
				'id' => $arr["alias"]["target"],
				'level' => $level,
				'url' => aw_global_get("section")."?level=".$level,
				"return_to"	=> post_ru(),
			), CL_ROOM_RESERVATION),
		));
		
		//property väärtuse saatmine kujul "property_nimi"_value
		exit_function("oom_reservation::parse_alias");
		return $this->parse();
	}
	
	function get_site_props($arr)
	{
		extract($arr);
		
		$data = array();
		$people_opts = array();
		$x=0;
		while($x < ($room->prop("max_capacity") + 1))
		{
			$people_opts[] = $x;
			$x++;
		}
		$data["people"] = html::select(array(
			"options" => $people_opts,
			"value" => $_SESSION["room_reservation"]["people"],
			"name" => "people",
		));
		$data["comment"] = html::textarea(array(
			"value" => $_SESSION["room_reservation"]["comment"],
			"name" => "comment",
		));
		$data["name"] = html::textbox(array(
			"value" => $_SESSION["room_reservation"]["name"],
			"name" => "name",
		));
		$data["email"] = html::textbox(array(
			"value" => $_SESSION["room_reservation"]["email"],
			"name" => "email",
		));
		$data["phone"] = html::textbox(array(
			"value" => $_SESSION["room_reservation"]["phone"],
			"name" => "phone",
		));
		
		$data["pay_link"] = "http://link.maksmisesse.aw";
		$data["products_link"] = "http://link.produktide_juurde.aw";
		$data["calendar_link"] = "http://link.kalendri.aw";
		$data["sum"] = $_SESSION["room_reservation"]["people"]*$_SESSION["room_reservation"]["price"];
		foreach($_SESSION["room_reservation"] as $key => $val)
		{
			$data[$key."_value"] = $val;
		}
		return $data;
	}

	/** submit
		@attrib name=submit nologin="1" 
		@param id required type=int 
		@param return_to required type=string 
		@param level optional type=int
	**/
	function submit($args = array())//tegeleb postitatud infoga
	{
		foreach($_POST as $key=>$val)
		{
			$_SESSION["room_reservation"][$key] = $val;
		}
		extract($args);
		if(!$_GET["level"])
		{
			return aw_url_change_var("", "" , $args["return_to"])."?level=".$level;
		}
		return aw_url_change_var("level", $level , $args["return_to"]);//."?level=".$level;
	}
}
?>
