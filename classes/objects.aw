<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/objects.aw,v 2.20 2001/10/22 05:09:59 kristo Exp $
// objects.aw - objektide haldamisega seotud funktsioonid

global $orb_defs;
$orb_defs["objects"] = "xml";

class db_objects extends aw_template 
{
	function db_objects() 
	{
		$this->db_init();
		$this->tpl_init();
		$this->typearr = array(CL_FORM,CL_IMAGE,CL_FORM_ENTRY,CL_GRAPH,CL_GALLERY,CL_TABLE,CL_FILE,CL_FORM_CHAIN,CL_EXTLINK);		
		$this->typearr2 = array(CL_PSEUDO,CL_FORM,CL_IMAGE,CL_FORM_ENTRY,CL_GRAPH,CL_GALLERY,CL_TABLE,CL_FILE,CL_FORM_CHAIN,CL_EXTLINK);	
	lc_load("definition");
	}

	function browser($args = array())
	{
		extract($args);
		$this->tpl_init("objects");
		load_vcl("html_frameset");
		$retval = "";
		global $baseurl;
		switch($type)
		{
			case "top":
				$retval .= sprintf(LC_OBJECTS_OBJECTS_CAL,"$baseurl/?class=objects&action=browser&type=middle&msgid=$msgid' target='content'","$baseurl/?class=planner&action=draw_day&msgid=$msgid' target='content'");

				break;

			case "middle":
				$frames = array(
					"mleft" => "$baseurl/?class=objects&action=browser&type=content&msgid=$msgid",
					"mright" => "$baseurl/?class=objects&action=browser&type=search&msgid=$msgid",
				);
				
				$frameset = new html_frameset(array(
					"cols" => "60%,40%",
					"rows" => "*",
					"frames" => $frames,
				));
				$retval = $frameset->generate();

				break;

			case "content":
				global $udata;
				$this->read_template("homedir.tpl");
				$prnt = ($parent) ? $parent : $udata["home_folder"];
				$this->get_objects(array(
						"parent" => $prnt,
						"class_id" => array(CL_PSEUDO,CL_FILE),
					));
				$c = "";
				$this->vars(array(
						"msgid" => $msgid,
				));
				while($row = $this->db_next())
				{

					$this->vars(array(
							"name" => $row["name"],
							"oid" => $row["oid"],
							"icon" => get_icon_url($row["class_id"],$row["name"]),
					));
					
					$tpl = ($row["class_id"] == CL_PSEUDO) ? "line" : "object";
					$c .= $this->parse($tpl);
				};

				$chain = $this->get_obj_chain(array(
							"oid" => $prnt,
							"stop" => $udata["home_folder"],
						));

				$fullpath = map2("<a href='$baseurl/?class=objects&action=browser&type=content&msgid=$msgid&parent=%s'>%s</a>",$chain);
			        $fullpath = join(" &gt; " ,array_reverse($fullpath));

				$this->vars(array(
						"fullpath" => $fullpath,
						"line" => $c,
						"reforb" => $this->mk_reforb("submit_hd",array()),
				));
				$retval = $this->parse();
				break;

			case "search":
				$this->read_template("search.tpl");
				$flist = $this->gen_folders();
				$this->vars(array(
						"folders" => $this->multiple_option_list($flist,$flist),
						"reforb" => $this->mk_reforb("hf_search",array("msgid" => $msgid)),
						));
				$retval = $this->parse();
				break;

			case "bottom":
				$this->read_template("bottom.tpl");
				$retval = $this->parse();
				break;

			default:
				global $baseurl;
				$frames = array(
					"test1" => "$baseurl/?class=objects&action=browser&type=top&msgid=$msgid",
					"content" => "$baseurl/?class=objects&action=browser&type=middle&msgid=$msgid",
					"test3" => "$baseurl/?class=objects&action=browser&type=bottom",
				);
				
				$frameset = new html_frameset(array(
					"cols" => "*",
					"rows" => "10%,80%,10%",
					"frames" => $frames,
				));

				$retval = $frameset->generate();
				
		}
		print $retval;
		exit;
	}

	function submit_hd($args = array())
	{
		extract($args);
		if (is_array($check))
		{
			classload("file");
			$awf = new file();
			// kopeerime koik lisatud objektid teate juurde
			foreach($check as $id)
			{
				$awf->cp(array("id" => $id,"parent" => $msgid));
			}
		};
		print "<script language='javascript'>parent.close();</script>";
		exit;
	}

