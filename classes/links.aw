<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/links.aw,v 2.56 2004/06/08 13:45:41 kristo Exp $

/*

@groupinfo Javascript caption=Javascript
@groupinfo Pilt caption=Pilt

@tableinfo extlinks index=id master_table=objects master_index=oid

@default group=general

@property comment type=textarea cols=30 rows=5 table=objects 
@caption Kommentaar lingikogusse

@default table=extlinks

@property url type=textbox 
@caption URL

@property docid type=hidden table=extlinks 

@property hits type=text 
@caption Klikke

@property url_int_text type=text store=no
@caption Saidi sisene link

@property alt type=textbox table=objects field=meta method=serialize search=1
@caption Alt tekst

@property newwindow type=checkbox ch_value=1 search=1
@caption Uues aknas

@property doclinkcollection type=checkbox ch_value=1  
@caption Dokumendi lingikogusse

@default group=Javascript
@default table=objects
@default field=meta
@default method=serialize

@property use_javascript type=checkbox ch_value=1 search=1
@caption Kasuta javascripti

@property newwinwidth type=textbox ch_value=1  
@caption Uue akna laius

@property newwinheight type=textbox ch_value=1 
@caption Uue akna k&otilde;rgus

@property newwintoolbar type=checkbox ch_value=1  
@caption Toolbar

@property newwinlocation type=checkbox ch_value=1  
@caption Address bar

@property newwinmenu type=checkbox ch_value=1  
@caption Men&uuml;&uuml;d

@property newwinscroll type=checkbox ch_value=1  
@caption Skrollbarid

@default group=Pilt

@property link_image type=fileupload store=no editonly=1
@caption Pilt

@property link_image_show type=text store=no editonly=1
@caption 

@property link_image_check_active type=checkbox ch_value=1 
@caption Pilt aktiivne

@property link_image_active_until type=date_select 
@caption Pilt aktiivne kuni

@classinfo no_status=1

*/

class links extends class_base
{
	function links()
	{
		$this->init(array(
			"tpldir" => "automatweb/extlinks",
			"clid" => CL_EXTLINK,
		));

		$this->lc_load("extlinks","lc_extlinks");
	}

