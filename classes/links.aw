<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/links.aw,v 2.27 2002/12/18 13:16:45 kristo Exp $

/*

@classinfo objtable=extlinks
@classinfo objtable_index=id

@default group=general

@property status type=status table=objects group=general
@caption Staatus

@property comment type=textarea cols=30 rows=5 table=objects field=comment group=general
@caption Kommentaar lingikogusse

@property url table=extlinks type=textbox field=url group=general
@caption URL

@property url_int_text type=text group=general
@caption Saidi sisene link

@property alt type=textbox table=objects field=meta method=serialize group=general
@caption Alt tekst

@property newwindow type=checkbox ch_value=1 table=extlinks field=newwindow group=general
@caption Uues aknas

@property doclinkcollection type=checkbox ch_value=1 table=extlinks field=doclinkcollection group=general
@caption Dokumendi lingikogusse

@property use_javascript type=checkbox ch_value=1 table=objects field=meta method=serialize group=Javascript
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

classload("extlinks");
class links extends extlinks
{
	function links()
	{
		$this->extlinks();
		$this->init("automatweb/extlinks");
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

	function _serialize($arr)
	{
		extract($arr);
		$this->db_query("
			SELECT 
				extlinks.url as e_url,
				extlinks.name as e_name,
				extlinks.hits as e_hits,
				extlinks.oid as e_oid,
				extlinks.newwindow as e_newwindow,
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
		$this->quote(&$row);
		$id = $this->new_object($row);
		$this->db_query("INSERT INTO extlinks(id,url,name,hits,oid,newwindow,doclinkcollection) VALUES($id,'".$row["e_url"]."','".$row["e_name"]."','".$row["e_hits"]."','".$row["e_oid"]."','".$row["e_newwindow"]."','".$row["e_doclinkcollection"]."')");
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
}
?>