	function hf_search($args = array())
	{
		$this->tpl_init("objects");
		extract($args);
		if (!is_array($folders))
		{
			$retval = $this->mk_site_orb(array("action" => "browser","type" => "search"));
			return $retval;
		};
		$flist = $this->gen_folders(array("sq" => 0));
		$this->read_template("searchresults.tpl");
		$maps = sprintf("(%s)",join(",",$folders));
		$cid = sprintf("(%s)",join(",",array(CL_FILE)));
		$q = "SELECT * FROM objects WHERE parent IN $maps AND name LIKE '%$search%' AND class_id IN $cid ORDER BY parent";
		$this->db_query($q);
		$lastparent = -1;
		$c = "";
		$cnt = 0;
		while($row = $this->db_next())
		{
			$cnt++;
			if ($lastparent != $row["parent"])
			{
				$this->vars(array("name" => $flist[$row["parent"]]));
				$c .= $this->parse("line");
			};

			$lastparent = $row["parent"];
			$this->vars(array(
					"name" => $row["name"],
					"oid" => $row["oid"],
					"icon" => get_icon_url($row["class_id"],$row["name"]),
			));
			$c .= $this->parse("object");
		};
		$this->vars(array(
				"line" => $c,
				"reforb" => $this->mk_reforb("submit_hd",array("msgid" => $msgid)),
		));
		$retval = $this->parse();
		print $retval;
		exit;
	}

	function gen_folders($args = array())
	{
		extract($args);
		classload("menuedit_light");
		$mnl = new menuedit_light();
		global $udata;
		$flist = $mnl->gen_rec_list(array(
				"start_from" => $udata["home_folder"],
				"add_start_from" => true,
				"sq" => $sq,
			));
		return $flist;
	}



