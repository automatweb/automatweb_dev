<?php
// html_popup.aw - a class to deal with javascript popups
// $Header: /home/cvs/automatweb_dev/classes/Attic/html_popup.aw,v 2.0 2001/10/19 19:52:37 duke Exp $
global $orb_defs;
$orb_defs["html_popup"] = "xml";
class html_popup extends aw_template {
	function html_popup($args = array())
	{
		$this->db_init();
		$this->tpl_init("automatweb/html_popup");

	}

	function add($args = array())
	{
		extract($args);
		classload("objects");
		$ob = new db_objects;
		$menu = $ob->get_list();
		$this->mk_path($parent,"Lisa uus popup");
		$this->read_template("change.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent)),
			"menus" => $ob->multiple_option_list(array(),$menu),
		));
		return $this->parse();
	}

	function submit($args = array())
	{
		extract($args);
		// järelikult on tegu uue objektiga
		if ($parent)
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_HTML_POPUP,
				"status" => 2,
			));
		}
		else
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
			));
		}

		$meta = array(
			"url" => $url,
			"width" => $width,
			"height" => $height,
			"menus" => $menus,
		);

		$this->obj_set_meta(array(
			"oid" => $id,
			"meta" => $meta,
		));

		return $this->mk_orb("change",array("id" => $id));
	}
	
	function change($args = array())
	{
		extract($args);
		classload("objects");
		$ob = new db_objects;
		$menu = $ob->get_list();
		$obj = $this->get_object($id);
		$meta = $this->obj_get_meta(array("oid" => $id));
		$this->mk_path($obj["parent"],"Muuda popuppi");
		$this->read_template("change.tpl");
		if (not($meta["menus"]))
		{
			$meta["menus"] = array();
		};
		this->vars(array(
			"name" => $obj["name"],
			"url" => $meta["url"],
			"menus" => $ob->multiple_option_list(array_flip($meta["menus"]),$menu),
			"width" => $meta["width"],
			"height" => $meta["height"],
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}

		
};
?>
