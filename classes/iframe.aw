<?php
// $Header
// iframe.aw - iframes
class iframe extends aw_template
{
	function iframe() 
	{
		$this->init("iframe");
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

	////
	// !Used for adding of changing iframe object
	function change($args = array())
	{
		// Note: we cannot really show a sample iframe here, because that would be a security risk
		// - if that URL is located on some other site, it gets the URL of this page as referer.
		
		extract($args);

		$id = sprintf("%d",$id);

		$cprefix = "";

		if ($id)
		{

			$obj = $this->_get_iframe($id);
			$prnt = $obj["parent"];
			$caption = IFRAME_CAPTION_CHANGE;
		}
		else
		{
			// set the defaults, if we have a new object
			$obj = array();
			$obj["meta"]["width"] = $this->default_width;
			$obj["meta"]["height"] = $this->default_height;
			$caption = IFRAME_CAPTION_ADD;
			$prnt = $parent;
		};

		// if we were called from inside the alias manager, set the parent to 0
		// and display the "back" link instead of the path
		if ($return_url)
		{
			$prnt = 0;
			$cprefix = sprintf("<a href='%s'>%s</a> / ",$return_url,IFRAME_RETURN_URL);
		};


		$this->mk_path($prnt,$cprefix . $caption);

		$scrolling = array(
			"yes" => IFRAME_SCROLL_YES,
			"auto" => IFRAME_SCROLL_AUTO,
			"no" => IFRAME_SCROLL_NO,
		);
		
		$this->read_template("change.tpl");

		$this->vars(array(
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"width" => $obj["meta"]["width"],
			"height" => $obj["meta"]["height"],
			"frameborder" => checked($obj["meta"]["frameborder"]),
			"url" => $obj["meta"]["url"],
			"min_width" => $this->min_width,
			"min_height" => $this->min_height,
			"max_width" => $this->max_width,
			"max_height" => $this->max_height,
			"scrolling" => $this->picker($obj["meta"]["scrolling"],$scrolling),
			"reforb" => $this->mk_reforb("submit",array("id" => $id,"parent" => $parent,"return_url" => $return_url,"alias_to" => $alias_to)),
		));
		return $this->parse();
	}


	////
	// !Submits a new or changed iframe object
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);

		if ($parent)
		{
			$id = $this->new_object(array(
				"name" => $name,
				"comment" => $comment,
				"parent" => $parent,
				"class_id" => CL_HTML_IFRAME,
			));

			$this->_log("iframe",sprintf(IFRAME_SYSLOG_ADD,$name),$id);
		}
		else
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
			));
			
			$this->_log("iframe",sprintf(IFRAME_SYSLOG_CHANGE,$name),$id);
		};

		if ( ($height < $this->min_height) || ($height > $this->max_height) )
		{
			$height = $this->default_height;
		}
		
		if ( ($width < $this->min_width) || ($width > $this->max_width) )
		{
			$width = $this->default_width;
		};


		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"url" => $url,
				"width" => $width,
				"height" => $height,
				"frameborder" => ($frameborder) ? $frameborder : 0,
				"scrolling" => $scrolling,
			),
		));

		if ($alias_to)
		{
			$this->delete_alias($alias_to,$id);
			$this->add_alias($alias_to,$id);
		}

		return $this->mk_my_orb("change",array("id" => $id,"return_url" => urlencode($return_url),"alias_to" => $alias_to));
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
