<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/banner_profile.aw,v 2.3 2001/07/26 12:55:12 kristo Exp $

global $orb_defs;
$orb_defs["banner_profile"] = "xml";
lc_load("banner");
classload("banner");
class banner_profile extends banner
{
	function banner_profile()
	{
		$this->banner();
	lc_load("definition");	
	global $lc_banner;
		if (is_array($lc_banner))
		{
			$this->vars($lc_banner);}
	}

	////
	// !displays the form for creating a new profile
	function add($arr)
	{
		extract($arr);

		classload("config");
		$c = new db_config();
		$fid = $c->get_simple_config("banner_profile_form");
		if (!$fid)
		{
			$this->raise_error(LC_NO_FORM_FOR_BANNER, true);
		}

		$this->mk_path($parent, LC_ADD_BANNER_PROFILE);

		classload("form");
		$f = new form;

		return $f->gen_preview(array(
			"id" => $fid,
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
	}

	function submit($arr)
	{
		extract($arr);

		classload("form");
		$f = new form;

		classload("config");
		$c = new db_config();
		$fid = $c->get_simple_config("banner_profile_form");

		if ($id)
		{
			// save profile.
			$f->process_entry(array("id" => $fid, "entry_id" => $entry_id));
			$name = $f->get_element_value_by_name("nimi");
			$this->upd_object(array("oid" => $id,"name" => $name));
		}
		else
		{
			// add profile.
			$f->process_entry(array("id" => $fid));
			$e_id = $f->entry_id;

			$name = $f->get_element_value_by_name("nimi");

			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_BANNER_PROFILE, "status" => 1, "last" => $e_id,"name" => $name));
		}

		return $this->mk_orb("change", array("id" => $id));
	}

	////
	// !generates the form for changing banner profile
	function change($arr)
	{
		extract($arr);

		$o = $this->get_object($id);
		$e_id = $o["last"];

		classload("config");
		$c = new db_config();
		$fid = $c->get_simple_config("banner_profile_form");

		if (!$fid)
		{
			$this->raise_error(LC_NO_FORM_FOR_BANNER, true);
		}

		$this->mk_path($o["parent"], LC_CHANGE_BANNER_PROFILE);

		classload("form");
		$f = new form;

		return $f->gen_preview(array(
			"id" => $fid,
			"entry_id" => $e_id,
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
	}
}
?>
