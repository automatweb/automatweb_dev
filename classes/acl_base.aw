<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/acl_base.aw,v 2.60 2004/03/11 09:46:45 kristo Exp $

lc_load("definition");

classload("db");
class acl_base extends db_connector
{
	function sql_unpack_string()
	{
		// oi kakaja huinja, bljat. 
		// the point is, that php can only handle 32-bit integers, but mysql can handle 64-bit integers
		// and so, we do the packing/unpacking to integer in the database. whoop-e
		// of course, now that we only have 5 acl settings, we don't have to do this in the db no more. 
		// anyone wanna rewrite it? ;) - terryf
		$qstr = array();
		reset($this->cfg["acl"]["ids"]);
		while (list($bitpos, $name) = each($this->cfg["acl"]["ids"]))
		{
			$qstr[] = " ((acl >> $bitpos) & 3) AS $name";
		}

		$s =  join(",",$qstr);
		return $s;
	}

	function get_acl_groups_for_obj($oid)
	{
		$ret = array();
		$q = "SELECT *,groups.name as name,".$this->sql_unpack_string()."
					FROM acl LEFT JOIN groups ON groups.gid = acl.gid
					WHERE acl.oid = $oid";

		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$ret[$row["gid"]] = $row;
		}

		return $ret;
	}

	function add_acl_group_to_obj($gid,$oid,$aclarr = array(), $invd = true)
	{
		$this->db_query("insert into acl(gid,oid) values($gid,$oid)");
		if (sizeof($aclarr) == 0)
		{
			// set default acl if not specified otherwise
			$aclarr = $this->cfg["acl"]["default"];
		};
		$this->save_acl($oid,$gid,$aclarr);		
		if ($invd)
		{
			aw_session_set("__acl_cache", array());
			$c = get_instance("cache");
			$c->file_invalidate_regex("acl-cache(.*)");
		}
	}

	function remove_acl_group_from_obj($gid,$oid)
	{
		$this->db_query("DELETE FROM acl WHERE gid = $gid AND oid = $oid");
		aw_session_set("__acl_cache", array());
		$c = get_instance("cache");
		$c->file_invalidate_regex("acl-cache(.*)");
	}

	function save_acl($oid,$gid,$aclarr)
	{
		$acl_ids = $this->cfg["acl"]["ids"];
		reset($acl_ids);
		while(list($bitpos,$name) = each($acl_ids))
		{
			if (isset($aclarr[$name]) && $aclarr[$name] == 1)
			{
				$a = $this->cfg["acl"]["allowed"];
			}
			else
			{
				$a = $this->cfg["acl"]["denied"];
			}

			$qstr[] = " ( $a << $bitpos ) ";
		}
		$this->db_query("UPDATE acl SET acl = (".join(" | ",$qstr).") WHERE oid = $oid AND gid = $gid");
		aw_session_set("__acl_cache", array());
		$c = get_instance("cache");
		$c->file_invalidate_regex("acl-cache(.*)");
	}

	////
	// !saves the acl for oid<=>gid relation, but only touches the acls set in mask, leaves others in tact
	function save_acl_masked($oid,$gid,$aclarr,$mask)
	{
		$acl = $this->get_acl_for_oid_gid($oid,$gid);

		$acl_ids = $this->cfg["acl"]["ids"];
		reset($acl_ids);
		while(list($bitpos,$name) = each($acl_ids))
		{
			if (isset($mask[$name]))
			{
				if (isset($aclarr[$name]) && $aclarr[$name] == 1)
				{
					$a = $this->cfg["acl"]["allowed"];
				}
				else
				{
					$a = $this->cfg["acl"]["denied"];
				}
			}
			else
			{
				$a = $acl[$name];
			}

			$qstr[] = " ( $a << $bitpos ) ";
		}
		$this->db_query("UPDATE acl SET acl = (".join(" | ",$qstr).") WHERE oid = $oid AND gid = $gid");
		aw_session_set("__acl_cache", array());
		$c = get_instance("cache");
		$c->file_invalidate_regex("acl-cache(.*)");
	}

	function get_acl_for_oid_gid($oid,$gid)
	{
		if (!$oid || !$gid)
		{
			return;
		}
		$q = "SELECT
						*,
						acl.id as acl_rel_id,
						objects.parent as parent,
						".$this->sql_unpack_string().",
						groups.priority as priority,
						acl.oid as oid
					FROM acl 
						LEFT JOIN groups ON groups.gid = acl.gid
						LEFT JOIN objects ON objects.oid = acl.oid
					WHERE acl.oid = $oid AND acl.gid = $gid
				";
		$this->db_query($q);
		$row = $this->db_next();

		return $row;
	}

	function get_acl_for_oid($oid)
	{
		$g_pris = aw_global_get("gidlist_pri");	// this gets made in users::request_startup

		$max_pri = 0;
		$max_row = array();
		$q = "
			SELECT 
				acl.id as acl_rel_id, 
				objects.parent as parent,
				".$this->sql_unpack_string().",
				objects.oid as oid,
				objects.parent as parent,
				acl.gid as gid 
			FROM 
				objects
				LEFT JOIN acl ON objects.oid = acl.oid
			WHERE 
				objects.oid = $oid AND objects.status != 0
		";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$max_row["oid"] = $row["oid"];
			$max_row["parent"] = $row["parent"];
			if (!$row["gid"] || !isset($g_pris[$row["gid"]]))
			{
				continue;
			}

			if ($g_pris[$row["gid"]] >= $max_pri)
			{
				$max_pri = $g_pris[$row["gid"]];
				$max_row = $row;
				$max_row["priority"] = $max_pri;
			}
		}
		return $max_row;
	}

	function can_aw($access,$oid)
	{
		$o_oid = $oid;

		$access="can_".$access;

		$this->save_handle();

		$max_priority = -1;
		$max_acl = $this->cfg["acl"]["default"];
		$max_acl["acl_rel_id"] = "666";
		$cnt = 0;
		// here we must traverse the tree from $oid to 1, gather all the acls and return the one with the highest priority
		//	echo "entering can, access = $access, oid = $oid<br />";
		while ($oid > 0)
		{
			$_t = aw_cache_get("aclcache",$oid);
			if (is_array($_t))
			{
				$tacl = $_t;
				$parent = $_t["parent"];
			}
			else
			{
				$tacl = $this->get_acl_for_oid($oid);

				if (!isset($tacl["oid"]))
				{
					// if we are on any level and we get back no object, return no access
					// cause then we asked about an object that does not exist or an object that is below a deleted object!
					aw_cache_set("aclcache",$oid,$tacl);
					return array();
				}

				if ($tacl)
				{
					// found acl for this object from the database, so check it
					$parent = $tacl["parent"];
					aw_cache_set("aclcache",$oid,$tacl);
				}
				else
				{
					// no acl for this object in the database, find it's parent
					$parent = $this->db_fetch_field("SELECT parent FROM objects WHERE oid = '$oid'","parent");
					$tacl = array("oid" => $oid,"parent" => $parent,"priority" => -1);
					aw_cache_set("aclcache",$oid,$tacl);
				}
			}

			// now check if we found an acl with a higher priority, than the current one
			// this could be optimized a bit by finding out the highest priority among the groups, the user belongs to
			// and only looping until we find that, but that will not happen too often, since user groups always have the highest priority
			// and access is almost always granted by normal groups, not user groups so it isn't worth it
			if ($tacl["priority"] > $max_priority)
			{
				$max_priority = $tacl["priority"];
				$max_acl = $tacl;
			}
			if (++$cnt > 100)
			{
				$this->raise_error(ERR_ACL_EHIER,"acl_base->can($access,$oid): error in object hierarchy, count exceeded!",true);
			}

			$oid = $parent;
		}

		$this->restore_handle();

		// and now return the highest found
		return $max_acl;
	}

	// black magic follows.
	function can($access, $oid)
	{
		if ($GLOBALS["cfg"]["acl"]["no_check"])
		{
			return true;
		}
		if (!($max_acl = aw_cache_get("__aw_acl_cache", $oid)))
		{
			// try for file cache
			$fn = "acl-cache-".$oid."-uid-".$GLOBALS["__aw_globals"]["uid"];
			$hash = md5($fn);
			$fqfn = $GLOBALS["cfg"]["cache"]["page_cache"]."/".$hash{0}."/".$hash{1}."/".$hash{2}."/".$fn;
			if (file_exists($fqfn))
			{
				include($fqfn);
				aw_cache_set("__aw_acl_cache", $oid, $max_acl);
			}
			else
			{
				$max_acl = $this->can_aw($access,$oid);

				if ($GLOBALS["cfg"]["cache"]["page_cache"] != "")
				{
					$str = "<?php\n";
					$str .= aw_serialize($max_acl, SERIALIZE_PHP_FILE, array("arr_name" => "max_acl"));
					$str .= "?>";

					// make folders if not exist. this is copypaste from cache class, but we can't access that from here. 
					$fname = $GLOBALS["cfg"]["cache"]["page_cache"];

					// make 3-level folder structure
					$fname .= "/".$hash{0};
					if (!is_dir($fname))
					{
						mkdir($fname, 0777);
						chmod($fname, 0777);
					}

					$fname .= "/".$hash{1};
					if (!is_dir($fname))
					{
						mkdir($fname, 0777);
						chmod($fname, 0777);
					}

					$fname .= "/".$hash{2};
					if (!is_dir($fname))
					{
						mkdir($fname, 0777);
						chmod($fname, 0777);
					}

					$fp = fopen($fqfn, "w");
					flock($fp, LOCK_EX);
					fwrite($fp, $str);
					flock($fp, LOCK_UN);
					fclose($fp);
					chmod($fqfn, 0666);
				}

				aw_cache_set("__aw_acl_cache", $oid, $max_acl);
			}
		}

		$access="can_".$access;
		return $max_acl[$access];
	}

	function create_obj_access($oid,$uuid = "")
	{
		if (aw_global_get("__is_install"))
		{
			return;
		}

		if ($uuid == "")
		{
			$uuid = aw_global_get("uid");
		}

		$acl_ids = $this->cfg["acl"]["ids"];

		if ($uuid != "")
		{
			reset($acl_ids);
			$aclarr = array();
			while (list(,$k) = each($acl_ids))
			{
				$aclarr[$k] = $this->cfg["acl"]["allowed"];
			}

			$gr = $this->get_user_group($uuid);
			if (!$gr) 
			{
				$this->raise_error(ERR_ACL_NOGRP,LC_NO_DEFAULT_GROUP,true);
			};
			$this->add_acl_group_to_obj($gr["gid"], $oid, $aclarr, false);
			//$this->save_acl($oid,$gr["gid"], $aclarr);		// give full access to the creator
		}
	}

	////
	// v6tab k6ikide kasutajate grupilt 2ra 6igused sellele objektile
	function deny_obj_access($oid)
	{
		if (aw_global_get("__is_install"))
		{
			return;
		}
		$all_users_grp = aw_ini_get("groups.all_users_grp");
		if (!$all_users_grp)
		{
			return;
		}
		$acl_ids = $this->cfg["acl"]["ids"];

		reset($acl_ids);
		while (list(,$k) = each($acl_ids))
		{
			$aclarr[$k] = $this->cfg["acl"]["denied"];
		}

		// so we wouldn't add the group twice
		$grplist = $this->get_acl_groups_for_obj($oid);
		if (!is_array($grplist[$all_users_grp]))
		{
			$this->add_acl_group_to_obj($all_users_grp, $oid);
		}

		$this->save_acl($oid,$all_users_grp, $aclarr);		// give no access to all users
	}

	////
	// !Wrapper for "prog_acl", used to display the login form if the user is not logged in
	function prog_acl_auth($right,$progid)
	{
		if (aw_global_get("uid") != "")
		{
			return $this->prog_acl($right,$progid);
		}
		else
		{
			// show the login form
			$auth = get_instance("auth");
			print $auth->show_login();
			// dat sucks
			exit;
		}
	}

	////
	// !checks if the user has the $right for program $progid
	function prog_acl($right,$progid)
	{
		if (aw_global_get("uid") == "")
		{
			return aw_ini_get("acl.denied");
		}

		if (aw_ini_get("acl.check_prog") != true)
		{
			return aw_ini_get("acl.allowed");
		}
		else
		{
			// the only program you can ask for is PRG_MENUEDIT 
			error::throw_if($progid != PRG_MENUEDIT, array(
				"id" => ERR_PROG_ACL,
				"msg" => "acl_base::prog_acl($right, $progid): the only program you can get access rights for is PRG_MENUEDIT, all others are deprecated!"
			));

			$can_adm = false;
			$can_adm_max = 0;
			$can_adm_oid = 0;

			$gl = aw_global_get("gidlist_oid");
			foreach($gl as $g_oid)
			{	
				if ($this->can("view", $g_oid))
				{
					$o = obj($g_oid);
					if ($o->prop("priority") > $can_adm_max)
					{
						$can_adm = $o->prop("can_admin_interface");
						$can_adm_max = $o->prop("priority");
						$can_adm_oid = $g_oid;
					}
				}
			}

			if (aw_ini_get("site_id") == 84)
			{
				return $can_adm;
			}

			// ok, if we are returning false, send an error e-mail, so that we can fix the situation
			if (!$can_adm)
			{
				error::throw(array(
					"id" => ERR_NOTICE,
					"msg" => "acl_base::prog_acl($right, $progid): access was denied for user ".aw_global_get("uid").". please verify that the site is configured correctly ($can_adm_oid) gl = ".join(",", array_values($gl))."!",
					"fatal" => false,
					"show" => false
				));
			}

			// FIXME: after I have checked all the relevant checkboxes for all the sites, this should return $can_adm
			return true; //$can_adm;
		};
	}

	////
	// !returns an array of acls in the system as array(bitpos => name)
	function acl_list_acls()
	{
		return $this->cfg["acl"]["ids"];
	}

	function acl_get_acls_for_grp($gid,$min,$num)
	{
		// damn this thing is gonna be fuckin huge
		$ret= array();
		$this->db_query("SELECT objects.name as name,acl.oid as oid, ".$this->sql_unpack_string()."	FROM acl LEFT JOIN objects ON objects.oid = acl.oid WHERE acl.gid = $gid LIMIT $min,$num");
		return $ret;
	}

	function auth_error()
	{
		header ("HTTP/1.1 404 Not Found");
		echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">";
		echo "<html><head>";
		echo "<title>404 Not Found</title>";
		echo "</head><body>";
		echo "<h1>Not Found</h1>";
		echo "The requested URL ".aw_global_get("REQUEST_URI")." ";
		echo "was not found on this server.<p>";
		echo "<hr />";
		echo "<ADDRESS>Apache/1.3.14 Server at ".aw_global_get("HTTP_HOST");
		echo "Port 80</ADDRESS>";
		echo "</body></html>";
		die();
	}

	////
	// !returns all objects that have acl relations set for the groups
	// parameters
	//	grps - array of groups to return
	function acl_get_acls_for_groups($arr)
	{
		extract($arr);
		$gids = join(",", $grps);
		if ($gids == "")
		{
			return array();
		}

		$ret = array();

		$sql = "
			SELECT 
				objects.name as obj_name, 
				objects.oid,
				objects.createdby as createdby,
				objects.parent as obj_parent,
				acl.gid,
				groups.name as grp_name,
				".$this->sql_unpack_string()."
			FROM
				acl
				LEFT JOIN objects ON objects.oid = acl.oid
				LEFT JOIN groups ON groups.gid = acl.gid
			WHERE
				acl.gid IN ($gids)
		";
		$this->db_query($sql);
		while ($row = $this->db_next())
		{
			$ret[] = $row;
		}
		return $ret;
	}

	function init($args = NULL)
	{
		parent::init($args);
	}

	////
	// !returns the default group for the user
	function get_user_group($uid)
	{
		$this->db_query("SELECT * FROM groups WHERE type=1 AND name='$uid'");
		return $this->db_next();
	}
}
?>
