<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/banner/banner.aw,v 1.6 2005/03/18 12:02:30 ahti Exp $

/*

@tableinfo banners index=id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_BANNER relationmgr=yes
@default table=objects
@default group=general

@property url type=textbox table=banners 
@caption URL, kuhu klikkimisel suunatakse

@property banner_file type=relpicker reltype=RELTYPE_BANNER_FILE table=banners
@caption Banneri sisu

@property banner_file_2 type=relpicker reltype=RELTYPE_BANNER_FILE table=banners
@caption Banneri sisu lisaks

@property html type=textarea rows=5 cols=30 table=banners
@caption Banneri html

@groupinfo display caption="N&auml;itamine"

@property probability_tbl type=table group=display 
@caption N&auml;itamise t&otilde;en&auml;osus

@property probability type=textbox display=none table=banners

@property max_views type=textbox size=3 table=banners  group=display
@caption Mitu korda bannerit maksimaalselt n&auml;idata

@property max_clicks type=textbox size=3 table=banners  group=display
@caption Mitu klikki maksimaalselt

@groupinfo stats caption="Statistika"

@property clicks type=text group=stats
@caption Klikke

@property views type=text group=stats
@caption Vaatamisi

@property click_through type=text group=stats
@caption Click-through ratio


@reltype LOCATION value=1 clid=CL_BANNER_CLIENT
@caption banneri asukoht

@reltype BANNER_FILE value=2 clid=CL_IMAGE,CL_FILE,CL_FLASH,CL_EXTLINK,CL_DOCUMENT
@caption banneri sisu

*/

class banner extends class_base
{
	function banner()
	{
		$this->init(array(
			"tpldir" => "banner",
			"clid" => CL_BANNER
		));
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "clicks":
				$this->_init_stats($arr["obj_inst"]);
				$prop["value"] = (int)$this->clicks;
				break;

			case "views":
				$this->_init_stats($arr["obj_inst"]);
				$prop["value"] = (int)$this->views;
				break;

			case "click_through":
				$this->_init_stats($arr["obj_inst"]);
				$prop["value"] = $this->click_through." %";
				break;

			case "probability_tbl":
				$this->do_prob_tbl($arr);
				break;
		}

