<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/menu_tree.aw,v 2.8 2002/12/19 18:01:51 duke Exp $
// menu_tree.aw - menüüpuu

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property menus type=select multiple=1 size=15
	@caption Menüüd

	@property template type=select 
	@caption Template

*/
class menu_tree extends class_base
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

			case "template":
				$tpldir = $this->cfg["site_basedir"] . "/templates/menu_tree";
				$tpls = $this->get_directory(array(
					"dir" => $tpldir,
				));
				$data["options"] = $tpls;
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
