<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/converters.aw,v 1.21 2003/07/09 17:29:12 duke Exp $
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
		$q = "SELECT oid FROM objects WHERE class_id = 1";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->save_handle();
			$oldmeta = $this->get_object_metadata(array("oid" => $row["oid"]));
			if ($oldmeta)
			{
				if (!empty($oldmeta["tpl_dir"]))
				{
					$oldmeta["tpl_dir"] = "";
					$this->set_object_metadata(array(
						"oid" => $row["id"],
						"data" => $oldmeta,
					));	
				};
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
			$url = $this->cfg["baseurl"] . "/automatweb";
			header("Location: $url");
			exit;
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

	function groups_convert()
	{
		set_time_limit(0);

		$uroot = aw_ini_get("users.root_folder");
		if (!$uroot)
		{
			$this->raise_error(ERR_NO_USERS_ROOT,"Kasutajate rootketaloog on m&auml;&auml;ramata!", true);
		}

			
		aw_global_set("__from_raise_error",1);
		$this->db_query("ALTER TABLE users add oid int");
		aw_global_set("__from_raise_error",1);
		$this->db_query("ALTER TABLE users add index oid(oid)");

		// 1st, let's do users
		$q = 'select users.oid, users.uid, objects.parent from users left join objects ON objects.oid = users.oid';
		$arr = $this->db_fetch_array($q);
		foreach($arr as $val)
		{
			if (!is_numeric($val['oid']))
			{
				$oid = $this->new_object(array(
					"name" => $val['uid'], 
					"class_id" => CL_USER, 
					"status" => 2,
					"parent" => $uroot
				));
				if (is_numeric($oid))
				{
					$this->db_query('update users set oid='.$oid.' where uid="'.$val['uid'].'"');
				}
				echo "created object for user $val[uid] <br>\n";
				flush();
			}
			else
			if ($val["parent"] != aw_ini_get("users.root_folder"))
			{
				$this->db_query("UPDATE objects SET parent = ".aw_ini_get("users.root_folder")." WHERE oid = '".$val['oid']."'");
				echo "moved object to folder for user $val[uid] <br>\n";
				flush();
			}
		}

		// basically, move all groups objects to some rootmenu and that seems to be it.
		$rootmenu = aw_ini_get("groups.tree_root");
		if (!$rootmenu)
		{
			$this->raise_error(ERR_NO_USERS_ROOT,"Kasutajate rootketaloog on m&auml;&auml;ramata!", true);
		}
		// now, get all top-level groups.
		$this->db_query("SELECT gid,oid,type,search_form FROM groups WHERE (parent IS NULL or parent = 0) AND type IN(".GRP_REGULAR.",".GRP_DYNAMIC.")");
		while($row = $this->db_next())
		{
			$this->save_handle();
			if ($row["type"] == GRP_DYNAMIC)
			{
				$found = false;
				$aliases = $this->get_aliases_for($row["oid"]);
				foreach($aliases as $alias)
				{
					if ($alias["id"] == $row["search_form"])
					{
						$found = true;
					}
				}
		
				if (!$found)
				{
					// we must add an alias to the group object for the search form
					$this->addalias(array(
						"id" => $row["oid"],
						"alias" => $row["search_form"],
						"reltype" => 1
					));
				}
			}
			$sql = "UPDATE objects SET parent = $rootmenu WHERE oid = $row[oid]";
			$this->db_query($sql);
			echo "grupp: $row[gid] , oid = $row[oid] <br>\n";
			flush();

			// now we must also create brothers of all the group members below this group
			$u_objs = array();
			$sql = "SELECT oid, brother_of FROM objects WHERE parent = $row[oid] AND class_id = ".CL_USER." AND status != 0";
//			echo "sql = $sql <br>";
			$this->db_query($sql);
			while($urow = $this->db_next())
			{
				if (isset($u_objs[$urow["brother_of"]]))
				{
					// delete duplicates
					$this->save_handle();
					$this->delete_object($urow["oid"], false, false);
					$this->restore_handle();
				}
				else
				{
					$u_objs[$urow["brother_of"]] = $urow["oid"];
				}
			}
	
			// now get oids of group members
			$g_objs = array();
			$sql = "SELECT oid FROM users u LEFT JOIN groupmembers m ON m.uid = u.uid WHERE m.gid = $row[gid] AND oid IS NOT NULL AND oid > 0";
			$this->db_query($sql);
			while($grow = $this->db_next())
			{
				$g_objs[$grow["oid"]] = $grow["oid"];
			}

			// now, remove the ones that are not in the group
			foreach($u_objs as $real => $bro)
			{
				if (!isset($g_objs[$real]))
				{
					$this->delete_object($bro, false, false);
					$o_uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $real", "uid");
					echo "deleted bro for $o_uid (oid = $real) <br>\n";
					flush();
				}
			}

//			echo "u_objs = ".dbg::dump($u_objs)." <br>";
			// and add bros for the ones that are missing
			foreach($g_objs as $real)
			{
//				echo "real = $real <br>\n";
//				flush();
				if (!isset($u_objs[$real]))
				{
					$o_uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $real", "uid");
					$_t = $this->new_object(array(
						"parent" => $row["oid"],
						"class_id" => CL_USER,
						"brother_of" => $real,
						"name" => $o_uid,
						"no_flush" => 1,
						"status" => STAT_ACTIVE
					));
					echo "lisasin kasutaja venna $o_uid parent = $row[oid] , oid is $_t<br>\n";
					flush();
				}
			}

			// and also create aliases to all the members of the group in the group
			
			$sql = "SELECT users.uid, users.oid FROM groupmembers left join users on users.uid = groupmembers.uid WHERE groupmembers.gid = ".$row["gid"];
			$this->db_query($sql);
			while ($trow = $this->db_next())
			{
				if (!$trow["oid"])
				{
					continue;
				}
				$this->save_handle();

				// delete old aliases for this user.
				$this->db_query("DELETE FROM aliases WHERE target = $trow[oid] and source = $row[oid]");

				$this->addalias(array(
					"id" => $row["oid"],
					"alias" => $trow["oid"],
					"reltype" => 2
				));

				$this->restore_handle();
			}

			$this->_rec_groups_convert($row["gid"], $row["oid"]);
			$this->restore_handle();
		}
		die("Valmis!");
	}

	function _rec_groups_convert($pgid, $poid)
	{
		$this->db_query("SELECT gid,oid FROM groups WHERE parent = $pgid AND type IN(".GRP_REGULAR.",".GRP_DYNAMIC.")");
		while($row = $this->db_next())
		{
			$this->save_handle();
			if ($row["type"] == GRP_DYNAMIC)
			{
				$found = false;
				$aliases = $this->get_aliases_for($row["oid"]);
				foreach($aliases as $alias)
				{
					if ($alias["id"] == $row["search_form"])
					{
						$found = true;
					}
				}
		
				if (!$found)
				{
					// we must add an alias to the group object for the search form
					$this->addalias(array(
						"id" => $row["oid"],
						"alias" => $row["search_form"],
						"reltype" => 1
					));
				}
			}
			$sql = "UPDATE objects SET parent = $poid WHERE oid = $row[oid]";
			$this->db_query($sql);
			echo "grupp $row[gid] <br>\n";
			flush();

			// now we must also create brothers of all the group members below this group
			$u_objs = array();
			$sql = "SELECT oid, brother_of FROM objects WHERE parent = $row[oid] AND class_id = ".CL_USER." AND status != 0";
			$this->db_query($sql);
			while($urow = $this->db_next())
			{
				if (isset($u_objs[$urow["brother_of"]]))
				{
					// delete duplicates
					$this->save_handle();
					$this->delete_object($urow["oid"], false, false);
					$this->restore_handle();
				}
				else
				{
					$u_objs[$urow["brother_of"]] = $urow["oid"];
				}
			}

			// now get oids of group members
			$g_objs = array();
			$sql = "SELECT oid FROM users u LEFT JOIN groupmembers m ON m.uid = u.uid WHERE m.gid = $row[gid] AND oid IS NOT NULL AND oid > 0";
			$this->db_query($sql);
			while($grow = $this->db_next())
			{
				$g_objs[$grow["oid"]] = $grow["oid"];
			}

			// now, remove the ones that are not in the group
			foreach($u_objs as $real => $bro)
			{
				if (!isset($g_objs[$real]))
				{
					$this->delete_object($bro, false, false);
					$o_uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $real", "uid");
					echo "deleted bro for $o_uid (oid = $real) <br>\n";
					flush();
				}
			}

			// and add bros for the ones that are missing
			foreach($g_objs as $real)
			{
				if (!isset($u_objs[$real]))
				{
					$o_uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $real", "uid");
					$this->new_object(array(
						"parent" => $row["oid"],
						"class_id" => CL_USER,
						"brother_of" => $real,
						"name" => $o_uid,
						"status" => STAT_ACTIVE,
						"no_flush" => 1
					));
					echo "lisasin kasutaja venna $o_uid <br>\n";
					flush();
				}
			}

			// and also create aliases to all the members of the group in the group
			$sql = "SELECT users.uid, users.oid FROM groupmembers left join users on users.uid = groupmembers.uid WHERE groupmembers.gid = ".$row["gid"];
			$this->db_query($sql);
			while ($trow = $this->db_next())
			{
				if (!$trow["oid"])
				{
					continue;
				}
				$this->save_handle();

				// delete old aliases for this user.
				$this->db_query("DELETE FROM aliases WHERE target = $trow[oid] and source = $row[oid]");

				$this->addalias(array(
					"id" => $row["oid"],
					"alias" => $trow["oid"],
					"reltype" => 2
				));
				$this->restore_handle();
			}

			$this->_rec_groups_convert($row["gid"], $row["oid"]);
			$this->restore_handle();
		}
	}

	///////////////////////////////
	function convert_acl_to_classbase()
	{
		// go over all acl objects and make aliases for the selected roles/chains/groups
		$objs = $this->list_objects(array(
			"class" => CL_ACL,
			"return" => ARR_ALL
		));
		foreach($objs as $obj)
		{
			echo "converting object $obj[name] ($obj[oid]) <br>\n";
			$obj["meta"] = $this->get_object_metadata(array(
				"metadata" => $obj["metadata"]
			));
			flush();
			// ah, fuck it. 1st, delete all aliases
			$this->db_query("DELETE FROM aliases WHERE source = $obj[oid]");

			// now, add them back
			if ($obj["meta"]["role"])
			{
//				echo "role = ".$obj["meta"]["role"]." <br>";
				core::addalias(array(
					"id" => $obj["oid"],
					"alias" => $obj["meta"]["role"],
					"reltype" => 2
				));
			}
			if ($obj["meta"]["chain"])
			{
//				echo "chain = ".$obj["meta"]["chain"]." <br>";
				core::addalias(array(
					"id" => $obj["oid"],
					"alias" => $obj["meta"]["chain"],
					"reltype" => 1
				));
			}
			$_ar = new aw_array($obj["meta"]["groups"]);

			foreach($_ar->get() as $gid)
			{
//				echo "gid = $gid <br>";
				$u = get_instance("users");
				core::addalias(array(
					"id" => $obj["oid"],
					"alias" => $u->get_oid_for_gid($gid),
					"reltype" => 3
				));
				// also, add alias to acl object from group
				// but only if it does not exist
				$row = $this->db_fetch_row("SELECT * FROM aliases WHERE source = ".$u->get_oid_for_gid($gid)." AND target = ".$obj['oid']);
				if (!is_array($row))
				{
					core::addalias(array(
						"id" => $u->get_oid_for_gid($gid),
						"alias" => $obj['oid'],
						"reltype" => 3
					));
					echo "add alias to group ".$u->get_oid_for_gid($gid)." <br>";
				}
			}
		}
		die("Valmis!");
	}

	function convert_fg_tables_deleted()
	{
		$ol = $this->list_objects(array(
			"class" => CL_FORM
		));

		echo "converting formgen tables! <br><br>\n";

		foreach($ol as $oid => $_d)
		{
			echo "form $oid <br>\n";
			flush();
			$tbl = "form_".$oid."_entries";
			aw_global_set("__from_raise_error",1);
			$this->db_query("ALTER TABLE $tbl DROP deleted");
			aw_global_set("__from_raise_error",0);
			$this->db_query("ALTER TABLE $tbl ADD deleted int default 0");
			aw_global_set("__from_raise_error",1);
			$this->db_query("ALTER TABLE $tbl ADD index deleted(deleted)");
			aw_global_set("__from_raise_error",0);


			// now, also go oever all the entries and mark the deleted ones as deleted
			$this->db_query("SELECT f.id as id , o.status as status FROM $tbl f left join objects o on o.oid = f.id");
			while($row = $this->db_next())
			{
				if ($row["status"] < 1)
				{					
					$this->save_handle();
					$this->db_query("UPDATE $tbl SET deleted = 1 WHERE id = $row[id]");
					$this->restore_handle();
				}
			}
		}
		die();
	}

	function convert_really_old_aliases()
	{
		echo "converting really old image aliases... <br>\n\n<br>";
		flush();
		$this->db_query("SELECT oid FROM objects WHERE class_id = ".CL_DOCUMENT." AND status != 0");
		while ($row = $this->db_next())
		{
			$id = $row["oid"];
			$this->save_handle();
			$q = "SELECT objects.*,images.*
				FROM objects
				LEFT JOIN images ON (objects.oid = images.id)
				WHERE parent = '$id' AND class_id = '6' AND status = 2  
				ORDER BY idx";
			$this->db_query($q);

			while($row = $this->db_next()) 
			{
				$alias = "#p".$row["idx"]."#";

				// now check if the alias already exists
				$this->save_handle();
				if (!$this->db_fetch_field("SELECT id FROM aliases WHERE source = '$id' AND target = '$row[oid]'", "id"))
				{
					echo "adding alias for image $row[oid] to document $id <br>\n";
					flush();
					$this->addalias(array(
						"id" => $id,
						"alias" => $row["oid"],
					));
					$this->db_query("UPDATE aliases SET idx = '$row[idx]' WHERE source = '$id' AND target = '$row[oid]'");
					
				}
				$this->restore_handle();
			};
			$this->restore_handle();
		}
	}

	function convert_copy_makes_brother()
	{
		$this->_copy_makes_brother_fg();
		$this->_copy_makes_brother_menu();
		die("all done! <br>");
	}

	function _copy_makes_brother_fg()
	{
		$this->db_query("SELECT oid FROM objects WHERE class_id = ".CL_FORM." AND status != 0 AND brother_of != oid");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			
			$id = $this->db_fetch_field("SELECT id FROM forms WHERE id = '$row[oid]'", "id");
			if ($id)
			{
				$this->db_query("UPDATE objects SET brother_of = oid WHERE oid = '$id'");
				echo "fixed form $id <br>\n";
				flush();
			}
			$this->restore_handle();
		}
	}

	function _copy_makes_brother_menu()
	{
		$this->db_query("SELECT oid FROM objects WHERE class_id = ".CL_PSEUDO." AND status != 0 AND brother_of != oid");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			
			$id = $this->db_fetch_field("SELECT id FROM menu WHERE id = '$row[oid]'", "id");
			if ($id)
			{
				$this->db_query("UPDATE objects SET brother_of = oid WHERE oid = '$id'");
				echo "fixed menu $id <br>\n";
				flush();
			}
			$this->restore_handle();
		}
	}

	function convert_seealso_menus()
	{
		/*
		$q = "SELECT oid,metadata FROM objects WHERE class_id = 1 ORDER BY oid";
		$this->db_query($q);
		$sao = array();
		while($row = $this->db_next())
		{
			$unmet = aw_unserialize($row["metadata"]);
			if (is_array($unmet["seealso_refs"]))
			{
				$sao[$row["oid"]] = $unmet["seealso_refs"];
				print "<pre>";
				print_r($row);
				print "</pre>";
			};		
		}

		print "<pre>";
		print_r($sao);
		print "</pre>";
		*/

		$q = "SELECT id,seealso FROM menu WHERE seealso IS NOT NULL";
		$this->db_query($q);
		$oas = array();
		while($row = $this->db_next())
		{
			$unser_seealso = aw_unserialize($row["seealso"]);
			if (is_array($unser_seealso))
			{
				$res = array();
				foreach($unser_seealso as $key => $val)
				{
					if (is_array($val))
					{
						$res = $res + $val;
					}
					else
					{
						$res[$key] = 0;
					};
				};
				if (sizeof($res) > 0)
				{
					$oas[$row["id"]] = $res;
				};
			};
		};

		print "<pre>";
		print_r($oas);
		print "</pre>";

		$almgr = get_instance("aliasmgr");

		// now we cycle over oas, and create an assload of relations
		foreach($oas as $key => $val)
		{
			// $oas'i keyd on targetid
			// $val'i keyd on sourced .. ja kuhu pekki ma jrk panen?
			foreach($val as $vkey => $vval)
			{
				print "creating relation from $vkey to $key with jrk $vval<br>";
				flush();
				$almgr->create_alias(array(
					"id" => $vkey,
					"alias" => $key,
					"data" => $vval,
					"reltype" => 5,
				));
				print "done<br>";
				flush();
				// and just if I my ask do I put the freaking jrk?
				// no other way than to serialize it into "data"
			}

		}

	}
};
?>
