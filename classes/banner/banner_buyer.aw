<?php
// $Header: /home/cvs/automatweb_dev/classes/banner/Attic/banner_buyer.aw,v 1.1 2002/11/04 00:47:50 kristo Exp $

classload("banner/banner");
class banner_buyer extends banner
{
	function banner_buyer()
	{
		$this->banner();
		lc_load("definition");
		$this->lc_load("banner","lc_banner");
	}

	////
	// !generates form for adding a banner buyer
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_ADD_BANNER_CLIENT);
		$this->read_template("add_buyer.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		return $this->parse();
	}

	////
	// !generates the form for changing a banner buyer $id
	function change($arr)
	{
		extract($arr);
		$bo = $this->get($id);
		$this->mk_path($bo["parent"], LC_CHANGE_BANNER_CLIENT);
		$this->read_template("add_buyer.tpl");
		$this->vars(array(
			"name" => $bo["name"],
			"comment" => $bo["comment"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"stats" => $this->mk_orb("buyer_banner_stats", array("id" => $id))
		));
		$this->parse("CHANGE");
		return $this->parse();
	}

	////
	// !saves the banner buyer info or adds if necessary
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_BANNER_BUYER, "status" => 2, "name" => $name, "comment" => $comment));
		}

		return $this->mk_orb("change", array("id" => $id));
	}

	////
	// !fetches the banner buyer object
	function get($id)
	{
		return $this->get_object($id);
	}

	////
	// !generates statistics for banner buyer $id
	function buyer_banner_stats($arr)
	{
		extract($arr);
		$this->read_template("buyer_stat.tpl");
		$b_id = $id;
		$bo = $this->get_object($id);
		$this->mk_path($bo["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda klienti</a> / Statistika");

		$bid = array();
		$banners = array();
		$bar = $this->get_banners_for_buyer($id);
		$bars = join(",",$bar);
		if ($bars != "")
		{
			$this->db_query("SELECT banners.*,objects.* FROM objects LEFT JOIN banners ON banners.id = objects.oid WHERE objects.class_id = ".CL_BANNER." AND objects.status != 0 AND oid IN ($bars)");
			while ($row = $this->db_next())
			{
				$bid[] = $row["oid"];
				$banners[$row["oid"]] = $row;
			}
		}
		$bids = join(",",$bid);
		if ($bids != "")
		{
			$views = array();
			$this->db_query("SELECT COUNT(*) as cnt, bid FROM banner_views WHERE bid IN ($bids) GROUP BY bid");
			while ($row = $this->db_next())
			{
				$views[$row["bid"]] = $row["cnt"];
				$t_views+=$row["cnt"];
			}

			$this->db_query("SELECT COUNT(*) as cnt, bid FROM banner_clicks WHERE bid IN ($bids) GROUP BY bid");
			while ($row = $this->db_next())
			{
				$clicks[$row["bid"]] = $row["cnt"];
				$t_clicks+=$row["cnt"];
			}

			reset($banners);
			while (list($id,$row) = each($banners))
			{
				$this->vars(array(
					"id" => $row["oid"],
					"name" => $row["name"], 
					"img" => $this->get_noview_url($row["oid"]),
					"views" => $views[$row["oid"]],
					"clicks" => $clicks[$row["oid"]]
				));
				$this->parse("LINE");
			}
		}
		$this->vars(array(
			"add" => $this->mk_orb("add_site",array()),
			"reforb" => $this->mk_reforb("submit_status_site", array()),
			"t_views" => $t_views,
			"t_clicks" => $t_clicks,
			"stat_by_day" => $this->mk_orb("stat_by_day", array("id" => $b_id)),
			"stat_by_dow" => $this->mk_orb("stat_by_dow", array("id" => $b_id)),
			"stat_by_hr" => $this->mk_orb("stat_by_hr", array("id" => $b_id)),
			"stat_by_profile" => $this->mk_orb("stat_by_profile", array("id" => $b_id))
		));

		return $this->parse();
	}

	////
	// !shows stats for bannerbuyer $id on a day-to-day basis
	function stat_by_day($arr)
	{
		extract($arr);
		$this->read_template("client_stat_by_day.tpl");
		$bo = $this->get_object($id);
		$this->mk_path($bo["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda klienti</a> / <a href='".$this->mk_orb("buyer_banner_stats", array("id" => $id))."'>Statistika</a> / P&auml;evade kaupa");

		$this->do_stats_by_day($this->get_banners_for_buyer($id));
		return $this->parse();
	}

	////
	// !shows stats for banner buyer $id for days of the week.
	function stat_by_dow($arr)
	{
		extract($arr);
		$this->read_template("client_stat_by_dow.tpl");
		$bo = $this->get_object($id);
		$this->mk_path($bo["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>LC_CHANGE_CLIENT</a> / <a href='".$this->mk_orb("buyer_banner_stats", array("id" => $id))."'>Statistika</a> / N&auml;dalap&auml;evade kaupa");

		$this->do_stats_by_dow($this->get_banners_for_buyer($id));
		return $this->parse();
	}

	////
	// !shows stats for banner buyer $id for hours of the day.
	function stat_by_hr($arr)
	{
		extract($arr);
		$this->read_template("client_stat_by_hr.tpl");
		$bo = $this->get_object($id);
		$this->mk_path($bo["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda klienti</a> / <a href='".$this->mk_orb("buyer_banner_stats", array("id" => $id))."'>Statistika</a> / Tundide kaupa");

		$this->do_stat_by_hr($this->get_banners_for_buyer($id));

		return $this->parse();
	}

	////
	// !profile stats for  banner buyer $id
	function stat_by_profile($arr)
	{
		extract($arr);
		$this->read_template("stat_by_profile.tpl");
		$bo = $this->get_object($id);
		$this->mk_path($bo["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda klienti</a> / <a href='".$this->mk_orb("buyer_banner_stats", array("id" => $id))."'>Statistika</a> / Profiilide kaupa");

		$this->do_stat_by_profile($this->get_banners_for_buyer($id));
		return $this->parse();
	}

	////
	// !lets the user select a banner buyer and redirects to the correct url or if only one banner buyer is visible redirect immediately
	function sel_buyer_redirect($arr)
	{
		extract($arr);

		$bar = array();
		$bcnt = 0;
		$this->listacl("class_id = ".CL_BANNER_BUYER." AND status != 0");
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_BANNER_BUYER." AND status != 0");
		while ($row = $this->db_next())
		{
			if ($this->can("edit", $row["oid"]))
			{
				$bar[$row["oid"]] = $row["name"];
				$bcnt++;
			}
		}

		if ($bcnt < 1)
		{
			$this->read_template("no_buyers.tpl");
			return $this->parse();
		}
		else
		if ($bcnt == 1)
		{	
			// redirect.
			reset($bar);
			list($id,) = each($bar);
			header("Location: ".$this->mk_orb($fun,array("id" => $id),$r_class));
			die();
		}
		else
		{
			// let the user pick
			$this->read_template("sel_buyer.tpl");
			$this->vars(array(
				"fun" => $fun,
				"r_class" => $r_class,
				"buyers" => $this->picker(0,$bar)
			));
			return $this->parse();
		}
	}
}

?>