	function search_objs($docid)
	{
		$this->tpl_init("automatweb/objects");
		$this->read_template("search_doc.tpl");
		global $s_name, $s_comment,$s_type,$SITE_ID;
		if ($s_name != "" || $s_comment != "" || $s_type > 0)
		{
			$se = array();
			if ($s_name != "")
			{
				$se[] = " objects.name LIKE '%".$s_name."%' ";
			}
			if ($s_comment != "")
			{
				$se[] = " objects.comment LIKE '%".$s_comment."%' ";
			}
			if ($s_type > 0)
			{
				$se[] = " objects.class_id = '".$s_type."' ";
			}
			else
			{
				$se[] = " objects.class_id IN (".join(",",$this->typearr).") ";
			}
			$this->db_query("SELECT objects.name as name,objects.oid as oid,objects.class_id as class_id,objects.created as created,objects.createdby as createdby,objects.modified as modified,objects.modifiedby as modifiedby,pobjs.name as parent_name FROM objects, objects AS pobjs WHERE pobjs.oid = objects.parent AND objects.status != 0 AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) AND ".join("AND",$se));
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"], 
					"id" => $row["oid"],
					"type"	=> $GLOBALS["class_defs"][$row["class_id"]]["name"],
					"created" => $this->time2date($row["created"],2),
					"modified" => $this->time2date($row["modified"], 2),
					"createdby" => $row["createdby"],
					"modifiedby" => $row["modifiedby"],
					"parent_name" => $row["parent_name"],
					"pickurl" => (in_array($row["class_id"],$this->typearr) ? "<a href='".$this->mk_orb("addalias",array("id" => $docid, "alias" => $row["oid"]),"document")."'>Pick this</a>" : "")));
				$l.=$this->parse("LINE");
			}
			$this->vars(array("LINE" => $l));
		}
		else
		{
			$s_name = "%";
			$s_comment = "%";
			$s_type = 0;
		}
		$tar = array(0 => LC_OBJECTS_ALL);
		reset($this->typearr);
		while (list(,$v) = each($this->typearr))
		{
			$tar[$v] = $GLOBALS["class_defs"][$v]["name"];
		}
		$this->vars(array("docid" => $docid,
											"s_name"	=> $s_name,
											"s_type"	=> $s_type,
											"s_comment"	=> $s_comment,
											"types"	=> $this->picker($s_type, $tar)));
		return $this->parse();
	}

	////
	// !kasutatakse dokude juurde aliaste lisamiseks
	function gen_pickable_list($parent,$docid,&$mstring) 
	{
		global $PHP_SELF;
		global $ext;
		$this->tpl_init("automatweb/objects");
		if ($parent > 0) 
		{
			$parentlist = $this->get_object_chain($parent,true);
			while(list($p_oid,$p_cap) = each($parentlist)) 
			{
				if ($p_oid == $parent) 
				{
					$mmap[] = $p_cap["name"];
				} 
				else 
				{
					$mmap[] = sprintf("<a href='%s?docid=%d&parent=%d'>%s</a>",$PHP_SELF,$docid,$p_oid,$p_cap["name"]);
				};
			};
			$mstring = join(" &gt; ",array_reverse($mmap));
		};

		$this->read_template("pick.tpl");
		$this->vars(array("search" => "pickobject.".$GLOBALS["ext"]."?type=search&docid=".$docid));
		$this->listall_types($parent,$this->typearr2);
		$lines = "";
		$count = 0;
		while($row = $this->db_next()) 
		{
			$count++;
			$this->vars(array("oid" => $row[oid]));
			extract($row);
			// saveme handle, sest count_by_parent vajab handlerit
      $this->save_handle();
			$subs = $this->count_by_parent($oid,$this->typearr2);
      $this->restore_handle();
			// kui selle objekti all on veel elemente, siis saab expandida
			if ($subs > 0) 
			{
				$expandurl = "<a href='$PHP_SELF?parent=$oid&docid=$docid'><b>+</b></a> ($subs)";
			} 
			else 
			{
				$expandurl = "($subs)";
			};
			$this->vars(array("oid" => $oid,
						"parent"				=> $parent,
						"name"					=> $name,
						"rec"						=> $count,
						"modifier"			=> $modifiers[$class_id],
						"modifiedby"		=> $row[modifiedby],
						"created"				=> $this->time2date($created),
						"modified"			=> $this->time2date($modified),
						"class"					=> $GLOBALS["class_defs"][$class_id][name],
						"docid"					=> $docid,
						"expandurl"     => $expandurl,
						"pickurl"				=> (in_array($class_id,$this->typearr) ? "<a href='".$this->mk_orb("addalias",array("id" => $docid, "alias" => $oid),"document")."'>Võta see</a>" : "")));
			$lines .= $this->parse("line");
		};
		$this->vars(array(
			"line"    => $lines,
		  "total"   => verbalize_number($count),
      "parent"  => $parent,
		  "message" => $message));
    return $this->parse();
	}

	function mkah(&$arr, &$ret,$parent,$prefix)
	{
		if (!is_array($arr[$parent]))
		{
			return;
		}

		reset($arr[$parent]);
		while (list(,$v) = each($arr[$parent]))
		{
			$name = $prefix == "" ? $v["name"] : $prefix."/".$v["name"];
			$ret[$v["oid"]] = $name;
			$this->mkah(&$arr,&$ret,$v["oid"],$name);
		}
	}

	////
	// !Genereerib mingit klassi objektide nimekirja, rekursiivselt alates $start_from-ist
	// Eeliseks järgneva funktsiooni ees on see, et ei loeta koiki menüüsid sisse
	// see versioon ei prindi objektide nimekirja v2lja ka. mix seda yldse vaja oli printida?!?
	// ja tagastatav array on kujul array($oid => $row)
	function gen_rec_list_noprint($args = array())
	{
		extract($args);
		// vaatame ainult seda tüüpi objekte
		$this->class_id = 1;
		$this->spacer = 0;
		// moodustame 2mootmelise array koigist objektidest
		// parent -> child1,(child2,...childn)
		$this->rec_list = array(); // siia satuvad koik need objektid
		$this->no_parent_rel = true;
		$this->_gen_rec_list(array("$start_from"));
		return $this->rec_list;
	}

	////
	// !Rekursiivne funktsioon, kutsutakse välja gen_rec_list seest
	function _gen_rec_list($parents = array())
	{
		$this->save_handle();
		$plist = join(",",$parents);
		if($plist == "")
		{
			$this->restore_handle();
			return;
		}
		$q = sprintf("SELECT * FROM objects WHERE class_id = '%d' AND parent IN (%s)",
				$this->class_id,
				$plist);
		$this->db_query($q);
		$_parents = array();
		while($row = $this->db_next())
		{
			$_parents[] = $row["oid"];
			if ($this->no_parent_rel)
			{
				$this->rec_list[$row["oid"]] = $row;
			}
			else
			{
				$this->rec_list[$row["parent"]][$row["oid"]] = $row;
			}
		};
		if (sizeof($_parents) > 0)
		{
			$this->_gen_rec_list($_parents);
		};
		$this->restore_handle();
	}

	////
	// !teeb objektide nimekirja ja tagastab selle arrays, sobiv picker() funxioonile ette andmisex
	// ignore_langmenus = kui sait on mitme keelne ja on const.aw sees on $lang_menus = true kribatud
	// siis kui see parameeter on false siis loetaxe aint aktiivse kelle menyyd

	// empty - kui see on true, siis pannaxe k6ige esimesex arrays tyhi element
	// (see on muiltiple select boxide jaoks abix)

	// rootobj - mis objektist alustame
	function get_list($ignore_langmenus = false,$empty = false,$rootobj = -1) 
	{
		global $awt;
		$awt->start("objects::get_list");
		$awt->count("objects::get_list");

		if (!$ignore_langmenus)
		{
			if ($GLOBALS["lang_menus"] == 1)
			{
				$aa = " AND (objects.lang_id = ".$GLOBALS["lang_id"]." OR menu.type = 69)";
			}
		}
		$this->db_query("SELECT objects.oid as oid, 
														objects.parent as parent,
														objects.name as name
											FROM objects 
											LEFT JOIN menu ON menu.id = objects.oid
											WHERE objects.class_id = 1 AND objects.status != 0 $aa
											GROUP BY objects.oid
											ORDER BY objects.parent, menu.is_l3,jrk");
		while ($row = $this->db_next())
		{
			$ret[$row["parent"]][] = $row;
		}

		$tt = array();
		if ($empty)
		{
			$tt[] = "";
		}
		global $admin_rootmenu;
		if ($rootobj == -1)
		{
			$rootobj = $admin_rootmenu;
		}
		$this->mkah(&$ret,&$tt,$rootobj,"");

		if ($rootobj == $admin_rootmenu)
		{
			$hf = $this->db_fetch_field("SELECT home_folder FROM users WHERE uid = '".$GLOBALS["uid"]."'","home_folder");
			$this->mkah(&$ret,&$tt,$hf,$GLOBALS["uid"]);
		}

		$awt->stop("objects::get_list");
		return $tt;
	}
	
	function count_by_parent($parent,$typearr = "") 
	{
		if (is_array($typearr))
		{
			$typestr = "AND class_id IN (".join(",",$typearr).") ";
		}
		else
		{
			$typestr = "";
		}
		$q = "SELECT count(*) as cnt
			FROM objects
			WHERE parent = '$parent' $typestr";
		return $this->db_fetch_field($q,"cnt");
	}

	function listall_types($parent,$typearr)
	{
		$tstr = join(",", $typearr);
		$this->db_query("SELECT * FROM objects WHERE parent = $parent AND class_id IN ($tstr) ");
	}
};


