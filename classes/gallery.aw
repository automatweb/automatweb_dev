<?php
classload("images");

global $orb_defs;
$orb_defs["gallery"] = "xml";

class gallery extends aw_template
{
	function gallery($id = 0)
	{
		$this->db_init();
		$this->tpl_init("gallery");
		$this->sub_merge = 1;
		if ($id != 0)
		{
			$this->load($id);
		}
	}

	////
	// !generates the form for adding a gallery
	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path($parent,"Lisa galerii");

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
		$this->mk_path($row["parent"],"Muuda galeriid");

		$this->vars(array(
			"name" => $row["name"], 
			"comment" => $row["comment"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"content" => $this->mk_orb("admin", array("id" => $id))
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

	function load($id)
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
		$page = max($page,0);
		$this->read_template("grid.tpl");
		$this->load($id);
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
			"add_page" => $this->mk_orb("add_page", array("id" => $id))
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
					"date" => $cell["date"]
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
			"reforb" => $this->mk_reforb("c_submit", array("id" => $id,"page" => $page))
		));
		return $this->parse();
	}

	function add_rows($pg,$num)
	{
		$this->arr[$pg][rows] += $num;
		$this->save();
	}

	function add_cols($pg,$num)
	{
		$this->arr[$pg][cols] += $num;
		$this->save();
	}

	function save()
	{
		$content = serialize($this->arr);
		$this->db_query("UPDATE galleries SET content = '$content' WHERE id = $this->id");
	}

	function del_col($pg)
	{
		$this->arr[$pg][cols]--;
		for ($i=0; $i < $this->arr[$pg][rows]; $i++)
			$this->arr[$pg][content][$i][$this->arr[$pg][cols]] = "";
		$this->save();
	}

	function del_row($pg)
	{
		$this->arr[$pg][rows]--;
		for ($i=0; $i < $this->arr[$pg][cols]; $i++)
			$this->arr[$pg][content][$this->arr[$pg][rows]][$i] = "";
		$this->save();
	}

	function submit()
	{
		global $page;
		for ($row = 0; $row < $this->arr[$page][rows]; $row++)
		{
			for ($col = 0; $col < $this->arr[$page][cols]; $col++)
			{
				$t = new db_images;
				$var = "tn_".$row."_".$col;
				global $$var,${$var."_type"};

				if ($$var != "none")
				{
					if ($this->arr[$page][content][$row][$col][tn_id] != 0)
					{
						$ar = $t->_replace(array("filename" => $$var,"file_type" => ${$var."_type"}, "poid" => $this->arr[$page][content][$row][$col][tn_id]));
						$pid = $ar[id];
					}
					else
					{
						$ar = $t->_upload(array("filename" => $$var,"file_type" => ${$var."_type"}, "oid" => $this->id));
						$pid = $ar[id];
					}
					$img = $t->get_img_by_id($pid);
					$this->arr[$page][content][$row][$col][tn_id] = $img[id];
					$this->arr[$page][content][$row][$col][tnurl] = $img[url];
				}

				$t = new db_images;
				$var = "im_".$row."_".$col;
				global $$var,${$var."_type"};

				if ($$var != "none")
				{
					if ($this->arr[$page][content][$row][$col][im_id] != 0)
					{
						$ar = $t->_replace(array("filename" => $$var,"file_type" => ${$var."_type"}, "poid" => $this->arr[$page][content][$row][$col][im_id]));
						$pid = $ar[id];
					}
					else
					{
						$ar = $t->_upload(array("filename" => $$var,"file_type" => ${$var."_type"}, "oid" => $this->id));
						$pid = $ar[id];
					}
					$sz = getimagesize($$var);
					$img = $t->get_img_by_id($pid);
					$this->arr[$page][content][$row][$col][im_id] = $img[id];
					$this->arr[$page][content][$row][$col][bigurl] = $img[url];
					$this->arr[$page][content][$row][$col][xsize] = $sz[0];
					$this->arr[$page][content][$row][$col][ysize] = $sz[1];
				}


				$var = "caption_".$row."_".$col;
				global $$var;
				$v = str_replace("\\","",$$var);
				$v = str_replace("'","\"",$v);
				$this->arr[$page][content][$row][$col][caption] = $v;

				$var = "date_".$row."_".$col;
				global $$var;
				$v = str_replace("\\","",$$var);
				$v = str_replace("'","\"",$v);
				$this->arr[$page][content][$row][$col][date] = $v;

				$var = "erase_".$row."_".$col;
				global $$var;
				if ($$var == 1)
				{
					$this->arr[$page][content][$row][$col] = array();
				}
			}
		}
		$this->save();
	}

	function add_page()
	{
		$this->arr[pages] ++;
		$this->save();
		return $this->arr[pages]-1;
	}

	function show($page)
	{
		if ($page < 1)
			$page = 0;

		$baseurl = "/gallery.".$GLOBALS["ext"]."/id=".$this->id;

		if (isset($GLOBALS["col"]) && isset($GLOBALS["row"]))
		{
			global $col, $row;
			$this->read_template("show_pic.tpl");
			$cell = $this->arr[$page][content][$row][$col];
			$this->vars(array("bigurl" => $cell[bigurl], "caption" => $cell[caption], "date" => $cell[date]));
		}
		else
		{
			$this->read_template("show.tpl");

			for ($row = 0; $row < $this->arr[$page][rows]; $row++)
			{
				$c = "";
				for ($col = 0; $col < $this->arr[$page][cols]; $col++)
				{
					$cell = $this->arr[$page][content][$row][$col];
					$xsize = $cell[xsize] ? $cell[xsize] : 500;
					$ysize = $cell[ysize] ? $cell[ysize] + 50: 400;
					$this->vars(array("tnurl" => $cell[tnurl], "caption" => $cell[caption], "date" => $cell[date],"bigurl" => $baseurl."/col=$col/row=$row/page=$page","xsize" => $xsize, "ysize" => $ysize));
					if ($cell[tnurl] != "")
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

		for ($pg = 0; $pg < $this->arr[pages]; $pg++)
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

		if ($this->arr[pages] > 0)
		{
			$this->vars(array("PAGES" => $this->parse("PAGES")));
		}
		return $this->parse();
	}
}
?>