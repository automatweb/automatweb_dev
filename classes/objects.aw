<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/objects.aw,v 2.31 2002/07/11 20:09:28 duke Exp $
// objects.aw - objektide haldamisega seotud funktsioonid
classload("cache");
class db_objects extends aw_template 
{
	function db_objects() 
	{
		$this->init("");
		lc_load("definition");
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
		$udata = $this->get_user();
		$flist = $mnl->gen_rec_list(array(
			"start_from" => $udata["home_folder"],
			"add_start_from" => true,
			"sq" => $sq,
		));
		return $flist;
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
		$admin_rootmenu = $this->cfg["admin_rootmenu2"];

		$cf_name = "objects::get_list::ign::".((int)$ignore_langmenus)."::empty::".((int)$ignore_langmenus)."::rootobj::".$rootobj;
		$cf_name.= "::adminroot::".$admin_rootmenu."::uid::".aw_global_get("uid");

		if (!$ignore_langmenus)
		{
			if ($this->cfg["lang_menus"] == 1)
			{
				$aa = " AND (objects.lang_id = ".aw_global_get("lang_id")." OR menu.type = 69)";
				$cf_name.="::lm::1::lang_id::".aw_global_get("lang_id");
			}
		}

		// 1st memory cache
		if (($ret = aw_global_get($cf_name)))
		{
			return $ret;
		}

		// then disk cache
		$cache = new cache;
		if (($cont = $cache->file_get($cf_name)))
		{
			$dat = aw_unserialize($cont);
			aw_global_set($cf_name, $dat);
			return $dat;
		}
		
		// and finally, the database
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
		if ($rootobj == -1)
		{
			$rootobj = $admin_rootmenu;
		}
		$this->mkah(&$ret,&$tt,$rootobj,"");

		if ($rootobj == $admin_rootmenu)
		{
			$hf = $this->db_fetch_field("SELECT home_folder FROM users WHERE uid = '".aw_global_get("uid")."'","home_folder");
			$hf_name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = '$hf'","name");
			// but we must also add the home folder itself!
			$tt[$hf] = $hf_name;
			$this->mkah(&$ret,&$tt,$hf,aw_global_get("uid"));
		}

		$cache->file_set($cf_name,aw_serialize($tt));
		aw_global_set($cf_name, $tt);

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

	////
	// !Object search
	// otype(int) - allow to search only for a single object type
	// one(int) - use picker template (search_one) 
	function search($arr)
	{
		$this->tpl_init("automatweb/objects");
		$this->sub_merge = 1;
		extract($arr);

		// search types:
		// 0 or not set	: the usual object search
		// 1 : called from messenger to search for objects to attach
		// the only difference between right now is a different template
		if ($arr["stype"] == 1)
		{
			$this->read_template("search_messenger.tpl");
			// target specifies the message to which the objects should be attached
			$this->target = $arr["target"];
		}
		else
		{
			if ($one)
			{
				$prnt_url = $this->mk_my_orb("login_menus",array(),"config");
				$this->mk_path(0,"<a href='$prnt_url'>Action menüüd</a> / Vali menüü");
				$tpl = "search_one.tpl";
			}
			else
			{
				$tpl = "search.tpl";
			};

			$this->read_template($tpl);
		};

		// FIXME: loeb globaalsest skoobist parameetreid (NB! need on arrayd)
		global $s;

		$SITE_ID = $this->cfg["site_id"];

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
					"type"	=> $this->cfg["classes"][$row["class_id"]]["name"],
					"change" => $this->mk_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $this->cfg["classes"][$row["class_id"]]["file"]),
					"created" => $this->time2date($row["created"], 2),
					"modified" => $this->time2date($row["modified"], 2),
					"createdby" => $row["createdby"],
					"modifiedby" => $row["modifiedby"],
					"parent_name" => $row["parent_name"],
					"oid" => $row["oid"],
					"class_id" => $row["class_id"],
					"pick" => urldecode($return_url) . "&pick=$row[oid]",
					"parent_parent_name" => $row["parent_parent_name"],
					"parent_parent_parent_name" => $row["parent_parent_parent_name"]
				));
				$l.=$this->parse("LINE");
			}
		
			classload("objects");
			$ob = new db_objects;


			$this->vars(array(
				"LINE" => $l,
				"moveto" => $this->picker(0,$ob->get_list(false,true)),
			));
			$this->parse("FOUND");
		}
		else
		{
			$s["name"] = "%";
			$s["comment"] = "%";
			$s["type"] = 0;
		}

		if ($otype)
		{
			$tar[$otype] = $this->cfg["classes"][$otype]["name"];
		}
		else
		{
			$tar = array(0 => " " . LC_OBJECTS_ALL);
			reset($this->cfg["classes"]);
			while (list($v,) = each($this->cfg["classes"]))
			{
				$tar[$v] = $this->cfg["classes"][$v]["name"];
			}
		};

		// sort type list by name
		asort($tar);

		classload("users");
		$u = new users;
		$uids = $u->listall_acl();
		$uids[""] = "";


		$li = $this->get_list(false,true);
		//$li[1] = "Root";
		$this->vars(array(
			"s_name"	=> $s["name"],
			"s_comment"	=> $s["comment"],
			"types"	=> $this->picker($s["class_id"], $tar),
			"parents" => $this->picker($s["parent"],$li),
			"createdby" => $this->picker($s["createdby"],$uids),
			"modifiedby" => $this->picker($s["modifiedby"],$uids),
			"active"	=> checked($s["active"]),
			"alias"		=> $s["alias"],
			"reforb" => $this->mk_reforb("search_submit", array("stype" => $stype,"target" => $target,"otype" => $otype,"one" => $one,"return_url" => urlencode($return_url))),
		));
		return $this->parse();
	}

	function search_submit($arr)
	{
		extract($arr);

		if ($attach)
		{
			$this->_attach_objects($arr);
			//print "<script>window.close();</script>";
			exit;
		};

		$updmenus = array();
		// Renaming ...
		if (is_array($old_text))
		{
			foreach($old_text as $oid => $name)
			{
				if ($text[$oid] != $name)
				{
					$this->upd_object(array("oid" => $oid, "name" => $text[$oid]));
					
					// form elements get special handling
					if ($class_id[$oid] == CL_FORM_ELEMENT)
					{
						$this->_search_rename_form_element($oid,$name);
					}
					else
					if ($class_id[$oid] == CL_PSEUDO)
					{
						$updmenus[] = $oid;
					}
				}
			}
		}

		// ..moving..
		if (is_array($sel) && $moveto != 0)
		{
			foreach($sel as $oid => $one)
			{
				$this->upd_object(array("oid" => $oid, "parent" => $moveto));
				if ($class_id[$oid] == CL_PSEUDO)
				{
					$updmenus[] = $oid;
				}
			}
		}

		// ..and deleting..
		if (is_array($sel) && $delete != "")
		{
			foreach($sel as $oid => $one)
			{
				if ($one == 1)
				{
					$this->delete_object($oid);
					if ($class_id[$oid] == CL_PSEUDO)
					{
						$updmenus[] = $oid;
					}
				}
			}
		}

		// just in case if any menus were deleted or changed - I thought it would be too expensive to put checks in 
		// core::delete_object and core::upd_object to check if menus were changed and then flush the cache 
		// - yeah, that would be a lot safer, but is it really necessary?
		classload("menuedit");
		$m = new menuedit;
		$m->invalidate_menu_cache($updmenus);

		return $this->mk_my_orb("search", array("s[name]" => $s["name"],"s[comment]" => $s["comment"],"s[class_id]" => $s["class_id"],"s[parent]" => $s["parent"],"s[createdby]" => $s["createdby"], "s[modifiedby]" => $s["modifiedby"], "s[active]" => $s["active"], "s[alias]" => $s["alias"],"stype" => $stype,"target" => $target,"otype" => $otype,"one" => $one,"return_url" => urlencode($return_url)));
	}
						
	function _search_rename_form_element($oid,$name)
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

	function _attach_objects($args = array())
	{
		extract($args);
		// sel nimelises arrays on meil objektide ID-d, mida siis tuleks, saaks, vmt attachida
		// target sisaldab kirja id-d, mille kylge objekt attachida tuleb
		if (is_array($sel))
		{
			classload("file");
			$awf = new file();
			foreach($sel as $key => $val)
			{
				$obj = $this->get_object($key);
				if ($obj["class_id"] == CL_FILE)
				{
					// those get special handling
					$awf->cp(array("id" => $key,"parent" => $target));
				}
				else
				{
					$html = $this->show(array("id" => $key));
					$awf->put(array(
						"type" => "text/html",
						"content" => $html,
						"parent" => $target,
						// extension is for that stupid browser, that ignores mime types
						// and looks only at the end of the filename. 
						"filename" => $this->name . ".html",
					));
				};
			};
		};

		// save[0], because the composer windows has two buttons with that name and therefore
		// we cannot refer to it as just "save"
		print "<script>window.opener.document.writemessage.save[0].click(); window.close();</script>";
			
	}


	////
	// !Displays an object. Any object.
	// and yes, it's not very smart. all the functionality to generate a preview of an object
	// should be inside the correspondending class
	function show($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		if (not($obj))
		{
			return false;
		};

		$this->name = strip_tags($obj["name"]);
		$this->has_output = true;
		// shouldn't we check ACL here?
		switch($obj["class_id"])
		{
			case CL_EXTLINK:
				classload("extlinks");
				$t = new extlinks();
				list($url,$target,$caption) = $t->draw_link($obj["oid"]);
				$replacement = sprintf("<a href='%s' %s>%s</a>",$url,$target,$caption);
				break;

			case CL_IMAGE:
				classload("image");
				$t = new image();
				$idata = $t->get_image_by_id($obj["oid"]);
				$replacement = sprintf("<img src='%s'><br>%s",$idata["url"],$idata["comment"]);
				break;
			case CL_TABLE:
				classload("table");
				$t = new table();
				$replacement = $t->show(array("id" => $obj["oid"],"align" => $align));
				break;

			case CL_FORM_ENTRY:
				classload("form");
				$t = new form();
				$frm = $t->get_form_for_entry($obj["oid"]);
				$ops = $t->get_op_list($frm);
				list($x,$y) = each($ops);
				if (is_array($y))
				{
					list($id,$name) = each($y);
				};

				$replacement = $t->show(array(
					"id" => $frm,
					"entry_id" => $obj["oid"],
					"op_id" => $id,
				));
				break;

			case CL_FORM_OUTPUT:
				classload("form");
				$t = new form();
				$frm = $t->get_op_forms($obj["oid"]);
				$x = $y = 0;
				if (is_array($frm))
				{
					list($x,$y) = each($frm);
				};
				
				$replacement = $t->show(array(
					"id" => $x,
					"op_id" => $id,
				));

				break;

			
			case CL_FORM:
				classload("form");
				$t = new form();
				$replacement = $t->gen_preview(array(
					"id" => $obj["oid"],
					"form_action" => "/reforb.".$this->cfg["ext"],
				));
				break;
			
			case CL_FORM_CHAIN:
				classload("form_chain");
				$t = new form_chain();
				$replacement = $t->show(array(
					"id" => $obj["oid"],
				));
				break;

			case CL_GRAPH:
				$replacement = "<img src='".$this->mk_my_orb("show", array("id" => $obj["oid"]),"graph",false,true)."'>";
				break;

			case CL_GALLERY:
				classload("gallery");
				$t = new gallery();
				$t->load($obj["oid"],$GLOBALS["page"]);
				$replacement = $t->show($GLOBALS["page"]);
				break;

			case CL_FILE:
				classload("file");
				$t = new file;
				$fi = $t->get_file_by_id($obj["oid"]);
				if ($fi["showal"] == 1)
				{
					// n2itame kohe
					// kontrollime koigepealta, kas headerid on ehk väljastatud juba.
					// dokumendi preview vaatamisel ntx on.
					if ($fi["type"] == "text/html")
					{
						if (!headers_sent())
						{
							header("Content-type: text/html");
						};

						$replacement = $fi["content"];
					}
					else
					{
						header("Content-type: ".$fi["type"]);
						die($fi["content"]);
					}
				}
				else
				{
					if ($fi["newwindow"])
					{
						$ss = "target=\"_new\"";
					}

					$comment = $fi["comment"];
					if ($comment == "")
					{
						$comment = $fi["name"];
					}

					classload("file");
					$replacement = "<a $ss class=\"sisutekst\" href='".file::get_url($obj["oid"],$fi["name"])."'>$comment</a>";
				}	
				break;


			case CL_DOCUMENT:
				classload("document");
				$t = new document();
				$replacement = $t->gen_preview(array("docid" => $obj["oid"]));
				break;

			case CL_PSEUDO:
				$replacement = "<a href='/index.".$this->cfg["ext"]."?section=$obj[oid]'>$obj[name]</a>";
				break;

			case CL_CALENDAR:
				classload("planner");
				$cal = new planner();
				$cform = $args["form"];
				$ctrl = 0;

				// chain entry id.
				$ceid = $cform->current_chain_entry;

				if ($cform && $cform->arr["has_calendar"] && $cform->arr["cal_controller"])
				{
					$ctrl = $cform->id;
				};

				//$curl = $this->mk_my_orb("view",array("type" => "week","id" => $obj["oid"],"ctrl" => $ctrl,"ctrle" => $ceid,"chain_id" => $cform->id),"planner",false,true);
				$curl = $this->mk_my_orb("view",array("type" => "week","id" => $ceid),"planner",false,true);
				if (not($caption))
				{
					$caption = "Näita kalendrit";
				};
				$replacement = "<a target='new' href='$curl'>$caption</a>";
				/*
				$replacement = $cal->view(array("id" => $obj["oid"],"type" => "week"));
				*/
				break;

			default:
				$this->has_output = false;
				$replacement = $obj["class_id"] . " This object class has no output yet<br>";
		}
		return $replacement;

	}
}

?>
