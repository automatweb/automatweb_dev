<?php

global $orb_defs;
$orb_defs["currency"] = "xml";

class currency extends aw_template
{
	function currency()
	{
		$this->db_init();
		$this->tpl_init("currency");
		$this->sub_merge = 1;
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add_currency.tpl");
		$this->mk_path($parent, "Lisa valuuta");

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
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $ratio));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_CURRENCY, "name" => $name, "comment" => $ratio));
		}
		return $id;
	}

	function change($arr)
	{
		extract($arr);
		$o = $this->get_object($id);
		$this->mk_path($o["parent"], "Muuda valuutat");
		$this->read_template("add_currency.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
		return $this->parse();
	}
}
?>