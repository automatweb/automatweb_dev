<?php
class class_base extends aw_template
{

	function class_base($args = array())
	{
		$this->init("");
	}


	function add($args = array())
	{
		extract($args);
		$this->file = $class;
		$this->check_class();

		$cfg = get_instance("cfg/cfgmanager");
		return $cfg->change(array(
			"class_id" => $this->clid,
			"parent" => $parent,
			"orb_class" => $class,
		));

	}

	function change($args = array())
	{
		extract($args);
		$this->file = $class;
		$this->check_class();
		$cfg = get_instance("cfg/cfgmanager");
		return $cfg->change(array(
			"id" => $id,
			"orb_class" => $class,
			"group" => $group,
		));
	}

	function submit($args = array())
	{
		$this->file = $args["class"];
		$this->check_class();
		$cfg = get_instance("cfg/cfgmanager");
		return $cfg->submit($args);
	}

	////
	// !This decides whether to perform the requested action or not
	// acl checks for example
	function check_class()
	{
		$cfgu = get_instance("cfg/cfgutils");
		$has_properties = $cfgu->has_properties(array("file" => $this->file));
		if (!$has_properties)
		{
			die("this class does not have any defined properties ");
		};
	}
};
?>
