<?php

classload("shop");
class shop_stat extends shop
{
	function shop_stat()
	{
		$this->shop();
	}

	////
	// !shows adding form
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_SHOP_STAT_ADD_SHOP_STAT);
		$this->read_template("shop_stat_add.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"shop_list" => $this->multiple_option_list(array(),$this->get_list())
		));
		return $this->parse();
	}

	////
	// !adds or saves the shop stat object
	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_SHOP_STATS, "name" => $name, "comment" => $comment));
		}

		$this->db_query("DELETE FROM shop2shop_stat WHERE stat_id = $id");

		if (is_array($shops))
		{
			foreach($shops as $shid)
			{
				$this->db_query("INSERT INTO shop2shop_stat(shop_id,stat_id) values($shid,$id)");
			}
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	////
	// !returns an array of shops for stat object $id
	function get_shops_for_stat($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM shop2shop_stat WHERE stat_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["shop_id"]] = $row["shop_id"];
		}
		return $ret;
	}

	////
	// !return sthe stat object
	function get($id)
	{
		return $this->get_object($id);
	}

	////
	// !shows statistics
	function change($arr)
	{
		extract($arr);
		$st = $this->get($id);
		$this->mk_path($st["parent"], LC_SHOP_STAT_SHOPS_STAT);
		$this->read_template("show_shop_stat.tpl");

		load_vcl("date_edit");
		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
			"hour" => "",
			"minute" => ""
		));
	
		// we have to do this, cause in php all functions are virtual, and get($id) gets overriden in this class
		$shop = new shop;

		$shmenus = array();
		$shar = $this->get_shops_for_stat($id);
		foreach ($shar as $shid)
		{
			$shmenus = $shmenus + $shop->get_shop_categories($shid);
		}

		$this->vars(array(
			"change" => $this->mk_my_orb("change_stat", array("id" => $id)),
			"t_from" => $de->gen_edit_form("from", 0),
			"t_to"	=> $de->gen_edit_form("to", time()),
			"categories" => $this->multiple_option_list(array(),$shmenus),
			"reforb" => $this->mk_reforb("show_stat", array("id" => $id,"no_reforb" => true)),
			"change_stat" => $this->mk_my_orb("change_stat", array("id" => $id))
		));
		return $this->parse();
	}

	////
	// !change form for stat object
	function change_stat($arr)
	{
		extract($arr);
		$st = $this->get($id);
		$this->mk_path($st["parent"], LC_SHOP_STAT_CHANGE_SHOP_STAT);
		$this->read_template("shop_stat_add.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"shop_list" => $this->multiple_option_list($this->get_shops_for_stat($id),$this->get_list()),
			"name" => $st["name"],
			"comment" => $st["comment"]
		));

		return $this->parse();
	}

	////
	// !shows the statistics based on selections from the form
	function show_stat($arr)
	{
		extract($arr);
		$from = mktime($from["hour"],$from["minute"],0,$from["month"],$from["day"],$from["year"]);
		$to = mktime($to["hour"],$to["minute"],0,$to["month"],$to["day"],$to["year"]);
		
		switch($stat_type)
		{
			case "by_day":
				return $this->to_stat_by_day($id,$from,$to,$cats,$show_to);

			case "by_month":
				return $this->to_stat_by_month($id,$from,$to,$cats,$show_to);

			case "by_wd":
				return $this->to_stat_by_wd($id,$from,$to,$cats,$show_to);

			case "by_hr":
				return $this->to_stat_by_hr($id,$from,$to,$cats,$show_to);
		}
	}

	////
	// !turnover stats by day, for shop $id from $from to $to
	function to_stat_by_day($id,$from,$to,$cats,$show_to)
	{
		if ($show_to)
		{
			$this->read_template("to_stat_by_day.tpl");
		}
		else
		{
			$this->read_template("to_stat_by_day_no_to.tpl");
		}
		$sh = $this->get($id);
		$shopss = join(",",$this->map2("%s",$this->get_shops_for_stat($id)));

		$this->mk_path($sh["parent"],"<a href='".$this->mk_orb("change", array("id" => $id))."'>Statistics</a> / By days");

		$this->db_query("SELECT COUNT(*) AS cnt, SUM(t_price) AS sum, AVG(t_price) AS avg,day FROM orders WHERE tm >= $from AND tm <= $to AND shop_id IN ($shopss) GROUP BY day");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"day" => $this->time2date($row["day"],3),
				"sum" => $row["sum"],
				"avg" => $row["avg"],
				"cnt" => $row["cnt"]
			));
			$this->parse("DAY");
			$days[] = $this->time2date($row["day"],3);
			$m_sum = max($m_sum,$row["sum"]);
			$m_cnt = max($m_cnt,$row["cnt"]);
			$sum[] = $row["sum"];
			$avg[] = $row["avg"];
			$cnt[] = $row["cnt"];
			$t_sum += $row["sum"];
			$t_avg = $row["avg"];
			$t_cnt += $row["cnt"];
		}
		if (!$show_to)
		{
			$chart = $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$days)),
																									 "yvals" => urlencode(join(",",array(0,$m_cnt))),
																									 "data"  => urlencode(join(",",$cnt)),
																									 "title" => "Kogus päevade kaupa",
																									 "xtitle" => "Päev",
																									 "ytitle" => "Kogus"),"banner");
		}
		else
		{
			$chart = $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$days)),
																									 "yvals" => urlencode(join(",",array(0,max($m_sum,$m_cnt)))),
																									 "data"  => urlencode(join(",",$sum)),
																									 "data2"  => urlencode(join(",",$avg)),
																									 "data3"  => urlencode(join(",",$cnt)),
																									 "title" => "Käive päevade kaupa",
																									 "xtitle" => "Päev",
																									 "ytitle" => "Käive"),"banner");		
		}
		$this->vars(array(
			"name" => $sh["name"],
			"from" => $this->time2date($from,3),
			"to" => $this->time2date($to,3),
			"chart" => $chart,
			"t_sum" => $t_sum,
			"t_avg" => $t_avg,
			"t_cnt" => $t_cnt
		));
		return $this->parse();
	}

	////
	// !turnover stats by month, for shop $id from $from to $to
	function to_stat_by_month($id,$from,$to,$cats,$show_to)
	{
		if ($show_to)
		{
			$this->read_template("to_stat_by_month.tpl");
		}
		else
		{
			$this->read_template("to_stat_by_month_no_to.tpl");
		}
		$sh = $this->get($id);
		$shopss = join(",",$this->map2("%s",$this->get_shops_for_stat($id)));

		$this->mk_path($sh["parent"],"<a href='".$this->mk_orb("change", array("id" => $id))."'>Statistcs</a> / By months");

		$this->db_query("SELECT COUNT(*) AS cnt, SUM(t_price) AS sum, AVG(t_price) AS avg,month FROM orders WHERE tm >= $from AND tm <= $to AND shop_id IN ($shopss) GROUP BY month");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"mon" => $this->time2date($row["month"],7),
				"sum" => $row["sum"],
				"avg" => $row["avg"],
				"cnt" => $row["cnt"]
			));
			$this->parse("MONTH");
			$days[] = $this->time2date($row["month"],7);
			$m_sum = max($m_sum,$row["sum"]);
			$m_cnt = max($m_cnt,$row["cnt"]);
			$sum[] = $row["sum"];
			$avg[] = $row["avg"];
			$cnt[] = $row["cnt"];
			$t_sum += $row["sum"];
			$t_avg = $row["avg"];
			$t_cnt += $row["cnt"];
		}
		if (!$show_to)
		{
			$chart = $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$days)),
																								 "yvals" => urlencode(join(",",array(0,$m_cnt))),
																								 "data"  => urlencode(join(",",$cnt)),
																								 "title" => "Kogus kuude kaupa",
																								 "xtitle" => "Kuu",
																								 "ytitle" => "Kogus"),"banner");
		}
		else
		{
			$chart = $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$days)),
																								 "yvals" => urlencode(join(",",array(0,max($m_sum,$m_cnt)))),
																								 "data"  => urlencode(join(",",$sum)),
																								 "data2"  => urlencode(join(",",$avg)),
																								 "data3"  => urlencode(join(",",$cnt)),
																								 "title" => "Käive kuude kaupa",
																								 "xtitle" => "Kuu",
																								 "ytitle" => "Käive"),"banner");
		}
		$this->vars(array(
			"name" => $sh["name"],
			"from" => $this->time2date($from,3),
			"to" => $this->time2date($to,3),
			"chart" => $chart,
			"t_sum" => $t_sum,
			"t_avg" => $t_avg,
			"t_cnt" => $t_cnt
		));
		return $this->parse();
	}

	////
	// !turnover stats by weekday, for shop $id from $from to $to
	function to_stat_by_wd($id,$from,$to,$cats,$show_to)
	{
		if ($show_to)
		{
			$this->read_template("to_stat_by_wd.tpl");
		}
		else
		{
			$this->read_template("to_stat_by_wd_no_to.tpl");
		}
		$sh = $this->get($id);
		$shopss = join(",",$this->map2("%s",$this->get_shops_for_stat($id)));

		$this->mk_path($sh["parent"],"<a href='".$this->mk_orb("change", array("id" => $id)).LC_SHOP_STAT_BY_WEEKDAYS);

		$days = array(0 => LC_SUNDAY, 1 => LC_MONDAY, 2 => LC_TUESDAY, 3 => LC_WEDNESDAY, 4 => LC_THURSDAY, 5 => LC_FRIDAY, 6 => LC_SATURDAY);

		$r_i_cnt = array();
		for ($i=0; $i < 7; $i++)
		{
			$sum[$i] = "0";
			$avg[$i] = "0";
			$o_cnt[$i] = "0";
			$i_cnt[$i] = "0";
			$r_i_cnt[$i] = "0";
		}

		// k6igepealt p2rime kaubaartiklite koguse
		$this->db_query("SELECT COUNT(order2item.count) AS i_cnt, wd FROM orders LEFT JOIN order2item ON order2item.order_id = orders.id WHERE tm >= $from AND tm <= $to AND shop_id IN ($shopss) GROUP BY wd");
		while ($row = $this->db_next())
		{
			$r_i_cnt[$row["wd"]] = $row["i_cnt"];
		}

		// ja siis muu data
		$this->db_query("SELECT count(orders.id) as o_cnt, SUM(t_price) AS sum, AVG(t_price) AS avg,wd FROM orders WHERE tm >= $from AND tm <= $to AND shop_id IN ($shopss) GROUP BY wd");
		
		while ($row = $this->db_next())
		{
			$m_sum = max($m_sum,$row["sum"]);
			$m_o_cnt = max($m_o_cnt,$row["o_cnt"]);
			$m_i_cnt = max($m_i_cnt,$r_i_cnt[$row["wd"]]);
			$sum[$row["wd"]] = $row["sum"];
			$avg[$row["wd"]] = $row["avg"];
			$o_cnt[$row["wd"]] = $row["o_cnt"];
			$i_cnt[$row["wd"]] = $r_i_cnt[$row["wd"]];
			$t_sum += $row["sum"];
			$t_avg = $row["avg"];
			$t_o_cnt += $row["o_cnt"];
			$t_i_cnt += $r_i_cnt[$row["wd"]];
		}

		for ($i=0; $i < 7; $i++)
		{
			$this->vars(array(
				"wd" => $days[$i],
				"sum" => $sum[$i],
				"avg" => $avg[$i],
				"o_cnt" => $o_cnt[$i],
				"i_cnt" => $i_cnt[$i]
			));
			$this->parse("WD");
		}

		if ($show_to)
		{
			$chart = $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",array("Puhapaev","Esmaspaev","Teisipaev","Kolmapaev","Neljapaev","Reede","Laupaev"))),
																									 "yvals" => urlencode(join(",",array(0,max($m_sum,max($m_o_cnt,$m_i_cnt))))),
																									 "data"  => urlencode(join(",",map("%s",$sum))),
																									 "data2"  => urlencode(join(",",map("%s",$avg))),
																									 "data3"  => urlencode(join(",",map("%d ",$o_cnt))),
																									 "data4"  => urlencode(join(",",map("%d ",$i_cnt))),
																									 "title" => "Käive nädalapäevade kaupa",
																									 "xtitle" => "Päev",
																									 "ytitle" => "Käive"),"banner");
		}
		else
		{
			$chart = $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",array("Puhapaev","Esmaspaev","Teisipaev","Kolmapaev","Neljapaev","Reede","Laupaev"))),
																									 "yvals" => urlencode(join(",",array(0,max($m_o_cnt,$m_i_cnt)))),
																									 "data"  => urlencode(join(",",map("%d",$o_cnt))),
																									 "data2"  => urlencode(join(",",map("%d",$i_cnt))),
																									 "title" => "Kogus nädalapäevade kaupa",
																									 "xtitle" => "Päev",
																									 "ytitle" => "Kogus"),"banner");
		}
		$this->vars(array(
			"name" => $sh["name"],
			"from" => $this->time2date($from,3),
			"to" => $this->time2date($to,3),
			"chart" => $chart,
			"t_sum" => $t_sum,
			"t_avg" => $t_avg,
			"t_o_cnt" => $t_o_cnt,
			"t_i_cnt" => $t_i_cnt
		));
		return $this->parse();
	}

	////
	// !turnover stats by hr, for shop $id from $from to $to
	function to_stat_by_hr($id,$from,$to,$cats,$show_to)
	{
		if ($show_to)
		{
			$this->read_template("to_stat_by_hr.tpl");
		}
		else
		{
			$this->read_template("to_stat_by_hr_no_to.tpl");
		}
		$sh = $this->get($id);
		$shopss = join(",",$this->map2("%s",$this->get_shops_for_stat($id)));

		$this->mk_path($sh["parent"],"<a href='".$this->mk_orb("change", array("id" => $id)).LC_SHOP_STAT_BY_HOUR);

		for ($i=0; $i < 24; $i++)
		{
			$sum[$i] = "0";
			$avg[$i] = "0";
			$cnt[$i] = "0";
		}

		$this->db_query("SELECT COUNT(*) AS cnt, SUM(t_price) AS sum, AVG(t_price) AS avg,hr FROM orders WHERE tm >= $from AND tm <= $to AND shop_id IN ($shopss) GROUP BY hr");
		while ($row = $this->db_next())
		{
			$m_sum = max($m_sum,$row["sum"]);
			$m_cnt = max($m_cnt,$row["cnt"]);
			$sum[$row["hr"]] = $row["sum"];
			$avg[$row["hr"]] = $row["avg"];
			$cnt[$row["hr"]] = $row["cnt"];
			$t_sum += $row["sum"];
			$t_avg = $row["avg"];
			$t_cnt += $row["cnt"];
		}

		for ($i=0; $i < 24; $i++)
		{
			$this->vars(array(
				"hr" => $i,
				"sum" => $sum[$i],
				"avg" => $avg[$i],
				"cnt" => $cnt[$i]
			));
			$this->parse("HR");
			$hrs[] = $i;
		}

		if ($show_to)
		{
			$chart = $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$hrs)),
																								 "yvals" => urlencode(join(",",array(0,max($m_sum,$m_cnt)))),
																								 "data"  => urlencode(join(",",map("%s",$sum))),
																								 "data2"  => urlencode(join(",",map("%s",$avg))),
																								 "data3"  => urlencode(join(",",map("%s",$cnt))),
																								 "title" => "Käive tundide kaupa",
																								 "xtitle" => "Tund",
																								 "ytitle" => "Käive"),"banner");
		}
		else
		{
			$chart = $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$hrs)),
																								 "yvals" => urlencode(join(",",array(0,$m_cnt))),
																								 "data"  => urlencode(join(",",map("%d",$cnt))),
																								 "title" => "Kogus tundide kaupa",
																								 "xtitle" => "Tund",
																								 "ytitle" => "Kogus"),"banner");
		}
		$this->vars(array(
			"name" => $sh["name"],
			"from" => $this->time2date($from,3),
			"to" => $this->time2date($to,3),
			"chart" => $chart,
			"t_sum" => $t_sum,
			"t_avg" => $t_avg,
			"t_cnt" => $t_cnt
		));
		return $this->parse();
	}

	function check_environment(&$sys, $fix = false)
	{
		$op_table = array(
			"name" => "shop2shop_stat", 
			"fields" => array(
				"stat_id" => array("name" => "stat_id", "length" => 11, "type" => "int", "flags" => ""),
				"shop_id" => array("name" => "shop_id", "length" => 11, "type" => "int", "flags" => ""),
			)
		);

		$ret = $sys->check_admin_templates("shop", array("shop_stat_add.tpl","show_shop_stat.tpl","to_stat_by_day.tpl","to_stat_by_day_no_to.tpl","to_stat_by_month.tpl","to_stat_by_month_no_to.tpl","to_stat_by_wd.tpl","to_stat_by_wd_no_to.tpl","to_stat_by_hr.tpl","to_stat_by_hr_no_to.tpl"));
		$ret.= $sys->check_orb_defs(array("shop_stat"));
		$ret.= $sys->check_db_tables(array($op_table),$fix);

		return $ret;
	}
}
?>