<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/converters.aw,v 1.61 2006/01/18 18:09:07 kristo Exp $
// converters.aw - this is where all kind of converters should live in
class converters extends aw_template
{
	// this will be set to document id if only one document is shown, a document which can be edited
	var $active_doc = false;

	function converters()
	{
		$this->init("");

	}

	/**  
		
		@attrib name=menu_convimages params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function menu_convimages()
	{
		$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu on menu.id = objects.oid WHERE class_id = ".CL_MENU." AND status != 0");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			
			$meta = aw_unserialize($row["metadata"]);

			$cnt = 0;
			$imgar = array();

			$t = get_instance(CL_IMAGE);
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

			$o = obj($row["oid"]);
			$o->set_meta("menu_images", $imgar);
			$o->save();

			echo "menu $row[oid] <br />\n";
			flush();
			$this->restore_handle();
		}
	}
	
	/**  
		
		@attrib name=menu_reset_template_sets params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function menu_reset_template_sets()
	{
		$q = "SELECT oid FROM objects WHERE class_id = 1 AND status > 0";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->save_handle();
			aw_disable_acl();
			$tmp = obj($row["oid"]);
			$oldmeta = $tmp->meta();
			if ($oldmeta)
			{
				if (!empty($oldmeta["tpl_dir"]))
				{
					$tmp->set_meta("tpl_dir", "");
					$tmp->save();
				};
			}
			$this->restore_handle();
		}
	}
	
	/**  
		
		@attrib name=promo_convert params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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
			print "doing $row[oid]<br />";
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

				aw_disable_acl();
				$tmp = obj($row["oid"]);
				$tmp->set_comment($comment);
				$awa = new aw_array($meta);
				foreach($awa->get() as $k => $v)
				{
					$tmp->set_meta($k, $v);
				}
				$tmp->save();
			};
			print "<pre>";
			print_r($meta);
			print "</pre>";
			$this->restore_handle();
			print "done<br />";
			sleep(1);
			flush();
		};
	}

	////
	// some nonfunctional code here, that will convert the data stored in object metadata
	// to relations ... thrown out from the main class. I don't think anyone will miss
	// that code, b
	function convert_promo_relations($promo_box_id)
	{
	       // now, check, whether we have to convert the current contents of comment and sss to relation objects
                // we use a flag in object metainfo for that

                // and still, it would be nice if we could convert all the promo boxes at once.
                // then I wouldn't have to check for this shit each fucking time, for each
                // fucking promo box. But maybe it's not as bad as I imagine it
		$obj = new object($promo_box_id);
                if ($obj->meta("uses_relationmgr"))
                {
                        return true;
                };

		$oldaliases = $obj->connections_from(array(
			"class" => CL_MENU,
		));

                $flatlist = array();


		// basically, I have to get a list of menus in $args["object"]["meta"]["section"]
		// and create a relation of type RELTYPE_ASSIGNED_MENU for each of those

                $sections = $args["obj_inst"]->meta("section");
                if ( is_array($sections) && (sizeof($sections) > 0) )
                {
                        foreach($sections as $key => $val)
                        {
                                // beiskli I need to check whether that relation exists, and if so
                                // then I should not create a new one
                                if (!$flatlist[$val])
                                {
									$o = obj($id);
									$o->connect(array(
										"to" => $val,
										"reltype" => "RELTYPE_ASSIGNED_MENU",
									));
                                };
                        };
                }

		               // then I have to get a list of menus in $args["object"]["meta"]["last_menus"] and
                // create a relation of type RELTYPE_DOC_SOURCE for each of those.

                // I also want to keep the old representation around, so that old code keeps working
                $last_menus = $args["obj_inst"]->meta("last_menus");
                if ( is_array($last_menus) && (sizeof($last_menus) > 0) )
                {
                        foreach($last_menus as $key => $val)
                        {
                                if (!$flatlist[$val])
                                {
									$o = obj($id);
									$o->connect(array(
										"to" => $val,
										"reltype" => "RELTYPE_DOC_SOURCE",
									));
                                };
                        };
                }

                // update reltype information, that is only if there is anything to update
                       $args["obj_inst"]->set_meta("uses_relationmgr",1);
                        $args["obj_inst"]->save();

	}
	
	/**  
		
		@attrib name=convert_aliases params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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
				print "<br />";
				flush();
				$this->db_query($q);
				sleep(1);
			};
		};			
		print "all done!<br />";
	}

	// parent argument should specify the folder under which to create the periods
	/**  
		
		@attrib name=convert_periods params=name default="0"
		
		@param parent optional type=int
		
		@returns
		
		
		@comment

	**/
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
				"parent" => cfg_get_admin_rootmenu2(),
				"type" => MN_CLIENT,

			));
		}

		// now, cycle over all the periods, and create an object for each one
		// under .. what? 
		set_time_limit(14400);
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
				print "converting $row[description]<br />";
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
				$_meta = aw_serialize($undat);
				$this->quote(&$_meta);
				$this->db_query("INSERT INTO 
						objects(parent,name,comment,class_id, jrk, status, metadata, createdby, created, modifiedby, modified)
						values('$parent','$row[description]','$comment',".CL_PERIOD.",'$row[jrk]','".!empty($row["archived"]) ? STAT_ACTIVE : STAT_NOTACTIVE."',
						'$_meta','".aw_global_get("uid")."','".time()."','".aw_global_get("uid")."','".time()."')
				");
				$newid = $this->db_last_insert_id();
				if ($img_id)
				{
					// create the relation too
					$o = obj($newid);
					$o->connect(array(
						"to" => $img_id,
						"reltype" => 1
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

	/**  
		
		@attrib name=groups_convert params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function groups_convert()
	{
		set_time_limit(14400);

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
				$this->db_query("INSERT INTO 
						objects(parent,name,comment,class_id, jrk, status, metadata, createdby, created, modifiedby, modified)
						values('$uroot','$val[uid]','',".CL_USER.",'','2',
						'','".aw_global_get("uid")."','".time()."','".aw_global_get("uid")."','".time()."')
				");
				$oid = $this->db_last_insert_id();

				if (is_numeric($oid))
				{
					$this->db_query('update users set oid='.$oid.' where uid="'.$val['uid'].'"');
				}
				echo "created object for user $val[uid] <br />\n";
				flush();
			}
			else
			if ($val["parent"] != aw_ini_get("users.root_folder"))
			{
				$this->db_query("UPDATE objects SET parent = ".aw_ini_get("users.root_folder")." WHERE oid = '".$val['oid']."'");
				echo "moved object to folder for user $val[uid] <br />\n";
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
			$sql = "UPDATE objects SET parent = $rootmenu WHERE oid = $row[oid]";
			$this->db_query($sql);
			echo "grupp: $row[gid] , oid = $row[oid] <br />\n";
			flush();

			// now we must also create brothers of all the group members below this group
			$u_objs = array();
			$sql = "SELECT oid, brother_of FROM objects WHERE parent = $row[oid] AND class_id = ".CL_USER." AND status != 0";
//			echo "sql = $sql <br />";
			$this->db_query($sql);
			while($urow = $this->db_next())
			{
				if (isset($u_objs[$urow["brother_of"]]))
				{
					// delete duplicates
					$this->save_handle();
					aw_disable_acl();
					$tmp = obj($urow["oid"]);
					$tmp->delete();
					aw_restore_acl();
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
					aw_disable_acl();
					$tmp = obj($bro);
					$tmp->delete();
					aw_restore_acl();
					$o_uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $real", "uid");
					echo "deleted bro for $o_uid (oid = $real) <br />\n";
					flush();
				}
			}

//			echo "u_objs = ".dbg::dump($u_objs)." <br />";
			// and add bros for the ones that are missing
			foreach($g_objs as $real)
			{
//				echo "real = $real <br />\n";
//				flush();
				if (!isset($u_objs[$real]))
				{
					$tmp = obj($real);
					$_t = $tmp->create_brother($row["oid"]);
					echo "lisasin kasutaja venna $o_uid parent = $row[oid] , oid is $_t<br />\n";
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

				$o = obj($row["oid"]);
				$o->connect(array(
					"to" => $trow["oid"],
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
			$sql = "UPDATE objects SET parent = $poid WHERE oid = $row[oid]";
			$this->db_query($sql);
			echo "grupp $row[gid] <br />\n";
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
					aw_disable_acl();
					$tmp = obj($urow["oid"]);
					$tmp->delete();
					aw_restore_acl();
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
					aw_disable_acl();
					$tmp = obj($bro);
					$tmp->delete();
					aw_restore_acl();
					$o_uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $real", "uid");
					echo "deleted bro for $o_uid (oid = $real) <br />\n";
					flush();
				}
			}

			// and add bros for the ones that are missing
			foreach($g_objs as $real)
			{
				if (!isset($u_objs[$real]))
				{
					$o_uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $real", "uid");
					$tmp = obj($real);
					$tmp->create_brother($row["oid"]);

					echo "lisasin kasutaja venna $o_uid <br />\n";
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

				$o = obj($row["oid"]);
				$o->connect(array(
					"to" => $trow["oid"],
					"reltype" => 2
				));
				$this->restore_handle();
			}

			$this->_rec_groups_convert($row["gid"], $row["oid"]);
			$this->restore_handle();
		}
	}

	///////////////////////////////
	/**  
		
		@attrib name=convert_acl_to_classbase params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function convert_acl_to_classbase()
	{
		// go over all acl objects and make aliases for the selected roles/chains/groups
		$ol = new object_list(array(
			"class_id" => CL_ACL,
			"site_id" => array(),
			"lang_id" => array()
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$obj = $o->fetch();
			echo "converting object $obj[name] ($obj[oid]) <br />\n";
			$obj["meta"] = aw_unserialize($obj["metadata"]);
			flush();
			// ah, fuck it. 1st, delete all aliases
			$this->db_query("DELETE FROM aliases WHERE source = $obj[oid]");

			// now, add them back
			if ($obj["meta"]["role"])
			{
//				echo "role = ".$obj["meta"]["role"]." <br />";
				$o = obj($obj["oid"]);
				$o->connect(array(
					"to" => $obj["meta"]["role"],
					"reltype" => 2
				));
			}
			if ($obj["meta"]["chain"])
			{
//				echo "chain = ".$obj["meta"]["chain"]." <br />";
				$o = obj($obj["oid"]);
				$o->connect(array(
					"to" => $obj["meta"]["chain"],
					"reltype" => 1
				));
			}
			$_ar = new aw_array($obj["meta"]["groups"]);

			foreach($_ar->get() as $gid)
			{
//				echo "gid = $gid <br />";
				$u = get_instance("users");
				$o = obj($obj["oid"]);
				$o->connect(array(
					"to" => $u->get_oid_for_gid($gid),
					"reltype" => 3
				));
				// also, add alias to acl object from group
				// but only if it does not exist
				$row = $this->db_fetch_row("SELECT * FROM aliases WHERE source = ".$u->get_oid_for_gid($gid)." AND target = ".$obj['oid']);
				if (!is_array($row))
				{
					$o = obj($u->get_oid_for_gid($gid));
					$o->connect(array(
						"to" => $obj['oid'],
						"reltype" => 3
					));
					echo "add alias to group ".$u->get_oid_for_gid($gid)." <br />";
				}
			}
		}
		die("Valmis!");
	}

	/**  
		
		@attrib name=convert_fg_tables_deleted params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function convert_fg_tables_deleted()
	{
		$ol = new object_list(array(
			"class_id" => CL_FORM,
			"site_id" => array(),
			"lang_id" => array()
		));

		echo "converting formgen tables! <br /><br />\n";

		foreach($ol->names() as $oid => $_d)
		{
			echo "form $oid <br />\n";
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

	/**  
		
		@attrib name=convert_really_old_aliases params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function convert_really_old_aliases()
	{
		echo "converting really old image aliases... <br />\n\n<br />";
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
					echo "adding alias for image $row[oid] to document $id <br />\n";
					flush();
					$o = obj($id);
					$o->connect(array(
						"to" => $row['oid'],
					));
					$this->db_query("UPDATE aliases SET idx = '$row[idx]' WHERE source = '$id' AND target = '$row[oid]'");
					
				}
				$this->restore_handle();
			};
			$this->restore_handle();
		}
	}

	/** creates indexes for aliases
		@attrib name=convert_alias_idx
	**/
	function convert_alias_idx()
	{
		$this->db_query("SELECT * FROM aliases WHERE idx = 0");
		while ($row = $this->db_next())
		{
			$lut[$row["source"]][$row["type"]] ++;
			$this->save_handle();
			$this->db_query("UPDATE aliases SET idx = ".$lut[$row["source"]][$row["type"]]." WHERE id = ".$row["id"]);
			echo "updated alias from dooc $row[source] to idx ".$lut[$row["source"]][$row["type"]]." <BR>";
			$this->restore_handle();
		}
	}

	/**  
		
		@attrib name=convert_copy_makes_brother params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function convert_copy_makes_brother()
	{
		$this->_copy_makes_brother_fg();
		$this->_copy_makes_brother_menu();
		die("all done! <br />");
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
				echo "fixed form $id <br />\n";
				flush();
			}
			$this->restore_handle();
		}
	}

	function _copy_makes_brother_menu()
	{
		$this->db_query("SELECT oid FROM objects WHERE class_id = ".CL_MENU." AND status != 0 AND brother_of != oid");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			
			$id = $this->db_fetch_field("SELECT id FROM menu WHERE id = '$row[oid]'", "id");
			if ($id)
			{
				$this->db_query("UPDATE objects SET brother_of = oid WHERE oid = '$id'");
				echo "fixed menu $id <br />\n";
				flush();
			}
			$this->restore_handle();
		}
	}

	/**  
		
		@attrib name=convert_seealso_menus params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
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
				print "creating relation from $vkey to $key with jrk $vval<br />";
				flush();
				$almgr->create_alias(array(
					"id" => $vkey,
					"alias" => $key,
					"data" => $vval,
					"reltype" => 5,
				));
				print "done<br />";
				flush();
				// and just if I my ask do I put the freaking jrk?
				// no other way than to serialize it into "data"
			}

		}

	}

	/** creates the active_documents list for each folder in the system. the shitty part about this is, of course that 
		
		@attrib name=convert_active_documents_list params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment
		all section modifiers will be fucked.

	**/
	function convert_active_documents_list()
	{
		set_time_limit(14400);
		echo "creating active document lists! <br>\n";
		flush();
		$ol = new object_list(array(
			"class_id" => array(CL_DOCUMENT, CL_PERIODIC_SECTION)
		));
		
		$di = get_instance("doc");
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			echo "document ".$o->name()." (".$o->id()." ) <br>\n";
			flush();
			$di->on_save_document(array("oid" => $o->id()));
		}

		die("all done!");
	}

	/**  
		
		@attrib name=convert_doc_templates params=name nologin="1" default="0"
		
		@param parent required
		
		@returns
		
		
		@comment

	**/
	function convert_doc_templates($arr)
	{
		$parent = $arr["parent"];

		// check for oid column.
		$tbl = $this->db_get_table("template");
		if (!isset($tbl["fields"]["obj_id"]))
		{
			$this->db_query("ALTER TABLE template ADD obj_id int default 0");
		}
		$this->db_query("SELECT * FROM template WHERE obj_id = 0 OR obj_id IS NULL");
		while ($row = $this->db_next())
		{
			$this->save_handle();

			$this->db_query("INSERT INTO 
					objects(parent,name,comment,class_id, jrk, status, metadata, createdby, created, modifiedby, modified)
					values('$parent','$row[name]','',".CL_CONFIG_AW_DOCUMENT_TEMPLATE.",'','2',
					'','".aw_global_get("uid")."','".time()."','".aw_global_get("uid")."','".time()."')
			");
			$id = $this->db_last_insert_id();
			$this->db_query("UPDATE template SET obj_id = '$id' WHERE id = '$row[id]'");

			echo "template $row[name] <br>";
			$this->restore_handle();
		}
	}

	/**  
		
		@attrib name=convert_menu_images params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function convert_menu_images($arr)
	{
		echo "converting menu image aliases<br>\n";
		flush();
		$ol = new object_list(array(
			"class_id" => CL_MENU
		));
		echo "got list of all menus (".$ol->count().")<br>\n";
		flush();
		aw_disable_acl();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			echo "menu ".$o->name()." (".$o->id().")<br>\n";
			flush();

			$t = $o->meta("menu_images");
			$mi = new aw_array($t);
			foreach($mi->get() as $idx => $i)
			{
				if ($i["id"])
				{
					
					$o->connect(array(
						"to" => $i["id"],
						"reltype" => 14
					));
					$t[$idx]["image_id"] = $i["id"];
				}
			}
			if ($o->parent() && $o->class_id())
			{
				$o->set_meta("menu_images", $t);
				$o->save();
			}
		}

		die("all done!");
	}
	
	/**  
		
		@attrib name=convert_crm_relations2 nologin="1"
		
		
		@returns
		
		
		@comment

	**/
	function convert_crm_relations2($arr)
	{
		// see annab mulle kõik aadressiobjektid, millel on seos URL objektiga
		set_time_limit(14400);
		// 21 / 6 / 16 is URL
		// 219 / 9 / 17 is phone (but really fax)
		// 219 / 7,8 / 17 , is phone
		//$q = "select aliases.id,aliases.source as oldsource,aliases2.source as newsource,aliases.target as newtarget from aliases,objects,aliases as aliases2,objects as objects2  where aliases.source = objects.oid and aliases2.target = objects.oid and aliases2.source = objects2.oid and objects2.class_id = 129 and aliases.type = 21 and aliases.reltype = 6 and aliases2.reltype = 3 and objects.class_id = 146 and objects.status != 0";
		$q = "select aliases.id,aliases.source as oldsource,aliases2.source as newsource,aliases.target as newtarget from aliases,objects,aliases as aliases2,objects as objects2  where aliases.source = objects.oid and aliases2.target = objects.oid and aliases2.source = objects2.oid and objects2.class_id = 129 and aliases.type = 21 and aliases.reltype = 5 and aliases2.reltype = 3 and objects.class_id = 146 and objects.status != 0";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->save_handle();
			// read old relation, fix it.
			$id = $row["id"];
			$newsource = $row["newsource"];
			//$q = "UPDATE aliases SET source = '$newsource', reltype = 16 WHERE id = '$id'";
			$q = "UPDATE aliases SET source = '$newsource', reltype = 15 WHERE id = '$id'";
			print $q;
			print "<br>";
			$this->db_query($q);

			$this->restore_handle();
		};
		print "all done<br>";
		//  I need to get aliases that are linked to those object and sources from them.


	}

	
	
	/**  
		
		@attrib name=convert_crm_relations nologin="1"
		
		
		@returns
		
		
		@comment

	**/
	function convert_crm_relations($arr)
	{
		set_time_limit(14400);
		$q = "SELECT objects.oid,objects.name,aliases.reltype,aliases.target AS target FROM aliases,objects WHERE aliases.source = objects.oid AND aliases.type = 219;";
		$this->db_query($q);
		$oids = $targets = array();
		while($row = $this->db_next())
		{
			//print "<pre>";
			$oids[] = $row["oid"];
			$targets[$row["oid"]] = $row["target"]; 
			//print_r($row);
			//print "</pre>";
		};

		//$oids = array(90281,92468);

		$q = "SELECT oid,name,class_id,aliases.target AS target FROM aliases,objects WHERE aliases.source = objects.oid AND aliases.target IN (" . join(",",$oids) . ")";
		$this->db_query($q);

		// now I have to all those ID-s. some are 145, which means isik, others are 129
		// which are companies
		while($row = $this->db_next())
		{
			$this->save_handle();
			// now I need to create the new links
			//if ($row["oid"] != 90281 && $row["oid"] != 92648)
			//{
				//continue;
			//};
			print "<pre>";
			print_r($row);
			print "</pre>";
			flush();
			$tg_phone = new object($targets[$row["target"]]);
			$src_obj = new object($row["oid"]);
			if ($row["class_id"] == 145)
			{
				print "Lingin isiku telefoniga " . $targets[$row["target"]] . "/" . $tg_phone->name() . "<br>";
				$src_obj->connect(array(
					"to" => $tg_phone->id(),
					"reltype" => 13,
				));
				// seose tüüp - 13
			};
			if ($row["class_id"] == 129)
			{
				print "Lingin organisatsiooni telefoniga " . $targets[$row["target"]] . "/" . $tg_phone->name() . "<br>";
				print $tg_phone->name();
				$src_obj->connect(array(
					"to" => $tg_phone->id(),
					"reltype" => 17,
				));
				// seose tüüp - 17
			};
			flush();
			$this->restore_handle();
		};

	}

	/**

	@attrib name=convert_person_org_relations

	**/
	function convert_person_org_relations($arr)
	{
		// list all connections from organizations to persons
		set_time_limit(14400);
		$q = "SELECT aliases.source,aliases.target FROM aliases,objects WHERE type = 145 AND reltype = 8 AND aliases.source = objects.oid AND objects.class_id = 129 AND objects.status != 0";
		$this->db_query($q);
		$res = array();
		while($row = $this->db_next())
		{
			$this->save_handle();
			$q = "SELECT * FROM aliases WHERE target = '$row[source]' AND source = '$row[target]'";
			$this->db_query($q);
			$row2 = $this->db_next();
			if ($row2)
			{
				//print "org is connected $row[source],$row[target]<bR>";
			}
			else
			{
				/*
				$per_obj = new object($row["target"]);
				$per_obj->connect(array(
					"to" => $row["source"],
					"reltype" => 6,
				));
				*/
				print "person needs to be connected $row[target],$row[source]<br>";
			};
			flush();
			$this->restore_handle();
		};

		print "persons done<br>";
		
		$q = "SELECT aliases.source,aliases.target FROM aliases,objects WHERE type = 129 AND reltype = 6 AND aliases.source = objects.oid AND objects.class_id = 145 AND objects.status != 0";
		$this->db_query($q);
		$res = array();
		while($row = $this->db_next())
		{
			// there can be more than one .. fucking shit.
			// fucking fuckety fuckety fucking fuckety shit
			//$res[$row["source"]][$row["target"]] = $row["target"];
			$this->save_handle();
			$q = "SELECT * FROM aliases WHERE target = '$row[source]' AND source = '$row[target]'";
			$this->db_query($q);
			$row2 = $this->db_next();
			if ($row2)
			{
				print "org is connected $row[source],$row[target]<bR>";
			}
			else
			{
				/*
				$per_obj = new object($row["target"]);
				$per_obj->connect(array(
					"to" => $row["source"],
					"reltype" => 8,
				));
				*/
				print "person needs to be connected $row[target],$row[source]<br>";
			};
			flush();
			$this->restore_handle();
		};

		print "orgs done<br>";
	}

	/**  
		
		@attrib name=confirm_crm_choices

	*/
	function confirm_crm_choices($arr)
	{
		// go over all objects, figure out the ones that do not have a confirmed relation
		// and if there are any .. then confirm those thingies

		// phone_id / 17
		// url_id / 16
		// email_id / 15
		// telefax_id / 18

		$q = "SELECT oid,target
			FROM kliendibaas_firma,aliases
			WHERE kliendibaas_firma.oid = aliases.source AND email_id = 0 AND aliases.reltype = 15";
		$this->db_query($q);
		$qs = array();
		while($row = $this->db_next())
		{
			$pid = $row["target"];
			$oid = $row["oid"];
			$qs[] = "UPDATE kliendibaas_firma SET email_id = $pid WHERE oid = $oid";
		};

		// phone_id, url_id, email_id, fax_id
		foreach($qs as $q)
		{
			print $q;
			flush();
			$this->db_query($q);
		};
		print "all done<br>";

	}

	/**  
		
		@attrib name=convert_docs_from_menu nologin="1"
		
		
		@returns
		
		
		@comment

	**/
	function convert_docs_from_menu($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_MENU,
			"site_id" => array(),
			"lang_id" => array()
		));
		echo "converting docs from menu relations <br>\n";
		flush();
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			echo "object ".$o->id()." name = ".$o->name()." <br>\n";
			flush();
			
			$sss = new aw_array($o->meta("sss"));
			foreach($sss->get() as $mnid)
			{
				// 9 - RELTYPE_DOCS_FROM_MENU
				if (!$o->is_connected_to(array("to" => $mnid, "type" => 9 )))
				{
					$o->connect(array(
						"to" => $mnid,
						"reltype" => 9
					));
					echo "connect to $mnid <br>\n";
					flush();
				}
			}
		}
		die("all done! ");
	}

	/**
		@attrib name=convert_crm_links

		@comment some e-mail addresses were originally created as link objects whereas
		they should have been created ml_members (the class that deals with mail
		addresses). This converts them.

	**/
	function convert_crm_links($arr)
	{
		// first I need to create records in ml_users.mail table for each
		// 
		// extlinks.url should become ml_users.mail

		// and I should also remove shit from extlinks table

		// and I should change class_id
		$q = "SELECT target,extlinks.url AS url FROM aliases,extlinks
			WHERE aliases.target = extlinks.id AND reltype = 15 AND aliases.type = 21";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->save_handle();
			$id = $row["target"];
			$mail = $row["url"];
			$sakk = "SELECT * FROM ml_users WHERE id = '$id'";
			$this->db_query($sakk);
			$rx = $this->db_next();
			if (!$rx)
			{
				$q = "INSERT INTO ml_users (id,mail) VALUES ($id,'$mail')";
				print $q;
				$this->db_query($q);
				print "<br>";
			};
			$q = "DELETE FROM extlinks WHERE id = '$id'";
			print $q;
			$this->db_query($q);
			print "<bR>";
			$q = "UPDATE objects SET class_id = 73 WHERE oid = '$id'";
			print $q;
			$this->db_query($q);
			print "<br>";
			flush();
			$this->restore_handle();
		};
		print "all done<br>";


	}
	

	/** converts acl entries to relations

		@attrib name=convert_acl_rels

	**/
	function convert_acl_rels($arr)
	{
		$GLOBALS["cfg"]["acl"]["no_check"] = 1;
		// get list og groups that are not user groups
		$gl = array();
		$this->db_query("select gid FROM groups WHERE type IN (".GRP_REGULAR.",".GRP_DYNAMIC.")");
		while ($row = $this->db_next())
		{
			$gl[] = $row["gid"];
		}
	
		$us = get_instance("users");

		$gs = join(",", $gl);
		echo "got groups as $gs <br>";
		$this->db_query("SELECT *,".$this->sql_unpack_string()." FROM acl WHERE gid IN ($gs)");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			if (!$this->db_fetch_field("SELECT oid FROM objects WHERE oid = $row[oid] AND status != 0", "oid"))
			{
				$this->restore_handle();
				continue;
			}
			$this->restore_handle();
			echo "oid = $row[oid] gid = $row[gid] <br>\n";
			flush();
			$obj = obj($row["oid"]);
			$g_obj = obj($us->get_oid_for_gid($row["gid"]));

			$goid = $g_obj->id();
			if (is_oid($goid))
			{
				$obj->connect(array(
					"to" => $goid,
					"reltype" => RELTYPE_ACL,
				));
			}
			// we don't need to do more, because the acl is read from the acl table!
		}
		die("all done!");
	}

	/** converts languages to objects
		@attrib name=lang_new_convert

		@param parent required type=int

	**/
	function lang_new_convert($arr)
	{
		$this->db_query("SELECT * FROM languages WHERE oid < 1 or oid is null");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			echo "keel ".$row["name"]." <br>";
			$oid = $this->db_fetch_field("SELECT max(oid) as oid FROM objects", "oid")+1;

			$this->db_query("INSERT INTO 
				objects(
					name,				status,			site_id,					lang_id,
					createdby,			created,		modifiedby, 				modified,
					class_id,			parent,			brother_of,					oid,
					alias
				)
				VALUES(
					'$row[name]',	2,				".aw_ini_get("site_id").",	".aw_global_get("lang_id").",
					'".aw_global_get("uid")."',".time().",'".aw_global_get("uid")."',".time().",
					".CL_LANGUAGE.",	$arr[parent],	$oid,	$oid,
					''
				)
			");
			$this->db_query("UPDATE languages SET oid = ".$oid." WHERE id = ".$row["id"]);
			$this->restore_handle();
		}
	}


	/** converts files from db to fs
	
		@attrib name=conv_files_to_fs

	**/
	function conv_files_to_fs()
	{
		$this->db_query("SELECT * FROM files WHERE file IS NULL");
		while ($row = $this->db_next())
		{
			if (strlen($row["content"]) > 0)
			{
				echo "putting file $row[id] to fs! <br>\n";
				flush();
				$f = get_instance(CL_FILE);
				$fs = $f->_put_fs(array(
					"type" => $row["type"],
					"content" => $row["content"]
				));
				$this->save_handle();
				$this->db_query("UPDATE files SET file = '$fs' WHERE id = '$row[id]'");
				echo "wrote as $fs <br>\n";
				flush();
				$this->restore_handle();
			}
		}
		die("all done");
	}

	/** convert acl to object table

		@attrib name=acl_to_objtbl 

	**/
	function acl_to_objtbl($arr)
	{
		$aclids = aw_ini_get("acl.ids");

		$this->db_query("UPDATE objects SET acldata = ''");

		// for all entries in the acl table
		// that are not for the owner of the object
		// write those suckers to the objects table acldata field
		$this->db_query("
			SELECT
				objects.createdby as createdby, 
				acl.gid as gid,
				acl.oid as oid,
				acl.acl as acl,
				groups.type as g_type,
				groups.name as g_name,
				groups.oid as g_oid
			FROM 
				acl
				LEFT JOIN objects ON objects.oid = acl.oid
				LEFT JOIN groups ON groups.gid = acl.gid
		");
		while ($row = $this->db_next())
		{
			$skip = ($row["g_type"] == GRP_DEFAULT || $row["g_type"] == GRP_DELETED_USER) && (strtolower($row["g_name"]) == strtolower($row["createdby"]) || $row["createdby"] == "");

			if (true || !$skip)
			{
				echo "row ".join(",", map2("%s => %s", $row))." is real, write to objtbl <br>";
				// get prev value 
				$this->save_handle();

				$curacl = safe_array(aw_unserialize($this->db_fetch_field("SELECT acldata FROM objects WHERE oid = $row[oid]", "acldata")));
				$curacl[$row["g_oid"]] = array();
				foreach($aclids as $bp => $nm)
				{
					$curacl[$row["g_oid"]][$nm] = (((1 << $bp) & $row["acl"]) ? 1 : 0);
				}

				//echo "got curacl as ".dbg::dump($curacl);
				$ser = aw_serialize($curacl);
				$this->quote(&$ser);

				$this->db_query("UPDATE objects SET acldata = '$ser' WHERE oid = $row[oid]");
				$this->restore_handle();
			}

			if (((++$cnt) % 500) == 1)
			{
				echo "obj nr $cnt , oid = $row[oid] <br>\n";
				flush();
			}
		}

		if (!$arr["no_die"])
		{
			die("all done");
		}
	}	

	/**
		
		@attrib name=test_acl

	**/
	function test_acl()
	{
		set_time_limit(14400);
		$aclids = aw_ini_get("acl.ids");
		$this->db_query("SELECT oid FROM objects ");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			foreach($aclids as $nm)
			{
				$nm = str_replace("can_", "", $nm);
				$r1 = $this->can($nm, $row["oid"]);
				//$r1 = $GLOBALS["object_loader"]->can($nm, $row["oid"], true);
				$r2 = $GLOBALS["object_loader"]->can($nm, $row["oid"]);
				if ($r1 != $r2)
				{
					echo "diff in acl! old = $r1 , new = $r2 , oid = $row[oid], nm = $nm <br>\n";
					/*$r2 = $GLOBALS["object_loader"]->can($nm, $row["oid"], true);
					$GLOBALS["acl_dbg"] = 1;
					$r1 = $this->can($nm, $row["oid"]);
					$GLOBALS["acl_dbg"] = 0;
					echo "------------------------------- <br>\n";*/
					flush();
					$cnt++;
					break;
				}
			}
			if (($cnt++ % 500) == 1)
			{
				echo "obj cnt $cnt, oid = $row[oid] <br>\n";
				flush();
			}
			$this->restore_handle();
		}
		die("all done");
	}

	/** adds aliases for form entries for user profiles

		@attrib name=convert_user_fg_prof

	**/
	function convert_user_fg_prof($arr)
	{
		aw_disable_messages();
		$ul = new object_list(array(
			"class_id" => CL_USER,
			"site_id" => array(),
			"lang_id" => array(),
			"brother_of" => new obj_predicate_prop("id")
		));
		foreach($ul->arr() as $o)
		{
			echo "user ".$o->name()." <br>\n";
			flush();
			$jfe = safe_array(aw_unserialize($o->prop("join_form_entry")));
			foreach($jfe as $eid)
			{
				if (!$o->is_connected_to(array("to" => $eid, "type" => 7)) && $this->can("view", $eid))
				{
					$o->connect(array(
						"to" => $eid,
						"reltype" => 7
					));
				}
			}
		}
		aw_restore_messages();
		die("all done");
	}

	/** convert users mail addresses

		@attrib name=convert_user_mails

	**/
	function convert_user_mails($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_USER,
			"brother_of" => new obj_predicate_prop("id"),
		));
		foreach($ol->arr() as $o)
		{
			echo "check user ".$o->prop("uid")." <br>\n";
			flush();

			$c = $o->connections_from(array(
				"type" =>  "RELTYPE_EMAIL",
			));
			if (!count($c))
			{
				// trigger message handler
				$u = $o->instance();
				$u->on_save_user(array(
					"oid" => $o->id()
				));
				echo "added mail <br>\n";
				flush();
			}
		}
		die("all done!");
	}

	/** cache data to sep table

		@attrib name=cache_data_to_sep_tbl

	**/
	function cache_data_to_sep_tbl($arr)
	{
		$ex = array();
		$this->db_query("SELECT oid FROM objects_cache_data");
		while ($row = $this->db_next())
		{
			$ex[$row["oid"]] = $row["oid"];
		}

		$this->db_query("SELECT oid FROM objects");
		while($row = $this->db_next())
		{
			if (!isset($ex[$row["oid"]]))
			{
				$this->save_handle();
				$this->db_query("INSERT INTO objects_cache_data (oid) values($row[oid])");
				echo "ins $row[oid] <br>";
				$this->restore_handle();
			}
		}
		die("all done");
	}

	/**
		@attrib name=conv_task_rows

	**/
	function conv_task_rows($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_TASK,
			"lang_id" => array(),
			"site_id" => array()
		));
		aw_global_set("no_cache_flush", 1);
		foreach($ol->arr() as $o)
		{
			// get all rows from task, convert to objects below task, connect to task and clear rows
			foreach(safe_array($o->meta("rows")) as $row)
			{
				$ro = obj();
				$ro->set_parent($o->id());
				$ro->set_class_id(CL_TASK_ROW);
				$ro->set_name($row["task"]);
				$ro->set_prop("content", $row["task"]);
				$ro->set_prop("date", $row["date"]);
				$ro->set_prop("impl", $row["impl"]);
				$ro->set_prop("time_guess", $row["time_guess"]);
				$ro->set_prop("time_real", $row["time_real"]);
				$ro->set_prop("time_to_cust", $row["time_to_cust"]);
				$ro->set_prop("done", $row["done"]);
				$ro->set_prop("on_bill", $row["on_bill"]);
				$ro->set_prop("bill_id", $row["bill_id"]);
				$ro->save();

				$o->connect(array(
					"to" => $ro->id(),
					"type" => "RELTYPE_ROW"
				));

				$o->set_meta("rows", null);
				$o->save();
				echo "converted ".$o->id()."<br>\n";
				flush();
			}
		}
		$c = get_instance("cache");
		$c->full_flush();
		die("all done");
	}
};
?>
