<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/promo.aw,v 2.4 2001/06/18 18:47:13 kristo Exp $

global $orb_defs;
$orb_defs["promo"] = array("new" => array("function" => "add", "params" => array("parent")),
													 "change"	=> array("function" => "change", "params" => array("id")),
													 "submit" => array("function" => "submit", "params" => array()));

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
		classload("objects");
		$ob = new db_objects;
		$menu = $ob->get_list();
		$menu[$GLOBALS["rootmenu"]] = "Esileht";

		// kysime infot adminnitemplatede kohta
		$q = "SELECT * FROM template WHERE type = 0 ORDER BY id";
		$this->db_query($q);
		$edit_templates = array();
		while($tpl = $this->db_fetch_row()) {
			$edit_templates[$tpl[id]] = $tpl[name];
		};
		// kysime infot lyhikeste templatede kohta
		$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
		$this->db_query($q);
		$short_templates = array();
		while($tpl = $this->db_fetch_row()) {
			$short_templates[$tpl[id]] = $tpl[name];
		};

		$this->vars(array("title" => "", "promo_id" => "", "section" => $ob->option_list($parent,$menu),"right_sel" => "","left_sel" => "CHECKED","parent" => $parent,"tpl_edit" => $this->option_list(0,$edit_templates),"tpl_lead" => $this->option_list(0, $short_templates),
											"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
											"interface" => "new"));
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
				$a[$v]=$v;
		}

		$sets = array("section" => $a,"right" => $right,"tpl_lead" => $tpl_lead, "tpl_edit" => $tpl_edit);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $title,"last" => $type,"comment" => serialize($sets)));
			$this->db_query("UPDATE menu SET tpl_lead = '$tpl_lead', tpl_edit = '$tpl_edit' , link = '$link' WHERE id = $id");
			$this->_log("promo", "Muutis promo kasti $title");
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent,"name" => $title,"class_id" => CL_PROMO,"comment" => serialize($sets),"last" => 1,"visible" => $type));
			$this->db_query("INSERT INTO menu (id,link,type,is_l3,tpl_lead,tpl_edit) VALUES($id,'$link',".MN_PROMO_BOX.",0,'$tpl_lead','$tpl_edit')");
			$this->_log("promo", "Lisas promo kasti $title");
		}
		return $this->mk_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add_promo.tpl");
		$this->mk_path($id, "Muuda promo kasti");

		if (!($row = $this->get_object($id)))
			$this->raise_error("promo->gen_change($id): No such box!", true);

		classload("objects");
		$ob = new db_objects;

		$sets = unserialize($row[comment]);

		// kysime infot adminnitemplatede kohta
		$q = "SELECT * FROM template WHERE type = 0 ORDER BY id";
		$this->db_query($q);
		$edit_templates = array();
		while($tpl = $this->db_fetch_row()) {
			$edit_templates[$tpl[id]] = $tpl[name];
		};
		// kysime infot lyhikeste templatede kohta
		$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
		$this->db_query($q);
		$short_templates = array();
		while($tpl = $this->db_fetch_row()) {
			$short_templates[$tpl[id]] = $tpl[name];
		};

		$this->db_query("SELECT * FROM menu WHERE id = $id");
		$rw = $this->db_next();

		$menu = $ob->get_list();
		$menu[$GLOBALS["frontpage"]] = "Esileht";
		$this->vars(array("title"			=> $row[name], 
											"promo_id"	=> $id,
											"section"		=> $ob->multiple_option_list($sets[section],$menu),
											"right_sel"	=> ($sets[right] == 1 ? "CHECKED" : ""),
											"left_sel"	=> ($sets[right] != 1 ? "CHECKED" : ""),
											"parent"		=> $row[parent],
											"link"			=> $rw[link],
											"tpl_edit" => $this->option_list($rw[tpl_edit],$edit_templates),
											"tpl_lead" => $this->option_list($rw[tpl_lead],$short_templates),
											"reforb" => $this->mk_reforb("change", array("id" => $id))));

		return $this->parse();
	}
}
?>

