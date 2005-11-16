<?php

class popup_menu extends aw_template
{
	var $items;

	function popup_menu()
	{
		$this->init("automatweb/menuedit");
		$this->items = array();
	}

	function begin_menu($menu_id)
	{
		$this->items = array();
		$this->menu_id = $menu_id;
	}

	/**
		@comment
			$arr - popup menu item data, text, link
	**/
	function add_item($arr)
	{
		$this->items[] = $arr;
	}

	/**
		@comment
			$param:
				icon - icon image name
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
			$is .= "<a class=\"menuItem\" href=\"".$item["link"]."\">".$item["text"]."</a>";
		}
		$this->vars(array(
			"MENU_ITEM" => $is,
			"menu_id" => $this->menu_id,
			"menu_icon" => $this->cfg["baseurl"].$icon,
			"alt" => $param["alt"]
		));

		return $this->parse();
	}
}
?>