<?php

class contents extends aw_template
{
	function contents()
	{
		$this->init("contents");
	}

	function show($arr)
	{
		extract($arr);
		$this->read_template("show.tpl");

		$this->mned = get_instance("contentmgmt/site_content");
		$this->mned->make_menu_caches();

		$this->mc = get_instance("menu_cache");
		$this->doc = get_instance("document");
		
		$this->per = get_instance("period");

		$this->period = aw_global_get("act_per_id");
		
		$this->count = 0;
		$this->max_count = $arr["max"];

		$this->count = 0;
		$this->max_count = $arr["max"];

		// this will not work if there are different menu areas for 
		// different languages .e.g menuedit.menu_defs[1][YLEMINE] = 66
		// menuedit.menu_defs[2][YLEMINE] = 88

		// there are not many sites that use this feature .. actually
		// I can't remember even one that uses it .. but I'm sure there
 		// is one.
		$mareas = aw_ini_get("menuedit.menu_defs");
	
		$morder = aw_ini_get("contents.menu_order");

		if (is_array($morder))
		{
			//foreach($mareas as $pid => $an)
			foreach($morder as $order => $mname)
			{
				// now find the id from the menu_defs 
				$pid = array_search($mname, $mareas);
				$this->req_menus($pid);
			}
		}
		else
		{
			foreach($mareas as $pid => $an)
			{
				$this->req_menus($pid);
			}
		}

		$ld = $this->get_cval("contents::document");
		if ($ld)
		{
				$this->doc->db_query("SELECT documents.lead AS lead,
			documents.docid AS docid,
			documents.title AS title,
			documents.*,
			objects.period AS period,
			objects.class_id as class_id,
			objects.parent as parent,
			objects.period AS period
			FROM documents
			LEFT JOIN objects ON
			(documents.docid = objects.brother_of)
			WHERE objects.period = '".$this->period."' AND objects.parent = '$ld' AND objects.status = 2 ORDER BY objects.jrk");
			$row = $this->doc->db_next();
			$this->doc->save_handle();
			$this->vars(array(
				"last_doc" => $this->doc->gen_preview(array(
							"docid" => $row["docid"],
							"tpl" => "lead.tpl",
							"doc" => $row,
							"leadonly" => 1
						))
			));
			$this->doc->restore_handle();
		}
		
		$act_per = $this->per->get($this->period);

		if (!empty($act_per["data"]["image"]))
		{
			$img = get_instance("image");
			$dat = $img->get_image_by_id($act_per["data"]["image"]);
			$imgurl = $dat["url"];
		}
		else
		{
			$imgurl = $this->cfg["baseurl"] . "/automatweb/images/trans.gif";
		};

                $this->vars(array(
                        "act_per_comment" => $act_per["comment"],
                        "act_per_name" => $act_per["name"],
			"act_per_image_url" => $imgurl, 
                        "MENU" => $this->l,
                ));

		return $this->parse();
	}

	function req_menus($pid)
	{
		$menus = $this->mc->get_cached_menu_by_parent($pid);
		foreach($menus as $mid => $md)
		{
			if ($this->mned->has_sub_dox($md["oid"]))
			{
				$s = "";

				// now docs under the menu
//				$this->doc->list_docs($md["oid"], $this->period, 2);
				$this->doc->db_query("SELECT documents.lead AS lead,
			documents.docid AS docid,
			documents.title AS title,
			documents.*,
			objects.period AS period,
			objects.class_id as class_id,
			objects.parent as parent,
			objects.period AS period
			FROM documents
			LEFT JOIN objects ON
			(documents.docid = objects.brother_of)
			WHERE objects.period = '".$this->period."' AND objects.parent = '$md[oid]' AND objects.status = 2 ORDER BY objects.jrk");
				while ($row = $this->doc->db_next())
				{
					$this->doc->save_handle();
					$this->vars(array(
						"doc" => $this->doc->gen_preview(array(
							"docid" => $row["docid"],
							"doc" => $row,
							"tpl" => "lead.tpl",
							"leadonly" => 1
						))
					));
					$s.=$this->parse("STORY");
					$this->doc->restore_handle();
				}
				$this->vars(array(
					"menu_name" => $md["name"],
					"menu_link" => $this->mned->make_menu_link($md),
					"STORY" => $s
				));
				$this->count++;
				if (($this->max_count > 0 ) && ($this->count > $this->max_count))
				{
					return;
				}
				$this->l.=$this->parse("MENU");
			}
			$this->req_menus($md["oid"]);
		}
	}

	function admin($arr)
	{
		extract($arr);
		$this->read_template("admin.tpl");

		$ld = $this->get_cval("contents::document");

		$ob = get_instance("objects");
		$this->vars(array(
			"menus" => $this->picker($ld, $ob->get_list(false,true)),
			"reforb" => $this->mk_reforb("submit_admin")
		));

		return $this->parse();
	}

	function submit_admin($arr)
	{
		extract($arr);

		$cf = get_instance("config");
		$cf->set_simple_config("contents::document", $menu);

		return $this->mk_my_orb("admin");
	}
}
?>
