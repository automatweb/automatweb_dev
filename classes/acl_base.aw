<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/acl_base.aw,v 2.14 2002/01/31 00:30:04 kristo Exp $

define("DENIED",0);
define("ALLOWED",1);

$aclcache;	// this is the place, where all the acl will be stored after querying

global $acl_ids;
$acl_ids = array("0" =>	 "can_edit",
								 "2" =>	"can_add",
								 "4" =>	"can_admin",
								 "6" =>  "can_delete",
								 "8" =>	"can_clone",
								 "10" => "can_stat",
								 "12" => "can_view",
								 "14" => "can_fill",
								 "16" => "can_export",
								"18" => "can_import",
								"20" => "can_action",
								"22" => "can_import_styles",
								"24" => "can_import_data",
								"26" => "can_add_output",
								"28" => "can_delegate",
								"30" => "can_export_styles",
								"32" => "can_export_data",
								"34" => "can_view_filled",
								"36" => "can_send",
								"38" => "can_active",
								"40" => "can_periodic",
								"42" => "can_order",
								"44" => "can_copy",
								"46" => "can_view_users",
								"48" => "can_change_users",
								"50" => "can_delete_users",
								"52" => "can_add_users",
								"54" => "can_change_variables",
								"56" => "can_change_variable_acl");

global $acl_default;
$acl_default = array("can_view" => ALLOWED);

lc_load("definition");

classload("core");
class acl_base extends core
{
	function sql_unpack_string()
	{
		// oi kakaja huinja, bljat. 
		// the point is, that php can only handle 32-bit integers, but mysql can handle 64-bit integers
		// and so, we do the packing/unpacking to integer in the database. whoop-e
		global $acl_ids;
		$qstr = array();
		reset($acl_ids);
		while (list($bitpos, $name) = each($acl_ids))
			$qstr[] = " ((acl >> $bitpos) & 3) AS $name";

		$s =  join(",",$qstr);
//		echo $s,"<br><br>";
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
			$ret[$row["gid"]] = $row;

		return $ret;
	}

	function add_acl_group_to_obj($gid,$oid)
	{
		global $acl_default;
		$this->db_query("insert into acl(gid,oid) values($gid,$oid)");
		$this->save_acl($oid,$gid,$acl_default);		// set default acl to the new relation
	}

	function remove_acl_group_from_obj($gid,$oid)
	{
		$this->db_query("DELETE FROM acl WHERE gid = $gid AND oid = $oid");
	}

	function save_acl($oid,$gid,$aclarr)
	{
		global $acl_ids;
		reset($acl_ids);
		while(list($bitpos,$name) = each($acl_ids))
		{
			if (isset($aclarr[$name]) && $aclarr[$name] == 1)
			{
				$a = ALLOWED;
			}
			else
			{
				$a = DENIED;
			}

			$qstr[] = " ( $a << $bitpos ) ";
		}
		$this->db_query("UPDATE acl SET acl = (".join(" | ",$qstr).") WHERE oid = $oid AND gid = $gid");
	}

	////
	// !saves the acl for oid<=>gid relation, but only touches the acls set in mask, leaves others in tact
	function save_acl_masked($oid,$gid,$aclarr,$mask)
	{
		$acl = $this->get_acl_for_oid_gid($oid,$gid);

		global $acl_ids;
		reset($acl_ids);
		while(list($bitpos,$name) = each($acl_ids))
		{
			if (isset($mask[$name]))
			{
				if (isset($aclarr[$name]) && $aclarr[$name] == 1)
				{
					$a = ALLOWED;
				}
				else
				{
					$a = DENIED;
				}
			}
			else
			{
				$a = $acl[$name];
			}

			$qstr[] = " ( $a << $bitpos ) ";
		}
		$this->db_query("UPDATE acl SET acl = (".join(" | ",$qstr).") WHERE oid = $oid AND gid = $gid");
	}

