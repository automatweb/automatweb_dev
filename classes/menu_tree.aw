<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/menu_tree.aw,v 2.7 2002/12/17 18:35:03 duke Exp $
// menu_tree.aw - menüüpuu

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property menus type=select multiple=1 size=15
	@caption Menüüd

*/
class menu_tree extends aw_template
{
	function menu_tree()
	{
		$this->init(array(
			"clid" => CL_MENU_TREE,
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
			case "menus":
				$ob = get_instance("objects");
				$menus = $ob->get_list();
				$data["options"] = $menus;
				break;
		}
		return PROP_OK;
        }

	function parse_alias($args = array())
	{
		extract($args);
		$obj = $this->get_obj_meta($alias["target"]);
		$menus = $obj["meta"]["menus"];
		global $DBUG;
		if ($DBUG)
		{
			print "<pre>";
			print_r($menus);
			print "</pre>";
		};
		$folder_list = array();
		// FIXME: this should use menu cache 
		if (is_array($menus))
		{
			$mnl = get_instance("menuedit_light");
			foreach($menus as $val)
			{
				$folder_list = array_merge($folder_list,$mnl->gen_rec_list(array(
						"start_from" => $val,
						"add_start_from" => true,
						"single_tpl" => 1,
						"tpl_name" => "content",
						"tpl" => "menu_tree/menu_tree.tpl",
				)));
				$mnl->level = 0;
			};
		};
		$fl = join("",$folder_list);
		return $fl;
		
	}


}
?>
