<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/converters.aw,v 1.6 2003/05/12 18:15:15 duke Exp $
// converters.aw - this is where all kind of converters should live in
class converters extends aw_template
{
	// this will be set to document id if only one document is shown, a document which can be edited
	var $active_doc = false;

	function converters()
	{
		$this->init("");

	}

	function menu_convimages()
	{
		$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu on menu.id = objects.oid WHERE class_id = ".CL_PSEUDO." AND status != 0");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			
			$meta = $this->get_object_metadata(array(
				"metadata" => $row["metadata"]
			));

			$cnt = 0;
			$imgar = array();

			$t = get_instance("image");
			if ($row["img_id"])
			{
				$img = $t->get_img_by_id($row["img_id"]);
				$this->vars(array(
					"image" => "<img src='".$img["url"]."'>",
					"img_ord1" => $meta["img1_ord"]
				));
				$imgar[$cnt]["id"] = $row["img_id"];
				$imgar[$cnt]["url"] = $img["url"];
				$imgar[$cnt]["ord"] = $meta["img1_ord"];
				$cnt++;
			}

			if ($meta["img2_id"])
			{
				$img2 = $t->get_img_by_id($meta["img2_id"]);
				$this->vars(array(
					"image2" => "<img src='".$img2["url"]."'>",
					"img_ord2" => $meta["img2_ord"]
				));
				$imgar[$cnt]["id"] = $meta["img2_id"];
				$imgar[$cnt]["url"] = $img2["url"];
				$imgar[$cnt]["ord"] = $meta["img2_ord"];
				$cnt++;
			}
			if ($meta["img3_id"])
			{
				$img3 = $t->get_img_by_id($meta["img3_id"]);
				$this->vars(array(
					"image3" => "<img src='".$img3["url"]."'>",
					"img_ord3" => $meta["img3_ord"]
				));
				$imgar[$cnt]["id"] = $meta["img3_id"];
				$imgar[$cnt]["url"] = $img3["url"];
				$imgar[$cnt]["ord"] = $meta["img3_ord"];
				$cnt++;
			}
			if ($meta["img4_id"])
			{
				$img4 = $t->get_img_by_id($meta["img4_id"]);
				$this->vars(array(
					"image4" => "<img src='".$img4["url"]."'>",
					"img_ord4" => $meta["img4_ord"]
				));
				$imgar[$cnt]["id"] = $meta["img4_id"];
				$imgar[$cnt]["url"] = $img4["url"];
				$imgar[$cnt]["ord"] = $meta["img4_ord"];
				$cnt++;
			}
			if ($meta["img5_id"])
			{
				$img5 = $t->get_img_by_id($meta["img5_id"]);
				$this->vars(array(
					"image5" => "<img src='".$img5["url"]."'>",
					"img_ord5" => $meta["img5_ord"]
				));
				$imgar[$cnt]["id"] = $meta["img5_id"];
				$imgar[$cnt]["url"] = $img5["url"];
				$imgar[$cnt]["ord"] = $meta["img5_ord"];
				$cnt++;
			}

			usort($imgar,array($this,"_menu_img_cmp"));

			$this->set_object_metadata(array(
				"oid" => $row["oid"],
				"key" => "menu_images",
				"value" => $imgar
			));

			echo "menu $row[oid] <br>\n";
			flush();
			$this->restore_handle();
		}
	}
	
	function menu_reset_template_sets()
	{
		$q = "SELECT id FROM menu";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->save_handle();
			$oldmeta = $this->get_object_metadata(array("oid" => $row["id"]));
			if ($oldmeta)
			{
				$oldmeta["tpl_dir"] = "";
				$this->set_object_metadata(array(
					"oid" => $row["id"],
					"data" => $oldmeta,
				));	
			}
			$this->restore_handle();
		}
	}
	
	function promo_convert($args = array())
	{
		$q = sprintf("SELECT oid,name,comment,metadata,menu.sss FROM objects LEFT JOIN menu ON (objects.oid = menu.id) WHERE class_id = %d AND site_id = %d",CL_PROMO,aw_ini_get("site_id"));
		$this->db_query($q);
		// so, basically, if I load a CL_PROMO object and discover that it's
		// comment field is serialized - I will have to convert all promo
		// boxes in the system.

		// menu.sss tuleb ka unserialiseerida, saadud asjad annavad meile
		// last_menus sisu

		// so, how on earth do i make a callback into this class

		$convert = false;

		while($row = $this->db_next())
		{
			print "doing $row[oid]<br>";
			$this->save_handle();
			$meta_add = aw_unserialize($row["comment"]);
			$last_menus = aw_unserialize($row["sss"]);
			$meta = aw_unserialize($row["metadata"]);
			if (is_array($last_menus) || is_array($meta_add))
			{
				$convert = true;
			};
			$meta["last_menus"] = $last_menus;
			$meta["section"] = $meta_add["section"];
			if ($meta_add["right"])
			{
				$meta["type"] = 1;
			}
			elseif ($meta_add["up"])
			{
				$meta["type"] = 2;
			}
			elseif ($meta_add["down"])
			{
				$meta["type"] = 3;
			}
			elseif ($meta_add["scroll"])
			{
				$meta["type"] = "scroll";
			}
			else
			{
				$meta["type"] = 0;
			};
			$meta["all_menus"] = $meta_add["all_menus"];
			$comment = $meta_add["comment"];
			// reset sss field of menu table
			if ($convert)
			{
				$q = "UPDATE menu SET sss = '' WHERE id = '$row[oid]'";
				$this->db_query($q);

				$this->upd_object(array(
					"oid" => $row["oid"],
					"comment" => $comment,
					"metadata" => $meta,
				));
			};
			print "<pre>";
			print_r($meta);
			print "</pre>";
			$this->restore_handle();
			print "done<br>";
			sleep(1);
			flush();
		};
	}
	
	function convert_aliases()
	{
		$q = "SELECT target,source,type,relobj_id FROM aliases LEFT JOIN objects ON (aliases.relobj_id = objects.oid) WHERE objects.class_id = 179 AND relobj_id != 0";
		$this->db_query($q);
		$updates = array();
		while($row = $this->db_next())
		{
			$updates[] = "UPDATE objects SET subclass = $row[type] WHERE oid = $row[relobj_id]";
		};
		if (is_array($updates))
		{
			foreach($updates as $q)
			{
				print $q;
				print "<br>";
				flush();
				$this->db_query($q);
				sleep(1);
			};
		};			
		print "all done!<br>";
	}

	// parent argument should specify the folder under which to create the periods
	function convert_periods($args)
	{
		$tableinfo = $this->db_get_table("periods");
		// first, create the field in the periods table to sync with objects table
		$parent = $args["parent"];
		if (!$tableinfo["fields"]["obj_id"])
		{
			$q = "ALTER TABLE periods ADD obj_id bigint unsigned";
			$this->db_query($q);

		};
		
		$pid = $this->cfg["per_oid"];
		$q = "SELECT count(*) AS pcnt FROM periods WHERE oid = '$pid'";
		$this->db_query($q);
		$row = $this->db_next();
		if ($row["pcnt"] == 0)
		{
			return "Perioodid on juba konverditud";
		};
		

		if (empty($args["parent"]))
		{
			$m = get_instance("menuedit");
			$parent = $m->add_new_menu(array(
				"name" => "Perioodid (K)",
				"parent" => $this->cfg["admin_rootmenu2"],
				"type" => MN_CLIENT,

			));
		}

		// now, cycle over all the periods, and create an object for each one
		// under .. what? 
		set_time_limit(0);
		$map = array();
		$q = "SELECT * FROM periods WHERE oid = '$pid'";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			if (empty($row["obj_id"]))
			{
				$img_id = false;
				$this->save_handle();
				$undat = aw_unserialize($row["data"]);
				$comment = $undat["comment"];
				print "converting $row[description]<br>";
				flush();
				if (!empty($undat["image"]["id"]))
				{
					$img_id = $undat["image"]["id"];
				};
				unset($undat["comment"]);
				unset($undat["image"]);
				// it will be a relation object, so I only need to store the
				// id
				if ($img_id)
				{
					$undat["image"] = $img_id;
				};
				$newid = $this->new_object(array(
					"parent" => $parent,
					"name" => $row["description"],
					"comment" => $comment,
					"class_id" => CL_PERIOD,
					"jrk" => $row["jrk"],
					"status" => !empty($row["archived"]) ? STAT_ACTIVE : STAT_NOTACTIVE,
					"metadata" => $undat,
				));
				if ($img_id)
				{
					// create the relation too
					$this->addalias(array(
						"id" => $newid,
						"alias" => $img_id,
						"reltype" => 1,

					));
				}
				$map[$row["id"]] = $newid;
			};
			$this->restore_handle();
		};

		// and now, write out the newly created oids
		$awmap = new aw_array($map);
		foreach($awmap->get() as $key => $val)
		{
			$q = sprintf("UPDATE periods SET obj_id = %d WHERE id = %d",$val,$key);
			$this->db_query($q);
		};
		
		
	}
};
?>
