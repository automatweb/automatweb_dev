<?php
// $Id: menu_alias.aw,v 2.9 2004/06/25 18:13:31 kristo Exp $
// menu_alias.aw - Deals with menu aliases
class menu_alias extends aw_template
{
	function menu_alias()
	{
		$this->init(array(
			"tpldir" => "automatweb/menu",
		));
	}

	/** Displays the form for adding a new menu alias 
		
		@attrib name=add_alias params=name default="0"
		
		@param parent required type=int
		@param return_url optional
		@param alias_to optional
		
		@returns
		
		
		@comment

	**/
	/**  
		
		@attrib name=new params=name default="0"
		
		@param parent required type=int
		@param return_url optional
		@param alias_to optional
		
		@returns
		
		
		@comment

	**/
	/**  
		
		@attrib name=change params=name default="0"
		
		@param id required type=int
		@param return_url optional
		
		@returns
		
		
		@comment

	**/
	/**  
		
		@attrib name=change_alias params=name default="0"
		
		@param id required type=int
		@param return_url optional
		
		@returns
		
		
		@comment

	**/
	function change_alias($args = array())
	{
		extract($args);
		$this->read_template("change_alias.tpl");
		if ($id)
		{
			$obj = $this->get_object($id);
			$title = "Muuda menüü linki";
		}
		else
		{
			$obj = array();
			$title = "Lisa menüü link";
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

	/**  
		
		@attrib name=submit_alias params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_alias($args = array())
	{
		extract($args);
		if ($id)
		{
			$target = obj($menu);
			$tmp = obj($id);
			$tmp->set_name($target->name());
			$tmp->set_comment($target->comment());
			$tmp->save();
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

		$o = obj();
		$o->set_parent($parent);
		$o->set_name($name);
		$o->set_comment($comment);
		$o->set_class_id(CL_MENU_ALIAS);
		$id = $o->save();
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

		$o = obj($target["oid"]);

		if ($o->prop("link") != "")
		{
			$link = $o->prop("link");
		}	
		else
		{
			$link = $this->cfg["baseurl"]."/".$target["oid"];
		}

		$ltarget = "";
		if ($o->prop("target"))
		{
			$ltarget = "target='_blank'";
		}

		if (aw_global_get("section") == $target["oid"])
		{
			$ret = sprintf("<a $ltarget class=\"sisutekst-sel\" href='$link'>%s</a>",$target["name"]);
		}
		else
		{
			$ret = sprintf("<a $ltarget class=\"sisutekst\" href='$link'>%s</a>",$target["name"]);
		}
		return $ret;
	}

	function _serialize($arr)
	{
		$i = get_instance("admin/admin_menus");
		return $i->_serialize($arr);
	}

	function _unserialize($arr)
	{
		$i = get_instance("admin/admin_menus");
		return $i->_unserialize($arr);
	}
};
?>
