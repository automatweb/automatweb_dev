<?php

global $orb_defs;
$orb_defs["forum"] = "xml";

class forum extends aw_template
{
	function forum()
	{
		$this->db_init();
		$this->tpl_init("msgboard");
		$this->sub_merge = 1;
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add_forum.tpl");
		$this->mk_path($parent,"Lisa foorum");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_FORUM, "name" => $name,"comment" => $comment));
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add_forum.tpl");
		$o = $this->get_object($id);
		$this->mk_path($o["parent"], "Muuda foorumit");
		$this->vars(array(
			"name" => $o["name"],
			"comment" => $o["comment"],
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
			"url" => $GLOBALS["baseurl"]."/comments.".$GLOBALS["ext"]."?action=topics&forum_id=".$id
		));
		$this->parse("CHANGE");
		return $this->parse();
	}
}
?>