	function get_acl_for_oid_gid($oid,$gid)
	{
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
		global $gidlist;
		// select acl entry for this object, whose group is one of
		// the groups the current user is in 
		// and whose priority is highest
		if (!is_array($gidlist))
		{
//			echo "no gidlist arr<br>";
			return false;
		}
		$gidstr = join(",",$gidlist);
		if ($gidstr == "")
		{
//			echo "gidlist empty <br>";
			return false;
		}
		$q = "SELECT *,acl.id as acl_rel_id, objects.parent as parent,".$this->sql_unpack_string().",groups.priority as priority,acl.oid as oid FROM acl 
										 LEFT JOIN groups ON groups.gid = acl.gid
										 LEFT JOIN objects ON objects.oid = acl.oid
										 WHERE acl.oid = $oid AND acl.gid IN (".$gidstr.") 
										 ORDER BY groups.priority DESC
										 LIMIT 1";
//		echo "q = $q <br>";
		$this->db_query($q);
		$row = $this->db_next();
//		echo "<pre>",var_dump($row),"</pre><Br>";

		return $row;
	}

	function can_aw($access,$oid)
	{
		global $acl_default,$no_check_acl;
		global $SITE_ID,$uid;
		$o_oid = $oid;
		
		global $awt;
		$awt->start("acl::can");
		$awt->count("acl::can");
		$access="can_".$access;

		$this->save_handle();

		$max_priority = -1;
		$max_acl = $acl_default;
		$max_acl["acl_rel_id"] = "666";
		$cnt = 0;
		// here we must traverse the tree from $oid to 1, gather all the acls and return the one with the highest priority
		//	echo "entering can, access = $access, oid = $oid<Br>";
		while ($oid > 0)
		{
			// echo "oid = $oid<br>";
			if (is_array($GLOBALS["aclcache"][$oid]))
			{
				$tacl = $GLOBALS["aclcache"][$oid];
				$parent = $GLOBALS["aclcache"][$oid]["parent"];
				//echo "found in cache! tacl[$access] = ",$tacl[$access], ", parent = $parent<br>";
			}
			else
			{
				//echo "not found in cache!<br>";
				if ($tacl = $this->get_acl_for_oid($oid))
				{
					// found acl for this object from the database, so check it
					$parent = $tacl["parent"];
					//echo "found in db, tacl[$access] = ",$tacl[$access],", parent = $parent<br>";
					$GLOBALS["aclcache"][$oid] = $tacl;
				}
				else
				{
					// no acl for this object in the database, find it's parent
					$parent = $this->db_fetch_field("SELECT parent FROM objects WHERE oid = $oid","parent");
					$tacl = array("oid" => $oid,"parent" => $parent,"priority" => -1);
					$GLOBALS["aclcache"][$oid] = $tacl;
					//echo "not found in db, parent = $parent<br>";
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
				//echo "bigger than max priority (",$tacl[priority],") , setting max<br>";
			}
			// siin oli 100, aga seda on imho ilmselgelt liiga palju
			// 25 peaks vist piisama kyll
			if (++$cnt > 25)
			{
				$this->raise_error("acl_base->can($access,$oid): error in object hierarchy, count exceeded!",true);
			}

			$oid = $parent;
		}

		$this->restore_handle();
		// and now return the highest found
//		return 1;
		
		$awt->stop("acl::can");
		// nini ja nyt kui see on aw.struktuur.ee siis kysime java k2est ka
		return $max_acl;
	}

	function can_server($access,$oid)
	{
		global $acl_default,$no_check_acl;
		global $SITE_ID,$uid;
		$o_oid = $oid;

		global $awt;
		$access = "can_".$access;
		$awt->start("acl::can_server");
		if (!$o_oid)
		{
			dbg("olen real 231 OID=".$o_oid." tagastan false<br>");
			return false;
		}
		dbg("olen real 234 <br>");
		global $acl_server_socket;
		if (!$acl_server_socket)
		{
			dbg("olen real 237 võtan javaga yhendust<br>");
			$awt->start("acl::can_server::connect");
			$acl_server_socket = fsockopen("127.0.0.1", 10000,$errno,$errstr,10);
			$awt->stop("acl::can_server::connect");
		}
		if (!$acl_server_socket)
		{
			echo "ACL: error: $errstr ($errno) <br>\n";
			flush();
		}
		else
		{
			if ($uid == "")
			{
				$u_uid = ",";
			}
			else
			{
				$u_uid = $uid;
			}
			//echo "saadan: -1 ".$SITE_ID." ".$u_uid." ".$o_oid."<br>";
			fputs($acl_server_socket,"-1 ".$SITE_ID." ".$u_uid." ".$o_oid."\n");
			$s_res.=fgets ($acl_server_socket,1000);
			// parsime tulemuse laiali
			$s_rights = explode(",",$s_res);
			foreach($s_rights as $s_line)
			{
				list($s_r_name, $s_r_value) = explode(" ",$s_line);
				$max_acl[$s_r_name] = $s_r_value;
				//echo "got access for $s_r_name as $s_r_value <br>";
			}
	//		echo "out of can() <br>";
		}
		$awt->stop("acl::can_server");

		return $max_acl;
	}

	// black magic follows.
	function can($access, $oid)
	{
		global $no_check_acl,$compare_acls;
		if ($no_check_acl)
		{
			return true;
		}

		$max_acl = array();
		if ((!($GLOBALS["use_acl_server"] && ($GLOBALS["uid"] == "kix" || $GLOBALS["uid"] == "risto"))) || $compare_acls)
		{
			$max_acl = $this->can_aw($access,$oid);
			$cmp_aw = $max_acl;
		}

		if (($GLOBALS["use_acl_server"] && ($GLOBALS["uid"] == "kix" || $GLOBALS["uid"] == "risto")) || $compare_acls)
		{
			$max_acl = $this->can_server($access,$oid);
			$cmp_server = $max_acl;
		}


		if ($compare_acls)
		{
			global $acl_ids;
			foreach($acl_ids as $bp => $aname)
			{
				if (((int)$cmp_aw[$aname]) != ((int)$cmp_server[$aname]))
				{
					echo "erinevus! oid = $oid , access = $aname , aw andis ",((int)$cmp_aw[$aname])," ja server andis ",((int)$cmp_server[$aname]),"<br>\n";
					flush();
				}
			}
		}

		$access="can_".$access;
		return $max_acl[$access];
	}

	// SELECT * FROM objects join(" ",map2("LEFT JOIN %s ON %s",$joins)) WHERE $where
	// this function just caches the results, so that when you list objects, asking their acl is lots faster later
	function listacl($where,$joins = -1)
	{
		$this->save_handle();

		global $gidlist;

		if (!is_array($gidlist) || count($gidlist) < 1)
		{
			return;
		}

		$js = "";
		if (is_array($joins))
			$js = join(' ',$this->map2('LEFT JOIN %s ON %s',$joins));

		// stuff all the objects in the cache, because the next query will not 
		// get a list of objects if they don't have their acl specified
		$q = "SELECT objects.oid as oid, objects.parent as parent FROM objects $js WHERE ($where)";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$row["priority"] = -1;
			$GLOBALS["aclcache"][$row["oid"]] = $row;
			//echo "adding to cache ",$row[oid],"<br>";
		}

		$q = "SELECT objects.parent as parent,".$this->sql_unpack_string().", groups.priority as priority, acl.oid as oid 
		                 FROM objects
										 LEFT JOIN acl on (objects.oid = acl.oid )
										 LEFT JOIN groups ON (groups.gid = acl.gid)
										 $js
										 WHERE ($where) AND acl.gid IN (".join(',',$gidlist).")  
										 ORDER BY acl.oid";
										 //OR isnull(acl.oid)	// seems we don't need these, cause every object is owned by somebody
										 //OR  isnull(acl.gid)// and therefore will have a record in the acl table. 
	//	 echo "query: '$q'<br>";

		$this->db_query($q);
		$currobj = array(); $curr_oid = 0;
		while ($row = $this->db_next())
		{
//			echo "row! <br>";
			// now find all records with the same oid and get the acl with the largest priority
			if ($row["oid"] == $curr_oid)
			{
				$currobj[] = $row;
	//			 echo "same oid ($curr_oid), adding<br>";
			}
			else
			{
		//		 echo "diff oid<br>";
				if ($curr_oid)
				{
			//		 echo "listing same oids to find highest priority<br>";
					$mp = -1;
					reset($currobj);
					while (list(,$v) = each($currobj))
					{
						if ($v["priority"] > $mp)
						{
							$mp = $v["priority"];
							$ma = $v;
				//			 echo "higher priority ($mp), setting new high, ",$v[oid],"<br>";
						}
					}
					$GLOBALS["aclcache"][$ma["oid"]] = $ma;
//				echo "adding to cache ",$ma[oid]," access: '",$ma[can_change],"'<br>";
				}
				$currobj = array();
				$curr_oid = $row["oid"];
				$currobj[] = $row;
			}
		}

		if ($curr_oid)
		{
//			echo "1listing same oids to find highest priority<br>";
			$mp = -1;
			reset($currobj);
			while (list(,$v) = each($currobj))
			{
				if ($v["priority"] > $mp)
				{
					$mp = $v["priority"];
					$ma = $v;
	//				 echo "higher priority ($mp), setting new high<br>";
				}
			}
			$GLOBALS["aclcache"][$ma["oid"]] = $ma;
//			echo "adding to cache ",$ma[oid]," access: '",$ma[can_change],"'<br>";
		}
		$this->restore_handle();
	}

