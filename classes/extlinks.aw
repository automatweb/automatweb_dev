<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/extlinks.aw,v 2.7 2001/09/28 14:47:08 duke Exp $
// extlinks.aw - Väliste linkide haldamise klass
lc_load("extlinks");


class extlinks extends aw_template {

	function extlinks($args = array())
	{
		$this->db_init();
		$this->tpl_init();
		lc_load("definition");
		
		global $lc_extlinks;
		if (is_array($lc_extlinks))
		{
			$this->vars($lc_extlinks);
		}
		
		
	

	}
	 
	
  
  // lisab lingi objekti och dokumendi juurde
	function add_link($args) {
	  extract($args);
		if ($type == "")
		{
			$type = "ext";
		}
		$q = "INSERT INTO extlinks (id,oid,url,name,hits,newwindow,descript,type,docid,doclinkcollection) 
						VALUES('$id','$oid','$url','$name','$hits','$newwindow','$desc','$type','$docid','$doclinkcollection')";
		$this->db_query($q);
		$this->_log("link",sprintf(LC_EXTLINKS_ADD_LINK,$name));

		

	}

	////
	// !Hoolitseb ntx doku sees olevate extlinkide aliaste parsimise eest (#l2#)
	function parse_alias($args = array())
	{
		extract($args);
		// koigepealt siis kysime koigi extrnal linkide aliased. 
		// peale esimest kaivitamist jatame nad meelde, et edaspidi ei peaks rohkem paringuid tegema
		if (!is_array($this->extlinkaliases))
		{
			$this->extlinkaliases = $this->get_aliases(array(
							"oid" => $oid,
							"type" => CL_EXTLINK,
					));
		};

		// now, match[3] contains the index inside the aliases array
		$l = $this->extlinkaliases[$matches[3] - 1];
		$link = $this->get_link($l["target"]);
		$this->dequote(&$link);

		global $baseurl,$ext;
		$linksrc = sprintf("%s/indexx.%s?id=%d",$baseurl,$ext,$link["id"]);
		if ($link["use_javascript"])
		{
			$target = sprintf("onClick='javascript:window.open(\"%s\",\"w%s\",\"toolbar=%d\")'",$linksrc,$link["id"],$link["newwintoolbar"]);
			$url = "#";
		}
		else
		{
			$url = $linksrc;
			$target = $link["newwindow"] ? "target='_blank'" : "";
		};
		$vars = array(
				"url" => $url,
				"caption" => $link["name"],
				"target" => $target,
			);


		$replacement = $this->localparse($tpls["link"],$vars);
		return $replacement;
	}

	////
	// !resetib aliased
	function reset_aliases()
	{
		$this->extlinkaliases = "";
	}

	function save_link($args) {
		$this->quote($args);
		extract($args);
		if ($type == "")
		{
			$type = "ext";
		}
		$q = "UPDATE extlinks 
			SET name = '$name',
			    url = '$url',
			    descript = '$desc',
					newwindow = '$newwindow',
					type = '$type',
					doclinkcollection = '$doclinkcollection',
					docid = '$docid'
			WHERE id = '$lid'";
		$this->db_query($q);
		$this->upd_object(array("oid" => $lid,
					"name" => $name));
		$this->_log("link",sprintf(LC_EXTLINKS_CHANGED_LINK,$name));
	}

	function get_link($id) {
		$q = "SELECT extlinks.*,objects.* FROM extlinks LEFT JOIN objects ON objects.oid = extlinks.id WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_fetch_row();
		$meta = $this->obj_get_meta(array("meta" => $row["meta"]));
		$row = array_merge($row,$meta);
		
		if ($row["type"] == "int")
		{
			$row["url"] = $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."?section=".$row["docid"];
		}
		return $row;
	}

  // registreerib kliki lingile

	// peab ehitama ka mehhanisimi spämmimise vältimiseks
	function add_hit($id,$host,$uid) {
		$q = "UPDATE extlinks
							SET hits = hits + 1
							WHERE id = '$id'";
		$this->db_query($q);
		$t = time();
		$q = "INSERT INTO extlinkstats (lid,tm,host,uid) 
						VALUES ('$id',$t,'$host','$uid')";
		$this->db_query($q);
		$name = $this->db_fetch_field("SELECT name FROM objects where oid = $id","name");
		$this->_log("link",sprintf(LC_EXTLINKS_CLIK_LINK,$name));
	}

};
?>
