<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/promo.aw,v 2.5 2001/06/18 19:17:06 kristo Exp $

global $orb_defs;
$orb_defs["promo"] = "xml";

classload("objects");
class promo extends aw_template
{
	function promo()
	{
		$this->tpl_init("promo");
		$this->db_init();
	}

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, "Lisa promo kast");

		$this->read_template("add_promo.tpl");
		$ob = new db_objects;
		$menu = $ob->get_list();
		$menu[$GLOBALS["rootmenu"]] = "Esileht";

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
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
		));
		return $this->parse();
	}

	function submit($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		if (is_array($section))
		{
			reset($section);
			$a = array();
			while (list(,$v) = each($section))
			{
				$a[$v]=$v;
			}
		}

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
			$this->db_query("UPDATE menu SET tpl_lead = '$tpl_lead', tpl_edit = '$tpl_edit' , link = '$link' WHERE id = $id");
			$this->_log("promo", "Muutis promo kasti $title");
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
			$this->db_query("INSERT INTO menu (id,link,type,is_l3,tpl_lead,tpl_edit) VALUES($id,'$link',".MN_PROMO_BOX.",0,'$tpl_lead','$tpl_edit')");
			$this->_log("promo", "Lisas promo kasti $title");
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

		$this->mk_path($row["parent"],"Muuda promo kasti");
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

		$this->db_query("SELECT * FROM menu WHERE id = $id");
		$rw = $this->db_next();

		$menu = $ob->get_list();
		$menu[0] = "";
		$menu[$GLOBALS["frontpage"]] = "Esileht";
		$this->vars(array(
			"title" => $row["name"], 
			"section"	=> $ob->multiple_option_list($sets["section"],$menu),
			"all_menus"	=> checked($row["comment"] == "all_menus"),
			"right_sel"	=> ($sets["right"] == 1 ? "CHECKED" : ""),
			"left_sel" => ($sets["right"] != 1 ? "CHECKED" : ""),
			"scroll_sel" => checked($sets["scroll"]),
			"link" => $rw["link"],
			"tpl_edit" => $this->option_list($rw["tpl_edit"],$edit_templates),
			"tpl_lead" => $this->option_list($rw["tpl_lead"],$short_templates),
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
		return $this->parse();
	}
}
?>