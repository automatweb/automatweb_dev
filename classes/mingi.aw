<?php

class mingi extends aw_template
{
	function mingi()
	{
		enter_function("mingi::mingi",array());
		$this->init("mingi");
		exit_function("mingi::mingi");
	}

	function orb_teeseda($arr)
	{
		enter_function("mingi::orb_teeseda",array());
		extract($arr);

		exit_function("mingi::orb_teeseda");
		return "blabla";
	}

	function add($arr)
	{
		enter_function("mingi::add",array());
		extract($arr);
		$this->mk_path($parent, "Lisa miski");
		$this->read_template("add.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to))
		));

		exit_function("mingi::add");
		return $this->parse();
	}

	function submit($arr)
	{
		enter_function("mingi::submit",array());
		extract($arr);

		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"metadata" => array(
					"age" => $age
				)
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_MINGI,
				"name" => $name,
				"metadata" => array(
					"age" => $age
				)
			));

			if ($alias_to)
			{
				$this->add_alias($alias_to, $id);
			}
		}

		exit_function("mingi::submit");
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		enter_function("mingi::change",array());
		extract($arr);

		$dat = $this->get_object($id);

		$this->mk_path($dat["parent"], "Muuda midagi");
		$this->read_template("add.tpl");

		$this->vars(array(
			"name" => $dat["name"],
			"age" => $dat["meta"]["age"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));

		exit_function("mingi::change");
		return $this->parse();
	}

	function parse_alias($arr)
	{
		enter_function("mingi::parse_alias",array());
		extract($arr);

		$dat = $this->get_object($alias["target"]);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $dat["name"],
			"age" => $dat["meta"]["age"]
		));

		exit_function("mingi::parse_alias");
		return $this->parse();
	}
}
?>