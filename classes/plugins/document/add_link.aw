<?php
class add_link extends aw_template
{
	// that is just a generic add link plugin for documents
	function add_link()
	{
		$this->init("");
	}

	function get_property()
	{
		print "getting property!";
	}


	function show($args = array())
	{
		return !empty($args["value"]) ? $args["tpl"] : "";
	}

	////
	// !that thingie is needed until the class_base based document class is not
	// yet ready to replace the old static one.
	function get_static_property($args = array())
	{
		return html::checkbox(array(
			"caption" => "Lisa link n�htav",
			"checked" => !empty($args["value"]),
			"name" => "plugins[" . get_class($this) . "]",
		));
	}
}
?>
