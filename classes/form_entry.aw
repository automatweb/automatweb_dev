<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_entry.aw,v 2.2 2001/06/14 08:47:39 kristo Exp $

global $orb_defs;

$orb_defs["form_entry"] = array("change" => array("function" => "change", "params" => array("id")),
																"delete" => array("function" => "delete", "params" => array("id"))
																);

// basically this is an interface class :)
// it provides a form_entry manipulating interface to menueditor via orb. 
// but it doesn't contain any of the functionality, it just forwards calls to class form
// well, ok, not an interface class in it's purest meaning, but still pretty cool

class form_entry extends aw_template
{
	function form_entry()
	{
		$this->db_init();
		$this->tpl_init("forms");
	}

	function change($arr)
	{
		extract($arr);
		$f = $this->db_fetch_field("SELECT form_id FROM form_entries WHERE id = $id", "form_id");

		$o = $this->get_object($id);
		$this->mk_path($o["parent"], "Muuda formi sisestust");

		classload("orb");
		$orb = new orb(array("class" => "form", "action" => "show", "vars" => array("id" => $f, "entry_id" => $id)));
		return $orb->get_data();
	}
}
?>
