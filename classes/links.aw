<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/links.aw,v 2.31 2003/06/10 15:44:13 duke Exp $

/*

@groupinfo general caption=&Uuml;ldine
@groupinfo Javascript caption=Javascript
@groupinfo Pilt caption=Pilt

@tableinfo extlinks index=id

@classinfo objtable=extlinks
@classinfo objtable_index=id
@tableinfo extlinks index=id master_table=objects master_index=oid

@default group=general

@property status type=status table=objects group=general
@caption Staatus

@property comment type=textarea cols=30 rows=5 table=objects field=comment group=general
@caption Kommentaar lingikogusse

@property url table=extlinks type=textbox field=url group=general
@caption URL

@property url_int_text type=text group=general
@caption Saidi sisene link

@property alt type=textbox table=objects field=meta method=serialize group=general search=1
@caption Alt tekst

@property newwindow type=checkbox ch_value=1 table=extlinks field=newwindow group=general search=1
@caption Uues aknas

@property doclinkcollection type=checkbox ch_value=1 table=extlinks field=doclinkcollection group=general
@caption Dokumendi lingikogusse

@property use_javascript type=checkbox ch_value=1 table=objects field=meta method=serialize group=Javascript search=1
@caption Kasuta javascripti

@property newwinwidth type=textbox ch_value=1 table=objects field=meta method=serialize group=Javascript
@caption Uue akna laius

@property newwinheight type=textbox ch_value=1 table=objects field=meta method=serialize group=Javascript
@caption Uue akna k&otilde;rgus

@property newwintoolbar type=checkbox ch_value=1 table=objects field=meta method=serialize group=Javascript
@caption Toolbar

@property newwinlocation type=checkbox ch_value=1 table=objects field=meta method=serialize group=Javascript
@caption Address bar

@property newwinmenu type=checkbox ch_value=1 table=objects field=meta method=serialize group=Javascript
@caption Men&uuml;&uuml;d

@property newwinscroll type=checkbox ch_value=1 table=objects field=meta method=serialize group=Javascript
@caption Skrollbarid

@property link_image type=fileupload group=Pilt
@caption Pilt

@property link_image_show type=text group=Pilt
@caption 

@property link_image_check_active type=checkbox ch_value=1 field=meta table=objects method=serialize group=Pilt
@caption Pilt aktiivne

@property link_image_active_until type=date_select field=meta table=objects method=serialize group=Pilt
@caption Pilt aktiivne kuni

*/

