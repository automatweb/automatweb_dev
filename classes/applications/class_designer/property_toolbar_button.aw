<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_toolbar_button.aw,v 1.2 2005/03/03 18:03:57 kristo Exp $
// property_toolbar_button.aw - Taoolbari nupp 
/*

@classinfo syslog_type=ST_PROPERTY_TOOLBAR_BUTTON relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property b_type type=select 
@caption Nupu t&uuml;&uuml;p

@default group=but_props

	@property but_action type=textbox 
	@caption Action

	@property but_tooltip type=textbox
	@caption Tooltip

	@property but_img type=textbox
	@caption Pilt

@default group=men_props 

	@layout hbox1 type=hbox group=men_props
	@property men_toolbar type=toolbar no_caption=1 store=no parent=hbox1

	@layout vbox1 type=vbox group=men_props
	@layout hbox2 type=hbox group=men_props parent=vbox1 width=30%:70%

	@property men_tree type=treeview parent=hbox2 no_caption=1

	@property men_list type=table no_caption=1 parent=hbox2 no_caption=1
	

@groupinfo but_props caption="Nupu m&auml;&auml;rangud"
@groupinfo men_props caption="Nupu m&auml;&auml;rangud" submit=no

*/

class property_toolbar_button extends class_base
{
	function property_toolbar_button()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/property_toolbar_button",
			"clid" => CL_PROPERTY_TOOLBAR_BUTTON
		));

		$this->button_types = array(
			"sep" => "Eraldaja",
			"but" => "Nupp", 
			"men" => "Menu&uuml;&uuml;"
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "b_type":
				$prop["options"] = $this->button_types;
				break;

			case "men_tree":
				$this->get_men_tree($arr);
				break;

			case "men_list":
				$this->get_men_list($arr);
				break;

			case "men_toolbar":
				$this->get_men_toolbar($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "general")
		{
			return true;
		}

		if ($arr["id"] == "but_props" && $arr["obj_inst"]->prop("b_type") == "but")
		{
			return true;
		}

		if ($arr["id"] == "men_props" && $arr["obj_inst"]->prop("b_type") == "men")
		{
			return true;
		}

		return false;
	}

	function get_button($but, &$tb)
	{
		switch($but->prop("b_type"))
		{
			case "sep":
				$tb->add_separator();
				break;

			case "men":
				$this->_get_menu_button($but, $tb);
				break;

			case "but":
				$this->_get_but_button($but, $tb);
				break;

			default:
				break;
		}
	}

	function _get_menu_button($but, &$tb)
	{
		
	}

	function _get_but_button($but, &$tb)
	{
		$tb->add_button(array(
			"name" => $but->name(),
			"action" => $but->prop("but_action"),
			"tooltip" => $but->prop("but_tooltip"),
			"img" => $but->prop("but_img")
		));
	}

	function get_men_tree($arr)
	{
		$items = safe_array($arr["obj_inst"]->meta("but_items"));
		
		$var = "t_item";
		foreach($items as $item)
		{
			$oname = $item["name"];

			if ($arr["request"][$var] == $num)
			{
				$oname = "<b>".$oname."</b>";
			}

			$parent = $item["parent"];
			if ($parent == "")
			{
				$parent = 0;
			}

			$arr["prop"]["vcl_inst"]->add_item($parent,array(
				"name" => $oname,
				"id" => $item["name"],
				"url" => aw_url_change_var ("t_item", $item["id"]),
			));
			
		}
	}

	function _init_men_list_t(&$t)
	{	
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "ord",
			"caption" => "J&auml;rjekord",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "url",
			"caption" => "URL",
			"align" => "center",
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id"
		));
		$t->set_sortable(false);
	}

	function get_men_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_men_list_t($t);

		$items = safe_array($arr["obj_inst"]->meta("but_items"));
		$parent = $arr["request"]["t_item"];

		foreach($items as $nr => $item)
		{
			if ($item["parent"] != $parent)
			{	
				continue;
			}

			$t->define_data(array(
				"name" => html::textbox(array(
					"name" => "items[$nr][name]",
					"value" => $item["name"],
				)),
				"ord" => html::textbox(array(
					"name" => "items[$nr][ord]",
					"value" => $item["ord"],
				)),
				"url" => html::textbox(array(
					"name" => "items[$nr][url]",
					"value" => $item["url"],
				)),
				"id" => $nr
			));
		}

		$nr = count($items) ? max(array_keys($items))+1 :  1; 

		$t->define_data(array(
			"name" => html::textbox(array(
				"name" => "items[$nr][name]",
				"value" => "",
			)),
			"ord" => html::textbox(array(
				"name" => "items[$nr][ord]",
				"value" => "",
			)),
			"url" => html::textbox(array(
				"name" => "items[$nr][url]",
				"value" => "",
			)),
			"id" => $nr
		));
	}

	function get_men_toolbar($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"action" => "save_men",
			"img" => "save.gif"
		));

		$tb->add_button(array(
			"name" => "del",
			"tooltip" => t("Kustuta"),
			"action" => "del_men",
			"img" => "delete.gif"
		));
	}

	function callback_mod_reforb(&$arr)
	{
		$arr["return_url"] = aw_global_get("REQUEST_URI");
		$arr["t_item"] = $_GET["t_item"];
	}

	/**

		@attrib name=save_men

	**/
	function save_men($arr)
	{
		$o = obj($arr["id"]);
		$mens = safe_array($o->meta("but_items"));
		$inf = safe_array($arr["items"]);

		foreach($mens as $nr => $item)
		{
			if ($item["parent"] != $arr["t_item"])
			{
				continue;
			}

			if (!isset($inf[$nr]))
			{
				unset($mens[$nr]);
			}
			else
			{
				$mens[$nr]["name"] = $inf[$nr]["name"];
				$mens[$nr]["ord"] = $inf[$nr]["ord"];
				$mens[$nr]["url"] = $inf[$nr]["url"];
				unset($inf[$nr]);
			}
		}

		foreach($inf as $nr => $it)
		{
			$mens[$nr] = $inf[$nr];
			$mens[$nr]["parent"] = $arr["t_item"];
			$mens[$nr]["id"] = $nr;
		}

		$o->set_meta("but_items", $mens);
		$o->save();

		return $arr["return_url"];
	}

	/**

		@attrib name=del_men

	**/
	function del_men($arr)
	{
		$o = obj($arr["id"]);
		$mens = safe_array($o->meta("but_items"));

		foreach(safe_array($arr["sel"]) as $nr)
		{
			unset($mens[$nr]);
		}

		$o->set_meta("but_items", $mens);
		$o->save();

		return $arr["return_url"];
	}
}
?>
