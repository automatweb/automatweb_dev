<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_entry.aw,v 2.5 2001/07/18 16:13:51 duke Exp $

global $orb_defs;

$orb_defs["form_entry"] = "xml";

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

	////
	// !fetchib info mingi entry kohta
	// argumendid:
	// eid(int) - entry id
	function get_entry($args = array())
	{
		extract($args);
		// koigepealt teeme kindlaks, millise vormi juurde see entry kuulub
		$fid = $this->db_fetch_field("SELECT form_id FROM form_entries WHERE id = '$eid'","form_id");
		
		$entry = $this->get_record("form_" . $fid . "_entries","id",$eid);
		
		// if it is part of a chain, then fetch all the other entries as well
		if ($entry["chain_id"])
		{
			$chain_entry = $this->get_record("form_chain_entries","id",$row["chain_id"]);
			$els = $xml->xml_unserialize(array("source" => $chain_entry["ids"]));
		}
		else
		{
			$els = array($fid => $eid);
		};
 
		$block = array();
		foreach($els as $form_id => $entry_id)
		{
			$block = $block + $this->get_record("form_" . $form_id . "_entries","id",$entry_id);
		};
		return $block;
	}
}
?>