class links extends class_base
{
	function links()
	{
		$this->init(array(
			"tpldir" => "automatweb/extlinks",
			"clid" => CL_EXTLINK
		));

		$this->lc_load("extlinks","lc_extlinks");
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
			header("Content-type: ");
			exit;
		};
	}
	
	function get_property(&$arr)
	{
	  $prop = &$arr["prop"];
	  if ($prop["name"] == "link_image_show" && $arr["obj"]["oid"])
	  {
		$foid = $this->db_fetch_field("SELECT oid FROM objects WHERE parent = ".$arr["obj"]["oid"]." AND class_id =".CL_FILE." AND status != 0","oid");
		if ($foid)
		{
		  $f = get_instance("file");
		  $fdat = $f->get_file_by_id($foid);
		  if ($f->can_be_embedded($fdat))
			{
			  $prop['value'] = html::img(array(
				 'url' => file::get_url($fdat['oid'],$fdat['name'])
			  ));
			}
		}
	  }
	  else
		if ($prop["name"] == "url_int_text")
		  {
			$this->read_template("intlink.tpl");
			$this->vars(array(
							  'search_doc' => $this->mk_my_orb('search_doc')
							  ));
			$prop['value'] = $this->parse();
		  }
	  return PROP_OK;
	}  

	function set_property(&$arr)
	{
		$prop = $arr["prop"];
		if ($prop["name"] == "link_image")
		{
		  $f = get_instance("file");
		  $foid = $this->db_fetch_field("SELECT oid FROM objects WHERE parent = ".$arr["obj"]["oid"]." AND class_id =".CL_FILE." AND status != 0","oid");
		  $nfoid = $f->add_upload_image("link_image", $arr['obj']['oid'], $foid);
		  return PROP_IGNORE;
		}
		return PROP_OK;
	}
	
	////
	// !Hoolitseb ntx doku sees olevate extlinkide aliaste parsimise eest (#l2#)
	function parse_alias($args = array())
	{
		extract($args);

		list($url,$target,$caption) = $this->draw_link($alias["target"]);
		if ($this->img)
		{
			$caption = $this->img;
		};
		$vars = array(
			"url" => $url,
			"caption" => $caption,
			"target" => $target,
			"img" => $this->img,
		);
		if (isset($tpls["link"]))
		{
			$replacement = trim(localparse($tpls["link"],$vars));
		}
		else
		{
			if ($img)
			{
				$replacement = sprintf("<a href='%s' %s><img src='%s' alt='%s' border='0'></a>",$url,$target,$this->img,$caption);
			}
			else
			{
				$replacement = sprintf("<a href='%s' %s>%s</a>",$url,$target,$caption);
			}
		};
		$this->img = "";
		return $replacement;
	}
	
	function draw_link($target)
	{
		$link = $this->get_link($target);
		if (not($link))
		{
			return;
		}

		if (strpos($link["url"],"@") > 0)
		{
			$linksrc = $link["url"];
		}
		elseif (aw_ini_get("extlinks.directlink") == 1)
		{
			$linksrc = $link["url"];
		}
		else
		{
			$linksrc = aw_ini_get("baseurl")."/".$link["id"];//$this->mk_my_orb("show", array("id" => $link["id"]),"links",false,true);
		};

		if ($link["link_image_check_active"] && ($link["link_image_active_until"] >= time()) )
		{
			$awf = get_instance("file");
			$q = "SELECT * FROM objects LEFT JOIN files ON objects.oid = files.id WHERE parent = '$target' AND class_id = " . CL_FILE;
			$this->db_query($q);
			$row = $this->db_next();

			if ($row && $awf->can_be_embedded(&$row))
			{
				$img = $awf->get_url($row["oid"],"");
				$img = "<img border='0' src='$img' alt='$link[alt]' title='$link[alt]' />";
			}
			else
			{
				$img = "";
			};

			$this->img = $img;
		}
		
		if ($link["use_javascript"])
		{
			$target = sprintf("onClick='javascript:window.open(\"%s\",\"w%s\",\"toolbar=%d,location=%d,menubar=%d,scrollbars=%d,width=%d,height=%d\")'",$linksrc,$link["id"],$link["newwintoolbar"],$link["newwinlocation"],$link["newwinmenu"],$link["newwinscroll"],$link["newwinwidth"],$link["newwinheight"]);
			$url = "javascript:void(0)";
		}
		else
		{
			$url = $linksrc;
			$target = $link["newwindow"] ? "target='_blank'" : "";
		};


		return(array($url,$target,$link["name"]));
	}
	
	////
	// !resetib aliased
	function reset_aliases()
	{
		$this->extlinkaliases = "";
	}

	function get_link($id)
	{
		// bail out if no id	
		if (not($id))
		{
			return;
		};
		$q = "SELECT extlinks.*,objects.* FROM extlinks LEFT JOIN objects ON objects.oid = extlinks.id WHERE id = '$id'";
		$row = $this->db_fetch_row($q);
		$row = array_merge($row,aw_unserialize($row['metadata']));
		if ($row["type"] == "int")
		{
			$row["url"] = $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?section=".$row["docid"];
		}
		return $row;
	}

  // registreerib kliki lingile

	// peab ehitama ka mehhanisimi spämmimise vältimiseks
	function add_hit($id,$host,$uid) 
	{
		$q = "UPDATE extlinks
							SET hits = hits + 1
							WHERE id = '$id'";
		$this->db_query($q);
		$t = time();
		$q = "INSERT INTO extlinkstats (lid,tm,host,uid) 
						VALUES ('$id',$t,'$host','$uid')";
		$this->db_query($q);
		$name = $this->db_fetch_field("SELECT name FROM objects where oid = $id","name");
		$this->_log(ST_EXTLINK, SA_CLICK, $name, $id);
	}
}
?>
