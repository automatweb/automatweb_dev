<?php
// $Header
global $orb_defs;
$orb_defs["links"] = array("new"		=>	array("function"	=> "add",	"params"	=> array("parent"), "opt" => array("docid")),
													 "submit"	=>	array("function"	=> "submit","params" => array("parent","id")),
													 "change"	=>	array("function"	=> "change", "params" => array("id"), "opt" => array("docid","parent")),
													 "delete"	=>	array("function"	=> "delete", "params" => array("parent", "id")),
													 "search_doc" => array("function" => "search_doc", "params" => array())
													);

classload("extlinks");
class links extends extlinks
{
	function links()
	{
		$this->tpl_init("automatweb/extlinks");
		$this->db_init();
		global $lc_extlinks;
		if (is_array($lc_extlinks))
		{
			$this->vars($lc_extlinks);
		}
		lc_load("definition");
	}

	function add($arr)
	{
		extract($arr);
		classload("menuedit");
		$t = new menuedit;
		$this->mk_path($parent, LC_LINKS_ADD);
		$this->read_template("nadd.tpl");
		classload("objects");
		$ob = new db_objects;
		$this->vars(array("reforb" => $this->mk_reforb("submit", array("id" => 0, "docid" => $docid)),
											"parent" => $this->picker($parent,$ob->get_list()),
											"search_doc" => $this->mk_orb("search_doc", array()),
											"extlink" => "checked",
											"docs" => $this->picker(0,$t->mk_docsel())));
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

		$this->mk_path($link[parent], LC_LINKS_CHANGE);
		$this->read_template("nadd.tpl");
		$ob = new db_objects;
		$this->vars(array("reforb"	=> $this->mk_reforb("submit", array("docid" => $docid,"id" => $id)),
											"name"		=> $link[name],
											"url"			=> $link[url],
											"search_doc" => $this->mk_orb("search_doc", array()),
											"desc"		=> $link[descript],
											"newwinwidth" => ($link["newwinwidth"]) ? $link["newwinwidth"] : 640,
											"newwinheight" => ($link["newwinheight"]) ? $link["newwinheight"] : 480,
											"newwintoolbar" => checked($link["newwintoolbar"]),
											"newwinlocation" => checked($link["newwinlocation"]),
											"newwinmenu" => checked($link["newwinmenu"]),
											"use_javascript" => checked($link["use_javascript"]),
											"comment"	=> $link[comment],
											"parent"	=> $this->picker($link[parent], $ob->get_list()),
											"extlink"	=> checked($link[type] != "int"),
											"intlink"	=> checked($link[type] == "int"),
											"doclinkcollection"	=> checked($link[doclinkcollection]),
											"docs"		=> $this->picker($link[docid], $t->mk_docsel()),
											"newwindow" => checked($link[newwindow])));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		if (!$id)
		{
			$newlinkid = $this->new_object(array("parent" => $parent,"name" => $name,"class_id" => CL_EXTLINK,"comment" => $comment));
			$this->add_link(array("id"  => $newlinkid,	"oid" => $parent, "name" => $name,"url" => $url,"desc"  => $desc,"newwindow" => $newwindow,"type" => $type, "docid" => $a_docid,"doclinkcollection" => $doclinkcollection));
			$linkid = $newlinkid;
			if ($docid)
			{
				$this->add_alias($docid,$newlinkid);
			}
		}
		else
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "parent" => $parent,"comment" => $comment));
			$linkid = $id;
			$this->save_link(array("lid" => $id, "name" => $name, "url" => $url, "desc" => $desc, "newwindow" => $newwindow,"type" => $type,"docid" => $a_docid,"doclinkcollection" => $doclinkcollection));
		}
		// tegelikult peaks metainfo salvetamise upd_object sisse panema muidugi
		$meta = array(
			"use_javascript" => $use_javascript,
			"newwinwidth" => $newwinwidth,
			"newwinheight" => $newwinheight,
			"newwintoolbar" => $newwintoolbar,
			"newwinlocation" => $newwinlocation,
			"newwinmenu" => $newwinmenu,
		);

		$this->obj_set_meta(array("oid" => $linkid,"meta" => $meta));
		
		if ($docid)
		{
			return $this->mk_my_orb("change", array("id" => $docid), "document");
		}
		else
		{
			return $this->mk_orb("obj_list", array("parent" => $parent),"menuedit");
		}
	}

	function delete($arr)
	{
		extract($arr);
		$this->delete_object($id);
		$this->delete_alias($parent,$id);
		header("Location: ".$this->mk_orb("obj_list", array("parent" => $parent),"menuedit"));
	}

	function search_doc($arr)
	{
		$this->read_template("search_doc.tpl");
		$this->vars(array("index_file" => $GLOBALS["index_file"]));
		global $s_name, $s_content,$SITE_ID,$baseurl,$index_file,$ext,$s_class_id;
		if ($s_name != "" || $s_content != "")
		{
			if ($s_class_id == "item")
			{
				$se = "";
				if ($s_name != "")
				{
					$se = " AND name LIKE '%".$s_name."%' ";
				}
				$this->db_query("SELECT objects.name as name,objects.oid as oid,objects.parent as parent FROM objects WHERE objects.status != 0 AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) AND (objects.class_id = ".CL_SHOP_ITEM.") $se");
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
				$this->db_query("SELECT documents.title as name,objects.oid as oid,objects.parent as parent FROM objects LEFT JOIN documents ON documents.docid=objects.oid WHERE objects.status != 0  AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) AND (objects.class_id = ".CL_DOCUMENT." OR objects.class_id = ".CL_PERIODIC_SECTION." ) AND ".join("AND",$se));
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
					$url = "/".$index_file.".".$ext."/section=".$row["oid"];
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
}
?>
