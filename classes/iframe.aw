<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/iframe.aw,v 2.6 2003/01/26 20:49:34 duke Exp $
// iframe.aw - iframes

/*

	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property url type=textbox size=40 
	@caption URL

	@property width type=textbox size=4 maxlength=4
	@caption Laius

	@property height type=textbox size=4 maxlength=4
	@caption Kõrgus

	@property frameborder type=checkbox value=1 ch_value=1 
	@caption Ümbritseda raamiga

	@property scrolling type=select 
	@caption Kerimisribad

*/
class iframe extends class_base
{
	function iframe() 
	{
		$this->init(array(
			"tpldir" => "iframe",
			"clid" => CL_HTML_IFRAME,
		));
		// defaults
		$this->default_width = 300;
		$this->default_height = 300;
		
		// minimum values
		$this->min_width = 30;
		$this->min_height = 30;

		// max values
		$this->max_width = 600;
		$this->max_height = 600;

		global $lc_iframe;
		lc_load("iframe");

		$this->lc_load("iframe","lc_iframe");


	}

	////
	// !Fetches an iframe object from database and returns it
	function _get_iframe($id)
	{
		return $this->get_object(array(
				"oid" => $id,
				"class_id" => CL_HTML_IFRAME,
				"unserialize_meta" => 1,
		));
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "scrolling":
				$data["options"] = array(
					"yes" => IFRAME_SCROLL_YES,
					"auto" => IFRAME_SCROLL_AUTO,
					"no" => IFRAME_SCROLL_NO,
				);
				break;

			case "width":
				if (!$args["obj"]["oid"])
				{
					$data["value"] = $this->default_width;
				};
				break;

			case "height":
				if (!$args["obj"]["oid"])
				{
					$data["value"] = $this->default_height;
				};
				break;
		};
		return $retval;
	}

	function set_property($args = array())
	{
                $data = &$args["prop"];
		$form_data = &$args["form_data"];
                $retval = PROP_OK;
                switch($data["name"])
                {
			case "width":
				if ($form_data["width"] > $this->max_width)
				{
					$form_data["width"] = $this->max_width;
				};
				if ($form_data["width"] < $this->min_width)
				{
					$form_data["width"] = $this->min_width;
				};
				break;

			case "height":
				if ($form_data["height"] > $this->max_height)
				{
					$form_data["height"] = $this->max_height;
				};
				if ($form_data["height"] < $this->min_height)
				{
					$form_data["height"] = $this->min_height;
				};
				break;
		};
		return $retval;
	}

	////
	// !Parses an iframe alias
	function parse_alias($args = array())
	{
		extract($args);
		if (not($alias["target"]))
		{
			return "";
		};
			
		$obj = $this->_get_iframe($alias["target"]);

		$this->read_adm_template("iframe.tpl");

		$align = array(
			"" => "",
			"v" => "left",
			"k" => "center",
			"p" => "right",
		);

		$this->vars(array(
			"url" => $obj["meta"]["url"],
			"width" => $obj["meta"]["width"],
			"height" => $obj["meta"]["height"],
			"scrolling" => $obj["meta"]["scrolling"],
			"frameborder" => $obj["meta"]["frameborder"],
			"comment" => $obj["meta"]["comment"],
			"align" => $align[$matches[4]], // that's where the align char is
		));

		return $this->parse();
	}
	
}
?>
