<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/links.aw,v 2.20 2002/09/25 15:03:13 kristo Exp $

classload("extlinks");
class links extends extlinks
{
	function links()
	{
		$this->extlinks();
		$this->init("automatweb/extlinks");
	}

	////
	// !Kuvab uue lingi lisamise vormi
	function add($arr)
	{
		extract($arr);
		classload("menuedit");
		$t = new menuedit;
		if ($return_url)
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / ".LC_LINKS_ADD);
		}
		else
		{
			$this->mk_path($parent, LC_LINKS_ADD);
		}
		$this->read_template("nadd.tpl");
		classload("objects");
		$ob = new db_objects;
		load_vcl("date_edit");
		$de = new date_edit("active_until");
		$de->configure(array(
			"day" => "",
			"month" => "",
			"year" => "",
			"hour" => "",
			"minute" => ""
		));
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url)),
			"parent" => $this->picker($parent,$ob->get_list()),
			"search_doc" => $this->mk_orb("search_doc", array()),
			"extlink" => "checked",
			"link_image_active_until" => $de->gen_edit_form("active_until",0),
		));
		return $this->parse();
	}

	////
	// !Kuvab lingi muutmise vormi
	function change($arr)
	{
		extract($arr);

		$link = $this->get_link($id);
		classload("menuedit");
		$t = new menuedit;

		if ($return_url)
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / ".LC_LINKS_CHANGE);
		}
		else
		{
			$this->mk_path($link["parent"], LC_LINKS_CHANGE);
		}

		// check whether this link has an image attached
		$q = "SELECT * FROM objects LEFT JOIN files ON objects.oid = files.id WHERE parent = '$id' AND class_id = " . CL_FILE;
		$this->db_query($q);
		$row = $this->db_next();
		$awf = get_instance("file");
		
		$this->read_template("nadd.tpl");

		if ($row && $awf->can_be_embedded(&$row))
		{
			$url = $awf->get_url($row["oid"],"");
			$this->vars(array("link_image" => "<img src='$url'>"));
		}
		
		load_vcl("date_edit");
		$de = new date_edit("active_until");
		$de->configure(array(
			"day" => "",
			"month" => "",
			"year" => "",
			"hour" => "",
			"minute" => ""
		));

		$active_until = ($link["link_image_check_active"]) ? $link["link_image_active_until"] : time() + (3 * 86400);


		$ob = new db_objects;
		$this->vars(array(
			"reforb"	=> $this->mk_reforb("submit", array("docid" => $docid,"id" => $id,"xparent" => $parent,"return_url" => $return_url)),
			"name"		=> $link["name"],
			"url"			=> $link["url"],
			"search_doc" => $this->mk_orb("search_doc", array()),
			"desc"		=> $link["descript"],
			"newwinwidth" => ($link["newwinwidth"]) ? $link["newwinwidth"] : 640,
			"newwinheight" => ($link["newwinheight"]) ? $link["newwinheight"] : 480,
			"newwintoolbar" => checked($link["newwintoolbar"]),
			"newwinlocation" => checked($link["newwinlocation"]),
			"newwinmenu" => checked($link["newwinmenu"]),
			"newwinscroll" => checked($link["newwinscroll"]),
			"use_javascript" => checked($link["use_javascript"]),
			"comment"	=> $link["comment"],
			"parent"	=> $this->picker($link["parent"], $ob->get_list()),
			"extlink"	=> checked($link["type"] != "int"),
			"intlink"	=> checked($link["type"] == "int"),
			"doclinkcollection"	=> checked($link["doclinkcollection"]),
			"docs"		=> $this->picker($link["docid"], $t->mk_docsel()),
			"newwindow" => checked($link["newwindow"]),
			"link_image_active_until" => $de->gen_edit_form("active_until",$active_until),
			"link_image_check_active" => checked($link["link_image_check_active"]),
			"alt" => $link["alt"],
		));
		return $this->parse();
	}

	////
	// !Submitib add voi change actioni tulemuse
	function submit($arr)
	{
		$this->quote($arr);
		extract($arr);
			
		if (!$id)
		{
			$newlinkid = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_EXTLINK,
				"comment" => $comment,
				"metadata" => array(
					"alt" => $alt,
				),
			));

			$this->add_link(array(
				"id"  => $newlinkid,	
				"oid" => $parent, 
				"name" => $name,
				"url" => $url,
				"desc"  => $desc,
				"newwindow" => $newwindow,
				"type" => $type, 
				"docid" => $a_docid,
				"doclinkcollection" => $doclinkcollection,
			));

			$linkid = $newlinkid;

			if ($alias_to)
			{
				$this->add_alias($alias_to,$newlinkid);
			}

			$id = $newlinkid;
		}
		else
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"parent" => $parent,
				"comment" => $comment,
				"metadata" => array(
					"alt" => $alt,
				),
			));

			$linkid = $id;

			$this->save_link(array(
				"lid" => $id, 
				"name" => $name, 
				"url" => $url, 
				"desc" => $desc, 
				"newwindow" => $newwindow,
				"type" => $type,
				"docid" => $a_docid,
				"doclinkcollection" => $doclinkcollection,
			));
		}
		// tegelikult voiks metainfo salvetamise upd_object sisse panema muidugi
		load_vcl("date_edit");
		$de = new date_edit("foo");
		$meta = array(
			"use_javascript" => $use_javascript,
			"newwinwidth" => $newwinwidth,
			"newwinheight" => $newwinheight,
			"newwintoolbar" => $newwintoolbar,
			"newwinlocation" => $newwinlocation,
			"newwinmenu" => $newwinmenu,
			"newwinscroll" => $newwinscroll,
			"link_image_active_until" => $de->get_timestamp($active_until),
			"link_image_check_active" => $link_image_check_active,
			"alt" => $alt,
		);

		$this->obj_set_meta(array("oid" => $linkid,"meta" => $meta));
		
		$_fi = new file;
		// figure out whether that link already has an image attached
		$q = "SELECT * FROM objects WHERE parent = '$id' AND class_id = " . CL_FILE;
		$this->db_query($q);
		$row = $this->db_next();
		if ($row)
		{
			$img_id = $row["oid"];
		}
		else
		{	
			$img_id = "";
		};
		
		global $link_image,$link_image_name,$link_image_type;
		if ($link_image != "" && $link_image != "none")
		{
			if (is_uploaded_file($link_image))
			{
				$fl = $_fi->_put_fs(array("type" => $link_image_type, "content" => $this->get_file(array("file" => $link_image))));	
				// I'm only interested in the filename, not the path
				$fl = basename($fl);

				$fn = $link_image_name;

				if ($img_id)
				{
					$this->db_query("UPDATE files SET file = '$fl',type = '$link_image_type' WHERE id = $img_id");
				}
				else
				{
					$img_id = $this->new_object(array(
						"parent" => $id,
						"name" => "link image",
						"class_id" => CL_FILE,
						"status" => 2,
					));

					$this->db_query("INSERT INTO files (id,file,type) VALUES ('$img_id','$fl','$link_image_type')");
				};
			}
		};

		// arendaks miskit plugin arhitektuuri siin.
		// ntx, klass providib vahendid linkide lisamiseks, muutmiseks ja submiti
		// handlemiseks, aga redirectid tehaks siiski sellest klassist, mis vajab
		// neid teenuseid. Ntx dokumendiklassi sees.

		#$par_obj = $this->get_object($xparent);
		return $this->mk_my_orb("change",array("id" => $id, "return_url" => $return_url));
		/*
		if ($docid)
		{
			return $this->mk_my_orb("list_aliases", array("id" => $docid), "aliasmgr");
		}
		else
		{
			return $this->mk_orb("obj_list", array("parent" => $parent),"menuedit");
		}
		*/
	}

	////
	// !Kustutab lingi objekti
	function delete($arr)
	{
		extract($arr);
		$p_obj = $this->get_object($parent);
		$this->delete_object($id);
		$this->delete_alias($parent,$id);
		if ($p_obj["class_id"] != CL_PSEUDO)
		{
			return $this->mk_my_orb("change",array("id" => $parent),"document");
		}
		else
		{
			header("Location: ".$this->mk_orb("obj_list", array("parent" => $parent),"menuedit"));
		};
	}

	function search_doc($arr)
	{
		$this->read_template("search_doc.tpl");
		$this->vars(array("index_file" => $this->cfg["index_file"]));
		global $s_name, $s_content,$s_class_id;
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		if ($s_name != "" || $s_content != "")
		{
			if ($s_class_id == "item")
			{
				$se = "";
				if ($s_name != "")
				{
					$se = " AND name LIKE '%".$s_name."%' ";
				}
				$this->db_query("SELECT objects.name as name,objects.oid as oid,objects.parent as parent FROM objects WHERE objects.status != 0 AND (objects.site_id = ".$this->cfg["site_id"]." OR objects.site_id IS NULL) AND (objects.class_id = ".CL_SHOP_ITEM.") $se");
			}
			else
			{
				$se = array();
				if ($s_name != "")
				{
					$se[] = " name LIKE '%".$s_name."%' ";
				}
				if ($s_content != "")
				{
					$se[] = " content LIKE '%".$s_content."%' ";
				}
				$this->db_query("SELECT documents.title as name,objects.oid as oid,objects.parent as parent FROM objects LEFT JOIN documents ON documents.docid=objects.oid WHERE objects.status != 0  AND (objects.site_id = ".$this->cfg["site_id"]." OR objects.site_id IS NULL) AND (objects.class_id = ".CL_DOCUMENT." OR objects.class_id = ".CL_PERIODIC_SECTION." ) AND ".join("AND",$se));
			}
			while ($row = $this->db_next())
			{
				if ($s_class_id == "item")
				{
					$url = $this->mk_site_orb(array("action" => "order_item", "item_id" => $row["oid"], "section" => $row["parent"],"class" => "shop"));
					$url = substr($url,strlen($baseurl));
				}
				else
				{
					if (aw_ini_get("menuedit.long_section_url"))
					{
						$url = "/".$this->cfg["index_file"].".".$ext."/section=".$row["oid"];
					}
					else
					{
						$url = "/".$row["oid"];
					}
				}
				$name = strip_tags($row["name"]);
				$name = str_replace("'","",$name);
				$this->vars(array(
					"name" => $name, 
					//"name" => htmlentities($row["name"],ENT_QUOTES), 
					"id" => $row["oid"],
					"url" => $url
				));
				$l.=$this->parse("LINE");
			}
			$this->vars(array("LINE" => $l));
		}
		else
		{
			$s_name = "%";
			$s_content = "%";
		}
		$this->vars(array(
			"reforb" => $this->mk_reforb("search_doc", array("reforb" => 0)),
			"s_name"	=> $s_name,
			"s_content"	=> $s_content,
			"doc_sel" => checked($s_class_id != "item"),
			"item_sel" => checked($s_class_id == "item")
		));
		return $this->parse();
	}

	function _serialize($arr)
	{
		extract($arr);
		$this->db_query("
			SELECT 
				extlinks.url as e_url,
				extlinks.name as e_name,
				extlinks.hits as e_hits,
				extlinks.oid as e_oid,
				extlinks.descript as e_descript,
				extlinks.newwindow as e_newwindow,
				extlinks.type as e_type, 
				extlinks.docid as e_docid, 
				extlinks.doclinkcollection as e_doclinkcollection,
				objects.* 
			FROM extlinks 
				LEFT JOIN objects ON objects.oid = extlinks.id 
			WHERE id = $oid");
		$row = $this->db_next();
		return serialize($row);
	}

	function _unserialize($arr)
	{
		extract($arr);

		$row = unserialize($str);
		$row["parent"] = $parent;
		$id = $this->new_object($row);
		$this->db_query("INSERT INTO extlinks(id,url,name,hits,oid,descript,newwindow,type,docid,doclinkcollection) VALUES($id,'".$row["e_url"]."','".$row["e_name"]."','".$row["e_hits"]."','".$row["e_oid"]."','".$row["e_descript"]."','".$row["e_newwindow"]."','".$row["e_type"]."','".$row["e_docid"]."','".$row["e_doclinkcollection"]."')");
		return true;
	}

	function show($arr)
	{
		extract($arr);
		$link = $this->get_link($id);
		if (!$link) 
		{
			print "Sellist linki pole baasis";
		} 
		else 
		{
			$this->add_hit($id,aw_global_get("HTTP_HOST"),aw_global_get("uid"));
			header("Location: ".$link["url"]);
			exit;
		};
	}
}
?>
