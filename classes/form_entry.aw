<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_entry.aw,v 2.4 2001/07/12 04:23:45 kristo Exp $

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
		lc_load("definition");
	}

	function change($arr)
	{
		extract($arr);
		$fid = $this->db_fetch_field("SELECT form_id FROM form_entries WHERE id = $id", "form_id");

		$o = $this->get_object($id);
		$this->mk_path($o["parent"], LC_FORM_ENTRY_CHANGE_ENTRY);

		classload("form");
		$f = new form;
		return $f->gen_preview(array("id" => $fid, "entry_id" => $id));
	}
}
?>
