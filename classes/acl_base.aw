<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/acl_base.aw,v 2.47 2004/02/02 19:10:59 kristo Exp $

define("DENIED",0);
define("ALLOWED",1);

lc_load("definition");

classload("db");
class acl_base extends db_connector
{
	function sql_unpack_string()
	{
		// oi kakaja huinja, bljat. 
		// the point is, that php can only handle 32-bit integers, but mysql can handle 64-bit integers
		// and so, we do the packing/unpacking to integer in the database. whoop-e
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

	function add_acl_group_to_obj($gid,$oid)
	{
		$this->db_query("insert into acl(gid,oid) values($gid,$oid)");
		$this->save_acl($oid,$gid,$this->cfg["acl"]["default"]);		// set default acl to the new relation
		aw_session_set("__acl_cache", array());
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
		$gidlist = aw_global_get("gidlist");
		// select acl entry for this object, whose group is one of
		// the groups the current user is in 
		// and whose priority is highest
		if (!is_array($gidlist))
		{
			return false;
		}
		$gidstr = join(",",$gidlist);
		if ($gidstr == "")
		{
			return false;
		}
		// previously this query was:
		/*		$q = "SELECT *,acl.id as acl_rel_id, objects.parent as parent,".$this->sql_unpack_string().",groups.priority as priority,acl.oid as oid FROM acl 
										 LEFT JOIN groups ON groups.gid = acl.gid
										 LEFT JOIN objects ON objects.oid = acl.oid
										 WHERE acl.oid = $oid AND acl.gid IN (".$gidstr.") 
										 ORDER BY groups.priority DESC
										 LIMIT 1";*/
		// it returned just one row - the correct one
		// but it was slow - created a temp table and caused a table rescan (using filesort and using temporary)
		//
		// so we remove the groups join and do the sort in memory
		// we have a list of all groups that the user belongs to with their priorites
		// and we return the one from the list with the bigest priority

		$g_pris = aw_global_get("gidlist_pri");	// this gets made in users::request_startup

		$max_pri = 0;
		$max_row = array();
		$q = "SELECT *,acl.id as acl_rel_id, objects.parent as parent,".$this->sql_unpack_string().",acl.oid as oid,acl.gid as gid FROM acl 
										 LEFT JOIN objects ON objects.oid = acl.oid
										 WHERE acl.oid = $oid AND acl.gid IN (".$gidstr.") ";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			if ($g_pris[$row["gid"]] >= $max_pri)
			{
				$max_pri = $g_pris[$row["gid"]];
				$max_row = $row;
				$max_row["priority"] = $max_pri;
			}
		}
		unset($max_row['metadata']);	// no need to save this in the cache, it just uses up unnecessary space.
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
//			echo "oid = $oid<br />";
			$_t = aw_cache_get("aclcache",$oid);
			if (is_array($_t))
			{
				$tacl = $_t;
				$parent = $_t["parent"];
//				echo "found in cache! tacl[$access] = ",$tacl[$access], ", parent = $parent<br />";
			}
			else
			{
//				echo "not found in cache!<br />";
				if ($tacl = $this->get_acl_for_oid($oid))
				{
					// found acl for this object from the database, so check it
					$parent = $tacl["parent"];
//					echo "found in db, tacl[$access] = ",$tacl[$access],", parent = $parent<br />";
					aw_cache_set("aclcache",$oid,$tacl);
				}
				else
				{
					// no acl for this object in the database, find it's parent
					$parent = $this->db_fetch_field("SELECT parent FROM objects WHERE oid = $oid","parent");
					$tacl = array("oid" => $oid,"parent" => $parent,"priority" => -1);
					aw_cache_set("aclcache",$oid,$tacl);
//					echo "not found in db, parent = $parent<br />";
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
//				echo "bigger than max priority (",$tacl[priority],") , setting max<br />";
			}
			// siin oli 100, aga seda on imho ilmselgelt liiga palju
			// 25 peaks vist piisama kyll
			if (++$cnt > 100)
			{
				$this->raise_error(ERR_ACL_EHIER,"acl_base->can($access,$oid): error in object hierarchy, count exceeded!",true);
			}

			$oid = $parent;
		}

		$this->restore_handle();
		// and now return the highest found
//		return 1;

		// nini ja nyt kui see on aw.struktuur.ee siis kysime java k2est ka
//		echo "returning from can_aw , oid = $o_oid , result = <pre>", var_dump($max_acl),"</pre> <br />";
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
			$fqfn = $GLOBALS["cfg"]["cache"]["page_cache"]."/acl-cache-".$oid."-uid-".$GLOBALS["__aw_globals"]["uid"];
			if (file_exists($fqfn))
			{
//				echo "acl cache hit file $oid ($fqfn) <br>";
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
				
					$fp = fopen($fqfn, "w");
					flock($fp, LOCK_EX);
					fwrite($fp, $str);
					flock($fp, LOCK_UN);
					fclose($fp);
				}

				aw_cache_set("__aw_acl_cache", $oid, $max_acl);
//				echo "acl cache miss, set file to $fqfn <br>";
			}
		}
/*		else
		{
			echo "acl cache hit memory $oid <Br>";
		}*/

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
			while (list(,$k) = each($acl_ids))
			{
				$aclarr[$k] = $this->cfg["acl"]["allowed"];
			}

			$gr = $this->get_user_group($uuid);
			if (!$gr) 
			{
				$this->raise_error(ERR_ACL_NOGRP,LC_NO_DEFAULT_GROUP,true);
			};
			$this->add_acl_group_to_obj($gr["gid"], $oid);
			$this->save_acl($oid,$gr["gid"], $aclarr);		// give full access to the creator
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
			$prog_cache = aw_global_get("prog_cache");
			if (!is_array($prog_cache))
			{
				$c = get_instance("config");
				$prog_cache = aw_unserialize($c->get_simple_config("accessmgr"));
				aw_global_set("prog_cache",$prog_cache);
			}
			return $this->can($right,$prog_cache[$progid]);
		};
	}

	////
	// !generates an error message to the user and exits aw
	function prog_acl_error($right,$prog)
	{
		die("Sorry, but you do not have $right access to program ".$this->cfg["programs"][$prog]["name"]."<br />");
	}

	function check_environment(&$sys, $fix = false)
	{
		$op_table = array(
			"name" => "acl", 
			"fields" => array(
				"id" => array("name" => "id", "length" => 11, "type" => "int", "flags" => ""),
				"gid" => array("name" => "gid", "length" => 11, "type" => "int", "flags" => ""),
				"oid" => array("name" => "oid", "length" => 11, "type" => "int", "flags" => ""),
				"acl" => array("name" => "acl", "length" => 20, "type" => "int", "flags" => "")
			)
		);

		$ret= $sys->check_db_tables(array($op_table),$fix);
		return $ret;
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
