<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/guestbook.aw,v 2.3 2001/06/14 08:47:39 kristo Exp $

global $orb_defs;
$orb_defs["guestbook"] = array("new" => array("function" => "add" , "params" => array("parent"), "opt" => array("docid")),
															 "submit_gb" => array("function" => "submit_gb", "params" => array()),
															 "change" => array("function" => "change", "params" => array("id"), "opt" => array("parent", "docid")),
															 "add_entry" => array("function" => "add_entry", "params" => array("id","url"))
															);

class guestbook extends aw_template
{
	function guestbook()
	{
		$this->tpl_init("guestbook");
		$this->db_init();
		$this->sub_merge = 1;
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

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, "Lisa k&uuml;lalisteraamat");
		$this->read_template("add.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit_gb", array("parent" => $parent,"docid" => $docid))));
		return $this->parse();
	}

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
						$this->delete_object($id);
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

	function change($arr)
	{
		extract($arr);
		$this->read_template("change.tpl");
		$this->mk_path($parent, "Muuda k&uuml;lalisteraamatut");
		$o = $this->get_object($id);

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_GUESTBOOK_ENTRY." AND status != 0 AND parent = $id");
		while ($row = $this->db_next())
		{
			$this->vars(array("name" => $row[name], "email" => $row[last], "comment" => $row[comment], "date" => $this->time2date($row[created], 2), "id" => $row[oid]));
			$this->parse("ENTRY");
		}
		$this->vars(array("name" => $o[name], "comment" => $o[comment],
											"reforb" => $this->mk_reforb("submit_gb", array("id" => $id, "parent" => $parent, "docid" => $docid))));

		return $this->parse();
	}

	function draw($id)
	{
		$this->read_template("show.tpl");

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_GUESTBOOK_ENTRY." AND status != 0 AND parent = $id");
		while ($row = $this->db_next())
		{
			$from = $row[name];
			if ($row[last] != "")
			{
				$from = "<a href='mailto:".$row[last]."'>$from</a>";
			}
			$this->vars(array("from" => $from, "message" => nl2br($row[comment]), "date" => $this->time2date($row[created], 2)));
			$this->parse("ENTRY");
		}
		$this->vars(array("reforb" => $this->mk_reforb("add_entry", array("id" => $id, "url" => urlencode($GLOBALS["REQUEST_URI"])))));
		return $this->parse();
	}

	function add_entry($arr)
	{
		extract($arr);

		$this->new_object(array("parent" => $id, "class_id" => CL_GUESTBOOK_ENTRY, "name" => $name, "last" => $email, "comment" => $comment));

		return $GLOBALS["baseurl"].urldecode($url);
	}
}
?>
