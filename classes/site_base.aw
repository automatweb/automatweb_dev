<?php

// this is the empty template. start here please.
// you are supposed to use this when building new sites. 
// basically, you copy all the files to the new folder, change a few paths
// and fill this one in and you're good to go.

class site_base extends aw_template
{
	function site_base()
	{
		$this->init("");
	}

	////
	// !well, this is obviously for drawing the frontpage - iow, it will only get called 
	// on pageviews to the front page
	//
	// it must return the content of the front page
	//
	// btw, menus are not drawn on the front page by default. if you want them, then instead of
	// returnig the content, do this:
	// return $this->do_fp_menus_return($content);
	// it will draw the menus around the content
	function on_frontpage() 
	{
		return "";
	}

	////
	// !this will get called on every pageview and must return an array of
	// template_name => template_value pairs, that will be imported in the menu
	// drawing template
	function on_page() 
	{
		return array();
	}

	////
	// !this will get called one per pageview
	// it may return an array of subtemplate_name => function_name pairs
	// that will get executed if the subtemplates exist in main.tpl and
	// their output will replace the subtemplates
	// the functions must be members of the site class
	function get_sub_callbacks() 
	{
		return array();
	}

	////
	// !stub for parse_document, you get the chance to modify the contents of
	// the document for a particular site needs if you override this in subclass
	function parse_document(&$doc)
	{

	}

	////
	// !stub for parse_search_result_document, allows to perform site specific
	// operations on search results (replacing aliases for example). I needed that
	// for hightechestonia.com
	function parse_search_result_document(&$doc)
	{

	}

	////
	// !this can be used in on_frontpage when we need to draw the menus on frontpage
	function do_fp_menus_return($ret,$arr = array())
	{
		$m = new menuedit(aw_ini_get("per_oid"));
		if (is_array($arr["vars"]))
		{
			$arr["vars"] += $this->on_page();
		}
		else
		{
			$arr["vars"] = $this->on_page();
		}
		$arr["text"] = $ret;
		if (is_array($arr["sub_callbacks"]))
		{
			$arr["sub_callbacks"] += $this->get_sub_callbacks();
		}
		else
		{
			$arr["sub_callbacks"] = $this->get_sub_callbacks();
		}
		return $m->gen_site_html($arr);
	}
}

?>
