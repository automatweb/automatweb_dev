<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/pullout.aw,v 2.5 2002/11/07 10:52:24 kristo Exp $
// pullout.aw - Pullout manager
class pullout extends aw_template
{
	function pullout()
	{
		$this->init("pullout");
		$this->align = array(
			"left" => "Vasak",
			"center" => "Keskel",
			"right" => "Paremal"
		);
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		if ($return_url)
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa pullout");
		}
		else
		{
			$this->mk_path($parent,"Lisa pullout");
		}

		$m = get_instance("menuedit");
		$u = get_instance("users");

		$this->vars(array(
			"docs" => $this->picker(0,$m->mk_docsel()),
			"groups" => $this->multiple_option_list(array(),$u->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)))),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent,"return_url" => $return_url,"alias_to" => $alias_to)),
			"align" => $this->picker(0,$this->align),
			"template" => $this->picker(0,$this->get_template_picker())
		));
		
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		
		if ($id)
		{
			$this->upd_object(array(
				"name" => $name,
				"oid" => $id,
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_PULLOUT
			));
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"groups" => $this->make_keys($groups),
				"docs" => $docs,
				"align" => $align,
				"width" => $width,
				"right" => $right,
				"template" => $template,
			),
		));

		if ($alias_to)
		{
			$this->delete_alias($alias_to,$id);
			$this->add_alias($alias_to,$id);
		}

		return $this->mk_my_orb("change", array("id" => $id,"return_url" => urlencode($return_url),"alias_to" => $alias_to));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		
		$o = $this->get_object($id);
		if ($return_url)
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda pullouti");
		}
		else
		{
			$this->mk_path($o["parent"],"Muuda pullouti");
		}

		$m = get_instance("menuedit");
		$u = get_instance("users");

		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));

		$this->vars(array(
			"width" => $meta["width"],
			"right" => $meta["right"],
			"align" => $this->picker($meta["align"],$this->align),
			"docs" => $this->picker($meta["docs"],$m->mk_docsel()),
			"groups" => $this->multiple_option_list($meta["groups"],$u->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)))),
			"template" => $this->picker($meta["template"],$this->get_template_picker()),
			"name" => $o["name"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => $return_url,"alias_to" => $alias_to))
		));

		return $this->parse();
	}

	////
	// !Aliaste parsimine
	function parse_alias($args = array())
	{
		extract($args);
    if (!$alias)
    {
			return "";
    }
		return $this->view(array("id" => $alias["target"],"doc" => $oid));
	}

	function view($arr)
	{
		extract($arr);
		$o = $this->get_object($id);
		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));

		$gidlist = aw_global_get("gidlist");
		$found = false;
		if (is_array($meta["groups"]))
		{
			foreach($meta["groups"] as $gid)
			{
				if ($gidlist[$gid] == $gid)
				{
					$found = true;
				}
			}
		}

		if (!$found || $meta["docs"] == $oid)
		{
			return "";
		}

		$do = get_instance("document");
		$this->read_template($meta["template"]);
		$this->vars(array(
			"width" => $meta["width"],
			"align" => $meta["align"],
			"right" => $meta["right"],
			"content" => $do->gen_preview(array(
				"docid" => $meta["docs"]
			)),
			"title" => $o["name"]
		));
		return $this->parse();
	}

	function get_template_picker()
	{
		$ret = array();
		$this->db_query("SELECT name,filename FROM template WHERE type = 3");
		while ($row = $this->db_next())
		{
			$ret[$row["filename"]] = $row["name"];
		}
		return $ret;
	}
}
?>
