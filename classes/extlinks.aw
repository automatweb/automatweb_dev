<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/extlinks.aw,v 2.29 2003/04/23 13:58:57 kristo Exp $
// extlinks.aw - Väliste linkide haldamise klass


class extlinks extends class_base
{
	function extlinks($args = array())
	{
		$this->init(array(
		    "tpldir" => "",
		    "clid" => CL_EXTLINK
		));
		lc_load("definition");
	
		$this->lc_load("extlinks","lc_extlinks");
	}
	
  
  // lisab lingi objekti och dokumendi juurde
	function add_link($args) 
	{
	  extract($args);
		$q = "INSERT INTO extlinks (id,oid,url,name,hits,newwindow,doclinkcollection) 
						VALUES('$id','$oid','$url','$name','$hits','$newwindow','$doclinkcollection')";
		$this->db_query($q);
		$this->_log(ST_EXTLINK, SA_ADD, $name, $id);
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
		elseif ($this->cfg["directlink"] == 1)
		{
			$linksrc = $link["url"];
		}
		else
		{
			$linksrc = $this->mk_my_orb("show", array("id" => $link["id"]),"links",false,true);
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

	function save_link($args) 
	{
		extract($args);
		if ($type == "")
		{
			$type = "ext";
		}
		$q = "UPDATE extlinks 
			SET name = '$name',
			    url = '$url',
					newwindow = '$newwindow',
					doclinkcollection = '$doclinkcollection',
			WHERE id = '$lid'";
		$this->db_query($q);
		$this->upd_object(array("oid" => $lid,"name" => $name));
		$this->_log(ST_EXTLINK, SA_CHANGE, $name, $lid);
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

};
?>
