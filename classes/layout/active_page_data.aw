<?php
// $Header: /home/cvs/automatweb_dev/classes/layout/active_page_data.aw,v 1.7 2003/11/13 11:10:09 kristo Exp $
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
			$so = obj(aw_global_get("section"));
			$o_path = $so->path();
			$cur_path = array();
			foreach($o_path as $o)
			{
				$cur_path[] = $o->id();
			}
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
