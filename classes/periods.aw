<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/periods.aw,v 2.29 2003/05/12 19:42:00 kristo Exp $
// this is here so that orb will work...
class periods extends aw_template
{
	function periods($oid = 0)
	{
		if (!$oid)
		{
			$oid = $this->cfg["per_oid"];
		}
		$this->init("automatweb/periods");
		lc_load("definition");
		$this->oid = $this->cfg["per_oid"];
		$this->lc_load("periods","lc_periods");	
		$this->cf_name = "periods::cache::site_id::".$this->cfg["site_id"]."::period::";
		$this->cf_ap_name = "active_period::cache::site_id::".$this->cfg["site_id"];
		$this->cache = get_instance("cache");
		$this->init_active_period_cache();
	}
	
	function init_active_period_cache()
	{
		if (($cc = $this->cache->file_get($this->cf_ap_name)))
		{
			aw_cache_set_array("active_period",aw_unserialize($cc));
		}
	}

	function mk_percache()
	{
		if (!aw_global_get("aw_period_cache"))
		{
			$this->db_query("SELECT * FROM periods");
			while ($row = $this->db_next())
			{
				$this->period_cache[$row["oid"]][] = $row;
			}
			aw_global_set("aw_period_cache",1);
		}
	}

	function clist($arc_only = -1) 
	{
		// oh, come on. this is like bad and stuff. what if no period is active? we must still be able to add
		// periods. ok, I'm rewriting this. - terryf

		$this->mk_percache();

		// read all periods from db and then compare the oids to the ones in the object chain for $oid
		$oid = $this->oid;
		$sufix = ($arc_only > -1) ? " AND archived = 1 " : "";
		$ochain = $this->get_object_chain($this->oid);
		$valid_period = 0;
		if (is_array($ochain)) 
		{
			// hm, but we must make sure we go from bottom to top always
			$parent = $this->oid;
			while ($parent > 1)
			{
				// now, if some periods exist for this object, use that object. 
				if (is_array($this->period_cache[$parent]))
				{
					$valid_period = $parent;
					break;
				}

				$parent = $ochain[$parent]["parent"];
			}
		}

		if (!$valid_period)
		{
			$valid_period = $this->oid;
		}
		// if no periods were found, attach them to the object - this happens if no periods exist for instance
		$q = "SELECT * FROM periods	WHERE oid = '$valid_period' $sufix ORDER BY jrk DESC";
		$this->oid = $valid_period;
		$this->db_query($q);
	}

	function activate_period($id,$oid) 
	{
		$q = "UPDATE menu SET active_period = '$id' WHERE id = '$oid'";
		$this->db_query($q);
		$this->flush_cache();
		$this->cache->file_invalidate($this->cf_ap_name);
	}
	
	// see funktsioon tagastab k�igi eksisteerivate perioodide nimekirja
	// array kujul
	// $active muutujaga saab ette anda selle, milline periood peaks olema aktiivne
	// kui $active == 0, siis on selected see option, mis parajasti aktiivne on
	// kui $active == 'somethingelse', siis on selectitud vastava id-ga element
	function period_list($active, $addempty = false)
	{
		if ($active == 0)
		{
			$active = $this->get_cval("activeperiod");
		};
		$this->active = $active;
		$this->clist();
		if ($addempty)
		{
			$elements = array("0" => "");
		}
		else
		{
			$elements = array();
		}
		while($row = $this->db_next())
		{
			$elements[$row["id"]] = $row["description"];
		};
		return $elements;
	}
	
	function period_olist($active = 0) 
	{
		return $this->picker($this->active,$this->period_list($active));
	}

	function period_mlist($active)
	{
		return $this->mpicker($this->active,$this->period_list($active));
	}

	function get_active_period($oid = 0) 
	{
		if (!$oid) 
		{
			$oid = $this->oid;
		};
		// ok, here we have problem - $ap could very well be empty and then we will think
		// that it is not in the cache.
		// so, to fix that we rewrite 0 to -1 :)
		// ok, basically when we would normally add a 0 to the cache, now we add -1 
		// and when retrieving it, we act accordingly
		if (($ap = aw_cache_get("active_period", $oid)))
		{
			return ($ap == -1 ? 0 : $ap);
		}
		else
		{
			// the good bit about this is, that active_period is set only through this class, so we can 
			// contain the cache flushing pretty well
			$q = "SELECT active_period FROM menu WHERE id = '".$oid."'";
			$ap = $this->db_fetch_field($q,"active_period");
			if (!$ap)
			{
				$ap = $this->rec_get_active_period(($oid ? $oid : -1));
			}

			// now add this period to the cache
			aw_cache_set("active_period", $oid,($ap == 0 ? -1 : $ap));

			// and also to the file-on-disk cache
			$str = aw_serialize(aw_cache_get_array("active_period"));
			$this->cache->file_set($this->cf_ap_name,$str);
			return $ap;
		}
	}

