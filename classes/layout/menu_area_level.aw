<?php
// $Header: /home/cvs/automatweb_dev/classes/layout/Attic/menu_area_level.aw,v 1.9 2005/02/09 16:28:25 duke Exp $
/*

@classinfo syslog_type=ST_MENU_AREA_LEVEL relationmgr=yes

@default table=objects
@default group=general

@default field=meta
@default method=serialize

@property first_item_style type=relpicker reltype=RELTYPE_STYLE
@caption Esimese men&uuml;&uuml; stiil

@property item_style type=relpicker reltype=RELTYPE_STYLE
@caption Men&uuml;&uuml; stiil

@property last_item_style type=relpicker reltype=RELTYPE_STYLE
@caption Viimase men&uuml;&uuml; stiil

@property sel_item_style type=relpicker reltype=RELTYPE_STYLE
@caption Valitud men&uuml;&uuml; stiil

@property first_sel_item_style type=relpicker reltype=RELTYPE_STYLE
@caption Valitud esimese men&uuml;&uuml; stiil

@property last_sel_item_style type=relpicker reltype=RELTYPE_STYLE
@caption Valitud viimase men&uuml;&uuml; stiil

@property separator type=textbox size=10
@caption Erldaja

@property has_sub_menus type=checkbox ch_value=1
@caption Kas j&auml;rgmise taseme men&uuml&uuml;d on selle taseme men&uuml;&uuml;de vahel

@property has_sub_menus_sep type=textbox 
@caption J&auml;rgmise taseme men&uuml;&uuml;de eraldaja

@property pre_image type=relpicker reltype=RELTYPE_IMAGE
@caption Men&uuml;&uuml; pilt

@property show_comment type=checkbox ch_value=1
@caption N&auml;ita kommentaari

@property comment_sep type=textbox
@caption Kommentaari eraldaja

@reltype STYLE value=1 clid=CL_CSS
@caption stiil

@reltype IMAGE value=2 clid=CL_IMAGE
@caption pilt

*/

class menu_area_level extends class_base
{
	function menu_area_level()
	{
		$this->init(array(
			'tpldir' => 'layout/menu_area_level',
			'clid' => CL_MENU_AREA_LEVEL
		));
		$this->mned = get_instance("contentmgmt/site_content");
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object.
	function show($arr)
	{
		extract($arr);
		$this->ob = obj($id);

		// ok, here we need to figure out if this level's parent menu is active. how the hell do we do that?
		// ok, first get the path
		// then crawl through the path, until we find the root menu of the menu_area that this object is attached to
		// and then we can get the active parent, cause it is in the path at level root_menu_level + this_level
		$sg = get_instance("layout/active_page_data");
		$path = $sg->get_active_path();

		$ma = get_instance("layout/menu_area");
		$rootmenu = $ma->get_root_menu($this->ob->meta("menu_area"));

		if ($this->ob->meta('has_sub_menus'))
		{
			$next_level_id = $ma->get_next_level_id(array(
				"id" => $this->ob->meta("menu_area"),
				"cur_level" => $this->ob->meta('level')
			));
		}

		if ($this->ob->meta('pre_image'))
		{
			$im = get_instance("image");
			$imd = $im->get_image_by_id($this->ob->meta('pre_image'));
			$imstr = image::make_img_tag(image::check_url($imd["url"]));
		}

		$root_menu_level = 0;
		foreach($path as $level => $oid)
		{
			if ($oid == $rootmenu)
			{
				// we got the root menu level
				$root_menu_level = $level;
				break;
			}
		}

		$cont = "";
		if (($root_menu_level && ($parent = $path[$root_menu_level+$this->ob->meta('level')])) || ($this->ob->meta('level') == 0) || $force_show)
		{
			if ($force_show)
			{
				$parent = $force_parent;
			}
			if ($this->ob->meta('level') == 0)
			{
				$parent = $rootmenu;
			}

			// now! show all the menus that are under $parent!
			$mc = get_instance("menu_cache");
			$menus = $mc->get_cached_menu_by_parent($parent);
			$names = array();
			$cnt_menus = count($menus);
			foreach($menus as $idx => $menu_data)
			{
				$names[] = $prepend_sep.$imstr.$this->draw_menu_item(array(
					"data" => $menu_data,
					"total" => $cnt_menus,
					"cur" => $idx,
					"active" => in_array($menu_data["oid"], $path)
				));

				if ($this->ob->meta('has_sub_menus') && $next_level_id)
				{
					// find next level menus and draw them
					$nl = get_instance("layout/menu_area_level");
					$names[] = $nl->show(array(
						"id" => $next_level_id,
						"force_show" => true,
						"force_parent" => $menu_data["oid"],
						"prepend_sep" => $prepend_sep.$this->ob->meta('has_sub_menus_sep')
					));
				}
			}
			return join($this->ob->meta('separator'), $names);
		}

		return "";
	}

	function get_menu_link($mdat)
	{
		return $this->mned->make_menu_link($mdat);
	}

	////
	// !draws one single menu item
	// params: 
	//   data - menu item data
	//   total - count of items on this level
	//   cur - current menu number on this level
	//   active - bool, if true, this is an active menu
	function draw_menu_item($arr)
	{
		extract($arr);
		// right. check if there is a style for this menu
		$style_name = "item_style";
		if ($cur == 0)
		{
			$style_name = "first_".$style_name;
		}
		else
		if ($cur == ($total-1))
		{
			$style_name = "last_".$style_name;
		}
		if ($active)
		{
			$style_name = "sel_".$style_name;
		}

		// if a style for this item that matches the type exactly is not set, then use the default
		if (!($stylid = $this->ob->meta($style_name)))
		{
			$stylid = $this->ob->meta("item_style");
		}
		
		$link = html::href(array(
			"url" => $this->get_menu_link($data),
			"caption" => $data["name"]
		));

		if ($this->ob->meta("show_comment") && $data["comment"] != "")
		{
			$link .= $this->ob->meta("comment_sep").$data["comment"];
		}

		if ($stylid)
		{
			active_page_data::add_site_css_style($stylid);
			return html::span(array(
				"class" => "st".$stylid,
				"content" => $link
			));
		}
		else
		{
			return $link;
		}
	}

	function get_property($arr)
	{
		$prop =& $arr['prop'];
		return PROP_OK;
	}
}
?>
