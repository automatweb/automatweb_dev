<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/promo.aw,v 2.16 2002/09/25 15:05:43 kristo Exp $

classload("objects");
classload("menuedit","users");
class promo extends aw_template
{
	function promo()
	{
		$this->init("promo");
		lc_load("definition");
		$this->lc_load("promo","lc_promo");
	}

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, LC_PROMO_TODAY);

		$this->read_template("add_promo.tpl");
		$ob = new db_objects;
		$menu = $ob->get_list();
		$menu[$this->cfg["rootmenu"]] = LC_PROMO_FRONTPAGE;

		// kysime infot adminnitemplatede kohta
		$q = "SELECT * FROM template WHERE type = 0 ORDER BY id";
		$this->db_query($q);
		$edit_templates = array();
		while($tpl = $this->db_fetch_row()) 
		{
			$edit_templates[$tpl["id"]] = $tpl["name"];
		};
		// kysime infot lyhikeste templatede kohta
		$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
		$this->db_query($q);
		$short_templates = array();
		while($tpl = $this->db_fetch_row()) 
		{
			$short_templates[$tpl["id"]] = $tpl["name"];
		};

		$u = new users;
		$this->vars(array(
			"section" => $ob->option_list($parent,$menu),
			"left_sel" => "CHECKED",
			"tpl_edit" => $this->option_list(0,$edit_templates),
			"tpl_lead" => $this->option_list(0, $short_templates),
			"last_menus" => $this->multiple_option_list(array(),$menu),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"groups" => $this->multiple_option_list(array(),$u->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)))),
		));
		return $this->parse();
	}

	function submit($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		$a = $this->make_keys($section);

		$sets = array(
			"section" => $a,
			"right" => ($right == 1 ? 1 : 0), 
			"up" => ($right == 2 ? 1 : 0), 
			"down" => ($right == 3 ? 1 : 0), 
			"scroll" => ($right == 'scroll' ? 1 : 0),
			"tpl_lead" => $tpl_lead, 
			"tpl_edit" => $tpl_edit,
			"comment" => $comment,
		);

		$com = serialize($sets);

		if ($id)
		{
			$com = $all_menus ? "all_menus" : serialize($sets);
			$this->upd_object(array("oid" => $id, "name" => $title,"last" => $type,"comment" => $com));
			$this->db_query("UPDATE menu SET tpl_lead = '$tpl_lead', tpl_edit = '$tpl_edit' , link = '$link',ndocs = '$num_last',sss = '".serialize($this->make_keys($last_menus))."' WHERE id = $id");
			$this->_log("promo", sprintf(LC_PROMO_CHANGED_PROMO_BOX,$title));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $title,
				"class_id" => CL_PROMO,
				"comment" => $com,
				"last" => 1,
			));
			$this->db_query("INSERT INTO menu (id,link,type,is_l3,tpl_lead,tpl_edit,ndocs,sss) VALUES($id,'$link',".MN_PROMO_BOX.",0,'$tpl_lead','$tpl_edit','$num_last','".serialize($this->make_keys($last_menus))."')");
			$this->_log("promo", sprintf(LC_PROMO_ADD_TO_PROMO_BOX,$title));
		}

		$this->set_object_metadata(array("oid" => $id, "key" => "no_title", "value" => $no_title));
		$this->set_object_metadata(array("oid" => $id, "key" => "link_caption", "value" => $link_caption));
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "groups",
			"value" => $this->make_keys($groups)
		));

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add_promo.tpl");

		if (!($row = $this->get_object($id)))
		{
			$this->raise_error(ERR_PROMO_NOBOX,"promo->gen_change($id): No such box!", true);
		}

		$this->mk_path($row["parent"],LC_PROMO_CHANGE_PROMO_BOX);
		$ob = new db_objects;

		$sets = unserialize($row["comment"]);

		// kysime infot adminnitemplatede kohta
		$q = "SELECT * FROM template WHERE type = 0 ORDER BY id";
		$this->db_query($q);
		$edit_templates = array();
		while($tpl = $this->db_fetch_row()) 
		{
			$edit_templates[$tpl["id"]] = $tpl["name"];
		};
		// kysime infot lyhikeste templatede kohta
		$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
		$this->db_query($q);
		$short_templates = array();
		while($tpl = $this->db_fetch_row()) 
		{
			$short_templates[$tpl["id"]] = $tpl["name"];
		};


		$no_title = $this->get_object_metadata(array("oid" => $id, "key" => "no_title"));
		$link_caption = $this->get_object_metadata(array("oid" => $id, "key" => "link_caption"));
		$groups = $this->get_object_metadata(array("oid" => $id, "key" => "groups"));

		$this->db_query("SELECT * FROM menu WHERE id = $id");
		$rw = $this->db_next();

		$menu = $ob->get_list();
		$menu[0] = "";
		$menu[$this->cfg["frontpage"]] = LC_PROMO_FRONTPAGE;


		$u = new users;
		$this->vars(array(
			"last_menus" => $this->multiple_option_list(unserialize($rw["sss"]), $menu),
			"num_last" => $rw["ndocs"],
			"title" => $row["name"], 
			"section"	=> $ob->multiple_option_list($sets["section"],$menu),
			"all_menus"	=> checked($row["comment"] == "all_menus"),
			"right_sel"	=> ($sets["right"] == 1 ? "CHECKED" : ""),
			"up_sel"	=> ($sets["up"] == 1 ? "CHECKED" : ""),
			"down_sel"	=> ($sets["down"] == 1 ? "CHECKED" : ""),
			"left_sel" => ( ($sets["right"] != 1) && not($sets["scroll"]) && not($sets["up"]) && not($sets["down"])) ? "CHECKED" : "",
			"scroll_sel" => checked($sets["scroll"]),
			"link" => $rw["link"],
			"comment" => $sets["comment"],
			"link_caption" => $link_caption,
			"tpl_edit" => $this->option_list($rw["tpl_edit"],$edit_templates),
			"tpl_lead" => $this->option_list($rw["tpl_lead"],$short_templates),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"groups" => $this->multiple_option_list($groups,$u->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)))),
			"no_title" => checked($no_title)
		));
		return $this->parse();
	}

	////
	// !override the mk_path on core.aw , cause in menuedit mk_path is used in the upper frame, not in the objects frame
	// !and thus must go to a different place when clicked.
	function mk_path($oid,$text = "",$period = 0,$set = true)
	{
		$ext = $this->cfg["ext"];

		$ch = $this->get_object_chain($oid,false,$this->cfg["admin_rootmenu2"]);
		$path = "";
		reset($ch);
		while (list(,$row) = each($ch))
		{
			$path="<a target='list' href='".$this->mk_my_orb("right_frame",array("fastcall" => 1,"parent" => $row["oid"],"period" => $period),"menuedit")."'>".strip_tags($row["name"])."</a> / ".$path;
		}

		if ($set)
		{
			$GLOBALS["site_title"] = $path.$text;
		}
		return $path;
	}
}
?>
