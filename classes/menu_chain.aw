<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/menu_chain.aw,v 2.7 2002/12/04 14:11:30 duke Exp $
// menu_chain.aw - menüüpärjad

class menu_chain extends aw_template 
{
	function menu_chain($args = array())
	{
		extract($args);
		$this->init("automatweb/menu_chain");
	}

	////
	// !Displays the form for adding or editing a menu chain
	function change($args = array())
	{
		extract($args);
		
		if ($id)
		{
			$title = "Muuda menüüpärga";
			$obj = $this->get_object($id);
			$meta = aw_unserialize($obj["metadata"]);
			$parent = $obj["parent"];
		}
		else
		{
			$title = "Uus menüüpärg";
			$obj = array();
		};

		$meta = (is_array($meta))? $meta : array();

		if ($return_url)
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / $title");
		}
		else
		{
			$this->mk_path($parent,$obj["name"] . " / $title");
		};
			
		$this->read_template("change.tpl");

		$dbo = get_instance("objects");
		$olist = $dbo->get_list();

		$this->vars(array(
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"menus" => $this->multiple_option_list(array_flip($meta),$olist),
			"weburl" => $this->mk_site_orb(array("action" => "view","id" => $id)),
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent,"id" => $id,"return_url" => $return_url)),
		));
		return $this->parse();
	}

	////
	// !Saves the menu_chain object
	function submit($args = array())
	{
		extract($args);
		if ($id)
		{
			$_tmp = $this->get_object($id);
			$par_obj = $this->get_object($_tmp["parent"]);
			$parent = $par_obj["oid"];
			// the menu aliases the parent of this menu has
			if ( ($par_obj["class_id"] == CL_DOCUMENT) || ($par_obj["class_id"] == CL_TABLE) )
			{
				$old_menus = array_merge(array(),aw_unserialize($_tmp["metadata"]));
				$r_old = array_flip($old_menus);
				$menu_chains = $this->get_aliases_for($parent,CL_MENU_ALIAS);
				if (is_array($menu_chains))
				{
					foreach($menu_chains as $key => $val)
					{
						if (isset($r_old[$val["last"]]))
						{
							$this->delete_alias($parent,$val["oid"]);
							$this->delete_object($val["oid"]);
						};
					};
				};
				if (is_array($old_menus))
				{
					foreach($old_menus as $key => $val)
					{
						#$target = $this->get_object($val);
						#print "parent is $parent, val is $val<br>";
						#$this->delete_alias($parent,$val);
						#$this->delete_object($val);
					};
				};
			}
			
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => $menus,
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"status" => 2,
				"metadata" => $menus,
				"class_id" => CL_MENU_CHAIN,
			));
		
			$par_obj = $this->get_object($parent);
			if ( ($par_obj["class_id"] == CL_DOCUMENT) || ($par_obj["class_id"] == CL_TABLE) )
			{
				$this->add_alias($parent,$id);
			};
		};

		if (is_array($menus))
		{
			$mn = get_instance("menu");
			foreach($menus as $key => $val)
			{
				$_id = $mn->create_menu_alias(array(
					"parent" => $parent,
					"menu" => $val,
				));
			}
		};
		$url = $this->mk_my_orb("change",array("id" => $id,"return_url" => urlencode($return_url)));
		return $url;
	}

	function view($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$this->read_template("view.tpl");
		$meta = aw_unserialize($obj["metadata"]);
		$content = "";
		$m = get_instance("menuedit");
		if (is_array($meta))
		{
			foreach($meta as $key => $val)
			{
				$tobj = $this->get_object($val);
				$this->vars(array(
					"name" => $tobj["name"],
					"link" => $this->mk_link(array("?section" => $val)),
				));

				$content .= $this->parse("line");
			};
			$this->vars(array(
				"title" => $obj["name"],
				"line" => $content,
			));
		};
		return $this->parse();
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
        "type" => CL_MENU_CHAIN,
			));
    };
    $f = $this->mcaliases[$matches[3] - 1];
    if (!$f["target"])
    {
			return "";
    }
		return $this->view(array("id" => $f["target"]));
	}
};
?>