		return PROP_OK;
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];

		switch($prop["name"])
		{
			case "probability_tbl":
				$this->do_save_prob_tbl($arr);
				break;
		}
		return PROP_OK;
	}

	function do_save_prob_tbl(&$arr)
	{
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_LOCATION")) as $c)
		{
			$loc = $c->to();
			// banners for that location
			foreach($loc->connections_to(array("from.class_id" => CL_BANNER)) as $c)
			{
				$bann = $c->from();
				$bann->set_prop("probability", $arr["request"]["prob"][$bann->id()]);
				$bann->save();
			}
		}		
	}

	function init_prob_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Banner",
			"width" => "90%"
		));

		$t->define_field(array(
			"name" => "prob",
			"caption" => "N&auml;itamise t&otilde;en&auml;osuse %",
			"align" => "center"
		));
	}

	function do_prob_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_prob_tbl($t);

		// get the location(s) for this banner and then get all banners for that location. stick them into a table 
		// and let the user enter probabilities
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_LOCATION")) as $c)
		{
			$loc = $c->to();
			// banners for that location
			$t->define_data(array(
				"name" => "<b>".$loc->name()."</b>"
			));

			foreach($loc->connections_to(array("from.class_id" => CL_BANNER)) as $c)
			{
				$bann = $c->from();
				$t->define_data(array(
					"name" => "&nbsp;&nbsp;&nbsp;&nbsp;".$bann->name(),
					"prob" => html::textbox(array(
						"name" => "prob[".$bann->id()."]",
						"value" => $bann->prop("probability"),
						"size" => 6
					))
				));
			}
		}
		$t->set_sortable(false);
	}

	function _init_stats($o)
	{
		if (!$this->__inited)
		{
			$this->clicks = $this->db_fetch_field("SELECT count(*) as cnt FROM banner_clicks WHERE bid = '".$o->id()."'", "cnt");
			$this->views = $this->db_fetch_field("SELECT count(*) as cnt FROM banner_views WHERE bid = '".$o->id()."'", "cnt");
			if ($this->views > 0)
			{
				$this->click_through = ($this->clicks / $this->views) * 100.0;
			}
			else
			{
				$this->click_through = 0;
			}
			$this->__inited = true;
		}
	}

	/** randomly selects a banner from the specified group

		@comment
			$gid - banner area id
	**/
	function get_grp($gid)
	{
		$baids = array();
		$cnt = 0;
		$t = time();
		// selektime k6ik bannerid selle kliendi kohta. 
		$g_obj = obj($gid);
		$bbs = array();
		foreach($g_obj->connections_to(array("from.class_id" => CL_BANNER)) as $c)
		{
			$bbs[] = $c->prop("from");
		}
		$bbs = join(",",$bbs);
		if ($bbs == "")
		{
			$this->error_banner();
		}

		$q = "
			SELECT 
				banners.id as id,
				banners.probability as pb 
			FROM banners 
			LEFT JOIN objects ON objects.oid = banners.id
			WHERE 
				banners.id IN ($bbs) 
				AND objects.status = 2 
				AND (clicks <= max_clicks OR (max_clicks is null OR max_clicks = 0)) 
				AND (views <= max_views OR (max_views is null OR max_views = 0)) 
		";
		$this->db_query($q);

		$bans = array();
		$baids = array();
		$found = false;
		while ($row = $this->db_next())
		{
			$bans[] = $row;
			$baids[$cnt++] = $row;
			if ($row["probability"] > 0)
			{
				$found = true;
			}
		}

		if ($cnt > 0)
		{
			srand ((double) microtime() * 10000000);
			if ($found == false)
			{
				$bid = $baids[array_rand($baids)]["id"];
				if (!$bid)
				{
					$this->error_banner();
				}
			}
			else
			{
				// select the banner by it's probability. 
				// so. we pick a number between one and 100
				// and if that number is <= the banners probability, we pick that banner
				$num = 0;
				$found = false;
				while (!$found)
				{
					$r = rand(0,101);
					if ($r <= $baids[$num]["pb"] )
					{
						$bid = $baids[$num]["id"];
						$found = true;
					}
					$num = ($num+1)%$cnt;
				}
			}
			return obj($bid);
		}
		else
		{
			if (!$bid)
			{
				$this->error_banner();
			}
		}
	}

	////
	// !adds a record to the banner_ids table to identify shown banners l8r
	function add_view($bid,$ss,$noview,$clid)
	{
		$this->db_query("INSERT INTO banner_ids(ss,bid,tm,clid) values('$ss','$bid',".time().",'$clid')");
		if (!$noview)
		{
			$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
			if ($ip == "" || !(strpos($ip,"unknown") === false))
			{
				$ip = aw_global_get("REMOTE_ADDR");
			}
			// ok. a prize for anybody (except terryf and duke) who figures out why the next line is like it is :)
			$day = mktime(8,42,17,date("n"),date("d"),date("Y"));
			$this->db_query("INSERT INTO banner_views(tm,bid,ip,clid) VALUES(".time().",$bid,'$ip','$clid')");
			$this->db_query("UPDATE banners SET views=views+1 WHERE id = $bid");
		}
	}

	/** called when user clicks on banner, finds the correct banner according to the session id 

		@comment 
			also cleans the banner_ids table of all views older than 48 hours	
	**/
	function find_url($ss,$clid)
	{
		$bid = $this->db_fetch_field("SELECT bid FROM banner_ids WHERE ss = '$ss' AND clid = '$clid'","bid");
		if (!$bid)
		{
			$this->error_click();
		}
		// no need to do this every time. let's say it has a 1% probability of happening.
		srand ((double) microtime() * 10000000);
		$pb = rand(1,101);
		if ($pb == 69)
		{
			$this->db_query("DELETE FROM banner_ids WHERE tm < ".(time()-(48*3600)));
		}

		$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
		if ($ip == "" || !(strpos($ip,"unknown") === false))
		{
			$ip = aw_global_get("REMOTE_ADDR");
		}

		$this->db_query("INSERT INTO banner_clicks(tm,bid,ip,clid,refferer) VALUES(".time().",$bid,'$ip','$clid','".aw_global_get("HTTP_REFERER")."')");
		$this->db_query("UPDATE banners SET clicks=clicks+1 WHERE id = $bid");

		return obj($bid);
	}

	/** shows banner
		
		@attrib name=proc_banner nologin="1"

		@param id optional
		@param bid optional
		@param gid optional
		@param html optional
		@param ss optional
		@param click optional

	**/
	function proc_banner($arr)
	{
		extract($arr);
		if ($html)
		{
			// tagastame baasi kirjutatud htmli banneri naitamisex.
			if (!$gid)
			{
				die(LC_ERROR_NO_ID);
			}
			die($this->db_fetch_field("SELECT html FROM banner_clients WHERE id = $gid","html"));
		}
		else
		if ($click)
		{
			// redirect
			if ($bid)
			{
				$ba = obj($bid);
				header("Location: ".$ba->prop("url"));
				die();
			}

			if ($gid)
			{
				$ret = $this->find_url($ss,$gid);
				header("Location: ".$ret->prop("url"));
				die();
			}
		}
		else
		{
			// show
			$ba = array();
			if ($bid)
			{
				$ba = $this->get($bid);
				if (!$ba)
				{
					$this->error_banner();
				}
			}

			if ($gid)
			{
				$ba = $this->get_grp($gid);
				if (!is_object($ba))
				{
					$this->error_banner();
				}
				$this->add_view($ba->id(),$ss,$noview,$gid);
			}

			if (!is_object($ba))
			{
				$this->error_banner();
			}

			$this->display_banner($ba);
		}
	}

	////
	// !shows a transparent gif, used if any errors occur while showing banner
	function error_banner()
	{
		header("Content-type: image/gif");
		readfile($this->cfg["baseurl"]."/automatweb/images/trans.gif");
		die();
	}

	////
	// !redirects to the site, used when any errors occur when the user clicks on a banner
	function error_click()
	{
		header("Location: ".$this->cfg["baseurl"]);
		die();
	}

	function display_banner($o)
	{
		if (!$o->prop("banner_file"))
		{
			$this->error_banner();
		}

		$f_o = obj($o->prop("banner_file"));
		if ($f_o->class_id() == CL_IMAGE)
		{
			$i = get_instance("image");
			$r = $i->get_image_by_id($f_o->id());
			$i->show(array(
				"file" => basename($r["file"])
			));
		}
	}
}
?>
