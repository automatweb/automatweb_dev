<?php
// a$Header: /home/cvs/automatweb_dev/classes/Attic/periods.aw,v 2.20 2002/12/20 11:39:43 kristo Exp $

class db_periods extends aw_template 
{
	function db_periods() 
	{
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

	function get_next($id,$oid) 
	{
		$q = "SELECT * FROM periods WHERE oid = '$oid' ORDER BY jrk";
		$this->db_query($q);
		$select = 0;
		while($row = $this->db_next()) 
		{
			if ($select == 1) 
			{
				$next = $row["id"];
				$select = 0;
			};
			if ($row["id"] == $id) 
			{
				$select = 1;
			};
		};
		return $next;
	}
	
	function get_prev($id,$oid) 
	{
		$q = "SELECT * FROM periods WHERE oid = '$oid' ORDER BY jrk DESC";
		$this->db_query($q);
		$select = 0;
		while($row = $this->db_next()) 
		{
			if ($select == 1) 
			{
				$prev = $row["id"];
				$select = 0;
			};
			if ($row["id"] == $id) 
			{
				$select = 1;
			};
		};
		return $prev;
	}

	function add($archived,$description) 
	{
		$aflag = ($archived == "on") ? 1 : 0;
		$t = time();
		$oid = $this->oid;
		$q = "INSERT INTO periods (archived,description,created,oid)
			VALUES('$aflag','$description','$t','$oid')";
		$this->db_query($q);
		return $this->db_last_insert_id();
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

	function savestatus($data) 
	{
		// checkboxid, mis näitavad perioodi arhiveeritust
		$arc_flags = $data["arc"];
		// eelmised väärtused
		$old_arc_flags = $data["oldarc"];

		// salvestame flagid, mis naitavad perioodide arhiveeritust
		while(list($k,$v) = each($old_arc_flags)) 
		{
			// teeme kindlaks, kas staatust on vaja muuta
			$newstatus = ($arc_flags[$k] == "on") ? 1 : 0;
			if ($newstatus != $v) 
			{
				$q = "UPDATE periods SET archived = '$newstatus' WHERE id = '$k'";
				$this->db_query($q);
				// also flush caches
				$this->cache->file_invalidate($this->cf_name.$k);
				aw_cache_set("per_by_id", $k, false);
			};
		};

		if ($data["oldactiveperiod"] != $data["activeperiod"]) 
		{
			$this->activate_period($data["activeperiod"],$this->oid);
			$this->_log(ST_PERIOD,SA_ACTIVATE_PERIOD, sprintf(LC_PERIODS_ACTIVATED_PERIOD,$data["activeperiod"]));
		};

		$oldjrk = $data["oldjrk"];
		$jrk = $data["jrk"];

		// salvestame jarjekorranumbrid
		while(list($k,$v) = each($oldjrk)) 
		{
			$newjrk = $jrk[$k];
			if ($v != $newjrk) 
			{
				$q = "UPDATE periods SET jrk = '$newjrk' WHERE id = '$k'";
				$this->db_query($q);
				$this->cache->file_invalidate($this->cf_name.$k);
				aw_cache_set("per_by_id", $k, false);
			};
		};
	}

	function toggle_arc_flag($id) 
	{
		$old = $this->get($id);
		$new = ($old["archived"] == 1) ? "0" : "1";
		$q = "UPDATE periods SET archived = '$new' WHERE id = '$id'";
		$this->db_query($q);
		$this->cache->file_invalidate($this->cf_name.$id);
		aw_cache_set("per_by_id", $id, false);
	}

	function activate_period($id,$oid) 
	{
		$q = "UPDATE menu SET active_period = '$id' WHERE id = '$oid'";
		$this->db_query($q);
		$this->flush_cache();
		$this->cache->file_invalidate($this->cf_ap_name);
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
			$q = "SELECT menu.active_period as active_period,objects.parent as parent FROM menu left join objects on objects.oid = menu.id WHERE id = "  . $oid;
			$this->db_query($q);
			$row = $this->db_fetch_row();
			$oid = $row["parent"];
		} while (!$row["active_period"] && $row["parent"] > 1);

		return $row["active_period"];
	}


	// see funktsioon tagastab kõigi eksisteerivate perioodide nimekirja
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

	function save($data) 
	{
		extract($data);

		$old = $this->get($id);

		// if image uploaded, save it
		$img = get_instance("image");
		$old["data"]["image"] = $img->add_upload_image("image",0,$old["data"]["image"]["id"]);
		$old["data"]["image_link"] = $image_link;
		$old["data"]["pyear"] = $pyear;

		$dstr = aw_serialize($old["data"]);
		$this->quote($dstr);

		$q = "UPDATE periods
			SET description = '$description',
			    archived = '$archived',
					data = '$dstr'
			WHERE id = '$id'";
		$this->db_query($q);
		$this->cache->file_invalidate($this->cf_name.$id);
		aw_cache_set("per_by_id", $id, false);
	}
};

// this is here so that orb will work...
class periods extends db_periods
{
	function periods($oid = 0)
	{
		if (!$oid)
		{
			$oid = $this->cfg["per_oid"];
		}
		$this->db_periods($oid);
	}

