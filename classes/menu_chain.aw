<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/menu_chain.aw,v 2.1 2001/12/11 12:51:46 duke Exp $
// menu_chain.aw - menüüpärjad
global $orb_defs;
$orb_defs["menu_chain"] = "xml";
class menu_chain extends aw_template {
	function menu_chain($args = array())
	{
		extract($args);
		$this->db_init();
		$this->tpl_init("automatweb/menu_chain");
	}

	function add($args = array())
	{
		extract($args);
		$this->mk_path($parent, " / " . "Lisa uus menüüpärg");
		$this->read_template("add.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent)),
		));
		return $this->parse();
	}

	function change($args = array()){
		extract($args);
		$this->read_template("change.tpl");
		$obj = $this->get_object($id);
		classload("php");
		$phps = new php_serializer();
		$meta = $phps->php_unserialize($obj["metadata"]);
		classload("objects");
		$dbo = new db_objects();
		$olist = $dbo->get_list();
		$this->mk_path($obj["parent"],$obj["name"]);
		if (not(is_array($meta)))
		{
			$meta = array();
		};
		$this->vars(array(
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"menus" => $this->multiple_option_list(array_flip($meta),$olist),
			"weburl" => $this->mk_site_orb(array("action" => "view","id" => $id)),
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent,"id" => $id)),
		));
		return $this->parse();
	}

	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($id)
		{
			$_tmp = $this->get_object($id);
			$par_obj = $this->get_object($_tmp["parent"]);
			classload("php");
			$phps = new php_serializer();
			$meta = $phps->php_serialize($menus);
			
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => $meta,
			));
		
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"status" => 2,
				"class_id" => CL_MENU_CHAIN,
			));
		
			$par_obj = $this->get_object($parent);
			$new = 1;
			if ($par_obj["class_id"] == CL_DOCUMENT)
			{
				$this->add_alias($parent,$id);
			};
		};

		if (not($new) && $par_obj["class_id"] == CL_DOCUMENT)
		{
			return $this->mk_my_orb("list_aliases",array("id" => $par_obj["oid"]),"aliasmgr");
		}
		else
		{
			return $this->mk_my_orb("change",array("id" => $id));
		};
	}

	function view($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		classload("php");
		$phps = new php_serializer();
		$this->read_template("view.tpl");
		$meta = $phps->php_unserialize($obj["metadata"]);
		$content = "";
		classload("menuedit");
		$m = new menuedit();
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
