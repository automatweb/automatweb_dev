<?php

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
			$mn = get_instance("menuedit");
			$mn->make_menu_caches();
			$cur_path = $mn->get_path(aw_global_get("section"), $this->get_object(aw_global_get("section")));
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

	function on_shutdown_get_styles()
	{
		$styles = new aw_array(aw_global_get("__aw_site_styles"));
		$css = get_instance("css");

		$ret = "";
		foreach($styles->get() as $stylid)
		{
			$css_info = $this->get_obj_meta($stylid);
			$ret .= $css->_gen_css_style("st".$stylid,$css_info["meta"]["css"]);
		}

		if ($ret != "")
		{
			$ret = "<style type=\"text/css\">".$ret."</style>";
		}
		return $ret;
	}
}

?>
