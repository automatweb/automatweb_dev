<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/banner_client.aw,v 2.1 2001/05/16 03:03:48 duke Exp $

global $orb_defs;
$orb_defs["banner_client"] = "xml";

classload("banner");

class banner_client extends banner
{
	function banner_client()
	{
		$this->banner();
		$this->def_html = "<a href='".$GLOBALS["baseurl"]."/banner.".$GLOBALS["ext"]."?gid=%s&click=1&ss=[ss]'><img src='".$GLOBALS["baseurl"]."/banner.".$GLOBALS["ext"]."?gid=%s&ss=[ss]' border=0></a>";
	}

	////
	// !generates the form for adding a client
	function add($arr)
	{
		extract($arr);
		$this->read_template("add_client.tpl");
		$this->mk_path($parent,"Lisa asukoht");
		$this->vars(array("reforb" => $this->mk_reforb("submit", array("parent" => $parent))));
		return $this->parse();
	}

	////
	// !generates the form for changing a client
	function change($arr)
	{
		extract($arr);
		$cl = $this->get($id);
		$this->read_template("add_client.tpl");
		$this->mk_path($cl["parent"],"Muuda asukohta");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"name"	=> $cl["name"],
			"comment" => $cl["comment"],
			"id" => $id,
			"html" => $this->mk_orb("ch_html",array("id" => $id)),
			"stats" => $this->mk_orb("client_stats", array("client" => $id))
		));
		$this->vars(array("CHANGE" => $this->parse("CHANGE")));
		return $this->parse();
	}

	////
	// !saves changes or adds a client
	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name,"comment" => $comment));
			$this->db_query("UPDATE banner_clients SET name='$name' WHERE id = $id");
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_BANNER_CLIENT, "name" => $name,"comment" => $comment));
			$str = sprintf($this->def_html,$id,$id);
			$this->quote(&$str);
			$this->db_query("INSERT INTO banner_clients(id,name,html) values($id,'$name','$str')");
		}

		return $this->mk_orb("change", array("id" => $id));
	}

	function get($id)
	{
		$this->db_query("SELECT objects.*,banner_clients.* FROM objects LEFT JOIN banner_clients ON objects.oid = banner_clients.id WHERE objects.oid = $id");
		return $this->db_next();
	}

	////
	// !allows the user to change the html that is used to show baners.
	function ch_html($arr)
	{
		extract($arr);
		$cl = $this->get($id);
		$this->read_template("ch_html.tpl");
		$this->mk_path($cl["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda asukohta</a> / Muuda htmli");

		$this->vars(array(
			"html"	=> $cl["html"],
			"id" => $id,
			"reforb" => $this->mk_reforb("submit_html",array("id" => $id))
		));
		return $this->parse();
	}

	////
	// !saves the html for banner $id
	function submit_html($arr)
	{
		extract($arr);
		$this->db_query("UPDATE banner_clients SET html = '$html' WHERE id = $id");
		return $this->mk_orb("ch_html", array("id" => $id));
	}

	////
	// !generates a list of banners for client with stats
	function client_stats($arr)
	{
		extract($arr);
		$this->read_template("client_stats.tpl");

		$ar = $this->get_clientarr();
		$co = $this->get_object($client);
		$this->mk_path($co["parent"],"Kliendi ".$ar[$client]." - statistika");

		$bs = array();
		$this->db_query("SELECT * FROM banner2client WHERE clid = $client");
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
			$this->db_query("SELECT COUNT(*) AS cnt,bid FROM banner_views WHERE bid IN($bstr) AND clid = $client GROUP BY bid");
			while ($row = $this->db_next())
			{
				$views[$row["bid"]] = $row["cnt"];
			}

			$clics = array();
			$this->db_query("SELECT COUNT(*) AS cnt,bid FROM banner_clicks WHERE bid IN($bstr) AND clid = $client GROUP BY bid");
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
					"detail"	=> $this->mk_orb("showstats", array("id" => $row["oid"]))
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
			"add" => $this->mk_orb("new", array("client" => $client)),
			"views"	=> $t_vi,
			"clics" => $t_cl,
			"ctr" => $t_ctr,
			"cl_stats_by_day" => $this->mk_orb("client_stats_by_day", array("client" => $client),"banner_client"),
			"cl_stats_by_dow" => $this->mk_orb("client_stats_by_dow", array("client" => $client),"banner_client"),
			"cl_stats_by_hr" => $this->mk_orb("client_stats_by_hr", array("client" => $client),"banner_client"),
			"cl_stats_by_profile" => $this->mk_orb("stat_by_profile", array("client" => $client),"banner_client"),
			"cl_stats_by_buser" => $this->mk_orb("client_stats_by_buser", array("client" => $client),"banner_client")
		));
		return $this->parse();
	}

	////
	// !shows stats for client $client on a day-to-day basis
	function client_stats_by_day($arr)
	{
		extract($arr);
		$this->read_template("client_stat_by_day.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("change", array("id" => $client))."'>Muuda asukohta</a> / <a href='".$this->mk_orb("client_stats", array("client" => $client))."'>Asukoha statistika</a> / P&auml;evade kaupa");

		$clb = array();
		$this->db_query("SELECT bid FROM banner2client LEFT JOIN objects ON objects.oid = banner2client.bid WHERE objects.status != 0 AND  clid = '$client'");
		while ($row = $this->db_next())
		{
			$clb[] = $row["bid"];
		}
		$this->do_stats_by_day($clb);
		return $this->parse();
	}

	////
	// !shows stats for client $client for days of the week.
	function client_stats_by_dow($arr)
	{
		extract($arr);
		$this->read_template("client_stat_by_dow.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("change", array("id" => $client))."'>Muuda asukohta</a> / <a href='".$this->mk_orb("client_stats", array("client" => $client))."'>Asukoha statistika</a> / N&auml;dalap&auml;evade kaupa");

		$clb = array();
		$this->db_query("SELECT bid FROM banner2client LEFT JOIN objects ON objects.oid = banner2client.bid WHERE objects.status != 0 AND  clid = '$client'");
		while ($row = $this->db_next())
		{
			$clb[] = $row["bid"];
		}
		$this->do_stats_by_dow($clb);
		return $this->parse();
	}

	////
	// !shows stats for client $client for hours of the day.
	function client_stats_by_hr($arr)
	{
		extract($arr);
		$this->read_template("client_stat_by_hr.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("change", array("id" => $client))."'>Muuda asukohta</a> / <a href='".$this->mk_orb("client_stats", array("client" => $client))."'>Asukoha statistika</a> / Tundide kaupa");

		$clb = array();
		$this->db_query("SELECT bid FROM banner2client LEFT JOIN objects ON objects.oid = banner2client.bid WHERE objects.status != 0 AND  clid = '$client'");
		while ($row = $this->db_next())
		{
			$clb[] = $row["bid"];
		}

		$this->do_stat_by_hr($clb);

		return $this->parse();
	}

	////
	// !profile stats for banner client $client
	function stat_by_profile($arr)
	{
		extract($arr);
		$this->read_template("stat_by_profile.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("change", array("id" => $client))."'>Muuda asukohta</a> / <a href='".$this->mk_orb("client_stats", array("client" => $client))."'>Asukoha statistika</a> / Profiilide kaupa");

		// make list of all the banners for this client
		$clb = array();
		$this->db_query("SELECT bid FROM banner2client LEFT JOIN objects ON objects.oid = banner2client.bid WHERE objects.status != 0 AND clid = '$client'");
		while ($row = $this->db_next())
		{
			$clb[] = $row["bid"];
		}
		$this->do_stat_by_profile($clb);
		return $this->parse();
	}
}
?>
