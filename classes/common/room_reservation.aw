<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room_reservation.aw,v 1.14 2006/12/07 20:40:17 markop Exp $
// room_reservation.aw - Ruumi broneerimine 
/*
@default table=objects
@default group=general
@default field=meta
@default method=serialize

@classinfo syslog_type=ST_ROOM_RESERVATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@property rooms type=relpicker multiple=1 reltype=RELTYPE_ROOM store=connect field=meta method=serialize
@caption Ruumid

@property reservation_template type=select 
@caption Broneeringu template

@property levels type=table no_caption=1 store=no
@caption Tasemed

@reltype ROOM value=1 clid=CL_ROOM
@caption Ruum mida broneerida


*/

class room_reservation extends class_base
{
	function room_reservation()
	{
		$this->init(array(
			"tpldir" => "common/room",
			"clid" => CL_ROOM_RESERVATION
		));
		$this->banks = array(
			"hansapank" => "Hansapank",
			"seb" => "Ühispank",
			"sampopank" => "Sampopank",
		);
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
					{
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
		//	$so->save();
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
		$this->vars($this->get_level_urls($levels));
		if(!isset($level))
		{
			$level=0;
		}
		else 
		{
			$level++;
		}
		$tpl = $levels[$level]["template"];

		$this->read_template($tpl);
		lc_site_load("room_reservation", &$this);
		
		
		$data = array("joga" => "jogajoga");
		$data["revoke_url"] = $this->mk_my_orb("revoke_reservation", array("room" => $room->id()));


		$this->vars($this->get_site_props(array(
			"room" => $room,
			"id" => $arr["alias"]["target"],
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
			"back_url" => aw_global_get("section")."?level=".($level-2),
			"url" => aw_global_get("section")."?level=".$level,
			"submit" => $this->mk_my_orb('submit_data',array(
				'id' => $arr["alias"]["target"],
				'level' => $level,
				'url' => aw_global_get("section")."?level=".$level,
				"return_to"	=> post_ru(),
			), CL_ROOM_RESERVATION),
			"affirm_url" => $this->mk_my_orb("affirm_reservation", array(
				"section" => aw_global_get("section"),
				"level" => $level,
				"id" => $id,
				"room" => $room->id(),
			)),
			"pay_url" => $this->mk_my_orb("pay_reservation", array(
//				"section" => aw_global_get("section"),
//				"level" => $level,
				"bron_id" => $_SESSION["room_reservation"][$room->id()]["bron_id"],
				"room" => $room->id(),
			)),
		));
		
		//property väärtuse saatmine kujul "property_nimi"_value
		exit_function("oom_reservation::parse_alias");
		return $this->parse();
	}
	
	
	//returns url for each level
	function get_level_urls($levels)
	{
		$data = array();
		foreach($levels as $key => $level)
		{

			if(!($key))
			{
				$data[$level["name"]."_url"] = aw_global_get("section");
			}
			else
			{
				$data[$level["name"]."_url"] = aw_global_get("section")."?level=".($key-1);
			}
		}
		
		$data["pay_url"] = "http://link.maksmisesse.aw";
		return $data;
	}
	function get_site_props($arr)
	{
		extract($arr);
		$data = array();
		$people_opts = array();
		$x=1;
		while($x < ($room->prop("max_capacity") + 1))
		{
			$people_opts[$x] = $x;
			$x++;
		}
		$data["people"] = html::select(array(
			"options" => $people_opts,
			"value" => $_SESSION["room_reservation"][$room->id()]["people"],
			"name" => "people",
		));
		$data["comment"] = html::textarea(array(
			"value" => $_SESSION["room_reservation"][$room->id()]["comment"],
			"name" => "comment",
		));
		$data["name"] = html::textbox(array(
			"value" => $_SESSION["room_reservation"][$room->id()]["name"],
			"name" => "name",
		));
		$data["email"] = html::textbox(array(
			"value" => $_SESSION["room_reservation"][$room->id()]["email"],
			"name" => "email",
		));
		$data["phone"] = html::textbox(array(
			"value" => $_SESSION["room_reservation"][$room->id()]["phone"],
			"name" => "phone",
		));
		
		$loc = obj($room->prop("location"));
		$bank_inst = get_instance(CL_BANK_PAYMENT);
		$bank_payment = $loc->prop("bank_payment");
		if(is_oid($bank_payment))
		{
			$payment = obj($bank_payment);
			foreach($payment->meta("bank") as $key => $val)
			{
				if(!$val["sender_id"])
				{
					continue;
				}
				$checked=0;
				if($_SESSION["room_reservation"][$room->id()]["bank"] == $key)
				{
					$checked = 1;
				}
				$data["bank_".$key] = html::radiobutton(array(
					"value" => $key,
					"checked" => $checked,
					"name" => "bank",
				));
			}
		}

		$data["products_link"] = html::popup(array(
			"url" 	=> $this->mk_my_orb("get_web_products_table", array(
					"id" => $id,
					"room" => $room->id(),
				)),
			"no_link" => 1,
			"width" => 600,
			"height" => 600,
			"scrollbars" => 1,
		));
		//"http://link.produktide_juurde.aw";
		$data["calendar_link"] = html::popup(array(
			"url" 	=> $this->mk_my_orb("get_web_calendar_table", array(
					"id" => $id,
					"room" => $room->id(),
				)),
			"no_link" => 1,
			"scrollbars" => 1,
			"width" => 600,
			"height" => 600,
		));
		$room_inst = get_instance(CL_ROOM);
		
		$data["sum"] = $room_inst->cal_room_price(array(
			"room" => $room->id(),
			"people" => $_SESSION["room_reservation"][$room->id()]["people"],
			"start" => $_SESSION["room_reservation"][$room->id()]["start"],
			"end" => $_SESSION["room_reservation"][$room->id()]["end"],
			"products" => $_SESSION["room_reservation"][$room->id()]["products"],
		));
		//muidu annab massiivi kõikide valuutade hindadega... et eks selgub, kuda seda hiljem tahetakse
		$data["sum"] = reset($data["sum"]);
		if(!$data["sum"])
		{
			$data["sum"] = 0;
		}
		
		$data["bargain"] = $room_inst->bargain_value;
		$data["sum_wb"] = (double)$data["sum"] + (double)$room_inst->bargain_value;
		
		$data["time_from"] = "";
		$data["time_to"] = "";
		$data["time_day"] = "";
		$data["time"] = "";
		$data["hours"] = (int)(($_SESSION["room_reservation"][$room->id()]["end"]-$_SESSION["room_reservation"][$room->id()]["start"] + 60) / 3600);
		$data["minutes"] = (int)(($_SESSION["room_reservation"][$room->id()]["end"]-$_SESSION["room_reservation"][$room->id()]["start"]) / 60) - $data["hours"]*60;
		$data["time_str"] = $this->get_time_str(array(
			"start" => $_SESSION["room_reservation"][$room->id()]["start"],
			"end" => $_SESSION["room_reservation"][$room->id()]["end"]
		));
		foreach($_SESSION["room_reservation"][$room->id()] as $key => $val)
		{
			$data[$key."_value"] = $val;
		}
			$data["bank_value"] = $this->banks[$_SESSION["room_reservation"][$room->id()]["bank"]];
		return $data;
	}

	function get_time_str($arr)
	{
		$room_inst = get_instance(CL_ROOM);
		extract($arr);
		$res = "";
		$res.= $room_inst->weekdays[(int)date("w" , $arr["start"])];
		$res.= ", ";
		$res.= date("d.m.Y" , $arr["start"]);
		$res.= ", ";
		$res.= date("h:i" , $arr["start"]);
		$res.= " - ";
		$res.= date("h:i" , $arr["end"]);
		return $res;
	}

	/** submit_data
		@attrib name=submit_data nologin="1" 
		@param id required type=int 
		@param return_to required type=string 
		@param level optional type=int
	**/
	function submit_data($args = array())//tegeleb postitatud infoga
	{
		$bron_obj = obj($args["id"]);
		$room = reset($bron_obj->prop("rooms"));
		foreach($_POST as $key=>$val)
		{
			$_SESSION["room_reservation"][$room][$key] = $val;
		}
		extract($args);
		if(!$_GET["level"])
		{
			return aw_url_change_var("", "" , $args["return_to"])."?level=".$level;
		}
		return aw_url_change_var("level", $level , $args["return_to"]);//."?level=".$level;
	}
	
	/**
	@attrib name=get_web_products_table api=1 params=name nologin=1
		@param room required type=int
	**/
	function get_web_products_table($arr)
	{
		extract($arr);
		if(is_oid($room) && $this->can("view", $room))
		{
			$room = obj($room);
		}
		else
		{
			return false;
		}
		classload("vcl/table");
		$t = new vcl_table;
		$res_inst = get_instance(CL_RESERVATION);
		$html = $res_inst->_get_products_order_view(array(
			"prop" => array("vcl_inst" => &$t),
			"web" => 1,
			"room" => $room->id(),
		));
		//kui tuleb kuskilt kaugelt müstilisest templatest tellimise vaade, siis jääb asi nii nagu on... muidu teeb tabeli
		if(!$html)
		{
			$html = $t->draw();
		}
		
		$sf = new aw_template;
		$sf->db_init();
		$sf->tpl_init("automatweb");
		$sf->read_template("index.tpl");
			
		$action = $this->mk_my_orb("submit_web_products_table", array("room" => $room->id()));
		$sf->vars(array(
			"content" => "<form name='products_form' action=".$action." method=POST>".$html."<br></form>",
			"uid" => aw_global_get("uid"),
			"charset" => aw_global_get("charset")
		));
//		die($ret);
		die($sf->parse());
		die($t->draw()."<br>".html::submit());
	}
	
	/**
	@attrib name=submit_web_products_table api=1 params=name nologin=1
		@param amount optional type=array
		@param room required type=oid
	**/
	function submit_web_products_table($arr)
	{
		//see siis variant kui info tuleb templatest jne
		if(is_array($arr["add_to_cart"]))
		{
			$_SESSION["room_reservation"][$arr["room"]]["products"] = $arr["add_to_cart"];
			$_SESSION["soc_err"] = $arr["add_to_cart"];
//			aw_global_set("soc_err", $arr["add_to_cart"]);
//			"quantity" => $soce[$oid]["ordered_num_enter"],
//			aw_global_set("quantity" => $soce[$oid]["ordered_num_enter"],
		}
		else
		{
			$_SESSION["room_reservation"][$arr["room"]]["products"] = $arr["amount"];
		}
		$ret.= '<script language="javascript">
			window.opener.location.reload();
			window.close();
		</script>';
		//return $ret;
		die($ret);
	}
	
	/**
	@attrib name=get_web_calendar_table api=1 params=name nologin=1
		@param room optional type=int
		@param id optional type=int
	**/
	function get_web_calendar_table($arr)
	{
		if(is_oid($arr["id"]))
		{
			$room_res = obj($arr["id"]);
			$rooms = $room_res->prop("rooms");
		}
		else
		{
			$rooms = array($arr["room"]);
		}
		$tables = "";
		classload("vcl/table");

		$sf = new aw_template;
		$sf->db_init();

		if($room_res->prop("reservation_template"))
		{
			$sf->tpl_init("common/room");
			$tpl = $room_res->prop("reservation_template");
		}
		else
		{
			$sf->tpl_init("automatweb");
			$tpl = "index.tpl";
		}
		$sf->read_template($tpl);
		$action = $this->mk_my_orb("submit_web_calendar_table", array("room" => $arr["room"]));
		$arr["obj_inst"] = obj($arr["room"]);
		$res_inst = get_instance(CL_ROOM);
		$select = $res_inst->_get_calendar_select($arr);
		if($sf->is_template("CALENDAR"))
		{
			$c = "";
			foreach($rooms as $room)
			{
				$t = new vcl_table();

				$res_inst->_get_calendar_tbl(array(
					"prop" => array("vcl_inst" => &$t),
					"room" => $room,
					"web" => 1,
				));
				$sf->vars(array("calendar" => $t->draw()));
				$c.= $sf->parse("CALENDAR");
			}
			$sf->vars(array(
				"CALENDAR" => $c,
				"select" => $select,
				"time_select" => $res_inst->_get_time_select($arr),
				"length_select" => $res_inst->_get_length_select($arr),
				"submit_url" => $action,
			));
			$sf->vars(array("SELECT" => $sf->parse("SELECT")));
		
		}
		else
		{
			foreach($rooms as $room)
			{
				$t = new vcl_table();
				$res_inst->_get_calendar_tbl(array(
					"prop" => array("vcl_inst" => &$t),
					"room" => $room,
					"web" => 1,
	//				
				));
				$tables.= $t->draw();
			}
		}
		$sf->vars(array(
			"content" => "<form name='products_form' action=".$action." method=POST>".$select.$tables."<br>".html::submit(array("value" => t("Salvesta")))."</form>",
			"uid" => aw_global_get("uid"),
			"charset" => aw_global_get("charset")
		));
		
//		die($ret);
		die($sf->parse());

		die($t->draw());
	}
	
	/**
	@attrib name=submit_web_calendar_table api=1 params=name nologin=1
		@param bron optional type=array
		@param room required type=oid
	**/
	function submit_web_calendar_table($arr)
	{
		$room_inst = get_instance(CL_ROOM);
		$times = $room_inst->_get_bron_time(array(
			"bron" => $arr["bron"][$arr["room"]],
			"id" => $arr["room"],
		));
		$_SESSION["room_reservation"][$arr["room"]]["start"] = $times["start"];
		$_SESSION["room_reservation"][$arr["room"]]["end"] = $times["end"];
		$ret.= '<script language="javascript">
			window.opener.location.reload();
			window.close();
		</script>';
		//return $ret;
		die($ret);
	}
	
	//makes the reservation object ... then this stuff is ready for paying and stuff
	/**
	@attrib name=affirm_reservation api=1 params=name nologin=1
		@param bron_id optional type=array
			reservation id
		@param room required type=oid
			room id
		@param section optional type=string
			aw section
		@param level optional type=int	
			web reservarion level	
	**/
	function affirm_reservation($arr)
	{
		extract($arr);
		$room_inst = get_instance(CL_ROOM);
		$bron_id;
		if(!$bron_id)
		{
			$bron_id = $_SESSION["room_reservation"][$room]["bron_id"];
		}
		$_SESSION["room_reservation"][$room]["bron_id"] = $room_inst->make_reservation(array(
			"id" => $room,
			"res_id" => $bron_id,
			"data" => $_SESSION["room_reservation"][$room],
		));
		//return $section."?level=".$level;
	}

	//makes the reservation object ... then this stuff is ready for paying and stuff
	/**
	@attrib name=pay_reservation api=1 params=name nologin=1
		@param bron_id optional type=array
			reservation id
		@param room required type=oid
			room id
		@param section optional type=string
			aw section
		@param level optional type=int	
			web reservarion level	
	**/
	function pay_reservation($arr)
	{
		extract($arr);
		$room_inst = get_instance(CL_ROOM);
		$room = obj($room);
		$bron_id;
		if(!$bron_id)
		{
			$bron_id = $_SESSION["room_reservation"][$room->id()]["bron_id"];
		}
		
		$_SESSION["room_reservation"][$room->id()]["bron_id"] = $room_inst->make_reservation(array(
			"id" => $room->id(),
			"res_id" => $bron_id,
			"data" => $_SESSION["room_reservation"][$room->id()],
		));
		$bron = obj($_SESSION["room_reservation"][$room->id()]["bron_id"]);
		if(!is_oid($room->prop("location")))
		{
			return;
		}
		$loc = obj($room->prop("location"));
		$bank_inst = get_instance(CL_BANK_PAYMENT);
		$bank_payment = $loc->prop("bank_payment");
		$_SESSION["bank_payment"]["url"] = "lahe link kuhu vastuseid saada";
		$ret = $bank_inst->do_payment(array(
			"bank_id" => $_SESSION["room_reservation"][$room->id()]["bank"],
			"amount" => $_SESSION["room_reservation"][$room->id()]["sum"],
			"reference_nr" => $_SESSION["room_reservation"][$room->id()]["bron_id"],
			"payment_id" => $bank_payment,
			"expl" => $bron->name(),
		));
		//kuna siiani asi ei jõua, siis makse kontrollis peaks vist sessiooni ära nullima... või ma ei tea
		$_SESSION["room_reservation"][$room->id()] = null;;
		return $ret;
		//return $section."?level=".$level;
	}

	//makes the reservation object ... then this stuff is ready for paying and stuff
	/**
	@attrib name=revoke_reservation api=1 params=name nologin=1
		@param bron_id optional type=array
			reservation oid
		@param room required type=oid	
			room oid
	**/
	function revoke_reservation($arr)
	{
		extract($arr);
		if(!$bron_id)
		{
			$bron_id = $_SESSION["room_reservation"][$arr["room"]]["bron_id"];
		}
		if(is_oid($bron_id))
		{
			$bron = obj($bron_id);
			$bron->delete();
		}
		$_SESSION["room_reservation"][$arr["room"]] = null;
		return $section;
	}
}
?>