	/**  
		
		@attrib name=search_doc params=name 
		
		@param s_name optional
		@param s_content optional

		@returns


		@comment

	**/
	function search_doc($arr)
	{
		extract($arr);
		$this->read_template("search_doc.tpl");

		if ($s_name != "" || $s_content != "")
		{

			load_vcl("table");
			$t = new aw_table(array(
				"layout" => "generic"
			));
			$t->define_field(array(
				"name" => "pick",
				"caption" => "Vali see",
			));
			$t->define_field(array(
				"name" => "name",
				"caption" => "Nimetus",
				"sortable" => 1
			));
			$t->define_field(array(
				"name" => "parent",
				"caption" => "Asukoht",
				"sortable" => 1
			));
			$t->define_field(array(
				"name" => "createdby",
				"caption" => "Looja",
				"sortable" => 1
			));
			$t->define_field(array(
				"name" => "modified",
				"caption" => "Viimati muudetud",
				"type" => "time",
				"format" => "d.m.Y / H:i",
				"sortable" => 1
			));

			$sres = new object_list(array(
				"class_id" => CL_DOCUMENT,
				"name" => "%".$s_name."%",
				"content" => "%".$s_content."%"
			));
			for($o =& $sres->begin(); !$sres->end(); $o =& $sres->next())
			{
				if (aw_ini_get("menuedit.long_section_url"))
				{
					$url = "/".$this->cfg["index_file"].".".$this->cfg["ext"]."/section=".$o->id();
				}
				else
				{
					$url = "/".$o->id();
				}
				$name = strip_tags($o->name());
				$name = str_replace("'","",$name);

				$row["pick"] = html::href(array(
					"url" => 'javascript:ss("'.$url.'","'.$o->name().'")',
					"caption" => "Vali see"
				));
				$row["name"] = html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id())),
					"caption" => $o->name()
				));
				$row["parent"] = $o->path_str(array(
					"max_len" => 4
				));
				$_tmp = $o->createdby();
				$row["createdby"] = $_tmp->name();
				$row["modified"] = $o->modified();
				$t->define_data($row);

			}

			$t->set_default_sortby("name");
			$t->sort_by();
			$this->vars(array("LINE" => $t->draw()));
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
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=show params=name nologin="1" 
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function show($arr)
	{
		extract($arr);
		$link = obj($id);
		$this->add_hit($id,aw_global_get("HTTP_HOST"),aw_global_get("uid"));
		$url = $link->prop("url");
		if ($url == "" && $link->prop("docid") != "")
		{
			$url = "/".$link->prop("docid");
		}
		header("Location: ".$url);
		header("Content-type: ");
		exit;
	}
	
	function get_property(&$arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "link_image_show":
				$img = new object_list(array(
					"parent" => $arr["obj_inst"]->id(),
					"class_id" => CL_FILE
				));
				if ($img->count() > 0)
				{
					$o =& $img->begin();
					$f = get_instance("file");
					if ($f->can_be_embedded($o))
					{
						$prop['value'] = html::img(array(
							'url' => file::get_url($o->id(),$o->name())
						));
					}
				}
				break;

			case "url_int_text":
				$this->read_template("intlink.tpl");
				$this->vars(array(
					'search_doc' => $this->mk_my_orb('search_doc')
				));
				$prop['value'] = $this->parse();
				break;
	
			case "url":
				if ($prop["value"] == "" && $arr["obj_inst"]->prop("docid") != "")
				{
					$prop["value"] = "/".$arr["obj_inst"]->prop("docid");
				}
				break;
		}
		return $retval;
	}

	function set_property(&$arr)
	{
		$prop = $arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "link_image":
				$old_file = 0;

				$img = new object_list(array(
					"parent" => $arr["obj_inst"]->id(),
					"class_id" => CL_FILE
				));
				if ($img->count() > 0)
				{
					$o =& $img->begin();
					$old_file = $o->id();
				}

				$f = get_instance("file");
				$f->add_upload_image("link_image", $arr['obj_inst']->id(), $old_file);
				$retval = PROP_IGNORE;
				break;
		};
		return $retval;
	}

	////
	// !Hoolitseb ntx doku sees olevate extlinkide aliaste parsimise eest (#l2#)
	function parse_alias($args = array())
	{
		extract($args);

		$this->img = false;

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
			"real_link" => $this->real_link
		);
		if (isset($tpls["link"]))
		{
			$replacement = trim(localparse($tpls["link"],$vars));
		}
		else
		{
			if ($this->img)
			{
				$replacement = sprintf("<a href='%s' %s alt='%s' title='%s'><img src='%s' alt='%s' border='0'></a>",$url,$target,$this->cur_link->prop("alt"),$this->cur_link->prop("alt"),$this->img,$this->cur_link->prop("alt"));
			}
			else
			{
				$replacement = sprintf("<a href='%s' %s alt='%s' title='%s'>%s</a>",$url,$target,$this->cur_link->prop("alt"),$this->cur_link->prop("alt"),$caption);
			}
		};
		$this->img = "";
		return $replacement;
	}

	function draw_link($target)
	{
		$link = obj($target);
		$this->cur_link = $link;

		if (strpos($link->prop("url"),"@") > 0)
		{
			$linksrc = $link->prop("url");
		}
		elseif (aw_ini_get("extlinks.directlink") == 1)
		{
			$linksrc = $link->prop("url");
		}
		else
		{
			$linksrc = aw_ini_get("baseurl")."/".$link->id();
		};
		$this->real_link = $link->prop("url");

		if ($link->prop("link_image_check_active") && ($link->prop("link_image_active_until") < 100 || $link->prop("link_image_active_until") >= time()) )
		{
			$img = new object_list(array(
				"parent" => $link->id(),
				"class_id" => CL_FILE
			));

			$awf = get_instance("file");
			if ($img->count() > 0 && $awf->can_be_embedded($o =& $img->begin()))
			{
				$img = $awf->get_url($o->id(),"");
				//$img = "<img border='0' src='$img' alt='".$link->prop("alt")."' title='".$link->prop("alt")."' />";
			}
			else
			{
				$img = "";
			};

			$this->img = $img;
		}

		if ($link->prop("use_javascript"))
		{
			$target = sprintf("onClick='javascript:window.open(\"%s\",\"w%s\",\"toolbar=%d,location=%d,menubar=%d,scrollbars=%d,width=%d,height=%d\")'",
				$linksrc,
				$link->id(),
				$link->prop("newwintoolbar"),
				$link->prop("newwinlocation"),
				$link->prop("newwinmenu"),
				$link->prop("newwinscroll"),
				$link->prop("newwinwidth"),
				$link->prop("newwinheight")
			);
			$url = "javascript:void(0)";
		}
		else
		{
			$url = $linksrc;
			$target = $link->prop("newwindow") ? "target='_blank'" : "";
		};


		return array($url,$target,$link->name());
	}
	
	// registreerib kliki lingile
	// peab ehitama ka mehhanisimi sp�mmimise v�ltimiseks
	function add_hit($id,$host,$uid) 
	{
		/*$o = obj($id);
		if ($o->can("edit"))
		{
			$o->set_prop("hits", $o->prop("hits")+1);
			// this would clear the entire site cache and nothing can possibly change from this, so I'm commenting this out.
			// does anyone really need this hit count thingie anyway?
			$o->save();
		}*/

		$this->_log(ST_EXTLINK, SA_CLICK, $o->name(), $id);
	}

	function request_execute($obj)
	{
		$this->show(array("id" => $obj->id()));
	}
}
?>
