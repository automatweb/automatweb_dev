<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/gallery.aw,v 2.12 2001/09/14 19:10:23 kristo Exp $
classload("images");
lc_load("gallery");
global $orb_defs;
$orb_defs["gallery"] = "xml";

class gallery extends aw_template
{
	function gallery($id = 0)
	{
		$this->db_init();
		$this->tpl_init("gallery");
		$this->sub_merge = 1;
		if ($id)
		{
			$this->load($id,$GLOBALS["page"]);
		}
		global $lc_gallery;
		if (is_array($lc_gallery))
		{
			$this->vars($lc_gallery);
		}
		lc_load("definition");
	}

	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->galaliases))
		{
			$this->galaliases = $this->get_aliases(array(
								"oid" => $oid,
								"type" => CL_GALLERY,
						));
		};
		$g = $this->galaliases[$matches[3] - 1];
		$this->load($g["target"],$GLOBALS["page"]);
		$replacement = $this->show($GLOBALS["page"]);
		return $replacement;
	}
		

	////
	// !generates the form for adding a gallery
	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path($parent,LC_GALLERY_ADD_GAL);

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_doc" => $alias_doc))
		));
		return $this->parse();
	}

	////
	// !generates the form for changing a gallery
	function change($arr)
	{
		extract($arr);
		if (!($row = $this->get_object($id)))
		{
			$this->raise_error("no such gallery($id)!", true);
		}
		$this->read_template("add.tpl");
		$this->mk_path($row["parent"],LC_GALLERY_CHANGE_GAL);

		$this->vars(array(
			"name" => $row["name"], 
			"comment" => $row["comment"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"content" => $this->mk_orb("admin", array("id" => $id, "page" => "0"))
		));
		$this->parse("CHANGE");
		return $this->parse();
	}

	////
	// !saves or creates the gallery
	function csubmit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$parent = $parent ? $parent : $GLOBALS["rootmenu"];
			$id = $this->new_object(array("parent" => $parent,"name" => $name, "comment" => $comment, "class_id" => CL_GALLERY));
			$this->db_query("INSERT INTO galleries VALUES($id,'')");
			if ($alias_doc)
			{
				$this->add_alias($alias_doc, $id);
			}
		}
		return $this->mk_orb("change", array("id" => $id));
	}

	function load($id,$pg)
	{
		$this->db_query("SELECT objects.*, galleries.content as content FROM objects LEFT JOIN galleries ON galleries.id = objects.oid WHERE oid = $id");
		if (!($row = $this->db_next()))
		{
			$this->raise_error("load_gallery($id): no such gallery!", true);
		}

		$this->arr = unserialize($row["content"]);
		$this->name = $row["name"]; 
		$this->parent = $row["parent"];
		$this->id = $id;
		$this->comment = $row["comment"];
		$this->vars(array("id" => $id, "name" => $this->name, "comment" => $this->comment));

		if ($this->arr["pages"] < 1)
		{
			$this->arr["pages"] = 1;
		}
		for ($pg = 0; $pg < $this->arr["pages"]; $pg++)
		{
			if ($this->arr[$pg]["rows"] < 1)
			{
				$this->arr[$pg]["rows"] = 1;
			}
			if ($this->arr[$pg]["cols"] < 1)
			{
				$this->arr[$pg]["cols"] = 1;
			}
		}
	}

	////
	// !generates the form for uploading pictures for gallery $id, page $page
	function admin($arr)
	{
		extract($arr);
		if ($page < 1)
		{
			$page = "0";
		}
		$this->read_template("grid.tpl");
		$this->load($id,$page);
		$this->mk_path($this->parent, "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda</a> / Sisu");
		
		for ($pg = 0; $pg < $this->arr["pages"]; $pg++)
		{
			$this->vars(array(
				"page" => $pg,
				"to_page" => $this->mk_orb("admin", array("id" => $id, "page" => $pg))
			));
			if ($pg == $page)
			{
				$p.=$this->parse("SEL_PAGE");
			}
			else
			{
				$p.=$this->parse("PAGE");
			}
		}
		$this->vars(array(
			"PAGE" => $p, 
			"SEL_PAGE" => "",
			"add_page" => $this->mk_orb("add_page", array("id" => $id)),
			"del_page" => $this->mk_orb("del_page", array("id" => $id))
		));

		for ($row = 0; $row < $this->arr[$page]["rows"]; $row++)
		{
			$this->vars(array("row" => $row));
			$c = "";
			for ($col = 0; $col < $this->arr[$page]["cols"]; $col++)
			{
				$cell = $this->arr[$page]["content"][$row][$col];
				$this->vars(array(
					"imgurl" => $cell["tnurl"], 
					"caption" => $cell["caption"], 
					"bigurl" => $cell["bigurl"],
					"col" => $col,
					"date" => $cell["date"],
					"link" => $cell["link"]
				));
				$b = $cell["bigurl"] != "" ? $this->parse("BIG") : "";
				$this->vars(array("BIG" => $b));
				$c.=$this->parse("CELL");
			}
			$this->vars(array("CELL" => $c));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"page" => $page,
			"reforb" => $this->mk_reforb("c_submit", array("id" => $id,"page" => $page)),
			"del_row" => $this->mk_orb("del_row", array("id" => $id, "page" => $page)),
			"del_col" => $this->mk_orb("del_col", array("id" => $id, "page" => $page))
		));
		return $this->parse();
	}

	function add_rows($arr)
	{
		extract($arr);
		$this->load($id,$page);
		$this->arr[$page]["rows"] += $rows;
		$this->save();
		header("Location: ".$this->mk_orb("admin", array("id" => $id, "page" => $page)));
		die();
	}

	function add_cols($arr)
	{
		extract($arr);
		$this->load($id,$page);
		$this->arr[$page]["cols"] += $cols;
		$this->save();
		header("Location: ".$this->mk_orb("admin", array("id" => $id, "page" => $page)));
		die();
	}

	function del_col($arr)
	{
		extract($arr);
		$this->load($id,$page);
		$this->arr[$page]["cols"]--;
		for ($i=0; $i < $this->arr[$page]["rows"]; $i++)
		{
			$this->arr[$page]["content"][$i][$this->arr[$page]["cols"]] = "";
		}
		$this->save();
		header("Location: ".$this->mk_orb("admin", array("id" => $id, "page" => $page)));
		die();
	}

	function del_row($arr)
	{
		extract($arr);
		$this->load($id,$page);
		$this->arr[$page]["rows"]--;
		for ($i=0; $i < $this->arr[$page]["cols"]; $i++)
		{
			$this->arr[$page]["content"][$this->arr[$page]["rows"]][$i] = "";
		}
		$this->save();
		header("Location: ".$this->mk_orb("admin", array("id" => $id, "page" => $page)));
		die();
	}

	function save()
	{
		$content = serialize($this->arr);
		$this->db_query("UPDATE galleries SET content = '$content' WHERE id = $this->id");
	}

	////
	// !saves the uploaded pictures for gallery $id, on page $page
	function submit($arr)
	{
		extract($arr);
/*		echo "submit! \n<br>";
		flush();*/
		$this->load($id,$page);
		if ($page < 1)
		{
			$page = 0;
		}
//		echo "load! \n<br>";
//		flush();

		for ($row = 0; $row < $this->arr[$page]["rows"]; $row++)
		{
//			echo "row $row ! \n<br>";
//			flush();
			for ($col = 0; $col < $this->arr[$page]["cols"]; $col++)
			{
//				echo "col $col ! \n<br>";
//				flush();
				$t = new db_images;
				$var = "tn_".$row."_".$col;
				global $$var,${$var."_type"};
				if ($$var != "none")
				{
					if ($this->arr[$page]["content"][$row][$col]["tn_id"] != 0)
					{
						$ar = $t->_replace(array("filename" => $$var,"file_type" => ${$var."_type"}, "poid" => $this->arr[$page]["content"][$row][$col]["tn_id"]));
						$pid = $ar["id"];
					}
					else
					{
						$ar = $t->_upload(array("filename" => $$var,"file_type" => ${$var."_type"}, "oid" => $this->id));
						$pid = $ar["id"];
					}
					$img = $t->get_img_by_id($pid);
					$this->arr[$page]["content"][$row][$col]["tn_id"] = $img["id"];
					$this->arr[$page]["content"][$row][$col]["tnurl"] = $img["url"];
				}

				$t = new db_images;
				$var = "im_".$row."_".$col;
				global $$var,${$var."_type"};

				if ($$var != "none")
				{
					if ($this->arr[$page]["content"][$row][$col]["im_id"] != 0)
					{
						$ar = $t->_replace(array("filename" => $$var,"file_type" => ${$var."_type"}, "poid" => $this->arr[$page]["content"][$row][$col]["im_id"]));
						$pid = $ar["id"];
					}
					else
					{
						$ar = $t->_upload(array("filename" => $$var,"file_type" => ${$var."_type"}, "oid" => $this->id));
						$pid = $ar["id"];
					}
					$sz = getimagesize($$var);
					$img = $t->get_img_by_id($pid);
					$this->arr[$page]["content"][$row][$col]["im_id"] = $img["id"];
					$this->arr[$page]["content"][$row][$col]["bigurl"] = $img["url"];
					$this->arr[$page]["content"][$row][$col]["xsize"] = $sz[0];
					$this->arr[$page]["content"][$row][$col]["ysize"] = $sz[1];
				}


				$var = "caption_".$row."_".$col;
				global $$var;
				$v = str_replace("\\","",$$var);
				$v = str_replace("'","\"",$v);
				$this->arr[$page]["content"][$row][$col]["caption"] = $v;

				$var = "link_".$row."_".$col;
				global $$var;
				$v = str_replace("\\","",$$var);
				$v = str_replace("'","\"",$v);
				$this->arr[$page]["content"][$row][$col]["link"] = $v;

				$var = "date_".$row."_".$col;
				global $$var;
				$v = str_replace("\\","",$$var);
				$v = str_replace("'","\"",$v);
				$this->arr[$page]["content"][$row][$col]["date"] = $v;

				$var = "erase_".$row."_".$col;
				global $$var;
				if ($$var == 1)
				{
					$this->arr[$page]["content"][$row][$col] = array();
				}
			}
		}
		$this->save();
		return $this->mk_orb("admin", array("id" => $id, "page" => $page));
	}

	////
	// !adds a page to the gallery and returns to grid
	function add_page($arr)
	{
		extract($arr);
		$this->load($id,$page);
		$this->arr["pages"] ++;
		$this->save();
		header("Location: ".$this->mk_orb("admin", array("id" => $id, "page" => $page)));
		die();
	}

	function show($page)
	{
		if ($page < 1)
			$page = 0;

		$baseurl = $GLOBALS["baseurl"]."/gallery.".$GLOBALS["ext"]."/id=".$this->id;

		if (isset($GLOBALS["col"]) && isset($GLOBALS["row"]))
		{
			global $col, $row;
			$this->read_template("show_pic.tpl");
			$cell = $this->arr[$page]["content"][$row][$col];
			$bigurl = preg_replace("/^http:\/\/.*\//","/",$cell["bigurl"]);
			$this->vars(array("bigurl" => $bigurl, "caption" => $cell["caption"], "date" => $cell["date"]));
		}
		else
		{
			$this->read_template("show.tpl");

			for ($row = 0; $row < $this->arr[$page]["rows"]; $row++)
			{
				$c = "";
				for ($col = 0; $col < $this->arr[$page]["cols"]; $col++)
				{
					$cell = $this->arr[$page]["content"][$row][$col];
					$xsize = $cell["xsize"] ? $cell["xsize"] : 500;
					$ysize = $cell["ysize"] ? $cell["ysize"] + 50: 400;
					if ($cell["link"] != "")
					{
						$url = $cell["link"];
						$target="target=\"_blank\"";
					}
					else
					{	
						$url = "javascript:rremote(\"$baseurl/col=$col/row=$row/page=$page\",$xsize,$ysize)";
						$target = "";
					}

					// strip the beginning of a posible absolute url
					$tnurl = preg_replace("/^http:\/\/.*\//","/",$cell["tnurl"]);
					$this->vars(array(
						"tnurl" => $tnurl, 
						"caption" => $cell["caption"], 
						"date" => $cell["date"],
						"url" => $url,
						"target" => $target
					));
					if ($cell["tnurl"] != "")
					{
						$c.=$this->parse("IMAGE");
					}
				}
				$this->vars(array("IMAGE" => $c));
				$l.=$this->parse("LINE");
			}
		}

		global $section;
		$baseurl = $GLOBALS["baseurl"]."/index.aw/section=$section";

		for ($pg = 0; $pg < $this->arr["pages"]; $pg++)
		{
			$this->vars(array("num" => $pg,"url" => $baseurl."/page=$pg"));
			$p.=$this->parse("PAGE");
		}

		$pr = "";
		if ($page > 0)
		{
			$this->vars(array("url" => $baseurl."/page=".($page-1)));
			$pr = $this->parse("PREVIOUS");
		}
		$nx = "";
		if ($page < ($this->arr[pages]-1))
		{
			$this->vars(array("url" => $baseurl."/page=".($page+1)));
			$nx = $this->parse("NEXT");
		}
		$this->vars(array("LINE" => $l,"PAGE" => $p,"sel_page" => $page,"PREVIOUS" => $pr, "NEXT" => $nx));

		if ($this->arr[pages] > 1)
		{
			$this->vars(array("PAGES" => $this->parse("PAGES")));
		}
		else
		{
			$this->vars(array("PAGES" => ""));
		}
		return $this->parse();
	}

	function del_page($arr)
	{
		extract($arr);
		$this->load($id,$page);
		$this->arr["pages"] --;
		$this->save();
		header("Location: ".$this->mk_orb("admin", array("id" => $id, "page" => 0)));
		die();
	}
}
?>
