<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_entry.aw,v 2.7 2001/07/19 14:24:30 duke Exp $

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
		classload("xml");
		$xml = new xml();
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

	////
	// !Teeb entryst koopia
	// argumendid:
	// eid (int), entry_id, mida kopeerida
	// parent (int), mille alla uu koopia teha. Kui defineerimata, siis jääb samasse kohta
	function cp($args = array())
	{
		extract($args);
		// koigepealt registreerime uue objekti.
		$old = $this->get_object($eid);
		// üle kanname koik andmed, parent-i asendame
		if ($args["parent"])
		{
			$old["parent"] = $args["parent"];
		};
		// acl-iga voib kamm tekkida.
		$new_id = $this->new_object($old);
		
		$oldentry = $this->get_record("form_entries","id",$eid);

		$t = time();

		$q = "INSERT INTO form_entries (id,form_id,contents,tm)
			VALUES ('$oldentry[id]','$oldentry[form_id],$oldentry[contents],$t)";

		$this->db_query($q);

		$ftable = sprintf("form_%s_entries",$oldentry["form_id"]);

		$old_f_entry = $this->get_record($ftable,"id",$eid);

		$old_f_entry["id"] = $new_id;

		$keys = array_keys($old_f_entry);
		$values = array_values($old_f_entry);

		$q = sprintf("INSERT INTO $ftable (%s) VALUES (%s)",join(",",$keys),join(",",map('%s',$values)));

		$this->db_query($q);

	}
}
?>
