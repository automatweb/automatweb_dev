<?php
// $Header: /home/cvs/automatweb_dev/classes/banner/Attic/banner_profile.aw,v 1.2 2004/01/13 16:24:18 kristo Exp $

classload("banner/banner");
class banner_profile extends banner
{
	function banner_profile()
	{
		$this->banner();
		lc_load("definition");
		$this->lc_load("banner","lc_banner");
	}

	/** displays the form for creating a new profile 
		
		@attrib name=new params=name default="0"
		
		@param parent required
		
		@returns
		
		
		@comment

	**/
	function add($arr)
	{
		extract($arr);

		$fid = $this->get_cval("banner_profile_form");
		if (!$fid)
		{
			$this->raise_error(ERR_BANNER_NOFORM,LC_NO_FORM_FOR_BANNER, true);
		}

		$this->mk_path($parent, LC_ADD_BANNER_PROFILE);

		$f = get_instance("formgen/form");

		return $f->gen_preview(array(
			"id" => $fid,
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
	}

	/**  
		
		@attrib name=submit params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit($arr)
	{
		extract($arr);

		$f = get_instance("formgen/form");

		$fid = $this->get_cval("banner_profile_form");

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

	/** generates the form for changing banner profile 
		
		@attrib name=change params=name default="0"
		
		@param id required
		@param parent optional
		
		@returns
		
		
		@comment

	**/
	function change($arr)
	{
		extract($arr);

		$o = $this->get_object($id);
		$e_id = $o["last"];

		$fid = $this->get_cval("banner_profile_form");

		if (!$fid)
		{
			$this->raise_error(ERR_BANNER_NOFORM,LC_NO_FORM_FOR_BANNER, true);
		}

		$this->mk_path($o["parent"], LC_CHANGE_BANNER_PROFILE);

		$f = get_instance("formgen/form");

		return $f->gen_preview(array(
			"id" => $fid,
			"entry_id" => $e_id,
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
	}
}
?>
