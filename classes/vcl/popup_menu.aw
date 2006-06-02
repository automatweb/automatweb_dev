<?php

class popup_menu extends aw_template
{
	var $items;

	function popup_menu()
	{
		$this->init("vcl/popup_menu");
		$this->items = array();
	}
	/** 

		@attrib name=begin_menu params=pos api=1 

		@param menu_id required type=string
			String which identifies popup menu
		@comment 
			Sets the popup menu's id and resets items array

		@examples
                        $popup_menu = get_instance("vcl/popup_menu");
                        $popup_menu->begin_menu("my_popup_menu");
	**/
	function begin_menu($menu_id)
	{
		$this->items = array();
		$this->menu_id = $menu_id;
	}

	/** Adds new item to popup menu

		@attrib name=add_item params=name api=1 

		@param text required type=string
			Item's caption
		@param link required type=string
			Item's link

		@examples
                        $popup_menu = get_instance("vcl/popup_menu");
                        $popup_menu->begin_menu("my_popup_menu");
                        $popup_menu->add_item(array(
                                "text" => t("Valik"),
                                "link" => 'http://www.neti.ee'
                        ));
	**/
	function add_item($arr)
	{
		$this->items[] = $arr;
	}

	/** Returns the HTML of the popup menu

		@attrib name=get_menu params=name api=1 

		@param icon required type=string
			Icon image name

		@examples
                        $popup_menu = get_instance("vcl/popup_menu");
                        $popup_menu->begin_menu("my_popup_menu");
                        $popup_menu->add_item(array(
                                "text" => t("Valik"),
                                "link" => 'http://www.neti.ee'
                        ));
			echo $popup_menu->get_menu();
	**/
	function get_menu($param = NULL)
	{
		$this->read_template("js_popup_menu.tpl");

		if (!isset($param["icon"]))
		{
			$icon = "/automatweb/images/blue/obj_settings.gif";
		}
		else
		{
			$icon = "/automatweb/images/icons/".$param["icon"];
		}

		$is = "";
		foreach($this->items as $item)
		{
			$is .= "<a class=\"menuItem\" $item[oncl] href=\"".$item["link"]."\">".$item["text"]."</a>";
		}

		$this->vars(array(
			"MENU_ITEM" => $is,
			"menu_id" => $this->menu_id,
			"menu_icon" => $this->cfg["baseurl"].$icon,
			"alt" => $param["alt"]
		));

		if (!empty($param["text"]))
		{
			$this->vars(array(
				"text" => $param["text"]
			));
			$this->vars(array(
				"HAS_TEXT" => $this->parse("HAS_TEXT")
			));
		}
		else
		{
			$this->vars(array(
				"HAS_ICON" => $this->parse("HAS_ICON")
			));
		}
		return $this->parse();
	}
}
?>
