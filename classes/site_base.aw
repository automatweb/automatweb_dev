<?php

// this is the base class for the "site" class where you can put site-specific functionality
// and that has callbacks and hooks for all the actions you could ever want, so you can intercept aw 
// when you need to do something site specific
classload("aw_template");
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
	// these functions will get called BEFORE any menu parsing or document parsing take place
	function get_sub_callbacks() 
	{
		return array();
	}

	////
	// !this will get called one per pageview
	// it may return an array of subtemplate_name => function_name pairs
	// that will get executed if the subtemplates exist in main.tpl and
	// their output will replace the subtemplates
	// the functions must be members of the site class
	// these functions will get called AFTER any menu parsing or document parsing take place
	function get_sub_callbacks_after() 
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
	// !this gets called when showing documents - if it returns false, the document is not shown
	function can_show_document(&$doc)
	{
		return true;
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
		$m = get_instance("contentmgmt/site_cache");
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
		return $m->show($arr);
	}

	function get_page_template()
	{
		return "main.tpl";
	}
}

?>