	function create_obj_access($oid,$uuid = "")
	{
		global $uid;
		if ($uuid == "")
			$uuid = $uid;

		global $acl_ids;

		if ($uuid != "")
		{
			reset($acl_ids);
			while (list(,$k) = each($acl_ids))
				$aclarr[$k] = ALLOWED;

			$gr = $this->get_user_group($uuid);
			if (!$gr) 
			{
				$this->raise_error(LC_NO_DEFAULT_GROUP,true);
			};
			$this->add_acl_group_to_obj($gr["gid"], $oid);
			$this->save_acl($oid,$gr["gid"], $aclarr);		// give full access to the creator
		}
	}

	function deny_obj_access($oid)
	{
		// @desc: v6tab k6ikide kasutajate grupilt 2ra 6igused sellele objektile

		global $all_users_grp;
		if (!$all_users_grp)
		{
			return;
		}
		global $acl_ids;

		reset($acl_ids);
		while (list(,$k) = each($acl_ids))
			$aclarr[$k] = DENIED;

		// so we wouldn't add the group twice
		$grplist = $this->get_acl_groups_for_obj($oid);
		if (!is_array($grplist[$all_users_grp]))
			$this->add_acl_group_to_obj($all_users_grp, $oid);

		$this->save_acl($oid,$all_users_grp, $aclarr);		// give no access to all users
	}

