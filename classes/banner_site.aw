<?php

global $orb_defs;
$orb_defs["banner_site"] = "xml";

classload("banner");

class banner_site extends banner
{
	function banner_site()
	{
		$this->banner();
	}

	////
	// !generates form for adding a banner site
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,"Lisa banneri sait");
		$this->read_template("add_b_site.tpl");

		$ca = $this->get_clientarr();
		$this->vars(array(
			"locations" => $this->multiple_option_list(array(),$ca),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		return $this->parse();
	}

	////
	// !generates the form for changing banner site $id
	function change($arr)
	{
		extract($arr);
		$bo = $this->get($id);
		$this->mk_path($bo["parent"], "Muuda banneri saiti");
		$this->read_template("add_b_site.tpl");
		$ca = $this->get_clientarr();
		$ca_sel = $this->get_clients_for_site($id);
		$this->vars(array(
			"name" => $bo["name"],
			"comment" => $bo["comment"],
			"locations" => $this->multiple_option_list($ca_sel,$ca),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"stats" => $this->mk_orb("stats", array("id" => $id))
		));
		$this->parse("CHANGE");
		return $this->parse();
	}

	////
	// !saves the banner site info or adds if necessary
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_BANNER_SITE, "status" => 2, "name" => $name, "comment" => $comment));
		}

		$this->db_query("DELETE FROM site2location WHERE site_id = $id");
		if (is_array($locations))
		{
			reset($locations);
			while (list(,$lid) = each($locations))
			{
				$this->db_query("INSERT INTO site2location(site_id,location_id) VALUES($id,$lid)");
			}
		}
		return $this->mk_orb("change", array("id" => $id));
	}

	////
	// !fetches the banner site object
	function get($id)
	{
		return $this->get_object($id);
	}

	////
	// !returns an array of client id's for this site.
	function get_clients_for_site($id)
	{
		$ca_sel = array();
		$this->db_query("SELECT * FROM site2location WHERE site_id = $id");
		while ($row = $this->db_next())
		{
			$ca_sel[$row["location_id"]] = $row["location_id"];

		}
		return $ca_sel;
	}

	////
	// !generates a list of banners for site with stats
	function stats($arr)
	{
		extract($arr);
		$this->read_template("client_stats.tpl");

		$ar = $this->get_clientarr();
		$co = $this->get_object($id);
		$this->mk_path($co["parent"],"Saidi \"".$co["name"]."\" statistika");

		$s_c_ar = $this->get_clients_for_site($id);
		$s_c_str = join(",",$s_c_ar);
		if ($s_c_str == "")
		{
			return $this->parse();
		}

		$bs = array();
		$this->db_query("SELECT DISTINCT(bid) AS bid FROM banner2client WHERE clid IN ($s_c_str)");
		while ($d = $this->db_next())
		{
			$bs[] = $d["bid"];
		}
		$bs = join(",",$bs);
		$bar = array();
		$bans = array();
		if ($bs != "")
		{
			$this->db_query("SELECT objects.*,banners.url as url FROM objects LEFT JOIN banners ON banners.id = objects.oid WHERE class_id = ".CL_BANNER." AND oid IN($bs) AND status != 0");
			while ($row = $this->db_next())
			{
				$bar[] = $row["oid"];
				$bans[] = $row;
			}
		}
		
		$bstr = join(",",$bar);
		if ($bstr != "")
		{
			$views = array();
			$this->db_query("SELECT COUNT(*) AS cnt,bid FROM banner_views WHERE bid IN($bstr) AND clid IN ($s_c_str) GROUP BY bid");
			while ($row = $this->db_next())
			{
				$views[$row["bid"]] = $row["cnt"];
			}

			$clics = array();
			$this->db_query("SELECT COUNT(*) AS cnt,bid FROM banner_clicks WHERE bid IN($bstr) AND clid IN ($s_c_str) GROUP BY bid");
			while ($row = $this->db_next())
			{
				$clics[$row["bid"]] = $row["cnt"];
			}

			reset($bans);
			while (list(,$row) = each($bans))
			{
				$vi = $views[$row["oid"]];
				$cl = $clics[$row["oid"]];
				$t_vi+=$vi;
				$t_cl+=$cl;
				$this->vars(array(
					"img" => "<img src='".$GLOBALS["baseurl"]."/banner.".$GLOBALS["ext"]."?bid=".$row["oid"]."&noview=1&ss=".$this->gen_uniq_id()."'>",
					"views" => $vi,
					"clics" => $cl,
					"ctr"	=> ($vi ? ((double)$cl/(double)$vi)*100.0 : 0),
					"detail"	=> $this->mk_orb("showstats", array("id" => $row["oid"]),"banner")
				));
				$this->parse("LINE");
			}
		}

		$t_ctr = 0;
		if ($t_vi)
		{
			$t_ctr = ((double)$t_cl / (double)$t_vi)*100.0;
		}

		$this->vars(array(
			"add" => $this->mk_orb("new", array()),
			"views"	=> $t_vi,
			"clics" => $t_cl,
			"ctr" => $t_ctr,
			"cl_stats_by_day" => $this->mk_orb("site_stats_by_day", array("id" => $id)),
			"cl_stats_by_dow" => $this->mk_orb("site_stats_by_dow", array("id" => $id)),
			"cl_stats_by_hr" => $this->mk_orb("site_stats_by_hr", array("id" => $id)),
			"cl_stats_by_profile" => $this->mk_orb("stat_by_profile", array("id" => $id)),
			"cl_stats_by_buser" => $this->mk_orb("site_stats_by_buser", array("id" => $id))
		));
		return $this->parse();
	}

	////
	// !shows stats for site $id on a day-to-day basis
	function site_stats_by_day($arr)
	{
		extract($arr);
		$this->read_template("client_stat_by_day.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda saiti</a> / <a href='".$this->mk_orb("stats", array("id" => $id))."'>Saidi statistika</a> / P&auml;evade kaupa");

		$s_c_ar = $this->get_clients_for_site($id);
		$s_c_str = join(",",$s_c_ar);
		if ($s_c_str == "")
		{
			return $this->parse();
		}

		$clb = array();
		$this->db_query("SELECT DISTINCT(bid) AS bid FROM banner2client LEFT JOIN objects ON objects.oid = banner2client.bid WHERE objects.status != 0 AND clid IN ($s_c_str)");
		while ($row = $this->db_next())
		{
			$clb[] = $row["bid"];
		}
		$this->do_stats_by_day($clb);
		return $this->parse();
	}

	////
	// !shows stats for site $id for days of the week.
	function site_stats_by_dow($arr)
	{
		extract($arr);
		$this->read_template("client_stat_by_dow.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda saiti</a> / <a href='".$this->mk_orb("stats", array("id" => $id))."'>Saidi statistika</a> / N&auml;dalap&auml;evade kaupa");

		$s_c_ar = $this->get_clients_for_site($id);
		$s_c_str = join(",",$s_c_ar);
		if ($s_c_str == "")
		{
			return $this->parse();
		}

		$clb = array();
		$this->db_query("SELECT DISTINCT(bid) AS bid FROM banner2client LEFT JOIN objects ON objects.oid = banner2client.bid WHERE objects.status != 0 AND  clid IN ($s_c_str)");
		while ($row = $this->db_next())
		{
			$clb[] = $row["bid"];
		}
		$this->do_stats_by_dow($clb);
		return $this->parse();
	}

	////
	// !shows stats for site $id for hours of the day.
	function site_stats_by_hr($arr)
	{
		extract($arr);
		$this->read_template("client_stat_by_hr.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda saiti</a> / <a href='".$this->mk_orb("stats", array("id" => $id))."'>Saidi statistika</a> / Tundide kaupa");

		$s_c_ar = $this->get_clients_for_site($id);
		$s_c_str = join(",",$s_c_ar);
		if ($s_c_str == "")
		{
			return $this->parse();
		}

		$clb = array();
		$this->db_query("SELECT DISTINCT(bid) AS bid FROM banner2client LEFT JOIN objects ON objects.oid = banner2client.bid WHERE objects.status != 0 AND clid IN ($s_c_str)");
		while ($row = $this->db_next())
		{
			$clb[] = $row["bid"];
		}
		$this->do_stat_by_hr($clb);
		return $this->parse();
	}

	////
	// !profile stats for banner site $id
	function stat_by_profile($arr)
	{
		extract($arr);
		$this->read_template("stat_by_profile.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda saiti</a> / <a href='".$this->mk_orb("stats", array("id" => $id))."'>Saidi statistika</a> / Profiilide kaupa");

		$s_c_ar = $this->get_clients_for_site($id);
		$s_c_str = join(",",$s_c_ar);
		if ($s_c_str == "")
		{
			return $this->parse();
		}

		// make list of all the banners for this client
		$clb = array();
		$this->db_query("SELECT DISTINCT(bid) AS bid FROM banner2client LEFT JOIN objects ON objects.oid = banner2client.bid WHERE objects.status != 0 AND clid IN ($s_c_str)");
		while ($row = $this->db_next())
		{
			$clb[] = $row["bid"];
		}
		$this->do_stat_by_profile($clb);
		return $this->parse();
	}
}

?>