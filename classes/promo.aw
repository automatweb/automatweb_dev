<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/promo.aw,v 2.11 2001/11/20 13:40:23 cvs Exp $
lc_load("promo");
global $orb_defs;
$orb_defs["promo"] = "xml";

classload("objects");
classload("menuedit");
class promo extends aw_template
{
	function promo()
	{
		$this->tpl_init("promo");
		$this->db_init();
		lc_load("definition");
		global $lc_promo;
		if (is_array($lc_promo))
		{
			$this->vars($lc_promo);}
	}

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, LC_PROMO_TODAY);

		$this->read_template("add_promo.tpl");
		$ob = new db_objects;
		$menu = $ob->get_list();
		$menu[$GLOBALS["rootmenu"]] = LC_PROMO_FRONTPAGE;

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

		$this->vars(array(
			"section" => $ob->option_list($parent,$menu),
			"left_sel" => "CHECKED",
			"tpl_edit" => $this->option_list(0,$edit_templates),
			"tpl_lead" => $this->option_list(0, $short_templates),
			"last_menus" => $this->multiple_option_list(array(),$menu),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
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
			"scroll" => ($right == 'scroll' ? 1 : 0),
			"tpl_lead" => $tpl_lead, 
			"tpl_edit" => $tpl_edit
		);

		if ($id)
		{
			$com = $all_menus ? "all_menus" : serialize($sets);
			$this->upd_object(array("oid" => $id, "name" => $title,"last" => $type,"comment" => $com));
			$this->db_query("UPDATE menu SET tpl_lead = '$tpl_lead', tpl_edit = '$tpl_edit' , link = '$link',ndocs = '$num_last',sss = '".serialize($this->make_keys($last_menus))."' WHERE id = $id");
			$this->set_object_metadata(array("oid" => $id, "key" => "no_title", "value" => $no_title));
			$this->_log("promo", sprintf(LC_PROMO_CHANGED_PROMO_BOX,$title));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $title,
				"class_id" => CL_PROMO,
				"comment" => serialize($sets),
				"last" => 1,
			));
			$this->db_query("INSERT INTO menu (id,link,type,is_l3,tpl_lead,tpl_edit,ndocs,sss) VALUES($id,'$link',".MN_PROMO_BOX.",0,'$tpl_lead','$tpl_edit','$num_last','".serialize($this->make_keys($last_menus))."')");
			$this->set_object_metadata(array("oid" => $id, "key" => "no_title", "value" => $no_title));
			$this->_log("promo", sprintf(LC_PROMO_ADD_TO_PROMO_BOX,$title));
		}

		return $this->mk_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add_promo.tpl");

		if (!($row = $this->get_object($id)))
		{
			$this->raise_error("promo->gen_change($id): No such box!", true);
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

		$this->db_query("SELECT * FROM menu WHERE id = $id");
		$rw = $this->db_next();

		$menu = $ob->get_list();
		$menu[0] = "";
		$menu[$GLOBALS["frontpage"]] = LC_PROMO_FRONTPAGE;

		$this->vars(array(
			"last_menus" => $this->multiple_option_list(unserialize($rw["sss"]), $menu),
			"num_last" => $rw["ndocs"],
			"title" => $row["name"], 
			"section"	=> $ob->multiple_option_list($sets["section"],$menu),
			"all_menus"	=> checked($row["comment"] == "all_menus"),
			"right_sel"	=> ($sets["right"] == 1 ? "CHECKED" : ""),
			"left_sel" => ($sets["right"] != 1 ? "CHECKED" : ""),
			"scroll_sel" => checked($sets["scroll"]),
			"link" => $rw["link"],
			"tpl_edit" => $this->option_list($rw["tpl_edit"],$edit_templates),
			"tpl_lead" => $this->option_list($rw["tpl_lead"],$short_templates),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"no_title" => checked($no_title)
		));
		return $this->parse();
	}

	////
	// !override the mk_path on core.aw , cause in menuedit mk_path is used in the upper frame, not in the objects frame
	// !and thus must go to a different place when clicked.
	function mk_path($oid,$text = "",$period = 0,$set = true)
	{
		global $ext;

		$ch = $this->get_object_chain($oid,false,$GLOBALS["admin_rootmenu2"]);
		$path = "";
		reset($ch);
		while (list(,$row) = each($ch))
		{
			$path="<a target='list' href='menuedit_right.$ext?parent=".$row["oid"]."&period=".$period."'>".strip_tags($row["name"])."</a> / ".$path;
		}

		if ($set)
		{
			$GLOBALS["site_title"] = $path.$text;
		}
		return $path;
	}
}
?>