	////
	// !checks if the user has the $right for program $progid
	function prog_acl($right,$progid)
	{
		//dbg("nime pikkusex=".strlen(UID)."   nimex on=".defined("UID")."<br>");
		global $prog_cache,$SITE_ID;
		if ((!defined("UID")) or (strlen(UID) == 0))
		{
			return DENIED;
		}
		
		if (isset($GLOBALS["no_check_acl"]) && $GLOBALS["no_check_acl"] == true)
		{
				return ALLOWED;
		}
		else
		{
			if (!is_array($prog_cache))
			{
				classload("config");
				$c = new db_config;
				$prog_cache = unserialize($c->get_simple_config("accessmgr"));
			}
			dbg("olen real 441 ".$right."<br>");
			return $this->can($right,$prog_cache[$progid]);
		};
	}

	////
	// !generates an error message to the user and exits aw
	function prog_acl_error($right,$prog)
	{
		global $programs;
		die("Sorry, but you do not have $right access to program ".$programs[$prog]["name"]."<br>");
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
		global $acl_ids;
		return $acl_ids;
	}

	function acl_get_acls_for_grp($gid,$min,$num)
	{
		// damn this thing is gonna be fuckin huge
		$ret= array();
		$this->db_query("SELECT objects.name as name,acl.oid as oid, ".$this->sql_unpack_string()."	FROM acl LEFT JOIN objects ON objects.oid = acl.oid WHERE acl.gid = $gid LIMIT $min,$num");
		return $ret;
	}
}
?>
