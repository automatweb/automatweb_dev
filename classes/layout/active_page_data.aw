<?php
// $Header: /home/cvs/automatweb_dev/classes/layout/active_page_data.aw,v 1.6 2003/10/13 13:09:25 duke Exp $
class active_page_data extends class_base
{
	function active_page_data()
	{
		$this->init();
	}

	function get_active_path()
	{
		static $cur_path = false;
		if (!is_array($cur_path))
		{
			$mn = get_instance("contentmgmt/site_content");
			$mn->make_menu_caches();
			$cur_path = $mn->get_path(aw_global_get("section"));
		}
		return is_array($cur_path) ? $cur_path : array();
	}

	function get_text_content($txt = "")
	{
		static $txt_content;
		if ($txt != "")
		{
			$txt_content = $txt;
		}
		return $txt_content;
	}

	function get_active_section()
	{
		static $active_section = -1;
		if ($active_section == -1)
		{
			$active_section = aw_global_get("section");
		}
		return $active_section;
	}

	function add_site_css_style($stylid)
	{
		$styles= aw_global_get("__aw_site_styles");
		$styles[$stylid] = $stylid;
		aw_global_set("__aw_site_styles", $styles);
	}

	function add_serialized_css_style($text)
	{
		$serialized_styles = aw_global_get("__aw_serialized_styles");
		$serialized_styles[] = $text;
		aw_global_set("__aw_serialized_styles", $serialized_styles);
	}

	function on_shutdown_get_styles()
	{
		$styles = new aw_array(aw_global_get("__aw_site_styles"));
		$css = get_instance("css");

		$ret = "";
		foreach($styles->get() as $stylid)
		{
			$ret .= $css->get_style_data_by_id($stylid);
		}

		$serialized_styles = new aw_array(aw_global_get("__aw_serialized_styles"));
		foreach($serialized_styles->get() as $styletext)
		{
			$ret .= $styletext;
		};

		if ($ret != "")
		{
			$ret = "<style type=\"text/css\">".$ret."</style>";
		}
		return $ret;
	}
}

?>