	// ee, v6ib ju nii olla, et sellel sektsioonil pole aktiivset perioodi m22ratud, aga tema parentil on, niiet tuleb see otsida...
	function rec_get_active_period($oid = -1) 
	{
		$oid = $oid == -1 ? $this->oid : $oid;
		do {
			$q = "SELECT menu.active_period as active_period,objects.parent as parent FROM menu left join objects on objects.oid = menu.id WHERE id = '"  . $oid."'";
			$this->db_query($q);
			$row = $this->db_fetch_row();
			$oid = $row["parent"];
		} while (!$row["active_period"] && $row["parent"] > 1);

		return $row["active_period"];
	}

	////
	// !returns period $id
	function get($id) 
	{
		// 1st, the in-memory cache
		if (($pr = aw_cache_get("per_by_id", $id)))
		{
			dbg::p1("period::get cache hit level 1");
			return $pr;
		}
		// 2nd, the file-on-disk cache
		if (($cc = $this->cache->file_get($this->cf_name.$id)))
		{
			$pr = aw_unserialize($cc);
			aw_cache_set("per_by_id", $id, $pr);
			dbg::p1("period::get cache hit level 2");
			return $pr;
		}
		// and finally, the db
		dbg::p1("period::get no hit ");
		$q = "SELECT * FROM periods WHERE id = '$id'";
		$this->db_query($q);
		$pr = $this->db_fetch_row();
		$pr["data"] = aw_unserialize($pr["data"]);

		$str = aw_serialize($pr);
		$this->cache->file_set($this->cf_name.$id, $str);
		aw_cache_set("per_by_id", $id, $pr);
		return $pr;
	}

	function request_startup()
	{
		// check if a period number was specified in the url
		$period = aw_global_get("period");
		if ($period) 
		{
			// if it was, we should switch 
			$act_per_id = $period;
			aw_session_set("act_per_id", $act_per_id);

			// now we check if the newly selected period is the active period - 
			// yes, this will take a query, but this will not be done often, only when the user switches periods
			$r_act_per = $this->get_active_period();

			if ($r_act_per = $act_per_id)
			{
				$in_archive = false;
			}
			else
			{
				$in_archive = true;
			}
			aw_session_set("in_archive", $in_archive);
		} 
		else 
		{
			// no period specified in the url
			if (!aw_global_get("act_per_id"))
			{
				// and no period was previously active, pick the default. 
				$act_per_id = $this->get_active_period();
				aw_session_set("act_per_id", $act_per_id);
				$in_archive = false;
				aw_session_set("in_archive", $in_archive);
			}
			// if a period was previously active we just leave it like that
		};

		if (($ap = aw_global_get("act_per_id")))
		{
			// and if after all this we have managed to figure out the active period we go and spoil it all by loading it
			aw_global_set("act_period",$this->get($ap));
		}
	}

	function site_list($arr)
	{
		$this->read_template("arhiiv.tpl");
		$this->clist(1);
		$pyear = 0;
		while($row = $this->db_next()) 
		{
			$dat = aw_unserialize($row["data"]);
			if ($pyear != $dat["pyear"])
			{
				$this->vars(array(
					"pyear" => $dat["pyear"],
				));
				
				$content .= $this->parse("year");
				$pyear = $dat["pyear"];
			};
			$this->vars(array(
				"period" => $row["id"],
				"description" => $row["description"]
			));
			if ($row["id"] == aw_global_get("act_per_id")) 
			{
				$content .= $this->parse("active");
			} 
			else 
			{
				$content .= $this->parse("passive");
			};
		};
		return $content;
	}

	function list_periods($args = array())
	{
		$this->clist();
		$retval = array();
		while($row = $this->db_next())
		{
			$row["data"] = aw_unserialize($row["data"]);
			$retval[] = $row;
		};
		return $retval;
	}
};
?>
