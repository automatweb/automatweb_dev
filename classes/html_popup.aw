<?php
// html_popup.aw - a class to deal with javascript popups
// $Header: /home/cvs/automatweb_dev/classes/Attic/html_popup.aw,v 2.5 2003/01/26 21:16:07 duke Exp $

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property url type=textbox size=40 
	@caption Sisu (URL)

	@property width type=textbox size=4 maxlength=4
	@caption Laius

	@property height type=textbox size=4 maxlength=4
	@caption Kõrgus
	
	@property menus type=select multiple=1 size=20
	@caption Menüüd



*/
class html_popup extends class_base
{
	function html_popup($args = array())
	{
		$this->init(array(
			"tpldir" => "automatweb/html_popup",
			"clid" => CL_HTML_POPUP,
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "menus":
				$ob = get_instance("objects");
				$data["options"] = $ob->get_list();
				break;
		};
		return $retval;
	}
};
?>
