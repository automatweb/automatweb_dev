<?php
// $Header: /home/cvs/automatweb_dev/classes/menu.aw,v 2.4 2002/06/13 23:06:16 kristo Exp $
// right now this class manages only the functios related to adding menu aliases
// to documents and tables. But I think that all functions dealing with a single
// menu should be moved here.
class menu extends aw_template 
{
	function menu($args = array())
	{
		$this->init("automatweb/menu");
	}

	////
	// !Displays the form for adding a new menu alias
	function change_alias($args = array())
	{
		extract($args);
		$this->read_template("change_alias.tpl");
		if ($id)
		{
			$obj = $this->get_object($id);
			$this->dequote($obj);
			$title = "Muuda men�� linki";
		}
		else
		{
			$obj = array();
			$title = "Lisa men�� link";
		};
		$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / $title");
		classload("objects");
		$dbo = new db_objects();
		$olist = $dbo->get_list();

		$this->vars(array(
			"menu" => $this->picker($obj["last"],$olist),
			"reforb" => $this->mk_reforb("submit_alias",array("id" => $id,"parent" => $parent, "return_url" => $return_url)),
		));
		return $this->parse();
	}

	function submit_alias($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($id)
		{
			$target = $this->get_object($menu);
			$name = $target["name"];
			$comment = $target["comment"];
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"last" => $menu,
			));
		}
		else
		{
			$id = $this->create_menu_alias($args);
		};
		return $this->mk_my_orb("change_alias",array("id" => $id,"return_url" => urlencode($return_url)));
	}

	function create_menu_alias($args = array())
	{
		extract($args);
		$target = $this->get_object($menu);
		$name = $target["name"];
		$comment = $target["comment"];
		$id = $this->new_object(array(
			"parent" => $parent,
			"name" => $name,
			"comment" => $comment,
			"class_id" => CL_MENU_ALIAS,
			"last" => $menu,
		));
		$this->add_alias($parent,$id);
		return $id;
	}

	
	////
	// !Aliaste parsimine
	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->mcaliases))
		{
	    $this->mcaliases = $this->get_aliases(array(
        "oid" => $oid,
        "type" => CL_PSEUDO,
      ));
    };
    $f = $this->mcaliases[$matches[3] - 1];
    if (!$f["target"])
    {
			return "";
    }
		$target = $f;
		return sprintf("<a href='".$this->cfg["baseurl"]."/%d'>%s</a>",$target["oid"],$target["name"]);
	}
};
?>
