<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/guestbook.aw,v 2.9 2004/06/11 09:16:23 kristo Exp $

class guestbook extends aw_template
{
	function guestbook()
	{
		$this->init("guestbook");
		$this->sub_merge = 1;
		lc_load("definition");
		$this->lc_load("guestbook","lc_guestbook");
	}

	////
	// Oh, puh-lease, by now you really ought to know what this function is for
	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->gbaliases))
		{
			$this->gbaliases = $this->get_aliases(array(
				"oid" => $oid,
				"type" => CL_GUESTBOOK,
			));
		};
		$g = $this->gbaliases[$matches[3] - 1];
		$replacement = $this->draw($g["target"]);
		return $replacement;
	}

	/**  
		
		@attrib name=new params=name default="0"
		
		@param parent required acl="add"
		@param docid optional
		
		@returns
		
		
		@comment

	**/
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, LC_GUESTBOOK_ADD);
		$this->read_template("add.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit_gb", array("parent" => $parent,"docid" => $docid))));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_gb params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_gb($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
			if (is_array($comments))
			{
				reset($comments);
				while (list($id,$comment) = each($comments))
				{
					if ($erase[$id] == 1)
					{
						$tmp = obj($id);
						$tmp->delete();
					}
					else
					{
						$this->upd_object(array("oid" => $id, "comment" => $comment));
					}
				}
			}
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_GUESTBOOK, "name" => $name, "comment" => $comment));
			if ($docid)
			{
				$this->add_alias($docid,$id);
			}
		}
		if ($docid)
		{
			return $this->mk_orb("change", array("id" => $docid), "document");
		}
		return $this->mk_orb("obj_list", array("parent" => $parent), "menuedit");
	}

	/**  
		
		@attrib name=change params=name default="0"
		
		@param id required acl="edit;view"
		@param parent optional
		@param docid optional
		
		@returns
		
		
		@comment

	**/
	function change($arr)
	{
		extract($arr);
		$this->read_template("change.tpl");
		$this->mk_path($parent, LC_GUESTBOOK_CHANGE);
		$o = $this->get_object($id);

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_GUESTBOOK_ENTRY." AND status != 0 AND parent = $id");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"name" => $row["name"], 
				"email" => $row["last"], 
				"comment" => $row["comment"], 
				"date" => $this->time2date($row["created"], 2), "id" => $row["oid"]
			));
			$this->parse("ENTRY");
		}
		$this->vars(array(
			"name" => $o["name"], 
			"comment" => $o["comment"],
			"reforb" => $this->mk_reforb("submit_gb", array("id" => $id, "parent" => $parent, "docid" => $docid))
		));

		return $this->parse();
	}

	function draw($id)
	{
		$this->read_template("show.tpl");

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_GUESTBOOK_ENTRY." AND status != 0 AND parent = $id");
		while ($row = $this->db_next())
		{
			$from = htmlspecialchars($row["name"]);
			if ($row["last"] != "")
			{
				$from = "<a href='mailto:".htmlspecialchars($row["last"])."'>$from</a>";
			}
			$this->vars(array(
				"from" => $from, 
				"message" => nl2br(htmlspecialchars($row["comment"])), 
				"date" => $this->time2date($row["created"], 2)
			));
			$this->parse("ENTRY");
		}
		$this->vars(array(
			"reforb" => $this->mk_reforb("add_entry", array("id" => $id, "url" => urlencode(aw_global_get("REQUEST_URI"))))
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=add_entry params=name default="0"
		
		@param id required
		@param url required
		
		@returns
		
		
		@comment

	**/
	function add_entry($arr)
	{
		extract($arr);

		$this->new_object(array("parent" => $id, "class_id" => CL_GUESTBOOK_ENTRY, "name" => $name, "last" => $email, "comment" => $comment));

		return $this->cfg["baseurl"].urldecode($url);
	}
}
?>
