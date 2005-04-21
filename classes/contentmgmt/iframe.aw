<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/iframe.aw,v 1.3 2005/04/21 08:48:48 kristo Exp $
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

	@property frameborder type=checkbox default=1 ch_value=1 
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
		$this->max_width = 6000;
		$this->max_height = 6000;

		global $lc_iframe;
		lc_load("iframe");

		$this->lc_load("iframe","lc_iframe");


	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
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
				if ($arr["new"])
				{
					$data["value"] = $this->default_width;
				};
				break;

			case "height":
				if ($arr["new"])
				{
					$data["value"] = $this->default_height;
				};
				break;
		};
		return $retval;
	}

	function set_property($arr)
	{
                $data = &$arr["prop"];
                $retval = PROP_OK;
                switch($data["name"])
                {
			case "width":
				if ($data["value"] > $this->max_width)
				{
					$data["value"] = $this->max_width;
				};
				if ($data["value"] < $this->min_width)
				{
					$data["value"] = $this->min_width;
				};
				break;

			case "height":
				if ($data["value"] > $this->max_height)
				{
					$data["value"] = $this->max_height;
				};
				if ($data["value"] < $this->min_height)
				{
					$data["value"] = $this->min_height;
				};
				break;
		};
		return $retval;
	}

	////
	// !Parses an iframe alias
	function parse_alias($arr)
	{
		extract($arr);
		if (not($alias["target"]))
		{
			return "";
		};

		$obj = new object($alias["target"]);
			
		$this->read_adm_template("iframe.tpl");

		$align = array(
			"" => "",
			"v" => "left",
			"k" => "center",
			"p" => "right",
		);

		$this->vars(array(
			"url" => $obj->prop("url"),
			"width" => $obj->prop("width"),
			"height" => $obj->prop("height"),
			"scrolling" => $obj->prop("scrolling"),
			"frameborder" => $obj->prop("frameborder"),
			"comment" => $obj->comment(),
			"align" => $align[$matches[4]], // that's where the align char is
		));

		return $this->parse();
	}

	function request_execute($o)
	{
		die("<html><body topmargin=0 leftmargin=0 margintop=0 marginleft=0>".$this->parse_alias(array(
			"alias" => array("target" => $o->id())
		))."</body></html>");
	}	
}
?>