	function admin_list($arr)
	{
		extract($arr);
		$this->read_template("list.tpl");
		$active = $this->rec_get_active_period();
		$this->clist();
		load_vcl("table");
		$table = new aw_table(array(
			"prefix" => "periods",
		));
	
		$table->parse_xml_def($this->cfg["basedir"]."/xml/periods/list.xml");


		while($row = $this->db_next()) 
		{
			$jrk_html = "<input type='text' size='3' maxlength='3' name='jrk[$row[id]]' value='$row[jrk]'><input type='hidden' name='oldjrk[$row[id]]' value='$row[jrk]'>";
			$archived = checked($row["archived"]);
			$arc_html = "<input type='checkbox' name='arc[$row[id]]' $archived><input type='hidden' name='oldarc[$row[id]]' value='$row[archived]'>";
			$actcheck = checked($row["id"] == $active);
			$act_html = "<input type='radio' name='activeperiod' $actcheck value='$row[id]'>";
			$row["jrk"] = $jrk_html;
			$row["archived"] = $arc_html;
			$row["active"] = $act_html;
			$ch_url = $this->mk_my_orb("change",array("id" => $row["id"]));
			$row["change"] = "<a href='$ch_url'>Muuda</a>";
			$table->define_data($row);
		};
		
		$table->sort_by(array("sortby" => $sortby));

		$this->vars(array(
			"add" => $this->mk_my_orb("add"),
			"table" => $table->draw(),
			"reforb" => $this->mk_reforb("savestatus", array("oldactiveperiod" => $active, "oid" => $this->oid))
		));
		return $this->parse();
	}

	function orb_add($arr)
	{
		extract($arr);
		$this->mk_path(0,"<a href='".$this->mk_my_orb("admin_list")."'>Perioodid</a> / Lisa uus");
		$this->read_template("add.tpl");
		$years = array(
			"2000" => "2000",
			"2001" => "2001",
			"2002" => "2002",
			"2003" => "2003",
			"2004" => "2004",
			"2005" => "2005",
		);
		$this->vars(array(
			"pyear" => $this->picker(-1,array("0" => "--vali--") + $years),
			"reforb" => $this->mk_reforb("submit_add", array("oid" => $this->oid))
		));
		return $this->parse();
	}

	function orb_submit_add($arr)
	{
		extract($arr);
		if (!$id)
		{
			$id = $this->add($archived,$description);
		};
		$arr["id"] = $id;
		$this->save($arr);
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function orb_edit($arr)
	{
		extract($arr);
		$this->read_template("edit.tpl");
		$this->mk_path(0,"<a href='".$this->mk_my_orb("admin_list")."'>Perioodid</a> / Muuda");
		$cper = $this->get($id);
		$years = array(
			"2000" => "2000",
			"2001" => "2001",
			"2002" => "2002",
			"2003" => "2003",
			"2004" => "2004",
			"2005" => "2005",
		);
		classload("image");
		$this->vars(array(
			"ID" => $cper["id"],
      "description" => $cper["description"],
			"plist" => $this->period_olist(),
	    "arc" => $this->option_list($cper["archived"],array("0" => "Ei","1" => "Jah")),
			"image" => image::make_img_tag(image::check_url($cper["data"]["image"]["url"])),
			"image_link" => $cper["data"]["image_link"],
			"pyear" => $this->picker($cper["data"]["pyear"],array("0" => "--vali--") + $years),
			"reforb" => $this->mk_reforb("submit_add", array("id" => $id))
		));
		return $this->parse();
	}

	function orb_savestatus($arr)
	{
		extract($arr);
		$this->savestatus($arr);
		return $this->mk_my_orb("admin_list");
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
}

?>
