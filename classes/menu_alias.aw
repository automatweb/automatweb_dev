<?php
// $Id: menu_alias.aw,v 2.3 2003/05/08 10:27:00 kristo Exp $
// menu_alias.aw - Deals with menu aliases
class menu_alias extends aw_template
{
	function menu_alias()
	{
		$this->init(array(
			"tpldir" => "automatweb/menu",
		));
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
			$title = "Muuda men�� linki";
		}
		else
		{
			$obj = array();
			$title = "Lisa men�� link";
		};
		$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / $title");
		$dbo = get_instance("objects");
		$olist = $dbo->get_list();

		$this->vars(array(
			"menu" => $this->picker($obj["last"],$olist),
			"reforb" => $this->mk_reforb("submit_alias",array("id" => $id,"parent" => $parent, "return_url" => $return_url, "alias_to" => $alias_to)),
		));
		return $this->parse();
	}

	function submit_alias($args = array())
	{
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

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}
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
		// I think that add_alias is a mistake
//                $this->add_alias($parent,$id);
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
		$ret = sprintf("<a class=\"sisutekst\" href='".$this->cfg["baseurl"]."/%d'>%s</a>",$target["oid"],$target["name"]);
		return $ret;
	}
};
?>
