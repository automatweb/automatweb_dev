<?php
class add_comment extends aw_template
{
	// that is just a generic add link plugin for documents
	function add_comment()
	{
		$this->init("");
	}

	function get_property()
	{
		print "getting property!";
	}


	function show($args)
	{
		return !empty($args["value"]) ? $args["tpl"] : "";
	}

	////
	// !that thingie is needed until the class_base based document class is not
	// yet ready to replace the old static one.
	function get_static_property($args = array())
	{
		return html::checkbox(array(
			"caption" => "Lisa kommentaar nähtav",
			"checked" => !empty($args["value"]),
			"name" => "plugins[" . get_class($this) . "]",
		));
	}
}
?>
