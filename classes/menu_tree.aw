<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/menu_tree.aw,v 2.3 2002/06/26 11:26:44 duke Exp $
// menu_tree.aw - menüüpuu
class menu_tree extends aw_template
{
	function menu_tree()
	{
		$this->tpl_init("menu_tree");
		$this->db_init();
	}

	function change($arr)
	{
		extract($arr);
		if ($parent)
		{
			if ($return_url)
			{
				$this->mk_path(0,"<a href='$return_url'>tagasi</a> / Lisa menüüpuu");
			}
			else
			{
				$this->mk_path($parent, "Lisa menüüpuu");
			};
			$sel_menus = array();
		}
		else
		{
			$obj = $this->get_obj_meta($id);
			$name = $obj["name"];
			$sel_menus = $obj["meta"]["menus"];
			if (not(is_array($sel_menus)))
			{
				$sel_menus = array();
			};
			if ($return_url)
			{
				$_return_url = urldecode($return_url);
				$this->mk_path(0,"<a href='$_return_url'>tagasi</a> / Muuda menüüpuud");
			}
			else
			{
				$this->mk_path($obj["parent"], "Muuda menüüpuud");
			};
		};

		$this->read_template("add_tree.tpl");
		$ob = new db_objects;
		$menus = $ob->get_list();
		$this->vars(array(
			"name" => $name,
			"menus" => $this->multiple_option_list(array_flip($sel_menus),$menus),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent,"id" => $id,"alias_to" => $alias_to,"return_url" => $return_url)),
		));
		return $this->parse();
	}

	function submit($arr)
	{
		$this->quote(&$arr);
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_MENU_TREE,
				"name" => $name,
			));
		
			if ($alias_to)
			{
				$this->add_alias($alias_to,$id);
			};
		};
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "menus",
			"value" => $menus,
		));

		
		return $this->mk_orb("change", array("id" => $id,"return_url" => urlencode($return_url)));
	}
	
	function parse_alias($args = array())
	{
		extract($args);
		$obj = $this->get_obj_meta($alias["target"]);
		$menus = $obj["meta"]["menus"];
		$folder_list = array();
		// FIXME: this should use menu cache 
		if (is_array($menus))
		{
			classload("menuedit_light");
			$mnl = new menuedit_light();
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
