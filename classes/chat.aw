<?php

class chat extends aw_template
{
	function chat()
	{
		$this->init("chat");
		//$this->db_init();
	}

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, "Lisa jutuka objekt");                                                                             	

		$this->read_template("add.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to))
		));

		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $nimi,
				"metadata" => array(
					"host" => $host,
					"port" => $port,
					"nimi" => $nimi
				)
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"class_id" =>  CL_CHAT_LIST,
				"name" => $nimi,
				"metadata" => array(
					"host" => $host,
					"port" => $port,
					"nimi" => $nimi
				)
			));

			if ($alias_to)
			{
				$this->add_alias($alias_to, $id);
			}
		}

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);

		$dat = $this->get_object($id);

		$this->mk_path($dat["parent"], "Muuda objekti");
		$this->read_template("add.tpl");

		$this->vars(array(
			"name" => $dat["name"],
			"host" => $dat["meta"]["host"],
			"port" => $dat["meta"]["port"],
			"nimi" => $dat["meta"]["nimi"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));

		return $this->parse();
	}

	function parse_alias($arr)
	{
		extract($arr);

		$dat = $this->get_object($alias["target"]);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $dat["name"],
			"uid" => aw_global_get("uid"),
			"host" => $dat["meta"]["host"],
			"port" => $dat["meta"]["port"],
			"nimi" => $dat["meta"]["nimi"]
		));

		return $this->parse();
	}
}
?>