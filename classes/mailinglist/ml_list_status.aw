<?php

classload("mailinglist/ml_queue");
class ml_list_status extends aw_template
{
	function ml_list_status()
	{
		$this->init("mailinglist/ml_list_status");
		$this->a_status=array(
			"0" => "uus",
			"1" => "pooleli",
			"2" => "valmis",
			"3" => "hetkel saadab",
			"4" => "peatatud"
		);
	}

	////
	// !called, when adding a new object 
	// parameters:
	//    parent - the folder under which to add the object
	//    return_url - optional, if set, the "back" link should point to it
	//    alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created
	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa ml_list_status");
		}
		else
		{
			$this->mk_path($parent,"Lisa ml_list_status");
		}
		$this->read_template("change.tpl");

		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => "Kustuta",
			"url" => "javascript:Do('queue_delete')",
			"imgover" => "delete_over.gif",
			"img" => "delete.gif"
		));
		$tb->add_button(array(
			"name" => "sendnow",
			"tooltip" => "Saada kohe",
			"url" => "javascript:Do('queue_send_now')",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));

		$this->vars(array(
			"toolbar" => $tb->get_toolbar(),
			"lists" => $this->mpicker(array(), $this->list_objects(array("class" => CL_ML_LIST))),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url))
		));
		return $this->parse();
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	//    id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_ML_LIST_STATUS
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"lists" => $this->make_keys($lists)
			)
		));

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda ml_list_status");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda ml_list_status");
		}
		$this->read_template("change.tpl");
	





		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "ml_queue",
		));
		
		$t->parse_xml_def($this->cfg["basedir"] . "/xml/mlist/queue.xml");

		$ml = get_instance("mailinglist/ml_list");
		$lists = $ml->get_lists_and_groups(array());//võta kõik listide & gruppide nimed, et polex vaja iga kord queryda

		$q = "SELECT * FROM ml_queue WHERE lid IN (".join(",", $ob["meta"]["lists"]).")";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			//echo("<pre>");print_r($row);echo("</pre>");//dbg
			$listname = $lists[$row["lid"].":0"];
			$groupids=explode("|",$row["gid"]);
			$gnames=array();
			foreach ($groupids as $v)
			{
				if ($v != "0" && $v)
				{
					$gnames[]=$lists[$row["lid"].":".$v];
				};
			};

			$row["lid"] = "<a href='javascript:remote(0,450,270,\"".$this->mk_my_orb("queue_change",array("id"=>$row["qid"]))."\");'>$listname</a>";
			if (sizeof($gnames)>0)
			{
				$row["lid"] .= ":".join(",",$gnames);
			};

			$this->save_handle();
			$row["mid"] = $this->db_fetch_field("SELECT name FROM objects WHERE oid='".$row["mid"]."'","name")."(".$row["mid"].")";
			$this->restore_handle();
			if (!$row["patch_size"])
			{
				$row["patch_size"]="kõik";
			};
			$row["delay"]/=60;
			$row["status"]=$this->a_status[$row["status"]];
			$row["protsent"]=$this->queue_ready_indicator($row["position"],$row["total"]);
			$row["vali"]="<input type='checkbox' NAME='sel[]' value='".$row["qid"]."'>";
			$t->define_data($row);
		};

		$t->sort_by();
		$queue=$t->draw();


		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => "Kustuta",
			"url" => "javascript:Do('queue_delete')",
			"imgover" => "delete_over.gif",
			"img" => "delete.gif"
		));
		$tb->add_button(array(
			"name" => "sendnow",
			"tooltip" => "Saada kohe",
			"url" => "javascript:Do('queue_send_now')",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));

		$this->vars(array(
			"toolbar" => $tb->get_toolbar(),
			"name" => $ob["name"],
			"res_tbl" => $queue,
			"lists" => $this->mpicker($ob["meta"]["lists"], $this->list_objects(array("class" => CL_ML_LIST))),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "from_mlm" => true, "return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row["parent"] = $parent;
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	////
	//! teeb progress bari
	// tegelt saax seda pitidega teha a siis tekib iga progress bari kohta oma query <img src=
	// see olex overkill kui on palju queue itemeid
	function queue_ready_indicator($osa,$kogu)
	{
		if (!$kogu)
		{
			$p=100;
		} 
		else
		{
			$p=(int)((int)$osa * 100 / (int)$kogu);
		};
		$not_p=100-$p;
		//echo("qri($osa,$kogu)=$p");//dbg
		
		// tekst pane sinna, kus on rohkem ruumi.
		if ($p>$not_p)
		{
			$p1t="<span Style='font-size:10px;font-face:verdana;'><font color='white'>".$p."%</font></span>";
		} 
		else
		{
			$p2t="<span Style='font-size:10px;font-face:verdana;'><font color='black'>".$p."%</font></span>";
		};
		// kommentaar on selleks, et sorteerimine töötaks (hopefully)
		return "<!-- $p --><table bgcolor='#CCCCCC' Style='height:12;width:100%'><tr><td width=\"$p%\" bgcolor=\"blue\">$p1t</td><td width=\"$not_p%\">$p2t</td></tr></table>";
	}
}
?>