<?php





class mingi extends aw_template
{
	function mingi()
	{
		$this->init("mingi");
	}

	function orb_teeseda($arr)
	{
		extract($arr);

		return "blabla";
	}

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, "Lisa miski");
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

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);

		$dat = $this->get_object($id);

		$this->mk_path($dat["parent"], "Muuda midagi");
		$this->read_template("add.tpl");

		$this->vars(array(
			"name" => $dat["name"],
			"age" => $dat["meta"]["age"],
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
			"age" => $dat["meta"]["age"]
		));

		return $this->parse();
	}
}
?>