class objects extends db_objects
{
	function objects()
	{
		$this->db_objects();
	}

	function search($arr)
	{
		$this->tpl_init("automatweb/objects");
		$this->read_template("search.tpl");
		global $s,$SITE_ID,$class_defs;

		$numfields = array("parent" => 1,"class_id" => 1,"created" => 1,"modified" => 1 ,"status" => 1);
		$found = false;
		$se = array();
		if (is_array($s))
		{
			reset($s);
			while (list($k,$v) = each($s))
			{
				if ($v != "" || ($numfields[$k] && $v > 0))
				{
					$found = true;
					if ($k == "active" && $v == 1)
					{
						$se[] = " objects.status = 2 ";
					}
					else
					if ($numfields[$k])
					{
						if ($v > 0)
						{
							$se[] = " objects.$k = '".$v."' ";
						}
					}
					else
					{
						if ($v != "%")
						{
							$se[] = " objects.$k LIKE '%".$v."%' ";
						}
					}
				}
			}
		}
		if ($found)
		{
			$ses = join("AND",$se);
			if ($ses != "")
			{
				$ses="AND ".$ses;
			}
			$this->db_query("SELECT objects.*,o_p.name as parent_name,o_p_p.name as parent_parent_name,o_p_p_p.name as parent_parent_parent_name FROM objects, objects as o_p,objects as o_p_p,objects as o_p_p_p  WHERE o_p.oid = objects.parent AND o_p_p.oid = o_p.parent AND o_p_p_p.oid = o_p_p.parent AND objects.status != 0 AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) $ses");
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"], 
					"type"	=> $GLOBALS["class_defs"][$row["class_id"]]["name"],
					"change" => $this->mk_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $class_defs[$row["class_id"]]["file"]),
					"created" => $this->time2date($row["created"], 2),
					"modified" => $this->time2date($row["modified"], 2),
					"createdby" => $row["createdby"],
					"modifiedby" => $row["modifiedby"],
					"parent_name" => $row["parent_name"],
					"oid" => $row["oid"],
					"class_id" => $row["class_id"],
					"parent_parent_name" => $row["parent_parent_name"],
					"parent_parent_parent_name" => $row["parent_parent_parent_name"]
				));
				$l.=$this->parse("LINE");
			}
			$this->vars(array("LINE" => $l));
		}
		else
		{
			$s["name"] = "%";
			$s["comment"] = "%";
			$s["type"] = 0;
		}
		$tar = array(0 => LC_OBJECTS_ALL);
		global $class_defs;
		reset($class_defs);
		while (list($v,) = each($class_defs))
		{
			$tar[$v] = $GLOBALS["class_defs"][$v]["name"];
		}
		classload("users");
		$u = new users;
		$uids = $u->listall_acl();
		$uids[""] = "";

		classload("objects");
		$ob = new db_objects;

		$li = $this->get_list(false,true);
		$li[1] = "Root";
		$this->vars(array(
			"s_name"	=> $s["name"],
			"s_comment"	=> $s["comment"],
			"types"	=> $this->picker($s["class_id"], $tar),
			"parents" => $this->picker($s["parent"],$li),
			"createdby" => $this->picker($s["createdby"],$uids),
			"modifiedby" => $this->picker($s["modifiedby"],$uids),
			"active"	=> checked($s["active"]),
			"alias"		=> $s["alias"],
			"reforb" => $this->mk_reforb("search_submit", array()),
			"moveto" => $this->picker(0,$ob->get_list(false,true))
		));
		return $this->parse();
	}

	function search_submit($arr)
	{
		extract($arr);

		// here we must move the selected objects and change names
		if (is_array($old_text))
		{
			foreach($old_text as $oid => $name)
			{
				if ($text[$oid] != $name)
				{
					$this->upd_object(array("oid" => $oid, "name" => $text[$oid]));
					if ($class_id[$oid] == CL_FORM_ELEMENT)
					{
						$this->db_query("SELECT * FROM element2form WHERE el_id = ".$oid);
						while ($drow = $this->db_next())
						{
							$fup = new form;
							$fup->load($drow["form_id"]);
							for ($row = 0;$row < $fup->arr["rows"]; $row++)
							{
								for ($col = 0; $col < $fup->arr["cols"]; $col++)
								{
									if (is_array($fup->arr["elements"][$row][$col]))
									{
										foreach($fup->arr["elements"][$row][$col] as $k => $v)
										{
											if ($k == $oid)
											{
												$fup->arr["elements"][$row][$col][$k]["name"] = $name;
											}
										}
									}
								}
							}
							$fup->save();
						}
					}
				}
			}
		}

		if (is_array($sel) && $moveto != 0)
		{
			foreach($sel as $oid => $one)
			{
				$this->upd_object(array("oid" => $oid, "parent" => $moveto));
			}
		}

		if (is_array($sel) && $delete != "")
		{
			foreach($sel as $oid => $one)
			{
				if ($one == 1)
				{
					$this->delete_object($oid);
				}
			}
		}

		// just in case if any menus were deleted or changed - I thought it would be too expensive to put checks in 
		// core::delete_object and core::upd_object to check if menus were changed and then flush the cache 
		// - yeah, that would be a lot safer, but is it really necessary?
		$cache = new cache;
		$cache->db_invalidate("menuedit::menu_cache");

		return $this->mk_my_orb("search", array("s[name]" => $s["name"],"s[comment]" => $s["comment"],"s[class_id]" => $s["class_id"],"s[parent]" => $s["parent"],"s[createdby]" => $s["createdby"], "s[modifiedby]" => $s["modifiedby"], "s[active]" => $s["active"], "s[alias]" => $s["alias"]));
	}
}

?>